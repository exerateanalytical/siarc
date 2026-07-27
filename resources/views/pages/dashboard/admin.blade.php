@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'dashboard';
    $pageTitle = $isFr ? 'Tableau de Bord' : 'Dashboard';

    // ═══ Real platform aggregates (computed in the dashboard.admin route) ═══
    // [icon, iconColor, tileBg, label, value, btnLabel, btnBg, url]
    $adKpis = [
        ['user-plus',     '#157A43', '#E8F2EC', $isFr ? 'Artisans inscrits' : 'Registered artisans', number_format($stats['users']['business_owners']), $isFr ? 'Voir les artisans' : 'See the artisans',      '#0B3B20', route('admin.businesses')],
        ['building-2',    '#157A43', '#E8F2EC', $isFr ? 'Entreprises' : 'Businesses',                number_format($stats['businesses']['total']),  $isFr ? 'Voir les entreprises' : 'See the businesses', '#0B3B20', route('admin.businesses')],
        ['shopping-basket','#C97A16','#FBF1DD', $isFr ? 'Produits référencés' : 'Listed products',   number_format($stats['products']['total']), $isFr ? 'Voir les produits' : 'See the products',      '#DD8F0F', route('admin.products')],
        ['shopping-bag',  '#6D28D9', '#F0EAFB', 'Commandes',                                          number_format($ordersCount),  $isFr ? 'Voir les commandes' : 'See the orders',       '#6D28D9', route('admin.quotes')],
        ['eye',           '#157A43', '#E8F2EC', $isFr ? 'Visites plateforme' : 'Platform visits',    number_format($platformVisits), $isFr ? 'Voir les statistiques' : 'See the statistics', '#1D4ED8', route('admin.reports')],
        ['circle-dollar-sign','#C97A16','#FBF1DD', $isFr ? 'Chiffre d\'affaires' : 'Revenue',        number_format($revenueTotal, 0, ',', ' ') . ' FCFA', $isFr ? 'Voir les revenus' : 'See the revenue', '#0B3B20', route('admin.reports')],
    ];

    // [label, color, count, pct] — real per-region business counts
    $regionColors = ['#B9C08A', '#E9B23C', '#38BDF8', '#7C9BD8', '#C8CDD4', '#8B5E34', '#1E3A8A', '#F0A860', '#EFB7A2', '#D8467E'];
    $adRegionsView = $adRegions->values()->map(fn ($r, $i) => [
        $r->name_fr, $regionColors[$i % count($regionColors)], number_format($r->total),
        round($r->total / $adRegionsTotal * 100, 1) . '%',
    ]);

    $adVisitStats = [
        [$isFr ? 'Visites totales' : 'Total visits',    number_format($platformVisits)],
        [$isFr ? 'Visiteurs uniques' : 'Unique visitors', number_format($uniqueVisitors)],
    ];

    // [icon, iconColor, label, url]
    $adActions = [
        ['user-plus',     '#157A43', $isFr ? "Ajouter\nun artisan" : "Add\nan artisan",        route('admin.businesses')],
        ['package-plus',  '#157A43', $isFr ? "Ajouter\nun produit" : "Add\na product",         route('products.web-create')],
        ['building-2',    '#1D4ED8', $isFr ? "Ajouter\nune entreprise" : "Add\na business",    route('business.create')],
        ['layers',        '#C97A16', $isFr ? "Nouvelle\ncollection" : "New\ncollection",        route('admin.cms')],
        ['calendar-plus', '#DD6B20', $isFr ? "Créer un\névénement" : "Create\nan event",        route('admin.events')],
        ['shopping-cart', '#DD6B20', $isFr ? "Gérer les\ncommandes" : "Manage\norders",         route('admin.quotes')],
        ['chart-column',  '#157A43', $isFr ? "Générer\nrapport" : "Generate\nreport",           route('admin.reports')],
        ['megaphone',     '#C97A16', $isFr ? "Envoyer\nannonce" : "Send\nannouncement",         route('admin.cms')],
    ];

    // [rank, label, count, pct] — real per-industry product counts
    $adTopCatsView = $adTopCats->values()->map(fn ($c, $i) => [
        (string) ($i + 1), $c->name_fr, number_format($c->total), (int) round($c->total / $adTopCatsMax * 100),
    ]);

    // Donut builder: turns a [status => count] map + ordered [key,label,color] defs into
    // conic-gradient CSS and a legend list, entirely from real counts.
    $buildDonut = function ($rawCounts, $total, $defs) {
        $legend = []; $cssParts = []; $cursor = 0;
        foreach ($defs as [$key, $label, $color]) {
            $count = (int) ($rawCounts[$key] ?? 0);
            $pct = $total ? round($count / $total * 100, 1) : 0;
            $legend[] = [$label, $color, number_format($count), $pct . '%'];
            $next = $cursor + $pct;
            $cssParts[] = "{$color} {$cursor}% {$next}%";
            $cursor = $next;
        }
        return ['css' => 'conic-gradient(' . implode(', ', $cssParts) . ')', 'legend' => $legend];
    };
    $adKycDonut = $buildDonut($adKycRaw, $adKycTotal, [
        ['approved', $isFr ? 'Approuvés' : 'Approved', '#157A43'],
        ['under_review', $isFr ? 'En cours' : 'In progress', '#E9B23C'],
        ['submitted', $isFr ? 'Soumis' : 'Submitted', '#2563EB'],
        ['draft', $isFr ? 'Brouillon' : 'Draft', '#9CA3AF'],
        ['rejected', $isFr ? 'Rejetés' : 'Rejected', '#DC2626'],
    ]);
    $adKyc = $adKycDonut['legend'];
    $adKycCss = $adKycDonut['css'];

    $adBizDonut = $buildDonut($adBizStatusRaw, $adBizStatusTotal, [
        ['published', $isFr ? 'Actives' : 'Active', '#157A43'],
        ['draft', $isFr ? 'Brouillon' : 'Draft', '#7C3AED'],
        ['suspended', $isFr ? 'Suspendues' : 'Suspended', '#E9B23C'],
        ['rejected', $isFr ? 'Rejetées' : 'Rejected', '#DC2626'],
    ]);
    $adBizStatus = $adBizDonut['legend'];
    $adBizCss = $adBizDonut['css'];

    // Revenue bars — real monthly paid-invoice totals
    $adRevenueView = $adRevenue->map(fn ($m) => [$m['label'], $adRevenueMax ? (int) round($m['total'] / $adRevenueMax * 100) : 0]);

    // Real recent-activity feed (newest businesses, verification requests, orders)
    $adActivitiesView = $adActivities->map(fn ($a) => [$a[0], $a[1], $a[2], $a[3], $a[4], $a[5]]);
