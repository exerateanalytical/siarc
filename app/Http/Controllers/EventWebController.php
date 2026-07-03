<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Events\Models\Event;
use App\Modules\Events\Models\EventAttendee;
use App\Modules\Events\Models\EventExhibitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventWebController extends Controller
{
    private function lang(Request $request): string
    {
        $lang = $request->query('lang', $request->cookie('lang', 'fr'));
        return in_array($lang, ['fr', 'en']) ? $lang : 'fr';
    }

    public function index(Request $request)
    {
        $lang = $this->lang($request);

        $upcoming = Event::published()->upcoming()->orderBy('starts_at')->get();
        $past = Event::published()->past()->orderByDesc('starts_at')->limit(12)->get();

        return response(view('pages.events.index', compact('lang', 'upcoming', 'past')))
            ->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function ticket(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        $event = Event::published()->where('slug', $slug)->firstOrFail();

        return response(view('pages.events.ticket', compact('lang', 'event')))
            ->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function show(Request $request, string $slug)
    {
        $lang = $this->lang($request);

        $event = Event::published()->with(['industry', 'exhibitingBusinesses.industry', 'exhibitingBusinesses.city'])
            ->where('slug', $slug)->firstOrFail();

        $siacUser = session('siac_user');
        $myBusiness = null;
        $isExhibiting = false;
        $isAttending = false;

        if ($siacUser) {
            $myBusiness = Business::where('user_id', $siacUser['id'])->first();
            if ($myBusiness) {
                $isExhibiting = EventExhibitor::where('event_id', $event->id)->where('business_id', $myBusiness->id)->exists();
            }
            $isAttending = EventAttendee::where('event_id', $event->id)->where('user_id', $siacUser['id'])->exists();
        }

        $attendeeCount = EventAttendee::where('event_id', $event->id)->where('status', '!=', 'cancelled')->count();

        return response(view('pages.events.show', compact('lang', 'event', 'siacUser', 'myBusiness', 'isExhibiting', 'isAttending', 'attendeeCount')))
            ->cookie('lang', $lang, 60 * 24 * 30);
    }

    public function attend(Request $request, string $slug): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }

        $event = Event::published()->where('slug', $slug)->firstOrFail();

        EventAttendee::firstOrCreate(
            ['event_id' => $event->id, 'user_id' => $siacUser['id']],
            ['status' => 'registered', 'registered_at' => now()]
        );

        return back()->with('success', $lang === 'fr' ? 'Votre participation a été enregistrée.' : 'Your attendance has been registered.');
    }

    public function cancelAttend(Request $request, string $slug): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $event = Event::where('slug', $slug)->firstOrFail();
        EventAttendee::where('event_id', $event->id)->where('user_id', $siacUser['id'])->delete();

        return back()->with('success', $lang === 'fr' ? 'Participation annulée.' : 'Attendance cancelled.');
    }

    public function exhibit(Request $request, string $slug): RedirectResponse
    {
        $lang = $this->lang($request);
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->fullUrl()));
        }

        $business = Business::where('user_id', $siacUser['id'])->first();
        if (! $business) {
            return back()->withErrors(['exhibit' => $lang === 'fr' ? 'Vous devez avoir une entreprise pour vous inscrire comme exposant.' : 'You need a business to register as an exhibitor.']);
        }

        $event = Event::published()->where('slug', $slug)->firstOrFail();

        EventExhibitor::firstOrCreate(
            ['event_id' => $event->id, 'business_id' => $business->id],
            ['status' => 'confirmed', 'registered_at' => now()]
        );

        return back()->with('success', $lang === 'fr' ? 'Votre entreprise est inscrite comme exposant.' : 'Your business is registered as an exhibitor.');
    }
}
