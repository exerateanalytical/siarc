@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Real ids so detail links never 404 ──────────────────────────────────────
    $eid       = siarcEvent()?->id ?? 0;
    $meetingId = DB::table('b2b_meetings')->where('event_id',$eid)->value('id');

    // Action-route fallbacks (guard optional platform routes) ────────────────────
    $rdvUrl     = R::has('messages.compose') ? route('messages.compose', ['lang'=>$lang]) : $h('siarc.admin.matchmaking');
    $meetingUrl = $meetingId ? $h('siarc.admin.meeting', ['id'=>$meetingId]) : $h('siarc.admin.matchmaking');

    // ── KPI row — approved design figures (verbatim) ────────────────────────────
    $kpis = [
        ['users-round','#157A43','#E2F3E8','Entreprises inscrites','1,248','arrow-up','18.6%','text-siarc-green'],
        ['handshake','#3565DE','#E8EFFB','Rendez-vous confirmés','156','arrow-up','24.3%','text-siarc-green'],
        ['calendar','#7C4FE0','#F0EAFB','Demandes en attente','28','arrow-down','4.2%','text-siarc-red'],
        ['eye','#E68A00','#FDEBCF','Vues de profil','1,893','arrow-up','31.7%','text-siarc-green'],
        ['target','#157A43','#E2F3E8','Taux de correspondance','87%',null,'Excellent',null],
    ];

    // ── Result cards — verbatim from the approved design ────────────────────────
    $cards = [
        [
            'logo'=>'b2b-logo-1.png','name'=>'SAFIRA ARTISANAT SARL','tag'=>'Achat','tagTone'=>'green',
            'loc'=>'Yaoundé, Cameroun','flag'=>'🇨🇲',
            'desc'=>'Spécialisée dans la transformation du bois précieux et la fabrication de meubles haut de gamme.',
            'chips'=>['Bois & Ameublement','Mobilier sur mesure','Export'],
            'interest'=>'Fournisseurs de bois certifié, designers',
            'partnership'=>'Fourniture • Distribution','score'=>'92%',
        ],
        [
            'logo'=>'b2b-logo-2.png','name'=>'ETHNO TEXTILES','tag'=>'Vente','tagTone'=>'blue',
            'loc'=>'Bamako, Mali','flag'=>'🇲🇱',
            'desc'=>'Textiles traditionnels et tissus africains faits main avec teintures naturelles.',
            'chips'=>['Textile','Tissus traditionnels','Mode'],
            'interest'=>'Acheteurs, distributeurs, boutiques',
            'partnership'=>'Distribution • Commercial','score'=>'88%',
        ],
        [
            'logo'=>'b2b-logo-3.png','name'=>'AGRO NATURE CAMEROUN','tag'=>'Achat','tagTone'=>'green',
            'loc'=>'Douala, Cameroun','flag'=>'🇨🇲',
            'desc'=>'Transformation et exportation de produits agricoles biologiques et épices.',
            'chips'=>['Agroalimentaire','Épices','Produits bio'],
            'interest'=>'Fournisseurs, partenaires logistiques',
            'partnership'=>'Fourniture • Logistique','score'=>'85%',
        ],
        [
            'logo'=>'b2b-logo-4.png','name'=>'KALABASH DESIGN','tag'=>'Vente','tagTone'=>'blue',
            'loc'=>'Abidjan, Côte d\'Ivoire','flag'=>'🇨🇮',
            'desc'=>'Objets déco et art de la table en calebasse et matériaux naturels.',
            'chips'=>['Artisanat d\'art','Décoration','Design'],
            'interest'=>'Distributeurs, concept stores',
            'partnership'=>'Distribution • Co-branding','score'=>'80%',
        ],
    ];

    // ── Recommendations (verbatim) ──────────────────────────────────────────────
    $recos = [
        ['name'=>'BAMBOU DU CAMEROUN','loc'=>'Douala, Cameroun','score'=>'95%','logo'=>'b2b-logo-1.png'],
        ['name'=>'GLOBAL CRAFTS LTD','loc'=>'Lagos, Nigeria','score'=>'90%','logo'=>'b2b-logo-3.png'],
        ['name'=>'TERRE & COULEURS','loc'=>'Marrakech, Maroc','score'=>'89%','logo'=>'b2b-logo-4.png'],
    ];

    // ── Upcoming meetings (verbatim) ────────────────────────────────────────────
    $upcoming = [
        ['day'=>'28','mon'=>'JUIL','time'=>'10:00 – 10:30','title'=>'RDV avec SAFIRA Artisanat SARL','sub'=>'Pavillon Centre - Stand C-09','status'=>'Confirmé','tone'=>'green'],
        ['day'=>'29','mon'=>'JUIL','time'=>'14:00 – 14:30','title'=>'RDV avec Ethno Textiles','sub'=>'Salle B2B 1','status'=>'En attente','tone'=>'blue'],
        ['day'=>'30','mon'=>'JUIL','time'=>'11:00 – 11:30','title'=>'RDV avec Agro Nature Cameroun','sub'=>'Salle B2B 2','status'=>'En attente','tone'=>'blue'],
    ];

    $tagStyle = fn($t) => $t==='green'
        ? 'background:#E2F3E8;color:#157A43'
        : 'background:#E8EFFB;color:#2B4F9E';
