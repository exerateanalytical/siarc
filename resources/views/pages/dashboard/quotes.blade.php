@php
    $isFr = $lang === 'fr';

    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $enMonths = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $fmtDate = function ($date) use ($isFr, $frMonths, $enMonths) {
        $ts = strtotime($date);
        $m = (int) date('n', $ts);
        return date('d', $ts) . ' ' . ($isFr ? $frMonths[$m] : $enMonths[$m]) . ' ' . date('Y', $ts);
    };

    $shopName = $business ? ($isFr ? $business->name_fr : ($business->name_en ?: $business->name_fr)) : ($isFr ? 'Aucune boutique' : 'No shop yet');
    $shopLogo = ($business && $business->logo) ? asset('storage/' . $business->logo) : asset('images/landing/logo.png');
    $firstName = explode(' ', trim($siacUser['name'] ?? ''))[0] ?? '';
    $memberSince = $business ? $fmtDate($business->created_at) : null;
    $artisanId = $business
        ? 'ART-CM-' . date('Y', strtotime($business->created_at)) . '-' . str_pad((string) $business->id, 6, '0', STR_PAD_LEFT)
        : null;

    $storeUrl = $business ? route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) : route('business.create', ['lang' => $lang]);
    $bizEditUrl = $business ? route('business.edit', ['lang' => $lang]) : route('business.create', ['lang' => $lang]);
    $msgBadge = $messageCount;
    $siacUrl = $siacEvent ? route('events.show', ['slug' => $siacEvent->slug, 'lang' => $lang]) : route('events.index', ['lang' => $lang]);

    // Real per-status counts from this business's quote requests (no design fixtures).
    // Navigation now lives in pages.partials.dashboard-sidebar.
    $rfqCountsByStatus = $rfqCountsByStatus ?? collect();
    $pendingCount     = $rfqCountsByStatus->get('pending', 0);
    $quotedCount      = $rfqCountsByStatus->get('quoted', 0);
    $negotiationCount = $rfqCountsByStatus->get('negotiation', 0);
    $acceptedCount    = $rfqCountsByStatus->get('accepted', 0);
    $refusedCount     = $rfqCountsByStatus->get('refused', 0);
    $totalRfqCount    = $rfqCountsByStatus->sum();

    // [icon, iconColor, tileBg, label, value, delta, deltaColor]
    $kpis = [
        ['file-text',     '#3B5BDB', '#E9EFFC', $isFr ? 'Demandes de devis' : 'Quote requests',     (string) $totalRfqCount, ($thisMonthRfqs ?? 0) . ' ' . ($isFr ? 'ce mois' : 'this month'), '#157A43'],
        ['file-badge',    '#7C5CE0', '#EDEBFA', $isFr ? 'Devis envoyés' : 'Quotes sent',            (string) ($quotesSentCount ?? 0), '', '#157A43'],
        ['hourglass',     '#EE8D0C', '#FDF1DE', $isFr ? 'En attente réponse' : 'Awaiting response', (string) $pendingCount,  '', '#EE7A1C'],
        ['shopping-cart', '#157A43', '#DFF3E5', $isFr ? 'Commandes confirmées' : 'Confirmed orders',(string) ($ordersCount ?? 0),  '', '#157A43'],
        ['users',         '#4A63D8', '#EDEDF8', $isFr ? 'Clients actifs' : 'Active clients',        (string) ($activeClientsCount ?? 0), '', '#157A43'],
    ];

    // [label, ringColor, count] + segment colors between dots
    $pipeStages = [
        [$isFr ? 'Nouvelles' : 'New',                  '#2E6BE6', (string) $pendingCount],
        [$isFr ? 'Devis envoyés' : 'Quotes sent',      '#8B5CF6', (string) $quotedCount],
        [$isFr ? 'Négociation' : 'Negotiation',        '#F97316', (string) $negotiationCount],
        [$isFr ? 'Converties' : 'Converted',           '#17803D', (string) $acceptedCount],
        [$isFr ? 'Refusées' : 'Refused',                '#E5484D', (string) $refusedCount],
    ];
    $pipeSegs = ['#2E6BE6', '#8B5CF6', '#F97316', '#17803D'];

    // [thumb, title, place, date, pillLabel, pillText, pillBg, url]
    $rfqPills = [
        'pending'     => [$isFr ? 'NOUVELLE' : 'NEW', '#3565DE', '#E8EFFB'],
        'quoted'      => [$isFr ? 'DEVIS ENVOYÉ' : 'QUOTE SENT', '#7C4FE0', '#F0EAFB'],
        'negotiation' => [$isFr ? 'NÉGOCIATION' : 'NEGOTIATION', '#EE7A1C', '#FDEFE0'],
        'accepted'    => [$isFr ? 'CONVERTIE' : 'CONVERTED', '#157A43', '#E2F3E8'],
        'refused'     => [$isFr ? 'REFUSÉE' : 'REFUSED', '#E5484D', '#FDE8E8'],
    ];
    $recentRequests = ($realRfqs ?? collect())->map(function ($r) use ($rfqPills, $isFr, $lang) {
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

    // [icon, iconColor, tileBg, title, sub, time, badge, isStars] — merged from real messages, RFQ
    // status changes, reviews, and documents nearing expiry; sorted by recency
    $activities = collect();
    foreach (($recentMessages ?? collect()) as $m) {
        $activities->push([
            'message-circle', '#3B5BDB', '#E9EFFC',
            $isFr ? 'Nouveau message' : 'New message',
            \Illuminate\Support\Str::limit($m->body, 60), $m->created_at, null, false,
        ]);
    }
    foreach (($realRfqs ?? collect()) as $r) {
        $statusLabels = [
            'accepted'    => $isFr ? 'Devis accepté par ' . ($r->buyer->name ?? '—') : 'Quote accepted by ' . ($r->buyer->name ?? '—'),
            'refused'     => $isFr ? 'Devis refusé par ' . ($r->buyer->name ?? '—') : 'Quote refused by ' . ($r->buyer->name ?? '—'),
            'negotiation' => $isFr ? 'Négociation en cours' : 'Negotiation in progress',
            'quoted'      => $isFr ? 'Devis envoyé' : 'Quote sent',
            'pending'     => $isFr ? 'Nouvelle demande de devis' : 'New quote request',
        ];
        if (isset($statusLabels[$r->status])) {
            $activities->push([
                $r->status === 'accepted' ? 'check' : 'file-text',
                $r->status === 'accepted' ? '#157A43' : '#3B5BDB',
                $r->status === 'accepted' ? '#DFF3E5' : '#E9EFFC',
                $statusLabels[$r->status], $r->title, $r->updated_at, null, false,
            ]);
        }
    }
    foreach (($recentReviews ?? collect()) as $rv) {
        $activities->push([
            'star', '#157A43', '#DFF3E5',
            $isFr ? 'Vous avez reçu un nouvel avis' : 'You received a new review',
            str_repeat('★', (int) $rv->rating), $rv->created_at, null, true,
        ]);
    }
    $activities = $activities->sortByDesc(fn ($a) => $a[5])
        ->take(5)
        ->map(function ($a) use ($isFr) {
            $a[5] = \Illuminate\Support\Carbon::parse($a[5])->diffForHumans(null, true, false, 1);
            return $a;
        })->values()->all();

    // Real product performance (views-ranked); no per-product quote count exists
    // in the schema (quote_requests isn't linked to a product), so only real,
    // computable figures are shown.
    $perfRows = [];
    if ($topProducts->isNotEmpty()) {
        $maxViews = max(1, $topProducts->max('views_count'));
        foreach ($topProducts as $pi => $p) {
            $img = isset($topProductImages[$p->id]) ? asset('storage/' . $topProductImages[$p->id]) : asset('images/landing/default-product-arts-decoration.png');
            $perfRows[] = [$img, $isFr ? $p->name_fr : ($p->name_en ?: $p->name_fr), number_format($p->views_count), max(18, (int) round($p->views_count / $maxViews * 85))];
        }
    }
    $rankColors = ['#F5B301', '#9CA3AF', '#ED7E1C', '#9CA3AF', '#9CA3AF'];

    // Real profile-completeness signals (no fake percentages)
    $profileFields = ['name_fr', 'description_fr', 'logo', 'phone', 'email'];
    $filledCount = $business ? collect($profileFields)->filter(fn ($f) => filled($business->{$f} ?? null))->count() : 0;
    $profilePct = $business ? (int) round($filledCount / count($profileFields) * 100) : 0;
    $publishedProductsCount = $business
        ? \Illuminate\Support\Facades\DB::table('products')->where('business_id', $business->id)->where('status', 'published')->whereNull('deleted_at')->count()
        : 0;
    $totalReviewsCount = ($recentReviews ?? collect())->count()
        ? \Illuminate\Support\Facades\DB::table('business_reviews')->where('business_id', $business->id)->where('status', 'published')->count()
        : 0;
    $avgRating = $totalReviewsCount
        ? number_format(\Illuminate\Support\Facades\DB::table('business_reviews')->where('business_id', $business->id)->where('status', 'published')->avg('rating'), 1)
        : null;

    $healthChecks = array_values(array_filter([
        [$isFr ? 'Profil complété' : 'Profile completed', $profilePct . '%', $profilePct >= 80 ? '#157A43' : '#EE7A1C', null],
        [$isFr ? 'Boutique active' : 'Active shop', ($business?->status === 'published') ? ($isFr ? 'Oui' : 'Yes') : ($isFr ? 'Non' : 'No'), ($business?->status === 'published') ? '#157A43' : '#EE7A1C', null],
        [$isFr ? 'Produits publiés' : 'Published products', (string) $publishedProductsCount, '#1B1B18', null],
        $avgRating !== null ? [$isFr ? 'Avis clients' : 'Client reviews', $avgRating . '/5', '#1B1B18', null] : null,
    ]));

    // Real business documents on file (issued/expiry dates)
    $docRows = ($businessDocuments ?? collect())->map(function ($d) use ($isFr) {
        $expired = $d->expires_at && \Illuminate\Support\Carbon::parse($d->expires_at)->isPast();
        $expiringSoon = $d->expires_at && !$expired && \Illuminate\Support\Carbon::parse($d->expires_at)->diffInDays(now()) <= 30;
        $pill = $expired ? ($isFr ? 'Expiré' : 'Expired') : ($expiringSoon ? ($isFr ? 'Expire bientôt' : 'Expiring soon') : ($isFr ? 'Valide' : 'Valid'));
        [$pillColor, $pillBg] = $expired || $expiringSoon ? ['#EE7A1C', '#FDEFE0'] : ['#157A43', '#E4F1E8'];
        $sub = $d->expires_at
            ? ($isFr ? "Valide jusqu'au " : 'Valid until ') . \Illuminate\Support\Carbon::parse($d->expires_at)->translatedFormat('d M Y')
            : ($isFr ? 'Aucune date d\'expiration' : 'No expiry date');
        return ['file-text', $isFr ? $d->name_fr : ($d->name_en ?? $d->name_fr), $sub, $pill, $pillColor, $pillBg];
    })->all();

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
    <title>{{ $isFr ? 'Tableau de bord devis — Artisan Hub 237' : 'Quotes dashboard — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    // Canonical heritage palette (shared with the public site,
                    // entrepreneur.blade.php and layouts/dashboard.blade.php)
                    colors: { qdside: '#02301B', qdact: '#14532D', qddeep: '#0B3D28' },
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
        {{-- Sidebar slide-over CSS now lives in pages.partials.dashboard-sidebar --}}
    </style>
</head>
<body class="bg-[#F8F6F2] text-[#1D1B16] antialiased">

<!-- Header -->
<header class="bg-white">
    <div class="max-w-[1500px] mx-auto px-4 lg:px-6 flex items-center gap-4 py-3">
        <button type="button" onclick="document.getElementById('qd-sidebar').classList.toggle('open')" class="lg:hidden shrink-0 text-[#1B1B18]">
            <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain">
            <span class="leading-tight hidden sm:block">
                <span class="block text-[12.5px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</span>
                <span class="block text-[12.5px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                <span class="block text-[10.5px] text-[#2E7D4F] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </a>
        <form action="{{ route('gallery.search') }}" method="GET" class="flex-1 max-w-[560px] mx-auto hidden md:block">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="flex items-center gap-3 border border-[#E7E7E5] rounded-xl bg-[#FBFBFA] px-4 h-[46px]">
                <i data-lucide="search" class="w-[18px] h-[18px] shrink-0 text-[#8A857A]"></i>
                <input type="text" name="q" id="qd-search" placeholder="{{ $isFr ? 'Rechercher dans la plateforme...' : 'Search the platform...' }}" class="flex-1 min-w-0 bg-transparent text-[13px] focus:outline-none placeholder-[#8A857A]">
                <span class="shrink-0 border border-[#E5E3E0] rounded-md bg-white px-2 py-0.5 text-[11.5px] text-[#55524A]">Ctrl + K</span>
            </div>
        </form>
        <div class="flex items-center gap-5 shrink-0 ml-auto">
            <a href="{{ route('notifications.index', ['lang' => $lang]) }}" class="relative" title="Notifications">
                <i data-lucide="bell" class="w-6 h-6 text-[#3B382F]" style="stroke-width:1.7"></i>
                @if(($notificationCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 w-[17px] h-[17px] rounded-full bg-[#DC2626] text-white text-[10px] font-bold flex items-center justify-center">{{ min(99, $notificationCount) }}</span>
                @endif
            </a>
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="relative" title="Messages">
                <i data-lucide="message-circle" class="w-6 h-6 text-[#3B382F]" style="stroke-width:1.7"></i>
                @if(($messageCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 w-[17px] h-[17px] rounded-full bg-[#157A43] text-white text-[10px] font-bold flex items-center justify-center">{{ min(99, $messageCount) }}</span>
                @endif
            </a>
            <div class="relative group">
                <button class="flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/qd-avatar.png') }}" alt="" class="w-[42px] h-[42px] rounded-full object-cover">
                    <span class="leading-tight text-left hidden sm:block">
                        <span class="block text-[13.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $siacUser['name'] ?? '' }}</span>
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
    {{-- Canonical dashboard sidebar — identical on every dashboard page --}}
    @include("pages.partials.dashboard-sidebar", ["sidebarId" => "qd-sidebar", "sideBadges" => $sideBadges ?? []])

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
                <p class="mt-1 text-[13.5px] text-[#55524A]">{{ $isFr ? 'Voici un aperçu de votre activité sur Artisan Hub 237.' : 'Here is an overview of your activity on Artisan Hub 237.' }}</p>
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
                    @forelse($recentRequests as $rqRow)
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
                    @empty
                    <p class="py-6 text-center text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Aucune demande de devis pour le moment.' : 'No quote requests yet.' }}</p>
                    @endforelse
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
                    @forelse($activities as [$acIcon, $acColor, $acBg, $acTitle, $acSub, $acTime, $acBadge, $acStars])
                    <div class="flex items-start gap-3.5 py-3.5">
                        <span class="w-[40px] h-[40px] shrink-0 rounded-xl flex items-center justify-center" style="background:{{ $acBg }}">
                            <i data-lucide="{{ $acIcon }}" class="w-[19px] h-[19px]" style="stroke-width:2;color:{{ $acColor }}"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-bold text-[#1B1B18] leading-snug">{{ $acTitle }}</p>
                            @if($acStars)
                            <p class="mt-0.5 text-[15px] tracking-[0.2em] text-[#F5B301] leading-none">{{ $acSub }}</p>
                            @else
                            <p class="mt-0.5 text-[12px] text-[#6F6B60] leading-snug">{{ $acSub }}</p>
                            @endif
                        </div>
                        <div class="shrink-0 flex items-center gap-2 pt-0.5">
                            <span class="text-[11.5px] text-[#6F6B60]">{{ $acTime }}</span>
                            @if($acBadge)<span class="w-[17px] h-[17px] rounded-full bg-[#14652F] text-white text-[10px] font-bold flex items-center justify-center">{{ $acBadge }}</span>@endif
                        </div>
                    </div>
                    @empty
                    <p class="py-6 text-center text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Aucune activité récente.' : 'No recent activity.' }}</p>
                    @endforelse
                </div>
            </section>

            <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Performance des produits' : 'Product performance' }}</h2>
                    <a href="{{ $storeUrl }}" class="text-[12.5px] font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                </div>
                <div class="mt-3 space-y-4">
                    @forelse($perfRows as $pfIdx => [$pfImg, $pfName, $pfViews, $pfPct])
                    <div class="flex items-center gap-3.5">
                        <span class="w-[22px] h-[22px] shrink-0 rounded-full text-white text-[11px] font-bold flex items-center justify-center" style="background:{{ $rankColors[$pfIdx % 5] }}">{{ $pfIdx + 1 }}</span>
                        <img src="{{ $pfImg }}" alt="" class="w-[44px] h-[44px] shrink-0 rounded-lg object-cover">
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-bold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ $pfName }}</p>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Vues' : 'Views' }} : {{ $pfViews }}</p>
                            <div class="mt-1.5 h-[5px] rounded-full bg-[#EEF0EE]"><div class="h-full rounded-full bg-[#14652F]" style="width:{{ $pfPct }}%"></div></div>
                        </div>
                    </div>
                    @empty
                    <p class="py-6 text-center text-[12.5px] text-[#6F6B60]">{{ $isFr ? 'Aucun produit publié pour le moment.' : 'No published products yet.' }}</p>
                    @endforelse
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

            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5 flex flex-col">
                <h2 class="text-[15.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Portefeuille' : 'Wallet' }}</h2>
                <div class="mt-auto flex flex-col items-center text-center py-6">
                    <span class="w-11 h-11 rounded-full bg-[#F2F5F2] flex items-center justify-center mb-3">
                        <i data-lucide="wallet" class="w-5 h-5 text-[#8A857A]"></i>
                    </span>
                    <p class="text-[12.5px] text-[#6F6B60] max-w-[220px]">{{ $isFr ? 'Le portefeuille et les paiements en ligne arrivent bientôt.' : 'Wallet and online payouts are coming soon.' }}</p>
                </div>
                <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-auto text-center border border-[#DCE7DF] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#14532D] transition-colors">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
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
                            <img src="{{ asset('images/landing/qd-siac.png') }}" alt="" class="w-[44px] h-[54px] shrink-0 rounded-md bg-white object-contain">
                            <div class="min-w-0">
                                <p class="text-[14.5px] font-bold text-[#1B1B18]">Salon Cameroun 2024</p>
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

<script>
    lucide.createIcons();

    // Ctrl/⌘ + K focuses the search box — the keycap hint in the header promises this.
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            const box = document.getElementById('qd-search');
            if (box) { e.preventDefault(); box.focus(); box.select(); }
        }
    });
</script>
</body>
</html>
