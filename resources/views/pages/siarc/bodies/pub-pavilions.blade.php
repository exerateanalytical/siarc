@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;

    // ── Real data (read-only) ─────────────────────────────────────────────────
    $eid  = function_exists('siarcEvent') ? (siarcEvent()?->id ?? 0) : 0;
    $pavs = collect();
    try {
        if (Schema::hasTable('pavilions')) {
            $pavs = DB::table('pavilions')->where('event_id', $eid)
                ->orderBy('sort_order')
                ->get(['id','name_fr','name_en','description_fr','color','icon','capacity','code']);
        }
    } catch (\Throwable $e) { $pavs = collect(); }

    $exCount = [];
    try {
        if (Schema::hasTable('event_exhibitors') && $pavs->isNotEmpty()) {
            $exCount = DB::table('event_exhibitors')
                ->whereIn('pavilion_id', $pavs->pluck('id'))
                ->selectRaw('pavilion_id, COUNT(*) as c')
                ->groupBy('pavilion_id')->pluck('c','pavilion_id')->all();
        }
    } catch (\Throwable $e) { $exCount = []; }

    // Prefer real DB pavilions; else the cards the route already passed.
    $usingDb = $pavs->isNotEmpty();
    $items   = $usingDb ? $pavs : collect($sCards ?? []);

    $palette = ['#157A43','#C97A16','#E6B201','#C0010C','#0F4824','#7C4FE0','#2E8B57','#3565DE'];

    // Normalise a pavilion (DB row or passed card) into a common shape.
    $norm = function($p, $i) use ($usingDb, $isFr, $exCount, $palette) {
        $get = fn($k) => is_array($p) ? ($p[$k] ?? null) : ($p->{$k} ?? null);
        $name  = $usingDb ? ($isFr ? ($p->name_fr ?: $p->name_en) : ($p->name_en ?: $p->name_fr)) : ($get('name') ?? '');
        $pid   = $get('id');
        $color = ($get('color')) ?: $palette[$i % count($palette)];
        return [
            'id'    => $pid,
            'name'  => $name ?: ($isFr ? 'Pavillon' : 'Pavilion'),
            'desc'  => $usingDb ? ($p->description_fr ?? '') : ($get('description') ?? ''),
            'code'  => $get('code') ?? '',
            'icon'  => $get('icon') ?: 'layout-grid',
            'cap'   => $get('capacity'),
            'color' => $color,
            'count' => $pid !== null ? ($exCount[$pid] ?? null) : null,
        ];
    };

    $normd = $items->values()->map(fn($p,$i) => $norm($p,$i));
    $featured = $normd->take(4);
    $total    = $normd->count();

    // ── Approved illustrative aggregate figures (from the design PNG) ──────────
    $miniStats = [
        ['store','Pays représentés','24'],
        ['layout-grid','Pavillons thématiques','12'],
        ['grid-3x3','Stands','286'],
    ];
    $globalStats = [
        ['globe','#157A43','#E2F3E8','24','Pays représentés'],
        ['layout-grid','#7C4FE0','#F0EAFB','12','Pavillons thématiques'],
        ['grid-3x3','#C97A16','#FDF3E0','286','Stands occupés'],
        ['users-round','#3565DE','#E8EFFB','580+','Exposants inscrits'],
    ];
    $regions = [
        ['Afrique Centrale',56],['Afrique de l\'Ouest',78],['Afrique du Nord',34],
        ['Afrique de l\'Est',42],['Afrique Australe',28],['International',48],
    ];
    // Colour swatches for the mini "Carte des pavillons" tile grid.
    $mapTiles = ['#157A43','#7C4FE0','#3565DE','#2E8B57','#C97A16','#E6B201'];
@endphp

