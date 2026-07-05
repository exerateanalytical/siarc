@extends('layouts.siarc-admin')

@php
    $isFr = $fr ?? (($lang ?? 'fr') === 'fr');
    $adminActive = 'siarc-plan';
    $sTitle = 'Plan interactif du salon';

    // ── Navigational hrefs (real routes, guarded) ───────────────────────────────
    $standsHref = \Illuminate\Support\Facades\Route::has('siarc.admin.stands')
        ? route('siarc.admin.stands') : '#';

    // ── Fixed pavilion stand rosters (verbatim from the approved design) ─────────
    $nord   = ['N-01','N-02','N-03','N-04','N-05','N-06','N-07','N-08','N-09','N-10'];
    $sud    = ['S-01','S-02','S-03','S-04','S-05','S-06','S-07','S-08','S-09','S-10'];
    $ouest  = ['O-01','O-02','O-03','O-04','O-05','O-06','O-07','O-08','O-09','O-10'];
    $est    = ['E-01','E-02','E-03','E-04','E-05','E-06','E-07','E-08','E-09','E-10'];
    $centre = [
        ['C-01','C-02','C-03','C-04','C-05','C-06'],
        ['C-07','C-08','C-09','C-10','C-11','C-12'],
        ['C-13','C-14','C-15','C-16','C-17','C-18'],
        ['C-19','C-20','C-21','C-22','C-23','C-24'],
    ];
@endphp

@push('head')
@include('pages.siarc.partials.tokens')
@endpush

@section('content')
{{-- SIARC design tokens (cards, kente, badges) for this page --}}
@include('pages.siarc.partials.tokens')

