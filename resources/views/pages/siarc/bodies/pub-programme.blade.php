@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang' => $lang], $params)) : '#';
    $programme = $h('siarc.programme');

    // ── Header stat cards (verbatim from design) ─────────────────────────────
    $stats = [
        ['calendar-days', '#157A43', '#E7F3EC', '9',    'Jours d\'événements',   '27 Juillet – 05 Août 2026'],
        ['users',         '#C97A16', '#FDF3E0', '120+', 'Sessions',              'Conférences, ateliers, panels'],
        ['mic',           '#C97A16', '#FDF3E0', '250+', 'Intervenants',          'Experts & professionnels'],
        ['building-2',    '#1E6FD0', '#E6F0FB', '20+',  'Pavillons & Stands',    'Nationaux et internationaux'],
        ['star',          '#E6B201', '#FBF3D6', '15+',  'Activités spéciales',   'Networking & culture'],
    ];

    // ── Day tabs (verbatim) ──────────────────────────────────────────────────
    $days = [
        ['27 Juil.', 'Dim', false], ['28 Juil.', 'Lun', false], ['29 Juil.', 'Mar', true],
        ['30 Juil.', 'Mer', false], ['31 Juil.', 'Jeu', false], ['01 Août', 'Ven', false],
        ['02 Août', 'Sam', false], ['03 Août', 'Dim', false], ['04 Août', 'Lun', false],
    ];

    // ── Track columns (header) ───────────────────────────────────────────────
    $tracks = [
        ['building-2', '#157A43', '#E7F3EC', 'Conférence Plénière', 'Salle de Conférence A'],
        ['users-round', '#7C4FE0', '#F0EAFB', 'Ateliers & Formations', 'Salle de Formation 1'],
        ['mic', '#C97A16', '#FDF3E0', 'Panels & Discussions', 'Salle de Conférence B'],
        ['presentation', '#1E6FD0', '#E6F0FB', 'Innovation & Tech', 'Espace Innovation'],
        ['star', '#E6B201', '#FBF3D6', 'Culture & Patrimoine', 'Espace Animation'],
    ];

    // ── Session cards, per track. tone = [bg, accent] ─────────────────────────
    // Time-band rows: 09:00, 11:00-ish, 14:00, 16:00 (plus offset innovation/culture).
    $confBg = '#F1FBF5'; $confAc = '#157A43';
    $atelBg = '#F8F4FC'; $atelAc = '#7C4FE0';
    $panBg  = '#FDF6EE'; $panAc  = '#C97A16';
    $innBg  = '#EEF5FD'; $innAc  = '#1E6FD0';
    $cultBg = '#FEF9EC'; $cultAc = '#E6B201';

    $col1 = [
        ['09:00 – 10:30', 'Cérémonie officielle & Discours d\'ouverture', 'Ministre des Arts et Culture'],
        ['11:00 – 12:00', 'Conférence inaugurale L\'artisanat africain à l\'ère de l\'innovation', 'Dr. Alain Mbarga'],
        ['14:00 – 15:30', 'Conférence plénière Durabilité & compétitivité de l\'artisanat', 'Pr. Marie-Louise Bibish'],
        ['16:00 – 17:30', 'Conférence plénière Financement & croissance des entreprises artisanales', 'Banque Africaine de Développement'],
    ];
    $col2 = [
        ['09:00 – 11:00', 'Atelier : Design Thinking pour artisans', 'David K. Mensah'],
        ['11:30 – 13:00', 'Formation : Digitalisation des métiers artisanaux', 'Emmanuelle Tchamou'],
        ['14:00 – 15:30', 'Atelier : Packaging & Valorisation des produits', 'Paul Tchameni'],
        ['16:00 – 17:30', 'Atelier pratique : Photographie produit pour le e-commerce', 'Lydie Ngono'],
    ];
    $col3 = [
        ['09:00 – 10:30', 'Panel : Artisanat & Tourisme durable', 'Modérateur : A. Njoya'],
        ['11:00 – 12:30', 'Panel : Jeunes artisans & transmission des savoir-faire', 'Modérateur : P. Essomba'],
        ['14:00 – 15:30', 'Panel : Accès aux marchés internationaux', 'Modérateur : J. Dupont'],
        ['16:00 – 17:30', 'Panel : Politiques publiques & développement de l\'artisanat', 'Modérateur : M. Ewane'],
    ];
    $col4 = [
        ['09:30 – 11:00', 'Tech Talk : IA & artisanat Opportunités & défis', 'TechCraft Africa'],
        ['11:30 – 13:00', 'Présentation startups solutions innovantes pour l\'artisanat', '5 startups'],
        ['14:00 – 15:30', 'Démonstration : Impression 3D & artisanat', 'FabLab Cameroon'],
        ['16:00 – 17:30', 'Conférence : E-commerce & plateformes digitales pour artisans', 'Jumia Consulting'],
    ];
    $col5 = [
        ['10:00 – 11:30', 'Performance artistique Musiques & danses traditionnelles', ''],
        ['12:00 – 13:00', 'Défilé de mode Artisanat & textiles africains', ''],
        ['15:00 – 16:30', 'Contes & traditions valorisation du patrimoine immatériel', ''],
        ['17:00 – 18:00', 'Cérémonie des prix Excellence Artisanale', 'SIARC 2026'],
    ];

    // ── Right rail ───────────────────────────────────────────────────────────
    $calDays = [
        [['27', false, true], ['28', false, false], ['29', true, false], ['30', false, false], ['31', false, false], ['1', false, false], ['2', false, false]],
        [['3', false, false], ['4', false, false], ['5', false, true], ['6', false, false], ['7', false, false], ['8', false, false], ['9', false, false]],
        [['10', false, false], ['11', false, false], ['12', false, false], ['14', false, false], ['15', false, false], ['16', false, false], ['', false, false]],
    ];
    $quickFilters = [
        ['star', 'Mes favoris'],
        ['circle-dot', 'Sessions en direct'],
        ['handshake', 'Sessions B2B'],
        ['wifi', 'Accessibles en ligne'],
        ['award', 'Nouveautés'],
    ];
    $notMiss = [
        ['#C0010C', '#FDE8E8', 'trophy', 'Cérémonie d\'ouverture', '27 Juil. • 09:00 – 10:30', 'Salle de Conférence A'],
        ['#C97A16', '#FDF3E0', 'user', 'Conférence inaugurale', '27 Juil. • 11:00 – 12:00', 'Salle de Conférence A'],
        ['#E6B201', '#FBF3D6', 'award', 'Défilé de mode artisanal', '29 Juil. • 12:00 – 13:00', 'Espace Animation'],
    ];

    $legend = [
        ['Conférence', '#7C4FE0'], ['Atelier', '#C97A16'], ['Panel', '#5FA463'],
        ['Innovation', '#1E6FD0'], ['Culture', '#E6B201'], ['Autre', '#9A968C'],
    ];
