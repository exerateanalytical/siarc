@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Département technique' : 'Technical Department';
$tierLabels = ['basic' => $lang === 'fr' ? 'Basique' : 'Basic', 'verified' => $lang === 'fr' ? 'Vérifié' : 'Verified', 'certified' => $lang === 'fr' ? 'Certifié' : 'Certified'];
$docTypeLabels = [
    'rccm' => 'RCCM', 'niu' => 'NIU', 'anor' => 'ANOR', 'cnps' => 'CNPS', 'cmf' => 'CMF',
    'id_director' => $lang === 'fr' ? 'Pièce d\'identité' : 'Director ID',
    'financials' => $lang === 'fr' ? 'États financiers' : 'Financials',
    'product_cert' => $lang === 'fr' ? 'Certificat produit' : 'Product cert',
    'other' => $lang === 'fr' ? 'Autre' : 'Other',
];
@endphp

@section('content')
<div class="max-w-3xl">

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

    <!-- Verification applications -->
    <div class="flex items-center gap-2 mb-3">
        <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Demandes de vérification' : 'Verification applications' }}</h2>
        <span class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ $applications->count() }}</span>
    </div>
    <div class="space-y-4 mb-8">
        @forelse($applications as $app)
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $app->business->name_fr }}</p>
                    <p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Demande' : 'Requesting' }}: <span class="font-medium text-gray-600">{{ $tierLabels[$app->tier_requested] ?? $app->tier_requested }}</span> — {{ $app->submitted_at?->diffForHumans() }}</p>
                </div>
                <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $app->business->slug]) }}" target="_blank" class="text-xs text-forest-600 hover:underline flex items-center gap-1 shrink-0">
                    <i data-lucide="external-link" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Voir profil' : 'View profile' }}
                </a>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($app->documents as $doc)
                <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg hover:bg-gray-100">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-gray-400"></i>
                    {{ $docTypeLabels[$doc->type] ?? $doc->type }}
                </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('technical.verifications.approve', ['id' => $app->id]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium py-2 rounded-lg flex items-center justify-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Approuver' : 'Approve' }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-v-{{ $app->id }}').classList.toggle('hidden')" class="flex-1 border border-red-200 text-red-600 text-sm font-medium py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-red-50">
                    <i data-lucide="x" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Rejeter' : 'Reject' }}
                </button>
            </div>

            <div id="reject-v-{{ $app->id }}" class="hidden mt-3 pt-3 border-t border-gray-100">
                <form method="POST" action="{{ route('technical.verifications.reject', ['id' => $app->id]) }}">
                    @csrf
                    <textarea name="notes" required rows="2" placeholder="{{ $lang === 'fr' ? 'Raison du rejet (obligatoire)' : 'Rejection reason (required)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 mb-2 resize-none"></textarea>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Confirmer le rejet' : 'Confirm rejection' }}</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-xl text-center py-8">
            <i data-lucide="inbox" class="w-8 h-8 text-gray-200 mx-auto mb-2"></i>
            <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune demande en attente.' : 'No pending applications.' }}</p>
        </div>
        @endforelse
    </div>

    <!-- Certifications -->
    <div class="flex items-center gap-2 mb-3">
        <h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Certifications à vérifier' : 'Certifications to review' }}</h2>
        <span class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ $certifications->count() }}</span>
    </div>
    <div class="space-y-4">
        @forelse($certifications as $cert)
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $cert->business->name_fr }}</p>
                    <p class="text-xs text-gray-400">{{ $lang === 'fr' ? $cert->certification->name_fr : ($cert->certification->name_en ?? '') }} — {{ $cert->created_at->diffForHumans() }}</p>
                </div>
                @if($cert->certificate_file)
                <a href="{{ \Storage::url($cert->certificate_file) }}" target="_blank" class="text-xs text-forest-600 hover:underline flex items-center gap-1 shrink-0">
                    <i data-lucide="file-text" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Voir document' : 'View document' }}
                </a>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('technical.certifications.approve', ['id' => $cert->id]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium py-2 rounded-lg flex items-center justify-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-c-{{ $cert->id }}').classList.toggle('hidden')" class="flex-1 border border-red-200 text-red-600 text-sm font-medium py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-red-50">
                    <i data-lucide="x" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Rejeter' : 'Reject' }}
                </button>
            </div>
            <div id="reject-c-{{ $cert->id }}" class="hidden mt-3 pt-3 border-t border-gray-100">
                <form method="POST" action="{{ route('technical.certifications.reject', ['id' => $cert->id]) }}">
                    @csrf
                    <textarea name="notes" required rows="2" placeholder="{{ $lang === 'fr' ? 'Raison du rejet (obligatoire)' : 'Rejection reason (required)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 mb-2 resize-none"></textarea>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Confirmer le rejet' : 'Confirm rejection' }}</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-xl text-center py-8">
            <i data-lucide="inbox" class="w-8 h-8 text-gray-200 mx-auto mb-2"></i>
            <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune certification en attente.' : 'No pending certifications.' }}</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
