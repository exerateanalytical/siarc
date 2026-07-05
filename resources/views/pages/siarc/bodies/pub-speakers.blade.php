@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // Real speaker data from the DB (photo null → initials avatar)
    $eid = siarcEvent()?->id ?? 0;
    $spk = collect();
    try {
        $spk = DB::table('speakers')->where('event_id',$eid)->orderBy('sort_order')
            ->get(['id','name','role_fr','organization','photo','is_featured']);
    } catch (\Throwable $e) { $spk = collect(); }

    // Palette cycle for initials avatars
    $avatarBg = ['#157A43','#0F4824','#C97A16','#14652F','#C0010C','#0B3A1E'];
    $initials = function($name){
        $parts = preg_split('/\s+/', trim(preg_replace('/^(Dr\.?|M\.|Mme|Prof\.?)\s+/i','',$name)));
        $a = mb_substr($parts[0] ?? '', 0, 1);
        $b = mb_substr($parts[count($parts)-1] ?? '', 0, 1);
        return mb_strtoupper($a.$b);
    };

    // Sidebar summary categories (approved design figures)
    $spkCats = [['Conférenciers',54],['Panélistes',46],['Modérateurs',18],['Ateliers Animators',10]];
    $themes = [
        ['lightbulb','Innovation & Technologie',24,'#7C4FE0'],
        ['palette','Artisanat & Design',20,'#157A43'],
        ['rocket','Entrepreneuriat',18,'#C97A16'],
        ['store','Commerce & Marchés',18,'#C0010C'],
        ['leaf','Développement Durable',16,'#157A43'],
        ['landmark','Culture & Patrimoine',14,'#E6B201'],
    ];

    // Featured speaker (À l'honneur) — first featured, else first
    $featured = $spk->firstWhere('is_featured', 1) ?? $spk->first();
@endphp

{{-- ══════════════════ HEADER BAND ══════════════════ --}}
<section class="siarc-mud border-b border-[#EDE7DA] relative overflow-hidden">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-10 lg:py-12 siarc-in">
        <h1 class="font-display text-[34px] sm:text-[40px] font-extrabold tracking-tight text-[#1A1712]">
            {{ $isFr ? 'Répertoire des Intervenants' : 'Speaker Directory' }}
        </h1>
        <p class="mt-2 text-[14.5px] text-[#55524A] leading-relaxed max-w-[640px]">
            {{ $isFr ? 'Découvrez les experts, leaders et innovateurs qui prennent la parole au SIARC 2026.' : 'Discover the experts, leaders and innovators speaking at SIARC 2026.' }}
        </p>
    </div>
</section>

