@php
    $isFr = $lang === 'fr';

    $shopName = $business->name_fr ?? ($siacUser['name'] ?? 'Artisan');
    $shopLogo = ($business->logo ?? null) ? asset('storage/' . $business->logo) : asset('images/landing/sd-avatar-shop.png');
    $isVerified = in_array($business->verification_tier ?? '', ['verified', 'certified']);

    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $memberSince = null;
    if ($business && $business->created_at) {
        $mDate = \Illuminate\Support\Carbon::parse($business->created_at);
        $memberSince = $isFr ? ($frMonths[$mDate->month] . ' ' . $mDate->year) : $mDate->format('F Y');
    }

    $ownStoreUrl = $business ? route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) : route('business.create');

    // Sidebar navigation — design order; badges: real counts where the system exists,
    // design numbers where it doesn't (orders/reviews have no web system yet).
    $navItems = [
        ['sd-nav-1.png',  $isFr ? 'Tableau de bord' : 'Dashboard',              route('dashboard.entrepreneur'), null, true],
        ['sd-nav-2.png',  $isFr ? 'Commandes' : 'Orders',                       route('dashboard.quotes'), ['28', 'red'], false],
        ['sd-nav-3.png',  $isFr ? 'Produits' : 'Products',                      $ownStoreUrl, [(string) $productCount, 'gray'], false],
        ['sd-nav-4.png',  $isFr ? 'Événements' : 'Events',                      route('events.index'), [(string) $eventParticipations->count(), 'red'], false],
        ['sd-nav-5.png',  $isFr ? 'Collections' : 'Collections',                route('saved.index'), null, false],
        ['sd-nav-6.png',  'Messages',                                            route('messages.inbox'), [(string) $messageCount, 'red'], false],
        ['sd-nav-7.png',  $isFr ? 'Avis & Clients' : 'Reviews & Clients',       $ownStoreUrl, ['24', 'red'], false],
        ['sd-nav-8.png',  $isFr ? 'Statistiques' : 'Statistics',                '#performances', null, false],
        ['sd-nav-9.png',  $isFr ? 'Revenus & Portefeuille' : 'Revenue & Wallet', '#portefeuille', null, false],
        ['sd-nav-10.png', $isFr ? 'Promotions & Offres' : 'Promotions & Offers', route('contact'), null, false],
        ['sd-nav-11.png', $isFr ? 'Expéditions' : 'Shipping',                   route('support.index'), null, false],
        ['sd-nav-12.png', $isFr ? 'Paramètres boutique' : 'Shop settings',      $business ? route('business.edit') : route('business.create'), null, false],
        ['sd-nav-13.png', $isFr ? 'Mon compte' : 'My account',                  route('profile.show'), null, false],
        ['sd-nav-14.png', $isFr ? 'Aide & Support' : 'Help & Support',          route('support.index'), null, false],
    ];

    $kpis = [
        ['sd-kpi-icon-1.png', 'sd-kpi-spark-1.png', $isFr ? "Chiffre d'affaires" : 'Revenue', '356 000', 'FCFA', '↑ 24%', 'to-[#F1F8EF]', 'border-[#E3EFE2]'],
        ['sd-kpi-icon-2.png', 'sd-kpi-spark-2.png', $isFr ? 'Commandes' : 'Orders', '28', null, '↑ 18%', 'to-[#FEF7EC]', 'border-[#F4E8D3]'],
        ['sd-kpi-icon-3.png', 'sd-kpi-spark-3.png', $isFr ? 'Visites de la boutique' : 'Shop visits', number_format(max((int) ($business->views_count ?? 0), 0) ?: 1245, 0, ',', ' '), null, '↑ 12%', 'to-[#F2F6FE]', 'border-[#E1E9F8]'],
        ['sd-kpi-icon-4.png', 'sd-kpi-spark-4.png', $isFr ? 'Taux de conversion' : 'Conversion rate', '3.6%', null, '↑ 0.6%', 'to-[#FEF3F3]', 'border-[#F6DFDF]'],
        ['sd-kpi-icon-5.png', 'sd-kpi-spark-5.png', $isFr ? 'Avis positifs' : 'Positive reviews', '96%', null, null, 'to-[#F1F8EF]', 'border-[#E3EFE2]'],
    ];

    $designOrders = [
        ['sd-order-1.png', $isFr ? 'Masque Bamileké Royal' : 'Royal Bamileke Mask',           '#GVN-2025-0012', 'delivered', '05 Mai 2025', '75 000 FCFA'],
        ['sd-order-2.png', $isFr ? 'Panier Tressé Traditionnel' : 'Traditional Woven Basket', '#GVN-2025-0011', 'pending',   '04 Mai 2025', '28 000 FCFA'],
        ['sd-order-3.png', $isFr ? 'Collier Perles Recyclées' : 'Recycled Bead Necklace',     '#GVN-2025-0010', 'shipped',   '02 Mai 2025', '15 500 FCFA'],
        ['sd-order-4.png', $isFr ? 'Statue Traditionnelle Sawa' : 'Traditional Sawa Statue',  '#GVN-2025-0009', 'delivered', '01 Mai 2025', '38 000 FCFA'],
    ];
    $orderStatus = [
        'delivered' => ['✓ ' . ($isFr ? 'Livrée' : 'Delivered'), 'bg-[#E9F6EE] text-[#157A43]'],
        'pending'   => ['✓ ' . ($isFr ? 'En cours' : 'In progress'), 'bg-[#FDF7E3] text-[#B07C10]'],
        'shipped'   => ['✓ ' . ($isFr ? 'Expédiée' : 'Shipped'), 'bg-[#EBF1FD] text-[#2E5FD0]'],
    ];

    $activity = $isFr ? [
        ['shopping-cart', '#157A43', 'Il y a 5 min',  "Nouvelle commande #GVN-2025-0016\nMontant : 12 000 FCFA"],
        ['mail',          '#157A43', 'Il y a 23 min', "Nouveau message de Jean M.\nIntéressé par votre produit"],
        ['heart',         '#DC2626', 'Il y a 1 h',    "Votre produit \u{201C}Masque Sawa\u{201D} a été ajouté\naux favoris"],
        ['star',          '#F5A623', 'Il y a 2 h',    "Avis 5 étoiles reçu pour \u{201C}Collier Perles\nRecyclées\u{201D}"],
        ['check-circle-2','#157A43', 'Il y a 3 h',    "Produit \u{201C}Vase Décoratif Béti\u{201D} approuvé"],
    ] : [
        ['shopping-cart', '#157A43', '5 min ago',  "New order #GVN-2025-0016\nAmount: 12 000 FCFA"],
        ['mail',          '#157A43', '23 min ago', "New message from Jean M.\nInterested in your product"],
        ['heart',         '#DC2626', '1 h ago',    "Your product \u{201C}Masque Sawa\u{201D} was added\nto favorites"],
        ['star',          '#F5A623', '2 h ago',    "5-star review received for \u{201C}Collier Perles\nRecyclées\u{201D}"],
        ['check-circle-2','#157A43', '3 h ago',    "Product \u{201C}Vase Décoratif Béti\u{201D} approved"],
    ];

    // Popular products: real products (preserves the edit flow) or the design's five.
    $rankColors = ['#F5A623', '#0E3D22', '#2E7D4F', '#9AA39D', '#9AA39D'];
    $popPcts = ['↑ 24%', '↑ 18%', '↑ 12%', '↑ 8%', '↑ 6%'];
    $designPopular = [
        ['sd-pop-1.png', $isFr ? 'Masque Bamileké Royal' : 'Royal Bamileke Mask', '125'],
        ['sd-pop-2.png', $isFr ? 'Panier Tressé Traditionnel' : 'Traditional Woven Basket', '98'],
        ['sd-pop-3.png', $isFr ? 'Collier Perles Recyclées' : 'Recycled Bead Necklace', '76'],
        ['sd-pop-4.png', $isFr ? 'Statue Traditionnelle Sawa' : 'Traditional Sawa Statue', '65'],
        ['sd-pop-5.png', $isFr ? 'Vase Décoratif Béti' : 'Beti Decorative Vase', '54'],
    ];
    $realPopular = collect($products)->sortByDesc(fn ($p) => $p->views_count ?? 0)->take(5)->values();

    $regions = [
        ['Centre', '45%', '#157A43'],
        ['Littoral', '25%', '#F5A623'],
        ['Ouest', '15%', '#E8880E'],
        ['Nord-Ouest', '8%', '#9AA39D'],
        ['Sud-Ouest', '5%', '#9AA39D'],
        [$isFr ? 'Autres' : 'Others', '2%', '#C4C9C5'],
    ];

    $quickActions = [
        ['plus-square',    $isFr ? 'Ajouter un produit' : 'Add a product',        route('products.web-create'), null],
        ['package',        $isFr ? 'Gérer produits' : 'Manage products',          $ownStoreUrl, null],
        ['calendar-plus',  $isFr ? 'Ajouter un événement' : 'Add an event',       route('events.index'), null],
        ['clipboard-list', $isFr ? 'Voir commandes' : 'View orders',              route('messages.inbox'), '28'],
        ['message-circle', 'Messages',                                             route('messages.inbox'), (string) $messageCount],
        ['tags',           $isFr ? 'Créer une promotion' : 'Create a promotion',  route('contact'), null],
        ['truck',          $isFr ? 'Gérer expéditions' : 'Manage shipping',       route('support.index'), null],
        ['layers',         $isFr ? 'Mes collections' : 'My collections',          route('saved.index'), null],
        ['store',          $isFr ? 'Voir ma boutique' : 'View my shop',           $ownStoreUrl, null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Tableau de bord — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Dashboard — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sidegreen: '#002714',
                        sideband:  '#031E12',
                        siderow:   '#14391E',
                        dashgold:  '#FCB806',
                        leaf:      '#164C28',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        /* Sidebar slide-over (explicit CSS — Tailwind Play translate variants are unreliable here) */
        @media (max-width: 1023.5px) {
            #dash-sidebar { transform: translateX(-100%); }
            #dash-sidebar.open { transform: translateX(0); }
        }
    </style>
</head>
<body class="bg-[#FCFCFC] text-[#1B1B18] antialiased">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    <aside id="dash-sidebar" class="fixed inset-y-0 left-0 z-40 w-[300px] xl:w-[337px] bg-sidegreen overflow-y-auto">
        <!-- Brand -->
        <div class="bg-sideband px-5 py-4 flex items-center gap-3.5">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[52px] h-[56px] object-contain shrink-0">
            <span>
                <span class="block text-[12.5px] font-bold tracking-[0.02em] text-white uppercase leading-[1.4]">
                    {{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}<br>
                    {{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}
                </span>
                <span class="block mt-0.5 text-[10.5px] text-[#A8B8AC]">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </div>

        <!-- Profile -->
        <div class="px-5 pt-6 pb-5 flex items-start gap-4">
            <span class="relative shrink-0">
                <img src="{{ $shopLogo }}" alt="" class="w-[88px] h-[88px] rounded-full object-cover bg-white">
                <a href="{{ $business ? route('business.edit') : route('business.create') }}" aria-label="{{ $isFr ? 'Modifier le logo' : 'Edit logo' }}"
                    class="absolute -bottom-0.5 right-0 w-7 h-7 rounded-full bg-[#0B3018] border-2 border-sidegreen flex items-center justify-center text-white">
                    <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                </a>
            </span>
            <div class="pt-1 min-w-0">
                <a href="{{ route('verification.show') }}" class="flex items-center gap-1.5 text-[13.5px] font-semibold text-white">
                    {{ $isVerified ? ($isFr ? 'Boutique Verifiée' : 'Verified Shop') : ($isFr ? 'Boutique' : 'Shop') }}
                    @if($isVerified)<i data-lucide="badge-check" class="w-4 h-4 text-[#2FBF71]" style="fill:#2FBF71;color:#002714"></i>@endif
                </a>
                <p class="mt-0.5 text-[21px] font-bold text-white leading-tight truncate">{{ $shopName }}</p>
                <span class="mt-1.5 inline-block bg-gradient-to-b from-[#FFD84D] to-[#F5B301] text-[#3A2A03] text-[12px] font-bold px-3 py-1 rounded-md">
                    {{ $isFr ? 'Vendeur Gold' : 'Gold Seller' }}
                </span>
                @if($memberSince)
                <p class="mt-2 text-[12px] text-[#C6D4C9]">{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $memberSince }}</p>
                @endif
            </div>
        </div>

        <!-- Navigation -->
        <nav class="mt-1">
            @foreach($navItems as [$navIcon, $navLabel, $navHref, $navBadge, $navActive])
            <a href="{{ $navHref }}" class="relative flex items-center gap-4 pl-6 pr-5 py-[10.5px] {{ $navActive ? 'bg-siderow' : 'hover:bg-white/5' }} transition-colors">
                @if($navActive)<span class="absolute left-0 inset-y-0 w-[5px] bg-dashgold"></span>@endif
                <img src="{{ asset('images/landing/' . $navIcon) }}" alt="" class="w-[26px] h-[26px] shrink-0" aria-hidden="true">
                <span class="text-[14.5px] {{ $navActive ? 'text-dashgold font-semibold' : 'text-white' }}">{{ $navLabel }}</span>
                @if($navBadge)
                <span class="ml-auto min-w-[30px] text-center text-[11.5px] font-bold px-2 py-[3px] rounded-full {{ $navBadge[1] === 'red' ? 'bg-[#DC0508] text-white' : 'bg-white/15 text-white' }}">{{ $navBadge[0] }}</span>
                @endif
            </a>
            @endforeach
        </nav>

        <!-- Promo card -->
        <div class="relative m-4 mt-5 rounded-2xl overflow-hidden bg-[#0D3A20]">
            <div class="absolute inset-0 opacity-30 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <img src="{{ asset('images/landing/sd-promo-art.png') }}" alt="" class="absolute right-0 bottom-0 h-[92%] pointer-events-none select-none" aria-hidden="true">
            <div class="relative p-5 pr-[112px]">
                <h3 class="text-[17px] font-bold text-white leading-snug">{{ $isFr ? 'Développez votre activité' : 'Grow your business' }}</h3>
                <p class="mt-2 text-[12.5px] text-[#D4E0D6] leading-relaxed">
                    {{ $isFr ? 'Boostez votre visibilité et attirez plus de clients grâce à nos outils marketing.' : 'Boost your visibility and attract more clients with our marketing tools.' }}
                </p>
                <a href="{{ route('contact') }}" class="mt-4 inline-block bg-[#FEBF00] hover:bg-[#EDB100] text-[#3A2A03] text-[12.5px] font-bold px-4 py-2.5 rounded-lg transition-colors">
                    {{ $isFr ? 'Découvrir nos offres' : 'Discover our offers' }}
                </a>
            </div>
        </div>
    </aside>

    <div id="dash-backdrop" class="fixed inset-0 z-[35] bg-black/50 hidden lg:hidden"></div>

    <!-- Main column -->
    <div class="flex-1 min-w-0 lg:ml-[300px] xl:ml-[337px]">

        <!-- Tricolor bar (desktop) -->
        <div class="hidden lg:flex h-[27px]">
            <div class="w-[32.5%] bg-[#014D25]"></div>
            <div class="relative w-[25%] bg-[#CA0107]">
                <svg viewBox="0 0 24 24" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-4 h-4 fill-[#F3AA02]" aria-hidden="true">
                    <path d="M12 1.5 14.7 8.6l7.6.3-6 4.7 2.1 7.3L12 16.6 5.6 20.9l2.1-7.3-6-4.7 7.6-.3z"/>
                </svg>
            </div>
            <div class="flex-1 bg-[#F3AA02]"></div>
        </div>

        <!-- Header (desktop) -->
        <header class="hidden lg:block bg-white border-b border-[#F0F0EE]">
            <div class="flex items-center gap-3 xl:gap-5 px-4 xl:px-8 py-3.5">
                <button id="dash-menu-btn" class="lg:hidden p-2 -ml-1 rounded-md hover:bg-gray-100" aria-label="Menu">
                    <i data-lucide="menu" class="w-5 h-5 text-[#1B1B18]"></i>
                </button>

                <form action="{{ route('gallery.search') }}" method="GET" class="hidden md:flex items-stretch flex-1 max-w-[640px]">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="q" type="search" placeholder="{{ $isFr ? 'Rechercher un produit, un artisan, un événement...' : 'Search a product, an artisan, an event...' }}"
                        class="flex-1 min-w-0 h-[46px] border border-[#E7E7E5] rounded-l-xl px-5 text-[13.5px] placeholder-[#9A968C] focus:outline-none focus:border-dashgold">
                    <select name="categorie" class="hidden xl:block h-[46px] border-y border-[#E7E7E5] px-3 text-[13.5px] text-[#1B1B18] bg-white focus:outline-none cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes catégories' : 'All categories' }}</option>
                        @foreach(['arts-decoration' => $isFr ? 'Arts & Décoration' : 'Arts & Decoration', 'textile-mode' => $isFr ? 'Mode & Textile' : 'Fashion & Textile', 'bois-sculpture' => $isFr ? 'Bois & Sculpture' : 'Wood & Sculpture', 'poterie-ceramique' => $isFr ? 'Poterie & Céramique' : 'Pottery & Ceramics', 'bijouterie-accessoires' => $isFr ? 'Bijouterie & Accessoires' : 'Jewelry & Accessories'] as $catVal => $catLabel)
                        <option value="{{ $catVal }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                    <button type="submit" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"
                        class="w-[62px] h-[46px] bg-[#052912] hover:bg-leaf rounded-r-xl flex items-center justify-center text-white transition-colors">
                        <i data-lucide="search" class="w-[18px] h-[18px]"></i>
                    </button>
                </form>

                <div class="ml-auto flex items-center gap-3 xl:gap-6">
                    <a href="{{ route('notifications.index') }}" class="relative p-1" aria-label="Notifications">
                        <i data-lucide="bell" class="w-[22px] h-[22px] text-[#1B1B18]"></i>
                        <span class="absolute -top-1 -right-1.5 bg-[#DC0508] text-white text-[10px] font-bold min-w-[17px] h-[17px] px-1 rounded-full flex items-center justify-center">5</span>
                    </a>
                    <a href="{{ route('messages.inbox') }}" class="relative p-1" aria-label="Messages">
                        <i data-lucide="mail" class="w-[22px] h-[22px] text-[#1B1B18]"></i>
                        <span class="absolute -top-1 -right-1.5 bg-[#DC0508] text-white text-[10px] font-bold min-w-[17px] h-[17px] px-1 rounded-full flex items-center justify-center">{{ $messageCount }}</span>
                    </a>
                    <a href="{{ route('support.index') }}" class="hidden sm:flex items-center gap-1.5 text-[13.5px] font-medium text-[#1B1B18]">
                        <i data-lucide="circle-help" class="w-[18px] h-[18px]"></i>
                        {{ $isFr ? 'Aide' : 'Help' }}
                    </a>
                    <div class="relative group hidden sm:block">
                        <button class="flex items-center gap-1 text-[13.5px] font-semibold text-[#1B1B18]">
                            {{ strtoupper($lang) }}
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                        </button>
                        <div class="absolute right-0 top-full w-28 bg-white rounded-lg shadow-lg border border-[#E7E7E5] py-1 hidden group-hover:block z-50">
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-leaf' : 'text-[#262521]' }}">FR — Français</a>
                            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-leaf' : 'text-[#262521]' }}">EN — English</a>
                        </div>
                    </div>
                    <div class="relative group">
                        <button class="flex items-center gap-2.5">
                            <img src="{{ $shopLogo }}" alt="" class="w-[42px] h-[42px] rounded-full object-cover bg-[#F2F0E7]">
                            <span class="hidden xl:block text-left leading-tight">
                                <span class="block text-[13px] font-semibold text-[#1B1B18]">{{ $isVerified ? ($isFr ? 'Boutique Verifiée' : 'Verified Shop') : ($isFr ? 'Ma boutique' : 'My shop') }}</span>
                                <span class="block text-[12px] text-[#6F6B60]">{{ $shopName }}</span>
                            </span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i>
                        </button>
                        <div class="absolute right-0 top-full w-52 bg-white rounded-lg shadow-lg border border-[#E7E7E5] py-1 hidden group-hover:block z-50">
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-[13px] text-[#262521] hover:bg-gray-50">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                            <a href="{{ route('membership.certificate') }}" class="block px-4 py-2 text-[13px] text-[#262521] hover:bg-gray-50">{{ $isFr ? 'Mon certificat d\'adhésion' : 'My membership certificate' }}</a>
                            <a href="{{ route('security.show') }}" class="block px-4 py-2 text-[13px] text-[#262521] hover:bg-gray-50">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-[13px] text-red-600 hover:bg-red-50">{{ $isFr ? 'Se déconnecter' : 'Log out' }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ══════════════ Mobile dashboard (seller mobile dashboard.png) ══════════════ -->
        @php
            $visits = number_format(max((int) ($business->views_count ?? 0), 0) ?: 1245, 0, ',', ' ');
            $smKpis = [
                ['sm-kpi-1.png', '356K', 'FCFA', $isFr ? 'Revenus' : 'Revenue',            '↑ 24%', true,  '#sm-wallet'],
                ['sm-kpi-2.png', '28',  null,   $isFr ? 'Commandes' : 'Orders',            '↑ 18%', true,  route('messages.inbox')],
                ['sm-kpi-3.png', '145', null,   $isFr ? 'Demandes' : 'Requests',           '↑ 15%', true,  route('messages.inbox')],
                ['sm-kpi-4.png', '38',  null,   $isFr ? 'Devis en attente' : 'Pending quotes', '↓ 8%', false, route('messages.inbox')],
                ['sm-kpi-5.png', (string) $messageCount, null, 'Messages',                 '↓ 12%', false, route('messages.inbox')],
                ['sm-kpi-6.png', $visits, null, $isFr ? 'Visites' : 'Visits',              '↑ 12%', true,  $ownStoreUrl],
            ];
            $smPipeline = [
                ['sm-pipe-1.png', '145', $isFr ? "Demandes\nreçues" : "Requests\nreceived"],
                ['sm-pipe-2.png', '38',  $isFr ? 'Préparation' : 'Preparing'],
                ['sm-pipe-3.png', '24',  $isFr ? 'Devis envoyés' : 'Quotes sent'],
                ['sm-pipe-4.png', '18',  $isFr ? 'En négociation' : 'Negotiating'],
                ['sm-pipe-5.png', '14',  $isFr ? 'Acceptés' : 'Accepted'],
            ];
            $smActivity = $isFr ? [
                ['sm-act-1.png', "Nouvelle demande de devis pour \u{201C}Masque Bamileké Royal\u{201D}", 'Il y a 15 min', '#157A43'],
                ['sm-act-2.png', 'Commande #GVN-2025-0016 confirmée', 'Il y a 1 h', '#F5A623'],
                ['sm-act-3.png', 'Nouveau message de Jean M. (France)', 'Il y a 2 h', '#2E6BE0'],
                ['sm-act-4.png', "Avis 5 étoiles reçu pour \u{201C}Collier Perles Recyclées\u{201D}", 'Il y a 5 h', '#8B5CF6'],
                ['sm-act-5.png', 'Paiement reçu pour commande #GVN-2025-0014', 'Il y a 1 jour', '#157A43'],
            ] : [
                ['sm-act-1.png', "New quote request for \u{201C}Masque Bamileké Royal\u{201D}", '15 min ago', '#157A43'],
                ['sm-act-2.png', 'Order #GVN-2025-0016 confirmed', '1 h ago', '#F5A623'],
                ['sm-act-3.png', 'New message from Jean M. (France)', '2 h ago', '#2E6BE0'],
                ['sm-act-4.png', "5-star review received for \u{201C}Collier Perles Recyclées\u{201D}", '5 h ago', '#8B5CF6'],
                ['sm-act-5.png', 'Payment received for order #GVN-2025-0014', '1 day ago', '#157A43'],
            ];
            $smActions = [
                ['sm-qa-1.png', $isFr ? "Ajouter\nun produit" : "Add\na product",          route('products.web-create'), null],
                ['sm-qa-2.png', $isFr ? "Créer\nun devis" : "Create\na quote",             route('messages.inbox'), null],
                ['sm-qa-3.png', $isFr ? "Répondre\naux messages" : "Reply\nto messages",   route('messages.inbox'), (string) $messageCount],
                ['sm-qa-4.png', $isFr ? "Voir\ncommandes" : "View\norders",                route('messages.inbox'), null],
                ['sm-qa-5.png', $isFr ? "Créer\névénement" : "Create\nan event",           route('events.index'), null],
                ['sm-qa-6.png', $isFr ? "Ma\nboutique" : "My\nshop",                       $ownStoreUrl, null],
            ];
            $smProducts = [
                ['sm-prod-1.png', $isFr ? 'Masque Bamileké Royal' : 'Royal Bamileke Mask',           '1.2K', '24'],
                ['sm-prod-2.png', $isFr ? 'Panier Tressé Traditionnel' : 'Traditional Woven Basket', '980',  '18'],
                ['sm-prod-3.png', $isFr ? 'Collier Perles Recyclées' : 'Recycled Bead Necklace',     '760',  '12'],
                ['sm-prod-4.png', $isFr ? 'Statue Traditionnelle Sawa' : 'Traditional Sawa Statue',  '650',  '9'],
            ];
        @endphp
        <div class="lg:hidden">
            <!-- Mobile header -->
            <header class="bg-white px-4 pt-3.5 pb-3 flex items-center gap-3">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[39px] h-[42px] object-contain shrink-0">
                <span class="leading-tight min-w-0">
                    <span class="block text-[12px] font-bold tracking-[0.01em] text-[#14532D] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                    <span class="block text-[12px] font-bold tracking-[0.01em] text-[#14532D] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                    <span class="block text-[9.5px] text-[#2E7D4F] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                </span>
                <a href="{{ route('notifications.index') }}" class="relative ml-auto p-1 shrink-0" aria-label="Notifications">
                    <i data-lucide="bell" class="w-[24px] h-[24px] text-[#1B1B18]" style="stroke-width:1.8"></i>
                    <span class="absolute -top-1.5 -right-2 bg-[#D40C0F] text-white text-[10px] font-bold min-w-[19px] h-[19px] px-1 rounded-full flex items-center justify-center">12</span>
                </a>
                <button type="button" class="sm-menu-btn p-1 shrink-0" aria-label="Menu">
                    <i data-lucide="menu" class="w-[26px] h-[26px] text-[#1B1B18]" style="stroke-width:2.2"></i>
                </button>
            </header>

            <div class="px-4 pb-24 pt-1 space-y-3.5 bg-[#FEFEFE]">

                @if($business)

                <!-- Profile hero card -->
                <section class="relative bg-[#012716] rounded-2xl overflow-hidden">
                    <img src="{{ asset('images/landing/sm-hero-flag.png') }}" alt="" class="absolute right-0 inset-y-0 h-full pointer-events-none select-none" aria-hidden="true">
                    <div class="relative flex items-center gap-3.5 p-3.5">
                        <span class="relative shrink-0">
                            <img src="{{ ($business->logo ?? null) ? asset('storage/' . $business->logo) : asset('images/landing/sm-avatar.png') }}" alt="" class="w-[66px] h-[66px] rounded-full object-cover bg-white">
                            <a href="{{ route('business.edit') }}" aria-label="{{ $isFr ? 'Modifier le logo' : 'Edit logo' }}"
                                class="absolute -bottom-0.5 -right-0.5 w-[22px] h-[22px] rounded-full bg-[#0B5B31] border-2 border-[#012716] flex items-center justify-center text-white">
                                <i data-lucide="camera" class="w-3 h-3"></i>
                            </a>
                        </span>
                        <div class="min-w-0 py-1">
                            <a href="{{ route('verification.show') }}" class="flex items-center gap-1.5 text-[11.5px] font-semibold text-white">
                                {{ $isVerified ? ($isFr ? 'Boutique Vérifiée' : 'Verified Shop') : ($isFr ? 'Boutique' : 'Shop') }}
                                @if($isVerified)<i data-lucide="badge-check" class="w-3.5 h-3.5" style="fill:#2FBF71;color:#012716"></i>@endif
                            </a>
                            <p class="text-[17px] font-bold text-white leading-tight truncate">{{ $shopName }}</p>
                            <span class="mt-1 inline-flex items-center gap-1.5">
                                <span class="bg-gradient-to-b from-[#FFD84D] to-[#F5B301] text-[#3A2A03] text-[10px] font-bold px-2 py-0.5 rounded-md">{{ $isFr ? 'Vendeur Gold' : 'Gold Seller' }}</span>
                                <i data-lucide="star" class="w-3 h-3 text-[#F5B301]" style="fill:#F5B301"></i>
                            </span>
                            @if($memberSince)
                            <p class="mt-1 text-[10px] text-[#C6D4C9]">{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $memberSince }}</p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ $ownStoreUrl }}" class="absolute right-3.5 bottom-3 inline-flex items-center gap-2 bg-[#0A3D22]/80 border border-white/70 rounded-lg px-3.5 py-[7px] text-[12px] font-semibold text-white">
                        {{ $isFr ? 'Voir ma boutique' : 'View my shop' }}
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                </section>

                <!-- KPI tiles -->
                <div class="grid grid-cols-3 gap-2">
                    @foreach($smKpis as [$kIcon, $kVal, $kUnit, $kLabel, $kDelta, $kUp, $kHref])
                    <a href="{{ $kHref }}" class="bg-white border border-[#F0F0EE] rounded-xl px-2.5 py-3 flex items-start gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                        <img src="{{ asset('images/landing/' . $kIcon) }}" alt="" class="w-[28px] h-[28px] shrink-0" aria-hidden="true">
                        <span class="min-w-0">
                            <span class="block text-[15px] font-bold text-[#1B1B18] leading-tight">{{ $kVal }}@if($kUnit)<span class="text-[9px] font-bold ml-0.5">{{ $kUnit }}</span>@endif</span>
                            <span class="block text-[10px] text-[#55524A] leading-tight">{{ $kLabel }}</span>
                            <span class="block mt-0.5 text-[10px] font-semibold {{ $kUp ? 'text-[#157A43]' : 'text-[#D43C3C]' }}">{{ $kDelta }}</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6] ml-auto self-center shrink-0"></i>
                    </a>
                    @endforeach
                </div>

                <!-- Pipeline des devis -->
                <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Pipeline des devis' : 'Quote pipeline' }}</h2>
                        <a href="{{ route('messages.inbox') }}" class="text-[11.5px] font-medium text-[#157A43]">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
                    </div>
                    <div class="relative mt-4 flex items-start">
                        <span class="absolute left-[10%] right-[10%] top-[15px] border-t-2 border-dashed border-[#DBDDDB]"></span>
                        @foreach($smPipeline as [$pIcon, $pVal, $pLabel])
                        <div class="relative w-1/5 flex flex-col items-center text-center">
                            <img src="{{ asset('images/landing/' . $pIcon) }}" alt="" class="w-[31px] h-[31px]" aria-hidden="true">
                            <p class="mt-2 text-[15px] font-bold text-[#1B1B18] leading-none">{{ $pVal }}</p>
                            <p class="mt-1 text-[9.5px] text-[#55524A] leading-tight whitespace-pre-line">{{ $pLabel }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>

                <!-- Activité récente -->
                <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Activité récente' : 'Recent activity' }}</h2>
                        <a href="{{ route('notifications.index') }}" class="text-[11.5px] font-medium text-[#157A43]">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
                    </div>
                    <div class="mt-2 divide-y divide-[#F4F4F2]">
                        @foreach($smActivity as [$aIcon, $aText, $aTime, $aColor])
                        <div class="flex items-center gap-2.5 py-2.5">
                            <img src="{{ asset('images/landing/' . $aIcon) }}" alt="" class="w-[19px] h-[19px] shrink-0" aria-hidden="true">
                            <p class="flex-1 min-w-0 text-[11.5px] text-[#1B1B18] leading-snug">{{ $aText }}</p>
                            <span class="text-[10px] text-[#8A857A] whitespace-nowrap shrink-0">{{ $aTime }}</span>
                            <span class="w-[7px] h-[7px] rounded-full shrink-0" style="background:{{ $aColor }}"></span>
                        </div>
                        @endforeach
                    </div>
                </section>

                <!-- Actions rapides -->
                <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
                    <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-3 grid grid-cols-6 gap-1.5">
                        @foreach($smActions as [$qIcon, $qLabel, $qHref, $qBadge])
                        <a href="{{ $qHref }}" class="relative bg-[#F8F9F8] rounded-xl px-1 pt-2.5 pb-2 text-center">
                            <span class="relative inline-block">
                                <img src="{{ asset('images/landing/' . $qIcon) }}" alt="" class="w-[26px] h-[26px] mx-auto" aria-hidden="true">
                                @if($qBadge)
                                <span class="absolute -top-2 -right-3 bg-[#D40C0F] text-white text-[8.5px] font-bold min-w-[15px] h-[15px] px-0.5 rounded-full flex items-center justify-center">{{ $qBadge }}</span>
                                @endif
                            </span>
                            <span class="mt-1.5 block text-[9px] text-[#3B382F] leading-[1.25] whitespace-pre-line">{{ $qLabel }}</span>
                        </a>
                        @endforeach
                    </div>
                </section>

                <!-- Produits les plus performants -->
                <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Produits les plus performants' : 'Top performing products' }}</h2>
                        <a href="{{ $ownStoreUrl }}" class="text-[11.5px] font-medium text-[#157A43] whitespace-nowrap ml-2">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach($smProducts as [$prImg, $prName, $prViews, $prQuotes])
                        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="min-w-0">
                            <img src="{{ asset('images/landing/' . $prImg) }}" alt="" class="w-full rounded-xl">
                            <p class="mt-1.5 text-[10px] font-bold text-[#1B1B18] leading-tight truncate">{{ $prName }}</p>
                            <p class="mt-0.5 text-[9px] text-[#8A857A] truncate">{{ $prViews }} {{ $isFr ? 'vues' : 'views' }} <span class="mx-0.5">•</span> {{ $prQuotes }} {{ $isFr ? 'devis' : 'quotes' }}</p>
                        </a>
                        @endforeach
                    </div>
                </section>

                <!-- Wallet bar -->
                <section id="sm-wallet" class="bg-[#012716] rounded-2xl p-3 flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/sm-wallet-icon.png') }}" alt="" class="w-[29px] h-[29px] shrink-0" aria-hidden="true">
                    <div class="min-w-0">
                        <p class="text-[10.5px] text-[#B9CBBE] leading-tight">{{ $isFr ? 'Solde disponible' : 'Available balance' }}</p>
                        <p class="text-[16px] font-bold text-white leading-tight whitespace-nowrap">156 500 <span class="text-[9.5px]">FCFA</span></p>
                    </div>
                    <a href="{{ route('support.index') }}" class="ml-auto shrink-0 bg-[#FEBF00] text-[#3A2A03] text-[11px] font-bold px-3 py-2 rounded-lg whitespace-nowrap">
                        {{ $isFr ? 'Retirer mes gains' : 'Withdraw earnings' }}
                    </a>
                    <a href="{{ route('notifications.index') }}" class="shrink-0 w-[28px] h-[28px] rounded-full border border-white/40 flex items-center justify-center text-white" aria-label="{{ $isFr ? 'Historique' : 'History' }}">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </section>

                @else
                <!-- No business yet (mobile) -->
                <div class="bg-white rounded-2xl border-2 border-dashed border-[#E3E3E0] p-8 text-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="store" class="w-6 h-6 text-amber-500"></i>
                    </div>
                    <h2 class="text-[16px] font-bold text-[#1B1B18] mb-2">{{ $isFr ? 'Créez votre vitrine' : 'Create your storefront' }}</h2>
                    <p class="text-[12.5px] text-[#6F6B60] mb-5">
                        {{ $isFr ? 'Présentez vos produits à des acheteurs du monde entier. Gratuit, rapide, efficace.' : 'Showcase your products to buyers worldwide. Free, fast, effective.' }}
                    </p>
                    <a href="{{ route('business.create') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#052912] text-white font-semibold rounded-xl text-[13px]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Créer mon entreprise' : 'Create my business' }}
                    </a>
                </div>
                @endif
            </div>

            <!-- Mobile bottom nav -->
            <nav class="fixed bottom-0 inset-x-0 z-30 bg-white border-t border-[#EDEDEB] flex items-stretch h-[64px]" style="padding-bottom: env(safe-area-inset-bottom)">
                <a href="{{ route('dashboard.entrepreneur') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#0B5B31]">
                    <i data-lucide="house" class="w-[22px] h-[22px]"></i>
                    <span class="text-[10px] font-semibold">{{ $isFr ? 'Accueil' : 'Home' }}</span>
                    <span class="w-6 h-[3px] rounded-full bg-[#0B5B31]"></span>
                </a>
                <a href="{{ $ownStoreUrl }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A]">
                    <i data-lucide="package" class="w-[22px] h-[22px]"></i>
                    <span class="text-[10px]">{{ $isFr ? 'Produits' : 'Products' }}</span>
                </a>
                <a href="{{ route('products.web-create') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A]">
                    <span class="-mt-7 w-[52px] h-[52px] rounded-full bg-[#0B4D27] shadow-lg flex items-center justify-center text-white">
                        <i data-lucide="plus" class="w-6 h-6"></i>
                    </span>
                    <span class="text-[10px] mt-0.5">{{ $isFr ? 'Ajouter' : 'Add' }}</span>
                </a>
                <a href="{{ route('messages.inbox') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A]">
                    <span class="relative">
                        <i data-lucide="clipboard-list" class="w-[22px] h-[22px]"></i>
                        <span class="absolute -top-1.5 -right-3 bg-[#D40C0F] text-white text-[8.5px] font-bold min-w-[17px] h-[15px] px-0.5 rounded-full flex items-center justify-center">28</span>
                    </span>
                    <span class="text-[10px]">{{ $isFr ? 'Commandes' : 'Orders' }}</span>
                </a>
                <button type="button" class="sm-menu-btn flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A]">
                    <i data-lucide="circle-user" class="w-[22px] h-[22px]"></i>
                    <span class="text-[10px]">Menu</span>
                </button>
            </nav>
        </div>

        <!-- Content (desktop) -->
        <main class="hidden lg:block px-7 py-6">

            @if($business)

            <!-- Welcome row -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-[24px] font-bold text-[#1B1B18]">{{ $isFr ? 'Bienvenue' : 'Welcome' }}, {{ $shopName }} ! 👋</h1>
                    <p class="mt-1 text-[13px] text-[#6F6B60]">{{ $isFr ? 'Voici un aperçu complet de votre boutique aujourd\'hui.' : 'Here is a complete overview of your shop today.' }}</p>
                </div>
                <span class="flex items-center gap-2.5 bg-white border border-[#E7E7E5] rounded-xl px-4 h-[46px] text-[13.5px] shadow-sm">
                    <i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#1B1B18]"></i>
                    <span class="text-[#6F6B60]">{{ $isFr ? 'Période' : 'Period' }} :</span>
                    <span class="font-semibold text-[#1B1B18]">{{ $isFr ? '30 derniers jours' : 'Last 30 days' }}</span>
                </span>
            </div>

            <!-- KPI cards -->
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5 gap-4">
                @foreach($kpis as [$kpiIcon, $kpiSpark, $kpiLabel, $kpiValue, $kpiUnit, $kpiDelta, $kpiTint, $kpiBorder])
                <div class="rounded-2xl border {{ $kpiBorder }} bg-gradient-to-b from-white {{ $kpiTint }} p-4 pb-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[12.5px] text-[#55524A]">{{ $kpiLabel }}</p>
                            <p class="mt-1.5 text-[27px] font-bold text-[#1B1B18] leading-none">
                                {{ $kpiValue }}@if($kpiUnit)<span class="text-[13px] font-bold ml-1">{{ $kpiUnit }}</span>@endif
                            </p>
                            @if($kpiDelta)
                            <p class="mt-2 text-[11.5px]"><span class="text-[#157A43] font-semibold">{{ $kpiDelta }}</span> <span class="text-[#8A857A]">{{ $isFr ? 'vs mois dernier' : 'vs last month' }}</span></p>
                            @else
                            <p class="mt-2 text-[11.5px] font-bold text-[#157A43]">{{ $isFr ? 'Excellent' : 'Excellent' }}</p>
                            @endif
                        </div>
                        <img src="{{ asset('images/landing/' . $kpiIcon) }}" alt="" class="w-[46px] h-[46px] shrink-0" aria-hidden="true">
                    </div>
                    <img src="{{ asset('images/landing/' . $kpiSpark) }}" alt="" class="mt-1 w-full" aria-hidden="true">
                </div>
                @endforeach
            </div>

            <!-- Row 2: orders / activity / wallet -->
            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1.62fr_1.1fr_0.98fr] gap-4 items-start">

                <!-- Commandes récentes -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm">
                    <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-[#F4F4F2]">
                        <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Commandes récentes' : 'Recent orders' }}</h2>
                        <a href="{{ route('messages.inbox') }}" class="flex items-center gap-1.5 text-[12.5px] font-medium text-[#55524A] hover:text-leaf">
                            {{ $isFr ? 'Voir toutes' : 'View all' }}
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                    <div class="divide-y divide-[#F4F4F2]">
                        @foreach($designOrders as [$ordImg, $ordName, $ordRef, $ordStatus, $ordDate, $ordAmount])
                        <div class="flex items-center gap-3.5 px-5 py-3.5">
                            <img src="{{ asset('images/landing/' . $ordImg) }}" alt="" class="w-[51px] h-[51px] rounded-lg object-cover shrink-0">
                            <div class="min-w-0">
                                <p class="text-[13.5px] font-semibold text-[#1B1B18] truncate">{{ $ordName }}</p>
                                <p class="mt-0.5 text-[12px] text-[#8A857A]">{{ $ordRef }}</p>
                            </div>
                            <div class="ml-auto text-right shrink-0 hidden sm:block">
                                <span class="inline-block text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $orderStatus[$ordStatus][1] }}">{{ $orderStatus[$ordStatus][0] }}</span>
                                <p class="mt-1 text-[11.5px] text-[#8A857A]">{{ $ordDate }}</p>
                            </div>
                            <p class="text-[13.5px] font-bold text-[#1B1B18] shrink-0 w-[92px] text-right">{{ $ordAmount }}</p>
                            <a href="{{ route('messages.inbox') }}" class="text-[#8A857A] hover:text-[#1B1B18] shrink-0" aria-label="{{ $isFr ? 'Options' : 'Options' }}">
                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </section>

                <!-- Activité en temps réel -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Activité en temps réel' : 'Real-time activity' }}</h2>
                    <div class="mt-4 space-y-0">
                        @foreach($activity as $actIdx => [$actIcon, $actColor, $actTime, $actText])
                        <div class="relative flex gap-3.5 pb-5">
                            @if($actIdx < count($activity) - 1)<span class="absolute left-[13px] top-7 bottom-0 w-px bg-[#EDEDEB]"></span>@endif
                            <span class="w-[27px] h-[27px] shrink-0 rounded-full border flex items-center justify-center bg-white" style="border-color:{{ $actColor }}40">
                                <i data-lucide="{{ $actIcon }}" class="w-3.5 h-3.5" style="color:{{ $actColor }}"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11.5px] text-[#8A857A]">{{ $actTime }}</p>
                                <p class="mt-0.5 text-[12.5px] text-[#1B1B18] leading-snug whitespace-pre-line">{{ $actText }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('notifications.index') }}" class="flex items-center justify-center gap-2 py-2.5 border-t border-[#F4F4F2] text-[12.5px] font-semibold text-[#1B1B18] hover:text-leaf">
                        {{ $isFr ? 'Voir toute l\'activité' : 'View all activity' }}
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </section>

                <!-- Mon portefeuille -->
                <section id="portefeuille" class="relative rounded-2xl overflow-hidden bg-[#07271A] p-6">
                    <div class="absolute inset-0 opacity-20 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
                    <div class="relative">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-[15.5px] font-bold text-white">{{ $isFr ? 'Mon portefeuille' : 'My wallet' }}</h2>
                                <p class="mt-4 text-[12.5px] text-[#B9CBBE]">{{ $isFr ? 'Solde disponible' : 'Available balance' }}</p>
                                <p class="mt-1 text-[26px] font-bold text-white leading-none">156 500 <span class="text-[13px]">FCFA</span></p>
                            </div>
                            <img src="{{ asset('images/landing/sd-wallet-icon.png') }}" alt="" class="w-[64px] shrink-0" aria-hidden="true">
                        </div>
                        <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between text-[12.5px]">
                            <span class="text-[#B9CBBE]">{{ $isFr ? 'En attente de paiement' : 'Pending payment' }}</span>
                            <span class="font-bold text-white">45 200 FCFA</span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-[12.5px]">
                            <span class="text-[#B9CBBE]">{{ $isFr ? 'Total retiré' : 'Total withdrawn' }}</span>
                            <span class="font-bold text-white">890 750 FCFA</span>
                        </div>
                        <a href="{{ route('support.index') }}" class="mt-6 block w-full bg-[#FEBF00] hover:bg-[#EDB100] text-[#3A2A03] text-[13.5px] font-bold text-center py-3 rounded-lg transition-colors">
                            {{ $isFr ? 'Retirer mes gains' : 'Withdraw my earnings' }}
                        </a>
                        <a href="{{ route('notifications.index') }}" class="mt-3 block w-full border border-white/40 hover:bg-white/10 text-white text-[13.5px] font-semibold text-center py-3 rounded-lg transition-colors">
                            {{ $isFr ? 'Historique des transactions' : 'Transaction history' }}
                        </a>
                    </div>
                </section>
            </div>

            <!-- Row 3: performance chart / popular products / region stats -->
            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1.62fr_1.1fr_0.98fr] gap-4 items-start">

                <!-- Aperçu des performances -->
                <section id="performances" class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Aperçu des performances' : 'Performance overview' }}</h2>
                        <span class="flex items-center gap-2 border border-[#E7E7E5] rounded-lg px-3 py-1.5 text-[12px] text-[#55524A]">
                            {{ $isFr ? '30 derniers jours' : 'Last 30 days' }}
                        </span>
                    </div>
                    <div class="mt-4 flex gap-5">
                        <div class="shrink-0 space-y-5 pt-3">
                            <div>
                                <p class="flex items-center gap-2 text-[12px] text-[#55524A]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>{{ $isFr ? 'Ventes (FCFA)' : 'Sales (FCFA)' }}</p>
                                <p class="mt-1 pl-4 text-[16px] font-bold text-[#1B1B18]">356 000</p>
                            </div>
                            <div>
                                <p class="flex items-center gap-2 text-[12px] text-[#55524A]"><span class="w-2 h-2 rounded-full bg-[#F5A623]"></span>{{ $isFr ? 'Commandes' : 'Orders' }}</p>
                                <p class="mt-1 pl-4 text-[16px] font-bold text-[#1B1B18]">28</p>
                            </div>
                            <div>
                                <p class="flex items-center gap-2 text-[12px] text-[#55524A]"><span class="w-2 h-2 rounded-full bg-[#3B72E8]"></span>{{ $isFr ? 'Visites' : 'Visits' }}</p>
                                <p class="mt-1 pl-4 text-[16px] font-bold text-[#1B1B18]">1 245</p>
                            </div>
                        </div>
                        <img src="{{ asset('images/landing/sd-chart.png') }}" alt="" class="flex-1 min-w-0 self-end" aria-hidden="true">
                    </div>
                </section>

                <!-- Produits les plus populaires -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Produits les plus populaires' : 'Most popular products' }}</h2>
                        <a href="{{ $ownStoreUrl }}" class="flex items-center gap-1.5 text-[12.5px] font-medium text-[#55524A] hover:text-leaf whitespace-nowrap">
                            {{ $isFr ? 'Voir tous' : 'View all' }}
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                    <div class="mt-3">
                        @if($realPopular->count())
                            @foreach($realPopular as $popIdx => $popProd)
                            <a href="{{ route('products.web-edit', ['slug' => $popProd->slug]) }}" class="flex items-center gap-3 py-2.5 {{ $popIdx > 0 ? 'border-t border-[#F4F4F2]' : '' }} hover:bg-[#FAFAF8] transition-colors">
                                <span class="w-[22px] h-[22px] shrink-0 rounded-full text-white text-[11px] font-bold flex items-center justify-center" style="background:{{ $rankColors[$popIdx] }}">{{ $popIdx + 1 }}</span>
                                <img src="{{ asset('images/landing/' . $designPopular[$popIdx % 5][0]) }}" alt="" class="w-[31px] h-[31px] rounded-md object-cover shrink-0">
                                <div class="min-w-0">
                                    <p class="text-[12.5px] font-semibold text-[#1B1B18] truncate">{{ $isFr ? $popProd->name_fr : ($popProd->name_en ?? $popProd->name_fr) }}</p>
                                    <p class="text-[11px] text-[#8A857A]">{{ $popProd->views_count ?? 0 }} {{ $isFr ? 'vues' : 'views' }}</p>
                                </div>
                                <span class="ml-auto text-[11px] font-bold text-[#157A43] shrink-0">{{ $popPcts[$popIdx % 5] }}</span>
                            </a>
                            @endforeach
                        @else
                            @foreach($designPopular as $popIdx => [$popImg, $popName, $popViews])
                            <a href="{{ route('products.index', ['lang' => $lang]) }}" class="flex items-center gap-3 py-2.5 {{ $popIdx > 0 ? 'border-t border-[#F4F4F2]' : '' }} hover:bg-[#FAFAF8] transition-colors">
                                <span class="w-[22px] h-[22px] shrink-0 rounded-full text-white text-[11px] font-bold flex items-center justify-center" style="background:{{ $rankColors[$popIdx] }}">{{ $popIdx + 1 }}</span>
                                <img src="{{ asset('images/landing/' . $popImg) }}" alt="" class="w-[31px] h-[31px] rounded-md object-cover shrink-0">
                                <div class="min-w-0">
                                    <p class="text-[12.5px] font-semibold text-[#1B1B18] truncate">{{ $popName }}</p>
                                    <p class="text-[11px] text-[#8A857A]">{{ $popViews }} {{ $isFr ? 'vues' : 'views' }}</p>
                                </div>
                                <span class="ml-auto text-[11px] font-bold text-[#157A43] shrink-0">{{ $popPcts[$popIdx] }}</span>
                            </a>
                            @endforeach
                        @endif
                    </div>
                </section>

                <!-- Statistiques par région -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-4">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Statistiques par région' : 'Statistics by region' }}</h2>
                    <div class="mt-4 flex gap-3">
                        <div class="flex-1 space-y-3.5 pt-1">
                            @foreach($regions as [$regName, $regPct, $regColor])
                            <div class="flex items-center gap-2.5 text-[12px]">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background:{{ $regColor }}"></span>
                                <span class="text-[#55524A]">{{ $regName }}</span>
                                <span class="ml-auto font-bold text-[#1B1B18]">{{ $regPct }}</span>
                            </div>
                            @endforeach
                        </div>
                        <img src="{{ asset('images/landing/sd-region-map.png') }}" alt="" class="w-[132px] shrink-0 self-start" aria-hidden="true">
                    </div>
                </section>
            </div>

            <!-- Row 4: quick actions / events promo -->
            <div class="mt-5 grid grid-cols-1 xl:grid-cols-[2.85fr_1fr] gap-4 items-stretch">

                <!-- Actions rapides -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-5">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-3.5 grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-2.5">
                        @foreach($quickActions as [$qaIcon, $qaLabel, $qaHref, $qaBadge])
                        <a href="{{ $qaHref }}" class="relative border border-[#EEEEEC] rounded-xl px-2 py-3.5 text-center hover:border-dashgold hover:shadow-sm transition-all">
                            <span class="relative inline-block">
                                <i data-lucide="{{ $qaIcon }}" class="w-6 h-6 text-[#1B4332]" style="stroke-width:1.6"></i>
                                @if($qaBadge)
                                <span class="absolute -top-2 -right-3 bg-[#DC0508] text-white text-[9px] font-bold min-w-[16px] h-[16px] px-1 rounded-full flex items-center justify-center">{{ $qaBadge }}</span>
                                @endif
                            </span>
                            <span class="mt-2 block text-[11px] text-[#55524A] leading-tight">{{ $qaLabel }}</span>
                        </a>
                        @endforeach
                    </div>
                </section>

                <!-- Annoncez vos événements -->
                <section class="relative rounded-2xl overflow-hidden bg-[#031E11] p-5">
                    <img src="{{ asset('images/landing/sd-event-art.png') }}" alt="" class="absolute right-0 inset-y-0 h-full pointer-events-none select-none" aria-hidden="true">
                    <div class="relative pr-[96px]">
                        <h2 class="text-[14.5px] font-bold text-white leading-snug">{{ $isFr ? 'Annoncez vos événements' : 'Announce your events' }}</h2>
                        <p class="mt-1.5 text-[11.5px] text-[#C6D4C9] leading-relaxed">
                            {{ $isFr ? 'Augmentez votre visibilité en promouvant vos événements artisanaux.' : 'Increase your visibility by promoting your craft events.' }}
                        </p>
                        <a href="{{ route('events.index') }}" class="mt-3.5 inline-block bg-[#FEBF00] hover:bg-[#EDB100] text-[#3A2A03] text-[12px] font-bold px-4 py-2 rounded-lg transition-colors">
                            {{ $isFr ? 'Créer un événement' : 'Create an event' }}
                        </a>
                    </div>
                </section>
            </div>

            @else
            <!-- No business yet -->
            <div class="max-w-xl mx-auto mt-10 bg-white rounded-2xl border-2 border-dashed border-[#E3E3E0] p-10 text-center">
                <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="store" class="w-7 h-7 text-amber-500"></i>
                </div>
                <h2 class="text-[18px] font-bold text-[#1B1B18] mb-2">{{ $isFr ? 'Créez votre vitrine' : 'Create your storefront' }}</h2>
                <p class="text-[13.5px] text-[#6F6B60] mb-6 max-w-sm mx-auto">
                    {{ $isFr ? 'Présentez vos produits à des acheteurs du monde entier. Gratuit, rapide, efficace.' : 'Showcase your products to buyers worldwide. Free, fast, effective.' }}
                </p>
                <a href="{{ route('business.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#052912] text-white font-semibold rounded-xl hover:bg-leaf transition-colors text-[14px]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    {{ $isFr ? 'Créer mon entreprise' : 'Create my business' }}
                </a>
            </div>
            @endif
        </main>
    </div>
</div>

<script>
    lucide.createIcons();

    // Mobile sidebar (header hamburger, bottom-nav Menu tab, desktop fallback button)
    const sb = document.getElementById('dash-sidebar');
    const bd = document.getElementById('dash-backdrop');
    const toggleSidebar = () => {
        sb.classList.toggle('open');
        bd.classList.toggle('hidden');
    };
    document.querySelectorAll('.sm-menu-btn, #dash-menu-btn').forEach(b => b.addEventListener('click', toggleSidebar));
    bd.addEventListener('click', () => {
        sb.classList.remove('open');
        bd.classList.add('hidden');
    });
</script>
</body>
</html>
