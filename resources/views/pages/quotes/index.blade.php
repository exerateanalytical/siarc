@php
    $isFr = $lang === 'fr';

    // Row shape: [ref, date, thumb, product, artisan, place, type, amount,
    // amountSub, status, statusSub, dateCol, expiry, detailUrl].
    // type/status keys: sent|received / received|nego|accepted|refused|waiting
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
                        @foreach($resumeCards as [$rcLabel, $rcLabelColor, $rcValue, $rcIcon, $rcIconColor, $rcBg])
                        <div class="rounded-xl p-3.5" style="background:{{ $rcBg }}">
                            <p class="text-[11.5px] font-semibold leading-snug" style="color:{{ $rcLabelColor }}">{{ $rcLabel }}</p>
                            <div class="mt-1.5 flex items-end justify-between gap-2">
                                <p class="text-[19px] font-bold text-[#1B1B18] leading-tight {{ strlen($rcValue) > 5 ? 'text-[15px]' : '' }}">{{ $rcValue }}</p>
                                <i data-lucide="{{ $rcIcon }}" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.7;color:{{ $rcIconColor }}"></i>
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
