@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Vérification de l\'entreprise' : 'Business verification';
$tierLabels = [
    'unverified' => $lang === 'fr' ? 'Non vérifié' : 'Unverified',
    'basic'      => $lang === 'fr' ? 'Basique' : 'Basic',
    'verified'   => $lang === 'fr' ? 'Vérifié' : 'Verified',
    'certified'  => $lang === 'fr' ? 'Certifié' : 'Certified',
];
$statusLabels = [
    'draft'        => $lang === 'fr' ? 'Brouillon' : 'Draft',
    'submitted'    => $lang === 'fr' ? 'Soumise' : 'Submitted',
    'under_review' => $lang === 'fr' ? 'En cours d\'examen' : 'Under review',
    'approved'     => $lang === 'fr' ? 'Approuvée' : 'Approved',
    'rejected'     => $lang === 'fr' ? 'Rejetée' : 'Rejected',
];
$docTypeLabels = [
    'rccm' => 'RCCM', 'niu' => 'NIU', 'anor' => 'ANOR', 'cnps' => 'CNPS', 'cmf' => 'CMF',
    'id_director' => $lang === 'fr' ? 'Pièce d\'identité du dirigeant' : 'Director ID',
    'financials' => $lang === 'fr' ? 'États financiers' : 'Financial statements',
    'product_cert' => $lang === 'fr' ? 'Certificat produit' : 'Product certificate',
    'other' => $lang === 'fr' ? 'Autre' : 'Other',
];
$pendingApplication = $applications->whereIn('status', ['submitted', 'under_review'])->first();

$fileCls = 'ui-file';
@endphp

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="ui-alert ui-alert-danger mb-4">
        <i data-lucide="alert-circle" class="w-4 h-4"></i>
        <div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    </div>
    @endif

    <!-- Current tier -->
    <div class="ui-card mb-5">
        <p class="ui-dt">{{ $lang === 'fr' ? 'Niveau actuel' : 'Current tier' }}</p>
        <p class="text-lg font-bold text-[#1B1B18] flex items-center gap-2 mt-1">
            <i data-lucide="shield" class="w-5 h-5 text-forest-600"></i>
            {{ $tierLabels[$business->verification_tier] ?? $business->verification_tier }}
        </p>
    </div>

    <!-- Application history -->
    @if($applications->isNotEmpty())
    <div class="ui-card mb-5">
        <h2 class="ui-card-title mb-3">{{ $lang === 'fr' ? 'Historique des demandes' : 'Application history' }}</h2>
        <div class="space-y-2">
            @foreach($applications->sortByDesc('submitted_at') as $app)
            <div class="flex items-center justify-between p-3 bg-[#FBF9F4] rounded-lg">
                <div>
                    <p class="text-sm font-medium text-[#262521]">{{ $tierLabels[$app->tier_requested] ?? $app->tier_requested }}</p>
                    <p class="text-xs text-[#A8A296]">{{ $app->submitted_at?->format('d/m/Y') }} — {{ $app->documents->count() }} {{ $lang === 'fr' ? 'document(s)' : 'document(s)' }}</p>
                </div>
                <span @class([
                    'ui-pill',
                    'ui-pill-warn'    => in_array($app->status, ['submitted', 'under_review']),
                    'ui-pill-ok'      => $app->status === 'approved',
                    'ui-pill-danger'  => $app->status === 'rejected',
                    'ui-pill-neutral' => $app->status === 'draft',
                ])>{{ $statusLabels[$app->status] ?? $app->status }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($pendingApplication)
    <div class="ui-alert ui-alert-warn">
        <i data-lucide="clock" class="w-4 h-4"></i>
        {{ $lang === 'fr' ? 'Une demande est déjà en attente d\'examen.' : 'An application is already pending review.' }}
    </div>
    @endif

    <!-- Application form -->
    <form method="POST" action="{{ route('verification.apply') }}" enctype="multipart/form-data" class="ui-card mt-5 space-y-4">
        @csrf
        <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Nouvelle demande' : 'New application' }}</h2>

        <div>
            <label class="ui-label">{{ $lang === 'fr' ? 'Niveau demandé' : 'Requested tier' }}</label>
            <select name="tier_requested" required class="ui-field ui-select">
                <option value="basic">{{ $tierLabels['basic'] }}</option>
                <option value="verified">{{ $tierLabels['verified'] }}</option>
                <option value="certified">{{ $tierLabels['certified'] }}</option>
            </select>
        </div>

        <p class="ui-hint">{{ $lang === 'fr' ? 'Téléversez au moins un document justificatif (formats PDF, JPG, PNG).' : 'Upload at least one supporting document (PDF, JPG, PNG).' }}</p>

        @for($i = 0; $i < 3; $i++)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-[#FBF9F4] rounded-lg">
            <div>
                <label class="ui-label">{{ $lang === 'fr' ? 'Type de document' : 'Document type' }}</label>
                <select name="documents[{{ $i }}][type]" class="ui-field ui-select">
                    <option value="">{{ $lang === 'fr' ? 'Aucun' : 'None' }}</option>
                    @foreach($docTypeLabels as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="ui-label">{{ $lang === 'fr' ? 'Fichier' : 'File' }}</label>
                <input type="file" name="documents[{{ $i }}][file]" accept=".pdf,image/*" class="{{ $fileCls }}">
            </div>
        </div>
        @endfor

        <button type="submit" class="ui-btn ui-btn-primary ui-btn-lg ui-btn-block">
            <i data-lucide="send" class="w-4 h-4"></i>
            {{ $lang === 'fr' ? 'Soumettre la demande' : 'Submit application' }}
        </button>
    </form>
</div>
@endsection
