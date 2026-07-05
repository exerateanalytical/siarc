@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── real ids/slugs so detail links never 404 ──────────────────────────────
    $eid = siarcEvent()?->id ?? 0;
    $exhibitorSlug = DB::table('event_exhibitors as ee')
        ->join('businesses as b','b.id','=','ee.business_id')
        ->where('ee.event_id',$eid)->value('b.slug');
    $pavilionId = DB::table('pavilions')->where('event_id',$eid)->value('id');

    // profile URL: real exhibitor detail if we have a slug, else the list
    $profileUrl = ($exhibitorSlug && R::has('siarc.exhibitor'))
        ? route('siarc.exhibitor', ['lang'=>$lang, 'slug'=>$exhibitorSlug])
        : $h('siarc.exhibitors');
    // contact URL: messages compose if available, else register
    $contactUrl = R::has('messages.compose') ? route('messages.compose', ['lang'=>$lang]) : $h('siarc.register');

    // ── KPI stat band (approved design figures, verbatim) ─────────────────────
    $kpis = [
        ['users-round','#157A43','#E4F1E9','Total exposants','312','up',   '15,6% vs SIARC 2024'],
        ['calendar',   '#7C4FE0','#EFE9FB','Stands occupés','286','none',  "91,7% d'occupation"],
        ['globe',      '#C97A16','#FBEFDD','Pays représentés','24','none', 'Afrique, Europe, Asie…'],
        ['grid-3x3',   '#2456B3','#E7EEF9','Secteurs d\'activité','12','none','Catégories'],
        ['star',       '#E6B201','#FBF4DA','Exposants premium','38','none','En vedette'],
    ];

    // ── exhibitor rows (verbatim transcription of the PNG) ────────────────────
    $flags = [
        'cm'=>'🇨🇲','sn'=>'🇸🇳','za'=>'🇿🇦','ml'=>'🇲🇱','ma'=>'🇲🇦','ci'=>'🇨🇮','tn'=>'🇹🇳',
    ];
    $exhibitors = [
        [
            'logo'=>'Artisanat d’Excellence Cameroun','name'=>'Artisanat d’Excellence Cameroun','premium'=>true,
            'country'=>'Cameroun','flag'=>'cm','stand'=>'Stand A12',
            'desc'=>'Promotion et commercialisation des produits artisanaux haut de gamme du Cameroun.',
            'tags'=>['Bois sculpté','Textile','Décoration'],'more'=>'+3',
            'photos'=>['exh-r1-1.png','exh-r1-2.png','exh-r1-3.png','exh-r1-4.png'],
        ],
        [
            'logo'=>'Afrique Naturelle SARL','name'=>'Afrique Naturelle SARL','premium'=>true,
            'country'=>'Sénégal','flag'=>'sn','stand'=>'Stand B08',
            'desc'=>'Cosmétiques naturels et produits de bien-être à base d’ingrédients africains.',
            'tags'=>['Cosmétiques','Bien-être','Produits naturels'],'more'=>'+2',
            'photos'=>['exh-r2-1.png','exh-r2-2.png','exh-r2-3.png','exh-r2-4.png'],
        ],
        [
            'logo'=>'Design Afrika','name'=>'Design Afrika','premium'=>true,
            'country'=>'Afrique du Sud','flag'=>'za','stand'=>'Stand C15',
            'desc'=>'Mobilier design et objets déco inspirés du patrimoine africain contemporain.',
            'tags'=>['Mobilier','Design','Décoration'],'more'=>null,
            'photos'=>['exh-r3-1.png','exh-r3-2.png','exh-r3-3.png','exh-r3-4.png'],
        ],
        [
            'logo'=>'Tissages du Sahel','name'=>'Tissages du Sahel','premium'=>true,
            'country'=>'Mali','flag'=>'ml','stand'=>'Stand D21',
            'desc'=>'Tissus traditionnels et modernes tissés à la main par des artisans du Sahel.',
            'tags'=>['Textile','Tissage','Mode'],'more'=>'+1',
            'photos'=>['exh-r4-1.png','exh-r4-2.png','exh-r4-3.png','exh-r4-4.png'],
        ],
    ];

    // ── right rail: categories (verbatim names + counts) ──────────────────────
    $categories = [
        ['Arts décoratifs','64'], ['Textile & Mode','58'], ['Bois & Sculpture','45'],
        ['Bijouterie & Accessoires','32'], ['Céramique & Poterie','28'], ['Cosmétiques naturels','24'],
        ['Agroalimentaire','23'], ['Innovation & Technologies','18'], ['Éco-conception','12'], ['Autres','8'],
    ];

    // ── right rail: featured exhibitors (verbatim) ────────────────────────────
    $featured = [
        ['Terre & Tradition','Maroc','ma','Stand E05','#0B3A1E'],
        ['Ebène d’Afrique','Côte d’Ivoire','ci','Stand A03','#C97A16'],
        ['L’Atelier du Cuir','Tunisie','tn','Stand B14','#8A5A2B'],
    ];

    // pagination pages
    $pages = ['1','2','3','4','5'];
