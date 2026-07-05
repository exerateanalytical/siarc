@php
    use Illuminate\Support\Facades\Route as R;
    $lang  = $lang ?? 'fr'; $isFr = $isFr ?? ($lang === 'fr');
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang' => $lang], $params)) : null;

    // Tone → [tileBg, ink] — mirrors the design system so chips/badges match.
    $toneMap = [
        'green'  => ['#E2F3E8', '#157A43'], 'red'    => ['#FDE8E8', '#DC2626'],
        'blue'   => ['#E8EFFB', '#3565DE'], 'gold'   => ['#FDF3E0', '#C97A16'],
        'grey'   => ['#F1F1EF', '#6F6B60'], 'purple' => ['#F0EAFB', '#7C4FE0'],
    ];

    // ── Unpack the single stands table (real data) ─────────────────────────────
    $tbl  = $sTables[0] ?? ['title' => 'Stands', 'cols' => [], 'rows' => []];
    $rows = $tbl['rows'] ?? [];

    // ── Compute REAL per-status + per-pavilion counts from the table rows ───────
    // cells: [0]=code, [1]=pavilion, [2]=status array {badge,tone}, [3]=exhibitor
    $total       = count($rows);
    $statusCount = ['green' => 0, 'grey' => 0, 'blue' => 0, 'gold' => 0, 'red' => 0, 'purple' => 0];
    $pavGroups   = [];   // pavilion => ['rows'=>[], 'alloc'=>int, 'total'=>int]
    foreach ($rows as $row) {
        $cells   = $row['cells'] ?? [];
        $statusC = $cells[2] ?? [];
        $tn      = is_array($statusC) ? ($statusC['tone'] ?? 'grey') : 'grey';
        if (!isset($statusCount[$tn])) $statusCount[$tn] = 0;
        $statusCount[$tn]++;

        $pav = is_string($cells[1] ?? null) ? $cells[1] : '—';
        if (!isset($pavGroups[$pav])) $pavGroups[$pav] = ['rows' => [], 'alloc' => 0, 'total' => 0];
        $pavGroups[$pav]['rows'][] = $row;
        $pavGroups[$pav]['total']++;
        if ($tn === 'green') $pavGroups[$pav]['alloc']++;
    }
    // Sort pavilion zones by size (largest first) for a stable, readable hall map.
    uasort($pavGroups, fn ($a, $b) => $b['total'] <=> $a['total']);

    $allocated = $statusCount['green'] ?? 0;
    $available = $statusCount['grey']  ?? 0;
    $reserved  = $statusCount['blue']  ?? 0;
    $maint     = $statusCount['red']   ?? 0;      // maintenance-tone stands, if any
    $pendingS  = $statusCount['gold']  ?? 0;      // en attente-tone stands, if any
    $occRate   = $total > 0 ? round($allocated / $total * 100, 1) : 0.0;

    // ── Donut segments from REAL status counts (skip empty) ────────────────────
    $donut = array_values(array_filter([
        ['green', $isFr ? 'Attribués'   : 'Allocated',   $allocated],
        ['grey',  $isFr ? 'Disponibles' : 'Available',   $available],
        ['blue',  $isFr ? 'Réservés'    : 'Reserved',    $reserved],
        ['gold',  $isFr ? 'En attente'  : 'Pending',     $pendingS],
        ['red',   'Maintenance',                          $maint],
    ], fn ($seg) => $seg[2] > 0));

    // ── Legend (design order) — Alloué / Disponible / Réservé / Maintenance ─────
    $legend = [
        ['green', $isFr ? 'Attribué'    : 'Allocated'],
        ['grey',  $isFr ? 'Disponible'  : 'Available'],
        ['gold',  $isFr ? 'En attente'  : 'Pending'],
        ['red',   'Maintenance'],
        ['blue',  $isFr ? 'Réservé'     : 'Reserved'],
    ];

    // ── Filter selects (visual only) ───────────────────────────────────────────
    $pavOptions = array_keys($pavGroups);
    $planLink   = $sLinks[0] ?? null;

    // ── Selected stand = first real row (or empty state) ───────────────────────
    $sel        = $rows[0] ?? null;
    $selCells   = $sel['cells'] ?? [];
    $selCode    = $selCells[0] ?? null;
    $selPav     = is_string($selCells[1] ?? null) ? $selCells[1] : '—';
    $selStatusC = $selCells[2] ?? [];
    $selTone    = is_array($selStatusC) ? ($selStatusC['tone'] ?? 'grey') : 'grey';
    $selBadge   = is_array($selStatusC) ? ($selStatusC['badge'] ?? '') : (string) $selStatusC;
    $selExh     = is_string($selCells[3] ?? null) && $selCells[3] !== '—' ? $selCells[3] : null;
    $selTC      = $toneMap[$selTone] ?? $toneMap['grey'];
