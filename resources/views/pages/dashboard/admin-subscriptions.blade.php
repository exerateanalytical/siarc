@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'subscriptions';

    $pageTitle = $isFr ? 'ABONNEMENTS' : 'SUBSCRIPTIONS';
    $pageSubtitle = $isFr ? 'Gérez les abonnements, plans et facturations de la plateforme.' : 'Manage the platform\'s subscriptions, plans and billing.';
    $pageSearchPlaceholder = $isFr ? 'Rechercher un abonnement, un utilisateur...' : 'Search a subscription, a user...';

    $fmt = fn ($n) => number_format($n, 0, ',', ' ');

    $subStatusMeta = [
        'active'    => [$isFr ? 'Actif' : 'Active',       'text-[#157A43] dark:text-[#339B56]', '#157A43'],
        'pending'   => [$isFr ? 'En attente' : 'Pending', 'text-[#E08A00] dark:text-[#EDB33A]', '#E9A83A'],
        'expired'   => [$isFr ? 'Expiré' : 'Expired',     'text-[#D0342C] dark:text-[#F0555C]', '#D0342C'],
        'cancelled' => [$isFr ? 'Annulé' : 'Cancelled',   'text-[#6E6B63] dark:text-[#868778]', '#9B978D'],
    ];
    $subRoleMeta = [
        'artisan'     => [$isFr ? 'Artisan' : 'Artisan',       'bg-[#E9F3DE] dark:bg-[#0C3D1D] text-[#4D8A3C] dark:text-[#339B56]'],
        'entreprise'  => [$isFr ? 'Entreprise' : 'Enterprise', 'bg-[#E9EFFC] dark:bg-[#101C33] text-[#3F6FE0] dark:text-[#8FB6F5]'],
        'cooperative' => [$isFr ? 'Boutique' : 'Shop',         'bg-[#FCF0DB] dark:bg-[#3A2B06] text-[#CE8B1D] dark:text-[#EDB33A]'],
    ];

    // The design abbreviates months its own way ("Mar", not "Mars") — verbatim.
    $monthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $subDate = function ($value) use ($isFr, $monthsFr) {
        if (! $value) return '–';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y');
    };
    // Stat cards — real counts from $subStats; card 5 lives in the rail. The design's
    // month-over-month deltas had no prior-period source, so they are not rendered.
    // Icon chips: [lucide name, chip fill, glyph colour] — the design's raster
    // tiles redrawn as stroked lucide glyphs so they stay crisp at any size and
    // carry no baked-in white edge on the dark theme.
    $cards = [
        [['users', '#033F21', '#FFFFFF'],              number_format($subStats['active']),   $isFr ? 'Abonnements Actifs' : 'Active Subscriptions',    'sub-spark-1.png'],
        [['clock', '#FBE5C0', '#7A4A0C'],              number_format($subStats['pending']),  $isFr ? 'En Attente de Paiement' : 'Awaiting Payment',    'sub-spark-2.png'],
        [['calendar-clock', '#753B05', '#FFFFFF'],     number_format($subStats['expiring']), $isFr ? 'Expirent ce Mois' : 'Expiring This Month',       'sub-spark-3.png'],
        [['circle-dollar-sign', '#0B6234', '#FFFFFF'], number_format($subStats['revenue']),  $isFr ? 'Revenus Totaux (FCFA)' : 'Total Revenue (FCFA)', 'sub-spark-4.png'],
    ];
    $railCard = [['refresh-cw', '#41136F', '#FFFFFF'], $subStats['renewal'] . '%', $isFr ? 'Taux de Renouvellement' : 'Renewal Rate', 'sub-spark-5.png'];

    // Donut + legend — real plan distribution ($planDist), plan colours from the plans table.
    $planTotal = max(1, (int) $planDist->sum('n'));
    $planFallback = ['#044D24', '#CE8002', '#1F5DBD', '#57429E', '#8C2126'];
    $planLegend = []; $planSegs = []; $planAngle = 0;
    foreach ($planDist as $pi => $pd) {
        $color = $pd->color ?: $planFallback[$pi % 5];
        $to = $planAngle + ((int) $pd->n / $planTotal) * 360;
        if ((int) $pd->n > 0) {
            $planSegs[] = sprintf('%s %.2fdeg %.2fdeg', $color, $planAngle, $to);
            $planAngle = $to;
        }
        $planLegend[] = [$color, $isFr ? $pd->name_fr : ($pd->name_en ?? $pd->name_fr),
            round((int) $pd->n / $planTotal * 100, 1) . '% (' . (int) $pd->n . ')'];
    }
    $planConic = $planSegs ? 'conic-gradient(' . implode(', ', $planSegs) . ')' : '#EFEAE0';

    // Financial summary — real sums from $finance.
    $financeRows = [
        [$isFr ? 'Revenus ce mois' : 'Revenue this month',  number_format($finance['this_month']) . ' FCFA', '#23231F'],
        [$isFr ? 'Revenus en attente' : 'Pending revenue',  number_format($finance['pending']) . ' FCFA',    '#23231F'],
        [$isFr ? 'Remboursements' : 'Refunds',              number_format($finance['refunds']) . ' FCFA',    '#23231F'],
        [$isFr ? 'Revenus nets' : 'Net revenue',            number_format($finance['net']) . ' FCFA',        '#157A43'],
    ];

    $quickActions = [
        ['plus',           $isFr ? 'Ajouter un abonnement' : 'Add a subscription',      route('admin.businesses', ['lang' => $lang])],
        ['clipboard-list', $isFr ? 'Plans d\'abonnement' : 'Subscription plans',        route('admin.subscriptions', ['lang' => $lang])],
        ['ticket',         $isFr ? 'Codes promo' : 'Promo codes',                       route('admin.settings', ['lang' => $lang])],
        ['file-text',      'Factures',                                                  route('admin.reports', ['lang' => $lang])],
        ['settings',       $isFr ? 'Paramètres de facturation' : 'Billing settings',    route('admin.settings', ['lang' => $lang])],
    ];

    // Pagination model: page buttons 1 2 3 … last (design chrome), driven by the
    // real paginator — the design's "157" last-page label was not a real page count.
    $lastPage = $subscriptions->lastPage();
    $pageItems = [];
    if ($lastPage > 5) {
        $pageItems = [1, 2, 3, '…', $lastPage];
    } else {
        for ($i = 1; $i <= $lastPage; $i++) $pageItems[] = $i;
    }

    $perOptions = [10, 25, 50];
    $perUrl = fn ($n) => request()->fullUrlWithQuery(['per' => $n, 'page' => 1]);
