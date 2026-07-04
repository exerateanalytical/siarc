@extends('layouts.admin')

@php
    $isFr = ($lang ?? 'fr') === 'fr';

    // ---- KPI cards (driven by $anKpis) -------------------------------------
    // [icon, iconColor, tileBg, label, valueRaw, isMoney, sparkColor, sparkFill]
    $an = $anKpis ?? [];
    $anRevenue = $an['revenue'] ?? null;
    $anKpiCards = [
        ['coins',        '#157A43', '#E8F2EC', ($isFr ? 'REVENU (FCFA)'        : 'REVENUE (FCFA)'),      $anRevenue,               true,  '#2F7D4E', '#EAF4EE'],
        ['store',        '#2563EB', '#E7EEFD', ($isFr ? 'ENTREPRISES'          : 'BUSINESSES'),          $an['businesses'] ?? null, false, '#2563EB', '#E9EFFD'],
        ['users-round',  '#C97A16', '#FDF3E0', ($isFr ? 'UTILISATEURS'         : 'USERS'),               $an['users'] ?? null,      false, '#E09A2A', '#FCF2E1'],
        ['package',      '#7C4FE0', '#F0EAFB', ($isFr ? 'PRODUITS'             : 'PRODUCTS'),            $an['products'] ?? null,   false, '#7C4FE0', '#F1ECFB'],
        ['calendar-days','#0D9488', '#E1F4F1', ($isFr ? 'ÉVÉNEMENTS'           : 'EVENTS'),              $an['events'] ?? null,     false, '#0D9488', '#E4F4F1'],
        ['eye',          '#DC2626', '#FDECEC', ($isFr ? 'VUES TOTALES'         : 'TOTAL VIEWS'),         $an['views'] ?? null,      false, '#DC2626', '#FCEBEB'],
    ];

    // ---- Growth series (real) ---------------------------------------------
    $anRows = collect($anSeries ?? [])->map(fn ($r) => [
        'label' => $r['label'] ?? '—',
        'value' => (int) ($r['value'] ?? 0),
    ])->values();
    $anMax   = max(1, (int) $anRows->max('value'));
    $anCount = max(1, $anRows->count());

    // Chart geometry (viewBox 0 0 720 260, inner plot padded)
    $anPL = 44; $anPR = 16; $anPT = 18; $anPB = 34;
    $anPW = 720 - $anPL - $anPR;
    $anPH = 260 - $anPT - $anPB;
    $anPts = $anRows->map(function ($r, $i) use ($anPL, $anPW, $anPT, $anPH, $anMax, $anCount) {
        $x = $anCount > 1 ? $anPL + ($i / ($anCount - 1)) * $anPW : $anPL + $anPW / 2;
        $y = $anPT + $anPH - ($r['value'] / $anMax) * $anPH;
        return ['x' => round($x, 1), 'y' => round($y, 1), 'label' => $r['label'], 'value' => $r['value']];
    });
    $anLine = $anPts->map(fn ($p) => $p['x'] . ',' . $p['y'])->implode(' ');
    $anArea = $anPts->isNotEmpty()
        ? $anPts->first()['x'] . ',' . ($anPT + $anPH) . ' ' . $anLine . ' ' . $anPts->last()['x'] . ',' . ($anPT + $anPH)
        : '';
    // Y axis gridline labels (5 ticks) scaled to max
    $anTicks = collect(range(0, 4))->map(function ($i) use ($anMax, $anPT, $anPH) {
        $frac = $i / 4;
        return [
            'y'   => round($anPT + $anPH - $frac * $anPH, 1),
            'val' => round($anMax * $frac),
        ];
    });
    $anLastPoint = $anPts->last();

    // ---- Top categories (real) --------------------------------------------
    $anCats    = collect($anCategories ?? []);
    $anCatMax  = max(1, (int) $anCats->max('c'));
    $anCatBarColors = ['#0B4D2C', '#14652F', '#2F7D4E', '#4E9A6B', '#7BB894', '#A9D3BB'];
