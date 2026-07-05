@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;
    $exUrl = $h('siarc.exhibitors');
    $eid = siarcEvent()?->id ?? 0;
    $pavilionId = \Illuminate\Support\Facades\DB::table('pavilions')->where('event_id',$eid)->value('id');
    // Public pavilion cards drill into the exhibitors list filtered by pavilion (fallback to plain list).
    $pavHref = fn($pid = null) => $exUrl ? ($pid ? $exUrl.'?pavilion='.$pid : $exUrl) : ($h('siarc.pavilions') ?? $h('siarc.home'));
    $planUrl = $h('siarc.pavilions') ?? $h('siarc.home');

    // ── Design content (transcribed verbatim from the approved PNG) ────────────
    // Featured pavilions ("Pavillons à la une")
    $featured = [
        ['img'=>'pav-cameroun.png',   'flag'=>'🇨🇲', 'badge'=>'Pays',       'name'=>'Pavillon Cameroun',
         'desc'=>"Découvrez le meilleur de l'artisanat camerounais dans un espace dédié à la diversité culturelle nationale.",
         'stands'=>'48','exhib'=>'126'],
        ['img'=>'pav-maroc.png',      'flag'=>'🇲🇦', 'badge'=>'Pays',       'name'=>'Pavillon Maroc',
         'desc'=>"L'excellence marocaine à travers l'artisanat traditionnel, les tapis, la céramique et bien plus.",
         'stands'=>'32','exhib'=>'84'],
        ['img'=>'pav-senegal.png',    'flag'=>'🇸🇳', 'badge'=>'Pays',       'name'=>'Pavillon Sénégal',
         'desc'=>"L'art sénégalais à l'honneur : textiles, bijoux, cuir, sculpture et design contemporain.",
         'stands'=>'28','exhib'=>'71'],
        ['img'=>'pav-innovation.png', 'flag'=>'🇸🇳', 'badge'=>'Thématique', 'name'=>'Pavillon Innovation & Design',
         'desc'=>"Un espace dédié aux artisans innovants et aux créations design tournées vers l'avenir.",
         'stands'=>'20','exhib'=>'53'],
    ];

    // All pavilions ("Tous les pavillons")
    $allPav = [
        ['img'=>'pav-cotedivoire.png',     'flag'=>'🇨🇮', 'badge'=>'Pays',       'name'=>"Pavillon Côte d'Ivoire",   'stands'=>'24','exhib'=>'62'],
        ['img'=>'pav-tunisie.png',         'flag'=>'🇹🇳', 'badge'=>'Pays',       'name'=>'Pavillon Tunisie',         'stands'=>'18','exhib'=>'45'],
        ['img'=>'pav-artisanat-monde.png', 'ic'=>'globe', 'badge'=>'Thématique', 'name'=>'Pavillon Artisanat du Monde','stands'=>'30','exhib'=>'88'],
        ['img'=>'pav-jeunes.png',          'ic'=>'users-round','badge'=>'Thématique','name'=>'Pavillon Jeunes Artisans','stands'=>'16','exhib'=>'48'],
        ['img'=>'pav-afrique-centrale.png','flag'=>'🌍', 'badge'=>'Régional',   'name'=>'Pavillon Afrique Centrale', 'stands'=>'22','exhib'=>'55'],
        ['img'=>'pav-diaspora.png',        'ic'=>'globe', 'badge'=>'Régional',   'name'=>'Pavillon Diaspora Africaine','stands'=>'14','exhib'=>'33'],
    ];

    // Sidebar: pavilion map pin colours (2 rows × 3)
    $mapPins = ['#2E8B57','#7C4FE0','#3565DE','#157A43','#E6B201','#C97A16'];

    // Sidebar: Explorer par région
    $regions = [
        ['Afrique Centrale','56'],['Afrique de l\'Ouest','78'],['Afrique du Nord','34'],
        ['Afrique de l\'Est','42'],['Afrique Australe','28'],['International','48'],
    ];

    // Sidebar: Aperçu global
    $globalStats = [
        ['globe','#157A43','#E2F3E8','24','Pays représentés'],
        ['layout-grid','#7C4FE0','#F0EAFB','12','Pavillons thématiques'],
        ['store','#C97A16','#FDF3E0','286','Stands occupés'],
        ['users-round','#3565DE','#E8EFFB','580+','Exposants inscrits'],
    ];

    $badgeTone = fn($b) => match($b){
        'Pays' => ['bg'=>'#157A43'], 'Thématique' => ['bg'=>'#7C4FE0'],
        'Régional' => ['bg'=>'#C97A16'], default => ['bg'=>'#157A43'],
    };
