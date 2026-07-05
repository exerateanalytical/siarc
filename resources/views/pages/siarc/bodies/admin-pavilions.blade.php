@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── KPI row (approved SIARC design figures, verbatim) ──────────────────────
    $kpis = [
        ['building-2',  '#157A43','#E2F3E8','Total Pavillons',  '16',          '100% actifs',     false,''],
        ['layout-grid', '#3565DE','#E8EFFB','Stands totaux',    '512',         '100% disponibles',false,''],
        ['grid-3x3',    '#7C4FE0','#F0EAFB','Stands occupés',   '378',         '73.8%',           false,''],
        ['activity',    '#C97A16','#FDF3E0','Taux d’occupation','73.8%',       '12.6%',           true, 'vs dernier mois'],
        ['users-round', '#157A43','#E2F3E8','Exposants',        '842',         'Dans les pavillons',false,''],
        ['banknote',    '#C97A16','#FDF3E0','Revenus générés',  '128 450 000', '22%',             true, 'vs dernier mois'],
    ];

    // ── Category breakdown donut (approved figures) ────────────────────────────
    $catBreak = [
        ['Institutionnel',      '3 (18.8%)', 18.8,'#157A43'],
        ['Artisanat',           '3 (18.8%)', 18.8,'#C97A16'],
        ['Culture & Traditions','2 (12.5%)', 12.5,'#7C4FE0'],
        ['Design & Innovation', '2 (12.5%)', 12.5,'#3565DE'],
        ['Agriculture',         '1 (6.3%)',   6.3,'#E6B201'],
        ['International',        '5 (31.3%)', 31.3,'#2E8B57'],
    ];

    // ── Region breakdown bars (approved figures) ───────────────────────────────
    $regions = [
        ['Cameroun','10 (62.5%)',62.5,'#157A43'],
        ['Afrique', '2 (12.5%)', 12.5,'#C97A16'],
        ['Europe',  '1 (6.3%)',   6.3,'#E6B201'],
        ['Asie',    '1 (6.3%)',   6.3,'#C0010C'],
        ['Amériques','1 (6.3%)',  6.3,'#3565DE'],
    ];

    $sTable = ($sTables[0] ?? null);
    $rows   = $sTable['rows'] ?? [];

    $addHref  = $sLinks['add']  ?? ($sLinks[0]['href'] ?? $h('siarc.admin.pavilions'));
    $planHref = $sLinks['plan'] ?? ($sLinks[1]['href'] ?? $h('siarc.admin.floorplan'));
@endphp

{{-- ══ PAGE TITLE ══ --}}
<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $isFr ? 'Gestion des Pavillons' : 'Pavilion Management' }}</h1>
        <p class="text-[13px] text-[#8A857A] mt-0.5">{{ $isFr ? 'Gérez tous les pavillons et leur configuration.' : 'Manage all pavilions and their configuration.' }}</p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ $planHref }}" class="siarc-btn siarc-btn-outline !border-[#D8E5DC] !text-siarc-green bg-white text-[12.5px] px-4 py-2.5 siarc-shadow"><i data-lucide="map" class="w-4 h-4"></i>{{ $isFr ? 'Plan interactif' : 'Interactive map' }}</a>
        <a href="{{ $addHref }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2.5 siarc-shadow"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un pavillon' : 'Add pavilion' }}</a>
    </div>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
    @foreach($kpis as [$icon,$color,$tile,$label,$val,$trend,$isTrend,$suffix])
    <div class="siarc-card siarc-shadow p-4 siarc-in">
        <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-[22px] h-[22px]" style="color:{{ $color }}"></i></span>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ $label }}</p>
        <p class="mt-0.5 text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}@if($label==='Revenus générés')<span class="text-[11px] font-semibold text-[#8A857A]"> FCFA</span>@endif</p>
        <div class="mt-2 text-[11px]">
            @if($isTrend)
                <span class="inline-flex items-center gap-1 font-semibold text-siarc-green"><i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $trend }}<span class="text-[#B0AB9F] font-normal">{{ $suffix }}</span></span>
            @else
                <span class="text-[#157A43] font-semibold">{{ \Illuminate\Support\Str::before($trend,' ') }}</span> <span class="text-[#8A857A]">{{ \Illuminate\Support\Str::after($trend,' ') }}</span>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ══ MAIN GRID: TABLE + SIDE PANELS ══ --}}
