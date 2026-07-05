@extends('layouts.siarc-admin')

@php
    use Illuminate\Support\Facades\DB;
    $isFr = $fr ?? (($lang ?? 'fr') === 'fr');
    $adminActive = 'siarc-plan';
    $sTitle = 'Plan Interactif du Salon';
    $pageTitle = 'Plan Interactif du Salon';
    $pageSubtitle = $isFr ? 'Visualisez et gérez tous les pavillons et stands en temps réel.' : 'View and manage all pavilions and stands in real time.';

    // ── Status → colour map (fill / border / text) ──────────────────────────────
    $stTone = [
        'allocated' => ['#157A43', '#0F5A31', '#FFFFFF', $isFr ? 'Occupé' : 'Occupied'],
        'reserved'  => ['#3565DE', '#254FA8', '#FFFFFF', $isFr ? 'Réservé' : 'Reserved'],
        'available' => ['#E7E5DF', '#CFCcC2', '#6F6B60', $isFr ? 'Disponible' : 'Available'],
        'maintenance' => ['#F6C6C2', '#E4938C', '#8A2018', 'Maintenance'],
    ];
    $fillFor = function ($s) use ($stTone) {
        return ($stTone[$s->status] ?? ['#EDECE6', '#D6D4CB', '#8A857A', ''])[0];
    };

    $standsByPav = $stands->groupBy('pavilion_id');

    // ── Compute an SVG viewBox from real positions; fall back to a synthetic grid ─
    $hasPos = $stands->contains(fn ($s) => ($s->pos_w ?? 0) > 0 && ($s->pos_h ?? 0) > 0);
    $laid = collect();
    if ($hasPos) {
        $laid = $stands->filter(fn ($s) => ($s->pos_w ?? 0) > 0 && ($s->pos_h ?? 0) > 0)
            ->map(fn ($s) => (object) [
                'id' => $s->id, 'code' => $s->code, 'status' => $s->status, 'name_fr' => $s->name_fr,
                'pavilion_id' => $s->pavilion_id,
                'x' => (float) $s->pos_x, 'y' => (float) $s->pos_y, 'w' => (float) $s->pos_w, 'h' => (float) $s->pos_h,
            ])->values();
    } else {
        // Synthetic exhibition-hall layout: one horizontal band per pavilion, wrapped chips.
        $cols = 10; $cw = 96; $ch = 66; $gap = 10; $padL = 40; $padT = 96; $bandGap = 46;
        $y = $padT; $order = 0;
        foreach ($pavs as $p) {
            $ss = $standsByPav->get($p->id, collect())->values();
            if ($ss->isEmpty()) continue;
            $rowsInBand = (int) ceil($ss->count() / $cols);
            foreach ($ss as $i => $s) {
                $r = intdiv($i, $cols); $c = $i % $cols;
                $laid->push((object) [
                    'id' => $s->id, 'code' => $s->code, 'status' => $s->status, 'name_fr' => $s->name_fr,
                    'pavilion_id' => $s->pavilion_id,
                    'x' => $padL + $c * ($cw + $gap),
                    'y' => $y + 22 + $r * ($ch + $gap),
                    'w' => $cw, 'h' => $ch,
                ]);
            }
            $p->_bandY = $y; $p->_bandH = 22 + $rowsInBand * ($ch + $gap) + 8;
            $y += $p->_bandH + $bandGap; $order++;
        }
    }

    $maxX = max(1, (float) $laid->max(fn ($s) => $s->x + $s->w));
    $maxY = max(1, (float) $laid->max(fn ($s) => $s->y + $s->h));
    $vbW = $maxX + 40; $vbH = $maxY + 40;

    // ── Right-rail KPI figures (approved design metrics) ────────────────────────
    $nPav = $pavs->count();
    $nStands = $stands->count();
    $nOcc = $stands->where('status', 'allocated')->count();
    $occPct = $nStands ? round($nOcc / $nStands * 100, 1) : 0;
    $nAvail = $stands->where('status', 'available')->count();

    // Per-pavilion stats for the bottom strip
    $pavStats = $pavs->map(function ($p) use ($standsByPav) {
        $ss = $standsByPav->get($p->id, collect());
        $tot = $ss->count();
        $occ = $ss->where('status', 'allocated')->count();
        $av  = $ss->where('status', 'available')->count();
        return (object) [
            'name' => $p->name_fr, 'color' => $p->color ?? '#0F4824',
            'tot' => $tot, 'occ' => $occ, 'av' => $av,
            'pct' => $tot ? round($occ / $tot * 100) : 0,
        ];
    })->values();
    $legend = [
        ['available',   $isFr ? 'Disponible' : 'Available'],
        ['allocated',   $isFr ? 'Occupé' : 'Occupied'],
        ['reserved',    $isFr ? 'Réservé' : 'Reserved'],
        ['maintenance', 'Maintenance'],
    ];
