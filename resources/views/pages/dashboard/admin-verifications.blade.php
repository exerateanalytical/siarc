@extends('layouts.app')

@php
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
<div class="max-w-3xl mx-auto px-4 py-6">

    <div class="flex items-center gap-2 mb-6">
        <a href="/tableau-de-bord/admin" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <div class="w-8 h-8 bg-forest-100 rounded-lg flex items-center justify-center">
            <i data-lucide="badge-check" class="w-4 h-4 text-forest-600"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ $lang === 'fr' ? 'File d\'attente — Vérifications' : 'Verification queue' }}</h1>
        <span class="ml-auto text-xs font-medium bg-amber-100 text-amber-700 px-2 py-1 rounded-full">{{ $applications->total() }}</span>
    </div>

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

    <div class="space-y-4">
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
                <form method="POST" action="{{ route('admin.verifications.approve', ['id' => $app->id]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full bg-forest-600 hover:bg-forest-700 text-white text-sm font-medium py-2 rounded-lg flex items-center justify-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Approuver' : 'Approve' }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-{{ $app->id }}').classList.toggle('hidden')" class="flex-1 border border-red-200 text-red-600 text-sm font-medium py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-red-50">
                    <i data-lucide="x" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Rejeter' : 'Reject' }}
                </button>
            </div>

            <div id="reject-{{ $app->id }}" class="hidden mt-3 pt-3 border-t border-gray-100">
                <form method="POST" action="{{ route('admin.verifications.reject', ['id' => $app->id]) }}">
                    @csrf
                    <textarea name="notes" required rows="2" placeholder="{{ $lang === 'fr' ? 'Raison du rejet (obligatoire)' : 'Rejection reason (required)' }}" class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 mb-2 resize-none"></textarea>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">{{ $lang === 'fr' ? 'Confirmer le rejet' : 'Confirm rejection' }}</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white border border-gray-200 rounded-xl text-center py-12">
            <i data-lucide="inbox" class="w-10 h-10 text-gray-200 mx-auto mb-3"></i>
            <p class="text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucune demande en attente.' : 'No pending applications.' }}</p>
        </div>
        @endforelse
    </div>

    @if($applications->hasPages())
    <div class="mt-4">{{ $applications->links() }}</div>
    @endif
</div>
@endsection
