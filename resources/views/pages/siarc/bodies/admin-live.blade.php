@php
    // ── Live monitoring: use passed vars, fall back to honest approved figures ──
    $sStats  = $sStats  ?? [];
    $sTables = $sTables ?? [];
    $sIntro  = $sIntro  ?? null;

    // KPI strip — big real-time headline metrics (approved design figures)
    $liveKpis = [
        ['users-round','#157A43','#E2F3E8','Visiteurs en direct','2 458','+18.6% vs hier','up'],
        ['id-card','#3565DE','#E8EFFB','Visiteurs accrédités','1 892','77.1% du total',null],
        ['users','#C97A16','#FDF3E0','Sessions en cours','5','Sur 12 prévues',null],
        ['store','#7C4FE0','#F0EAFB','Exposants actifs','312','Sur 580 inscrits',null],
        ['handshake','#157A43','#E2F3E8','Rendez-vous B2B','86',"Aujourd'hui",null],
        ['shield','#C0010C','#FDE8E8','Alertes actives','3','Voir les alertes','alert'],
    ];

    // Zone occupancy (heatmap tiles) — approved figures
    $zones = [
        ['Pavillon A','856','#C0010C','#FDE8E8'],
        ['Pavillon B','412','#C97A16','#FDF3E0'],
        ['Expo Hall','623','#C0010C','#FDE8E8'],
        ['Espace B2B','198','#E6B201','#FCF6DE'],
        ['Salle Conf. A','312','#E6B201','#FCF6DE'],
        ['Salle Conf. B','142','#157A43','#E2F3E8'],
    ];
    $zoneLegend = [
        ['#C0010C','Très élevé','(> 500)'],
        ['#C97A16','Élevé','(200 – 500)'],
        ['#E6B201','Moyen','(50 – 200)'],
        ['#157A43','Faible','(< 50)'],
    ];

    // Top sessions (fréquentation) — approved figures
    $topSessions = [
        ['clipboard-list',"L'artisan à l'ère du numérique",'Salle Conf. A','312'],
        ['users','Financement & accompagnement','Salle Conf. B','248'],
        ['presentation','Design Thinking pour artisans','Atelier 1','186'],
        ['megaphone','Marketing digital pour artisans','Atelier 2','132'],
        ['globe','Export & marchés internationaux','Salle Conf. C','98'],
    ];

    // Live activity feed — prefer real $sTables['Flux des entrées'], else approved sample
    $feedRows = $sTables['Flux des entrées']['rows'] ?? ($sTables[0]['rows'] ?? null);
    $activityFeed = [
        ['users-round','#157A43','#E2F3E8','Nouveau visiteur accrédité','Paul Tchameni – Cameroun','14:02'],
        ['handshake','#157A43','#E2F3E8','Rendez-vous B2B commencé','TechCraft Africa ↔ Artisanat Plus','14:01'],
        ['trending-up','#C97A16','#FDF3E0','Pic de fréquentation détecté','Pavillon A – 856 visiteurs','14:00'],
        ['play-circle','#7C4FE0','#F0EAFB','Session commencée','Conférence – Salle de Conférence A','13:58'],
        ['shield-check','#157A43','#E2F3E8','Alerte sécurité résolue','Pavillon B – Allée 3','14:55'],
    ];

    // Active alerts — approved figures
    $alerts = [
        ['triangle-alert','#C0010C','#FDE8E8','Affluence élevée','Pavillon A – Allée 2','859 visiteurs','Critique','red','14:02'],
        ['bell-ring','#C97A16','#FDF3E0','File d\'attente importante','Accréditation – Entrée principale','Temps d\'attente : 18 min','Élevée','gold','14:01'],
        ['thermometer','#E6B201','#FCF6DE','Température élevée','Hall d\'Exposition','Température : 31.2°C','Moyenne','gold','13:59'],
    ];
    $badgeTone = ['red'=>['#FDE8E8','#C0010C'],'gold'=>['#FCF6DE','#9A6A00'],'green'=>['#E2F3E8','#157A43']];
@endphp

