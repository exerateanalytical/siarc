<?php

namespace App\Modules\Events\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Businesses\Models\Business;
use App\Modules\Events\Models\Event;
use App\Modules\Events\Models\EventAttendee;
use App\Modules\Events\Models\EventExhibitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::published()->with('industry');

        if ($request->query('filter') === 'upcoming') {
            $query->upcoming()->orderBy('starts_at');
        } elseif ($request->query('filter') === 'past') {
            $query->past()->orderByDesc('starts_at');
        } else {
            $query->orderByDesc('starts_at');
        }

        $events = $query->paginate(20)->withQueryString();

        return response()->json([
            'data' => $events->map(fn (Event $e) => $this->summary($e)),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'total'        => $events->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $event = Event::published()
            ->with(['industry', 'exhibitingBusinesses' => fn ($q) => $q->where('businesses.status', 'published')])
            ->where('slug', $slug)
            ->firstOrFail();

        $attendeeCount = EventAttendee::where('event_id', $event->id)->where('status', '!=', 'cancelled')->count();

        return response()->json(['data' => $this->summary($event) + [
            'description_fr' => $event->description_fr,
            'description_en' => $event->description_en,
            'attendee_count' => $attendeeCount,
            'exhibitors'     => $event->exhibitingBusinesses->map(fn ($b) => [
                'name_fr'      => $b->name_fr,
                'name_en'      => $b->name_en,
                'slug'         => $b->slug,
                'booth_number' => $b->pivot->booth_number,
            ]),
        ]]);
    }

    public function attend(Request $request, string $slug): JsonResponse
    {
        $event = Event::published()->where('slug', $slug)->firstOrFail();

        $attendee = EventAttendee::firstOrCreate(
            ['event_id' => $event->id, 'user_id' => $request->user()->id],
            ['status' => 'registered', 'registered_at' => now()]
        );

        return response()->json([
            'message' => 'Attendance registered.',
            'data'    => ['event' => $event->slug, 'status' => $attendee->status],
        ], $attendee->wasRecentlyCreated ? 201 : 200);
    }

    public function cancelAttend(Request $request, string $slug): JsonResponse
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        EventAttendee::where('event_id', $event->id)->where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Attendance cancelled.']);
    }

    public function exhibit(Request $request, string $slug): JsonResponse
    {
        $business = Business::where('user_id', $request->user()->id)->first();
        if (! $business) {
            return response()->json(['message' => 'You need a business to register as an exhibitor.'], 422);
        }

        $event = Event::published()->where('slug', $slug)->firstOrFail();

        $exhibitor = EventExhibitor::firstOrCreate(
            ['event_id' => $event->id, 'business_id' => $business->id],
            ['status' => 'confirmed', 'registered_at' => now()]
        );

        return response()->json([
            'message' => 'Business registered as exhibitor.',
            'data'    => ['event' => $event->slug, 'business' => $business->slug, 'status' => $exhibitor->status],
        ], $exhibitor->wasRecentlyCreated ? 201 : 200);
    }

    private function summary(Event $event): array
    {
        return [
            'slug'        => $event->slug,
            'name_fr'     => $event->name_fr,
            'name_en'     => $event->name_en,
            'location_fr' => $event->location_fr,
            'location_en' => $event->location_en,
            'starts_at'   => $event->starts_at?->toIso8601String(),
            'ends_at'     => $event->ends_at?->toIso8601String(),
            'cover_url'   => $event->cover_url,
            'industry'    => $event->industry ? [
                'slug'    => $event->industry->slug,
                'name_fr' => $event->industry->name_fr,
                'name_en' => $event->industry->name_en,
            ] : null,
        ];
    }
}
