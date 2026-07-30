@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $galleryActive = 'categories';

    // Official craft-taxonomy drill-down. The controller passes the current node
    // ($current, null = root), the $children to show at this level, the $trail
    // breadcrumb, the 10 illustrated $featured tiles, and rolled-up $biz/$prod counts.
    $nm = fn ($node) => $isFr ? $node->name_fr : ($node->name_en ?? $node->name_fr);
    $isLeaf = fn ($node) => $childrenByParent->get($node->id, collect())->isEmpty();
    $levelLabelFr = [1 => 'Secteur', 2 => 'Filière', 3 => 'Corps de métier', 4 => 'Métier'];
    $levelLabelEn = [1 => 'Sector', 2 => 'Branch', 3 => 'Trade group', 4 => 'Trade'];
    $levelLabel = fn ($lvl) => $isFr ? ($levelLabelFr[$lvl] ?? '') : ($levelLabelEn[$lvl] ?? '');
    $nodeIcon = fn ($lvl) => [1 => 'layers', 2 => 'git-branch', 3 => 'folder', 4 => 'hammer'][$lvl] ?? 'tag';
    $fmt = fn ($n) => $isFr ? number_format($n, 0, ',', ' ') : number_format($n);

    $sectors = $all->where('level', 1)->sortBy('sort_order')->values();
    $trailIds = collect($trail)->pluck('id')->all();

    $rootBiz  = array_sum(array_map(fn ($s) => $biz[$s->id] ?? 0, $sectors->all()));
    $headBiz  = $current ? ($biz[$current->id] ?? 0) : $rootBiz;
    $headProd = $current ? ($prod[$current->id] ?? 0) : array_sum(array_map(fn ($s) => $prod[$s->id] ?? 0, $sectors->all()));

    $trustItems = $isFr ? [
        ['shield-check','Authenticité garantie', "Tous nos produits sont\nauthentiques et certifiés."],
        ['users','Soutien aux artisans',  "Chaque achat soutient directement\nnos artisans locaux."],
        ['hand-coins','Paiement direct',  "Vous réglez l'artisan,\nsans intermédiaire."],
        ['truck','Livraison fiable',      "Livraison rapide partout\ndans le monde."],
        ['headset','Service client dédié',  "Une équipe à votre écoute\n7j/7."],
    ] : [
        ['shield-check','Guaranteed authenticity', "All our products are\nauthentic and certified."],
        ['users','Support for artisans',    "Every purchase directly supports\nour local artisans."],
        ['hand-coins','Direct payment',   "You pay the artisan,\nwith no middleman."],
        ['truck','Reliable delivery',       "Fast delivery anywhere\nin the world."],
        ['headset','Dedicated support',       "A team at your service\n7 days a week."],
    ];

    /*
     | SEO/AEO for the category browse page. $current is the real node this
     | request is drilled into (null = root); title/description and the
     | breadcrumb trail are built from it and from the real rolled-up
     | $headBiz/$headProd counts, never a made-up number. ?cat= (and, at
     | root, ?view=filieres) changes the actual content shown so it stays on
     | the canonical URL; ?lang=/?sort= do not, so those are stripped.
     */
    $seoCatQuery = array_filter([
        'cat'  => $current->slug ?? null,
        'view' => (! $current && $view === 'filieres') ? 'filieres' : null,
    ]);
    $seoCanonical = url('/galerie/secteurs') . ($seoCatQuery ? '?' . http_build_query($seoCatQuery) : '');
    $seoHreflangQuery = $seoCatQuery;

    $title = $current
        ? ($nm($current) . ' — Artisan Hub 237')
        : ($isFr ? 'Toutes les catégories — Artisan Hub 237' : 'All categories — Artisan Hub 237');
    // Real rolled-up counts when the category has any; a bare but truthful
    // line when it doesn't — "0 artisans" is honest yet tells a searcher
    // nothing worth clicking.
    if ($current) {
        $descCounts = collect([
            $headBiz > 0 ? ($fmt($headBiz) . ($isFr ? ' artisans' : ' artisans')) : null,
            $headProd > 0 ? ($fmt($headProd) . ($isFr ? ' produits' : ' products')) : null,
        ])->filter()->implode($isFr ? ' et ' : ' and ');
        $description = $descCounts !== ''
            ? ($isFr
                ? "{$nm($current)} — {$descCounts} camerounais sur Artisan Hub 237."
                : "{$nm($current)} — {$descCounts} from Cameroonian artisans on Artisan Hub 237.")
            : ($isFr
                ? "{$nm($current)} — catégorie de l'artisanat camerounais sur Artisan Hub 237."
                : "{$nm($current)} — a category of Cameroonian craft on Artisan Hub 237.");
    } else {
        $description = $isFr
            ? 'Toutes les catégories de l\'artisanat camerounais — explorez nos créations par catégorie.'
            : 'All categories of Cameroonian crafts — explore our creations by category.';
    }

    $seoBreadcrumb = \App\Support\Seo::breadcrumbSchema(array_merge(
        [
            ['name' => $isFr ? 'Accueil' : 'Home', 'url' => route('home', ['lang' => $lang])],
            ['name' => $isFr ? 'Catégories' : 'Categories', 'url' => route('industries.index', ['lang' => $lang])],
        ],
        collect($trail)->map(fn ($node) => ['name' => $nm($node), 'url' => route('industries.index', ['lang' => $lang, 'cat' => $node->slug])])->all()
    ));
    $seoJsonLd = [$seoBreadcrumb];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title }}</title>

    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-cream: 247 242 236;
            --c-gold: 201 148 46;
            --c-goldbt: 224 165 47;
            --c-leaf: 22 76 40;
            --f-serif: "Playfair Display", Georgia, serif;
        }
    </style>

    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    @include('pages.partials.seo-head')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#FEFDFC] dark:bg-[#12150F] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-header', ['dirNavActive' => 'categories'])