{{-- ══════════════════ PAGE HEADER BAND ══════════════════ --}}
<section class="siarc-mud border-b border-[#EDE7DA]">
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-9 siarc-in">
        <nav class="flex items-center gap-2 text-[12px] text-[#8A857A] mb-3" aria-label="Breadcrumb">
            @if($h('siarc.home'))<a href="{{ $h('siarc.home') }}" class="hover:text-siarc-green font-medium">{{ $isFr ? 'Accueil' : 'Home' }}</a>@else<span class="font-medium">{{ $isFr ? 'Accueil' : 'Home' }}</span>@endif
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
            <span class="font-semibold text-siarc-ochre">{{ $sCrumb ?? ($isFr ? 'Explorateur des Pavillons' : 'Pavilion Explorer') }}</span>
        </nav>
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-5">
            <div>
                <h1 class="font-display text-[30px] sm:text-[40px] font-extrabold text-[#0F2E1A] leading-[1.05] tracking-tight">
                    {{ $isFr ? 'Explorateur des Pavillons' : 'Pavilion Explorer' }}
                </h1>
                <div class="mt-3 h-[3.5px] w-[116px] bg-gradient-to-r from-siarc-gold via-[#F1D48A] to-transparent rounded-full"></div>
                <p class="mt-4 text-[14px] text-[#55524A] leading-relaxed max-w-[640px]">
                    {{ $isFr
                        ? 'Découvrez les pavillons nationaux, régionaux et thématiques du SIARC 2026.'
                        : 'Discover the national, regional and thematic pavilions of SIARC 2026.' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-x-7 gap-y-2 shrink-0">
                <span class="inline-flex items-center gap-2.5 text-[12.5px] font-semibold text-[#2A271F]"><i data-lucide="calendar-days" class="w-5 h-5 text-siarc-ochre"></i>27 Juillet – 05 Août 2026</span>
                <span class="inline-flex items-center gap-2.5 text-[12.5px] font-semibold text-[#2A271F]"><i data-lucide="map-pin" class="w-5 h-5 text-siarc-ochre"></i>Musée National de Yaoundé</span>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ FILTER TOOLBAR ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
            {{-- search --}}
            <div class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-[#A8A498] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" aria-label="{{ $isFr ? 'Rechercher un pavillon' : 'Search a pavilion' }}"
                    placeholder="{{ $isFr ? 'Rechercher un pavillon, pays ou thème…' : 'Search a pavilion, country or theme…' }}"
                    class="w-full pl-10 pr-4 py-2.5 text-[13px] rounded-xl border border-[#E4E0D6] bg-[#FBFAF7] focus:outline-none focus:ring-2 focus:ring-siarc-green/20 focus:border-siarc-green">
            </div>
            {{-- select dropdowns (visual) --}}
            @foreach([
                ['label'=>($isFr?'Type de pavillon':'Pavilion type'),'first'=>($isFr?'Tous les types':'All types')],
                ['label'=>($isFr?'Région / Pays':'Region / Country'),'first'=>($isFr?'Toutes les régions':'All regions')],
                ['label'=>($isFr?'Thématique':'Theme'),'first'=>($isFr?'Toutes les thématiques':'All themes')],
            ] as $sel)
            <div class="relative">
                <select aria-label="{{ $sel['label'] }}" class="appearance-none text-[13px] font-medium text-[#2A271F] pl-3.5 pr-9 py-2.5 rounded-xl border border-[#E4E0D6] bg-white focus:outline-none focus:ring-2 focus:ring-siarc-green/20 cursor-pointer min-w-[168px]">
                    <option>{{ $sel['first'] }}</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498] absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
            @endforeach
            <button type="button" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#2A271F] px-4 py-2.5 rounded-xl border border-[#E4E0D6] bg-white hover:bg-[#F5F3EE] transition-colors shrink-0">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-siarc-green"></i>{{ $isFr ? 'Plus de filtres' : 'More filters' }}
            </button>
        </div>
    </div>
</section>

{{-- ══════════════════ CONTENT: MAIN + SIDEBAR ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8 py-7 grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-6 items-start">

        {{-- ─────────────── MAIN COLUMN ─────────────── --}}
        <div class="min-w-0">

            {{-- ── HERO BANNER + INLINE KPIs ── --}}
            <div class="relative rounded-2xl overflow-hidden siarc-shadow-lg siarc-adire text-white mb-9">
                <div class="siarc-kente absolute top-0 left-0 right-0 opacity-80"></div>
                <div class="absolute inset-0" style="background:linear-gradient(115deg,#042B15 0%,rgba(11,58,30,.86) 42%,rgba(15,72,36,.30) 100%)"></div>
                <div class="siarc-kente-v absolute right-0 top-0 bottom-0 opacity-40"></div>
                <div class="relative px-7 sm:px-9 py-9 sm:py-11 max-w-[560px]">
                    <div class="flex items-center gap-1.5 mb-5">
                        @for($i=0;$i<4;$i++)<span class="w-2.5 h-2.5 rotate-45" style="background:{{ ['#E6B201','#C0010C','#2E8B57','#E6B201'][$i] }}"></span>@endfor
                    </div>
                    <h2 class="font-display text-[30px] sm:text-[36px] font-extrabold leading-[1.08]">
                        {{ $isFr ? 'Explorer les pavillons du SIARC 2026' : 'Explore the SIARC 2026 pavilions' }}
                    </h2>
                    <p class="mt-4 text-[14px] text-white/80 leading-relaxed max-w-[420px]">
                        {{ $isFr
                            ? 'Un voyage à travers la créativité, le savoir-faire et la diversité culturelle d\'Afrique et du monde.'
                            : 'A journey through the creativity, craftsmanship and cultural diversity of Africa and the world.' }}
                    </p>
                    <div class="flex flex-wrap items-center gap-x-8 gap-y-4 mt-8">
                        @foreach($miniStats as [$mi,$ml,$mv])
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="{{ $mi }}" class="w-5 h-5 text-siarc-gold"></i></span>
                            <div>
                                <p class="font-display text-[22px] font-extrabold leading-none">{{ $mv }}</p>
                                <p class="text-[10.5px] font-semibold tracking-wide text-white/60 mt-1">{{ $ml }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── PAVILLONS À LA UNE ── --}}
            <div class="flex items-end justify-between gap-4 mb-5">
                <h3 class="font-display text-[22px] font-bold text-[#1A1712]">{{ $isFr ? 'Pavillons à la une' : 'Featured pavilions' }}</h3>
                @if($h('siarc.exhibitors'))
                <a href="{{ $h('siarc.exhibitors') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green hover:gap-2.5 transition-all">
                    {{ $isFr ? 'Voir tous les pavillons' : 'See all pavilions' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                @endif
            </div>

            @if($featured->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-11">
                @foreach($featured as $fi => $p)
                    @php
                        $href = ($p['id'] !== null && $h('siarc.exhibitors', ['pavilion'=>$p['id']])) ? $h('siarc.exhibitors', ['pavilion'=>$p['id']]) : $h('siarc.exhibitors');
                        $badge = $isFr ? ($fi % 2 === 0 ? 'Pays' : 'Thématique') : ($fi % 2 === 0 ? 'Country' : 'Theme');
                    @endphp
                    <div class="siarc-card siarc-shadow siarc-lift overflow-hidden flex flex-col group">
                        {{-- heritage image tile (no invented photo) --}}
                        <div class="relative h-[140px] siarc-adire overflow-hidden" style="background-color:{{ $p['color'] }}">
                            <div class="absolute inset-0" style="background:linear-gradient(135deg,{{ $p['color'] }},{{ $p['color'] }}CC 55%,rgba(0,0,0,.28))"></div>
                            <div class="siarc-kente-v absolute right-0 top-0 bottom-0 opacity-30"></div>
                            <span class="absolute top-3 left-3 inline-flex items-center text-[10.5px] font-bold px-2.5 py-1 rounded-full bg-white/90 text-[#1A1712] backdrop-blur">{{ $badge }}</span>
                            <i data-lucide="{{ $p['icon'] }}" class="w-14 h-14 text-white/85 absolute bottom-4 right-4" style="stroke-width:1.4"></i>
                        </div>
                        <div class="px-5 pt-4 pb-5 flex flex-col flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $p['color'] }}"></span>
                                <h4 class="font-display text-[16px] font-bold text-[#1A1712] leading-snug">{{ $p['name'] }}</h4>
                            </div>
                            @if($p['desc'])
                            <p class="text-[12px] text-[#7A756A] leading-relaxed mb-4 line-clamp-3">{{ $p['desc'] }}</p>
                            @else
                            <p class="text-[12px] text-[#A8A498] italic leading-relaxed mb-4">{{ $isFr ? 'Description à venir.' : 'Description coming soon.' }}</p>
                            @endif
                            <div class="flex items-center gap-4 mb-4 mt-auto text-[11.5px] font-semibold text-[#55524A]">
                                @if($p['cap'])<span class="inline-flex items-center gap-1.5"><i data-lucide="grid-3x3" class="w-3.5 h-3.5 text-siarc-ochre"></i>{{ $p['cap'] }} {{ $isFr ? 'stands' : 'stands' }}</span>@endif
                                <span class="inline-flex items-center gap-1.5"><i data-lucide="users" class="w-3.5 h-3.5 text-siarc-green"></i>{{ $p['count'] !== null ? $p['count'].' '.($isFr?'exposants':'exhibitors') : ($isFr?'Exposants à venir':'Exhibitors soon') }}</span>
                            </div>
                            @if($href)
                            <a href="{{ $href }}" class="inline-flex items-center justify-center gap-1.5 text-[12.5px] font-semibold text-white rounded-lg px-4 py-2.5 group-hover:gap-2.5 transition-all" style="background:{{ $p['color'] }}">
                                {{ $isFr ? 'Explorer' : 'Explore' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- ── TOUS LES PAVILLONS ── --}}
            <div class="flex items-center justify-between gap-4 mb-5">
                <h3 class="font-display text-[22px] font-bold text-[#1A1712]">{{ $isFr ? 'Tous les pavillons' : 'All pavilions' }}</h3>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <label class="text-[11.5px] text-[#8A857A] mr-1.5">{{ $isFr ? 'Trier par' : 'Sort by' }}</label>
                        <select aria-label="{{ $isFr ? 'Trier par' : 'Sort by' }}" class="appearance-none text-[12.5px] font-medium text-[#2A271F] pl-3 pr-8 py-2 rounded-lg border border-[#E4E0D6] bg-white focus:outline-none focus:ring-2 focus:ring-siarc-green/20 cursor-pointer">
                            <option>A - Z</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                    <div class="flex items-center rounded-lg border border-[#E4E0D6] bg-white overflow-hidden">
                        <button type="button" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}" class="px-2.5 py-2 text-white bg-siarc-green"><i data-lucide="layout-grid" class="w-4 h-4"></i></button>
                        <button type="button" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}" class="px-2.5 py-2 text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="clipboard-list" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </div>

            @if($normd->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($normd as $gi => $p)
                    @php
                        $href = ($p['id'] !== null && $h('siarc.exhibitors', ['pavilion'=>$p['id']])) ? $h('siarc.exhibitors', ['pavilion'=>$p['id']]) : $h('siarc.exhibitors');
                        $badge = $isFr ? ($gi % 3 === 0 ? 'Pays' : ($gi % 3 === 1 ? 'Thématique' : 'Régional')) : ($gi % 3 === 0 ? 'Country' : ($gi % 3 === 1 ? 'Theme' : 'Regional'));
                        $Tag = $href ? 'a' : 'div';
                    @endphp
                    <{{ $Tag }} @if($href)href="{{ $href }}"@endif class="siarc-card siarc-shadow siarc-lift overflow-hidden flex flex-col group">
                        <div class="relative h-[104px] siarc-adire overflow-hidden" style="background-color:{{ $p['color'] }}">
                            <div class="absolute inset-0" style="background:linear-gradient(135deg,{{ $p['color'] }},{{ $p['color'] }}D9 60%,rgba(0,0,0,.24))"></div>
                            <span class="absolute top-2.5 left-2.5 inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-white/90 text-[#1A1712]">{{ $badge }}</span>
                            @if($p['code'])<span class="absolute top-2.5 right-2.5 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-black/25 text-white"><i data-lucide="tag" class="w-2.5 h-2.5"></i>{{ $p['code'] }}</span>@endif
                            <i data-lucide="{{ $p['icon'] }}" class="w-10 h-10 text-white/80 absolute bottom-3 right-3" style="stroke-width:1.5"></i>
                        </div>
                        <div class="px-4 pt-3.5 pb-4 flex flex-col flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $p['color'] }}"></span>
                                <h4 class="font-display text-[14.5px] font-bold text-[#1A1712] leading-snug">{{ $p['name'] }}</h4>
                            </div>
                            <div class="flex items-center gap-4 mt-auto pt-2 text-[11px] font-semibold text-[#55524A]">
                                @if($p['cap'])<span class="inline-flex items-center gap-1.5"><i data-lucide="grid-3x3" class="w-3.5 h-3.5 text-siarc-ochre"></i>{{ $p['cap'] }}</span>@endif
                                <span class="inline-flex items-center gap-1.5"><i data-lucide="users" class="w-3.5 h-3.5 text-siarc-green"></i>{{ $p['count'] !== null ? $p['count'].' '.($isFr?'exposants':'exhibitors') : ($isFr?'À venir':'Soon') }}</span>
                            </div>
                        </div>
                    </{{ $Tag }}>
                @endforeach
            </div>

            <div class="flex justify-center mt-9">
                <button type="button" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#2A271F] px-6 py-3 rounded-xl border border-[#E4E0D6] bg-white hover:bg-[#F5F3EE] siarc-shadow transition-colors">
                    {{ $isFr ? 'Charger plus de pavillons' : 'Load more pavilions' }} <i data-lucide="arrow-down" class="w-4 h-4 text-siarc-green"></i>
                </button>
            </div>
            @else
            {{-- Honest empty state --}}
            <div class="siarc-card siarc-shadow p-12 text-center">
                <span class="w-16 h-16 mx-auto rounded-2xl bg-[#F3F0E7] flex items-center justify-center mb-4">
                    <i data-lucide="layout-grid" class="w-8 h-8 text-siarc-green"></i>
                </span>
                <h3 class="font-display text-[20px] font-bold text-[#1A1712] mb-2">{{ $isFr ? 'Pavillons à venir' : 'Pavilions coming soon' }}</h3>
                <p class="text-[13px] text-[#7A756A] max-w-[420px] mx-auto">
                    {{ $isFr ? 'Les pavillons du SIARC 2026 seront bientôt annoncés. Revenez prochainement.' : 'SIARC 2026 pavilions will be announced soon. Please check back later.' }}
                </p>
            </div>
            @endif
        </div>

        {{-- ─────────────── SIDEBAR ─────────────── --}}
        <aside class="space-y-5 xl:sticky xl:top-6">

            {{-- Carte des pavillons --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[15px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Carte des pavillons' : 'Pavilion map' }}</h3>
                <div class="relative rounded-xl overflow-hidden border border-[#EEECE5] bg-[#F5F3EE] p-4"
                    style="background-image:linear-gradient(#E9E6DD 1px,transparent 1px),linear-gradient(90deg,#E9E6DD 1px,transparent 1px);background-size:26px 26px;">
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($mapTiles as $mt)
                        <button type="button" aria-label="{{ $isFr ? 'Voir le pavillon' : 'View pavilion' }}" class="relative aspect-square rounded-lg flex items-center justify-center transition-transform hover:scale-105"
                            style="background:{{ $mt }}1A;border:1.5px solid {{ $mt }}44;">
                            <i data-lucide="map-pin" class="w-5 h-5" style="color:{{ $mt }}"></i>
                        </button>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3">
                    <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Cliquez sur un pavillon pour voir les détails' : 'Click a pavilion to see details' }}</p>
                    <i data-lucide="maximize" class="w-4 h-4 text-[#B4B0A6]"></i>
                </div>
            </div>

            {{-- Explorer par région --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[15px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'Explorer par région' : 'Explore by region' }}</h3>
                <ul class="divide-y divide-[#F0EEE7]">
                    @foreach($regions as $ri => [$rname,$rc])
                    @php $rcolor = $palette[$ri % count($palette)]; $rhref = $h('siarc.exhibitors'); @endphp
                    <li>
                        <{{ $rhref ? 'a' : 'div' }} @if($rhref)href="{{ $rhref }}"@endif class="flex items-center justify-between py-2.5 group">
                            <span class="inline-flex items-center gap-2.5 text-[12.5px] font-medium text-[#2A271F] group-hover:text-siarc-green transition-colors">
                                <span class="w-2 h-2 rounded-full" style="background:{{ $rcolor }}"></span>{{ $rname }}
                            </span>
                            <span class="text-[12.5px] font-bold text-[#8A857A]">{{ $rc }}</span>
                        </{{ $rhref ? 'a' : 'div' }}>
                    </li>
                    @endforeach
                </ul>
                @if($h('siarc.exhibitors'))
                <a href="{{ $h('siarc.exhibitors') }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-siarc-green mt-3 hover:gap-2.5 transition-all">
                    {{ $isFr ? 'Voir toutes les régions' : 'See all regions' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
                @endif
            </div>

            {{-- Aperçu global --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="font-display text-[15px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Aperçu global' : 'Overview' }}</h3>
                <div class="space-y-3.5">
                    @foreach($globalStats as [$gicon,$gcolor,$gtile,$gval,$glabel])
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $gtile }}"><i data-lucide="{{ $gicon }}" class="w-5 h-5" style="color:{{ $gcolor }}"></i></span>
                        <div>
                            <p class="font-display text-[19px] font-extrabold text-[#161513] leading-none">{{ $gval }}</p>
                            <p class="text-[11px] font-medium text-[#8A857A] mt-1">{{ $glabel }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Besoin d'informations --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="siarc-kente h-[6px]"></div>
                <div class="p-5">
                    <h3 class="font-display text-[15px] font-bold text-[#1A1712] mb-2">{{ $isFr ? 'Besoin d\'informations ?' : 'Need information?' }}</h3>
                    <p class="text-[12px] text-[#7A756A] leading-relaxed mb-4">
                        {{ $isFr ? 'Notre équipe est à votre disposition pour vous accompagner.' : 'Our team is available to assist you.' }}
                    </p>
                    @if($h('siarc.register'))
                    <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-green w-full justify-center px-4 py-3 text-[12.5px]">
                        <i data-lucide="mail" class="w-4 h-4"></i>{{ $isFr ? 'Contacter l\'équipe SIARC' : 'Contact the SIARC team' }}
                    </a>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</section>
