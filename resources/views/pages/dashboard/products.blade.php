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
        <div class="ui-alert ui-alert-ok">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Toolbar: search + status tabs + primary action --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach($tabs as $val => [$label, $count])
            <a href="{{ route('products.web-index', array_filter(['status' => $val ?: null, 'q' => $q ?: null])) }}"
               class="ui-btn ui-btn-sm {{ (string) ($status ?? '') === $val ? 'ui-btn-primary' : 'ui-btn-secondary' }}">
                {{ $label }}
                <span class="text-[11px] opacity-70">{{ $count }}</span>
            </a>
            @endforeach
        </div>
        <a href="{{ route('products.web-create') }}" class="ui-btn ui-btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
        </a>
    </div>

    <form method="GET" action="{{ route('products.web-index') }}" class="flex items-center gap-2">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <div class="ui-field-group flex-1 min-w-0">
            <i data-lucide="search" class="w-4 h-4 shrink-0"></i>
            <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher dans mes produits…' : 'Search my products…' }}"
                   class="ui-field-bare">
        </div>
        <button type="submit" class="ui-btn ui-btn-secondary">
            {{ $isFr ? 'Filtrer' : 'Filter' }}
        </button>
    </form>

    @if($products->isEmpty())
    <div class="ui-card text-center py-14 px-4">
        <i data-lucide="package" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="text-[12.5px] text-[#8A857A]">
            {{ $q !== '' || $status
               ? ($isFr ? 'Aucun produit ne correspond à ce filtre.' : 'No product matches this filter.')
               : ($isFr ? 'Votre boutique est vide. Ajoutez votre premier produit.' : 'Your shop is empty. Add your first product.') }}
        </p>
        <a href="{{ route('products.web-create') }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
            <i data-lucide="plus" class="w-4 h-4"></i>
            {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
        </a>
    </div>
    @else
    <div class="ui-card ui-card--flush">
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
                <span class="ui-pill shrink-0 {{ $published ? 'ui-pill-ok' : 'ui-pill-warn' }}">
                    {{ $published ? ($isFr ? 'Publié' : 'Published') : ($isFr ? 'Brouillon' : 'Draft') }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0 sm:pl-2">
                @if($published)
                <a href="{{ route('products.show', $p->slug) }}" title="{{ $isFr ? 'Voir la fiche publique' : 'View public page' }}"
                   class="ui-btn ui-btn-secondary ui-btn-sm px-0 w-8">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </a>
                @endif
                <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}"
                   class="ui-btn ui-btn-secondary ui-btn-sm px-0 w-8">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </a>
                <form method="POST" action="{{ route('products.web-toggle-status', ['slug' => $p->slug]) }}">
                    @csrf
                    <button type="submit" title="{{ $published ? ($isFr ? 'Retirer de la boutique' : 'Unpublish') : ($isFr ? 'Publier' : 'Publish') }}"
                            class="ui-btn ui-btn-secondary ui-btn-sm px-0 w-8">
                        <i data-lucide="{{ $published ? 'eye-off' : 'send' }}" class="w-4 h-4"></i>
                    </button>
                </form>
                <form method="POST" action="{{ route('products.web-destroy', ['slug' => $p->slug]) }}"
                      onsubmit="return confirm('{{ $isFr ? 'Supprimer ce produit ? Cette action le retire de votre boutique.' : 'Delete this product? It will be removed from your shop.' }}')">
                    @csrf
                    <button type="submit" title="{{ $isFr ? 'Supprimer' : 'Delete' }}"
                            class="ui-btn ui-btn-danger ui-btn-sm px-0 w-8">
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
