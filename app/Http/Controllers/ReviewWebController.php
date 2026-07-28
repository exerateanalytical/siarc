<?php

namespace App\Http\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Businesses\Models\Business;
use App\Modules\Businesses\Models\BusinessReview;
use App\Modules\Messaging\Models\Conversation;
use App\Support\ArtisanReviews;
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
        $lang     = webLang($request);

        /*
         * Everything this method used to decide for itself now belongs to
         * App\Support\ArtisanReviews. It published on write, which put a
         * stranger's sentence on an artisan's livelihood with no moderator in
         * between; and it set the contact badge from the mere existence of a
         * conversation row, which is created the moment somebody clicks a
         * button and proves nothing. Both were quiet claims the platform could
         * not stand behind.
         */
        try {
            ArtisanReviews::submit($business, $user, [
                'rating' => $data['rating'],
                'title'  => $data['title'] ?? null,
                'body'   => $data['body'] ?? null,
            ]);
        } catch (\DomainException $e) {
            return back()->withErrors(['rating' => $this->refusalMessage($e->getMessage(), $lang)]);
        }

        return redirect($data['return_to'] ?? '/')
            ->with('review_submitted', true)
            ->with('success', $lang === 'fr'
                ? "Merci. Votre avis a été transmis à la modération ; il paraîtra sur le profil une fois relu."
                : 'Thank you. Your review has gone to moderation and will appear on the profile once it has been read.');
    }

    /**
     * Turns a refusal code into a sentence the reader can act on.
     *
     * The service throws with the machine reason in it; a person reading the
     * form needs to know which of "sign in", "confirm your address" and "this
     * is your own workshop" applies, because only two of the three are
     * something they can do anything about.
     */
    private function refusalMessage(string $raw, string $lang): string
    {
        $isFr = $lang === 'fr';

        if (str_contains($raw, 'own_business')) {
            return $isFr
                ? "Vous ne pouvez pas évaluer votre propre atelier."
                : 'You cannot review your own workshop.';
        }
        if (str_contains($raw, 'unverified')) {
            return $isFr
                ? "Confirmez d'abord votre adresse e-mail : un avis engage le nom d'un artisan."
                : 'Confirm your email address first — a review carries weight against an artisan’s name.';
        }
        if (str_contains($raw, 'account_inactive')) {
            return $isFr ? "Votre compte n'est pas actif." : 'Your account is not active.';
        }

        return $isFr
            ? "La note doit être un nombre entier de 1 à 5."
            : 'The rating must be a whole number from 1 to 5.';
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