@endphp

<div class="bg-[#FBFAF7] min-h-screen">
<div class="max-w-[1280px] mx-auto px-5 sm:px-8 py-6">

    {{-- ══════════ IN-BODY PAGE HEADER ══════════ --}}
    <div class="flex flex-wrap items-start justify-between gap-5 mb-6">
        <div>
            <h1 class="font-display text-[26px] sm:text-[28px] font-extrabold text-[#1A1712] leading-none">Programme Schedule</h1>
            <p class="text-[13px] text-[#8A857A] mt-1.5">Découvrez le programme complet du SIARC 2026</p>
        </div>
        <div class="inline-flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-xl bg-[#F3F0E7] flex items-center justify-center shrink-0"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#55524A]"></i></span>
            <span class="leading-tight">
                <span class="block text-[13px] font-bold text-[#1A1712]">27 Juillet – 05 Août 2026</span>
                <span class="block text-[11.5px] text-[#8A857A]">Musée National de Yaoundé</span>
            </span>
        </div>
    </div>

    {{-- ══════════ STAT CARDS ══════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
        @foreach($stats as [$icon, $color, $tile, $val, $label, $sub])
        <div class="siarc-card siarc-shadow px-5 py-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
            <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none tracking-tight">{{ $val }}</p>
            <p class="text-[13px] font-semibold text-[#2A271F] mt-2">{{ $label }}</p>
            <p class="text-[11.5px] text-[#8A857A] mt-0.5">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    {{-- ══════════ MAIN GRID: schedule (left) + rail (right) ══════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1fr_306px] gap-5 items-start">

        {{-- ────────── LEFT: schedule column ────────── --}}
        <div>
            {{-- View toggle + export --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div class="inline-flex items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[13px] font-semibold bg-siarc-dark text-white"><i data-lucide="calendar-days" class="w-4 h-4"></i>Vue par jour</button>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[13px] font-semibold text-[#55524A] border border-[#E4E0D6] bg-white hover:bg-[#F7F5EF]"><i data-lucide="clipboard-list" class="w-4 h-4"></i>Vue par piste</button>
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[13px] font-semibold text-[#55524A] border border-[#E4E0D6] bg-white hover:bg-[#F7F5EF]"><i data-lucide="map-pin" class="w-4 h-4"></i>Vue par lieu</button>
                </div>
                <button type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[13px] font-semibold text-[#55524A] border border-[#E4E0D6] bg-white hover:bg-[#F7F5EF]"><i data-lucide="download" class="w-4 h-4"></i>Exporter le programme</button>
            </div>

            {{-- Day tabs row --}}
            <div class="flex items-stretch gap-2 mb-4 overflow-x-auto siarc-scroll">
                <button type="button" class="shrink-0 w-9 rounded-xl border border-[#E4E0D6] bg-white flex items-center justify-center text-[#8A857A] hover:bg-[#F7F5EF]"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
                @foreach($days as [$dnum, $dname, $active])
                    <button type="button" class="shrink-0 min-w-[74px] px-4 py-2 rounded-xl text-center leading-tight border {{ $active ? 'bg-siarc-dark border-siarc-dark text-white' : 'bg-white border-[#E4E0D6] text-[#2A271F] hover:bg-[#F7F5EF]' }}">
                        <span class="block text-[13px] font-bold">{{ $dnum }}</span>
                        <span class="block text-[11px] {{ $active ? 'text-white/75' : 'text-[#8A857A]' }}">{{ $dname }}</span>
                    </button>
                @endforeach
                <button type="button" class="shrink-0 w-9 rounded-xl border border-[#E4E0D6] bg-white flex items-center justify-center text-[#8A857A] hover:bg-[#F7F5EF]"><i data-lucide="arrow-right" class="w-4 h-4"></i></button>
            </div>

            {{-- Filter row --}}
            <div class="flex flex-wrap items-center gap-3 mb-5">
                <button type="button" class="inline-flex items-center justify-between gap-6 px-3.5 py-2.5 rounded-xl border border-[#E4E0D6] bg-white text-[13px] text-[#55524A] min-w-[150px]"><span>Toutes les pistes</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i></button>
                <button type="button" class="inline-flex items-center justify-between gap-6 px-3.5 py-2.5 rounded-xl border border-[#E4E0D6] bg-white text-[13px] text-[#55524A] min-w-[180px]"><span>Tous les types de session</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i></button>
                <button type="button" class="inline-flex items-center justify-between gap-6 px-3.5 py-2.5 rounded-xl border border-[#E4E0D6] bg-white text-[13px] text-[#55524A] min-w-[130px]"><span>Tous les lieux</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i></button>
                <button type="button" class="inline-flex items-center gap-2 text-[13px] font-medium text-[#2A271F]"><i data-lucide="star" class="w-4 h-4 text-siarc-gold"></i>Mes favoris</button>
                <div class="flex-1 min-w-[220px] relative">
                    <i data-lucide="search" class="w-4 h-4 text-[#A8A498] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" placeholder="Rechercher une session, un intervenant…" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#E4E0D6] bg-white text-[13px] text-[#55524A] placeholder-[#A8A498] focus:outline-none focus:border-[#CDBE8F]">
                </div>
            </div>

            {{-- Schedule board --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="overflow-x-auto siarc-scroll">
                <div class="min-w-[860px]">

                    {{-- Track headers --}}
                    <div class="grid grid-cols-[52px_repeat(5,1fr)] border-b border-[#EEECE5]">
                        <div class="border-r border-[#F0EEE7]"></div>
                        @foreach($tracks as [$ticon, $tcolor, $ttile, $ttitle, $troom])
                        <div class="px-3 py-3.5 {{ !$loop->last ? 'border-r border-[#F0EEE7]' : '' }} flex items-start gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:{{ $ttile }}"><i data-lucide="{{ $ticon }}" class="w-4 h-4" style="color:{{ $tcolor }}"></i></span>
                            <span class="leading-tight min-w-0">
                                <span class="block text-[12.5px] font-bold text-[#1A1712]">{{ $ttitle }}</span>
                                <span class="block text-[11px] text-[#8A857A]">{{ $troom }}</span>
                            </span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Time grid --}}
                    @php
                        $hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
                        $rowH = 56; // px per hour band
                        $gridH = (count($hours) - 1) * $rowH;
                        // map a "HH:MM – HH:MM" string to top/height in px (08:00 = 0)
                        $mins = function ($hm) { [$h, $m] = array_map('intval', explode(':', trim($hm))); return ($h - 8) * 60 + $m; };
                        $place = function ($range) use ($mins, $rowH) {
                            [$a, $b] = array_map('trim', explode('–', str_replace('-', '–', $range)));
                            $top = $mins($a) / 60 * $rowH;
                            $ht  = max(48, ($mins($b) - $mins($a)) / 60 * $rowH - 6);
                            return [round($top, 1), round($ht, 1)];
                        };
                        $columns = [
                            [$col1, $confBg, $confAc],
                            [$col2, $atelBg, $atelAc],
                            [$col3, $panBg,  $panAc],
                            [$col4, $innBg,  $innAc],
                            [$col5, $cultBg, $cultAc],
                        ];
                    @endphp

                    <div class="grid grid-cols-[52px_repeat(5,1fr)]">
                        {{-- Hour gutter --}}
                        <div class="relative border-r border-[#F0EEE7]" style="height:{{ $gridH }}px">
                            @foreach($hours as $hi => $hh)
                            <span class="absolute right-2 text-[11px] text-[#A8A498] -translate-y-1/2" style="top:{{ $hi * $rowH }}px">{{ $hh }}</span>
                            @endforeach
                        </div>

                        {{-- Session columns --}}
                        @foreach($columns as $colIdx => [$cards, $cbg, $cac])
                        <div class="relative {{ $colIdx < 4 ? 'border-r border-[#F0EEE7]' : '' }}" style="height:{{ $gridH }}px">
                            {{-- hour gridlines --}}
                            @foreach($hours as $hi => $hh)
                                <span class="absolute left-0 right-0 border-t border-[#F5F3EE]" style="top:{{ $hi * $rowH }}px"></span>
                            @endforeach
                            @foreach($cards as [$time, $title, $sub])
                                @php [$top, $ht] = $place($time); @endphp
                                <div class="absolute left-2 right-2 rounded-xl px-3 py-2.5 overflow-hidden" style="top:{{ $top + 3 }}px;height:{{ $ht }}px;background:{{ $cbg }};border:1px solid {{ $cac }}20">
                                    <div class="flex items-start justify-between gap-1.5">
                                        <span class="text-[11px] font-semibold text-[#55524A]">{{ $time }}</span>
                                        <i data-lucide="star" class="w-3.5 h-3.5 text-[#C7C2B6] shrink-0"></i>
                                    </div>
                                    <p class="text-[12px] font-bold text-[#1A1712] leading-snug mt-1">{{ $title }}</p>
                                    @if($sub)
                                        <p class="text-[11px] text-[#8A857A] mt-1 leading-tight">{{ $sub }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                </div>

                {{-- Legend --}}
                <div class="border-t border-[#EEECE5] px-5 py-3.5 flex flex-wrap items-center gap-x-6 gap-y-2">
                    <span class="text-[12px] font-bold text-[#1A1712]">Types de session :</span>
                    @foreach($legend as [$lname, $lcolor])
                        <span class="inline-flex items-center gap-2 text-[12.5px] text-[#55524A]"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $lcolor }}"></span>{{ $lname }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ────────── RIGHT: rail ────────── --}}
        <aside class="space-y-5">

            {{-- Calendar --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Calendrier</h3>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" class="w-7 h-7 rounded-lg flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                    <span class="text-[13px] font-semibold text-[#2A271F]">Juillet – Août 2026</span>
                    <button type="button" class="w-7 h-7 rounded-lg flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                </div>
                <div class="grid grid-cols-7 gap-y-2 text-center">
                    @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $dow)
                        <span class="text-[11px] font-semibold text-[#A8A498]">{{ $dow }}</span>
                    @endforeach
                    @foreach($calDays as $week)
                        @foreach($week as [$d, $isToday, $isDim])
                            <span class="flex items-center justify-center">
                                @if($d === '')
                                    <span></span>
                                @elseif($isToday)
                                    <span class="w-8 h-8 rounded-full bg-siarc-dark text-white text-[12.5px] font-bold flex items-center justify-center">{{ $d }}</span>
                                @elseif($isDim)
                                    <span class="w-8 h-8 rounded-full bg-[#E7F3EC] text-siarc-green text-[12.5px] font-semibold flex items-center justify-center">{{ $d }}</span>
                                @else
                                    <span class="w-8 h-8 text-[12.5px] text-[#55524A] flex items-center justify-center">{{ $d }}</span>
                                @endif
                            </span>
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- Quick filters --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">Filtres rapides</h3>
                    <button type="button" class="text-[12.5px] font-semibold text-[#1E6FD0] hover:underline">Réinitialiser</button>
                </div>
                <ul class="space-y-3.5">
                    @foreach($quickFilters as [$qicon, $qlabel])
                    <li class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-2.5 text-[13px] text-[#3B382F]"><i data-lucide="{{ $qicon }}" class="w-4 h-4 text-[#8A857A]"></i>{{ $qlabel }}</span>
                        <span class="w-9 h-5 rounded-full bg-[#E4E0D6] relative shrink-0"><span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow-sm"></span></span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- À ne pas manquer --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">À ne pas manquer</h3>
                    <a href="{{ $programme }}" class="text-[12.5px] font-semibold text-[#8A857A] hover:text-siarc-green">Voir tout</a>
                </div>
                <ul class="space-y-4">
                    @foreach($notMiss as [$mcolor, $mtile, $micon, $mtitle, $mtime, $mroom])
                    <li class="flex gap-3">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $mtile }}"><i data-lucide="{{ $micon }}" class="w-4 h-4" style="color:{{ $mcolor }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-[12.5px] font-bold text-[#1A1712] leading-tight">{{ $mtitle }}</p>
                                <span class="shrink-0 text-[10px] font-semibold text-siarc-green bg-[#E7F3EC] px-2 py-0.5 rounded-full">En vedette</span>
                            </div>
                            <p class="text-[11px] text-[#8A857A] mt-1">{{ $mtime }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $mroom }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $programme }}" class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold text-[#2A271F] border border-[#E4E0D6] hover:bg-[#F7F5EF]">Voir le programme complet (PDF)<i data-lucide="download" class="w-4 h-4"></i></a>
            </div>

        </aside>
    </div>
</div>
</div>
