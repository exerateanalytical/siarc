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

    <!-- Verification applications -->
    <div class="flex items-center gap-2 mb-3">
        <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Demandes de vérification' : 'Verification applications' }}</h2>
        <span class="ui-pill ui-pill-warn">{{ $applications->count() }}</span>
    </div>
    <div class="space-y-4 mb-8">
        @forelse($applications as $app)
        <div class="ui-card">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <p class="text-[13px] font-semibold text-[#1B1B18]">{{ $app->business->name_fr }}</p>
                    <p class="ui-hint">{{ $lang === 'fr' ? 'Demande' : 'Requesting' }}: <span class="font-semibold text-[#3B382F]">{{ $tierLabels[$app->tier_requested] ?? $app->tier_requested }}</span> — {{ $app->submitted_at?->diffForHumans() }}</p>
                </div>
                <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $app->business->slug]) }}" target="_blank" class="ui-btn ui-btn-ghost ui-btn-sm shrink-0">
                    <i data-lucide="external-link" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Voir profil' : 'View profile' }}
                </a>
            </div>

            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($app->documents as $doc)
                <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="ui-btn ui-btn-secondary ui-btn-sm">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    {{ $docTypeLabels[$doc->type] ?? $doc->type }}
                </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('technical.verifications.approve', ['id' => $app->id]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-block">
                        <i data-lucide="check" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Approuver' : 'Approve' }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-v-{{ $app->id }}').classList.toggle('hidden')" class="ui-btn ui-btn-danger flex-1">
                    <i data-lucide="x" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Rejeter' : 'Reject' }}
                </button>
            </div>

            <div id="reject-v-{{ $app->id }}" class="hidden mt-3 pt-3 border-t border-[#F1EDE4]">
                <form method="POST" action="{{ route('technical.verifications.reject', ['id' => $app->id]) }}">
                    @csrf
                    <textarea name="notes" required rows="2" placeholder="{{ $lang === 'fr' ? 'Raison du rejet (obligatoire)' : 'Rejection reason (required)' }}" class="ui-field ui-textarea min-h-[64px] mb-2 resize-none"></textarea>
                    <button type="submit" class="ui-btn ui-btn-danger">{{ $lang === 'fr' ? 'Confirmer le rejet' : 'Confirm rejection' }}</button>
                </form>
            </div>
        </div>
        @empty
        <div class="ui-card text-center py-8">
            <i data-lucide="inbox" class="w-8 h-8 text-[#EFEBE2] mx-auto mb-2"></i>
            <p class="text-[12.5px] text-[#8A857A]">{{ $lang === 'fr' ? 'Aucune demande en attente.' : 'No pending applications.' }}</p>
        </div>
        @endforelse
    </div>

    <!-- Certifications -->
    <div class="flex items-center gap-2 mb-3">
        <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Certifications à vérifier' : 'Certifications to review' }}</h2>
        <span class="ui-pill ui-pill-warn">{{ $certifications->count() }}</span>
    </div>
    <div class="space-y-4">
        @forelse($certifications as $cert)
        <div class="ui-card">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <p class="text-[13px] font-semibold text-[#1B1B18]">{{ $cert->business->name_fr }}</p>
                    <p class="ui-hint">{{ $lang === 'fr' ? $cert->certification->name_fr : ($cert->certification->name_en ?? '') }} — {{ $cert->created_at->diffForHumans() }}</p>
                </div>
                @if($cert->certificate_file)
                <a href="{{ \Storage::url($cert->certificate_file) }}" target="_blank" class="ui-btn ui-btn-ghost ui-btn-sm shrink-0">
                    <i data-lucide="file-text" class="w-3 h-3"></i>{{ $lang === 'fr' ? 'Voir document' : 'View document' }}
                </a>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('technical.certifications.approve', ['id' => $cert->id]) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-block">
                        <i data-lucide="check" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Vérifier' : 'Verify' }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-c-{{ $cert->id }}').classList.toggle('hidden')" class="ui-btn ui-btn-danger flex-1">
                    <i data-lucide="x" class="w-4 h-4"></i>{{ $lang === 'fr' ? 'Rejeter' : 'Reject' }}
                </button>
            </div>
            <div id="reject-c-{{ $cert->id }}" class="hidden mt-3 pt-3 border-t border-[#F1EDE4]">
                <form method="POST" action="{{ route('technical.certifications.reject', ['id' => $cert->id]) }}">
                    @csrf
                    <textarea name="notes" required rows="2" placeholder="{{ $lang === 'fr' ? 'Raison du rejet (obligatoire)' : 'Rejection reason (required)' }}" class="ui-field ui-textarea min-h-[64px] mb-2 resize-none"></textarea>
                    <button type="submit" class="ui-btn ui-btn-danger">{{ $lang === 'fr' ? 'Confirmer le rejet' : 'Confirm rejection' }}</button>
                </form>
            </div>
        </div>
        @empty
        <div class="ui-card text-center py-8">
            <i data-lucide="inbox" class="w-8 h-8 text-[#EFEBE2] mx-auto mb-2"></i>
            <p class="text-[12.5px] text-[#8A857A]">{{ $lang === 'fr' ? 'Aucune certification en attente.' : 'No pending certifications.' }}</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
