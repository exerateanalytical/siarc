@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── KPI row — verbatim from approved design ─────────────────────────────
    $kpis = [
        ['calendar-days','#157A43','#E4F3E9','Total activités','68','Tout le programme'],
        ['mic','#7C4FE0','#F0EAFB','Conférences','24','Experts & Panels'],
        ['presentation','#C97A16','#FDF0DE','Ateliers','18','Sessions pratiques'],
        ['users-round','#3565DE','#E8EFFB','Intervenants','56','Experts internationaux'],
        ['map-pin','#C0010C','#FDE8E8','Lieux','6','Salles & Espaces'],
    ];

    // ── Day strip (SUN 27 JUL → MON 05 AUG) — verbatim ──────────────────────
    $days = [
        ['DIM','27 JUIL.',true],['LUN','28 JUIL.',false],['MAR','29 JUIL.',false],['MER','30 JUIL.',false],
        ['JEU','31 JUIL.',false],['VEN','01 AOÛT',false],['SAM','02 AOÛT',false],['DIM','03 AOÛT',false],
        ['LUN','04 AOÛT',false],['MAR','05 AOÛT',false],
    ];

    // ── Timeline activities — verbatim from approved design ──────────────────
    // [start,end, badge, badgeBg,badgeFg, dot, title, venue, avatar/type, speaker, role, pill,pillBg,pillFg]
    $activities = [
        ['09:00','10:00','CÉRÉMONIE','#E4F3E9','#157A43','#157A43',
            "Cérémonie d'ouverture officielle",'Espace Principal','img:programme-featured-1',
            'Ministre des PMEESA','Gouvernement du Cameroun','Public','#E4F3E9','#157A43'],
        ['10:30','12:00','CONFÉRENCE','#F0EAFB','#7C4FE0','#7C4FE0',
            "L'artisanat africain à l'ère de l'innovation et du numérique",'Salle de Conférence A','img:prog-av1',
            'Dr. Alain Mbarga','Expert en Innovation','Conférence','#E4F3E9','#157A43'],
        ['14:00','15:30','ATELIER','#FDF0DE','#C97A16','#C97A16',
            'Design & tendances : valoriser le patrimoine culturel','Atelier Créatif 1','img:prog-av3',
            'Marie Claire Nguimatsia','Designer','Atelier','#FDF0DE','#C97A16'],
        ['16:00','17:30','PANEL','#E8EFFB','#3565DE','#3565DE',
            'Financement et accompagnement des artisans','Salle de Conférence B','group',
            '4 intervenants','','Panel','#E8EFFB','#3565DE'],
        ['18:00','19:30','ANIMATION CULTURELLE','#FDE8E8','#C0010C','#C0010C',
            'Soirée culturelle – Musiques & danses traditionnelles','Espace Animation','none',
            '','','Culturel','#FDE8E8','#C0010C'],
    ];

    // ── Résumé du programme (donut) — verbatim ──────────────────────────────
    $mix = [
        ['Conférences','24','35.3%','#7C4FE0'],
        ['Ateliers','18','26.5%','#C97A16'],
        ['Panels','12','17.6%','#3565DE'],
        ['Animations','8','11.8%','#F0506E'],
        ['Autres','6','8.8%','#9A948A'],
    ];
    $mixTotal = 68;
    $circ = 326.7; $off = 0;

    // ── Catégories d'activités — verbatim ───────────────────────────────────
    $cats = [
        ['flame','#C0010C','Artisanat & Savoir-faire','20'],
        ['lightbulb','#3565DE','Innovation & Technologie','14'],
        ['banknote','#157A43','Entrepreneuriat & Financement','12'],
        ['palette','#7C4FE0','Design & Création','8'],
        ['landmark','#C97A16','Culture & Patrimoine','7'],
        ['leaf','#157A43','Développement durable','5'],
        ['clock','#9A948A','Autres','2'],
    ];

    // ── Téléchargements — verbatim ──────────────────────────────────────────
    $downloads = [
        'Programme complet (PDF)',
        'Plan des salles (PDF)',
        'Brochure des activités (PDF)',
    ];
