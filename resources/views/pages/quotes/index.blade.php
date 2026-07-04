@php
    $isFr = $lang === 'fr';

    // Design rows verbatim. [ref, date, thumb, product, artisan, place, type, amount, amountSub, status, statusSub, dateCol, expiry]
    // type/status keys: sent|received / received|nego|accepted|refused|waiting
    $rows = [
        ['ENQ-2024-000158', $isFr ? '12 Mai 2024' : '12 May 2024', 'qp-thumb-1.png', $isFr ? 'Masque traditionnel Bamileké' : 'Traditional Bamileke mask', 'Atelier Nshome Artisanat', 'Bafoussam, Ouest', 'sent', '180,000 FCFA', $isFr ? 'Estimation' : 'Estimate', 'received', $isFr ? '2 propositions' : '2 proposals', $isFr ? '12 Mai 2024' : '12 May 2024', $isFr ? 'Expire le 25 Mai 2024' : 'Expires 25 May 2024'],
        ['ENQ-2024-000157', $isFr ? '10 Mai 2024' : '10 May 2024', 'qp-thumb-2.png', $isFr ? 'Statue en bois - Fécondité' : 'Wooden statue - Fertility', 'Art Bois Nature', $isFr ? 'Yaoundé, Centre' : 'Yaounde, Centre', 'sent', '250,000 FCFA', $isFr ? 'Estimation' : 'Estimate', 'nego', $isFr ? '1 proposition' : '1 proposal', $isFr ? '10 Mai 2024' : '10 May 2024', $isFr ? 'Expire le 24 Mai 2024' : 'Expires 24 May 2024'],
        ['ENQ-2024-000156', $isFr ? '09 Mai 2024' : '09 May 2024', 'qp-thumb-3.png', $isFr ? 'Tabouret sculpté *' : 'Carved stool *', 'Les Ateliers du Noun', 'Foumbot, Ouest', 'sent', '75,000 FCFA', $isFr ? 'Estimation' : 'Estimate', 'received', $isFr ? '3 propositions' : '3 proposals', $isFr ? '09 Mai 2024' : '09 May 2024', $isFr ? 'Expire le 23 Mai 2024' : 'Expires 23 May 2024'],
        ['QUO-2024-000142', $isFr ? '08 Mai 2024' : '08 May 2024', 'qp-thumb-4.png', $isFr ? 'Coffre en bois décoratif' : 'Decorative wooden chest', 'Heritage Decor', 'Douala, Littoral', 'received', '210,000 FCFA', 'Proposition', 'nego', $isFr ? 'V2 - En attente' : 'V2 - Pending', $isFr ? '08 Mai 2024' : '08 May 2024', $isFr ? 'Expire le 18 Mai 2024' : 'Expires 18 May 2024'],
        ['QUO-2024-000139', $isFr ? '07 Mai 2024' : '07 May 2024', 'qp-thumb-5.png', $isFr ? 'Collier perles artisanales' : 'Artisanal bead necklace', 'Perles du Cameroun', $isFr ? 'Maroua, Extrême-Nord' : 'Maroua, Far North', 'received', '45,000 FCFA', 'Proposition', 'accepted', $isFr ? 'Commande créée' : 'Order created', $isFr ? '07 Mai 2024' : '07 May 2024', '-'],
        ['ENQ-2024-000150', $isFr ? '06 Mai 2024' : '06 May 2024', 'qp-thumb-6.png', $isFr ? 'Décoration murale *' : 'Wall decoration *', 'Heritage Decor', 'Douala, Littoral', 'sent', '125,000 FCFA', $isFr ? 'Estimation' : 'Estimate', 'received', $isFr ? '1 proposition' : '1 proposal', $isFr ? '06 Mai 2024' : '06 May 2024', $isFr ? 'Expire le 20 Mai 2024' : 'Expires 20 May 2024'],
        ['QUO-2024-000137', $isFr ? '05 Mai 2024' : '05 May 2024', 'qp-thumb-7.png', $isFr ? 'Mobilier de bureau en bois' : 'Wooden office furniture', 'Design Studio Paris', 'Paris, France', 'received', '350,000 FCFA', 'Proposition', 'refused', null, $isFr ? '05 Mai 2024' : '05 May 2024', '-'],
        ['ENQ-2024-000148', $isFr ? '04 Mai 2024' : '04 May 2024', 'qp-thumb-8.png', $isFr ? 'Paniers tressés *' : 'Woven baskets *', "Terres d'Artisanat", 'Bamenda, Nord-Ouest', 'sent', '60,000 FCFA', $isFr ? 'Estimation' : 'Estimate', 'waiting', $isFr ? '0 proposition' : '0 proposals', $isFr ? '04 Mai 2024' : '04 May 2024', $isFr ? 'Expire le 18 Mai 2024' : 'Expires 18 May 2024'],
    ];

    $typePills = [
        'sent'     => [$isFr ? 'Demande envoyée' : 'Request sent',       '#3565DE', '#E8EFFB'],
        'received' => [$isFr ? 'Proposition reçue' : 'Proposal received', '#157A43', '#E2F3E8'],
    ];
    $statusPills = [
        'received' => [$isFr ? 'Proposition reçue' : 'Proposal received', '#157A43', '#E2F3E8'],
        'nego'     => [$isFr ? 'En négociation' : 'In negotiation',       '#E8890C', '#FDF0DC'],
        'accepted' => [$isFr ? 'Acceptée' : 'Accepted',                   '#157A43', '#E2F3E8'],
        'refused'  => [$isFr ? 'Refusée' : 'Refused',                     '#E5484D', '#FDE8E8'],
        'waiting'  => [$isFr ? 'En attente' : 'Pending',                  '#55524A', '#F0F1F2'],
    ];

    // Real RFQs of the logged-in buyer, mapped into the same row shape and
    // rendered AHEAD of the design demo rows (index 13 = real detail URL).
    $statusMap = ['pending' => 'waiting', 'quoted' => 'received', 'negotiation' => 'nego', 'accepted' => 'accepted', 'refused' => 'refused', 'expired' => 'waiting'];
    $realRows = [];
    foreach (($realRequests ?? collect()) as $rr) {
        $latest = $rr->proposals->first();
        $realRows[] = [
            $rr->reference,
            $rr->created_at->format('d/m/Y'),
            'qp-thumb-' . (($rr->id % 8) + 1) . '.png',
            $rr->title,
            $rr->business->name_fr ?? '—',
            $isFr ? 'Cameroun' : 'Cameroon',
            $latest ? 'received' : 'sent',
            $latest ? number_format($latest->total) . ' FCFA' : '—',
            $latest ? 'Proposition' : ($isFr ? 'Estimation' : 'Estimate'),
            $statusMap[$rr->status] ?? 'waiting',
            $rr->proposals->count() . ' proposition' . ($rr->proposals->count() > 1 ? 's' : ''),
            $rr->created_at->format('d/m/Y'),
            $latest && $latest->valid_until ? (($isFr ? 'Expire le ' : 'Expires ') . $latest->valid_until->format('d/m/Y')) : '-',
            $latest
                ? route('quotes.detail', ['lang' => $lang, 'proposal' => $latest->id])
                : route('messages.inbox', ['lang' => $lang]),
        ];
    }
    $rows = array_merge($realRows, $rows);

    // Tabs filter the design rows (?tab=), search filters by text (?q=)
    $tabs = [
        ['toutes',       $isFr ? 'Toutes' : 'All',                          18, fn ($r) => true],
        ['demandes',     $isFr ? 'Demandes envoyées' : 'Requests sent',      8, fn ($r) => $r[6] === 'sent'],
        ['propositions', $isFr ? 'Propositions reçues' : 'Proposals received', 6, fn ($r) => $r[6] === 'received'],
        ['negociation',  $isFr ? 'En négociation' : 'In negotiation',        3, fn ($r) => $r[9] === 'nego'],
        ['acceptees',    $isFr ? 'Acceptées' : 'Accepted',                   1, fn ($r) => $r[9] === 'accepted'],
        ['refusees',     $isFr ? 'Refusées' : 'Refused',                     0, fn ($r) => $r[9] === 'refused'],
        ['expirees',     $isFr ? 'Expirées' : 'Expired',                     0, fn ($r) => false],
    ];
    $tab = request()->query('tab', 'toutes');
    $q = trim((string) request()->query('q', ''));
    $activeFilter = collect($tabs)->firstWhere(0, $tab) ?? $tabs[0];
    $visibleRows = array_values(array_filter($rows, function ($r) use ($activeFilter, $q) {
        if (! $activeFilter[3]($r)) return false;
        if ($q !== '' && stripos($r[0] . ' ' . $r[3] . ' ' . $r[4], $q) === false) return false;
        return true;
    }));

    // Résumé cards: [label, labelColor, value, delta, deltaColor, icon, iconColor, bg]
    $resumeCards = [
        [$isFr ? 'Demandes envoyées' : 'Requests sent',      '#1B1B18', '8', '+14%',  '#3565DE', 'trending-up',    '#3565DE', '#EEF1FC'],
        [$isFr ? 'Propositions reçues' : 'Proposals received','#1B1B18', '6', '+20%',  '#157A43', 'badge-check',    '#157A43', '#EFF6F1'],
        [$isFr ? 'En négociation' : 'In negotiation',        '#C97A16', '3', '-10%',  '#E8890C', 'message-circle', '#E8890C', '#FDF4E7'],
        [$isFr ? 'Acceptées' : 'Accepted',                   '#1B1B18', '1', '+100%', '#157A43', 'file-check-2',   '#157A43', '#F3F7F4'],
        [$isFr ? 'Commandes créées' : 'Orders created',      '#7C4FE0', '1', '+100%', '#7C4FE0', 'shopping-bag',   '#7C4FE0', '#F1EFFA'],
        [$isFr ? 'Total dépensé' : 'Total spent',            '#0F766E', '210,000 FCFA', '+100%', '#157A43', 'upload', '#0F766E', '#EBF5F3'],
    ];

    // Donut legend: [label, pct, count, color]
    $donutParts = [
        [$isFr ? 'Propositions reçues' : 'Proposals received', '33.3%', 6, '#1F8A4C'],
        [$isFr ? 'En négociation' : 'In negotiation',          '16.7%', 3, '#F5A623'],
        [$isFr ? 'Acceptées' : 'Accepted',                     '5.6%',  1, '#14652F'],
        [$isFr ? 'En attente' : 'Pending',                     '38.9%', 7, '#B9BEC4'],
        [$isFr ? 'Refusées' : 'Refused',                       '5.6%',  1, '#E5484D'],
    ];
    $donutCss = 'conic-gradient(#1F8A4C 0 33.3%, #F5A623 33.3% 50%, #14652F 50% 55.6%, #B9BEC4 55.6% 94.4%, #E5484D 94.4% 100%)';

    $quickActions = [
        ['circle-plus', $isFr ? 'Créer une demande de devis' : 'Create a quote request', route('quotes.create', ['lang' => $lang]), true],
        ['shopping-bag', $isFr ? 'Voir mes commandes' : 'View my orders',                route('messages.inbox', ['lang' => $lang]), false],
        ['map-pin',      $isFr ? 'Mes adresses de livraison' : 'My delivery addresses',  route('profile.show', ['lang' => $lang]), false],
        ['credit-card',  $isFr ? 'Mes moyens de paiement' : 'My payment methods',        route('profile.show', ['lang' => $lang]), false],
    ];

    $selfUrl = fn (array $extra = []) => route('quotes.index', array_merge(['lang' => $lang, 'tab' => $tab], $q !== '' ? ['q' => $q] : [], $extra));
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Mes Demandes & Propositions — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'My Requests & Proposals — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } }
        }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qb-sidebar { display: none; }
        #qb-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; background: #fff; }
        @media (min-width: 1024px) { #qb-sidebar, #qb-sidebar.open { display: block; position: static; width: 264px; overflow-y: visible; } }
    </style>
</head>
<body class="bg-[#F7F8F7] text-[#1B1B18] antialiased">

@php $qbSearchPlaceholder = $isFr ? 'Rechercher (produits, artisans, demandes, devis...)' : 'Search (products, artisans, requests, quotes...)'; $qbCompanyFirst = true; @endphp
@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        @if(session('success'))
        <div class="mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]">
            <i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Title row -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Mes Demandes & Propositions' : 'My Requests & Proposals' }}</h1>
                <p class="mt-1 text-[13px] text-[#55524A]">{{ $isFr ? 'Gérez toutes vos demandes de devis et propositions reçues.' : 'Manage all your quote requests and received proposals.' }}</p>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#14652F] transition-colors">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Exporter' : 'Export' }}
                </button>
                <a href="{{ route('quotes.create', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-white transition-colors">
                    {{ $isFr ? 'Créer une demande de devis' : 'Create a quote request' }}
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">

            <!-- Listing column -->
            <div class="flex-1 min-w-0 w-full">
                <div class="bg-white border border-[#EFF0EF] rounded-2xl">
                    <!-- Tabs -->
                    <div class="px-5 pt-1 border-b border-[#F0F1F0] overflow-x-auto">
                        <div class="flex items-center gap-7 min-w-max">
                            @foreach($tabs as [$tKey, $tLabel, $tCount])
                            <a href="{{ route('quotes.index', array_merge(['lang' => $lang, 'tab' => $tKey], $q !== '' ? ['q' => $q] : [])) }}"
                               class="relative py-4 text-[13px] whitespace-nowrap {{ $tab === $tKey ? 'font-bold text-[#14652F]' : 'text-[#55524A] hover:text-[#1B1B18]' }}">
                                {{ $tLabel }} ({{ $tCount }})
                                @if($tab === $tKey)<span class="absolute left-0 right-0 -bottom-px h-[3px] rounded-t bg-[#14652F]"></span>@endif
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <form method="GET" action="{{ route('quotes.index') }}" class="px-5 py-4 flex flex-wrap items-end gap-3">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="flex-1 min-w-[240px]">
                            <div class="flex items-center gap-3 h-[46px] border border-[#E5E7E5] rounded-lg px-4 focus-within:border-[#14532D]">
                                <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher par référence, produit, artisan, etc...' : 'Search by reference, product, artisan, etc...' }}" class="flex-1 min-w-0 text-[13px] focus:outline-none placeholder-[#8A857A]">
                                <button type="submit" class="shrink-0 text-[#3B382F]"><i data-lucide="search" class="w-[18px] h-[18px]"></i></button>
                            </div>
                        </div>
                        <div class="w-[170px]">
                            <label class="block text-[11px] text-[#6F6B60] mb-1.5">{{ $isFr ? 'Statut' : 'Status' }}</label>
                            <div class="relative">
                                <select name="tab" onchange="this.form.submit()" class="w-full h-[46px] border border-[#E5E7E5] rounded-lg pl-4 pr-8 text-[13px] bg-white appearance-none cursor-pointer focus:outline-none">
                                    <option value="toutes">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                                    @foreach(array_slice($tabs, 1) as [$tKey, $tLabel, $tCount])
                                    <option value="{{ $tKey }}" {{ $tab === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i>
                            </div>
                        </div>
                        <div class="w-[170px]">
                            <label class="block text-[11px] text-[#6F6B60] mb-1.5">{{ $isFr ? 'Période' : 'Period' }}</label>
                            <div class="relative">
                                <select name="periode" class="w-full h-[46px] border border-[#E5E7E5] rounded-lg pl-4 pr-8 text-[13px] bg-white appearance-none cursor-pointer focus:outline-none">
                                    <option>{{ $isFr ? '30 derniers jours' : 'Last 30 days' }}</option>
                                    <option>{{ $isFr ? '90 derniers jours' : 'Last 90 days' }}</option>
                                    <option>{{ $isFr ? 'Cette année' : 'This year' }}</option>
                                </select>
                                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A] pointer-events-none"></i>
                            </div>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2.5 h-[46px] border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                            <i data-lucide="filter" class="w-4 h-4" style="stroke-width:1.8"></i>
                            {{ $isFr ? 'Filtres avancés' : 'Advanced filters' }}
                        </button>
                    </form>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[980px]">
                            <thead>
                                <tr class="bg-[#F7F8F7] text-left">
                                    <th class="pl-5 pr-2 py-3"><input type="checkbox" class="w-4 h-4 rounded border-[#C9CFC9] accent-[#14652F]"></th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Référence' : 'Reference' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Produits / Artisan' : 'Products / Artisan' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Type</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Date</th>
                                    <th class="px-2 pr-5 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F2F1]">
                                @forelse($visibleRows as $qRow)
                                @php
                                    [$ref, $refDate, $thumb, $product, $artisan, $place, $type, $amount, $amountSub, $status, $statusSub, $dateCol, $expiry] = $qRow;
                                    $rowUrl = $qRow[13] ?? route('messages.inbox', ['lang' => $lang]);
                                @endphp
                                <tr>
                                    <td class="pl-5 pr-2 py-4 align-top"><input type="checkbox" class="w-4 h-4 mt-1 rounded border-[#C9CFC9] accent-[#14652F]"></td>
                                    <td class="px-2 py-4 align-top whitespace-nowrap">
                                        <p class="text-[13px] font-bold text-[#1B1B18]">{{ $ref }}</p>
                                        <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $refDate }}</p>
                                    </td>
                                    <td class="px-2 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $thumb) }}" alt="" class="w-[34px] h-[44px] shrink-0 rounded-md object-cover">
                                            <div class="min-w-0">
                                                <p class="text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $product }}</p>
                                                <p class="mt-0.5 text-[12px] text-[#6F6B60] whitespace-nowrap">{{ $artisan }}</p>
                                                <p class="mt-0.5 text-[12px] text-[#6F6B60] whitespace-nowrap">{{ $place }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-4 align-top">
                                        <span class="inline-block rounded-md px-3 py-1.5 text-[11.5px] font-semibold whitespace-nowrap" style="color:{{ $typePills[$type][1] }};background:{{ $typePills[$type][2] }}">{{ $typePills[$type][0] }}</span>
                                    </td>
                                    <td class="px-2 py-4 align-top whitespace-nowrap">
                                        <p class="text-[13.5px] font-bold text-[#1B1B18]">{{ $amount }}</p>
                                        <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $amountSub }}</p>
                                    </td>
                                    <td class="px-2 py-4 align-top">
                                        <span class="inline-block rounded-md px-3 py-1.5 text-[11.5px] font-bold whitespace-nowrap" style="color:{{ $statusPills[$status][1] }};background:{{ $statusPills[$status][2] }}">{{ $statusPills[$status][0] }}</span>
                                        @if($statusSub)<p class="mt-1.5 text-[12px] text-[#55524A] whitespace-nowrap">{{ $statusSub }}</p>@endif
                                    </td>
                                    <td class="px-2 py-4 align-top whitespace-nowrap">
                                        <p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $dateCol }}</p>
                                        <p class="mt-1 text-[12px] {{ $expiry === '-' ? 'text-[#6F6B60]' : 'font-semibold text-[#E8890C]' }}">{{ $expiry }}</p>
                                    </td>
                                    <td class="px-2 pr-5 py-4 align-top">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $rowUrl }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="w-[38px] h-[38px] rounded-lg border border-[#EAEBEA] hover:border-[#14532D] flex items-center justify-center text-[#3B382F] transition-colors">
                                                <i data-lucide="eye" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                                            </a>
                                            <a href="{{ $rowUrl }}" title="Actions" class="w-[38px] h-[38px] rounded-lg border border-[#EAEBEA] hover:border-[#14532D] flex items-center justify-center text-[#3B382F] transition-colors">
                                                <i data-lucide="ellipsis-vertical" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="px-5 py-10 text-center text-[13px] text-[#6F6B60]">{{ $isFr ? 'Aucune demande dans cette catégorie.' : 'No request in this category.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Affichage de 1 à 8 sur 18 résultats' : 'Showing 1 to 8 of 18 results' }}</p>
                    <div class="flex items-center gap-2">
                        <a href="{{ $selfUrl() }}" class="h-[40px] px-4 inline-flex items-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg text-[12.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Précédent' : 'Previous' }}</a>
                        <a href="{{ $selfUrl(['page' => 1]) }}" class="w-[40px] h-[40px] inline-flex items-center justify-center bg-[#0E5A2D] rounded-lg text-[13px] font-bold text-white">1</a>
                        <a href="{{ $selfUrl(['page' => 2]) }}" class="w-[40px] h-[40px] inline-flex items-center justify-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg text-[13px] font-semibold text-[#1B1B18] transition-colors">2</a>
                        <a href="{{ $selfUrl(['page' => 3]) }}" class="w-[40px] h-[40px] inline-flex items-center justify-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg text-[13px] font-semibold text-[#1B1B18] transition-colors">3</a>
                        <a href="{{ $selfUrl(['page' => 2]) }}" class="h-[40px] px-4 inline-flex items-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg text-[12.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Suivant' : 'Next' }}</a>
                    </div>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[330px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de mes activités' : 'Summary of my activities' }}</h2>
                        <span class="shrink-0 inline-flex items-center gap-1.5 border border-[#E5E7E5] rounded-lg px-2.5 py-1.5 text-[11.5px] text-[#3B382F]">
                            {{ $isFr ? '30 derniers jours' : 'Last 30 days' }}
                        </span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @foreach($resumeCards as [$rcLabel, $rcLabelColor, $rcValue, $rcDelta, $rcDeltaColor, $rcIcon, $rcIconColor, $rcBg])
                        <div class="rounded-xl p-3.5" style="background:{{ $rcBg }}">
                            <p class="text-[11.5px] font-semibold leading-snug" style="color:{{ $rcLabelColor }}">{{ $rcLabel }}</p>
                            <p class="mt-1.5 text-[19px] font-bold text-[#1B1B18] leading-tight {{ strlen($rcValue) > 5 ? 'text-[15px]' : '' }}">{{ $rcValue }}</p>
                            <div class="mt-1.5 flex items-center justify-between gap-2">
                                <span class="text-[11.5px] font-bold" style="color:{{ $rcDeltaColor }}">{{ $rcDelta }}</span>
                                <i data-lucide="{{ $rcIcon }}" class="w-[17px] h-[17px]" style="stroke-width:1.7;color:{{ $rcIconColor }}"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Répartition des statuts' : 'Status breakdown' }}</h2>
                    <div class="mt-5 flex items-center gap-5">
                        <div class="relative w-[118px] h-[118px] shrink-0 rounded-full" style="background:{{ $donutCss }}">
                            <div class="absolute inset-[17px] rounded-full bg-white"></div>
                        </div>
                        <ul class="flex-1 min-w-0 space-y-2.5">
                            @foreach($donutParts as [$dpLabel, $dpPct, $dpCount, $dpColor])
                            <li class="flex items-center gap-2.5">
                                <span class="w-[11px] h-[11px] shrink-0 rounded-sm" style="background:{{ $dpColor }}"></span>
                                <span class="flex-1 text-[11.5px] text-[#3B382F] whitespace-nowrap">{{ $dpLabel }}</span>
                                <span class="shrink-0 text-[11.5px] font-bold text-[#1B1B18]">{{ $dpPct }} ({{ $dpCount }})</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-5 border-t border-[#F0F1F0] pt-3.5 flex items-center justify-between">
                        <span class="text-[13px] font-bold text-[#1B1B18]">Total</span>
                        <span class="text-[13px] font-bold text-[#1B1B18]">18</span>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($quickActions as [$qaIcon, $qaLabel, $qaUrl, $qaArrow])
                        <a href="{{ $qaUrl }}" class="flex items-center gap-3.5 py-3.5 group">
                            <i data-lucide="{{ $qaIcon }}" class="w-[19px] h-[19px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qaLabel }}</span>
                            @if($qaArrow)<i data-lucide="arrow-right" class="w-4 h-4 shrink-0 text-[#3B382F]"></i>@endif
                        </a>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
