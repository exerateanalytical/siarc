<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\BusinessReview;
use App\Modules\Messaging\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewWebController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login?next=' . urlencode($request->input('return_to', '/')));
        }

        $data = $request->validate([
            'business_slug' => ['required', 'string', 'exists:businesses,slug'],
            'rating'        => ['required', 'integer', 'min:1', 'max:5'],
            'title'         => ['nullable', 'string', 'max:150'],
            'body'          => ['nullable', 'string', 'max:2000'],
            'return_to'     => ['nullable', 'string'],
        ]);

        $business = Business::where('slug', $data['business_slug'])->firstOrFail();
        $user     = User::findOrFail($siacUser['id']);

        if ($business->user_id === $user->id) {
            return back()->withErrors(['rating' => 'Vous ne pouvez pas évaluer votre propre entreprise.']);
        }

        $isVerifiedContact = Conversation::where('business_id', $business->id)
            ->where('buyer_id', $user->id)
            ->exists();

        BusinessReview::updateOrCreate(
            ['reviewer_id' => $user->id, 'business_id' => $business->id],
            [
                'rating'              => $data['rating'],
                'title'               => $data['title'] ?? null,
                'body'                => $data['body'] ?? null,
                'is_verified_contact' => $isVerifiedContact,
                'status'              => 'published',
            ]
        );

        return redirect($data['return_to'] ?? '/')
            ->with('success', 'Merci pour votre avis.');
    }

    public function markDeal(Request $request, int $conversationId): RedirectResponse
    {
        $siacUser = session('siac_user');
        if (! $siacUser) {
            return redirect('/login');
        }

        $user         = User::findOrFail($siacUser['id']);
        $conversation = Conversation::with('business')->findOrFail($conversationId);

        if (! $conversation->business || $conversation->business->user_id !== $user->id) {
            abort(403);
        }

        $conversation->update(['deal_marked_at' => now()]);

        return redirect()->route('messages.thread', ['id' => $conversationId])
            ->with('success', 'Affaire marquée comme conclue.');
    }
}
