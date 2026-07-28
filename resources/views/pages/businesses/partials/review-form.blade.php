{{--
    The form a signed-in buyer writes an artisan review in.

    INCLUDE CONTRACT
    ----------------
        @include('pages.businesses.partials.review-form', [
            'business' => $business,   // required — App\Modules\Businesses\Models\Business
            'lang'     => $lang,       // optional — 'fr' | 'en', defaults to 'fr'
        ])

    Nothing else is needed and nothing else is read. The partial finds the
    signed-in account itself from session('siac_user'), asks
    App\Support\ArtisanReviews::canReview() whether that account may write, and
    renders either the form or the reason it cannot. It is safe to include
    unconditionally: for a guest it renders a sign-in prompt, never a broken
    form. It posts to the existing route('reviews.store') and returns to the
    current URL.

    WHY THE FORM SAYS WHAT IT SAYS
    ------------------------------
    Two sentences here are not decoration and must not be trimmed.

    The first is that the review goes to moderation. It does — nothing written
    here reaches a profile until a named person has read it — and telling the
    author up front is the difference between a delay they understand and a
    system that appears to have swallowed their words.

    The second is what the platform will say beside their review if it can. The
    design this came from called that badge "Verified Buyer". It cannot be:
    this platform is not a party to sales, holds no orders and moves no money,
    so it has no way of ever knowing whether somebody bought anything. What it
    can check is that this account sent this artisan a message through the
    platform's own messaging. That is what the badge asserts and all it
    asserts, and the author is told so before they write rather than left to
    infer a claim the platform cannot support.

    There is no "verified" checkbox for the author to tick. The flag is computed
    from the messaging tables at submission; a badge the beneficiary can set is
    not a check.
--}}

@php
    $lang = $lang ?? 'fr';
    $isFr = $lang === 'fr';

    $siacUser    = session('siac_user');
    $reviewUser  = $siacUser ? \App\Modules\Auth\Models\User::find($siacUser['id']) : null;
    $verdict     = \App\Support\ArtisanReviews::canReview($business, $reviewUser);
    $existing    = $reviewUser
        ? \Illuminate\Support\Facades\DB::table('business_reviews')
            ->where('business_id', $business->id)->where('reviewer_id', $reviewUser->id)->first()
        : null;
    $willBeBadged = $reviewUser ? \App\Support\ArtisanReviews::verifiedContact($business, $reviewUser) : false;
@endphp

