@php
    $isFr = $lang === 'fr';

    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $enMonths = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $fmtDate = function ($date) use ($isFr, $frMonths, $enMonths) {
        $ts = strtotime($date);
        $m = (int) date('n', $ts);
        return date('d', $ts) . ' ' . ($isFr ? $frMonths[$m] : $enMonths[$m]) . ' ' . date('Y', $ts);
    };

    $shopName = $business ? ($isFr ? $business->name_fr : ($business->name_en ?: $business->name_fr)) : 'Atelier Nshome Artisanat';
    $shopLogo = ($business && $business->logo) ? asset('storage/' . $business->logo) : asset('images/landing/logo.png');
    $firstName = explode(' ', trim($siacUser['name'] ?? 'Jude'))[0];
    $memberSince = $business ? $fmtDate($business->created_at) : ($isFr ? '12 Mai 2024' : '12 May 2024');
    $artisanId = $business
        ? 'ART-CM-' . date('Y', strtotime($business->created_at)) . '-' . str_pad((string) $business->id, 6, '0', STR_PAD_LEFT)
        : 'ART-CM-2024-000158';

    $storeUrl = $business ? route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) : route('business.create', ['lang' => $lang]);
    $bizEditUrl = $business ? route('business.edit', ['lang' => $lang]) : route('business.create', ['lang' => $lang]);
    $msgBadge = $messageCount > 0 ? $messageCount : 5;
    $siacUrl = $siacEvent ? route('events.show', ['slug' => $siacEvent->slug, 'lang' => $lang]) : route('events.index', ['lang' => $lang]);

    // Sidebar nav — every design item mapped onto a real route
    $sideActivity = [
        ['file-text',      $isFr ? 'Demandes de devis' : 'Quote requests', '18',            route('messages.inbox', ['lang' => $lang])],
        ['file-badge',     $isFr ? 'Devis envoyés' : 'Quotes sent',        '12',            route('messages.inbox', ['lang' => $lang])],
        ['clipboard-list', $isFr ? 'Commandes' : 'Orders',                 '7',             route('messages.inbox', ['lang' => $lang])],
        ['message-circle', 'Messages',                                     (string) $msgBadge, route('messages.inbox', ['lang' => $lang])],
        ['users',          $isFr ? 'Clients & contacts' : 'Clients & contacts', null,       route('messages.inbox', ['lang' => $lang])],
    ];
    $sideShop = [
        ['package',   $isFr ? 'Produits & Services' : 'Products & Services', $storeUrl],
        ['image',     $isFr ? 'Galerie média' : 'Media gallery',             $bizEditUrl],
        ['tags',      $isFr ? 'Catégories' : 'Categories',                   route('industries.index', ['lang' => $lang])],
        ['percent',   $isFr ? 'Promotions & Offres' : 'Promotions & Offers', route('contact', ['lang' => $lang])],
        ['star',      $isFr ? 'Avis & Evaluations' : 'Reviews & Ratings',    $storeUrl],
    ];
    $sideManage = [
        ['file-text',    'Documents',                                          route('membership.certificate', ['lang' => $lang])],
        ['shield-check', 'Certifications',                                     route('verification.show', ['lang' => $lang])],
        ['truck',        $isFr ? 'Expédition & Livraison' : 'Shipping & Delivery', route('support.index', ['lang' => $lang])],
        ['settings',     $isFr ? 'Paramètres boutique' : 'Shop settings',      $bizEditUrl],
    ];
    $premiumPerks = $isFr
        ? ['Plus de visibilité', "Accès aux appels d'offres", 'Statistiques avancées', 'Badge artisan premium']
        : ['More visibility', 'Access to calls for tenders', 'Advanced statistics', 'Premium artisan badge'];

    // [icon, iconColor, tileBg, label, value, delta, deltaColor]
    $kpis = [
        ['file-text',     '#3B5BDB', '#E9EFFC', $isFr ? 'Demandes de devis' : 'Quote requests',     '18', $isFr ? '+6 ce mois' : '+6 this month', '#157A43'],
        ['file-badge',    '#7C5CE0', '#EDEBFA', $isFr ? 'Devis envoyés' : 'Quotes sent',            '12', $isFr ? '+4 ce mois' : '+4 this month', '#157A43'],
        ['hourglass',     '#EE8D0C', '#FDF1DE', $isFr ? 'En attente réponse' : 'Awaiting response', '7',  $isFr ? '+2 ce mois' : '+2 this month', '#EE7A1C'],
        ['shopping-cart', '#157A43', '#DFF3E5', $isFr ? 'Commandes confirmées' : 'Confirmed orders','7',  $isFr ? '+3 ce mois' : '+3 this month', '#157A43'],
        ['users',         '#4A63D8', '#EDEDF8', $isFr ? 'Clients actifs' : 'Active clients',        '23', $isFr ? '+8 ce mois' : '+8 this month', '#157A43'],
    ];

    // [label, ringColor, count] + segment colors between dots
    $pipeStages = [
        [$isFr ? 'Nouvelles' : 'New',                  '#2E6BE6', '18'],
        [$isFr ? 'En discussion' : 'In discussion',    '#F5B301', '7'],
        [$isFr ? 'Devis envoyés' : 'Quotes sent',      '#8B5CF6', '12'],
        [$isFr ? 'Négociation' : 'Negotiation',        '#F97316', '5'],
        [$isFr ? "En attente\nclient" : "Awaiting\nclient", '#2FB4A5', '7'],
        [$isFr ? 'Converties' : 'Converted',           '#17803D', '7'],
    ];
    $pipeSegs = ['#2E6BE6', '#F5B301', '#8B5CF6', '#F97316', '#2FB4A5'];

    // [thumb, title, place, date, pillLabel, pillText, pillBg]
    $recentRequests = [
        ['qd-req-1.png', $isFr ? 'Sculpture monumentale' : 'Monumental sculpture', $isFr ? 'Yaoundé, Cameroun' : 'Yaounde, Cameroon',     $isFr ? '02 Mai 2024' : '02 May 2024',  $isFr ? 'NOUVELLE' : 'NEW',            '#3565DE', '#E8EFFB'],
        ['qd-req-2.png', $isFr ? 'Mobilier de bureau' : 'Office furniture',        $isFr ? 'Douala, Cameroun' : 'Douala, Cameroon',       $isFr ? '30 Avr. 2024' : '30 Apr. 2024', $isFr ? 'EN DISCUSSION' : 'IN DISCUSSION', '#E8890C', '#FDF0DC'],
        ['qd-req-3.png', $isFr ? 'Décoration intérieure' : 'Interior decoration',  $isFr ? 'Bafoussam, Cameroun' : 'Bafoussam, Cameroon', $isFr ? '29 Avr. 2024' : '29 Apr. 2024', $isFr ? 'DEVIS ENVOYÉ' : 'QUOTE SENT', '#7C4FE0', '#F0EAFB'],
        ['qd-req-4.png', $isFr ? 'Masques traditionnels' : 'Traditional masks',    'Paris, France',                                       $isFr ? '28 Avr. 2024' : '28 Apr. 2024', $isFr ? 'NÉGOCIATION' : 'NEGOTIATION', '#EE7A1C', '#FDEFE0'],
        ['qd-req-5.png', $isFr ? 'Statue en bois' : 'Wooden statue',               'Lyon, France',                                        $isFr ? '26 Avr. 2024' : '26 Apr. 2024', $isFr ? 'CONVERTIE' : 'CONVERTED',     '#157A43', '#E2F3E8'],
    ];

    // Real RFQs override the design demo rows in "Demandes récentes" when present
    if (($realRfqs ?? collect())->isNotEmpty()) {
        $rfqPills = [
            'pending'     => [$isFr ? 'NOUVELLE' : 'NEW', '#3565DE', '#E8EFFB'],
            'quoted'      => [$isFr ? 'DEVIS ENVOYÉ' : 'QUOTE SENT', '#7C4FE0', '#F0EAFB'],
            'negotiation' => [$isFr ? 'NÉGOCIATION' : 'NEGOTIATION', '#EE7A1C', '#FDEFE0'],
            'accepted'    => [$isFr ? 'CONVERTIE' : 'CONVERTED', '#157A43', '#E2F3E8'],
            'refused'     => [$isFr ? 'REFUSÉE' : 'REFUSED', '#E5484D', '#FDE8E8'],
        ];
        $recentRequests = $realRfqs->map(function ($r) use ($rfqPills, $isFr, $lang) {
            [$pl, $pc, $pb] = $rfqPills[$r->status] ?? $rfqPills['pending'];
            return [
                'qd-req-' . (($r->id % 5) + 1) . '.png',
                $r->title,
                $r->buyer->name ?? '—',
                $r->created_at->format('d/m/Y'),
                $pl, $pc, $pb,
                route('quotes.builder', ['lang' => $lang, 'rfq' => $r->id]),
            ];
        })->all();
    }

    // [icon, iconColor, tileBg, title, sub, time, badge, isStars]
    $activities = [
        ['message-circle', '#3B5BDB', '#E9EFFC', $isFr ? 'Nouveau message de Global Crafts Ltd' : 'New message from Global Crafts Ltd', $isFr ? 'Bonjour, nous sommes intéressés par vos sculptures...' : 'Hello, we are interested in your sculptures...', '10:30', '1', false],
        ['check',          '#157A43', '#DFF3E5', $isFr ? 'Devis accepté par Jean-Paul Nkodo' : 'Quote accepted by Jean-Paul Nkodo',      $isFr ? 'Montant : 850,000 FCFA' : 'Amount: 850,000 FCFA',            $isFr ? 'Hier' : 'Yesterday', '1', false],
        ['file-text',      '#3B5BDB', '#E9EFFC', $isFr ? 'Nouvelle demande de devis' : 'New quote request',                              $isFr ? 'Mobilier en bois sur mesure' : 'Custom wooden furniture',    $isFr ? '2 Mai' : '2 May', null, false],
        ['star',           '#157A43', '#DFF3E5', $isFr ? 'Vous avez reçu un avis 5 étoiles' : 'You received a 5-star review',            '★★★★★',                                                              $isFr ? '2 Mai' : '2 May', null, true],
        ['triangle-alert', '#DC2626', '#FDE8E8', $isFr ? 'Document expirera bientôt : Attestation fiscale' : 'Document expiring soon: Tax certificate', $isFr ? 'Expire le 25 Mai 2024' : 'Expires 25 May 2024', $isFr ? '2 Mai' : '2 May', null, false],
    ];

    // Design product-performance rows (used verbatim when the business has no products)
    $designPerf = [
        ['qd-perf-1.png', $isFr ? 'Masque traditionnel Bamileke' : 'Traditional Bamileke mask', '568', '12', 85],
        ['qd-perf-2.png', $isFr ? 'Tabouret sculpté' : 'Carved stool',                          '432', '9',  72],
        ['qd-perf-3.png', $isFr ? 'Collier perles artisanales' : 'Artisanal bead necklace',     '389', '7',  64],
        ['qd-perf-4.png', $isFr ? 'Sculpture en bois' : 'Wood sculpture',                       '312', '6',  54],
        ['qd-perf-5.png', $isFr ? 'Panier tressé' : 'Woven basket',                             '298', '5',  50],
    ];
    $perfRows = [];
    if ($topProducts->isNotEmpty()) {
        $maxViews = max(1, $topProducts->max('views_count'));
        $devisStatic = ['12', '9', '7', '6', '5'];
        foreach ($topProducts as $pi => $p) {
            $img = isset($topProductImages[$p->id]) ? asset('storage/' . $topProductImages[$p->id]) : asset('images/landing/' . $designPerf[$pi % 5][0]);
            $perfRows[] = [$img, $isFr ? $p->name_fr : ($p->name_en ?: $p->name_fr), number_format($p->views_count), $devisStatic[$pi % 5], max(18, (int) round($p->views_count / $maxViews * 85))];
        }
    } else {
        foreach ($designPerf as $row) { $row[0] = asset('images/landing/' . $row[0]); $perfRows[] = $row; }
    }
    $rankColors = ['#F5B301', '#9CA3AF', '#ED7E1C', '#9CA3AF', '#9CA3AF'];

    // [label, valueHtml-safe pieces]
    $healthChecks = [
        [$isFr ? 'Profil complété' : 'Profile completed', '100%', '#157A43', null],
        [$isFr ? 'Boutique active' : 'Active shop',       '100%', '#157A43', null],
        [$isFr ? 'Produits publiés' : 'Published products', '18', '#1B1B18', '/20'],
        [$isFr ? 'Réponse rapide' : 'Fast response',      $isFr ? 'Bon' : 'Good', '#157A43', ' (90%)'],
        [$isFr ? 'Avis clients' : 'Client reviews',       '4.8/5', '#1B1B18', null],
    ];

    // [icon, title, sub, pill, pillColor, pillBg]
    $docRows = [
        ['file-text',    $isFr ? 'Registre de Commerce' : 'Trade Register',          $isFr ? "Valide jusqu'au 12 Mai 2025" : 'Valid until 12 May 2025',  'Valide',        '#157A43', '#E4F1E8'],
        ['file-text',    $isFr ? 'Attestation Fiscale' : 'Tax Certificate',          $isFr ? "Valide jusqu'au 12 Mai 2025" : 'Valid until 12 May 2025',  'Valide',        '#157A43', '#E4F1E8'],
        ['file-text',    $isFr ? "Carte d'Artisan" : 'Artisan Card',                 $isFr ? "Valide jusqu'au 12 Mai 2026" : 'Valid until 12 May 2026',  'Valide',        '#157A43', '#E4F1E8'],
        ['shield-check', $isFr ? 'Assurance Professionnelle' : 'Professional Insurance', $isFr ? 'Expire le 25 Mai 2024' : 'Expires 25 May 2024',        $isFr ? 'Expire bientôt' : 'Expiring soon', '#EE7A1C', '#FDEFE0'],
    ];
    if (!$isFr) { $docRows[0][3] = 'Valid'; $docRows[1][3] = 'Valid'; $docRows[2][3] = 'Valid'; }

    // [icon, title, sub]
    $tips = [
        ['mail-check',    $isFr ? 'Répondez rapidement aux demandes de devis' : 'Reply quickly to quote requests',      $isFr ? 'Augmentez vos chances de conversion de 40%' : 'Increase your conversion chances by 40%'],
        ['image-plus',    $isFr ? 'Ajoutez plus de photos de vos produits' : 'Add more photos of your products',        $isFr ? 'Les boutiques avec +20 photos reçoivent 3x plus de vues' : 'Shops with 20+ photos get 3x more views'],
        ['calendar-days', $isFr ? 'Participez aux événements et salons' : 'Take part in events and fairs',              $isFr ? 'Gagnez en visibilité et trouvez de nouveaux clients' : 'Gain visibility and find new clients'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Tableau de bord devis — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Quotes dashboard — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { qdside: '#01301C', qdact: '#06512C', qddeep: '#0A4D2E' },
                    fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qd-sidebar { display: none; }
        #qd-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #qd-sidebar, #qd-sidebar.open { display: block; position: static; width: auto; overflow-y: visible; } }
    </style>
</head>
<body class="bg-[#F5F7F6] text-[#1B1B18] antialiased">

<!-- Header -->
<header class="bg-white">
    <div class="max-w-[1500px] mx-auto px-4 lg:px-6 flex items-center gap-4 py-3">
        <button type="button" onclick="document.getElementById('qd-sidebar').classList.toggle('open')" class="lg:hidden shrink-0 text-[#1B1B18]">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain">
            <span class="leading-tight hidden sm:block">
                <span class="block text-[12.5px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                <span class="block text-[12.5px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                <span class="block text-[10.5px] text-[#2E7D4F] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </a>
        <form action="{{ route('gallery.search') }}" method="GET" class="flex-1 max-w-[560px] mx-auto hidden md:block">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="flex items-center gap-3 border border-[#E7E7E5] rounded-xl bg-[#FBFBFA] px-4 h-[46px]">
                <i data-lucide="search" class="w-[18px] h-[18px] shrink-0 text-[#8A857A]"></i>
                <input type="text" name="q" placeholder="{{ $isFr ? 'Rechercher dans la plateforme...' : 'Search the platform...' }}" class="flex-1 min-w-0 bg-transparent text-[13px] focus:outline-none placeholder-[#8A857A]">
                <span class="shrink-0 border border-[#E5E3E0] rounded-md bg-white px-2 py-0.5 text-[11.5px] text-[#55524A]">Ctrl + K</span>
            </div>
        </form>
        <div class="flex items-center gap-5 shrink-0 ml-auto">
            <a href="{{ route('notifications.index', ['lang' => $lang]) }}" class="relative" title="Notifications">
                <i data-lucide="bell" class="w-6 h-6 text-[#3B382F]" style="stroke-width:1.7"></i>
                <span class="absolute -top-1.5 -right-1.5 w-[17px] h-[17px] rounded-full bg-[#DC2626] text-white text-[10px] font-bold flex items-center justify-center">3</span>
            </a>
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="relative" title="Messages">
                <i data-lucide="message-circle" class="w-6 h-6 text-[#3B382F]" style="stroke-width:1.7"></i>
                <span class="absolute -top-1.5 -right-1.5 w-[17px] h-[17px] rounded-full bg-[#157A43] text-white text-[10px] font-bold flex items-center justify-center">2</span>
            </a>
            <div class="relative group">
                <button class="flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/qd-avatar.png') }}" alt="" class="w-[42px] h-[42px] rounded-full object-cover">
                    <span class="leading-tight text-left hidden sm:block">
                        <span class="block text-[13.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $siacUser['name'] ?? 'Jude Nshome' }}</span>
                        <span class="block text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Artisan' : 'Artisan' }}</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i>
                </button>
                <div class="absolute right-0 top-full w-56 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1.5 hidden group-hover:block z-50">
                    <a href="{{ route('profile.show', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                    <a href="{{ route('security.show', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                    <a href="{{ route('membership.certificate', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Mon certificat d\'adhésion' : 'My membership certificate' }}</a>
                    <div class="my-1 border-t border-[#EFEFED]"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-[12.5px] text-[#B42025] hover:bg-[#F6F7F6]">{{ $isFr ? 'Déconnexion' : 'Log out' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="max-w-[1500px] mx-auto lg:px-2 flex items-start gap-3 pb-4">

    <!-- Sidebar -->
    <aside id="qd-sidebar" class="lg:w-[272px] shrink-0 bg-qdside rounded-none lg:rounded-2xl text-white lg:sticky lg:top-2">
        <div class="px-4 pt-5 pb-6">
            <div class="flex items-center gap-3">
                <img src="{{ $shopLogo }}" alt="" class="w-[48px] h-[48px] rounded-full object-cover bg-white p-0.5">
                <div class="min-w-0">
                    <p class="text-[13.5px] font-bold leading-snug">{{ $shopName }}</p>
                    <a href="{{ route('verification.show', ['lang' => $lang]) }}" class="mt-1 inline-flex items-center gap-1.5 bg-[#0E8249]/30 border border-[#0E8249]/60 rounded-full px-2.5 py-0.5 text-[10.5px] font-semibold text-[#7FDCA8]">
                        {{ $isFr ? 'Vérifié' : 'Verified' }}
                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>
            <p class="mt-4 text-[11.5px] text-[#A9C4B3]">ID {{ $isFr ? 'Artisan' : 'Artisan' }} : {{ $artisanId }}</p>
            <p class="mt-1 text-[11.5px] text-[#A9C4B3]">{{ $isFr ? 'Membre depuis le' : 'Member since' }} {{ $memberSince }}</p>

            <a href="{{ route('dashboard.quotes', ['lang' => $lang]) }}" class="mt-5 flex items-center gap-3 bg-qdact rounded-xl px-4 py-3.5 text-[13.5px] font-bold">
                <i data-lucide="house" class="w-[18px] h-[18px]" style="stroke-width:2"></i>
                {{ $isFr ? 'Tableau de bord' : 'Dashboard' }}
            </a>

            <p class="mt-6 px-1 text-[11px] font-bold tracking-[0.08em] text-[#17A45D] uppercase">{{ $isFr ? 'Mon activité' : 'My activity' }}</p>
            <nav class="mt-2">
                @foreach($sideActivity as [$navIcon, $navLabel, $navBadge, $navUrl])
                <a href="{{ $navUrl }}" class="flex items-center gap-3.5 rounded-lg px-2.5 py-[9px] hover:bg-white/5">
                    <i data-lucide="{{ $navIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#CFE2D6]" style="stroke-width:1.7"></i>
                    <span class="flex-1 text-[13px] text-[#EAF2ED]">{{ $navLabel }}</span>
                    @if($navBadge)<span class="shrink-0 min-w-[26px] text-center bg-[#2E5B41] rounded-full px-2 py-0.5 text-[11px] font-semibold text-white">{{ $navBadge }}</span>@endif
                </a>
                @endforeach
            </nav>

            <div class="mt-4 border-t border-white/10"></div>
            <p class="mt-4 px-1 text-[11px] font-bold tracking-[0.08em] text-[#17A45D] uppercase">{{ $isFr ? 'Ma boutique' : 'My shop' }}</p>
            <nav class="mt-2">
                @foreach($sideShop as [$navIcon, $navLabel, $navUrl])
                <a href="{{ $navUrl }}" class="flex items-center gap-3.5 rounded-lg px-2.5 py-[9px] hover:bg-white/5">
                    <i data-lucide="{{ $navIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#CFE2D6]" style="stroke-width:1.7"></i>
                    <span class="flex-1 text-[13px] text-[#EAF2ED]">{{ $navLabel }}</span>
                </a>
                @endforeach
            </nav>

            <div class="mt-4 border-t border-white/10"></div>
            <p class="mt-4 px-1 text-[11px] font-bold tracking-[0.08em] text-[#17A45D] uppercase">{{ $isFr ? 'Gestion' : 'Management' }}</p>
            <nav class="mt-2">
                @foreach($sideManage as [$navIcon, $navLabel, $navUrl])
                <a href="{{ $navUrl }}" class="flex items-center gap-3.5 rounded-lg px-2.5 py-[9px] hover:bg-white/5">
                    <i data-lucide="{{ $navIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#CFE2D6]" style="stroke-width:1.7"></i>
                    <span class="flex-1 text-[13px] text-[#EAF2ED]">{{ $navLabel }}</span>
                </a>
                @endforeach
            </nav>

            <div class="mt-6 rounded-2xl border border-[#C89A3B] bg-[#12341F] p-4">
                <p class="flex items-center gap-3 text-[14px] font-bold text-white">
                    <i data-lucide="crown" class="w-[26px] h-[26px] text-[#E9B23C]" style="stroke-width:1.7"></i>
                    {{ $isFr ? 'Passez à Premium' : 'Go Premium' }}
                </p>
                <ul class="mt-3.5 space-y-2">
                    @foreach($premiumPerks as $perk)
                    <li class="flex items-center gap-2.5 text-[12px] text-[#DCE9E0]">
                        <i data-lucide="check" class="w-3.5 h-3.5 shrink-0 text-white" style="stroke-width:3"></i>
                        {{ $perk }}
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-4 block text-center bg-[#E3AF3D] hover:bg-[#F0BC4A] rounded-lg px-4 py-2.5 text-[13px] font-bold text-[#3A2A03] transition-colors">
                    {{ $isFr ? 'Découvrir les offres' : 'Discover the offers' }}
                </a>
            </div>

            <div class="mt-24 rounded-2xl border border-white/15 p-4">
                <p class="text-[14px] font-bold text-white">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</p>
                <p class="mt-1.5 text-[12px] text-[#A9C4B3]">{{ $isFr ? 'Notre équipe est là pour vous aider.' : 'Our team is here to help you.' }}</p>
                <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-3.5 inline-flex items-center gap-2.5 border border-white/25 hover:border-white/60 rounded-lg px-4 py-2 text-[12.5px] font-semibold text-white transition-colors">
                    <i data-lucide="headphones" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $isFr ? 'Nous contacter' : 'Contact us' }}
                </a>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 min-w-0 px-3 lg:px-1 pt-4">

        @if(session('success'))
        <div class="mb-4 bg-[#E2F3E8] border border-[#BFDCC8] rounded-xl px-4 py-3 flex items-center gap-3 text-[13px] text-[#14532D]">
            <i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#157A43]"></i>
            {{ session('success') }}
        </div>
        @endif

        <!-- Hello row -->
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-[28px] font-bold text-[#1B1B18]">👋 {{ $isFr ? 'Bonjour' : 'Hello' }}, {{ $firstName }} !</h1>
                <p class="mt-1 text-[13.5px] text-[#55524A]">{{ $isFr ? 'Voici un aperçu de votre activité sur la Galerie Virtuelle.' : 'Here is an overview of your activity on the Virtual Gallery.' }}</p>
            </div>
            <div class="shrink-0 bg-[#EFF5F0] rounded-2xl px-5 py-4 flex flex-wrap items-center gap-4">
                <img src="{{ asset('images/landing/qd-shield.png') }}" alt="" class="w-[42px] h-[48px] shrink-0" aria-hidden="true">
                <div class="min-w-0">
                    <p class="text-[14px] font-bold text-[#14532D]">{{ $isFr ? 'Compte vérifié' : 'Verified account' }}</p>
                    <p class="mt-0.5 text-[12px] text-[#3B382F]">{{ $isFr ? 'Votre profil est complet à 85%' : 'Your profile is 85% complete' }}</p>
                </div>
                <a href="{{ route('profile.show', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-white border border-[#DCE7DF] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#14532D] transition-colors">
                    {{ $isFr ? 'Voir mon profil' : 'View my profile' }}
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        <!-- Aperçu rapide -->
        <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
            <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Aperçu rapide' : 'Quick overview' }}</h2>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                @foreach($kpis as [$kIcon, $kColor, $kBg, $kLabel, $kValue, $kDelta, $kDeltaColor])
                <div class="border border-[#F0F1F0] rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <span class="w-[46px] h-[46px] shrink-0 rounded-xl flex items-center justify-center" style="background:{{ $kBg }}">
                            <i data-lucide="{{ $kIcon }}" class="w-[22px] h-[22px]" style="stroke-width:1.7;color:{{ $kColor }}"></i>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[11.5px] text-[#55524A] leading-snug">{{ $kLabel }}</span>
                            <span class="block mt-0.5 text-[22px] font-bold text-[#1B1B18] leading-none">{{ $kValue }}</span>
                        </span>
                    </div>
                    <p class="mt-2.5 text-[12px] font-semibold" style="color:{{ $kDeltaColor }}">{{ $kDelta }}</p>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Pipeline + Demandes récentes -->
        <div class="mt-3 flex flex-col xl:flex-row gap-3 items-stretch">
            <section class="flex-[1.18] min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Pipeline des demandes de devis' : 'Quote request pipeline' }}</h2>
                    <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                </div>
                <div class="mt-6 grid grid-cols-6">
                    @foreach($pipeStages as $psIdx => [$psLabel, $psColor, $psCount])
                    <div class="text-center">
                        <p class="h-[34px] flex items-end justify-center text-[11px] text-[#55524A] leading-tight whitespace-pre-line">{{ $psLabel }}</p>
                        <div class="mt-2.5 flex items-center">
                            <span class="flex-1 h-[3px] {{ $psIdx === 0 ? '' : '' }}" style="background:{{ $psIdx === 0 ? 'transparent' : $pipeSegs[$psIdx - 1] }}"></span>
                            <span class="w-[18px] h-[18px] shrink-0 rounded-full bg-white" style="border:4px solid {{ $psColor }}"></span>
                            <span class="flex-1 h-[3px]" style="background:{{ $psIdx === count($pipeStages) - 1 ? 'transparent' : $pipeSegs[$psIdx] }}"></span>
                        </div>
                        <p class="mt-3 text-[19px] font-bold text-[#1B1B18]">{{ $psCount }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 bg-[#F2F5F2] rounded-xl px-5 py-4 flex flex-wrap items-center gap-x-6 gap-y-3">
                    <div>
                        <p class="text-[12px] text-[#55524A]">{{ $isFr ? 'Taux de conversion' : 'Conversion rate' }}</p>
                        <p class="mt-0.5 text-[19px] font-bold text-[#1B1B18]">23%</p>
                        <p class="mt-0.5 text-[12px] text-[#55524A]">{{ $isFr ? 'Ce mois :' : 'This month:' }} <span class="font-semibold text-[#157A43]">+5% ↗</span></p>
                    </div>
                    <div class="hidden sm:block w-px self-stretch bg-[#DFE5DF]"></div>
                    <div class="min-w-0">
                        <p class="text-[12px] text-[#55524A]">{{ $isFr ? 'Valeur potentielle' : 'Potential value' }}</p>
                        <p class="mt-0.5 text-[19px] font-bold text-[#1B1B18] whitespace-nowrap">12,450,000 <span class="text-[#157A43]">FCFA</span></p>
                        <p class="mt-0.5 text-[12px] text-[#55524A]">{{ $isFr ? 'Ce mois' : 'This month' }}</p>
                    </div>
                    <img src="{{ asset('images/landing/qd-spark.png') }}" alt="" class="ml-auto h-[52px] object-contain" aria-hidden="true">
                </div>
            </section>

            <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Demandes récentes' : 'Recent requests' }}</h2>
                    <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir toutes' : 'See all' }}</a>
                </div>
                <div class="mt-2 divide-y divide-[#F1F2F1]">
                    @foreach($recentRequests as $rqRow)
                    @php
                        [$rqImg, $rqTitle, $rqPlace, $rqDate, $rqPill, $rqPillColor, $rqPillBg] = $rqRow;
                        $rqUrl = $rqRow[7] ?? route('messages.inbox', ['lang' => $lang]);
                    @endphp
                    <a href="{{ $rqUrl }}" class="flex items-center gap-3.5 py-3 group">
                        <img src="{{ asset('images/landing/' . $rqImg) }}" alt="" class="w-[42px] h-[42px] shrink-0 rounded-lg object-cover">
                        <span class="flex-1 min-w-0">
                            <span class="block text-[13px] font-bold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ $rqTitle }}</span>
                            <span class="block mt-0.5 text-[12px] text-[#6F6B60] whitespace-nowrap overflow-hidden text-ellipsis">{{ $rqPlace }} &nbsp; {{ $rqDate }}</span>
                        </span>
                        <span class="shrink-0 rounded-md px-2.5 py-1 text-[10px] font-bold tracking-[0.02em]" style="color:{{ $rqPillColor }};background:{{ $rqPillBg }}">{{ $rqPill }}</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 shrink-0 text-[#157A43] group-hover:translate-x-0.5"></i>
                    </a>
                    @endforeach
                </div>
            </section>
        </div>

        <!-- Activité + Performance -->
        <div class="mt-3 flex flex-col xl:flex-row gap-3 items-stretch">
            <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Activité récente' : 'Recent activity' }}</h2>
                    <a href="{{ route('notifications.index', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                </div>
                <div class="mt-2 divide-y divide-[#F1F2F1]">
                    @foreach($activities as [$acIcon, $acColor, $acBg, $acTitle, $acSub, $acTime, $acBadge, $acStars])
                    <div class="flex items-start gap-3.5 py-3.5">
                        <span class="w-[40px] h-[40px] shrink-0 rounded-xl flex items-center justify-center" style="background:{{ $acBg }}">
                            <i data-lucide="{{ $acIcon }}" class="w-[19px] h-[19px]" style="stroke-width:2;color:{{ $acColor }}"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-bold text-[#1B1B18] leading-snug">{{ $acTitle }}</p>
                            @if($acStars)
                            <p class="mt-0.5 text-[15px] tracking-[0.2em] text-[#F5B301] leading-none">★★★★★</p>
                            @else
                            <p class="mt-0.5 text-[12px] text-[#6F6B60] leading-snug">{{ $acSub }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 flex items-center gap-2 pt-0.5">
                            <span class="text-[11.5px] text-[#6F6B60]">{{ $acTime }}</span>
                            @if($acBadge)<span class="w-[17px] h-[17px] rounded-full bg-[#14652F] text-white text-[10px] font-bold flex items-center justify-center">{{ $acBadge }}</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Performance des produits' : 'Product performance' }}</h2>
                    <a href="{{ $storeUrl }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                </div>
                <div class="mt-3 space-y-4">
                    @foreach($perfRows as $pfIdx => [$pfImg, $pfName, $pfViews, $pfDevis, $pfPct])
                    <div class="flex items-center gap-3.5">
                        <span class="w-[22px] h-[22px] shrink-0 rounded-full text-white text-[11px] font-bold flex items-center justify-center" style="background:{{ $rankColors[$pfIdx % 5] }}">{{ $pfIdx + 1 }}</span>
                        <img src="{{ $pfImg }}" alt="" class="w-[44px] h-[44px] shrink-0 rounded-lg object-cover">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[13px] font-bold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ $pfName }}</p>
                                <p class="shrink-0 text-[12px] text-[#55524A]">{{ $isFr ? 'Devis' : 'Quotes' }} : {{ $pfDevis }}</p>
                            </div>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Vues' : 'Views' }} : {{ $pfViews }}</p>
                            <div class="mt-1.5 h-[5px] rounded-full bg-[#EEF0EE]"><div class="h-full rounded-full bg-[#14652F]" style="width:{{ $pfPct }}%"></div></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
        </div>

        <!-- Santé + Documents + Portefeuille -->
        <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-3 items-stretch">
            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Santé de votre boutique' : 'Your shop health' }}</h2>
                <div class="mt-4 flex items-center gap-5">
                    <div class="shrink-0 text-center">
                        <div class="relative w-[108px] h-[108px] rounded-full" style="background:conic-gradient(from -30deg, #B9BEB9 0 15%, #14652F 15% 100%)">
                            <div class="absolute inset-[13px] rounded-full bg-white flex items-center justify-center">
                                <span class="text-[24px] font-bold text-[#1B1B18]">85%</span>
                            </div>
                        </div>
                        <p class="mt-2.5 text-[13px] font-bold text-[#14532D]">{{ $isFr ? 'Excellente' : 'Excellent' }}</p>
                        <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Continuez ainsi !' : 'Keep it up!' }}</p>
                    </div>
                    <ul class="flex-1 min-w-0 space-y-2.5">
                        @foreach($healthChecks as [$hcLabel, $hcValue, $hcColor, $hcSuffix])
                        <li class="flex items-center gap-2.5">
                            <span class="w-[17px] h-[17px] shrink-0 rounded-full bg-[#DFF3E5] flex items-center justify-center">
                                <i data-lucide="check" class="w-2.5 h-2.5 text-[#14652F]" style="stroke-width:3.5"></i>
                            </span>
                            <span class="flex-1 text-[12px] text-[#3B382F]">{{ $hcLabel }}</span>
                            <span class="shrink-0 text-[12px] font-bold" style="color:{{ $hcColor }}">{{ $hcValue }}@if($hcSuffix)<span class="{{ $hcSuffix === '/20' ? 'text-[#ED7E1C]' : 'text-[#157A43]' }}">{{ $hcSuffix }}</span>@endif</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <a href="{{ $bizEditUrl }}" class="mt-5 block text-center border border-[#DCE7DF] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#14532D] transition-colors">
                    {{ $isFr ? 'Améliorer ma boutique' : 'Improve my shop' }}
                </a>
            </section>

            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Documents & Certifications' : 'Documents & Certifications' }}</h2>
                    <a href="{{ route('verification.show', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D] whitespace-nowrap">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                </div>
                <div class="mt-2 divide-y divide-[#F1F2F1]">
                    @foreach($docRows as [$dcIcon, $dcTitle, $dcSub, $dcPill, $dcColor, $dcBg])
                    <div class="flex items-center gap-3.5 py-3">
                        <span class="w-[40px] h-[40px] shrink-0 rounded-xl bg-[#E4F1E8] flex items-center justify-center">
                            <i data-lucide="{{ $dcIcon }}" class="w-[19px] h-[19px] text-[#14652F]" style="stroke-width:1.8"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12.5px] font-bold text-[#1B1B18] leading-snug">{{ $dcTitle }}</p>
                            <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">{{ $dcSub }}</p>
                        </div>
                        <span class="shrink-0 rounded-lg px-3 py-1.5 text-[11.5px] font-semibold" style="color:{{ $dcColor }};background:{{ $dcBg }}">{{ $dcPill }}</span>
                    </div>
                    @endforeach
                </div>
            </section>

            <section id="portefeuille" class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5 flex flex-col">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Portefeuille' : 'Wallet' }}</h2>
                    <a href="#portefeuille" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir détails' : 'See details' }}</a>
                </div>
                <p class="mt-4 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Solde disponible' : 'Available balance' }}</p>
                <p class="mt-1 flex items-center gap-3 text-[24px] font-bold text-[#0B3B22]">
                    450,000 <span class="text-[#157A43]">FCFA</span>
                    <i data-lucide="eye" class="w-5 h-5 ml-auto text-[#6F6B60]" style="stroke-width:1.7"></i>
                </p>
                <div class="mt-4 bg-[#EDF5EF] rounded-xl px-4 py-3.5 flex items-center gap-3.5">
                    <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-white flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:2"></i>
                    </span>
                    <div>
                        <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Prochain paiement prévu' : 'Next scheduled payment' }}</p>
                        <p class="mt-0.5 text-[12px] text-[#55524A]">{{ $isFr ? '15 Mai 2024' : '15 May 2024' }}</p>
                    </div>
                </div>
                <div class="mt-auto pt-5 flex items-center gap-3">
                    <a href="#portefeuille" class="flex-1 text-center border border-[#DCE7DF] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#14532D] transition-colors">{{ $isFr ? 'Historique' : 'History' }}</a>
                    <a href="{{ route('contact', ['lang' => $lang]) }}" class="flex-1 text-center bg-qddeep hover:bg-[#14652F] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-white transition-colors whitespace-nowrap">{{ $isFr ? 'Retirer des fonds' : 'Withdraw funds' }}</a>
                </div>
            </section>
        </div>

        <!-- Événements + Conseils -->
        <div class="mt-3 flex flex-col xl:flex-row gap-3 items-stretch">
            <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Événements & Opportunités' : 'Events & Opportunities' }}</h2>
                    <a href="{{ route('events.index', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                </div>
                <div class="mt-4 flex flex-col sm:flex-row gap-3 items-stretch">
                    <div class="flex-[1.35] bg-[#EBF2FC] rounded-xl p-4">
                        <div class="flex items-start gap-3.5">
                            <img src="{{ asset('images/landing/qd-siac.png') }}" alt="SIARC" class="w-[44px] h-[54px] shrink-0 rounded-md bg-white object-contain">
                            <div class="min-w-0">
                                <p class="text-[14.5px] font-bold text-[#1B1B18]">SIARC Cameroun 2024</p>
                                <p class="mt-1 text-[11.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? "Participez au Salon International de l'Aquaculture et de l'Artisanat" : 'Take part in the International Aquaculture and Crafts Fair' }}</p>
                                <p class="mt-2 flex items-center gap-2 text-[11.5px] text-[#3B382F]"><i data-lucide="calendar-days" class="w-3.5 h-3.5 text-[#3565DE]"></i> 05 - 10 {{ $isFr ? 'Juin' : 'June' }} 2024</p>
                                <p class="mt-1 flex items-center gap-2 text-[11.5px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#3565DE]"></i> {{ $isFr ? 'Douala, Cameroun' : 'Douala, Cameroon' }}</p>
                            </div>
                        </div>
                        <a href="{{ $siacUrl }}" class="mt-3.5 inline-block bg-qddeep hover:bg-[#14652F] rounded-lg px-5 py-2 text-[12.5px] font-semibold text-white transition-colors">{{ $isFr ? 'S\'inscrire' : 'Register' }}</a>
                    </div>
                    <div class="flex-1 bg-[#EFECFA] rounded-xl p-4 flex flex-col">
                        <i data-lucide="megaphone" class="w-[38px] h-[38px] text-[#5B4FD8] -scale-x-100" style="stroke-width:1.4"></i>
                        <p class="mt-4 text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Appels à candidatures' : 'Calls for applications' }}</p>
                        <p class="mt-1 text-[12px] text-[#3B382F]">{{ $isFr ? '3 opportunités disponibles' : '3 opportunities available' }}</p>
                        <a href="{{ route('events.index', ['lang' => $lang]) }}" class="mt-auto pt-3 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">
                            {{ $isFr ? 'Voir les opportunités' : 'See the opportunities' }}
                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </section>

            <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5 relative overflow-hidden">
                <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Conseils pour booster votre activité' : 'Tips to boost your activity' }}</h2>
                <img src="{{ asset('images/landing/qd-rocket.png') }}" alt="" class="absolute right-4 top-9 w-[110px] hidden sm:block" aria-hidden="true">
                <div class="mt-4 space-y-4 sm:pr-[130px]">
                    @foreach($tips as [$tpIcon, $tpTitle, $tpSub])
                    <div class="flex items-start gap-3.5">
                        <span class="w-[36px] h-[36px] shrink-0 rounded-lg bg-[#E4F1E8] flex items-center justify-center">
                            <i data-lucide="{{ $tpIcon }}" class="w-[17px] h-[17px] text-[#14652F]" style="stroke-width:1.8"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[13px] font-bold text-[#1B1B18] leading-snug">{{ $tpTitle }}</p>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60] leading-snug">{{ $tpSub }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-5 flex justify-end">
                    <a href="{{ route('support.index', ['lang' => $lang]) }}" class="inline-block border border-[#DCE7DF] hover:border-[#14532D] rounded-lg px-5 py-2.5 text-[13px] font-semibold text-[#14532D] transition-colors">{{ $isFr ? 'Voir tous les conseils' : 'See all the tips' }}</a>
                </div>
            </section>
        </div>

        <!-- Bottom bar -->
        <section class="mt-3 bg-[#EAF3EE] rounded-2xl px-5 py-4 flex flex-wrap items-center gap-4">
            <span class="w-[42px] h-[42px] shrink-0 rounded-full bg-white flex items-center justify-center">
                <i data-lucide="shield-check" class="w-[21px] h-[21px] text-[#14652F]" style="stroke-width:1.8"></i>
            </span>
            <p class="flex-1 min-w-[260px] text-[12.5px] text-[#3B382F] leading-relaxed">
                <span class="font-bold text-[#1B1B18]">{{ $isFr ? 'Votre profil est vérifié et visible par des acheteurs du monde entier.' : 'Your profile is verified and visible to buyers around the world.' }}</span><br>
                {{ $isFr ? 'Continuez à offrir des produits de qualité et à répondre aux demandes.' : 'Keep offering quality products and responding to requests.' }}
            </p>
            <a href="{{ $storeUrl }}" class="shrink-0 inline-flex items-center gap-2.5 bg-qddeep hover:bg-[#14652F] rounded-lg px-5 py-3 text-[13.5px] font-semibold text-white transition-colors">
                {{ $isFr ? 'Voir ma boutique publique' : 'View my public shop' }}
                <i data-lucide="external-link" class="w-4 h-4"></i>
            </a>
        </section>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
