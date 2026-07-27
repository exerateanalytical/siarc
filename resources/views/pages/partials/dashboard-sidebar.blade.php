{{-- ============================================================
     THE canonical dashboard sidebar for every authenticated area.

     One design, one item list per role, one set of heritage colours.
     Used by layouts/dashboard.blade.php AND by the self-contained
     dashboards (entrepreneur / quotes) so a seller never sees the
     navigation change shape when moving between pages.

     Self-contained: computes role, language, user and nav itself, so
     it can be @included from any page with no required variables.

     Options:
       $sidebarId   — DOM id used by that page's existing menu toggle
                      (defaults to 'dashboard-sidebar')
       $sideBadges  — ['route.name' => '3', ...] optional live counts
     ============================================================ --}}
@php
    $sbUser = session('siac_user') ?? [];
    $sbRole = $sbUser['role'] ?? 'buyer';
    $sbLang = in_array(request()->cookie('lang'), ['fr', 'en']) ? request()->cookie('lang') : 'fr';
    $sbLang = in_array(request()->query('lang'), ['fr', 'en']) ? request()->query('lang') : $sbLang;
    $sbIsFr = $sbLang === 'fr';
    $sidebarId = $sidebarId ?? 'dashboard-sidebar';
    $sideBadges = $sideBadges ?? [];

    $sbName = $sbUser['name'] ?? ($sbIsFr ? 'Utilisateur' : 'User');
    $sbInitials = strtoupper(collect(explode(' ', trim($sbName)))->filter()
        ->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')) ?: 'U';

    $sbRoleMeta = [
        'super_admin'        => [['fr' => 'Administrateur', 'en' => 'Administrator'], 'shield-check'],
        'admin'              => [['fr' => 'Administrateur', 'en' => 'Administrator'], 'shield-check'],
        'moderator'          => [['fr' => 'Modérateur', 'en' => 'Moderator'], 'shield-check'],
        'business_owner'     => [['fr' => 'Artisan / Entreprise', 'en' => 'Artisan / Business'], 'briefcase'],
        'buyer'              => [['fr' => 'Acheteur', 'en' => 'Buyer'], 'shopping-bag'],
        'regional_rep'       => [['fr' => 'Représentant régional', 'en' => 'Regional Rep'], 'map'],
        'technical_reviewer' => [['fr' => 'Département technique', 'en' => 'Technical Dept.'], 'microscope'],
    ];
    [$sbRoleLabel, $sbRoleIcon] = $sbRoleMeta[$sbRole] ?? [['fr' => 'Utilisateur', 'en' => 'User'], 'user'];

    // Role-scoped navigation. Every entry is a real, working route — items with
    // no backing feature are deliberately absent rather than shown as dead-ends.
    // [routeName, icon, labelFr, labelEn]
    if (in_array($sbRole, ['super_admin', 'admin', 'moderator'])) {
        $sbGroups = [
            [['fr' => 'Vue d\'ensemble', 'en' => 'Overview'], [
                ['dashboard.admin', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                ['admin.reports', 'bar-chart-2', 'Rapports & Statistiques', 'Reports & Statistics'],
                ['admin.audit-log', 'history', 'Journal d\'audit', 'Audit Log'],
            ]],
            [['fr' => 'Modération', 'en' => 'Moderation'], [
                ['admin.businesses', 'building-2', 'Entreprises', 'Businesses'],
                ['admin.products', 'package', 'Produits & Services', 'Products & Services'],
                ['admin.quotes', 'file-text', 'Devis & Commandes', 'Quotes & Orders'],
                ['admin.industries', 'tags', 'Catégories & Régions', 'Categories & Regions'],
                ['admin.verifications', 'badge-check', 'Vérifications', 'Verifications'],
                ['admin.moderation', 'flag', 'Modération', 'Moderation'],
                ['admin.users', 'users', 'Utilisateurs', 'Users'],
            ]],
            [['fr' => 'Contenu', 'en' => 'Content'], [
                ['admin.partners', 'handshake', 'Partenaires', 'Partners'],
                ['admin.events', 'calendar-days', 'Événements', 'Events'],
                ['admin.cms', 'file-text', 'Pages CMS', 'CMS Pages'],
                ['admin.support', 'life-buoy', 'Support', 'Support'],
            ]],
            [['fr' => 'Compte', 'en' => 'Account'], [
                ['admin.settings', 'settings-2', 'Paramètres', 'Settings'],
                ['profile.show', 'user-cog', 'Mon profil', 'My Profile'],
                ['security.show', 'lock', 'Sécurité', 'Security'],
            ]],
        ];
    } elseif ($sbRole === 'business_owner') {
        $sbGroups = [
            [['fr' => 'Mon activité', 'en' => 'My activity'], [
                ['dashboard.entrepreneur', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                ['dashboard.quotes', 'file-text', 'Demandes de devis', 'Quote requests'],
                ['orders.index', 'clipboard-list', 'Commandes reçues', 'Received orders'],
                ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
                ['events.index', 'calendar-days', 'Événements', 'Events'],
            ]],
            [['fr' => 'Ma boutique', 'en' => 'My shop'], [
                ['products.web-index', 'package', 'Mes produits', 'My products'],
                ['business.edit', 'building-2', 'Mon entreprise', 'My business'],
                ['verification.show', 'badge-check', 'Vérification', 'Verification'],
                ['membership.certificate', 'award', 'Mon certificat', 'My certificate'],
            ]],
            [['fr' => 'Compte', 'en' => 'Account'], [
                ['profile.show', 'user-cog', 'Mon profil', 'My profile'],
                ['security.show', 'lock', 'Sécurité', 'Security'],
                ['notifications.index', 'bell', 'Notifications', 'Notifications'],
                ['support.index', 'life-buoy', 'Aide & Support', 'Help & Support'],
            ]],
        ];
    } elseif ($sbRole === 'buyer') {
        $sbGroups = [
            [['fr' => 'Mon activité', 'en' => 'My activity'], [
                ['dashboard.buyer', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                ['quotes.index', 'file-text', 'Mes demandes & devis', 'My requests & quotes'],
                ['orders.index', 'clipboard-list', 'Mes commandes', 'My orders'],
                ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
                ['saved.index', 'bookmark', 'Mes favoris', 'Saved'],
            ]],
            [['fr' => 'Explorer', 'en' => 'Explore'], [
                ['businesses.index', 'search', 'Artisans & Entreprises', 'Artisans & Businesses'],
                ['products.index', 'package', 'Produits', 'Products'],
                ['events.index', 'calendar-days', 'Événements', 'Events'],
            ]],
            [['fr' => 'Compte', 'en' => 'Account'], [
                ['profile.show', 'user-cog', 'Mon profil', 'My profile'],
                ['security.show', 'lock', 'Sécurité', 'Security'],
                ['notifications.index', 'bell', 'Notifications', 'Notifications'],
                ['support.index', 'life-buoy', 'Aide & Support', 'Help & Support'],
            ]],
        ];
    } else {
        $sbRoleDash = [
            'regional_rep'       => 'dashboard.regional-rep',
            'technical_reviewer' => 'dashboard.technical-reviewer',
        ][$sbRole] ?? 'dashboard.siac';
        $sbGroups = [
            [null, [
                [$sbRoleDash, 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                ['messages.inbox', 'message-circle', 'Messages', 'Messages'],
            ]],
            [['fr' => 'Compte', 'en' => 'Account'], [
                ['profile.show', 'user-cog', 'Mon profil', 'My profile'],
                ['security.show', 'lock', 'Sécurité', 'Security'],
                ['support.index', 'life-buoy', 'Aide & Support', 'Help & Support'],
            ]],
        ];
    }
@endphp

{{-- Canonical mobile slide-over behaviour, emitted once per page. Every
     dashboard toggles the same `.open` class, so the interaction is identical
     everywhere instead of three competing CSS/JS mechanisms. --}}
@once
<style>
    @media (max-width: 1023.98px) {
        .dash-aside { position: fixed; inset: 0 auto 0 0; z-index: 60; transform: translateX(-100%); transition: transform .2s ease; }
        .dash-aside.open { transform: translateX(0); }
    }
    @media (min-width: 1024px) { .dash-aside { transform: none; } }
</style>
@endonce

<aside id="{{ $sidebarId }}"
    class="dash-aside fixed lg:static inset-y-0 left-0 z-40 w-[268px] shrink-0 bg-[#02301B] flex flex-col h-full lg:h-screen lg:sticky lg:top-0 overflow-y-auto">

    {{-- Brand --}}
    <a href="{{ route('home', ['lang' => $sbLang]) }}" class="h-[64px] flex items-center gap-3 px-4 border-b border-white/10 shrink-0">
        <img src="{{ brand_asset('mark') }}" alt="" class="w-[37px] h-[37px] object-contain shrink-0">
        <span class="leading-tight min-w-0">
            <span class="block text-[11.5px] font-bold tracking-[0.02em] text-white uppercase whitespace-nowrap">Artisan Hub 237</span>
            <span class="block text-[9.5px] font-semibold text-[#E5A82E] whitespace-nowrap">{{ $sbIsFr ? 'Notre héritage, notre fierté' : 'Our heritage, our pride' }}</span>
        </span>
    </a>

    {{-- Who am I --}}
    <div class="px-4 py-3.5 border-b border-white/10 shrink-0">
        <div class="bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 flex items-center gap-3">
            <span class="w-[38px] h-[38px] shrink-0 rounded-full bg-[#E5A82E] flex items-center justify-center text-[14px] font-bold text-[#02301B]">{{ $sbInitials }}</span>
            <div class="min-w-0">
                <p class="text-[12.5px] font-bold text-white truncate">{{ $sbName }}</p>
                <p class="flex items-center gap-1.5 text-[11px] font-semibold text-[#E5A82E]">
                    <i data-lucide="{{ $sbRoleIcon }}" class="w-3 h-3 shrink-0"></i>
                    <span class="truncate">{{ $sbRoleLabel[$sbLang] }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-3">
        @foreach($sbGroups as [$sbGroupTitle, $sbItems])
        <div class="px-3 mb-4">
            @if($sbGroupTitle)
            <p class="px-2.5 text-[10.5px] font-bold text-[#E5A82E] uppercase tracking-[0.08em] mb-1.5">{{ $sbGroupTitle[$sbLang] }}</p>
            @endif
            @foreach($sbItems as [$sbRoute, $sbIcon, $sbLabelFr, $sbLabelEn])
            @continue(! \Illuminate\Support\Facades\Route::has($sbRoute))
            @php
                $sbActive = request()->routeIs($sbRoute) || request()->routeIs($sbRoute . '.*');
                $sbBadge  = $sideBadges[$sbRoute] ?? null;
            @endphp
            <a href="{{ route($sbRoute, ['lang' => $sbLang]) }}"
                class="relative flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] mb-0.5 transition-colors {{ $sbActive ? 'bg-[#14532D] text-white font-bold' : 'text-[#DCE7DF] hover:bg-white/5 hover:text-white' }}">
                @if($sbActive)<span class="absolute left-0 inset-y-1.5 w-[3px] rounded-r bg-[#E5A82E]"></span>@endif
                <i data-lucide="{{ $sbIcon }}" class="w-[17px] h-[17px] shrink-0 {{ $sbActive ? 'text-[#E5A82E]' : 'text-[#A9C4B3]' }}" style="stroke-width:1.7"></i>
                <span class="truncate">{{ $sbIsFr ? $sbLabelFr : $sbLabelEn }}</span>
                @if($sbBadge && $sbBadge !== '0')
                <span class="ml-auto shrink-0 min-w-[22px] text-center text-[10.5px] font-bold px-1.5 py-[2px] rounded-full bg-[#DC0508] text-white">{{ $sbBadge }}</span>
                @endif
            </a>
            @endforeach
        </div>
        @endforeach
    </nav>

    {{-- Footer actions --}}
    <div class="p-3 border-t border-white/10 shrink-0 space-y-0.5">
        <a href="{{ route('home', ['lang' => $sbLang]) }}" class="flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] text-[#DCE7DF] hover:bg-white/5 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.7"></i>
            {{ $sbIsFr ? 'Retour au site' : 'Back to site' }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] text-[#F2B8B8] hover:bg-white/5 transition-colors text-left">
                <i data-lucide="log-out" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.7"></i>
                {{ $sbIsFr ? 'Déconnexion' : 'Log out' }}
            </button>
        </form>
    </div>
</aside>
