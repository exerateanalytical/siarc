@php
    $isFr = $lang === 'fr';

    // Row shape: [ref, date, thumb, product, artisan, place, type, amount,
    // amountSub, status, statusSub, dateCol, expiry, detailUrl].
    // type/status keys: sent|received / received|nego|accepted|refused|waiting.
    // Second entry is the kit pill variant — the kit has no blue tone, so "sent"
    // reads as neutral rather than carrying its own colour.
    $typePills = [
        'sent'     => [$isFr ? 'Demande envoyée' : 'Request sent',        'ui-pill-neutral'],
        'received' => [$isFr ? 'Proposition reçue' : 'Proposal received', 'ui-pill-ok'],
    ];
    $statusPills = [
        'received' => [$isFr ? 'Proposition reçue' : 'Proposal received', 'ui-pill-ok'],
        'nego'     => [$isFr ? 'En négociation' : 'In negotiation',       'ui-pill-warn'],
        'accepted' => [$isFr ? 'Acceptée' : 'Accepted',                   'ui-pill-ok'],
        'refused'  => [$isFr ? 'Refusée' : 'Refused',                     'ui-pill-danger'],
        'waiting'  => [$isFr ? 'En attente' : 'Pending',                  'ui-pill-neutral'],
    ];

    // Real RFQs of the logged-in buyer. The design also shipped eight fixture
    // rows here (Atelier Nshome, Design Studio Paris, "180,000 FCFA"…) which
    // every buyer saw mixed into their own list; they are gone.
    $statusMap = ['pending' => 'waiting', 'quoted' => 'received', 'negotiation' => 'nego', 'accepted' => 'accepted', 'refused' => 'refused', 'expired' => 'waiting'];
    $rows = [];
    foreach (($realRequests ?? collect()) as $rr) {
        $latest = $rr->proposals->first();
        $rows[] = [
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
                : route('quotes.request-detail', ['lang' => $lang, 'rfq' => $rr->id]),
        ];
    }

    // Tabs filter the rows (?tab=), search filters by text (?q=). Counts are
    // derived from the buyer's own rows, never hardcoded.
    $countBy = fn (callable $f) => count(array_filter($rows, $f));
    $tabs = [
        ['toutes',       $isFr ? 'Toutes' : 'All',                            count($rows), fn ($r) => true],
        ['demandes',     $isFr ? 'Demandes envoyées' : 'Requests sent',        $countBy(fn ($r) => $r[6] === 'sent'), fn ($r) => $r[6] === 'sent'],
        ['propositions', $isFr ? 'Propositions reçues' : 'Proposals received', $countBy(fn ($r) => $r[6] === 'received'), fn ($r) => $r[6] === 'received'],
        ['negociation',  $isFr ? 'En négociation' : 'In negotiation',          $countBy(fn ($r) => $r[9] === 'nego'), fn ($r) => $r[9] === 'nego'],
        ['acceptees',    $isFr ? 'Acceptées' : 'Accepted',                     $countBy(fn ($r) => $r[9] === 'accepted'), fn ($r) => $r[9] === 'accepted'],
        ['refusees',     $isFr ? 'Refusées' : 'Refused',                       $countBy(fn ($r) => $r[9] === 'refused'), fn ($r) => $r[9] === 'refused'],
    ];
    $tab = request()->query('tab', 'toutes');
    $q = trim((string) request()->query('q', ''));
    $activeFilter = collect($tabs)->firstWhere(0, $tab) ?? $tabs[0];
    $visibleRows = array_values(array_filter($rows, function ($r) use ($activeFilter, $q) {
        if (! $activeFilter[3]($r)) return false;
        if ($q !== '' && stripos($r[0] . ' ' . $r[3] . ' ' . $r[4], $q) === false) return false;
        return true;
    }));

    // Résumé cards, computed from the buyer's own rows: [label, labelColor,
    // value, icon, iconColor, bg]
    $cSent     = $countBy(fn ($r) => $r[6] === 'sent');
    $cReceived = $countBy(fn ($r) => $r[6] === 'received');
    $cNego     = $countBy(fn ($r) => $r[9] === 'nego');
    $cAccepted = $countBy(fn ($r) => $r[9] === 'accepted');
    $cRefused  = $countBy(fn ($r) => $r[9] === 'refused');
    $cWaiting  = $countBy(fn ($r) => $r[9] === 'waiting');
    $spendTotal = ($realRequests ?? collect())
        ->flatMap(fn ($rr) => $rr->proposals->where('status', 'accepted'))
        ->sum('total');

    $resumeCards = [
        [$isFr ? 'Demandes envoyées' : 'Requests sent',       '#1B1B18', (string) $cSent,     'trending-up',    '#3565DE', '#EEF1FC'],
        [$isFr ? 'Propositions reçues' : 'Proposals received', '#1B1B18', (string) $cReceived, 'badge-check',    '#157A43', '#EFF6F1'],
        [$isFr ? 'En négociation' : 'In negotiation',         '#C97A16', (string) $cNego,     'message-circle', '#E8890C', '#FDF4E7'],
        [$isFr ? 'Acceptées' : 'Accepted',                    '#1B1B18', (string) $cAccepted, 'file-check-2',   '#157A43', '#F3F7F4'],
        [$isFr ? 'Refusées' : 'Refused',                      '#B42025', (string) $cRefused,  'x-circle',       '#E5484D', '#FDE8E8'],
        [$isFr ? 'Total engagé' : 'Total committed',          '#0F766E', number_format((int) $spendTotal, 0, ',', ' ') . ' FCFA', 'upload', '#0F766E', '#EBF5F3'],
    ];

    // Donut legend: [label, pct, count, color] — proportional to real counts.
    $donutTotal = max(1, count($rows));
    $pct = fn ($n) => round($n / $donutTotal * 100, 1);
    $donutParts = [
        [$isFr ? 'Propositions reçues' : 'Proposals received', $pct($cReceived) . '%', $cReceived, '#1F8A4C'],
        [$isFr ? 'En négociation' : 'In negotiation',          $pct($cNego) . '%',     $cNego,     '#F5A623'],
        [$isFr ? 'Acceptées' : 'Accepted',                     $pct($cAccepted) . '%', $cAccepted, '#14652F'],
        [$isFr ? 'En attente' : 'Pending',                     $pct($cWaiting) . '%',  $cWaiting,  '#B9BEC4'],
        [$isFr ? 'Refusées' : 'Refused',                       $pct($cRefused) . '%',  $cRefused,  '#E5484D'],
    ];
    // Build the conic-gradient stops cumulatively so the ring matches the legend.
    $donutCss = 'conic-gradient(';
    $acc = 0; $stops = [];
    foreach ($donutParts as [, $dpPct, $dpCount, $dpColor]) {
        $slice = $pct($dpCount);
        $stops[] = $dpColor . ' ' . $acc . '% ' . ($acc + $slice) . '%';
        $acc += $slice;
    }
    if ($acc < 100) { $stops[] = '#EDEDEB ' . $acc . '% 100%'; }
    $donutCss .= implode(', ', $stops) . ')';

    $quickActions = [
        ['circle-plus', $isFr ? 'Créer une demande de devis' : 'Create a quote request', route('quotes.create', ['lang' => $lang]), true],
        ['shopping-bag', $isFr ? 'Voir mes commandes' : 'View my orders',                route('orders.index', ['lang' => $lang]), false],
        ['message-circle', $isFr ? 'Mes messages' : 'My messages',                       route('messages.inbox', ['lang' => $lang]), false],
        ['user-cog',     $isFr ? 'Mon profil' : 'My profile',                            route('profile.show', ['lang' => $lang]), false],
    ];

    $selfUrl = fn (array $extra = []) => route('quotes.index', array_merge(['lang' => $lang, 'tab' => $tab], $q !== '' ? ['q' => $q] : [], $extra));
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Mes Demandes & Propositions — Artisan Hub 237' : 'My Requests & Proposals — Artisan Hub 237' }}</title>

    @include('pages.partials.icons')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qb-sidebar { display: none; }
        #qb-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; background: #fff; }
        @media (min-width: 1024px) { #qb-sidebar, #qb-sidebar.open { display: block; position: static; width: 264px; overflow-y: visible; } }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#F7F8F7] dark:bg-[#12150F] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

@php $qbSearchPlaceholder = $isFr ? 'Rechercher (produits, artisans, demandes, devis...)' : 'Search (products, artisans, requests, quotes...)'; $qbCompanyFirst = true; @endphp
@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        @if(session('success'))
        <div class="mb-4 ui-alert ui-alert-ok">
            <i data-lucide="circle-check" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Title row -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Mes Demandes & Propositions' : 'My Requests & Proposals' }}</h1>
                <p class="mt-1 text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Gérez toutes vos demandes de devis et propositions reçues.' : 'Manage all your quote requests and received proposals.' }}</p>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Exporter' : 'Export' }}
                </button>
                <a href="{{ route('quotes.create', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">
                    {{ $isFr ? 'Créer une demande de devis' : 'Create a quote request' }}
                    <i data-lucide="plus" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">

            <!-- Listing column -->
            <div class="flex-1 min-w-0 w-full">
                <div class="ui-card ui-card--flush">
                    <!-- Tabs -->
                    <div class="px-5 pt-1 border-b border-[#F0F1F0] dark:border-[#262B21] overflow-x-auto">
                        <div class="flex items-center gap-7 min-w-max">
                            @foreach($tabs as [$tKey, $tLabel, $tCount])
                            <a href="{{ route('quotes.index', array_merge(['lang' => $lang, 'tab' => $tKey], $q !== '' ? ['q' => $q] : [])) }}"
                               class="relative py-4 text-[13px] whitespace-nowrap {{ $tab === $tKey ? 'font-bold text-[#14652F] dark:text-[#339B56] ' : 'text-[#55524A] dark:text-[#B4B5A6] hover:text-[#1B1B18] dark:hover:text-[#F3EFE7] ' }}">
                                {{ $tLabel }} ({{ $tCount }})
                                @if($tab === $tKey)<span class="absolute left-0 right-0 -bottom-px h-[3px] rounded-t bg-[#14652F] dark:bg-[#2E9250]"></span>@endif
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filter bar -->
                    <form method="GET" action="{{ route('quotes.index') }}" class="px-5 py-4 flex flex-wrap items-end gap-3">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <div class="flex-1 min-w-[240px]">
                            <div class="ui-field-group">
                                <input type="text" name="q" value="{{ $q }}" placeholder="{{ $isFr ? 'Rechercher par référence, produit, artisan, etc...' : 'Search by reference, product, artisan, etc...' }}" class="ui-field-bare">
                                <button type="submit" class="shrink-0 text-[#3B382F] dark:text-[#B4B5A6]"><i data-lucide="search" class="w-[18px] h-[18px]"></i></button>
                            </div>
                        </div>
                        <div class="w-[170px]">
                            <label class="ui-label">{{ $isFr ? 'Statut' : 'Status' }}</label>
                            <select name="tab" onchange="this.form.submit()" class="ui-field ui-select">
                                <option value="toutes">{{ $isFr ? 'Tous les statuts' : 'All statuses' }}</option>
                                @foreach(array_slice($tabs, 1) as [$tKey, $tLabel, $tCount])
                                <option value="{{ $tKey }}" {{ $tab === $tKey ? 'selected' : '' }}>{{ $tLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-[170px]">
                            <label class="ui-label">{{ $isFr ? 'Période' : 'Period' }}</label>
                            <select name="periode" class="ui-field ui-select">
                                <option>{{ $isFr ? '30 derniers jours' : 'Last 30 days' }}</option>
                                <option>{{ $isFr ? '90 derniers jours' : 'Last 90 days' }}</option>
                                <option>{{ $isFr ? 'Cette année' : 'This year' }}</option>
                            </select>
                        </div>
                        <button type="submit" class="ui-btn ui-btn-secondary">
                            <i data-lucide="filter" class="w-4 h-4" style="stroke-width:1.8"></i>
                            {{ $isFr ? 'Filtres avancés' : 'Advanced filters' }}
                        </button>
                    </form>

                    <!-- Table -->
                    <div class="ui-table-wrap">
                        <table class="ui-table min-w-[980px]">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" class="ui-check"></th>
                                    <th>{{ $isFr ? 'Référence' : 'Reference' }}</th>
                                    <th>{{ $isFr ? 'Produits / Artisan' : 'Products / Artisan' }}</th>
                                    <th>Type</th>
                                    <th>{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                    <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visibleRows as $qRow)
                                @php
                                    [$ref, $refDate, $thumb, $product, $artisan, $place, $type, $amount, $amountSub, $status, $statusSub, $dateCol, $expiry] = $qRow;
                                    $rowUrl = $qRow[13] ?? route('messages.inbox', ['lang' => $lang]);
                                @endphp
                                <tr>
                                    <td class="align-top"><input type="checkbox" class="ui-check mt-1"></td>
                                    <td class="align-top whitespace-nowrap">
                                        <p class="font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $ref }}</p>
                                        <p class="mt-1 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $refDate }}</p>
                                    </td>
                                    <td class="align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $thumb) }}" alt="" class="w-[34px] h-[44px] shrink-0 rounded-md object-cover">
                                            <div class="min-w-0">
                                                <p class="font-bold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ $product }}</p>
                                                <p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778] whitespace-nowrap">{{ $artisan }}</p>
                                                <p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778] whitespace-nowrap">{{ $place }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-top">
                                        <span class="ui-pill {{ $typePills[$type][1] }}">{{ $typePills[$type][0] }}</span>
                                    </td>
                                    <td class="align-top whitespace-nowrap">
                                        <p class="font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $amount }}</p>
                                        <p class="mt-1 text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $amountSub }}</p>
                                    </td>
                                    <td class="align-top">
                                        <span class="ui-pill {{ $statusPills[$status][1] }}">{{ $statusPills[$status][0] }}</span>
                                        @if($statusSub)<p class="mt-1.5 text-[12px] text-[#55524A] dark:text-[#B4B5A6] whitespace-nowrap">{{ $statusSub }}</p>@endif
                                    </td>
                                    <td class="align-top whitespace-nowrap">
                                        <p class="font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $dateCol }}</p>
                                        <p class="mt-1 text-[12px] {{ $expiry === '-' ? 'text-[#6F6B60] dark:text-[#868778] ' : 'font-semibold text-[#E8890C] dark:text-[#EDB33A] ' }}">{{ $expiry }}</p>
                                    </td>
                                    <td class="align-top">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ $rowUrl }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="ui-btn ui-btn-secondary w-[38px] px-0">
                                                <i data-lucide="eye" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                                            </a>
                                            <a href="{{ $rowUrl }}" title="Actions" class="ui-btn ui-btn-secondary w-[38px] px-0">
                                                <i data-lucide="ellipsis-vertical" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="ui-empty">{{ $isFr ? 'Aucune demande dans cette catégorie.' : 'No request in this category.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-[12.5px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Affichage de 1 à 8 sur 18 résultats' : 'Showing 1 to 8 of 18 results' }}</p>
                    <div class="flex items-center gap-2">
                        <a href="{{ $selfUrl() }}" class="ui-btn ui-btn-secondary">{{ $isFr ? 'Précédent' : 'Previous' }}</a>
                        <a href="{{ $selfUrl(['page' => 1]) }}" class="ui-btn ui-btn-primary w-[38px] px-0">1</a>
                        <a href="{{ $selfUrl(['page' => 2]) }}" class="ui-btn ui-btn-secondary w-[38px] px-0">2</a>
                        <a href="{{ $selfUrl(['page' => 3]) }}" class="ui-btn ui-btn-secondary w-[38px] px-0">3</a>
                        <a href="{{ $selfUrl(['page' => 2]) }}" class="ui-btn ui-btn-secondary">{{ $isFr ? 'Suivant' : 'Next' }}</a>
                    </div>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[330px] shrink-0 space-y-4">
                <section class="ui-card">
                    <div class="ui-card-head">
                        <h2 class="ui-card-title">{{ $isFr ? 'Résumé de mes activités' : 'Summary of my activities' }}</h2>
                        <span class="shrink-0 inline-flex items-center gap-1.5 border border-[#E5E7E5] dark:border-[#262B21] rounded-lg px-2.5 py-1.5 text-[11.5px] text-[#3B382F] dark:text-[#B4B5A6]">
                            {{ $isFr ? '30 derniers jours' : 'Last 30 days' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($resumeCards as [$rcLabel, $rcLabelColor, $rcValue, $rcIcon, $rcIconColor, $rcBg])
                        <div class="rounded-xl p-3.5" style="background:{{ $rcBg }}">
                            <p class="text-[11.5px] font-semibold leading-snug" style="color:{{ $rcLabelColor }}">{{ $rcLabel }}</p>
                            <div class="mt-1.5 flex items-end justify-between gap-2">
                                <p class="text-[19px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight {{ strlen($rcValue) > 5 ? 'text-[15px]' : '' }}">{{ $rcValue }}</p>
                                <i data-lucide="{{ $rcIcon }}" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.7;color:{{ $rcIconColor }}"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Répartition des statuts' : 'Status breakdown' }}</h2>
                    <div class="mt-5 flex items-center gap-5">
                        <div class="relative w-[118px] h-[118px] shrink-0 rounded-full" style="background:{{ $donutCss }}">
                            <div class="absolute inset-[17px] rounded-full bg-white dark:bg-[#12150F]"></div>
                        </div>
                        <ul class="flex-1 min-w-0 space-y-2.5">
                            @foreach($donutParts as [$dpLabel, $dpPct, $dpCount, $dpColor])
                            <li class="flex items-center gap-2.5">
                                <span class="w-[11px] h-[11px] shrink-0 rounded-sm" style="background:{{ $dpColor }}"></span>
                                <span class="flex-1 text-[11.5px] text-[#3B382F] dark:text-[#B4B5A6] whitespace-nowrap">{{ $dpLabel }}</span>
                                <span class="shrink-0 text-[11.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $dpPct }} ({{ $dpCount }})</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-5 border-t border-[#F0F1F0] dark:border-[#262B21] pt-3.5 flex items-center justify-between">
                        <span class="text-[13px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">Total</span>
                        <span class="text-[13px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ count($rows) }}</span>
                    </div>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1] dark:divide-[#262B21]">
                        @foreach($quickActions as [$qaIcon, $qaLabel, $qaUrl, $qaArrow])
                        <a href="{{ $qaUrl }}" class="flex items-center gap-3.5 py-3.5 group">
                            <i data-lucide="{{ $qaIcon }}" class="w-[19px] h-[19px] shrink-0 text-[#3B382F] dark:text-[#B4B5A6]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $qaLabel }}</span>
                            @if($qaArrow)<i data-lucide="arrow-right" class="w-4 h-4 shrink-0 text-[#3B382F] dark:text-[#B4B5A6]"></i>@endif
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
