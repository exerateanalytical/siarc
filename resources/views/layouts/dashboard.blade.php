<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tableau de bord — Artisan Hub 237' }}</title>

    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-brand-100: 246 228 190;
            --c-brand-200: 239 208 138;
            --c-brand-300: 233 188 92;
            --c-brand-400: 229 168 46;
            --c-brand-50: 251 241 221;
            --c-brand-500: 201 148 46;
            --c-brand-600: 168 122 34;
            --c-brand-700: 138 109 31;
            --c-brand-800: 107 83 24;
            --c-brand-900: 74 57 16;
            --c-forest-100: 207 229 214;
            --c-forest-200: 191 220 200;
            --c-forest-400: 46 125 79;
            --c-forest-50: 226 243 232;
            --c-forest-500: 21 122 67;
            --c-forest-600: 20 83 45;
            --c-forest-700: 11 61 40;
            --c-forest-800: 2 48 27;
            --c-forest-900: 1 32 15;
            --c-leaf: 20 101 47;
        }
    </style>
    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        /* Nothing may scroll the page sideways on a phone; wide content
           (tables, diagrams) scrolls inside its own container instead. */
        html, body { overflow-x: clip; }
body { font-family: 'Poppins', system-ui, sans-serif; }</style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#F8F6F2] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@php
    $siacUser = session('siac_user') ?? [];
    // No explicit Spatie role means "buyer" — same convention the dashboard redirect route uses.
    $role = $siacUser['role'] ?? 'buyer';

    $roleMeta = [
        'super_admin'        => ['label' => ['fr' => 'Administrateur', 'en' => 'Administrator'], 'icon' => 'shield-check'],
        'admin'              => ['label' => ['fr' => 'Administrateur', 'en' => 'Administrator'], 'icon' => 'shield-check'],
        'moderator'          => ['label' => ['fr' => 'Modérateur', 'en' => 'Moderator'],          'icon' => 'shield-check'],
        'business_owner'     => ['label' => ['fr' => 'Artisan / Entreprise', 'en' => 'Artisan / Business'], 'icon' => 'briefcase'],
        'buyer'              => ['label' => ['fr' => 'Acheteur', 'en' => 'Buyer'],                'icon' => 'shopping-bag'],
        'regional_rep'       => ['label' => ['fr' => 'Représentant régional', 'en' => 'Regional Rep'], 'icon' => 'map'],
        'technical_reviewer' => ['label' => ['fr' => 'Département technique', 'en' => 'Technical Department'], 'icon' => 'microscope'],
    ];
    $meta = $roleMeta[$role] ?? ['label' => ['fr' => 'Utilisateur', 'en' => 'User'], 'icon' => 'user'];

    $lang = in_array(request()->cookie('lang'), ['fr', 'en']) ? request()->cookie('lang') : 'fr';

    // The per-role navigation used to be defined here as well, in a $navGroups
    // array of about a hundred lines covering all five roles. Nothing read it:
    // the rail is rendered by pages.partials.dashboard-sidebar, which builds its
    // own list. It was a second, silently diverging copy of the navigation — the
    // admin branch here still listed screens the other branch had already renamed.
    // Deleted; the partial is the one definition.

    $unreadNotifications = $siacUser
        ? \App\Modules\Notifications\Models\UserNotification::where('user_id', $siacUser['id'])->unread()->count()
        : 0;

    $dashName = $siacUser['name'] ?? 'Utilisateur';
    $dashInitials = strtoupper(collect(explode(' ', trim($dashName)))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')) ?: 'U';
@endphp

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar overlay (mobile) -->
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

    {{-- Canonical dashboard sidebar — shared with every other dashboard --}}
    @include("pages.partials.dashboard-sidebar", ["sidebarId" => "dashboard-sidebar"])

    <!-- Main column -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="sticky top-0 z-20 bg-white dark:bg-[#12150F] border-b border-[#EEEFEE] dark:border-[#262B21] h-[64px] flex items-center px-4 lg:px-6 gap-3 shrink-0">
            <button id="sidebar-toggle" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E]">
                <i data-lucide="menu" class="w-5 h-5 text-[#3B382F] dark:text-[#B4B5A6]"></i>
            </button>
            <h1 class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $pageTitle ?? '' }}</h1>
            <div class="flex-1"></div>

            <div class="flex items-center gap-2">
                {{-- Dark-mode control. Presence of a `.theme-toggle` in the page
                     suppresses the floating fallback in pages.partials.theme. --}}
                @include('pages.partials.theme-toggle')
                <a href="{{ request()->fullUrlWithQuery(['lang' => $lang === 'fr' ? 'en' : 'fr']) }}" class="hidden sm:flex items-center justify-center h-[34px] px-3 rounded-lg border border-[#E5E7E5] dark:border-[#262B21] text-[14px] md:text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#14532D] dark:hover:border-[#2E9250] transition-colors">
                    {{ strtoupper($lang === 'fr' ? 'en' : 'fr') }}
                </a>
                <a href="{{ route('messages.inbox') }}" class="relative flex items-center justify-center w-[38px] h-[38px] rounded-lg text-[#3B382F] dark:text-[#B4B5A6] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E] transition-colors" title="Messages">
                    <i data-lucide="message-circle" class="w-[19px] h-[19px]" style="stroke-width:1.7"></i>
                </a>
                <a href="{{ route('notifications.index') }}" class="relative flex items-center justify-center w-[38px] h-[38px] rounded-lg text-[#3B382F] dark:text-[#B4B5A6] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E] transition-colors" title="Notifications">
                    <i data-lucide="bell" class="w-[19px] h-[19px]" style="stroke-width:1.7"></i>
                    @if($unreadNotifications > 0)
                    <span class="absolute top-0.5 right-0.5 min-w-[20px] h-5 md:min-w-[17px] md:h-[17px] px-1 bg-[#E01E1E] dark:bg-[#C0343B] text-white text-[14px] md:text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.show') }}" class="w-[36px] h-[36px] bg-[#DFEDE3] dark:bg-[#0C3D1D] hover:bg-[#CFE5D6] dark:hover:bg-[#0C3D1D] rounded-full flex items-center justify-center ml-1 transition-colors" title="{{ $lang === 'fr' ? 'Mon profil' : 'My Profile' }}">
                    <span class="text-[#14652F] dark:text-[#339B56] text-[14px] md:text-[12px] font-bold">{{ $dashInitials }}</span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>

<script>
    lucide.createIcons();
    var sidebar = document.getElementById('dashboard-sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var toggle = document.getElementById('sidebar-toggle');
    // Same `.open` convention as every other dashboard (see partials/dashboard-sidebar)
    function openSidebar() { sidebar.classList.add('open'); overlay.classList.remove('hidden'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.add('hidden'); }
    if (toggle) toggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
</script>
</body>
</html>
