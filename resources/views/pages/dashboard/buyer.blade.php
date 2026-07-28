@php
    $isFr = $lang === 'fr';
    $userName = $siacUser['name'] ?? 'Utilisateur';

    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $memberSince = null;
    if ($buyerSince ?? null) {
        $mDate = \Illuminate\Support\Carbon::parse($buyerSince);
        $memberSince = $isFr ? ($frMonths[$mDate->month] . ' ' . $mDate->year) : $mDate->format('F Y');
    }

    $msgCount = (int) ($unreadMessages ?? 0);

    // Every figure below is the buyer's own, queried in the route. The design
    // shipped invented KPIs with seller labels ("revenue", "shop views"); a
    // buyer has neither.
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');

    $bmKpis = [
        ['bm-kpi-1.png', $fmt($buyerStats['orders']),   null,   $isFr ? 'Commandes' : 'Orders',              'bg-[#F4F8F1] dark:bg-[#1A1E16] border-[#E4EEDF] dark:border-[#39402F]'],
        ['bm-kpi-2.png', $fmt($buyerStats['spend']),    'FCFA', $isFr ? 'Total dépensé' : 'Total spent',     'bg-[#FBF6EC] dark:bg-[#1A1E16] border-[#F1E7D2] dark:border-[#4A3A12]'],
        ['bm-kpi-3.png', $fmt($buyerStats['requests']), null,   $isFr ? 'Demandes de devis' : 'Quote requests', 'bg-[#F4F8FC] dark:bg-[#12150F] border-[#E0EAF4] dark:border-[#20304F]'],
        ['bm-kpi-4.png', $fmt($buyerStats['saved']),    null,   $isFr ? 'Favoris' : 'Saved items',           'bg-[#FDF4F4] dark:bg-[#1A1E16] border-[#F5E0E0] dark:border-[#5C2126]'],
    ];

    // Second element is the kit pill variant. "Shipped" had a bespoke blue with
    // no kit equivalent, so it reads as neutral until delivery turns it green.
    $bmStatus = [
        'delivered'     => [$isFr ? 'Livrée' : 'Delivered',            'ui-pill-ok'],
        'shipped'       => [$isFr ? 'Expédiée' : 'Shipped',            'ui-pill-neutral'],
        'in_production' => [$isFr ? 'En production' : 'In production', 'ui-pill-warn'],
        'confirmed'     => [$isFr ? 'Confirmée' : 'Confirmed',         'ui-pill-ok'],
        'created'       => [$isFr ? 'Créée' : 'Created',               'ui-pill-neutral'],
        'cancelled'     => [$isFr ? 'Annulée' : 'Cancelled',           'ui-pill-danger'],
    ];

    $bmActions = [
        ['bm-qa-1.png', $isFr ? 'Explorer' : 'Browse',                  route('products.index', ['lang' => $lang]), null],
        ['bm-qa-2.png', $isFr ? 'Demander un devis' : 'Request a quote', route('quotes.create'), null],
        ['bm-qa-3.png', $isFr ? 'Commandes' : 'Orders',                 route('orders.index'), $buyerStats['orders'] ? (string) $buyerStats['orders'] : null],
        ['bm-qa-4.png', 'Messages',                                      route('messages.inbox'), $msgCount ? (string) $msgCount : null],
        ['bm-qa-5.png', $isFr ? 'Mes devis' : 'My quotes',              route('quotes.index'), $buyerStats['pending'] ? (string) $buyerStats['pending'] : null],
        ['bm-qa-6.png', $isFr ? 'Favoris' : 'Saved',                    route('saved.index'), $buyerStats['saved'] ? (string) $buyerStats['saved'] : null],
        ['bm-qa-7.png', $isFr ? 'Artisans' : 'Artisans',                route('businesses.index', ['lang' => $lang]), null],
        ['bm-qa-8.png', $isFr ? 'Mon profil' : 'My profile',            route('profile.show'), null],
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
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
</head>
<body class="bg-[#F5F5F3] dark:bg-[#1A1E16] text-[#1B1B18] dark:text-[#F3EFE7] antialiased">

<!-- Slide-over menu (buyer flows live here) -->
<aside id="buyer-menu" class="fixed inset-y-0 left-0 z-50 w-[300px] bg-[#002714] dark:bg-[#0C3B1E] overflow-y-auto">
    <div class="bg-[#031E12] dark:bg-[#0C3B1E] px-5 py-4 flex items-center gap-3">
        <img src="{{ brand_asset('mark') }}" alt="" class="w-[48px] h-[48px] object-contain shrink-0">
        <span class="text-[11.5px] font-bold tracking-[0.02em] text-white uppercase leading-[1.4]">
            {{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}<br>
            {{ $isFr ? 'Marketplace des artisans' : 'Artisan Marketplace' }}
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
            ['clipboard-list', $isFr ? 'Mes commandes' : 'My orders',            route('orders.index')],
            ['message-circle', 'Messages',                                        route('messages.inbox')],
            ['bell',           'Notifications',                                   route('notifications.index')],
            ['user',           $isFr ? 'Mon profil' : 'My profile',              route('profile.show')],
            ['shield-check',   $isFr ? 'Sécurité' : 'Security',                  route('security.show')],
            ['circle-help',    $isFr ? 'Aide & Support' : 'Help & Support',      route('support.index')],
        ] as [$mIcon, $mLabel, $mHref])
        <a href="{{ $mHref }}" class="flex items-center gap-3.5 px-5 py-3 text-[14px] text-white hover:bg-white dark:hover:bg-[#242A1E]/5">
            <i data-lucide="{{ $mIcon }}" class="w-5 h-5 text-[#A8B8AC] dark:text-[#868778]" style="stroke-width:1.7"></i>
            {{ $mLabel }}
        </a>
        @endforeach
        <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-white/10 pt-2">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3.5 px-5 py-3 text-[14px] text-[#FF8B8B] dark:text-[#F0555C] hover:bg-white dark:hover:bg-[#242A1E]/5">
                <i data-lucide="log-out" class="w-5 h-5" style="stroke-width:1.7"></i>
                {{ $isFr ? 'Se déconnecter' : 'Log out' }}
            </button>
        </form>
    </nav>
