@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';

    // ── Status → pill meta (label + classes + dot color) matching the design badges
    $payStatusMeta = [
        'active'    => [$isFr ? 'Réussi' : 'Paid',       'bg-[#E2F3E8] text-[#157A43]', '#157A43'],
        'pending'   => [$isFr ? 'En attente' : 'Pending', 'bg-[#FDF3E0] text-[#C97A16]', '#E9A83A'],
        'expired'   => [$isFr ? 'Expiré' : 'Expired',     'bg-[#EEECE6] text-[#6F6B60]', '#8A857A'],
        'cancelled' => [$isFr ? 'Échoué' : 'Failed',      'bg-[#FDE8E8] text-[#DC2626]', '#DC2626'],
    ];

    // FCFA formatter for real numbers only
    $fcfa = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' FCFA';

    // ── KPI cards. PNG shows 6 tiles. Only the ones with a real source get numbers;
    //    the rest (retraits) show a neutral placeholder "—".
    $revenue   = $payKpis['revenue']  ?? 0;
    $activeCnt = $payKpis['active']   ?? 0;
    $pendingCnt= $payKpis['pending']  ?? 0;
    $txTotal   = $subPayments->count();

    $paidStatus    = $payByStatus['active']  ?? ['c' => 0, 'total' => 0];
    $pendingStatus = $payByStatus['pending'] ?? ['c' => 0, 'total' => 0];
    $successRate   = $txTotal > 0 ? round(($paidStatus['c'] / $txTotal) * 100, 1) : null;

    // [icon, iconColor, tileBg, value, label, sub]
    $payCards = [
        ['receipt',      '#157A43', '#E8F2EC', $fcfa($revenue),                     $isFr ? 'VOLUME TOTAL' : 'TOTAL VOLUME',       $isFr ? 'abonnements actifs' : 'active subscriptions'],
        ['arrow-down-circle', '#157A43', '#E8F2EC', $fcfa($paidStatus['total']),    $isFr ? 'PAIEMENTS REÇUS' : 'PAYMENTS RECEIVED', $isFr ? 'encaissés' : 'collected'],
        ['arrow-up-circle', '#3565DE', '#E8EFFB', '—',                              $isFr ? 'RETRAITS EFFECTUÉS' : 'WITHDRAWALS',  $isFr ? 'aucune donnée' : 'no data'],
        ['hourglass',    '#C97A16', '#FDF3E0', $fcfa($pendingStatus['total']),      $isFr ? 'EN ATTENTE' : 'PENDING',              $pendingCnt . ($isFr ? ' transaction(s)' : ' transaction(s)')],
        ['bar-chart-3',  '#7C4FE0', '#F0EAFB', number_format($txTotal),             'TRANSACTIONS',                                 $activeCnt . ($isFr ? ' actifs' : ' active')],
        ['target',       '#157A43', '#E8F2EC', $successRate === null ? '—' : $successRate . '%', $isFr ? 'TAUX DE RÉUSSITE' : 'SUCCESS RATE', $isFr ? 'des transactions' : 'of transactions'],
    ];

    // ── Right rail: répartition par statut (real, from $payByStatus)
    $statusOrder = [
        'active'    => [$isFr ? 'Payés' : 'Paid',        '#157A43'],
        'pending'   => [$isFr ? 'En attente' : 'Pending', '#E9A83A'],
        'expired'   => [$isFr ? 'Expirés' : 'Expired',   '#8A857A'],
        'cancelled' => [$isFr ? 'Échoués' : 'Failed',    '#DC2626'],
    ];
    $statusGrand = 0;
    foreach ($payByStatus as $s) { $statusGrand += (float) ($s['total'] ?? 0); }
    $statusGrandCount = 0;
    foreach ($payByStatus as $s) { $statusGrandCount += (int) ($s['c'] ?? 0); }

    // Build donut segments (by total amount)
    $donut = [];
    $acc = 0;
    foreach ($statusOrder as $key => [$lbl, $color]) {
        $row = $payByStatus[$key] ?? null;
        $amt = (float) ($row['total'] ?? 0);
        $cnt = (int) ($row['c'] ?? 0);
        $pct = $statusGrand > 0 ? ($amt / $statusGrand) * 100 : 0;
        $donut[] = ['key' => $key, 'label' => $lbl, 'color' => $color, 'amt' => $amt, 'cnt' => $cnt, 'pct' => $pct, 'offset' => $acc];
        $acc += $pct;
    }

    // ── Bottom secondary stat row. PNG metrics without a source → "—".
    $bottomStats = [
        ['shopping-cart', '#157A43', '#E8F2EC', $activeCnt > 0 ? $fcfa($revenue / max(1, $activeCnt)) : '—', $isFr ? 'PANIER MOYEN' : 'AVERAGE CART', $isFr ? 'par abonnement' : 'per subscription'],
        ['users',         '#3565DE', '#E8EFFB', number_format($activeCnt),                                   $isFr ? 'CLIENTS PAYANTS' : 'PAYING CLIENTS', $isFr ? 'actifs' : 'active'],
        ['package',       '#7C4FE0', '#F0EAFB', '—',                                                          $isFr ? 'PRODUITS VENDUS' : 'PRODUCTS SOLD', $isFr ? 'aucune donnée' : 'no data'],
        ['trending-up',   '#C97A16', '#FDF3E0', '—',                                                          $isFr ? 'TAUX DE CONVERSION' : 'CONVERSION RATE', $isFr ? 'aucune donnée' : 'no data'],
        ['rotate-ccw',    '#DC2626', '#FDECEC', $fcfa($pendingStatus['total']),                               $isFr ? 'REMBOURSEMENTS' : 'REFUNDS', $isFr ? 'en attente' : 'pending'],
    ];

    // ── Quick actions (PNG bottom-right block)
    $quickActions = [
        ['plus-circle', $isFr ? 'Nouveau paiement' : 'New payment',   'bg-[#0F4824] hover:bg-[#14652F] text-white'],
        ['arrow-up-circle', $isFr ? 'Demande de retrait' : 'Withdrawal request', 'bg-[#7C4FE0] hover:bg-[#6B41CC] text-white'],
        ['rotate-ccw', $isFr ? 'Remboursement' : 'Refund',            'bg-[#3565DE] hover:bg-[#2A54BC] text-white'],
        ['download',   $isFr ? 'Rapport financier' : 'Financial report', 'bg-[#E6B201] hover:bg-[#D4A400] text-white'],
    ];

    // Date formatter
    $payDate = function ($value) {
        if (! $value) return ['—', ''];
        $d = \Carbon\Carbon::parse($value);
        return [$d->format('d/m/Y'), $d->format('H:i')];
    };
