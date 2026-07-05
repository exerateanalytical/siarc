@extends('layouts.app')

@php
$title = ($lang === 'fr' ? 'Recherche' : 'Search') . ' — SIARC Galerie';
$totalResults = $businesses->count() + $products->count();
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="mb-6">
        <h1 class="text-xl font-bold text-[#1B1B18] flex items-center gap-2 mb-2">
            <i data-lucide="search" class="w-5 h-5 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Résultats pour' : 'Results for' }} "{{ $q }}"
        </h1>
        <p class="text-sm text-[#8A857A]">
            @if(strlen($q) < 2)
                {{ $lang === 'fr' ? 'Entrez au moins 2 caractères pour rechercher.' : 'Enter at least 2 characters to search.' }}
            @else
                {{ $totalResults }} {{ $lang === 'fr' ? 'résultat(s) trouvé(s)' : 'result(s) found' }}
            @endif
        </p>
    </div>

    @if(strlen($q) >= 2 && $totalResults === 0)
    <div class="text-center py-16 text-[#A8A296]">
        <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
        <p class="font-medium text-[#6F6B60]">{{ $lang === 'fr' ? 'Aucun résultat trouvé' : 'No results found' }}</p>
        <p class="text-sm mt-1">{{ $lang === 'fr' ? 'Essayez un autre mot-clé.' : 'Try a different keyword.' }}</p>
    </div>
    @endif

    @if($businesses->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-base font-semibold text-[#1B1B18] mb-4 flex items-center gap-2">
            <i data-lucide="building-2" class="w-4 h-4 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}
            <span class="text-sm font-normal text-[#A8A296]">({{ $businesses->count() }})</span>
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
        <h2 class="text-base font-semibold text-[#1B1B18] mb-4 flex items-center gap-2">
            <i data-lucide="package" class="w-4 h-4 text-forest-500"></i>
            {{ $lang === 'fr' ? 'Produits' : 'Products' }}
            <span class="text-sm font-normal text-[#A8A296]">({{ $products->count() }})</span>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($products as $product)
            <article class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)] flex flex-col">
                <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $product->slug]) }}" class="block aspect-square bg-[#F5F1E9] flex items-center justify-center">
                    @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->file_path) }}" alt="" class="w-full h-full object-cover">
                    @else
                    <i data-lucide="package" class="w-8 h-8 text-[#D9CFBB]"></i>
                    @endif
                </a>
                <div class="p-3 flex-1 flex flex-col">
                    <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $product->slug]) }}" class="text-[12.5px] font-bold text-[#1D1B16] truncate hover:text-leaf transition-colors">
                        {{ $lang === 'fr' ? $product->name_fr : ($product->name_en ?? $product->name_fr) }}
                    </a>
                    <p class="mt-0.5 text-[11px] text-[#6F6B60] truncate">{{ $lang === 'fr' ? $product->business->name_fr : ($product->business->name_en ?? $product->business->name_fr) }}</p>
                    <a href="{{ route('products.show', ['lang' => $lang, 'slug' => $product->slug]) }}"
                        class="mt-auto pt-2.5 block"><span class="h-[30px] border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center text-[11.5px] font-semibold text-[#1D1B16] transition-colors">{{ $lang === 'fr' ? 'Voir le produit' : 'View product' }}</span></a>
                </div>
            </article>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
