@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'orders';
    $pageTitle = $isFr ? 'Gestion des Commandes' : 'Order Management';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Commandes' : 'Orders', null]];

    // purchase_orders.status => bilingual label + pill classes
    $statusLabels = [
        'created'       => $isFr ? 'En attente' : 'Pending',
        'confirmed'     => $isFr ? 'Confirmée' : 'Confirmed',
        'in_production' => $isFr ? 'En production' : 'In production',
        'shipped'       => $isFr ? 'Expédiée' : 'Shipped',
        'delivered'     => $isFr ? 'Livrée' : 'Delivered',
        'cancelled'     => $isFr ? 'Annulée' : 'Cancelled',
    ];
    $statusPills = [
        'created'       => 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]',
        'confirmed'     => 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]',
        'in_production' => 'bg-[#EEEAFB] dark:bg-[#101C33] text-[#6D4FC2] dark:text-[#BCA4F0]',
        'shipped'       => 'bg-[#E3EDFB] dark:bg-[#101C33] text-[#2563EB] dark:text-[#8FB6F5]',
        'delivered'     => 'bg-[#DFF3EE] dark:bg-[#0C3D1D] text-[#0F766E] dark:text-[#868778]',
        'cancelled'     => 'bg-[#FDE3E3] dark:bg-[#3A1013] text-[#C0392B] dark:text-[#F0555C]',
    ];

    // Status tab band (design: Toutes / En attente / Confirmées / Expédiées / Livrées / Annulées)
    $statusTabs = [
        'toutes'      => [$isFr ? 'Toutes' : 'All',            $orderCounts['all']],
        'en-attente'  => [$isFr ? 'En attente' : 'Pending',    $orderCounts['created']],
        'confirmees'  => [$isFr ? 'Confirmées' : 'Confirmed',  $orderCounts['confirmed']],
        'expediees'   => [$isFr ? 'Expédiées' : 'Shipped',     $orderCounts['shipped']],
        'livrees'     => [$isFr ? 'Livrées' : 'Delivered',     $orderCounts['delivered']],
        'annulees'    => [$isFr ? 'Annulées' : 'Cancelled',    $orderCounts['cancelled']],
    ];
    $activeTab = request()->query('statut', 'toutes');
    if (!array_key_exists($activeTab, $statusTabs) && $activeTab !== 'en-production') { $activeTab = 'toutes'; }

    // ?statut= values accepted by the Statut dropdown
    $statutOptions = [
        'en-attente'    => $isFr ? 'En attente' : 'Pending',
        'confirmees'    => $isFr ? 'Confirmées' : 'Confirmed',
        'en-production' => $isFr ? 'En production' : 'In production',
        'expediees'     => $isFr ? 'Expédiées' : 'Shipped',
        'livrees'       => $isFr ? 'Livrées' : 'Delivered',
        'annulees'      => $isFr ? 'Annulées' : 'Cancelled',
    ];
    $dateOptions = [
        'aujourdhui' => $isFr ? "Aujourd'hui" : 'Today',
        '7j'         => $isFr ? '7 derniers jours' : 'Last 7 days',
        '30j'        => $isFr ? '30 derniers jours' : 'Last 30 days',
    ];

    // Payment method => [lucide icon, icon chip classes]
    $payBadge = function ($method) {
        $m = mb_strtolower((string) $method);
        if (str_contains($m, 'mtn') || str_contains($m, 'momo'))            return ['smartphone', 'bg-[#FFCC00] dark:bg-[#3A2B06] text-[#3A2A03] dark:text-[#EDB33A]'];
        if (str_contains($m, 'orange'))                                     return ['smartphone', 'bg-[#F97316] dark:bg-[#3A2B06] text-white'];
        if (str_contains($m, 'carte') || str_contains($m, 'card'))          return ['credit-card', 'bg-[#2563EB] text-white'];
        if (str_contains($m, 'virement') || str_contains($m, 'transfer') || str_contains($m, 'bancaire') || str_contains($m, 'bank')) return ['landmark', 'bg-[#0F766E] text-white'];
        return ['banknote', 'bg-[#E9E4D8] dark:bg-[#1A1E16] text-[#3B382F] dark:text-[#B4B5A6]'];
    };

    // Date rendering ("03 Juin 2025" in the design)
    $frMonths = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $fmtDate = function ($value) use ($isFr, $frMonths) {
        $d = \Illuminate\Support\Carbon::parse($value);
        return $isFr ? ($d->format('d') . ' ' . $frMonths[$d->month - 1] . ' ' . $d->format('Y')) : $d->format('M d, Y');
    };

    // Pagination window (1 … around current … last)
    $pgCurrent = $adminOrders->currentPage();
    $pgLast    = $adminOrders->lastPage();
    $pgItems = [];
    if ($pgLast <= 7) {
        $pgItems = range(1, $pgLast);
    } else {
        $pgItems[] = 1;
        $from = max(2, $pgCurrent - 2);
        $to   = min($pgLast - 1, $pgCurrent + 2);
        if ($from > 2) { $pgItems[] = '…'; }
        foreach (range($from, $to) as $pg) { $pgItems[] = $pg; }
        if ($to < $pgLast - 1) { $pgItems[] = '…'; }
        $pgItems[] = $pgLast;
    }