@endphp

<div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-8 siarc-in">

    {{-- ══════════════════ TITLE BAND ══════════════════ --}}
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6 mb-7">
        <div>
            <h1 class="font-display font-extrabold text-[28px] leading-tight text-[#1A1712]">Exhibitors Directory</h1>
            <p class="mt-1.5 text-[14px] text-[#8A857A]">Découvrez tous les exposants et leurs offres au SIARC 2026</p>
        </div>
        <div class="flex items-center gap-6 shrink-0">
            <div class="flex items-center gap-2.5">
                <i data-lucide="calendar-days" class="w-5 h-5 text-siarc-green"></i>
                <div class="leading-tight">
                    <p class="text-[13px] font-semibold text-[#1A1712]">27 Juillet – 05 Août 2026</p>
                    <p class="text-[12px] text-[#8A857A]">Musée National de Yaoundé</p>
                </div>
            </div>
            <button type="button" data-toast="Export en préparation…" class="siarc-btn siarc-btn-green px-5 py-2.5 text-[13px] shrink-0">
                <i data-lucide="download" class="w-4 h-4"></i>Exporter la liste
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-6 items-start">

        {{-- ══════════════════ LEFT COLUMN ══════════════════ --}}
        <div>

            {{-- KPI STAT BAND --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-5">
                @foreach($kpis as [$icon,$color,$tile,$label,$val,$trend,$sub])
                <div class="siarc-card siarc-shadow p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0" style="background:{{ $tile }}">
                            <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
                        </span>
                        <span class="text-[12.5px] font-semibold text-[#55524A] leading-tight">{{ $label }}</span>
                    </div>
                    <p class="font-display text-[28px] font-extrabold text-[#1A1712] leading-none">{{ $val }}</p>
                    <p class="mt-2 text-[11.5px] {{ $trend==='up' ? 'text-siarc-green font-semibold inline-flex items-center gap-1' : 'text-[#8A857A]' }}">
                        @if($trend==='up')<i data-lucide="arrow-up" class="w-3 h-3"></i>@endif{{ $sub }}
                    </p>
                </div>
                @endforeach
            </div>

            {{-- SEARCH + FILTER ROW --}}
            <div class="siarc-card siarc-shadow p-4 mb-5">
                <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
                    <label class="relative flex-1">
                        <i data-lucide="search" class="w-5 h-5 text-[#A8A498] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                        <input type="text" data-filter="#exhibitorList" placeholder="Rechercher un exposant, produit, service…"
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-[#E7E4DB] bg-[#FBFAF7] text-[13px] text-[#55524A] placeholder-[#A8A498] focus:outline-none focus:border-siarc-green">
                    </label>
                    @php
                        $selects = [
                            ["Secteur d'activité",'Tous les secteurs'],
                            ['Pays','Tous les pays'],
                            ['Type de stand','Tous les types'],
                        ];
                    @endphp
                    @foreach($selects as [$lbl,$ph])
                    <div class="min-w-[150px]">
                        <span class="block text-[11.5px] font-medium text-[#8A857A] mb-1.5">{{ $lbl }}</span>
                        <div class="relative">
                            <select data-filter-select="#exhibitorList" class="w-full appearance-none pl-3.5 pr-9 py-2.5 rounded-xl border border-[#E7E4DB] bg-white text-[13px] text-[#55524A] focus:outline-none">
                                <option>{{ $ph }}</option>
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>
                    @endforeach
                    <button type="button" data-toast="Filtres avancés bientôt disponibles" class="siarc-btn px-4 py-2.5 text-[13px] border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green shrink-0">
                        <i data-lucide="filter" class="w-4 h-4"></i>Plus de filtres
                    </button>
                </div>
            </div>

            {{-- RESULT COUNT + SORT + VIEW TOGGLE --}}
            <div class="flex items-center justify-between mb-4">
                <p class="text-[14px] font-semibold text-[#1A1712]">312 exposants trouvés</p>
                <div class="flex items-center gap-2.5">
                    <span class="text-[12.5px] text-[#8A857A]">Trier par</span>
                    <div class="relative">
                        <select data-sort="#exhibitorList" class="appearance-none pl-3 pr-8 py-2 rounded-lg border border-[#E7E4DB] bg-white text-[12.5px] font-semibold text-[#55524A] focus:outline-none">
                            <option value="az">Nom (A-Z)</option>
                            <option value="za">Nom (Z-A)</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                    <button type="button" data-toast="Vue grille active" class="w-9 h-9 rounded-lg bg-siarc-green text-white flex items-center justify-center"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
                    <button type="button" data-toast="Vue liste bientôt disponible" class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[#8A857A] flex items-center justify-center"><i data-lucide="clipboard-list" class="w-4 h-4"></i></button>
                </div>
            </div>

            {{-- ══════════════════ EXHIBITOR ROWS ══════════════════ --}}
            <div id="exhibitorList" class="space-y-4">
                @foreach($exhibitors as $ex)
                <article data-filter-item
                    data-filter-text="{{ $ex['name'] }} {{ $ex['country'] }} {{ $ex['stand'] }} {{ implode(' ', $ex['tags']) }} {{ $ex['desc'] }}"
                    data-filter-tags="{{ strtolower($ex['country'].' '.implode(' ', $ex['tags'])) }}"
                    data-sort-key="{{ $ex['name'] }}"
                    class="siarc-card siarc-shadow p-5">
                    <div class="flex flex-col lg:flex-row gap-5">

                        {{-- logo tile --}}
                        <div class="shrink-0">
                            <div class="w-[76px] h-[76px] rounded-2xl border border-[#EDEAE1] bg-[#FBFAF7] flex items-center justify-center text-center p-2">
                                <span class="font-display text-[8px] font-bold text-[#55524A] leading-tight tracking-wide uppercase">{{ $ex['logo'] }}</span>
                            </div>
                        </div>

                        {{-- identity + description + tags --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-display text-[17px] font-bold text-[#1A1712]">{{ $ex['name'] }}</h3>
                                @if($ex['premium'])
                                <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-md bg-[#E4F1E9] text-siarc-green">Premium</span>
                                @endif
                            </div>
                            <p class="mt-1.5 flex items-center gap-1.5 text-[12.5px] text-[#55524A]">
                                <span>{{ $ex['country'] }}</span>
                                <span class="text-[13px]">{{ $flags[$ex['flag']] ?? '' }}</span>
                                <span class="text-[#C9C4B7]">·</span>
                                <span class="font-semibold text-[#1A1712]">{{ $ex['stand'] }}</span>
                            </p>
                            <p class="mt-2 text-[13px] text-[#55524A] leading-relaxed max-w-[520px]">{{ $ex['desc'] }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                @foreach($ex['tags'] as $tag)
                                <span class="text-[11px] font-medium px-2.5 py-1 rounded-md bg-[#F3F0E7] text-[#55524A]">{{ $tag }}</span>
                                @endforeach
                                @if($ex['more'])
                                <span class="text-[11px] font-medium text-[#8A857A]">{{ $ex['more'] }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- product photos --}}
                        <div class="shrink-0 grid grid-cols-4 gap-2 self-center">
                            @foreach($ex['photos'] as $photo)
                            <span class="block w-[60px] h-[68px] rounded-xl overflow-hidden border border-[#EDEAE1] bg-[#F3F0E7]">
                                <img src="{{ asset('images/siarc/'.$photo) }}" alt="" class="w-full h-full object-cover">
                            </span>
                            @endforeach
                        </div>

                        {{-- action buttons --}}
                        <div class="shrink-0 flex flex-col gap-2 self-center w-full lg:w-[168px]">
                            <a href="{{ $profileUrl }}" class="siarc-btn siarc-btn-green justify-center w-full py-2.5 text-[12.5px]">
                                <i data-lucide="eye" class="w-4 h-4"></i>Voir le profil
                            </a>
                            <a href="{{ $contactUrl }}" class="siarc-btn justify-center w-full py-2.5 text-[12.5px] border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green">
                                <i data-lucide="mail" class="w-4 h-4"></i>Contacter
                            </a>
                            <button type="button" data-toast="Ajouté à vos favoris" class="siarc-btn justify-center w-full py-2.5 text-[12.5px] border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green">
                                <i data-lucide="star" class="w-4 h-4"></i>Ajouter aux favoris
                            </button>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            {{-- ══════════════════ PAGINATION ══════════════════ --}}
            <div data-page="#exhibitorList" data-page-size="10" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                <p data-page-info class="text-[13px] text-[#8A857A]">Affichage de 1 à 12 sur 312 exposants</p>
                <div class="flex items-center gap-1.5">
                    <button type="button" data-page-prev class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[#8A857A] flex items-center justify-center hover:border-siarc-green">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    @foreach($pages as $i => $p)
                    <button type="button" data-page-num="{{ $p }}" class="w-9 h-9 rounded-lg text-[13px] font-semibold flex items-center justify-center {{ $i===0 ? 'bg-siarc-green text-white' : 'border border-[#E7E4DB] bg-white text-[#55524A] hover:border-siarc-green' }}">{{ $p }}</button>
                    @endforeach
                    <span class="w-9 h-9 flex items-center justify-center text-[13px] text-[#8A857A]">…</span>
                    <button type="button" data-page-num="26" class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[13px] font-semibold text-[#55524A] flex items-center justify-center hover:border-siarc-green">26</button>
                    <button type="button" data-page-next class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[#8A857A] flex items-center justify-center hover:border-siarc-green">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                    <div class="relative ml-2">
                        <select data-toast="Taille de page mise à jour" class="appearance-none pl-3 pr-8 py-2 rounded-lg border border-[#E7E4DB] bg-white text-[12.5px] font-semibold text-[#55524A] focus:outline-none">
                            <option>12 / page</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════ RIGHT RAIL ══════════════════ --}}
        <aside class="space-y-5">

            {{-- Catégories --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[16px] font-bold text-[#1A1712] mb-3">Catégories</h3>
                <ul class="space-y-0.5">
                    @foreach($categories as [$cat,$c])
                    <li>
                        <a href="{{ $h('siarc.exhibitors') }}" class="flex items-center justify-between gap-3 py-2 group">
                            <span class="text-[13px] text-[#3B382F] group-hover:text-siarc-green transition-colors">{{ $cat }}</span>
                            <span class="text-[11.5px] font-bold px-2 py-0.5 rounded-md bg-[#F3F0E7] text-[#8A857A] shrink-0">{{ $c }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.exhibitors') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-3 hover:gap-2.5 transition-all">
                    Voir toutes les catégories <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- Exposants en vedette --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-[16px] font-bold text-[#1A1712]">Exposants en vedette</h3>
                    <a href="{{ $h('siarc.exhibitors') }}" class="text-[12px] font-semibold text-siarc-green hover:underline">Voir tout</a>
                </div>
                <ul class="space-y-4">
                    @foreach($featured as [$fname,$fcountry,$fflag,$fstand,$fcol])
                    <li>
                        <a href="{{ $profileUrl }}" class="flex items-center gap-3 group">
                            @php $fw = preg_split('/\s+/', trim($fname)); $fi = strtoupper(mb_substr($fw[0]??'S',0,1) . mb_substr($fw[count($fw)-1] ?? '',0,1)); @endphp
                            <span class="w-9 h-9 rounded-lg shrink-0 flex items-center justify-center text-white font-display font-bold text-[12px]" style="background:{{ $fcol }}">{{ $fi }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[13px] font-bold text-[#1A1712] truncate group-hover:text-siarc-green transition-colors">{{ $fname }}</p>
                                <p class="text-[11.5px] text-[#8A857A] truncate">{{ $fcountry }} {{ $flags[$fflag] ?? '' }} · {{ $fstand }}</p>
                            </div>
                            <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-md bg-[#E4F1E9] text-siarc-green shrink-0">Premium</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Plan du salon --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[16px] font-bold text-[#1A1712] mb-3">Plan du salon</h3>
                <a href="{{ $h('siarc.pavilions') }}" class="block rounded-xl border border-[#E7E1D4] overflow-hidden">
                    <img src="{{ asset('images/siarc/exh-floorplan.png') }}" alt="Plan du salon SIARC 2026" class="w-full block">
                </a>
                <a href="{{ $h('siarc.pavilions') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-3 hover:gap-2.5 transition-all">
                    Voir le plan interactif <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

        </aside>
    </div>
</div>
