<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description ?? ($lang === 'fr' ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun — Découvrez les artisans et producteurs camerounais.' : 'National Virtual Gallery of Cameroonian Crafts — Discover Cameroonian artisans and producers.') }}">
    <title>{{ $title ?? 'SIAC — Galerie Virtuelle' }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fef9ee',
                            100: '#fdf0d3',
                            200: '#fada9a',
                            300: '#f7c062',
                            400: '#f4a32a',
                            500: '#e8880e',
                            600: '#cc6a09',
                            700: '#a84e0b',
                            800: '#873d10',
                            900: '#6e3311',
                        },
                        forest: {
                            50:  '#f0f9f4',
                            100: '#dbf0e3',
                            200: '#b8e0c9',
                            300: '#8cc9a8',
                            400: '#5ba883',
                            500: '#2d6a4f',
                            600: '#1b4332',
                            700: '#0d2b1e',
                            800: '#082018',
                            900: '#03130e',
                        },
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- Inter font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

<!-- Top bar: language + search hint -->
<div class="bg-forest-600 text-white text-xs py-1.5">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
        <span class="flex items-center gap-1.5">
            <i data-lucide="map-pin" class="w-3 h-3"></i>
            {{ $lang === 'fr' ? 'Cameroun — SIAC 2026' : 'Cameroon — SIAC 2026' }}
        </span>
        <div class="flex items-center gap-3">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="{{ $lang === 'fr' ? 'font-semibold underline' : 'opacity-70 hover:opacity-100' }}">FR</a>
            <span class="opacity-40">|</span>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ $lang === 'en' ? 'font-semibold underline' : 'opacity-70 hover:opacity-100' }}">EN</a>
        </div>
    </div>
</div>

