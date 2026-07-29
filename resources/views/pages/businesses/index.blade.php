@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Header options (directory-header partial)
    $dirIconVariant = 'vendors';
    $dirNavActive = 'businesses';
    $dirSearchPlaceholder = $isFr ? 'Rechercher un artisan, une entreprise, un produit...' : 'Search an artisan, a business, a product...';

    // Vendor cards come from the real, filtered, paginated $businesses query.
    // Design crops (vendor-N.png / vendor-av-N.png) remain the fallback artwork
    // for businesses without an uploaded cover.
    $designVendors = $businesses->map(function ($b) use ($isFr) {
        static $i = 0;
        $i++;
        return [
            'slug'     => $b->slug,
            'img'      => $b->cover_image ? asset('storage/' . $b->cover_image) : asset('images/landing/vendor-' . (($i - 1) % 8 + 1) . '.png'),
            'av'       => 'vendor-av-' . (($i - 1) % 8 + 1) . '.png',
            'plus'     => '+' . (int) ($b->products_count ?? 0),
            'name'     => $isFr ? $b->name_fr : ($b->name_en ?? $b->name_fr),
            'cat'      => $b->industry ? ($isFr ? $b->industry->name_fr : ($b->industry->name_en ?? $b->industry->name_fr)) : '',
            'loc'      => trim(($b->city->name_fr ?? '') . ($b->city && $b->region ? ', ' : '') . ($b->region->name_fr ?? ''), ', ') ?: 'Cameroun',
            'desc'     => $isFr ? ($b->tagline_fr ?? '') : ($b->tagline_en ?? $b->tagline_fr ?? ''),
            'verified' => in_array($b->verification_tier, ['verified', 'certified'], true),
        ];
    })->all();

    // Each figure counts published businesses — the same population the listing
    // below shows. See FrontendController::businessIndex for what each query is.
    // The old "413+" and "100% Authentiques" were a fabricated suffix and a
    // fabricated ratio; both now come from the database or would not be here.
    $fmtStat = fn ($n) => $isFr ? number_format($n, 0, ',', ' ') : number_format($n);
    $statItems = [
        ['users',        $fmtStat($dirStats['businesses']), $isFr ? 'Artisans & Entreprises' : 'Artisans & Businesses'],
        ['layout-grid',  $fmtStat($dirStats['categories']), $isFr ? 'Catégories' : 'Categories'],
        ['map-pin',      $fmtStat($dirStats['regions']),    $isFr ? 'Régions représentées' : 'Regions represented'],
        ['shield-check', $fmtStat($dirStats['verified']),   $isFr ? 'Profils vérifiés' : 'Verified profiles'],
    ];

    $trustItems = [
        ['shield-check', $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity',
         $isFr ? "Tous nos artisans et entreprises\nsont vérifiés" : "All our artisans and businesses\nare verified"],
        ['user-round', $isFr ? 'Soutien à l\'économie locale' : 'Support for the local economy',
         $isFr ? "Ensemble, valorisons le savoir-faire\ncamerounais" : "Together, let's promote Cameroonian\nknow-how"],
        /* Was "Paiement sécurisé". The platform takes no payment for a sale and
           is not a party to it (config/legal.php) -- and the footer of this very
           page says so, so the two contradicted each other one screen apart.
           What replaces it is both true and the better promise: the money
           reaches the maker whole. */
        ['hand-coins', $isFr ? "L'artisan est payé directement" : 'The artisan is paid directly',
         $isFr ? "Vous réglez l'artisan,\nsans intermédiaire" : "You pay the artisan,\nwith no middleman"],
        ['message-circle', $isFr ? 'Mise en relation directe' : 'Direct connection',
         $isFr ? "Contactez directement artisans\net entreprises" : "Contact artisans and businesses\ndirectly"],
        ['megaphone', $isFr ? 'Visibilité & Promotion' : 'Visibility & Promotion',
         $isFr ? "Boostez votre activité et touchez\nplus de clients" : "Boost your activity and reach\nmore customers"],
    ];

    // Category filter options — the illustrated official categories (same tiles
    // as the landing/products), so the dropdown stays curated, not every trade.
    $searchCats = $industries
        ->filter(fn ($ind) => $ind->image_icon)
        ->sortBy('sort_order')
        ->map(fn ($ind) => [$ind->slug, $isFr ? $ind->name_fr : ($ind->name_en ?? $ind->name_fr)])
        ->values()->all();

    // Footer options (directory-footer partial)
    $dfShowHelp = true;
    $dfSocialStyle = 'outline';
    $dfShowLegalLinks = false;
    $dfNewsletterText = $isFr ? 'Recevez nos nouveautés et offres exclusives.' : 'Receive our new arrivals and exclusive offers.';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Annuaire des Artisans & Entreprises — Trouvez des partenaires de confiance et soutenez l\'économie locale.' : 'Artisans & Businesses directory — Find trusted partners and support the local economy.' }}">
    <title>{{ $isFr ? 'Annuaire des Artisans & Entreprises — Artisan Hub 237' : 'Artisans & Businesses directory — Artisan Hub 237' }}</title>

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
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    @php
        $title = $isFr ? 'Annuaire des Artisans & Entreprises — Artisan Hub 237' : 'Artisans & Businesses directory — Artisan Hub 237';
        $description = $isFr
            ? 'Annuaire des Artisans & Entreprises — Trouvez des partenaires de confiance et soutenez l\'économie locale.'
            : 'Artisans & Businesses directory — Find trusted partners and support the local economy.';
    @endphp
    @include('pages.partials.seo-head')
