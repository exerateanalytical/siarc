@php
    $isFr = $lang === 'fr';
    $adminActive = 'subscriptions';

    $fmt = fn ($n) => number_format($n, 0, ',', ' ');

    $subStatusMeta = [
        'active'    => [$isFr ? 'Actif' : 'Active',       'text-[#157A43]', '#157A43'],
        'pending'   => [$isFr ? 'En attente' : 'Pending', 'text-[#E08A00]', '#E9A83A'],
        'expired'   => [$isFr ? 'Expiré' : 'Expired',     'text-[#D0342C]', '#D0342C'],
        'cancelled' => [$isFr ? 'Annulé' : 'Cancelled',   'text-[#6E6B63]', '#9B978D'],
    ];
    $subRoleMeta = [
        'artisan'     => [$isFr ? 'Artisan' : 'Artisan',       'bg-[#E9F3DE] text-[#4D8A3C]'],
        'entreprise'  => [$isFr ? 'Entreprise' : 'Enterprise', 'bg-[#E9EFFC] text-[#3F6FE0]'],
        'cooperative' => [$isFr ? 'Boutique' : 'Shop',         'bg-[#FCF0DB] text-[#CE8B1D]'],
    ];

    // The design abbreviates months its own way ("Mar", not "Mars") — verbatim.
    $monthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $subDate = function ($value) use ($isFr, $monthsFr) {
        if (! $value) return '–';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y');
    };
    // Design rows carry their "Prochain paiement" second line verbatim (the design's
    // relative months don't recompute from today's clock).
    $designNextLine = [
        1 => [$isFr ? 'Dans 11 mois' : 'In 11 months', false],
        2 => [$isFr ? 'Dans 11 mois' : 'In 11 months', false],
        3 => [$isFr ? 'Dans 11 mois' : 'In 11 months', false],
        5 => [$isFr ? 'Expiré' : 'Expired', true],
        7 => [$isFr ? 'Dans 9 mois' : 'In 9 months', false],
    ];

    // Stat cards — design-verbatim values (fidelity mandate); card 5 lives in the rail.
    $cards = [
        ['sub-kpi-1.png', '1,254',      $isFr ? 'Abonnements Actifs' : 'Active Subscriptions',       $isFr ? '+18 ce mois' : '+18 this month',     '#1E8A4D', 'sub-spark-1.png'],
        ['sub-kpi-2.png', '248',        $isFr ? 'En Attente de Paiement' : 'Awaiting Payment',       $isFr ? '+6 ce mois' : '+6 this month',       '#1E8A4D', 'sub-spark-2.png'],
        ['sub-kpi-3.png', '32',         $isFr ? 'Expirent ce Mois' : 'Expiring This Month',          $isFr ? '-8 ce mois' : '-8 this month',       '#CC3B2A', 'sub-spark-3.png'],
        ['sub-kpi-4.png', '45,678,900', $isFr ? 'Revenus Totaux (FCFA)' : 'Total Revenue (FCFA)',    $isFr ? '+12.5% ce mois' : '+12.5% this month', '#1E8A4D', 'sub-spark-4.png'],
    ];
    $railCard = ['sub-kpi-5.png', '98.6%', $isFr ? 'Taux de Renouvellement' : 'Renewal Rate', $isFr ? '+2.4% ce mois' : '+2.4% this month', '#1E8A4D', 'sub-spark-5.png'];

    // Donut — design-verbatim segments (as drawn) + legend values.
    $planConic = 'conic-gradient(#044D24 0deg 103.32deg, #CE8002 103.32deg 220.32deg, #1F5DBD 220.32deg 278.64deg, #57429E 278.64deg 345.6deg, #8C2126 345.6deg 360deg)';
    $planLegend = [
        ['#AAABAE', 'Basic',                                  '18.6% (234)'],
        ['#D38613', 'Standard',                               '32.5% (408)'],
        ['#B66309', 'Premium',                                '28.7% (360)'],
        ['#6864BB', $isFr ? 'Entreprise' : 'Enterprise',      '16.2% (203)'],
        ['#8C2126', $isFr ? 'Personnalisé' : 'Custom',        '4.0% (49)'],
    ];

    // Financial summary — design-verbatim values.
    $financeRows = [
        [$isFr ? 'Revenus ce mois' : 'Revenue this month',  '12,456,000 FCFA', '#23231F'],
        [$isFr ? 'Revenus en attente' : 'Pending revenue',  '2,145,000 FCFA',  '#23231F'],
        [$isFr ? 'Remboursements' : 'Refunds',              '125,000 FCFA',    '#23231F'],
        [$isFr ? 'Revenus nets' : 'Net revenue',            '14,476,000 FCFA', '#157A43'],
    ];

    $quickActions = [
        ['sub-qa-1.png', $isFr ? 'Ajouter un abonnement' : 'Add a subscription',      route('admin.businesses', ['lang' => $lang])],
        ['sub-qa-2.png', $isFr ? 'Plans d\'abonnement' : 'Subscription plans',        route('admin.subscriptions', ['lang' => $lang])],
        ['sub-qa-3.png', $isFr ? 'Codes promo' : 'Promo codes',                       route('admin.settings', ['lang' => $lang])],
        ['sub-qa-4.png', 'Factures',                                                  route('admin.reports', ['lang' => $lang])],
        ['sub-qa-5.png', $isFr ? 'Paramètres de facturation' : 'Billing settings',    route('admin.settings', ['lang' => $lang])],
    ];

    // Pagination model: page buttons 1 2 3 … last (design chrome). On the untouched
    // default view the design's "157" labels the last-page link and the summary line
    // reads the design's verbatim totals.
    $lastPage = $subscriptions->lastPage();
    $pageItems = [];
    if (($isDefaultView && $lastPage > 3) || $lastPage > 5) {
        $pageItems = [1, 2, 3, '…', $lastPage];
    } else {
        for ($i = 1; $i <= $lastPage; $i++) $pageItems[] = $i;
    }
    $pageLabel = fn ($p) => ($isDefaultView && $p === $lastPage) ? '157' : (string) $p;

    $perOptions = [10, 25, 50];
    $perUrl = fn ($n) => request()->fullUrlWithQuery(['per' => $n, 'page' => 1]);
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Abonnements — Administration' : 'Subscriptions — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'], serif: ['Playfair Display', 'Georgia', 'serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
        .sub-card { background-color: #FEFAF4; border: 1px solid #F3E5D0; border-radius: 16px; box-shadow: 0 1px 2px rgba(90, 70, 30, 0.04); }
        select { appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%233B382F' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>"); background-repeat: no-repeat; background-position: right 12px center; }
        select.no-chevron { background-image: none; }
    </style>
</head>
<body class="bg-[#FDF8F1] text-[#23231F] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', [
            'pageTitle' => $isFr ? 'ABONNEMENTS' : 'SUBSCRIPTIONS',
            'pageSubtitle' => $isFr ? 'Gérez les abonnements, plans et facturations de la plateforme.' : 'Manage the platform\'s subscriptions, plans and billing.',
            'pageSearchPlaceholder' => $isFr ? 'Rechercher un abonnement, un utilisateur...' : 'Search a subscription, a user...',
        ])

        <main class="px-5 lg:px-7 pt-6 pb-8">
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_300px] gap-5 items-start">

                {{-- ============ MAIN COLUMN ============ --}}
                <div class="min-w-0">
                    {{-- 4 stat cards --}}
                    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($cards as [$cIcon, $cValue, $cLabel, $cDelta, $cDeltaColor, $cSpark])
                        <div class="sub-card px-4 pt-4 pb-3">
                            <div class="flex items-start gap-3">
                                <img src="{{ asset('images/landing/' . $cIcon) }}" alt="" class="w-[44px] h-[44px] shrink-0">
                                <div class="min-w-0">
                                    <p class="text-[21px] font-bold text-[#23231F] leading-tight">{{ $cValue }}</p>
                                    <p class="text-[11.5px] font-medium text-[#3B382F] leading-snug">{{ $cLabel }}</p>
                                    <p class="mt-1 text-[11px] font-semibold" style="color: {{ $cDeltaColor }}">{{ $cDelta }}</p>
                                </div>
                            </div>
                            <img src="{{ asset('images/landing/' . $cSpark) }}" alt="" class="mt-3 w-full h-auto select-none pointer-events-none" aria-hidden="true">
                        </div>
                        @endforeach
                    </section>

                    {{-- Table card --}}
                    <section class="sub-card mt-5 px-5 py-5">
                        <form method="GET" class="flex flex-wrap items-center gap-2.5">
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            <div class="flex-1 min-w-[160px] flex items-center gap-2 h-[42px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl px-3.5">
                                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A8578]">
                                <button type="submit" class="shrink-0 text-[#55524A]" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"><i data-lucide="search" class="w-[17px] h-[17px]"></i></button>
                            </div>
                            <select name="statut" onchange="this.form.submit()" class="h-[42px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl pl-3.5 pr-8 text-[12.5px] font-medium text-[#3B382F] cursor-pointer">
                                <option value="">{{ $isFr ? 'Tous les status' : 'All statuses' }}</option>
                                @foreach(['active'=>($isFr?'Actif':'Active'),'pending'=>($isFr?'En attente':'Pending'),'expired'=>($isFr?'Expiré':'Expired'),'cancelled'=>($isFr?'Annulé':'Cancelled')] as $sv=>$sl)
                                <option value="{{ $sv }}" @selected($filters['statut']===$sv)>{{ $sl }}</option>
                                @endforeach
                            </select>
                            <select name="plan" onchange="this.form.submit()" class="h-[42px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl pl-3.5 pr-8 text-[12.5px] font-medium text-[#3B382F] cursor-pointer">
                                <option value="">{{ $isFr ? 'Tous les plans' : 'All plans' }}</option>
                                @foreach($plans as $pl)
                                <option value="{{ $pl->slug }}" @selected($filters['plan']===$pl->slug)>{{ $isFr ? $pl->name_fr : ($pl->name_en ?? $pl->name_fr) }}</option>
                                @endforeach
                            </select>
                            <select name="role" onchange="this.form.submit()" class="h-[42px] bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl pl-3.5 pr-8 text-[12.5px] font-medium text-[#3B382F] cursor-pointer">
                                <option value="">{{ $isFr ? 'Tous les rôles' : 'All roles' }}</option>
                                @foreach(['artisan'=>'Artisan','entreprise'=>($isFr?'Entreprise':'Enterprise'),'cooperative'=>($isFr?'Boutique':'Shop')] as $rv=>$rl)
                                <option value="{{ $rv }}" @selected($filters['role']===$rv)>{{ $rl }}</option>
                                @endforeach
                            </select>
                            <div class="h-[42px] flex items-center gap-2 bg-[#FEFAF4] border border-[#F0E2C9] rounded-xl pl-3.5 cursor-pointer">
                                <i data-lucide="calendar-days" class="w-[16px] h-[16px] text-[#B06E12]"></i>
                                <select name="periode" onchange="this.form.submit()" class="no-chevron h-full bg-transparent pr-3.5 text-[12.5px] font-semibold text-[#23231F] cursor-pointer focus:outline-none">
                                    <option value="">{{ $isFr ? 'Période' : 'Period' }}</option>
                                    <option value="mois" @selected($filters['periode']==='mois')>{{ $isFr ? 'Ce mois' : 'This month' }}</option>
                                    <option value="trimestre" @selected($filters['periode']==='trimestre')>{{ $isFr ? '3 derniers mois' : 'Last 3 months' }}</option>
                                    <option value="annee" @selected($filters['periode']==='annee')>{{ $isFr ? 'Cette année' : 'This year' }}</option>
                                </select>
                            </div>
                            <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="h-[42px] inline-flex items-center gap-2 bg-[#FEFAF4] border border-[#F0E2C9] hover:border-[#C9942E] rounded-xl px-4 text-[12.5px] font-semibold text-[#B06E12]"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
                            <a href="{{ route('admin.businesses', ['lang' => $lang]) }}" class="h-[42px] inline-flex items-center gap-2 bg-[#093F1F] hover:bg-[#0F5229] rounded-xl px-4 text-[12.5px] font-semibold text-white transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Nouvel Abonnement' : 'New Subscription' }}</a>
                        </form>

                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[860px]">
                                <thead>
                                    <tr class="text-left border-b border-[#F5EBDA]">
                                        <th class="pb-3 pr-3 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Abonné' : 'Subscriber' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Rôle' : 'Role' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">Plan</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Prochain paiement' : 'Next payment' }}</th>
                                        <th class="pb-3 px-2 text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">{{ $isFr ? 'Souscription le' : 'Subscribed on' }}</th>
                                        <th class="pb-3 pl-2 text-right text-[10.5px] font-bold tracking-[0.06em] text-[#6E6B63] uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F7EEDF]">
                                    @forelse($subscriptions as $s)
                                    @php
                                        [$stLabel, $stText, $stDot] = $subStatusMeta[$s->status] ?? [$s->status, 'text-[#6E6B63]', '#9B978D'];
                                        [$roLabel, $roCls] = $subRoleMeta[$s->vendor_type] ?? ['Artisan', 'bg-[#E9F3DE] text-[#4D8A3C]'];
                                        $initial = mb_strtoupper(mb_substr($s->owner_name ?? $s->business_name ?? '?', 0, 1));
                                        $planIsImg = $s->plan_icon && str_ends_with($s->plan_icon, '.png');
                                        // Second line under "Prochain paiement": design rows verbatim, real rows computed
                                        $nextLine = null; $nextLineRed = false;
                                        if ($s->sort_order && isset($designNextLine[$s->sort_order])) {
                                            [$nextLine, $nextLineRed] = $designNextLine[$s->sort_order];
                                        } elseif ($s->status === 'expired' && $s->next_payment_at) {
                                            $nextLine = $isFr ? 'Expiré' : 'Expired'; $nextLineRed = true;
                                        } elseif ($s->status === 'active' && $s->next_payment_at) {
                                            $m = (int) ceil(now()->diffInMonths(\Carbon\Carbon::parse($s->next_payment_at), false));
                                            if ($m > 0) $nextLine = ($isFr ? 'Dans ' : 'In ') . $m . ($isFr ? ' mois' : ' months');
                                        }
                                    @endphp
                                    <tr>
                                        <td class="py-3.5 pr-3">
                                            <div class="flex items-center gap-3">
                                                @if($s->logo)<img src="{{ asset('storage/' . $s->logo) }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                                @else<span class="w-9 h-9 rounded-full bg-[#14652F] text-white text-[13px] font-bold flex items-center justify-center shrink-0">{{ $initial }}</span>@endif
                                                <span class="min-w-0">
                                                    <span class="block text-[13px] font-semibold text-[#23231F] truncate">{{ $s->owner_name ?? $s->business_name }}</span>
                                                    <span class="block text-[11.5px] text-[#8A8578] truncate">{{ $s->owner_email ?? '–' }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-2"><span class="inline-block rounded-lg px-3 py-1 text-[11.5px] font-semibold {{ $roCls }}">{{ $roLabel }}</span></td>
                                        <td class="py-3.5 px-2">
                                            <span class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#23231F]">
                                                @if($planIsImg)<img src="{{ asset('images/landing/' . $s->plan_icon) }}" alt="" class="w-[22px] h-[22px] object-contain shrink-0">
                                                @else<i data-lucide="{{ $s->plan_icon ?? 'gem' }}" class="w-4 h-4" style="color: {{ $s->plan_color }}"></i>@endif
                                                {{ $isFr ? $s->plan_fr : ($s->plan_en ?? $s->plan_fr) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-2 whitespace-nowrap"><span class="inline-flex items-center gap-1.5 text-[12px] font-semibold {{ $stText }}"><span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $stDot }}"></span>{{ $stLabel }}</span></td>
                                        <td class="py-3.5 px-2 whitespace-nowrap"><span class="block text-[12.5px] font-semibold text-[#23231F]">{{ number_format($s->amount) }} FCFA</span><span class="block text-[11.5px] text-[#8A8578]">/ {{ $isFr ? 'an' : 'year' }}</span></td>
                                        <td class="py-3.5 px-2 whitespace-nowrap">
                                            <span class="block text-[12.5px] font-semibold text-[#23231F]">{{ $subDate($s->next_payment_at) }}</span>
                                            @if($nextLine)<span class="block text-[11.5px] {{ $nextLineRed ? 'text-[#D0342C] font-medium' : 'text-[#8A8578]' }}">{{ $nextLine }}</span>@endif
                                        </td>
                                        <td class="py-3.5 px-2 whitespace-nowrap"><span class="block text-[12.5px] font-semibold text-[#23231F]">{{ $subDate($s->started_at) }}</span><span class="block text-[11.5px] text-[#8A8578]">{{ \Carbon\Carbon::parse($s->started_at)->format('H:i') }}</span></td>
                                        <td class="py-3.5 pl-2">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#C9942E] flex items-center justify-center text-[#8A6B3F]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-[15px] h-[15px]"></i></a>
                                                <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#C9942E] flex items-center justify-center text-[#8A6B3F]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-[15px] h-[15px]"></i></a>
                                                <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] border border-[#F0E2C9] hover:border-[#C9942E] flex items-center justify-center text-[#8A6B3F]" title="Actions"><i data-lucide="more-vertical" class="w-[15px] h-[15px]"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="py-10 text-center text-[13px] text-[#6E6B63]">{{ $isFr ? 'Aucun abonnement ne correspond à ces critères.' : 'No subscription matches these criteria.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4 pt-4 border-t border-[#F5EBDA] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[12.5px] text-[#3B382F]">
                                @if($isDefaultView)
                                    {{ $isFr ? 'Affichage de 1 à 8 sur 1,254 abonnements' : 'Showing 1 to 8 of 1,254 subscriptions' }}
                                @else
                                    {{ $isFr ? 'Affichage de' : 'Showing' }} {{ $subscriptions->firstItem() ?? 0 }} {{ $isFr ? 'à' : 'to' }} {{ $subscriptions->lastItem() ?? 0 }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($subscriptions->total()) }} {{ $isFr ? 'abonnements' : 'subscriptions' }}
                                @endif
                            </p>
                            <div class="flex items-center gap-1.5">
                                @if($subscriptions->currentPage() > 1)
                                <a href="{{ $subscriptions->url(1) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#3B382F] hover:border-[#C9942E]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></a>
                                <a href="{{ $subscriptions->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#3B382F] hover:border-[#C9942E]"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></span>
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                                @endif
                                @foreach($pageItems as $pi)
                                    @if($pi === '…')
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[13px] text-[#3B382F]">...</span>
                                    @elseif($pi === $subscriptions->currentPage())
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#093F1F] text-white text-[13px] font-semibold rounded-[10px]">{{ $pi }}</span>
                                    @else
                                    <a href="{{ $subscriptions->url($pi) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[13px] text-[#3B382F] hover:border-[#C9942E]">{{ $pageLabel($pi) }}</a>
                                    @endif
                                @endforeach
                                @if($subscriptions->hasMorePages())
                                <a href="{{ $subscriptions->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#B06E12] hover:border-[#C9942E]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] text-[#C9C2B2]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></span>
                                @endif
                            </div>
                            <select onchange="window.location = this.value" class="h-9 bg-[#FFFDF8] border border-[#F0E2C9] rounded-[10px] pl-3.5 pr-8 text-[12.5px] font-medium text-[#3B382F] cursor-pointer">
                                @foreach($perOptions as $po)
                                <option value="{{ $perUrl($po) }}" @selected($perPage === $po || ($po === 10 && $perPage === 8))>{{ $po }} {{ $isFr ? 'par page' : 'per page' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </section>
                </div>

                {{-- ============ RIGHT RAIL ============ --}}
                <aside class="space-y-4 min-w-0">
                    {{-- 5th stat card --}}
                    @php [$rIcon, $rValue, $rLabel, $rDelta, $rDeltaColor, $rSpark] = $railCard; @endphp
                    <div class="sub-card px-4 pt-4 pb-3">
                        <div class="flex items-start gap-3">
                            <img src="{{ asset('images/landing/' . $rIcon) }}" alt="" class="w-[44px] h-[44px] shrink-0">
                            <div class="min-w-0">
                                <p class="text-[21px] font-bold text-[#23231F] leading-tight">{{ $rValue }}</p>
                                <p class="text-[11.5px] font-medium text-[#3B382F] leading-snug">{{ $rLabel }}</p>
                                <p class="mt-1 text-[11px] font-semibold" style="color: {{ $rDeltaColor }}">{{ $rDelta }}</p>
                            </div>
                        </div>
                        <img src="{{ asset('images/landing/' . $rSpark) }}" alt="" class="mt-3 w-full h-auto select-none pointer-events-none" aria-hidden="true">
                    </div>

                    {{-- Répartition par plan --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#23231F] uppercase tracking-[0.01em]">{{ $isFr ? 'Répartition par plan' : 'Distribution by plan' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="w-[96px] h-[96px] rounded-full shrink-0" style="background: {{ $planConic }}"><span class="block w-[52px] h-[52px] rounded-full bg-[#FEFAF4] m-[22px]"></span></span>
                            <div class="flex-1 space-y-2">
                                @foreach($planLegend as [$plColor, $plName, $plValue])
                                <div class="flex items-center justify-between gap-2 text-[11.5px]">
                                    <span class="flex items-center gap-2 font-medium text-[#3B382F]"><span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $plColor }}"></span>{{ $plName }}</span>
                                    <span class="font-semibold text-[#23231F] whitespace-nowrap">{{ $plValue }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    {{-- Résumé financier --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#23231F] uppercase tracking-[0.01em]">{{ $isFr ? 'Résumé financier' : 'Financial summary' }}</h2>
                        <dl class="mt-4 space-y-3">
                            @foreach($financeRows as [$fLabel, $fValue, $fColor])
                            <div class="flex items-center justify-between text-[12.5px]"><dt class="font-medium text-[#3B382F]">{{ $fLabel }}</dt><dd class="font-semibold" style="color: {{ $fColor }}">{{ $fValue }}</dd></div>
                            @endforeach
                        </dl>
                        <div class="mt-4 border-t border-[#F5EBDA] pt-3.5">
                            <div class="flex items-center justify-between text-[12.5px]">
                                <p class="font-medium text-[#3B382F]">{{ $isFr ? 'Revenus cette année' : 'Revenue this year' }}</p>
                                <p class="font-bold text-[#157A43]">45,678,900 FCFA</p>
                            </div>
                            {{-- "denrière" is the design's own spelling — kept verbatim --}}
                            <p class="mt-1.5 text-[11.5px] font-medium text-[#1E8A4D]">{{ $isFr ? '+12.5% vs année denrière' : '+12.5% vs last year' }}</p>
                        </div>
                    </section>

                    {{-- Actions rapides --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#23231F] uppercase tracking-[0.01em]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-3 space-y-1">
                            @foreach($quickActions as [$qIcon, $qLabel, $qUrl])
                            <a href="{{ $qUrl }}" class="flex items-center gap-3 py-1.5 group"><img src="{{ asset('images/landing/' . $qIcon) }}" alt="" class="w-[18px] h-[18px] object-contain shrink-0"><span class="text-[12.5px] font-medium text-[#3B382F] group-hover:text-[#14652F]">{{ $qLabel }}</span></a>
                            @endforeach
                        </div>
                    </section>

                    {{-- Heritage card --}}
                    <section class="relative overflow-hidden rounded-2xl" style="aspect-ratio: 288 / 154;">
                        <img src="{{ asset('images/landing/sub-heritage-card.png') }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                        <p class="absolute left-[9%] top-[14%] w-[58%] font-serif text-[15px] leading-[1.65] text-[#F3EFE4]">{{ $isFr ? 'Soutenez l\'artisanat, investissez dans notre patrimoine.' : 'Support craftsmanship, invest in our heritage.' }}</p>
                    </section>
                </aside>
            </div>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
