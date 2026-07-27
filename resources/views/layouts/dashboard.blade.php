<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tableau de bord — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Semantic scales re-pointed onto the canonical heritage palette,
                        // so every existing forest-*/brand-* class inherits site branding.
                        // brand = platform gold, forest = platform green.
                        brand:  { 50:'#FBF1DD',100:'#F6E4BE',200:'#EFD08A',300:'#E9BC5C',400:'#E5A82E',500:'#C9942E',600:'#A87A22',700:'#8A6D1F',800:'#6B5318',900:'#4A3910' },
                        forest: { 50:'#E2F3E8',100:'#CFE5D6',200:'#BFDCC8',300:'#8CC9A8',400:'#2E7D4F',500:'#157A43',600:'#14532D',700:'#0B3D28',800:'#02301B',900:'#01200F' },
                        leaf:   '#14652F',
                    },
                    fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body { font-family: 'Poppins', system-ui, sans-serif; }</style>
</head>
<body class="bg-[#F8F6F2] text-[#1D1B16] antialiased">

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

    // Sidebar nav, scoped per role. Each item: [route name, icon, label_fr, label_en]
    $navGroups = [];
    if (in_array($role, ['super_admin', 'admin', 'moderator'])) {
        $navGroups = [
            [
                'title' => ['fr' => 'Vue d\'ensemble', 'en' => 'Overview'],
                'items' => [
                    ['dashboard.admin', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                    ['admin.reports', 'bar-chart-2', 'Rapports & Statistiques', 'Reports & Statistics'],
                    ['admin.audit-log', 'history', 'Journal d\'audit', 'Audit Log'],
                ],
            ],
            [
                'title' => ['fr' => 'Modération', 'en' => 'Moderation'],
                'items' => [
                    ['admin.businesses', 'building-2', 'Entreprises', 'Businesses'],
                    ['admin.products', 'package', 'Produits & Services', 'Products & Services'],
                    ['admin.quotes', 'file-text', 'Devis & Commandes', 'Quotes & Orders'],
                    ['admin.industries', 'tags', 'Catégories & Régions', 'Categories & Regions'],
                    ['admin.verifications', 'badge-check', 'Vérifications', 'Verifications'],
                    ['admin.moderation', 'flag', 'Modération', 'Moderation'],
                    ['admin.api-consumers', 'key-round', 'API & Développeurs', 'API & Developers'],
                    ['admin.users', 'users', 'Utilisateurs', 'Users'],
                ],
            ],
            [
                'title' => ['fr' => 'Contenu', 'en' => 'Content'],
                'items' => [
                    ['admin.partners', 'handshake', 'Partenaires', 'Partners'],
                    ['admin.events', 'calendar-days', 'Événements', 'Events'],
                    ['admin.cms', 'file-text', 'Pages CMS', 'CMS Pages'],
                    ['admin.support', 'life-buoy', 'Support', 'Support'],
                ],
            ],
            [
                'title' => ['fr' => 'Configuration', 'en' => 'Configuration'],
                'items' => [
                    ['admin.settings', 'settings-2', 'Paramètres & Intégrations', 'Settings & Integrations'],
                ],
            ],
            [
                'title' => ['fr' => 'Compte', 'en' => 'Account'],
                'items' => [
                    ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
                    ['security.show', 'shield-check', 'Sécurité', 'Security'],
                ],
            ],
        ];
    } elseif ($role === 'business_owner') {
        $navGroups = [
            [
                'title' => null,
                'items' => [
                    ['dashboard.entrepreneur', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                    ['dashboard.quotes', 'file-text', 'Demandes de devis', 'Quote requests'],
                    ['business.edit', 'building-2', 'Mon entreprise', 'My Business'],
                    ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
                    ['verification.show', 'badge-check', 'Vérification', 'Verification'],
                    ['events.index', 'calendar-days', 'Événements', 'Events'],
                    ['support.index', 'life-buoy', 'Support', 'Support'],
                    ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
                    ['security.show', 'shield-check', 'Sécurité', 'Security'],
                ],
            ],
        ];
    } elseif ($role === 'buyer') {
        $navGroups = [
            [
                'title' => null,
                'items' => [
                    ['dashboard.buyer', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                    ['quotes.index', 'file-text', 'Mes Demandes & Devis', 'My Requests & Quotes'],
                    ['saved.index', 'bookmark', 'Mes favoris', 'Saved'],
                    ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
                    ['businesses.index', 'search', 'Explorer', 'Browse'],
                    ['events.index', 'calendar-days', 'Événements', 'Events'],
                    ['support.index', 'life-buoy', 'Support', 'Support'],
                    ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
                    ['security.show', 'shield-check', 'Sécurité', 'Security'],
                ],
            ],
        ];
    } elseif ($role === 'regional_rep') {
        $navGroups = [['title' => null, 'items' => [
            ['dashboard.regional-rep', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
            ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
        ]]];
    } elseif ($role === 'technical_reviewer') {
        $navGroups = [['title' => null, 'items' => [
            ['dashboard.technical-reviewer', 'layout-dashboard', 'File d\'attente', 'Queue'],
            ['technical.history', 'history', 'Historique', 'History'],
            ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
        ]]];
    }

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
        <header class="sticky top-0 z-20 bg-white border-b border-[#EEEFEE] h-[64px] flex items-center px-4 lg:px-6 gap-3 shrink-0">
            <button id="sidebar-toggle" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-[#F6F7F6]">
                <i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i>
            </button>
            <h1 class="text-[14px] font-bold text-[#1B1B18] truncate">{{ $pageTitle ?? '' }}</h1>
            <div class="flex-1"></div>

            <div class="flex items-center gap-2">
                <a href="{{ request()->fullUrlWithQuery(['lang' => $lang === 'fr' ? 'en' : 'fr']) }}" class="hidden sm:flex items-center justify-center h-[34px] px-3 rounded-lg border border-[#E5E7E5] text-[12px] font-semibold text-[#3B382F] hover:border-[#14532D] transition-colors">
                    {{ strtoupper($lang === 'fr' ? 'en' : 'fr') }}
                </a>
                <a href="{{ route('messages.inbox') }}" class="relative flex items-center justify-center w-[38px] h-[38px] rounded-lg text-[#3B382F] hover:bg-[#F6F7F6] transition-colors" title="Messages">
                    <i data-lucide="message-circle" class="w-[19px] h-[19px]" style="stroke-width:1.7"></i>
                </a>
                <a href="{{ route('notifications.index') }}" class="relative flex items-center justify-center w-[38px] h-[38px] rounded-lg text-[#3B382F] hover:bg-[#F6F7F6] transition-colors" title="Notifications">
                    <i data-lucide="bell" class="w-[19px] h-[19px]" style="stroke-width:1.7"></i>
                    @if($unreadNotifications > 0)
                    <span class="absolute top-0.5 right-0.5 min-w-[17px] h-[17px] px-1 bg-[#E01E1E] text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.show') }}" class="w-[36px] h-[36px] bg-[#DFEDE3] hover:bg-[#CFE5D6] rounded-full flex items-center justify-center ml-1 transition-colors" title="{{ $lang === 'fr' ? 'Mon profil' : 'My Profile' }}">
                    <span class="text-[#14652F] text-[12px] font-bold">{{ $dashInitials }}</span>
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
