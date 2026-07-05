@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';
    $addHref  = $h('siarc.admin.pavilions');
    $planHref = $h('siarc.admin.floorplan');

    // ── KPI row — verbatim from the approved PNG ──────────────────────────────
    $kpis = [
        ['building-2',   '#157A43','#E2F3E8','Total Pavillons',   '16',          '100% actifs',      'plain',''],
        ['layout-grid',  '#3565DE','#E8EFFB','Stands totaux',     '512',         '100% disponibles', 'plain',''],
        ['grid-3x3',     '#7C4FE0','#F0EAFB','Stands occupés',    '378',         '73.8%',            'muted',''],
        ['pie-chart',    '#C97A16','#FDEEDA','Taux d’occupation', '73.8%',       '12.6%',            'up','vs dernier mois'],
        ['users-round',  '#157A43','#E2F3E8','Exposants',         '842',         'Dans les pavillons','plain',''],
        ['landmark',     '#C97A16','#FDEEDA','Revenus générés',   '128,450,000', '22%',              'up','vs dernier mois'],
    ];

    // ── Right column: category donut (verbatim) ───────────────────────────────
    $catBreak = [
        ['Institutionnel',       '3 (18.8%)', 18.8,'#157A43'],
        ['Artisanat',            '3 (18.8%)', 18.8,'#C97A16'],
        ['Culture & Traditions', '2 (12.5%)', 12.5,'#7C4FE0'],
        ['Design & Innovation',  '2 (12.5%)', 12.5,'#3565DE'],
        ['Agriculture',          '1 (6.3%)',   6.3,'#E6B201'],
        ['International',         '5 (31.3%)', 31.3,'#2E8B57'],
    ];

    // ── Right column: region bars (verbatim) ──────────────────────────────────
    $regions = [
        ['Cameroun',  '10 (62.5%)', 62.5,'#157A43'],
        ['Afrique',   '2 (12.5%)',  12.5,'#C97A16'],
        ['Europe',    '1 (6.3%)',    6.3,'#E6B201'],
        ['Asie',      '1 (6.3%)',    6.3,'#C0010C'],
        ['Amériques', '1 (6.3%)',    6.3,'#3565DE'],
    ];

    // ── Table rows — verbatim from the approved PNG ───────────────────────────
    // [thumb#, name, code, catégorie(1-2 lines), type, région, flag, total, occupés, dispo, taux, statut, tone]
    $flagCM = 'cm'; // Cameroon flag drawn inline
    $pavRows = [
        [1,'Pavillon Centre',              'P-01','Institutionnel',                 'National',      'Cameroun',        'cm',    80,68,12,'85%','Actif',       'ok'],
        [2,'Pavillon Ouest',               'P-02','Artisanat',                      'National',      'Cameroun',        'cm',    60,45,15,'75%','Actif',       'ok'],
        [3,'Pavillon Nord',                'P-03','Culture & Traditions',           'National',      'Cameroun',        'cm',    50,38,12,'76%','Actif',       'ok'],
        [4,'Pavillon Sud',                 'P-04','Design & Innovation',            'National',      'Cameroun',        'cm',    40,28,12,'70%','Actif',       'ok'],
        [5,'Pavillon Est',                 'P-05','Agriculture & Agroalimentaire',  'National',      'Cameroun',        'cm',    40,30,10,'75%','Actif',       'ok'],
        [6,'Pavillon Innovation',          'P-06','Technologies & Numérique',       'National',      'Cameroun',        'cm',    30,22, 8,'73%','Actif',       'ok'],
        [7,'Pavillon International Afrique','P-07','International',                  'International',  'Afrique (Divers)','globe', 70,55,15,'79%','Actif',       'ok'],
        [8,'Pavillon Europe',              'P-08','International',                  'International',  'Europe',          'globe', 40,28,12,'70%','Actif',       'ok'],
        [9,'Pavillon Asie',                'P-09','International',                  'International',  'Asie',            'globe', 40,25,15,'63%','Maintenance', 'warn'],
        [10,'Pavillon Amériques',          'P-10','International',                  'International',  'Amériques',       'globe', 30,20,10,'67%','Actif',       'ok'],
    ];
