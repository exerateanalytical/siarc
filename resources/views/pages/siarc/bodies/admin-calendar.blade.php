@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Type → colour map (verbatim from the design legend) ────────────────────
    //   [ink, background, left-border]
    $T = [
        'conf'      => ['#7C4FE0','#F0EAFB','#7C4FE0'], // Conférence
        'atelier'   => ['#C99A16','#FEF6DC','#E6B201'], // Atelier
        'panel'     => ['#2E8B57','#EAF6EF','#2E8B57'], // Panel / Table ronde
        'b2b'       => ['#0B3A1E','#E9EFEB','#0B3A1E'], // B2B / Matchmaking
        'ceremonie' => ['#157A43','#E6F4EC','#157A43'], // Cérémonie
        'animation' => ['#3565DE','#E8EFFB','#3565DE'], // Animation / Culture
        'autre'     => ['#8A857A','#F0EFEC','#8A857A'], // Autre
    ];

    // ── Calendar weeks (verbatim transcription of the PNG grid) ────────────────
    //   session = [time, title, meta, metaIcon, typeKey]
    //   metaIcon: 'pin' (map-pin) | 'user' (users-round)
    $weeks = [
        // ── Week 1: 20 – 26 juil. ──
        [
            ['date'=>'20 JUIL.', 'today'=>false, 'sessions'=>[], 'extra'=>0],
            ['date'=>'21 JUIL.', 'today'=>false, 'sessions'=>[], 'extra'=>0],
            ['date'=>'22 JUIL.', 'today'=>false, 'sessions'=>[], 'extra'=>0],
            ['date'=>'23 JUIL.', 'today'=>false, 'extra'=>0, 'sessions'=>[
                ['10:00','Réunion préparatoire Comité d\'organisation','Salle VIP','pin','conf'],
            ]],
            ['date'=>'24 JUIL.', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['14:00','Briefing des exposants','Centre de Conférence','pin','atelier'],
            ]],
            ['date'=>'25 JUIL.', 'today'=>false, 'extra'=>0, 'sessions'=>[
                ['09:00','Formation des bénévoles','Salle de Formation','user','ceremonie'],
            ]],
            ['date'=>'26 JUIL.', 'today'=>false, 'sessions'=>[], 'extra'=>0],
        ],
        // ── Week 2: 27 juil. – 02 août ──
        [
            ['date'=>'27 JUIL.', 'today'=>true, 'day'=>'27', 'mon'=>'JUIL.', 'extra'=>2, 'sessions'=>[
                ['09:00','Cérémonie d\'ouverture officielle','Espace Principal','pin','ceremonie'],
                ['11:00','Conférence inaugurale','Salle de Conférence A','pin','conf'],
                ['15:00','Visite des stands officiels','Hall d\'Exposition','pin','animation'],
            ]],
            ['date'=>'28 JUIL.', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['10:00','Atelier : Design & Innovation','Atelier 1','user','atelier'],
                ['14:00','Panel : Financement des artisans','Salle de Conférence B','pin','panel'],
            ]],
            ['date'=>'29 JUIL.', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['10:30','Conférence : Innovation & Numérique','Salle de Conférence A','pin','conf'],
                ['14:00','Atelier : Marketing digital pour artisans','Atelier 2','user','atelier'],
            ]],
            ['date'=>'30 JUIL.', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['10:30','Conférence : Artisanat et Export','Salle de Conférence A','pin','conf'],
                ['15:00','B2B Matchmaking Session 1','Espace B2B','pin','b2b'],
            ]],
            ['date'=>'31 JUIL.', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['10:00','Atelier : Patrimoine et Culture','Atelier 1','user','atelier'],
                ['16:00','Soirée culturelle','Espace Animation','user','animation'],
            ]],
            ['date'=>'01 AOÛT', 'today'=>false, 'extra'=>2, 'sessions'=>[
                ['09:00','B2B Matchmaking Session 2','Espace B2B','pin','b2b'],
                ['14:00','Conférence : Durabilité & Artisanat','Salle de Conférence B','pin','conf'],
            ]],
            ['date'=>'02 AOÛT', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['10:00','Atelier : Qualité & Labelisation','Atelier 2','user','atelier'],
                ['17:00','Défilé de mode artisanale','Espace Animation','pin','animation'],
            ]],
        ],
        // ── Week 3: 03 – 09 août ──
        [
            ['date'=>'03 AOÛT', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['09:30','B2B Matchmaking Session 3','Espace B2B','pin','b2b'],
                ['14:00','Conférence : Tourisme & Artisanat','Salle de Conférence A','pin','conf'],
            ]],
            ['date'=>'04 AOÛT', 'today'=>false, 'extra'=>0, 'sessions'=>[
                ['10:00','Atelier : Packaging & Design produit','Atelier 1','user','atelier'],
                ['15:00','Pitch des jeunes entrepreneurs','Salle de Conférence B','pin','panel'],
            ]],
            ['date'=>'05 AOÛT', 'today'=>false, 'extra'=>1, 'sessions'=>[
                ['10:00','Cérémonie de clôture & Remise des prix','Espace Principal','pin','ceremonie'],
                ['15:00','Cocktail de clôture','Espace VIP','user','animation'],
            ]],
            ['date'=>'06 AOÛT', 'today'=>false, 'sessions'=>[], 'extra'=>0],
            ['date'=>'07 AOÛT', 'today'=>false, 'sessions'=>[], 'extra'=>0],
            ['date'=>'08 AOÛT', 'today'=>false, 'sessions'=>[], 'extra'=>0],
            ['date'=>'09 AOÛT', 'today'=>false, 'sessions'=>[], 'extra'=>0],
        ],
    ];

    // ── Legend (verbatim, in order) ───────────────────────────────────────────
    $legend = [
        ['Conférence','#7C4FE0'],
        ['Atelier','#E6B201'],
        ['Panel / Table ronde','#2E8B57'],
        ['B2B / Matchmaking','#0B3A1E'],
        ['Cérémonie','#157A43'],
        ['Animation / Culture','#3565DE'],
        ['Autre','#8A857A'],
    ];

    // ── Right rail: Événements du jour (verbatim) ─────────────────────────────
    //   [time, title, place, count(null=—), dotColor]
    $dayEvents = [
        ['09:00','Cérémonie d\'ouverture officielle','Espace Principal','250','#157A43'],
        ['11:00','Conférence inaugurale','Salle de Conférence A','180','#7C4FE0'],
        ['15:00','Visite des stands officiels','Hall d\'Exposition', null,'#3565DE'],
        ['17:00','Cocktail de bienvenue','Espace VIP','150','#3565DE'],
        ['19:00','Spectacle culturel d\'ouverture','Espace Animation','300','#3565DE'],
        ['20:30','Dîner officiel','Espace VIP','120','#E6B201'],
    ];

    // ── Downloads (verbatim) ──────────────────────────────────────────────────
    $downloads = ['Programme complet (PDF)','Plan des salles (PDF)','Brochure des activités (PDF)'];