@endphp

<div class="bg-[#FBFAF7]">

    {{-- ══════════════════ FILTER TOOLBAR ══════════════════ --}}
    <section class="bg-white border-b border-[#EEECE5]">
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col lg:flex-row lg:items-end gap-3">
                {{-- search --}}
                <div class="relative flex-1 min-w-[220px] self-stretch lg:self-end">
                    <i data-lucide="search" class="w-4 h-4 text-[#A8A498] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" aria-label="Rechercher un pavillon" data-filter="#pavAllGrid"
                        placeholder="Rechercher un pavillon, pays ou thème…"
                        class="w-full pl-10 pr-4 py-2.5 text-[13px] rounded-lg border border-[#E4E0D6] bg-white focus:outline-none focus:ring-2 focus:ring-siarc-green/20 focus:border-siarc-green">
                </div>
                {{-- select dropdowns --}}
                @php $selMeta = [
                    ['label'=>'Type de pavillon','first'=>'Tous les types','info'=>true,'filter'=>true],
                    ['label'=>'Région / Pays','first'=>'Toutes les régions','info'=>true,'filter'=>false],
                    ['label'=>'Thématique','first'=>'Toutes les thématiques','info'=>false,'filter'=>false],
                ]; @endphp
                @foreach($selMeta as $sel)
                <div>
                    <div class="flex items-center gap-1 mb-1.5 text-[12px] font-medium text-[#6C685E]">
                        {{ $sel['label'] }}
                        @if($sel['info'])<i data-lucide="info" class="w-3 h-3 text-[#B4B0A6]"></i>@endif
                    </div>
                    <div class="relative">
                        <select aria-label="{{ $sel['label'] }}" @if($sel['filter'])data-filter-select="#pavAllGrid"@endif class="appearance-none text-[13px] font-medium text-[#2A271F] pl-3.5 pr-9 py-2.5 rounded-lg border border-[#E4E0D6] bg-white focus:outline-none focus:ring-2 focus:ring-siarc-green/20 cursor-pointer min-w-[170px]">
                            <option value="">{{ $sel['first'] }}</option>
                            @if($sel['filter'])
                                <option value="pays">Pays</option>
                                <option value="thématique">Thématique</option>
                                <option value="régional">Régional</option>
                            @endif
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
                @endforeach
                <button type="button" data-toast="Filtres avancés bientôt disponibles" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#2A271F] px-4 py-2.5 rounded-lg border border-[#E4E0D6] bg-white hover:bg-[#F5F3EE] transition-colors shrink-0 self-end">
                    <i data-lucide="filter" class="w-4 h-4 text-[#55524A]"></i>Plus de filtres
                </button>
            </div>
        </div>
    </section>

    {{-- ══════════════════ CONTENT: MAIN + SIDEBAR ══════════════════ --}}
    <section>
        <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-1 xl:grid-cols-[1fr_312px] gap-6 items-start">

            {{-- ─────────────── MAIN COLUMN ─────────────── --}}
            <div class="min-w-0">

                {{-- ── HERO BANNER ── --}}
                <div class="relative rounded-2xl overflow-hidden siarc-shadow-lg text-white mb-8">
                    <img src="{{ asset('images/siarc/pav-hero.png') }}" alt="Pavillons du SIARC 2026" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0" style="background:linear-gradient(100deg,#052C15 0%,rgba(6,40,20,.92) 34%,rgba(11,58,30,.55) 58%,rgba(15,72,36,.05) 100%)"></div>
                    <div class="relative px-8 sm:px-10 py-9 sm:py-10 max-w-[430px]">
                        <h2 class="font-display text-[28px] sm:text-[34px] font-extrabold leading-[1.1]">
                            Explorer les pavillons du SIARC 2026
                        </h2>
                        <p class="mt-4 text-[14px] text-white/85 leading-relaxed max-w-[360px]">
                            Un voyage à travers la créativité, le savoir-faire et la diversité culturelle d'Afrique et du monde.
                        </p>
                        <div class="flex flex-wrap items-center gap-x-9 gap-y-4 mt-8">
                            @foreach([['store','24','Pays représentés'],['clipboard-list','12','Pavillons thématiques'],['grid-3x3','286','Stands']] as [$mi,$mv,$ml])
                            <div class="flex items-center gap-2.5">
                                <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="{{ $mi }}" class="text-white/90" style="width:18px;height:18px"></i></span>
                                <div>
                                    <p class="font-display text-[20px] font-extrabold leading-none">{{ $mv }}</p>
                                    <p class="text-[10.5px] font-medium text-white/65 mt-1">{{ $ml }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ── PAVILLONS À LA UNE ── --}}
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="text-[20px] font-bold text-[#1A1712]">Pavillons à la une</h3>
                    @if($exUrl)
                    <a href="{{ $exUrl }}" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-siarc-green hover:gap-2.5 transition-all">
                        Voir tous les pavillons <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>

                <div class="relative mb-10">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($featured as $p)
                            @php $bt = $badgeTone($p['badge']); $href = $pavHref($pavilionId); @endphp
                            <a href="{{ $href }}" class="siarc-card siarc-shadow siarc-lift overflow-hidden flex flex-col group">
                                <div class="relative h-[104px] overflow-hidden">
                                    <img src="{{ asset('images/siarc/'.$p['img']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover">
                                    <span class="absolute top-2.5 left-2.5 inline-flex items-center text-[10.5px] font-bold px-2.5 py-0.5 rounded-md text-white" style="background:{{ $bt['bg'] }}">{{ $p['badge'] }}</span>
                                </div>
                                <div class="px-4 pt-3.5 pb-4 flex flex-col flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-[15px] leading-none">{{ $p['flag'] }}</span>
                                        <h4 class="text-[15px] font-bold text-[#1A1712] leading-snug">{{ $p['name'] }}</h4>
                                    </div>
                                    <p class="text-[12px] text-[#7A756A] leading-relaxed mb-4">{{ $p['desc'] }}</p>
                                    <div class="flex items-center justify-between mt-auto pt-3 border-t border-[#F0EEE7] text-[11.5px]">
                                        <span class="text-[#8A857A]">Stands <span class="font-bold text-[#2A271F]">{{ $p['stands'] }}</span></span>
                                        <span class="text-[#8A857A]">Exposants <span class="font-bold text-[#2A271F]">{{ $p['exhib'] }}</span></span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    {{-- carousel next control --}}
                    <button type="button" aria-label="Pavillon suivant" data-toast="Fin du carrousel" class="hidden lg:flex absolute -right-3 top-[72px] w-9 h-9 rounded-full bg-white siarc-shadow items-center justify-center text-[#2A271F] hover:bg-[#F5F3EE] transition-colors z-10">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- ── TOUS LES PAVILLONS ── --}}
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="text-[20px] font-bold text-[#1A1712]">Tous les pavillons</h3>
                    <div class="flex items-center gap-3">
                        <label class="text-[12px] text-[#8A857A]">Trier par</label>
                        <div class="relative">
                            <select aria-label="Trier par" data-sort="#pavAllGrid" class="appearance-none text-[12.5px] font-medium text-[#2A271F] pl-3 pr-8 py-1.5 rounded-lg border border-[#E4E0D6] bg-white focus:outline-none focus:ring-2 focus:ring-siarc-green/20 cursor-pointer">
                                <option value="az">A - Z</option>
                                <option value="za">Z - A</option>
                            </select>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" aria-label="Vue grille" data-toast="Vue grille active" class="w-8 h-8 rounded-lg flex items-center justify-center text-white bg-siarc-green"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
                            <button type="button" aria-label="Vue liste" data-toast="Vue liste bientôt disponible" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] border border-[#E4E0D6] bg-white hover:bg-[#F5F3EE]"><i data-lucide="clipboard-list" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                </div>

                <div id="pavAllGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($allPav as $p)
                        @php $bt = $badgeTone($p['badge']); $href = $pavHref($pavilionId); @endphp
                        <a href="{{ $href }}" data-filter-item data-filter-text="{{ $p['name'].' '.$p['badge'] }}" data-filter-tags="{{ \Illuminate\Support\Str::lower($p['badge']) }}" data-sort-key="{{ $p['name'] }}" class="siarc-card siarc-shadow siarc-lift overflow-hidden flex flex-col group">
                            <div class="relative h-[92px] overflow-hidden">
                                <img src="{{ asset('images/siarc/'.$p['img']) }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover">
                                <span class="absolute top-2.5 left-2.5 inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-md text-white" style="background:{{ $bt['bg'] }}">{{ $p['badge'] }}</span>
                            </div>
                            <div class="px-4 pt-3 pb-4 flex flex-col flex-1">
                                <div class="flex items-center gap-2 mb-3">
                                    @if(!empty($p['flag']))
                                        <span class="text-[14px] leading-none">{{ $p['flag'] }}</span>
                                    @else
                                        <i data-lucide="{{ $p['ic'] }}" class="w-4 h-4 text-siarc-green shrink-0"></i>
                                    @endif
                                    <h4 class="text-[14px] font-bold text-[#1A1712] leading-snug">{{ $p['name'] }}</h4>
                                </div>
                                <div class="flex items-center justify-between mt-auto pt-3 border-t border-[#F0EEE7] text-[11px]">
                                    <span class="text-[#8A857A]">Stands <span class="font-bold text-[#2A271F]">{{ $p['stands'] }}</span></span>
                                    <span class="text-[#8A857A]">Exposants <span class="font-bold text-[#2A271F]">{{ $p['exhib'] }}</span></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="flex justify-center mt-8">
                    @if($exUrl)
                    <a href="{{ $exUrl }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#2A271F] px-6 py-2.5 rounded-lg border border-[#E4E0D6] bg-white hover:bg-[#F5F3EE] transition-colors">
                        Charger plus de pavillons <i data-lucide="arrow-down" class="w-4 h-4 text-[#55524A]"></i>
                    </a>
                    @else
                    <button type="button" data-toast="Tous les pavillons sont affichés" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#2A271F] px-6 py-2.5 rounded-lg border border-[#E4E0D6] bg-white hover:bg-[#F5F3EE] transition-colors">
                        Charger plus de pavillons <i data-lucide="arrow-down" class="w-4 h-4 text-[#55524A]"></i>
                    </button>
                    @endif
                </div>
            </div>

            {{-- ─────────────── SIDEBAR ─────────────── --}}
            <aside class="space-y-5 xl:sticky xl:top-6">

                {{-- Carte des pavillons --}}
                <div class="siarc-card siarc-shadow p-5">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Carte des pavillons</h3>
                    <div class="relative rounded-xl overflow-hidden border border-[#EEECE5] bg-[#F7F5F0] p-5"
                        style="background-image:linear-gradient(#EAE7DE 1px,transparent 1px),linear-gradient(90deg,#EAE7DE 1px,transparent 1px);background-size:22px 22px;">
                        <div class="grid grid-cols-3 gap-3">
                            @foreach($mapPins as $mp)
                            <a href="{{ $pavHref($pavilionId) }}" aria-label="Voir le pavillon" class="relative aspect-square rounded-lg flex items-center justify-center transition-transform hover:scale-105"
                                style="background:{{ $mp }}22;border:1px solid {{ $mp }}55;">
                                <i data-lucide="map-pin" class="w-5 h-5" style="color:{{ $mp }};fill:{{ $mp }}33"></i>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-3">
                        <p class="text-[11.5px] text-[#8A857A]">Cliquez sur un pavillon pour voir les détails</p>
                        <i data-lucide="info" class="w-4 h-4 text-[#B4B0A6] shrink-0"></i>
                    </div>
                </div>

                {{-- Explorer par région --}}
                <div class="siarc-card siarc-shadow p-5">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-1">Explorer par région</h3>
                    <ul>
                        @foreach($regions as [$rname,$rc])
                        @php $rhref = $pavHref($pavilionId); @endphp
                        <li>
                            <a href="{{ $rhref }}" class="flex items-center justify-between py-2.5 group">
                                <span class="text-[13px] font-medium text-[#2A271F] group-hover:text-siarc-green transition-colors">{{ $rname }}</span>
                                <span class="inline-flex items-center justify-center min-w-[34px] px-2 py-0.5 rounded-full bg-[#F1EFE9] text-[12px] font-bold text-[#55524A]">{{ $rc }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @if($exUrl)
                    <a href="{{ $exUrl }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-2 hover:gap-2.5 transition-all">
                        Voir toutes les régions <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                    @endif
                </div>

                {{-- Aperçu global --}}
                <div class="siarc-card siarc-shadow p-5">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Aperçu global</h3>
                    <div class="space-y-4">
                        @foreach($globalStats as [$gicon,$gcolor,$gtile,$gval,$glabel])
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full flex items-center justify-center shrink-0" style="background:{{ $gtile }}"><i data-lucide="{{ $gicon }}" class="w-5 h-5" style="color:{{ $gcolor }}"></i></span>
                            <div>
                                <p class="font-display text-[19px] font-extrabold text-[#161513] leading-none">{{ $gval }}</p>
                                <p class="text-[11.5px] font-medium text-[#8A857A] mt-1">{{ $glabel }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Besoin d'informations --}}
                <div class="siarc-card siarc-shadow p-5">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Besoin d'informations ?</h3>
                    <p class="text-[12.5px] text-[#7A756A] leading-relaxed mb-4">
                        Notre équipe est à votre disposition pour vous accompagner.
                    </p>
                    @if($h('siarc.register'))
                    <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-green w-full justify-center px-4 py-2.5 text-[12.5px] rounded-lg">
                        <i data-lucide="phone" class="w-4 h-4"></i>Contacter l'équipe SIARC
                    </a>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</div>
