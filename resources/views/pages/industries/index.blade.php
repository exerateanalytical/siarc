@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
    $galleryActive = 'categories';

    // Design content (categories page.png) — names, counts and artwork come from the
    // official design verbatim; links map onto the matching industry slugs (seeded).
    $designCategories = [
        ['arts-decoration',         'Arts & Décoration',         'Arts & Decoration',          124, 'cat-icon-1.png',  'cat-side-1.png'],
        ['textile-mode',            'Mode & Textile',            'Fashion & Textile',          112, 'cat-icon-2.png',  'cat-side-2.png'],
        ['bois-sculpture',          'Bois & Sculpture',          'Wood & Sculpture',            96, 'cat-icon-3.png',  'cat-side-3.png'],
        ['poterie-ceramique',       'Poterie & Céramique',       'Pottery & Ceramics',          88, 'cat-icon-4.png',  'cat-side-4.png'],
        ['bijouterie-accessoires',  'Bijouterie & Accessoires',  'Jewelry & Accessories',       76, 'cat-icon-5.png',  'cat-side-5.png'],
        ['cuir-maroquinerie',       'Cuir & Maroquinerie',       'Leather & Leather Goods',     65, 'cat-icon-6.png',  'cat-side-6.png'],
        ['musique-instruments',     'Musique & Instruments',     'Music & Instruments',         58, 'cat-icon-7.png',  'cat-side-7.png'],
        ['produits-naturels',       'Produits Naturels',         'Natural Products',            73, 'cat-icon-8.png',  'cat-side-8.png'],
        ['agroalimentaire',         'Agroalimentaire',           'Agri-food',                   59, 'cat-icon-9.png',  'cat-side-9.png'],
        ['technologies-innovation', 'Technologies & Innovation', 'Technologies & Innovation',   42, 'cat-icon-10.png', 'cat-side-10.png'],
    ];

    $totalCount = 1245; // per the design ("1245" pill / "1 245 résultats trouvés")
    $fmt = fn ($n) => $isFr ? number_format($n, 0, ',', ' ') : number_format($n);

    $sorted = collect($designCategories);
    if (($sort ?? '') === 'name') {
        $sorted = $sorted->sortBy(fn ($c) => $isFr ? $c[1] : $c[2], SORT_NATURAL | SORT_FLAG_CASE)->values();
    } elseif (($sort ?? '') === 'products') {
        $sorted = $sorted->sortByDesc(fn ($c) => $c[3])->values();
    }

    $trustItems = $isFr ? [
        ['cat-trust-1.png', 'Authenticité garantie', "Tous nos produits sont\nauthentiques et certifiés."],
        ['cat-trust-2.png', 'Soutien aux artisans',  "Chaque achat soutient directement\nnos artisans locaux."],
        ['cat-trust-3.png', 'Paiement sécurisé',     "Transactions 100% sécurisées\net protégées."],
        ['cat-trust-4.png', 'Livraison fiable',      "Livraison rapide partout\ndans le monde."],
        ['cat-trust-5.png', 'Service client dédié',  "Une équipe à votre écoute\n7j/7."],
    ] : [
        ['cat-trust-1.png', 'Guaranteed authenticity', "All our products are\nauthentic and certified."],
        ['cat-trust-2.png', 'Support for artisans',    "Every purchase directly supports\nour local artisans."],
        ['cat-trust-3.png', 'Secure payment',          "100% secure and protected\ntransactions."],
        ['cat-trust-4.png', 'Reliable delivery',       "Fast delivery anywhere\nin the world."],
        ['cat-trust-5.png', 'Dedicated support',       "A team at your service\n7 days a week."],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Toutes les catégories de l\'artisanat camerounais — explorez nos créations par catégorie.' : 'All categories of Cameroonian crafts — explore our creations by category.' }}">
    <title>{{ $isFr ? 'Toutes les catégories — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'All categories — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream:  '#F7F2EC',
                        sand:   '#E7E1D4',
                        leaf:   '#164C28',
                        pine:   '#0E1D13',
                        deep:   '#0A331C',
                        gold:   '#C9942E',
                        goldlt: '#D9A439',
                        goldbt: '#E0A52F',
                        muted:  '#8A857A',
                        sage:   '#A8B8AC',
                    },
                    fontFamily: {
                        sans:  ['Poppins', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
    </style>
</head>
<body class="bg-[#FEFDFC] text-[#1D1B16] antialiased">

{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-header')

<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-5 pb-9">
    <div class="flex flex-col lg:flex-row gap-8 xl:gap-9 items-start">

        <!-- Sidebar -->
        <aside class="hidden lg:block w-[273px] shrink-0 space-y-5">
            <!-- Categories list -->
            <div class="rounded-xl shadow-sm border border-[#EFEDE7] overflow-hidden">
                <div class="flex items-center gap-3 bg-[#0A2C1D] px-4 h-[42px]">
                    <img src="{{ asset('images/landing/cat-sidebar-icon.png') }}" alt="" class="w-[22px] h-[20px]" aria-hidden="true">
                    <span class="text-[12px] font-bold tracking-[0.12em] text-white uppercase">{{ $isFr ? 'Catégories' : 'Categories' }}</span>
                </div>
                <nav class="bg-white py-1.5">
                    <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="relative flex items-center gap-3 px-4 py-[8px] bg-[#F7F4EA]">
                        <span class="absolute left-0 inset-y-0 w-[3px] bg-[#D9991F]"></span>
                        <img src="{{ asset('images/landing/cat-side-0.png') }}" alt="" class="w-[20px] h-[20px]" aria-hidden="true">
                        <span class="text-[13.5px] font-semibold text-[#14351F]">{{ $isFr ? 'Toutes les catégories' : 'All categories' }}</span>
                        <span class="ml-auto border border-[#E7E5DC] bg-white rounded-full px-2 py-0.5 text-[11px] text-[#6F6B60]">{{ $totalCount }}</span>
                    </a>
                    @foreach($designCategories as [$catSlug, $catFr, $catEn, $catCount, $catIcon, $catSideIcon])
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $catSlug]) }}" class="flex items-center gap-3 px-4 py-[8px] hover:bg-[#FAF8F2] transition-colors">
                        <img src="{{ asset('images/landing/' . $catSideIcon) }}" alt="" class="w-[20px] h-[20px]" aria-hidden="true">
                        <span class="text-[13.5px] text-[#26251F] truncate">{{ $isFr ? $catFr : $catEn }}</span>
                        <span class="ml-auto shrink-0 border border-[#E7E5DC] rounded-full px-2 py-0.5 text-[11px] text-[#6F6B60]">{{ $catCount }}</span>
                    </a>
                    @endforeach
                </nav>
            </div>

            <!-- Regions card -->
            <div class="relative rounded-xl border border-[#EFEDE7] bg-[#FBFAF6] shadow-sm overflow-hidden p-5">
                <img src="{{ asset('images/landing/cat-region-map.png') }}" alt="" class="absolute right-0 top-3 w-[68px] pointer-events-none select-none" aria-hidden="true">
                <h3 class="relative font-serif text-[19px] font-bold leading-snug text-[#1D1B16] max-w-[190px]">
                    {{ $isFr ? "Explorez l'artisanat par région" : 'Explore crafts by region' }}
                </h3>
                <p class="relative mt-2.5 text-[12.5px] text-[#6F6B60] leading-relaxed max-w-[190px]">
                    {{ $isFr ? 'Découvrez les trésors artisanaux des 10 régions du Cameroun.' : "Discover the craft treasures of Cameroon's 10 regions." }}
                </p>
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}"
                    class="relative mt-4 inline-flex items-center gap-2.5 bg-[#0E3022] hover:bg-leaf text-white text-[12.5px] font-semibold px-4 py-2.5 rounded-lg transition-colors">
                    {{ $isFr ? 'Voir les régions' : 'View regions' }}
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <!-- Help card -->
            <div class="rounded-xl border border-[#EFEDE7] bg-white shadow-sm p-5 flex items-start gap-3.5">
                <span class="w-12 h-12 shrink-0 rounded-lg bg-[#F2F0E7] flex items-center justify-center">
                    <i data-lucide="headset" class="w-6 h-6 text-[#1D4A2E]" style="stroke-width:1.5"></i>
                </span>
                <div>
                    <h3 class="text-[13.5px] font-bold text-[#1D1B16]">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</h3>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Notre équipe est à votre écoute' : 'Our team is here for you' }}</p>
                    <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-3 inline-flex items-center gap-2 bg-[#F1EFE9] hover:bg-[#EAE7DE] text-[12.5px] font-medium text-[#26251F] px-3.5 py-2 rounded-lg transition-colors">
                        {{ $isFr ? 'Nous contacter' : 'Contact us' }}
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="flex-1 min-w-0">
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 text-[13px]" aria-label="Breadcrumb">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#166534] hover:underline">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6]"></i>
                <span class="text-[#6F6B60]">{{ $isFr ? 'Catégories' : 'Categories' }}</span>
            </nav>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-5">
                <div>
                    <h1 class="font-serif text-[32px] sm:text-[38px] font-bold text-[#1D1B16] leading-tight">
                        {{ $isFr ? 'Toutes les catégories' : 'All categories' }}
                    </h1>
                    <div class="mt-2.5 h-[3.5px] w-[112px] bg-gradient-to-r from-[#D9991F] via-[#E9C989] to-transparent rounded-full"></div>
                    <p class="mt-4 text-[14.5px] text-[#55524A] leading-relaxed max-w-[440px]">
                        {{ $isFr
                            ? 'Découvrez la richesse et la diversité des créations artisanales camerounaises à travers nos différentes catégories.'
                            : 'Discover the richness and diversity of Cameroonian craft creations across our different categories.'
                        }}
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('industries.index') }}" class="flex items-center gap-2.5 h-[46px] bg-white border border-[#E5E3E0] rounded-xl px-4 shadow-sm">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <label for="sort" class="text-[13.5px] text-[#6F6B60] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                        <select id="sort" name="sort" onchange="this.form.submit()"
                            class="bg-transparent text-[14px] font-semibold text-[#1D1B16] focus:outline-none cursor-pointer pr-1">
                            <option value="" @selected(empty($sort))>{{ $isFr ? 'Populaires' : 'Popular' }}</option>
                            <option value="name" @selected(($sort ?? '') === 'name')>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                            <option value="products" @selected(($sort ?? '') === 'products')>{{ $isFr ? 'Produits' : 'Products' }}</option>
                        </select>
                    </form>
                    <div class="hidden sm:flex items-center h-[46px] bg-[#F4F2ED] rounded-xl p-1">
                        <button type="button" id="view-grid-btn" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}"
                            class="h-full px-3.5 rounded-lg bg-white shadow-sm flex items-center justify-center transition-colors">
                            <i data-lucide="layout-grid" class="w-[18px] h-[18px] text-[#14532D]" style="stroke-width:2.2"></i>
                        </button>
                        <button type="button" id="view-list-btn" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}"
                            class="h-full px-3.5 rounded-lg flex items-center justify-center transition-colors">
                            <i data-lucide="list" class="w-[18px] h-[18px] text-[#6F6B60]" style="stroke-width:2.2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <p class="mt-5 text-[13px] text-[#55524A]">
                {{ $fmt($totalCount) }} {{ $isFr ? 'résultats trouvés' : 'results found' }}
            </p>

            <!-- Category cards — grid view -->
            <div id="cards-grid" class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($sorted as [$catSlug, $catFr, $catEn, $catCount, $catIcon, $catSideIcon])
                <div class="bg-white border border-[#F1EFEA] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] px-3 pt-4 pb-5 text-center">
                    <img src="{{ asset('images/landing/' . $catIcon) }}" alt="" class="mx-auto w-[96px] h-[96px]" aria-hidden="true">
                    <h2 class="mt-4 text-[15px] font-bold text-[#1D1B16] leading-snug">
                        {{ $isFr ? $catFr : $catEn }}
                    </h2>
                    <div class="mx-auto mt-2 h-[2px] w-6 bg-[#E2B54D] rounded-full"></div>
                    <p class="mt-2 text-[13px] text-[#6F6B60]">{{ $catCount }} {{ $isFr ? 'produits' : 'products' }}</p>
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $catSlug]) }}"
                        class="mt-3.5 inline-flex items-center gap-2 text-[13.5px] font-semibold text-[#166534] hover:text-leaf transition-colors">
                        {{ $isFr ? 'Voir les produits' : 'View products' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Category cards — list view -->
            <div id="cards-list" class="hidden mt-4 space-y-3.5">
                @foreach($sorted as [$catSlug, $catFr, $catEn, $catCount, $catIcon, $catSideIcon])
                <div class="bg-white border border-[#F1EFEA] rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.04)] px-5 py-3.5 flex items-center gap-5">
                    <img src="{{ asset('images/landing/' . $catIcon) }}" alt="" class="w-[64px] h-[64px] shrink-0" aria-hidden="true">
                    <div class="min-w-0">
                        <h2 class="text-[15.5px] font-bold text-[#1D1B16] truncate">{{ $isFr ? $catFr : $catEn }}</h2>
                        <p class="mt-0.5 text-[13px] text-[#6F6B60]">{{ $catCount }} {{ $isFr ? 'produits' : 'products' }}</p>
                    </div>
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => $catSlug]) }}"
                        class="ml-auto shrink-0 inline-flex items-center gap-2 text-[13.5px] font-semibold text-[#166534] hover:text-leaf transition-colors">
                        {{ $isFr ? 'Voir les produits' : 'View products' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Trust strip -->
            <div class="mt-7 bg-[#F6F6EF] rounded-xl px-4 sm:px-7 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-y-6 lg:divide-x divide-[#E3DFC9]">
                @foreach($trustItems as [$trustIcon, $trustTitle, $trustDesc])
                <div class="flex items-start gap-2.5 lg:px-4 first:lg:pl-0 last:lg:pr-0">
                    <img src="{{ asset('images/landing/' . $trustIcon) }}" alt="" class="w-[52px] h-[52px] shrink-0 -mt-1" aria-hidden="true">
                    <div>
                        <h3 class="text-[13.5px] font-bold text-[#1D1B16]">{{ $trustTitle }}</h3>
                        <p class="mt-1 text-[12px] text-[#6F6B60] leading-relaxed whitespace-pre-line">{{ $trustDesc }}</p>
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
        gridBtn.classList.toggle('bg-white', isGrid);
        gridBtn.classList.toggle('shadow-sm', isGrid);
        listBtn.classList.toggle('bg-white', !isGrid);
        listBtn.classList.toggle('shadow-sm', !isGrid);
        gridBtn.querySelector('svg')?.setAttribute('class', 'w-[18px] h-[18px] ' + (isGrid ? 'text-[#14532D]' : 'text-[#6F6B60]'));
        listBtn.querySelector('svg')?.setAttribute('class', 'w-[18px] h-[18px] ' + (!isGrid ? 'text-[#14532D]' : 'text-[#6F6B60]'));
        try { localStorage.setItem('catView', view); } catch (e) {}
    }
    gridBtn.addEventListener('click', () => setView('grid'));
    listBtn.addEventListener('click', () => setView('list'));
    try { if (localStorage.getItem('catView') === 'list') setView('list'); } catch (e) {}
</script>
</body>
</html>
