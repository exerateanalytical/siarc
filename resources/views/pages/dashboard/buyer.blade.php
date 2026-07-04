@php
    $isFr = $lang === 'fr';
    $userName = $siacUser['name'] ?? 'Utilisateur';

    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $memberSince = null;
    if ($buyerSince ?? null) {
        $mDate = \Illuminate\Support\Carbon::parse($buyerSince);
        $memberSince = $isFr ? ($frMonths[$mDate->month] . ' ' . $mDate->year) : $mDate->format('F Y');
    }

    $msgCount = $conversations->count();

    $bmKpis = [
        ['bm-kpi-1.png', '28', null, $isFr ? 'Commandes' : 'Orders', '+18% ↗', 'bg-[#F4F8F1] border-[#E4EEDF]'],
        ['bm-kpi-2.png', '356 000', 'FCFA', $isFr ? 'Chiffre d\'affaires' : 'Revenue', '+24% ↗', 'bg-[#FBF6EC] border-[#F1E7D2]'],
        ['bm-kpi-3.png', '1 245', null, $isFr ? 'Vues de la boutique' : 'Shop views', '+12% ↗', 'bg-[#F4F8FC] border-[#E0EAF4]'],
        ['bm-kpi-4.png', '96%', null, $isFr ? 'Taux de satisfaction' : 'Satisfaction rate', null, 'bg-[#FDF4F4] border-[#F5E0E0]'],
    ];

    $bmOrders = [
        ['bm-order-1.png', $isFr ? 'Masque Bamileké Royal' : 'Royal Bamileke Mask',           '#GVN-2025-0012', 'delivered', '05 Mai 2025', '75 000 FCFA'],
        ['bm-order-2.png', $isFr ? 'Panier Tressé Traditionnel' : 'Traditional Woven Basket', '#GVN-2025-0011', 'pending',   '04 Mai 2025', '28 000 FCFA'],
        ['bm-order-3.png', $isFr ? 'Collier Perles Recyclées' : 'Recycled Bead Necklace',     '#GVN-2025-0010', 'shipped',   '02 Mai 2025', '15 500 FCFA'],
        ['bm-order-4.png', $isFr ? 'Statue Traditionnelle Sawa' : 'Traditional Sawa Statue',  '#GVN-2025-0009', 'delivered', '01 Mai 2025', '38 000 FCFA'],
    ];
    $bmStatus = [
        'delivered' => ['✓ ' . ($isFr ? 'Livrée' : 'Delivered'), 'bg-[#E9F6EE] text-[#157A43]'],
        'pending'   => ['✓ ' . ($isFr ? 'En cours' : 'In progress'), 'bg-[#FDF7E3] text-[#B07C10]'],
        'shipped'   => ['✓ ' . ($isFr ? 'Expédiée' : 'Shipped'), 'bg-[#EBF1FD] text-[#2E5FD0]'],
    ];

    $bmActions = [
        ['bm-qa-1.png', $isFr ? 'Ajouter produit' : 'Add product',      route('products.web-create'), null],
        ['bm-qa-2.png', $isFr ? 'Gérer produits' : 'Manage products',   route('products.index', ['lang' => $lang]), null],
        ['bm-qa-3.png', $isFr ? 'Commandes' : 'Orders',                 route('messages.inbox'), '3'],
        ['bm-qa-4.png', 'Messages',                                      route('messages.inbox'), (string) $msgCount],
        ['bm-qa-5.png', $isFr ? 'Statistiques' : 'Statistics',          '#apercu', null],
        ['bm-qa-6.png', $isFr ? 'Avis clients' : 'Client reviews',      route('saved.index'), '12'],
        ['bm-qa-7.png', 'Promotions',                                    route('contact'), null],
        ['bm-qa-8.png', $isFr ? 'Mon porte-monnaie' : 'My wallet',      route('profile.show'), null],
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
                        herogr: '#03341B',
                        leaf:   '#164C28',
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
        #buyer-menu { transform: translateX(-100%); }
        #buyer-menu.open { transform: translateX(0); }
    </style>
</head>
<body class="bg-[#F5F5F3] text-[#1B1B18] antialiased">

<!-- Slide-over menu (buyer flows live here) -->
<aside id="buyer-menu" class="fixed inset-y-0 left-0 z-50 w-[300px] bg-[#002714] overflow-y-auto">
    <div class="bg-[#031E12] px-5 py-4 flex items-center gap-3">
        <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[44px] h-[48px] object-contain shrink-0">
        <span class="text-[11.5px] font-bold tracking-[0.02em] text-white uppercase leading-[1.4]">
            {{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}<br>
            {{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}
        </span>
    </div>
    <div class="px-5 pt-5 pb-4 flex items-center gap-3.5 border-b border-white/10">
        <img src="{{ asset('images/landing/bm-avatar.png') }}" alt="" class="w-[56px] h-[56px] rounded-full">
        <div class="min-w-0">
            <p class="text-[15px] font-bold text-white truncate">{{ $userName }}</p>
            @if($memberSince)
            <p class="text-[11px] text-[#C6D4C9]">{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $memberSince }}</p>
            @endif
        </div>
    </div>
    <nav class="py-2">
        @foreach([
            ['house',          $isFr ? 'Accueil' : 'Home',                       route('dashboard.buyer')],
            ['compass',        $isFr ? 'Explorer la galerie' : 'Browse gallery', route('products.index', ['lang' => $lang])],
            ['bookmark',       $isFr ? 'Entreprises sauvegardées' : 'Saved businesses', route('saved.index')],
            ['file-text',      $isFr ? 'Mes Demandes & Devis' : 'My Requests & Quotes', route('quotes.index')],
            ['message-circle', 'Messages',                                        route('messages.inbox')],
            ['bell',           'Notifications',                                   route('notifications.index')],
            ['user',           $isFr ? 'Mon profil' : 'My profile',              route('profile.show')],
            ['shield-check',   $isFr ? 'Sécurité' : 'Security',                  route('security.show')],
            ['circle-help',    $isFr ? 'Aide & Support' : 'Help & Support',      route('support.index')],
        ] as [$mIcon, $mLabel, $mHref])
        <a href="{{ $mHref }}" class="flex items-center gap-3.5 px-5 py-3 text-[14px] text-white hover:bg-white/5">
            <i data-lucide="{{ $mIcon }}" class="w-5 h-5 text-[#A8B8AC]" style="stroke-width:1.7"></i>
            {{ $mLabel }}
        </a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-white/10 pt-2">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3.5 px-5 py-3 text-[14px] text-[#FF8B8B] hover:bg-white/5">
                <i data-lucide="log-out" class="w-5 h-5" style="stroke-width:1.7"></i>
                {{ $isFr ? 'Se déconnecter' : 'Log out' }}
            </button>
        </form>
    </nav>
</aside>
<div id="buyer-backdrop" class="fixed inset-0 z-40 bg-black/50 hidden"></div>

<div class="max-w-[431px] mx-auto min-h-screen bg-[#FEFEFE] shadow-sm">

    <!-- Header -->
    <header class="bg-white px-4 pt-3.5 pb-3 flex items-center gap-3">
        <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[39px] h-[42px] object-contain shrink-0">
        <span class="leading-tight min-w-0">
            <span class="block text-[12px] font-bold tracking-[0.01em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
            <span class="block text-[12px] font-bold tracking-[0.01em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
            <span class="block text-[9.5px] text-[#55524A] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
        </span>
        <a href="{{ route('notifications.index') }}" class="relative ml-auto p-1 shrink-0" aria-label="Notifications">
            <i data-lucide="bell" class="w-[24px] h-[24px] text-[#1B1B18]" style="stroke-width:1.8"></i>
            <span class="absolute -top-1.5 -right-1.5 bg-[#D40C0F] text-white text-[10px] font-bold min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center">3</span>
        </a>
        <button type="button" class="bm-menu-btn p-1 shrink-0" aria-label="Menu">
            <i data-lucide="menu" class="w-[26px] h-[26px] text-[#1B1B18]" style="stroke-width:2.2"></i>
        </button>
    </header>

    <div class="px-4 pb-24 pt-1 space-y-3.5">

        <!-- Hero card -->
        <section class="relative bg-herogr rounded-2xl overflow-hidden">
            <img src="{{ asset('images/landing/bm-hero-flag.png') }}" alt="" class="absolute right-0 inset-y-0 h-full pointer-events-none select-none" aria-hidden="true">
            <div class="absolute inset-0 opacity-15 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative flex items-start gap-3.5 p-4 pb-[52px]">
                <img src="{{ asset('images/landing/bm-avatar.png') }}" alt="" class="w-[58px] h-[58px] rounded-full shrink-0">
                <div class="min-w-0 pt-0.5">
                    <p class="text-[18px] font-bold text-white leading-snug truncate">{{ $isFr ? 'Bonjour' : 'Hello' }}, {{ $userName }} ! 👋</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[13px] font-semibold text-[#F5C33B]">
                        {{ $isFr ? 'Boutique Verifiée' : 'Verified Shop' }}
                        <i data-lucide="badge-check" class="w-4 h-4" style="fill:#F5C33B;color:#03341B"></i>
                    </p>
                    @if($memberSince)
                    <p class="mt-1 text-[11px] text-[#C6D4C9]">{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $memberSince }}</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('business.create') }}" class="absolute right-4 bottom-3.5 inline-flex items-center gap-2 bg-[#0A3D22]/80 border border-white/70 rounded-lg px-3.5 py-[7px] text-[12px] font-semibold text-white">
                <i data-lucide="store" class="w-3.5 h-3.5"></i>
                {{ $isFr ? 'Voir ma boutique' : 'View my shop' }}
            </a>
        </section>

        <!-- Aperçu de votre activité -->
        <section id="apercu" class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Aperçu de votre activité' : 'Your activity overview' }}</h2>
                <span class="flex items-center gap-1 text-[11px] text-[#55524A] whitespace-nowrap">
                    {{ $isFr ? 'Période : 30 derniers jours' : 'Period: last 30 days' }}
                </span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2.5">
                @foreach($bmKpis as [$kIcon, $kVal, $kUnit, $kLabel, $kDelta, $kTint])
                <div class="border rounded-xl p-3 {{ $kTint }}">
                    <div class="flex items-start gap-2.5">
                        <img src="{{ asset('images/landing/' . $kIcon) }}" alt="" class="w-[31px] h-[31px] shrink-0" aria-hidden="true">
                        <div class="min-w-0 flex-1">
                            <p class="text-[16.5px] font-bold text-[#1B1B18] leading-tight">{{ $kVal }}@if($kUnit)<span class="text-[10px] font-bold ml-1">{{ $kUnit }}</span>@endif</p>
                            <div class="flex items-end justify-between gap-1">
                                <span class="text-[11px] text-[#55524A] leading-tight">{{ $kLabel }}</span>
                                @if($kDelta)<span class="text-[10.5px] font-semibold text-[#157A43] whitespace-nowrap">{{ $kDelta }}</span>@endif
                            </div>
                            @unless($kDelta)<p class="text-[11px] font-bold text-[#157A43]">Excellent</p>@endunless
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Commandes récentes -->
        <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Commandes récentes' : 'Recent orders' }}</h2>
                <a href="{{ route('messages.inbox') }}" class="text-[11.5px] font-medium text-[#157A43]">{{ $isFr ? 'Voir toutes' : 'View all' }}</a>
            </div>
            <div class="mt-1 divide-y divide-[#F4F4F2]">
                @foreach($bmOrders as [$oImg, $oName, $oRef, $oStatus, $oDate, $oAmount])
                <a href="{{ route('messages.inbox') }}" class="flex items-center gap-3 py-3">
                    <img src="{{ asset('images/landing/' . $oImg) }}" alt="" class="w-[43px] h-[43px] rounded-lg object-cover shrink-0">
                    <div class="min-w-0 flex-1">
                        <p class="text-[12.5px] font-bold text-[#1B1B18] truncate">{{ $oName }}</p>
                        <p class="mt-0.5 text-[11px] text-[#8A857A]">{{ $oRef }}</p>
                    </div>
                    <div class="shrink-0 text-left w-[86px]">
                        <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md {{ $bmStatus[$oStatus][1] }}">{{ $bmStatus[$oStatus][0] }}</span>
                        <p class="mt-1 text-[10.5px] text-[#8A857A]">{{ $oDate }}</p>
                    </div>
                    <p class="text-[12.5px] font-bold text-[#1B1B18] shrink-0 whitespace-nowrap">{{ $oAmount }}</p>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-[#B4B0A6] shrink-0"></i>
                </a>
                @endforeach
            </div>
        </section>

        <!-- Actions rapides -->
        <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
            <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
            <div class="mt-3 grid grid-cols-4 gap-2">
                @foreach($bmActions as [$qIcon, $qLabel, $qHref, $qBadge])
                <a href="{{ $qHref }}" class="relative bg-[#F4F6F4] border border-[#EBEEEA] rounded-xl px-1.5 pt-3.5 pb-2.5 text-center">
                    @if($qBadge)
                    <span class="absolute top-1.5 right-1.5 bg-[#D40C0F] text-white text-[9px] font-bold min-w-[17px] h-[17px] px-1 rounded-full flex items-center justify-center">{{ $qBadge }}</span>
                    @endif
                    <img src="{{ asset('images/landing/' . $qIcon) }}" alt="" class="w-[24px] h-[24px] mx-auto" aria-hidden="true">
                    <span class="mt-2 block text-[10px] text-[#3B382F] leading-tight">{{ $qLabel }}</span>
                </a>
                @endforeach
            </div>
        </section>

        <!-- Promo -->
        <section class="relative bg-white border border-[#F0F0EE] rounded-2xl overflow-hidden">
            <img src="{{ asset('images/landing/bm-promo-art.png') }}" alt="" class="absolute right-0 bottom-0 h-full pointer-events-none select-none" aria-hidden="true">
            <div class="relative p-4 pr-[84px]">
                <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Développez votre boutique' : 'Grow your shop' }}</h2>
                <p class="mt-1 text-[11.5px] text-[#55524A] leading-relaxed max-w-[240px]">
                    {{ $isFr ? 'Boostez vos ventes avec nos outils marketing et attirez plus de clients !' : 'Boost your sales with our marketing tools and attract more clients!' }}
                </p>
                <a href="{{ route('contact') }}" class="mt-3 inline-block bg-[#033016] text-white text-[11.5px] font-bold px-4 py-2.5 rounded-lg">
                    {{ $isFr ? 'Découvrir nos offres' : 'Discover our offers' }}
                </a>
            </div>
        </section>
    </div>

    <!-- Bottom nav -->
    <nav class="fixed bottom-0 inset-x-0 z-30 bg-white border-t border-[#EDEDEB] max-w-[431px] mx-auto flex items-stretch h-[64px]" style="padding-bottom: env(safe-area-inset-bottom)">
        <a href="{{ route('dashboard.buyer') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#0B5B31]">
            <i data-lucide="house" class="w-[22px] h-[22px]"></i>
            <span class="text-[10px] font-semibold">{{ $isFr ? 'Accueil' : 'Home' }}</span>
        </a>
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A]">
            <i data-lucide="shopping-bag" class="w-[22px] h-[22px]"></i>
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
                <span class="absolute -top-1.5 -right-2.5 bg-[#D40C0F] text-white text-[8.5px] font-bold min-w-[16px] h-[15px] px-0.5 rounded-full flex items-center justify-center">3</span>
            </span>
            <span class="text-[10px]">{{ $isFr ? 'Commandes' : 'Orders' }}</span>
        </a>
        <button type="button" class="bm-menu-btn flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A]">
            <i data-lucide="menu" class="w-[22px] h-[22px]"></i>
            <span class="text-[10px]">Menu</span>
        </button>
    </nav>
</div>

<script>
    lucide.createIcons();

    const bm = document.getElementById('buyer-menu');
    const bmBd = document.getElementById('buyer-backdrop');
    document.querySelectorAll('.bm-menu-btn').forEach(b => b.addEventListener('click', () => {
        bm.classList.toggle('open');
        bmBd.classList.toggle('hidden');
    }));
    bmBd.addEventListener('click', () => {
        bm.classList.remove('open');
        bmBd.classList.add('hidden');
    });
</script>
</body>
</html>
