<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description ?? ($lang === 'fr' ? 'Artisan Hub 237 — Découvrez les artisans et producteurs camerounais.' : 'Artisan Hub 237 — Discover Cameroonian artisans and producers.') }}">
    <title>{{ $title ?? 'Artisan Hub 237' }}</title>

    <!-- Tailwind CSS (local vendor copy — keeps the platform styled offline) -->
    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-brand-100: 253 240 211;
            --c-brand-200: 250 218 154;
            --c-brand-300: 247 192 98;
            --c-brand-400: 244 163 42;
            --c-brand-50: 254 249 238;
            --c-brand-500: 232 136 14;
            --c-brand-600: 204 106 9;
            --c-brand-700: 168 78 11;
            --c-brand-800: 135 61 16;
            --c-brand-900: 110 51 17;
            --c-forest-100: 219 240 227;
            --c-forest-200: 184 224 201;
            --c-forest-400: 91 168 131;
            --c-forest-50: 240 249 244;
            --c-forest-500: 45 106 79;
            --c-forest-600: 27 67 50;
            --c-forest-700: 13 43 30;
            --c-forest-800: 8 32 24;
            --c-forest-900: 3 19 14;
            --c-leaf: 20 101 47;
        }
    </style>

    <!-- Lucide Icons (local vendor copy) -->
    @include('pages.partials.icons')

    <!-- Fonts (local vendor copy) -->

    <style>
        /* Nothing may scroll the page sideways on a phone; wide content
           (tables, diagrams) scrolls inside its own container instead. */
        html, body { overflow-x: clip; }

        body { font-family: 'Poppins', system-ui, sans-serif; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    @include('pages.partials.seo-head')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
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
            'href'   => $siacUser ? route('dashboard.siac') : route('login'),
            'icon'   => 'user',
            'label'  => $bottomNavLang === 'fr' ? 'Profil' : 'Profile',
            'active' => request()->is('tableau-de-bord') || request()->is('tableau-de-bord/entrepreneur') || request()->is('tableau-de-bord/admin*') || request()->is('login') || request()->is('inscription'),
        ],
    ];
@endphp
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-[#EEEDEA] flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @foreach($bottomTabs as $tab)
    <a href="{{ $tab['href'] }}" class="flex-1 min-w-0 flex flex-col items-center justify-center gap-0.5 py-2 {{ $tab['active'] ? 'text-[#14652F]' : 'text-[#8A857A]' }}">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5 {{ $tab['active'] ? 'fill-[#DFEDE3]' : '' }}"></i>
        {{-- `self-stretch` takes the label out of the row's `items-center` sizing
             so it spans the full column and centres each wrapped line — a 5-tab
             row at the universal 16px means the longest label ("Messages") no
             longer fits one line at 360px, and wrapping to two centred lines
             beats letting it bleed into the next tab. --}}
        <span class="text-[16px] md:text-[10px] font-semibold self-stretch text-center leading-tight break-words">{{ $tab['label'] }}</span>
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
