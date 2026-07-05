@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;

    // ── Normalise passed data ────────────────────────────────────────────────
    $stats  = collect($sStats ?? [])->values();
    $table  = collect($sTables ?? [])->first();
    $tCols  = data_get($table, 'cols', ['Type','ID','Porte','Heure']);
    $tRows  = collect(data_get($table, 'rows', []));
    $links  = collect($sLinks ?? []);

    // Icon / colour skin for the 3 REAL $sStats tiles (Entrées / Inscrits / Scans)
    $statSkin = [
        ['users-round', '#157A43', '#E2F3E8'],
        ['id-card',     '#3565DE', '#E8EFFB'],
        ['scan-line',   '#7C4FE0', '#F0EAFB'],
    ];

    // ── Aggregate illustrative datasets (approved design metrics) ────────────
    // Live entries line-chart (Entrées vs Sorties) — hourly, design-shaped
    $chartEntrees = [40,90,150,240,430,760,980,1180,1520,1620,1740,1660,1560,1380,1180,1020,980];
    $chartSorties = [10,30,60,110,180,290,420,560,720,820,900,880,860,780,700,620,560];
    $chartMax = 2000;
    $chartLabels = ['00:00','04:00','08:00','12:00','16:00','20:00','24:00'];

    // Donut — entries per access point
    $accessPoints = [
        ['Entrée Principale',   2045, 62.9, '#157A43'],
        ['Entrée Secondaire',    687, 21.1, '#3565DE'],
        ['Entrée VIP',           265,  8.2, '#7C4FE0'],
        ['Pavillon Innovation',  150,  4.6, '#E6B201'],
        ['Autres',               100,  3.1, '#B0AB9F'],
    ];
    $donutTotal = 3247;

    // Live camera tiles (honest placeholders)
    $cameras = [
        ['Caméra 1 — Entrée Principale'],
        ['Caméra 2 — Entrée Secondaire'],
        ['Caméra 3 — Pavillon Innovation'],
        ['Caméra 4 — Entrée VIP'],
    ];

    // Access-status tiles (approved aggregate figures)
    $accessStatus = [
        ['shield-check',  '#157A43', '#E2F3E8', 'Accès autorisés', '3,247', '18.6%', 'up'],
        ['circle-dot',    '#C0010C', '#FDE8E8', 'Accès refusés',       '42',  '5.0%', 'up'],
        ['clock',         '#E6B201', '#FEF6DE', 'En attente',          '18', '10.0%', 'down'],
        ['id-card',       '#7C4FE0', '#F0EAFB', 'Badges imprimés',  '2,987', '15.2%', 'up'],
    ];

    // Real-time alerts (honest/aggregate — no invented named individuals)
    $alerts = [
        ['activity',      '#C0010C','#FDE8E8', 'Capacité élevée',        'Entrée Principale (68% de capacité)', '10:24'],
        ['activity',      '#C97A16','#FDF3E0', 'Afflux inhabituel',      "+35% d'entrées en 15 min",            '10:20'],
        ['shield-check',  '#3565DE','#E8EFFB', 'Badge invalide détecté', "3 tentatives d'accès refusées",       '10:18'],
        ['check-circle-2','#157A43','#E2F3E8', 'Système opérationnel',   'Tous les points d\'accès fonctionnent','10:15'],
    ];

    // Passage-type badge tone
    $tone = function($t){
        $t = strtolower((string)$t);
        if (str_contains($t,'refus') || str_contains($t,'declin') || str_contains($t,'unknown') || str_contains($t,'inconnu')) return ['#FDE8E8','#C0010C','Refusé'];
        if (str_contains($t,'exposant') || str_contains($t,'vip'))  return ['#E8EFFB','#3565DE',null];
        if (str_contains($t,'staff'))                                return ['#F1F1EF','#55524A',null];
        return ['#E2F3E8','#157A43',null];
    };

    // SVG helpers for the line chart
    $cw = 660; $ch = 210; $px = 34; $py = 14; $baseY = 176;
    $plotW = $cw - $px - 8; $plotH = $baseY - $py;
    $ptX = fn($i,$n) => $px + ($plotW * ($n<=1 ? 0 : $i/($n-1)));
    $ptY = fn($v) => $baseY - ($plotH * min($v,$chartMax) / $chartMax);
    $line = function($series) use ($ptX,$ptY){
        $n = count($series); $pts = [];
        foreach($series as $i=>$v){ $pts[] = round($ptX($i,$n),1).','.round($ptY($v),1); }
        return implode(' ', $pts);
    };
    $entreesPts = $line($chartEntrees);
    $sortiesPts = $line($chartSorties);
    $areaPts = $entreesPts.' '.round($ptX(count($chartEntrees)-1,count($chartEntrees)),1).','.$baseY.' '.$px.','.$baseY;
