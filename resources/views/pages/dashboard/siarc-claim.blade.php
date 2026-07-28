@extends('layouts.dashboard')

@php
    $isFr = $lang === 'fr';
    $pageTitle = $isFr ? 'Récupérer mon profil SIARC 2026' : 'Claim my SIARC 2026 profile';
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
        <h1 class="text-[19px] font-bold text-[#1B1B18]">{{ $pageTitle }}</h1>
        <p class="mt-1.5 text-[13px] text-[#55524A] leading-relaxed max-w-[560px]">
            {{ $isFr
               ? 'Les artisans retenus au SIARC 2026 ont déjà une fiche sur la plateforme, créée à partir du dossier de la compétition. Si l\'une d\'elles est la vôtre, récupérez-la : vous en devenez propriétaire et pourrez la corriger avant toute publication.'
               : 'Artisans selected for SIARC 2026 already have a profile here, created from the competition records. If one of them is yours, claim it — you become its owner and can correct it before anything is published.' }}
        </p>
    </div>

    @forelse($candidates as $c)
    <div class="ui-card">
        <div class="ui-card-head">
            <div class="min-w-0">
                <p class="ui-eyebrow">SIARC {{ $c->siarc_code }}</p>
                <h2 class="text-[16px] font-bold text-[#1B1B18] mt-0.5">{{ $c->name_fr }}</h2>
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
        <p class="text-[12.5px] text-[#55524A] leading-relaxed">{{ $c->description_fr }}</p>
        @endif

        <form method="POST" action="{{ route('siarc.claim.assign', ['business' => $c->id]) }}" class="mt-4"
              onsubmit="return confirm('{{ $isFr ? 'Confirmez-vous que cette fiche est la vôtre ?' : 'Do you confirm this profile is yours?' }}')">
            @csrf
            <input type="hidden" name="lang" value="{{ $lang }}">
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
            <p class="mt-2 text-[12px] text-[#8A857A] max-w-[440px] mx-auto leading-relaxed">
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
