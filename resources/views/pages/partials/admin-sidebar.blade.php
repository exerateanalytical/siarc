{{-- Admin suite sidebar — replica of the CURRENT admin design family's sidebar
     (Subscriptions.png / KYC Centre.png / User Management.png…): dark green with
     the design's cropped gold icons, gold group labels, light-green active pill,
     orange count badges, and the bottom "Valorisons l'artisanat" vases card.
     Expects: $lang, $isFr. Options: $adminActive (key of the active item). --}}
@php
    $adminActive = $adminActive ?? 'dashboard';

    // Real live counts for sidebar badges (cached briefly — sidebar renders on every admin page).
    // A badge only exists where the number means "this many things are waiting for
    // you". The businesses badge used to count every business on the platform,
    // which is a statistic, not a queue, and it sat in orange next to two genuine
    // queues; it now counts the ones actually awaiting a decision.
    $sideBadges = \Illuminate\Support\Facades\Cache::remember('admin_sidebar_badges', 60, function () {
        return [
            'businesses'    => \Illuminate\Support\Facades\DB::table('businesses')->whereNull('deleted_at')->where('status', 'pending')->count(),
            'kyc'           => \Illuminate\Support\Facades\DB::table('verification_applications')->whereIn('status', ['submitted', 'under_review'])->count(),
            'moderation'    => \Illuminate\Support\Facades\DB::table('product_reports')->where('status', 'open')->count(),
            'notifications' => \Illuminate\Support\Facades\DB::table('user_notifications')->whereNull('read_at')->count(),
        ];
    });
    $sideBadge = fn ($k) => ($sideBadges[$k] ?? 0) > 0 ? (string) $sideBadges[$k] : null;

    // [key, lucide icon, label, url, badge] — every item maps onto a real route
    // that renders. Three things changed here when the panel was consolidated:
    //
    // 1. Three rows were the same destination wearing a second name — Commissions
    //    pointed at admin.reports alongside Rapports, Documents at admin.media with
    //    a query string, Demandes d'Inscription at admin.artisans with a query
    //    string. A filter is not a section. Both filters live on the page they
    //    filter, so the rows are gone and nothing is lost.
    // 2. Vérifications is gone because the screen is: it queued the same rows the
    //    KYC Centre lists, and /verifications now redirects there.
    // 3. Thirteen working screens had no way in at all — payments, quotes,
    //    moderation, reviews, support, analytics, partners, certificates among them.
    //    A screen that the navigation does not admit to is a screen nobody uses.
    //    Every one of them is now one click from here, and the reviews queue two
    //    (via the tab strip on Modération).
    //
    // Deliberately absent: Abonnements, Sauvegardes and API Consommateurs. Those
    // three routes abort(404) today by decision, and a navigation entry that leads
    // to a 404 is worse than no entry. They belong here the day they render.
    $adGroups = [
        [null, [
            ['dashboard', 'layout-dashboard', $isFr ? 'TABLEAU DE BORD' : 'DASHBOARD', route('dashboard.admin'), null],
        ]],
        [$isFr ? 'Artisans & vérification' : 'Artisans & verification', [
            ['artisans',    'user',          'Artisans',                                              route('admin.artisans'), null],
            ['businesses',  'store',         $isFr ? 'Artisans & Boutiques' : 'Artisans & Shops',     route('admin.businesses'), $sideBadge('businesses')],
            ['kyc',         'badge-check',   $isFr ? 'KYC & Vérification' : 'KYC & Verification',     route('admin.kyc'), $sideBadge('kyc')],
            ['certificates','award',         $isFr ? 'Certificats' : 'Certificates',                  route('admin.certificates'), null],
        ]],
        [$isFr ? 'Catalogue & contenu' : 'Catalogue & content', [
            ['products',    'package',       $isFr ? 'Produits & Services' : 'Products & Services',   route('admin.products'), null],
            ['industries',  'shapes',        $isFr ? 'Catégories d\'Artisanat' : 'Craft Categories',  route('admin.industries'), null],
            ['regions',     'map-pin',       $isFr ? 'Régions & Centres' : 'Regions & Centres',       route('admin.regions'), null],
            ['collections', 'layers',        'Collections',                                           route('admin.collections'), null],
            ['news',        'newspaper',     $isFr ? 'Actualités & Annonces' : 'News & Announcements', route('admin.news'), null],
            ['events',      'calendar-days', $isFr ? 'Événements' : 'Events',                         route('admin.events'), null],
            ['media',       'image',         $isFr ? 'Médias & Ressources' : 'Media & Resources',     route('admin.media'), null],
            ['partners',    'handshake',     $isFr ? 'Partenaires' : 'Partners',                      route('admin.partners'), null],
            ['pages',       'file-text',     $isFr ? 'Pages & FAQ' : 'Pages & FAQ',                   route('admin.cms'), null],
        ]],
        [$isFr ? 'Commerce' : 'Commerce', [
            ['quotes',      'file-signature', $isFr ? 'Demandes de devis' : 'Quote requests',         route('admin.quotes'), null],
            ['orders',      'receipt',        $isFr ? 'Commandes & Factures' : 'Orders & Invoices',   route('admin.orders'), null],
            ['payments',    'wallet',         $isFr ? 'Paiements à contrôler' : 'Payments to check',  route('admin.payments'), null],
        ]],
        [$isFr ? 'Utilisateurs & modération' : 'Users & moderation', [
            ['users',       'users',         $isFr ? 'Utilisateurs' : 'Users',                        route('admin.users'), null],
            ['roles',       'shield-check',  $isFr ? 'Rôles & Permissions' : 'Roles & Permissions',   route('admin.roles'), null],
            ['moderation',  'flag',          $isFr ? 'Signalements & Avis' : 'Reports & Reviews',     route('admin.moderation'), $sideBadge('moderation')],
            ['adminsupport','life-buoy',     'Support',                                               route('admin.support'), null],
        ]],
        [$isFr ? 'Analyse & système' : 'Insight & system', [
            ['analytics',     'chart-line',     $isFr ? 'Analytique' : 'Analytics',                  route('admin.analytics'), null],
            ['reports',       'chart-column',   $isFr ? 'Rapports & Statistiques' : 'Reports & Statistics', route('admin.reports'), null],
            ['logs',          'scroll-text',    $isFr ? 'Journal d\'Activité' : 'Activity Log',      route('admin.audit-log'), null],
            ['exports',       'download',       $isFr ? 'Centre d\'export' : 'Data Export Centre',   route('admin.exports'), null],
            ['notifications', 'bell',           'Notifications',                                     route('admin.notifications'), $sideBadge('notifications')],
            ['settings',      'settings',       $isFr ? 'Paramètres Généraux' : 'General Settings',  route('admin.settings'), null],
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
            <p class="text-[13px] md:text-[11px] font-bold tracking-[0.02em] text-white uppercase leading-snug">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</p>
            <p class="mt-1 text-[12px] md:text-[9.5px] italic text-[#E9C25A] leading-snug">{{ $isFr ? 'Notre Héritage, Notre Fierté, Notre Avenir' : 'Our Heritage, Our Pride, Our Future' }}</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-4 pb-4 pt-1">
        @foreach($adGroups as [$adTitle, $adItems])
            @if($adTitle)
            <p class="mt-5 mb-2 px-1 text-[13px] md:text-[11.5px] font-bold tracking-[0.02em] text-[#E6B201] uppercase">{{ $adTitle }}</p>
            @endif
            @foreach($adItems as [$adKey, $adIcon, $adLabel, $adUrl, $adBadge])
                @if($adKey === $adminActive)
                {{-- The active pill has to separate from the rail it sits on. On the
                     deepened rail #0D5A30 is only 1.52:1, so dark mode uses the
                     contract's `brand` #2E9250 (3.22:1 on the rail) with `brand-ink`
                     #04150A on it (4.78:1) — white on that fill is 3.93:1 and fails. --}}
                <a href="{{ $adUrl }}" aria-current="page" class="mt-0.5 flex items-center gap-3 bg-[#0D5A30] dark:bg-[#2E9250] rounded-xl px-3 py-[9px] min-h-[44px] text-[14px] md:text-[13px] font-bold text-white dark:text-[#04150A] shadow-[inset_0_1px_0_rgba(255,255,255,0.12)] dark:shadow-none">
                    <i data-lucide="{{ $adIcon }}" class="w-[17px] h-[17px] shrink-0" style="stroke-width:1.9"></i>
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#DE8E14] text-white dark:text-[#2A1902] text-[12px] md:text-[10.5px] font-bold rounded-md px-2 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @else
                <a href="{{ $adUrl }}" class="flex items-center gap-3 rounded-xl px-3 py-[7px] min-h-[44px] text-[14px] md:text-[12.5px] {{ $adKey === 'dashboard' ? 'font-bold tracking-[0.03em] text-white' : 'text-[#EDF2EC]' }} hover:bg-white/5 transition-colors">
                    <i data-lucide="{{ $adIcon }}" class="w-[17px] h-[17px] shrink-0 text-[#E9C25A]" style="stroke-width:1.8"></i>
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#DE8E14] text-white dark:text-[#2A1902] text-[12px] md:text-[10.5px] font-bold rounded-md px-2 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="shrink-0 relative">
        <img src="{{ asset('images/landing/ad-side-vases.png') }}" alt="" class="w-full h-auto block">
        <p class="absolute left-[9%] top-[15%] w-[62%] font-serif text-[13.5px] leading-[1.6] md:text-[13.5px] text-[#F0EBDD]">{{ $adSideQuote }}</p>
    </div>
</aside>
