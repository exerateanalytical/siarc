{{-- Admin suite sidebar — replica of the CURRENT admin design family's sidebar
     (Subscriptions.png / KYC Centre.png / User Management.png…): dark green with
     the design's cropped gold icons, gold group labels, light-green active pill,
     orange count badges, and the bottom "Valorisons l'artisanat" vases card.
     Expects: $lang, $isFr. Options: $adminActive (key of the active item). --}}
@php
    $adminActive = $adminActive ?? 'dashboard';

    // Real live counts for sidebar badges (cached briefly — sidebar renders on every admin page)
    $sideBadges = \Illuminate\Support\Facades\Cache::remember('admin_sidebar_badges', 60, function () {
        return [
            'businesses'    => \Illuminate\Support\Facades\DB::table('businesses')->whereNull('deleted_at')->count(),
            'kyc'           => \Illuminate\Support\Facades\DB::table('verification_applications')->where('status', 'pending')->count(),
            'notifications' => \Illuminate\Support\Facades\DB::table('user_notifications')->whereNull('read_at')->count(),
        ];
    });

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
            ['documents',   'side-ic-documents',   'Documents',                                             route('admin.media', ['type' => 'document']), null],
        ]],
        [$isFr ? 'Gestion des utilisateurs' : 'User management', [
            ['users',       'side-ic-users',       $isFr ? 'Utilisateurs' : 'Users',                        route('admin.users'), null],
            ['roles',       'side-ic-roles',       $isFr ? 'Rôles & Permissions' : 'Roles & Permissions',   route('admin.roles'), null],
            ['pending-art', 'side-ic-inscription', $isFr ? 'Demandes d\'Inscription' : 'Registration Requests', route('admin.artisans') . '?statut=en-attente', null],
            ['businesses',  'side-ic-boutiques',   $isFr ? 'Artisans & Boutiques' : 'Artisans & Shops',     route('admin.businesses'), (string) $sideBadges['businesses']],
            ['kyc',         'side-ic-kyc',         $isFr ? 'KYC & Vérification' : 'KYC & Verification',     route('admin.kyc'), (string) $sideBadges['kyc']],
            ['certificates','side-ic-kyc',         $isFr ? 'Certificats' : 'Certificates',                  route('admin.certificates'), null],
        ]],
        [$isFr ? 'Commercial & Finance' : 'Commercial & Finance', [
            ['commissions',   'side-ic-commissions',   'Commissions',                                       route('admin.reports'), null],
            ['orders',        'side-ic-factures',      'Factures',                                          route('admin.orders'), null],
            ['reports',       'side-ic-rapports',      $isFr ? 'Rapports' : 'Reports',                      route('admin.reports'), null],
        ]],
        [$isFr ? 'Système' : 'System', [
            ['settings',      'side-ic-settings',      $isFr ? 'Paramètres Généraux' : 'General Settings',  route('admin.settings'), null],
            ['logs',          'side-ic-journal',       $isFr ? 'Journal d\'Activité' : 'Activity Log',      route('admin.audit-log'), null],
            ['notifications', 'bell', 'Notifications',                                     route('admin.notifications'), (string) $sideBadges['notifications']],
            ['exports',       'side-ic-exports',       'Data Export Centre',                                route('admin.exports'), null],
            ['pages',         'side-ic-outils',        $isFr ? 'Outils & Maintenance' : 'Tools & Maintenance', route('admin.cms'), null],
        ]],
    ];
    $adSideQuote = $adSideQuote ?? ($isFr ? 'Valorisons l\'artisanat, développons nos communautés, préservons notre héritage.' : 'Let\'s value craftsmanship, grow our communities, preserve our heritage.');
@endphp
{{-- Dark mode deepens this rail rather than inverting it: it is already dark in
     light mode, so an inversion would turn the admin navigation white. The
     gradient moves onto the contract's `brand-deep` (#0C3B1E → #093017), which
     sits 1.55:1 above the #0A0C09 page — visible, but not a second page. The
     page-facing edge gets a real boundary at #2A6B41 (3.07:1 on the page,
     meeting WCAG 1.4.11) because a 1.55:1 fill alone is a soft edge on a large
     region. Every label was re-measured on the new fill: gold group headings
     6.47:1, idle items 11.15:1, the tagline 7.43:1. --}}
<aside id="ad-sidebar" class="shrink-0 lg:w-[290px] bg-gradient-to-b from-[#042B15] to-[#03200E] dark:from-[#0C3B1E] dark:to-[#093017] dark:border-r dark:border-[#2A6B41] text-white flex flex-col relative">
    <div class="px-4 pt-4 pb-3 flex items-center gap-3">
        <img src="{{ brand_asset('mark') }}" alt="" class="w-[50px] h-[50px] object-contain shrink-0">
        <div class="leading-tight min-w-0">
            <p class="text-[11px] font-bold tracking-[0.02em] text-white uppercase leading-snug">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</p>
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
                {{-- The active pill has to separate from the rail it sits on. On the
                     deepened rail #0D5A30 is only 1.52:1, so dark mode uses the
                     contract's `brand` #2E9250 (3.22:1 on the rail) with `brand-ink`
                     #04150A on it (4.78:1) — white on that fill is 3.93:1 and fails. --}}
                <a href="{{ $adUrl }}" class="mt-0.5 flex items-center gap-3 bg-[#0D5A30] dark:bg-[#2E9250] rounded-xl px-3 py-[9px] text-[13px] font-bold text-white dark:text-[#04150A] shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] dark:shadow-none">
                    <img src="{{ asset('images/landing/' . $adIcon . '.png') }}" alt="" class="w-[17px] h-[16px] object-contain shrink-0">
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#DE8E14] text-white dark:text-[#2A1902] text-[10.5px] font-bold rounded-md px-2 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @else
                <a href="{{ $adUrl }}" class="flex items-center gap-3 rounded-xl px-3 py-[7px] text-[12.5px] {{ $adKey === 'dashboard' ? 'font-bold tracking-[0.03em] text-white' : 'text-[#EDF2EC]' }} hover:bg-white/5 transition-colors">
                    <img src="{{ asset('images/landing/' . $adIcon . '.png') }}" alt="" class="w-[17px] h-[16px] object-contain shrink-0">
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#DE8E14] text-white dark:text-[#2A1902] text-[10.5px] font-bold rounded-md px-2 py-0.5">{{ $adBadge }}</span>@endif
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
