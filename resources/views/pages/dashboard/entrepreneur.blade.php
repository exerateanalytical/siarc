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

    // Navigation lives in pages.partials.dashboard-sidebar — the single canonical
    // sidebar shared by every dashboard page.

    // [icon, spark, label, value, unit, delta, bgGradient, border] — delta omitted where not computable
    $kpis = [
        ['sd-kpi-icon-1.png', 'sd-kpi-spark-1.png', $isFr ? "Chiffre d'affaires" : 'Revenue', number_format($revenueTotal, 0, ',', ' '), 'FCFA', null, 'to-[#F1F8EF]', 'border-[#E3EFE2]'],
        ['sd-kpi-icon-2.png', 'sd-kpi-spark-2.png', $isFr ? 'Commandes' : 'Orders', (string) $ordersCount, null, null, 'to-[#FEF7EC]', 'border-[#F4E8D3]'],
        ['sd-kpi-icon-3.png', 'sd-kpi-spark-3.png', $isFr ? 'Visites de la boutique' : 'Shop visits', number_format((int) ($business->views_count ?? 0), 0, ',', ' '), null, null, 'to-[#F2F6FE]', 'border-[#E1E9F8]'],
        ['sd-kpi-icon-5.png', 'sd-kpi-spark-5.png', $isFr ? 'Avis positifs' : 'Positive reviews', $positiveReviewsPct !== null ? $positiveReviewsPct . '%' : '—', null, null, 'to-[#F1F8EF]', 'border-[#E3EFE2]'],
    ];

    // Real purchase orders for this business
    $orderStatus = [
        'pending'   => ['✓ ' . ($isFr ? 'En cours' : 'In progress'), 'bg-[#FDF7E3] text-[#B07C10]'],
        'confirmed' => ['✓ ' . ($isFr ? 'Confirmée' : 'Confirmed'), 'bg-[#EBF1FD] text-[#2E5FD0]'],
        'delivered' => ['✓ ' . ($isFr ? 'Livrée' : 'Delivered'), 'bg-[#E9F6EE] text-[#157A43]'],
        'cancelled' => ['✓ ' . ($isFr ? 'Annulée' : 'Cancelled'), 'bg-[#FDE8E8] text-[#DC2626]'],
    ];
    $realOrders = ($bizPurchaseOrders ?? collect())->take(4);

    // Real activity feed, merged from messages, RFQ status changes, and reviews
    $activity = collect();
    foreach (($recentMessages ?? collect()) as $m) {
        $activity->push(['mail', '#157A43', $m->created_at, ($isFr ? 'Nouveau message' : 'New message') . "\n" . \Illuminate\Support\Str::limit($m->body, 50)]);
    }
    foreach (($bizPurchaseOrders ?? collect())->take(5) as $po) {
        $activity->push(['shopping-cart', '#157A43', $po->created_at, ($isFr ? 'Nouvelle commande ' : 'New order ') . $po->reference . "\n" . $po->title]);
    }
    $activity = $activity->sortByDesc(fn ($a) => $a[2])->take(5)->map(function ($a) {
        $a[2] = \Illuminate\Support\Carbon::parse($a[2])->diffForHumans(null, true, false, 1);
        return $a;
    })->values()->all();

    // Popular products: real products only
    $rankColors = ['#F5A623', '#0E3D22', '#2E7D4F', '#9AA39D', '#9AA39D'];
    $realPopular = collect($products)->sortByDesc(fn ($p) => $p->views_count ?? 0)->take(5)->values();

    $quickActions = [
        ['plus-square',    $isFr ? 'Ajouter un produit' : 'Add a product',        route('products.web-create'), null],
        ['package',        $isFr ? 'Gérer produits' : 'Manage products',          route('products.web-index'), (string) count($products)],
        ['calendar-plus',  $isFr ? 'Ajouter un événement' : 'Add an event',       route('events.index'), null],
        ['clipboard-list', $isFr ? 'Voir commandes' : 'View orders',              route('orders.index'), (string) $ordersCount],
        ['message-circle', 'Messages',                                             route('messages.inbox'), (string) $messageCount],
        ['badge-check',    $isFr ? 'Vérification' : 'Verification',               route('verification.show'), null],
        ['building-2',     $isFr ? 'Mon entreprise' : 'My business',              route('business.edit'), null],
        ['store',          $isFr ? 'Voir ma boutique' : 'View my shop',           $ownStoreUrl, null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Tableau de bord — Artisan Hub 237' : 'Dashboard — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Canonical heritage palette (shared with the public site,
                        // quotes.blade.php and layouts/dashboard.blade.php)
                        sidegreen: '#02301B',
                        sideband:  '#0B3D28',
                        siderow:   '#14532D',
                        dashgold:  '#E5A82E',
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
        {{-- Sidebar slide-over CSS now lives in pages.partials.dashboard-sidebar --}}
    </style>
</head>
<body class="bg-[#F8F6F2] text-[#1D1B16] antialiased">

<div class="flex min-h-screen">

    <!-- Sidebar -->
    {{-- Canonical dashboard sidebar — identical on every dashboard page --}}
    @include("pages.partials.dashboard-sidebar", ["sidebarId" => "dash-sidebar", "sideBadges" => $sideBadges ?? []])

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
                        @if(($notificationCount ?? 0) > 0)
                        <span class="absolute -top-1 -right-1.5 bg-[#DC0508] text-white text-[10px] font-bold min-w-[17px] h-[17px] px-1 rounded-full flex items-center justify-center">{{ min(99, $notificationCount) }}</span>
                        @endif
                    </a>
                    <a href="{{ route('messages.inbox') }}" class="relative p-1" aria-label="Messages">
                        <i data-lucide="mail" class="w-[22px] h-[22px] text-[#1B1B18]"></i>
                        @if(($messageCount ?? 0) > 0)
                        <span class="absolute -top-1 -right-1.5 bg-[#DC0508] text-white text-[10px] font-bold min-w-[17px] h-[17px] px-1 rounded-full flex items-center justify-center">{{ min(99, $messageCount) }}</span>
                        @endif
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
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-[13px] text-[#262521] hover:bg-[#F8F6F2]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                            <a href="{{ route('membership.certificate') }}" class="block px-4 py-2 text-[13px] text-[#262521] hover:bg-[#F8F6F2]">{{ $isFr ? 'Mon certificat d\'adhésion' : 'My membership certificate' }}</a>
                            <a href="{{ route('security.show') }}" class="block px-4 py-2 text-[13px] text-[#262521] hover:bg-[#F8F6F2]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-[13px] text-[#B42025] hover:bg-[#FDE8E8]">{{ $isFr ? 'Se déconnecter' : 'Log out' }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ══════════════ Mobile dashboard (seller mobile dashboard.png) ══════════════ -->
        @php
            // Mobile mirrors the desktop figures — same queries, same truth. The
            // design fixtures that used to live here (356K revenue, 28 orders,
            // a fake activity feed) shipped to every seller on a phone.
            $visits = number_format((int) ($business->views_count ?? 0), 0, ',', ' ');
            $smKpis = [
                ['sm-kpi-1.png', number_format($revenueTotal, 0, ',', ' '), 'FCFA', $isFr ? 'Revenus' : 'Revenue', route('orders.index')],
                ['sm-kpi-2.png', (string) $ordersCount, null, $isFr ? 'Commandes' : 'Orders',                      route('orders.index')],
                ['sm-kpi-3.png', (string) $pipeline['received'], null, $isFr ? 'Demandes' : 'Requests',            route('dashboard.quotes')],
                ['sm-kpi-4.png', (string) $pipeline['pending'], null, $isFr ? 'Devis en attente' : 'Pending quotes', route('dashboard.quotes')],
                ['sm-kpi-5.png', (string) ($unreadMessages ?? 0), null, 'Messages',                                route('messages.inbox')],
                ['sm-kpi-6.png', $visits, null, $isFr ? 'Visites' : 'Visits',                                      $ownStoreUrl],
            ];
            $smPipeline = [
                ['sm-pipe-1.png', (string) $pipeline['received'],    $isFr ? "Demandes\nreçues" : "Requests\nreceived"],
                ['sm-pipe-2.png', (string) $pipeline['pending'],     $isFr ? 'À traiter' : 'To handle'],
                ['sm-pipe-3.png', (string) $pipeline['quoted'],      $isFr ? 'Devis envoyés' : 'Quotes sent'],
                ['sm-pipe-4.png', (string) $pipeline['negotiation'], $isFr ? 'En négociation' : 'Negotiating'],
                ['sm-pipe-5.png', (string) $pipeline['accepted'],    $isFr ? 'Acceptés' : 'Accepted'],
            ];
            // Same merged feed the desktop column renders, re-shaped for the
            // mobile card. Empty when nothing has happened yet.
            $smActivity = collect($activity)->map(fn ($a) => [
                $a[0], str_replace("\n", ' — ', $a[3]),
                ($isFr ? 'Il y a ' : '') . $a[2] . ($isFr ? '' : ' ago'),
                $a[1],
            ])->all();
            $smActions = [
                ['sm-qa-1.png', $isFr ? "Ajouter\nun produit" : "Add\na product",          route('products.web-create'), null],
                ['sm-qa-2.png', $isFr ? "Mes\nproduits" : "My\nproducts",                  route('products.web-index'), (string) $productCount],
                ['sm-qa-3.png', $isFr ? "Répondre\naux messages" : "Reply\nto messages",   route('messages.inbox'), (string) ($unreadMessages ?? 0)],
                ['sm-qa-4.png', $isFr ? "Voir\ncommandes" : "View\norders",                route('orders.index'), (string) $ordersCount],
                ['sm-qa-5.png', $isFr ? "Créer\névénement" : "Create\nan event",           route('events.index'), null],
                ['sm-qa-6.png', $isFr ? "Ma\nboutique" : "My\nshop",                       $ownStoreUrl, null],
            ];
            $smProducts = collect($realPopular)->take(4)->map(fn ($p) => [
                $p, $isFr ? $p->name_fr : ($p->name_en ?? $p->name_fr), (string) ((int) ($p->views_count ?? 0)),
            ])->all();
        @endphp
        <div class="lg:hidden">
            <!-- Mobile header -->
            <header class="bg-white px-4 pt-3.5 pb-3 flex items-center gap-3">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[39px] h-[42px] object-contain shrink-0">
                <span class="leading-tight min-w-0">
                    <span class="block text-[12px] font-bold tracking-[0.01em] text-[#14532D] uppercase whitespace-nowrap">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</span>
                    <span class="block text-[12px] font-bold tracking-[0.01em] text-[#14532D] uppercase whitespace-nowrap">{{ $isFr ? 'Marketplace des artisans' : 'Artisan Marketplace' }}</span>
                    <span class="block text-[9.5px] text-[#2E7D4F] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                </span>
                <a href="{{ route('notifications.index') }}" class="relative ml-auto p-1 shrink-0" aria-label="Notifications">
                    <i data-lucide="bell" class="w-[24px] h-[24px] text-[#1B1B18]" style="stroke-width:1.8"></i>
                    @if(($notificationCount ?? 0) > 0)
                    <span class="absolute -top-1.5 -right-2 bg-[#D40C0F] text-white text-[10px] font-bold min-w-[19px] h-[19px] px-1 rounded-full flex items-center justify-center">{{ min(99, $notificationCount) }}</span>
                    @endif
                </a>
                <button type="button" class="sm-menu-btn p-1 shrink-0" aria-label="Menu">
                    <i data-lucide="menu" class="w-[26px] h-[26px] text-[#1B1B18]" style="stroke-width:2.2"></i>
                </button>
            </header>

            <div class="px-4 pb-24 pt-1 space-y-3.5 bg-[#FEFEFE]">

                @if($business)

                <!-- Profile hero card -->
                <section class="relative bg-[#02301B] rounded-2xl overflow-hidden">
                    <img src="{{ asset('images/landing/sm-hero-flag.png') }}" alt="" class="absolute right-0 inset-y-0 h-full pointer-events-none select-none" aria-hidden="true">
                    <div class="relative flex items-center gap-3.5 p-3.5">
                        <span class="relative shrink-0">
                            <img src="{{ ($business->logo ?? null) ? asset('storage/' . $business->logo) : asset('images/landing/sm-avatar.png') }}" alt="" class="w-[66px] h-[66px] rounded-full object-cover bg-white">
                            <a href="{{ route('business.edit') }}" aria-label="{{ $isFr ? 'Modifier le logo' : 'Edit logo' }}"
                                class="absolute -bottom-0.5 -right-0.5 w-[22px] h-[22px] rounded-full bg-[#157A43] border-2 border-[#02301B] flex items-center justify-center text-white">
                                <i data-lucide="camera" class="w-3 h-3"></i>
                            </a>
                        </span>
                        <div class="min-w-0 py-1">
                            <a href="{{ route('verification.show') }}" class="flex items-center gap-1.5 text-[11.5px] font-semibold text-white">
                                {{ $isVerified ? ($isFr ? 'Boutique Vérifiée' : 'Verified Shop') : ($isFr ? 'Boutique' : 'Shop') }}
                                @if($isVerified)<i data-lucide="badge-check" class="w-3.5 h-3.5" style="fill:#17A34A;color:#02301B"></i>@endif
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
                    @foreach($smKpis as [$kIcon, $kVal, $kUnit, $kLabel, $kHref])
                    <a href="{{ $kHref }}" class="bg-white border border-[#F0F0EE] rounded-xl px-2.5 py-3 flex items-start gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
                        <img src="{{ asset('images/landing/' . $kIcon) }}" alt="" class="w-[28px] h-[28px] shrink-0" aria-hidden="true">
                        <span class="min-w-0">
                            <span class="block text-[15px] font-bold text-[#1B1B18] leading-tight">{{ $kVal }}@if($kUnit)<span class="text-[9px] font-bold ml-0.5">{{ $kUnit }}</span>@endif</span>
                            <span class="block text-[10px] text-[#55524A] leading-tight">{{ $kLabel }}</span>
                        </span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B4B0A6] ml-auto self-center shrink-0"></i>
                    </a>
                    @endforeach
                </div>

                <!-- Pipeline des devis -->
                <section class="bg-white border border-[#F0F0EE] rounded-2xl p-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Pipeline des devis' : 'Quote pipeline' }}</h2>
                        <a href="{{ route('dashboard.quotes') }}" class="text-[11.5px] font-medium text-[#157A43]">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
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
                    @if(empty($smActivity))
                    <p class="mt-3 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Rien pour le moment. Les demandes de devis, commandes et messages apparaîtront ici.' : 'Nothing yet. Quote requests, orders and messages will appear here.' }}</p>
                    @else
                    <div class="mt-2 divide-y divide-[#F4F4F2]">
                        @foreach($smActivity as [$aIcon, $aText, $aTime, $aColor])
                        <div class="flex items-center gap-2.5 py-2.5">
                            <i data-lucide="{{ $aIcon }}" class="w-[19px] h-[19px] shrink-0" style="color:{{ $aColor }};stroke-width:1.7"></i>
                            <p class="flex-1 min-w-0 text-[11.5px] text-[#1B1B18] leading-snug">{{ $aText }}</p>
                            <span class="text-[10px] text-[#8A857A] whitespace-nowrap shrink-0">{{ $aTime }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
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
                        <a href="{{ route('products.web-index') }}" class="text-[11.5px] font-medium text-[#157A43] whitespace-nowrap ml-2">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
                    </div>
                    @if(empty($smProducts))
                    <p class="mt-3 text-[11.5px] text-[#8A857A]">
                        {{ $isFr ? 'Aucun produit publié pour le moment.' : 'No published products yet.' }}
                        <a href="{{ route('products.web-create') }}" class="font-semibold text-[#157A43]">{{ $isFr ? 'Ajouter un produit' : 'Add a product' }}</a>
                    </p>
                    @else
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        @foreach($smProducts as [$pr, $prName, $prViews])
                        <a href="{{ route('products.web-edit', ['slug' => $pr->slug]) }}" class="min-w-0">
                            <span class="block w-full aspect-square rounded-xl bg-[#F4F6F4] border border-[#EBEEEA] flex items-center justify-center">
                                <i data-lucide="package" class="w-5 h-5 text-[#8A857A]"></i>
                            </span>
                            <p class="mt-1.5 text-[10px] font-bold text-[#1B1B18] leading-tight truncate">{{ $prName }}</p>
                            <p class="mt-0.5 text-[9px] text-[#8A857A] truncate">{{ $prViews }} {{ $isFr ? 'vues' : 'views' }}</p>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </section>

                {{-- Outstanding invoices. There is no wallet or payout rail on the
                     platform, so this reports what buyers still owe rather than a
                     withdrawable balance. --}}
                <section id="sm-wallet" class="bg-[#02301B] rounded-2xl p-3 flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/sm-wallet-icon.png') }}" alt="" class="w-[29px] h-[29px] shrink-0" aria-hidden="true">
                    <div class="min-w-0">
                        <p class="text-[10.5px] text-[#B9CBBE] leading-tight">{{ $isFr ? 'Factures en attente de paiement' : 'Invoices awaiting payment' }}</p>
                        <p class="text-[16px] font-bold text-white leading-tight whitespace-nowrap">{{ number_format((int) $pendingInvoiceTotal, 0, ',', ' ') }} <span class="text-[9.5px]">FCFA</span></p>
                    </div>
                    <a href="{{ route('orders.index') }}" class="ml-auto shrink-0 bg-[#FEBF00] text-[#3A2A03] text-[11px] font-bold px-3 py-2 rounded-lg whitespace-nowrap">
                        {{ $isFr ? 'Voir les commandes' : 'View orders' }}
                    </a>
                </section>

                @else
                <!-- No business yet (mobile) -->
                <div class="bg-white rounded-2xl border-2 border-dashed border-[#E3E3E0] p-8 text-center">
                    <div class="w-12 h-12 bg-[#F6E4BE] rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="store" class="w-6 h-6 text-[#E5A82E]"></i>
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
                <a href="{{ route('dashboard.entrepreneur') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#157A43]">
                    <i data-lucide="house" class="w-[22px] h-[22px]"></i>
                    <span class="text-[10px] font-semibold">{{ $isFr ? 'Accueil' : 'Home' }}</span>
                    <span class="w-6 h-[3px] rounded-full bg-[#157A43]"></span>
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
                        @forelse($realOrders as $ord)
                        <div class="flex items-center gap-3.5 px-5 py-3.5">
                            <span class="w-[51px] h-[51px] rounded-lg bg-[#F2F5F2] flex items-center justify-center shrink-0">
                                <i data-lucide="package" class="w-5 h-5 text-[#8A857A]"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[13.5px] font-semibold text-[#1B1B18] truncate">{{ $ord->title }}</p>
                                <p class="mt-0.5 text-[12px] text-[#8A857A]">{{ $ord->reference }}</p>
                            </div>
                            <div class="ml-auto text-right shrink-0 hidden sm:block">
                                @if(isset($orderStatus[$ord->status]))
                                <span class="inline-block text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $orderStatus[$ord->status][1] }}">{{ $orderStatus[$ord->status][0] }}</span>
                                @endif
                                <p class="mt-1 text-[11.5px] text-[#8A857A]">{{ \Illuminate\Support\Carbon::parse($ord->created_at)->format('d M Y') }}</p>
                            </div>
                            <p class="text-[13.5px] font-bold text-[#1B1B18] shrink-0 w-[92px] text-right">{{ $ord->total ? number_format($ord->total, 0, ',', ' ') . ' FCFA' : '—' }}</p>
                            <a href="{{ route('quotes.po', ['lang' => $lang, 'po' => $ord->id]) }}" class="text-[#8A857A] hover:text-[#1B1B18] shrink-0" aria-label="{{ $isFr ? 'Voir' : 'View' }}">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                        @empty
                        <p class="px-5 py-6 text-center text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Aucune commande pour le moment.' : 'No orders yet.' }}</p>
                        @endforelse
                    </div>
                </section>

                <!-- Activité en temps réel -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Activité en temps réel' : 'Real-time activity' }}</h2>
                    <div class="mt-4 space-y-0">
                        @forelse($activity as $actIdx => [$actIcon, $actColor, $actTime, $actText])
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
                        @empty
                        <p class="py-6 text-center text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Aucune activité récente.' : 'No recent activity.' }}</p>
                        @endforelse
                    </div>
                    <a href="{{ route('notifications.index') }}" class="flex items-center justify-center gap-2 py-2.5 border-t border-[#F4F4F2] text-[12.5px] font-semibold text-[#1B1B18] hover:text-leaf">
                        {{ $isFr ? 'Voir toute l\'activité' : 'View all activity' }}
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </section>

                <!-- Mon portefeuille -->
                <section class="relative rounded-2xl overflow-hidden bg-[#07271A] p-6">
                    <div class="absolute inset-0 opacity-20 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
                    <div class="relative">
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-[15.5px] font-bold text-white">{{ $isFr ? 'Mon portefeuille' : 'My wallet' }}</h2>
                                <p class="mt-4 text-[12.5px] text-[#B9CBBE]">{{ $isFr ? 'Reçu (factures payées)' : 'Received (paid invoices)' }}</p>
                                <p class="mt-1 text-[26px] font-bold text-white leading-none">{{ number_format($revenueTotal, 0, ',', ' ') }} <span class="text-[13px]">FCFA</span></p>
                            </div>
                            <img src="{{ asset('images/landing/sd-wallet-icon.png') }}" alt="" class="w-[64px] shrink-0" aria-hidden="true">
                        </div>
                        <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between text-[12.5px]">
                            <span class="text-[#B9CBBE]">{{ $isFr ? 'En attente de paiement' : 'Pending payment' }}</span>
                            <span class="font-bold text-white">{{ number_format($pendingInvoiceTotal, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <p class="mt-5 text-[11.5px] text-[#B9CBBE] leading-relaxed">{{ $isFr ? 'Les retraits en ligne arrivent bientôt.' : 'Online withdrawals are coming soon.' }}</p>
                        <a href="{{ route('support.index') }}" class="mt-4 block w-full border border-white/40 hover:bg-white/10 text-white text-[13.5px] font-semibold text-center py-3 rounded-lg transition-colors">
                            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
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
                                <p class="mt-1 pl-4 text-[16px] font-bold text-[#1B1B18]">{{ number_format($revenueTotal, 0, ',', ' ') }}</p>
                            </div>
                            <div>
                                <p class="flex items-center gap-2 text-[12px] text-[#55524A]"><span class="w-2 h-2 rounded-full bg-[#F5A623]"></span>{{ $isFr ? 'Commandes' : 'Orders' }}</p>
                                <p class="mt-1 pl-4 text-[16px] font-bold text-[#1B1B18]">{{ $ordersCount }}</p>
                            </div>
                            <div>
                                <p class="flex items-center gap-2 text-[12px] text-[#55524A]"><span class="w-2 h-2 rounded-full bg-[#3B72E8]"></span>{{ $isFr ? 'Visites' : 'Visits' }}</p>
                                <p class="mt-1 pl-4 text-[16px] font-bold text-[#1B1B18]">{{ number_format((int) ($business->views_count ?? 0), 0, ',', ' ') }}</p>
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
                        @forelse($realPopular as $popIdx => $popProd)
                        <a href="{{ route('products.web-edit', ['slug' => $popProd->slug]) }}" class="flex items-center gap-3 py-2.5 {{ $popIdx > 0 ? 'border-t border-[#F4F4F2]' : '' }} hover:bg-[#FAFAF8] transition-colors">
                            <span class="w-[22px] h-[22px] shrink-0 rounded-full text-white text-[11px] font-bold flex items-center justify-center" style="background:{{ $rankColors[$popIdx] }}">{{ $popIdx + 1 }}</span>
                            <div class="min-w-0">
                                <p class="text-[12.5px] font-semibold text-[#1B1B18] truncate">{{ $isFr ? $popProd->name_fr : ($popProd->name_en ?? $popProd->name_fr) }}</p>
                                <p class="text-[11px] text-[#8A857A]">{{ $popProd->views_count ?? 0 }} {{ $isFr ? 'vues' : 'views' }}</p>
                            </div>
                        </a>
                        @empty
                        <p class="py-6 text-center text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Aucun produit publié pour le moment.' : 'No published products yet.' }}</p>
                        @endforelse
                    </div>
                </section>

                <!-- Statistiques par région -->
                <section class="bg-white rounded-2xl border border-[#F0F0EE] shadow-sm px-5 pt-4 pb-4">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Statistiques par région' : 'Statistics by region' }}</h2>
                    <div class="mt-6 flex flex-col items-center text-center py-4">
                        <i data-lucide="map" class="w-7 h-7 text-[#B9B4A9] mb-2"></i>
                        <p class="text-[12px] text-[#6F6B60] max-w-[200px]">{{ $isFr ? 'La répartition géographique arrive bientôt.' : 'Regional breakdown is coming soon.' }}</p>
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
                                <i data-lucide="{{ $qaIcon }}" class="w-6 h-6 text-[#14532D]" style="stroke-width:1.6"></i>
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
                <div class="w-14 h-14 bg-[#F6E4BE] rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="store" class="w-7 h-7 text-[#E5A82E]"></i>
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
    document.querySelectorAll('.sm-menu-btn').forEach(b => b.addEventListener('click', toggleSidebar));
    bd.addEventListener('click', () => {
        sb.classList.remove('open');
        bd.classList.add('hidden');
    });
</script>
</body>
</html>
