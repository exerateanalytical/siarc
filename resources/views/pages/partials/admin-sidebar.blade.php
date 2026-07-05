{{-- Admin suite sidebar — replica of the CURRENT admin design family's sidebar
     (Subscriptions.png / KYC Centre.png / User Management.png…): dark green with
     the design's cropped gold icons, gold group labels, light-green active pill,
     orange count badges, and the bottom "Valorisons l'artisanat" vases card.
     Expects: $lang, $isFr. Options: $adminActive (key of the active item). --}}
@php
    $adminActive = $adminActive ?? 'dashboard';

    // [key, icon crop, label, url, badge] — every item maps onto a real route.
    $adGroups = [
        [null, [
            ['dashboard', 'side-ic-dashboard', $isFr ? 'TABLEAU DE BORD' : 'DASHBOARD', route('dashboard.admin'), null],
        ]],
        [$isFr ? 'Gestion du contenu' : 'Content management', [
            ['artisans',    'side-ic-artisans',    'Artisans',                                              route('admin.artisans'), null],
            ['products',    'side-ic-products',    $isFr ? 'Produits & Services' : 'Products & Services',   route('admin.products'), null],
            ['industries',  'side-ic-industries',  $isFr ? 'Catégories d\'Artisanat' : 'Craft Categories',  route('admin.industries'), null],
            ['regions',     'map-pin',     $isFr ? 'Régions & Centres' : 'Regions & Centres',       route('admin.regions'), null],
            ['collections', 'side-ic-collections', 'Collections',                                           route('admin.collections'), null],
            ['news',        'side-ic-news',        $isFr ? 'Actualités & Annonces' : 'News & Announcements', route('admin.news'), null],
            ['media',       'side-ic-media',       $isFr ? 'Médias & Ressources' : 'Media & Resources',     route('admin.media'), null],
            ['events',      'side-ic-events',      $isFr ? 'Événements' : 'Events',                         route('admin.events'), null],
            ['documents',   'side-ic-documents',   'Documents',                                             route('admin.media') . '#documents', null],
        ]],
        [$isFr ? 'Salon SIARC 2026' : 'SIARC 2026 Fair', [
            ['siarc',         'side-ic-events',        $isFr ? 'Tableau SIARC' : 'SIARC Dashboard',         route('siarc.admin.dashboard'), null],
            ['siarc-exh',     'side-ic-boutiques',     $isFr ? 'Exposants' : 'Exhibitors',                  route('siarc.admin.exhibitors'), null],
            ['siarc-plan',    'map-pin',               $isFr ? 'Pavillons & Plan' : 'Pavilions & Plan',     route('siarc.admin.floorplan'), null],
            ['siarc-vis',     'side-ic-users',         $isFr ? 'Visiteurs & Badges' : 'Visitors & Badges',  route('siarc.admin.visitors'), null],
            ['siarc-prog',    'side-ic-events',        $isFr ? 'Programme' : 'Programme',                   route('siarc.admin.programme'), null],
            ['siarc-b2b',     'side-ic-subscriptions', $isFr ? 'B2B & Matchmaking' : 'B2B & Matchmaking',   route('siarc.admin.b2b'), null],
        ]],
        [$isFr ? 'Gestion des utilisateurs' : 'User management', [
            ['users',       'side-ic-users',       $isFr ? 'Utilisateurs' : 'Users',                        route('admin.users'), null],
            ['roles',       'side-ic-roles',       $isFr ? 'Rôles & Permissions' : 'Roles & Permissions',   route('admin.roles'), null],
            ['pending-art', 'side-ic-inscription', $isFr ? 'Demandes d\'Inscription' : 'Registration Requests', route('admin.artisans') . '?statut=en-attente', null],
            ['businesses',  'side-ic-boutiques',   $isFr ? 'Artisans & Boutiques' : 'Artisans & Shops',     route('admin.businesses'), '248'],
            ['kyc',         'side-ic-kyc',         $isFr ? 'KYC & Vérification' : 'KYC & Verification',     route('admin.kyc'), '36'],
            ['certificates','side-ic-kyc',         $isFr ? 'Certificats' : 'Certificates',                  route('admin.certificates'), null],
        ]],
        [$isFr ? 'Commercial & Finance' : 'Commercial & Finance', [
            ['subscriptions', 'side-ic-subscriptions', $isFr ? 'Abonnements' : 'Subscriptions',             route('admin.subscriptions'), null],
            ['commissions',   'side-ic-commissions',   'Commissions',                                       route('admin.reports'), null],
            ['payments',      'side-ic-transactions',  'Transactions',                                      route('admin.payments'), null],
            ['orders',        'side-ic-factures',      'Factures',                                          route('admin.orders'), null],
            ['reports',       'side-ic-rapports',      $isFr ? 'Rapports' : 'Reports',                      route('admin.reports'), null],
        ]],
        [$isFr ? 'Système' : 'System', [
            ['settings',      'side-ic-settings',      $isFr ? 'Paramètres Généraux' : 'General Settings',  route('admin.settings'), null],
            ['logs',          'side-ic-journal',       $isFr ? 'Journal d\'Activité' : 'Activity Log',      route('admin.audit-log'), null],
            ['notifications', 'bell', 'Notifications',                                     route('admin.notifications'), '12'],
            ['backups',       'database',   $isFr ? 'Sauvegardes & Logs' : 'Backups & Logs',                   route('admin.backups'), null],
            ['exports',       'side-ic-exports',       'Data Export Centre',                                route('admin.exports'), null],
            ['pages',         'side-ic-outils',        $isFr ? 'Outils & Maintenance' : 'Tools & Maintenance', route('admin.cms'), null],
        ]],
    ];
    $adSideQuote = $adSideQuote ?? ($isFr ? 'Valorisons l\'artisanat, développons nos communautés, préservons notre héritage.' : 'Let\'s value craftsmanship, grow our communities, preserve our heritage.');
