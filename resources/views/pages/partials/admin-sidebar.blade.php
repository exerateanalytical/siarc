{{-- Admin panel replica sidebar (design: "admin panel.png"): dark green with gold kente
     borders, grouped nav, République du Cameroun card.
     Expects: $lang, $isFr. Options: $adminActive (key of the active item). --}}
@php
    $adminActive = $adminActive ?? 'dashboard';

    // [key, icon, label, url, badge]
    $adGroups = [
        [null, [
            ['dashboard', 'layout-dashboard', $isFr ? 'Tableau de bord' : 'Dashboard', route('dashboard.admin'), null],
        ]],
        [$isFr ? 'Gestion du contenu' : 'Content management', [
            ['artisans',    'users',         'Artisans',                                        route('admin.businesses'), null],
            ['businesses',  'building-2',    $isFr ? 'Entreprises & Boutiques' : 'Businesses & Shops', route('admin.businesses'), null],
            ['products',    'package',       $isFr ? 'Produits & Services' : 'Products & Services', route('admin.products'), null],
            ['collections', 'layers',        $isFr ? 'Collections Héritage' : 'Heritage Collections', route('admin.cms'), null],
            ['industries',  'tags',          $isFr ? 'Catégories de Métiers' : 'Craft Categories', route('admin.industries'), null],
            ['regions',     'map-pin',       $isFr ? 'Régions & Centres d\'Artisanat' : 'Regions & Craft Centres', route('admin.industries') . '#regions', null],
            ['media',       'image',         $isFr ? 'Médias & Documents' : 'Media & Documents', route('admin.cms'), null],
            ['events',      'calendar-days', $isFr ? 'Événements & Festivals' : 'Events & Festivals', route('admin.events'), null],
            ['news',        'megaphone',     $isFr ? 'Actualités & Annonces' : 'News & Announcements', route('admin.cms'), null],
        ]],
        [$isFr ? 'Gestion des utilisateurs' : 'User management', [
            ['users',         'user-cog',     $isFr ? 'Utilisateurs' : 'Users',                 route('admin.users'), null],
            ['pending-art',   'user-check',   $isFr ? 'Artisans en Attente' : 'Pending Artisans', route('admin.verifications'), '28'],
            ['pending-biz',   'building',     $isFr ? 'Entreprises en Attente' : 'Pending Businesses', route('admin.businesses'), '16'],
            ['kyc',           'shield-check', $isFr ? 'Vérifications KYC' : 'KYC Verifications', route('admin.verifications'), null],
            ['roles',         'key-round',    $isFr ? 'Rôles & Permissions' : 'Roles & Permissions', route('admin.users'), null],
        ]],
        [$isFr ? 'Commerce & Transactions' : 'Commerce & Transactions', [
            ['orders',        'shopping-cart', $isFr ? 'Commandes' : 'Orders',                  route('admin.quotes'), null],
            ['payments',      'credit-card',   $isFr ? 'Paiements & Retraits' : 'Payments & Withdrawals', route('admin.reports'), null],
            ['quotes',        'file-text',     $isFr ? 'Devis & Propositions' : 'Quotes & Proposals', route('admin.quotes'), null],
            ['subscriptions', 'repeat',        $isFr ? 'Abonnements' : 'Subscriptions',         route('admin.api-consumers'), null],
            ['commissions',   'percent',       'Commissions',                                    route('admin.reports'), null],
        ]],
        ['SIARC 2026', [
            ['siarc',      'store',         $isFr ? 'Exposants & Pavillons' : 'Exhibitors & Pavilions', route('admin.siarc'), null],
            ['siarc-prog', 'calendar-days', $isFr ? 'Programme & Activités' : 'Programme & Activities', route('admin.siarc') . '#programme', null],
            ['siarc-vis',  'ticket',        $isFr ? 'Inscriptions Visiteurs' : 'Visitor Registrations', route('admin.siarc') . '#visiteurs', null],
            ['siarc-b2b',  'handshake',     $isFr ? 'Rendez-vous B2B' : 'B2B Meetings',        route('admin.siarc') . '#b2b', null],
            ['siarc-spk',  'mic',           $isFr ? 'Intervenants & Conférenciers' : 'Speakers & Lecturers', route('admin.siarc') . '#intervenants', null],
        ]],
        ['Administration', [
            ['partners',     'handshake', $isFr ? 'Partenaires' : 'Partners',                  route('admin.partners'), null],
            ['moderation',   'flag',      $isFr ? 'Modération' : 'Moderation',                 route('admin.moderation'), null],
            ['reports',      'chart-column', $isFr ? 'Rapports & Statistiques' : 'Reports & Statistics', route('admin.reports'), null],
            ['adminsupport', 'life-buoy', 'Support',                                            route('admin.support'), null],
        ]],
        [$isFr ? 'Outils & Paramètres' : 'Tools & Settings', [
            ['settings',      'settings',    $isFr ? 'Paramètres Généraux' : 'General Settings', route('admin.settings'), null],
            ['pages',         'panel-top',   $isFr ? 'Pages & Menus' : 'Pages & Menus',        route('admin.cms'), null],
            ['languages',     'languages',   $isFr ? 'Langues & Traductions' : 'Languages & Translations', route('admin.settings'), null],
            ['notifications', 'bell',        'Notifications',                                   route('notifications.index'), null],
            ['logs',          'database',    $isFr ? 'Sauvegardes & Logs' : 'Backups & Logs',  route('admin.audit-log'), null],
        ]],
    ];