@endphp

@section('content')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary shrink-0">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    {{ $isFr ? 'Exporter' : 'Export' }}
                </a>
            </div>

            {{-- Status tab band --}}
            <section class="ui-card ui-card--flush mt-4">
                <div class="grid grid-cols-3 sm:grid-cols-6 divide-x divide-[#F5F1E8] dark:divide-[#262B21]">
                    @foreach($statusTabs as $tabKey => [$tabLabel, $tabCount])
                    <a href="{{ request()->fullUrlWithQuery(['statut' => $tabKey === 'toutes' ? null : $tabKey, 'page' => null]) }}"
                       class="block px-4 py-3.5 border-t-[3px] {{ $activeTab === $tabKey ? 'border-[#14652F] dark:border-[#2E9250] bg-[#F8F4EC] dark:bg-[#1A1E16] ' : 'border-transparent hover:bg-[#FCFAF5] dark:hover:bg-[#242A1E] ' }}">
                        <span class="block text-[13px] md:text-[11.5px] {{ $activeTab === $tabKey ? 'font-semibold text-[#14652F] dark:text-[#339B56] ' : 'text-[#6F6B60] dark:text-[#868778] ' }}">{{ $tabLabel }}</span>
                        <span class="block mt-0.5 text-[19px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($tabCount) }}</span>
                    </a>
                    @endforeach
                </div>
            </section>

            {{-- Search + filters --}}
            <form method="GET" action="{{ route('admin.orders') }}" class="ui-card mt-4 flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="ui-field-group flex-1 min-w-[210px]">
                    <i data-lucide="search" class="w-[15px] h-[15px] shrink-0"></i>
                    <input type="text" name="q" value="{{ request()->query('q') }}" placeholder="{{ $isFr ? 'Rechercher une commande...' : 'Search an order...' }}" class="ui-field-bare flex-1 min-w-0">
                </div>
                <select name="statut" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                    @foreach($statutOptions as $optKey => $optLabel)
                    <option value="{{ $optKey }}" @selected(request()->query('statut') === $optKey)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                <select name="paiement" class="ui-field ui-select">
                    <option value="">{{ $isFr ? 'Méthode de paiement' : 'Payment method' }}</option>
                    @foreach($paymentMethods as $pm)
                    <option value="{{ $pm }}" @selected(request()->query('paiement') === $pm)>{{ $pm }}</option>
                    @endforeach
                </select>
                <select name="date" class="ui-field ui-select">
                    <option value="">Date</option>
                    @foreach($dateOptions as $optKey => $optLabel)
                    <option value="{{ $optKey }}" @selected(request()->query('date') === $optKey)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ui-btn ui-btn-secondary">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    {{ $isFr ? 'Filtrer' : 'Filter' }}
                </button>
            </form>

            {{-- Orders table --}}
            <section class="ui-card ui-card--flush mt-4">
                <div class="ui-table-wrap">
                    <table class="ui-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>{{ $isFr ? 'Commande' : 'Order' }}</th>
                                <th>Client</th>
                                <th class="text-right">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                <th class="text-center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th>{{ $isFr ? 'Paiement' : 'Payment' }}</th>
                                <th>Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adminOrders as $o)
                            <tr>
                                <td>
                                    <p class="text-[15px] md:text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ $o->reference ?? ('PO-' . $o->id) }}</p>
                                </td>
                                <td>
                                    <p class="text-[15px] md:text-[12.5px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $o->client_name ?? '—' }}</p>
                                    @if($o->business_name)
                                    <p class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $o->business_name }}</p>
                                    @endif
                                </td>
                                <td class="text-right font-semibold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ number_format($o->total, 0, ',', ' ') }} FCFA</td>
                                <td class="text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[12px] md:text-[10.5px] font-semibold {{ $statusPills[$o->status] ?? 'bg-[#F1EFE9] dark:bg-[#1A1E16] text-[#55524A] dark:text-[#B4B5A6] ' }}">{{ $statusLabels[$o->status] ?? $o->status }}</span>
                                </td>
                                <td>
                                    @if($o->payment_method)
                                        @php [$payIcon, $payClasses] = $payBadge($o->payment_method); @endphp
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-[22px] h-[22px] rounded-md flex items-center justify-center {{ $payClasses }}"><i data-lucide="{{ $payIcon }}" class="w-[13px] h-[13px]"></i></span>
                                            <span class="text-[14px] md:text-[12px] text-[#3B382F] dark:text-[#B4B5A6]">{{ $o->payment_method }}</span>
                                        </span>
                                        @if($o->invoice_status)
                                        <span class="ml-1.5 inline-block rounded-md px-2 py-0.5 text-[12px] md:text-[10px] font-semibold {{ $o->invoice_status === 'paid' ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A] ' }}">{{ $o->invoice_status === 'paid' ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}</span>
                                        @endif
                                    @elseif($o->invoice_status)
                                        <span class="text-[14px] md:text-[12px] text-[#8A857A] dark:text-[#868778]">—</span>
                                        <span class="ml-1.5 inline-block rounded-md px-2 py-0.5 text-[12px] md:text-[10px] font-semibold {{ $o->invoice_status === 'paid' ? 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56] ' : 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A] ' }}">{{ $o->invoice_status === 'paid' ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}</span>
                                    @else
                                        <span class="text-[14px] md:text-[12px] text-[#8A857A] dark:text-[#868778]">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ $fmtDate($o->created_at) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('quotes.po', ['po' => $o->id, 'lang' => $lang]) }}" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] text-[#55524A] dark:text-[#B4B5A6] hover:text-[#14652F] dark:hover:text-[#339B56] hover:border-[#14652F] dark:hover:border-[#2E9250] transition-colors" title="{{ $isFr ? 'Voir la commande' : 'View order' }}">
                                        <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="ui-empty">{{ $isFr ? 'Aucune commande.' : 'No orders.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($adminOrders->total() > 0)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-t border-[#F5F1E8] dark:border-[#262B21]">
                    <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">
                        {{ $isFr
                            ? 'Affichage de ' . $adminOrders->firstItem() . ' à ' . $adminOrders->lastItem() . ' sur ' . number_format($adminOrders->total()) . ' commandes'
                            : 'Showing ' . $adminOrders->firstItem() . ' to ' . $adminOrders->lastItem() . ' of ' . number_format($adminOrders->total()) . ' orders' }}
                    </p>
                    @if($pgLast > 1)
                    <nav class="flex items-center gap-1.5">
                        <a href="{{ $pgCurrent > 1 ? $adminOrders->url($pgCurrent - 1) : '#' }}" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] text-[#55524A] dark:text-[#B4B5A6] {{ $pgCurrent > 1 ? 'hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56]' : 'opacity-40 pointer-events-none' }}">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                        @foreach($pgItems as $pg)
                            @if($pg === '…')
                            <span class="inline-flex items-center justify-center w-[30px] h-[30px] text-[14px] md:text-[12px] text-[#8A857A] dark:text-[#868778]">…</span>
                            @else
                            <a href="{{ $adminOrders->url($pg) }}" class="inline-flex items-center justify-center min-w-[44px] h-11 md:min-w-[30px] md:h-[30px] px-1.5 rounded-lg text-[14px] md:text-[12px] font-semibold {{ $pg === $pgCurrent ? 'bg-[#0F4824] dark:bg-[#2E9250] text-white dark:text-[#04150A]' : 'border border-[#EAE5D8] dark:border-[#262B21] text-[#55524A] dark:text-[#B4B5A6] hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56] ' }}">{{ $pg }}</a>
                            @endif
                        @endforeach
                        <a href="{{ $pgCurrent < $pgLast ? $adminOrders->url($pgCurrent + 1) : '#' }}" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] text-[#55524A] dark:text-[#B4B5A6] {{ $pgCurrent < $pgLast ? 'hover:border-[#14652F] dark:hover:border-[#2E9250] hover:text-[#14652F] dark:hover:text-[#339B56]' : 'opacity-40 pointer-events-none' }}">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </nav>
                    @endif
                </div>
                @endif
            </section>

            <p class="mt-6 text-center text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">
                {{ $isFr
                    ? '© 2025 Artisan Hub 237. Tous droits réservés.'
                    : '© 2025 Artisan Hub 237. All rights reserved.' }}
            </p>
@endsection
