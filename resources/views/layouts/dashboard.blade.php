<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tableau de bord — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Semantic palette kept for content sections that still reference brand/forest tints
                        brand:  { 50:'#fef9ee',100:'#fdf0d3',200:'#fada9a',300:'#f7c062',400:'#f4a32a',500:'#e8880e',600:'#cc6a09',700:'#a84e0b',800:'#873d10',900:'#6e3311' },
                        forest: { 50:'#f0f9f4',100:'#dbf0e3',200:'#b8e0c9',300:'#8cc9a8',400:'#5ba883',500:'#2d6a4f',600:'#1b4332',700:'#0d2b1e',800:'#082018',900:'#03130e' },
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
<body class="bg-[#F7F8F7] text-[#1B1B18] antialiased">

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
        'ministry'           => ['label' => ['fr' => 'Ministère', 'en' => 'Ministry'],            'icon' => 'landmark'],
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
                    ['admin.siarc', 'store', 'SIARC 2026', 'SIARC 2026'],
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
    } elseif ($role === 'ministry') {
        $navGroups = [['title' => null, 'items' => [
            ['dashboard.ministry', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
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

    <!-- Sidebar -->
    <aside id="dashboard-sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-[268px] shrink-0 -translate-x-full lg:translate-x-0 bg-white border-r border-[#EEEFEE] flex flex-col h-full">
        <div class="h-[64px] flex items-center gap-3 px-4 border-b border-[#F0F1F0] shrink-0">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[34px] h-[37px] object-contain">
            <span class="leading-tight min-w-0">
                <span class="block text-[11px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $lang === 'fr' ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                <span class="block text-[9.5px] font-semibold text-[#157A43] whitespace-nowrap">{{ $lang === 'fr' ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </div>

        <div class="px-4 py-3.5 border-b border-[#F0F1F0] shrink-0">
            <div class="border border-[#EDEEED] bg-[#FBFBFA] rounded-xl px-3 py-2.5 flex items-center gap-3">
                <span class="w-[38px] h-[38px] shrink-0 rounded-full bg-[#DFEDE3] flex items-center justify-center text-[14px] font-semibold text-[#14652F]">{{ $dashInitials }}</span>
                <div class="min-w-0">
                    <p class="text-[12.5px] font-bold text-[#1B1B18] truncate">{{ $dashName }}</p>
                    <p class="flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]">
                        <i data-lucide="{{ $meta['icon'] }}" class="w-3 h-3 shrink-0"></i>
                        <span class="truncate">{{ $meta['label'][$lang] }}</span>
                    </p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-3">
            @foreach($navGroups as $group)
            <div class="px-3 mb-4">
                @if($group['title'])
                <p class="px-2.5 text-[10.5px] font-bold text-[#157A43] uppercase tracking-[0.08em] mb-1.5">{{ $group['title'][$lang] }}</p>
                @endif
                @foreach($group['items'] as [$routeName, $icon, $labelFr, $labelEn])
                    @php $active = request()->routeIs($routeName) || request()->routeIs($routeName . '.*'); @endphp
                    <a href="{{ \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : '#' }}"
                        class="flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] mb-0.5 transition-colors {{ $active ? 'bg-[#E7F1EA] text-[#14652F] font-bold' : 'text-[#3B382F] hover:bg-[#F6F7F6]' }}">
                        <i data-lucide="{{ $icon }}" class="w-[17px] h-[17px] shrink-0 {{ $active ? 'text-[#14652F]' : 'text-[#55524A]' }}" style="stroke-width:1.7"></i>
                        <span class="truncate">{{ $lang === 'fr' ? $labelFr : $labelEn }}</span>
                    </a>
                @endforeach
            </div>
            @endforeach
        </nav>

        <div class="p-3 border-t border-[#F0F1F0] shrink-0 space-y-0.5">
            <a href="/" class="flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] text-[#55524A] hover:bg-[#F6F7F6] transition-colors">
                <i data-lucide="arrow-left" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.7"></i>
                {{ $lang === 'fr' ? 'Retour au site' : 'Back to site' }}
            </a>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] text-[#B42025] hover:bg-[#FDE8E8] transition-colors text-left">
                    <i data-lucide="log-out" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.7"></i>
                    {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
                </button>
            </form>
        </div>
    </aside>

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
    function openSidebar() { sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
    function closeSidebar() { sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }
    if (toggle) toggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
</script>
</body>
</html>
