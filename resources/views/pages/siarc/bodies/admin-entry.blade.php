@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;

    // Real routes (all guaranteed present in the route map) used as safe fallbacks.
    $entryUrl     = route('siarc.admin.entry',     ['lang'=>$lang]);
    $liveUrl      = route('siarc.admin.live',      ['lang'=>$lang]);
    $incidentsUrl = route('siarc.admin.incidents', ['lang'=>$lang]);
    $visitorsUrl  = route('siarc.admin.visitors',  ['lang'=>$lang]);
    $badgesUrl    = route('siarc.admin.badges',    ['lang'=>$lang]);
    $reportsUrl   = route('siarc.admin.reports',   ['lang'=>$lang]);
    $floorplanUrl = route('siarc.admin.floorplan', ['lang'=>$lang]);

    // ── KPI tiles (approved design figures, verbatim) ────────────────────────
    // [icon, iconColor, tileBg, label, value, sub|null, trend|null, trendDir, sparkColor|null, sparkPts|null]
    $spark = [
        'green' => '2,26 8,24 14,25 20,20 26,21 32,15 38,17 44,10 50,12 56,6',
        'blue'  => '2,24 8,25 14,20 20,22 26,16 32,18 38,12 44,14 50,8 56,6',
        'purple'=> '2,24 8,20 14,22 20,17 26,19 32,13 38,15 44,9 50,11 56,5',
        'orange'=> '2,25 8,22 14,24 20,18 26,20 32,14 38,16 44,12 50,7 56,9',
    ];
    $kpis = [
        ['users-round','#157A43','#E2F3E8','Entrées aujourd\'hui','3,247',null,'18.6% vs hier','up','#157A43',$spark['green']],
        ['users','#2F6BE0','#E7EFFC','Total visiteurs','15,842','Depuis le 27 Juillet 2026',null,null,'#2F6BE0',$spark['blue']],
        ['id-card','#7C4FE0','#F0EAFB','Visiteurs en cours','1,248','Dans l\'enceinte',null,null,'#7C4FE0',$spark['purple']],
        ['door-open','#C97A16','#FDF3E0','Sorties aujourd\'hui','2,189',null,'12.3% vs hier','up','#C97A16',$spark['orange']],
    ];

    // Live entries line-chart (Entrées vs Sorties) — hourly, design-shaped
    $chartEntrees = [40,90,150,240,430,760,980,1180,1520,1620,1740,1660,1560,1380,1180,1020,980];
    $chartSorties = [10,30,60,110,180,290,420,560,720,820,900,880,860,780,700,620,560];
    $chartMax = 2000;
    $chartLabels = ['00:00','04:00','08:00','12:00','16:00','20:00','24:00'];

    // Donut — Entrées par point d'accès
    $accessPoints = [
        ['Entrée Principale',   '2,045', '62.9%', '#157A43'],
        ['Entrée Secondaire',    '687',  '21.1%', '#2F6BE0'],
        ['Entrée VIP',           '265',  '8.2%',  '#E06B16'],
        ['Pavillon Innovation',  '150',  '4.6%',  '#7C4FE0'],
        ['Autres',               '100',  '3.1%',  '#B0AB9F'],
    ];
    $donutSlices = [
        ['#157A43',62.9],['#2F6BE0',21.1],['#E06B16',8.2],['#7C4FE0',4.6],['#B0AB9F',3.1],
    ];

    // Live camera tiles — cropped from the design PNG
    $cameras = [
        ['entry-cam-1.png','Entrée Principale'],
        ['entry-cam-2.png','Entrée Secondaire'],
        ['entry-cam-3.png','Pavillon Innovation'],
        ['entry-cam-4.png','Entrée VIP'],
    ];

    // Statut des accès (4 mini tiles)
    $accessStatus = [
        ['users-round',  '#157A43', '#E2F3E8', 'Accès autorisés', '3,247', '18.6%', 'up'],
        ['user-x',       '#C0010C', '#FDE8E8', 'Accès refusés',      '42',  '5.0%',  'up'],
        ['clock',        '#E6B201', '#FEF6DE', 'En attente',         '18',  '10.0%', 'down'],
        ['id-card',      '#7C4FE0', '#F0EAFB', 'Badges imprimés', '2,987', '15.2%', 'up'],
    ];

    // Activité des points d'accès table
    $apRows = [
        ['id-card','Entrée Principale','Porte A','Général','#E2F3E8','#157A43','3,000','2,045','1,326','68%','#157A43','Actif'],
        ['id-card','Entrée Secondaire','Porte B','Général','#E2F3E8','#157A43','2,000','687','563','34%','#E6B201','Actif'],
        ['id-card','Entrée VIP','Porte C','VIP','#F0EAFB','#7C4FE0','500','265','188','53%','#157A43','Actif'],
        ['id-card','Pavillon Innovation','Porte D','Exposants','#FDF3E0','#C97A16','1,000','150','87','15%','#157A43','Actif'],
        ['id-card','Accès Staff','Porte E','Staff','#F1F1EF','#55524A','300','100','25','33%','#E6B201','Actif'],
    ];

    // Flux d'entrées en direct (verbatim from PNG)
    $feed = [
        ['entry-face-1.png',null,'Marie Claire Abessolo','Visiteur Général • CM-24876','Entrée Principale','10:24:35','Autorisé'],
        ['entry-face-2.png',null,'Paul Tchameni','Exposant • EX-1245','Pavillon Centre - Stand C-09','10:24:20','Autorisé'],
        ['entry-face-3.png',null,'Sophie Ngono','Visiteur Général • CM-24877','Entrée Secondaire','10:24:10','Autorisé'],
        [null,'unknown','Visitor Unknown','Badge non reconnu','Entrée Principale','10:23:58','Refusé'],
        ['entry-face-5.png',null,'Jean Claude Mbarga','VIP • VIP-0021','Entrée VIP','10:23:40','Autorisé'],
        [null,'logo','Art Bois Précieux SARL','Exposant • EX-0895','Pavillon Innovation - Stand I-15','10:23:30','Autorisé'],
    ];

    // Alertes en temps réel
    $alerts = [
        ['activity',      '#C0010C','#FDE8E8', 'Capacité élevée',        'Entrée Principale (68% de capacité)', '10:24'],
        ['activity',      '#C97A16','#FDF3E0', 'Afflux inhabituel',      "+35% d'entrées en 15 min",            '10:20'],
        ['shield-alert',  '#2F6BE0','#E7EFFC', 'Badge invalide détecté', "3 tentatives d'accès refusées",       '10:18'],
        ['check-circle-2','#157A43','#E2F3E8', 'Système opérationnel',   "Tous les points d'accès fonctionnent",'10:15'],
    ];

    // Raccourcis rapides — [icon, label, tileBg, iconColor, url]
    $shortcuts = [
        ['scan-line',      'Scanner Badge',      '#F0EAFB','#7C4FE0', $entryUrl],
        ['search',         'Rechercher Visiteur','#E7EFFC','#2F6BE0', $visitorsUrl],
        ['printer',        'Imprimer Badge',     '#E2F3E8','#157A43', $badgesUrl],
        ['clipboard-list', 'Rapports Accès',     '#FDF3E0','#C97A16', $reportsUrl],
    ];

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

