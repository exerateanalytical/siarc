@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Recherche' : 'Search') . ' — SIAC Galerie';
$totalResults = $businesses->count() + $products->count();
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2 mb-2">
            <i data-lucide="search" class="w-5 h-5 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Résultats pour' : 'Results for' }} "{{ $q }}"
        </h1>
        <p class="text-sm text-gray-500">
            @if(strlen($q) < 2)
                {{ $lang === 'fr' ? 'Entrez au moins 2 caractères pour rechercher.' : 'Enter at least 2 characters to search.' }}
            @else
                {{ $totalResults }} {{ $lang === 'fr' ? 'résultat(s) trouvé(s)' : 'result(s) found' }}
            @endif
        </p>
    </div>

    @if(strlen($q) >= 2 && $totalResults === 0)
    <div class="text-center py-16 text-gray-400">
        <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p class="font-medium text-gray-600">{{ $lang === 'fr' ? 'Aucun résultat trouvé' : 'No results found' }}</p>
        <p class="text-sm mt-1">{{ $lang === 'fr' ? 'Essayez un autre mot-clé.' : 'Try a different keyword.' }}</p>
    </div>
    @endif

    @if($businesses->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="building-2" class="w-4 h-4 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}
            <span class="text-sm font-normal text-gray-400">({{ $businesses->count() }})</span>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($businesses as $business)
            @include('components.business-card', ['business' => $business, 'lang' => $lang])
            @endforeach
        </div>
    </div>
    @endif

    @if($products->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Produits' : 'Products' }}
            <span class="text-sm font-normal text-gray-400">({{ $products->count() }})</span>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($products as $product)
            <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $product->slug]) }}" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-forest-300 hover:shadow-sm transition-all">
                <div class="aspect-square bg-gray-100 flex items-center justify-center">
                    @if($product->primaryImage)
                    <img src="{{ $product->primaryImage->url }}" alt="" class="w-full h-full object-cover">
                    @else
                    <i data-lucide="package" class="w-6 h-6 text-gray-300"></i>
                    @endif
                </div>
                <div class="p-2.5">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ $lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr) }}</p>
                    <p class="text-[11px] text-gray-400 truncate">{{ $lang === 'fr' ? $product->business->name_fr : ($product->business->name_en ?? $product->business->name_fr) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
