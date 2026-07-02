@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Header options (directory-header partial)
    $dirIconVariant = 'vendors';
    $dirNavActive = 'businesses';
    $dirSearchPlaceholder = $isFr ? 'Rechercher un artisan, une entreprise, un produit...' : 'Search an artisan, a business, a product...';

    // The design's 8 vendors, verbatim (also seeded as real businesses — see DesignVendorsSeeder)
    $designVendors = [
        ['slug' => 'ceramiques-du-noun',     'img' => 'vendor-1.png', 'av' => 'vendor-av-1.png', 'plus' => '+12',
         'name' => 'Céramiques du Noun',
         'cat' => $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics',
         'loc' => 'Foumban, Ouest',
         'desc' => $isFr ? "Spécialistes de la poterie traditionnelle\net de la céramique artisanale." : "Specialists in traditional pottery\nand artisanal ceramics."],
        ['slug' => 'afrik-cuir-excellence',  'img' => 'vendor-2.png', 'av' => 'vendor-av-2.png', 'plus' => '+18',
         'name' => 'Afrik Cuir Excellence',
         'cat' => $isFr ? 'Cuir & Maroquinerie' : 'Leather & Leatherwork',
         'loc' => 'Douala, Littoral',
         'desc' => $isFr ? "Conception et fabrication d'articles\nen cuir de haute qualité." : "Design and manufacture of\nhigh-quality leather goods."],
        ['slug' => 'sawa-wood-art',          'img' => 'vendor-3.png', 'av' => 'vendor-av-3.png', 'plus' => '+9',
         'name' => 'Sawa Wood Art',
         'cat' => $isFr ? 'Bois & Sculpture' : 'Wood & Sculpture',
         'loc' => 'Kribi, Sud',
         'desc' => $isFr ? "Sculptures et objets d'art en bois\ninspirés de la tradition Sawa." : "Wooden sculptures and works of art\ninspired by the Sawa tradition."],
        ['slug' => 'tressage-bamenda',       'img' => 'vendor-4.png', 'av' => 'vendor-av-4.png', 'plus' => '+15',
         'name' => 'Tressage Bamenda',
         'cat' => $isFr ? 'Arts & Décoration' : 'Arts & Decoration',
         'loc' => 'Bamenda, Nord-Ouest',
         'desc' => $isFr ? "Créations en fibres naturelles\nfaites à la main par nos artisans." : "Natural-fibre creations\nhandmade by our artisans."],
        ['slug' => 'perles-du-sahel',        'img' => 'vendor-5.png', 'av' => 'vendor-av-5.png', 'plus' => '+8',
         'name' => 'Perles du Sahel',
         'cat' => $isFr ? 'Bijouterie & Accessoires' : 'Jewellery & Accessories',
         'loc' => 'Maroua, Extrême-Nord',
         'desc' => $isFr ? "Bijoux et accessoires fabriqués\navec des perles africaines." : "Jewellery and accessories made\nwith African beads."],
        ['slug' => 'tissus-racines',         'img' => 'vendor-6.png', 'av' => 'vendor-av-6.png', 'plus' => '+14',
         'name' => 'Tissus & Racines',
         'cat' => $isFr ? 'Mode & Textile' : 'Fashion & Textile',
         'loc' => 'Yaoundé, Centre',
         'desc' => $isFr ? "Tissus traditionnels et créations\ninspirées du patrimoine africain." : "Traditional fabrics and creations\ninspired by African heritage."],
        ['slug' => 'rythmes-dafrique',       'img' => 'vendor-7.png', 'av' => 'vendor-av-7.png', 'plus' => '+6',
         'name' => 'Rythmes d\'Afrique',
         'cat' => $isFr ? 'Musique & Instruments' : 'Music & Instruments',
         'loc' => 'Douala, Littoral',
         'desc' => $isFr ? "Instruments de musique traditionnels\net accessoires." : "Traditional musical instruments\nand accessories."],
        ['slug' => 'nature-bienfaits',       'img' => 'vendor-8.png', 'av' => 'vendor-av-8.png', 'plus' => '+10',
         'name' => 'Nature & Bienfaits',
         'cat' => $isFr ? 'Produits Naturels' : 'Natural Products',
         'loc' => 'Bafoussam, Ouest',
         'desc' => $isFr ? "Produits naturels et cosmétiques\nfaits à partir d'ingrédients locaux." : "Natural products and cosmetics\nmade from local ingredients."],
    ];

    $statItems = [
        ['users',        '2,548', $isFr ? 'Artisans & Entreprises' : 'Artisans & Businesses'],
        ['layout-grid',  '10+',   $isFr ? 'Catégories' : 'Categories'],
        ['map-pin',      '58',    $isFr ? 'Régions représentées' : 'Regions represented'],
        ['shield-check', '100%',  $isFr ? 'Authentiques' : 'Authentic'],
    ];

    $trustItems = [
        ['vendor-trust-1.png', $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity',
         $isFr ? "Tous nos artisans et entreprises\nsont vérifiés" : "All our artisans and businesses\nare verified"],
        ['vendor-trust-2.png', $isFr ? 'Soutien à l\'économie locale' : 'Support for the local economy',
         $isFr ? "Ensemble, valorisons le savoir-faire\ncamerounais" : "Together, let's promote Cameroonian\nknow-how"],
        ['vendor-trust-3.png', $isFr ? 'Paiement sécurisé' : 'Secure payment',
         $isFr ? "Transactions protégées\net sécurisées" : "Protected and secure\ntransactions"],
        ['vendor-trust-4.png', $isFr ? 'Mise en relation directe' : 'Direct connection',
         $isFr ? "Contactez directement artisans\net entreprises" : "Contact artisans and businesses\ndirectly"],
        ['vendor-trust-5.png', $isFr ? 'Visibilité & Promotion' : 'Visibility & Promotion',
         $isFr ? "Boostez votre activité et touchez\nplus de clients" : "Boost your activity and reach\nmore customers"],
    ];

    $searchCats = [
        ['arts-decoration',          $isFr ? 'Arts & Décoration' : 'Arts & Decoration'],
        ['textile-mode',             $isFr ? 'Mode & Textile' : 'Fashion & Textile'],
        ['bois-sculpture',           $isFr ? 'Bois & Sculpture' : 'Wood & Sculpture'],
        ['poterie-ceramique',        $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics'],
        ['bijouterie-accessoires',   $isFr ? 'Bijouterie & Accessoires' : 'Jewellery & Accessories'],
        ['cuir-maroquinerie',        $isFr ? 'Cuir & Maroquinerie' : 'Leather & Leatherwork'],
        ['musique-instruments',      $isFr ? 'Musique & Instruments' : 'Music & Instruments'],
        ['produits-naturels',        $isFr ? 'Produits Naturels' : 'Natural Products'],
        ['agroalimentaire',          $isFr ? 'Agroalimentaire' : 'Agri-food'],
        ['technologies-innovation',  $isFr ? 'Technologies & Innovation' : 'Technology & Innovation'],
    ];

    // Footer options (directory-footer partial)
    $dfShowHelp = true;
    $dfSocialStyle = 'outline';
    $dfShowLegalLinks = false;
    $dfNewsletterText = $isFr ? 'Recevez nos nouveautés et offres exclusives.' : 'Receive our new arrivals and exclusive offers.';
    $dfExplorer = [
        ($isFr ? 'Catégories' : 'Categories')  => route('industries.index', ['lang' => $lang]),
        'Artisans'                             => route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']),
        ($isFr ? 'Entreprises' : 'Businesses') => route('businesses.index', ['lang' => $lang]),
        ($isFr ? 'Régions' : 'Regions')        => route('businesses.index', ['lang' => $lang]),
        'Collections'                          => route('industries.index', ['lang' => $lang]),
    ];
    $dfRessources = [
        ($isFr ? 'Guide de l\'artisan' : 'Artisan guide')      => route('about'),
        'FAQ'                                                  => route('about'),
        'Blog'                                                 => route('events.index'),
        ($isFr ? 'Centre d\'aide' : 'Help center')             => route('support.index'),
        ($isFr ? 'Conditions d\'utilisation' : 'Terms of use') => route('terms'),
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Annuaire des Artisans & Entreprises — Trouvez des partenaires de confiance et soutenez l\'économie locale.' : 'Artisans & Businesses directory — Find trusted partners and support the local economy.' }}">
    <title>{{ $isFr ? 'Annuaire des Artisans & Entreprises — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Artisans & Businesses directory — National Virtual Gallery of Cameroonian Crafts' }}</title>

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

