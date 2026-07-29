@extends('layouts.admin')

@php
/*
 * The moderator's desk for what buyers say about artisans, and for the
 * distinctions the platform repeats on their behalf.
 *
 * Two registers on one page because they fail in the same way. A review reaches
 * the public profile only when a named person here decides it should, and an
 * award reaches it only when a named person here says they saw the evidence.
 * Neither happens by writing a row. The table this platform inherited defaulted
 * reviews to published, which meant a stranger could put a sentence on an
 * artisan's livelihood with nobody in between; the queue below is what replaced
 * that default.
 *
 * The badge column is worth reading carefully, because the design it comes from
 * called it "Verified Buyer" and it is not that. This platform is not a party
 * to sales — no orders, no funds, no way of ever knowing whether somebody
 * bought anything. What it can check is that this account sent this artisan a
 * message through the platform's own messaging, and that is the whole of what
 * the badge says. A moderator must not read it as proof of a purchase, and the
 * wording here refuses to let them.
 *
 * Rejecting and withdrawing both take a reason, in a field that is required in
 * the route as well as here. Publishing does not: approving is agreeing with
 * what is already written, and there is nothing extra to say.
 *
 * Deliberately absent: bulk publish. Reading a review is the work; a control
 * that approves thirty at once is a control for approving thirty nobody read.
 */

$isFr      = $lang === 'fr';
$pageTitle = $isFr ? 'Avis et distinctions' : 'Reviews and distinctions';

$stateLabels = [
    'pending'   => $isFr ? 'En attente de relecture' : 'Waiting to be read',
    'published' => $isFr ? 'Publiés'                 : 'Published',
    'rejected'  => $isFr ? 'Refusés'                 : 'Refused',
    'hidden'    => $isFr ? 'Retirés'                 : 'Withdrawn',
];
@endphp

