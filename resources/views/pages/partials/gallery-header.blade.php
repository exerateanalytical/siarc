{{-- Gallery replica header: tricolor bar + white site header with search.
     Expects: $lang, $isFr, $siacUser, $galleryActive (nav key, e.g. 'categories') --}}

@php
    $galleryNav = [
        ['explorer',    $isFr ? 'Explorer' : 'Explore',         route('businesses.index', ['lang' => $lang])],
        ['collections', $isFr ? 'Collections' : 'Collections',  route('industries.index', ['lang' => $lang])],
        ['artisans',    $isFr ? 'Artisans' : 'Artisans',        route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['regions',     $isFr ? 'Régions' : 'Regions',          route('businesses.index', ['lang' => $lang])],
        ['categories',  $isFr ? 'Catégories' : 'Categories',    route('industries.index', ['lang' => $lang])],
        ['businesses',  $isFr ? 'Entreprises' : 'Businesses',   route('businesses.index', ['lang' => $lang])],
        ['events',      $isFr ? 'Événements' : 'Events',        route('events.index')],
        ['about',       $isFr ? 'À propos' : 'About',           route('about')],
    ];
@endphp

<!-- Tricolor top bar -->
<div class="relative flex h-6 overflow-hidden">
    <div class="w-[28%] bg-[#034226]"></div>
    <div class="relative w-[33%] bg-[#B70415]">
        <svg viewBox="0 0 24 24" class="absolute left-[75%] top-1/2 -translate-y-1/2 w-3 h-3 fill-[#F5C33B]" aria-hidden="true">
            <path d="M12 1.5 14.7 8.6l7.6.3-6 4.7 2.1 7.3L12 16.6 5.6 20.9l2.1-7.3-6-4.7 7.6-.3z"/>
        </svg>
    </div>
    <div class="relative flex-1 bg-gradient-to-r from-[#F2B01C] to-[#E6C89A] flex items-center justify-end pr-6">
        <svg viewBox="0 0 24 24" class="absolute left-[2px] top-1/2 -translate-y-1/2 w-3 h-3 fill-[#F5C33B]" aria-hidden="true">
            <path d="M12 1.5 14.7 8.6l7.6.3-6 4.7 2.1 7.3L12 16.6 5.6 20.9l2.1-7.3-6-4.7 7.6-.3z"/>
        </svg>
        <span class="hidden sm:block text-[12px] font-medium text-[#3A2E10]">
            {{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}
        </span>
    </div>
</div>

<!-- Header -->
<header class="bg-[#FEFDFC] border-b border-[#EFEDEA]">
    <div class="max-w-[1472px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between gap-4 py-4">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3.5 shrink-0">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[54px] h-[58px] xl:w-[60px] xl:h-[64px] object-contain">
                <span class="leading-tight">
                    <span class="block text-[13px] xl:text-[15.5px] font-bold tracking-[0.02em] text-[#1D1B16] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                    <span class="block text-[13px] xl:text-[15.5px] font-bold tracking-[0.02em] text-[#1D1B16] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                    <span class="block text-[10.5px] xl:text-[11.5px] text-[#6F6B60] mt-1 max-w-[280px]">{{ $isFr ? 'Plateforme officielle de promotion de l\'artisanat camerounais' : 'Official platform for the promotion of Cameroonian crafts' }}</span>
                </span>
            </a>

            <nav class="hidden lg:flex items-center gap-4 xl:gap-[30px]">
                @foreach($galleryNav as [$navKey, $navLabel, $navHref])
                <a href="{{ $navHref }}" class="relative whitespace-nowrap text-[13px] xl:text-[14.5px] {{ $navKey === ($galleryActive ?? '') ? 'font-semibold text-[#14532D]' : 'font-medium text-[#1D1B16]' }} hover:text-leaf transition-colors pb-1">
                    {{ $navLabel }}
                    @if($navKey === ($galleryActive ?? ''))<span class="absolute left-0 right-0 -bottom-[9px] h-[3px] bg-[#14532D]"></span>@endif
                </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3 xl:gap-4 shrink-0">
                <form action="{{ route('gallery.search') }}" method="GET" class="hidden md:block relative">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="q" type="search" placeholder="{{ $isFr ? 'Rechercher un produit, artisan...' : 'Search a product, artisan...' }}"
                        class="w-[180px] xl:w-[228px] h-[38px] bg-white border border-[#E5E3E0] rounded-lg pl-4 pr-9 text-[12.5px] placeholder-[#8A857A] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/40 transition">
                    <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#55524A] hover:text-leaf transition-colors">
                        <i data-lucide="search" class="w-[16px] h-[16px]"></i>
                    </button>
                </form>

                <div class="relative group hidden sm:block">
                    <button class="flex items-center gap-1 px-1 py-2 text-[13.5px] font-semibold text-[#1D1B16]">
                        {{ strtoupper($lang) }}
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                    </button>
                    <div class="absolute right-0 top-full w-28 bg-white rounded-lg shadow-lg border border-sand py-1 hidden group-hover:block z-50">
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-leaf' : 'text-[#262521] hover:bg-cream' }}">FR — Français</a>
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-leaf' : 'text-[#262521] hover:bg-cream' }}">EN — English</a>
                    </div>
                </div>

                @if($siacUser)
                <a href="/tableau-de-bord" class="hidden sm:inline-flex items-center gap-2 whitespace-nowrap bg-[#0A3020] hover:bg-leaf text-white text-[13.5px] font-semibold pl-4 pr-5 h-[40px] rounded-lg transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    {{ $isFr ? 'Tableau de bord' : 'Dashboard' }}
                </a>
                @else
                <a href="/login?lang={{ $lang }}" class="hidden sm:inline-flex items-center gap-2 whitespace-nowrap bg-[#0A3020] hover:bg-leaf text-white text-[13.5px] font-semibold pl-4 pr-5 h-[40px] rounded-lg transition-colors">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    {{ $isFr ? 'Connexion' : 'Sign in' }}
                </a>
                @endif

                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-md hover:bg-sand/50" aria-label="Menu">
                    <i data-lucide="menu" class="w-5 h-5 text-[#262521]"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden pb-4 border-t border-sand pt-3">
            <form action="{{ route('gallery.search') }}" method="GET" class="relative mb-2 px-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input name="q" type="search" placeholder="{{ $isFr ? 'Rechercher un produit, artisan...' : 'Search a product, artisan...' }}"
                    class="w-full h-[38px] bg-white border border-[#E5E3E0] rounded-lg pl-4 pr-9 text-[13px] placeholder-[#8A857A] focus:outline-none focus:border-gold">
                <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}" class="absolute right-5 top-1/2 -translate-y-1/2 text-[#55524A]">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </form>
            @foreach($galleryNav as [$navKey, $navLabel, $navHref])
            <a href="{{ $navHref }}" class="block px-2 py-2 text-[14px] font-medium {{ $navKey === ($galleryActive ?? '') ? 'text-leaf' : 'text-[#262521]' }} hover:bg-sand/40 rounded">{{ $navLabel }}</a>
            @endforeach
            <div class="border-t border-sand mt-2 pt-2 flex items-center justify-between px-2">
                @if($siacUser)
                <a href="/tableau-de-bord" class="inline-flex items-center bg-[#0A3020] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                @else
                <a href="/login?lang={{ $lang }}" class="inline-flex items-center bg-[#0A3020] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Connexion' : 'Sign in' }}</a>
                @endif
                <span class="flex items-center gap-2 text-[13px] font-semibold">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="{{ $isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">FR</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ !$isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">EN</a>
                </span>
            </div>
        </div>
    </div>
</header>