@endphp

{{-- ══ IN-BODY HEADING (subtitle sits under the topbar title) ══ --}}

{{-- ══ KPI ROW ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($kpis as [$icon,$color,$tile,$label,$val,$dir,$chg,$chgCls])
    <div class="siarc-card siarc-shadow p-4 siarc-in">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-full flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
            <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ $label }}</p>
        </div>
        <p class="mt-3 text-[26px] font-extrabold text-[#161513] leading-none tracking-tight">{{ $val }}</p>
        @if($dir === null)
            <span class="inline-flex items-center gap-1 mt-2.5 text-[11.5px] font-semibold text-siarc-green">{{ $chg }}</span>
        @else
            <span class="inline-flex items-center gap-1 mt-2.5 text-[11.5px] font-semibold {{ $chgCls }}">
                <i data-lucide="{{ $dir }}" class="w-3.5 h-3.5"></i>{{ $chg }}<span class="text-[#B0AB9F] font-normal">vs dernier salon</span>
            </span>
        @endif
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_326px] gap-5">
    {{-- ══ MAIN COLUMN ══ --}}
    <div class="min-w-0 space-y-5">

        {{-- ── Tabs + search panel ─────────────────────────────────────────────── --}}
        <div class="siarc-card siarc-shadow siarc-in overflow-hidden">
            {{-- Tabs --}}
            <div data-tabs="b2b" class="flex items-center gap-7 px-6 pt-4 border-b border-[#EFEDE6]">
                <button class="si-tab is-active relative text-[13.5px] font-bold pb-3" data-tab="search">Recherche de partenaires</button>
                <button class="si-tab text-[13.5px] font-semibold text-[#8A857A] pb-3 hover:text-[#3B382F]" data-tab="recommended">Entreprises recommandées</button>
                <button class="si-tab text-[13.5px] font-semibold text-[#8A857A] pb-3 hover:text-[#3B382F]" data-tab="requests">Requêtes reçues</button>
                <button class="si-tab text-[13.5px] font-semibold text-[#8A857A] pb-3 hover:text-[#3B382F]" data-tab="availability">Disponibilités</button>
            </div>

            {{-- Search row --}}
            <div class="px-6 pt-5 pb-4">
                <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr_1fr_auto] gap-4 items-end">
                    <div>
                        <label class="block text-[11.5px] font-semibold text-[#8A857A] mb-1.5">Que recherchez-vous ?</label>
                        <input type="text" data-filter="#b2bResults" placeholder="Produit, service, compétence..." class="w-full rounded-xl border border-[#EFEDE6] px-3.5 py-2.5 text-[12.5px] text-[#3B382F] placeholder-[#B0AB9F] focus:outline-none focus:border-siarc-green">
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-semibold text-[#8A857A] mb-1.5">Catégorie</label>
                        <div class="flex items-center justify-between rounded-xl border border-[#EFEDE6] px-3.5 py-2.5 text-[12.5px] text-[#3B382F] bg-white">Toutes les catégories<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></div>
                    </div>
                    <div>
                        <label class="block text-[11.5px] font-semibold text-[#8A857A] mb-1.5">Pays / Région</label>
                        <div class="flex items-center justify-between rounded-xl border border-[#EFEDE6] px-3.5 py-2.5 text-[12.5px] text-[#3B382F] bg-white">Tous les pays<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></div>
                    </div>
                    <button data-toast="Recherche en cours…" class="siarc-btn siarc-btn-green px-5 py-2.5 text-[12.5px] justify-center whitespace-nowrap"><i data-lucide="search" class="w-4 h-4"></i>Rechercher</button>
                </div>
                <div class="flex justify-end mt-3">
                    <button data-toast="Recherche avancée bientôt disponible" class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-siarc-green hover:underline"><i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>Recherche avancée</button>
                </div>
            </div>
        </div>

        {{-- ── TAB PANEL: Recherche de partenaires ─────────────────────────────── --}}
        <div data-panel="search" data-tabs-for="b2b" class="space-y-5">

        {{-- ── Result count + sort ─────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between px-1">
            <p class="text-[13px] text-[#3B382F]"><span class="font-bold text-[#161513]">1,248</span> résultats trouvés</p>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5 text-[12px] text-[#8A857A]">
                    Trier par :
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-[#EFEDE6] px-2.5 py-1.5 text-[12px] font-semibold text-[#3B382F] bg-white">Pertinence<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#B0AB9F]"></i></span>
                </div>
                <div class="flex items-center gap-1">
                    <button data-toast="Vue grille" class="w-8 h-8 rounded-lg flex items-center justify-center bg-[#E2F3E8] text-siarc-green"><i data-lucide="grid-3x3" class="w-4 h-4"></i></button>
                    <button data-toast="Vue mosaïque" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#B0AB9F] hover:bg-[#FBFAF6]"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
                    <button data-toast="Vue liste" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#B0AB9F] hover:bg-[#FBFAF6]"><i data-lucide="menu" class="w-4 h-4"></i></button>
                </div>
            </div>
        </div>

        {{-- ── Result cards ────────────────────────────────────────────────────── --}}
        <div id="b2bResults" class="siarc-card siarc-shadow siarc-in overflow-hidden divide-y divide-[#F1EFE9]">
            @foreach($cards as $c)
            <div data-filter-item data-filter-text="{{ $c['name'].' '.$c['loc'].' '.$c['desc'].' '.implode(' ',$c['chips']).' '.$c['interest'].' '.$c['partnership'] }}" class="p-5 hover:bg-[#FCFBF8] transition-colors">
                <div class="flex gap-5">
                    {{-- Logo --}}
                    <span class="w-14 h-14 rounded-full border border-[#ECEAE3] bg-white flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="{{ asset('images/siarc/'.$c['logo']) }}" alt="{{ $c['name'] }}" class="w-full h-full object-contain">
                    </span>

                    {{-- Identity + description --}}
                    <div class="w-[280px] shrink-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-[15px] font-extrabold text-[#161513]">{{ $c['name'] }}</h3>
                            <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full" style="{{ $tagStyle($c['tagTone']) }}">{{ $c['tag'] }}</span>
                        </div>
                        <p class="flex items-center gap-1.5 text-[12px] text-[#55524A] mt-1.5">{{ $c['loc'] }} <span class="text-[13px] leading-none">{{ $c['flag'] }}</span></p>
                        <p class="text-[12px] text-[#55524A] leading-snug mt-2">{{ $c['desc'] }}</p>
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach($c['chips'] as $chip)
                            <span class="text-[10.5px] font-medium px-2.5 py-0.5 rounded-full bg-[#F0F4FB] text-[#2B4F9E]">{{ $chip }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Interest / partnership --}}
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="text-[11px] font-bold text-[#161513]">Intérêt recherché</p>
                        <p class="text-[12px] text-[#55524A] mt-1">{{ $c['interest'] }}</p>
                        <p class="text-[11px] font-bold text-[#161513] mt-4">Type de partenariat</p>
                        <p class="text-[12px] text-[#55524A] mt-1">{{ $c['partnership'] }}</p>
                    </div>

                    {{-- Compatibility donut --}}
                    <div class="w-[92px] shrink-0 flex flex-col items-center pt-0.5">
                        <p class="text-[11px] text-[#8A857A] mb-2">Compatibilité</p>
                        @php $pct = (int) rtrim($c['score'],'%'); $circ = 2 * 3.14159 * 26; $dash = $circ * $pct / 100; @endphp
                        <div class="relative w-[64px] h-[64px]">
                            <svg viewBox="0 0 64 64" class="w-full h-full -rotate-90">
                                <circle cx="32" cy="32" r="26" fill="none" stroke="#EDEBE4" stroke-width="6"></circle>
                                <circle cx="32" cy="32" r="26" fill="none" stroke="#157A43" stroke-width="6" stroke-linecap="round" stroke-dasharray="{{ $dash }} {{ $circ }}"></circle>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-[13px] font-extrabold text-[#161513]">{{ $c['score'] }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="w-[150px] shrink-0 flex flex-col gap-2 pt-0.5">
                        <a href="{{ $rdvUrl }}" class="siarc-btn siarc-btn-green justify-center py-2.5 text-[12px]">Demander un RDV</a>
                        <a href="{{ $meetingUrl }}" class="siarc-btn justify-center py-2.5 text-[12px] border border-[#EFEDE6] text-[#3B382F] hover:bg-[#FBFAF6]">Voir le profil</a>
                        <button data-toast="Ajouté à vos favoris" class="self-end w-8 h-8 rounded-full flex items-center justify-center text-[#B0AB9F] hover:text-siarc-red hover:bg-[#FBE3E3]"><i data-lucide="heart" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Load more ───────────────────────────────────────────────────────── --}}
        <div class="flex justify-center">
            <button data-toast="Chargement des résultats suivants…" class="siarc-btn px-6 py-2.5 text-[12.5px] font-semibold border border-[#EFEDE6] text-[#3B382F] bg-white siarc-shadow hover:bg-[#FBFAF6]">Charger plus de résultats <i data-lucide="chevron-down" class="w-4 h-4"></i></button>
        </div>
        </div>{{-- /panel: search --}}

        {{-- ── TAB PANEL: Entreprises recommandées ─────────────────────────────── --}}
        <div data-panel="recommended" data-tabs-for="b2b" hidden>
            <div class="siarc-card siarc-shadow p-8 siarc-in text-center">
                <p class="text-[13px] text-[#55524A]">Consultez les entreprises que notre moteur de correspondance recommande spécialement pour votre profil.</p>
                <a href="{{ $h('siarc.admin.matchmaking') }}" class="siarc-btn siarc-btn-green px-6 py-2.5 text-[12.5px] justify-center mt-4 inline-flex">Ouvrir le matchmaking</a>
            </div>
        </div>

        {{-- ── TAB PANEL: Requêtes reçues ──────────────────────────────────────── --}}
        <div data-panel="requests" data-tabs-for="b2b" hidden>
            <div class="siarc-card siarc-shadow p-8 siarc-in text-center">
                <p class="text-[13px] text-[#55524A]">Gérez les demandes de rendez-vous que d'autres entreprises vous ont adressées.</p>
                <a href="{{ $h('siarc.admin.b2b') }}" class="siarc-btn siarc-btn-green px-6 py-2.5 text-[12.5px] justify-center mt-4 inline-flex">Voir les rendez-vous B2B</a>
            </div>
        </div>

        {{-- ── TAB PANEL: Disponibilités ───────────────────────────────────────── --}}
        <div data-panel="availability" data-tabs-for="b2b" hidden>
            <div class="siarc-card siarc-shadow p-8 siarc-in text-center">
                <p class="text-[13px] text-[#55524A]">Définissez vos créneaux disponibles pour les rendez-vous B2B durant le salon.</p>
                <a href="{{ $h('siarc.admin.calendar') }}" class="siarc-btn siarc-btn-green px-6 py-2.5 text-[12.5px] justify-center mt-4 inline-flex">Ouvrir le calendrier</a>
            </div>
        </div>
    </div>

    {{-- ══ SIDEBAR ══ --}}
    <div class="space-y-5">

        {{-- Filtres rapides --}}
        <div class="siarc-card siarc-shadow p-5 siarc-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-bold text-[#1A1712]">Filtres rapides</h3>
                <button data-toast="Filtres réinitialisés" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Réinitialiser</button>
            </div>
            <div class="space-y-3.5">
                @foreach(['Intérêt principal'=>'Tous','Type de partenariat'=>'Tous','Secteur d\'activité'=>'Tous','Pays / Région'=>'Tous'] as $flabel => $fval)
                <div>
                    <label class="block text-[11.5px] font-semibold text-[#55524A] mb-1.5">{{ $flabel }}</label>
                    <div class="flex items-center justify-between rounded-xl border border-[#EFEDE6] px-3.5 py-2.5 text-[12.5px] text-[#3B382F] bg-white">{{ $fval }}<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></div>
                </div>
                @endforeach

                <label class="flex items-center gap-2.5 text-[12.5px] text-[#3B382F] pt-0.5 cursor-pointer">
                    <span class="w-4 h-4 rounded bg-siarc-green flex items-center justify-center shrink-0"><i data-lucide="check" class="w-3 h-3 text-white"></i></span>
                    Disponibles pour des RDV
                </label>
                <label class="flex items-center gap-2.5 text-[12.5px] text-[#3B382F] cursor-pointer">
                    <span class="w-4 h-4 rounded border border-[#D8D5CC] bg-white shrink-0"></span>
                    Entreprises premium
                </label>

                <button data-toast="Filtres appliqués" class="w-full siarc-btn justify-center py-2.5 text-[12.5px] mt-1" style="background:#0B3A1E;color:#fff">Appliquer les filtres</button>
            </div>
        </div>

        {{-- Recommandés pour vous --}}
        <div class="siarc-card siarc-shadow p-5 siarc-in">
            <h3 class="flex items-center gap-1.5 text-[14px] font-bold text-[#1A1712] mb-4">Recommandés pour vous <i data-lucide="help-circle" class="w-3.5 h-3.5 text-[#B0AB9F]"></i></h3>
            <ul class="space-y-4">
                @foreach($recos as $r)
                <li class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-full border border-[#ECEAE3] bg-white flex items-center justify-center shrink-0 overflow-hidden">
                        <img src="{{ asset('images/siarc/'.$r['logo']) }}" alt="{{ $r['name'] }}" class="w-full h-full object-contain">
                    </span>
                    <div class="min-w-0">
                        <p class="text-[12px] font-bold text-[#1A1712] truncate">{{ $r['name'] }}</p>
                        <p class="text-[11px] text-[#8A857A] truncate">{{ $r['loc'] }}</p>
                    </div>
                    <span class="ml-auto inline-flex items-center gap-1 text-[11.5px] font-semibold text-siarc-green shrink-0"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i>{{ $r['score'] }}</span>
                    <a href="{{ $h('siarc.admin.matchmaking') }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline shrink-0">Voir profil</a>
                </li>
                @endforeach
            </ul>
            <a href="{{ $h('siarc.admin.matchmaking') }}" class="flex items-center justify-end gap-1.5 text-[12px] font-semibold text-siarc-green mt-4 hover:gap-2 transition-all">Voir toutes les recommandations <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>

        {{-- Prochains rendez-vous --}}
        <div class="siarc-card siarc-shadow p-5 siarc-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-bold text-[#1A1712]">Prochains rendez-vous</h3>
                <a href="{{ $h('siarc.admin.calendar') }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir tout</a>
            </div>
            <ul class="space-y-4">
                @foreach($upcoming as $m)
                <li class="flex gap-3">
                    <span class="w-11 shrink-0 rounded-xl bg-[#E2F3E8] flex flex-col items-center justify-center py-1.5">
                        <span class="text-[15px] font-extrabold text-siarc-green leading-none">{{ $m['day'] }}</span>
                        <span class="text-[9px] font-bold text-siarc-green mt-0.5 tracking-wide">{{ $m['mon'] }}</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] text-[#8A857A]">{{ $m['time'] }}</p>
                        <p class="text-[12px] font-bold text-[#1A1712] mt-0.5 truncate">{{ $m['title'] }}</p>
                        <p class="text-[11px] text-[#8A857A] mt-0.5 truncate">{{ $m['sub'] }}</p>
                    </div>
                    <span class="self-start text-[10.5px] font-semibold px-2 py-0.5 rounded-full shrink-0" style="{{ $tagStyle($m['tone']) }}">{{ $m['status'] }}</span>
                </li>
                @endforeach
            </ul>
            <a href="{{ $h('siarc.admin.calendar') }}" class="flex items-center justify-end gap-1.5 text-[12px] font-semibold text-siarc-green mt-4 hover:gap-2 transition-all">Voir mon agenda complet <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>
    </div>
</div>