@endphp

<div class="siarc-in">

    {{-- ═══ TOOLBAR ═══ --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="inline-flex rounded-xl border border-[#E6E3DB] bg-white p-1 siarc-shadow">
            <span class="siarc-btn siarc-btn-green px-3.5 py-2 text-[12.5px]"><i data-lucide="layout-grid" class="w-4 h-4"></i>Vue calendrier</span>
            <a href="{{ $h('siarc.admin.programme') }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-[12.5px] font-semibold text-[#55524A] rounded-lg hover:bg-[#F5F3EE]"><i data-lucide="clipboard-list" class="w-4 h-4"></i>Vue liste</a>
        </div>
        <button type="button" class="inline-flex items-center gap-2 rounded-xl border border-[#E6E3DB] bg-white px-3.5 py-2.5 text-[12.5px] font-medium text-[#55524A] siarc-shadow">
            <span>Tous les lieux</span><i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
        </button>
        <div class="ml-auto flex items-center gap-3">
            <a href="{{ $h('siarc.admin.reports') }}" class="siarc-btn px-4 py-2.5 text-[12.5px] font-semibold text-[#55524A] bg-white border border-[#E6E3DB] siarc-shadow"><i data-lucide="download" class="w-4 h-4"></i>Exporter</a>
            <a href="{{ $h('siarc.admin.programme') }}" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[12.5px]"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un événement</a>
        </div>
    </div>

    {{-- ═══ MAIN GRID: calendar (left) + sidebar (right) ═══ --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5">

        {{-- ── LEFT: month/agenda calendar ── --}}
        <div class="siarc-card siarc-shadow overflow-hidden">

            {{-- month nav bar --}}
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-[#EEECE5]">
                <div class="flex items-center gap-2">
                    <button type="button" class="w-8 h-8 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
                    <button type="button" class="px-3.5 h-8 rounded-lg border border-[#E6E3DB] flex items-center text-[12px] font-semibold text-[#55524A] hover:bg-[#F5F3EE]">Aujourd'hui</button>
                    <button type="button" class="w-8 h-8 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="arrow-right" class="w-4 h-4"></i></button>
                </div>
                <h2 class="font-display text-[17px] font-bold text-[#1A1712] flex items-center gap-1.5">Juillet – Août 2026<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></h2>
                <button type="button" class="inline-flex items-center gap-1.5 px-3.5 h-8 rounded-lg border border-[#E6E3DB] text-[12px] font-semibold text-[#55524A] hover:bg-[#F5F3EE]"><i data-lucide="filter" class="w-3.5 h-3.5"></i>Filtres</button>
            </div>

            {{-- weekday header --}}
            <div class="grid grid-cols-7 border-b border-[#EEECE5] bg-[#FBFAF6]">
                @foreach(['LUN','MAR','MER','JEU','VEN','SAM','DIM'] as $wd)
                <div class="px-3 py-3 text-[11px] font-bold tracking-wide text-[#8A857A] text-center border-r border-[#F1EFE9] last:border-r-0">{{ $wd }}</div>
                @endforeach
            </div>

            {{-- day cells --}}
            <div class="grid grid-cols-7">
                @foreach($weeks as $week)
                    @foreach($week as $cell)
                    <div class="min-h-[188px] p-2.5 border-r border-b border-[#F1EFE9] [&:nth-child(7n)]:border-r-0 align-top">

                        {{-- date header --}}
                        <div class="flex items-center gap-1.5 mb-2">
                            @if(!empty($cell['today']))
                            <span class="w-6 h-6 rounded-full bg-siarc-green text-white text-[11px] font-bold flex items-center justify-center">{{ $cell['day'] }}</span>
                            <span class="text-[12px] font-bold text-[#3B382F]">{{ $cell['mon'] }}</span>
                            @else
                            <span class="text-[12px] font-bold text-[#3B382F]">{{ $cell['date'] }}</span>
                            @endif
                        </div>

                        {{-- sessions --}}
                        <div class="space-y-1.5">
                            @foreach($cell['sessions'] as [$time,$title,$meta,$mIcon,$tk])
                            @php [$ink,$bg,$bd] = $T[$tk]; @endphp
                            <div class="rounded-lg px-2 py-1.5 border-l-[3px]" style="background:{{ $bg }};border-color:{{ $bd }}">
                                <p class="text-[10.5px] font-bold leading-none mb-1" style="color:{{ $ink }}">{{ $time }}</p>
                                <p class="text-[11px] font-semibold text-[#2A2822] leading-snug">{{ $title }}</p>
                                <p class="mt-1 flex items-center gap-1 text-[10px] text-[#8A857A]"><i data-lucide="{{ $mIcon === 'user' ? 'users-round' : 'map-pin' }}" class="w-3 h-3 shrink-0"></i>{{ $meta }}</p>
                            </div>
                            @endforeach
                            @if(($cell['extra'] ?? 0) > 0)
                            <p class="text-[11px] font-semibold text-[#8A857A] pl-1 pt-0.5">+{{ $cell['extra'] }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @endforeach
            </div>

            {{-- legend --}}
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 px-4 py-3.5 border-t border-[#EEECE5] bg-[#FBFAF6]">
                @foreach($legend as [$lbl,$col])
                <span class="inline-flex items-center gap-1.5 text-[11.5px] text-[#55524A] font-medium"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $col }}"></span>{{ $lbl }}</span>
                @endforeach
            </div>
        </div>

        {{-- ── RIGHT: sidebar ── --}}
        <div class="space-y-5">

            {{-- mini calendar --}}
            <div class="siarc-card siarc-shadow p-4">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">Mini calendrier</h3>
                <div class="flex items-center justify-between mb-3">
                    <button type="button" class="w-7 h-7 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                    <p class="text-[13px] font-bold text-[#1A1712]">Juillet – Août 2026</p>
                    <button type="button" class="w-7 h-7 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                </div>
                <div class="grid grid-cols-7 gap-0.5 text-center mb-1">
                    @foreach(['L','M','M','J','V','S','D'] as $d)<span class="text-[10.5px] font-bold text-[#B0AB9F] py-1">{{ $d }}</span>@endforeach
                </div>
                @php
                    // Static July → August grid (starts Mon 20 Jul), verbatim from PNG.
                    $miniCells = [
                        [20,21,22,23,24,25,26],
                        [27,28,29,30,31,1,2],
                        [3,4,5,6,7,8,9],
                        [10,11,12,13,14,15,16],
                    ];
                    // Days rendered with a faint green highlight in the PNG.
                    $miniHi = [26, 5];
                @endphp
                <div class="grid grid-cols-7 gap-0.5 text-center">
                    @foreach($miniCells as $wi => $wk)
                        @foreach($wk as $num)
                        @php
                            $isToday = ($wi === 1 && $num === 27);
                            // In Jul→Aug view, month rollover: week 0-1 = July (>=20 or 27..31), else August small numbers.
                            $isJuly = ($num >= 17);
                            $faint  = (!$isToday && ( ($wi===0 && $num===26) || ($wi===2 && $num===5) ));
                        @endphp
                        <span class="aspect-square flex items-center justify-center text-[11.5px] rounded-full
                            {{ $isToday ? 'bg-siarc-green text-white font-bold' : ($faint ? 'bg-[#E6F4EC] text-[#157A43] font-semibold' : ($isJuly ? 'text-[#1A1712] font-medium' : 'text-[#B0AB9F] font-medium')) }}">
                            {{ $num }}
                        </span>
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- événements du jour --}}
            <div class="siarc-card siarc-shadow p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">Événements du jour</h3>
                    <span class="text-[12px] font-bold text-siarc-green">6</span>
                </div>
                <ul class="relative pl-5 space-y-4">
                    <span class="absolute left-[4px] top-2 bottom-2 w-px bg-[#EEECE5]"></span>
                    @foreach($dayEvents as [$time,$title,$place,$count,$dot])
                    <li class="relative">
                        <span class="absolute -left-5 top-1 w-[9px] h-[9px] rounded-full ring-2 ring-white" style="background:{{ $dot }}"></span>
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-[#8A857A] mb-0.5">{{ $time }}</p>
                                <p class="text-[12.5px] font-semibold text-[#1A1712] leading-snug">{{ $title }}</p>
                                <p class="text-[11px] text-[#8A857A] mt-0.5 flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3 shrink-0"></i>{{ $place }}</p>
                            </div>
                            <span class="flex items-center gap-1 text-[11px] text-[#8A857A] shrink-0 pt-4">
                                <i data-lucide="users-round" class="w-3 h-3"></i>{{ $count ?? '—' }}
                            </span>
                        </div>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.admin.programme') }}" class="mt-4 block text-center text-[12px] font-semibold text-[#55524A] border border-[#E6E3DB] rounded-xl px-3 py-2.5 hover:bg-[#F5F3EE]">Voir le programme complet</a>
            </div>

            {{-- téléchargements --}}
            <div class="siarc-card siarc-shadow p-4">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">Téléchargements</h3>
                <ul class="space-y-1">
                    @foreach($downloads as $doc)
                    <li>
                        <a href="{{ $h('siarc.admin.reports') }}" class="flex items-center gap-2.5 rounded-lg px-1 py-2 hover:bg-[#FBFAF6] transition-colors">
                            <i data-lucide="file-text" class="w-4 h-4 text-[#9C6DE0] shrink-0"></i>
                            <span class="text-[12px] font-medium text-[#3B382F] flex-1">{{ $doc }}</span>
                            <i data-lucide="download" class="w-4 h-4 text-[#B0AB9F]"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.admin.reports') }}" class="mt-3 block text-center text-[12px] font-semibold text-[#55524A] border border-[#E6E3DB] rounded-xl px-3 py-2.5 hover:bg-[#F5F3EE]">Voir tous les téléchargements</a>
            </div>

        </div>
    </div>
</div>
