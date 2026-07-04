@php
    $isFr = $lang === 'fr';
    $adminActive = 'products';

    $statusMeta = [
        'published' => ['fr' => 'Publié',     'en' => 'Published', 'pill' => 'bg-[#E2F3E8] text-[#157A43]'],
        'draft'     => ['fr' => 'En attente', 'en' => 'Pending',   'pill' => 'bg-[#FDF0DC] text-[#C97A16]'],
        'suspended' => ['fr' => 'Suspendu',   'en' => 'Suspended', 'pill' => 'bg-[#FDE8E8] text-[#C0362C]'],
        'rejected'  => ['fr' => 'Rejeté',     'en' => 'Rejected',  'pill' => 'bg-[#F3F1EA] text-[#6F6B60]'],
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
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Gestion des Produits & Services — Administration' : 'Products & Services Management — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
        details.row-menu > summary { list-style: none; cursor: pointer; }
        details.row-menu > summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')

        <main class="px-5 lg:px-7 pb-8">
            {{-- Page header --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Gestion des Produits & Services' : 'Products & Services Management' }}</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">
                        <a href="{{ route('dashboard.admin', ['lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                        <span class="mx-1 text-[#C9C3B4]">›</span>
                        <span class="text-[#3B382F]">{{ $isFr ? 'Produits & Services' : 'Products & Services' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('products.web-create') }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Ajouter un produit' : 'Add a product' }}
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="inline-flex items-center gap-2 bg-white border border-[#E9E4D8] hover:border-[#14652F] hover:text-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#3B382F] transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        {{ $isFr ? 'Exporter' : 'Export' }}
                    </a>
                </div>
            </div>

            {{-- STATISTIQUES PRODUITS chip row --}}
            <section class="mt-5">
                <p class="text-[11px] font-bold tracking-[0.08em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statistiques produits' : 'Product statistics' }}</p>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-2.5">
                    @foreach([
                        ['icon' => 'package',      'tone' => 'text-[#14652F] bg-[#E2F3E8]', 'value' => $prodStats['total'],        'fr' => 'Total produits',   'en' => 'Total products'],
                        ['icon' => 'check-circle-2','tone' => 'text-[#157A43] bg-[#E2F3E8]', 'value' => $prodStats['published'],    'fr' => 'Approuvés',        'en' => 'Approved'],
                        ['icon' => 'clock',        'tone' => 'text-[#C97A16] bg-[#FDF0DC]', 'value' => $prodStats['draft'],        'fr' => 'En attente',       'en' => 'Pending'],
                        ['icon' => 'pause-circle', 'tone' => 'text-[#C0362C] bg-[#FDE8E8]', 'value' => $prodStats['suspended'],    'fr' => 'Suspendus',        'en' => 'Suspended'],
                        ['icon' => 'x-circle',     'tone' => 'text-[#6F6B60] bg-[#F3F1EA]', 'value' => $prodStats['rejected'],     'fr' => 'Rejetés',          'en' => 'Rejected'],
                        ['icon' => 'sparkles',     'tone' => 'text-[#0E7490] bg-[#E0F2FE]', 'value' => $prodStats['new_month'],    'fr' => 'Nouveaux ce mois', 'en' => 'New this month'],
                        ['icon' => 'alert-triangle','tone' => 'text-[#B45309] bg-[#FEF3C7]','value' => $prodStats['out_of_stock'], 'fr' => 'En rupture',       'en' => 'Out of stock'],
                        ['icon' => 'eye',          'tone' => 'text-[#6D28D9] bg-[#EDE9FE]', 'value' => $prodStats['views'],        'fr' => 'Total vues',       'en' => 'Total views'],
                    ] as $chip)
                    <div class="bg-white border border-[#EFEBE2] rounded-xl px-3 py-2.5 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $chip['tone'] }}">
                            <i data-lucide="{{ $chip['icon'] }}" class="w-4 h-4"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[15px] font-bold leading-tight text-[#1B1B18]">{{ number_format($chip['value']) }}</p>
                            <p class="text-[10.5px] text-[#8A857A] truncate">{{ $isFr ? $chip['fr'] : $chip['en'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Status tabs --}}
            <div class="mt-5 flex flex-wrap items-center gap-1.5 border-b border-[#E9E4D8]">
                @foreach($tabs as $val => $tab)
                <a href="{{ route('admin.products', array_filter(['lang' => $lang, 'statut' => $val, 'q' => request('q'), 'categorie' => request('categorie'), 'entreprise' => request('entreprise')], fn ($v) => $v !== null && $v !== '')) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 -mb-px border-b-2 text-[12.5px] font-semibold transition-colors {{ $currentStatut === $val ? 'border-[#14652F] text-[#14652F]' : 'border-transparent text-[#6F6B60] hover:text-[#1B1B18]' }}">
                    {{ $isFr ? $tab['fr'] : $tab['en'] }}
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $currentStatut === $val ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#F3F1EA] text-[#6F6B60]' }}">{{ number_format($tab['count']) }}</span>
                </a>
                @endforeach
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.products') }}" class="mt-4 flex flex-wrap items-center gap-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex items-center gap-2 bg-white border border-[#E9E4D8] rounded-lg px-3 h-[38px] flex-1 min-w-[190px] max-w-[320px]">
                    <i data-lucide="search" class="w-4 h-4 shrink-0 text-[#8A857A]"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Rechercher un produit...' : 'Search a product...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A857A]">
                </div>
                <select name="categorie" class="bg-white border border-[#E9E4D8] rounded-lg px-3 h-[38px] text-[12.5px] text-[#3B382F] focus:outline-none">
                    <option value="">{{ $isFr ? 'Catégorie' : 'Category' }}</option>
                    @foreach($industriesList as $ind)
                    <option value="{{ $ind->slug }}" {{ request('categorie') === $ind->slug ? 'selected' : '' }}>{{ $isFr ? $ind->name_fr : ($ind->name_en ?? $ind->name_fr) }}</option>
                    @endforeach
                </select>
                <select name="entreprise" class="bg-white border border-[#E9E4D8] rounded-lg px-3 h-[38px] text-[12.5px] text-[#3B382F] focus:outline-none max-w-[220px]">
                    <option value="">{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</option>
                    @foreach($businessOptions as $b)
                    <option value="{{ $b->slug }}" {{ request('entreprise') === $b->slug ? 'selected' : '' }}>{{ $isFr ? $b->name_fr : ($b->name_en ?? $b->name_fr) }}</option>
                    @endforeach
                </select>
                <select name="statut" class="bg-white border border-[#E9E4D8] rounded-lg px-3 h-[38px] text-[12.5px] text-[#3B382F] focus:outline-none">
                    <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                    @foreach($statusMeta as $val => $meta)
                    <option value="{{ $val }}" {{ $currentStatut === $val ? 'selected' : '' }}>{{ $isFr ? $meta['fr'] : $meta['en'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    {{ $isFr ? 'Filtrer' : 'Filter' }}
                </button>
            </form>

            {{-- Products table --}}
            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px]">
                        <thead>
                            <tr class="bg-[#F8F4EC] text-left">
                                <th class="pl-5 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Artisan / Entreprise' : 'Artisan / Business' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Prix (FCFA)' : 'Price (FCFA)' }}</th>
                                <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Stock</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Créé le' : 'Created' }}</th>
                                <th class="px-2 pr-5 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5F1E8]">
                            @forelse($adminProducts as $p)
                            @php $meta = $statusMeta[$p->status] ?? null; @endphp
                            <tr>
                                <td class="pl-5 pr-2 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($p->thumb_path)
                                        <img src="{{ asset('storage/' . $p->thumb_path) }}" alt="{{ $p->name_fr }}" class="w-10 h-10 rounded-lg object-cover shrink-0 border border-[#EFEBE2]">
                                        @else
                                        <span class="w-10 h-10 rounded-lg bg-[#F3F1EA] flex items-center justify-center shrink-0 text-[#8A857A]">
                                            <i data-lucide="package" class="w-4 h-4"></i>
                                        </span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[12.5px] font-bold text-[#1B1B18] truncate max-w-[220px]">{{ $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr) }}</p>
                                            <p class="text-[11px] text-[#8A857A] truncate max-w-[220px]">{{ $p->sku ? '#' . $p->sku : $p->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-3">
                                    <p class="text-[12px] font-medium text-[#3B382F]">{{ $p->business_name_fr ? ($isFr ? $p->business_name_fr : ($p->business_name_en ?? $p->business_name_fr)) : '—' }}</p>
                                    @if($p->vendor_type && isset($vendorMeta[$p->vendor_type]))
                                    <p class="text-[11px] text-[#8A857A]">{{ $isFr ? $vendorMeta[$p->vendor_type]['fr'] : $vendorMeta[$p->vendor_type]['en'] }}</p>
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-[12px] text-[#3B382F]">{{ $p->industry_fr ? ($isFr ? $p->industry_fr : ($p->industry_en ?? $p->industry_fr)) : '—' }}</td>
                                <td class="px-2 py-3 text-right text-[12px] font-semibold text-[#1B1B18]">{{ $p->price_amount !== null ? number_format($p->price_amount, 0) : '—' }}</td>
                                <td class="px-2 py-3 text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold {{ $meta['pill'] ?? 'bg-[#F3F1EA] text-[#6F6B60]' }}">{{ $meta ? ($isFr ? $meta['fr'] : $meta['en']) : $p->status }}</span>
                                </td>
                                <td class="px-2 py-3 text-right text-[12px] text-[#3B382F]">{{ $p->quantity_available !== null ? number_format($p->quantity_available) : '—' }}</td>
                                <td class="px-2 py-3 text-[12px] text-[#3B382F] whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($p->created_at)->locale($isFr ? 'fr' : 'en')->translatedFormat('d M Y') }}</td>
                                <td class="px-2 pr-5 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('products.show', ['slug' => $p->slug, 'lang' => $lang]) }}" class="p-2 rounded-lg hover:bg-[#E2F3E8] text-[#157A43]" title="{{ $isFr ? 'Voir' : 'View' }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" class="p-2 rounded-lg hover:bg-[#FDF0DC] text-[#C97A16]" title="{{ $isFr ? 'Modifier' : 'Edit' }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <details class="row-menu relative inline-block">
                                            <summary class="p-2 rounded-lg hover:bg-[#F3F1EA] text-[#6F6B60] inline-flex" title="{{ $isFr ? 'Plus d\'actions' : 'More actions' }}">
                                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                            </summary>
                                            <div class="absolute right-0 z-20 mt-1 w-52 bg-white border border-[#EFEBE2] rounded-xl shadow-lg py-1.5 text-left">
                                                <a href="{{ route('products.show', ['slug' => $p->slug, 'lang' => $lang]) }}" class="flex items-center gap-2 px-3.5 py-2 text-[12px] text-[#3B382F] hover:bg-[#F8F4EC]">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir la fiche publique' : 'View public page' }}
                                                </a>
                                                <a href="{{ route('products.web-edit', ['slug' => $p->slug]) }}" class="flex items-center gap-2 px-3.5 py-2 text-[12px] text-[#3B382F] hover:bg-[#F8F4EC]">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Modifier le produit' : 'Edit product' }}
                                                </a>
                                                @if($p->business_slug)
                                                <a href="{{ route('businesses.show', ['slug' => $p->business_slug, 'lang' => $lang]) }}" class="flex items-center gap-2 px-3.5 py-2 text-[12px] text-[#3B382F] hover:bg-[#F8F4EC]">
                                                    <i data-lucide="store" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir l\'entreprise' : 'View business' }}
                                                </a>
                                                @endif
                                            </div>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun produit trouvé.' : 'No products found.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-t border-[#F5F1E8]">
                    <p class="text-[12px] text-[#6F6B60]">
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
                        <a @if($adminProducts->onFirstPage()) aria-disabled="true" @else href="{{ $adminProducts->previousPageUrl() }}" @endif class="w-8 h-8 rounded-lg border border-[#E9E4D8] flex items-center justify-center {{ $adminProducts->onFirstPage() ? 'text-[#C9C3B4] cursor-default' : 'text-[#3B382F] hover:border-[#14652F] hover:text-[#14652F]' }}">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                        @if($from > 1)
                        <a href="{{ $adminProducts->url(1) }}" class="w-8 h-8 rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[12px] font-semibold text-[#3B382F] hover:border-[#14652F] hover:text-[#14652F]">1</a>
                        @if($from > 2)<span class="px-1 text-[12px] text-[#8A857A]">…</span>@endif
                        @endif
                        @foreach($adminProducts->getUrlRange($from, $to) as $page => $url)
                        <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-[12px] font-semibold {{ $page === $cur ? 'bg-[#0F4824] text-white' : 'border border-[#E9E4D8] text-[#3B382F] hover:border-[#14652F] hover:text-[#14652F]' }}">{{ $page }}</a>
                        @endforeach
                        @if($to < $last)
                        @if($to < $last - 1)<span class="px-1 text-[12px] text-[#8A857A]">…</span>@endif
                        <a href="{{ $adminProducts->url($last) }}" class="w-8 h-8 rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[12px] font-semibold text-[#3B382F] hover:border-[#14652F] hover:text-[#14652F]">{{ number_format($last) }}</a>
                        @endif
                        <a @if(!$adminProducts->hasMorePages()) aria-disabled="true" @else href="{{ $adminProducts->nextPageUrl() }}" @endif class="w-8 h-8 rounded-lg border border-[#E9E4D8] flex items-center justify-center {{ $adminProducts->hasMorePages() ? 'text-[#3B382F] hover:border-[#14652F] hover:text-[#14652F]' : 'text-[#C9C3B4] cursor-default' }}">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </nav>
                    @endif
                    <span class="text-[12px] text-[#6F6B60] bg-[#F8F4EC] border border-[#EFEBE2] rounded-lg px-3 py-1.5">10 / page</span>
                </div>
            </section>

            {{-- Bottom cards --}}
            <div class="mt-5 grid lg:grid-cols-2 gap-4">
                {{-- Produits par catégorie (Top 5) --}}
                <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Produits par catégorie (Top 5)' : 'Products by category (Top 5)' }}</h2>
                        <a href="{{ route('admin.industries', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#14652F] hover:underline">{{ $isFr ? 'Voir tout →' : 'View all →' }}</a>
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
                    <p class="mt-4 text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun produit.' : 'No products.' }}</p>
                    @else
                    <div class="mt-4 flex flex-col sm:flex-row items-center gap-5">
                        <div class="relative w-[132px] h-[132px] shrink-0 rounded-full" style="background: conic-gradient({{ implode(', ', $stops) }});">
                            <div class="absolute inset-[18px] bg-white rounded-full flex flex-col items-center justify-center">
                                <p class="text-[16px] font-bold leading-tight text-[#1B1B18]">{{ number_format($prodStats['total']) }}</p>
                                <p class="text-[10px] text-[#8A857A]">{{ $isFr ? 'Produits' : 'Products' }}</p>
                            </div>
                        </div>
                        <ul class="flex-1 w-full space-y-2">
                            @foreach($topCategories as $i => $c)
                            <li class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-sm shrink-0" style="background: {{ $donutPalette[$i] ?? '#A8A29E' }};"></span>
                                <span class="flex-1 min-w-0 text-[12px] text-[#3B382F] truncate">{{ $c->name_fr ? ($isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr)) : ($isFr ? 'Sans catégorie' : 'Uncategorized') }}</span>
                                <span class="text-[12px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ number_format($c->cnt) }} <span class="font-normal text-[#8A857A]">({{ $c->pct }}%)</span></span>
                            </li>
                            @endforeach
                            @if($othersCnt > 0)
                            <li class="flex items-center gap-2.5">
                                <span class="w-2.5 h-2.5 rounded-sm shrink-0 bg-[#D6D3D1]"></span>
                                <span class="flex-1 min-w-0 text-[12px] text-[#3B382F] truncate">{{ $isFr ? 'Autres catégories' : 'Other categories' }}</span>
                                <span class="text-[12px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ number_format($othersCnt) }} <span class="font-normal text-[#8A857A]">({{ $othersPct }}%)</span></span>
                            </li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </section>

                {{-- Gamme de prix --}}
                <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                    <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Gamme de prix' : 'Price range' }}</h2>
                    @php $pricedTotal = collect($priceRanges)->sum('cnt'); @endphp
                    @if($pricedTotal === 0)
                    <p class="mt-4 text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun produit avec un prix renseigné.' : 'No products with a price set.' }}</p>
                    @else
                    <p class="mt-0.5 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Répartition des ' . number_format($pricedTotal) . ' produits avec prix renseigné.' : 'Distribution of the ' . number_format($pricedTotal) . ' products with a price set.' }}</p>
                    <ul class="mt-4 space-y-3.5">
                        @foreach($priceRanges as $range)
                        <li>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[12px] text-[#3B382F]">{{ $isFr ? $range['fr'] : $range['en'] }}</span>
                                <span class="text-[12px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ number_format($range['cnt']) }} <span class="font-normal text-[#8A857A]">({{ $range['pct'] }}%)</span></span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-[#F3F1EA] overflow-hidden">
                                <div class="h-full rounded-full bg-[#14652F]" style="width: {{ min(100, $range['pct']) }}%;"></div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </section>
            </div>
        </main>
    </div>
</div>
<script>
    lucide.createIcons();
    // Close open row menus when clicking elsewhere.
    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.row-menu[open]').forEach(function (d) {
            if (!d.contains(e.target)) d.removeAttribute('open');
        });
    });
</script>
</body>
</html>