{{-- ══════════════════ FILTER BAR ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-5 flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[240px]">
            <i data-lucide="search" class="w-4.5 h-4.5 text-[#A8A498] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" placeholder="Rechercher un intervenant par nom, thème, organisation…"
                class="w-full text-[13px] text-[#55524A] bg-[#FBFAF7] border border-[#ECEAE3] rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-siarc-green">
        </div>
        <button class="inline-flex items-center gap-2 text-[13px] font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            Tous les rôles <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i>
        </button>
        <button class="inline-flex items-center gap-2 text-[13px] font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            Tous les pays <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i>
        </button>
        <button class="inline-flex items-center gap-2 text-[13px] font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            Tous les thèmes <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498]"></i>
        </button>
        <button class="inline-flex items-center gap-2 text-[13px] font-semibold text-siarc-green bg-white border border-[#ECEAE3] rounded-xl px-4 py-2.5 hover:border-siarc-green transition-colors">
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i> Plus de filtres
        </button>
    </div>
</section>

{{-- ══════════════════ MAIN GRID ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-9 grid lg:grid-cols-[1fr_320px] gap-6 items-start">

        {{-- ── LEFT: KPIs + speaker cards ─────────────────────────────── --}}
        <div>
            {{-- KPI ROW --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                @php
                    $kpis = [
                        ['users-round','128','Intervenants','Experts & leaders','#157A43','#E7F1EB'],
                        ['users','35','Pays représentés','Afrique, Europe, Asie…','#7C4FE0','#EFEAFB'],
                        ['mic','12','Thématiques','Secteurs couverts','#C97A16','#FBEFE0'],
                        ['presentation','64','Sessions','Conférences & panels','#157A43','#E7F1EB'],
                    ];
                @endphp
                @foreach($kpis as [$icon,$num,$label,$sub,$col,$bg])
                <div class="siarc-card siarc-shadow p-5">
                    <span class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3.5" style="background:{{ $bg }}">
                        <i data-lucide="{{ $icon }}" class="w-6 h-6" style="color:{{ $col }}"></i>
                    </span>
                    <p class="font-display text-[30px] font-extrabold text-[#1A1712] leading-none">{{ $num }}</p>
                    <p class="text-[13px] font-semibold text-[#1A1712] mt-2">{{ $label }}</p>
                    <p class="text-[11.5px] text-[#8A857A] mt-0.5">{{ $sub }}</p>
                </div>
                @endforeach
            </div>

            {{-- TOOLBAR --}}
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-display text-[22px] font-bold text-[#1A1712]">Tous les intervenants</h2>
                <div class="flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-2 text-[12.5px] text-[#8A857A]">
                        Trier par :
                        <button class="inline-flex items-center gap-1.5 font-medium text-[#55524A] bg-white border border-[#ECEAE3] rounded-lg px-3 py-1.5">
                            Nom (A-Z) <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498]"></i>
                        </button>
                    </div>
                    <div class="flex items-center gap-1">
                        <button class="w-8 h-8 rounded-lg bg-siarc-green text-white flex items-center justify-center"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
                        <button class="w-8 h-8 rounded-lg bg-white border border-[#ECEAE3] text-[#A8A498] flex items-center justify-center"><i data-lucide="clipboard-list" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </div>

            {{-- SPEAKER CARDS --}}
            @if($spk->isEmpty())
                <div class="siarc-card siarc-shadow p-12 text-center">
                    <span class="w-16 h-16 rounded-full siarc-adire flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="users-round" class="w-8 h-8 text-siarc-gold"></i>
                    </span>
                    <p class="font-display text-[18px] font-bold text-[#1A1712]">Programme des intervenants à venir</p>
                    <p class="text-[13px] text-[#8A857A] mt-2 max-w-[380px] mx-auto">La liste complète des experts et leaders du SIARC 2026 sera publiée prochainement.</p>
                </div>
            @else
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($spk as $s)
                    @php
                        [$role, $org] = array_pad(explode('·', (string)($s->role_fr ?? ''), 2), 2, '');
                        $role = trim($role); $org = trim($org);
                        $bg = $avatarBg[$loop->index % count($avatarBg)];
                    @endphp
                    <a href="{{ $h('siarc.speaker', ['id'=>$s->id]) }}" class="siarc-card siarc-shadow siarc-lift p-6 flex flex-col relative">
                        @if($s->is_featured)
                        <span class="absolute top-4 right-4 inline-flex items-center gap-1 text-[10px] font-bold text-siarc-gold bg-[#FDF6E3] border border-[#F3E4B0] rounded-full px-2.5 py-1">
                            <i data-lucide="star" class="w-3 h-3" style="fill:currentColor"></i> À la une
                        </span>
                        @endif

                        {{-- avatar --}}
                        @if($s->photo)
                            <img src="{{ asset($s->photo) }}" alt="{{ $s->name }}" class="w-20 h-20 rounded-full object-cover mx-auto siarc-shadow">
                        @else
                            <span class="w-20 h-20 rounded-full flex items-center justify-center mx-auto font-display text-[24px] font-extrabold text-white siarc-shadow" style="background:{{ $bg }}">
                                {{ $initials($s->name) }}
                            </span>
                        @endif

                        <h3 class="font-display text-[17px] font-bold text-[#1A1712] text-center mt-4 leading-snug">{{ $s->name }}</h3>
                        @if($role)
                            <p class="text-[12.5px] font-semibold text-siarc-ochre text-center mt-1.5 leading-snug">{{ $role }}</p>
                        @endif
                        @if($org)
                            <p class="text-[12px] text-[#8A857A] text-center mt-0.5">{{ $org }}</p>
                        @endif

                        <span class="inline-flex items-center justify-center gap-1.5 text-[12px] font-semibold text-siarc-green mt-4 pt-4 border-t border-[#EFEDE6] mx-auto">
                            Voir le profil <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </span>
                    </a>
                    @endforeach
                </div>

                {{-- LOAD MORE --}}
                <div class="flex justify-center mt-9">
                    <button class="siarc-btn px-6 py-3 text-[13px] font-semibold text-[#55524A] bg-white border border-[#ECEAE3] rounded-xl hover:border-siarc-green transition-colors">
                        Charger plus d'intervenants <i data-lucide="arrow-down" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- ── RIGHT: SIDEBAR ─────────────────────────────────────────── --}}
        <aside class="space-y-6">
            {{-- Catégories d'intervenants --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">Catégories d'intervenants</h3>
                <div class="space-y-3.5">
                    @foreach($spkCats as [$cname,$ccount])
                    <div class="flex items-center justify-between {{ !$loop->last ? 'pb-3.5 border-b border-[#F2F0EA]' : '' }}">
                        <span class="text-[13px] text-[#55524A]">{{ $cname }}</span>
                        <span class="text-[12px] font-bold text-[#55524A] bg-[#F5F3EE] rounded-md px-2.5 py-0.5">{{ $ccount }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Thématiques --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">Thématiques</h3>
                <div class="space-y-3.5">
                    @foreach($themes as [$icon,$tname,$tcount,$tcol])
                    <div class="flex items-center gap-3">
                        <i data-lucide="{{ $icon }}" class="w-4.5 h-4.5 shrink-0" style="color:{{ $tcol }}"></i>
                        <span class="text-[13px] text-[#55524A] flex-1 leading-snug">{{ $tname }}</span>
                        <span class="text-[12px] font-bold text-[#55524A] bg-[#F5F3EE] rounded-md px-2.5 py-0.5">{{ $tcount }}</span>
                    </div>
                    @endforeach
                </div>
                <a href="{{ $h('siarc.programme') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-5 pt-4 border-t border-[#F2F0EA] w-full justify-center hover:gap-2.5 transition-all">
                    Voir toutes les thématiques <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            {{-- Devenez intervenant CTA --}}
            <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-[#14652F] to-[#042B15] text-white p-6 relative">
                <div class="siarc-kente absolute top-0 left-0 right-0 opacity-80"></div>
                <span class="w-11 h-11 rounded-2xl bg-white/10 flex items-center justify-center mb-3.5 mt-3"><i data-lucide="mic" class="w-6 h-6 text-siarc-gold"></i></span>
                <h3 class="font-display text-[18px] font-bold mb-2">Devenez intervenant</h3>
                <p class="text-[12.5px] text-white/75 leading-relaxed mb-5">Partagez votre expertise et inspirez des milliers d'acteurs de l'artisanat.</p>
                <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-primary px-4 py-2.5 text-[12.5px]">
                    <i data-lucide="mic" class="w-4 h-4"></i> Proposer une intervention
                </a>
            </div>

            {{-- Intervenant à l'honneur --}}
            @if($featured)
            @php
                [$frole, $forg] = array_pad(explode('·', (string)($featured->role_fr ?? ''), 2), 2, '');
                $frole = trim($frole);
            @endphp
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">Intervenant à l'honneur</h3>
                <div class="flex items-start gap-3.5">
                    @if($featured->photo)
                        <img src="{{ asset($featured->photo) }}" alt="{{ $featured->name }}" class="w-14 h-14 rounded-full object-cover shrink-0">
                    @else
                        <span class="w-14 h-14 rounded-full flex items-center justify-center shrink-0 font-display text-[17px] font-extrabold text-white" style="background:{{ $avatarBg[0] }}">{{ $initials($featured->name) }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="font-display text-[16px] font-bold text-[#1A1712] leading-snug">{{ $featured->name }}</p>
                        @if($featured->organization)
                            <p class="text-[12px] text-[#8A857A] mt-0.5">{{ $featured->organization }}</p>
                        @endif
                        <span class="inline-block text-[10.5px] font-bold text-siarc-green bg-[#E7F1EB] rounded-md px-2 py-0.5 mt-2">Keynote Speaker</span>
                    </div>
                </div>
                @if($frole)
                    <p class="text-[12.5px] text-[#55524A] leading-relaxed mt-4">{{ $frole }}</p>
                @endif
                <a href="{{ $h('siarc.speaker', ['id'=>$featured->id]) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-4 pt-4 border-t border-[#F2F0EA] w-full justify-center hover:gap-2.5 transition-all">
                    Voir son profil <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            @endif
        </aside>
    </div>
</section>
