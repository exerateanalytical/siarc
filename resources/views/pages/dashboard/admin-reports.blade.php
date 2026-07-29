@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'payments';
    $pageTitle = $isFr ? 'Rapports & Statistiques' : 'Reports & Statistics';

    $funnelLabels = [
        'submitted'    => $isFr ? 'Soumises' : 'Submitted',
        'under_review' => $isFr ? 'En cours' : 'Under review',
        'approved'     => $isFr ? 'Approuvées' : 'Approved',
        'rejected'     => $isFr ? 'Rejetées' : 'Rejected',
    ];
    $funnelColors = ['submitted' => '#0F4824', 'under_review' => '#C97A16', 'approved' => '#157A43', 'rejected' => '#DC2626'];
    $funnelMax = max(1, ...array_map(fn ($k) => $verificationFunnel[$k] ?? 0, array_keys($funnelLabels)));

    $fmtFcfa = fn ($v) => $v === null ? '—' : number_format($v, 0, ',', ' ') . ' FCFA';

    // 5 KPI cards [icon, color, tile, value, label, sub, spark]
    // No revenue tile: it summed business_subscriptions, a table with no write path.
    $repCards = [
        ['shopping-cart',  '#3565DE', '#E8EFFB', number_format($repKpis['orders']), $isFr ? 'Commandes Totales' : 'Total Orders', $isFr ? 'bons de commande' : 'purchase orders', '#3565DE'],
        ['users-round',    '#7C4FE0', '#F0EAFB', number_format($repKpis['artisans']), $isFr ? 'Artisans Actifs' : 'Active Artisans', $isFr ? 'entreprises publiées' : 'published businesses', '#7C4FE0'],
        ['eye',            '#C97A16', '#FDF3E0', number_format($repKpis['views']), $isFr ? 'Vues Totales' : 'Total Views', $isFr ? 'produits + entreprises' : 'products + businesses', '#E9A83A'],
        ['filter',         '#0E7C86', '#E3F4F6', $repKpis['conversion'] !== null ? $repKpis['conversion'] . '%' : '—', $isFr ? 'Taux de Conversion' : 'Conversion Rate', $isFr ? 'commandes / demandes' : 'orders / requests', '#0E7C86'],
        ['shopping-bag',   '#8A5A1F', '#F5EEDD', $repKpis['avg_order'] !== null ? $fmtFcfa($repKpis['avg_order']) : '—', $isFr ? 'Panier Moyen' : 'Average Order', $isFr ? 'par commande' : 'per order', '#B98A3D'],
    ];

    // Category donut (real $repCategoryDist)
    $catTotal = max(1, $repCategoryDist->sum('total'));
    $catColors = ['#157A43', '#3565DE', '#C97A16', '#7C4FE0', '#DC2626', '#8A857A'];
    $deg = 0; $catStops = [];
    foreach ($repCategoryDist as $i => $row) {
        $pct = $row->total / $catTotal * 100;
        $start = $deg; $deg += $pct / 100 * 360;
        $catStops[] = ($catColors[$i % count($catColors)]) . " {$start}deg {$deg}deg";
    }
    $catConic = 'conic-gradient(' . implode(', ', $catStops) . ')';

    $regionMax = max(1, (int) $topRegions->max('total'));
@endphp