@endphp

@section('content')
            <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_300px] gap-5 items-start">

                {{-- ============ MAIN COLUMN ============ --}}
                <div class="min-w-0">
                    {{-- 4 stat cards --}}
                    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($cards as [$cIcon, $cValue, $cLabel, $cSpark])
                        <div class="sub-card px-4 pt-4 pb-3">
                            <div class="flex items-start gap-3">
                                <span class="w-[44px] h-[44px] shrink-0 rounded-xl grid place-items-center" style="background:{{ $cIcon[1] }};color:{{ $cIcon[2] }}" aria-hidden="true"><i data-lucide="{{ $cIcon[0] }}" class="w-[22px] h-[22px]" stroke-width="1.9"></i></span>
                                <div class="min-w-0">
                                    <p class="text-[21px] font-bold text-[#23231F] dark:text-[#F3EFE7] leading-tight">{{ $cValue }}</p>
                                    <p class="text-[11.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6] leading-snug">{{ $cLabel }}</p>
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
                            <div class="ui-field-group flex-1 min-w-[160px]">
                                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}" class="ui-field-bare flex-1 min-w-0">
                                <button type="submit" class="shrink-0 text-[#55524A] dark:text-[#B4B5A6]" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"><i data-lucide="search" class="w-[17px] h-[17px]"></i></button>
                            </div>
                            <select name="statut" onchange="this.form.submit()" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Tous les status' : 'All statuses' }}</option>
                                @foreach(['active'=>($isFr?'Actif':'Active'),'pending'=>($isFr?'En attente':'Pending'),'expired'=>($isFr?'Expiré':'Expired'),'cancelled'=>($isFr?'Annulé':'Cancelled')] as $sv=>$sl)
                                <option value="{{ $sv }}" @selected($filters['statut']===$sv)>{{ $sl }}</option>
                                @endforeach
                            </select>
                            <select name="plan" onchange="this.form.submit()" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Tous les plans' : 'All plans' }}</option>
                                @foreach($plans as $pl)
                                <option value="{{ $pl->slug }}" @selected($filters['plan']===$pl->slug)>{{ $isFr ? $pl->name_fr : ($pl->name_en ?? $pl->name_fr) }}</option>
                                @endforeach
                            </select>
                            <select name="role" onchange="this.form.submit()" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Tous les rôles' : 'All roles' }}</option>
                                @foreach(['artisan'=>'Artisan','entreprise'=>($isFr?'Entreprise':'Enterprise'),'cooperative'=>($isFr?'Boutique':'Shop')] as $rv=>$rl)
                                <option value="{{ $rv }}" @selected($filters['role']===$rv)>{{ $rl }}</option>
                                @endforeach
                            </select>
                            <div class="ui-field-group">
                                <i data-lucide="calendar-days" class="w-[16px] h-[16px]"></i>
                                <select name="periode" onchange="this.form.submit()" class="ui-field-bare ui-select">
                                    <option value="">{{ $isFr ? 'Période' : 'Period' }}</option>
                                    <option value="mois" @selected($filters['periode']==='mois')>{{ $isFr ? 'Ce mois' : 'This month' }}</option>
                                    <option value="trimestre" @selected($filters['periode']==='trimestre')>{{ $isFr ? '3 derniers mois' : 'Last 3 months' }}</option>
                                    <option value="annee" @selected($filters['periode']==='annee')>{{ $isFr ? 'Cette année' : 'This year' }}</option>
                                </select>
                            </div>
                            <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-lg"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
                            <a href="{{ route('admin.businesses', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary ui-btn-lg"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Nouvel Abonnement' : 'New Subscription' }}</a>
                        </form>

                        <div class="ui-table-wrap mt-4">
                            <table class="ui-table min-w-[860px]">
                                <thead>
                                    <tr class="border-b border-[#F5EBDA] dark:border-[#262B21]">
                                        <th>{{ $isFr ? 'Abonné' : 'Subscriber' }}</th>
                                        <th>{{ $isFr ? 'Rôle' : 'Role' }}</th>
                                        <th>Plan</th>
                                        <th>{{ $isFr ? 'Statut' : 'Status' }}</th>
                                        <th>{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                        <th>{{ $isFr ? 'Prochain paiement' : 'Next payment' }}</th>
                                        <th>{{ $isFr ? 'Souscription le' : 'Subscribed on' }}</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subscriptions as $s)
                                    @php
                                        [$stLabel, $stText, $stDot] = $subStatusMeta[$s->status] ?? [$s->status, 'text-[#6E6B63] dark:text-[#868778]', '#9B978D'];
                                        [$roLabel, $roCls] = $subRoleMeta[$s->vendor_type] ?? ['Artisan', 'bg-[#E9F3DE] dark:bg-[#0C3D1D] text-[#4D8A3C] dark:text-[#339B56]'];
                                        $initial = mb_strtoupper(mb_substr($s->owner_name ?? $s->business_name ?? '?', 0, 1));
                                        // Plans seeded before the icon set moved to lucide still carry a
                                        // raster filename (sub-plan-*.png). Every one of those was a gem
                                        // in the plan's own colour, so they resolve to lucide `gem`
                                        // rather than rendering a fixed-colour PNG on a dark page.
                                        $planIcon = (! $s->plan_icon || str_ends_with($s->plan_icon, '.png')) ? 'gem' : $s->plan_icon;
                                        // Second line under "Prochain paiement" — computed from the row's real
                                        // next_payment_at (the design's fixed "Dans 11 mois" labels were invented)
                                        $nextLine = null; $nextLineRed = false;
                                        if ($s->status === 'expired' && $s->next_payment_at) {
                                            $nextLine = $isFr ? 'Expiré' : 'Expired'; $nextLineRed = true;
                                        } elseif ($s->status === 'active' && $s->next_payment_at) {
                                            $m = (int) ceil(now()->diffInMonths(\Carbon\Carbon::parse($s->next_payment_at), false));
                                            if ($m > 0) $nextLine = ($isFr ? 'Dans ' : 'In ') . $m . ($isFr ? ' mois' : ' months');
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @if($s->logo)<img src="{{ asset('storage/' . $s->logo) }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                                @else<span class="w-9 h-9 rounded-full bg-[#14652F] dark:bg-[#2E9250] text-white dark:text-[#04150A] text-[13px] font-bold flex items-center justify-center shrink-0">{{ $initial }}</span>@endif
                                                <span class="min-w-0">
                                                    <span class="block text-[13px] font-semibold text-[#23231F] dark:text-[#F3EFE7] truncate">{{ $s->owner_name ?? $s->business_name }}</span>
                                                    <span class="block text-[11.5px] text-[#8A8578] dark:text-[#868778] truncate">{{ $s->owner_email ?? '–' }}</span>
                                                </span>
                                            </div>
                                        </td>
                                        <td><span class="inline-block rounded-lg px-3 py-1 text-[11.5px] font-semibold {{ $roCls }}">{{ $roLabel }}</span></td>
                                        <td>
                                            <span class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7]">
                                                <i data-lucide="{{ $planIcon }}" class="w-[18px] h-[18px] shrink-0" style="color: {{ $s->plan_color }}" aria-hidden="true"></i>
                                                {{ $isFr ? $s->plan_fr : ($s->plan_en ?? $s->plan_fr) }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap"><span class="inline-flex items-center gap-1.5 text-[12px] font-semibold {{ $stText }}"><span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $stDot }}"></span>{{ $stLabel }}</span></td>
                                        <td class="whitespace-nowrap"><span class="block text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7]">{{ number_format($s->amount) }} FCFA</span><span class="block text-[11.5px] text-[#8A8578] dark:text-[#868778]">/ {{ $isFr ? 'an' : 'year' }}</span></td>
                                        <td class="whitespace-nowrap">
                                            <span class="block text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7]">{{ $subDate($s->next_payment_at) }}</span>
                                            @if($nextLine)<span class="block text-[11.5px] {{ $nextLineRed ? 'text-[#D0342C] dark:text-[#F0555C] font-medium' : 'text-[#8A8578] dark:text-[#868778] ' }}">{{ $nextLine }}</span>@endif
                                        </td>
                                        <td class="whitespace-nowrap"><span class="block text-[12.5px] font-semibold text-[#23231F] dark:text-[#F3EFE7]">{{ $subDate($s->started_at) }}</span><span class="block text-[11.5px] text-[#8A8578] dark:text-[#868778]">{{ \Carbon\Carbon::parse($s->started_at)->format('H:i') }}</span></td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1.5">
                                                <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#C9942E] dark:hover:border-[#E9A81E] flex items-center justify-center text-[#8A6B3F] dark:text-[#EDB33A]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-[15px] h-[15px]"></i></a>
                                                <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#C9942E] dark:hover:border-[#E9A81E] flex items-center justify-center text-[#8A6B3F] dark:text-[#EDB33A]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-[15px] h-[15px]"></i></a>
                                                <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-[34px] h-[34px] rounded-[10px] bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] hover:border-[#C9942E] dark:hover:border-[#E9A81E] flex items-center justify-center text-[#8A6B3F] dark:text-[#EDB33A]" title="Actions"><i data-lucide="more-vertical" class="w-[15px] h-[15px]"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="8" class="py-10 text-center text-[13px] text-[#6E6B63] dark:text-[#868778]">{{ $isFr ? 'Aucun abonnement ne correspond à ces critères.' : 'No subscription matches these criteria.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="mt-4 pt-4 border-t border-[#F5EBDA] dark:border-[#262B21] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[12.5px] text-[#3B382F] dark:text-[#B4B5A6]">
                                {{ $isFr ? 'Affichage de' : 'Showing' }} {{ $subscriptions->firstItem() ?? 0 }} {{ $isFr ? 'à' : 'to' }} {{ $subscriptions->lastItem() ?? 0 }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($subscriptions->total()) }} {{ $isFr ? 'abonnements' : 'subscriptions' }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                @if($subscriptions->currentPage() > 1)
                                <a href="{{ $subscriptions->url(1) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></a>
                                <a href="{{ $subscriptions->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevrons-left" class="w-4 h-4"></i></span>
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                                @endif
                                @foreach($pageItems as $pi)
                                    @if($pi === '…')
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[13px] text-[#3B382F] dark:text-[#B4B5A6]">...</span>
                                    @elseif($pi === $subscriptions->currentPage())
                                    <span class="w-9 h-9 flex items-center justify-center bg-[#093F1F] dark:bg-[#0C3B1E] text-white text-[13px] font-semibold rounded-[10px]">{{ $pi }}</span>
                                    @else
                                    <a href="{{ $subscriptions->url($pi) }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[13px] text-[#3B382F] dark:text-[#B4B5A6] hover:border-[#C9942E] dark:hover:border-[#E9A81E]">{{ $pi }}</a>
                                    @endif
                                @endforeach
                                @if($subscriptions->hasMorePages())
                                <a href="{{ $subscriptions->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#B06E12] dark:text-[#EDB33A] hover:border-[#C9942E] dark:hover:border-[#E9A81E]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></a>
                                @else
                                <span class="w-9 h-9 flex items-center justify-center bg-[#FFFDF8] dark:bg-[#12150F] border border-[#F0E2C9] dark:border-[#4A3A12] rounded-[10px] text-[#C9C2B2] dark:text-[#868778]"><i data-lucide="chevrons-right" class="w-4 h-4"></i></span>
                                @endif
                            </div>
                            <select onchange="window.location = this.value" class="ui-field ui-select">
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
                    @php [$rIcon, $rValue, $rLabel, $rSpark] = $railCard; @endphp
                    <div class="sub-card px-4 pt-4 pb-3">
                        <div class="flex items-start gap-3">
                            <span class="w-[44px] h-[44px] shrink-0 rounded-xl grid place-items-center" style="background:{{ $rIcon[1] }};color:{{ $rIcon[2] }}" aria-hidden="true"><i data-lucide="{{ $rIcon[0] }}" class="w-[22px] h-[22px]" stroke-width="1.9"></i></span>
                            <div class="min-w-0">
                                <p class="text-[21px] font-bold text-[#23231F] dark:text-[#F3EFE7] leading-tight">{{ $rValue }}</p>
                                <p class="text-[11.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6] leading-snug">{{ $rLabel }}</p>
                            </div>
                        </div>
                        <img src="{{ asset('images/landing/' . $rSpark) }}" alt="" class="mt-3 w-full h-auto select-none pointer-events-none" aria-hidden="true">
                    </div>

                    {{-- Répartition par plan --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="ui-card-title">{{ $isFr ? 'Répartition par plan' : 'Distribution by plan' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="w-[96px] h-[96px] rounded-full shrink-0" style="background: {{ $planConic }}"><span class="block w-[52px] h-[52px] rounded-full bg-[#FEFAF4] dark:bg-[#12150F] m-[22px]"></span></span>
                            <div class="flex-1 space-y-2">
                                @forelse($planLegend as [$plColor, $plName, $plValue])
                                <div class="flex items-center justify-between gap-2 text-[11.5px]">
                                    <span class="flex items-center gap-2 font-medium text-[#3B382F] dark:text-[#B4B5A6]"><span class="w-2 h-2 rounded-full shrink-0" style="background-color: {{ $plColor }}"></span>{{ $plName }}</span>
                                    <span class="font-semibold text-[#23231F] dark:text-[#F3EFE7] whitespace-nowrap">{{ $plValue }}</span>
                                </div>
                                @empty
                                <p class="text-[11.5px] text-[#6E6B63] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée pour le moment.' : 'No data yet.' }}</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    {{-- Résumé financier --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="ui-card-title">{{ $isFr ? 'Résumé financier' : 'Financial summary' }}</h2>
                        <dl class="mt-4 space-y-3">
                            @foreach($financeRows as [$fLabel, $fValue, $fColor])
                            <div class="flex items-center justify-between text-[12.5px]"><dt class="font-medium text-[#3B382F] dark:text-[#B4B5A6]">{{ $fLabel }}</dt><dd class="font-semibold" style="color: {{ $fColor }}">{{ $fValue }}</dd></div>
                            @endforeach
                        </dl>
                        <div class="mt-4 border-t border-[#F5EBDA] dark:border-[#262B21] pt-3.5">
                            <div class="flex items-center justify-between text-[12.5px]">
                                <p class="font-medium text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? 'Revenus cette année' : 'Revenue this year' }}</p>
                                <p class="font-bold text-[#157A43] dark:text-[#339B56]">{{ number_format($finance['year']) }} FCFA</p>
                            </div>
                            {{-- The design's year-over-year delta line is gone: no prior-year figure exists --}}
                        </div>
                    </section>

                    {{-- Actions rapides --}}
                    <section class="sub-card px-5 py-5">
                        <h2 class="ui-card-title">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-3 space-y-1">
                            @foreach($quickActions as [$qIcon, $qLabel, $qUrl])
                            <a href="{{ $qUrl }}" class="flex items-center gap-3 py-1.5 group"><i data-lucide="{{ $qIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#8A5A16] dark:text-[#EDB33A]" stroke-width="1.9" aria-hidden="true"></i><span class="text-[12.5px] font-medium text-[#3B382F] dark:text-[#B4B5A6] group-hover:text-[#14652F] dark:group-hover:text-[#339B56]">{{ $qLabel }}</span></a>
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

            <style>
                .sub-card { background-color: #FEFAF4; border: 1px solid #F3E5D0; border-radius: 16px; box-shadow: 0 1px 2px rgba(90, 70, 30, 0.04); }
            </style>
@endsection
