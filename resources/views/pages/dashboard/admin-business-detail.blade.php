@extends('layouts.dashboard')

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
<div class="max-w-3xl mx-auto">

    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.businesses') }}" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
            <i data-lucide="arrow-left" class="w-4 h-4 text-gray-500"></i>
        </a>
        <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $business->slug]) }}" target="_blank" class="text-xs text-forest-600 hover:underline flex items-center gap-1 ml-auto">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>{{ $lang === 'fr' ? 'Voir le profil public' : 'View public profile' }}
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl p-3.5 mb-4 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
    </div>
    @endif

    <!-- Header card -->
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ $business->name_fr }}</h1>
                <p class="text-sm text-gray-500">{{ $business->industry?->name_fr }} — {{ $business->city?->name_fr ?? $business->region?->name_fr }}</p>
            </div>
            <span @class([
                'text-xs font-medium px-2 py-1 rounded-full shrink-0',
                'bg-green-100 text-green-700' => $business->status === 'published',
                'bg-gray-100 text-gray-500' => $business->status === 'draft',
                'bg-red-100 text-red-700' => in_array($business->status, ['suspended', 'rejected']),
            ])>{{ $statusLabels[$business->status] ?? $business->status }}</span>
        </div>

        <div class="flex flex-wrap gap-2 mb-4">
            @if($business->status !== 'published')
            <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $business->id]) }}">
                @csrf<input type="hidden" name="status" value="published">
                <button type="submit" class="text-xs font-medium bg-green-50 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-100">{{ $lang === 'fr' ? 'Publier' : 'Publish' }}</button>
            </form>
            @endif
            @if($business->status !== 'suspended')
            <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $business->id]) }}">
                @csrf<input type="hidden" name="status" value="suspended">
                <button type="submit" class="text-xs font-medium bg-red-50 text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-100">{{ $lang === 'fr' ? 'Suspendre' : 'Suspend' }}</button>
            </form>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm border-t border-gray-100 pt-4">
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Propriétaire' : 'Owner' }}</p><p class="font-medium text-gray-800">{{ $business->user?->name }}</p></div>
            <div><p class="text-xs text-gray-400">Email</p><p class="font-medium text-gray-800 truncate">{{ $business->user?->email }}</p></div>
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Niveau' : 'Tier' }}</p><p class="font-medium text-gray-800 capitalize">{{ $business->verification_tier }}</p></div>
            <div><p class="text-xs text-gray-400">{{ $lang === 'fr' ? 'Vues' : 'Views' }}</p><p class="font-medium text-gray-800">{{ number_format($business->views_count) }}</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $business->products->count() }}</p>
            <p class="text-xs text-gray-500">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $business->reviews->count() }}</p>
            <p class="text-xs text-gray-500">{{ $lang === 'fr' ? 'Avis' : 'Reviews' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-gray-900">{{ $business->certifications->count() }}</p>
            <p class="text-xs text-gray-500">{{ $lang === 'fr' ? 'Certifications' : 'Certifications' }}</p>
        </div>
    </div>

    <!-- Products -->
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Produits' : 'Products' }}</h2></div>
        @forelse($business->products->take(8) as $product)
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0">
            <span class="text-sm text-gray-700 flex-1 truncate">{{ $product->name_fr }}</span>
            <span class="text-xs text-gray-400">{{ $product->status }}</span>
        </div>
        @empty
        <div class="text-center py-6 text-sm text-gray-400">{{ $lang === 'fr' ? 'Aucun produit.' : 'No products.' }}</div>
        @endforelse
    </div>

    <!-- Verification history -->
    @if($business->verificationApplications->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Historique de vérification' : 'Verification history' }}</h2></div>
        @foreach($business->verificationApplications as $app)
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0">
            <span class="text-sm text-gray-700 flex-1">{{ $lang === 'fr' ? 'Demande' : 'Request' }}: {{ $app->tier_requested }}</span>
            <span class="text-xs text-gray-400">{{ $app->status }} — {{ $app->created_at->format('d/m/Y') }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Audit trail -->
    @if($auditEntries->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100"><h2 class="text-sm font-semibold text-gray-900">{{ $lang === 'fr' ? 'Actions administratives' : 'Admin actions' }}</h2></div>
        @foreach($auditEntries as $entry)
        <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0">
            <span class="text-sm text-gray-700 flex-1">{{ $entry->action }} — {{ $entry->user?->name }}</span>
            <span class="text-xs text-gray-400">{{ $entry->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
