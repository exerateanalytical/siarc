@extends('layouts.dashboard')

@php
/*
 * The reviewer's queue for manual settlement of the platform's own fees.
 *
 * The single most important thing on this page is a sentence rather than a
 * control. There is no payment gateway: nothing in this system knows whether
 * money arrived. Every row here is somebody's claim, and pressing Confirm does
 * not check anything — it records that a named human asserted the transfer is
 * real. If a reviewer confirms without opening the operator's statement, the
 * platform has granted a membership for free and the audit trail will say a
 * person verified it. That is why the reminder sits above the buttons and why
 * this comment exists: the interface should admit where its integrity actually
 * lives instead of implying the software supplies it.
 *
 * Everything a reviewer needs to do that check is therefore on the row: who,
 * how much, which method, the operator reference they quoted, the proof if they
 * uploaded one, and how long they have been waiting. And the append-only event
 * trail is under each row, so a second reviewer months later can see who did
 * what without taking the status column's word for it.
 *
 * Rejection requires a reason in the route as well as in the markup. A `required`
 * attribute is advisory; the control is server-side. It is a reason and not a
 * checkbox because a rejection with no reason is a dead end for somebody who may
 * genuinely have paid.
 *
 * Note what is deliberately absent: no bulk confirm. Confirming is an assertion
 * about one transfer on one statement line, and a control that lets somebody
 * assert thirty of them at once is a control for asserting things nobody looked
 * at.
 */

$isFr      = $lang === 'fr';
$pageTitle = $isFr ? 'Paiements à contrôler' : 'Payments to check';

$reviewerId = $siacUser['id'] ?? null;

$statusLabels = [
    'awaiting_payment' => $isFr ? 'En attente de paiement'  : 'Awaiting payment',
    'reported'         => $isFr ? 'Signalé'                 : 'Reported',
    'under_review'     => $isFr ? 'Contrôle en cours'       : 'Being checked',
    'confirmed'        => $isFr ? 'Confirmé'                : 'Confirmed',
    'rejected'         => $isFr ? 'Rejeté'                  : 'Rejected',
    'cancelled'        => $isFr ? 'Annulé'                  : 'Cancelled',
    'expired'          => $isFr ? 'Expiré'                  : 'Expired',
];

$purposeLabels = [
    'registration'        => $isFr ? "Frais d'inscription" : 'Registration fee',
    'membership'          => $isFr ? 'Adhésion'             : 'Membership',
    'renewal'             => $isFr ? 'Renouvellement'       : 'Renewal',
    'verification'        => $isFr ? 'Vérification'         : 'Verification',
    'workshop_inspection' => $isFr ? "Inspection d'atelier" : 'Workshop inspection',
    'other'               => $isFr ? 'Frais de plateforme'  : 'Platform fee',
];

$eventLabels = [
    'opened'         => $isFr ? 'Ouvert'                  : 'Opened',
    'reported'       => $isFr ? 'Déclaré par le payeur'   : 'Reported by the payer',
    'review_started' => $isFr ? 'Contrôle commencé'       : 'Review started',
    'confirmed'      => $isFr ? 'Confirmé'                : 'Confirmed',
    'rejected'       => $isFr ? 'Rejeté'                  : 'Rejected',
    'cancelled'      => $isFr ? 'Annulé'                  : 'Cancelled',
    'expired'        => $isFr ? 'Expiré'                  : 'Expired',
];

$methodLabels = [];
foreach ((array) config('payments.methods', []) as $code => $m) {
    $methodLabels[$code] = $isFr ? ($m['label_fr'] ?? $code) : ($m['label_en'] ?? $code);
}

$tabs = ['reported', 'under_review', 'confirmed', 'rejected', 'expired', 'cancelled'];
@endphp