</aside>
<div id="buyer-backdrop" class="fixed inset-0 z-40 bg-black/50 hidden"></div>

<div class="max-w-[431px] lg:max-w-[1160px] mx-auto min-h-screen bg-[#FEFEFE] dark:bg-[#12150F] shadow-sm">

    <!-- Header -->
    <header class="bg-white dark:bg-[#12150F] px-4 pt-3.5 pb-3 flex items-center gap-3">
        <img src="{{ brand_asset('mark') }}" alt="" class="w-[42px] h-[42px] object-contain shrink-0">
        <span class="leading-tight min-w-0">
            <span class="block text-[12px] font-bold tracking-[0.01em] text-[#1B1B18] dark:text-[#F3EFE7] uppercase whitespace-nowrap">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</span>
            <span class="block text-[12px] font-bold tracking-[0.01em] text-[#1B1B18] dark:text-[#F3EFE7] uppercase whitespace-nowrap">{{ $isFr ? 'Marketplace des artisans' : 'Artisan Marketplace' }}</span>
            <span class="block text-[9.5px] text-[#55524A] dark:text-[#B4B5A6] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
        </span>
        <a href="{{ route('notifications.index') }}" class="relative ml-auto p-1 shrink-0" aria-label="Notifications">
            <i data-lucide="bell" class="w-[24px] h-[24px] text-[#1B1B18] dark:text-[#F3EFE7]" style="stroke-width:1.8"></i>
            @if(($notificationCount ?? 0) > 0)
            <span class="absolute -top-1.5 -right-1.5 bg-[#D40C0F] dark:bg-[#C0343B] text-white text-[10px] font-bold min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center">{{ min(99, $notificationCount) }}</span>
            @endif
        </a>
        <button type="button" class="bm-menu-btn p-1 shrink-0" aria-label="Menu">
            <i data-lucide="menu" class="w-[26px] h-[26px] text-[#1B1B18] dark:text-[#F3EFE7]" style="stroke-width:2.2"></i>
        </button>
    </header>

    <div class="px-4 lg:px-8 pb-24 lg:pb-10 pt-1 space-y-3.5 lg:space-y-0 lg:grid lg:grid-cols-2 lg:gap-5 lg:items-start lg:pt-4">

        <!-- Hero card -->
        <section class="relative bg-herogr rounded-2xl overflow-hidden lg:col-span-2">
            <img src="{{ asset('images/landing/bm-hero-flag.png') }}" alt="" class="absolute right-0 inset-y-0 h-full pointer-events-none select-none" aria-hidden="true">
            <div class="absolute inset-0 opacity-15 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative flex items-start gap-3.5 p-4 pb-[52px]">
                <img src="{{ asset('images/landing/bm-avatar.png') }}" alt="" class="w-[58px] h-[58px] rounded-full shrink-0">
                <div class="min-w-0 pt-0.5">
                    <p class="text-[18px] font-bold text-white leading-snug truncate">{{ $isFr ? 'Bonjour' : 'Hello' }}, {{ $userName }} ! 👋</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[13px] font-semibold text-[#F5C33B]">
                        {{ $isFr ? 'Compte acheteur' : 'Buyer account' }}
                        <i data-lucide="badge-check" class="w-4 h-4" style="fill:#F5C33B;color:#03341B"></i>
                    </p>
                    @if($memberSince)
                    <p class="mt-1 text-[11px] text-[#C6D4C9]">{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $memberSince }}</p>
                    @endif
                </div>
            </div>
            <a href="{{ route('products.index', ['lang' => $lang]) }}" class="absolute right-4 bottom-3.5 inline-flex items-center gap-2 bg-[#0A3D22] dark:bg-[#0C3B1E]/80 border border-white/70 rounded-lg px-3.5 py-[7px] text-[12px] font-semibold text-white">
                <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                {{ $isFr ? 'Explorer la galerie' : 'Browse the gallery' }}
            </a>
        </section>

        <!-- Aperçu de votre activité -->
        <section id="apercu" class="ui-card p-4">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-[14.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Aperçu de votre activité' : 'Your activity overview' }}</h2>
                <span class="flex items-center gap-1 text-[11px] text-[#55524A] dark:text-[#B4B5A6] whitespace-nowrap">
                    {{ $isFr ? 'Depuis votre inscription' : 'Since you joined' }}
                </span>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2.5">
                @foreach($bmKpis as [$kIcon, $kVal, $kUnit, $kLabel, $kTint])
                <div class="border rounded-xl p-3 {{ $kTint }}">
                    <div class="flex items-start gap-2.5">
                        <img src="{{ asset('images/landing/' . $kIcon) }}" alt="" class="w-[31px] h-[31px] shrink-0" aria-hidden="true">
                        <div class="min-w-0 flex-1">
                            <p class="text-[16.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-tight">{{ $kVal }}@if($kUnit)<span class="text-[10px] font-bold ml-1">{{ $kUnit }}</span>@endif</p>
                            <span class="text-[11px] text-[#55524A] dark:text-[#B4B5A6] leading-tight">{{ $kLabel }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Commandes récentes -->
        <section class="ui-card p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Commandes récentes' : 'Recent orders' }}</h2>
                <a href="{{ route('orders.index') }}" class="text-[11.5px] font-medium text-[#157A43] dark:text-[#339B56]">{{ $isFr ? 'Voir toutes' : 'View all' }}</a>
            </div>
            @if($buyerOrders->isEmpty())
            <div class="ui-empty py-8">
                <i data-lucide="clipboard-list" class="w-7 h-7 text-[#DCE7DF] mx-auto mb-2"></i>
                <p>{{ $isFr ? 'Aucune commande pour le moment.' : 'No orders yet.' }}</p>
                <a href="{{ route('products.index', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-2">{{ $isFr ? 'Explorer la galerie' : 'Browse the gallery' }}</a>
            </div>
            @else
            <div class="mt-1 divide-y divide-[#F4F4F2] dark:divide-[#262B21]">
                @foreach($buyerOrders as $o)
                @php $oMeta = $bmStatus[$o->status] ?? $bmStatus['created']; @endphp
                <a href="{{ route('quotes.po', ['po' => $o->id]) }}" class="flex items-center gap-3 py-3">
                    <span class="w-[43px] h-[43px] rounded-lg bg-[#F4F6F4] dark:bg-[#1A1E16] border border-[#EBEEEA] dark:border-[#262B21] shrink-0 flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-[#8A857A] dark:text-[#868778]"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $o->proposal?->request?->title ?? $o->reference }}</p>
                        <p class="mt-0.5 text-[11px] text-[#8A857A] dark:text-[#868778] truncate">{{ $o->reference }} · {{ $o->proposal?->request?->business?->name_fr }}</p>
                    </div>
                    <div class="shrink-0 text-left w-[86px]">
                        <span class="ui-pill {{ $oMeta[1] }}">{{ $oMeta[0] }}</span>
                        <p class="mt-1 text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $o->created_at?->translatedFormat('d M Y') }}</p>
                    </div>
                    <p class="text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] shrink-0 whitespace-nowrap">{{ number_format((int) $o->total, 0, ',', ' ') }} FCFA</p>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-[#B4B0A6] dark:text-[#868778] shrink-0"></i>
                </a>
                @endforeach
            </div>
            @endif
        </section>

        <!-- Actions rapides -->
        <section class="ui-card p-4">
            <h2 class="text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
            <div class="mt-3 grid grid-cols-4 gap-2">
                @foreach($bmActions as [$qIcon, $qLabel, $qHref, $qBadge])
                <a href="{{ $qHref }}" class="relative bg-[#F4F6F4] dark:bg-[#1A1E16] border border-[#EBEEEA] dark:border-[#262B21] rounded-xl px-1.5 pt-3.5 pb-2.5 text-center">
                    @if($qBadge)
                    <span class="absolute top-1.5 right-1.5 bg-[#D40C0F] dark:bg-[#C0343B] text-white text-[9px] font-bold min-w-[17px] h-[17px] px-1 rounded-full flex items-center justify-center">{{ $qBadge }}</span>
                    @endif
                    <img src="{{ asset('images/landing/' . $qIcon) }}" alt="" class="w-[24px] h-[24px] mx-auto" aria-hidden="true">
                    <span class="mt-2 block text-[10px] text-[#3B382F] dark:text-[#B4B5A6] leading-tight">{{ $qLabel }}</span>
                </a>
                @endforeach
            </div>
        </section>

        <!-- Promo -->
        <section class="ui-card ui-card--flush relative lg:col-span-2">
            <img src="{{ asset('images/landing/bm-promo-art.png') }}" alt="" class="absolute right-0 bottom-0 h-full pointer-events-none select-none" aria-hidden="true">
            <div class="relative p-4 pr-[84px]">
                <h2 class="text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? 'Vous êtes artisan ?' : 'Are you an artisan?' }}</h2>
                <p class="mt-1 text-[11.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed max-w-[240px]">
                    {{ $isFr ? 'Créez votre boutique, publiez vos produits et recevez des demandes de devis.' : 'Create your shop, publish your products and start receiving quote requests.' }}
                </p>
                <a href="{{ route('business.create') }}" class="ui-btn ui-btn-primary ui-btn-sm mt-3">
                    {{ $isFr ? 'Créer ma boutique' : 'Create my shop' }}
                </a>
            </div>
        </section>
    </div>

    <!-- Bottom nav -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white dark:bg-[#12150F] border-t border-[#EDEDEB] dark:border-[#262B21] max-w-[431px] mx-auto flex items-stretch h-[64px]" style="padding-bottom: env(safe-area-inset-bottom)">
        <a href="{{ route('dashboard.buyer') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#0B5B31] dark:text-[#339B56]">
            <i data-lucide="house" class="w-[22px] h-[22px]"></i>
            <span class="text-[10px] font-semibold">{{ $isFr ? 'Accueil' : 'Home' }}</span>
        </a>
        <a href="{{ route('products.index', ['lang' => $lang]) }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A] dark:text-[#B4B5A6]">
            <i data-lucide="shopping-bag" class="w-[22px] h-[22px]"></i>
            <span class="text-[10px]">{{ $isFr ? 'Produits' : 'Products' }}</span>
        </a>
        <a href="{{ route('quotes.create') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A] dark:text-[#B4B5A6]">
            <span class="-mt-7 w-[52px] h-[52px] rounded-full bg-[#0B4D27] dark:bg-[#2E9250] shadow-lg flex items-center justify-center text-white dark:text-[#04150A]">
                <i data-lucide="plus" class="w-6 h-6"></i>
            </span>
            <span class="text-[10px] mt-0.5">{{ $isFr ? 'Devis' : 'Quote' }}</span>
        </a>
        <a href="{{ route('orders.index') }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A] dark:text-[#B4B5A6]">
            <span class="relative">
                <i data-lucide="clipboard-list" class="w-[22px] h-[22px]"></i>
                @if($buyerStats['orders'] > 0)
                <span class="absolute -top-1.5 -right-2.5 bg-[#D40C0F] dark:bg-[#C0343B] text-white text-[8.5px] font-bold min-w-[16px] h-[15px] px-0.5 rounded-full flex items-center justify-center">{{ min(99, $buyerStats['orders']) }}</span>
                @endif
            </span>
            <span class="text-[10px]">{{ $isFr ? 'Commandes' : 'Orders' }}</span>
        </a>
        <button type="button" class="bm-menu-btn flex-1 flex flex-col items-center justify-center gap-0.5 text-[#55524A] dark:text-[#B4B5A6]">
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
