@extends('layouts.admin')

@php
$pageTitle = $business->name_fr;
$statusLabels = [
    'draft' => $lang === 'fr' ? 'Brouillon' : 'Draft',
    'published' => $lang === 'fr' ? 'Publiée' : 'Published',
    'suspended' => $lang === 'fr' ? 'Suspendue' : 'Suspended',
    'rejected' => $lang === 'fr' ? 'Rejetée' : 'Rejected',
];
@endphp

@section('content')
<div class="max-w-3xl">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.businesses') }}" class="ui-btn ui-btn-ghost ui-btn-sm -ml-2">
            <i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A]"></i>
        </a>
        <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" target="_blank" class="text-xs text-[#157A43] hover:underline flex items-center gap-1 ml-auto">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Voir le profil public' : 'View public profile' }}
        </a>
    </div>

    @if(session('success'))
    <div class="ui-alert ui-alert-ok mb-4">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Header card -->
    <div class="ui-card mb-4">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h1 class="text-lg font-bold text-[#1B1B18]">{{ $business->name_fr }}</h1>
                <p class="ui-card-sub">{{ $business->industry?->name_fr }} — {{ $business->city?->name_fr ?? $business->region?->name_fr }}</p>
            </div>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                'bg-green-100 text-green-700' => $business->status === 'published',
                'bg-[#F1EDE3] text-[#8A857A]' => $business->status === 'draft',
                'bg-red-100 text-red-700' => in_array($business->status, ['suspended', 'rejected']),
            ])>{{ $statusLabels[$business->status] ?? $business->status }}</span>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @if($business->status !== 'published')
            <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $business->id]) }}">
                @csrf<input type="hidden" name="status" value="published">
                <button type="submit" class="ui-btn ui-btn-secondary ui-btn-sm">{{ $lang === 'fr' ? 'Publier' : 'Publish' }}</button>
            </form>
            @endif
            @if($business->status !== 'suspended')
            <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $business->id]) }}">
                @csrf<input type="hidden" name="status" value="suspended">
                <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">{{ $lang === 'fr' ? 'Suspendre' : 'Suspend' }}</button>
            </form>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm border-t border-[#F5F1E8] pt-4">
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Propriétaire' : 'Owner' }}</p><p class="ui-dd">{{ $business->user?->name }}</p></div>
            <div><p class="ui-dt">Email</p><p class="ui-dd truncate">{{ $business->user?->email }}</p></div>
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Niveau' : 'Tier' }}</p><p class="ui-dd capitalize">{{ $business->verification_tier }}</p></div>
            <div><p class="ui-dt">{{ $lang === 'fr' ? 'Vues' : 'Views' }}</p><p class="ui-dd">{{ number_format($business->views_count) }}</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div class="ui-card text-center">
            <p class="text-[21px] font-bold text-[#1B1B18] leading-tight">{{ $business->products->count() }}</p>
            <p class="ui-dt">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</p>
        </div>
        <div class="ui-card text-center">
            <p class="text-[21px] font-bold text-[#1B1B18] leading-tight">{{ $business->reviews->count() }}</p>
            <p class="ui-dt">{{ $lang === 'fr' ? 'Avis' : 'Reviews' }}</p>
        </div>
        <div class="ui-card text-center">
            <p class="text-[21px] font-bold text-[#1B1B18] leading-tight">{{ $business->certifications->count() }}</p>
            <p class="ui-dt">{{ $lang === 'fr' ? 'Certifications' : 'Certifications' }}</p>
        </div>
    </div>

    <!-- Products -->
    <div class="ui-card ui-card--flush mb-4">
        <div class="px-5 py-3.5 border-b border-[#F5F1E8]"><h2 class="ui-card-title">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</h2></div>
        @forelse($business->products->take(8) as $product)
        <div class="flex items-center gap-3 px-5 py-2.5 border-b border-[#F5F1E8] last:border-0">
            <span class="flex-1 truncate text-[12.5px] text-[#3B382F]">{{ $product->name_fr }}</span>
            <span class="ui-dt">{{ $product->status }}</span>
        </div>
        @empty
        <div class="ui-empty">{{ $lang === 'fr' ? 'Aucun produit.' : 'No products.' }}</div>
        @endforelse
    </div>

    <!-- Verification history -->
    @if($business->verificationApplications->isNotEmpty())
    <div class="ui-card ui-card--flush mb-4">
        <div class="px-5 py-3.5 border-b border-[#F5F1E8]"><h2 class="ui-card-title">{{ $lang === 'fr' ? 'Historique de vérification' : 'Verification history' }}</h2></div>
        @foreach($business->verificationApplications as $app)
        <div class="flex items-center gap-3 px-5 py-2.5 border-b border-[#F5F1E8] last:border-0">
            <span class="flex-1 text-[12.5px] text-[#3B382F]">{{ $lang === 'fr' ? 'Demande' : 'Request' }}: {{ $app->tier_requested }}</span>
            <span class="ui-dt">{{ $app->status }} — {{ $app->created_at->format('d/m/Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Audit trail -->
    @if($auditEntries->isNotEmpty())
    <div class="ui-card ui-card--flush">
        <div class="px-5 py-3.5 border-b border-[#F5F1E8]"><h2 class="ui-card-title">{{ $lang === 'fr' ? 'Actions administratives' : 'Admin actions' }}</h2></div>
        @foreach($auditEntries as $entry)
        <div class="flex items-center gap-3 px-5 py-2.5 border-b border-[#F5F1E8] last:border-0">
            <span class="flex-1 text-[12.5px] text-[#3B382F]">{{ $entry->action }} — {{ $entry->user?->name }}</span>
            <span class="ui-dt">{{ $entry->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