@endphp

{{-- ══ IN-BODY PAGE HEADING (subtitle from the PNG) ══ --}}
<div class="mb-5">
    <h1 class="font-display text-[27px] font-extrabold text-[#1A1712] leading-tight">Gestion des Pavillons</h1>
    <p class="text-[13px] text-[#8A857A] mt-1">Gérez tous les pavillons et leur configuration.</p>
</div>

{{-- ══ KPI CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    @foreach($kpis as [$icon,$color,$tile,$label,$val,$sub,$mode,$suffix])
    <div class="siarc-card siarc-shadow px-4 py-4 siarc-in">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-[22px] h-[22px]" style="color:{{ $color }}"></i></span>
            <p class="text-[11.5px] text-[#8A857A] font-semibold leading-tight">{{ $label }}</p>
        </div>
        <p class="mt-3 text-[26px] font-extrabold text-[#1A1712] leading-none tracking-tight">{{ $val }}@if($label==='Revenus générés')<span class="text-[11.5px] font-semibold text-[#8A857A] ml-1">FCFA</span>@endif</p>
        <div class="mt-2 text-[11.5px] leading-tight">
            @if($mode==='up')
                <span class="inline-flex items-center gap-1 font-semibold text-siarc-green"><i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $sub }} <span class="text-[#B0AB9F] font-normal">{{ $suffix }}</span></span>
            @elseif($mode==='muted')
                <span class="font-semibold text-siarc-green">{{ $sub }}</span>
            @else
                <span class="font-semibold text-siarc-green">{{ \Illuminate\Support\Str::before($sub,' ') }}</span> <span class="text-[#8A857A]">{{ \Illuminate\Support\Str::after($sub,' ') }}</span>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ══ MAIN GRID: TABLE (2/3) + SIDE ANALYTICS (1/3) ══ --}}