<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-5 pb-9">
    {{-- `items-start` only from lg up. In the mobile `flex-col` phase it made
         <main> size to fit-content instead of stretching, so the widest child
         set the column width and the page ran 21px past a 320px screen. --}}
    <div class="flex flex-col lg:flex-row gap-8 xl:gap-9 lg:items-start">

        <!-- Sidebar -->
        <aside class="hidden lg:block w-[273px] shrink-0 space-y-5">
            <!-- Categories list -->
            <div class="rounded-xl shadow-sm border border-[#EFEDE7] dark:border-[#262B21] overflow-hidden">
                <div class="flex items-center gap-3 bg-[#0A2C1D] px-4 h-[42px]">
                    <img src="{{ asset('images/landing/cat-sidebar-icon.png') }}" alt="" class="w-[22px] h-[20px]" aria-hidden="true">
                    <span class="text-[12px] font-bold tracking-[0.12em] text-white uppercase">{{ $isFr ? 'Secteurs' : 'Sectors' }}</span>
                </div>
                <nav class="bg-white dark:bg-[#12150F] py-1.5">
                    <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="relative flex items-center gap-3 px-4 py-[8px] {{ $current ? 'hover:bg-[#FAF8F2] hover:dark:bg-[#0A0C09]' : 'bg-[#F7F4EA] dark:bg-[#0A0C09]' }} transition-colors">
                        @unless($current)<span class="absolute left-0 inset-y-0 w-[3px] bg-[#D9991F]"></span>@endunless
                        <img src="{{ asset('images/landing/cat-side-0.png') }}" alt="" class="w-[20px] h-[20px]" aria-hidden="true">
                        <span class="text-[13.5px] font-semibold text-[#14351F] dark:text-[#339B56]">{{ $isFr ? 'Toutes les catégories' : 'All categories' }}</span>
                        <span class="ml-auto border border-[#E7E5DC] dark:border-[#262B21] bg-white dark:bg-[#12150F] rounded-full px-2 py-0.5 text-[11px] text-[#6F6B60] dark:text-[#868778]">{{ $rootBiz }}</span>
                    </a>
                    @foreach($sectors as $sec)
                    @php $secActive = in_array($sec->id, $trailIds); @endphp
                    <a href="{{ route('industries.index', ['lang' => $lang, 'cat' => $sec->slug]) }}" class="relative flex items-center gap-3 px-4 py-[8px] {{ $secActive ? 'bg-[#F7F4EA] dark:bg-[#0A0C09]' : 'hover:bg-[#FAF8F2] hover:dark:bg-[#0A0C09]' }} transition-colors">
                        @if($secActive)<span class="absolute left-0 inset-y-0 w-[3px] bg-[#D9991F]"></span>@endif
                        <i data-lucide="layers" class="w-[18px] h-[18px] text-[#1D4A2E] dark:text-[#339B56] shrink-0" style="stroke-width:1.8"></i>
                        <span class="text-[13.5px] {{ $secActive ? 'font-semibold text-[#14351F] dark:text-[#339B56]' : 'text-[#26251F] dark:text-[#F3EFE7]' }} truncate">{{ $nm($sec) }}</span>
                        <span class="ml-auto shrink-0 border border-[#E7E5DC] dark:border-[#262B21] rounded-full px-2 py-0.5 text-[11px] text-[#6F6B60] dark:text-[#868778]">{{ $biz[$sec->id] ?? 0 }}</span>
                    </a>
                    @endforeach
                </nav>
            </div>

            <!-- Regions card -->
            <div class="relative rounded-xl border border-[#EFEDE7] dark:border-[#262B21] bg-[#FBFAF6] dark:bg-[#12150F] shadow-sm overflow-hidden p-5">
                <img src="{{ asset('images/landing/cat-region-map.png') }}" alt="" class="absolute right-0 top-3 w-[68px] pointer-events-none select-none" aria-hidden="true">
                <h3 class="relative font-serif text-[19px] font-bold leading-snug text-[#1D1B16] dark:text-[#F3EFE7] max-w-[190px]">
                    {{ $isFr ? "Explorez l'artisanat par région" : 'Explore crafts by region' }}
                </h3>
                <p class="relative mt-2.5 text-[12.5px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[190px]">
                    {{ $isFr ? 'Découvrez les trésors artisanaux des 10 régions du Cameroun.' : "Discover the craft treasures of Cameroon's 10 regions." }}
                </p>
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}"
                    class="relative mt-4 inline-flex items-center gap-2.5 bg-[#0E3022] hover:bg-leaf text-white text-[12.5px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    {{ $isFr ? 'Voir les régions' : 'View regions' }}
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Help card -->
            <div class="rounded-xl border border-[#EFEDE7] dark:border-[#262B21] bg-white dark:bg-[#12150F] shadow-sm p-5 flex items-start gap-3.5">
                <span class="w-12 h-12 shrink-0 rounded-lg bg-[#F2F0E7] dark:bg-[#0A0C09] flex items-center justify-center">
                    <i data-lucide="headset" class="w-6 h-6 text-[#1D4A2E] dark:text-[#339B56]" style="stroke-width:1.5"></i>
                </span>
                <div>
                    <h3 class="text-[13.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</h3>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Notre équipe est à votre écoute' : 'Our team is here for you' }}</p>
                    <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-3 inline-flex items-center gap-2 bg-[#F1EFE9] dark:bg-[#0A0C09] hover:bg-[#EAE7DE] hover:dark:bg-[#0A0C09] text-[12.5px] font-medium text-[#26251F] dark:text-[#F3EFE7] px-3.5 py-2 rounded-lg transition-colors">
                        {{ $isFr ? 'Nous contacter' : 'Contact us' }}
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 min-w-0">
            <!-- Breadcrumb -->
            <nav class="flex flex-wrap items-center gap-2 text-[14px] md:text-[13px]" aria-label="Breadcrumb">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#166534] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
                <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="{{ $current ? 'text-[#166534] dark:text-[#339B56] hover:underline' : 'text-[#6F6B60] dark:text-[#868778]' }}">{{ $isFr ? 'Catégories' : 'Categories' }}</a>
                @foreach($trail as $crumb)
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
                @if($loop->last)
                <span class="text-[#6F6B60] dark:text-[#868778] truncate max-w-[260px]">{{ $nm($crumb) }}</span>
                @else
                <a href="{{ route('industries.index', ['lang' => $lang, 'cat' => $crumb->slug]) }}" class="text-[#166534] dark:text-[#339B56] hover:underline truncate max-w-[180px]">{{ $nm($crumb) }}</a>
                @endif
                @endforeach
            </nav>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-5">
                <div class="min-w-0">
                    @if($current)
                    <span class="inline-block text-[14px] md:text-[11.5px] font-bold uppercase tracking-[0.1em] text-[#B8860B]">{{ $levelLabel($current->level) }}</span>
                    @endif
                    <h1 class="font-serif text-[28px] sm:text-[36px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] leading-tight">
                        {{ $current ? $nm($current) : ($view === 'filieres' ? ($isFr ? 'Toutes les filières' : 'All branches') : ($isFr ? 'Toutes les catégories' : 'All categories')) }}
                    </h1>
                    <div class="mt-2.5 h-[3.5px] w-[112px] bg-gradient-to-r from-[#D9991F] via-[#E9C989] to-transparent rounded-full"></div>
                    <p class="mt-4 text-[14px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed max-w-[460px]">
                        @if($current)
                            {{ $isFr
                                ? ($isLeaf($current) ? 'Découvrez les artisans et les créations de ce métier.' : 'Parcourez les sous-catégories officielles de cette branche de l\'artisanat.')
                                : ($isLeaf($current) ? 'Discover the artisans and creations of this trade.' : 'Browse the official sub-categories of this craft branch.') }}
                        @elseif($view === 'filieres')
                            {{ $isFr
                                ? 'Les 14 filières officielles — les grandes familles de métiers, tous secteurs confondus. Cliquez sur une filière pour explorer ses corps de métier.'
                                : 'The 14 official filières — the main craft families across every sector. Click a filière to explore its trade groups.' }}
                        @else
                            {{ $isFr
                                ? 'Explorez l\'artisanat camerounais selon la nomenclature officielle : secteur, filière, corps de métier et métier.'
                                : 'Explore Cameroonian crafts by the official nomenclature: sector, branch, trade group and trade.' }}
                        @endif
                    </p>
                    <p class="mt-3 text-[14px] md:text-[13px] text-[#6F6B60] dark:text-[#868778]">
                        <span class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $fmt($headBiz) }}</span> {{ $isFr ? 'artisans' : 'artisans' }}
                        <span class="mx-1.5 text-[#CFC9BB]">·</span>
                        <span class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $fmt($headProd) }}</span> {{ $isFr ? 'produits' : 'products' }}
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('industries.index') }}" class="flex items-center gap-2.5 h-[46px] bg-white dark:bg-[#12150F] border border-[#E5E3E0] dark:border-[#262B21] rounded-xl px-4 shadow-sm">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        @if($current)<input type="hidden" name="cat" value="{{ $current->slug }}">@endif
                        @if(!$current && $view === 'filieres')<input type="hidden" name="view" value="filieres">@endif
                        <label for="sort" class="text-[14px] md:text-[13.5px] text-[#6F6B60] dark:text-[#868778] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                        <select id="sort" name="sort" onchange="this.form.submit()"
                            class="ui-field-bare font-semibold cursor-pointer w-auto pr-1">
                            <option value="" @selected(empty($sort))>{{ $isFr ? 'Ordre officiel' : 'Official order' }}</option>
                            <option value="name" @selected(($sort ?? '') === 'name')>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                            <option value="products" @selected(($sort ?? '') === 'products')>{{ $isFr ? 'Produits' : 'Products' }}</option>
                        </select>
                    </form>
                    <div class="hidden sm:flex items-center h-[46px] bg-[#F4F2ED] dark:bg-[#0A0C09] rounded-xl p-1">
                        <button type="button" id="view-grid-btn" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}"
                            class="h-full px-3.5 rounded-lg bg-white dark:bg-[#12150F] shadow-sm flex items-center justify-center transition-colors">
                            <i data-lucide="layout-grid" class="w-[18px] h-[18px] text-[#14532D] dark:text-[#339B56]" style="stroke-width:2.2"></i>
                        </button>
                        <button type="button" id="view-list-btn" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}"
                            class="h-full px-3.5 rounded-lg flex items-center justify-center transition-colors">
                            <i data-lucide="list" class="w-[18px] h-[18px] text-[#6F6B60] dark:text-[#868778]" style="stroke-width:2.2"></i>
                        </button>
                    </div>
                </div>
            </div>

            @unless($current)
            <div class="mt-5 inline-flex items-center bg-[#F4F2ED] dark:bg-[#0A0C09] rounded-xl p-1 gap-1">
                <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="px-4 h-[44px] md:h-[36px] inline-flex items-center rounded-lg text-[14px] md:text-[13px] font-semibold transition-colors {{ $view === 'filieres' ? 'text-[#6F6B60] dark:text-[#868778] hover:text-[#1D1B16] hover:dark:text-[#F3EFE7]' : 'bg-white dark:bg-[#12150F] shadow-sm text-[#14532D] dark:text-[#339B56]' }}">{{ $isFr ? 'Par secteur' : 'By sector' }}</a>
                <a href="{{ route('industries.index', ['lang' => $lang, 'view' => 'filieres']) }}" class="px-4 h-[44px] md:h-[36px] inline-flex items-center rounded-lg text-[14px] md:text-[13px] font-semibold transition-colors {{ $view === 'filieres' ? 'bg-white dark:bg-[#12150F] shadow-sm text-[#14532D] dark:text-[#339B56]' : 'text-[#6F6B60] dark:text-[#868778] hover:text-[#1D1B16] hover:dark:text-[#F3EFE7]' }}">{{ $isFr ? 'Toutes les filières' : 'All branches' }}</a>
            </div>
            @endunless

            <p class="{{ $current ? 'mt-5' : 'mt-3' }} text-[14px] md:text-[13px] text-[#55524A] dark:text-[#B4B5A6]">
                {{ $fmt($children->count()) }}
                @if(!$current && $view === 'filieres')
                    {{ $isFr ? ($children->count() === 1 ? 'filière' : 'filières') : ($children->count() === 1 ? 'branch' : 'branches') }}
                @else
                    {{ $isFr ? ($children->count() === 1 ? 'catégorie' : 'catégories') : ($children->count() === 1 ? 'category' : 'categories') }}
                @endif
            </p>

            @if($children->isEmpty())
            <!-- Leaf métier: no sub-categories -->
            <div class="mt-4 bg-white dark:bg-[#12150F] border border-[#F1EFEA] dark:border-[#262B21] rounded-xl px-6 py-12 text-center">
                <span class="block mx-auto w-14 h-14 rounded-full bg-[#F2F0E7] dark:bg-[#0A0C09] flex items-center justify-center">
                    <i data-lucide="hammer" class="w-7 h-7 text-[#1D4A2E] dark:text-[#339B56]" style="stroke-width:1.5"></i>
                </span>
                <p class="mt-4 text-[14px] text-[#55524A] dark:text-[#B4B5A6] max-w-[380px] mx-auto">{{ $isFr ? "Ce métier n'a pas de sous-catégorie. Découvrez les artisans qui l'exercent." : 'This trade has no sub-category. Discover the artisans who practise it.' }}</p>
                @if($current)
                <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $current->slug]) }}" class="mt-5 inline-flex items-center gap-2 bg-[#0E3022] hover:bg-leaf text-white text-[14px] md:text-[13px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    {{ $isFr ? 'Voir les artisans' : 'View artisans' }}<i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                @endif
            </div>
            @endif

            {{-- The craft categories a visitor recognises, shown first. The official
                 nomenclature (secteur → filière → corps → métier) follows below;
                 leading with it meant the page opened on "Banque & Finance". --}}
            @unless($current)
            <div class="mt-4">
                <div class="flex items-center gap-3">
                    <h2 class="font-serif text-[22px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] whitespace-normal sm:whitespace-nowrap">{{ $isFr ? 'Catégories populaires' : 'Popular categories' }}</h2>
                    <span class="h-px flex-1 bg-[#EAE7DE] dark:bg-[#0A0C09]"></span>
                </div>
                <div class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                    @foreach($featured as $tile)
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $tile->slug]) }}" class="group bg-white dark:bg-[#12150F] border border-[#F1EFEA] dark:border-[#262B21] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] px-3 pt-4 pb-5 text-center hover:shadow-md hover:-translate-y-0.5 transition-all">
                        <span class="block mx-auto w-[84px] h-[84px] rounded-full overflow-hidden">
                            <img src="{{ asset('images/landing/' . $tile->image_icon) }}" alt="" class="w-full h-full object-cover scale-[1.17]" aria-hidden="true">
                        </span>
                        {{-- `line-clamp-2` sets `display:-webkit-box`; the `flex` on the
                             same element overrode it, so instead of clamping to two
                             lines the title became one centred, horizontally clipped
                             line — at 360 "Sculpteur/décorateur sur tous matériaux"
                             read as "culpteur/décorateu". Clamp only; the parent `a`
                             is already `text-center`. --}}
                        <h3 class="mt-3 text-[14px] md:text-[13px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] leading-snug line-clamp-2 min-h-[40px] md:min-h-[34px] break-words">{{ $nm($tile) }}</h3>
                        <p class="mt-1 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $fmt($biz[$tile->id] ?? 0) }} {{ $isFr ? 'artisans' : 'artisans' }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
            @endunless

@unless($current)
            <div class="mt-9 flex items-center gap-3">
                <h2 class="font-serif text-[22px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] whitespace-normal sm:whitespace-nowrap">{{ $isFr ? 'Toutes les catégories officielles' : 'All official categories' }}</h2>
                <span class="h-px flex-1 bg-[#EAE7DE] dark:bg-[#0A0C09]"></span>
            </div>
            @endunless

            <!-- Category cards — grid view -->
            <div id="cards-grid" class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($children as $child)
                @php $leaf = $isLeaf($child); $href = $leaf ? route('businesses.index', ['lang' => $lang, 'industry' => $child->slug]) : route('industries.index', ['lang' => $lang, 'cat' => $child->slug]); @endphp
                <div class="group bg-white dark:bg-[#12150F] border border-[#F1EFEA] dark:border-[#262B21] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] px-3 pt-4 pb-5 text-center hover:shadow-md hover:-translate-y-0.5 transition-all">
                    @if($child->image_icon)
                    <span class="block mx-auto w-[84px] h-[84px] rounded-full overflow-hidden">
                        <img src="{{ asset('images/landing/' . $child->image_icon) }}" alt="" class="w-full h-full object-cover scale-[1.17]" aria-hidden="true">
                    </span>
                    @else
                    <span class="block mx-auto w-[84px] h-[84px] rounded-full bg-[#F2F0E7] dark:bg-[#0A0C09] flex items-center justify-center">
                        <i data-lucide="{{ $nodeIcon($child->level) }}" class="w-8 h-8 text-[#1D4A2E] dark:text-[#339B56]" style="stroke-width:1.5"></i>
                    </span>
                    @endif
                    <span class="mt-3 inline-block text-[14px] md:text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#B8860B]">{{ $levelLabel($child->level) }}</span>
                    {{-- Same `line-clamp` / `flex` conflict as the featured tiles above. --}}
                    <h2 class="mt-1 text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] leading-snug line-clamp-2 min-h-[38px] break-words">
                        {{ $nm($child) }}
                    </h2>
                    <div class="mx-auto mt-1.5 h-[2px] w-6 bg-[#E2B54D] rounded-full"></div>
                    <p class="mt-2 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $fmt($biz[$child->id] ?? 0) }} {{ $isFr ? 'artisans' : 'artisans' }} <span class="text-[#CFC9BB]">·</span> {{ $fmt($prod[$child->id] ?? 0) }} {{ $isFr ? 'produits' : 'products' }}</p>
                    <a href="{{ $href }}" class="ui-tap-inset mt-3.5 inline-flex items-center gap-2 text-[14px] md:text-[13px] font-semibold text-[#166534] dark:text-[#339B56] hover:text-leaf hover:dark:text-[#339B56] transition-colors">
                        {{ $leaf ? ($isFr ? 'Voir les artisans' : 'View artisans') : ($isFr ? 'Explorer' : 'Explore') }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Category cards — list view -->
            <div id="cards-list" class="hidden mt-4 space-y-3.5">
                @foreach($children as $child)
                @php $leaf = $isLeaf($child); $href = $leaf ? route('businesses.index', ['lang' => $lang, 'industry' => $child->slug]) : route('industries.index', ['lang' => $lang, 'cat' => $child->slug]); @endphp
                <div class="bg-white dark:bg-[#12150F] border border-[#F1EFEA] dark:border-[#262B21] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] px-5 py-3.5 flex items-center gap-5">
                    @if($child->image_icon)
                    <span class="block w-[56px] h-[56px] shrink-0 rounded-full overflow-hidden">
                        <img src="{{ asset('images/landing/' . $child->image_icon) }}" alt="" class="w-full h-full object-cover scale-[1.17]" aria-hidden="true">
                    </span>
                    @else
                    <span class="block w-[56px] h-[56px] shrink-0 rounded-full bg-[#F2F0E7] dark:bg-[#0A0C09] flex items-center justify-center">
                        <i data-lucide="{{ $nodeIcon($child->level) }}" class="w-6 h-6 text-[#1D4A2E] dark:text-[#339B56]" style="stroke-width:1.5"></i>
                    </span>
                    @endif
                    <div class="min-w-0">
                        <span class="text-[14px] md:text-[10.5px] font-bold uppercase tracking-[0.08em] text-[#B8860B]">{{ $levelLabel($child->level) }}</span>
                        <h2 class="text-[15px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] truncate">{{ $nm($child) }}</h2>
                        <p class="mt-0.5 text-[14px] md:text-[12.5px] text-[#6F6B60] dark:text-[#868778]">{{ $fmt($biz[$child->id] ?? 0) }} {{ $isFr ? 'artisans' : 'artisans' }} <span class="text-[#CFC9BB]">·</span> {{ $fmt($prod[$child->id] ?? 0) }} {{ $isFr ? 'produits' : 'products' }}</p>
                    </div>
                    <a href="{{ $href }}" class="ml-auto shrink-0 inline-flex items-center gap-2 text-[14px] md:text-[13px] font-semibold text-[#166534] dark:text-[#339B56] hover:text-leaf hover:dark:text-[#339B56] transition-colors">
                        {{ $leaf ? ($isFr ? 'Voir les artisans' : 'View artisans') : ($isFr ? 'Explorer' : 'Explore') }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @endforeach
            </div>


            <!-- Trust strip -->
            <div class="mt-7 bg-[#F6F6EF] dark:bg-[#0A0C09] rounded-xl px-4 sm:px-7 py-4 grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-y-6 lg:divide-x divide-[#E3DFC9] dark:divide-[#6A5210]">
                @foreach($trustItems as [$trustIcon, $trustTitle, $trustDesc])
                <div class="flex items-start gap-2.5 lg:px-4 first:lg:pl-0 last:lg:pr-0">
                    <span class="w-[52px] h-[52px] rounded-full bg-[#FBF3E6] dark:bg-[#1B2016] flex items-center justify-center shrink-0 -mt-1" aria-hidden="true">
                        <i data-lucide="{{ $trustIcon }}" class="w-[26px] h-[26px] text-[#04291A] dark:text-[#EDB33A]" stroke-width="1.5"></i>
                    </span>
                    <div>
                        <h3 class="text-[14px] md:text-[13.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $trustTitle }}</h3>
                        <p class="mt-1 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778] leading-relaxed whitespace-pre-line">{{ $trustDesc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </main>
    </div>
</div>

{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();

    // Mobile menu
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    // Grid / list view toggle
    const gridBtn = document.getElementById('view-grid-btn');
    const listBtn = document.getElementById('view-list-btn');
    const gridEl = document.getElementById('cards-grid');
    const listEl = document.getElementById('cards-list');

    function setView(view) {
        const isGrid = view !== 'list';
        gridEl.classList.toggle('hidden', !isGrid);
        listEl.classList.toggle('hidden', isGrid);
        gridBtn.classList.toggle('bg-white dark:bg-[#12150F]', isGrid);
        gridBtn.classList.toggle('shadow-sm', isGrid);
        listBtn.classList.toggle('bg-white dark:bg-[#12150F]', !isGrid);
        listBtn.classList.toggle('shadow-sm', !isGrid);
        gridBtn.querySelector('svg')?.setAttribute('class', 'w-[18px] h-[18px] ' + (isGrid ? 'text-[#14532D] dark:text-[#339B56]' : 'text-[#6F6B60] dark:text-[#868778]'));
        listBtn.querySelector('svg')?.setAttribute('class', 'w-[18px] h-[18px] ' + (!isGrid ? 'text-[#14532D] dark:text-[#339B56]' : 'text-[#6F6B60] dark:text-[#868778]'));
        try { localStorage.setItem('catView', view); } catch (e) {}
    }
    gridBtn.addEventListener('click', () => setView('grid'));
    listBtn.addEventListener('click', () => setView('list'));
    try { if (localStorage.getItem('catView') === 'list') setView('list'); } catch (e) {}
</script>
</body>
</html>