@endphp

{{-- ══ TITLE ══ --}}
<div class="mb-5 siarc-in">
    <p class="siarc-kicker text-siarc-ochre mb-2">{{ $isFr ? 'Pavillons & Stands' : 'Pavilions & Stands' }}</p>
    <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $sTitle ?? ($isFr ? 'Attribution des Stands' : 'Stand Allocation') }}</h1>
    <p class="text-[13px] text-[#8A857A] mt-1">{{ $sIntro ?? ($isFr ? 'Attribuez et gérez les stands pour les exposants.' : 'Assign and manage stands for exhibitors.') }}</p>
</div>

{{-- ══ KPI ROW ══ --}}
@if(!empty($sStats))
@php
    // Extra derived KPI tiles to match the design (5-card row). These are honest
    // aggregates computed from the real stand rows — not invented records.
    $pctOf = fn($n) => $total > 0 ? round($n / $total * 100, 1) . '%' : '0%';
    $kpiExtra = [
        ['circle-dot', '#C97A16', '#FDF3E0', $pendingS, $isFr ? "En attente d'attribution" : 'Pending allocation', $pctOf($pendingS)],
        ['activity',   '#157A43', '#E2F3E8', $occRate . '%', $isFr ? "Taux d'occupation" : 'Occupancy rate', null],
    ];
@endphp
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($sStats as $i => [$stIcon, $stColor, $stTile, $stValue, $stLabel, $stSub])
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $stTile }}"><i data-lucide="{{ $stIcon }}" class="w-[22px] h-[22px]" style="color:{{ $stColor }}"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ $stLabel }}</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">{{ $stValue }}</p>
            </div>
        </div>
        @php $subline = $i === 0 ? '100%' : ($i === 1 ? $pctOf($allocated) : ($i === 2 ? $pctOf($available) : $stSub)); @endphp
        @if($subline)<p class="text-[11px] font-semibold text-siarc-green mt-2">{{ $subline }}</p>@endif
    </div>
    @endforeach
    @foreach($kpiExtra as [$eIcon, $eColor, $eTile, $eVal, $eLabel, $eSub])
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $eTile }}"><i data-lucide="{{ $eIcon }}" class="w-[22px] h-[22px]" style="color:{{ $eColor }}"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ $eLabel }}</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">{{ $eVal }}</p>
            </div>
        </div>
        @if($eSub)<p class="text-[11px] font-semibold text-siarc-green mt-2">{{ $eSub }}</p>@endif
    </div>
    @endforeach
</div>
@endif