@endphp

@section('content')

    {{-- Title + breadcrumb + top action buttons (project adaptation: layout owns the top bar) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 pt-1 pb-1">
        <div>
            <h1 class="text-[26px] font-bold text-[#1B1B18] leading-tight">{{ $isFr ? 'Gestion des Paiements & Retraits' : 'Payments & Withdrawals Management' }}</h1>
            <nav class="mt-1 flex items-center gap-1.5 text-[12.5px] text-[#8A857A]">
                <a href="{{ route('dashboard.admin', ['lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Paiements & Retraits' : 'Payments & Withdrawals' }}</span>
            </nav>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" class="h-[42px] inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 text-[13px] font-semibold text-white transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Nouveau paiement' : 'New payment' }}
            </button>
            <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="h-[42px] inline-flex items-center gap-2 bg-white border border-[#E5E7E5] hover:border-[#14652F] rounded-lg px-4 text-[13px] font-semibold text-[#3B382F] transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Exporter' : 'Export' }}
            </a>
        </div>
    </div>

    {{-- KPI stat cards --}}
    <section class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
        @foreach($payCards as [$pcIcon, $pcColor, $pcTile, $pcValue, $pcLabel, $pcSub])
        <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4 flex flex-col">
            <span class="w-[42px] h-[42px] rounded-xl flex items-center justify-center" style="background-color: {{ $pcTile }}">
                <i data-lucide="{{ $pcIcon }}" class="w-5 h-5" style="color: {{ $pcColor }};stroke-width:1.8"></i>
            </span>
            <p class="mt-3 text-[10px] font-bold tracking-[0.06em] text-[#8A857A] uppercase">{{ $pcLabel }}</p>
            <p class="mt-1 text-[19px] font-bold text-[#1B1B18] leading-tight">{{ $pcValue }}</p>
            <p class="mt-1 text-[11px] text-[#6F6B60]">{{ $pcSub }}</p>
        </div>
        @endforeach
    </section>

    <div class="mt-5 grid grid-cols-1 2xl:grid-cols-[1fr_340px] gap-5 items-start">

        {{-- ── Left: filter bar + transactions table ── --}}
        <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">

            {{-- Filter / tab bar --}}
            <form method="GET" class="flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex-1 min-w-[180px] flex items-center gap-2 h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3.5">
                    <i data-lucide="search" class="w-4 h-4 text-[#8A857A]"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Rechercher un paiement...' : 'Search a payment...' }}" class="flex-1 min-w-0 text-[12.5px] focus:outline-none placeholder-[#8A857A]">
                </div>
                <select name="type" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                    <option value="">Type</option>
                    <option value="paiement">{{ $isFr ? 'Paiement' : 'Payment' }}</option>
                    <option value="retrait">{{ $isFr ? 'Retrait' : 'Withdrawal' }}</option>
                </select>
                <select name="statut" onchange="this.form.submit()" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                    <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                    @foreach($payStatusMeta as $sv => [$sl, , ])
                    <option value="{{ $sv }}" @selected(request('statut') === $sv)>{{ $sl }}</option>
                    @endforeach
                </select>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                <select name="methode" class="h-[40px] bg-white border border-[#E5E7E5] rounded-lg px-3 text-[12.5px] text-[#3B382F] cursor-pointer">
                    <option value="">{{ $isFr ? 'Méthode' : 'Method' }}</option>
                </select>
                <button type="submit" class="h-[40px] inline-flex items-center gap-2 bg-[#FBF6EA] border border-[#EAD9AC] hover:border-[#C9942E] rounded-lg px-4 text-[12.5px] font-semibold text-[#7A5A12] transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>{{ $isFr ? 'Filtres' : 'Filters' }}
                </button>
            </form>

            {{-- Transactions table --}}
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[760px]">
                    <thead>
                        <tr class="text-left border-b border-[#F0F1F0]">
                            <th class="pb-2.5 pr-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Référence' : 'Reference' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Type</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Utilisateur' : 'User' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Méthode' : 'Method' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                            <th class="pb-2.5 px-2 text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Date</th>
                            <th class="pb-2.5 pl-2 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F4F5F4]">
                        @forelse($subPayments as $tx)
                        @php
                            [$stLabel, $stCls, $stDot] = $payStatusMeta[$tx->status] ?? [$tx->status, 'bg-[#EEECE6] text-[#6F6B60]', '#8A857A'];
                            $ref = 'PAY-' . str_pad($tx->id, 6, '0', STR_PAD_LEFT);
                            [$dDate, $dTime] = $payDate($tx->created_at ?? $tx->started_at);
                            $curr = $tx->plan_currency ?? 'FCFA';
                        @endphp
                        <tr>
                            <td class="py-3.5 pr-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-lg bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="credit-card" class="w-[18px] h-[18px] text-[#14652F]"></i></span>
                                    <span class="min-w-0">
                                        <span class="block text-[12.5px] font-semibold text-[#1B1B18] truncate">{{ $ref }}</span>
                                        <span class="block text-[11px] text-[#8A857A] truncate">{{ $tx->plan_name ?? '—' }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-2"><span class="inline-block rounded-md px-2.5 py-1 text-[11px] font-semibold bg-[#E2F3E8] text-[#157A43]">{{ $isFr ? 'Paiement' : 'Payment' }}</span></td>
                            <td class="py-3.5 px-2">
                                <span class="block text-[12.5px] font-semibold text-[#1B1B18] truncate max-w-[180px]">{{ $tx->biz_name ?? '—' }}</span>
                                <span class="block text-[11px] text-[#8A857A]">{{ $isFr ? 'Abonnement' : 'Subscription' }}</span>
                            </td>
                            <td class="py-3.5 px-2 text-[12.5px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ number_format((float) $tx->amount, 0, ',', ' ') }} {{ $curr }}</td>
                            <td class="py-3.5 px-2">
                                <span class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-md bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="wallet" class="w-[15px] h-[15px] text-[#55524A]"></i></span>
                                    <span class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Abonnement' : 'Subscription' }}</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-2"><span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[11px] font-semibold {{ $stCls }}"><span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $stDot }}"></span>{{ $stLabel }}</span></td>
                            <td class="py-3.5 px-2 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $dDate }}<br><span class="text-[11px] text-[#8A857A]">{{ $dTime }}</span></td>
                            <td class="py-3.5 pl-2">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="{{ $isFr ? 'Voir' : 'View' }}"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="w-8 h-8 rounded-lg border border-[#E5E7E5] hover:border-[#14652F] flex items-center justify-center text-[#55524A]" title="Actions"><i data-lucide="more-vertical" class="w-4 h-4"></i></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="py-12 text-center">
                            <i data-lucide="credit-card" class="w-8 h-8 text-[#C9C4B8] mx-auto"></i>
                            <p class="mt-2 text-[13px] text-[#6F6B60]">{{ $isFr ? 'Aucune transaction pour le moment.' : 'No transactions yet.' }}</p>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer count --}}
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Affichage de' : 'Showing' }} {{ $subPayments->count() > 0 ? 1 : 0 }} {{ $isFr ? 'à' : 'to' }} {{ $subPayments->count() }} {{ $isFr ? 'sur' : 'of' }} {{ number_format($subPayments->count()) }} {{ $isFr ? 'transactions' : 'transactions' }}</p>
                <div class="flex items-center gap-1.5">
                    <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                    <span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] text-white text-[12.5px] font-semibold rounded-md">1</span>
                    <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                </div>
            </div>
        </section>

        {{-- ── Right rail ── --}}
        <aside class="space-y-4">

            {{-- Répartition par statut (donut + legend, real) --}}
            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Répartition par statut' : 'Breakdown by status' }}</h2>
                    <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43] hover:underline">{{ $isFr ? 'Voir le rapport' : 'View report' }}</a>
                </div>

                <div class="mt-4 flex items-center justify-center">
                    <div class="relative w-[150px] h-[150px]">
                        <svg viewBox="0 0 42 42" class="w-full h-full -rotate-90">
                            <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#F0EFEA" stroke-width="5"></circle>
                            @if($statusGrand > 0)
                                @foreach($donut as $seg)
                                    @if($seg['pct'] > 0)
                                    <circle cx="21" cy="21" r="15.9155" fill="none" stroke="{{ $seg['color'] }}" stroke-width="5"
                                        stroke-dasharray="{{ $seg['pct'] }} {{ 100 - $seg['pct'] }}"
                                        stroke-dashoffset="{{ 100 - $seg['offset'] + 25 }}"></circle>
                                    @endif
                                @endforeach
                            @endif
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-[16px] font-bold text-[#1B1B18] leading-none">{{ number_format($statusGrand, 0, ',', ' ') }}</span>
                            <span class="text-[10px] text-[#8A857A] mt-0.5">FCFA Total</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-2.5">
                    @foreach($donut as $seg)
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-sm" style="background-color: {{ $seg['color'] }}"></span>{{ $seg['label'] }}</span>
                        <span class="text-[#1B1B18]">{{ number_format($seg['amt'], 0, ',', ' ') }} <span class="text-[#8A857A]">({{ number_format($seg['pct'], 1) }}%)</span></span>
                    </div>
                    @endforeach
                    @if($statusGrand <= 0)
                    <p class="text-[11.5px] text-[#8A857A] text-center pt-1">{{ $isFr ? 'Aucune donnée disponible.' : 'No data available.' }}</p>
                    @endif
                </div>
            </section>

            {{-- Volumes par mois — no per-month source → neutral empty-state --}}
            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Volumes par mois (FCFA)' : 'Volumes by month (FCFA)' }}</h2>
                    <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43] hover:underline">{{ $isFr ? 'Voir le rapport' : 'View report' }}</a>
                </div>
                <div class="mt-4 h-[120px] flex flex-col items-center justify-center rounded-xl bg-[#FAF8F2] border border-dashed border-[#EDE7D8]">
                    <i data-lucide="bar-chart-3" class="w-6 h-6 text-[#C9C4B8]"></i>
                    <p class="mt-1.5 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée mensuelle' : 'No monthly data' }}</p>
                </div>
            </section>

            {{-- Méthodes de paiement — no source → placeholder --}}
            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Méthodes de paiement' : 'Payment methods' }}</h2>
                    <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43] hover:underline">{{ $isFr ? 'Voir le rapport' : 'View report' }}</a>
                </div>
                <div class="mt-3.5 flex items-center gap-3 py-2">
                    <span class="w-8 h-8 rounded-md bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="wallet" class="w-[16px] h-[16px] text-[#55524A]"></i></span>
                    <span class="flex-1 text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Abonnement' : 'Subscription' }}</span>
                    <span class="text-[12.5px] font-semibold text-[#1B1B18]">{{ number_format($revenue, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex items-center gap-3 py-2">
                    <span class="w-8 h-8 rounded-md bg-[#F3F0E6] flex items-center justify-center shrink-0"><i data-lucide="credit-card" class="w-[16px] h-[16px] text-[#55524A]"></i></span>
                    <span class="flex-1 text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Autres méthodes' : 'Other methods' }}</span>
                    <span class="text-[12.5px] font-semibold text-[#8A857A]">—</span>
                </div>
            </section>

            {{-- Actions rapides --}}
            <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                <h2 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                <div class="mt-3.5 grid grid-cols-2 gap-2.5">
                    @foreach($quickActions as [$qaIcon, $qaLabel, $qaCls])
                    <button type="button" class="h-[62px] rounded-xl flex flex-col items-center justify-center gap-1.5 text-[11.5px] font-semibold transition-colors {{ $qaCls }}">
                        <i data-lucide="{{ $qaIcon }}" class="w-[18px] h-[18px]"></i>{{ $qaLabel }}
                    </button>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    {{-- Bottom secondary stat row --}}
    <section class="mt-5 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        @foreach($bottomStats as [$bsIcon, $bsColor, $bsTile, $bsValue, $bsLabel, $bsSub])
        <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4 flex flex-col">
            <span class="w-[42px] h-[42px] rounded-xl flex items-center justify-center" style="background-color: {{ $bsTile }}">
                <i data-lucide="{{ $bsIcon }}" class="w-5 h-5" style="color: {{ $bsColor }};stroke-width:1.8"></i>
            </span>
            <p class="mt-3 text-[10px] font-bold tracking-[0.06em] text-[#8A857A] uppercase">{{ $bsLabel }}</p>
            <p class="mt-1 text-[18px] font-bold text-[#1B1B18] leading-tight">{{ $bsValue }}</p>
            <p class="mt-1 text-[11px] text-[#6F6B60]">{{ $bsSub }}</p>
        </div>
        @endforeach
    </section>

    <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">© {{ now()->year }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</p>

@endsection