@endphp

{{-- ══ PAGE HEADER ══ --}}
<div class="mb-5 siarc-in">
    <h1 class="font-display text-[26px] font-extrabold text-[#0B3A1E] leading-tight">Contrôle d'accès</h1>
    <p class="text-[13px] text-[#55524A] mt-0.5">Surveillez les entrées en temps réel et gérez l'accès au SIARC 2026.</p>
</div>

{{-- ══ KPI TILES : 3 real $sStats + 2 aggregate ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($stats as $i => $st)
        @php [$icon,$color,$tile] = $statSkin[$i % count($statSkin)]; @endphp
        <div class="siarc-card siarc-shadow p-4 siarc-lift">
            <div class="flex items-start justify-between">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
                </span>
                @if(data_get($st,'trend'))
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-green">
                        <i data-lucide="arrow-up" class="w-3 h-3"></i>{{ data_get($st,'trend') }}
                    </span>
                @endif
            </div>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ data_get($st,4) }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight mt-0.5">{{ data_get($st,3) }}</p>
            @if(data_get($st,5))
                <p class="text-[11px] text-[#B0AB9F] mt-1">{{ data_get($st,5) }}</p>
            @endif
        </div>
    @endforeach

    {{-- Aggregate tile — Sorties aujourd'hui --}}
    <div class="siarc-card siarc-shadow p-4 siarc-lift">
        <div class="flex items-start justify-between">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#FDF3E0">
                <i data-lucide="door-open" class="w-5 h-5" style="color:#C97A16"></i>
            </span>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-green">
                <i data-lucide="arrow-up" class="w-3 h-3"></i>12.3%
            </span>
        </div>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium leading-tight">Sorties aujourd'hui</p>
        <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight mt-0.5">2,189</p>
        <p class="text-[11px] text-[#B0AB9F] mt-1">vs hier</p>
    </div>

    {{-- Aggregate tile — Capacité actuelle (mini gauge) --}}
    <div class="siarc-card siarc-shadow p-4 siarc-lift">
        <div class="flex items-start justify-between">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#E2F3E8">
                <i data-lucide="clock" class="w-5 h-5" style="color:#157A43"></i>
            </span>
            <svg viewBox="0 0 44 44" class="w-11 h-11 -rotate-90 shrink-0">
                <circle cx="22" cy="22" r="18" fill="none" stroke="#EDF3EE" stroke-width="5"/>
                <circle cx="22" cy="22" r="18" fill="none" stroke="#157A43" stroke-width="5" stroke-linecap="round"
                    stroke-dasharray="{{ round(113.1*0.42,1) }} 113.1"/>
            </svg>
        </div>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium leading-tight">Capacité actuelle</p>
        <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight mt-0.5">42%</p>
        <p class="text-[11px] text-[#B0AB9F] mt-1">3,247 / 7,500</p>
    </div>
</div>

{{-- ══ SCAN PANEL ══ --}}
<div class="siarc-card siarc-shadow overflow-hidden mb-5">
    <div class="siarc-kente"></div>
    <div class="p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <p class="siarc-kicker text-siarc-green">Point de contrôle</p>
                <h2 class="font-display text-[19px] font-bold text-[#1A1712] mt-1.5">Scannez un badge</h2>
            </div>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#8A857A] border border-[#EFEDE6] rounded-lg px-3 py-1.5">
                <i data-lucide="door-open" class="w-3.5 h-3.5"></i> Entrée Principale
            </span>
        </div>

        <div class="grid lg:grid-cols-3 gap-5 items-stretch">
            {{-- dashed scan frame --}}
            <div class="lg:col-span-2 relative rounded-2xl border-2 border-dashed border-[#CBD8CF] bg-[#F7FAF8] px-6 py-10 text-center flex flex-col items-center justify-center">
                <span class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background:#E2F3E8">
                    <i data-lucide="qr-code" class="w-8 h-8" style="color:#157A43"></i>
                </span>
                <p class="text-[14px] font-semibold text-[#1A1712]">Présentez le badge devant la caméra</p>
                <p class="text-[12px] text-[#8A857A] mt-1 max-w-md">La vérification QR s'effectue automatiquement à l'approche du code.</p>
                <span class="inline-flex items-center gap-1.5 mt-4 text-[11px] font-semibold text-siarc-green">
                    <span class="w-2 h-2 rounded-full bg-siarc-green siarc-pulse"></span> Lecteur prêt
                </span>
            </div>

            {{-- manual code entry + quick links --}}
            <div class="flex flex-col">
                <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">Ou saisissez le code du badge</label>
                <div class="relative mb-2.5">
                    <i data-lucide="scan-line" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#B0AB9F]"></i>
                    <input type="text" placeholder="Ex. CM-24876" aria-label="Code du badge"
                        class="w-full pl-9 pr-3 py-3 rounded-xl border border-[#E4E1D8] text-[13px] text-[#1A1712] placeholder-[#B0AB9F] focus:outline-none focus:border-siarc-green focus:ring-2 focus:ring-[#157A43]/15 bg-white">
                </div>
                <button type="button" class="siarc-btn siarc-btn-green px-6 py-3 text-[13px] justify-center w-full">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i> Valider
                </button>
                <p class="text-[11px] text-[#B0AB9F] mt-2 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> Chaque validation est enregistrée dans le journal.
                </p>

                @if($links->isNotEmpty())
                <div class="mt-auto pt-4 grid grid-cols-1 gap-2.5">
                    @foreach($links as $lk)
                        @php
                            $lkLabel = data_get($lk,'label', is_string($lk) ? $lk : data_get($lk,0, 'Ouvrir'));
                            $lkUrl   = data_get($lk,'url', is_string($lk) ? null : data_get($lk,1));
                            $lkIcon  = data_get($lk,'icon', str_contains(strtolower((string)$lkLabel),'mobile') ? 'scan-line' : 'qr-code');
                        @endphp
                        @if($lkUrl)
                            <a href="{{ $lkUrl }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] p-2.5 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#E2F3E8">
                                    <i data-lucide="{{ $lkIcon }}" class="w-4 h-4" style="color:#157A43"></i>
                                </span>
                                <span class="text-[12px] font-semibold text-[#3B382F] leading-tight">{{ $lkLabel }}</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 ml-auto text-[#B0AB9F]"></i>
                            </a>
                        @else
                            <div class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] p-2.5">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#E2F3E8">
                                    <i data-lucide="{{ $lkIcon }}" class="w-4 h-4" style="color:#157A43"></i>
                                </span>
                                <span class="text-[12px] font-semibold text-[#3B382F] leading-tight">{{ $lkLabel }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══ CHARTS ROW : line chart + donut + cameras ══ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">

    {{-- ── Line chart : Entrées en temps réel ── --}}
    <div class="siarc-card siarc-shadow p-5">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-[14px] font-bold text-[#1A1712]">Entrées en temps réel</h3>
            <span class="text-[11px] font-medium text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1 inline-flex items-center gap-1">
                Aujourd'hui <i data-lucide="chevron-down" class="w-3 h-3"></i>
            </span>
        </div>
        <div class="flex items-center gap-4 mb-2 text-[11px]">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 rounded" style="background:#157A43"></span>Entrées</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 rounded border-b border-dashed" style="border-color:#157A43"></span>Sorties</span>
        </div>
        <svg viewBox="0 0 {{ $cw }} {{ $ch }}" class="w-full" preserveAspectRatio="none">
            {{-- grid + y labels --}}
            @foreach(['2K'=>0,'1.5K'=>500,'1K'=>1000,'500'=>1500,'0'=>2000] as $lbl=>$v)
                @php $gy = $baseY - ($plotH*(2000-$v)/2000); @endphp
                <line x1="{{ $px }}" y1="{{ round($gy,1) }}" x2="{{ $cw-8 }}" y2="{{ round($gy,1) }}" stroke="#F1F1EF"/>
                <text x="{{ $px-6 }}" y="{{ round($gy,1)+3 }}" font-size="9" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>
            @endforeach
            {{-- area under entries --}}
            <polygon points="{{ $areaPts }}" fill="#157A43" opacity="0.08"/>
            {{-- sorties (dashed) --}}
            <polyline points="{{ $sortiesPts }}" fill="none" stroke="#157A43" stroke-width="2" stroke-dasharray="5 4" opacity="0.55"/>
            {{-- entrées (solid) --}}
            <polyline points="{{ $entreesPts }}" fill="none" stroke="#157A43" stroke-width="2.4"/>
            {{-- entries dots --}}
            @foreach($chartEntrees as $i=>$v)
                @if($i % 2 === 0)
                    <circle cx="{{ round($ptX($i,count($chartEntrees)),1) }}" cy="{{ round($ptY($v),1) }}" r="2.6" fill="#157A43"/>
                @endif
            @endforeach
            {{-- x labels --}}
            @foreach($chartLabels as $i=>$d)
                <text x="{{ round($px + ($plotW*$i/(count($chartLabels)-1)),1) }}" y="{{ $ch-6 }}" font-size="9" fill="#B0AB9F" text-anchor="middle">{{ $d }}</text>
            @endforeach
        </svg>
    </div>

    {{-- ── Donut : Entrées par point d'accès ── --}}
    <div class="siarc-card siarc-shadow p-5">
        <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Entrées par point d'accès</h3>
        <div class="flex items-center gap-4">
            <svg viewBox="0 0 120 120" class="w-[118px] h-[118px] shrink-0 -rotate-90">
                @php $circ=326.7; $off=0; @endphp
                @foreach($accessPoints as [$lbl,$val,$pct,$col])
                    <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="15"
                        stroke-dasharray="{{ round($circ*$pct/100,1) }} {{ round($circ-($circ*$pct/100),1) }}"
                        stroke-dashoffset="{{ -round($off,1) }}"/>
                    @php $off += $circ*$pct/100; @endphp
                @endforeach
                <text x="60" y="58" transform="rotate(90 60 60)" text-anchor="middle" font-size="16" font-weight="800" fill="#1A1712" font-family="Playfair Display, serif">{{ number_format($donutTotal,0,',',' ') }}</text>
                <text x="60" y="72" transform="rotate(90 60 60)" text-anchor="middle" font-size="8" fill="#8A857A">Entrées</text>
            </svg>
            <ul class="flex-1 space-y-2">
                @foreach($accessPoints as [$lbl,$val,$pct,$col])
                    <li class="flex items-center gap-2 text-[11px]">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span>
                        <span class="text-[#3B382F] font-medium truncate">{{ $lbl }}</span>
                        <span class="ml-auto text-[#8A857A] whitespace-nowrap">{{ number_format($val,0,',',' ') }} ({{ $pct }}%)</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ── Live cameras ── --}}
    <div class="siarc-card siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-[#1A1712]">Caméras en direct</h3>
            @php $camAll = $h('siarc.admin.entry') @endphp
            @if($camAll)
                <a href="{{ $camAll }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir toutes</a>
            @endif
        </div>
        <div class="grid grid-cols-2 gap-3">
            @foreach($cameras as [$camLabel])
                <div class="rounded-xl overflow-hidden siarc-adire border border-[#EFEDE6]">
                    <div class="relative h-24 flex items-center justify-center">
                        <i data-lucide="video" class="w-7 h-7 text-white/40"></i>
                        <span class="absolute top-2 left-2 inline-flex items-center gap-1 text-[9px] font-bold tracking-wide text-white bg-siarc-red/90 rounded px-1.5 py-0.5">
                            <span class="w-1 h-1 rounded-full bg-white siarc-pulse"></span> LIVE
                        </span>
                    </div>
                    <div class="bg-white px-2.5 py-2 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-siarc-green shrink-0"></span>
                        <p class="text-[10px] font-medium text-[#3B382F] leading-tight truncate">{{ $camLabel }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ ACCESS STATUS ROW ══ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    @foreach($accessStatus as [$icon,$color,$tile,$label,$val,$trend,$dir])
        <div class="siarc-card siarc-shadow p-4 siarc-lift">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ $label }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight mt-0.5">{{ $val }}</p>
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold mt-1 {{ $dir==='up' ? ($label==='Accès refusés' ? 'text-siarc-red' : 'text-siarc-green') : 'text-[#C97A16]' }}">
                <i data-lucide="arrow-{{ $dir === 'up' ? 'up' : 'down' }}" class="w-3 h-3"></i>{{ $trend }}
            </span>
        </div>
    @endforeach
</div>

{{-- ══ BOTTOM ROW : live feed + alerts ══ --}}
<div class="grid lg:grid-cols-3 gap-5">

    {{-- ── Derniers passages (REAL $sTables) ── --}}
    <div class="lg:col-span-2 siarc-card siarc-shadow p-5 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-[#1A1712]">Derniers passages</h3>
            <span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold tracking-wide text-siarc-green bg-[#E2F3E8] rounded-full px-2.5 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-siarc-green siarc-pulse"></span> EN DIRECT
            </span>
        </div>

        @if($tRows->isNotEmpty())
            <ul class="space-y-2.5 flex-1">
                @foreach($tRows as $row)
                    @php
                        $cells  = data_get($row,'cells', $row);
                        $coerce = fn($c) => is_array($c) ? (string)($c['badge'] ?? '') : (string)$c;
                        $type   = $coerce(data_get($cells,0));
                        $id     = $coerce(data_get($cells,1));
                        $porte  = $coerce(data_get($cells,2));
                        $heure  = $coerce(data_get($cells,3));
                        [$bg,$fg,$override] = $tone($type);
                        $displayType = $override ?: ($type ?: '—');
                        $initials = strtoupper(mb_substr(trim($id) !== '' ? $id : $type, 0, 2));
                    @endphp
                    <li class="flex items-center gap-3 rounded-xl border border-[#F1F0EB] p-3 hover:border-[#E4E1D8] transition-colors">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 text-[11px] font-bold" style="background:{{ $bg }};color:{{ $fg }}">
                            {{ $initials ?: '—' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight truncate">{{ $id ?: '—' }}</p>
                                <span class="text-[10px] font-semibold rounded-full px-2 py-0.5 shrink-0" style="background:{{ $bg }};color:{{ $fg }}">{{ $displayType }}</span>
                            </div>
                            <p class="text-[11px] text-[#8A857A] truncate flex items-center gap-1 mt-0.5">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>{{ $porte ?: 'Porte —' }}
                            </p>
                        </div>
                        @php $isRefused = str_contains(strtolower($displayType),'refus'); @endphp
                        <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold rounded-lg px-2 py-1 shrink-0"
                            style="{{ $isRefused ? 'background:#FDE8E8;color:#C0010C' : 'background:#E2F3E8;color:#157A43' }}">
                            <i data-lucide="{{ $isRefused ? 'circle-dot' : 'check-circle-2' }}" class="w-3 h-3"></i>{{ $isRefused ? 'Refusé' : 'Autorisé' }}
                        </span>
                        <span class="text-[11px] font-medium text-[#B0AB9F] whitespace-nowrap">{{ $heure ?: '—' }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center py-12">
                <span class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3" style="background:#F1F1EF">
                    <i data-lucide="circle-dashed" class="w-7 h-7 text-[#B0AB9F]"></i>
                </span>
                <p class="text-[13px] font-semibold text-[#55524A]">Aucun passage pour le moment</p>
                <p class="text-[12px] text-[#8A857A] mt-1">Les entrées apparaîtront ici dès le premier scan.</p>
            </div>
        @endif

        @php $journal = $h('siarc.admin.activity') @endphp
        @if($journal)
            <a href="{{ $journal }}" class="mt-4 pt-4 border-t border-[#F1F0EB] text-[12px] font-semibold text-siarc-green inline-flex items-center gap-1 hover:gap-1.5 transition-all">
                Voir tout le journal <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        @endif
    </div>

    {{-- ── Alertes en temps réel (honest / aggregate) ── --}}
    <div class="siarc-card siarc-shadow p-5 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-[#1A1712]">Alertes en temps réel</h3>
            @php $alertsAll = $h('siarc.admin.activity') @endphp
            @if($alertsAll)
                <a href="{{ $alertsAll }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir tout</a>
            @endif
        </div>
        <ul class="space-y-2.5 flex-1">
            @foreach($alerts as [$icon,$color,$tile,$title,$sub,$time])
                <li class="flex gap-3 rounded-xl p-3" style="background:{{ $tile }}55">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $color }}"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight truncate">{{ $title }}</p>
                            <span class="text-[10px] text-[#8A857A] whitespace-nowrap shrink-0">{{ $time }}</span>
                        </div>
                        <p class="text-[11px] text-[#8A857A] leading-tight mt-0.5">{{ $sub }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