<!-- Nav -->
<nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between h-14">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-2.5 shrink-0">
                <div class="w-7 h-7 bg-forest-500 rounded flex items-center justify-center">
                    <i data-lucide="store" class="w-4 h-4 text-white"></i>
                </div>
                <span class="font-bold text-gray-900 text-sm leading-tight hidden sm:block">
                    {{ $lang === 'fr' ? 'Galerie Artisanat' : 'Craft Gallery' }}<br>
                    <span class="font-normal text-gray-400 text-xs">SIAC Cameroun</span>
                </span>
            </a>

            <!-- Desktop nav links -->
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-1.5">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}
                </a>
                <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    {{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}
                </a>
                <a href="{{ url('/docs/api') }}" class="px-3 py-1.5 rounded text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 flex items-center gap-1.5" target="_blank">
                    <i data-lucide="code-2" class="w-4 h-4"></i>
                    API
                </a>
            </div>

            <!-- Search + auth + mobile menu -->
            <div class="flex items-center gap-2">
                <form action="{{ route('businesses.index', ['lang' => $lang]) }}" method="GET" class="relative hidden sm:block">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <i data-lucide="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"></i>
                    <input name="q" value="{{ request('q') }}" type="search"
                        placeholder="{{ $lang === 'fr' ? 'Rechercher...' : 'Search...' }}"
                        class="pl-8 pr-3 py-1.5 text-sm bg-gray-100 border border-transparent rounded-lg focus:outline-none focus:border-forest-400 focus:bg-white w-44 transition-all">
                </form>

                @php $siacUser = session('siac_user'); @endphp
                @if($siacUser)
                    {{-- Logged in --}}
                    <a href="/tableau-de-bord" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
                    </a>
                    <div class="relative group hidden sm:block">
                        <button class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-sm text-gray-700 hover:bg-gray-100 transition-colors border border-gray-200">
                            <div class="w-5 h-5 bg-forest-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-[10px] font-bold">{{ strtoupper(substr($siacUser['name'] ?? 'U', 0, 1)) }}</span>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400"></i>
                        </button>
                        <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 hidden group-hover:block z-50">
                            <div class="px-3 py-2 border-b border-gray-50">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $siacUser['name'] ?? '' }}</p>
                                <p class="text-[10px] text-gray-400 truncate">{{ $siacUser['email'] ?? '' }}</p>
                            </div>
                            <a href="/tableau-de-bord" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-gray-400"></i>
                                {{ $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
                            </a>
                            <form method="POST" action="/logout">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Guest --}}
                    <a href="/login?lang={{ $lang }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-colors">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Connexion' : 'Sign In' }}
                    </a>
                    <a href="/inscription?lang={{ $lang }}" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-forest-500 text-white rounded-lg text-sm font-semibold hover:bg-forest-600 transition-colors">
                        {{ $lang === 'fr' ? 'S\'inscrire' : 'Register' }}
                    </a>
                @endif

                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden md:hidden pb-3 border-t border-gray-100 pt-2">
            <form action="{{ route('businesses.index', ['lang' => $lang]) }}" method="GET" class="mb-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input name="q" type="search" placeholder="{{ $lang === 'fr' ? 'Rechercher...' : 'Search...' }}"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-gray-100 rounded-lg focus:outline-none focus:bg-white border border-transparent focus:border-forest-400">
                </div>
            </form>
            <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}
            </a>
            <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                <i data-lucide="layers" class="w-4 h-4"></i>
                {{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}
            </a>
            <a href="{{ url('/docs/api') }}" class="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                <i data-lucide="code-2" class="w-4 h-4"></i>
                API
            </a>
            <div class="border-t border-gray-100 pt-2 mt-1">
                @if(session('siac_user'))
                    <a href="/tableau-de-bord" class="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded font-medium">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Tableau de bord' : 'Dashboard' }}
                    </a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-2 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
                        </button>
                    </form>
                @else
                    <a href="/login?lang={{ $lang }}" class="flex items-center gap-2 px-2 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'Connexion' : 'Sign In' }}
                    </a>
                    <a href="/inscription?lang={{ $lang }}" class="flex items-center gap-2 px-2 py-2 text-sm text-forest-500 font-semibold hover:bg-forest-50 rounded">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        {{ $lang === 'fr' ? 'S\'inscrire' : 'Register' }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</nav>

<!-- Main content -->
<main>
    @yield('content')
</main>

<!-- Footer -->
<footer class="bg-gray-900 text-gray-400 mt-16">
    <div class="max-w-6xl mx-auto px-4 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 bg-forest-500 rounded flex items-center justify-center">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-white"></i>
                    </div>
                    <span class="text-white font-semibold text-sm">{{ $lang === 'fr' ? 'Galerie Artisanat' : 'Craft Gallery' }}</span>
                </div>
                <p class="text-xs leading-relaxed">
                    {{ $lang === 'fr'
                        ? 'Plateforme officielle SIAC — Salon International Interprofessionnel de l\'Aquaculture du Cameroun.'
                        : 'Official SIAC platform — International Interprofessional Aquaculture Fair of Cameroon.'
                    }}
                </p>
            </div>
            <div>
                <h4 class="text-white text-sm font-medium mb-3">{{ $lang === 'fr' ? 'Navigation' : 'Navigation' }}</h4>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Accueil' : 'Home' }}</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Entreprises' : 'Businesses' }}</a></li>
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $lang === 'fr' ? 'Secteurs' : 'Sectors' }}</a></li>
                    <li><a href="{{ url('/docs/api') }}" class="hover:text-white transition-colors">API Docs</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-sm font-medium mb-3">{{ $lang === 'fr' ? 'Contact' : 'Contact' }}</h4>
                <ul class="space-y-1.5 text-xs">
                    <li class="flex items-center gap-1.5"><i data-lucide="mail" class="w-3 h-3"></i> contact@artisanatcameroun.cm</li>
                    <li class="flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3 h-3"></i> Yaoundé, Cameroun</li>
                    <li class="flex items-center gap-1.5 mt-3"><i data-lucide="calendar" class="w-3 h-3"></i> SIAC 2026 — 15 Nov. 2026</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs">
            <span>&copy; {{ date('Y') }} SIAC Cameroun. {{ $lang === 'fr' ? 'Tous droits réservés.' : 'All rights reserved.' }}</span>
            <span class="flex items-center gap-1">
                <i data-lucide="code-2" class="w-3 h-3"></i>
                API v1 — <a href="{{ url('/docs/api') }}" class="hover:text-white ml-1">docs</a>
            </span>
        </div>
    </div>
</footer>

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