{{-- ══ FILTER / ACTION BAR ══ --}}
<div class="siarc-card siarc-shadow p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        {{-- Pavillon --}}
        <div class="min-w-[150px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">{{ $isFr ? 'Pavillon' : 'Pavilion' }}</label>
            <select class="w-full px-3 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                <option>{{ $isFr ? 'Tous les pavillons' : 'All pavilions' }}</option>
                @foreach($pavOptions as $po)<option>{{ $po }}</option>@endforeach
            </select>
        </div>
        {{-- Catégorie --}}
        <div class="min-w-[130px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">{{ $isFr ? 'Catégorie' : 'Category' }}</label>
            <select class="w-full px-3 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                <option>{{ $isFr ? 'Tous' : 'All' }}</option>
            </select>
        </div>
        {{-- Statut d'attribution --}}
        <div class="min-w-[140px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">{{ $isFr ? "Statut d'attribution" : 'Allocation status' }}</label>
            <select class="w-full px-3 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                <option>{{ $isFr ? 'Tous' : 'All' }}</option>
                <option>{{ $isFr ? 'Attribué' : 'Allocated' }}</option>
                <option>{{ $isFr ? 'Disponible' : 'Available' }}</option>
                <option>{{ $isFr ? 'Réservé' : 'Reserved' }}</option>
            </select>
        </div>
        {{-- Statut de paiement --}}
        <div class="min-w-[130px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">{{ $isFr ? 'Statut de paiement' : 'Payment status' }}</label>
            <select class="w-full px-3 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                <option>{{ $isFr ? 'Tous' : 'All' }}</option>
            </select>
        </div>
        {{-- Search --}}
        <div class="relative flex-1 min-w-[180px]">
            <label class="block text-[10.5px] font-semibold text-transparent mb-1">.</label>
            <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-[30px]"></i>
            <input type="text" placeholder="{{ $isFr ? 'Rechercher un stand ou exposant…' : 'Search a stand or exhibitor…' }}" class="w-full pl-9 pr-3 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] placeholder:text-[#B0AB9F] focus:outline-none focus:border-[#D7E4DB] bg-[#FBFAF6]">
        </div>
        {{-- Filtres / Réinitialiser (visual) --}}
        <button type="button" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2 text-[12.5px]"><i data-lucide="filter" class="w-4 h-4"></i>{{ $isFr ? 'Filtres' : 'Filters' }}</button>
        <button type="button" class="siarc-btn border border-[#ECEAE3] text-[#8A857A] px-3.5 py-2 text-[12.5px]"><i data-lucide="activity" class="w-4 h-4"></i>{{ $isFr ? 'Réinitialiser' : 'Reset' }}</button>
        {{-- Attribution manuelle --}}
        <a @if($planLink)href="{{ $planLink['href'] }}"@endif class="siarc-btn siarc-btn-green px-4 py-2 text-[12.5px]"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Attribution manuelle' : 'Manual allocation' }}</a>
    </div>
</div>

{{-- ══ MAIN GRID: HALL MAP  +  RIGHT COLUMN ══ --}}
<div class="grid xl:grid-cols-3 gap-5 mb-5">

    {{-- ── LEFT: Plan des stands (hall map) ─────────────────────────────────── --}}
    <div class="xl:col-span-2 siarc-card siarc-shadow p-5">
        {{-- Legend + zoom controls --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex flex-wrap items-center gap-3.5">
                @foreach($legend as [$lt, $label])
                @php $lc = $toneMap[$lt]; @endphp
                <span class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#3B382F]">
                    <span class="w-3.5 h-3.5 rounded-[4px]" style="background:{{ $lc[0] }};border:1.5px solid {{ $lc[1] }}"></span>{{ $label }}
                </span>
                @endforeach
            </div>
            <div class="inline-flex items-center gap-1.5">
                <button type="button" title="{{ $isFr ? 'Déplacer' : 'Pan' }}" class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6]"><i data-lucide="maximize" class="w-4 h-4"></i></button>
                <button type="button" title="{{ $isFr ? 'Zoom avant' : 'Zoom in' }}" class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6]"><i data-lucide="plus" class="w-4 h-4"></i></button>
                <button type="button" title="{{ $isFr ? 'Zoom arrière' : 'Zoom out' }}" class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6]"><i data-lucide="arrow-down" class="w-4 h-4"></i></button>
            </div>
        </div>

        @if($total)
        <div class="rounded-2xl border border-[#ECEAE3] bg-[#FBFAF6] p-4 overflow-x-auto">
            {{-- Entrée principale marker --}}
            <div class="flex items-center justify-center gap-1.5 mb-4 text-[10px] font-bold tracking-wide text-[#8A857A]">
                <i data-lucide="door-open" class="w-3.5 h-3.5"></i>{{ $isFr ? 'ENTRÉE PRINCIPALE' : 'MAIN ENTRANCE' }}
            </div>

            <div class="space-y-4 min-w-[560px]">
                @foreach($pavGroups as $pav => $grp)
                @php
                    // Zone header colour cycles through the palette for visual variety.
                    $zoneColors = ['#157A43', '#C97A16', '#C0010C', '#3565DE', '#7C4FE0'];
                    $zc = $zoneColors[$loop->index % count($zoneColors)];
                @endphp
                <div class="rounded-xl border border-[#ECEAE3] bg-white p-3.5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="inline-flex items-center gap-1.5 text-[11.5px] font-bold text-white px-3 py-1 rounded-md" style="background:{{ $zc }}">
                            <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>{{ $pav }}
                        </span>
                        <span class="text-[11px] text-[#8A857A]">{{ $grp['alloc'] }}/{{ $grp['total'] }} {{ $isFr ? 'attribués' : 'allocated' }}</span>
                    </div>
                    <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-1.5">
                        @foreach($grp['rows'] as $row)
                            @php
                                $rc    = $row['cells'] ?? [];
                                $code  = $rc[0] ?? '—';
                                $rsC   = $rc[2] ?? [];
                                $rtn   = is_array($rsC) ? ($rsC['tone'] ?? 'grey') : 'grey';
                                $rbadge= is_array($rsC) ? ($rsC['badge'] ?? '') : (string) $rsC;
                                $cc    = $toneMap[$rtn] ?? $toneMap['grey'];
                                $exh   = is_string($rc[3] ?? null) && $rc[3] !== '—' ? $rc[3] : null;
                                $ttl   = trim($rbadge . ($exh ? ' · ' . $exh : ''));
                            @endphp
                            <a @if(!empty($row['href']))href="{{ $row['href'] }}"@endif title="{{ $ttl }}"
                               class="siarc-lift h-[42px] rounded-lg flex items-center justify-center text-[11px] font-bold border"
                               style="background:{{ $cc[0] }};color:{{ $cc[1] }};border-color:{{ $cc[1] }}55">{{ $code }}</a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex items-center justify-center gap-1.5 mt-4 text-[10px] font-bold tracking-wide text-[#8A857A]">
                <i data-lucide="door-open" class="w-3.5 h-3.5"></i>{{ $isFr ? 'ENTRÉE SECONDAIRE' : 'SECONDARY ENTRANCE' }}
            </div>
        </div>
        @else
        <div class="rounded-2xl border border-dashed border-[#E3E0D8] py-16 text-center">
            <span class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-3 siarc-adire"><i data-lucide="grid-3x3" class="w-7 h-7 text-siarc-gold"></i></span>
            <p class="text-[13.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucun stand configuré' : 'No stands configured' }}</p>
            <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'Le plan du salon apparaîtra ici.' : 'The hall map will appear here.' }}</p>
        </div>
        @endif
    </div>

    {{-- ── RIGHT: donut + pavilion bars + activity ──────────────────────────── --}}
    <div class="space-y-5">

        {{-- Donut: Statut des attributions --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Statut des attributions' : 'Allocation status' }}</h3>
            <div class="flex items-center gap-4">
                <div class="relative w-[128px] h-[128px] shrink-0">
                    <svg viewBox="0 0 120 120" class="w-[128px] h-[128px] -rotate-90">
                        @php $circ = 2 * 3.14159 * 52; $off = 0; @endphp
                        @if($total > 0)
                            @foreach($donut as [$dt, $dlbl, $dval])
                                @php $dc = $toneMap[$dt][1]; $len = $circ * $dval / $total; @endphp
                                <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $dc }}" stroke-width="15"
                                    stroke-dasharray="{{ round($len, 1) }} {{ round($circ - $len, 1) }}"
                                    stroke-dashoffset="{{ -round($off, 1) }}"/>
                                @php $off += $len; @endphp
                            @endforeach
                        @else
                            <circle cx="60" cy="60" r="52" fill="none" stroke="#F1F1EF" stroke-width="15"/>
                        @endif
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-display text-[24px] font-extrabold text-[#1A1712] leading-none">{{ $total }}</span>
                        <span class="text-[10px] text-[#8A857A]">Stands</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-2">
                    @forelse($donut as [$dt, $dlbl, $dval])
                    @php $dc = $toneMap[$dt][1]; $dpct = $total > 0 ? round($dval / $total * 100, 1) : 0; @endphp
                    <li class="flex items-center gap-2 text-[11.5px]">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $dc }}"></span>
                        <span class="text-[#3B382F] font-medium">{{ $dlbl }}</span>
                        <span class="ml-auto text-[#8A857A]">{{ $dval }} ({{ $dpct }}%)</span>
                    </li>
                    @empty
                    <li class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée' : 'No data' }}</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Répartition par pavillon --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Répartition par pavillon' : 'By pavilion' }}</h3>
            @if(count($pavGroups))
            <ul class="space-y-3">
                @foreach($pavGroups as $pav => $grp)
                @php $ppct = $grp['total'] > 0 ? round($grp['alloc'] / $grp['total'] * 100, 1) : 0; @endphp
                <li>
                    <div class="flex items-center justify-between text-[12px] mb-1">
                        <span class="text-[#3B382F] font-medium truncate pr-2">{{ $pav }}</span>
                        <span class="text-[#8A857A] whitespace-nowrap">{{ $grp['alloc'] }} / {{ $grp['total'] }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 flex-1 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full bg-siarc-green" style="width:{{ $ppct }}%"></div></div>
                        <span class="text-[11px] font-semibold text-[#8A857A] w-[42px] text-right">{{ $ppct }}%</span>
                    </div>
                </li>
                @endforeach
            </ul>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-[#ECEAE3] text-[12px]">
                <span class="font-bold text-[#1A1712]">Total</span>
                <span class="text-[#8A857A]">{{ $allocated }} / {{ $total }}</span>
                <span class="font-bold text-siarc-green">{{ $occRate }}%</span>
            </div>
            @else
            <p class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Aucun pavillon.' : 'No pavilions.' }}</p>
            @endif
        </div>

        {{-- Activités récentes (honest placeholder — no invented names) --}}
        <div class="siarc-card siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Activités récentes' : 'Recent activity' }}</h3>
                @if($planLink)<a href="{{ $planLink['href'] }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tout' : 'View all' }}</a>@endif
            </div>
            <div class="py-8 text-center">
                <span class="w-11 h-11 mx-auto rounded-xl flex items-center justify-center mb-3 bg-[#F1F1EF]"><i data-lucide="clock" class="w-5 h-5 text-[#8A857A]"></i></span>
                <p class="text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucune activité récente' : 'No recent activity' }}</p>
                <p class="text-[11.5px] text-[#8A857A] mt-1">{{ $isFr ? "Les attributions et paiements apparaîtront ici." : 'Allocations and payments will appear here.' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- ══ STAND SÉLECTIONNÉ (detail panel) ══ --}}
<div class="siarc-card siarc-shadow p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Stand sélectionné' : 'Selected stand' }}</h3>
        <button type="button" class="w-7 h-7 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]"><i data-lucide="ellipsis" class="w-4 h-4"></i></button>
    </div>

    @if($sel)
    <div class="grid md:grid-cols-3 gap-5">
        {{-- Stand identity --}}
        <div class="flex gap-3">
            <span class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 font-display text-[15px] font-extrabold border" style="background:{{ $selTC[0] }};color:{{ $selTC[1] }};border-color:{{ $selTC[1] }}55">{{ $selCode }}</span>
            <div class="min-w-0">
                <p class="text-[13px] font-bold text-[#161513]">{{ $selPav }}</p>
                <p class="text-[12px] text-[#8A857A] mt-0.5">{{ $isFr ? 'Statut' : 'Status' }} :
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full align-middle" style="background:{{ $selTC[0] }};color:{{ $selTC[1] }}">
                        <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $selTC[1] }}"></span>{{ $selBadge }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Exhibitor --}}
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#8A857A] mb-1.5">{{ $isFr ? 'Exposant attribué' : 'Assigned exhibitor' }}</p>
            @if($selExh)
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-full bg-siarc-green text-white flex items-center justify-center text-[12px] font-bold shrink-0">{{ mb_strtoupper(mb_substr($selExh, 0, 2)) }}</span>
                <p class="text-[13px] font-semibold text-[#161513] leading-tight">{{ $selExh }}</p>
            </div>
            @else
            <div class="flex items-center gap-2 text-[12.5px] text-[#8A857A]">
                <i data-lucide="circle-dashed" class="w-4 h-4"></i>{{ $isFr ? 'Aucun exposant attribué' : 'No exhibitor assigned' }}
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col gap-2 md:items-end">
            @if(!empty($sel['href']))
            <a href="{{ $sel['href'] }}" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2 text-[12px] w-full md:w-auto justify-center"><i data-lucide="external-link" class="w-4 h-4"></i>{{ $isFr ? 'Voir le détail du stand' : 'View stand detail' }}</a>
            <a href="{{ $sel['href'] }}" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2 text-[12px] w-full md:w-auto justify-center"><i data-lucide="users" class="w-4 h-4"></i>{{ $selExh ? ($isFr ? "Changer d'exposant" : 'Change exhibitor') : ($isFr ? 'Attribuer un exposant' : 'Assign exhibitor') }}</a>
            @endif
        </div>
    </div>
    @else
    <div class="py-10 text-center">
        <span class="w-12 h-12 mx-auto rounded-2xl flex items-center justify-center mb-3 bg-[#F1F1EF]"><i data-lucide="scan-line" class="w-6 h-6 text-[#8A857A]"></i></span>
        <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Sélectionnez un stand' : 'Select a stand' }}</p>
        <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'Choisissez un stand sur le plan pour voir ses détails.' : 'Pick a stand on the map to see its details.' }}</p>
    </div>
    @endif