<div class="grid xl:grid-cols-3 gap-5 items-start">

    {{-- ── LEFT: tabs + filters + table ── --}}
    <div class="xl:col-span-2 siarc-card siarc-shadow overflow-hidden">

        {{-- tabs --}}
        <div class="flex items-center gap-7 px-5 pt-4 border-b border-[#EFEDE6]">
            <button class="pb-3 -mb-px text-[13px] font-bold text-siarc-green border-b-2 border-siarc-green">Tous les pavillons (16)</button>
            <button class="pb-3 -mb-px text-[13px] font-medium text-[#8A857A] hover:text-[#3B382F]">Pavillons nationaux (10)</button>
            <button class="pb-3 -mb-px text-[13px] font-medium text-[#8A857A] hover:text-[#3B382F]">Pavillons internationaux (6)</button>
        </div>

        {{-- filter row --}}
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" placeholder="Rechercher un pavillon..." class="w-full text-[12.5px] rounded-xl border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach([['Catégorie','Tous'],['Type','Tous'],['Statut','Tous']] as [$flabel,$fval])
            <div class="min-w-[130px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $flabel }}</label>
                <div class="flex items-center justify-between gap-2 text-[12.5px] rounded-xl border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F] cursor-pointer hover:border-[#D8E5DC]"><span>{{ $fval }}</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></div>
            </div>
            @endforeach
            <div class="flex flex-col items-end gap-2 ml-auto">
                <div class="flex items-center gap-2.5">
                    <button class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-xl bg-white hover:border-[#D8E5DC]"><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i>Exporter</button>
                    <a href="{{ $addHref }}" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2.5"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un pavillon</a>
                </div>
                <div class="flex items-center gap-2.5">
                    <button class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-xl bg-white hover:border-[#D8E5DC]"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
                    <button class="siarc-btn text-[12.5px] text-siarc-green px-2 py-2"><i data-lucide="rotate-ccw" class="w-4 h-4"></i>Réinitialiser</button>
                </div>
            </div>
        </div>

        {{-- table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[860px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        <th rowspan="2" class="px-5 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase align-bottom">Pavillon</th>
                        <th rowspan="2" class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase align-bottom">Catégorie</th>
                        <th rowspan="2" class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase align-bottom">Type</th>
                        <th rowspan="2" class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase align-bottom">Pays / Région</th>
                        <th colspan="3" class="px-3 pt-3 pb-1 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase text-center">Stands</th>
                        <th rowspan="2" class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase text-center align-bottom">Taux</th>
                        <th rowspan="2" class="px-3 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase align-bottom">Statut</th>
                        <th rowspan="2" class="px-5 py-3 text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase text-right align-bottom">Actions</th>
                    </tr>
                    <tr class="bg-[#FBFAF6] border-b border-[#EFEDE6]">
                        <th class="px-3 pb-2 text-[9.5px] font-semibold tracking-wide text-[#B0AB9F] uppercase text-center">Total</th>
                        <th class="px-3 pb-2 text-[9.5px] font-semibold tracking-wide text-[#B0AB9F] uppercase text-center">Occupés</th>
                        <th class="px-3 pb-2 text-[9.5px] font-semibold tracking-wide text-[#B0AB9F] uppercase text-center">Disponibles</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pavRows as [$thumb,$name,$code,$cat,$type,$region,$flag,$total,$occ,$dispo,$taux,$statut,$tone])
                    <tr class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/siarc/pavilion-thumb-'.$thumb.'.png') }}" alt="" class="w-9 h-9 rounded-lg object-cover shrink-0 border border-[#EFEDE6]">
                                <span>
                                    <span class="block text-[12.5px] font-semibold text-[#1A1712] leading-tight">{{ $name }}</span>
                                    <span class="block text-[11px] text-[#B0AB9F]">{{ $code }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-[12px] text-[#6B6659] leading-tight max-w-[160px]">{{ $cat }}</td>
                        <td class="px-3 py-3 text-[12px] text-[#6B6659]">{{ $type }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center gap-2 text-[12px] text-[#3B382F]">
                                @if($flag==='cm')
                                    <span class="inline-flex w-[18px] h-[13px] rounded-[2px] overflow-hidden shrink-0 border border-black/5">
                                        <span class="w-1/3 h-full" style="background:#007A5E"></span><span class="w-1/3 h-full relative" style="background:#CE1126"><span class="absolute inset-0 flex items-center justify-center text-[#FCD116] text-[8px] leading-none">★</span></span><span class="w-1/3 h-full" style="background:#FCD116"></span>
                                    </span>
                                @else
                                    <i data-lucide="globe" class="w-[15px] h-[15px] text-[#3565DE]"></i>
                                @endif
                                {{ $region }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center text-[12.5px] font-semibold text-[#1A1712]">{{ $total }}</td>
                        <td class="px-3 py-3 text-center text-[12.5px] font-semibold text-[#1A1712]">{{ $occ }}</td>
                        <td class="px-3 py-3 text-center text-[12.5px] text-[#6B6659]">{{ $dispo }}</td>
                        <td class="px-3 py-3 text-center text-[12.5px] font-semibold text-[#1A1712]">{{ $taux }}</td>
                        <td class="px-3 py-3">
                            @if($tone==='ok')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold text-[#157A43] bg-[#E4F3EA]">{{ $statut }}</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold text-[#C97A16] bg-[#FBEBD5]">{{ $statut }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1 text-[#B0AB9F]">
                                <a href="{{ $addHref }}" class="w-7 h-7 rounded-lg hover:bg-[#EFEDE6] flex items-center justify-center hover:text-siarc-green"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="{{ $addHref }}" class="w-7 h-7 rounded-lg hover:bg-[#EFEDE6] flex items-center justify-center hover:text-siarc-green"><i data-lucide="square-pen" class="w-4 h-4"></i></a>
                                <button class="w-7 h-7 rounded-lg hover:bg-[#EFEDE6] flex items-center justify-center hover:text-siarc-green"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- pagination footer --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-[#EFEDE6]">
            <span class="text-[11.5px] text-[#8A857A]">Affichage de 1 à 10 sur 16 pavillons</span>
            <div class="flex items-center gap-1.5">
                <button class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#B0AB9F] hover:border-[#D8E5DC]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button class="w-8 h-8 rounded-lg bg-siarc-green text-white text-[12px] font-semibold">1</button>
                <button class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] text-[#3B382F] hover:border-[#D8E5DC]">2</button>
                <button class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#B0AB9F] hover:border-[#D8E5DC]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
            </div>
            <div class="flex items-center gap-2 text-[11.5px] text-[#8A857A]">
                <span>Lignes par page</span>
                <div class="flex items-center gap-1.5 rounded-lg border border-[#EFEDE6] px-2.5 py-1.5 bg-white text-[#3B382F] cursor-pointer"><span>10</span><i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#B0AB9F]"></i></div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT: side analytics ── --}}
    <div class="space-y-5">

        {{-- category donut --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Répartition par catégorie</h3>
            <div class="flex items-center gap-4">
                <div class="relative shrink-0">
                    <svg viewBox="0 0 120 120" class="w-[124px] h-[124px] -rotate-90">
                        @php $circ = 2 * 3.14159 * 52; $off = 0; @endphp
                        @foreach($catBreak as [$lbl,$disp,$pct,$col])
                            <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="15"
                                stroke-dasharray="{{ round($circ*$pct/100,1) }} {{ round($circ-($circ*$pct/100),1) }}"
                                stroke-dashoffset="{{ -round($off,1) }}"/>
                            @php $off += $circ*$pct/100; @endphp
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="font-display text-[24px] font-extrabold text-[#1A1712] leading-none">16</span>
                        <span class="text-[9.5px] text-[#8A857A] mt-0.5">Pavillons</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-1.5">
                    @foreach($catBreak as [$lbl,$disp,$pct,$col])
                    <li class="flex items-center gap-2 text-[11px]"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span><span class="text-[#3B382F] font-medium">{{ $lbl }}</span><span class="ml-auto text-[#8A857A] whitespace-nowrap">{{ $disp }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- region bars --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Répartition par pays / région</h3>
            <ul class="space-y-3.5">
                @foreach($regions as [$rname,$disp,$pct,$col])
                <li class="flex items-center gap-3">
                    <span class="text-[12px] text-[#3B382F] font-medium w-20 shrink-0">{{ $rname }}</span>
                    <div class="h-2 flex-1 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full" style="width:{{ max($pct,4) }}%;background:{{ $col }}"></div></div>
                    <span class="text-[11px] text-[#8A857A] w-[68px] text-right shrink-0">{{ $disp }}</span>
                </li>
                @endforeach
            </ul>
        </div>

        {{-- global occupancy gauge --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-2">Occupation globale des stands</h3>
            <div class="relative mx-auto w-[220px] h-[118px]">
                <svg viewBox="0 0 220 118" class="w-full h-full">
                    <path d="M20 108 A90 90 0 0 1 200 108" fill="none" stroke="#ECEAE3" stroke-width="16" stroke-linecap="round"/>
                    @php $gp = 73.8/100; $len = 3.14159*90; @endphp
                    <path d="M20 108 A90 90 0 0 1 200 108" fill="none" stroke="#157A43" stroke-width="16" stroke-linecap="round"
                        stroke-dasharray="{{ round($len*$gp,1) }} {{ round($len,1) }}"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-end pb-1 text-center">
                    <span class="font-display text-[30px] font-extrabold text-[#1A1712] leading-none">73.8%</span>
                </div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-[#B0AB9F] -mt-1 px-1">
                <span>0%</span><span class="text-[11px] text-[#3B382F] font-medium">378 / 512 stands occupés</span><span>100%</span>
            </div>
            <p class="text-center text-[11.5px] font-semibold text-siarc-green mt-3 inline-flex items-center gap-1 w-full justify-center"><i data-lucide="arrow-up" class="w-3 h-3"></i>12.6% <span class="text-[#B0AB9F] font-normal">vs dernier mois</span></p>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>