@endphp

<section class="siarc-in">

    {{-- ══ PAGE HEADING + date pill ══ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div>
            <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">Programme &amp; Activités</h1>
            <p class="text-[13px] text-[#8A857A] mt-1">Découvrez le programme officiel du SIARC 2026</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-full bg-[#F1EFE8] flex items-center justify-center shrink-0"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#8A857A]"></i></span>
            <span class="leading-tight">
                <span class="block text-[13px] font-semibold text-[#161513]">27 Juillet – 05 Août 2026</span>
                <span class="block text-[11.5px] text-[#8A857A]">Musée National de Yaoundé</span>
            </span>
        </div>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
        @foreach($kpis as [$icon,$color,$tile,$label,$val,$sub])
        <div class="siarc-card siarc-shadow p-4 flex items-center gap-3.5">
            <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-[21px] h-[21px]" style="color:{{ $color }}"></i></span>
            <span class="min-w-0">
                <span class="block text-[12px] text-[#8A857A] font-medium">{{ $label }}</span>
                <span class="block text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}</span>
                <span class="block text-[11px] text-[#B0AB9F] mt-0.5">{{ $sub }}</span>
            </span>
        </div>
        @endforeach
    </div>

    {{-- ══ MAIN GRID: schedule (2/3) + summary rail (1/3) ══ --}}
    <div class="grid lg:grid-cols-3 gap-5 items-start">

        {{-- ─────────────── LEFT: schedule card ─────────────── --}}
        <div class="lg:col-span-2 siarc-card siarc-shadow overflow-hidden">

            {{-- View tabs + primary actions --}}
            <div class="flex flex-wrap items-center gap-3 px-5 pt-4 border-b border-[#F1EFE8]">
                <div class="flex flex-wrap items-center gap-7 flex-1 min-w-0">
                    @foreach([['Vue calendrier',true],['Vue liste',false],['Par lieu',false]] as [$t,$active])
                    <button class="relative pb-3 text-[13px] font-semibold transition-colors {{ $active ? 'text-siarc-green' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                        {{ $t }}
                        @if($active)<span class="absolute left-0 -bottom-px h-[2.5px] w-full rounded-full bg-siarc-green"></span>@endif
                    </button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2.5 pb-3">
                    <button class="siarc-btn siarc-btn-outline !border-[#E1DED5] !text-[#3B382F] text-[12.5px] px-3.5 py-2 hover:bg-[#FBFAF6]"><i data-lucide="download" class="w-4 h-4"></i>Exporter le programme</button>
                    <a href="{{ $h('siarc.admin.programme') }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter une activité</a>
                </div>
            </div>

            {{-- Filters: day / date-range / category / venue / search --}}
            <div class="flex flex-wrap items-center gap-2.5 px-5 py-4 border-b border-[#F1EFE8]">
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">Tous les jours <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">27 Juillet – 05 Août 2026 <i data-lucide="calendar" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">Toutes catégories <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <button class="h-[38px] flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[12.5px] text-[#3B382F] hover:border-[#D8E5DC]">Tous les lieux <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></button>
                <label class="relative flex-1 min-w-[160px]">
                    <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" placeholder="Rechercher une activité..." class="w-full h-[38px] rounded-xl border border-[#ECEAE3] bg-white pl-9 pr-3 text-[12.5px] text-[#3B382F] placeholder-[#B0AB9F] focus:outline-none focus:border-[#D8E5DC]">
                </label>
            </div>

            {{-- Day strip --}}
            <div class="flex items-center gap-2 px-5 py-3.5 border-b border-[#F1EFE8] overflow-x-auto">
                <button class="w-9 h-[52px] shrink-0 rounded-xl border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]"><i data-lucide="arrow-left" class="w-4 h-4"></i></button>
                @foreach($days as [$dow,$dt,$active])
                <button class="w-[62px] shrink-0 rounded-xl px-2 py-2 text-center transition-colors {{ $active ? 'bg-siarc-green text-white' : 'border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6]' }}">
                    <span class="block text-[10px] font-bold tracking-wide {{ $active ? 'text-white/80' : 'text-[#B0AB9F]' }}">{{ $dow }}</span>
                    <span class="block text-[12px] font-semibold leading-tight mt-0.5">{{ $dt }}</span>
                </button>
                @endforeach
                <button class="w-9 h-[52px] shrink-0 rounded-xl border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]"><i data-lucide="arrow-right" class="w-4 h-4"></i></button>
            </div>

            {{-- ── Timeline of sessions (verbatim) ── --}}
            <div class="px-5 py-2">
                @foreach($activities as $i => $a)
                    @php
                        [$start,$end,$badge,$bBg,$bFg,$dot,$title,$venue,$avatar,$speaker,$role,$pill,$pBg,$pFg] = $a;
                        $isLast = $i === array_key_last($activities);
                    @endphp
                    <div class="flex gap-4 py-5 {{ $isLast ? '' : 'border-b border-[#F4F2EC]' }}">
                        {{-- time rail --}}
                        <div class="w-[46px] shrink-0 pt-0.5 leading-tight">
                            <p class="text-[13px] font-semibold text-[#161513]">{{ $start }}</p>
                            <p class="text-[13px] text-[#B0AB9F] mt-0.5">{{ $end }}</p>
                        </div>
                        {{-- colour dot + connector line --}}
                        <div class="relative shrink-0 flex flex-col items-center pt-1.5">
                            <span class="w-3 h-3 rounded-full ring-4 ring-white z-10" style="background:{{ $dot }}"></span>
                            @unless($isLast)<span class="flex-1 w-[2px] mt-1 rounded-full" style="background:{{ $dot }}20"></span>@endunless
                        </div>
                        {{-- content --}}
                        <div class="flex-1 min-w-0 flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide mb-2" style="background:{{ $bBg }};color:{{ $bFg }}">{{ $badge }}</span>
                                <p class="text-[15px] font-semibold text-[#161513] leading-snug">{{ $title }}</p>
                                <p class="text-[12px] text-[#8A857A] mt-1.5 inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $venue }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                @if($speaker)
                                <div class="flex items-center gap-2.5">
                                    @if(str_starts_with($avatar,'img:'))
                                        <img src="{{ asset('images/siarc/'.substr($avatar,4).'.png') }}" alt="{{ $speaker }}" class="w-9 h-9 rounded-full object-cover shrink-0 ring-2 ring-white">
                                    @elseif($avatar === 'group')
                                        <span class="flex -space-x-2 shrink-0">
                                            <img src="{{ asset('images/siarc/prog-av1.png') }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-white">
                                            <img src="{{ asset('images/siarc/prog-av2.png') }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-white">
                                            <img src="{{ asset('images/siarc/prog-av3.png') }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-white">
                                        </span>
                                    @endif
                                    <span class="min-w-0 max-w-[170px]">
                                        <span class="block text-[13px] font-semibold text-[#161513] leading-tight truncate">{{ $speaker }}</span>
                                        @if($role)<span class="block text-[11.5px] text-[#8A857A] truncate">{{ $role }}</span>@endif
                                    </span>
                                </div>
                                @endif
                                <span class="inline-flex items-center rounded-md px-3 py-1 text-[11.5px] font-semibold whitespace-nowrap" style="background:{{ $pBg }};color:{{ $pFg }}">{{ $pill }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- footer --}}
            <div class="px-5 py-4 border-t border-[#F1EFE8] text-center">
                <button class="siarc-btn text-[12.5px] px-5 py-2.5 rounded-full border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6] mx-auto">Voir plus d'activités <i data-lucide="chevron-down" class="w-4 h-4"></i></button>
            </div>
        </div>

        {{-- ─────────────── RIGHT: summary rail ─────────────── --}}
        <div class="space-y-5">

            {{-- Résumé du programme (donut) --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Résumé du programme</h3>
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <svg viewBox="0 0 120 120" class="w-[118px] h-[118px] -rotate-90">
                            @foreach($mix as [$lbl,$cnt,$share,$col])
                                @php $len = $circ * ($cnt/$mixTotal); @endphp
                                <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="15"
                                    stroke-dasharray="{{ round($len,1) }} {{ round($circ-$len,1) }}"
                                    stroke-dashoffset="{{ -round($off,1) }}"/>
                                @php $off += $len; @endphp
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-[22px] font-extrabold text-[#161513] leading-none">{{ $mixTotal }}</span>
                            <span class="text-[10px] text-[#8A857A] mt-0.5">Activités</span>
                        </div>
                    </div>
                    <ul class="flex-1 space-y-2.5">
                        @foreach($mix as [$lbl,$cnt,$share,$col])
                        <li class="flex items-center gap-2 text-[11.5px]">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span>
                            <span class="text-[#3B382F] font-medium">{{ $lbl }} ({{ $cnt }})</span>
                            <span class="ml-auto text-[#8A857A]">{{ $share }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Catégories d'activités --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Catégories d'activités</h3>
                <ul class="space-y-3.5">
                    @foreach($cats as [$icon,$col,$name,$n])
                    <li class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $col }}1A"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
                        <span class="text-[12.5px] text-[#3B382F] font-medium flex-1 min-w-0 truncate">{{ $name }}</span>
                        <span class="text-[13px] font-semibold text-[#8A857A]">{{ $n }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Activité à ne pas manquer (featured) --}}
            <div>
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">Activité à ne pas manquer</h3>
                <div class="rounded-2xl siarc-adire siarc-shadow p-5 text-white relative overflow-hidden">
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/siarc/programme-featured-1.png') }}" alt="Dr. Christian Tchana" class="w-14 h-14 rounded-full object-cover shrink-0 ring-2 ring-white/25">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <span class="text-[10.5px] font-bold uppercase tracking-wide text-siarc-gold">Conférence inaugurale</span>
                                <i data-lucide="bookmark" class="w-4 h-4 text-white/80 shrink-0"></i>
                            </div>
                            <p class="font-display text-[17px] font-extrabold leading-snug mt-1.5">L'avenir de l'artisanat africain : défis et opportunités</p>
                        </div>
                    </div>
                    <div class="mt-3 ml-[72px]">
                        <p class="text-[13px] font-semibold leading-tight">Dr. Christian Tchana</p>
                        <p class="text-[11.5px] text-white/70">Économiste – Spécialiste Afrique</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mt-4 text-[11.5px] text-white/85">
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5"></i>28 Juillet 2026</span>
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5"></i>10:00 – 11:30</span>
                        <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Salle A</span>
                    </div>
                </div>
            </div>

            {{-- Téléchargements --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Téléchargements</h3>
                <ul class="space-y-2.5">
                    @foreach($downloads as $label)
                    <li>
                        <a href="{{ route('siarc.home', ['lang' => $lang]) }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-3 py-2.5 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-[#FDE8E8] flex items-center justify-center shrink-0"><i data-lucide="file-text" class="w-4 h-4 text-siarc-red"></i></span>
                            <span class="text-[12.5px] font-medium text-[#3B382F] flex-1 min-w-0 truncate">{{ $label }}</span>
                            <i data-lucide="download" class="w-4 h-4 text-[#B0AB9F]"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.admin.programme') }}" class="mt-3 w-full siarc-btn text-[12.5px] px-4 py-2.5 border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6] justify-center">Voir tous les téléchargements</a>
            </div>
        </div>
    </div>

</section>
