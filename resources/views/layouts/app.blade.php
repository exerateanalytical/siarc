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

@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');
@endphp
{{-- New-design chrome: the directory replica header (same family as the
     product/vendor directories) so every layouts/app page shares the platform identity. --}}
@include('pages.partials.directory-header')


<!-- Main content -->
<main class="pb-16 sm:pb-0">
    @yield('content')
</main>

<!-- Footer -->
{{-- New-design chrome: the directory replica footer --}}
@include('pages.partials.directory-footer')

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
