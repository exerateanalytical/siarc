@extends('layouts.dashboard')

@php
/*
 * The artisan's own view of what they owe the platform and what has been done
 * about it.
 *
 * The thing this page must never do is tell somebody their fee is settled when
 * all the platform holds is their own word for it. There is no payment gateway;
 * money moves by mobile money outside our systems; the only event that settles
 * anything is a human at the platform finding the transfer on the operator's
 * statement and saying so. So "confirmed" is rendered from reviewed_by and
 * reviewed_at on the record, never inferred from the fact that the artisan
 * reported a payment — a reported payment stays in the outstanding list, which
 * is the honest place for it.
 *
 * The second duty is to a rejected payment. Being told no, with no reason and no
 * next step, is the worst thing this page could do to somebody who has actually
 * sent money. The reason the reviewer typed is shown verbatim, with the way to
 * dispute it beside it.
 *
 * Scope, restated because it is load-bearing: these are the platform's own fees.
 * The platform is not a party to sales between buyers and artisans and receives
 * no product price. Nothing on this page concerns an order.
 *
 * Gathering happens here because the route passes only $lang, exactly as the
 * neighbouring certificates page does.
 */

use App\Support\ManualPayment;
use App\Support\PlatformFees;

$isFr      = $lang === 'fr';
$pageTitle = $isFr ? 'Mes paiements' : 'My payments';

$siacUser = session('siac_user') ?? [];

// first(), not firstOrFail(): an artisan who has not opened a shop is a normal
// state of the world, and payments attach to a business.
$business = \App\Modules\Businesses\Models\Business::where('user_id', $siacUser['id'] ?? null)->first();

$outstanding = collect();
$history     = collect();

// Ma formule / My plan. isActive() is the exact gate BusinessService::publish
// uses, so a vendor already paid up is never shown a fresh registration
// picker here — only their current standing. Below it, every plan comes
// straight off subscription_plans via PlatformFees::plans(); nothing here
// invents a price or a feature.
$hasActiveSubscription = false;
$currentSubscription   = null;
$currentPlan           = null;
$availablePlans        = collect();

if ($business) {
    // Both reads come from ManualPayment so that what counts as "outstanding"
    // is defined in exactly one place. Notably it includes `reported`: a claim
    // is not a settlement, and dropping it off this list the moment somebody
    // reports would tell them the fee was dealt with when nobody had looked.
    $outstanding = ManualPayment::outstandingFor($business);
    $history     = ManualPayment::historyFor($business);

    $hasActiveSubscription = PlatformFees::isActive($business);
    if ($hasActiveSubscription) {
        $currentSubscription = PlatformFees::subscriptionFor($business);
        $currentPlan         = PlatformFees::planFor($business);
    } else {
        $availablePlans = collect(PlatformFees::plans($lang));
    }
}

$windowDays = (int) config('payments.confirmation_window_days');

$purposeLabels = [
    'registration'        => $isFr ? "Frais d'inscription"     : 'Registration fee',
    'membership'          => $isFr ? 'Adhésion'                 : 'Membership',
    'renewal'             => $isFr ? 'Renouvellement'           : 'Renewal',
    'verification'        => $isFr ? 'Vérification'             : 'Verification',
    'workshop_inspection' => $isFr ? "Inspection d'atelier"     : 'Workshop inspection',
    'other'               => $isFr ? 'Frais de plateforme'      : 'Platform fee',
];

/* Status in words, written from the platform's side of its own ignorance.
   "Signalé" says what somebody told us; it does not say what happened. */
$statusLabels = [
    'awaiting_payment' => $isFr ? 'En attente de votre paiement'    : 'Awaiting your payment',
    'reported'         => $isFr ? 'Signalé, en attente de contrôle' : 'Reported, awaiting checking',
    'under_review'     => $isFr ? 'Contrôle en cours'               : 'Being checked',
    'confirmed'        => $isFr ? 'Confirmé par un examinateur'     : 'Confirmed by a reviewer',
    'rejected'         => $isFr ? 'Rejeté'                          : 'Rejected',
    'cancelled'        => $isFr ? 'Annulé'                          : 'Cancelled',
    'expired'          => $isFr ? 'Expiré'                          : 'Expired',
];