<div class="ui-card" data-review-form>
    <div class="ui-card-head">
        <p class="ui-card-title">
            {{ $existing
               ? ($isFr ? 'Modifier votre avis' : 'Edit your review')
               : ($isFr ? 'Donner votre avis'   : 'Leave a review') }}
        </p>
    </div>

    <div class="px-4 py-4 space-y-4">

        @if(session('review_submitted'))
        <div class="ui-alert ui-alert-ok" role="status">
            <i data-lucide="check" class="w-4 h-4"></i>
            <span>
                {{ $isFr
                   ? "Merci. Votre avis part en relecture ; il paraîtra sur ce profil une fois relu par une personne de l'équipe."
                   : 'Thank you. Your review has gone for review and will appear on this profile once a person on the team has read it.' }}
            </span>
        </div>
        @endif

        @if(! $verdict['allowed'])

            @if($verdict['reason'] === 'guest')
            <p class="ui-body">
                {{ $isFr ? 'Connectez-vous pour donner votre avis sur cet atelier.' : 'Sign in to review this workshop.' }}
            </p>
            <a href="{{ url('/login?next=' . urlencode(request()->fullUrl())) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                {{ $isFr ? 'Se connecter' : 'Sign in' }}
            </a>

            @elseif($verdict['reason'] === 'unverified')
            <p class="ui-body">
                {{ $isFr
                   ? "Confirmez d'abord votre adresse e-mail. Un avis engage le nom d'un artisan, et un compte que personne n'a confirmé peut être créé à l'infini."
                   : 'Confirm your email address first. A review carries weight against an artisan’s name, and an unconfirmed account can be created without limit.' }}
            </p>

            @elseif($verdict['reason'] === 'own_business')
            <p class="ui-body">
                {{ $isFr ? 'Ceci est votre propre atelier : vous ne pouvez pas l’évaluer.' : 'This is your own workshop: you cannot review it.' }}
            </p>

            @else
            <p class="ui-body">
                {{ $isFr ? "Votre compte ne peut pas déposer d'avis pour le moment." : 'Your account cannot leave a review at the moment.' }}
            </p>
            @endif

        @else

        @if($existing)
        <p class="ui-hint">
            {{ $isFr
               ? "Vous avez déjà écrit sur cet atelier. Ce formulaire remplace votre avis : le texte modifié repart en relecture."
               : 'You have already written about this workshop. This form replaces your review, and the changed text goes back for review.' }}
        </p>
        @endif

        <form method="POST" action="{{ route('reviews.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="business_slug" value="{{ $business->slug }}">
            <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">

            <div>
                <label class="ui-label" for="review-rating">
                    {{ $isFr ? 'Votre note' : 'Your rating' }}<span class="ui-req">*</span>
                </label>
                <select id="review-rating" name="rating" required class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Choisir…' : 'Choose…' }}</option>
                    @foreach([5, 4, 3, 2, 1] as $star)
                    <option value="{{ $star }}" @selected(old('rating', $existing->rating ?? null) == $star)>
                        {{ $star }} / 5
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label" for="review-title">{{ $isFr ? 'Titre' : 'Title' }}</label>
                <input id="review-title" name="title" type="text" maxlength="150" class="ui-field"
                       value="{{ old('title', $existing->title ?? '') }}">
            </div>

            <div>
                <label class="ui-label" for="review-body">{{ $isFr ? 'Votre commentaire' : 'Your comment' }}</label>
                <textarea id="review-body" name="body" rows="5" maxlength="2000" class="ui-field ui-textarea"
                          placeholder="{{ $isFr ? "Décrivez le travail, l'échange, les délais." : 'Describe the work, the exchange, the timing.' }}">{{ old('body', $existing->body ?? '') }}</textarea>
            </div>

            {{-- What the platform will and will not vouch for beside these words. --}}
            @if($willBeBadged)
            <p class="ui-hint">
                <i data-lucide="message-square" class="w-3.5 h-3.5 inline-block align-[-2px]"></i>
                {{ $isFr
                   ? "Votre avis portera la mention « " . \App\Support\ArtisanReviews::contactBadgeLabel('fr') . " », parce que vous avez écrit à cet atelier depuis la messagerie. La plateforme n'étant partie à aucune vente, elle ne peut rien certifier d'autre — surtout pas un achat."
                   : 'Your review will carry the note “' . \App\Support\ArtisanReviews::contactBadgeLabel('en') . '”, because you wrote to this workshop through the messaging. The platform is party to no sale, so it can certify nothing beyond that — least of all a purchase.' }}
            </p>
            @else
            <p class="ui-hint">
                {{ $isFr
                   ? "Vous n'avez pas encore écrit à cet atelier depuis la messagerie ; votre avis paraîtra donc sans mention de contact. La plateforme n'atteste que ce qu'elle peut vérifier chez elle."
                   : 'You have not yet written to this workshop through the messaging, so your review will appear with no contact note. The platform attests only to what it can check in its own records.' }}
            </p>
            @endif

            <p class="ui-hint">
                {{ $isFr
                   ? "Votre avis est relu par une personne de l'équipe avant d'apparaître sur ce profil."
                   : 'Your review is read by a person on the team before it appears on this profile.' }}
            </p>

            <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ $existing
                   ? ($isFr ? 'Mettre à jour mon avis' : 'Update my review')
                   : ($isFr ? 'Envoyer mon avis'       : 'Send my review') }}
            </button>
        </form>

        @endif

    </div>
</div>
