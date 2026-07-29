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
            // An admin only reaches this rail on the shared account pages (profile,
            // security); every admin screen proper renders the grouped rail in
            // pages/partials/admin-sidebar.blade.php. The two are kept in step so
            // the same job is not filed under two different names in one product.
            // 'admin.verifications' is gone from here for the same reason it left
            // the other rail: it is now a redirect onto the KYC Centre.
            [['fr' => 'Vue d\'ensemble', 'en' => 'Overview'], [
                ['dashboard.admin', 'layout-dashboard', 'Tableau de bord', 'Dashboard'],
                ['admin.analytics', 'bar-chart-2', 'Analytique', 'Analytics'],
                ['admin.reports', 'bar-chart-2', 'Rapports & Statistiques', 'Reports & Statistics'],
                ['admin.audit-log', 'history', 'Journal d\'audit', 'Audit Log'],
            ]],
            [['fr' => 'Artisans & vérification', 'en' => 'Artisans & verification'], [
                ['admin.businesses', 'building-2', 'Artisans & Boutiques', 'Artisans & Shops'],
                ['admin.kyc', 'badge-check', 'KYC & Vérification', 'KYC & Verification'],
                ['admin.certificates', 'award', 'Certificats', 'Certificates'],
            ]],
            [['fr' => 'Catalogue & commerce', 'en' => 'Catalogue & commerce'], [
                ['admin.products', 'package', 'Produits & Services', 'Products & Services'],
                ['admin.industries', 'tags', 'Catégories & Régions', 'Categories & Regions'],
                ['admin.quotes', 'file-text', 'Demandes de devis', 'Quote requests'],
                ['admin.orders', 'receipt', 'Commandes & Factures', 'Orders & Invoices'],
                ['admin.payments', 'wallet', 'Paiements à contrôler', 'Payments to check'],
            ]],
            [['fr' => 'Utilisateurs & modération', 'en' => 'Users & moderation'], [
                ['admin.users', 'users', 'Utilisateurs', 'Users'],
                ['admin.moderation', 'flag', 'Signalements & Avis', 'Reports & Reviews'],
                ['admin.support', 'life-buoy', 'Support', 'Support'],
            ]],
            [['fr' => 'Contenu', 'en' => 'Content'], [
                ['admin.partners', 'handshake', 'Partenaires', 'Partners'],
                ['admin.events', 'calendar-days', 'Événements', 'Events'],
                ['admin.cms', 'file-text', 'Pages & FAQ', 'Pages & FAQ'],
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
    {{-- The rail is already dark in light mode, so dark mode does not invert it:
         it moves to the contract's `brand-deep` (#0C3B1E), which sits 1.55:1
         above the #0A0C09 page instead of disappearing into it. Every label on
         it was re-measured against that fill — the quietest, #A9C4B3, is
         6.77:1. --}}
    class="dash-aside fixed lg:static inset-y-0 left-0 z-40 w-[268px] shrink-0 bg-[#02301B] dark:bg-[#0C3B1E] dark:border-r dark:border-[#2A6B41] flex flex-col h-full lg:h-screen lg:sticky lg:top-0 overflow-y-auto">

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
                {{-- The active pill must separate from the rail behind it. #1E7A44 on
                     the deepened rail is 2.36:1 — under 1.4.11's 3:1 — so dark mode
                     takes the contract's `brand` #2E9250 (3.22:1 on the rail) with
                     `brand-ink` #04150A on it (4.78:1). Gold at #E5A82E is only
                     2.54:1 on that fill, so the active icon takes the ink too and the
                     gold survives as the left marker bar (6.01:1 on the rail). --}}
                class="relative flex items-center gap-3 px-3 py-[9px] rounded-xl text-[13px] mb-0.5 transition-colors {{ $sbActive ? 'bg-[#14532D] dark:bg-[#2E9250] text-white dark:text-[#04150A] font-bold' : 'text-[#DCE7DF] hover:bg-white/5 hover:text-white' }}">
                @if($sbActive)<span class="absolute left-0 inset-y-1.5 w-[3px] rounded-r bg-[#E5A82E]"></span>@endif
                <i data-lucide="{{ $sbIcon }}" class="w-[17px] h-[17px] shrink-0 {{ $sbActive ? 'text-[#E5A82E] dark:text-[#04150A]' : 'text-[#A9C4B3]' }}" style="stroke-width:1.7"></i>
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
