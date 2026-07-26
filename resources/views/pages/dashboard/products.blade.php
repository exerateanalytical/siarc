@extends('layouts.dashboard')

@php
$isFr = $lang === 'fr';
$pageTitle = $isFr ? 'Mes produits' : 'My products';

$tabs = [
    ''          => [$isFr ? 'Tous' : 'All',          $counts['all']],
    'published' => [$isFr ? 'Publiés' : 'Published', $counts['published']],
    'draft'     => [$isFr ? 'Brouillons' : 'Drafts', $counts['draft']],
];
@endphp

@section('content')
<div class="max-w-5xl space-y-5">

    @if(session('success'))
        <div class="flex items-start gap-2 bg-[#E2F3E8] border border-[#BFDCC8] rounded-lg px-4 py-3 text-sm text-[#14532D]">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar: search + status tabs + primary action --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach($tabs as $val => [$label, $count])
            <a href="{{ route('products.web-index', array_filter(['status' => $val ?: null, 'q' => $q ?: null])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12.5px] font-semibold border transition-colors
                      {{ (string) ($status ?? '') === $val
                         ? 'bg-[#14532D] border-[#14532D] text-white'
                         : 'bg-white border-[#ECECEA] text-[#55524A] hover:border-[#14652F] hover:text-[#14652F]' }}">
                {{ $label }}
                <span class="text-[11px] opacity-70">{{ $count }}</span>
            </a>
            @endforeach
        </div>
        <a href="{{ route('products.web-create') }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-[#14652F] hover:bg-[#14532D] text-white text-[13px] font-semibold transition-colors">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
        </a>
    </div>

    <form method="GET" action="{{ route('products.web-index') }}" class="flex items-center gap-2">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <div class="flex items-center gap-2 flex-1 min-w-0 bg-white border border-[#ECECEA] rounded-lg px-3 h-[40px] focus-within:border-[#14652F]">
            <i data-lucide="search" class="w-4 h-4 shrink-0 text-[#8A857A]"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher dans mes produits…' : 'Search my products…' }}"
                   class="flex-1 min-w-0 bg-transparent text-[13px] focus:outline-none placeholder-[#8A857A]">
        </div>
        <button type="submit" class="px-4 h-[40px] rounded-lg border border-[#ECECEA] bg-white text-[13px] font-semibold text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
            {{ $isFr ? 'Filtrer' : 'Filter' }}
        </button>
    </form>

    @if($products->isEmpty())
    <div class="bg-white border border-[#ECECEA] rounded-xl text-center py-14 px-4">
        <i data-lucide="package" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="text-sm text-[#8A857A]">
            {{ $q !== '' || $status
               ? ($isFr ? 'Aucun produit ne correspond à ce filtre.' : 'No product matches this filter.')
               : ($isFr ? 'Votre boutique est vide. Ajoutez votre premier produit.' : 'Your shop is empty. Add your first product.') }}
        </p>
        <a href="{{ route('products.web-create') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-[#14652F] hover:text-[#14532D]">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
        </a>
    </div>
    @else
    <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
        @foreach($products as $p)
        @php
            $name = $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr);
            $published = $p->status === 'published';
        @endphp
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-4 py-3.5 border-b border-[#F0F1F0] last:border-0">

            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-12 h-12 rounded-lg bg-[#F5F1E9] border border-[#F0F1F0] shrink-0 overflow-hidden flex items-center justify-center">
                    @if($p->primaryImage)
                        <img src="{{ asset('storage/' . $p->primaryImage->file_path) }}" alt="" class="w-full h-full object-cover">
                    @else
                        <i data-lucide="image" class="w-4 h-4 text-[#CFC9BF]"></i>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" class="block text-[13.5px] font-semibold text-[#1B1B18] hover:text-[#14652F] truncate">
                        {{ $name }}
                    </a>
                    <p class="text-[11.5px] text-[#8A857A] truncate mt-0.5">
                        {{ $p->category ? ($isFr ? $p->category->name_fr : ($p->category->name_en ?? $p->category->name_fr)) : ($isFr ? 'Sans catégorie' : 'Uncategorised') }}
                        @if($p->price_amount)
                            · {{ number_format((float) $p->price_amount, 0, ',', ' ') }} FCFA
                        @endif
                        · {{ (int) ($p->views_count ?? 0) }} {{ $isFr ? 'vues' : 'views' }}
                    </p>
                </div>
                <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold
                             {{ $published ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FBF1DD] text-[#8A6D1F]' }}">
                    {{ $published ? ($isFr ? 'Publié' : 'Published') : ($isFr ? 'Brouillon' : 'Draft') }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0 sm:pl-2">
                @if($published)
                <a href="{{ route('products.show', $p->slug) }}" title="{{ $isFr ? 'Voir la fiche publique' : 'View public page' }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#ECECEA] text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </a>
                @endif
                <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#ECECEA] text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form method="POST" action="{{ route('products.web-toggle-status', ['slug' => $p->slug]) }}">
                    @csrf
                    <button type="submit" title="{{ $published ? ($isFr ? 'Retirer de la boutique' : 'Unpublish') : ($isFr ? 'Publier' : 'Publish') }}"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#ECECEA] text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
                        <i data-lucide="{{ $published ? 'eye-off' : 'send' }}" class="w-4 h-4"></i>
                    </button>
                </form>
                <form method="POST" action="{{ route('products.web-destroy', ['slug' => $p->slug]) }}"
                      onsubmit="return confirm('{{ $isFr ? 'Supprimer ce produit ? Cette action le retire de votre boutique.' : 'Delete this product? It will be removed from your shop.' }}')">
                    @csrf
                    <button type="submit" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#ECECEA] text-[#B42025] hover:border-[#B42025] hover:bg-[#FDE8E8] transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    @if($products->hasPages())
    <div class="pt-1">{{ $products->links() }}</div>
    @endif
    @endif
</div>
@endsection