</div>

{{-- ══ ALLOCATION TABLE (real data) ══ --}}
<div class="siarc-card siarc-shadow overflow-hidden">
    <div class="px-5 py-4 border-b border-[#ECEAE3] flex items-center justify-between">
        <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $tbl['title'] ?? ($isFr ? 'Stands' : 'Stands') }}</h3>
        <span class="text-[11.5px] text-[#8A857A]">{{ $total }} {{ $isFr ? 'lignes' : 'rows' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[11px] font-semibold uppercase tracking-wide text-[#8A857A] bg-[#FBFAF6]">
                    @foreach(($tbl['cols'] ?? []) as $col)
                    <th class="px-5 py-3 whitespace-nowrap">{{ $col }}</th>
                    @endforeach
                    <th class="px-5 py-3 text-right whitespace-nowrap">{{ $isFr ? 'Action' : 'Action' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1EFEA]">
                @forelse($rows as $row)
                    @php
                        $rc      = $row['cells'] ?? [];
                        $statusC = $rc[2] ?? [];
                        $t       = is_array($statusC) ? ($statusC['tone'] ?? 'grey') : 'grey';
                        $badge   = is_array($statusC) ? ($statusC['badge'] ?? '') : (string) $statusC;
                        $bc      = $toneMap[$t] ?? $toneMap['grey'];
                        $isAvail = $t === 'grey';
                        $exhCell = is_string($rc[3] ?? null) ? $rc[3] : '—';
                    @endphp
                    <tr class="hover:bg-[#FBFAF6] transition-colors">
                        {{-- Stand code --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <a @if(!empty($row['href']))href="{{ $row['href'] }}"@endif class="font-bold text-[13px] text-[#161513] hover:text-siarc-green">{{ $rc[0] ?? '—' }}</a>
                        </td>
                        {{-- Pavillon --}}
                        <td class="px-5 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ is_string($rc[1] ?? null) ? $rc[1] : '—' }}</td>
                        {{-- Statut badge --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full" style="background:{{ $bc[0] }};color:{{ $bc[1] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $bc[1] }}"></span>{{ $badge }}
                            </span>
                        </td>
                        {{-- Exposant --}}
                        <td class="px-5 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $exhCell }}</td>
                        {{-- Action --}}
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            @if($isAvail)
                            <a @if(!empty($row['href']))href="{{ $row['href'] }}"@endif class="siarc-btn siarc-btn-primary px-3 py-1.5 text-[11.5px]">
                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Attribuer' : 'Assign' }}
                            </a>
                            @else
                            <a @if(!empty($row['href']))href="{{ $row['href'] }}"@endif class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-siarc-green hover:gap-1.5 transition-all">
                                {{ $isFr ? 'Détails' : 'Details' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucune donnée' : 'No data' }}</p>
                            <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'À venir' : 'Coming soon' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
