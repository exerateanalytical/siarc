<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description ?? ($lang === 'fr' ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun — Découvrez les artisans et producteurs camerounais.' : 'National Virtual Gallery of Cameroonian Crafts — Discover Cameroonian artisans and producers.') }}">
    <title>{{ $title ?? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' }}</title>

    <!-- Tailwind CSS (local vendor copy — keeps the platform styled offline) -->
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Semantic tints still referenced by content sections of inherited pages
                        brand: {
                            50:  '#fef9ee', 100: '#fdf0d3', 200: '#fada9a', 300: '#f7c062', 400: '#f4a32a',
                            500: '#e8880e', 600: '#cc6a09', 700: '#a84e0b', 800: '#873d10', 900: '#6e3311',
                        },
                        forest: {
                            50:  '#f0f9f4', 100: '#dbf0e3', 200: '#b8e0c9', 300: '#8cc9a8', 400: '#5ba883',
                            500: '#2d6a4f', 600: '#1b4332', 700: '#0d2b1e', 800: '#082018', 900: '#03130e',
                        },
                        leaf: '#14652F',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <!-- Lucide Icons (local vendor copy) -->
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>

    <!-- Fonts (local vendor copy) -->
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

<!-- Top utility bar: tagline + language -->
<div class="bg-[#0A2C1D] text-white text-[11.5px] py-1.5">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
        <span class="flex items-center gap-1.5">
            <i data-lucide="map-pin" class="w-3 h-3 text-[#E5A82E]"></i>
            {{ $lang === 'fr' ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}
        </span>
        <div class="flex items-center gap-3">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="{{ $lang === 'fr' ? 'font-semibold text-[#E5A82E]' : 'opacity-70 hover:opacity-100' }}">FR</a>
            <span class="opacity-40">|</span>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ $lang === 'en' ? 'font-semibold text-[#E5A82E]' : 'opacity-70 hover:opacity-100' }}">EN</a>
        </div>
    </div>
</div>

<!-- Nav -->
<nav class="bg-white border-b border-[#EEEDEA] sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between h-[64px]">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[38px] h-[41px] object-contain">
                <span class="leading-tight hidden lg:block">
                    <span class="block text-[11px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $lang === 'fr' ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                    <span class="block text-[11px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $lang === 'fr' ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                </span>
            </a>

            <!-- Desktop nav links -->
            <div class="hidden lg:flex items-center gap-1">
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="px-3 py-2 rounded-lg text-[13px] text-[#3B382F] hover:text-[#14652F] hover:bg-[#F3F8F3] flex items-center gap-1.5 transition-colors">
                    <i data-lucide="building-2" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}
                </a>
                <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="px-3 py-2 rounded-lg text-[13px] text-[#3B382F] hover:text-[#14652F] hover:bg-[#F3F8F3] flex items-center gap-1.5 transition-colors">
                    <i data-lucide="layers" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}
                </a>
                <a href="{{ route('events.index') }}" class="px-3 py-2 rounded-lg text-[13px] text-[#3B382F] hover:text-[#14652F] hover:bg-[#F3F8F3] flex items-center gap-1.5 transition-colors">
                    <i data-lucide="calendar-days" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $lang === 'fr' ? 'Événements' : 'Events' }}
                </a>
                <a href="{{ url('/docs/api') }}" class="px-3 py-2 rounded-lg text-[13px] text-[#3B382F] hover:text-[#14652F] hover:bg-[#F3F8F3] flex items-center gap-1.5 transition-colors" target="_blank">
                    <i data-lucide="code-2" class="w-4 h-4" style="stroke-width:1.7"></i>
                    API
                </a>
            </div>

            <!-- Search + auth + mobile menu -->
            <div class="flex items-center gap-2">
                <form action="{{ route('gallery.search', ['lang' => $lang]) }}" method="GET" class="relative hidden md:block">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#8A857A] pointer-events-none"></i>
                    <input name="q" value="{{ request('q') }}" type="search"
                        placeholder="{{ $lang === 'fr' ? 'Rechercher...' : 'Search...' }}"
                        class="pl-9 pr-3 py-2 text-[13px] bg-[#FBFBFA] border border-[#E7E7E5] rounded-lg focus:outline-none focus:border-[#14532D] focus:bg-white w-36 xl:w-44 transition-all placeholder-[#8A857A]">
                </form>

                @php
                    $siacUser = session('siac_user');
                    $unreadNotifications = $siacUser
                        ? \App\Modules\Notifications\Models\UserNotification::where('user_id', $siacUser['id'])->unread()->count()
                        : 0;
                @endphp
                @if($siacUser)
                    {{-- Logged in --}}
                    <a href="{{ route('notifications.index') }}" class="relative hidden sm:flex items-center justify-center w-9 h-9 rounded-lg text-[#3B382F] hover:bg-[#F6F7F6] transition-colors">
                        <i data-lucide="bell" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                        @if($unreadNotifications > 0)
                        <span class="absolute top-0.5 right-0.5 min-w-[16px] h-4 px-1 bg-[#E01E1E] text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                        @endif
                    </a>
                    <a href="/tableau-de-bord" class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-lg text-[13px] font-semibold text-[#14652F] hover:bg-[#F3F8F3] transition-colors">
                        <i data-lucide="layout-dashboard" class="w-4 h-4" style="stroke-width:1.7"></i>
                        {{ $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
                    </a>
                    <div class="relative group hidden sm:block">
                        <button class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg text-[13px] text-[#3B382F] hover:bg-[#F6F7F6] transition-colors border border-[#E7E7E5]">
                            <div class="w-6 h-6 bg-[#DFEDE3] rounded-full flex items-center justify-center">
                                <span class="text-[#14652F] text-[10px] font-bold">{{ strtoupper(substr($siacUser['name'] ?? 'U', 0, 1)) }}</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                        </button>
                        <div class="absolute right-0 top-full mt-1 w-52 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1 hidden group-hover:block z-50">
                            <div class="px-3.5 py-2.5 border-b border-[#F0F1F0]">
                                <p class="text-[12px] font-bold text-[#1B1B18] truncate">{{ $siacUser['name'] ?? '' }}</p>
                                <p class="text-[10.5px] text-[#8A857A] truncate">{{ $siacUser['email'] ?? '' }}</p>
                            </div>
                            <a href="/tableau-de-bord" class="flex items-center gap-2.5 px-3.5 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-[#55524A]" style="stroke-width:1.7"></i>
                                {{ $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
                            </a>
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-[12.5px] text-[#B42025] hover:bg-[#FDE8E8] text-left">
                                    <i data-lucide="log-out" class="w-4 h-4" style="stroke-width:1.7"></i>
                                    {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Guest --}}
                    <a href="/login?lang={{ $lang }}" class="hidden sm:flex items-center gap-1.5 px-3 py-2 rounded-lg text-[13px] font-semibold text-[#3B382F] hover:text-[#14652F] hover:bg-[#F3F8F3] transition-colors">
                        <i data-lucide="log-in" class="w-4 h-4" style="stroke-width:1.7"></i>
                        {{ $lang === 'fr' ? 'Connexion' : 'Sign In' }}
                    </a>
                    <a href="/inscription?lang={{ $lang }}" class="hidden sm:flex items-center gap-1.5 px-4 py-2 bg-[#0A3020] text-white rounded-lg text-[13px] font-semibold hover:bg-[#14652F] transition-colors">
                        {{ $lang === 'fr' ? 'S\'inscrire' : 'Register' }}
                    </a>
                @endif

                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg hover:bg-[#F6F7F6]">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden pb-3 border-t border-[#F0F1F0] pt-2">
            <form action="{{ route('gallery.search', ['lang' => $lang]) }}" method="GET" class="mb-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#8A857A]"></i>
                    <input name="q" type="search" placeholder="{{ $lang === 'fr' ? 'Rechercher...' : 'Search...' }}"
                        class="w-full pl-9 pr-3 py-2 text-[13px] bg-[#FBFBFA] rounded-lg focus:outline-none focus:bg-white border border-[#E7E7E5] focus:border-[#14532D]">
                </div>
            </form>
            <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#3B382F] hover:bg-[#F6F7F6] rounded-lg">
                <i data-lucide="building-2" class="w-4 h-4" style="stroke-width:1.7"></i>
                {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}
            </a>
            <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#3B382F] hover:bg-[#F6F7F6] rounded-lg">
                <i data-lucide="layers" class="w-4 h-4" style="stroke-width:1.7"></i>
                {{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}
            </a>
            <a href="{{ route('events.index') }}" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#3B382F] hover:bg-[#F6F7F6] rounded-lg">
                <i data-lucide="calendar-days" class="w-4 h-4" style="stroke-width:1.7"></i>
                {{ $lang === 'fr' ? 'Événements' : 'Events' }}
            </a>
            <a href="{{ url('/docs/api') }}" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#3B382F] hover:bg-[#F6F7F6] rounded-lg">
                <i data-lucide="code-2" class="w-4 h-4" style="stroke-width:1.7"></i>
                API
            </a>
            <div class="border-t border-[#F0F1F0] pt-2 mt-1">
                @if(session('siac_user'))
                    <a href="/tableau-de-bord" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#14652F] hover:bg-[#F3F8F3] rounded-lg font-semibold">
                        <i data-lucide="layout-dashboard" class="w-4 h-4" style="stroke-width:1.7"></i>
                        {{ $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
                    </a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#B42025] hover:bg-[#FDE8E8] rounded-lg">
                            <i data-lucide="log-out" class="w-4 h-4" style="stroke-width:1.7"></i>
                            {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
                        </button>
                    </form>
                @else
                    <a href="/login?lang={{ $lang }}" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#3B382F] hover:bg-[#F6F7F6] rounded-lg">
                        <i data-lucide="log-in" class="w-4 h-4" style="stroke-width:1.7"></i>
                        {{ $lang === 'fr' ? 'Connexion' : 'Sign In' }}
                    </a>
                    <a href="/inscription?lang={{ $lang }}" class="flex items-center gap-2.5 px-2 py-2 text-[13px] text-[#14652F] font-semibold hover:bg-[#F3F8F3] rounded-lg">
                        <i data-lucide="user-plus" class="w-4 h-4" style="stroke-width:1.7"></i>
                        {{ $lang === 'fr' ? 'S\'inscrire' : 'Register' }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- Tricolor identity strip -->
<div class="flex h-[6px]">
    <div class="w-[46%] bg-[#094F2B]"></div>
    <div class="w-[26%] bg-[#B61012]"></div>
    <div class="flex-1 bg-[#E9A411]"></div>
</div>

<!-- Main content -->
<main class="pb-16 sm:pb-0">
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-[#0B2C1E] text-[#A8C0AF] mt-16">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[34px] h-[37px] object-contain">
                    <span class="text-white font-bold text-[12px] uppercase tracking-[0.02em] leading-tight">{{ $lang === 'fr' ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</span>
                </div>
                <p class="text-[12px] leading-relaxed">
                    {{ $lang === 'fr'
                        ? 'Plateforme officielle de promotion et de valorisation de l\'artisanat camerounais à travers le digital.'
                        : 'Official platform for promoting and showcasing Cameroonian craftsmanship through digital.'
                    }}
                </p>
            </div>
            <div>
                <h4 class="text-[#E5A82E] text-[12px] font-bold uppercase tracking-[0.06em] mb-3">{{ $lang === 'fr' ? 'Navigation' : 'Navigation' }}</h4>
                <ul class="space-y-1.5 text-[12px]">
                    <li><a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Accueil' : 'Home' }}</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</a></li>
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}</a></li>
                    <li><a href="{{ route('partners.index') }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Partenaires' : 'Partners' }}</a></li>
                    <li><a href="{{ url('/docs/api') }}" class="hover:text-white transition-colors">API Docs</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'À propos' : 'About' }}</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Conditions' : 'Terms' }}</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Confidentialité' : 'Privacy' }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-[#E5A82E] text-[12px] font-bold uppercase tracking-[0.06em] mb-3">{{ $lang === 'fr' ? 'Contact' : 'Contact' }}</h4>
                <ul class="space-y-1.5 text-[12px]">
                    <li class="flex items-center gap-2"><i data-lucide="mail" class="w-3 h-3 text-[#E5A82E]"></i> contact@artisanatcameroun.cm</li>
                    <li class="flex items-center gap-2"><i data-lucide="map-pin" class="w-3 h-3 text-[#E5A82E]"></i> Yaoundé, Cameroun</li>
                    <li class="flex items-center gap-2 mt-3"><i data-lucide="calendar" class="w-3 h-3 text-[#E5A82E]"></i> SIAC 2026 — 15 Nov. 2026</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px]">
            <span>&copy; {{ date('Y') }} {{ $lang === 'fr' ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</span>
            <span class="flex items-center gap-1">
                <i data-lucide="code-2" class="w-3 h-3"></i>
                API v1 — <a href="{{ url('/docs/api') }}" class="hover:text-white ml-1">docs</a>
            </span>
        </div>
    </div>
</footer>

<!-- Mobile bottom navigation -->
@php
    $siacUser = session('siac_user');
    $bottomNavLang = $lang ?? 'fr';
    $bottomTabs = [
        [
            'href'   => route('home', ['lang' => $bottomNavLang]),
            'icon'   => 'home',
            'label'  => $bottomNavLang === 'fr' ? 'Accueil' : 'Home',
            'active' => request()->is('/'),
        ],
        [
            'href'   => route('businesses.index', ['lang' => $bottomNavLang]),
            'icon'   => 'compass',
            'label'  => $bottomNavLang === 'fr' ? 'Explorer' : 'Explore',
            'active' => request()->is('galerie/entreprises*') || request()->is('galerie/secteurs*'),
        ],
        [
            'href'   => $siacUser ? route('saved.index') : '/login',
            'icon'   => 'bookmark',
            'label'  => $bottomNavLang === 'fr' ? 'Favoris' : 'Saved',
            'active' => request()->is('tableau-de-bord/sauvegardes'),
        ],
        [
            'href'   => $siacUser ? route('messages.inbox') : '/login',
            'icon'   => 'message-circle',
            'label'  => $bottomNavLang === 'fr' ? 'Messages' : 'Messages',
            'active' => request()->is('tableau-de-bord/messages*'),
        ],
        [
            'href'   => $siacUser ? '/tableau-de-bord' : '/login',
            'icon'   => 'user',
            'label'  => $bottomNavLang === 'fr' ? 'Profil' : 'Profile',
            'active' => request()->is('tableau-de-bord') || request()->is('tableau-de-bord/entrepreneur') || request()->is('tableau-de-bord/admin*') || request()->is('login') || request()->is('inscription'),
        ],
    ];
@endphp
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-[#EEEDEA] flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @foreach($bottomTabs as $tab)
    <a href="{{ $tab['href'] }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 {{ $tab['active'] ? 'text-[#14652F]' : 'text-[#8A857A]' }}">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5 {{ $tab['active'] ? 'fill-[#DFEDE3]' : '' }}"></i>
        <span class="text-[10px] font-semibold">{{ $tab['label'] }}</span>
    </a>
    @endforeach
</nav>

<script>
    // Init Lucide icons
    lucide.createIcons();

    // Mobile menu toggle
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    if (btn && menu) {
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    }
</script>
</body>
</html>