<div class="grid xl:grid-cols-3 gap-5">

    {{-- ── LEFT: filters + table ── --}}
    <div class="xl:col-span-2 siarc-card siarc-shadow overflow-hidden">
        {{-- tabs --}}
        <div class="flex items-center gap-6 px-5 pt-4 border-b border-[#EFEDE6]">
            <button class="pb-3 text-[13px] font-bold text-siarc-green border-b-2 border-siarc-green">{{ $isFr ? 'Tous les pavillons' : 'All pavilions' }} (16)</button>
            <button class="pb-3 text-[13px] font-medium text-[#8A857A] hover:text-[#3B382F]">{{ $isFr ? 'Pavillons nationaux' : 'National pavilions' }} (10)</button>
            <button class="pb-3 text-[13px] font-medium text-[#8A857A] hover:text-[#3B382F]">{{ $isFr ? 'Pavillons internationaux' : 'International pavilions' }} (6)</button>
        </div>

        {{-- filter row --}}
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" placeholder="{{ $isFr ? 'Rechercher un pavillon...' : 'Search a pavilion...' }}" class="w-full text-[12.5px] rounded-xl border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-[#FBFAF6]">
            </div>
            @foreach([['Catégorie','Tous'],['Type','Tous'],['Statut','Tous']] as [$flabel,$fval])
            <div class="min-w-[120px]">
                <label class="block text-[10px] font-semibold text-[#8A857A] mb-1">{{ $flabel }}</label>
                <div class="flex items-center justify-between text-[12.5px] rounded-xl border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F] cursor-pointer hover:border-[#D8E5DC]"><span>{{ $fval }}</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></div>
            </div>
            @endforeach
            <div class="flex items-center gap-2 ml-auto">
                <button class="siarc-btn text-[12px] text-[#3B382F] border border-[#EFEDE6] px-3 py-2.5 rounded-xl bg-white hover:border-[#D8E5DC]"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</button>
                <button class="siarc-btn text-[12px] text-[#3B382F] border border-[#EFEDE6] px-3 py-2.5 rounded-xl bg-white hover:border-[#D8E5DC]"><i data-lucide="filter" class="w-4 h-4"></i>{{ $isFr ? 'Filtres' : 'Filters' }}</button>
                <button class="siarc-btn text-[12px] text-siarc-green px-2 py-2.5"><i data-lucide="activity" class="w-4 h-4"></i>{{ $isFr ? 'Réinitialiser' : 'Reset' }}</button>
            </div>
        </div>

        {{-- table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                    <tr class="border-y border-[#EFEDE6] bg-[#FBFAF6]">
                        <th class="px-5 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $isFr ? 'Pavillon' : 'Pavilion' }}</th>
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase text-center">{{ $isFr ? 'Stands' : 'Stands' }}</th>
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $isFr ? 'Occupation' : 'Occupancy' }}</th>
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase text-center">{{ $isFr ? 'Exposants' : 'Exhibitors' }}</th>
                        <th class="px-5 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php
                            $c = $r['cells'] ?? [];
                            // cols[Pavillon, Code, Stands, Alloués, Exposants]
                            $pav     = $c[0] ?? '—';
                            $code    = $c[1] ?? '';
                            $total   = (int) preg_replace('/\D/','', (string)($c[2] ?? 0));
                            $alloc   = (int) preg_replace('/\D/','', (string)($c[3] ?? 0));
                            $expos   = $c[4] ?? '';
                            $pct     = $total > 0 ? round($alloc / $total * 100) : 0;
                            $barCol  = $pct >= 80 ? '#157A43' : ($pct >= 50 ? '#C97A16' : '#C0010C');
                            $rhref   = $r['href'] ?? '#';
                            $initials= strtoupper(mb_substr(preg_replace('/^Pavillon\s+/i','',$pav), 0, 2));
                        @endphp
                        <tr class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="{{ $rhref }}" class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-lg siarc-adire flex items-center justify-center shrink-0 text-[11px] font-bold text-siarc-gold font-display">{{ $initials }}</span>
                                    <span>
                                        <span class="block text-[12.5px] font-semibold text-[#1A1712] leading-tight">{{ $pav }}</span>
                                        <span class="block text-[11px] text-[#B0AB9F]">{{ $code }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="px-3 py-3.5 text-center">
                                <span class="text-[12.5px] font-semibold text-[#1A1712]">{{ $alloc }}</span><span class="text-[11px] text-[#B0AB9F]"> / {{ $total }}</span>
                            </td>
                            <td class="px-3 py-3.5">
                                <div class="flex items-center gap-2 min-w-[110px]">
                                    <div class="h-2 flex-1 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full" style="width:{{ $pct }}%;background:{{ $barCol }}"></div></div>
                                    <span class="text-[11px] font-semibold text-[#3B382F] w-9 text-right">{{ $pct }}%</span>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 text-center text-[12px] text-[#3B382F]">{{ $expos !== '' ? $expos : '—' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5 text-[#B0AB9F]">
                                    <a href="{{ $rhref }}" class="w-7 h-7 rounded-lg hover:bg-[#EFEDE6] flex items-center justify-center hover:text-siarc-green"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <a href="{{ $rhref }}" class="w-7 h-7 rounded-lg hover:bg-[#EFEDE6] flex items-center justify-center hover:text-siarc-green"><i data-lucide="settings" class="w-4 h-4"></i></a>
                                    <button class="w-7 h-7 rounded-lg hover:bg-[#EFEDE6] flex items-center justify-center hover:text-siarc-green"><i data-lucide="grid-3x3" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucun pavillon pour le moment.' : 'No pavilions yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- pagination footer --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-[#EFEDE6]">
            <span class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Affichage de' : 'Showing' }} 1 {{ $isFr ? 'à' : 'to' }} {{ count($rows) }} {{ $isFr ? 'sur' : 'of' }} 16 {{ $isFr ? 'pavillons' : 'pavilions' }}</span>
            <div class="flex items-center gap-1.5">
                <button class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#B0AB9F] hover:border-[#D8E5DC]"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
                <button class="w-8 h-8 rounded-lg bg-siarc-green text-white text-[12px] font-semibold">1</button>
                <button class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] text-[#3B382F] hover:border-[#D8E5DC]">2</button>
                <button class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#B0AB9F] hover:border-[#D8E5DC]"><i data-lucide="arrow-right" class="w-4 h-4"></i></button>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: side analytics ── --}}
    <div class="space-y-5">
        {{-- category donut --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'Répartition par catégorie' : 'By category' }}</h3>
            <div class="flex items-center gap-4">
                <div class="relative shrink-0">
                    <svg viewBox="0 0 120 120" class="w-[120px] h-[120px] -rotate-90">
                        @php $circ=326.7; $off=0; @endphp
                        @foreach($catBreak as [$lbl,$disp,$pct,$col])
                            <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="14"
                                stroke-dasharray="{{ round($circ*$pct/100,1) }} {{ round($circ-($circ*$pct/100),1) }}"
                                stroke-dashoffset="{{ -round($off,1) }}"/>
                            @php $off += $circ*$pct/100; @endphp
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="font-display text-[22px] font-extrabold text-[#1A1712] leading-none">16</span>
                        <span class="text-[9px] text-[#8A857A]">Pavillons</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-1.5">
                    @foreach($catBreak as [$lbl,$disp,$pct,$col])
                    <li class="flex items-center gap-2 text-[11px]"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span><span class="text-[#3B382F] font-medium truncate">{{ $lbl }}</span><span class="ml-auto text-[#8A857A] whitespace-nowrap">{{ $disp }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- region bars --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Répartition par pays / région' : 'By country / region' }}</h3>
            <ul class="space-y-3">
                @foreach($regions as [$name,$disp,$pct,$col])
                <li class="flex items-center gap-3">
                    <span class="text-[12px] text-[#3B382F] font-medium w-20 shrink-0">{{ $name }}</span>
                    <div class="h-2 flex-1 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full" style="width:{{ $pct }}%;background:{{ $col }}"></div></div>
                    <span class="text-[11px] text-[#8A857A] w-16 text-right shrink-0">{{ $disp }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- global occupancy gauge --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-2">{{ $isFr ? 'Occupation globale des stands' : 'Global stand occupancy' }}</h3>
            <div class="relative mx-auto w-[200px] h-[110px]">
                <svg viewBox="0 0 200 110" class="w-full h-full">
                    <path d="M18 100 A82 82 0 0 1 182 100" fill="none" stroke="#EFEDE6" stroke-width="16" stroke-linecap="round"/>
                    @php $gp = 73.8/100; $len = 3.14159*82; @endphp
                    <path d="M18 100 A82 82 0 0 1 182 100" fill="none" stroke="#157A43" stroke-width="16" stroke-linecap="round"
                        stroke-dasharray="{{ round($len*$gp,1) }} {{ round($len,1) }}"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-end pb-1 text-center">
                    <span class="font-display text-[28px] font-extrabold text-[#1A1712] leading-none">73.8%</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-[#B0AB9F] -mt-1 px-1">
                <span>0%</span><span class="text-[11px] text-[#3B382F] font-medium">378 / 512 stands occupés</span><span>100%</span>
            </div>
            <p class="text-center text-[11.5px] font-semibold text-siarc-green mt-2 inline-flex items-center gap-1 w-full justify-center"><i data-lucide="arrow-up" class="w-3 h-3"></i>12.6% <span class="text-[#B0AB9F] font-normal">vs dernier mois</span></p>
        </div>
    </div>
</div>
