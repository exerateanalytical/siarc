@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'products';
    $pageTitle = $isFr ? 'Gestion des Produits & Services' : 'Products & Services Management';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Produits & Services' : 'Products & Services', null]];

    $statusMeta = [
        'published' => ['fr' => 'Publié',     'en' => 'Published', 'pill' => 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        'draft'     => ['fr' => 'En attente', 'en' => 'Pending',   'pill' => 'bg-[#FDF0DC] text-[#C97A16]'],
        'suspended' => ['fr' => 'Suspendu',   'en' => 'Suspended', 'pill' => 'bg-[#FDE8E8] dark:bg-[#3A1013] text-[#C0362C] dark:text-[#F0555C]'],
        'rejected'  => ['fr' => 'Rejeté',     'en' => 'Rejected',  'pill' => 'bg-[#F3F1EA] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'],
    ];
    $vendorMeta = [
        'artisan'     => ['fr' => 'Artisan',     'en' => 'Artisan'],
        'entreprise'  => ['fr' => 'Entreprise',  'en' => 'Business'],
        'cooperative' => ['fr' => 'Coopérative', 'en' => 'Cooperative'],
    ];
    $tabs = [
        ''          => ['fr' => 'Tous',       'en' => 'All',       'count' => $tabCounts['all']],
        'published' => ['fr' => 'Approuvés',  'en' => 'Approved',  'count' => $tabCounts['published']],
        'draft'     => ['fr' => 'En attente', 'en' => 'Pending',   'count' => $tabCounts['draft']],
        'suspended' => ['fr' => 'Suspendus',  'en' => 'Suspended', 'count' => $tabCounts['suspended']],
        'rejected'  => ['fr' => 'Rejetés',    'en' => 'Rejected',  'count' => $tabCounts['rejected']],
    ];
    $currentStatut = in_array(request('statut'), ['published', 'draft', 'suspended', 'rejected'], true) ? request('statut') : '';
    $donutPalette = ['#14652F', '#C97A16', '#0E7490', '#6D28D9', '#BE123C'];
@endphp

@section('content')
            {{-- Page header --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div></div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('products.web-create') }}" class="ui-btn ui-btn-primary">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="ui-btn ui-btn-secondary">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        {{ $isFr ? 'Exporter' : 'Export' }}
                    </a>
                </div>
            </div>

            {{-- STATISTIQUES PRODUITS chip row --}}
            <section class="mt-5">
                <p class="ui-eyebrow">{{ $isFr ? 'Statistiques produits' : 'Product statistics' }}</p>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-2.5">
                    @foreach([
                        ['icon' => 'package',      'tone' => 'text-[#14652F] dark:text-[#339B56] bg-[#E2F3E8] dark:bg-[#0C3D1D]', 'value' => $prodStats['total'],        'fr' => 'Total produits',   'en' => 'Total products'],
                        ['icon' => 'check-circle-2','tone' => 'text-[#157A43] dark:text-[#339B56] bg-[#E2F3E8] dark:bg-[#0C3D1D]', 'value' => $prodStats['published'],    'fr' => 'Approuvés',        'en' => 'Approved'],
                        ['icon' => 'clock',        'tone' => 'text-[#C97A16] dark:text-[#EDB33A] bg-[#FDF0DC] dark:bg-[#3A2B06]', 'value' => $prodStats['draft'],        'fr' => 'En attente',       'en' => 'Pending'],
                        ['icon' => 'pause-circle', 'tone' => 'text-[#C0362C] dark:text-[#F0555C] bg-[#FDE8E8] dark:bg-[#3A1013]', 'value' => $prodStats['suspended'],    'fr' => 'Suspendus',        'en' => 'Suspended'],
                        ['icon' => 'x-circle',     'tone' => 'text-[#6F6B60] dark:text-[#868778] bg-[#F3F1EA] dark:bg-[#1A1E16]', 'value' => $prodStats['rejected'],     'fr' => 'Rejetés',          'en' => 'Rejected'],
                        ['icon' => 'sparkles',     'tone' => 'text-[#0E7490] dark:text-[#868778] bg-[#E0F2FE] dark:bg-[#101C33]', 'value' => $prodStats['new_month'],    'fr' => 'Nouveaux ce mois', 'en' => 'New this month'],
                        ['icon' => 'alert-triangle','tone' => 'text-[#B45309] dark:text-[#EDB33A] bg-[#FEF3C7] dark:bg-[#3A2B06]','value' => $prodStats['out_of_stock'], 'fr' => 'En rupture',       'en' => 'Out of stock'],
                        ['icon' => 'eye',          'tone' => 'text-[#6D28D9] bg-[#EDE9FE]', 'value' => $prodStats['views'],        'fr' => 'Total vues',       'en' => 'Total views'],
                    ] as $chip)
                    <div class="ui-card flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $chip['tone'] }}">
                            <i data-lucide="{{ $chip['icon'] }}" class="w-4 h-4"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[15px] font-bold leading-tight text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($chip['value']) }}</p>
                            <p class="text-[12px] md:text-[10.5px] text-[#8A857A] dark:text-[#868778] truncate">{{ $isFr ? $chip['fr'] : $chip['en'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Status tabs --}}
            <div class="mt-5 flex flex-wrap items-center gap-1.5 border-b border-[#EAE5D8] dark:border-[#262B21]">
                @foreach($tabs as $val => $tab)
                <a href="{{ route('admin.products', array_filter(['lang' => $lang, 'statut' => $val, 'q' => request('q'), 'categorie' => request('categorie'), 'entreprise' => request('entreprise')], fn ($v) => $v !== null && $v !== '')) }}"
                   class="inline-flex items-center gap-1.5 min-h-[44px] md:min-h-0 px-3.5 py-2 -mb-px border-b-2 text-[14px] md:text-[12.5px] font-semibold transition-colors {{ $currentStatut === $val ? 'border-[#14652F] dark:border-[#2E9250] text-[#14652F] dark:text-[#339B56] ' : 'border-transparent text-[#6F6B60] dark:text-[#868778] hover:text-[#1B1B18] dark:hover:text-[#F3EFE7] ' }}">
                    {{ $isFr ? $tab['fr'] : $tab['en'] }}
                    <span class="rounded-full px-1.5 py-0.5 text-[12px] md:text-[10px] font-bold {{ $currentStatut === $val ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#F3F1EA] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778] ' }}">{{ number_format($tab['count']) }}</span>
                </a>
                @endforeach
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.products') }}" class="mt-4 flex flex-wrap items-center gap-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="ui-field-group flex-1 min-w-[190px] max-w-[320px]">
                    <i data-lucide="search" class="w-4 h-4 shrink-0 text-[#8A857A] dark:text-[#868778]"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Rechercher un produit...' : 'Search a product...' }}" class="ui-field-bare flex-1 min-w-0">
                </div>
                <select name="categorie" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Catégorie' : 'Category' }}</option>
                    @foreach($industriesList as $ind)
                    <option value="{{ $ind->slug }}" {{ request('categorie') === $ind->slug ? 'selected' : '' }}>{{ $isFr ? $ind->name_fr : ($ind->name_en ?? $ind->name_fr) }}</option>
                    @endforeach
                </select>
                <select name="entreprise" class="ui-field ui-select max-w-[220px]">
                    <option value="">{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</option>
                    @foreach($businessOptions as $b)
                    <option value="{{ $b->slug }}" {{ request('entreprise') === $b->slug ? 'selected' : '' }}>{{ $isFr ? $b->name_fr : ($b->name_en ?? $b->name_fr) }}</option>
                    @endforeach
                </select>
                <select name="statut" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                    @foreach($statusMeta as $val => $meta)
                    <option value="{{ $val }}" {{ $currentStatut === $val ? 'selected' : '' }}>{{ $isFr ? $meta['fr'] : $meta['en'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ui-btn ui-btn-primary">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    {{ $isFr ? 'Filtrer' : 'Filter' }}
                </button>
            </form>

            {{-- Products table --}}
            <section class="ui-card ui-card--flush mt-4">
                <div class="ui-table-wrap">
                    <table class="ui-table min-w-[980px]">
                        <thead>
                            <tr>
                                <th>{{ $isFr ? 'Produit' : 'Product' }}</th>
                                <th>{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</th>
                                <th>{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                <th class="text-right">{{ $isFr ? 'Prix (FCFA)' : 'Price (FCFA)' }}</th>
                                <th class="text-center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="text-right">Stock</th>
                                <th>{{ $isFr ? 'Créé le' : 'Created' }}</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminProducts as $p)
                            @php $meta = $statusMeta[$p->status] ?? null; @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($p->thumb_path)
                                        <img src="{{ asset('storage/' . $p->thumb_path) }}" alt="{{ $p->name_fr }}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-[#EFEBE2] dark:border-[#262B21]">
                                        @else
                                        <span class="w-10 h-10 rounded-lg bg-[#F3F1EA] dark:bg-[#1A1E16] flex items-center justify-center shrink-0 text-[#8A857A] dark:text-[#868778]">
                                            <i data-lucide="package" class="w-4 h-4"></i>
                                        </span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[15px] md:text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] truncate max-w-[220px]">{{ $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr) }}</p>
                                            <p class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778] truncate max-w-[220px]">{{ $p->sku ? '#' . $p->sku : $p->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-[14px] md:text-[12px] font-medium text-[#3B382F] dark:text-[#B4B5A6]">{{ $p->business_name_fr ? ($isFr ? $p->business_name_fr : ($p->business_name_en ?? $p->business_name_fr)) : '—' }}</p>
                                    @if($p->vendor_type && isset($vendorMeta[$p->vendor_type]))
                                    <p class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? $vendorMeta[$p->vendor_type]['fr'] : $vendorMeta[$p->vendor_type]['en'] }}</p>
                                    @endif
                                </td>
                                <td>{{ $p->industry_fr ? ($isFr ? $p->industry_fr : ($p->industry_en ?? $p->industry_fr)) : '—' }}</td>
                                <td class="text-right font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $p->price_amount !== null ? number_format($p->price_amount, 0) : '—' }}</td>
                                <td class="text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[12px] md:text-[10.5px] font-semibold {{ $meta['pill'] ?? 'bg-[#F3F1EA] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778] ' }}">{{ $meta ? ($isFr ? $meta['fr'] : $meta['en']) : $p->status }}</span>
                                </td>
                                <td class="text-right">{{ $p->quantity_available !== null ? number_format($p->quantity_available) : '—' }}</td>
                                <td class="whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($p->created_at)->locale($isFr ? 'fr' : 'en')->translatedFormat('d M Y') }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('products.show', ['slug' => $p->slug, 'lang' => $lang]) }}" class="p-2 rounded-lg hover:bg-[#E2F3E8] dark:hover:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]" title="{{ $isFr ? 'Voir' : 'View' }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" class="p-2 rounded-lg hover:bg-[#FDF0DC] dark:hover:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]" title="{{ $isFr ? 'Modifier' : 'Edit' }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <details class="row-menu relative inline-block">
                                            <summary class="p-2 rounded-lg hover:bg-[#F3F1EA] dark:hover:bg-[#242A1E] text-[#6F6B60] dark:text-[#868778] inline-flex" title="{{ $isFr ? 'Plus d\'actions' : 'More actions' }}">
                                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-1 w-52 bg-white dark:bg-[#12150F] border border-[#EFEBE2] dark:border-[#262B21] rounded-xl shadow-lg py-1.5 text-left">
                                                <a href="{{ route('products.show', ['slug' => $p->slug, 'lang' => $lang]) }}" class="flex items-center gap-2 min-h-[44px] md:min-h-0 px-3.5 py-2 text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6] hover:bg-[#F8F4EC] dark:hover:bg-[#242A1E]">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir la fiche publique' : 'View public page' }}
                                                </a>
                                                <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" class="flex items-center gap-2 min-h-[44px] md:min-h-0 px-3.5 py-2 text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6] hover:bg-[#F8F4EC] dark:hover:bg-[#242A1E]">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Modifier le produit' : 'Edit product' }}
                                                </a>
                                                @if($p->business_slug)
                                                <a href="{{ route('businesses.show', ['slug' => $p->business_slug, 'lang' => $lang]) }}" class="flex items-center gap-2 min-h-[44px] md:min-h-0 px-3.5 py-2 text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6] hover:bg-[#F8F4EC] dark:hover:bg-[#242A1E]">
                                                    <i data-lucide="store" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir l\'entreprise' : 'View business' }}
                                                </a>
                                                @endif
                                            </div>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="ui-empty">{{ $isFr ? 'Aucun produit trouvé.' : 'No products found.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-t border-[#F5F1E8] dark:border-[#262B21]">
                    <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">
                        {{ $isFr
                            ? 'Affichage de ' . number_format($adminProducts->count() ? $adminProducts->firstItem() : 0) . ' à ' . number_format($adminProducts->count() ? $adminProducts->lastItem() : 0) . ' sur ' . number_format($adminProducts->total()) . ' produits'
                            : 'Showing ' . number_format($adminProducts->count() ? $adminProducts->firstItem() : 0) . ' to ' . number_format($adminProducts->count() ? $adminProducts->lastItem() : 0) . ' of ' . number_format($adminProducts->total()) . ' products' }}
                    </p>
                    @if($adminProducts->hasPages())
                    @php
                        $cur = $adminProducts->currentPage();
                        $last = $adminProducts->lastPage();
                        $from = max(1, $cur - 2);
                        $to = min($last, $cur + 2);
                    @endphp
                    <nav class="flex items-center gap-1">
                        <a @if($adminProducts->onFirstPage()) aria-disabled="true" @else href="{{ $adminProducts->previousPageUrl() }}" @endif class="w-11 h-11 md:w-8 md:h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center {{ $adminProducts->onFirstPage() ? 'text-[#C9C3B4] dark:text-[#868778] cursor-default' : 'text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56] ' }}">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                        @if($from > 1)
                        <a href="{{ $adminProducts->url(1) }}" class="w-11 h-11 md:w-8 md:h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[14px] md:text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56]">1</a>
                        @if($from > 2)<span class="px-1 text-[14px] md:text-[12px] text-[#8A857A] dark:text-[#868778]">…</span>@endif
                        @endif
                        @foreach($adminProducts->getUrlRange($from, $to) as $page => $url)
                        <a href="{{ $url }}" class="w-11 h-11 md:w-8 md:h-8 rounded-lg flex items-center justify-center text-[14px] md:text-[12px] font-semibold {{ $page === $cur ? 'bg-[#0F4824] dark:bg-[#2E9250] text-white dark:text-[#04150A]' : 'border border-[#EAE5D8] dark:border-[#262B21] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56] ' }}">{{ $page }}</a>
                        @endforeach
                        @if($to < $last)
                        @if($to < $last - 1)<span class="px-1 text-[14px] md:text-[12px] text-[#8A857A] dark:text-[#868778]">…</span>@endif
                        <a href="{{ $adminProducts->url($last) }}" class="w-11 h-11 md:w-8 md:h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[14px] md:text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56]">{{ number_format($last) }}</a>
                        @endif
                        <a @if(!$adminProducts->hasMorePages()) aria-disabled="true" @else href="{{ $adminProducts->nextPageUrl() }}" @endif class="w-11 h-11 md:w-8 md:h-8 rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center {{ $adminProducts->hasMorePages() ? 'text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56] ' : 'text-[#C9C3B4] dark:text-[#868778] cursor-default' }}">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </nav>
                    @endif
                    <span class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778] bg-[#F8F4EC] dark:bg-[#1A1E16] border border-[#EFEBE2] dark:border-[#262B21] rounded-lg px-3 py-1.5">10 / page</span>
                </div>
            </section>

            {{-- Bottom cards --}}
            <div class="mt-5 grid lg:grid-cols-2 gap-4">
                {{-- Produits par catégorie (Top 5) --}}
                <section class="ui-card">
                    <div class="flex items-center justify-between">
                        <h2 class="ui-card-title">{{ $isFr ? 'Produits par catégorie (Top 5)' : 'Products by category (Top 5)' }}</h2>
                        <a href="{{ route('admin.industries', ['lang' => $lang]) }}" class="text-[13px] md:text-[11.5px] font-semibold text-[#14652F] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Voir tout →' : 'View all →' }}</a>
                    </div>
                    @php
                        $topTotal = $topCategories->sum('cnt');
                        $othersCnt = max(0, $prodStats['total'] - $topTotal);
                        $othersPct = $prodStats['total'] > 0 ? round($othersCnt * 100 / $prodStats['total'], 1) : 0;
                        $stops = [];
                        $acc = 0.0;
                        foreach ($topCategories as $i => $c) {
                            $stops[] = ($donutPalette[$i] ?? '#A8A29E') . ' ' . $acc . '% ' . ($acc + $c->pct) . '%';
                            $acc += $c->pct;
                        }
                        $stops[] = '#D6D3D1 ' . $acc . '% 100%';
                    @endphp
                    @if($topCategories->isEmpty())
                    <p class="mt-4 text-[14px] md:text-[12.5px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Aucun produit.' : 'No products.' }}</p>
                    @else
                    <div class="mt-4 flex flex-col sm:flex-row items-center gap-5">
                        <div class="relative w-[132px] h-[132px] shrink-0 rounded-full" style="background: conic-gradient({{ implode(', ', $stops) }});">
                            <div class="absolute inset-[18px] bg-white dark:bg-[#12150F] rounded-full flex flex-col items-center justify-center">
                                <p class="text-[16px] font-bold leading-tight text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($prodStats['total']) }}</p>
                                <p class="text-[12px] md:text-[10px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Produits' : 'Products' }}</p>
                            </div>
                        </div>
                        <ul class="flex-1 w-full space-y-2">
                            @foreach($topCategories as $i => $c)
                            <li class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background: {{ $donutPalette[$i] ?? '#A8A29E' }};"></span>
                                <span class="flex-1 min-w-0 text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6] truncate">{{ $c->name_fr ? ($isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr)) : ($isFr ? 'Sans catégorie' : 'Uncategorized') }}</span>
                                <span class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ number_format($c->cnt) }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $c->pct }}%)</span></span>
                            </li>
                            @endforeach
                            @if($othersCnt > 0)
                            <li class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-sm shrink-0 bg-[#D6D3D1] dark:bg-[#1A1E16]"></span>
                                <span class="flex-1 min-w-0 text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6] truncate">{{ $isFr ? 'Autres catégories' : 'Other categories' }}</span>
                                <span class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ number_format($othersCnt) }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $othersPct }}%)</span></span>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </section>

                {{-- Gamme de prix --}}
                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Gamme de prix' : 'Price range' }}</h2>
                    @php $pricedTotal = collect($priceRanges)->sum('cnt'); @endphp
                    @if($pricedTotal === 0)
                    <p class="mt-4 text-[14px] md:text-[12.5px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Aucun produit avec un prix renseigné.' : 'No products with a price set.' }}</p>
                    @else
                    <p class="mt-0.5 text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Répartition des ' . number_format($pricedTotal) . ' produits avec prix renseigné.' : 'Distribution of the ' . number_format($pricedTotal) . ' products with a price set.' }}</p>
                    <ul class="mt-4 space-y-3.5">
                        @foreach($priceRanges as $range)
                        <li>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? $range['fr'] : $range['en'] }}</span>
                                <span class="text-[14px] md:text-[12px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ number_format($range['cnt']) }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ $range['pct'] }}%)</span></span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-[#F3F1EA] dark:bg-[#1A1E16] overflow-hidden">
                                <div class="h-full rounded-full bg-[#14652F] dark:bg-[#2E9250]" style="width: {{ min(100, $range['pct']) }}%;"></div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </section>
            </div>

<style>
    details.row-menu > summary { list-style: none; cursor: pointer; }
    details.row-menu > summary::-webkit-details-marker { display: none; }
</style>
<script>
    // Close open row menus when clicking elsewhere.
    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.row-menu[open]').forEach(function (d) {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });
</script>
@endsection
