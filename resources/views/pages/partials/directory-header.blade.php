{{-- Canonical platform chrome — header.
     Brought up to `certificates/artisan profile v2 desktop.png` (2026-07-28):
     white topbar with the logo lockup, a centred search carrying a category
     select and a green search button, then icon-with-label utility links and an
     account avatar; below it the deep-green section bar with the gold
     "SELL ON ARTISANHUB237" call to action on the right.

     Expects: $lang, $isFr, $siacUser
     Optional: $dirSearchCategories (slug/label pairs for the select),
               $dirSearchPlaceholder, $dirIconVariant ('products'|'vendors'|…,
               kept for callers — the utility row is the same on every page now),
               $dirNavActive (override which key is highlighted — auto-detected
               from the current route if omitted), $dirTopBar (tricolor strip
               carries its links).

     The nav item list below (green bar + mobile menu) is the single canonical
     platform menu: every page that includes this header gets the identical set,
     in the identical order — this partial must not be forked per page. --}}

@php
    $dirIconVariant = $dirIconVariant ?? 'products';
    $dirSearchPlaceholder = $dirSearchPlaceholder
        ?? ($isFr ? 'Rechercher artisans, produits, catégories...' : 'Search artisans, products, categories...');

    // Canonical platform menu — one array, shared by the green section bar and
    // the mobile menu below, so they can never drift apart again.
    // [key, icon, label, route]
    // Ordered as the artwork reads it: ARTISANS · PRODUCTS · CATEGORIES ·
    // COLLECTIONS · STORIES · EXHIBITIONS · MARKETPLACE · ABOUT US. The last two
    // sections the platform really has (Partners, Contact) follow, because the
    // artwork's single "MARKETPLACE" entry has no route of its own here — the
    // marketplace *is* the products/artisans gallery already listed.
    $dirNavItems = [
        ['businesses',  'users',         $isFr ? 'Artisans' : 'Artisans',            route('businesses.index', ['lang' => $lang])],
        ['products',    'package',       $isFr ? 'Produits' : 'Products',            route('products.index', ['lang' => $lang])],
        ['categories',  'layout-grid',   $isFr ? 'Catégories' : 'Categories',        route('industries.index', ['lang' => $lang])],
        ['collections', 'gallery-vertical-end', 'Collections',                       route('collections.index', ['lang' => $lang])],
        ['news',        'newspaper',     $isFr ? 'Actualités' : 'Stories',           route('news.index', ['lang' => $lang])],
        ['events',      'calendar-days', $isFr ? 'Événements' : 'Exhibitions',       route('events.index', ['lang' => $lang])],
        ['centres',     'store',         $isFr ? 'Centres' : 'Craft centres',        route('centres.index', ['lang' => $lang])],
        ['about',       'info',          $isFr ? 'À propos' : 'About us',            route('about', ['lang' => $lang])],
        ['partners',    'handshake',     $isFr ? 'Partenaires' : 'Partners',         route('partners.index', ['lang' => $lang])],
        ['contact',     'message-circle', $isFr ? 'Contact' : 'Contact',             route('contact', ['lang' => $lang])],
    ];

    // Auto-detect the active item from the current route when the including
    // page doesn't force one — no page should have to remember to set this.
    if (! isset($dirNavActive)) {
        $dirRouteMap = [
            'home' => 'home', 'products.*' => 'products', 'industries.*' => 'categories',
            'businesses.*' => 'businesses', 'events.*' => 'events', 'collections.*' => 'collections',
            'centres.*' => 'centres',
            'partners.*' => 'partners', 'news.*' => 'news',
            'about' => 'about', 'contact' => 'contact',
        ];
        $dirNavActive = collect($dirRouteMap)->first(fn ($key, $pattern) => request()->routeIs($pattern));
    }

    $dhCategories = $dirSearchCategories ?? [
        ['arts-decoration',          $isFr ? 'Arts & Décoration' : 'Arts & Decoration'],
        ['mode-textile',             $isFr ? 'Mode & Textile' : 'Fashion & Textile'],
        ['bois-sculpture',           $isFr ? 'Bois & Sculpture' : 'Wood & Sculpture'],
        ['poterie-ceramique',        $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics'],
        ['bijouterie-accessoires',   $isFr ? 'Bijouterie & Accessoires' : 'Jewellery & Accessories'],
        ['cuir-maroquinerie',        $isFr ? 'Cuir & Maroquinerie' : 'Leather & Leatherwork'],
        ['musique-instruments',      $isFr ? 'Musique & Instruments' : 'Music & Instruments'],
        ['produits-naturels',        $isFr ? 'Produits Naturels' : 'Natural Products'],
        ['agroalimentaire',          $isFr ? 'Agroalimentaire' : 'Agri-food'],
        ['technologies-innovation',  $isFr ? 'Technologies & Innovation' : 'Technology & Innovation'],
    ];

    // Utility row, as the artwork draws it: Explore · Map · Verify · Wishlist ·
    // Cart. Every destination below is a route this platform actually serves.
    //   Map  → the craft-centres directory, the only geographic browse there is.
    //   Cart → there is no basket on this platform; buyers request quotes, so
    //          the fifth slot is the quote/inquiry desk instead.
    $dhLoginHref = route('login', ['lang' => $lang]);
    $dirUtility = [
        ['compass',      $isFr ? 'Explorer' : 'Explore',  route('businesses.index', ['lang' => $lang])],
        ['map',          $isFr ? 'Carte' : 'Map',         route('centres.index', ['lang' => $lang])],
        ['badge-check',  $isFr ? 'Vérifier' : 'Verify',   route('certificate.verify', ['lang' => $lang])],
        ['heart',        $isFr ? 'Favoris' : 'Wishlist',  $siacUser ? route('saved.index') : $dhLoginHref],
        ['shopping-bag', $isFr ? 'Demandes' : 'Inquiries', $siacUser ? route('quotes.index') : $dhLoginHref],
    ];
@endphp

<!-- Tricolor top bar -->
@if($dirTopBar ?? false)
<div class="relative flex h-[26px] overflow-hidden text-[10.5px]">
    <div class="w-[37.5%] bg-[#012C1B] flex items-center pl-4 sm:pl-6">
        <span class="text-white/90 truncate">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
    </div>
    <div class="relative w-[27.6%] bg-[#C0010C]">
        <svg viewBox="0 0 24 24" class="absolute left-[45.2%] top-1/2 -translate-x-1/2 -translate-y-1/2 w-[12px] h-[12px] fill-[#FBB604]" aria-hidden="true">
            <path d="M12 1.5 14.7 8.6l7.6.3-6 4.7 2.1 7.3L12 16.6 5.6 20.9l2.1-7.3-6-4.7 7.6-.3z"/>
        </svg>
    </div>
    <div class="flex-1 bg-[#FBB604] hidden sm:flex items-center justify-end pr-4 sm:pr-6 text-[#3A2E08] font-medium whitespace-nowrap">
        <a href="{{ route('partners.index') }}" class="hover:underline">{{ $isFr ? 'Devenir partenaire' : 'Become a partner' }}</a>
        <span class="mx-2 opacity-50">|</span>
        <a href="{{ route('login', ['lang' => $lang]) }}" class="hover:underline">{{ $isFr ? 'Espace Artisan' : 'Artisan area' }}</a>
        <span class="mx-2 opacity-50">|</span>
        <a href="{{ route('login', ['lang' => $lang]) }}" class="hover:underline">{{ $isFr ? 'Espace Entreprise' : 'Business area' }}</a>
        <span class="mx-2 opacity-50">|</span>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="hover:underline">{{ $isFr ? 'Aide' : 'Help' }}</a>
    </div>
    <div class="flex-1 bg-[#FBB604] sm:hidden"></div>
</div>
@else
<div class="relative flex h-5 overflow-hidden">
    <div class="w-[37.5%] bg-[#012C1B]"></div>
    <div class="relative w-[27.6%] bg-[#C0010C]">
        <svg viewBox="0 0 24 24" class="absolute left-[45.2%] top-1/2 -translate-x-1/2 -translate-y-1/2 w-[11px] h-[11px] fill-[#FBB604]" aria-hidden="true">
            <path d="M12 1.5 14.7 8.6l7.6.3-6 4.7 2.1 7.3L12 16.6 5.6 20.9l2.1-7.3-6-4.7 7.6-.3z"/>
        </svg>
    </div>
    <div class="flex-1 bg-[#FBB604]"></div>
</div>
@endif

<!-- Topbar — measured off the artwork: 64px band at 1024 = 80px at 1280,
     cream #FBF6EF, content 1230 wide inside a 25px page margin. -->
<header class="bg-[#FBF6EF] border-b border-[#EFE4D5]">
    <div class="max-w-[1280px] mx-auto px-[25px]">
        <div class="flex items-center justify-between gap-4 xl:gap-8 lg:h-[80px] py-3 lg:py-0">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-[15px] shrink-0">
                <img src="{{ brand_asset('mark') }}" alt="" class="w-[56px] h-[56px] lg:w-[68px] lg:h-[68px] object-contain shrink-0">
                <span class="leading-none">
                    <span class="block text-[24px] lg:text-[29px] font-bold tracking-[-0.005em] text-[#0F3D24] whitespace-nowrap">Artisan<span class="text-[#0F3D24]">Hub</span><span class="text-[#B8891F]">237</span></span>
                    <span class="block mt-[10px] text-[9px] font-semibold tracking-[0.16em] text-[#3C4A3E] uppercase whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                </span>
            </a>

            <!-- Search + category select + green search button -->
            <form action="{{ route('gallery.search') }}" method="GET" class="hidden lg:flex items-center flex-1 min-w-0 max-w-[459px]">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="ui-field-group flex-1 min-w-0 gap-0 overflow-hidden px-0 rounded-r-none border-r-0">
                    <input name="q" type="search" placeholder="{{ $dirSearchPlaceholder }}" class="ui-field-bare px-4 min-w-0">
                    <span class="h-[22px] w-px bg-[var(--ui-border-field)] shrink-0"></span>
                    <select name="categorie" aria-label="{{ $isFr ? 'Catégorie' : 'Category' }}"
                        {{-- Fixed width on purpose. Left to size itself the select
                             takes the width of its longest option ("Bijouterie &
                             Accessoires") and eats 190px of a 459px search bar,
                             truncating the placeholder. --}}
                        class="ui-field-bare h-full pl-3 pr-7 cursor-pointer appearance-none w-[86px] shrink-0 bg-no-repeat bg-[right_0.6rem_center]"
                        style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;10&quot; height=&quot;6&quot; viewBox=&quot;0 0 10 6&quot;><path d=&quot;M1 1l4 4 4-4&quot; stroke=&quot;%236F6B60&quot; stroke-width=&quot;1.5&quot; fill=&quot;none&quot; stroke-linecap=&quot;round&quot;/></svg>')">
                        <option value="">{{ $isFr ? 'Toutes' : 'All' }}</option>
                        @foreach($dhCategories as [$dhSlug, $dhLabel])
                        <option value="{{ $dhSlug }}">{{ $dhLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"
                    class="w-[46px] shrink-0 self-stretch bg-[#0F4227] hover:bg-[#155F35] text-white rounded-r-lg flex items-center justify-center transition-colors">
                    <i data-lucide="search" class="w-[17px] h-[17px]"></i>
                </button>
            </form>

            <!-- Utility icon links + account avatar -->
            <div class="flex items-center gap-[22px] shrink-0">
                @foreach($dirUtility as [$duIcon, $duLabel, $duHref])
                <a href="{{ $duHref }}" class="hidden md:flex flex-col items-center gap-[7px] text-[#1D1B16] hover:text-leaf transition-colors">
                    <i data-lucide="{{ $duIcon }}" class="w-[17.5px] h-[17.5px]"></i>
                    <span class="text-[10px] font-medium leading-none whitespace-nowrap">{{ $duLabel }}</span>
                </a>
                @endforeach

                {{-- Dark-mode control. Sits in the utility row so it is reachable
                     from every public page's chrome; `data-theme-toggle-slot` on
                     the wrapper tells pages/partials/theme.blade.php not to mount
                     its floating fallback here. --}}
                <div data-theme-toggle-slot class="hidden sm:block shrink-0">
                    @include('pages.partials.theme-toggle')
                </div>

                <!-- Account -->
                <div class="relative group hidden sm:block">
                    @if($siacUser)
                    <a href="{{ route('dashboard.siac') }}" aria-label="{{ $isFr ? 'Mon compte' : 'My account' }}"
                        class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-[#0F4227] text-white text-[13px] font-bold ring-2 ring-[#E3D6B0]">
                        {{ mb_strtoupper(mb_substr($siacUser['name'] ?? 'A', 0, 1)) }}
                    </a>
                    <div class="absolute right-0 top-full w-52 pt-2 hidden group-hover:block z-50">
                        <div class="bg-white rounded-lg shadow-lg border border-[#E7E1D4] py-1">
                            <a href="{{ route('dashboard.siac') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                            <a href="{{ route('messages.inbox') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">Messages</a>
                            <a href="{{ route('saved.index') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Favoris' : 'Wishlist' }}</a>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('login', ['lang' => $lang]) }}" aria-label="{{ $isFr ? 'Se connecter' : 'Sign in' }}"
                        class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-[#EFEDE6] text-[#0F4227] ring-2 ring-[#E3D6B0]">
                        <i data-lucide="user" class="w-[19px] h-[19px]"></i>
                    </a>
                    <div class="absolute right-0 top-full w-52 pt-2 hidden group-hover:block z-50">
                        <div class="bg-white rounded-lg shadow-lg border border-[#E7E1D4] py-1">
                            <a href="{{ route('login', ['lang' => $lang]) }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
                            <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Créer mon compte' : 'Create an account' }}</a>
                            <a href="{{ route('register.quick', ['lang' => $lang]) }}" class="block px-3 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Inscription rapide' : 'Quick signup' }}</a>
                        </div>
                    </div>
                    @endif
                </div>

                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-md hover:bg-[#E7E1D4]/50" aria-label="Menu">
                    <i data-lucide="menu" class="w-5 h-5 text-[#262521]"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden pb-4 border-t border-[#E7E1D4] pt-3">
            <form action="{{ route('gallery.search') }}" method="GET" class="relative mb-3">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input name="q" type="search" placeholder="{{ $dirSearchPlaceholder }}"
                    class="ui-field pr-9">
                <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#55524A]">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </form>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 px-1 mb-2">
                @foreach($dirUtility as [$duIcon, $duLabel, $duHref])
                <a href="{{ $duHref }}" class="flex items-center gap-2 text-[13.5px] font-medium text-[#1D1B16]">
                    <i data-lucide="{{ $duIcon }}" class="w-4 h-4"></i>{{ $duLabel }}
                </a>
                @endforeach
            </div>
            {{-- Main page links — same $dirNavItems array as the green bar below --}}
            <div class="mb-2">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-2.5 px-1 py-2 text-[13.5px] {{ $dirNavActive === 'home' ? 'font-semibold text-leaf' : 'text-[#1D1B16] hover:text-leaf' }}">
                    <i data-lucide="home" class="w-4 h-4 text-[#55524A]"></i>{{ $isFr ? 'Accueil' : 'Home' }}
                </a>
                @foreach($dirNavItems as [$mmKey, $mmIcon, $mmLabel, $mmHref])
                <a href="{{ $mmHref }}" class="flex items-center gap-2.5 px-1 py-2 text-[13.5px] {{ $mmKey === $dirNavActive ? 'font-semibold text-leaf' : 'text-[#1D1B16] hover:text-leaf' }}">
                    <i data-lucide="{{ $mmIcon }}" class="w-4 h-4 text-[#55524A]"></i>{{ $mmLabel }}
                </a>
                @endforeach
            </div>
            <div class="border-t border-[#E7E1D4] pt-3 flex flex-wrap items-center justify-between gap-2 px-1">
                @if($siacUser)
                <a href="{{ route('dashboard.siac') }}" class="inline-flex items-center bg-[#02301B] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                @else
                <a href="{{ route('login', ['lang' => $lang]) }}" class="inline-flex items-center bg-[#02301B] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
                @endif
                <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 bg-[#C9942E] text-[#231903] text-[12.5px] font-bold px-4 py-2 rounded-lg uppercase tracking-[0.04em]">
                    {{ $isFr ? 'Vendre sur ArtisanHub237' : 'Sell on ArtisanHub237' }}
                </a>
                <span class="flex items-center gap-2 text-[13px] font-semibold">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="{{ $isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">FR</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ !$isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">EN</a>
                </span>
                {{-- Same control again for the phone menu, where the utility row
                     above is hidden. Duplicate `.theme-toggle` nodes are wired by
                     delegation, so this needs no script of its own. --}}
                @include('pages.partials.theme-toggle')
            </div>
        </div>
    </div>
</header>

{{-- Deep-green section bar — always rendered, same $dirNavItems as the mobile
     menu above. `min-w-0` on the nav plus the shrink-0 button keeps the row
     inside the viewport at every width instead of pushing the page sideways. --}}
<div class="hidden lg:block bg-[#002A0D]">
    <div class="max-w-[1280px] mx-auto px-[25px]">
        <div class="flex items-center justify-between gap-4 h-[40px]">
            <nav class="flex items-center gap-[18px] min-w-0 flex-1">
                @foreach($dirNavItems as [$dnKey, $dnIcon, $dnLabel, $dnHref])
                @if($dnKey === 'categories' && !empty($navSectors) && $navSectors->count())
                {{-- Categories megamenu: official sectors → filières --}}
                <div class="relative group shrink-0">
                    <a href="{{ $dnHref }}" class="relative flex items-center gap-1 h-[40px] text-[12.5px] font-semibold uppercase tracking-[0.06em] {{ $dnKey === $dirNavActive ? 'text-[#E29A08]' : 'text-white hover:text-[#E29A08]' }} transition-colors whitespace-nowrap">
                        {{ $dnLabel }}
                        <i data-lucide="chevron-down" class="w-3 h-3 opacity-70"></i>
                        @if($dnKey === $dirNavActive)<span class="absolute left-0 right-0 bottom-0 h-[3px] bg-[#E7A320]"></span>@endif
                    </a>
                    <div class="absolute left-0 top-full pt-1 hidden group-hover:block z-50">
                        <div class="w-[600px] max-w-[92vw] bg-white rounded-xl shadow-xl border border-[#EFEDEA] p-5">
                            <div class="grid grid-cols-3 gap-x-5 gap-y-4">
                                @foreach($navSectors as $sec)
                                <div class="min-w-0">
                                    <a href="{{ route('industries.index', ['lang' => $lang, 'cat' => $sec->slug]) }}" class="flex items-center gap-1.5 text-[12.5px] font-bold text-[#14532D] hover:underline mb-2">
                                        <i data-lucide="layers" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span class="truncate">{{ $isFr ? $sec->name_fr : ($sec->name_en ?? $sec->name_fr) }}</span>
                                    </a>
                                    <div class="space-y-1">
                                        @foreach($sec->filieres as $fil)
                                        @php $filName = $isFr ? $fil->name_fr : ($fil->name_en ?? $fil->name_fr); @endphp
                                        <a href="{{ route('industries.index', ['lang' => $lang, 'cat' => $fil->slug]) }}" title="{{ $filName }}" class="block text-[11.5px] text-[#55524A] hover:text-leaf truncate">{{ $filName }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <a href="{{ $dnHref }}" class="mt-4 pt-3 border-t border-[#F1F0EC] flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43] hover:underline">
                                {{ $isFr ? 'Explorer toute la nomenclature officielle' : 'Explore the full official nomenclature' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ $dnHref }}" class="relative shrink-0 flex items-center h-[40px] text-[12.5px] font-semibold uppercase tracking-[0.06em] {{ $dnKey === $dirNavActive ? 'text-[#E29A08]' : 'text-white hover:text-[#E29A08]' }} transition-colors whitespace-nowrap">
                    {{ $dnLabel }}
                    @if($dnKey === $dirNavActive)<span class="absolute left-0 right-0 bottom-0 h-[3px] bg-[#E7A320]"></span>@endif
                </a>
                @endif
                @endforeach
            </nav>

            {{-- Gold call to action. Points at the real become-a-member route;
                 the chevron opens the other two real ways in. --}}
            <div class="relative group shrink-0 flex items-center h-[40px]">
                <a href="{{ route('onboarding', ['lang' => $lang]) }}"
                    class="flex items-center justify-center gap-2 bg-gradient-to-b from-[#A2700F] to-[#925104] hover:from-[#B37D14] hover:to-[#A25C08] text-white text-[12.5px] font-bold uppercase tracking-[0.06em] min-w-[206px] px-3 h-[30px] rounded-md transition-colors whitespace-nowrap">
                    {{ $isFr ? 'Vendre sur ArtisanHub237' : 'Sell on ArtisanHub237' }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </a>
                <div class="absolute right-0 top-full w-60 pt-1.5 hidden group-hover:block z-50">
                    <div class="bg-white rounded-lg shadow-xl border border-[#E7E1D4] py-1">
                        <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Créer mon compte artisan' : 'Create my artisan account' }}</a>
                        <a href="{{ route('register.quick', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? 'Inscription rapide' : 'Quick signup' }}</a>
                        <a href="{{ route('guide.artisan', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED]">{{ $isFr ? "Guide de l'artisan" : 'Artisan guide' }}</a>
                        <a href="{{ route('login', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] hover:bg-[#F8F3ED] border-t border-[#F1F0EC]">{{ $isFr ? 'J\'ai déjà un compte' : 'I already have an account' }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- The hamburger toggle ships WITH the markup it controls. Consuming pages used
     to each duplicate this handler, and five of them drifted without one — the
     mobile menu button was simply dead on those pages. @once keeps it to a
     single emission even when the header is included more than once. --}}
@once
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('#mobile-menu-btn');
        if (!btn) return;
        const menu = document.getElementById('mobile-menu');
        if (menu) menu.classList.toggle('hidden');
    });
</script>
@endonce