{{-- ══ KPI TILES ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5 siarc-in">
    @foreach($kpis as [$icon,$color,$tile,$label,$val,$sub,$trend,$dir,$sparkCol,$sparkPts])
        <div class="siarc-card siarc-shadow p-4 siarc-lift">
            <div class="flex items-start justify-between">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
                </span>
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight mt-1">{{ $label }}</p>
            </div>
            <p class="text-[26px] font-extrabold text-[#161513] leading-none tracking-tight mt-3">{{ $val }}</p>
            <div class="flex items-end justify-between mt-1.5">
                <div>
                    @if($trend)
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-green">
                            <i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $trend }}
                        </span>
                    @elseif($sub)
                        <span class="text-[11px] text-[#B0AB9F]">{{ $sub }}</span>
                    @endif
                </div>
                <svg viewBox="0 0 58 30" class="w-14 h-7 shrink-0" preserveAspectRatio="none">
                    <polyline points="{{ $sparkPts }}" fill="none" stroke="{{ $sparkCol }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    @endforeach

    {{-- Capacité actuelle — donut gauge --}}
    <div class="siarc-card siarc-shadow p-4 siarc-lift">
        <div class="flex items-start justify-between">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#E2F3E8">
                <i data-lucide="clock" class="w-5 h-5" style="color:#157A43"></i>
            </span>
            <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight mt-1">Capacité actuelle</p>
        </div>
        <div class="flex items-end justify-between mt-3">
            <div>
                <p class="text-[26px] font-extrabold text-[#161513] leading-none tracking-tight">42%</p>
                <p class="text-[11px] text-[#B0AB9F] mt-1.5">3,247 / 7,500</p>
            </div>
            <div class="relative w-12 h-12 shrink-0">
                <svg viewBox="0 0 44 44" class="w-12 h-12 -rotate-90">
                    <circle cx="22" cy="22" r="18" fill="none" stroke="#EDF3EE" stroke-width="5"/>
                    <circle cx="22" cy="22" r="18" fill="none" stroke="#157A43" stroke-width="5" stroke-linecap="round"
                        stroke-dasharray="{{ round(113.1*0.42,1) }} 113.1"/>
                </svg>
                <span class="absolute inset-0 flex items-center justify-center text-[9px] font-bold text-[#157A43]">42%</span>
            </div>
        </div>
    </div>