<style>
    .fp-cell{transition:filter .15s ease;cursor:pointer;}
    .fp-cell:hover rect{filter:brightness(1.05);}
    .fp-hidden{opacity:.16;}
    .fp-tool{width:38px;height:38px;border-radius:10px;background:#fff;border:1px solid #E7E4DC;
        display:flex;align-items:center;justify-content:center;color:#3B382F;
        box-shadow:0 2px 6px rgba(16,40,24,.06);transition:background .15s;}
    .fp-tool:hover{background:#F1F1EF;}
    .fp-tool.is-active{background:#0B3A1E;border-color:#0B3A1E;color:#fff;}
</style>

<div class="max-w-[1480px]">

    {{-- ── Toolbar ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="siarc-card px-3.5 py-2 siarc-shadow min-w-[236px]">
            <p class="text-[10px] font-semibold tracking-wide text-[#A8A498] uppercase mb-0.5">{{ $isFr ? 'Niveau' : 'Level' }}</p>
            <div class="flex items-center justify-between gap-6 text-[13px] font-semibold text-[#1A1712]">
                {{ $isFr ? 'Rez-de-chaussée (RDC)' : 'Ground floor (GF)' }}
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i>
            </div>
        </div>

        <div class="siarc-card siarc-shadow flex items-center gap-2 px-4 h-[52px] flex-1 min-w-[240px]">
            <input id="fp-search" type="text" placeholder="{{ $isFr ? 'Rechercher un stand, exposant…' : 'Search a stand, exhibitor…' }}"
                   class="w-full text-[13px] text-[#1A1712] placeholder-[#B0AB9F] outline-none bg-transparent">
            <i data-lucide="search" class="w-[18px] h-[18px] text-[#B0AB9F]"></i>
        </div>

        <button type="button" class="siarc-btn siarc-card siarc-shadow px-4 h-[52px] text-[13px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
            <i data-lucide="filter" class="w-[16px] h-[16px] text-[#8A857A]"></i>{{ $isFr ? 'Filtres' : 'Filters' }}
        </button>
        <button type="button" class="siarc-btn siarc-card siarc-shadow px-4 h-[52px] text-[13px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
            <i data-lucide="list" class="w-[16px] h-[16px] text-[#8A857A]"></i>{{ $isFr ? 'Légende' : 'Legend' }}
        </button>
        <button type="button" class="siarc-btn siarc-btn-green px-4 h-[52px] text-[13px] siarc-shadow">
            <i data-lucide="box" class="w-[16px] h-[16px]"></i>{{ $isFr ? 'Vue 3D' : '3D view' }}
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-5 items-start">

        {{-- ══ INTERACTIVE FLOOR MAP ══ --}}
        <div class="siarc-card siarc-shadow p-3 sm:p-4 relative overflow-hidden">
            {{-- floating map tools --}}
            <div class="absolute top-6 left-6 z-10 flex flex-col gap-2.5">
                <span class="fp-tool is-active" title="{{ $isFr ? 'Sélection' : 'Select' }}"><i data-lucide="mouse-pointer-2" class="w-[18px] h-[18px]"></i></span>
                <button type="button" id="fp-zoom-in" class="fp-tool mt-1" title="Zoom +"><i data-lucide="plus" class="w-[18px] h-[18px]"></i></button>
                <button type="button" id="fp-zoom-out" class="fp-tool" title="Zoom −"><i data-lucide="minus" class="w-[18px] h-[18px]"></i></button>
                <button type="button" id="fp-reset" class="fp-tool mt-1" title="{{ $isFr ? 'Ajuster' : 'Fit' }}"><i data-lucide="maximize" class="w-[18px] h-[18px]"></i></button>
                <button type="button" id="fp-rotate" class="fp-tool" title="{{ $isFr ? 'Réinitialiser' : 'Reset' }}"><i data-lucide="rotate-ccw" class="w-[18px] h-[18px]"></i></button>
            </div>

            <div class="rounded-xl bg-white overflow-hidden">
                <svg id="fp-svg" viewBox="0 0 1000 720" class="w-full h-auto block select-none" style="min-height:560px" preserveAspectRatio="xMidYMid meet">
                    <g id="fp-zoomable">

                    {{-- ═══ HALL OUTER STRUCTURE ═══ --}}
                    {{-- outer walls / corridors --}}
                    <rect x="70" y="70" width="860" height="580" rx="4" fill="#F7F6F2" stroke="#D9D6CD" stroke-width="2"/>
                    <rect x="96" y="128" width="808" height="470" rx="3" fill="#FFFFFF" stroke="#E4E1D8" stroke-width="1.4"/>

                    {{-- structural column ticks along the top / bottom corridors --}}
                    @for($i=0;$i<9;$i++)
                        <rect x="{{ 150 + $i*84 }}" y="120" width="12" height="10" fill="#DedbD2"/>
                        <rect x="{{ 150 + $i*84 }}" y="596" width="12" height="10" fill="#DEDBD2"/>
                    @endfor

                    {{-- ── ENTRÉE PRINCIPALE (top) ── --}}
                    <g>
                        <text x="500" y="94" text-anchor="middle" font-size="11" font-weight="700" fill="#3B382F"
                              style="letter-spacing:.05em">{{ $isFr ? 'ENTRÉE' : 'ENTRANCE' }}</text>
                        <text x="500" y="108" text-anchor="middle" font-size="11" font-weight="700" fill="#3B382F"
                              style="letter-spacing:.05em">{{ $isFr ? 'PRINCIPALE' : 'MAIN' }}</text>
                        <path d="M500 114 v14 M494 122 l6 6 l6 -6" fill="none" stroke="#157A43" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>

                    {{-- ── ENTRÉE SECONDAIRE (bottom) ── --}}
                    <g>
                        <path d="M500 616 v-14 M494 608 l6 -6 l6 6" fill="none" stroke="#157A43" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <text x="500" y="632" text-anchor="middle" font-size="11" font-weight="700" fill="#3B382F"
                              style="letter-spacing:.05em">{{ $isFr ? 'ENTRÉE' : 'ENTRANCE' }}</text>
                        <text x="500" y="646" text-anchor="middle" font-size="11" font-weight="700" fill="#3B382F"
                              style="letter-spacing:.05em">{{ $isFr ? 'SECONDAIRE' : 'SECONDARY' }}</text>
                    </g>

                    {{-- restroom markers --}}
                    @php $wc = [[250,150],[655,150],[250,568],[655,568]]; @endphp
                    @foreach($wc as [$wx,$wy])
                        <g transform="translate({{ $wx }},{{ $wy }})">
                            <rect x="-13" y="-11" width="26" height="22" rx="4" fill="#EFEDE6"/>
                            <circle cx="-4" cy="0" r="4.2" fill="none" stroke="#8A857A" stroke-width="1.6"/>
                            <circle cx="6" cy="0" r="4.2" fill="none" stroke="#8A857A" stroke-width="1.6"/>
                        </g>
                    @endforeach

                    {{-- ═══ ESPACE CONFÉRENCES & ATELIERS (top-right) ═══ --}}
                    <g>
                        <rect x="812" y="128" width="92" height="128" rx="3" fill="#EFEDE6" stroke="#D9D6CD" stroke-width="1.4"/>
                        <text x="858" y="168" text-anchor="middle" font-size="9.5" font-weight="700" fill="#55524A" style="letter-spacing:.03em">ESPACE</text>
                        <text x="858" y="182" text-anchor="middle" font-size="9.5" font-weight="700" fill="#55524A" style="letter-spacing:.03em">CONFÉRENCES</text>
                        <text x="858" y="196" text-anchor="middle" font-size="9.5" font-weight="700" fill="#55524A" style="letter-spacing:.03em">&amp; ATELIERS</text>
                        <g transform="translate(858,222)" stroke="#8A857A" stroke-width="1.6" fill="none">
                            <circle cx="-8" cy="-2" r="3"/><path d="M-13 8 a5 5 0 0 1 10 0"/>
                            <circle cx="8" cy="-2" r="3"/><path d="M3 8 a5 5 0 0 1 10 0"/>
                            <circle cx="0" cy="-5" r="3.4"/><path d="M-6 8 a6 6 0 0 1 12 0"/>
                        </g>
                    </g>

                    {{-- ═══ ESPACE INNOVATION (bottom-left) ═══ --}}
                    <g>
                        <rect x="96" y="470" width="92" height="128" rx="3" fill="#FBF3DF" stroke="#EBD9A6" stroke-width="1.4"/>
                        <g transform="translate(142,512)" stroke="#C97A16" stroke-width="1.8" fill="none" stroke-linecap="round">
                            <path d="M0 -10 a9 9 0 0 1 5 16 h-10 a9 9 0 0 1 5 -16 Z"/>
                            <path d="M-4 9 h8 M-3 13 h6"/>
                        </g>
                        <text x="142" y="548" text-anchor="middle" font-size="9.5" font-weight="700" fill="#B26A0F" style="letter-spacing:.03em">ESPACE</text>
                        <text x="142" y="562" text-anchor="middle" font-size="9.5" font-weight="700" fill="#B26A0F" style="letter-spacing:.03em">INNOVATION</text>
                    </g>

                    {{-- ═══ ESPACE RESTAURATION (bottom-right) ═══ --}}
                    <g>
                        <rect x="812" y="470" width="92" height="128" rx="3" fill="#FBF3DF" stroke="#EBD9A6" stroke-width="1.4"/>
                        <g transform="translate(858,512)" stroke="#C97A16" stroke-width="1.8" fill="none" stroke-linecap="round">
                            <path d="M-6 -11 v10 M-3 -11 v10 M-4.5 -1 v13 M6 -11 v9 a3 3 0 0 1 -3 3 v11"/>
                        </g>
                        <text x="858" y="548" text-anchor="middle" font-size="9.5" font-weight="700" fill="#B26A0F" style="letter-spacing:.03em">ESPACE</text>
                        <text x="858" y="562" text-anchor="middle" font-size="9.5" font-weight="700" fill="#B26A0F" style="letter-spacing:.03em">RESTAURATION</text>
                    </g>

                    {{-- ═══════════════ PAVILLON NORD (green, top row) ═══════════════ --}}
                    <g>
                        <rect x="286" y="180" width="440" height="60" rx="6" fill="#EAF4EC" stroke="#CFE6D6" stroke-width="1.2"/>
                        <rect x="437" y="172" width="126" height="22" rx="11" fill="#157A43"/>
                        <text x="500" y="187" text-anchor="middle" font-size="11" font-weight="700" fill="#fff" style="letter-spacing:.04em">PAVILLON NORD</text>
                        @foreach($nord as $i => $c)
                        <g class="fp-cell" data-code="{{ Str::lower($c) }}" data-status="available" data-standcode="{{ $c }}" data-exhib="{{ $isFr?'Disponible':'Available' }}">
                            <title>{{ $c }}</title>
                            <rect x="{{ 292 + $i*43 }}" y="204" width="39" height="30" rx="4" fill="#DCEEE1" stroke="#BFDFC8" stroke-width="1"/>
                            <text x="{{ 292 + $i*43 + 19.5 }}" y="223" text-anchor="middle" font-size="9.5" font-weight="700" fill="#2C5B3B" pointer-events="none">{{ $c }}</text>
                        </g>
                        @endforeach
                    </g>

                    {{-- ═══════════════ PAVILLON SUD (red, bottom row) ═══════════════ --}}
                    <g>
                        <rect x="286" y="486" width="440" height="60" rx="6" fill="#FBECEC" stroke="#F0CFCE" stroke-width="1.2"/>
                        <rect x="437" y="478" width="126" height="22" rx="11" fill="#C0010C"/>
                        <text x="500" y="493" text-anchor="middle" font-size="11" font-weight="700" fill="#fff" style="letter-spacing:.04em">PAVILLON SUD</text>
                        @foreach($sud as $i => $c)
                        <g class="fp-cell" data-code="{{ Str::lower($c) }}" data-status="maintenance" data-standcode="{{ $c }}" data-exhib="Maintenance">
                            <title>{{ $c }}</title>
                            <rect x="{{ 292 + $i*43 }}" y="510" width="39" height="30" rx="4" fill="#F7D9D6" stroke="#E9B4B0" stroke-width="1"/>
                            <text x="{{ 292 + $i*43 + 19.5 }}" y="529" text-anchor="middle" font-size="9.5" font-weight="700" fill="#8A2018" pointer-events="none">{{ $c }}</text>
                        </g>
                        @endforeach
                    </g>

                    {{-- ═══════════════ PAVILLON OUEST (blue, left column) ═══════════════ --}}
                    <g>
                        <rect x="200" y="266" width="60" height="188" rx="6" fill="#E9EFFB" stroke="#CBD9F4" stroke-width="1.2"/>
                        <g transform="translate(216,360)">
                            <text transform="rotate(-90)" text-anchor="middle" font-size="11" font-weight="700" fill="#254FA8" style="letter-spacing:.04em">PAVILLON OUEST</text>
                        </g>
                        @foreach($ouest as $i => $c)
                        <g class="fp-cell" data-code="{{ Str::lower($c) }}" data-status="reserved" data-standcode="{{ $c }}" data-exhib="{{ $isFr?'Réservé':'Reserved' }}">
                            <title>{{ $c }}</title>
                            <rect x="232" y="{{ 272 + $i*18.2 }}" width="70" height="15" rx="3" fill="#D6E2FA" stroke="#B6CCF3" stroke-width="0.9"/>
                            <text x="267" y="{{ 272 + $i*18.2 + 11 }}" text-anchor="middle" font-size="9" font-weight="700" fill="#254FA8" pointer-events="none">{{ $c }}</text>
                        </g>
                        @endforeach
                    </g>

                    {{-- ═══════════════ PAVILLON EST (purple, right column) ═══════════════ --}}
                    <g>
                        <rect x="740" y="266" width="60" height="188" rx="6" fill="#F1EBFB" stroke="#DDCEF3" stroke-width="1.2"/>
                        <g transform="translate(784,360)">
                            <text transform="rotate(-90)" text-anchor="middle" font-size="11" font-weight="700" fill="#6A3FC0" style="letter-spacing:.04em">PAVILLON EST</text>
                        </g>
                        @foreach($est as $i => $c)
                        <g class="fp-cell" data-code="{{ Str::lower($c) }}" data-status="reserved" data-standcode="{{ $c }}" data-exhib="{{ $isFr?'Réservé':'Reserved' }}">
                            <title>{{ $c }}</title>
                            <rect x="698" y="{{ 272 + $i*18.2 }}" width="70" height="15" rx="3" fill="#E4D8F7" stroke="#CFB9EF" stroke-width="0.9"/>
                            <text x="733" y="{{ 272 + $i*18.2 + 11 }}" text-anchor="middle" font-size="9" font-weight="700" fill="#6A3FC0" pointer-events="none">{{ $c }}</text>
                        </g>
                        @endforeach
                    </g>

                    {{-- ═══════════════ PAVILLON CENTRE (gold/orange, middle grid) ═══════════════ --}}
                    <g>
                        <rect x="330" y="292" width="340" height="172" rx="8" fill="#FEF6E7" stroke="#F1DCA6" stroke-width="1.4" stroke-dasharray="5 5"/>
                        <rect x="437" y="284" width="126" height="22" rx="11" fill="#E6B201"/>
                        <text x="500" y="299" text-anchor="middle" font-size="11" font-weight="700" fill="#3a2a00" style="letter-spacing:.04em">PAVILLON CENTRE</text>
                        @foreach($centre as $r => $row)
                            @foreach($row as $c => $code)
                            <g class="fp-cell" data-code="{{ Str::lower($code) }}" data-status="allocated" data-standcode="{{ $code }}" data-exhib="{{ $isFr?'Occupé':'Occupied' }}">
                                <title>{{ $code }}</title>
                                <rect x="{{ 344 + $c*52 }}" y="{{ 316 + $r*34 }}" width="46" height="28" rx="4" fill="#FBE4B4" stroke="#EEC974" stroke-width="1"/>
                                <text x="{{ 344 + $c*52 + 23 }}" y="{{ 316 + $r*34 + 18 }}" text-anchor="middle" font-size="9.5" font-weight="700" fill="#8A5A0E" pointer-events="none">{{ $code }}</text>
                            </g>
                            @endforeach
                        @endforeach
                    </g>

                    </g>
                </svg>
            </div>

            {{-- click-details panel (hidden until a stand is clicked) --}}
            <div id="fp-detail" class="hidden absolute bottom-6 left-1/2 -translate-x-1/2 siarc-card siarc-shadow-lg px-5 py-3.5 min-w-[280px] max-w-[92%]">
                <div class="flex items-center gap-3">
                    <span id="fp-detail-dot" class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#FDF3E0">
                        <i data-lucide="store" class="w-[18px] h-[18px]" style="color:#C97A16"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold tracking-wide text-[#A8A498] uppercase">{{ $isFr ? 'Stand' : 'Stand' }} <span id="fp-detail-code" class="text-[#3B382F]"></span></p>
                        <p id="fp-detail-exhib" class="text-[13.5px] font-bold text-[#1A1712] truncate"></p>
                    </div>
                    <span id="fp-detail-badge" class="ml-auto text-[10.5px] font-semibold px-2.5 py-1 rounded-full"></span>
                    <button type="button" id="fp-detail-close" class="ml-1 w-7 h-7 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="x" class="w-4 h-4 text-[#8A857A]"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══ RIGHT RAIL ══ --}}
        <div class="space-y-5">

            {{-- Aperçu du salon --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Aperçu du salon' : 'Show overview' }}</h3>
                <div class="grid grid-cols-2 gap-3">
                    @php $ov = [
                        ['building-2','#7C4FE0','#F0EAFB','16','Pavillons','100% actifs','#157A43'],
                        ['grid-3x3','#3565DE','#E8EFFB','512','Stands totaux','100% disponibles','#157A43'],
                        ['store','#7C4FE0','#F0EAFB','378','Stands occupés','73.8%','#8A857A'],
                        ['bar-chart-3','#C97A16','#FDF3E0','73.8%','Taux d\'occupation','↑ 12.6% vs dernier mois','#157A43'],
                    ]; @endphp
                    @foreach($ov as [$icon,$col,$tile,$val,$lbl,$sub,$subCol])
                    <div class="rounded-xl border border-[#EFEDE6] p-3">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center mb-2.5" style="background:{{ $tile }}">
                            <i data-lucide="{{ $icon }}" class="w-[17px] h-[17px]" style="color:{{ $col }}"></i>
                        </span>
                        <p class="font-display text-[23px] font-extrabold text-[#161513] leading-none">{{ $val }}</p>
                        <p class="text-[11px] text-[#8A857A] mt-1.5 leading-tight">{{ $lbl }}</p>
                        <p class="text-[10.5px] font-semibold mt-1" style="color:{{ $subCol }}">{{ $sub }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Légende --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-3.5">{{ $isFr ? 'Légende' : 'Legend' }}</h3>
                @php $legend = [
                    ['#DCEEE1','#BFDFC8','store','#2C5B3B','Disponible'],
                    ['#FBE4B4','#EEC974','store','#8A5A0E','Occupé'],
                    ['#FBF3DF','#EBD9A6','store','#B26A0F','Réservé'],
                    ['#F7D9D6','#E9B4B0','wrench','#8A2018','Maintenance'],
                    ['#EFEDE6','#D9D6CD','minus','#8A857A','Non attribué'],
                ]; @endphp
                <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                    @foreach($legend as [$bg,$bd,$icon,$ic,$lbl])
                    <span class="flex items-center gap-2 text-[12px] text-[#3B382F]">
                        <span class="w-[22px] h-[22px] rounded-md flex items-center justify-center shrink-0" style="background:{{ $bg }};border:1px solid {{ $bd }}">
                            <i data-lucide="{{ $icon }}" class="w-3 h-3" style="color:{{ $ic }}"></i>
                        </span>{{ $lbl }}
                    </span>
                    @endforeach
                </div>
            </div>

            {{-- Navigation rapide --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-3.5">{{ $isFr ? 'Navigation rapide' : 'Quick navigation' }}</h3>
                <div class="grid grid-cols-2 gap-2.5">
                    @php $navq = [
                        ['door-open','#157A43','Entrée principale'],
                        ['presentation','#3565DE','Conférences & Ateliers'],
                        ['utensils','#C97A16','Restauration'],
                        ['lightbulb','#E6B201','Espace Innovation'],
                    ]; @endphp
                    @foreach($navq as [$icon,$col,$lbl])
                    <button type="button" class="flex items-center gap-2 rounded-xl border border-[#EFEDE6] px-3 py-2.5 text-[11.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6] transition-colors text-left">
                        <i data-lucide="{{ $icon }}" class="w-[16px] h-[16px] shrink-0" style="color:{{ $col }}"></i>{{ $lbl }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Mini vue --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-3.5">{{ $isFr ? 'Mini vue' : 'Mini map' }}</h3>
                <div class="rounded-xl bg-[#F1F0EB] border border-[#EDEBE4] p-3 relative">
                    <svg viewBox="0 0 300 190" class="w-full h-auto block" preserveAspectRatio="xMidYMid meet">
                        <rect x="0" y="0" width="300" height="190" fill="#F1F0EB"/>
                        {{-- centre --}}
                        <rect x="95" y="70" width="110" height="52" rx="3" fill="#FBE4B4"/>
                        {{-- nord --}}
                        <rect x="80" y="46" width="140" height="16" rx="2" fill="#DCEEE1"/>
                        {{-- sud --}}
                        <rect x="80" y="130" width="140" height="16" rx="2" fill="#F7D9D6"/>
                        {{-- ouest --}}
                        <rect x="56" y="62" width="18" height="66" rx="2" fill="#D6E2FA"/>
                        {{-- est --}}
                        <rect x="226" y="62" width="18" height="66" rx="2" fill="#E4D8F7"/>
                        {{-- corner spaces --}}
                        <rect x="18" y="130" width="30" height="40" fill="#FBF3DF"/>
                        <rect x="252" y="130" width="30" height="40" fill="#FBF3DF"/>
                        <rect x="252" y="34" width="30" height="40" fill="#EFEDE6"/>
                        {{-- viewport frame --}}
                        <rect x="6" y="6" width="288" height="178" fill="none" stroke="#157A43" stroke-width="4"/>
                    </svg>
                    <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 translate-y-1/2 siarc-card px-2.5 py-1 text-[11px] font-bold text-[#157A43] siarc-shadow">73.8%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ STATISTIQUES PAR PAVILLON ══ --}}
    <div class="siarc-card siarc-shadow p-5 mt-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[15px] font-bold text-[#1A1712]">{{ $isFr ? 'Statistiques par pavillon' : 'Stats by pavilion' }}</h3>
            <span class="text-[11.5px] font-medium text-[#8A857A] inline-flex items-center gap-1.5"><i data-lucide="circle" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Voir en %' : 'View in %' }}</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
            @php $circ = 138.2; $pavStats = [
                ['Pavillon Centre','#C97A16',80,68,12,85,'85%'],
                ['Pavillon Nord','#157A43',80,58,22,73,'73%'],
                ['Pavillon Sud','#C0010C',80,52,28,65,'65%'],
                ['Pavillon Ouest','#3565DE',80,70,10,88,'88%'],
                ['Pavillon Est','#7C4FE0',80,68,12,85,'85%'],
                ['Total','#157A43',512,378,134,73.8,'73.8%'],
            ]; @endphp
            @foreach($pavStats as $i => [$name,$color,$tot,$occ,$av,$pct,$pctLbl])
            <div class="rounded-xl border border-[#EFEDE6] p-4 {{ $i===5 ? 'bg-[#FBFAF6]' : '' }}">
                <p class="text-[12.5px] font-bold mb-2.5" style="color:{{ $i===5 ? '#1A1712' : $color }}">{{ $name }}</p>
                <div class="flex items-center gap-2">
                    <ul class="text-[11px] text-[#55524A] space-y-0.5 flex-1">
                        <li>{{ $isFr ? 'Stands' : 'Stands' }}: <span class="text-[#1A1712] font-semibold">{{ $tot }}</span></li>
                        <li>{{ $isFr ? 'Occupés' : 'Taken' }}: <span class="text-[#1A1712] font-semibold">{{ $occ }}</span></li>
                        <li>{{ $isFr ? 'Disponibles' : 'Free' }}: <span class="text-[#1A1712] font-semibold">{{ $av }}</span></li>
                    </ul>
                    <svg viewBox="0 0 50 50" class="w-[54px] h-[54px] shrink-0 -rotate-90">
                        <circle cx="25" cy="25" r="22" fill="none" stroke="#EFEDE6" stroke-width="5"/>
                        <circle cx="25" cy="25" r="22" fill="none" stroke="{{ $color }}" stroke-width="5" stroke-linecap="round"
                                stroke-dasharray="{{ round($circ * $pct / 100, 1) }} {{ round($circ, 1) }}"/>
                        <text x="25" y="25" transform="rotate(90 25 25)" text-anchor="middle" dominant-baseline="central"
                              font-size="{{ $i===5 ? 9.5 : 11 }}" font-weight="800" fill="#1A1712">{{ $pctLbl }}</text>
                    </svg>
                </div>
                <p class="text-[11px] text-[#8A857A] mt-2">{{ $isFr ? 'Occupation' : 'Occupancy' }}: <span class="font-semibold text-[#3B382F]">{{ $pctLbl }}</span></p>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
(function(){
    var tones = {
        allocated:  {bg:'#FDF3E0', ic:'#C97A16', icon:'store',  badgeBg:'#FDF3E0', badgeFg:'#C97A16', label:@json($isFr?'Occupé':'Occupied')},
        reserved:   {bg:'#FBF3DF', ic:'#B26A0F', icon:'ticket', badgeBg:'#FBF3DF', badgeFg:'#B26A0F', label:@json($isFr?'Réservé':'Reserved')},
        available:  {bg:'#DCEEE1', ic:'#2C5B3B', icon:'circle-check', badgeBg:'#DCEEE1', badgeFg:'#2C5B3B', label:@json($isFr?'Disponible':'Available')},
        maintenance:{bg:'#F7D9D6', ic:'#C0010C', icon:'wrench', badgeBg:'#F7D9D6', badgeFg:'#C0010C', label:'Maintenance'}
    };
    var panel = document.getElementById('fp-detail');
    var dot   = document.getElementById('fp-detail-dot');
    var codeEl= document.getElementById('fp-detail-code');
    var exhEl = document.getElementById('fp-detail-exhib');
    var badge = document.getElementById('fp-detail-badge');
    var active = null;

    document.querySelectorAll('.fp-cell').forEach(function(g){
        g.addEventListener('click', function(){
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
    if(closeBtn) closeBtn.addEventListener('click', function(){ panel.classList.add('hidden'); });

    // search filter
    var search = document.getElementById('fp-search');
    if(search) search.addEventListener('input', function(){
        var q = this.value.trim().toLowerCase();
        document.querySelectorAll('.fp-cell').forEach(function(g){
            var hit = !q || (g.getAttribute('data-code')||'').indexOf(q) > -1;
            g.classList.toggle('fp-hidden', !hit);
        });
    });

    // zoom controls
    var g = document.getElementById('fp-zoomable');
    var scale = 1;
    function apply(){ g.setAttribute('transform','translate(500,360) scale('+scale+') translate(-500,-360)'); }
    var zi=document.getElementById('fp-zoom-in'), zo=document.getElementById('fp-zoom-out'),
        zr=document.getElementById('fp-reset'), rr=document.getElementById('fp-rotate');
    if(zi) zi.addEventListener('click', function(){ scale=Math.min(2.4, scale+0.2); apply(); });
    if(zo) zo.addEventListener('click', function(){ scale=Math.max(0.6, scale-0.2); apply(); });
    if(zr) zr.addEventListener('click', function(){ scale=1; apply(); });
    if(rr) rr.addEventListener('click', function(){ scale=1; apply();
        document.querySelectorAll('.fp-cell').forEach(function(x){x.classList.remove('fp-hidden');});
        if(search) search.value='';
        if(panel) panel.classList.add('hidden');
    });
})();
</script>
@endsection