@endphp

@section('content')
<div class="pt-5">

    {{-- Page title + breadcrumb + date-range action row --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-[24px] font-bold text-[#1B1B18] leading-tight">{{ $isFr ? 'Tableau de Bord Analytique' : 'Analytics Dashboard' }}</h1>
            <nav class="mt-1.5 flex items-center gap-2 text-[12.5px] text-[#8A857A]">
                <span>{{ $isFr ? 'Accueil' : 'Home' }}</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Tableaux de Bord' : 'Dashboards' }}</span>
            </nav>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="h-[40px] inline-flex items-center gap-3 bg-white border border-[#E5E7E5] rounded-lg px-4 text-[12.5px] text-[#3B382F]">
                <i data-lucide="calendar" class="w-4 h-4 text-[#8A857A]"></i>
                <span class="text-[#8A857A]">{{ $isFr ? 'Période sélectionnée' : 'Selected period' }}</span>
                <span class="text-[#B9B4A9]">—</span>
            </div>
            <button type="button" class="h-[40px] inline-flex items-center gap-2 bg-[#0B4D2C] hover:bg-[#14652F] rounded-lg px-4 text-[12.5px] font-semibold text-white transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>{{ $isFr ? 'Actualiser' : 'Refresh' }}
            </button>
        </div>
    </div>

    {{-- KPI cards --}}
    <section class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach($anKpiCards as [$kIcon, $kColor, $kTile, $kLabel, $kValue, $kMoney, $kSpark, $kFill])
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-4 flex flex-col">
            <div class="flex items-center justify-between">
                <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $kTile }}">
                    <i data-lucide="{{ $kIcon }}" class="w-[20px] h-[20px]" style="color: {{ $kColor }};stroke-width:1.8"></i>
                </span>
            </div>
            <p class="mt-3 text-[11px] font-semibold tracking-[0.04em] text-[#8A857A] uppercase">{{ $kLabel }}</p>
            <p class="mt-1 text-[22px] font-bold text-[#1B1B18] leading-none">
                @if(is_numeric($kValue))
                    {{ number_format((float) $kValue) }}{{ $kMoney ? '' : '' }}
                @else
                    —
                @endif
            </p>
            <svg viewBox="0 0 120 26" class="mt-3 w-full h-[24px]" preserveAspectRatio="none" aria-hidden="true">
                <polyline points="0,20 15,15 30,17 45,9 60,13 75,6 90,10 105,4 120,7" fill="none" stroke="{{ $kSpark }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.9"/>
            </svg>
        </div>
        @endforeach
    </section>

    {{-- Second KPI strip: Abonnements actifs (from $anKpis) --}}
    <section class="mt-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 flex items-center gap-4">
            <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center bg-[#FBF3DE]">
                <i data-lucide="credit-card" class="w-[22px] h-[22px] text-[#C9942E]" style="stroke-width:1.8"></i>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Abonnements actifs' : 'Active subscriptions' }}</p>
                <p class="mt-0.5 text-[22px] font-bold text-[#1B1B18] leading-none">{{ is_numeric($an['subs_active'] ?? null) ? number_format((float) $an['subs_active']) : '—' }}</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 flex items-center gap-4">
            <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center bg-[#E8F2EC]">
                <i data-lucide="wallet" class="w-[22px] h-[22px] text-[#157A43]" style="stroke-width:1.8"></i>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Revenu total (FCFA)' : 'Total revenue (FCFA)' }}</p>
                <p class="mt-0.5 text-[22px] font-bold text-[#1B1B18] leading-none">{{ is_numeric($anRevenue) ? number_format((float) $anRevenue) . ' FCFA' : '—' }}</p>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 flex items-center gap-4">
            <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center bg-[#E7EEFD]">
                <i data-lucide="trending-up" class="w-[22px] h-[22px] text-[#2563EB]" style="stroke-width:1.8"></i>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold tracking-[0.04em] text-[#8A857A] uppercase">{{ $isFr ? 'Taux de conversion' : 'Conversion rate' }}</p>
                <p class="mt-0.5 text-[22px] font-bold text-[#B9B4A9] leading-none">—</p>
            </div>
        </div>
    </section>

    {{-- Main content: growth chart + repartition + top categories --}}
    <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1.5fr_1fr] gap-5 items-start">

        {{-- Growth chart --}}
        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Évolution des Entreprises' : 'Business Growth' }}</h2>
                    <p class="mt-0.5 text-[12px] text-[#8A857A]">{{ $isFr ? 'Nouvelles entreprises par mois' : 'New businesses per month' }}</p>
                </div>
                <span class="h-[34px] inline-flex items-center gap-2 bg-[#F6F7F6] border border-[#EDEEED] rounded-lg px-3 text-[12px] font-medium text-[#3B382F]">
                    {{ $isFr ? '6 derniers mois' : 'Last 6 months' }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                </span>
            </div>

            <div class="mt-4 relative">
                <svg viewBox="0 0 720 260" class="w-full h-auto" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="anArea" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#14652F" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#14652F" stop-opacity="0.02"/>
                        </linearGradient>
                    </defs>

                    {{-- Gridlines + Y labels --}}
                    @foreach($anTicks as $tick)
                    <line x1="{{ $anPL }}" y1="{{ $tick['y'] }}" x2="{{ 720 - $anPR }}" y2="{{ $tick['y'] }}" stroke="#F0F1F0" stroke-width="1"/>
                    <text x="{{ $anPL - 8 }}" y="{{ $tick['y'] + 3 }}" text-anchor="end" font-size="10" fill="#8A857A" font-family="Poppins, sans-serif">{{ number_format($tick['val']) }}</text>
                    @endforeach

                    {{-- Area + line --}}
                    @if($anArea)
                    <polygon points="{{ $anArea }}" fill="url(#anArea)"/>
                    <polyline points="{{ $anLine }}" fill="none" stroke="#14652F" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    @endif

                    {{-- Points + X labels --}}
                    @foreach($anPts as $p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#ffffff" stroke="#14652F" stroke-width="2.5"/>
                    <text x="{{ $p['x'] }}" y="{{ 260 - 12 }}" text-anchor="middle" font-size="11" fill="#6F6B60" font-family="Poppins, sans-serif">{{ $p['label'] }}</text>
                    @endforeach
                </svg>

                @if($anLastPoint)
                <div class="absolute top-2 right-3 bg-white border border-[#E5E7E5] rounded-lg px-3 py-2 shadow-sm">
                    <p class="text-[11px] text-[#8A857A]">{{ $anLastPoint['label'] }}</p>
                    <p class="text-[14px] font-bold text-[#0B4D2C] leading-tight">{{ number_format($anLastPoint['value']) }}</p>
                    <p class="text-[10.5px] text-[#8A857A]">{{ $isFr ? 'nouvelles entreprises' : 'new businesses' }}</p>
                </div>
                @endif
            </div>
        </section>

        {{-- Répartition par catégorie (real top categories, ranked bar list) --}}
        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Répartition par Catégorie' : 'By Category' }}</h2>
                <span class="h-[34px] inline-flex items-center gap-2 bg-[#F6F7F6] border border-[#EDEEED] rounded-lg px-3 text-[12px] font-medium text-[#3B382F]">
                    {{ $isFr ? 'Ce mois' : 'This month' }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                </span>
            </div>

            <div class="mt-4 space-y-3.5">
                @forelse($anCats as $i => $cat)
                @php
                    $cc  = (int) ($cat['c'] ?? 0);
                    $pct = round($cc / $anCatMax * 100);
                    $barColor = $anCatBarColors[$i] ?? '#0B4D2C';
                @endphp
                <div>
                    <div class="flex items-center justify-between text-[12.5px]">
                        <span class="flex items-center gap-2 text-[#3B382F] min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $barColor }}"></span>
                            <span class="truncate">{{ $cat['name_fr'] ?? '—' }}</span>
                        </span>
                        <span class="font-semibold text-[#1B1B18] shrink-0 pl-2">{{ number_format($cc) }}</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-[#F0EFEA] overflow-hidden">
                        <span class="block h-full rounded-full" style="width: {{ max(4, $pct) }}%; background-color: {{ $barColor }}"></span>
                    </div>
                </div>
                @empty
                <p class="py-8 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée de catégorie disponible.' : 'No category data available.' }}</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Secondary analytics panels (no data source → neutral empty-states) --}}
    <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-5">

        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Visites par Source' : 'Visits by Source' }}</h2>
                <span class="text-[12px] text-[#B9B4A9]">—</span>
            </div>
            <div class="mt-8 flex flex-col items-center justify-center text-center py-4">
                <span class="w-[52px] h-[52px] rounded-full bg-[#F3F4F3] flex items-center justify-center">
                    <i data-lucide="pie-chart" class="w-6 h-6 text-[#B9B4A9]"></i>
                </span>
                <p class="mt-3 text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée de source disponible' : 'No source data available' }}</p>
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Visites par Appareil' : 'Visits by Device' }}</h2>
                <span class="text-[12px] text-[#B9B4A9]">—</span>
            </div>
            <div class="mt-5 space-y-4">
                @foreach([['smartphone', $isFr?'Mobile':'Mobile'], ['monitor', $isFr?'Bureau':'Desktop'], ['tablet', $isFr?'Tablette':'Tablet']] as [$dIcon, $dLabel])
                <div>
                    <div class="flex items-center justify-between text-[12.5px]">
                        <span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="{{ $dIcon }}" class="w-4 h-4 text-[#8A857A]"></i>{{ $dLabel }}</span>
                        <span class="font-semibold text-[#B9B4A9]">—</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-[#F0EFEA] overflow-hidden"></div>
                </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Répartition Géographique' : 'Geographic Distribution' }}</h2>
                <span class="text-[12px] text-[#B9B4A9]">—</span>
            </div>
            <div class="mt-8 flex flex-col items-center justify-center text-center py-4">
                <span class="w-[52px] h-[52px] rounded-full bg-[#F3F4F3] flex items-center justify-center">
                    <i data-lucide="globe" class="w-6 h-6 text-[#B9B4A9]"></i>
                </span>
                <p class="mt-3 text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée géographique disponible' : 'No geographic data available' }}</p>
            </div>
        </section>
    </div>

    {{-- Bottom panels: recent activity + alerts (no data source → empty-state) --}}
    <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Activité Récente' : 'Recent Activity' }}</h2>
                <span class="text-[12px] text-[#B9B4A9]">—</span>
            </div>
            <div class="mt-6 flex flex-col items-center justify-center text-center py-6">
                <span class="w-[52px] h-[52px] rounded-full bg-[#F3F4F3] flex items-center justify-center">
                    <i data-lucide="activity" class="w-6 h-6 text-[#B9B4A9]"></i>
                </span>
                <p class="mt-3 text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune activité récente à afficher' : 'No recent activity to display' }}</p>
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl px-5 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Alertes & Notifications' : 'Alerts & Notifications' }}</h2>
                <span class="text-[12px] text-[#B9B4A9]">—</span>
            </div>
            <div class="mt-6 flex flex-col items-center justify-center text-center py-6">
                <span class="w-[52px] h-[52px] rounded-full bg-[#F3F4F3] flex items-center justify-center">
                    <i data-lucide="bell" class="w-6 h-6 text-[#B9B4A9]"></i>
                </span>
                <p class="mt-3 text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune alerte à afficher' : 'No alerts to display' }}</p>
            </div>
        </section>
    </div>

    <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
</div>
@endsection
