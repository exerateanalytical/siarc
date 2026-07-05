@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Speakers (design content, verbatim from approved PNG) ───────────────────
    // role tone: green=Conférencier, purple=Paneliste/Panéliste, pink=Modératrice
    $speakers = [
        ['img'=>'spk-1','badge'=>'Conférencier','tone'=>'green','name'=>'Dr. Alain Mbarga','flag'=>'🇨🇲','country'=>'Cameroun','role'=>'Expert en Innovation & Transformation Digitale','tags'=>['Innovation','Technologie']],
        ['img'=>'spk-2','badge'=>'Paneliste','tone'=>'purple','name'=>'Awa Diop','flag'=>'🇸🇳','country'=>'Sénégal','role'=>"Entrepreneure & Fondatrice Afrik'Artisan",'tags'=>['Entrepreneuriat','Artisanat']],
        ['img'=>'spk-3','badge'=>'Conférencier','tone'=>'green','name'=>'Jean-Marc Leclerc','flag'=>'🇫🇷','country'=>'France','role'=>'Expert en Design & Patrimoine Culturel','tags'=>['Design','Patrimoine']],
        ['img'=>'spk-4','badge'=>'Modératrice','tone'=>'pink','name'=>'Njeri Wanjiku','flag'=>'🇰🇪','country'=>'Kenya','role'=>'Spécialiste Commerce Équitable & Impact Social','tags'=>['Commerce équitable','Impact']],
        ['img'=>'spk-5','badge'=>'Conférencier','tone'=>'green','name'=>'Youssef El Amrani','flag'=>'🇲🇦','country'=>'Maroc','role'=>'Expert en Export & Marchés Internationaux','tags'=>['Export','Marchés']],
        ['img'=>'spk-6','badge'=>'Paneliste','tone'=>'purple','name'=>'Funke Adewale','flag'=>'🇳🇬','country'=>'Nigeria','role'=>'Fondatrice, Creative Hands Initiative','tags'=>['Artisanat','Formation']],
        ['img'=>'spk-7','badge'=>'Conférencier','tone'=>'green','name'=>'Patrick Uwizeyimana','flag'=>'🇷🇼','country'=>'Rwanda','role'=>'Consultant en Innovation & PME','tags'=>['Artisanat','PME']],
        ['img'=>'spk-8','badge'=>'Paneliste','tone'=>'purple','name'=>'Dr. Anna Müller','flag'=>'🇩🇪','country'=>'Allemagne','role'=>'Experte en Développement Durable','tags'=>['Durabilité','Économie verte']],
    ];

    $toneMap = [
        'green'  => ['text'=>'#157A43','bg'=>'#E7F1EB','br'=>'#CDE6D8'],
        'purple' => ['text'=>'#7C4FE0','bg'=>'#EFEAFB','br'=>'#DED3F5'],
        'pink'   => ['text'=>'#C4457E','bg'=>'#FBE8F1','br'=>'#F4D2E3'],
    ];

    // KPI row
    $kpis = [
        ['users-round','128','Intervenants','Experts & leaders','#157A43','#E7F1EB'],
        ['users','35','Pays représentés','Afrique, Europe, Asie…','#7C4FE0','#EFEAFB'],
        ['mic','12','Thématiques','Secteurs couverts','#C97A16','#FBEFE0'],
        ['presentation','64','Sessions','Conférences & panels','#157A43','#E7F1EB'],
    ];

    // Sidebar — Catégories d'intervenants
    $spkCats = [['Conférenciers',54],['Panélistes',46],['Modérateurs',18],['Ateliers Animators',10]];

    // Sidebar — Thématiques
    $themes = [
        ['lightbulb','Innovation & Technologie',24,'#7C4FE0'],
        ['palette','Artisanat & Design',22,'#157A43'],
        ['rocket','Entrepreneuriat',20,'#C97A16'],
        ['store','Commerce & Marchés',18,'#C0010C'],
        ['leaf','Développement Durable',16,'#157A43'],
        ['landmark','Culture & Patrimoine',14,'#E6B201'],
    ];
@endphp