@section('content')
<div class="max-w-[1400px]">

    {{-- Breadcrumb + top actions (project adaptation: layouts.admin owns the real topbar) --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="flex items-center gap-1.5 text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">
            <a href="{{ route('dashboard.admin') }}" class="hover:text-[#14652F] dark:hover:text-[#339B56]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $pageTitle }}</span>
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.exports') }}" class="ui-btn ui-btn-primary"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter ce rapport' : 'Export this report' }}</a>
            <a href="{{ route('admin.exports') }}" class="ui-btn ui-btn-secondary"><i data-lucide="calendar-clock" class="w-4 h-4"></i>{{ $isFr ? 'Planifier un rapport' : 'Schedule a report' }}</a>
        </div>
    </div>

    {{-- Tab bar (static — first tab is the built overview) --}}
    <div class="flex flex-wrap gap-1 border-b border-[#EAE5D8] dark:border-[#262B21] mb-5 text-[14px] md:text-[12.5px] font-semibold">
        @foreach(($isFr ? ['Vue d\'ensemble','Ventes & Revenus','Utilisateurs','Produits','Géographie','Performances','Exportations','Personnalisés'] : ['Overview','Sales & Revenue','Users','Products','Geography','Performance','Exports','Custom']) as $i => $tab)
        <span class="px-3.5 py-2.5 -mb-px border-b-2 {{ $i === 0 ? 'border-[#157A43] dark:border-[#2E9250] text-[#157A43] dark:text-[#339B56] ' : 'border-transparent text-[#8A857A] dark:text-[#868778] ' }}">{{ $tab }}</span>
        @endforeach
    </div>

    {{-- 5 KPI cards --}}
    <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        @foreach($repCards as [$rcIcon, $rcColor, $rcTile, $rcValue, $rcLabel, $rcSub, $rcSpark])
        <div class="ui-card flex flex-col">
            <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $rcTile }}">
                <i data-lucide="{{ $rcIcon }}" class="w-[19px] h-[19px]" style="color: {{ $rcColor }};stroke-width:1.8"></i>
            </span>
            <p class="mt-3 text-[17px] font-extrabold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight">{{ $rcValue }}</p>
            <p class="mt-1 text-[13px] md:text-[11.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6] uppercase tracking-wide">{{ $rcLabel }}</p>
            <p class="mt-0.5 text-[12px] md:text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $rcSub }}</p>
            <svg viewBox="0 0 120 24" class="mt-2 w-full h-[22px]" preserveAspectRatio="none" aria-hidden="true">
                <polyline points="0,18 15,14 30,16 45,8 60,12 75,6 90,10 105,4 120,7" fill="none" stroke="{{ $rcSpark }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
            </svg>
        </div>
        @endforeach
    </section>

    {{-- The revenue-evolution chart lived here; it plotted business_subscriptions
         only, so it was a permanently flat line. --}}
    <div class="mt-5 grid grid-cols-1 xl:grid-cols-2 gap-5 items-start">
        {{-- Category breakdown (real business count by industry) --}}
        <div class="ui-card">
            <h3 class="ui-card-title mb-3">{{ $isFr ? 'RÉPARTITION DES ENTREPRISES PAR CATÉGORIE' : 'BUSINESSES BY CATEGORY' }}</h3>
            @if($repCategoryDist->isNotEmpty())
            <div class="flex items-center gap-4">
                <span class="w-[84px] h-[84px] rounded-full shrink-0" style="background: {{ $catConic }}"><span class="block w-[46px] h-[46px] rounded-full bg-white dark:bg-[#12150F] m-[19px]"></span></span>
                <ul class="space-y-1.5 min-w-0 text-[13px] md:text-[11.5px]">
                    @foreach($repCategoryDist as $i => $row)
                    <li class="flex items-center gap-2 text-[#3B382F] dark:text-[#B4B5A6]">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $catColors[$i % count($catColors)] }}"></span>
                        <span class="flex-1 truncate">{{ $isFr ? $row->name_fr : $row->name_fr }}</span>
                        <span class="font-semibold shrink-0">{{ round($row->total / $catTotal * 100, 1) }}% ({{ $row->total }})</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @else
            <div class="h-[100px] flex items-center justify-center text-[14px] md:text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</div>
            @endif
        </div>

        {{-- Region performance --}}
        <div class="ui-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="ui-card-title">{{ $isFr ? 'PERFORMANCES DES RÉGIONS' : 'REGION PERFORMANCE' }}</h3>
                <a href="{{ route('admin.regions', ['lang' => $lang]) }}" class="text-[13px] md:text-[11.5px] font-semibold text-[#157A43] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Voir le rapport' : 'View report' }}</a>
            </div>
            <div class="space-y-2.5">
                @forelse($topRegions as $row)
                <div>
                    <div class="flex items-center justify-between text-[14px] md:text-[12px] mb-1">
                        <span class="text-[#3B382F] dark:text-[#B4B5A6] truncate">{{ $isFr ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span>
                        <span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] shrink-0">{{ $row->total }} {{ $isFr ? 'entreprises' : 'businesses' }}</span>
                    </div>
                    <div class="h-[6px] bg-[#F1F1EF] dark:bg-[#1A1E16] rounded-full overflow-hidden"><div class="h-full bg-[#157A43] dark:bg-[#2E9250] rounded-full" style="width: {{ round($row->total / $regionMax * 100, 1) }}%"></div></div>
                </div>
                @empty
                <p class="text-[14px] md:text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 xl:grid-cols-3 gap-5 items-start">
        {{-- Top products by views (honest relabel of "top artisans by revenue" — no per-business sales are tracked yet) --}}
        <div class="ui-card ui-card--flush xl:col-span-1">
            <div class="px-5 pt-4 pb-3 flex items-center justify-between">
                <h3 class="ui-card-title">{{ $isFr ? 'TOP PRODUITS PAR VUES' : 'TOP PRODUCTS BY VIEWS' }}</h3>
                <a href="{{ route('admin.products') }}" class="text-[13px] md:text-[11.5px] font-semibold text-[#157A43] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
            </div>
            {{-- Scrolls inside its own box so a narrow screen never scrolls the page. --}}
            <div class="ui-table-wrap">
            <table class="ui-table min-w-[520px]">
                <thead><tr><th>#</th><th>{{ $isFr ? 'PRODUIT' : 'PRODUCT' }}</th><th>{{ $isFr ? 'RÉGION' : 'REGION' }}</th><th class="text-right">{{ $isFr ? 'VUES' : 'VIEWS' }}</th></tr></thead>
                <tbody>
                    @forelse($topProducts as $i => $product)
                    <tr class="border-t border-[#EFEBE2] dark:border-[#262B21]">
                        <td class="text-[#8A857A] dark:text-[#868778]">{{ $i + 1 }}</td>
                        <td class="font-medium text-[#1B1B18] dark:text-[#F3EFE7] truncate max-w-[140px]">{{ $product->name_fr }}</td>
                        <td class="truncate max-w-[100px]">{{ $product->business?->region?->name_fr ?? '—' }}</td>
                        <td class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7] text-right">{{ number_format($product->views_count) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8 text-[14px] md:text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{-- Map + region list (decorative Cameroon map — no per-region shading data exists) --}}
        <div class="ui-card">
            <div class="flex items-center justify-between mb-3">
                <h3 class="ui-card-title">{{ $isFr ? 'ENTREPRISES PAR RÉGION' : 'BUSINESSES BY REGION' }}</h3>
                <a href="{{ route('admin.regions', ['lang' => $lang]) }}" class="text-[13px] md:text-[11.5px] font-semibold text-[#157A43] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Voir le rapport' : 'View report' }}</a>
            </div>
            <img src="{{ asset('images/landing/rg-map.png') }}" alt="{{ $isFr ? 'Carte du Cameroun' : 'Map of Cameroon' }}" class="w-full h-auto max-h-[130px] object-contain mx-auto mb-2">
            <ul class="space-y-1 text-[13px] md:text-[11.5px]">
                @foreach($topRegions->take(5) as $row)
                <li class="flex items-center justify-between text-[#3B382F] dark:text-[#B4B5A6]"><span class="truncate">{{ $isFr ? $row->name_fr : ($row->name_en ?? $row->name_fr) }}</span><span class="font-semibold shrink-0">{{ $row->total }}</span></li>
                @endforeach
            </ul>
        </div>

        {{-- Verification funnel (real) --}}
        <div class="ui-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="ui-card-title">{{ $isFr ? 'ENTONNOIR DE VÉRIFICATION' : 'VERIFICATION FUNNEL' }}</h3>
            </div>
            <div class="space-y-2">
                @foreach($funnelLabels as $key => $label)
                    @php $v = $verificationFunnel[$key] ?? 0; $w = max(8, round($v / $funnelMax * 100)); @endphp
                <div>
                    <div class="flex items-center justify-between text-[13px] md:text-[11.5px] mb-1"><span class="text-[#3B382F] dark:text-[#B4B5A6]">{{ $label }}</span><span class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $v }}</span></div>
                    <div class="h-[16px] rounded-md" style="width: {{ $w }}%; background-color: {{ $funnelColors[$key] }}"></div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Traffic/device/payment analytics — honest empty-states: no page-analytics tracking exists yet --}}
    <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-5 xl:grid-cols-4">
        @foreach(($isFr ? ['Sources de Trafic','Appareils Utilisés','Méthodes de Paiement'] : ['Traffic Sources','Devices Used','Payment Methods']) as $panel)
        <div class="ui-card flex flex-col items-center text-center">
            <h3 class="ui-card-title mb-4 self-start">{{ strtoupper($panel) }}</h3>
            <div class="w-[72px] h-[72px] rounded-full border-[10px] border-[#EFEBE2] dark:border-[#262B21] mb-3"></div>
            <p class="text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778] leading-relaxed">{{ $isFr ? 'Suivi non disponible — nécessite l\'intégration d\'un outil d\'analyse de trafic.' : 'Not available yet — requires a traffic-analytics integration.' }}</p>
        </div>
        @endforeach

        {{-- Quick reports --}}
        <div class="ui-card">
            <h3 class="ui-card-title mb-3">{{ $isFr ? 'RAPPORTS RAPIDES' : 'QUICK REPORTS' }}</h3>
            <div class="grid grid-cols-2 gap-2">
                @foreach([
                    ['shopping-cart', '#157A43', $isFr ? 'Rapport des ventes' : 'Sales report', route('admin.orders')],
                    ['users-round', '#3565DE', $isFr ? 'Rapport des utilisateurs' : 'Users report', route('admin.users')],
                    ['package', '#C97A16', $isFr ? 'Rapport des produits' : 'Products report', route('admin.products')],
                    ['map', '#7C4FE0', $isFr ? 'Rapport géographique' : 'Geographic report', route('admin.regions')],
                ] as [$qIcon, $qColor, $qLabel, $qUrl])
                <a href="{{ $qUrl }}" class="flex flex-col items-center gap-1.5 text-center border border-[#EFEBE2] dark:border-[#262B21] rounded-xl py-3 hover:border-[#14652F] dark:hover:border-[#2E9250] transition-colors">
                    <i data-lucide="{{ $qIcon }}" class="w-4 h-4" style="color: {{ $qColor }}"></i>
                    <span class="text-[12px] md:text-[10.5px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $qLabel }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