@endphp

@push('head')
@include('pages.siarc.partials.tokens')
@endpush

@section('content')
{{-- SIARC design tokens (cards, kente, badges) for this page --}}
@include('pages.siarc.partials.tokens')

<style>
    .fp-stand{transition:filter .15s ease, transform .12s ease;cursor:pointer;}
    .fp-stand:hover rect{filter:brightness(1.06);}
    .fp-stand.is-active rect{stroke:#E6B201;stroke-width:2.4;}
    .fp-hidden{opacity:.14;}
    .fp-tool{width:38px;height:38px;border-radius:10px;background:#fff;border:1px solid #E7E4DC;
        display:flex;align-items:center;justify-content:center;color:#3B382F;
        box-shadow:0 2px 6px rgba(16,40,24,.06);transition:background .15s;}
    .fp-tool:hover{background:#F1F1EF;}
</style>

<div class="max-w-[1480px]" x-data>

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="siarc-card px-3.5 py-2.5 siarc-shadow">
            <p class="text-[10px] font-semibold tracking-wide text-[#A8A498] uppercase mb-0.5">{{ $isFr ? 'Niveau' : 'Level' }}</p>
            <div class="flex items-center gap-6 text-[13px] font-semibold text-[#1A1712]">
                {{ $isFr ? 'Rez-de-chaussée (RDC)' : 'Ground floor (GF)' }}
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i>
            </div>
        </div>

        <div class="siarc-card siarc-shadow flex items-center gap-2 px-4 h-[52px] flex-1 min-w-[240px]">
            <i data-lucide="search" class="w-[18px] h-[18px] text-[#B0AB9F]"></i>
            <input id="fp-search" type="text" placeholder="{{ $isFr ? 'Rechercher un stand, exposant…' : 'Search a stand, exhibitor…' }}"
                   class="w-full text-[13px] text-[#1A1712] placeholder-[#B0AB9F] outline-none bg-transparent">
        </div>

        <button type="button" class="siarc-btn siarc-card siarc-shadow px-4 h-[52px] text-[13px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
            <i data-lucide="filter" class="w-[16px] h-[16px] text-[#8A857A]"></i>{{ $isFr ? 'Filtres' : 'Filters' }}
        </button>
        <a href="{{ route('siarc.admin.stands') }}" class="siarc-btn siarc-card siarc-shadow px-4 h-[52px] text-[13px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
            <i data-lucide="sliders-horizontal" class="w-[16px] h-[16px] text-[#8A857A]"></i>{{ $isFr ? 'Légende' : 'Legend' }}
        </a>
        <button type="button" class="siarc-btn siarc-btn-green px-4 h-[52px] text-[13px] siarc-shadow">
            <i data-lucide="scan-line" class="w-[16px] h-[16px]"></i>{{ $isFr ? 'Vue 3D' : '3D view' }}
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">

        {{-- ══ INTERACTIVE FLOOR MAP ══ --}}
        <div class="siarc-card siarc-shadow p-3 sm:p-4 relative overflow-hidden">
            {{-- floating map tools --}}
            <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                <span class="fp-tool" title="{{ $isFr ? 'Sélection' : 'Select' }}"><i data-lucide="scan-line" class="w-[18px] h-[18px]"></i></span>
                <button type="button" id="fp-zoom-in" class="fp-tool" title="Zoom +"><i data-lucide="plus" class="w-[18px] h-[18px]"></i></button>
                <button type="button" id="fp-zoom-out" class="fp-tool" title="Zoom −"><i data-lucide="arrow-down" class="w-[18px] h-[18px]"></i></button>
                <button type="button" id="fp-reset" class="fp-tool" title="{{ $isFr ? 'Ajuster' : 'Fit' }}"><i data-lucide="grid-3x3" class="w-[18px] h-[18px]"></i></button>
                <button type="button" id="fp-rotate" class="fp-tool" title="{{ $isFr ? 'Réinitialiser' : 'Reset' }}"><i data-lucide="activity" class="w-[18px] h-[18px]"></i></button>
            </div>

            <div class="rounded-xl bg-[#F7F6F2] border border-[#EDEBE4] overflow-hidden">
                <svg id="fp-svg" viewBox="0 0 {{ $vbW }} {{ $vbH }}" class="w-full h-auto block select-none" style="min-height:520px" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <pattern id="fp-grid" width="28" height="28" patternUnits="userSpaceOnUse">
                            <path d="M28 0H0V28" fill="none" stroke="#ECEAE1" stroke-width="1"/>
                        </pattern>
                    </defs>
                    <rect x="0" y="0" width="{{ $vbW }}" height="{{ $vbH }}" fill="url(#fp-grid)"/>

                    <g id="fp-zoomable" transform="translate(0,0) scale(1)">
                    {{-- pavilion zone tints (soft background bands behind their stands) --}}
                    @foreach($pavs as $p)
                        @php $ps = $laid->where('pavilion_id', $p->id); @endphp
                        @if($ps->isNotEmpty())
                            @php
                                $zx = $ps->min(fn($s)=>$s->x) - 10; $zy = $ps->min(fn($s)=>$s->y) - 24;
                                $zw = $ps->max(fn($s)=>$s->x+$s->w) - $ps->min(fn($s)=>$s->x) + 20;
                                $zh = $ps->max(fn($s)=>$s->y+$s->h) - $ps->min(fn($s)=>$s->y) + 34;
                                $zc = $p->color ?? '#0F4824';
                            @endphp
                            <rect x="{{ $zx }}" y="{{ $zy }}" width="{{ $zw }}" height="{{ $zh }}" rx="12"
                                  fill="{{ $zc }}" fill-opacity="0.06" stroke="{{ $zc }}" stroke-opacity="0.28" stroke-dasharray="4 5"/>
                            <g>
                                <rect x="{{ $zx + $zw/2 - 78 }}" y="{{ $zy - 2 }}" width="156" height="24" rx="12" fill="{{ $zc }}"/>
                                <text x="{{ $zx + $zw/2 }}" y="{{ $zy + 14.5 }}" text-anchor="middle" font-size="11.5"
                                      font-weight="700" fill="#fff" style="text-transform:uppercase;letter-spacing:.04em">{{ $p->name_fr }}</text>
                            </g>
                        @endif
                    @endforeach

                    {{-- stands --}}
                    @foreach($laid as $s)
                        @php
                            $t = $stTone[$s->status] ?? ['#EDECE6', '#D6D4CB', '#8A857A', ''];
                            $lbl = $s->name_fr ? ($s->code.' — '.$s->name_fr) : ($s->code.' · '.($t[3] ?: ($isFr?'Libre':'Free')));
                        @endphp
                        <g class="fp-stand" data-code="{{ Str::lower($s->code) }}" data-name="{{ Str::lower($s->name_fr ?? '') }}"
                           data-status="{{ $s->status }}" data-exhib="{{ e($s->name_fr ?? ($isFr ? 'Libre' : 'Free')) }}"
                           data-standcode="{{ $s->code }}">
                            <title>{{ $lbl }}</title>
                            <rect x="{{ $s->x }}" y="{{ $s->y }}" width="{{ $s->w }}" height="{{ $s->h }}" rx="6"
                                  fill="{{ $t[0] }}" stroke="{{ $t[1] }}" stroke-width="1.2"/>
                            <text x="{{ $s->x + $s->w/2 }}" y="{{ $s->y + $s->h/2 + 4 }}" text-anchor="middle"
                                  font-size="{{ $s->w < 60 ? 10 : 12 }}" font-weight="700" fill="{{ $t[2] }}" pointer-events="none">{{ $s->code }}</text>
                        </g>
                    @endforeach
                    </g>
                </svg>
            </div>

            {{-- click-details panel (hidden until a stand is clicked) --}}
            <div id="fp-detail" class="hidden absolute bottom-4 left-1/2 -translate-x-1/2 siarc-card siarc-shadow-lg px-5 py-3.5 min-w-[280px] max-w-[92%]">
                <div class="flex items-center gap-3">
                    <span id="fp-detail-dot" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#E2F3E8">
                        <i data-lucide="store" class="w-[18px] h-[18px]" style="color:#157A43"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold tracking-wide text-[#A8A498] uppercase">{{ $isFr ? 'Stand' : 'Stand' }} <span id="fp-detail-code" class="text-[#3B382F]"></span></p>
                        <p id="fp-detail-exhib" class="text-[13.5px] font-bold text-[#1A1712] truncate"></p>
                    </div>
                    <span id="fp-detail-badge" class="ml-auto text-[10.5px] font-semibold px-2.5 py-1 rounded-full"></span>
                    <button type="button" id="fp-detail-close" class="ml-1 w-7 h-7 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="arrow-right" class="w-4 h-4 text-[#8A857A]"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ RIGHT RAIL ══ --}}
        <div class="space-y-5">

            {{-- Aperçu du salon --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Aperçu du salon' : 'Show overview' }}</h3>
                <div class="grid grid-cols-2 gap-3">
                    @php $ov = [
                        ['building-2','#7C4FE0','#F0EAFB',$nPav,$isFr?'Pavillons':'Pavilions','100% '.($isFr?'actifs':'active')],
                        ['grid-3x3','#3565DE','#E8EFFB',$nStands,$isFr?'Stands totaux':'Total stands','100% '.($isFr?'disponibles':'available')],
                        ['store','#C97A16','#FDF3E0',$nOcc,$isFr?'Stands occupés':'Stands taken',$occPct.'%'],
                        ['bar-chart-3','#157A43','#E2F3E8',$occPct.'%',$isFr?'Taux d\'occupation':'Occupancy',($isFr?'vs mois dernier':'vs last month')],
                    ]; @endphp
                    @foreach($ov as [$icon,$col,$tile,$val,$lbl,$sub])
                    <div class="rounded-xl border border-[#EFEDE6] p-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center mb-2" style="background:{{ $tile }}">
                            <i data-lucide="{{ $icon }}" class="w-[16px] h-[16px]" style="color:{{ $col }}"></i>
                        </span>
                        <p class="font-display text-[22px] font-extrabold text-[#161513] leading-none">{{ $val }}</p>
                        <p class="text-[11px] text-[#8A857A] mt-1 leading-tight">{{ $lbl }}</p>
                        <p class="text-[10.5px] text-siarc-green font-semibold mt-1">{{ $sub }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Légende --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-3.5">{{ $isFr ? 'Légende' : 'Legend' }}</h3>
                <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 mb-4">
                    @foreach($legend as [$st,$lbl])
                    @php $t = $stTone[$st]; @endphp
                    <span class="flex items-center gap-2 text-[12px] text-[#3B382F]">
                        <span class="w-4 h-4 rounded" style="background:{{ $t[0] }};border:1px solid {{ $t[1] }}"></span>{{ $lbl }}
                    </span>
                    @endforeach
                    <span class="flex items-center gap-2 text-[12px] text-[#3B382F]">
                        <span class="w-4 h-4 rounded" style="background:#EDECE6;border:1px solid #D6D4CB"></span>{{ $isFr ? 'Non attribué' : 'Unassigned' }}
                    </span>
                </div>
                <p class="text-[10.5px] font-semibold tracking-wide text-[#A8A498] uppercase mb-2">{{ $isFr ? 'Pavillons' : 'Pavilions' }}</p>
                <ul class="space-y-2">
                    @foreach($pavs as $p)
                    <li class="flex items-center gap-2 text-[12px]">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $p->color ?? '#0F4824' }}"></span>
                        <span class="text-[#3B382F] font-medium">{{ $p->name_fr }}</span>
                        <span class="ml-auto text-[#8A857A]">{{ optional($standsByPav->get($p->id))->count() ?? 0 }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Navigation rapide --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-3.5">{{ $isFr ? 'Navigation rapide' : 'Quick navigation' }}</h3>
                <div class="grid grid-cols-2 gap-2.5">
                    @php $navq = [
                        ['door-open','#157A43',$isFr?'Entrée principale':'Main entrance'],
                        ['presentation','#3565DE',$isFr?'Conférences & Ateliers':'Talks & Workshops'],
                        ['store','#C97A16','Restauration'],
                        ['activity','#E6B201',$isFr?'Espace Innovation':'Innovation Space'],
                    ]; @endphp
                    @foreach($navq as [$icon,$col,$lbl])
                    <span class="flex items-center gap-2 rounded-xl border border-[#EFEDE6] px-3 py-2.5 text-[11.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6] transition-colors">
                        <i data-lucide="{{ $icon }}" class="w-[16px] h-[16px]" style="color:{{ $col }}"></i>{{ $lbl }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Mini vue --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-3.5">{{ $isFr ? 'Mini vue' : 'Mini map' }}</h3>
                <div class="rounded-xl bg-[#F7F6F2] border border-[#EDEBE4] p-2 relative">
                    <svg viewBox="0 0 {{ $vbW }} {{ $vbH }}" class="w-full h-auto block" preserveAspectRatio="xMidYMid meet" style="max-height:150px">
                        <rect x="0" y="0" width="{{ $vbW }}" height="{{ $vbH }}" fill="#F1F0EB"/>
                        @foreach($laid as $s)
                        <rect x="{{ $s->x }}" y="{{ $s->y }}" width="{{ $s->w }}" height="{{ $s->h }}" rx="3" fill="{{ $fillFor($s) }}"/>
                        @endforeach
                        <rect x="1" y="1" width="{{ $vbW - 2 }}" height="{{ $vbH - 2 }}" fill="none" stroke="#157A43" stroke-width="4"/>
                    </svg>
                </div>
                <p class="text-center text-[11px] text-[#8A857A] mt-2">{{ $occPct }}% {{ $isFr ? 'occupé' : 'occupied' }}</p>
            </div>
        </div>
    </div>

    {{-- ══ STATISTIQUES PAR PAVILLON ══ --}}
    <div class="siarc-card siarc-shadow p-5 mt-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Statistiques par pavillon' : 'Stats by pavilion' }}</h3>
            <span class="text-[11.5px] font-medium text-[#8A857A] inline-flex items-center gap-1.5"><i data-lucide="circle-dot" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir en %' : 'View in %' }}</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
            @php $circ = 138.2; @endphp
            @forelse($pavStats as $ps)
            <div class="rounded-xl border border-[#EFEDE6] p-4">
                <p class="text-[12.5px] font-bold mb-2.5" style="color:{{ $ps->color }}">{{ $ps->name }}</p>
                <div class="flex items-center gap-2">
                    <ul class="text-[11px] text-[#55524A] space-y-0.5 flex-1">
                        <li>{{ $isFr ? 'Stands' : 'Stands' }}: <span class="text-[#1A1712] font-semibold">{{ $ps->tot }}</span></li>
                        <li>{{ $isFr ? 'Occupés' : 'Taken' }}: <span class="text-[#1A1712] font-semibold">{{ $ps->occ }}</span></li>
                        <li>{{ $isFr ? 'Disponibles' : 'Free' }}: <span class="text-[#1A1712] font-semibold">{{ $ps->av }}</span></li>
                    </ul>
                    <svg viewBox="0 0 50 50" class="w-[54px] h-[54px] shrink-0 -rotate-90">
                        <circle cx="25" cy="25" r="22" fill="none" stroke="#EFEDE6" stroke-width="5"/>
                        <circle cx="25" cy="25" r="22" fill="none" stroke="{{ $ps->color }}" stroke-width="5" stroke-linecap="round"
                                stroke-dasharray="{{ round($circ * $ps->pct / 100, 1) }} {{ round($circ, 1) }}"/>
                        <text x="25" y="25" transform="rotate(90 25 25)" text-anchor="middle" dominant-baseline="central"
                              font-size="11" font-weight="800" fill="#1A1712">{{ $ps->pct }}%</text>
                    </svg>
                </div>
                <p class="text-[11px] text-[#8A857A] mt-2">{{ $isFr ? 'Occupation' : 'Occupancy' }}: <span class="font-semibold text-[#3B382F]">{{ $ps->pct }}%</span></p>
            </div>
            @empty
            <div class="col-span-full text-center text-[13px] text-[#8A857A] py-8">{{ $isFr ? 'Aucun pavillon configuré.' : 'No pavilion configured.' }}</div>
            @endforelse

            {{-- Total column --}}
            <div class="rounded-xl border border-[#EFEDE6] p-4 bg-[#FBFAF6]">
                <p class="text-[12.5px] font-bold text-[#1A1712] mb-2.5">Total</p>
                <div class="flex items-center gap-2">
                    <ul class="text-[11px] text-[#55524A] space-y-0.5 flex-1">
                        <li>{{ $isFr ? 'Stands' : 'Stands' }}: <span class="text-[#1A1712] font-semibold">{{ $nStands }}</span></li>
                        <li>{{ $isFr ? 'Occupés' : 'Taken' }}: <span class="text-[#1A1712] font-semibold">{{ $nOcc }}</span></li>
                        <li>{{ $isFr ? 'Disponibles' : 'Free' }}: <span class="text-[#1A1712] font-semibold">{{ $nAvail }}</span></li>
                    </ul>
                    <svg viewBox="0 0 50 50" class="w-[54px] h-[54px] shrink-0 -rotate-90">
                        <circle cx="25" cy="25" r="22" fill="none" stroke="#EFEDE6" stroke-width="5"/>
                        <circle cx="25" cy="25" r="22" fill="none" stroke="#157A43" stroke-width="5" stroke-linecap="round"
                                stroke-dasharray="{{ round($circ * $occPct / 100, 1) }} {{ round($circ, 1) }}"/>
                        <text x="25" y="25" transform="rotate(90 25 25)" text-anchor="middle" dominant-baseline="central"
                              font-size="10" font-weight="800" fill="#1A1712">{{ $occPct }}%</text>
                    </svg>
                </div>
                <p class="text-[11px] text-[#8A857A] mt-2">{{ $isFr ? 'Occupation' : 'Occupancy' }}: <span class="font-semibold text-[#3B382F]">{{ $occPct }}%</span></p>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // ── stand click → details panel ────────────────────────────────────────
    var tones = {
        allocated: {bg:'#E2F3E8', ic:'#157A43', icon:'store',        badgeBg:'#E2F3E8', badgeFg:'#157A43', label:@json($stTone['allocated'][3])},
        reserved:  {bg:'#E8EFFB', ic:'#3565DE', icon:'ticket',       badgeBg:'#E8EFFB', badgeFg:'#3565DE', label:@json($stTone['reserved'][3])},
        available: {bg:'#F1F0EB', ic:'#8A857A', icon:'circle-dashed', badgeBg:'#F1F0EB', badgeFg:'#6F6B60', label:@json($stTone['available'][3])},
        maintenance:{bg:'#FBE7E5', ic:'#C0010C', icon:'settings',    badgeBg:'#FBE7E5', badgeFg:'#C0010C', label:'Maintenance'}
    };
    var panel = document.getElementById('fp-detail');
    var dot   = document.getElementById('fp-detail-dot');
    var codeEl= document.getElementById('fp-detail-code');
    var exhEl = document.getElementById('fp-detail-exhib');
    var badge = document.getElementById('fp-detail-badge');
    var active = null;

    document.querySelectorAll('.fp-stand').forEach(function(g){
        g.addEventListener('click', function(){
            if(active) active.classList.remove('is-active');
            active = g; g.classList.add('is-active');
            var st = g.getAttribute('data-status') || 'available';
            var t = tones[st] || tones.available;
            codeEl.textContent = g.getAttribute('data-standcode');
            exhEl.textContent = g.getAttribute('data-exhib');
            dot.style.background = t.bg;
            dot.innerHTML = '<i data-lucide="'+t.icon+'" class="w-[18px] h-[18px]" style="color:'+t.ic+'"></i>';
            badge.textContent = t.label;
            badge.style.background = t.badgeBg; badge.style.color = t.badgeFg;
            panel.classList.remove('hidden');
            if(window.lucide) lucide.createIcons();
        });
    });
    var closeBtn = document.getElementById('fp-detail-close');
    if(closeBtn) closeBtn.addEventListener('click', function(){
        panel.classList.add('hidden'); if(active){active.classList.remove('is-active'); active=null;}
    });

    // ── search filter ──────────────────────────────────────────────────────
    var search = document.getElementById('fp-search');
    if(search) search.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        document.querySelectorAll('.fp-stand').forEach(function(g){
            var hit = !q || (g.getAttribute('data-code')||'').indexOf(q) > -1 || (g.getAttribute('data-name')||'').indexOf(q) > -1;
            g.classList.toggle('fp-hidden', !hit);
        });
    });

    // ── zoom / pan controls ────────────────────────────────────────────────
    var g = document.getElementById('fp-zoomable');
    var scale = 1;
    function apply(){ g.setAttribute('transform','scale('+scale+')'); }
    var zi=document.getElementById('fp-zoom-in'), zo=document.getElementById('fp-zoom-out'),
        zr=document.getElementById('fp-reset'), rr=document.getElementById('fp-rotate');
    if(zi) zi.addEventListener('click', function(){ scale=Math.min(2.4, scale+0.2); apply(); });
    if(zo) zo.addEventListener('click', function(){ scale=Math.max(0.6, scale-0.2); apply(); });
    if(zr) zr.addEventListener('click', function(){ scale=1; apply(); });
    if(rr) rr.addEventListener('click', function(){ scale=1; apply();
        document.querySelectorAll('.fp-stand').forEach(function(x){x.classList.remove('fp-hidden');});
        if(search) search.value='';
    });
})();
</script>
@endsection