{{-- ══════════════════ PAGE HEADING ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 pt-7 pb-6 siarc-in">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-[26px] font-extrabold tracking-tight text-[#1A1712]">Speaker Directory</h1>
                <p class="mt-1.5 text-[13.5px] text-[#8A857A] leading-relaxed">Découvrez les experts, leaders et innovateurs qui prennent la parole au SIARC 2026</p>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ FILTER BAR ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-4 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px]">
            <i data-lucide="search" class="w-4 h-4 text-[#A8A498] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" placeholder="Rechercher un intervenant par nom, thème, organisation…"
                class="w-full text-[12.5px] text-[#55524A] bg-[#FBFAF7] border border-[#ECEAE3] rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-siarc-green">
        </div>
        <button class="inline-flex items-center gap-6 text-[12.5px] font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            Tous les rôles <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i>
        </button>
        <button class="inline-flex items-center gap-6 text-[12.5px] font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            Tous les pays <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i>
        </button>
        <button class="inline-flex items-center gap-6 text-[12.5px] font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            Tous les thèmes <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i>
        </button>
        <button class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            <i data-lucide="sliders-horizontal" class="w-4 h-4 text-[#8A857A]"></i> Plus de filtres
        </button>
    </div>
</section>

{{-- ══════════════════ MAIN GRID ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-7 grid lg:grid-cols-[1fr_296px] gap-6 items-start">

        {{-- ── LEFT COLUMN ────────────────────────────────────────────────── --}}
        <div>
            {{-- KPI ROW --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7">
                @foreach($kpis as [$icon,$num,$label,$sub,$col,$bg])
                <div class="siarc-card siarc-shadow px-5 py-4 flex items-center gap-3.5">
                    <span class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0" style="background:{{ $bg }}">
                        <i data-lucide="{{ $icon }}" class="w-5.5 h-5.5" style="color:{{ $col }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="font-display text-[24px] font-extrabold text-[#1A1712] leading-none">{{ $num }}</p>
                        <p class="text-[12.5px] font-semibold text-[#1A1712] mt-1">{{ $label }}</p>
                        <p class="text-[10.5px] text-[#A8A498] mt-0.5">{{ $sub }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- TOOLBAR --}}
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-[19px] font-bold text-[#1A1712]">Tous les intervenants</h2>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 text-[12px] text-[#8A857A]">
                        Trier par :
                        <button class="inline-flex items-center gap-2 font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-lg px-3 py-1.5">
                            Nom (A-Z) <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498]"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button class="w-8 h-8 rounded-lg bg-siarc-green text-white flex items-center justify-center"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
                        <button class="w-8 h-8 rounded-lg bg-white border border-[#ECEAE3] text-[#A8A498] flex items-center justify-center"><i data-lucide="list" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </div>

            {{-- SPEAKER CARDS --}}
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                @foreach($speakers as $s)
                @php $t = $toneMap[$s['tone']]; @endphp
                <div class="siarc-card siarc-shadow siarc-lift p-4 flex flex-col relative">
                    {{-- role badge + star --}}
                    <div class="flex items-start justify-between mb-3">
                        <span class="inline-flex items-center text-[10.5px] font-semibold rounded-full px-2.5 py-1"
                              style="color:{{ $t['text'] }};background:{{ $t['bg'] }};border:1px solid {{ $t['br'] }}">{{ $s['badge'] }}</span>
                        <button class="text-[#C9C4B8] hover:text-siarc-gold transition-colors"><i data-lucide="star" class="w-4 h-4"></i></button>
                    </div>

                    {{-- avatar --}}
                    <img src="{{ asset('images/siarc/'.$s['img'].'.png') }}" alt="{{ $s['name'] }}"
                         class="w-[74px] h-[74px] rounded-full object-cover mx-auto">

                    <h3 class="font-display text-[15.5px] font-bold text-[#1A1712] text-center mt-3 leading-snug">{{ $s['name'] }}</h3>
                    <p class="flex items-center justify-center gap-1.5 text-[11.5px] text-[#8A857A] mt-1">
                        <span class="text-[13px] leading-none">{{ $s['flag'] }}</span> {{ $s['country'] }}
                    </p>

                    <p class="text-[11.5px] text-[#55524A] text-center mt-3 leading-relaxed min-h-[34px]">{{ $s['role'] }}</p>

                    {{-- theme tags --}}
                    <div class="flex flex-wrap justify-center gap-1.5 mt-3">
                        @foreach($s['tags'] as $tag)
                        <span class="text-[10.5px] text-[#6B675E] bg-[#F5F3EE] rounded-md px-2 py-1">{{ $tag }}</span>
                        @endforeach
                    </div>

                    {{-- footer actions --}}
                    <div class="flex items-center gap-2 mt-4 pt-3.5 border-t border-[#F2F0EA]">
                        <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[#8A857A] flex items-center justify-center shrink-0 hover:border-siarc-green hover:text-siarc-green transition-colors"><i data-lucide="calendar-days" class="w-4 h-4"></i></button>
                        <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[#8A857A] flex items-center justify-center shrink-0 hover:border-siarc-green hover:text-siarc-green transition-colors"><i data-lucide="id-card" class="w-4 h-4"></i></button>
                        <a href="{{ $h('siarc.speakers') }}" class="flex-1 inline-flex items-center justify-center text-[11.5px] font-semibold text-siarc-green border border-[#CDE6D8] bg-white rounded-lg px-2 py-2 hover:bg-[#E7F1EB] transition-colors">Voir le profil</a>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- LOAD MORE --}}
            <div class="flex justify-center mt-7">
                <button class="siarc-btn px-6 py-3 text-[12.5px] font-semibold text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl hover:border-siarc-green transition-colors">
                    <i data-lucide="rotate-cw" class="w-4 h-4 text-[#8A857A]"></i> Charger plus d'intervenants <i data-lucide="arrow-down" class="w-4 h-4 text-[#8A857A]"></i>
                </button>
            </div>
        </div>

        {{-- ── RIGHT SIDEBAR ──────────────────────────────────────────────── --}}
        <aside class="space-y-5">
            {{-- Catégories d'intervenants --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[15.5px] font-bold text-[#1A1712] mb-4">Catégories d'intervenants</h3>
                <div class="space-y-3">
                    @foreach($spkCats as [$cname,$ccount])
                    <div class="flex items-center justify-between {{ !$loop->last ? 'pb-3 border-b border-[#F2F0EA]' : '' }}">
                        <span class="text-[12.5px] text-[#55524A]">{{ $cname }}</span>
                        <span class="text-[11.5px] font-bold text-[#55524A] bg-[#F5F3EE] rounded-md px-2.5 py-0.5">{{ $ccount }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Thématiques --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[15.5px] font-bold text-[#1A1712] mb-4">Thématiques</h3>
                <div class="space-y-3">
                    @foreach($themes as [$icon,$tname,$tcount,$tcol])
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0" style="color:{{ $tcol }}"></i>
                        <span class="text-[12.5px] text-[#55524A] flex-1 leading-snug">{{ $tname }}</span>
                        <span class="text-[11.5px] font-bold text-[#55524A] bg-[#F5F3EE] rounded-md px-2.5 py-0.5">{{ $tcount }}</span>
                    </div>
                    @endforeach
                </div>
                <a href="{{ $h('siarc.programme') }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-siarc-green mt-4 pt-3.5 border-t border-[#F2F0EA] w-full justify-center hover:gap-2.5 transition-all">
                    Voir toutes les thématiques <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- Devenez intervenant CTA --}}
            <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-[#0F5B2C] to-[#042B15] text-white p-5 relative">
                <span class="absolute right-3 bottom-0 text-white/10"><i data-lucide="mic" class="w-24 h-24"></i></span>
                <div class="relative">
                    <h3 class="font-display text-[16px] font-bold mb-2">Devenez intervenant</h3>
                    <p class="text-[11.5px] text-white/75 leading-relaxed mb-4 max-w-[190px]">Partagez votre expertise et inspirer des milliers d'acteurs de l'artisanat.</p>
                    <a href="{{ $h('siarc.register') }}" class="inline-flex items-center gap-2 text-[11.5px] font-semibold text-[#0B3A1E] bg-white rounded-lg px-3.5 py-2.5 hover:bg-white/90 transition-colors">
                        <i data-lucide="mic" class="w-4 h-4"></i> Proposer une intervention
                    </a>
                </div>
            </div>

            {{-- Intervenant à l'honneur --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[15.5px] font-bold text-[#1A1712] mb-4">Intervenant à l'honneur</h3>
                <div class="flex items-start gap-3">
                    <img src="{{ asset('images/siarc/spk-1.png') }}" alt="Dr. Alain Mbarga" class="w-12 h-12 rounded-full object-cover shrink-0">
                    <div class="min-w-0">
                        <p class="font-display text-[14px] font-bold text-[#1A1712] leading-snug">Dr. Alain Mbarga</p>
                        <p class="flex items-center gap-1.5 text-[11.5px] text-[#8A857A] mt-0.5"><span class="text-[13px] leading-none">🇨🇲</span> Cameroun</p>
                        <span class="inline-block text-[10px] font-bold text-siarc-green bg-[#E7F1EB] rounded-md px-2 py-0.5 mt-1.5">Keynote Speaker</span>
                    </div>
                </div>
                <p class="text-[12px] text-[#55524A] leading-relaxed mt-3.5">L'avenir de l'artisanat africain dans l'ère numérique</p>
                <a href="{{ $h('siarc.speakers') }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-siarc-green mt-3.5 pt-3.5 border-t border-[#F2F0EA] w-full justify-center hover:gap-2.5 transition-all">
                    Voir son profil <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </aside>
    </div>
</section>
