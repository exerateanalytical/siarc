{{-- Directory replica header (product/vendor directory design): thin tricolor with one
     centered star + white header with search + category select + icon links.
     Expects: $lang, $isFr, $siacUser
     Optional: $dirSearchCategories (slug/label pairs for the select),
               $dirSearchPlaceholder, $dirIconVariant ('products': Favoris+Demandes |
               'vendors': Favoris+Messages+Panier(3)), $dirNavActive (override which
               key is highlighted — auto-detected from the current route if omitted).
     The nav item list below (desktop bar + mobile menu) is the single canonical
     platform menu: every page that includes this header gets the identical set,
     in the identical order — this partial must not be forked per page. --}}

@php
    $dirIconVariant = $dirIconVariant ?? 'products';
    $dirSearchPlaceholder = $dirSearchPlaceholder
        ?? ($isFr ? 'Rechercher un produit, artisan, catégorie...' : 'Search a product, artisan, category...');

    // Canonical platform menu — one array, shared by the desktop bar and the
    // mobile menu below, so they can never drift apart again.
    // [key, icon, label, route]
    // Collections, Centres and FAQ live in the footer menu, not here.
    $dirNavItems = [
        ['home',        'home',          $isFr ? 'Accueil' : 'Home',                    route('home', ['lang' => $lang])],
        ['products',    'package',       $isFr ? 'Produits' : 'Products',               route('products.index', ['lang' => $lang])],
        ['categories',  'layout-grid',   $isFr ? 'Catégories' : 'Categories',           route('industries.index', ['lang' => $lang])],
        ['businesses',  'users',         $isFr ? 'Artisans & Entreprises' : 'Artisans & Businesses', route('businesses.index', ['lang' => $lang])],
        ['events',      'calendar-days', $isFr ? 'Événements' : 'Events',               route('events.index', ['lang' => $lang])],
        ['partners',    'handshake',    $isFr ? 'Partenaires' : 'Partners',             route('partners.index', ['lang' => $lang])],
        ['news',        'newspaper',    $isFr ? 'Actualités' : 'News',                  route('news.index', ['lang' => $lang])],
        ['about',       'info',         $isFr ? 'À propos' : 'About',                   route('about', ['lang' => $lang])],
        ['contact',     'message-circle', $isFr ? 'Contact' : 'Contact',                route('contact', ['lang' => $lang])],
    ];

    // Auto-detect the active item from the current route when the including
    // page doesn't force one — no page should have to remember to set this.
    if (! isset($dirNavActive)) {
        // Collections/Centres/FAQ aren't header items (they live in the footer),
        // so pages under those routes simply have no highlighted header item.
        $dirRouteMap = [
            'home' => 'home', 'products.*' => 'products', 'industries.*' => 'categories',
            'businesses.*' => 'businesses', 'events.*' => 'events',
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

<!-- Header -->
<header class="bg-[#FEFEFE] border-b border-[#EFEDEA]">
    <div class="max-w-[1472px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between gap-4 xl:gap-6 py-3.5">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[44px] h-[48px] object-contain">
                <span class="leading-tight">
                    <span class="block text-[12.5px] font-bold tracking-[0.03em] text-[#1D1B16] uppercase whitespace-nowrap">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</span>
                    <span class="block text-[12.5px] font-bold tracking-[0.03em] text-[#1D1B16] uppercase whitespace-nowrap">{{ $isFr ? 'Marketplace des artisans' : 'Artisan Marketplace' }}</span>
                    <span class="block text-[10px] text-[#6F6B60] mt-0.5 whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                </span>
            </a>

            <!-- Search + category select -->
            <form action="{{ route('gallery.search') }}" method="GET" class="hidden lg:flex items-center gap-2.5 flex-1 max-w-[575px]">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="ui-field-group flex-1 gap-0 overflow-hidden px-0">
                    <input name="q" type="search" placeholder="{{ $dirSearchPlaceholder }}" class="ui-field-bare px-4">
                    <span class="h-[22px] w-px bg-[var(--ui-border-field)] shrink-0"></span>
                    <select name="categorie" class="h-full pl-3 pr-7 text-[12.5px] text-[#1D1B16] bg-transparent focus:outline-none cursor-pointer appearance-none bg-no-repeat bg-[right_0.6rem_center]"
                        style="background-image:url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;10&quot; height=&quot;6&quot; viewBox=&quot;0 0 10 6&quot;><path d=&quot;M1 1l4 4 4-4&quot; stroke=&quot;%236F6B60&quot; stroke-width=&quot;1.5&quot; fill=&quot;none&quot; stroke-linecap=&quot;round&quot;/></svg>')">
                        <option value="">{{ $isFr ? 'Toutes les catégories' : 'All categories' }}</option>
                        @foreach($dhCategories as [$dhSlug, $dhLabel])
                        <option value="{{ $dhSlug }}">{{ $dhLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"
                    class="w-[38px] h-[38px] shrink-0 bg-[#02301B] hover:bg-leaf text-white rounded-lg flex items-center justify-center transition-colors">
                    <i data-lucide="search" class="w-[16px] h-[16px]"></i>
                </button>
            </form>

            <div class="flex items-center gap-4 xl:gap-6 shrink-0">
                <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" class="hidden md:flex items-center gap-2 text-[13px] font-medium text-[#1D1B16] hover:text-leaf transition-colors">
                    <i data-lucide="heart" class="w-[17px] h-[17px]"></i>
                    {{ $isFr ? 'Favoris' : 'Saved' }}
                </a>
                @php
                    $dirShowMessages = in_array($dirIconVariant, ['vendors', 'vdetail']);
                    $dirMsgBadge = $dirIconVariant === 'vdetail' ? ($dirMsgCount ?? 3) : null;
                    $dirShowCart = in_array($dirIconVariant, ['vendors', 'detail', 'vdetail']);
                    $dirCartBadge = $dirIconVariant === 'vendors' ? ($dirCartCount ?? 3) : ($dirIconVariant === 'detail' ? ($dirCartCount ?? 2) : null);
                @endphp
                @if($dirShowMessages || $dirShowCart)
                @if($dirShowMessages)
                <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}" class="hidden md:flex items-center gap-2 text-[13px] font-medium text-[#1D1B16] hover:text-leaf transition-colors">
                    <span class="relative">
                        <i data-lucide="mail" class="w-[17px] h-[17px]"></i>
                        @if($dirMsgBadge)
                        <span class="absolute -top-2 -right-2.5 w-[15px] h-[15px] text-white text-[9px] font-bold rounded-full flex items-center justify-center" style="background-color: {{ $dirMsgBadgeColor ?? '#02301B' }}">{{ $dirMsgBadge }}</span>
                        @endif
                    </span>
                    Messages
                </a>
                @endif
                @if($dirShowCart)
                <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" class="hidden md:flex items-center gap-2 text-[13px] font-medium text-[#1D1B16] hover:text-leaf transition-colors">
                    <span class="relative">
                        <i data-lucide="shopping-cart" class="w-[17px] h-[17px]"></i>
                        @if($dirCartBadge)
                        <span class="absolute -top-2 -right-2.5 w-[15px] h-[15px] bg-[#02301B] text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $dirCartBadge }}</span>
                        @endif
                    </span>
                    {{ $isFr ? 'Panier' : 'Cart' }}
                </a>
                @endif
                @else
                <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}" class="hidden md:flex items-center gap-2 text-[13px] font-medium text-[#1D1B16] hover:text-leaf transition-colors">
                    <i data-lucide="shopping-bag" class="w-[17px] h-[17px]"></i>
                    {{ $isFr ? 'Demandes' : 'Inquiries' }}
                </a>
                @endif

                <!-- Language -->
                <div class="relative group hidden sm:block">
                    <button class="flex items-center gap-1.5 py-2 text-[13px] font-medium text-[#1D1B16]">
                        <i data-lucide="globe" class="w-[16px] h-[16px]"></i>
                        {{ strtoupper($lang) }}
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                    </button>
                    <div class="absolute right-0 top-full w-28 bg-white rounded-lg shadow-lg border border-[#E7E1D4] py-1 hidden group-hover:block z-50">
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-leaf' : 'text-[#262521] hover:bg-[#F8F3ED]' }}">FR — Français</a>
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-leaf' : 'text-[#262521] hover:bg-[#F8F3ED]' }}">EN — English</a>
                    </div>
                </div>

                @if($siacUser)
                <a href="{{ route('dashboard.siac') }}" class="hidden sm:inline-flex items-center whitespace-nowrap bg-[#02301B] hover:bg-leaf text-white text-[13px] font-semibold px-5 h-[40px] rounded-lg transition-colors">
                    {{ $isFr ? 'Tableau de bord' : 'Dashboard' }}
                </a>
                @else
                <a href="{{ route('login', ['lang' => $lang]) }}" class="hidden sm:inline-flex items-center whitespace-nowrap bg-[#02301B] hover:bg-leaf text-white text-[13px] font-semibold px-5 h-[40px] rounded-lg transition-colors">
                    {{ $isFr ? 'Se connecter' : 'Sign in' }}
                </a>
                @endif

                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-md hover:bg-[#E7E1D4]/50" aria-label="Menu">
                    <i data-lucide="menu" class="w-5 h-5 text-[#262521]"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden pb-4 border-t border-[#E7E1D4] pt-3">
            <form action="{{ route('gallery.search') }}" method="GET" class="relative mb-3">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input name="q" type="search" placeholder="{{ $isFr ? 'Rechercher un produit, artisan, catégorie...' : 'Search a product, artisan, category...' }}"
                    class="ui-field pr-9">
                <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#55524A]">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </form>
            <div class="flex items-center gap-4 px-1 mb-2">
                <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" class="flex items-center gap-2 text-[13.5px] font-medium text-[#1D1B16]">
                    <i data-lucide="heart" class="w-4 h-4"></i>{{ $isFr ? 'Favoris' : 'Saved' }}
                </a>
                <a href="{{ $siacUser ? route('messages.inbox') : '/login?lang=' . $lang }}" class="flex items-center gap-2 text-[13.5px] font-medium text-[#1D1B16]">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>{{ $isFr ? 'Demandes' : 'Inquiries' }}
                </a>
            </div>
            {{-- Main page links — same $dirNavItems array as the desktop bar below --}}
            <div class="mb-2">
                @foreach($dirNavItems as [$mmKey, $mmIcon, $mmLabel, $mmHref])
                <a href="{{ $mmHref }}" class="flex items-center gap-2.5 px-1 py-2 text-[13.5px] {{ $mmKey === $dirNavActive ? 'font-semibold text-leaf' : 'text-[#1D1B16] hover:text-leaf' }}">
                    <i data-lucide="{{ $mmIcon }}" class="w-4 h-4 text-[#55524A]"></i>{{ $mmLabel }}
                </a>
                @endforeach
            </div>
            <div class="border-t border-[#E7E1D4] pt-2 flex items-center justify-between px-1">
                @if($siacUser)
                <a href="{{ route('dashboard.siac') }}" class="inline-flex items-center bg-[#02301B] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                @else
                <a href="{{ route('login', ['lang' => $lang]) }}" class="inline-flex items-center bg-[#02301B] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Se connecter' : 'Sign in' }}</a>
                <a href="{{ route('register.quick', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 border border-[#02301B] text-[#02301B] text-[13px] font-semibold px-4 py-2 rounded-lg"><i data-lucide="zap" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Inscription rapide' : 'Quick signup' }}</a>
                @endif
                <span class="flex items-center gap-2 text-[13px] font-semibold">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="{{ $isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">FR</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ !$isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">EN</a>
                </span>
            </div>
        </div>
    </div>
</header>

{{-- Secondary icon nav bar — always rendered, same $dirNavItems as the mobile menu above --}}
<div class="hidden lg:block bg-[#FEFEFE] border-b border-[#EFEDEA]">
    <div class="max-w-[1472px] mx-auto px-4 sm:px-6 overflow-x-auto">
        <nav class="flex items-center justify-center gap-6 xl:gap-9 w-max mx-auto">
            @foreach($dirNavItems as [$dnKey, $dnIcon, $dnLabel, $dnHref])
            @if($dnKey === 'categories' && !empty($navSectors) && $navSectors->count())
            {{-- Categories megamenu: official sectors → filières --}}
            <div class="relative group">
                <a href="{{ $dnHref }}" class="relative flex items-center gap-2 py-3 text-[13px] {{ $dnKey === $dirNavActive ? 'font-semibold text-[#14532D]' : 'font-medium text-[#3A3A35] hover:text-leaf' }} transition-colors whitespace-nowrap">
                    <i data-lucide="{{ $dnIcon }}" class="w-[15px] h-[15px]"></i>
                    {{ $dnLabel }}
                    <i data-lucide="chevron-down" class="w-3 h-3 text-[#8A857A]"></i>
                    @if($dnKey === $dirNavActive)<span class="absolute left-0 right-0 bottom-0 h-[3px] bg-[#E7A320]"></span>@endif
                </a>
                <div class="absolute left-1/2 -translate-x-1/2 top-full pt-2 hidden group-hover:block z-50">
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
            <a href="{{ $dnHref }}" class="relative flex items-center gap-2 py-3 text-[13px] {{ $dnKey === $dirNavActive ? 'font-semibold text-[#14532D]' : 'font-medium text-[#3A3A35] hover:text-leaf' }} transition-colors whitespace-nowrap">
                <i data-lucide="{{ $dnIcon }}" class="w-[15px] h-[15px]"></i>
                {{ $dnLabel }}
                @if($dnKey === $dirNavActive)<span class="absolute left-0 right-0 bottom-0 h-[3px] bg-[#E7A320]"></span>@endif
            </a>
            @endif
            @endforeach
        </nav>
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