</div>

{{-- ══ MAIN GRID : 3 columns ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    {{-- ═════ LEFT COLUMN (span 5) ═════ --}}
    <div class="lg:col-span-5 space-y-5">

        {{-- ── Entrées en temps réel (line chart) ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-[15px] font-bold text-[#1A1712]">Entrées en temps réel</h3>
                <button type="button" data-toast="Filtre de période à venir" class="text-[11px] font-medium text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1 inline-flex items-center gap-1">
                    Aujourd'hui <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
            </div>
            <div class="flex items-center gap-4 mb-2 text-[11px] text-[#3B382F]">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 rounded" style="background:#157A43"></span>Entrées</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 border-t border-dashed" style="border-color:#157A43"></span>Sorties</span>
            </div>
            <svg viewBox="0 0 {{ $cw }} {{ $ch }}" class="w-full" preserveAspectRatio="none">
                @foreach(['2K'=>2000,'1.5K'=>1500,'1K'=>1000,'500'=>500,'0'=>0] as $lbl=>$v)
                    @php $gy = $baseY - ($plotH*$v/2000); @endphp
                    <line x1="{{ $px }}" y1="{{ round($gy,1) }}" x2="{{ $cw-8 }}" y2="{{ round($gy,1) }}" stroke="#F1F1EF"/>
                    <text x="{{ $px-6 }}" y="{{ round($gy,1)+3 }}" font-size="9" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>
                @endforeach
                <polygon points="{{ $areaPts }}" fill="#157A43" opacity="0.08"/>
                <polyline points="{{ $sortiesPts }}" fill="none" stroke="#157A43" stroke-width="2" stroke-dasharray="5 4" opacity="0.5"/>
                <polyline points="{{ $entreesPts }}" fill="none" stroke="#157A43" stroke-width="2.4"/>
                @foreach($chartEntrees as $i=>$v)
                    @if($i % 2 === 0)
                        <circle cx="{{ round($ptX($i,count($chartEntrees)),1) }}" cy="{{ round($ptY($v),1) }}" r="2.6" fill="#157A43"/>
                    @endif
                @endforeach
                @foreach($chartLabels as $i=>$d)
                    <text x="{{ round($px + ($plotW*$i/(count($chartLabels)-1)),1) }}" y="{{ $ch-6 }}" font-size="9" fill="#B0AB9F" text-anchor="middle">{{ $d }}</text>
                @endforeach
            </svg>
        </div>

        {{-- ── Statut des accès (4 mini tiles) ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Statut des accès</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($accessStatus as [$icon,$color,$tile,$label,$val,$trend,$dir])
                    <div class="rounded-xl border border-[#F1F0EB] p-3">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                            <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
                        </span>
                        <p class="mt-2.5 text-[10.5px] text-[#8A857A] font-medium leading-tight">{{ $label }}</p>
                        <p class="text-[20px] font-extrabold text-[#161513] leading-tight tracking-tight mt-0.5">{{ $val }}</p>
                        <span class="inline-flex items-center gap-0.5 text-[10.5px] font-semibold mt-0.5 {{ $dir==='up' ? ($label==='Accès refusés' ? 'text-siarc-red' : 'text-siarc-green') : 'text-siarc-red' }}">
                            <i data-lucide="arrow-{{ $dir }}" class="w-3 h-3"></i>{{ $trend }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Activité des points d'accès (table) ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Activité des points d'accès</h3>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-left border-collapse min-w-[560px]">
                    <thead>
                        <tr class="text-[9.5px] font-bold tracking-wide uppercase text-[#A8A498]">
                            <th class="pb-3 px-1 font-bold">Point d'accès</th>
                            <th class="pb-3 px-1 font-bold">Type</th>
                            <th class="pb-3 px-1 font-bold">Capacité max</th>
                            <th class="pb-3 px-1 font-bold">Entrées auj.</th>
                            <th class="pb-3 px-1 font-bold">Sorties auj.</th>
                            <th class="pb-3 px-1 font-bold">Occupation</th>
                            <th class="pb-3 px-1 font-bold">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11.5px]">
                        @foreach($apRows as [$icon,$name,$door,$type,$tBg,$tFg,$cap,$ent,$sor,$occ,$occCol,$status])
                            <tr class="border-t border-[#F1F0EB]">
                                <td class="py-3 px-1">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:#F5F3EE">
                                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-[#55524A]"></i>
                                        </span>
                                        <span class="leading-tight">
                                            <span class="block font-semibold text-[#1A1712]">{{ $name }}</span>
                                            <span class="block text-[10px] text-[#8A857A]">{{ $door }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-1">
                                    <span class="text-[10px] font-semibold rounded-md px-2 py-1 whitespace-nowrap" style="background:{{ $tBg }};color:{{ $tFg }}">{{ $type }}</span>
                                </td>
                                <td class="py-3 px-1 font-medium text-[#3B382F]">{{ $cap }}</td>
                                <td class="py-3 px-1 font-medium text-[#3B382F]">{{ $ent }}</td>
                                <td class="py-3 px-1 font-medium text-[#3B382F]">{{ $sor }}</td>
                                <td class="py-3 px-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-semibold text-[#3B382F] w-8">{{ $occ }}</span>
                                        <span class="flex-1 h-1.5 rounded-full bg-[#EFEDE6] overflow-hidden min-w-[52px]">
                                            <span class="block h-full rounded-full" style="width:{{ $occ }};background:{{ $occCol }}"></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-1">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold rounded-full px-2 py-1" style="background:#E2F3E8;color:#157A43">
                                        <span class="w-1.5 h-1.5 rounded-full bg-siarc-green"></span>{{ $status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═════ MIDDLE COLUMN (span 4) ═════ --}}
    <div class="lg:col-span-4 space-y-5">

        {{-- ── Entrées par point d'accès (donut) ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Entrées par point d'accès</h3>
            <div class="flex items-center gap-4">
                <svg viewBox="0 0 120 120" class="w-[122px] h-[122px] shrink-0 -rotate-90">
                    @php $circ=326.7; $off=0; @endphp
                    @foreach($donutSlices as [$col,$pct])
                        <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="15"
                            stroke-dasharray="{{ round($circ*$pct/100,1) }} {{ round($circ-($circ*$pct/100),1) }}"
                            stroke-dashoffset="{{ -round($off,1) }}"/>
                        @php $off += $circ*$pct/100; @endphp
                    @endforeach
                    <text x="60" y="57" transform="rotate(90 60 60)" text-anchor="middle" font-size="17" font-weight="800" fill="#1A1712" font-family="Playfair Display, serif">3,247</text>
                    <text x="60" y="71" transform="rotate(90 60 60)" text-anchor="middle" font-size="8" fill="#8A857A">Entrées</text>
                </svg>
                <ul class="flex-1 space-y-2.5">
                    @foreach($accessPoints as [$lbl,$val,$pct,$col])
                        <li class="flex items-center gap-2 text-[11px]">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span>
                            <span class="text-[#3B382F] font-medium truncate">{{ $lbl }}</span>
                            <span class="ml-auto text-[#8A857A] whitespace-nowrap">{{ $val }} ({{ $pct }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- ── Flux d'entrées en direct ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-[#1A1712]">Flux d'entrées en direct</h3>
                <a href="{{ $entryUrl }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir tout</a>
            </div>
            <ul class="space-y-3">
                @foreach($feed as [$img,$avatarKind,$name,$meta,$loc,$time,$status])
                    @php $refused = $status === 'Refusé'; @endphp
                    <li class="flex items-start gap-3">
                        @if($img)
                            <img src="{{ asset('images/siarc/'.$img) }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                        @elseif($avatarKind === 'logo')
                            <span class="w-10 h-10 rounded-full siarc-adire flex items-center justify-center shrink-0 overflow-hidden">
                                <i data-lucide="store" class="w-5 h-5 text-siarc-gold"></i>
                            </span>
                        @else
                            <span class="w-10 h-10 rounded-full bg-[#F1F1EF] flex items-center justify-center shrink-0">
                                <i data-lucide="user" class="w-5 h-5 text-[#B0AB9F]"></i>
                            </span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[12.5px] font-bold text-[#1A1712] leading-tight truncate">{{ $name }}</p>
                                <span class="text-[10px] text-[#B0AB9F] whitespace-nowrap shrink-0 mt-0.5">{{ $time }}</span>
                            </div>
                            <p class="text-[11px] text-[#8A857A] leading-tight mt-0.5 truncate">{{ $meta }}</p>
                            <div class="flex items-center justify-between gap-2 mt-1">
                                <p class="text-[11px] text-[#8A857A] leading-tight truncate">{{ $loc }}</p>
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold rounded-md px-2 py-0.5 shrink-0"
                                    style="{{ $refused ? 'background:#FDE8E8;color:#C0010C' : 'background:#E2F3E8;color:#157A43' }}">
                                    <i data-lucide="{{ $refused ? 'x-circle' : 'check-circle-2' }}" class="w-3 h-3"></i>{{ $status }}
                                </span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- ═════ RIGHT COLUMN (span 3) ═════ --}}
    <div class="lg:col-span-3 space-y-5">

        {{-- ── Caméras en direct ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-[#1A1712]">Caméras en direct</h3>
                <a href="{{ $liveUrl }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir toutes</a>
            </div>
            <div class="grid grid-cols-2 gap-3">
                @foreach($cameras as [$camImg,$camLabel])
                    <a href="{{ $liveUrl }}" class="block">
                        <div class="relative rounded-xl overflow-hidden border border-[#EFEDE6]">
                            <img src="{{ asset('images/siarc/'.$camImg) }}" alt="{{ $camLabel }}" class="w-full h-[62px] object-cover">
                            <span class="absolute top-1.5 left-1.5 inline-flex items-center gap-1 text-[8px] font-bold tracking-wide text-white bg-siarc-red rounded px-1.5 py-0.5">
                                <span class="w-1 h-1 rounded-full bg-white siarc-pulse"></span> LIVE
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-siarc-green shrink-0"></span>
                            <p class="text-[10px] font-medium text-[#3B382F] leading-tight truncate">{{ $camLabel }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ── Alertes en temps réel ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-[#1A1712]">Alertes en temps réel</h3>
                <a href="{{ $incidentsUrl }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir tout</a>
            </div>
            <ul class="space-y-2.5">
                @foreach($alerts as [$icon,$color,$tile,$title,$sub,$time])
                    <li class="flex gap-3 rounded-xl p-3" style="background:{{ $tile }}80">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $color }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[12px] font-semibold text-[#1A1712] leading-tight">{{ $title }}</p>
                                <span class="text-[10px] text-[#8A857A] whitespace-nowrap shrink-0">{{ $time }}</span>
                            </div>
                            <p class="text-[11px] text-[#8A857A] leading-tight mt-0.5">{{ $sub }}</p>
                            @if($title !== 'Système opérationnel')
                                <a href="{{ $incidentsUrl }}" class="inline-flex items-center gap-1 text-[10.5px] font-semibold mt-1" style="color:{{ $color }}">
                                    Voir détails <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10.5px] font-semibold text-siarc-green mt-1">OK</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- ── Raccourcis rapides ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Raccourcis rapides</h3>
            <div class="grid grid-cols-4 gap-3">
                @foreach($shortcuts as [$icon,$label,$tile,$color,$url])
                    <a href="{{ $url }}" class="rounded-xl border border-[#EFEDE6] p-3 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors text-center">
                        <span class="w-9 h-9 mx-auto rounded-lg flex items-center justify-center mb-2" style="background:{{ $tile }}">
                            <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]" style="color:{{ $color }}"></i>
                        </span>
                        <p class="text-[10px] font-semibold text-[#3B382F] leading-tight">{{ $label }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
