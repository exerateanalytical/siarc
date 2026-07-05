@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Tone map (badge → [bg, fg]) ────────────────────────────────────────────
    $toneMap = [
        'green'  => ['#E2F3E8','#157A43'],
        'gold'   => ['#FDF3E0','#C97A16'],
        'red'    => ['#FDE8E8','#C0010C'],
        'blue'   => ['#E8EFFB','#3565DE'],
        'grey'   => ['#F1F1EF','#8A857A'],
        'purple' => ['#F0EAFB','#7C4FE0'],
    ];

    // Real passed stats (Sessions, Ateliers, Intervenants). Shape: [icon,color,tile,val,label,sub]
    $sStats = $sStats ?? [];
    $stat = function($i) use ($sStats) { return $sStats[$i] ?? null; };
    $sessionsN = $stat(0)[3] ?? 0;
    $ateliersN = $stat(1)[3] ?? 0;
    $speakersN = $stat(2)[3] ?? 0;

    // KPI row — 5 cards per the approved design. Real figures where passed;
    // Conférences / Lieux reproduced verbatim as approved design metrics.
    $kpis = [
        ['calendar-days','#157A43','#E2F3E8', $sessionsN, $isFr ? 'Total activités' : 'Total activities', $isFr ? 'Tout le programme' : 'Whole programme'],
        ['mic','#7C4FE0','#F0EAFB', '24', $isFr ? 'Conférences' : 'Conferences', 'Experts & Panels'],
        ['presentation','#C97A16','#FDF3E0', $ateliersN, $isFr ? 'Ateliers' : 'Workshops', $isFr ? 'Sessions pratiques' : 'Practical sessions'],
        ['users-round','#3565DE','#E8EFFB', $speakersN, $isFr ? 'Intervenants' : 'Speakers', $isFr ? 'Experts internationaux' : 'International experts'],
        ['map-pin','#C0010C','#FDE8E8', '6', $isFr ? 'Lieux' : 'Venues', $isFr ? 'Salles & Espaces' : 'Rooms & Spaces'],
    ];

    // Sessions table (real passed data). cols: Titre, Type, Horaire, Pavillon, Intervenant
    $sTables = $sTables ?? [];
    $tbl = $sTables[0] ?? ['rows' => []];
    $rows = $tbl['rows'] ?? [];

    // Type filter chips (visual)
    $chips = [
        [$isFr ? 'Tous' : 'All', true, null],
        ['Conférences', false, '#7C4FE0'],
        ['Ateliers', false, '#C97A16'],
        ['Panels', false, '#3565DE'],
    ];

    // Right column — Résumé du programme (approved design figures)
    $mix = [
        ['Conférences', 24, '35.3%', '#7C4FE0'],
        ['Ateliers', 18, '26.5%', '#C97A16'],
        ['Panels', 12, '17.6%', '#3565DE'],
        ['Animations', 8, '11.8%', '#C0010C'],
        ['Autres', 6, '8.8%', '#8A857A'],
    ];
    $mixTotal = 68;
    // donut geometry
    $circ = 326.7; $off = 0;

    $cats = [
        ['flame','#C0010C', $isFr ? 'Artisanat & Savoir-faire' : 'Crafts & Know-how', 20],
        ['lightbulb','#3565DE', $isFr ? 'Innovation & Technologie' : 'Innovation & Technology', 14],
        ['banknote','#157A43', $isFr ? 'Entrepreneuriat & Financement' : 'Entrepreneurship & Funding', 12],
        ['palette','#7C4FE0', $isFr ? 'Design & Création' : 'Design & Creation', 8],
        ['landmark','#C97A16', $isFr ? 'Culture & Patrimoine' : 'Culture & Heritage', 7],
        ['leaf','#157A43', $isFr ? 'Développement durable' : 'Sustainable development', 5],
        ['clock','#8A857A', $isFr ? 'Autres' : 'Other', 2],
    ];

    $downloads = [
        [$isFr ? 'Programme complet (PDF)' : 'Full programme (PDF)'],
        [$isFr ? 'Plan des salles (PDF)' : 'Room map (PDF)'],
        [$isFr ? 'Brochure des activités (PDF)' : 'Activities brochure (PDF)'],
    ];

    // Featured session — take the first passed row when present
    $featured = $rows[0] ?? null;

    $sLinks = $sLinks ?? [];
@endphp