@section('content')
<div class="max-w-5xl space-y-5">

    @include('pages.partials.admin-moderation-tabs', ['isFr' => $isFr, 'modTab' => 'reviews'])

    <div>
        <h1 class="ui-h text-[19px]">{{ $pageTitle }}</h1>
        <p class="ui-hint mt-1">
            {{ $isFr
               ? "Rien ne paraît sur un profil sans qu'une personne nommée l'ait décidé ici. La plateforme n'est pas partie aux ventes : elle ne peut donc pas certifier un achat, seulement un échange passé par sa messagerie."
               : 'Nothing appears on a profile unless a named person decided it here. The platform is not a party to sales, so it cannot certify a purchase — only an exchange that passed through its own messaging.' }}
        </p>
    </div>

    @if(session('review_moderated') || session('award_recorded'))
    <div class="ui-alert ui-alert-ok" role="status">
        <i data-lucide="check" class="w-4 h-4"></i>
        <span>{{ $isFr ? 'Votre décision est enregistrée et attribuée à votre compte.' : 'Your decision is recorded and attributed to your account.' }}</span>
    </div>
    @endif

    {{-- ══ Reviews ══════════════════════════════════════════════════════════ --}}

    <div class="flex flex-wrap gap-1.5">
        @foreach($states as $state)
        <a href="{{ route('admin.reviews', ['status' => $state, 'lang' => $lang]) }}"
           class="ui-btn ui-btn-sm {{ $filter === $state ? 'ui-btn-primary' : 'ui-btn-secondary' }}">
            {{ $stateLabels[$state] ?? $state }} ({{ $counts[$state] ?? 0 }})
        </a>
        @endforeach
    </div>

    @if($rows->isEmpty())
    @php
        /* This desk opens on "pending", which is empty whenever the moderators
           are caught up — while the 128 already-published reviews sit one tab
           away. An empty table with no explanation reads as a dead page, so
           name the queue, say what fills it, and point at the tabs that do
           hold rows. $counts is the real per-state tally. */
        $elsewhere = collect($counts)->filter(fn ($n, $s) => $s !== $filter && $n > 0);
    @endphp
    <div class="ui-card ui-card--flush">
        @include('pages.partials.empty-state', [
            'icon'  => 'message-square',
            'state' => 'empty',
            'title' => $isFr
                ? 'Aucun avis dans la file « ' . ($stateLabels[$filter] ?? $filter) . ' »'
                : 'No review in the "' . ($stateLabels[$filter] ?? $filter) . '" queue',
            'body'  => ($isFr
                ? 'Les avis laissés par les acheteurs sur les profils d\'artisans arrivent d\'abord ici pour relecture, puis sont publiés, refusés ou retirés.'
                : 'Reviews left by buyers on artisan profiles land here first to be read, then get published, refused or withdrawn.')
                . ' ' . ($elsewhere->isNotEmpty()
                    ? ($isFr ? 'Les autres files ne sont pas vides : ' : 'The other queues are not empty: ')
                      . $elsewhere->map(fn ($n, $s) => ($stateLabels[$s] ?? $s) . ' (' . $n . ')')->implode(', ') . '.'
                    : ($isFr ? 'Aucune autre file ne contient d\'avis non plus.' : 'No other queue holds a review either.')),
        ])
    </div>
    @endif

    @foreach($rows as $r)
    @php
        $waiting = \Illuminate\Support\Carbon::parse($r->created_at)->diffInDays(now());
    @endphp
    <div class="ui-card" data-review="{{ $r->id }}" data-review-status="{{ $r->status }}">
        <div class="ui-card-head">
            <div class="min-w-0">
                <p class="ui-card-title">{{ $r->business_name ?: ($isFr ? 'Atelier inconnu' : 'Unknown workshop') }}</p>
                <p class="ui-card-sub">
                    {{ $isFr ? 'Avis de' : 'Review by' }} {{ $r->reviewer_name ?: ($isFr ? 'compte supprimé' : 'deleted account') }}
                    @if($r->reviewer_email) <span class="ui-muted">· {{ $r->reviewer_email }}</span> @endif
                </p>
            </div>
            <span class="ui-pill ui-pill-neutral">
                {{ (int) $r->rating }}/5 · {{ $isFr ? 'depuis' : 'waiting' }} {{ $waiting }} {{ $isFr ? 'j' : 'd' }}
            </span>
        </div>

        <div class="px-4 py-4 space-y-4">

            {{-- The badge, stated in full. It is computed at submission from the
                 messaging tables and the reviewer has no say in it. --}}
            @if($r->is_verified_contact)
            <p class="ui-hint">
                <i data-lucide="message-square" class="w-3.5 h-3.5 inline-block align-[-2px]"></i>
                {{ \App\Support\ArtisanReviews::contactBadgeLabel($lang) }}.
                {{ $isFr
                   ? "Ce n'est pas une preuve d'achat : la plateforme n'en tient aucune."
                   : 'This is not proof of a purchase: the platform holds no such thing.' }}
            </p>
            @else
            <p class="ui-hint">
                {{ $isFr
                   ? "Aucun échange constaté entre ce compte et cet atelier sur la plateforme."
                   : 'No exchange between this account and this workshop was found on the platform.' }}
            </p>
            @endif

            @if($r->title)
            <p class="ui-body font-medium">{{ $r->title }}</p>
            @endif

            @if($r->body)
            <p class="ui-body whitespace-pre-line">{{ $r->body }}</p>
            @else
            <p class="ui-hint">{{ $isFr ? 'Note seule, sans texte.' : 'A rating only, with no text.' }}</p>
            @endif

            @if($r->moderated_at)
            <dl class="ui-dl">
                <dt class="ui-dt">{{ $isFr ? 'Décidé par' : 'Decided by' }}</dt>
                <dd class="ui-dd">{{ $r->moderated_by ?: ($isFr ? 'aucun modérateur enregistré' : 'no moderator recorded') }}</dd>
                <dt class="ui-dt">{{ $isFr ? 'Le' : 'On' }}</dt>
                <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($r->moderated_at)->format('d/m/Y H:i') }}</dd>
                @if($r->moderation_note)
                <dt class="ui-dt">{{ $isFr ? 'Motif' : 'Reason' }}</dt>
                <dd class="ui-dd">{{ $r->moderation_note }}</dd>
                @endif
            </dl>
            @endif

            @if($r->status === \App\Support\ArtisanReviews::PENDING)
            <div class="grid gap-4 sm:grid-cols-2 pt-1">
                <form method="POST" action="{{ route('admin.reviews.publish', ['id' => $r->id]) }}">
                    @csrf
                    <p class="ui-hint">
                        {{ $isFr
                           ? "Publier, c'est mettre ce texte sur le profil sous la responsabilité de la plateforme."
                           : 'Publishing puts this text on the profile under the platform’s own responsibility.' }}
                    </p>
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm mt-3">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        {{ $isFr ? 'Publier — je l’ai lu' : 'Publish — I have read it' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.reviews.reject', ['id' => $r->id]) }}">
                    @csrf
                    <label class="ui-label" for="reject-{{ $r->id }}">
                        {{ $isFr ? 'Motif du refus' : 'Reason for refusing' }}<span class="ui-req">*</span>
                    </label>
                    <input id="reject-{{ $r->id }}" name="reason" type="text" required minlength="3" maxlength="500"
                           class="ui-field"
                           placeholder="{{ $isFr ? 'ex. propos injurieux, sans rapport avec le travail' : 'e.g. abusive, unrelated to the work' }}">
                    <p class="ui-hint">
                        {{ $isFr
                           ? "Obligatoire. C'est ce que lira la personne qui rouvrira ce dossier."
                           : 'Required. It is what the next person to reopen this file will read.' }}
                    </p>
                    <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm mt-3">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        {{ $isFr ? 'Refuser' : 'Refuse' }}
                    </button>
                </form>
            </div>
            @elseif($r->status === \App\Support\ArtisanReviews::PUBLISHED)
            <form method="POST" action="{{ route('admin.reviews.hide', ['id' => $r->id]) }}">
                @csrf
                <label class="ui-label" for="hide-{{ $r->id }}">
                    {{ $isFr ? 'Motif du retrait' : 'Reason for withdrawing' }}<span class="ui-req">*</span>
                </label>
                <input id="hide-{{ $r->id }}" name="reason" type="text" required minlength="3" maxlength="500"
                       class="ui-field"
                       placeholder="{{ $isFr ? 'ex. contesté et non étayé' : 'e.g. disputed and unsupported' }}">
                <p class="ui-hint">
                    {{ $isFr
                       ? "L'avis quitte le profil mais reste au registre : un texte publié puis retiré est un fait, pas une erreur à effacer."
                       : 'The review leaves the profile but stays in the register: text that was public and was withdrawn is a fact, not a mistake to erase.' }}
                </p>
                <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm mt-3">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                    {{ $isFr ? 'Retirer du profil' : 'Withdraw from the profile' }}
                </button>
            </form>
            @elseif($r->status === \App\Support\ArtisanReviews::REJECTED)
            <p class="ui-hint">
                {{ $isFr
                   ? "Refusé. Il n'y a pas de retour en arrière depuis ici : si l'auteur récrit son avis, il repasse en file d'attente."
                   : 'Refused. There is no way back from here: if the author rewrites their review, it returns to the queue.' }}
            </p>
            @endif

            {{-- Permanent removal. It came from the old /moderation reviews tab,
                 which offered nothing else and offered it on every row — including
                 rows nobody had read yet. It survives, but only where a moderator
                 has already made and recorded a decision: erasing a review that is
                 still in the queue erases the decision as well as the text. --}}
            @if(in_array($r->status, [\App\Support\ArtisanReviews::REJECTED, \App\Support\ArtisanReviews::HIDDEN], true))
            <form method="POST" action="{{ route('admin.reviews.destroy', ['id' => $r->id]) }}" class="pt-3"
                  onsubmit="return confirm('{{ $isFr ? 'Supprimer définitivement cet avis ? Le registre ne le retrouvera pas.' : 'Permanently delete this review? The register will not get it back.' }}')">
                @csrf
                <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    {{ $isFr ? 'Supprimer définitivement' : 'Delete permanently' }}
                </button>
            </form>
            @endif

        </div>
    </div>
    @endforeach

    {{-- ══ Distinctions ═════════════════════════════════════════════════════ --}}

    <div class="pt-4">
        <h2 class="ui-h text-[16px]">{{ $isFr ? 'Distinctions' : 'Distinctions' }}</h2>
        <p class="ui-hint mt-1">
            {{ $isFr
               ? "Une distinction nomme un organisme extérieur : l'inscrire ici, c'est affirmer que cet organisme a bien honoré cet artisan. Elle se relève d'une pièce justificative, jamais d'une déclaration de l'artisan. Ce projet a déjà dû retirer des mentions UNESCO et ministérielles inventées."
               : 'A distinction names an outside body: recording it here asserts that that body really honoured this artisan. It is taken from evidence, never from the artisan’s own word. This project has already had to strip invented UNESCO and ministry honours off certificates.' }}
        </p>
    </div>

    <div class="ui-card">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Enregistrer une distinction' : 'Record a distinction' }}</p>
        </div>
        <form method="POST" action="{{ route('admin.awards.store') }}" class="px-4 py-4 grid gap-4 sm:grid-cols-2">
            @csrf

            <div class="sm:col-span-2">
                <label class="ui-label" for="award-business">{{ $isFr ? 'Atelier' : 'Workshop' }}<span class="ui-req">*</span></label>
                <select id="award-business" name="business_id" required class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Choisir…' : 'Choose…' }}</option>
                    @foreach($businesses as $b)
                    <option value="{{ $b->id }}" @selected(old('business_id') == $b->id)>{{ $b->name_fr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label" for="award-title-fr">{{ $isFr ? 'Intitulé (français)' : 'Title (French)' }}<span class="ui-req">*</span></label>
                <input id="award-title-fr" name="title_fr" type="text" required maxlength="255" class="ui-field" value="{{ old('title_fr') }}">
            </div>

            <div>
                <label class="ui-label" for="award-title-en">{{ $isFr ? 'Intitulé (anglais)' : 'Title (English)' }}</label>
                <input id="award-title-en" name="title_en" type="text" maxlength="255" class="ui-field" value="{{ old('title_en') }}">
                <p class="ui-hint">
                    {{ $isFr
                       ? "Facultatif. À laisser vide si l'organisme n'a pas donné de nom anglais : mieux vaut afficher le nom d'origine qu'en inventer un."
                       : 'Optional. Leave empty if the body gave no English name — better to show the original than to invent one.' }}
                </p>
            </div>

            <div>
                <label class="ui-label" for="award-issuer">{{ $isFr ? 'Organisme qui l’a décernée' : 'Body that gave it' }}<span class="ui-req">*</span></label>
                <input id="award-issuer" name="issuer" type="text" required maxlength="255" class="ui-field" value="{{ old('issuer') }}">
                <p class="ui-hint">
                    {{ $isFr ? "Le nom exact, tel qu'il figure sur la pièce." : 'The exact name, as it appears on the document.' }}
                </p>
            </div>

            <div>
                <label class="ui-label" for="award-year">{{ $isFr ? 'Année' : 'Year' }}</label>
                <input id="award-year" name="year" type="number" min="1900" max="{{ date('Y') + 1 }}" class="ui-field" value="{{ old('year') }}">
            </div>

            <div>
                <label class="ui-label" for="award-evidence">{{ $isFr ? 'Lien vers la pièce' : 'Link to the evidence' }}</label>
                <input id="award-evidence" name="evidence_url" type="url" maxlength="500" class="ui-field" value="{{ old('evidence_url') }}">
            </div>

            <div>
                <label class="ui-label" for="award-reference">{{ $isFr ? 'Référence de la pièce' : 'Document reference' }}</label>
                <input id="award-reference" name="reference" type="text" maxlength="120" class="ui-field" value="{{ old('reference') }}">
            </div>

            <div class="sm:col-span-2">
                <p class="ui-hint">
                    {{ $isFr
                       ? "Le lien et la référence sont facultatifs, mais ce sont les seules choses qui rendent la mention vérifiable par quelqu'un d'autre que nous. Sans elles, vous demandez au lecteur de nous croire sur parole."
                       : 'The link and the reference are optional, but they are the only things that make the claim checkable by somebody other than us. Without them you are asking the reader to take our word for it.' }}
                </p>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm mt-3">
                    <i data-lucide="award" class="w-4 h-4"></i>
                    {{ $isFr ? "Enregistrer — j'ai vu la pièce" : 'Record — I have seen the evidence' }}
                </button>
            </div>
        </form>
    </div>

    @if($awards->isEmpty())
    <div class="ui-card text-center py-10 px-4">
        <p class="ui-body">{{ $isFr ? 'Aucune distinction enregistrée.' : 'No distinction recorded.' }}</p>
    </div>
    @else
    <div class="ui-card">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Distinctions enregistrées' : 'Recorded distinctions' }}</p>
        </div>
        <ul class="divide-y divide-[#F0F1F0] dark:divide-[#262B21]">
            @foreach($awards as $a)
            <li class="px-4 py-3 flex items-start justify-between gap-4" data-award="{{ $a->id }}">
                <div class="min-w-0">
                    <p class="ui-body">{{ $a->title_fr }} @if($a->year)<span class="ui-muted">· {{ $a->year }}</span>@endif</p>
                    <p class="ui-hint">
                        {{ $a->issuer }} · {{ $a->business_name }}
                        · {{ $isFr ? 'relevé par' : 'recorded by' }} {{ $a->recorder_name ?: ($isFr ? 'compte supprimé' : 'deleted account') }}
                        @if($a->reference) · {{ $a->reference }} @endif
                    </p>
                    @if($a->evidence_url)
                    <a href="{{ $a->evidence_url }}" target="_blank" rel="noopener noreferrer" class="ui-hint underline">
                        {{ $isFr ? 'Pièce justificative' : 'Evidence' }}
                    </a>
                    @else
                    <p class="ui-hint">{{ $isFr ? 'Aucune pièce liée.' : 'No evidence linked.' }}</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.awards.destroy', ['id' => $a->id]) }}">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-ghost ui-btn-sm">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        {{ $isFr ? 'Supprimer' : 'Delete' }}
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

</div>
@endsection