<main class="relative pb-16 sm:pb-0">
    <!-- Decorative right-margin pattern (design's page margin) -->
    <div class="absolute right-0 top-0 bottom-0 w-[72px] bg-repeat-y hidden 2xl:block pointer-events-none select-none" style="background-image:url('{{ asset('images/landing/vendor-margin.png') }}')" aria-hidden="true"></div>

<div class="relative max-w-[1440px] mx-auto px-4 sm:px-6 pt-4 pb-10">
    <nav class="flex items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16]">{{ $isFr ? 'Annuaire des artisans & entreprises' : 'Artisans & businesses directory' }}</span>
    </nav>

    <div class="mt-4 flex flex-col lg:flex-row gap-8">

        <!-- Sidebar -->
        <aside class="w-full lg:w-[250px] shrink-0">
            <!-- Search & filter -->
            <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
                <div class="flex items-center gap-2.5 bg-[#04291A] px-4 h-[38px]">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-white"></i>
                    <span class="text-[11.5px] font-bold tracking-[0.1em] text-white uppercase">{{ $isFr ? 'Rechercher & Filtrer' : 'Search & Filter' }}</span>
                </div>
                <form method="GET" action="{{ route('businesses.index') }}" class="p-4">
                    <input type="hidden" name="lang" value="{{ $lang }}">

                    <p class="text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Recherche' : 'Search' }}</p>
                    <div class="relative mt-2">
                        <input name="q" type="search" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Nom, entreprise, spécialité...' : 'Name, business, specialty...' }}"
                            class="w-full h-[36px] bg-white border border-[#E3E3E1] rounded-md pl-3 pr-8 text-[12px] text-[#3A3A35] placeholder-[#8A857A] focus:outline-none focus:border-gold">
                        <i data-lucide="search" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-[#6F6B60]"></i>
                    </div>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Catégorie' : 'Category' }}</p>
                    <select name="industry" class="mt-2 w-full h-[36px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les catégories' : 'All categories' }}</option>
                        @foreach($searchCats as [$scSlug, $scLabel])
                        <option value="{{ $scSlug }}" {{ request('industry') === $scSlug ? 'selected' : '' }}>{{ $scLabel }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Région' : 'Region' }}</p>
                    <select name="region" class="mt-2 w-full h-[36px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                        @foreach($regions as $regionRow)
                        <option value="{{ $regionRow->code }}" {{ request('region') === $regionRow->code ? 'selected' : '' }}>{{ $lang === 'fr' ? $regionRow->name_fr : ($regionRow->name_en ?? $regionRow->name_fr) }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Type de profil' : 'Profile type' }}</p>
                    <div class="mt-2.5 space-y-2.5">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" name="type[]" value="artisan" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                                <span class="text-[12px] text-[#3A3A35]">Artisan</span>
                            </span>
                            <span class="text-[11.5px] text-[#6F6B60]">(1,842)</span>
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" name="type[]" value="entreprise" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                                <span class="text-[12px] text-[#3A3A35]">{{ $isFr ? 'Entreprise' : 'Business' }}</span>
                            </span>
                            <span class="text-[11.5px] text-[#6F6B60]">(542)</span>
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" name="type[]" value="cooperative" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                                <span class="text-[12px] text-[#3A3A35]">{{ $isFr ? 'Coopérative' : 'Cooperative' }}</span>
                            </span>
                            <span class="text-[11.5px] text-[#6F6B60]">(164)</span>
                        </label>
                    </div>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Spécialité / Métier' : 'Specialty / Trade' }}</p>
                    <select name="specialite" class="mt-2 w-full h-[36px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les spécialités' : 'All specialties' }}</option>
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">Certification</p>
                    <label class="mt-2.5 flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="tier" value="certified" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                        <span class="text-[12px] text-[#3A3A35]">{{ $isFr ? 'Artisanat Authentique' : 'Authentic Craftsmanship' }}</span>
                        <img src="{{ asset('images/landing/vendor-cert-icon.png') }}" alt="" class="w-[15px] h-[14px] object-contain">
                    </label>

                    <button type="submit" class="mt-5 w-full h-[38px] bg-[#02331C] hover:bg-leaf text-white text-[12.5px] font-semibold rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="filter" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Appliquer les filtres' : 'Apply filters' }}
                    </button>
                </form>
            </div>

            <!-- Join CTA -->
            <div class="relative mt-4 bg-[#02301B] rounded-xl overflow-hidden p-4 pr-[72px]">
                <img src="{{ asset('images/landing/vendor-cta-mask.png') }}" alt="" class="absolute right-0 top-0 h-full w-auto object-cover" aria-hidden="true">
                <p class="text-[13px] font-bold text-white leading-snug">{{ $isFr ? 'Vous êtes artisan ou entreprise ?' : 'Are you an artisan or a business?' }}</p>
                <p class="mt-2 text-[11px] text-[#B9C4BC] leading-relaxed">{{ $isFr ? 'Rejoignez notre plateforme pour promouvoir vos créations et développer votre activité.' : 'Join our platform to promote your creations and grow your activity.' }}</p>
                <a href="/inscription?lang={{ $lang }}" class="relative z-10 mt-4 inline-flex items-center gap-2 bg-[#EBA51D] hover:bg-goldbt text-[#3A2E08] text-[12px] font-semibold px-3.5 py-2.5 rounded-lg transition-colors">
                    {{ $isFr ? 'S\'inscrire maintenant' : 'Sign up now' }}
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </aside>

        <!-- Main -->
        <section class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-[560px]">
                    <h1 class="font-serif text-[28px] sm:text-[32px] leading-tight text-[#12432A] font-semibold">{{ $isFr ? 'Annuaire des Artisans & Entreprises' : 'Artisans & Businesses Directory' }}</h1>
                    <p class="mt-1.5 text-[13.5px] font-semibold text-[#C9862B]">{{ $isFr ? 'Trouvez des partenaires de confiance et soutenez l\'économie locale' : 'Find trusted partners and support the local economy' }}</p>
                    <p class="mt-3 text-[13px] text-[#55524A] leading-relaxed">
                        {{ $isFr ? 'Parcourez notre répertoire d\'artisans et d\'entreprises authentiques du Cameroun.' : 'Browse our directory of authentic Cameroonian artisans and businesses.' }}<br>
                        {{ $isFr ? 'Entre en contact direct, collaborez et soutenez le savoir-faire local.' : 'Get in direct contact, collaborate and support local know-how.' }}
                    </p>
                </div>
                <img src="{{ asset('images/landing/vendor-hero-map.png') }}" alt="" class="hidden xl:block w-[96px] shrink-0 mt-2" aria-hidden="true">
                <div class="bg-white border border-[#ECECEA] rounded-xl px-5 py-4 shrink-0">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 sm:divide-x sm:divide-[#EFEDEA]">
                        @foreach($statItems as $statIdx => [$statIcon, $statValue, $statLabel])
                        <div class="{{ $statIdx > 0 ? 'sm:pl-6' : '' }}">
                            <i data-lucide="{{ $statIcon }}" class="w-[22px] h-[22px] text-[#E08A21]" stroke-width="1.8"></i>
                            <p class="mt-2.5 text-[15px] font-bold text-[#1D1B16] leading-none">{{ $statValue }}</p>
                            <p class="mt-1.5 text-[10.5px] text-[#6F6B60] whitespace-nowrap">{{ $statLabel }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <p class="text-[13px] font-semibold text-[#1D1B16]">{{ $isFr ? '2,548 artisans & entreprises trouvés' : '2,548 artisans & businesses found' }}</p>
                <div class="flex items-center gap-3">
                    <form method="GET" action="{{ route('businesses.index') }}" class="flex items-center gap-2 h-[38px] bg-white border border-[#E3E3E1] rounded-lg px-3.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <label for="sort-select" class="text-[12px] text-[#55524A] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                        <select id="sort-select" name="sort" onchange="this.form.submit()"
                            class="text-[12px] font-medium text-[#1D1B16] bg-transparent focus:outline-none cursor-pointer">
                            <option value="recents" {{ request('sort', 'recents') === 'recents' ? 'selected' : '' }}>{{ $isFr ? 'Plus récents' : 'Most recent' }}</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                        </select>
                    </form>
                    <div class="flex items-center h-[38px] bg-white border border-[#E3E3E1] rounded-lg overflow-hidden">
                        <button type="button" id="view-grid" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}" class="w-[38px] h-full flex items-center justify-center bg-[#F2F5F2] text-[#0B3D28]">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        </button>
                        <span class="h-[20px] w-px bg-[#E3E3E1]"></span>
                        <button type="button" id="view-list" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}" class="w-[38px] h-full flex items-center justify-center text-[#8A857A]">
                            <i data-lucide="list" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vendor grid -->
            <div id="vendor-grid" class="mt-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                @php
                    $shownVendors = $designVendors;
                    if (request('sort') === 'name') {
                        usort($shownVendors, fn ($a, $b) => strcoll($a['name'], $b['name']));
                    }
                @endphp
                @foreach($shownVendors as $vendor)
                <article class="vend-card bg-white border border-[#ECECEA] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <div class="vend-media relative">
                        <a href="{{ route('businesses.show', ['slug' => $vendor['slug'], 'lang' => $lang]) }}">
                            <img src="{{ asset('images/landing/' . $vendor['img']) }}" alt="{{ $vendor['name'] }}" class="vend-img w-full h-[140px] object-cover">
                        </a>
                        {{-- The heart is baked into the artwork; this transparent link makes it functional --}}
                        <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                            class="absolute top-1 right-1 w-10 h-10 rounded-full"></a>
                    </div>
                    <div class="p-3.5">
                        <h3 class="flex items-center gap-1.5 text-[13.5px] font-bold text-[#1D1B16]">
                            <a href="{{ route('businesses.show', ['slug' => $vendor['slug'], 'lang' => $lang]) }}" class="truncate hover:text-leaf transition-colors">{{ $vendor['name'] }}</a>
                            <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0" aria-label="{{ $isFr ? 'Vérifié' : 'Verified' }}">
                                <circle cx="8" cy="8" r="8" fill="#17A34A"/>
                                <path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </h3>
                        <p class="mt-1 text-[11.5px] text-[#55524A]">{{ $vendor['cat'] }}</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#6F6B60]">
                            <i data-lucide="map-pin" class="w-[12px] h-[12px]"></i>
                            {{ $vendor['loc'] }}
                        </p>
                        <p class="mt-2 text-[11.5px] text-[#55524A] leading-relaxed whitespace-pre-line">{{ $vendor['desc'] }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <img src="{{ asset('images/landing/' . $vendor['av']) }}" alt="" class="h-[26px] w-auto">
                            <span class="text-[11px] font-semibold text-[#55524A]">{{ $vendor['plus'] }}</span>
                        </div>
                        <div class="mt-3.5 flex items-center gap-2">
                            <a href="{{ route('businesses.show', ['slug' => $vendor['slug'], 'lang' => $lang]) }}"
                                class="flex-1 h-[34px] border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center text-[12px] font-semibold text-[#1D1B16] transition-colors">
                                {{ $isFr ? 'Voir le profil' : 'View profile' }}
                            </a>
                            <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Envoyer un message' : 'Send a message' }}"
                                class="w-[38px] h-[34px] border border-[#DBDFDC] hover:border-leaf hover:text-leaf rounded-lg flex items-center justify-center text-[#55524A] transition-colors">
                                <i data-lucide="message-square" class="w-[15px] h-[15px]"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <nav class="mt-8 flex items-center justify-center gap-1.5" aria-label="Pagination">
                <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md" aria-current="page">1</a>
                @foreach([2, 3, 4, 5] as $pageNum)
                <a href="{{ route('businesses.index', ['lang' => $lang, 'page' => $pageNum]) }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pageNum }}</a>
                @endforeach
                <span class="w-8 h-8 flex items-center justify-center text-[#6F6B60] text-[12.5px]">…</span>
                <a href="{{ route('businesses.index', ['lang' => $lang, 'page' => 64]) }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">64</a>
                <a href="{{ route('businesses.index', ['lang' => $lang, 'page' => 2]) }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
            </nav>
        </section>
    </div>
</div>

<!-- Trust strip -->
<div class="relative bg-[#FAF7F3] border-t border-[#EEEBE4]">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @foreach($trustItems as [$trustIcon, $trustTitle, $trustSub])
            <div class="flex items-start gap-3">
                <img src="{{ asset('images/landing/' . $trustIcon) }}" alt="" class="w-[40px] h-[42px] object-contain shrink-0">
                <div class="leading-tight">
                    <p class="text-[12px] font-bold text-[#1D1B16]">{{ $trustTitle }}</p>
                    <p class="mt-1 text-[11px] text-[#6F6B60] whitespace-pre-line leading-relaxed">{{ $trustSub }}</p>
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

    // Grid / list toggle
    const grid = document.getElementById('vendor-grid');
    const btnGrid = document.getElementById('view-grid');
    const btnList = document.getElementById('view-list');
    function setView(view) {
        const isList = view === 'list';
        grid.classList.toggle('sm:grid-cols-2', !isList);
        grid.classList.toggle('xl:grid-cols-4', !isList);
        grid.querySelectorAll('.vend-card').forEach(c => {
            c.classList.toggle('sm:flex', isList);
            c.classList.toggle('items-stretch', isList);
        });
        grid.querySelectorAll('.vend-media').forEach(m => {
            m.classList.toggle('sm:w-[260px]', isList);
            m.classList.toggle('shrink-0', isList);
        });
        grid.querySelectorAll('.vend-img').forEach(i => {
            i.classList.toggle('sm:h-full', isList);
        });
        btnGrid.classList.toggle('bg-[#F2F5F2]', !isList);
        btnGrid.classList.toggle('text-[#0B3D28]', !isList);
        btnGrid.classList.toggle('text-[#8A857A]', isList);
        btnList.classList.toggle('bg-[#F2F5F2]', isList);
        btnList.classList.toggle('text-[#0B3D28]', isList);
        btnList.classList.toggle('text-[#8A857A]', !isList);
        localStorage.setItem('vendView', view);
    }
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));
    if (localStorage.getItem('vendView') === 'list') setView('list');
</script>
</body>
</html>