</head>
<body class="bg-[#FEFEFE] dark:bg-[#12150F] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<main class="relative pb-16 sm:pb-0">
    <!-- Decorative right-margin pattern (design's page margin) -->
    <div class="absolute right-0 top-0 bottom-0 w-[72px] bg-repeat-y hidden 2xl:block pointer-events-none select-none" style="background-image:url('{{ asset('images/landing/vendor-margin.png') }}')" aria-hidden="true"></div>

<div class="relative max-w-[1440px] mx-auto px-4 sm:px-6 pt-4 pb-10">
    <nav class="flex items-center gap-2 text-[14px] md:text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Annuaire des artisans & entreprises' : 'Artisans & businesses directory' }}</span>
    </nav>

    <div class="mt-4 flex flex-col lg:flex-row gap-8">

        <!-- Sidebar -->
        <aside class="hidden lg:block w-full lg:w-[250px] shrink-0">
            <!-- Browse by official sector -->
            <div class="mb-5">@include('pages.partials.sector-browser')</div>
            <!-- Search & filter -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden">
                <div class="flex items-center gap-2.5 bg-[#04291A] px-4 h-[38px]">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4 text-white"></i>
                    <span class="text-[13px] md:text-[11.5px] font-bold tracking-[0.1em] text-white uppercase">{{ $isFr ? 'Rechercher & Filtrer' : 'Search & Filter' }}</span>
                </div>
                <form method="GET" action="{{ route('businesses.index') }}" class="p-4">
                    <input type="hidden" name="lang" value="{{ $lang }}">

                    <p class="text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Recherche' : 'Search' }}</p>
                    <div class="relative mt-2">
                        <input name="q" type="search" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Nom, entreprise, spécialité...' : 'Name, business, specialty...' }}"
                            class="ui-field ui-field--sm pr-8">
                        <i data-lucide="search" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-[#6F6B60] dark:text-[#868778]"></i>
                    </div>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Catégorie' : 'Category' }}</p>
                    <select name="industry" class="ui-field ui-select ui-field--sm mt-2">
                        <option value="">{{ $isFr ? 'Toutes les catégories' : 'All categories' }}</option>
                        @foreach($searchCats as [$scSlug, $scLabel])
                        <option value="{{ $scSlug }}" {{ request('industry') === $scSlug ? 'selected' : '' }}>{{ $scLabel }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Région' : 'Region' }}</p>
                    <select name="region" class="ui-field ui-select ui-field--sm mt-2">
                        <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                        @foreach($regions as $regionRow)
                        <option value="{{ $regionRow->code }}" {{ request('region') === $regionRow->code ? 'selected' : '' }}>{{ $lang === 'fr' ? $regionRow->name_fr : ($regionRow->name_en ?? $regionRow->name_fr) }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Type de profil' : 'Profile type' }}</p>
                    <div class="mt-2.5 space-y-2.5">
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" name="type[]" value="artisan" class="ui-check">
                                <span class="text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">Artisan</span>
                            </span>
                            <span class="text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">({{ $vendorTypeCounts['artisan'] ?? 0 }})</span>
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" name="type[]" value="entreprise" class="ui-check">
                                <span class="text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Entreprise' : 'Business' }}</span>
                            </span>
                            <span class="text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">({{ $vendorTypeCounts['entreprise'] ?? 0 }})</span>
                        </label>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="flex items-center gap-2.5">
                                <input type="checkbox" name="type[]" value="cooperative" class="ui-check">
                                <span class="text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Coopérative' : 'Cooperative' }}</span>
                            </span>
                            <span class="text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">({{ $vendorTypeCounts['cooperative'] ?? 0 }})</span>
                        </label>
                    </div>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Spécialité / Métier' : 'Specialty / Trade' }}</p>
                    <select name="specialite" class="ui-field ui-select ui-field--sm mt-2">
                        <option value="">{{ $isFr ? 'Toutes les spécialités' : 'All specialties' }}</option>
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">Certification</p>
                    <label class="mt-2.5 flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="tier" value="certified" class="ui-check">
                        <span class="text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">{{ $isFr ? 'Artisanat Authentique' : 'Authentic Craftsmanship' }}</span>
                        <i data-lucide="award" class="w-[15px] h-[15px] text-[#C08A16] dark:text-[#EDB33A] shrink-0" stroke-width="1.8" aria-hidden="true"></i>
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
                <p class="mt-2 text-[13px] md:text-[11px] text-[#B9C4BC] leading-relaxed">{{ $isFr ? 'Rejoignez notre plateforme pour promouvoir vos créations et développer votre activité.' : 'Join our platform to promote your creations and grow your activity.' }}</p>
                <a href="{{ route('inscription', ['lang' => $lang]) }}" class="relative z-10 mt-4 inline-flex items-center gap-2 bg-[#EBA51D] hover:bg-goldbt text-[#3A2E08] text-[12px] font-semibold px-3.5 py-2.5 rounded-lg transition-colors">
                    {{ $isFr ? 'S\'inscrire maintenant' : 'Sign up now' }}
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </aside>

        <!-- Main -->
        <section class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-[560px]">
                    <h1 class="font-serif text-[28px] sm:text-[32px] leading-tight text-[#12432A] dark:text-[#339B56] font-semibold">{{ $isFr ? 'Annuaire des Artisans & Entreprises' : 'Artisans & Businesses Directory' }}</h1>
                    <p class="mt-1.5 text-[14px] md:text-[13.5px] font-semibold text-[#C9862B]">{{ $isFr ? 'Trouvez des partenaires de confiance et soutenez l\'économie locale' : 'Find trusted partners and support the local economy' }}</p>
                    <p class="mt-3 text-[14px] md:text-[13px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">
                        {{ $isFr ? 'Parcourez notre répertoire d\'artisans et d\'entreprises authentiques du Cameroun.' : 'Browse our directory of authentic Cameroonian artisans and businesses.' }}<br>
                        {{ $isFr ? 'Entre en contact direct, collaborez et soutenez le savoir-faire local.' : 'Get in direct contact, collaborate and support local know-how.' }}
                    </p>
                </div>
                <img src="{{ asset('images/landing/vendor-hero-map.png') }}" alt="" class="hidden xl:block w-[96px] shrink-0 mt-2" aria-hidden="true">
                {{-- Stats strip. `shrink-0` plus `whitespace-nowrap` labels used to
                     force this card wider than a 360px viewport and drag the whole
                     page into horizontal scroll: "Artisans & Entreprises" cannot fit
                     on one line in a half-column. The card is now full-width and the
                     labels wrap; it only becomes an auto-width sidecar at `xl`.

                     `xl`, not `lg`: at exactly 1024 the `lg` sidebar is already
                     taking its column, so an auto-width four-across strip in
                     what is left measured 1038px inside a 1024px viewport and
                     `shrink-0` forbade it from giving the 14px back. The strip
                     goes four-across only once the artwork's own 1280 layout is
                     in reach; 1024 keeps the two-by-two card. --}}
                <div class="w-full xl:w-auto xl:shrink-0 min-w-0 bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl px-4 sm:px-5 py-4">
                    <div class="grid grid-cols-2 xl:grid-cols-4 gap-x-4 sm:gap-x-6 gap-y-4 xl:divide-x xl:divide-[#EFEDEA] xl:dark:divide-[#262B21]">
                        @foreach($statItems as $statIdx => [$statIcon, $statValue, $statLabel])
                        <div class="min-w-0 {{ $statIdx > 0 ? 'xl:pl-6' : '' }}">
                            <i data-lucide="{{ $statIcon }}" class="w-[22px] h-[22px] text-[#E08A21]" stroke-width="1.8"></i>
                            <p class="mt-2.5 text-[16px] font-bold text-[#1D1B16] dark:text-[#F3EFE7] leading-none">{{ $statValue }}</p>
                            <p class="mt-1.5 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778] leading-snug">{{ $statLabel }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                {{-- Was a hardcoded "2,548", which contradicted both the stats strip
                     above and the page of results below. This is the real total of
                     the filtered query the visitor is actually looking at. --}}
                <p class="text-[14px] md:text-[13px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">
                    {{ $fmtStat($businesses->total()) }}
                    {{ $isFr ? ($businesses->total() === 1 ? 'artisan ou entreprise trouvé' : 'artisans & entreprises trouvés') : ($businesses->total() === 1 ? 'artisan or business found' : 'artisans & businesses found') }}
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- 44px on phones, the drawn 38px from `md` up. The sort
                         box and the two view toggles are the only controls in
                         this strip and all three measured 36–38px, under the
                         contract's tap floor. docs/RESPONSIVE-CONTRACT.md 4.
                         flex-wrap on this holder (not just its flex-wrap parent)
                         because at 320px the sort form + toggle group together
                         are wider than the available row and, without their own
                         wrap point, forced the whole page to scroll sideways
                         instead of dropping the toggle group to its own line. --}}
                    <form method="GET" action="{{ route('businesses.index') }}" class="flex items-center gap-2 h-[46px] md:h-[38px] bg-white dark:bg-[#12150F] border border-[#E3E3E1] dark:border-[#262B21] rounded-lg px-3.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <label for="sort-select" class="text-[14px] md:text-[12px] text-[#55524A] dark:text-[#B4B5A6] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                        <select id="sort-select" name="sort" onchange="this.form.submit()"
                            class="ui-field-bare font-medium cursor-pointer w-auto">
                            <option value="recents" {{ request('sort', 'recents') === 'recents' ? 'selected' : '' }}>{{ $isFr ? 'Plus récents' : 'Most recent' }}</option>
                            <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                        </select>
                    </form>
                    <div class="flex items-center h-[46px] md:h-[38px] bg-white dark:bg-[#12150F] border border-[#E3E3E1] dark:border-[#262B21] rounded-lg overflow-hidden">
                        <button type="button" id="view-grid" aria-label="{{ $isFr ? 'Vue grille' : 'Grid view' }}" class="w-[44px] md:w-[38px] h-full flex items-center justify-center bg-[#F2F5F2] dark:bg-[#0A0C09] text-[#0B3D28] dark:text-[#339B56]">
                            <i data-lucide="layout-grid" class="w-4 h-4"></i>
                        </button>
                        <span class="h-[20px] w-px bg-[#E3E3E1] dark:bg-[#0A0C09]"></span>
                        <button type="button" id="view-list" aria-label="{{ $isFr ? 'Vue liste' : 'List view' }}" class="w-[44px] md:w-[38px] h-full flex items-center justify-center text-[#8A857A] dark:text-[#868778]">
                            <i data-lucide="list" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Vendor grid -->
            {{-- Two up on phones, matching the product directory. One card per row
                 wasted most of the screen and pushed the second shop below the
                 fold on every device. --}}
            <div id="vendor-grid" class="mt-4 grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-5">
                @forelse($designVendors as $vendor)
                <article class="vend-card bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden shadow-[0_1px_2px_rgba(0,0,0,0.04)]">
                    <div class="vend-media relative">
                        <a href="{{ route('businesses.show', ['slug' => $vendor['slug'], 'lang' => $lang]) }}">
                            <img src="{{ $vendor['img'] }}" alt="{{ $vendor['name'] }}" class="vend-img w-full h-[140px] object-cover">
                        </a>
                        {{-- The heart is baked into the artwork; this transparent link makes it functional --}}
                        <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Ajouter aux favoris' : 'Save to favorites' }}"
                            class="absolute top-1 right-1 w-11 h-11 md:w-10 md:h-10 rounded-full"></a>
                    </div>
                    <div class="p-3.5">
                        <h3 class="flex items-center gap-1.5 text-[14px] md:text-[13.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">
                            <a href="{{ route('businesses.show', ['slug' => $vendor['slug'], 'lang' => $lang]) }}" class="ui-tap-inset truncate hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $vendor['name'] }}</a>
                            @if($vendor['verified'])
                            <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0" aria-label="{{ $isFr ? 'Vérifié' : 'Verified' }}">
                                <circle cx="8" cy="8" r="8" fill="#17A34A"/>
                                <path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            @endif
                        </h3>
                        <p class="mt-1 text-[14px] md:text-[11.5px] text-[#55524A] dark:text-[#B4B5A6]">{{ $vendor['cat'] }}</p>
                        <p class="mt-1.5 flex items-center gap-1.5 text-[14px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]">
                            <i data-lucide="map-pin" class="w-[12px] h-[12px]"></i>
                            {{ $vendor['loc'] }}
                        </p>
                        <p class="mt-2 text-[14px] md:text-[11.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed whitespace-pre-line">{{ $vendor['desc'] }}</p>
                        <div class="mt-3 flex items-center gap-2">
                            <img src="{{ asset('images/landing/' . $vendor['av']) }}" alt="" class="h-[26px] w-auto">
                            <span class="text-[14px] md:text-[11px] font-semibold text-[#55524A] dark:text-[#B4B5A6]">{{ $vendor['plus'] }}</span>
                        </div>
                        <div class="mt-3.5 flex items-center gap-2">
                            <a href="{{ route('businesses.show', ['slug' => $vendor['slug'], 'lang' => $lang]) }}"
                                class="flex-1 h-[44px] md:h-[34px] border border-[#DBDFDC] dark:border-[#262B21] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg flex items-center justify-center text-[14px] md:text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                                {{ $isFr ? 'Voir le profil' : 'View profile' }}
                            </a>
                            <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Envoyer un message' : 'Send a message' }}"
                                class="w-[44px] h-[44px] md:w-[38px] md:h-[34px] border border-[#DBDFDC] dark:border-[#262B21] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6] transition-colors">
                                <i data-lucide="message-square" class="w-[15px] h-[15px]"></i>
                            </a>
                        </div>
                    </div>
                </article>
                @empty
                <p class="col-span-full py-10 text-center text-[14px] md:text-[13px] text-[#6F6B60] dark:text-[#868778]">
                    {{ $isFr ? 'Aucune entreprise ne correspond à ces critères.' : 'No business matches these criteria.' }}
                </p>
                @endforelse
            </div>

            <!-- Pagination (real) -->
            @if($businesses->lastPage() > 1)
            <nav class="mt-8 flex items-center justify-center gap-1.5" aria-label="Pagination">
                @if($businesses->onFirstPage())
                <span class="w-11 h-11 md:w-8 md:h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                @else
                <a href="{{ $businesses->previousPageUrl() }}" class="w-11 h-11 md:w-8 md:h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#F3EFE7] hover:bg-[#F2F5F2] hover:dark:bg-[#0A0C09] rounded-md" aria-label="{{ $isFr ? 'Page précédente' : 'Previous page' }}"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                @endif

                @foreach(range(1, $businesses->lastPage()) as $pageNum)
                @if($pageNum === $businesses->currentPage())
                <span class="w-11 h-11 md:w-8 md:h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[14px] md:text-[12.5px] font-semibold rounded-md" aria-current="page">{{ $pageNum }}</span>
                @else
                <a href="{{ $businesses->url($pageNum) }}" class="w-11 h-11 md:w-8 md:h-8 flex items-center justify-center text-[14px] md:text-[12.5px] text-[#3A3A35] dark:text-[#F3EFE7] hover:bg-[#F2F5F2] hover:dark:bg-[#0A0C09] rounded-md">{{ $pageNum }}</a>
                @endif
                @endforeach

                @if($businesses->hasMorePages())
                <a href="{{ $businesses->nextPageUrl() }}" class="w-11 h-11 md:w-8 md:h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#F3EFE7] hover:bg-[#F2F5F2] hover:dark:bg-[#0A0C09] rounded-md" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                @else
                <span class="w-11 h-11 md:w-8 md:h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                @endif
            </nav>
            @endif
        </section>
    </div>
</div>

<!-- Trust strip -->
<div class="relative bg-[#FAF7F3] dark:bg-[#0A0C09] border-t border-[#EEEBE4] dark:border-[#262B21]">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 py-4">
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            @foreach($trustItems as [$trustIcon, $trustTitle, $trustSub])
            <div class="flex items-start gap-3">
                <span class="w-[42px] h-[42px] rounded-full bg-[#FBF3E6] dark:bg-[#1B2016] flex items-center justify-center shrink-0" aria-hidden="true">
                    <i data-lucide="{{ $trustIcon }}" class="w-[21px] h-[21px] text-[#04291A] dark:text-[#EDB33A]" stroke-width="1.6"></i>
                </span>
                <div class="leading-tight">
                    <p class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $trustTitle }}</p>
                    <p class="mt-1 text-[14px] md:text-[11px] text-[#6F6B60] dark:text-[#868778] whitespace-pre-line leading-relaxed">{{ $trustSub }}</p>
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
        // The grid is 2-up on phones in both views (list styling only starts at
        // sm), so switching views means collapsing to one full-width row from sm
        // upward and dropping the 4-column desktop step.
        grid.classList.toggle('xl:grid-cols-4', !isList);
        grid.classList.toggle('sm:grid-cols-1', isList);
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
        btnGrid.classList.toggle('bg-[#F2F5F2] dark:bg-[#0A0C09]', !isList);
        btnGrid.classList.toggle('text-[#0B3D28] dark:text-[#339B56]', !isList);
        btnGrid.classList.toggle('text-[#8A857A] dark:text-[#868778]', isList);
        btnList.classList.toggle('bg-[#F2F5F2] dark:bg-[#0A0C09]', isList);
        btnList.classList.toggle('text-[#0B3D28] dark:text-[#339B56]', isList);
        btnList.classList.toggle('text-[#8A857A] dark:text-[#868778]', !isList);
        localStorage.setItem('vendView', view);
    }
    btnGrid.addEventListener('click', () => setView('grid'));
    btnList.addEventListener('click', () => setView('list'));
    if (localStorage.getItem('vendView') === 'list') setView('list');
</script>
</body>
</html>