@endphp
<aside id="ad-sidebar" class="shrink-0 lg:w-[250px] bg-gradient-to-b from-[#12522C] to-[#0B3B20] text-white flex flex-col relative">
    <img src="{{ asset('images/landing/ad-side-edge.png') }}" alt="" class="absolute inset-y-0 right-0 w-[13px] h-full object-cover opacity-90 pointer-events-none select-none hidden lg:block" aria-hidden="true">

    <div class="px-4 pt-4 pb-3 flex items-center gap-3">
        <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain shrink-0">
        <div class="leading-tight min-w-0">
            <p class="text-[11px] font-bold tracking-[0.02em] text-white uppercase leading-snug">{{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</p>
            <p class="mt-1 text-[9.5px] text-[#E9C25A] leading-snug">{{ $isFr ? 'Notre Héritage, Notre Fierté, Notre Avenir' : 'Our Heritage, Our Pride, Our Future' }}</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3.5 pb-4">
        @foreach($adGroups as [$adTitle, $adItems])
            @if($adTitle)
            <p class="mt-5 mb-1.5 px-1.5 text-[10.5px] font-bold tracking-[0.1em] text-[#E9C25A] uppercase">{{ $adTitle }}</p>
            @endif
            @foreach($adItems as [$adKey, $adIcon, $adLabel, $adUrl, $adBadge])
                @if($adKey === $adminActive)
                <a href="{{ $adUrl }}" class="mt-1 flex items-center gap-3 bg-[#F7F3E7] rounded-lg px-3.5 py-[9px] text-[13px] font-bold text-[#0B3B20]">
                    <i data-lucide="{{ $adIcon }}" class="w-[16px] h-[16px] shrink-0" style="stroke-width:2"></i>
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#E9B23C] text-[#3A2A03] text-[10.5px] font-bold rounded-md px-1.5 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @else
                <a href="{{ $adUrl }}" class="flex items-center gap-3 rounded-lg px-1.5 py-[7px] text-[12.5px] text-[#E8F0EA] hover:bg-white/5 transition-colors">
                    <i data-lucide="{{ $adIcon }}" class="w-[15px] h-[15px] shrink-0" style="stroke-width:1.7;color:#C9DCCF"></i>
                    <span class="flex-1 truncate">{{ $adLabel }}</span>
                    @if($adBadge)<span class="shrink-0 bg-[#E9B23C] text-[#3A2A03] text-[10.5px] font-bold rounded-md px-1.5 py-0.5">{{ $adBadge }}</span>@endif
                </a>
                @endif
            @endforeach
            @if(!$loop->last && $adTitle !== null)
            @endif
        @endforeach
    </nav>

    <div class="shrink-0">
        <img src="{{ asset('images/landing/ad-side-band.png') }}" alt="" class="w-full h-[13px] object-cover" aria-hidden="true">
        <div class="p-3">
            <div class="bg-[#FBF9F2] rounded-xl px-3 py-2.5 flex items-center gap-3">
                <img src="{{ asset('images/landing/ad-coat.png') }}" alt="" class="w-[30px] h-[36px] object-contain shrink-0" aria-hidden="true">
                <div class="leading-tight">
                    <p class="text-[10.5px] font-bold text-[#1B1B18] uppercase">{{ $isFr ? 'République du Cameroun' : 'Republic of Cameroon' }}</p>
                    <p class="mt-0.5 text-[10px] text-[#55524A]">{{ $isFr ? 'Paix - Travail - Patrie' : 'Peace - Work - Fatherland' }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>
