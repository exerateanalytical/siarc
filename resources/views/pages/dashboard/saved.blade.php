@extends('layouts.dashboard')

@php
$pageTitle = $lang === 'fr' ? 'Mes favoris' : 'Saved Items';
@endphp

@section('content')
<div class="max-w-3xl space-y-6">

    @if(session('success'))
        <div class="ui-alert ui-alert-ok">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Saved products --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="package" class="w-4 h-4 text-[#14652F]"></i>
            <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Produits sauvegardés' : 'Saved products' }}</h2>
            <span class="text-[11.5px] text-[#8A857A]">({{ $savedProducts->count() }})</span>
        </div>

        @if($savedProducts->isEmpty())
        <div class="ui-card text-center py-10 px-4">
            <i data-lucide="bookmark" class="w-8 h-8 text-[#DCE7DF] mx-auto mb-2"></i>
            <p class="text-[12.5px] text-[#8A857A]">{{ $lang === 'fr' ? 'Aucun produit sauvegardé. Explorez la galerie pour en ajouter.' : 'No saved products yet. Browse the gallery to add some.' }}</p>
            <a href="{{ route('gallery.search') }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-3">
                <i data-lucide="search" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Explorer les produits' : 'Browse products' }}
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($savedProducts as $product)
            <div class="ui-card ui-card--flush flex flex-col">
                <a href="{{ route('products.show', $product->slug) }}" class="block h-32 bg-[#F5F1E9] overflow-hidden">
                    @if($product->primaryImage)
                        <img src="{{ asset('storage/' . $product->primaryImage->file_path) }}" alt="" class="w-full h-full object-cover hover:scale-105 transition-transform">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <i data-lucide="image" class="w-6 h-6 text-[#CFC9BF]"></i>
                        </div>
                    @endif
                </a>
                <div class="p-3 flex-1 flex flex-col">
                    <a href="{{ route('products.show', $product->slug) }}" class="text-sm font-semibold text-[#1B1B18] hover:text-[#14652F] truncate">
                        {{ $lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr) }}
                    </a>
                    @if($product->business)
                    <p class="text-xs text-[#8A857A] truncate mt-0.5">
                        {{ $lang === 'fr' ? $product->business->name_fr : ($product->business->name_en ?? $product->business->name_fr) }}
                    </p>
                    @endif
                    <form method="POST" action="{{ route('products.toggle-save', $product->slug) }}" class="mt-auto pt-2">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ route('saved.index') }}">
                        <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">
                            <i data-lucide="bookmark-x" class="w-3.5 h-3.5"></i>
                            {{ $lang === 'fr' ? 'Retirer' : 'Remove' }}
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Saved businesses --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="building-2" class="w-4 h-4 text-[#14652F]"></i>
            <h2 class="ui-card-title">{{ $lang === 'fr' ? 'Entreprises sauvegardées' : 'Saved businesses' }}</h2>
            <span class="text-[11.5px] text-[#8A857A]">({{ $savedBusinesses->count() }})</span>
        </div>

        @if($savedBusinesses->isEmpty())
        <div class="ui-card text-center py-10 px-4">
            <i data-lucide="bookmark" class="w-8 h-8 text-[#DCE7DF] mx-auto mb-2"></i>
            <p class="text-[12.5px] text-[#8A857A]">{{ $lang === 'fr' ? 'Aucune entreprise sauvegardée.' : 'No saved businesses yet.' }}</p>
            <a href="{{ route('businesses.index') }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-3">
                <i data-lucide="search" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Explorer les entreprises' : 'Browse businesses' }}
            </a>
        </div>
        @else
        <div class="ui-card ui-card--flush">
            @foreach($savedBusinesses as $biz)
            <div class="flex items-center gap-3 px-4 py-3 border-b border-[#F0F1F0] last:border-0">
                <div class="w-10 h-10 rounded-xl bg-[#F5F1E9] flex items-center justify-center shrink-0 overflow-hidden border border-[#F0F1F0]">
                    @if($biz->logo)
                        <img src="{{ asset('storage/' . $biz->logo) }}" alt="" class="w-10 h-10 object-cover">
                    @else
                        <i data-lucide="building-2" class="w-4 h-4 text-[#8A857A]"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('businesses.show', $biz->slug) }}" class="text-sm font-semibold text-[#1B1B18] hover:text-[#14652F] truncate block">
                        {{ $lang === 'fr' ? $biz->name_fr : ($biz->name_en ?? $biz->name_fr) }}
                    </a>
                    <p class="text-xs text-[#8A857A] truncate">
                        {{ $lang === 'fr' ? ($biz->industry_fr ?? '') : ($biz->industry_en ?? $biz->industry_fr ?? '') }}
                    </p>
                </div>
                @if(in_array($biz->verification_tier, ['verified', 'certified']))
                <span class="ui-pill ui-pill-ok shrink-0">
                    <i data-lucide="badge-check" class="w-3 h-3"></i>
                    {{ $lang === 'fr' ? 'Vérifiée' : 'Verified' }}
                </span>
                @endif
                <form method="POST" action="{{ route('businesses.toggle-save', $biz->slug) }}" class="shrink-0">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('saved.index') }}">
                    <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm" title="{{ $lang === 'fr' ? 'Retirer' : 'Remove' }}">
                        <i data-lucide="bookmark-x" class="w-3.5 h-3.5"></i>
                        {{ $lang === 'fr' ? 'Retirer' : 'Remove' }}
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