@endphp

@section('content')

            <!-- Welcome row -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    {{-- No operator name invented: greet without one when the session has none. --}}
                    <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Bienvenue' : 'Welcome' }}@if(! empty($siacUser['name'])), {{ $siacUser['name'] }}@endif 👋</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Centre de Commandement du Patrimoine & de l\'Artisanat Camerounais' : 'Command Centre of Cameroonian Heritage & Craftsmanship' }}</p>
                </div>
                <div class="shrink-0 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2.5 bg-white border border-[#E9E4D8] rounded-lg px-4 h-[40px] text-[12.5px] font-semibold text-[#1B1B18]">
                        <i data-lucide="calendar-days" class="w-4 h-4 text-[#55524A]" style="stroke-width:1.7"></i>
                        {{-- The KPIs below are all-time platform totals, not a filtered range, so
                             the badge states when they were read instead of a made-up period. --}}
                        {{ $isFr ? 'Au' : 'As of' }} {{ now()->locale($lang)->translatedFormat('d F Y') }}
                    </span>
                    <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-2.5 bg-white border border-[#DCE7DF] hover:border-[#14652F] rounded-lg px-4 h-[40px] text-[12.5px] font-semibold text-[#14652F] transition-colors">
                        <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Exporter le rapport' : 'Export the report' }}
                    </a>
                </div>
            </div>

            <!-- KPI cards -->
            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-6 gap-3">
                @foreach($adKpis as [$kIcon, $kColor, $kBg, $kLabel, $kValue, $kBtn, $kBtnBg, $kUrl])
                <div class="bg-white border border-[#EFEBE2] rounded-2xl p-3.5 flex flex-col">
                    <div class="flex items-start gap-2.5">
                        <span class="w-[40px] h-[40px] shrink-0 rounded-full border flex items-center justify-center" style="background:{{ $kBg }};border-color:{{ $kColor }}33">
                            <i data-lucide="{{ $kIcon }}" class="w-[19px] h-[19px]" style="stroke-width:1.7;color:{{ $kColor }}"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase leading-tight">{{ $kLabel }}</p>
                            <p class="mt-0.5 text-[16.5px] font-bold text-[#1B1B18] leading-tight">{{ $kValue }}</p>
                        </div>
                    </div>
                    <a href="{{ $kUrl }}" class="mt-auto pt-3 block">
                        <span class="flex items-center justify-center gap-2 rounded-lg h-[32px] text-[11.5px] font-semibold text-white transition-opacity hover:opacity-90" style="background:{{ $kBtnBg }}">
                            {{ $kBtn }}
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </a>
                </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-col 2xl:flex-row gap-4 items-start">
                <div class="flex-1 min-w-0 w-full">

                    <!-- Map + visits -->
                    <div class="flex flex-col xl:flex-row gap-4 items-stretch">
                        <section class="flex-[1.05] min-w-0 bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[13px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Répartition des artisans par région' : 'Artisans by region' }}</h2>
                                <a href="{{ route('admin.regions') }}" class="shrink-0 text-[11.5px] font-semibold text-[#157A43] hover:text-[#14532D] whitespace-nowrap">{{ $isFr ? 'Voir la carte détaillée' : 'Detailed map' }} →</a>
                            </div>
                            <div class="mt-3 flex flex-col sm:flex-row gap-4">
                                <img src="{{ asset('images/landing/ad-map.png') }}" alt="{{ $isFr ? 'Carte du Cameroun' : 'Map of Cameroon' }}" class="w-full sm:w-[60%] object-contain self-start">
                                <ul class="flex-1 min-w-0 space-y-[7px] self-center">
                                    @forelse($adRegionsView as [$rgLabel, $rgColor, $rgCount, $rgPct])
                                    <li class="flex items-center gap-2.5 text-[11px] text-[#3B382F] whitespace-nowrap">
                                        <span class="w-[10px] h-[10px] shrink-0 rounded-full" style="background:{{ $rgColor }}"></span>
                                        <span class="flex-1">{{ $rgLabel }}</span>
                                        <span class="font-semibold">{{ $rgCount }} ({{ $rgPct }})</span>
                                    </li>
                                    @empty
                                    <li class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée régionale.' : 'No regional data.' }}</li>
                                    @endforelse
                                </ul>
                            </div>
                            <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="mt-3 inline-flex items-center gap-2 border border-[#CFE0D4] hover:border-[#14652F] rounded-lg px-4 py-2 text-[12px] font-semibold text-[#14652F] transition-colors">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                {{ $isFr ? 'Explorer par région' : 'Explore by region' }}
                            </a>
                        </section>

                        <section class="flex-1 min-w-0 bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[13px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Évolution des visites' : 'Visits over time' }}</h2>
                                <span class="shrink-0 inline-flex items-center gap-1.5 border border-[#E9E4D8] rounded-lg px-3 py-1.5 text-[11.5px] text-[#3B382F]">
                                    {{ $isFr ? 'Cette période' : 'This period' }}
                                </span>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-y-3">
                                @foreach($adVisitStats as [$vsLabel, $vsValue])
                                <div class="px-3 first:pl-0">
                                    <p class="text-[10px] font-bold tracking-[0.05em] text-[#8A857A] uppercase whitespace-nowrap">{{ $vsLabel }}</p>
                                    <p class="mt-1 text-[15.5px] font-bold text-[#1B1B18]">{{ $vsValue }}</p>
                                </div>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <!-- Quick actions -->
                    <section class="mt-4">
                        <h2 class="text-[13px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3">
                            @foreach($adActions as [$qaIcon, $qaColor, $qaLabel, $qaUrl])
                            <a href="{{ $qaUrl }}" class="bg-white border border-[#EFEBE2] hover:border-[#14652F] rounded-2xl px-3 py-4 text-center transition-colors">
                                <i data-lucide="{{ $qaIcon }}" class="w-[26px] h-[26px] mx-auto" style="stroke-width:1.5;color:{{ $qaColor }}"></i>
                                <p class="mt-2.5 text-[11.5px] font-semibold text-[#3B382F] leading-snug whitespace-pre-line">{{ $qaLabel }}</p>
                            </a>
                            @endforeach
                        </div>
                    </section>

                    <!-- Top categories + donuts -->
                    <div class="mt-4 grid grid-cols-1 xl:grid-cols-3 gap-4 items-stretch">
                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase leading-snug">{{ $isFr ? 'Top 5 catégories de produits' : 'Top 5 product categories' }}</h2>
                                <a href="{{ route('admin.industries') }}" class="shrink-0 text-[11.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                            </div>
                            <div class="mt-4 space-y-3.5">
                                @forelse($adTopCatsView as [$tcRank, $tcLabel, $tcCount, $tcPct])
                                <div class="flex items-center gap-3">
                                    <span class="w-[22px] h-[22px] shrink-0 rounded-full bg-[#F1EADB] text-[#8A6D1F] text-[11px] font-bold flex items-center justify-center">{{ $tcRank }}</span>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-[12px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $tcLabel }}</p>
                                            <p class="shrink-0 text-[11px] text-[#55524A] whitespace-nowrap">{{ $tcCount }} {{ $isFr ? 'produits' : 'products' }}</p>
                                        </div>
                                        <div class="mt-1.5 h-[4px] rounded-full bg-[#EFEBE2]"><div class="h-full rounded-full bg-[#14652F]" style="width:{{ $tcPct }}%"></div></div>
                                    </div>
                                </div>
                                @empty
                                <p class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Aucune catégorie de produits pour le moment.' : 'No product categories yet.' }}</p>
                                @endforelse
                            </div>
                        </section>

                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase leading-snug">{{ $isFr ? 'Statut des vérifications KYC' : 'KYC verification status' }}</h2>
                                <a href="{{ route('admin.verifications') }}" class="shrink-0 text-[11.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                            </div>
                            <div class="mt-4 flex items-center gap-4">
                                <div class="relative w-[110px] h-[110px] shrink-0 rounded-full" style="background:{{ $adKycCss }}">
                                    <div class="absolute inset-[16px] rounded-full bg-white flex flex-col items-center justify-center text-center">
                                        <span class="text-[9px] text-[#8A857A] uppercase font-bold">Total</span>
                                        <span class="text-[15px] font-bold text-[#1B1B18] leading-tight">{{ number_format($adKycTotal) }}</span>
                                        <span class="text-[9px] text-[#8A857A]">{{ $isFr ? 'demandes' : 'requests' }}</span>
                                    </div>
                                </div>
                                <ul class="flex-1 min-w-0 space-y-2.5">
                                    @foreach($adKyc as [$kyLabel, $kyColor, $kyCount, $kyPct])
                                    <li class="flex items-center gap-2 text-[11px] text-[#3B382F] whitespace-nowrap">
                                        <span class="w-[9px] h-[9px] shrink-0 rounded-full" style="background:{{ $kyColor }}"></span>
                                        <span class="flex-1">{{ $kyLabel }}</span>
                                        <span class="font-bold">{{ $kyCount }} ({{ $kyPct }})</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>

                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase leading-snug">{{ $isFr ? 'Entreprises par statut' : 'Businesses by status' }}</h2>
                                <a href="{{ route('admin.businesses') }}" class="shrink-0 text-[11.5px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                            </div>
                            <div class="mt-4 flex items-center gap-4">
                                <div class="relative w-[110px] h-[110px] shrink-0 rounded-full" style="background:{{ $adBizCss }}">
                                    <div class="absolute inset-[16px] rounded-full bg-white flex flex-col items-center justify-center text-center">
                                        <span class="text-[9px] text-[#8A857A] uppercase font-bold">Total</span>
                                        <span class="text-[15px] font-bold text-[#1B1B18] leading-tight">{{ number_format($adBizStatusTotal) }}</span>
                                        <span class="text-[9px] text-[#8A857A]">{{ $isFr ? 'entreprises' : 'businesses' }}</span>
                                    </div>
                                </div>
                                <ul class="flex-1 min-w-0 space-y-2.5">
                                    @foreach($adBizStatus as [$bzLabel, $bzColor, $bzCount, $bzPct])
                                    <li class="flex items-center gap-2 text-[11px] text-[#3B382F] whitespace-nowrap">
                                        <span class="w-[9px] h-[9px] shrink-0 rounded-full" style="background:{{ $bzColor }}"></span>
                                        <span class="flex-1">{{ $bzLabel }}</span>
                                        <span class="font-bold">{{ $bzCount }} ({{ $bzPct }})</span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </section>
                    </div>

                    <!-- Region activity table -->
                    <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Activité par région' : 'Activity by region' }}</h2>
                        <div class="mt-2 overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-[#F0EBE0]">
                                        <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Région' : 'Region' }}</th>
                                        <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Entreprises' : 'Businesses' }}</th>
                                        <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Produits' : 'Products' }}</th>
                                        <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Visites' : 'Visits' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adRegionTable as $rr)
                                    <tr>
                                        <td class="py-2 text-[12px] text-[#1B1B18]">{{ $rr->name_fr }}</td>
                                        <td class="py-2 text-[12px] text-[#3B382F]">{{ number_format($rr->businesses_total) }}</td>
                                        <td class="py-2 text-[12px] text-[#3B382F]">{{ number_format($rr->products_total) }}</td>
                                        <td class="py-2 text-[12px] text-[#3B382F]">{{ number_format($rr->visits_total) }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="py-4 text-center text-[12px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée régionale.' : 'No regional data.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <p class="mt-5 text-center text-[11.5px] text-[#8A857A]">© 2025 {{ $isFr ? 'Artisan Hub 237. Tous droits réservés.' : 'Artisan Hub 237. All rights reserved.' }}</p>
                </div>

                <!-- Right rail -->
                <aside class="w-full 2xl:w-[330px] shrink-0 space-y-4">
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Artisan à l\'honneur' : 'Featured artisan' }}</h2>
                            <a href="{{ route('admin.businesses') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir tous' : 'See all' }} ↗</a>
                        </div>
                        @if($featuredBusiness)
                        <div class="mt-3 flex items-center gap-3.5">
                            <img src="{{ $featuredBusiness->logo ? asset('storage/'.$featuredBusiness->logo) : asset('images/landing/ad-artisan.png') }}" alt="{{ $featuredBusiness->name_fr }}" class="w-[92px] h-[92px] rounded-xl object-cover shrink-0">
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-[#1B1B18]">{{ $featuredBusiness->name_fr }}</p>
                                @if($featuredBusiness->industry_fr)<p class="mt-0.5 text-[11.5px] font-semibold text-[#C97A16] underline underline-offset-2">{{ $featuredBusiness->industry_fr }}</p>@endif
                                @if($featuredBusiness->region_fr)<p class="mt-1 flex items-center gap-1.5 text-[11px] text-[#6F6B60]"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $featuredBusiness->region_fr }}</p>@endif
                                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="mt-2 inline-flex items-center bg-[#0B3B20] hover:bg-[#14652F] rounded-lg px-4 py-1.5 text-[11.5px] font-semibold text-white transition-colors">{{ $isFr ? 'Voir le profil' : 'View the profile' }}</a>
                            </div>
                        </div>
                        @else
                        <p class="mt-3 text-[12px] text-[#8A857A]">{{ $isFr ? 'Aucune entreprise mise en avant pour le moment.' : 'No featured business yet.' }}</p>
                        @endif
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Activités récentes' : 'Recent activities' }}</h2>
                            <a href="{{ route('admin.audit-log') }}" class="shrink-0 text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir toutes' : 'See all' }} ↗</a>
                        </div>
                        <div class="mt-2 divide-y divide-[#F5F1E8]">
                            @forelse($adActivitiesView as [$avIcon, $avColor, $avBg, $avTitle, $avSub, $avWhen])
                            <div class="flex items-start gap-3 py-2.5">
                                <span class="w-[32px] h-[32px] shrink-0 rounded-full flex items-center justify-center" style="background:{{ $avBg }}">
                                    <i data-lucide="{{ $avIcon }}" class="w-[15px] h-[15px]" style="stroke-width:1.8;color:{{ $avColor }}"></i>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11.5px] font-bold text-[#1B1B18] leading-snug">{{ $avTitle }}</p>
                                    <p class="mt-0.5 text-[10.5px] text-[#6F6B60]">{{ $avSub }}</p>
                                </div>
                                <span class="shrink-0 text-[10px] text-[#8A857A] whitespace-nowrap pt-0.5">{{ $avWhen }}</span>
                            </div>
                            @empty
                            <p class="py-4 text-center text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucune activité récente.' : 'No recent activity.' }}</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Revenus par mois (FCFA)' : 'Revenue by month (FCFA)' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir le rapport' : 'Report' }}</a>
                        </div>
                        <div class="mt-3 flex items-stretch gap-2">
                            <div class="flex-1 flex items-end justify-between gap-2 h-[130px] border-b border-[#F0EBE0] px-1">
                                @foreach($adRevenueView as [$rvMonth, $rvPct])
                                <div class="flex-1 flex items-end justify-center h-full">
                                    <div class="w-full max-w-[26px] bg-[#14652F] rounded-t-[3px]" style="height:{{ $rvPct }}%"></div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-1.5 flex items-center justify-between gap-2 px-1">
                            @foreach($adRevenueView as [$rvMonth, $rvPct])
                            <span class="flex-1 text-center text-[10px] text-[#8A857A]">{{ $rvMonth }}</span>
                            @endforeach
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center justify-between gap-2 px-1 text-[11.5px] text-[#55524A]">
                        <a href="{{ route('support.index') }}" class="flex items-center gap-1.5 hover:text-[#14652F]"><i data-lucide="circle-help" class="w-3.5 h-3.5"></i> {{ $isFr ? 'Aide' : 'Help' }}</a>
                        <a href="{{ route('admin.support') }}" class="flex items-center gap-1.5 hover:text-[#14652F]"><i data-lucide="life-buoy" class="w-3.5 h-3.5"></i> {{ $isFr ? 'Centre d\'assistance' : 'Assistance centre' }}</a>
                        <a href="{{ route('contact', ['lang' => $lang]) }}" class="flex items-center gap-1.5 hover:text-[#14652F]"><i data-lucide="mail" class="w-3.5 h-3.5"></i> {{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
                    </div>
                </aside>
            </div>
@endsection
