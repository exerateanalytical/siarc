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
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl p-3.5 mb-4">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <!-- Current tier -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
        <p class="text-xs text-gray-400 mb-1">{{ $lang === 'fr' ? 'Niveau actuel' : 'Current tier' }}</p>
        <p class="text-lg font-bold text-gray-900 flex items-center gap-2">
            <i data-lucide="shield" class="w-5 h-5 text-forest-600"></i>
            {{ $tierLabels[$business->verification_tier] ?? $business->verification_tier }}
        </p>
    </div>

    <!-- Application history -->
    @if($applications->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">{{ $lang === 'fr' ? 'Historique des demandes' : 'Application history' }}</h2>
        <div class="space-y-2">
            @foreach($applications->sortByDesc('submitted_at') as $app)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $tierLabels[$app->tier_requested] ?? $app->tier_requested }}</p>
                    <p class="text-xs text-gray-400">{{ $app->submitted_at?->format('d/m/Y') }} — {{ $app->documents->count() }} {{ $lang === 'fr' ? 'document(s)' : 'document(s)' }}</p>
                </div>
                <span @class([
                    'text-xs font-medium px-2 py-1 rounded-full',
                    'bg-amber-100 text-amber-700' => in_array($app->status, ['submitted', 'under_review']),
                    'bg-green-100 text-green-700' => $app->status === 'approved',
                    'bg-red-100 text-red-700' => $app->status === 'rejected',
                    'bg-gray-100 text-gray-500' => $app->status === 'draft',
                ])>{{ $statusLabels[$app->status] ?? $app->status }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($pendingApplication)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-700 flex items-start gap-2">
        <i data-lucide="clock" class="w-4 h-4 shrink-0 mt-0.5"></i>
        {{ $lang === 'fr' ? 'Une demande est déjà en attente d\'examen.' : 'An application is already pending review.' }}
    </div>
    @endif

    <!-- Application form -->
    <form method="POST" action="{{ route('verification.apply') }}" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-xl p-5 mt-5 space-y-4">
        @csrf
        <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Nouvelle demande' : 'New application' }}</h2>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $lang === 'fr' ? 'Niveau demandé' : 'Requested tier' }}</label>
            <select name="tier_requested" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                <option value="basic">{{ $tierLabels['basic'] }}</option>
                <option value="verified">{{ $tierLabels['verified'] }}</option>
                <option value="certified">{{ $tierLabels['certified'] }}</option>
            </select>
        </div>

        <p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Téléversez au moins un document justificatif (formats PDF, JPG, PNG).' : 'Upload at least one supporting document (PDF, JPG, PNG).' }}</p>

        @for($i = 0; $i < 3; $i++)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-gray-50 rounded-lg">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $lang === 'fr' ? 'Type de document' : 'Document type' }}</label>
                <select name="documents[{{ $i }}][type]" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-forest-400">
                    <option value="">{{ $lang === 'fr' ? 'Aucun' : 'None' }}</option>
                    @foreach($docTypeLabels as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $lang === 'fr' ? 'Fichier' : 'File' }}</label>
                <input type="file" name="documents[{{ $i }}][file]" accept=".pdf,image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-white file:border file:border-gray-200 file:text-xs">
            </div>
        </div>
        @endfor

        <button type="submit" class="w-full bg-forest-600 hover:bg-forest-700 text-white font-semibold py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
            <i data-lucide="send" class="w-4 h-4"></i>
            {{ $lang === 'fr' ? 'Soumettre la demande' : 'Submit application' }}
        </button>
    </form>
</div>
@endsection