<section class="siarc-in">

    {{-- ══ PAGE HEADING ══ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div>
            <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $isFr ? 'Programme & Activités' : 'Programme & Activities' }}</h1>
            <p class="text-[13px] text-[#8A857A] mt-1">{{ $isFr ? 'Découvrez le programme officiel du SIARC 2026' : 'Discover the official SIARC 2026 programme' }}</p>
        </div>
        <div class="siarc-card siarc-shadow px-4 py-2.5 flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="calendar" class="w-[18px] h-[18px] text-siarc-green"></i></span>
            <span class="leading-tight">
                <span class="block text-[12.5px] font-semibold text-[#161513]">27 Juillet – 05 Août 2026</span>
                <span class="block text-[11px] text-[#8A857A]">Musée National de Yaoundé</span>
            </span>
        </div>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
        @foreach($kpis as [$icon,$color,$tile,$val,$label,$sub])
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}</p>
            <p class="text-[11px] text-[#B0AB9F] mt-1">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    {{-- ══ MAIN GRID: timeline (2/3) + summary rail (1/3) ══ --}}
    <div class="grid lg:grid-cols-3 gap-5 items-start">

        {{-- ─────────────── LEFT: schedule card ─────────────── --}}
        <div class="lg:col-span-2 siarc-card siarc-shadow overflow-hidden">

            {{-- View tabs + primary actions --}}
            <div class="flex flex-wrap items-center gap-3 px-5 pt-4 border-b border-[#F1EFE8]">
                <div class="flex flex-wrap items-center gap-6 flex-1 min-w-0">
                    @foreach([[$isFr?'Vue calendrier':'Calendar view',true],[$isFr?'Vue liste':'List view',false],[$isFr?'Par lieu':'By venue',false]] as [$t,$active])
                    <button class="relative pb-3 text-[13px] font-semibold transition-colors {{ $active ? 'text-siarc-green' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                        {{ $t }}
                        @if($active)<span class="absolute left-0 -bottom-px h-[2.5px] w-full rounded-full bg-siarc-green"></span>@endif
                    </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2.5 pb-3">
                    <button class="siarc-btn siarc-btn-outline !border-[#E1DED5] !text-[#3B382F] text-[12.5px] px-3.5 py-2 hover:bg-[#FBFAF6]"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter le programme' : 'Export programme' }}</button>
                    <a href="{{ $h('siarc.admin.programme') }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter une activité' : 'Add an activity' }}</a>
                </div>
            </div>

            {{-- Filters: day / date-range / category / venue / search --}}
            <div class="flex flex-wrap items-center gap-2.5 px-5 py-4 border-b border-[#F1EFE8]">
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">{{ $isFr ? 'Tous les jours' : 'All days' }} <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]"><i data-lucide="calendar" class="w-4 h-4 text-[#B0AB9F]"></i>27 Juillet – 05 Août 2026</button>
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">{{ $isFr ? 'Toutes catégories' : 'All categories' }} <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">{{ $isFr ? 'Tous les lieux' : 'All venues' }} <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <label class="relative flex-1 min-w-[160px]">
                    <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" placeholder="{{ $isFr ? 'Rechercher une activité...' : 'Search an activity...' }}" class="w-full h-[38px] rounded-xl border border-[#ECEAE3] bg-white pl-9 pr-3 text-[12.5px] text-[#3B382F] placeholder-[#B0AB9F] focus:outline-none focus:border-[#D8E5DC]">
                </label>
            </div>

            {{-- Day strip --}}
            <div class="flex items-center gap-2 px-5 py-3.5 border-b border-[#F1EFE8] overflow-x-auto">
                <button class="w-9 h-[52px] shrink-0 rounded-xl border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
                @php
                    $days = [
                        ['DIM','27 JUIL.',true],['LUN','28 JUIL.',false],['MAR','29 JUIL.',false],['MER','30 JUIL.',false],
                        ['JEU','31 JUIL.',false],['VEN','01 AOÛT',false],['SAM','02 AOÛT',false],['DIM','03 AOÛT',false],
                        ['LUN','04 AOÛT',false],['MAR','05 AOÛT',false],
                    ];
                @endphp
                @foreach($days as [$dow,$dt,$active])
                <button class="w-[62px] shrink-0 rounded-xl px-2 py-2 text-center transition-colors {{ $active ? 'bg-siarc-green text-white' : 'border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6]' }}">
                    <span class="block text-[10px] font-bold tracking-wide {{ $active ? 'text-white/80' : 'text-[#B0AB9F]' }}">{{ $dow }}</span>
                    <span class="block text-[12px] font-semibold leading-tight mt-0.5">{{ $dt }}</span>
                </button>
                @endforeach
                <button class="w-9 h-[52px] shrink-0 rounded-xl border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]"><i data-lucide="arrow-right" class="w-4 h-4"></i></button>
            </div>

            {{-- ── Timeline of sessions (REAL passed rows) ── --}}
            <div class="px-5 py-2">
                @forelse($rows as $row)
                    @php
                        $c = $row['cells'] ?? [];
                        // cells: [title, {badge,tone}, time, pavilion, speaker]
                        $title   = $c[0] ?? '—';
                        $badge   = is_array($c[1] ?? null) ? $c[1] : ['badge' => (string)($c[1] ?? ''), 'tone' => 'grey'];
                        [$bBg,$bFg] = $toneMap[$badge['tone'] ?? 'grey'] ?? $toneMap['grey'];
                        $time    = $c[2] ?? '';
                        $pavilion= $c[3] ?? '—';
                        $speaker = $c[4] ?? '—';
                    @endphp
                    <div class="flex gap-4 py-4 border-b border-[#F4F2EC] last:border-0 group">
                        {{-- time rail --}}
                        <div class="w-[52px] shrink-0 pt-0.5 relative">
                            <p class="text-[13px] font-semibold text-[#161513] leading-tight">{{ $time }}</p>
                        </div>
                        {{-- colour dot + line --}}
                        <div class="relative shrink-0 flex flex-col items-center pt-1.5">
                            <span class="w-3 h-3 rounded-full ring-4 ring-white" style="background:{{ $bFg }}"></span>
                            <span class="flex-1 w-px mt-1 bg-[#EFEDE6] group-last:hidden"></span>
                        </div>
                        {{-- content --}}
                        <a href="{{ $row['href'] ?? '#' }}" class="flex-1 min-w-0 flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide mb-1.5" style="background:{{ $bBg }};color:{{ $bFg }}">{{ $badge['badge'] }}</span>
                                <p class="text-[14px] font-semibold text-[#161513] leading-snug group-hover:text-siarc-green">{{ $title }}</p>
                                <p class="text-[11.5px] text-[#8A857A] mt-1 inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $pavilion }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="w-9 h-9 rounded-full siarc-adire flex items-center justify-center font-display text-[12px] font-extrabold text-siarc-gold shrink-0">{{ mb_substr(($speaker && $speaker !== '—') ? $speaker : $title, 0, 1) }}</span>
                                <span class="min-w-0 max-w-[160px]">
                                    <span class="block text-[12.5px] font-semibold text-[#161513] leading-tight truncate">{{ $speaker }}</span>
                                    <span class="block text-[11px] text-[#8A857A] truncate">{{ $isFr ? 'Intervenant' : 'Speaker' }}</span>
                                </span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold whitespace-nowrap" style="background:{{ $bBg }};color:{{ $bFg }}">{{ $badge['badge'] }}</span>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="py-16 text-center">
                        <span class="w-12 h-12 mx-auto rounded-2xl bg-[#F1F1EF] flex items-center justify-center mb-3"><i data-lucide="calendar-clock" class="w-6 h-6 text-[#B0AB9F]"></i></span>
                        <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucune session au programme' : 'No sessions in the programme' }}</p>
                        <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'À venir — les sessions apparaîtront ici dès leur publication.' : 'Coming soon — sessions will appear here once published.' }}</p>
                    </div>
                @endforelse
            </div>

            {{-- footer --}}
            @if(count($rows))
            <div class="px-5 py-4 border-t border-[#F1EFE8] text-center">
                <button class="siarc-btn text-[12.5px] px-4 py-2 border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6] mx-auto">{{ $isFr ? "Voir plus d'activités" : 'Show more activities' }} <i data-lucide="chevron-down" class="w-4 h-4"></i></button>
            </div>
            @endif
        </div>

        {{-- ─────────────── RIGHT: summary rail ─────────────── --}}
        <div class="space-y-5">

            {{-- Résumé du programme (donut) --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Résumé du programme' : 'Programme summary' }}</h3>
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <svg viewBox="0 0 120 120" class="w-[120px] h-[120px] -rotate-90">
                            @foreach($mix as [$lbl,$pct,$share,$col])
                                @php $len = $circ * ($pct/$mixTotal); @endphp
                                <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="13"
                                    stroke-dasharray="{{ round($len,1) }} {{ round($circ-$len,1) }}"
                                    stroke-dashoffset="{{ -round($off,1) }}"/>
                                @php $off += $len; @endphp
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-[22px] font-extrabold text-[#161513] leading-none">{{ $mixTotal }}</span>
                            <span class="text-[10px] text-[#8A857A]">{{ $isFr ? 'Activités' : 'Activities' }}</span>
                        </div>
                    </div>
                    <ul class="flex-1 space-y-2">
                        @foreach($mix as [$lbl,$pct,$share,$col])
                        <li class="flex items-center gap-2 text-[11.5px]">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span>
                            <span class="text-[#3B382F] font-medium">{{ $lbl }} ({{ $pct }})</span>
                            <span class="ml-auto text-[#8A857A]">{{ $share }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Catégories d'activités --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? "Catégories d'activités" : 'Activity categories' }}</h3>
                <ul class="space-y-3">
                    @foreach($cats as [$icon,$col,$name,$n])
                    <li class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $col }}1A"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
                        <span class="text-[12.5px] text-[#3B382F] font-medium flex-1 min-w-0 truncate">{{ $name }}</span>
                        <span class="text-[12.5px] font-semibold text-[#8A857A]">{{ $n }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Activité à ne pas manquer (featured — real row when present) --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'Activité à ne pas manquer' : 'Not to be missed' }}</h3>
                @php
                    $fc = $featured['cells'] ?? null;
                    $fTitle = $fc[0] ?? ($isFr ? "L'avenir de l'artisanat africain : défis et opportunités" : 'The future of African crafts: challenges & opportunities');
                    $fBadgeArr = is_array($fc[1] ?? null) ? $fc[1] : ['badge' => $isFr ? 'Conférence inaugurale' : 'Opening conference', 'tone' => 'gold'];
                    $fTime = $fc[2] ?? '10:00 – 11:30';
                    $fPav = $fc[3] ?? 'Salle A';
                    $fSpeaker = ($fc[4] ?? null) && ($fc[4] !== '—') ? $fc[4] : 'Dr. Christian Tchana';
                @endphp
                <div class="rounded-2xl siarc-adire p-5 text-white relative overflow-hidden">
                    <div class="siarc-kente absolute top-0 left-0 right-0"></div>
                    <div class="flex items-start justify-between gap-3 pt-2">
                        <div class="min-w-0">
                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-siarc-gold text-[#3a2a00] mb-2">{{ $fBadgeArr['badge'] }}</span>
                            <p class="font-display text-[16px] font-extrabold leading-snug">{{ $fTitle }}</p>
                        </div>
                        <i data-lucide="star" class="w-5 h-5 text-siarc-gold shrink-0"></i>
                    </div>
                    <div class="flex items-center gap-3 mt-4">
                        <span class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center font-display text-[14px] font-extrabold text-siarc-gold shrink-0">{{ mb_substr($fSpeaker,0,1) }}</span>
                        <div class="min-w-0">
                            <p class="text-[13px] font-semibold leading-tight truncate">{{ $fSpeaker }}</p>
                            <p class="text-[11px] text-white/70 truncate">{{ $isFr ? 'Économiste – Spécialiste Afrique' : 'Economist – Africa specialist' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-4 pt-4 border-t border-white/15 text-[11.5px] text-white/85">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>{{ $isFr ? '28 Juillet 2026' : '28 July 2026' }}</span>
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5"></i>{{ $fTime }}</span>
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $fPav }}</span>
                    </div>
                </div>
            </div>

            {{-- Téléchargements --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Téléchargements' : 'Downloads' }}</h3>
                <ul class="space-y-2.5">
                    @foreach($downloads as [$label])
                    <li>
                        <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-3 py-2.5 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-[#FDE8E8] flex items-center justify-center shrink-0"><i data-lucide="clipboard-list" class="w-4 h-4 text-siarc-red"></i></span>
                            <span class="text-[12.5px] font-medium text-[#3B382F] flex-1 min-w-0 truncate">{{ $label }}</span>
                            <i data-lucide="download" class="w-4 h-4 text-[#B0AB9F]"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.programme') }}" class="mt-3 w-full siarc-btn text-[12.5px] px-4 py-2.5 border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6] justify-center">{{ $isFr ? 'Voir tous les téléchargements' : 'See all downloads' }}</a>
            </div>
        </div>
    </div>

    {{-- ══ RELATED PAGES STRIP (from $sLinks) ══ --}}
    @if(count($sLinks))
    <div class="mt-6">
        <p class="siarc-kicker text-siarc-ochre mb-3">{{ $isFr ? 'Gestion liée' : 'Related pages' }}</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($sLinks as $lk)
            @php
                $lt = $lk['label'] ?? ($lk[1] ?? '');
                $lHref = $lk['href'] ?? (isset($lk[2]) ? $h($lk[2]) : '#');
                $li = $lk['icon'] ?? ($lk[0] ?? 'arrow-right');
            @endphp
            <a href="{{ $lHref }}" class="siarc-card siarc-shadow siarc-lift p-4 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="{{ $li }}" class="w-5 h-5 text-siarc-green"></i></span>
                <span class="min-w-0">
                    <span class="block text-[12.5px] font-semibold text-[#161513] leading-tight truncate">{{ $lt }}</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-ochre">{{ $isFr ? 'Ouvrir' : 'Open' }} <i data-lucide="arrow-right" class="w-3 h-3"></i></span>
                </span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</section>
