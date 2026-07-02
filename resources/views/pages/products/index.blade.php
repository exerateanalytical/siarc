@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // The design's 11 sidebar categories, verbatim (label, count, icon, slug)
    $designSideCats = [
        ['',                          $isFr ? 'Toutes les catégories' : 'All categories',           '5248', 'product-side-0.png'],
        ['arts-decoration',           $isFr ? 'Arts & Décoration' : 'Arts & Decoration',            '642',  'product-side-1.png'],
        ['mode-textile',              $isFr ? 'Mode & Textile' : 'Fashion & Textile',               '918',  'product-side-2.png'],
        ['bois-sculpture',            $isFr ? 'Bois & Sculpture' : 'Wood & Sculpture',              '567',  'product-side-3.png'],
        ['poterie-ceramique',         $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics',         '487',  'product-side-4.png'],
        ['bijouterie-accessoires',    $isFr ? 'Bijouterie & Accessoires' : 'Jewellery & Accessories','713',  'product-side-5.png'],
        ['cuir-maroquinerie',         $isFr ? 'Cuir & Maroquinerie' : 'Leather & Leatherwork',      '398',  'product-side-6.png'],
        ['musique-instruments',       $isFr ? 'Musique & Instruments' : 'Music & Instruments',      '296',  'product-side-7.png'],
        ['produits-naturels',         $isFr ? 'Produits Naturels' : 'Natural Products',             '621',  'product-side-8.png'],
        ['agroalimentaire',           $isFr ? 'Agroalimentaire' : 'Agri-food',                      '834',  'product-side-9.png'],
        ['technologies-innovation',   $isFr ? 'Technologies & Innovation' : 'Technology & Innovation','172', 'product-side-10.png'],
    ];

    // The design's 12 products, verbatim (also seeded as real products — see DesignProductsSeeder)
    $designProducts = [
        ['slug' => 'panier-africain-tresse',    'img' => 'product-1.png',  'badge' => 'new',
         'name' => $isFr ? 'Panier africain tressé' : 'African woven basket',
         'cat' => $isFr ? 'Arts & Décoration' : 'Arts & Decoration',            'catSlug' => 'arts-decoration'],
        ['slug' => 'sculpture-en-bois-sawa',    'img' => 'product-2.png',  'badge' => 'best',
         'name' => $isFr ? 'Sculpture en bois Sawa' : 'Sawa wood sculpture',
         'cat' => $isFr ? 'Bois & Sculpture' : 'Wood & Sculpture',              'catSlug' => 'bois-sculpture'],
        ['slug' => 'sac-a-main-traditionnel',   'img' => 'product-3.png',  'badge' => null,
         'name' => $isFr ? 'Sac à main traditionnel' : 'Traditional handbag',
         'cat' => $isFr ? 'Mode & Textile' : 'Fashion & Textile',               'catSlug' => 'mode-textile'],
        ['slug' => 'vase-en-terre-cuite',       'img' => 'product-4.png',  'badge' => null,
         'name' => $isFr ? 'Vase en terre cuite' : 'Terracotta vase',
         'cat' => $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics',         'catSlug' => 'poterie-ceramique'],
        ['slug' => 'collier-perles-africaines', 'img' => 'product-5.png',  'badge' => 'new',
         'name' => $isFr ? 'Collier perles africaines' : 'African bead necklace',
         'cat' => $isFr ? 'Bijouterie & Accessoires' : 'Jewellery & Accessories','catSlug' => 'bijouterie-accessoires'],
        ['slug' => 'sac-en-cuir-veritable',     'img' => 'product-6.png',  'badge' => null,
         'name' => $isFr ? 'Sac en cuir véritable' : 'Genuine leather bag',
         'cat' => $isFr ? 'Cuir & Maroquinerie' : 'Leather & Leatherwork',      'catSlug' => 'cuir-maroquinerie'],
        ['slug' => 'djembe-traditionnel',       'img' => 'product-7.png',  'badge' => null,
         'name' => $isFr ? 'Djembé traditionnel' : 'Traditional djembe',
         'cat' => $isFr ? 'Musique & Instruments' : 'Music & Instruments',      'catSlug' => 'musique-instruments'],
        ['slug' => 'miel-naturel-du-cameroun',  'img' => 'product-8.png',  'badge' => null,
         'name' => $isFr ? 'Miel naturel du Cameroun' : 'Natural Cameroon honey',
         'cat' => $isFr ? 'Produits Naturels' : 'Natural Products',             'catSlug' => 'produits-naturels'],
        ['slug' => 'feves-de-cacao-premium',    'img' => 'product-9.png',  'badge' => null,
         'name' => $isFr ? 'Fèves de cacao premium' : 'Premium cocoa beans',
         'cat' => $isFr ? 'Agroalimentaire' : 'Agri-food',                      'catSlug' => 'agroalimentaire'],
        ['slug' => 'savon-naturel-artisanal',   'img' => 'product-10.png', 'badge' => null,
         'name' => $isFr ? 'Savon naturel artisanal' : 'Natural handmade soap',
         'cat' => $isFr ? 'Produits Naturels' : 'Natural Products',             'catSlug' => 'produits-naturels'],
        ['slug' => 'lampe-solaire-artisanale',  'img' => 'product-11.png', 'badge' => null,
         'name' => $isFr ? 'Lampe solaire artisanale' : 'Handcrafted solar lamp',
         'cat' => $isFr ? 'Technologies & Innovation' : 'Technology & Innovation','catSlug' => 'technologies-innovation'],
        ['slug' => 'beurre-de-karite-pur',      'img' => 'product-12.png', 'badge' => null,
         'name' => $isFr ? 'Beurre de karité pur' : 'Pure shea butter',
         'cat' => $isFr ? 'Produits Naturels' : 'Natural Products',             'catSlug' => 'produits-naturels'],
    ];

    // Category filter (?categorie=) narrows the static grid; sort works on the static array
    $activeCat = $categorie ?? '';
    $shownProducts = $activeCat === ''
        ? $designProducts
        : array_values(array_filter($designProducts, fn ($p) => $p['catSlug'] === $activeCat));

    if (($sort ?? 'recents') === 'name') {
        usort($shownProducts, fn ($a, $b) => strcoll($a['name'], $b['name']));
    }

    $trustItems = [
        ['product-trust-1.png', $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity', $isFr ? 'Produits 100% authentiques' : '100% authentic products'],
        ['product-trust-2.png', $isFr ? 'Soutien aux artisans' : 'Support for artisans',     $isFr ? 'Vous contribuez à l\'économie locale' : 'You support the local economy'],
        ['product-trust-3.png', $isFr ? 'Paiement sécurisé' : 'Secure payment',              $isFr ? 'Transactions protégées' : 'Protected transactions'],
        ['product-trust-4.png', $isFr ? 'Livraison fiable' : 'Reliable delivery',            $isFr ? "Partout au Cameroun et\nà l'international" : "Across Cameroon and\nworldwide"],
        ['product-trust-5.png', $isFr ? 'Service client dédié' : 'Dedicated customer service',$isFr ? 'Nous sommes à votre écoute' : 'We are here to listen'],
    ];

    $regions = ['Centre','Littoral','Ouest','Nord-Ouest','Sud-Ouest','Nord','Adamaoua','Est','Sud','Extrême-Nord'];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Annuaire des produits — Découvrez des milliers de créations authentiques fabriquées par nos artisans et entreprises.' : 'Product directory — Discover thousands of authentic creations made by our artisans and businesses.' }}">
    <title>{{ $isFr ? 'Annuaire des produits — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Product directory — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        leaf:   '#164C28',
                        deepfc: '#02301B',
                        gold:   '#E5A82E',
                        goldbt: '#F0B93E',
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
<body class="bg-[#FEFEFE] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-5 pb-10">
    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Sidebar -->
        <aside class="w-full lg:w-[265px] shrink-0">
            <!-- Categories -->
            <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
                <div class="flex items-center gap-2.5 bg-[#04291A] px-4 h-[38px]">
                    <i data-lucide="list" class="w-4 h-4 text-white"></i>
                    <span class="text-[12px] font-bold tracking-[0.1em] text-white uppercase">{{ $isFr ? 'Catégories' : 'Categories' }}</span>
                </div>
                <ul class="py-1.5">
                    @foreach($designSideCats as [$scSlug, $scLabel, $scCount, $scIcon])
                    @php $scActive = $scSlug === $activeCat; @endphp
                    <li>
                        <a href="{{ route('products.index', array_filter(['lang' => $lang, 'categorie' => $scSlug])) }}"
                            class="relative flex items-center gap-2.5 pl-4 pr-3 h-[30px] {{ $scActive ? 'bg-[#F9F4EC]' : 'hover:bg-[#FAFAF8]' }}">
                            @if($scActive)<span class="absolute left-0 top-0 bottom-0 w-[4px] bg-[#E7A320]"></span>@endif
                            <img src="{{ asset('images/landing/' . $scIcon) }}" alt="" class="w-[16px] h-[16px] object-contain shrink-0">
                            <span class="flex-1 truncate text-[12.5px] {{ $scActive ? 'font-semibold text-[#1D1B16]' : 'text-[#3A3A35]' }}">{{ $scLabel }}</span>
                            <span class="shrink-0 text-[10.5px] {{ $scActive ? 'bg-[#F8F1DE] border border-[#EAD9AC] text-[#7A5A12] font-semibold' : 'bg-white border border-[#E7E7E5] text-[#6F6B60]' }} rounded-full px-2 py-[1px]">{{ $scCount }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Filters -->
            <div class="mt-4 bg-white border border-[#ECECEA] rounded-xl p-4">
                <button type="button" class="w-full flex items-center justify-between" onclick="document.getElementById('filters-body').classList.toggle('hidden')">
                    <span class="flex items-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4 text-[#1D1B16]"></i>
                        <span class="text-[12px] font-bold tracking-[0.1em] text-[#1D1B16] uppercase">{{ $isFr ? 'Filtres' : 'Filters' }}</span>
                    </span>
                    <i data-lucide="chevron-up" class="w-4 h-4 text-[#6F6B60]"></i>
                </button>
                <form id="filters-body" method="GET" action="{{ route('products.index') }}" class="mt-4">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @if($activeCat !== '')<input type="hidden" name="categorie" value="{{ $activeCat }}">@endif

                    <p class="text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Région' : 'Region' }}</p>
                    <select name="region" class="mt-2 w-full h-[36px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12.5px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                        @foreach($regions as $regionName)
                        <option value="{{ \Illuminate\Support\Str::slug($regionName) }}" {{ ($region ?? '') === \Illuminate\Support\Str::slug($regionName) ? 'selected' : '' }}>{{ $regionName }}</option>
                        @endforeach
                    </select>

                    <p class="mt-5 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Type de vendeur' : 'Vendor type' }}</p>
                    <div class="mt-2.5 space-y-2.5">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="vendeur[]" value="artisan" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                            <span class="text-[12.5px] text-[#3A3A35]">Artisan&nbsp;&nbsp;(3421)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="vendeur[]" value="entreprise" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                            <span class="text-[12.5px] text-[#3A3A35]">{{ $isFr ? 'Entreprise' : 'Business' }}&nbsp;&nbsp;(1642)</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="vendeur[]" value="cooperative" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                            <span class="text-[12.5px] text-[#3A3A35]">{{ $isFr ? 'Coopérative' : 'Cooperative' }}&nbsp;&nbsp;(185)</span>
                        </label>
                    </div>

                    <p class="mt-5 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Disponibilité' : 'Availability' }}</p>
                    <label class="mt-2.5 flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="dispo" value="1" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                        <span class="text-[12.5px] text-[#3A3A35]">{{ $isFr ? 'Disponible maintenant' : 'Available now' }}</span>
                    </label>

                    <button type="submit" class="mt-6 w-full h-[40px] bg-[#02331C] hover:bg-leaf text-white text-[12.5px] font-semibold rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="filter" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Appliquer les filtres' : 'Apply filters' }}
                    </button>
                    <a href="{{ route('products.index', ['lang' => $lang]) }}" class="mt-2.5 w-full h-[36px] border border-[#D7D7D7] hover:bg-[#FAFAF8] text-[#3A3A35] text-[12.5px] font-medium rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Réinitialiser' : 'Reset' }}
                    </a>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <section class="flex-1 min-w-0 relative">
            <img src="{{ asset('images/landing/product-stamp.png') }}" alt="{{ $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity' }}"
                class="hidden xl:block absolute right-0 top-2 w-[84px] select-none pointer-events-none">

            <nav class="flex items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
                <span class="text-[#1D1B16]">{{ $isFr ? 'Annuaire des produits' : 'Product directory' }}</span>
            </nav>

            <div class="mt-3 flex flex-wrap items-end justify-between gap-4 xl:pr-[110px]">
                <div>
                    <h1 class="font-serif text-[30px] sm:text-[34px] leading-tight text-[#1D1B16] font-semibold">{{ $isFr ? 'Annuaire des produits' : 'Product directory' }}</h1>
                    <p class="mt-1.5 text-[13px] text-[#55524A]">{{ $isFr ? 'Découvrez des milliers de créations authentiques fabriquées par nos artisans et entreprises.' : 'Discover thousands of authentic creations made by our artisans and businesses.' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2 h-[40px] bg-white border border-[#E3E3E1] rounded-lg px-3.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        @if($activeCat !== '')<input type="hidden" name="categorie" value="{{ $activeCat }}">@endif
                        <label for="sort-select" class="text-[12.5px] text-[#55524A] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                        <select id="sort-select" name="sort" onchange="this.form.submit()"
                            class="text-[12.5px] font-medium text-[#1D1B16] bg-transparent focus:outline-none cursor-pointer">
                            <option value="recents" {{ ($sort ?? 'recents') === 'recents' ? 'selected' : '' }}>{{ $isFr ? 'Plus récents' : 'Most recent' }}</option>
                            <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                        </select>
                    </form>
                    <div class="flex items-center h-[40px] bg-white border border-[#E3E3E1] rounded-lg overflow-hidden">
                        <button type="button" id="view-grid" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}" class="w-[40px] h-full flex items-center justify-center bg-[#F2F5F2] text-[#0B3D28]">
                            <i data-lucide="layout-grid" class="w-[17px] h-[17px]"></i>
                        </button>
                        <span class="h-[22px] w-px bg-[#E3E3E1]"></span>
                        <button type="button" id="view-list" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}" class="w-[40px] h-full flex items-center justify-center text-[#8A857A]">
                            <i data-lucide="list" class="w-[17px] h-[17px]"></i>
                        </button>
                    </div>
                </div>
            </div>

            <p class="mt-5 flex items-center gap-2 text-[12.5px] font-semibold text-[#1D1B16]">
                <i data-lucide="package" class="w-[15px] h-[15px] text-[#0B3D28]"></i>
                {{ $isFr ? '5 248 produits disponibles' : '5,248 products available' }}
            </p>

            <!-- Product grid -->
            <div id="product-grid" class="mt-4 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
                @foreach($shownProducts as $product)
                <article class="prod-card bg-white border border-[#ECECEA] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <div class="prod-media relative">
                        <a href="{{ route('products.show', ['slug' => $product['slug'], 'lang' => $lang]) }}">
                            <img src="{{ asset('images/landing/' . $product['img']) }}" alt="{{ $product['name'] }}" class="prod-img w-full h-[168px] object-cover">
                        </a>
                        @if($product['badge'] === 'new')
                        <span class="absolute top-2.5 left-2.5 bg-[#0E3D26] text-white text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">{{ $isFr ? 'Nouveau' : 'New' }}</span>
                        @elseif($product['badge'] === 'best')
                        <span class="absolute top-2.5 left-2.5 bg-[#EFA912] text-white text-[9.5px] font-bold tracking-[0.06em] uppercase rounded-md px-2 py-1">Best-seller</span>
                        @endif
                        <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                            class="absolute top-2 right-2 w-8 h-8 bg-white/95 hover:bg-white rounded-full flex items-center justify-center text-[#1D1B16] transition-colors">
                            <i data-lucide="heart" class="w-[15px] h-[15px]"></i>
                        </a>
                    </div>
                    <div class="p-3">
                        <h3 class="text-[13px] font-bold text-[#1D1B16] truncate">
                            <a href="{{ route('products.show', ['slug' => $product['slug'], 'lang' => $lang]) }}" class="hover:text-leaf transition-colors">{{ $product['name'] }}</a>
                        </h3>
                        <p class="mt-1 text-[11.5px] text-[#6F6B60] truncate">{{ $product['cat'] }}</p>
                        <a href="{{ route('products.show', ['slug' => $product['slug'], 'lang' => $lang]) }}"
                            class="mt-3 w-full h-[34px] border border-[#E2E6E3] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center gap-2 text-[10.5px] font-bold tracking-[0.08em] text-[#26312B] uppercase transition-colors">
                            <i data-lucide="message-square" class="w-[13px] h-[13px]"></i>
                            {{ $isFr ? 'Enquérir' : 'Inquire' }}
                        </a>
                    </div>
                </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <nav class="mt-8 flex items-center justify-center gap-1.5" aria-label="Pagination">
                <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                <a href="{{ route('products.index', ['lang' => $lang]) }}" class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md" aria-current="page">1</a>
                @foreach([2, 3, 4, 5] as $pageNum)
                <a href="{{ route('products.index', ['lang' => $lang, 'page' => $pageNum]) }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pageNum }}</a>
                @endforeach
                <span class="w-8 h-8 flex items-center justify-center text-[#6F6B60] text-[12.5px]">…</span>
                <a href="{{ route('products.index', ['lang' => $lang, 'page' => 175]) }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">175</a>
                <a href="{{ route('products.index', ['lang' => $lang, 'page' => 2]) }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
            </nav>
        </section>
    </div>
</div>

<!-- Trust strip -->
<div class="bg-[#F8F6F2] border-t border-[#EEEBE4]">
    <div class="max-w-[1472px] mx-auto px-4 sm:px-6 py-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @foreach($trustItems as [$trustIcon, $trustTitle, $trustSub])
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/landing/' . $trustIcon) }}" alt="" class="w-[42px] h-[42px] object-contain shrink-0">
                <div class="leading-tight">
                    <p class="text-[12.5px] font-bold text-[#1D1B16]">{{ $trustTitle }}</p>
                    <p class="mt-0.5 text-[11.5px] text-[#6F6B60] whitespace-pre-line">{{ $trustSub }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    // Grid / list toggle (persisted like the categories page's catView)
    const grid = document.getElementById('product-grid');
    const btnGrid = document.getElementById('view-grid');
    const btnList = document.getElementById('view-list');
    function setView(view) {
        const isList = view === 'list';
        grid.classList.toggle('grid-cols-2', !isList);
        grid.classList.toggle('sm:grid-cols-3', !isList);
        grid.classList.toggle('xl:grid-cols-6', !isList);
        grid.classList.toggle('grid-cols-1', isList);
        grid.querySelectorAll('.prod-card').forEach(c => {
            c.classList.toggle('flex', isList);
            c.classList.toggle('items-stretch', isList);
        });
        grid.querySelectorAll('.prod-media').forEach(m => {
            m.classList.toggle('w-[220px]', isList);
            m.classList.toggle('shrink-0', isList);
        });
        grid.querySelectorAll('.prod-img').forEach(i => {
            i.classList.toggle('h-full', isList);
            i.classList.toggle('h-[168px]', !isList);
        });
        btnGrid.classList.toggle('bg-[#F2F5F2]', !isList);
        btnGrid.classList.toggle('text-[#0B3D28]', !isList);
        btnGrid.classList.toggle('text-[#8A857A]', isList);
        btnList.classList.toggle('bg-[#F2F5F2]', isList);
        btnList.classList.toggle('text-[#0B3D28]', isList);
        btnList.classList.toggle('text-[#8A857A]', !isList);
        localStorage.setItem('prodView', view);
    }
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));
    if (localStorage.getItem('prodView') === 'list') setView('list');
</script>
</body>
</html>
