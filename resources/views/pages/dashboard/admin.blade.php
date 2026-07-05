@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'dashboard';
    $pageTitle = $isFr ? 'Tableau de Bord' : 'Dashboard';

    // ═══ Design-static content (pixel mandate: design numbers verbatim) ═══
    // [icon, iconColor, tileBg, label, value, delta, btnLabel, btnBg, url]
    $adKpis = [
        ['user-plus',     '#157A43', '#E8F2EC', $isFr ? 'Artisans inscrits' : 'Registered artisans', '24,568', '12.5%', $isFr ? 'Voir les artisans' : 'See the artisans',      '#0B3B20', route('admin.businesses')],
        ['building-2',    '#157A43', '#E8F2EC', $isFr ? 'Entreprises' : 'Businesses',                '8,742',  '9.4%',  $isFr ? 'Voir les entreprises' : 'See the businesses', '#0B3B20', route('admin.businesses')],
        ['shopping-basket','#C97A16','#FBF1DD', $isFr ? 'Produits référencés' : 'Listed products',   '36,845', '14.3%', $isFr ? 'Voir les produits' : 'See the products',      '#DD8F0F', route('admin.products')],
        ['shopping-bag',  '#6D28D9', '#F0EAFB', 'Commandes',                                          '1,256',  '18.7%', $isFr ? 'Voir les commandes' : 'See the orders',       '#6D28D9', route('admin.quotes')],
        ['eye',           '#157A43', '#E8F2EC', $isFr ? 'Visites plateforme' : 'Platform visits',    '320,985','21.8%', $isFr ? 'Voir les statistiques' : 'See the statistics', '#1D4ED8', route('admin.reports')],
        ['circle-dollar-sign','#C97A16','#FBF1DD', $isFr ? 'Chiffre d\'affaires' : 'Revenue',        '125,890,000 FCFA', '16.2%', $isFr ? 'Voir les revenus' : 'See the revenue', '#0B3B20', route('admin.reports')],
    ];

    // [label, color, count, pct]
    $adRegions = [
        ['Extrême-Nord', '#B9C08A', '2,845', '11.6%'],
        ['Nord',         '#E9B23C', '3,421', '13.9%'],
        ['Adamaoua',     '#38BDF8', '2,739', '11.2%'],
        ['Nord-Ouest',   '#7C9BD8', '3,987', '16.2%'],
        ['Ouest',        '#C8CDD4', '4,582', '18.6%'],
        ['Sud-Ouest',    '#8B5E34', '3,256', '13.3%'],
        ['Littoral',     '#1E3A8A', '4,128', '16.8%'],
        ['Centre',       '#F0A860', '3,012', '12.3%'],
        ['Est',          '#EFB7A2', '2,256', '9.2%'],
        ['Sud',          '#D8467E', '1,102', '4.5%'],
    ];

    $adVisitStats = [
        [$isFr ? 'Visites totales' : 'Total visits',    '320,985'],
        [$isFr ? 'Visiteurs uniques' : 'Unique visitors','182,450'],
        [$isFr ? 'Pages vues' : 'Page views',           '1,248,670'],
        [$isFr ? 'Durée moyenne' : 'Average duration',  '04m 35s'],
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

    // [rank, label, count, pct]
    $adTopCats = [
        ['1', 'Sculpture & Bois',    '8,562', 92],
        ['2', 'Textiles & Tissus',   '6,845', 76],
        ['3', 'Poterie & Céramique', '5,985', 66],
        ['4', 'Bijouterie & Métal',  '4,521', 52],
        ['5', 'Vannerie & Rotin',    '3,256', 38],
    ];

    // KYC donut: [label, color, count, pct]
    $adKyc = [
        [$isFr ? 'Vérifiés' : 'Verified',   '#157A43', '812', '64.7%'],
        [$isFr ? 'En cours' : 'In progress','#E9B23C', '286', '22.8%'],
        [$isFr ? 'En attente' : 'Pending',  '#2563EB', '98',  '7.8%'],
        [$isFr ? 'Rejetés' : 'Rejected',    '#DC2626', '60',  '4.7%'],
    ];
    $adKycCss = 'conic-gradient(#157A43 0 64.7%, #E9B23C 64.7% 87.5%, #2563EB 87.5% 95.3%, #DC2626 95.3% 100%)';

    $adBizStatus = [
        [$isFr ? 'Actives' : 'Active',       '#157A43', '5,124', '58.6%'],
        [$isFr ? 'En attente' : 'Pending',   '#7C3AED', '1,256', '14.4%'],
        [$isFr ? 'Suspendues' : 'Suspended', '#E9B23C', '842',   '9.6%'],
        [$isFr ? 'Inactives' : 'Inactive',   '#DC2626', '1,520', '17.4%'],
    ];
    $adBizCss = 'conic-gradient(#157A43 0 58.6%, #7C3AED 58.6% 73%, #E9B23C 73% 82.6%, #DC2626 82.6% 100%)';

    // Region activity table (design's two-column layout, verbatim incl. "Nord-Oèst" [sic])
    $adRegionRowsL = [
        ['Littoral',   '4,128', '1,542', '6,845', '68,452', 'ad-spark-1.png'],
        ['Ouest',      '4,562', '1,823', '7,562', '72,851', 'ad-spark-2.png'],
        ['Nord-Ouest', '3,987', '1,256', '5,421', '54,236', 'ad-spark-3.png'],
    ];
    $adRegionRowsR = [
        ['Nord-Oèst',  '3,987', '1,256', '5,421',  'ad-spark-4.png'],
        ['Centre',     '3,012', '1,102', '42,365', 'ad-spark-5.png'],
        ['Sud',        '1,102', '452',   '20,365', 'ad-spark-6.png'],
    ];

    // [icon, iconColor, tileBg, title, sub, when]
    $adActivities = [
        ['user-plus',   '#157A43', '#E8F2EC', $isFr ? 'Nouvel artisan en attente de validation' : 'New artisan awaiting validation', 'Emmanuel Tchana (Nord-Ouest)', $isFr ? 'Il y a 15 min' : '15 min ago'],
        ['building-2',  '#1D4ED8', '#E9EFFC', $isFr ? 'Nouvelle entreprise inscrite' : 'New business registered', 'Crafts Du Cameroun SARL (Littoral)', $isFr ? 'Il y a 25 min' : '25 min ago'],
        ['badge-check', '#157A43', '#E8F2EC', $isFr ? 'Paiement confirmé' : 'Payment confirmed', $isFr ? 'Commande #CM2360603156' : 'Order #CM2360603156', $isFr ? 'Il y a 35 min' : '35 min ago'],
        ['ticket',      '#6D28D9', '#F0EAFB', $isFr ? 'Inscription visiteur SIARC 2026' : 'SIARC 2026 visitor registration', 'John Smith (États-Unis)', $isFr ? 'Il y a 1 h' : '1 h ago'],
        ['calendar-days','#D8467E','#FDE9F1', $isFr ? 'Nouveau rendez-vous B2B' : 'New B2B meeting', $isFr ? '25 Juin 2025 - Pavillon B' : '25 June 2025 - Pavilion B', $isFr ? 'Il y a 2 h' : '2 h ago'],
    ];

    // Revenue bars Jan..Juin (percent heights per the design)
    $adRevenue = [['Jan', 30], ['Fév', 48], ['Mar', 58], ['Avr', 50], ['Mai', 90], ['Juin', 72]];
@endphp

@section('content')

            <!-- Welcome row -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Bienvenue' : 'Welcome' }}, {{ $siacUser['name'] ?? 'Admin Super' }} 👋</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Centre de Commandement du Patrimoine & de l\'Artisanat Camerounais' : 'Command Centre of Cameroonian Heritage & Craftsmanship' }}</p>
                </div>
                <div class="shrink-0 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2.5 bg-white border border-[#E9E4D8] rounded-lg px-4 h-[40px] text-[12.5px] font-semibold text-[#1B1B18]">
                        <i data-lucide="calendar-days" class="w-4 h-4 text-[#55524A]" style="stroke-width:1.7"></i>
                        01 {{ $isFr ? 'Mai' : 'May' }} 2025 - 03 {{ $isFr ? 'Juin' : 'June' }} 2025
                    </span>
                    <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-2.5 bg-white border border-[#DCE7DF] hover:border-[#14652F] rounded-lg px-4 h-[40px] text-[12.5px] font-semibold text-[#14652F] transition-colors">
                        <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Exporter le rapport' : 'Export the report' }}
                    </a>
                </div>
            </div>

            <!-- KPI cards -->
            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-6 gap-3">
                @foreach($adKpis as [$kIcon, $kColor, $kBg, $kLabel, $kValue, $kDelta, $kBtn, $kBtnBg, $kUrl])
                <div class="bg-white border border-[#EFEBE2] rounded-2xl p-3.5 flex flex-col">
                    <div class="flex items-start gap-2.5">
                        <span class="w-[40px] h-[40px] shrink-0 rounded-full border flex items-center justify-center" style="background:{{ $kBg }};border-color:{{ $kColor }}33">
                            <i data-lucide="{{ $kIcon }}" class="w-[19px] h-[19px]" style="stroke-width:1.7;color:{{ $kColor }}"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase leading-tight">{{ $kLabel }}</p>
                            <p class="mt-0.5 text-[16.5px] font-bold text-[#1B1B18] leading-tight">{{ $kValue }} <span class="text-[11px] font-bold text-[#157A43] whitespace-nowrap">↑ {{ $kDelta }}</span></p>
                            <p class="text-[10.5px] text-[#8A857A]">{{ $isFr ? 'vs mois dernier' : 'vs last month' }}</p>
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
                                    @foreach($adRegions as [$rgLabel, $rgColor, $rgCount, $rgPct])
                                    <li class="flex items-center gap-2.5 text-[11px] text-[#3B382F] whitespace-nowrap">
                                        <span class="w-[10px] h-[10px] shrink-0 rounded-full" style="background:{{ $rgColor }}"></span>
                                        <span class="flex-1">{{ $rgLabel }}</span>
                                        <span class="font-semibold">{{ $rgCount }} ({{ $rgPct }})</span>
                                    </li>
                                    @endforeach
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
                            <img src="{{ asset('images/landing/ad-chart.png') }}" alt="" class="mt-3 w-full object-contain" aria-hidden="true">
                            <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 sm:divide-x divide-[#F0EBE0] border-t border-[#F0EBE0] pt-3 gap-y-3">
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
                                @foreach($adTopCats as [$tcRank, $tcLabel, $tcCount, $tcPct])
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
                                @endforeach
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
                                        <span class="text-[15px] font-bold text-[#1B1B18] leading-tight">1,256</span>
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
                                        <span class="text-[15px] font-bold text-[#1B1B18] leading-tight">8,742</span>
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
                            <div class="min-w-[820px] grid grid-cols-2 gap-x-10">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left border-b border-[#F0EBE0]">
                                            <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Région' : 'Region' }}</th>
                                            <th class="py-2 text-[11px] font-semibold text-[#8A857A]">Artisans</th>
                                            <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Entreprises' : 'Businesses' }}</th>
                                            <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Produits' : 'Products' }}</th>
                                            <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Visites' : 'Visits' }}</th>
                                            <th class="py-2 text-[11px] font-semibold text-[#8A857A]">{{ $isFr ? 'Tendance' : 'Trend' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($adRegionRowsL as [$raRegion, $raA, $raE, $raP, $raV, $raSpark])
                                        <tr>
                                            <td class="py-2 text-[12px] text-[#1B1B18]">{{ $raRegion }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $raA }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $raE }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $raP }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $raV }}</td>
                                            <td class="py-2"><img src="{{ asset('images/landing/' . $raSpark) }}" alt="" class="h-[16px]" aria-hidden="true"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <table class="w-full self-end">
                                    <tbody>
                                        <tr class="border-b border-transparent"><td class="py-2 text-[11px] text-transparent select-none" colspan="5">.</td></tr>
                                        @foreach($adRegionRowsR as [$rbRegion, $rbA, $rbE, $rbP, $rbSpark])
                                        <tr>
                                            <td class="py-2 text-[12px] text-[#1B1B18]">{{ $rbRegion }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $rbA }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $rbE }}</td>
                                            <td class="py-2 text-[12px] text-[#3B382F]">{{ $rbP }}</td>
                                            <td class="py-2"><img src="{{ asset('images/landing/' . $rbSpark) }}" alt="" class="h-[16px]" aria-hidden="true"></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <p class="mt-5 text-center text-[11.5px] text-[#8A857A]">© 2025 {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
                </div>

                <!-- Right rail -->
                <aside class="w-full 2xl:w-[330px] shrink-0 space-y-4">
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden flex">
                        <div class="flex-1 min-w-0 p-4">
                            <h2 class="text-[17px] font-bold text-[#8A6D1F]">SIARC 2026</h2>
                            <p class="mt-0.5 text-[11px] text-[#55524A] leading-snug">{{ $isFr ? 'Salon International de l\'Artisanat du Cameroun' : 'International Craft Fair of Cameroon' }}</p>
                            <div class="mt-3 grid grid-cols-4 gap-1.5">
                                @foreach([['54', $isFr ? 'JOURS' : 'DAYS'], ['12', $isFr ? 'HEURES' : 'HOURS'], ['36', 'MIN'], ['48', 'SEC']] as [$cdV, $cdL])
                                <div class="bg-[#F8F4EC] border border-[#EFE7D6] rounded-lg py-1.5 text-center">
                                    <p class="text-[15px] font-bold text-[#1B1B18] leading-tight">{{ $cdV }}</p>
                                    <p class="text-[8.5px] font-bold text-[#8A857A]">{{ $cdL }}</p>
                                </div>
                                @endforeach
                            </div>
                            <a href="{{ route('admin.siarc') }}" class="mt-3 flex items-center justify-center gap-2 bg-[#0B3B20] hover:bg-[#14652F] rounded-lg h-[34px] text-[11px] font-semibold text-white transition-colors whitespace-nowrap">
                                {{ $isFr ? 'Accéder à la gestion SIARC 2026' : 'Open SIARC 2026 management' }} →
                            </a>
                        </div>
                        <img src="{{ asset('images/landing/ad-siarc.png') }}" alt="" class="w-[104px] object-cover shrink-0 hidden sm:block" aria-hidden="true">
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Artisan à l\'honneur' : 'Featured artisan' }}</h2>
                            <a href="{{ route('admin.businesses') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir tous' : 'See all' }} ↗</a>
                        </div>
                        <div class="mt-3 flex items-center gap-3.5">
                            <img src="{{ asset('images/landing/ad-artisan.png') }}" alt="Dame Rosine Mefou" class="w-[92px] h-[92px] rounded-xl object-cover shrink-0">
                            <div class="min-w-0">
                                <p class="text-[14px] font-bold text-[#1B1B18]">Dame Rosine Mefou</p>
                                <p class="mt-0.5 text-[11.5px] font-semibold text-[#C97A16] underline underline-offset-2">{{ $isFr ? 'Sculpture sur bois' : 'Wood sculpture' }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-[11px] text-[#6F6B60]"><i data-lucide="map-pin" class="w-3 h-3"></i> {{ $isFr ? 'Centre Région' : 'Centre Region' }}</p>
                                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="mt-2 inline-flex items-center bg-[#0B3B20] hover:bg-[#14652F] rounded-lg px-4 py-1.5 text-[11.5px] font-semibold text-white transition-colors">{{ $isFr ? 'Voir le profil' : 'View the profile' }}</a>
                            </div>
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Activités récentes' : 'Recent activities' }}</h2>
                            <a href="{{ route('admin.audit-log') }}" class="shrink-0 text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir toutes' : 'See all' }} ↗</a>
                        </div>
                        <div class="mt-2 divide-y divide-[#F5F1E8]">
                            @foreach($adActivities as [$avIcon, $avColor, $avBg, $avTitle, $avSub, $avWhen])
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
                            @endforeach
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-4">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Revenus par mois (FCFA)' : 'Revenue by month (FCFA)' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir le rapport' : 'Report' }}</a>
                        </div>
                        <div class="mt-3 flex items-stretch gap-2">
                            <div class="flex flex-col justify-between text-right text-[9px] text-[#8A857A] py-0.5">
                                <span>100M</span><span>80M</span><span>60M</span><span>40M</span><span>20M</span><span>0</span>
                            </div>
                            <div class="flex-1 flex items-end justify-between gap-2 h-[130px] border-b border-[#F0EBE0] px-1">
                                @foreach($adRevenue as [$rvMonth, $rvPct])
                                <div class="flex-1 flex items-end justify-center h-full">
                                    <div class="w-full max-w-[26px] bg-[#14652F] rounded-t-[3px]" style="height:{{ $rvPct }}%"></div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-1.5 ml-7 flex items-center justify-between gap-2 px-1">
                            @foreach($adRevenue as [$rvMonth, $rvPct])
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