@section('content')
<div class="max-w-5xl space-y-5">

    <div>
        <h1 class="ui-h text-[19px]">{{ $pageTitle }}</h1>
        <p class="ui-hint mt-1">
            {{ $isFr
               ? "Frais de plateforme uniquement — inscription, adhésion, renouvellement, vérification. La plateforme n'est pas partie aux ventes entre acheteurs et artisans et n'encaisse aucun prix de produit."
               : 'Platform fees only — registration, membership, renewal, verification. The platform is not a party to sales between buyers and artisans and receives no product price.' }}
        </p>
    </div>

    {{-- The reminder. Above the queue, because it is a condition on using it. --}}
    <div class="ui-alert ui-alert-warn" data-verify-reminder>
        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
        <span>
            {{ $isFr
               ? "Avant de confirmer, ouvrez le relevé de l'opérateur et retrouvez le versement : le montant, la référence et la date. Rien dans ce logiciel ne sait si l'argent est arrivé — confirmer n'enregistre que votre propre constat, sous votre nom. C'est ce contrôle humain, et lui seul, qui tient tout l'édifice."
               : "Before confirming, open the operator's statement and find the transfer: the amount, the reference and the date. Nothing in this software knows whether the money arrived — confirming records only your own finding, under your name. That human check, and nothing else, is what holds this together." }}
        </span>
    </div>

    @if(session('payment_reviewed'))
    <div class="ui-alert ui-alert-ok" role="status">
        <i data-lucide="check" class="w-4 h-4"></i>
        <span>{{ $isFr ? 'Votre décision est enregistrée et attribuée à votre compte.' : 'Your decision is recorded and attributed to your account.' }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="ui-alert ui-alert-danger">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- Queue selector. Counts included so a reviewer can see the queues they
         are not currently looking at rather than assuming they are empty. --}}
    <div class="flex flex-wrap gap-1.5">
        @foreach($tabs as $tab)
        <a href="{{ route('admin.payments', ['status' => $tab, 'lang' => $lang]) }}"
           class="ui-btn ui-btn-sm {{ $filter === $tab ? 'ui-btn-primary' : 'ui-btn-secondary' }}">
            {{ $statusLabels[$tab] }} ({{ $counts[$tab] ?? 0 }})
        </a>
        @endforeach
    </div>

    @if($rows->isEmpty())
    <div class="ui-card text-center py-14 px-4">
        <i data-lucide="inbox" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="ui-body">{{ $isFr ? 'Aucun paiement dans cette file.' : 'No payment in this queue.' }}</p>
    </div>
    @endif

    @foreach($rows as $p)
    @php
        // Oldest first, so this is the number that matters most on the row: a
        // person who reported nine days ago has been waiting past the window we
        // promised them, and the queue should make that visible not buried.
        $since   = \Illuminate\Support\Carbon::parse($p->reported_at ?? $p->created_at);
        $waiting = $since->diffInDays(now());
        $isOwn   = $p->user_id !== null && (string) $p->user_id === (string) $reviewerId;
    @endphp
    <div class="ui-card" data-payment="{{ $p->reference }}" data-payment-status="{{ $p->status }}">
        <div class="ui-card-head">
            <div class="min-w-0">
                <p class="ui-card-title">{{ $purposeLabels[$p->purpose] ?? $purposeLabels['other'] }}</p>
                <p class="ui-card-sub">{{ $p->reference }}</p>
            </div>
            <span class="ui-pill {{ $waiting >= (int) config('payments.confirmation_window_days') ? 'ui-pill-danger' : 'ui-pill-neutral' }}">
                {{ $isFr ? 'En attente depuis' : 'Waiting' }} {{ $waiting }} {{ $isFr ? 'j' : 'd' }}
            </span>
        </div>

        <div class="px-4 py-4 space-y-4">

            <dl class="ui-dl">
                <dt class="ui-dt">{{ $isFr ? 'Montant' : 'Amount' }}</dt>
                {{-- Printed from the record. The reviewer compares this against
                     the statement, so a recomputed figure here would be a way of
                     confirming the wrong number. --}}
                <dd class="ui-dd"><strong>{{ number_format((float) $p->amount, 0, ',', ' ') }} {{ $p->currency }}</strong></dd>

                <dt class="ui-dt">{{ $isFr ? 'Moyen' : 'Method' }}</dt>
                <dd class="ui-dd">{{ $methodLabels[$p->method_code] ?? $p->method_code }}</dd>

                <dt class="ui-dt">{{ $isFr ? 'Activité' : 'Business' }}</dt>
                <dd class="ui-dd">
                    {{ $p->business_name ?: ($isFr ? 'Aucune activité rattachée' : 'No business attached') }}
                </dd>

                <dt class="ui-dt">{{ $isFr ? 'Compte' : 'Account' }}</dt>
                <dd class="ui-dd">
                    {{ $p->user_name ?: ($isFr ? 'Inconnu' : 'Unknown') }}
                    @if($p->user_email) <span class="ui-muted">· {{ $p->user_email }}</span> @endif
                </dd>

                <dt class="ui-dt">{{ $isFr ? 'Nom du payeur (déclaré)' : 'Payer name (claimed)' }}</dt>
                <dd class="ui-dd">{{ $p->payer_name ?: '—' }}</dd>

                <dt class="ui-dt">{{ $isFr ? 'Numéro du payeur (déclaré)' : 'Payer number (claimed)' }}</dt>
                <dd class="ui-dd">{{ $p->payer_number ?: '—' }}</dd>

                <dt class="ui-dt">{{ $isFr ? "Référence de l'opérateur (déclarée)" : 'Operator reference (claimed)' }}</dt>
                <dd class="ui-dd">{{ $p->payer_reference ?: '—' }}</dd>

                <dt class="ui-dt">{{ $isFr ? 'Déclaré le' : 'Reported on' }}</dt>
                <dd class="ui-dd">
                    {{ $p->reported_at
                       ? \Illuminate\Support\Carbon::parse($p->reported_at)->format('d/m/Y H:i')
                       : ($isFr ? 'Pas encore déclaré' : 'Not reported yet') }}
                </dd>
            </dl>

            <p class="ui-hint">
                {{ $isFr
                   ? "Tout ce qui est marqué « déclaré » vient du payeur et n'a été vérifié par personne."
                   : 'Everything marked "claimed" comes from the payer and has been checked by nobody.' }}
            </p>

            @if($p->proof_path)
            <a href="{{ route('admin.payments.proof', ['id' => $p->id]) }}" target="_blank" rel="noopener"
               class="ui-btn ui-btn-secondary ui-btn-sm">
                <i data-lucide="paperclip" class="w-4 h-4"></i>
                {{ $isFr ? 'Voir le justificatif' : 'View the proof' }}
            </a>
            @else
            <p class="ui-hint">{{ $isFr ? 'Aucun justificatif joint.' : 'No proof attached.' }}</p>
            @endif

            {{-- ── The trail ── --}}
            <div>
                <p class="ui-eyebrow">{{ $isFr ? 'Journal' : 'Event trail' }}</p>
                <ul class="mt-2 divide-y divide-[#F0F1F0]">
                    @foreach($trails[$p->id] ?? [] as $e)
                    <li class="py-2" data-event="{{ $e->event }}">
                        <p class="text-[12.5px] ui-ink">
                            {{ $eventLabels[$e->event] ?? $e->event }}
                            @if($e->from_status)
                                <span class="ui-muted">
                                    ({{ $statusLabels[$e->from_status] ?? $e->from_status }}
                                    →
                                    {{ $statusLabels[$e->to_status] ?? $e->to_status }})
                                </span>
                            @endif
                        </p>
                        <p class="ui-hint">
                            {{ \Illuminate\Support\Carbon::parse($e->occurred_at)->format('d/m/Y H:i') }}
                            @if($e->actor_user_id)
                                · {{ $isFr ? 'par' : 'by' }} {{ $e->actor_user_id }}
                            @else
                                · {{ $isFr ? 'sans acteur enregistré' : 'no actor recorded' }}
                            @endif
                            @if($e->note) · {{ $e->note }} @endif
                        </p>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- ── The decision ── --}}
            @if($isOwn)
                {{-- An administrator who also owes this fee. The buttons are gone
                     AND the route refuses the POST; this notice exists so the
                     reviewer understands why rather than reporting a bug. --}}
                <div class="ui-alert ui-alert-warn">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                    <span>
                        {{ $isFr
                           ? "Ce paiement est le vôtre. Personne ne contrôle son propre versement : demandez à un autre administrateur de le faire."
                           : 'This payment is your own. Nobody checks their own transfer: ask another administrator to do it.' }}
                    </span>
                </div>
            @elseif(in_array($p->status, ['awaiting_payment', 'reported', 'under_review'], true))
                <div class="grid gap-4 sm:grid-cols-2 pt-1">

                    <form method="POST" action="{{ route('admin.payments.confirm', ['id' => $p->id]) }}">
                        @csrf
                        <label class="ui-label" for="note-{{ $p->id }}">
                            {{ $isFr ? 'Où avez-vous constaté le versement ?' : 'Where did you see the transfer?' }}
                        </label>
                        <input id="note-{{ $p->id }}" name="note" type="text" maxlength="500" class="ui-field"
                               placeholder="{{ $isFr ? 'ex. relevé MTN du 12/07, ligne 4' : 'e.g. MTN statement 12/07, line 4' }}">
                        <p class="ui-hint">
                            {{ $isFr ? "Facultatif, mais c'est ce que lira la personne qui rouvrira ce dossier."
                                      : 'Optional, but it is what the next person to reopen this file will read.' }}
                        </p>
                        <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm mt-3">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            {{ $isFr ? "Confirmer — j'ai vu l'argent" : 'Confirm — I have seen the money' }}
                        </button>
                    </form>

                    @if(in_array($p->status, ['reported', 'under_review'], true))
                    <form method="POST" action="{{ route('admin.payments.reject', ['id' => $p->id]) }}">
                        @csrf
                        <label class="ui-label" for="reason-{{ $p->id }}">
                            {{ $isFr ? 'Motif du rejet' : 'Reason for rejecting' }}<span class="ui-req">*</span>
                        </label>
                        <input id="reason-{{ $p->id }}" name="reason" type="text" maxlength="500" required minlength="3"
                               class="ui-field"
                               placeholder="{{ $isFr ? 'ex. aucun versement à cette référence' : 'e.g. no transfer under that reference' }}">
                        <p class="ui-hint">
                            {{ $isFr ? "Obligatoire. Le payeur le lit tel quel : écrivez ce qu'il peut corriger."
                                      : 'Required. The payer reads it verbatim: write what they can act on.' }}
                        </p>
                        <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm mt-3">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            {{ $isFr ? 'Rejeter' : 'Reject' }}
                        </button>
                    </form>
                    @endif

                </div>
            @else
                <dl class="ui-dl">
                    <dt class="ui-dt">{{ $isFr ? 'Décidé par' : 'Decided by' }}</dt>
                    <dd class="ui-dd">{{ $p->reviewed_by ?: ($isFr ? 'Aucun examinateur enregistré' : 'No reviewer recorded') }}</dd>
                    <dt class="ui-dt">{{ $isFr ? 'Le' : 'On' }}</dt>
                    <dd class="ui-dd">
                        {{ $p->reviewed_at ? \Illuminate\Support\Carbon::parse($p->reviewed_at)->format('d/m/Y H:i') : '—' }}
                    </dd>
                    @if($p->review_note)
                    <dt class="ui-dt">{{ $isFr ? 'Constat' : 'Finding' }}</dt>
                    <dd class="ui-dd">{{ $p->review_note }}</dd>
                    @endif
                    @if($p->rejection_reason)
                    <dt class="ui-dt">{{ $isFr ? 'Motif du rejet' : 'Rejection reason' }}</dt>
                    <dd class="ui-dd">{{ $p->rejection_reason }}</dd>
                    @endif
                </dl>
            @endif

        </div>
    </div>
    @endforeach

</div>
@endsection
