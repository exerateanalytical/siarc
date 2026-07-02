<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Tableau de bord — SIAC' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand:  { 50:'#fef9ee',100:'#fdf0d3',200:'#fada9a',300:'#f7c062',400:'#f4a32a',500:'#e8880e',600:'#cc6a09',700:'#a84e0b',800:'#873d10',900:'#6e3311' },
                        forest: { 50:'#f0f9f4',100:'#dbf0e3',200:'#b8e0c9',300:'#8cc9a8',400:'#5ba883',500:'#2d6a4f',600:'#1b4332',700:'#0d2b1e',800:'#082018',900:'#03130e' },
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

@php
    $siacUser = session('siac_user') ?? [];
    // No explicit Spatie role means "buyer" — same convention the dashboard redirect route uses.
    $role = $siacUser['role'] ?? 'buyer';

    // Identity color per role — used ONLY for the small role badge + active-nav tint,
    // never for buttons/alerts (those use the semantic palette: green/amber/red/blue).
    $roleMeta = [
        'super_admin'        => ['label' => ['fr' => 'Administrateur', 'en' => 'Administrator'], 'color' => 'slate',   'icon' => 'shield-check'],
        'admin'              => ['label' => ['fr' => 'Administrateur', 'en' => 'Administrator'], 'color' => 'slate',   'icon' => 'shield-check'],
        'moderator'          => ['label' => ['fr' => 'Modérateur', 'en' => 'Moderator'],          'color' => 'slate',   'icon' => 'shield-check'],
        'business_owner'     => ['label' => ['fr' => 'Entrepreneur', 'en' => 'Business Owner'],   'color' => 'orange',  'icon' => 'briefcase'],
        'buyer'              => ['label' => ['fr' => 'Acheteur', 'en' => 'Buyer'],                'color' => 'sky',     'icon' => 'shopping-bag'],
        'regional_rep'       => ['label' => ['fr' => 'Représentant régional', 'en' => 'Regional Rep'], 'color' => 'indigo', 'icon' => 'map'],
        'ministry'           => ['label' => ['fr' => 'Ministère', 'en' => 'Ministry'],            'color' => 'violet',  'icon' => 'landmark'],
        'technical_reviewer' => ['label' => ['fr' => 'Département technique', 'en' => 'Technical Department'], 'color' => 'teal', 'icon' => 'microscope'],
    ];
    $meta = $roleMeta[$role] ?? ['label' => ['fr' => 'Utilisateur', 'en' => 'User'], 'color' => 'gray', 'icon' => 'user'];
    $identityColor = $meta['color'];

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
                    ['admin.verifications', 'badge-check', 'Vérifications', 'Verifications'],
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
                'title' => ['fr' => 'Compte', 'en' => 'Account'],
                'items' => [
                    ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
                ],
            ],
        ];
    } elseif ($role === 'business_owner') {
        $navGroups = [
            [
                'title' => null,
                'items' => [
                    ['dashboard.entrepreneur', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                    ['business.edit', 'building-2', 'Mon entreprise', 'My Business'],
                    ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
                    ['verification.show', 'badge-check', 'Vérification', 'Verification'],
                    ['events.index', 'calendar-days', 'Événements', 'Events'],
                    ['support.index', 'life-buoy', 'Support', 'Support'],
                    ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
                ],
            ],
        ];
    } elseif ($role === 'buyer') {
        $navGroups = [
            [
                'title' => null,
                'items' => [
                    ['dashboard.buyer', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                    ['saved.index', 'bookmark', 'Mes favoris', 'Saved'],
                    ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
                    ['businesses.index', 'search', 'Explorer', 'Browse'],
                    ['events.index', 'calendar-days', 'Événements', 'Events'],
                    ['support.index', 'life-buoy', 'Support', 'Support'],
                    ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
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
@endphp

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar overlay (mobile) -->
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="dashboard-sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-64 shrink-0 -translate-x-full lg:translate-x-0 transition-transform duration-200 bg-white border-r border-gray-200 flex flex-col h-full">
        <div class="h-14 flex items-center gap-2 px-4 border-b border-gray-100 shrink-0">
            <div class="w-7 h-7 bg-forest-500 rounded-lg flex items-center justify-center">
                <i data-lucide="store" class="w-4 h-4 text-white"></i>
            </div>
            <span class="font-bold text-gray-900 text-sm">Galerie Artisanat</span>
        </div>

        <div class="px-4 py-3 border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-{{ $identityColor }}-100 rounded-lg flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4 text-{{ $identityColor }}-600"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-900 truncate">{{ $siacUser['name'] ?? '' }}</p>
                    <p class="text-[11px] text-{{ $identityColor }}-600 font-medium">{{ $meta['label'][$lang] }}</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-3">
            @foreach($navGroups as $group)
            <div class="px-3 mb-4">
                @if($group['title'])
                <p class="px-2 text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1.5">{{ $group['title'][$lang] }}</p>
                @endif
                @foreach($group['items'] as [$routeName, $icon, $labelFr, $labelEn])
                    @php $active = request()->routeIs($routeName) || request()->routeIs($routeName . '.*'); @endphp
                    <a href="{{ \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : '#' }}"
                        class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm mb-0.5 transition-colors {{ $active ? "bg-{$identityColor}-50 text-{$identityColor}-700 font-medium" : 'text-gray-600 hover:bg-gray-50' }}">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">{{ $lang === 'fr' ? $labelFr : $labelEn }}</span>
                    </a>
                @endforeach
            </div>
            @endforeach
        </nav>

        <div class="p-3 border-t border-gray-100 shrink-0 space-y-0.5">
            <a href="/" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-gray-500 hover:bg-gray-50 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 shrink-0"></i>
                {{ $lang === 'fr' ? 'Retour au site' : 'Back to site' }}
            </a>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-colors text-left">
                    <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i>
                    {{ $lang === 'fr' ? 'Déconnexion' : 'Logout' }}
                </button>
            </form>
        </div>
    </aside>

    <!-- Main column -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="sticky top-0 z-20 bg-white border-b border-gray-200 h-14 flex items-center px-4 gap-3 shrink-0">
            <button id="sidebar-toggle" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100">
                <i data-lucide="menu" class="w-5 h-5 text-gray-600"></i>
            </button>
            <h1 class="text-sm font-semibold text-gray-800 truncate">{{ $pageTitle ?? '' }}</h1>
            <div class="flex-1"></div>

            <div class="flex items-center gap-1">
                <a href="{{ request()->fullUrlWithQuery(['lang' => $lang === 'fr' ? 'en' : 'fr']) }}" class="hidden sm:flex items-center justify-center w-9 h-9 rounded-lg text-xs font-semibold text-gray-500 hover:bg-gray-100 transition-colors">
                    {{ strtoupper($lang === 'fr' ? 'en' : 'fr') }}
                </a>
                <a href="{{ route('notifications.index') }}" class="relative flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                    @if($unreadNotifications > 0)
                    <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.show') }}" class="w-7 h-7 bg-{{ $identityColor }}-500 hover:opacity-80 rounded-full flex items-center justify-center ml-1 transition-opacity" title="{{ $lang === 'fr' ? 'Mon profil' : 'My Profile' }}">
                    <span class="text-white text-[11px] font-bold">{{ strtoupper(substr($siacUser['name'] ?? 'U', 0, 1)) }}</span>
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