$pillClass = [
    'confirmed' => 'ui-pill-ok',
    'rejected'  => 'ui-pill-danger',
];
@endphp

@section('content')
<div class="max-w-4xl space-y-5">

    <div>
        <h1 class="ui-h text-[19px]">{{ $pageTitle }}</h1>
        <p class="ui-hint mt-1">
            {{ $isFr
               ? "Les frais que la plateforme facture pour ses propres services, et où en est chacun. Il ne s'agit jamais de la vente de vos pièces : la plateforme n'est pas partie à ces ventes et n'encaisse aucun prix de produit."
               : 'The fees the platform charges for its own services, and where each one stands. This is never about the sale of your pieces: the platform is not a party to those sales and receives no product price.' }}
        </p>
    </div>

    @if(! $business)
        <div class="ui-card text-center py-14 px-4">
            <i data-lucide="store" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
            <p class="ui-body max-w-md mx-auto">
                {{ $isFr
                   ? "Les frais de plateforme se rattachent à une activité enregistrée. Vous n'en avez pas encore, il n'y a donc rien à régler ici."
                   : 'Platform fees attach to a registered business. You do not have one yet, so there is nothing to settle here.' }}
            </p>
            <a href="{{ route('business.create') }}" class="ui-btn ui-btn-primary mt-4">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ $isFr ? 'Enregistrer mon activité' : 'Register my business' }}
            </a>
        </div>
    @else

    {{-- ───────────── 0. Ma formule / My plan ───────────── --}}
    <div class="ui-card" data-plan-panel="{{ $hasActiveSubscription ? 'active' : 'picker' }}">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Ma formule' : 'My plan' }}</p>
        </div>

        @if($errors->has('plan'))
        <div class="ui-alert ui-alert-danger mx-4 mt-3">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            {{ $errors->first('plan') }}
        </div>
        @endif

        @if($hasActiveSubscription)
            {{-- A live subscription already exists: no fresh registration intent
                 is offered here, only the honest current standing, so nobody
                 accidentally opens a second intent beside a paid one. --}}
            <div class="px-4 py-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-[15px] font-bold ui-ink">{{ $currentPlan ? ($isFr ? $currentPlan->name_fr : ($currentPlan->name_en ?: $currentPlan->name_fr)) : '—' }}</p>
                        <p class="ui-hint mt-0.5">
                            {{ number_format((float) $currentSubscription->amount, 0, ',', ' ') }} {{ $currentPlan->currency ?? 'XAF' }}
                            {{ $isFr ? '/ an' : '/ year' }}
                        </p>
                    </div>
                    <span class="ui-pill ui-pill-ok" data-plan-status="active">{{ $isFr ? 'Active' : 'Active' }}</span>
                </div>
                @if($currentSubscription->next_payment_at)
                <p class="ui-hint mt-2">
                    {{ $isFr ? 'Renouvellement le' : 'Renews on' }}
                    {{ \Illuminate\Support\Carbon::parse($currentSubscription->next_payment_at)->format('d/m/Y') }}
                </p>
                @endif
            </div>
        @elseif($availablePlans->isEmpty())
            <div class="px-4 py-10 text-center">
                <p class="ui-body">
                    {{ $isFr
                       ? "Aucune formule n'est configurée pour le moment. Écrivez-nous pour connaître le tarif en vigueur."
                       : 'No plan is configured at the moment. Write to us to learn the current rate.' }}
                </p>
            </div>
        @else
            <div class="px-4 py-4">
                <p class="ui-hint">
                    {{ $isFr
                       ? 'Choisissez la formule qui correspond à votre activité. Le montant à régler s\'affichera sur la page de paiement, par mobile money.'
                       : 'Choose the plan that matches your activity. The amount to pay will appear on the payment page, by mobile money.' }}
                </p>
                <form method="POST" action="{{ route('dashboard.plan.select') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5" role="radiogroup" aria-label="{{ $isFr ? 'Formule' : 'Plan' }}">
                        @foreach($availablePlans as $i => $p)
                        <label class="flex items-start gap-2.5 rounded-xl border border-[#EFEBE2] dark:border-[#262B21] px-3.5 py-3 cursor-pointer has-[:checked]:border-[#157A43] has-[:checked]:bg-[#EFF5F0] dark:has-[:checked]:bg-[#0C3D1D] min-h-[44px]" data-plan-option="{{ $p['slug'] }}">
                            <input type="radio" name="plan" value="{{ $p['slug'] }}" class="ui-check mt-1" @checked($i === 0) required>
                            <span class="min-w-0">
                                <span class="block text-[13.5px] font-semibold ui-ink">{{ $p['name'] }}</span>
                                <span class="block text-[13px] text-[#55524A] dark:text-[#B4B5A6]">
                                    {{ number_format($p['price_yearly'], 0, ',', ' ') }} {{ $p['currency'] }}{{ $isFr ? ' / an' : ' / year' }}
                                </span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    <button type="submit" class="ui-btn ui-btn-primary mt-4">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        {{ $isFr ? 'Choisir cette formule' : 'Choose this plan' }}
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- ───────────── 1. Outstanding ───────────── --}}
    <div class="ui-card">
        <div class="ui-card-head">
            <div>
                <p class="ui-card-title">{{ $isFr ? 'À régler' : 'Outstanding' }}</p>
                <p class="ui-card-sub">{{ $business->name_fr }}</p>
            </div>
            <span class="ui-pill ui-pill-neutral">{{ $outstanding->count() }}</span>
        </div>

        @if($outstanding->isEmpty())
        <div class="px-4 py-10 text-center">
            <p class="ui-body">
                {{ $isFr
                   ? "Aucun frais de plateforme n'est en attente pour votre activité."
                   : 'No platform fee is outstanding for your business.' }}
            </p>
        </div>
        @else
        <div>
            @foreach($outstanding as $p)
            <div class="px-4 py-3.5 border-b border-[#F0F1F0] dark:border-[#262B21] last:border-0"
                 data-payment="{{ $p->reference }}" data-payment-status="{{ $p->status }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-[13px] font-semibold ui-ink min-w-0">
                        {{ $purposeLabels[$p->purpose] ?? $purposeLabels['other'] }}
                    </p>
                    <span class="ui-pill {{ $pillClass[$p->status] ?? 'ui-pill-neutral' }}">
                        {{ $statusLabels[$p->status] ?? $p->status }}
                    </span>
                </div>

                <dl class="ui-dl mt-2">
                    <dt class="ui-dt">{{ $isFr ? 'Montant' : 'Amount' }}</dt>
                    {{-- Straight off the record. Nothing here recomputes a fee. --}}
                    <dd class="ui-dd">{{ number_format((float) $p->amount, 0, ',', ' ') }} {{ $p->currency }}</dd>
                    <dt class="ui-dt">{{ $isFr ? 'Référence' : 'Reference' }}</dt>
                    <dd class="ui-dd">{{ $p->reference }}</dd>
                    @if($p->reported_at)
                    <dt class="ui-dt">{{ $isFr ? 'Déclaré le' : 'Reported on' }}</dt>
                    <dd class="ui-dd">{{ \Illuminate\Support\Carbon::parse($p->reported_at)->format('d/m/Y') }}</dd>
                    @endif
                </dl>

                @if(in_array($p->status, ['reported', 'under_review'], true))
                <p class="ui-hint mt-1">
                    {{ $isFr
                       ? "Vous avez déclaré ce versement ; il reste en attente tant qu'une personne de l'équipe ne l'a pas retrouvé chez l'opérateur. Comptez jusqu'à {$windowDays} jours."
                       : "You reported this transfer; it stays outstanding until somebody on the team has found it with the operator. Allow up to {$windowDays} days." }}
                </p>
                @endif

                <a href="{{ route('payment.instructions', ['reference' => $p->reference, 'lang' => $lang]) }}"
                   class="ui-btn ui-btn-primary ui-btn-sm mt-2.5">
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    {{ $isFr ? 'Comment régler' : 'How to pay' }}
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ───────────── 2. History ───────────── --}}
    <div class="ui-card">
        <div class="ui-card-head">
            <p class="ui-card-title">{{ $isFr ? 'Historique' : 'History' }}</p>
        </div>

        @if($history->isEmpty())
        <div class="px-4 py-10 text-center">
            <p class="ui-body">{{ $isFr ? 'Rien à afficher pour l\'instant.' : 'Nothing to show yet.' }}</p>
        </div>
        @else
        <div>
            @foreach($history as $p)
            @php
                /* "Confirmed" is asserted only where the record itself carries a
                   reviewer and a moment. A status column can be written by any
                   code path; reviewed_by is written by exactly one, and it names
                   the person who is answerable for the decision. */
                $reallyConfirmed = $p->status === 'confirmed' && $p->reviewed_by && $p->reviewed_at;
            @endphp
            <div class="px-4 py-3.5 border-b border-[#F0F1F0] dark:border-[#262B21] last:border-0"
                 data-history="{{ $p->reference }}" data-payment-status="{{ $p->status }}">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-[13px] font-semibold ui-ink min-w-0">
                        {{ $purposeLabels[$p->purpose] ?? $purposeLabels['other'] }} —
                        {{ number_format((float) $p->amount, 0, ',', ' ') }} {{ $p->currency }}
                    </p>
                    <span class="ui-pill {{ $reallyConfirmed ? 'ui-pill-ok' : ($pillClass[$p->status] ?? 'ui-pill-neutral') }}">
                        {{ $statusLabels[$p->status] ?? $p->status }}
                    </span>
                </div>

                <dl class="ui-dl mt-2">
                    <dt class="ui-dt">{{ $isFr ? 'Référence' : 'Reference' }}</dt>
                    <dd class="ui-dd">{{ $p->reference }}</dd>
                    <dt class="ui-dt">{{ $isFr ? 'Déclaré le' : 'Reported on' }}</dt>
                    <dd class="ui-dd">
                        {{ $p->reported_at
                           ? \Illuminate\Support\Carbon::parse($p->reported_at)->format('d/m/Y')
                           : ($isFr ? 'Pas encore déclaré' : 'Not reported yet') }}
                    </dd>
                    <dt class="ui-dt">{{ $isFr ? 'Confirmé le' : 'Confirmed on' }}</dt>
                    <dd class="ui-dd">
                        {{-- Never a date unless a reviewer put one there. --}}
                        {{ $reallyConfirmed
                           ? \Illuminate\Support\Carbon::parse($p->reviewed_at)->format('d/m/Y')
                           : ($isFr ? 'Pas confirmé' : 'Not confirmed') }}
                    </dd>
                </dl>

                @if($p->status === 'rejected')
                <div class="ui-alert ui-alert-danger mt-2.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                    <span>
                        <strong>{{ $isFr ? 'Motif :' : 'Reason:' }}</strong>
                        {{ $p->rejection_reason ?: ($isFr ? 'Aucun motif enregistré.' : 'No reason recorded.') }}
                    </span>
                </div>
                <p class="ui-hint mt-1.5">
                    {{ $isFr
                       ? "Que faire : vérifiez la référence indiquée dans le motif de votre transfert et le montant envoyé, puis déclarez à nouveau depuis la page de règlement. Si vous êtes certain d'avoir payé, écrivez-nous avec le SMS de l'opérateur — un rejet est une constatation, pas une accusation, et il se corrige."
                       : 'What to do: check the reference you put in the transfer reason and the amount you sent, then report again from the settlement page. If you are sure you paid, write to us with the operator SMS — a rejection is an observation, not an accusation, and it can be put right.' }}
                </p>
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <a href="{{ route('payment.instructions', ['reference' => $p->reference, 'lang' => $lang]) }}"
                       class="ui-btn ui-btn-secondary ui-btn-sm">
                        {{ $isFr ? 'Déclarer à nouveau' : 'Report again' }}
                    </a>
                    <a href="{{ route('support.index', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                        <i data-lucide="life-buoy" class="w-4 h-4"></i>
                        {{ $isFr ? 'Contester' : 'Dispute this' }}
                    </a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <p class="ui-hint">
        {{ $isFr
           ? "La plateforme n'utilise aucune passerelle de paiement : l'argent circule par mobile money, en dehors de nos systèmes. Un règlement n'est acquis que lorsqu'une personne de l'équipe a constaté le versement chez l'opérateur et l'a confirmé."
           : 'The platform uses no payment gateway: the money moves by mobile money, outside our systems. A settlement holds only once somebody on the team has seen the transfer with the operator and confirmed it.' }}
    </p>

    @endif

</div>
@endsection