{{-- ══ PAGE ACTION BAR ══ --}}
<div class="flex flex-wrap items-center justify-end gap-2.5 mb-4 siarc-in">
    <button class="siarc-btn text-[12.5px] bg-white border border-[#E4E1D8] text-[#3B382F] px-3.5 py-2 hover:bg-[#FBFAF6]">
        <i data-lucide="refresh-cw" class="w-4 h-4"></i>Actualiser
    </button>
    <button class="siarc-btn text-[12.5px] bg-white border border-[#E4E1D8] text-[#3B382F] px-3.5 py-2 hover:bg-[#FBFAF6]">
        <i data-lucide="bell" class="w-4 h-4"></i>Paramètres d'alertes
    </button>
    <button class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2">
        <i data-lucide="download" class="w-4 h-4"></i>Exporter le rapport
    </button>
</div>

{{-- ══ LIVE KPI STRIP ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
    @foreach($liveKpis as [$icon,$color,$tile,$label,$val,$sub,$flag])
    <div class="siarc-card siarc-shadow p-4 siarc-in" style="animation-delay:{{ $loop->index*0.04 }}s">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
        <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none tracking-tight mt-0.5">{{ $val }}</p>
        @if($flag==='up')
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-green mt-2"><i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $sub }}</span>
        @elseif($flag==='alert')
            <a href="#alertes" class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-red mt-2 hover:underline">{{ $sub }}</a>
        @else
            <p class="text-[11px] text-[#8A857A] mt-2">{{ $sub }}</p>
        @endif
    </div>
    @endforeach
</div>

{{-- ══ LIVE STREAM · SESSION EN COURS · ZONES ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-5 mb-5">

    {{-- Live stream --}}
    <div class="xl:col-span-5 siarc-card siarc-shadow p-5 siarc-in">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[14px] font-bold text-[#1A1712] flex items-center gap-2">
                <i data-lucide="presentation" class="w-4 h-4 text-siarc-green"></i>Live Stream – Salle de Conférence A
            </h3>
            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold tracking-wide text-white bg-siarc-red rounded px-2 py-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-white siarc-pulse"></span>LIVE
            </span>
        </div>
        <div class="relative rounded-xl overflow-hidden aspect-video bg-black">
            <img src="{{ asset('images/siarc/live-stream-1.png') }}" alt="Live Stream – Salle de Conférence A" class="absolute inset-0 w-full h-full object-cover">
        </div>
    </div>

    {{-- Session en cours --}}
    <div class="xl:col-span-3 siarc-card siarc-shadow p-5 siarc-in">
        <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">Session en cours</h3>
        <span class="inline-block text-[10px] font-bold tracking-wide text-[#7C4FE0] bg-[#F0EAFB] rounded px-2 py-1 mb-2.5">CONFÉRENCE</span>
        <p class="font-display text-[16px] font-bold text-[#1A1712] leading-snug">L'artisanat africain à l'ère de l'innovation et du numérique</p>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-3 text-[11.5px] text-[#8A857A]">
            <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Salle de Conférence A</span>
            <span class="inline-flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5"></i>10:30 – 12:00</span>
        </div>
        <span class="inline-block mt-2 text-[10px] font-semibold text-siarc-green bg-[#E2F3E8] rounded px-2 py-0.5">En cours</span>

        <p class="text-[10.5px] font-semibold tracking-wide text-[#B0AB9F] uppercase mt-4 mb-2">Intervenant principal</p>
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full bg-siarc-green text-white flex items-center justify-center font-display font-bold text-[13px] shrink-0">AM</span>
            <div class="min-w-0">
                <p class="text-[12.5px] font-bold text-[#1A1712] leading-tight">Dr. Alain Mbarga</p>
                <p class="text-[10.5px] text-[#8A857A] leading-tight">Expert en Innovation &amp; Transformation Digitale</p>
                <p class="text-[10.5px] text-[#8A857A]">TechCraft Africa</p>
            </div>
        </div>

        <div class="flex items-center justify-between text-[10.5px] text-[#8A857A] mt-4 mb-1"><span>10:30</span><span>12:00</span></div>
        <div class="h-1.5 rounded-full bg-[#EFEDE6] overflow-hidden"><div class="h-full rounded-full bg-siarc-green" style="width:81%"></div></div>
        <p class="text-[10.5px] text-[#B0AB9F] mt-1.5">01:12:45 / 01:30:00</p>

        <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="siarc-btn w-full justify-center mt-4 text-[12px] border border-[#E4E1D8] text-[#3B382F] py-2 hover:bg-[#FBFAF6]">
            Voir détails de la session <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    {{-- Répartition par zone --}}
    <div class="xl:col-span-4 siarc-card siarc-shadow p-5 siarc-in">
        <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">Répartition des visiteurs par zone</h3>
        <div class="flex gap-4">
            <img src="{{ asset('images/siarc/zone-map-1.png') }}" alt="Répartition des visiteurs par zone" class="flex-1 min-w-0 rounded-lg">
            <ul class="space-y-3 shrink-0 pt-1">
                @foreach($zoneLegend as [$lc,$ll,$lr])
                <li class="flex items-start gap-2 text-[10.5px]">
                    <span class="w-2.5 h-2.5 rounded-full mt-0.5 shrink-0" style="background:{{ $lc }}"></span>
                    <span class="leading-tight"><span class="font-semibold text-[#3B382F]">{{ $ll }}</span><br><span class="text-[#B0AB9F]">{{ $lr }}</span></span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

{{-- ══ FRÉQUENTATION · TOP SESSIONS · ENTRÉES/SORTIES · FLUX ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-5 mb-5">

    {{-- Fréquentation temps réel --}}
    <div class="xl:col-span-3 siarc-card siarc-shadow p-5 siarc-in">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">Fréquentation en temps réel</h3>
        <div class="relative">
            <svg viewBox="0 0 300 180" class="w-full">
                @foreach(['3K'=>25,'2K'=>70,'1K'=>115,'0'=>150] as $t=>$yy)
                    <line x1="28" y1="{{ $yy }}" x2="295" y2="{{ $yy }}" stroke="#F1F1EF"/>
                    <text x="22" y="{{ $yy+3 }}" font-size="8" fill="#B0AB9F" text-anchor="end">{{ $t }}</text>
                @endforeach
                <polygon points="28,150 28,132 60,120 92,112 124,88 156,58 188,66 220,52 252,48 295,40 295,150" fill="#157A43" opacity="0.10"/>
                <polyline points="28,132 60,120 92,112 124,88 156,58 188,66 220,52 252,48 295,40" fill="none" stroke="#157A43" stroke-width="2.2"/>
                <line x1="156" y1="24" x2="156" y2="150" stroke="#157A43" stroke-width="1" stroke-dasharray="3 3" opacity="0.5"/>
                <circle cx="156" cy="58" r="3.4" fill="#157A43" stroke="#fff" stroke-width="1.5"/>
                @foreach(['08:00','10:00','12:00','14:00','16:00','18:00'] as $i=>$d)<text x="{{ 28+$i*53 }}" y="168" font-size="7.5" fill="#B0AB9F" text-anchor="middle">{{ $d }}</text>@endforeach
            </svg>
            <div class="absolute left-[46%] top-[16%] inline-flex flex-col items-start text-[10.5px] bg-white siarc-shadow border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 leading-tight">
                <span class="font-semibold text-[#1A1712]">14:00</span>
                <span class="text-siarc-green font-semibold whitespace-nowrap">▲ 2 458 visiteurs</span>
            </div>
        </div>
    </div>

    {{-- Top sessions --}}
    <div class="xl:col-span-3 siarc-card siarc-shadow p-5 siarc-in">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">Top sessions (fréquentation)</h3>
        <ul class="space-y-3">
            @foreach($topSessions as [$icon,$title,$loc,$count])
            <li class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-4 h-4 text-siarc-green"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-semibold text-[#1A1712] leading-tight truncate">{{ $title }}</p>
                    <p class="text-[10.5px] text-[#8A857A]">{{ $loc }}</p>
                </div>
                <span class="text-[12px] font-bold text-[#1A1712] mr-2">{{ $count }}</span>
                <svg viewBox="0 0 40 16" class="w-9 h-4 shrink-0" preserveAspectRatio="none">
                    <polyline points="0,11 6,9 12,12 18,7 24,9 30,5 40,8" fill="none" stroke="#157A43" stroke-width="1.4" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Entrées / Sorties donut --}}
    <div class="xl:col-span-3 siarc-card siarc-shadow p-5 siarc-in">
        <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">Entrées / Sorties</h3>
        <div class="flex items-center gap-4">
            <div class="relative shrink-0">
                <svg viewBox="0 0 120 120" class="w-[110px] h-[110px] -rotate-90">
                    @php $circ=326.7; @endphp
                    <circle cx="60" cy="60" r="52" fill="none" stroke="#157A43" stroke-width="14" stroke-dasharray="{{ round($circ*0.56,1) }} {{ round($circ*0.44,1) }}"/>
                    <circle cx="60" cy="60" r="52" fill="none" stroke="#3565DE" stroke-width="14" stroke-dasharray="{{ round($circ*0.44,1) }} {{ round($circ*0.56,1) }}" stroke-dashoffset="{{ -round($circ*0.56,1) }}"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-[9px] text-[#8A857A]">Total</span>
                    <span class="font-display text-[19px] font-extrabold text-[#1A1712] leading-none">2 458</span>
                    <span class="text-[9px] text-[#8A857A]">visiteurs</span>
                </div>
            </div>
            <div class="flex-1 space-y-3">
                <div>
                    <p class="text-[11px] text-[#8A857A]">Entrées</p>
                    <p class="font-display text-[18px] font-extrabold text-[#1A1712] leading-none">1 372</p>
                    <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold text-siarc-green"><i data-lucide="arrow-up" class="w-3 h-3"></i>(55.8%)</span>
                </div>
                <div>
                    <p class="text-[11px] text-[#8A857A]">Sorties</p>
                    <p class="font-display text-[18px] font-extrabold text-[#1A1712] leading-none">1 086</p>
                    <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold text-siarc-red"><i data-lucide="arrow-down" class="w-3 h-3"></i>(44.2%)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Flux d'activités en direct --}}
    <div class="xl:col-span-3 siarc-card siarc-shadow p-5 siarc-in">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[13.5px] font-bold text-[#1A1712]">Flux d'activités en direct</h3>
            <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="text-[11px] font-semibold text-siarc-green hover:underline">Voir tout</a>
        </div>
        <ul class="space-y-3.5">
            @if($feedRows)
                @foreach(array_slice($feedRows,0,5) as $row)
                    @php
                        $r = isset($row['cells']) ? array_values($row['cells']) : array_values((array)$row);
                        $cell = fn($v) => is_array($v) ? ($v['badge'] ?? '') : $v;
                        $c0 = $cell($r[0] ?? '—'); $c1 = $cell($r[1] ?? ''); $c2 = $cell($r[2] ?? ''); $c3 = $cell($r[3] ?? '');
                    @endphp
                    <li class="flex gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="scan-line" class="w-4 h-4 text-siarc-green"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12px] font-semibold text-[#1A1712] leading-tight truncate">{{ $c0 !== '' ? $c0 : '—' }}</p>
                            <p class="text-[10.5px] text-[#8A857A] truncate">{{ trim($c1.' · '.$c2,' ·') }}</p>
                        </div>
                        <span class="text-[10px] text-[#B0AB9F] whitespace-nowrap">{{ $c3 }}</span>
                    </li>
                @endforeach
            @else
                @foreach($activityFeed as [$icon,$color,$tile,$title,$sub,$time])
                <li class="flex gap-3">
                    <span class="text-[10px] text-[#B0AB9F] whitespace-nowrap pt-0.5 w-10 shrink-0">{{ $time }}</span>
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $color }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-semibold text-[#1A1712] leading-tight">{{ $title }}</p>
                        <p class="text-[10.5px] text-[#8A857A] truncate flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>{{ $sub }}</p>
                    </div>
                </li>
                @endforeach
            @endif
        </ul>
        @if($sIntro)
            <p class="mt-3 pt-3 border-t border-[#F1F1EF] text-[10px] text-[#B0AB9F] flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-siarc-green siarc-pulse"></span>{{ $sIntro }}
            </p>
        @endif
    </div>
</div>

{{-- ══ ALERTES ACTIVES ══ --}}
<div id="alertes" class="siarc-in">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-[14px] font-bold text-siarc-red flex items-center gap-2">
            <i data-lucide="triangle-alert" class="w-4 h-4"></i>Alertes actives (3)
        </h3>
        <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir toutes les alertes</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($alerts as [$icon,$color,$tile,$title,$loc,$detail,$badge,$tone,$time])
        @php [$bg,$fg] = $badgeTone[$tone]; @endphp
        <div class="siarc-card siarc-shadow p-4 flex gap-3">
            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $color }}"></i></span>
            <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-[12.5px] font-bold text-[#1A1712] leading-tight">{{ $title }}</p>
                    <span class="text-[10px] font-semibold rounded px-2 py-0.5 whitespace-nowrap" style="background:{{ $bg }};color:{{ $fg }}">{{ $badge }}</span>
                </div>
                <p class="text-[11px] text-[#8A857A] mt-0.5">{{ $loc }}</p>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-[11px] text-[#3B382F] font-medium">{{ $detail }}</p>
                    <span class="text-[10px] text-[#B0AB9F]">{{ $time }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
