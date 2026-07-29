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
<div class="relative flex h-[26px] overflow-hidden text-[14px] md:text-[10.5px]">
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
<header class="bg-[#FBF6EF] dark:bg-[#12150F] border-b border-[#EFE4D5] dark:border-[#262B21]">
    {{-- Page gutter per docs/RESPONSIVE-CONTRACT.md: 16px on a phone, the
         artwork's 25px from `sm` up. --}}
    <div class="max-w-[1280px] mx-auto px-4 sm:px-[25px]">
        <div class="flex items-center justify-between gap-3 sm:gap-4 xl:gap-8 lg:h-[80px] py-2.5 lg:py-0">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-2.5 sm:gap-[15px] shrink-0 min-w-0 min-h-[44px]">
                <img src="{{ brand_asset('mark') }}" alt="" class="w-[42px] h-[42px] sm:w-[56px] sm:h-[56px] lg:w-[68px] lg:h-[68px] object-contain shrink-0">
                <span class="leading-none min-w-0">
                    <span class="block text-[19px] sm:text-[24px] lg:text-[29px] font-bold tracking-[-0.005em] text-[#0F3D24] dark:text-[#339B56] whitespace-nowrap">Artisan<span class="text-[#0F3D24] dark:text-[#339B56]">Hub</span><span class="text-[#B8891F]">237</span></span>
                    {{-- 9px uppercase micro-lettering is the desktop artwork's, and
                         it is below the contract's 12px mobile floor as well as
                         being the single widest thing in a 360px topbar. It is a
                         desktop-only strapline; the same words already run in the
                         tricolor bar above. --}}
                    <span class="hidden xl:block mt-[10px] text-[9px] font-semibold tracking-[0.16em] text-[#3C4A3E] dark:text-[#B4B5A6] uppercase whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
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
            {{-- The gap is the artwork's 22px only where there is room for it.
                 Between `md` and `xl` the five utility links, the theme pill and
                 the avatar all show at once and 22px of air pushed the row past
                 768px — the topbar was the whole reason the tablet width scrolled
                 sideways. --}}
            <div class="flex items-center gap-3 xl:gap-[22px] shrink-0">
                @foreach($dirUtility as [$duIcon, $duLabel, $duHref])
                <a href="{{ $duHref }}" class="hidden md:flex flex-col items-center gap-[7px] text-[#1D1B16] dark:text-[#F3EFE7] hover:text-leaf hover:dark:text-[#339B56] transition-colors">
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
                        class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-[#0F4227] text-white text-[13px] font-bold ring-2 ring-[#E3D6B0] dark:ring-[#6A5210]">
                        {{ mb_strtoupper(mb_substr($siacUser['name'] ?? 'A', 0, 1)) }}
                    </a>
                    <div class="absolute right-0 top-full w-52 pt-2 hidden group-hover:block z-50">
                        <div class="bg-white dark:bg-[#12150F] rounded-lg shadow-lg border border-[#E7E1D4] dark:border-[#262B21] py-1">
                            <a href="{{ route('dashboard.siac') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                            <a href="{{ route('messages.inbox') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">Messages</a>
                            <a href="{{ route('saved.index') }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Favoris' : 'Wishlist' }}</a>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('login', ['lang' => $lang]) }}" aria-label="{{ $isFr ? 'Se connecter' : 'Sign in' }}"
                        class="flex items-center justify-center w-[36px] h-[36px] rounded-full bg-[#EFEDE6] dark:bg-[#0A0C09] text-[#0F4227] dark:text-[#339B56] ring-2 ring-[#E3D6B0] dark:ring-[#6A5210]">
                        <i data-lucide="user" class="w-[19px] h-[19px]"></i>
                    </a>
                    <div class="absolute right-0 top-full w-52 pt-2 hidden group-hover:block z-50">
                        <div class="bg-white dark:bg-[#12150F] rounded-lg shadow-lg border border-[#E7E1D4] dark:border-[#262B21] py-1">
                            <a href="{{ route('login', ['lang' => $lang]) }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
                            <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Créer mon compte' : 'Create an account' }}</a>
                            <a href="{{ route('register.quick', ['lang' => $lang]) }}" class="block px-3 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Inscription rapide' : 'Quick signup' }}</a>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- 44×44 is the contract's floor, not a suggestion. `-mr-2`
                     keeps the optical alignment the artwork has while the target
                     itself stays full size. --}}
                <button id="mobile-menu-btn" type="button"
                    class="lg:hidden -mr-2 w-11 h-11 shrink-0 inline-flex items-center justify-center rounded-lg hover:bg-[#E7E1D4]/50 dark:hover:bg-[#1A1E16]"
                    aria-label="{{ $isFr ? 'Ouvrir le menu' : 'Open menu' }}" aria-expanded="false" aria-controls="mobile-menu" aria-haspopup="dialog">
                    <i data-lucide="menu" class="w-6 h-6 text-[#262521] dark:text-[#F3EFE7]"></i>
                </button>
            </div>
        </div>

        {{-- ── Mobile menu ────────────────────────────────────────────────
             A full-height sheet, not a dropped list. It leaves the topbar's
             flow (`fixed inset-0`) so it can own the screen: dimmed backdrop,
             its own scroll, 48px rows that clear the 44px tap floor, and a
             sticky footer holding the two calls to action, the language pair
             and the theme toggle — the three controls the desktop utility row
             carries and a phone otherwise cannot reach.

             `hidden` is still what shows and hides it, because twenty page
             views bind their own legacy handler to that exact class and this
             partial does not own those files. See the script at the foot. --}}
        <div id="mobile-menu" class="hidden lg:hidden fixed inset-0 z-[80]" role="dialog" aria-modal="true"
             aria-label="{{ $isFr ? 'Menu principal' : 'Main menu' }}">
            <div data-mm-close class="absolute inset-0 bg-black/55 backdrop-blur-[2px]"></div>

            <div class="absolute inset-y-0 right-0 flex w-[min(90vw,380px)] max-w-full flex-col bg-[#FBF6EF] dark:bg-[#12150F] shadow-[0_0_40px_rgba(0,0,0,.45)]">

                <!-- Sheet head -->
                <div class="flex items-center justify-between gap-3 px-4 h-[60px] shrink-0 border-b border-[#E7E1D4] dark:border-[#262B21]">
                    <span class="flex items-center gap-2.5 min-w-0">
                        <img src="{{ brand_asset('mark') }}" alt="" class="w-[34px] h-[34px] object-contain shrink-0">
                        <span class="text-[16px] font-bold tracking-[-0.005em] text-[#0F3D24] dark:text-[#339B56] truncate">Artisan<span class="text-[#B8891F]">Hub237</span></span>
                    </span>
                    <button type="button" data-mm-close
                        class="-mr-2 w-11 h-11 shrink-0 inline-flex items-center justify-center rounded-lg text-[#262521] dark:text-[#F3EFE7] hover:bg-[#E7E1D4]/60 dark:hover:bg-[#1A1E16]"
                        aria-label="{{ $isFr ? 'Fermer le menu' : 'Close menu' }}">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Sheet body -->
                <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-4 py-4">
                    <form action="{{ route('gallery.search') }}" method="GET" class="relative">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <input name="q" type="search" placeholder="{{ $dirSearchPlaceholder }}" class="ui-field h-11 pr-11">
                        <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"
                            class="absolute right-0 top-0 w-11 h-11 inline-flex items-center justify-center text-[#55524A] dark:text-[#B4B5A6]">
                            <i data-lucide="search" class="w-[18px] h-[18px]"></i>
                        </button>
                    </form>

                    {{-- Main page links — same $dirNavItems array as the green bar below --}}
                    <nav class="mt-4 -mx-1">
                        @php
                            $mmRow = 'flex items-center gap-3 min-h-[48px] px-3 rounded-lg text-[16px]';
                            $mmOn  = 'font-semibold text-[#0F4227] dark:text-[#339B56] bg-[#0F4227]/[0.07] dark:bg-[#339B56]/[0.12]';
                            $mmOff = 'font-medium text-[#1D1B16] dark:text-[#F3EFE7] hover:bg-[#E7E1D4]/50 dark:hover:bg-[#1A1E16]';
                        @endphp
                        <a href="{{ route('home', ['lang' => $lang]) }}" @class([$mmRow, $mmOn => $dirNavActive === 'home', $mmOff => $dirNavActive !== 'home'])
                           @if($dirNavActive === 'home') aria-current="page" @endif>
                            <i data-lucide="home" class="w-[18px] h-[18px] shrink-0 {{ $dirNavActive === 'home' ? 'text-[#0F4227] dark:text-[#339B56]' : 'text-[#55524A] dark:text-[#B4B5A6]' }}"></i>
                            <span class="min-w-0 truncate">{{ $isFr ? 'Accueil' : 'Home' }}</span>
                            @if($dirNavActive === 'home')<span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#C9942E]" aria-hidden="true"></span>@endif
                        </a>
                        @foreach($dirNavItems as [$mmKey, $mmIcon, $mmLabel, $mmHref])
                        @php $mmActive = $mmKey === $dirNavActive; @endphp
                        <a href="{{ $mmHref }}" @class([$mmRow, $mmOn => $mmActive, $mmOff => ! $mmActive]) @if($mmActive) aria-current="page" @endif>
                            <i data-lucide="{{ $mmIcon }}" class="w-[18px] h-[18px] shrink-0 {{ $mmActive ? 'text-[#0F4227] dark:text-[#339B56]' : 'text-[#55524A] dark:text-[#B4B5A6]' }}"></i>
                            <span class="min-w-0 truncate">{{ $mmLabel }}</span>
                            @if($mmActive)<span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#C9942E]" aria-hidden="true"></span>@endif
                        </a>
                        @endforeach
                    </nav>

                    {{-- The desktop utility row, which is hidden on a phone. --}}
                    <p class="mt-5 mb-1 px-2 text-[14px] font-bold uppercase tracking-[0.08em] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Raccourcis' : 'Shortcuts' }}</p>
                    <div class="-mx-1 grid grid-cols-2 gap-1">
                        @foreach($dirUtility as [$duIcon, $duLabel, $duHref])
                        <a href="{{ $duHref }}" class="flex items-center gap-2.5 min-h-[48px] px-3 rounded-lg text-[14px] font-medium text-[#1D1B16] dark:text-[#F3EFE7] hover:bg-[#E7E1D4]/50 dark:hover:bg-[#1A1E16]">
                            <i data-lucide="{{ $duIcon }}" class="w-[17px] h-[17px] shrink-0 text-[#55524A] dark:text-[#B4B5A6]"></i>
                            <span class="min-w-0 truncate">{{ $duLabel }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Sheet foot: the two ways in, the language pair, the theme -->
                <div class="shrink-0 border-t border-[#E7E1D4] dark:border-[#262B21] bg-[#F5EFE5] dark:bg-[#0A0C09] px-4 py-3 space-y-2.5"
                     style="padding-bottom: calc(0.75rem + env(safe-area-inset-bottom))">
                    <div class="grid grid-cols-2 gap-2">
                        @if($siacUser)
                        <a href="{{ route('dashboard.siac') }}" class="inline-flex items-center justify-center min-h-[44px] px-3 rounded-lg bg-[#02301B] text-white text-[14px] font-semibold text-center">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                        @else
                        <a href="{{ route('login', ['lang' => $lang]) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 rounded-lg bg-[#02301B] text-white text-[14px] font-semibold">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
                        @endif
                        {{-- Mobile signup is the fast form only (owner directive, 2026-07-29):
                             name, contact, trade, account type, register, then straight to
                             email verification — not the longer desktop wizard. --}}
                        <a href="{{ route('register.quick', ['lang' => $lang]) }}" class="inline-flex items-center justify-center min-h-[44px] px-3 rounded-lg bg-[#C9942E] text-[#231903] text-[14px] font-bold uppercase tracking-[0.04em] text-center leading-tight">
                            {{ $isFr ? 'Vendre ici' : 'Sell here' }}
                        </a>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex items-center rounded-lg border border-[#D9D2C4] dark:border-[#262B21] overflow-hidden" role="group" aria-label="{{ $isFr ? 'Langue' : 'Language' }}">
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" @if($isFr) aria-current="true" @endif
                               class="inline-flex items-center justify-center min-w-[52px] h-11 text-[14px] font-semibold {{ $isFr ? 'bg-[#0F4227] text-white' : 'text-[#55524A] dark:text-[#B4B5A6]' }}">FR</a>
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" @if(! $isFr) aria-current="true" @endif
                               class="inline-flex items-center justify-center min-w-[52px] h-11 text-[14px] font-semibold {{ ! $isFr ? 'bg-[#0F4227] text-white' : 'text-[#55524A] dark:text-[#B4B5A6]' }}">EN</a>
                        </span>
                        {{-- Same control again for the phone menu, where the utility row
                             above is hidden. Duplicate `.theme-toggle` nodes are wired by
                             delegation, so this needs no script of its own. --}}
                        @include('pages.partials.theme-toggle')
                    </div>
                </div>
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
                        <div class="w-[600px] max-w-[92vw] bg-white dark:bg-[#12150F] rounded-xl shadow-xl border border-[#EFEDEA] dark:border-[#262B21] p-5">
                            <div class="grid grid-cols-3 gap-x-5 gap-y-4">
                                @foreach($navSectors as $sec)
                                <div class="min-w-0">
                                    <a href="{{ route('industries.index', ['lang' => $lang, 'cat' => $sec->slug]) }}" class="flex items-center gap-1.5 text-[12.5px] font-bold text-[#14532D] dark:text-[#339B56] hover:underline mb-2">
                                        <i data-lucide="layers" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span class="truncate">{{ $isFr ? $sec->name_fr : ($sec->name_en ?? $sec->name_fr) }}</span>
                                    </a>
                                    <div class="space-y-1">
                                        @foreach($sec->filieres as $fil)
                                        @php $filName = $isFr ? $fil->name_fr : ($fil->name_en ?? $fil->name_fr); @endphp
                                        <a href="{{ route('industries.index', ['lang' => $lang, 'cat' => $fil->slug]) }}" title="{{ $filName }}" class="block text-[11.5px] text-[#55524A] dark:text-[#B4B5A6] hover:text-leaf hover:dark:text-[#339B56] truncate">{{ $filName }}</a>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <a href="{{ $dnHref }}" class="mt-4 pt-3 border-t border-[#F1F0EC] dark:border-[#262B21] flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43] dark:text-[#339B56] hover:underline">
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
                    <div class="bg-white dark:bg-[#12150F] rounded-lg shadow-xl border border-[#E7E1D4] dark:border-[#262B21] py-1">
                        <a href="{{ route('onboarding', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Créer mon compte artisan' : 'Create my artisan account' }}</a>
                        <a href="{{ route('register.quick', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? 'Inscription rapide' : 'Quick signup' }}</a>
                        <a href="{{ route('guide.artisan', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09]">{{ $isFr ? "Guide de l'artisan" : 'Artisan guide' }}</a>
                        <a href="{{ route('login', ['lang' => $lang]) }}" class="block px-3.5 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F8F3ED] hover:dark:bg-[#0A0C09] border-t border-[#F1F0EC] dark:border-[#262B21]">{{ $isFr ? 'J\'ai déjà un compte' : 'I already have an account' }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── The hamburger, and why it used to do nothing ──────────────────────────
     The toggle ships WITH the markup it controls, because consuming pages used
     to each duplicate the handler and five of them drifted without one. What
     that change did not do was remove the copies: twenty views — about.blade,
     home.blade, businesses/index, products/show, layouts/app and the rest —
     still run `mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'))`
     of their own. So one tap fired TWO toggles: the page's listener on the
     button (target/bubble phase, first), then this delegated one on document
     (last). The panel was un-hidden and re-hidden inside a single event and
     never painted. Measured, not guessed: after a synthetic click the class
     list read `lg:hidden … hidden` — removed, then appended again.

     The fix is to stop toggling. Ordering is the one thing that is guaranteed
     here — a listener on `document` always runs after a listener bound to the
     button itself — so this handler reads the state IT owns (`data-open`),
     flips that, and then *sets* the classes to match. Whatever the legacy
     page handlers did a microsecond earlier is simply overwritten, however
     many of them there are, and no file this partial does not own is touched.

     @once keeps it to a single emission even when the header is included more
     than once. --}}
@once
<script>
(function () {
    var menu = function () { return document.getElementById('mobile-menu'); };

    function setOpen(open) {
        var m = menu();
        if (!m) return;
        m.setAttribute('data-open', open ? 'true' : 'false');
        m.classList.toggle('hidden', !open);       // authoritative, not a toggle
        var btn = document.getElementById('mobile-menu-btn');
        if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        // The sheet owns the screen; the page behind it must not scroll with it.
        document.documentElement.style.overflow = open ? 'hidden' : '';
        if (open && window.lucide) { try { window.lucide.createIcons(); } catch (e) {} }
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest) return;

        if (e.target.closest('#mobile-menu-btn')) {
            var m = menu();
            setOpen(!(m && m.getAttribute('data-open') === 'true'));
            return;
        }
        // Backdrop, the X, or any link inside the sheet: leaving means closing.
        if (e.target.closest('[data-mm-close]') || e.target.closest('#mobile-menu a')) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setOpen(false);
    });

    // A width that shows the desktop nav must not leave the sheet latched open.
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) setOpen(false);
    });
})();
</script>
@endonce
