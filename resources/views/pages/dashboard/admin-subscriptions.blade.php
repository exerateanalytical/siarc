@php
    $isFr = $lang === 'fr';
    $adminActive = 'subscriptions';

    $fmt = fn ($n) => number_format($n, 0, ',', ' ');

    $subStatusMeta = [
        'active'    => [$isFr ? 'Actif' : 'Active',       'text-[#157A43]', '#157A43'],
        'pending'   => [$isFr ? 'En attente' : 'Pending', 'text-[#C97A16]', '#E9A83A'],
        'expired'   => [$isFr ? 'Expiré' : 'Expired',     'text-[#DC2626]', '#DC2626'],
        'cancelled' => [$isFr ? 'Annulé' : 'Cancelled',   'text-[#8A857A]', '#8A857A'],
    ];
    $subRoleMeta = [
        'artisan'     => [$isFr ? 'Artisan' : 'Artisan',       'bg-[#E2F3E8] text-[#157A43]'],
        'entreprise'  => [$isFr ? 'Entreprise' : 'Enterprise', 'bg-[#E8EFFB] text-[#3565DE]'],
        'cooperative' => [$isFr ? 'Boutique' : 'Shop',         'bg-[#FDF3E0] text-[#C97A16]'],
    ];

    $monthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $subDate = function ($value) use ($isFr, $monthsFr) {
        if (! $value) return '—';
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $monthsFr[$d->month], $d->year) : $d->format('d M Y');
    };

    $cards = [
        ['users-round', '#157A43', '#E8F2EC', $fmt($subStats['active']),  $isFr ? 'Abonnements Actifs' : 'Active Subscriptions',   $isFr ? '+18 ce mois' : '+18 this month', '#3FA96A'],
        ['clock',       '#C97A16', '#FDF3E0', $fmt($subStats['pending']), $isFr ? 'En Attente de Paiement' : 'Awaiting Payment',   $isFr ? '+6 ce mois' : '+6 this month',   '#E9A83A'],
        ['calendar-x',  '#9B1C31', '#F6E7E4', $fmt($subStats['expiring']),$isFr ? 'Expirent ce Mois' : 'Expiring This Month',      $isFr ? '-8 ce mois' : '-8 this month',   '#DC2626'],
        ['circle-dollar-sign', '#0E6B37', '#E2F3E8', $fmt($subStats['revenue']), $isFr ? 'Revenus Totaux (FCFA)' : 'Total Revenue (FCFA)', $isFr ? '+12.5% ce mois' : '+12.5% this month', '#157A43'],
        ['repeat',      '#7C4FE0', '#F0EAFB', $subStats['renewal'] . '%', $isFr ? 'Taux de Renouvellement' : 'Renewal Rate',        $isFr ? '+2.4% ce mois' : '+2.4% this month', '#7C4FE0'],
    ];

    $planTotal = max(1, $planDist->sum('n'));
    // Build conic-gradient for the donut from real plan counts
    $angle = 0; $segs = [];
    foreach ($planDist as $pd) {
        $to = $angle + ($pd->n / $planTotal) * 360;
        $segs[] = sprintf('%s %.2fdeg %.2fdeg', $pd->color ?? '#C9BFA8', $angle, $to);
        $angle = $to;
    }
    $planConic = 'conic-gradient(' . implode(', ', $segs) . ')';

    $financeRows = [
        [$isFr ? 'Revenus ce mois' : 'Revenue this month', $fmt($finance['this_month']) . ' FCFA', '#1B1B18'],
        [$isFr ? 'Revenus en attente' : 'Pending revenue',  $fmt($finance['pending']) . ' FCFA',    '#1B1B18'],
        [$isFr ? 'Remboursements' : 'Refunds',              $fmt($finance['refunds']) . ' FCFA',    '#DC2626'],
        [$isFr ? 'Revenus nets' : 'Net revenue',            $fmt($finance['net']) . ' FCFA',        '#157A43'],
    ];

    $quickActions = [
        ['plus',          $isFr ? 'Ajouter un abonnement' : 'Add a subscription', route('admin.businesses', ['lang' => $lang])],
        ['layout-grid',   $isFr ? 'Plans d\'abonnement' : 'Subscription plans',   route('admin.subscriptions', ['lang' => $lang])],
        ['ticket-percent',$isFr ? 'Codes promo' : 'Promo codes',                  route('admin.settings', ['lang' => $lang])],
        ['file-text',     'Factures',                                             route('admin.reports', ['lang' => $lang])],
        ['settings',      $isFr ? 'Paramètres de facturation' : 'Billing settings', route('admin.settings', ['lang' => $lang])],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Abonnements — Administration' : 'Subscriptions — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-heritage-header', [
            'pageTitle' => $isFr ? 'ABONNEMENTS' : 'SUBSCRIPTIONS',
            'pageSubtitle' => $isFr ? 'Gérez les abonnements, plans et facturations de la plateforme.' : 'Manage the platform\'s subscriptions, plans and billing.',
            'pageSearchPlaceholder' => $isFr ? 'Rechercher un abonnement, un utilisateur...' : 'Search a subscription, a user...',
        ])

        <main class="px-5 lg:px-7 pt-5 pb-8">

            {{-- Stat cards --}}
            <section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
                @foreach($cards as [$cIcon, $cColor, $cTile, $cValue, $cLabel, $cSub, $cSpark])
                <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4 flex flex-col">
                    <span class="w-[44px] h-[44px] rounded-xl flex items-center justify-center" style="background-color: {{ $cTile }}"><i data-lucide="{{ $cIcon }}" class="w-[21px] h-[21px]" style="color: {{ $cColor }};stroke-width:1.8"></i></span>
                    <p class="mt-3 text-[22px] font-bold text-[#1B1B18] leading-none">{{ $cValue }}</p>
                    <p class="mt-1 text-[12px] font-semibold text-[#3B382F]">{{ $cLabel }}</p>
                    <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ $cSub }}</p>
                    <svg viewBox="0 0 120 26" class="mt-2 w-full h-[24px]" preserveAspectRatio="none" aria-hidden="true"><polyline points="0,19 15,15 30,17 45,9 60,13 75,6 90,10 105,4 120,7" fill="none" stroke="{{ $cSpark }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/></svg>
                </div>
                @endforeach
            </section>

            <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_320px] gap-5 items-start">
                {{-- Table --}}
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <form method="GET" class="flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex-1 min-w-[160px] flex items-center gap-2 h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3.5">
                            <i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i>
                            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}" class="flex-1 min-w-0 text-[12.5px] focus:outline-none placeholder-[#8A857A]">
                        </div>
                        <select name="statut" onchange="this.form.submit()" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les status' : 'All statuses' }}</option>
                            @foreach(['active'=>($isFr?'Actif':'Active'),'pending'=>($isFr?'En attente':'Pending'),'expired'=>($isFr?'Expiré':'Expired'),'cancelled'=>($isFr?'Annulé':'Cancelled')] as $sv=>$sl)
                            <option value="{{ $sv }}" @selected($filters['statut']===$sv)>{{ $sl }}</option>
                            @endforeach
                        </select>
                        <select name="plan" onchange="this.form.submit()" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les plans' : 'All plans' }}</option>
                            @foreach($plans as $pl)
                            <option value="{{ $pl->slug }}" @selected($filters['plan']===$pl->slug)>{{ $isFr ? $pl->name_fr : ($pl->name_en ?? $pl->name_fr) }}</option>
                            @endforeach
                        </select>
                        <select name="role" onchange="this.form.submit()" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                            <option value="">{{ $isFr ? 'Tous les rôles' : 'All roles' }}</option>
                            @foreach(['artisan'=>($isFr?'Artisan':'Artisan'),'entreprise'=>($isFr?'Entreprise':'Enterprise'),'cooperative'=>($isFr?'Boutique':'Shop')] as $rv=>$rl)
                            <option value="{{ $rv }}" @selected($filters['role']===$rv)>{{ $rl }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="h-[40px] inline-flex items-center gap-2 bg-[#FBF6EA] border border-[#EAD9AC] hover:border-[#C9942E] rounded-lg px-3.5 text-[12px] font-semibold text-[#7A5A12]"><i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}</a>
                        <a href="{{ route('admin.businesses', ['lang' => $lang]) }}" class="h-[40px] inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 text-[12px] font-semibold text-white transition-colors"><i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Nouvel Abonnement' : 'New Subscription' }}</a>
                    </form>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[860px]">
                            <thead>
                                <tr class="text-left border-b border-[#F0F1F0]">
                                    <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Abonné' : 'Subscriber' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Rôle' : 'Role' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Plan</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Prochain paiement' : 'Next payment' }}</th>
                                    <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Souscription le' : 'Subscribed on' }}</th>
                                    <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F4F5F4]">
                                @forelse($subscriptions as $s)
                                @php
                                    [$stLabel, $stText, $stDot] = $subStatusMeta[$s->status] ?? [$s->status, 'text-[#6F6B60]', '#8A857A'];
                                    [$roLabel, $roCls] = $subRoleMeta[$s->vendor_type] ?? [$isFr ? 'Artisan' : 'Artisan', 'bg-[#E2F3E8] text-[#157A43]'];
                                    $initial = mb_strtoupper(mb_substr($s->owner_name ?? $s->business_name ?? '?', 0, 1));
                                    $nextDiff = $s->next_payment_at ? \Carbon\Carbon::parse($s->next_payment_at) : null;
                                @endphp
                                <tr>
                                    <td class="py-3.5 pr-3">
                                        <div class="flex items-center gap-3">
                                            @if($s->logo)<img src="{{ asset('storage/' . $s->logo) }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                            @else<span class="w-9 h-9 rounded-full bg-[#14652F] text-white text-[13px] font-bold flex items-center justify-center shrink-0">{{ $initial }}</span>@endif
                                            <span class="min-w-0">
                                                <span class="block text-[13px] font-semibold text-[#1B1B18] truncate">{{ $s->owner_name ?? $s->business_name }}</span>
                                                <span class="block text-[11px] text-[#8A857A] truncate">{{ $s->owner_email ?? '—' }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-2"><span class="inline-block rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $roCls }}">{{ $roLabel }}</span></td>
                                    <td class="py-3.5 px-2">
                                        <span class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#1B1B18]"><i data-lucide="{{ $s->plan_icon ?? 'gem' }}" class="w-4 h-4" style="color: {{ $s->plan_color }}"></i>{{ $isFr ? $s->plan_fr : ($s->plan_en ?? $s->plan_fr) }}</span>
                                    </td>
                                    <td class="py-3.5 px-2"><span class="inline-flex items-center gap-1.5 text-[12px] font-semibold {{ $stText }}"><span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $stDot }}"></span>{{ $stLabel }}</span></td>
                                    <td class="py-3.5 px-2 text-[12.5px] text-[#1B1B18] whitespace-nowrap">{{ $fmt($s->amount) }} FCFA<br><span class="text-[11px] text-[#8A857A]">/ {{ $isFr ? 'an' : 'year' }}</span></td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F] whitespace-nowrap">
                                        {{ $subDate($s->next_payment_at) }}
                                        @if($nextDiff && $s->status === 'active')<br><span class="text-[11px] text-[#8A857A]">{{ $isFr ? 'Dans' : 'In' }} {{ (int) ceil(now()->diffInMonths($nextDiff, false)) }} {{ $isFr ? 'mois' : 'months' }}</span>
                                        @elseif($s->status === 'expired')<br><span class="text-[11px] text-[#DC2626] font-semibold">{{ $isFr ? 'Expiré' : 'Expired' }}</span>@endif
                                    </td>
                                    <td class="py-3.5 px-2 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $subDate($s->started_at) }}<br><span class="text-[11px] text-[#8A857A]">{{ \Carbon\Carbon::parse($s->started_at)->format('H:i') }}</span></td>
                                    <td class="py-3.5 pl-2">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                            <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="{{ $isFr ? 'Modifier' : 'Edit' }}"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                            <a href="{{ route('admin.businesses.detail', ['id' => $s->business_id, 'lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="Actions"><i data-lucide="more-vertical" class="w-4 h-4"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="py-10 text-center text-[13px] text-[#6F6B60]">{{ $isFr ? 'Aucun abonnement ne correspond à ces critères.' : 'No subscription matches these criteria.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($subscriptions->hasPages())
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $subscriptions->firstItem() }} {{ $isFr ? 'à' : 'to' }} {{ $subscriptions->lastItem() }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($subscriptions->total()) }} {{ $isFr ? 'abonnements' : 'subscriptions' }}</p>
                        <div class="flex items-center gap-1.5">
                            @if($subscriptions->onFirstPage())<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                            @else<a href="{{ $subscriptions->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>@endif
                            @foreach($subscriptions->getUrlRange(1, $subscriptions->lastPage()) as $pn => $url)
                            @if($pn === $subscriptions->currentPage())<span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md">{{ $pn }}</span>
                            @else<a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-[12.5px] text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md">{{ $pn }}</a>@endif
                            @endforeach
                            @if($subscriptions->hasMorePages())<a href="{{ $subscriptions->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] hover:bg-[#F2F5F2] rounded-md"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                            @else<span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>@endif
                        </div>
                    </div>
                    @endif
                </section>

                {{-- Right rail --}}
                <aside class="space-y-4">
                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Répartition par plan' : 'Distribution by plan' }}</h2>
                        <div class="mt-4 flex items-center gap-4">
                            <span class="w-[92px] h-[92px] rounded-full shrink-0" style="background: {{ $planConic }}"><span class="block w-[46px] h-[46px] rounded-full bg-white m-[23px]"></span></span>
                            <div class="flex-1 space-y-1.5">
                                @foreach($planDist as $pd)
                                @php $pct = round($pd->n / $planTotal * 100, 1); @endphp
                                <div class="flex items-center justify-between text-[11.5px]">
                                    <span class="flex items-center gap-1.5 text-[#3B382F]"><span class="w-2 h-2 rounded-full" style="background-color: {{ $pd->color }}"></span>{{ $isFr ? $pd->name_fr : ($pd->name_en ?? $pd->name_fr) }}</span>
                                    <span class="text-[#6F6B60]">{{ $pct }}% ({{ $pd->n }})</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé financier' : 'Financial summary' }}</h2>
                        <dl class="mt-3.5 space-y-2.5">
                            @foreach($financeRows as [$fLabel, $fValue, $fColor])
                            <div class="flex items-center justify-between text-[12px]"><dt class="text-[#6F6B60]">{{ $fLabel }}</dt><dd class="font-semibold" style="color: {{ $fColor }}">{{ $fValue }}</dd></div>
                            @endforeach
                        </dl>
                        <div class="mt-3.5 border-t border-[#F0F1F0] pt-3.5">
                            <p class="text-[11px] text-[#6F6B60]">{{ $isFr ? 'Revenus cette année' : 'Revenue this year' }}</p>
                            <p class="text-[16px] font-bold text-[#157A43]">{{ $fmt($finance['year']) }} FCFA</p>
                            <p class="mt-0.5 text-[11px] text-[#157A43]">+12.5% {{ $isFr ? 'vs année dernière' : 'vs last year' }}</p>
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-2 divide-y divide-[#F4F5F4]">
                            @foreach($quickActions as [$qIcon, $qLabel, $qUrl])
                            <a href="{{ $qUrl }}" class="flex items-center gap-3 py-2.5 group"><i data-lucide="{{ $qIcon }}" class="w-[17px] h-[17px] text-[#55524A] group-hover:text-[#14652F]"></i><span class="text-[12.5px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qLabel }}</span></a>
                            @endforeach
                        </div>
                    </section>

                    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-[#0E3D22] to-[#12522C] px-5 py-6 text-center">
                        <p class="font-serif text-[15px] leading-relaxed text-white italic">{{ $isFr ? 'Soutenez l\'artisanat, investissez dans notre patrimoine.' : 'Support craftsmanship, invest in our heritage.' }}</p>
                        <p class="mt-3 text-[#E9C25A] text-[12px]">◇ ◈ ◇</p>
                    </section>
                </aside>
            </div>

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
