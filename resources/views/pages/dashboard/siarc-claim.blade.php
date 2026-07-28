@extends('layouts.dashboard')

@php
    $isFr = $lang === 'fr';
    $pageTitle = $isFr ? 'Récupérer mon profil SIARC 2026' : 'Claim my SIARC 2026 profile';

    // The entry price, read from `subscription_plans` via PlatformFees. If the
    // table holds no priced plan, nothing about money is rendered at all — a
    // figure invented in a template is a figure somebody would pay.
    $paidPlans  = collect($plans ?? [])->where('price_yearly', '>', 0)->sortBy('price_yearly')->values();
    $entryPlan  = $paidPlans->first();
@endphp

@section('content')
<div class="max-w-3xl space-y-5">

    @if($errors->has('claim'))
    <div class="ui-alert ui-alert-danger">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        {{ $errors->first('claim') }}
    </div>
    @endif

    <div>
        <h1 class="text-[19px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $pageTitle }}</h1>
        <p class="mt-1.5 text-[13px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed max-w-[560px]">
            {{ $isFr
               ? 'Les artisans retenus au SIARC 2026 ont déjà une fiche sur la plateforme, créée à partir du dossier de la compétition. Si l\'une d\'elles est la vôtre, récupérez-la : vous en devenez propriétaire et pourrez la corriger avant toute publication.'
               : 'Artisans selected for SIARC 2026 already have a profile here, created from the competition records. If one of them is yours, claim it — you become its owner and can correct it before anything is published.' }}
        </p>
    </div>

    {{--
        Said before the claim, not after it. The two halves of this notice are
        the whole arrangement: taking back your own record costs nothing, and
        being listed publicly is what the subscription pays for. Anyone reading
        this should be able to claim, correct their details and stop there
        without owing the platform anything.
    --}}
    @if($candidates->isNotEmpty())
    <div class="ui-card">
        <h2 class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">
            {{ $isFr ? 'Ce que la récupération implique' : 'What claiming means' }}
        </h2>
        <ul class="mt-2 space-y-1.5 text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
            <li>
                {{ $isFr
                   ? 'La récupération est gratuite. La fiche devient la vôtre : vous pouvez la corriger, la compléter ou demander sa suppression, sans rien payer.'
                   : 'Claiming is free. The profile becomes yours: you can correct it, complete it or ask for it to be deleted, without paying anything.' }}
            </li>
            <li>
                {{ $isFr
                   ? 'Tant qu\'aucun paiement n\'est confirmé, la fiche reste un brouillon : elle n\'apparaît pas dans l\'annuaire public.'
                   : 'Until a payment is confirmed the profile stays a draft: it does not appear in the public directory.' }}
            </li>
            @if($entryPlan)
            <li>
                {{ $isFr
                   ? 'Apparaître publiquement demande une cotisation annuelle, à partir de'
                   : 'Appearing publicly requires a yearly subscription, from' }}
                <strong class="text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($entryPlan['price_yearly'], 0, ',', ' ') }} {{ $entryPlan['currency'] }}</strong>
                {{ $isFr ? 'par an (formule ' : 'per year (' }}{{ $entryPlan['name'] }}{{ $isFr ? ').' : ' plan).' }}
                {{ $isFr
                   ? 'Le paiement se fait par mobile money et n\'est enregistré qu\'après vérification par un administrateur.'
                   : 'Payment is by mobile money and is only recorded once an administrator has checked it.' }}
            </li>
            @endif
        </ul>
    </div>
    @endif

    @forelse($candidates as $c)
    <div class="ui-card">
        <div class="ui-card-head">
            <div class="min-w-0">
                <p class="ui-eyebrow">SIARC {{ $c->siarc_code }}</p>
                <h2 class="text-[16px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] mt-0.5">{{ $c->name_fr }}</h2>
            </div>
            <span class="ui-pill ui-pill-warn shrink-0">{{ $isFr ? 'Non revendiquée' : 'Unclaimed' }}</span>
        </div>

        <dl class="ui-dl ui-dl--2">
            @if($c->industry)
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Métier' : 'Trade' }}</dt>
                <dd class="ui-dd">{{ $c->source_metier ?: $c->industry->name_fr }}</dd>
            </div>
            @endif
            @if($c->region)
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Région' : 'Region' }}</dt>
                <dd class="ui-dd">{{ $c->region->name_fr }}</dd>
            </div>
            @endif
            @if($c->phone)
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Téléphone' : 'Phone' }}</dt>
                <dd class="ui-dd">{{ $c->phone }}</dd>
            </div>
            @endif
        </dl>

        @if($c->description_fr)
        <hr class="ui-divider">
        <p class="text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">{{ $c->description_fr }}</p>
        @endif

        <form method="POST" action="{{ route('siarc.claim.assign', ['business' => $c->id]) }}" class="mt-4"
              onsubmit="return confirm('{{ $isFr ? 'Confirmez-vous que cette fiche est la vôtre ?' : 'Do you confirm this profile is yours?' }}')">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">
            @if($paidPlans->count() > 1)
            {{-- Chosen now so the amount on the payment sheet is the one the
                 artisan picked, rather than one assigned on their behalf. --}}
            <label class="ui-label" for="plan-{{ $c->id }}">{{ $isFr ? 'Formule souhaitée' : 'Preferred plan' }}</label>
            <select name="plan" id="plan-{{ $c->id }}" class="ui-select mb-3">
                @foreach($paidPlans as $p)
                <option value="{{ $p['slug'] }}">{{ $p['name'] }} — {{ number_format($p['price_yearly'], 0, ',', ' ') }} {{ $p['currency'] }}{{ $isFr ? ' / an' : ' / year' }}</option>
                @endforeach
            </select>
            @elseif($entryPlan)
            <input type="hidden" name="plan" value="{{ $entryPlan['slug'] }}">
            @endif
            <button type="submit" class="ui-btn ui-btn-primary">
                <i data-lucide="check" class="w-4 h-4"></i>
                {{ $isFr ? "C'est mon profil — le récupérer" : 'This is mine — claim it' }}
            </button>
        </form>
    </div>
    @empty
    <div class="ui-card ui-card--flush">
        <div class="ui-empty">
            <i data-lucide="search-x" class="w-8 h-8 text-[#DCE7DF] mx-auto mb-2"></i>
            {{ $isFr
               ? 'Aucune fiche SIARC ne correspond à votre nom ou à votre numéro de téléphone.'
               : 'No SIARC profile matches your name or phone number.' }}
            <p class="mt-2 text-[12px] text-[#8A857A] dark:text-[#868778] max-w-[440px] mx-auto leading-relaxed">
                {{ $isFr
                   ? 'Vérifiez que le nom et le téléphone de votre compte sont bien ceux transmis lors de la compétition, puis revenez ici. Sinon, créez simplement votre entreprise.'
                   : 'Check that your account name and phone match what you gave at the competition, then come back. Otherwise, just create your business.' }}
            </p>
            <a href="{{ route('business.create', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-3">
                {{ $isFr ? 'Créer mon entreprise' : 'Create my business' }}
            </a>
        </div>
    </div>
    @endforelse
</div>
@endsection