@endphp
<aside id="ad-sidebar" class="shrink-0 lg:w-[290px] bg-gradient-to-b from-[#042B15] to-[#03200E] text-white flex flex-col relative">
    <div class="px-4 pt-4 pb-3 flex items-center gap-3">
        <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain shrink-0">
        <div class="leading-tight min-w-0">
            <p class="text-[11px] font-bold tracking-[0.02em] text-white uppercase leading-snug">{{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</p>
            <p class="mt-1 text-[9.5px] italic text-[#E9C25A] leading-snug">{{ $isFr ? 'Notre Héritage, Notre Fierté, Notre Avenir' : 'Our Heritage, Our Pride, Our Future' }}</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 pb-4 pt-1">
        @foreach($adGroups as [$adTitle, $adItems])
            @if($adTitle)
            <p class="mt-5 mb-2 px-1 text-[11.5px] font-bold tracking-[0.02em] text-[#E6B201] uppercase">{{ $adTitle }}</p>
            @endif
            @foreach($adItems as [$adKey, $adIcon, $adLabel, $adUrl, $adBadge])
                @if($adKey === $adminActive)
                <a href="{{ $adUrl }}" class="mt-0.5 flex items-center gap-3 bg-[#0D5A30] rounded-xl px-3 py-[9px] text-[13px] font-bold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.12)]">
                    <img src="{{ asset('images/landing/' . $adIcon . '.png') }}" alt="" class="w-[17px] h-[16px] object-contain shrink-0">
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#DE8E14] text-white text-[10.5px] font-bold rounded-md px-2 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @else
                <a href="{{ $adUrl }}" class="flex items-center gap-3 rounded-xl px-3 py-[7px] text-[12.5px] {{ $adKey === 'dashboard' ? 'font-bold tracking-[0.03em] text-white' : 'text-[#EDF2EC]' }} hover:bg-white/5 transition-colors">
                    <img src="{{ asset('images/landing/' . $adIcon . '.png') }}" alt="" class="w-[17px] h-[16px] object-contain shrink-0">
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#DE8E14] text-white text-[10.5px] font-bold rounded-md px-2 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="shrink-0 relative">
        <img src="{{ asset('images/landing/ad-side-vases.png') }}" alt="" class="w-full h-auto block">
        <p class="absolute left-[9%] top-[15%] w-[62%] font-serif text-[13.5px] leading-[1.6] text-[#F0EBDD]">{{ $adSideQuote }}</p>
    </div>
</aside>
