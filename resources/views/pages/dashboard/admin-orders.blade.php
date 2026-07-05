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
        'created'       => 'bg-[#FDF0DC] text-[#C97A16]',
        'confirmed'     => 'bg-[#E2F3E8] text-[#157A43]',
        'in_production' => 'bg-[#EEEAFB] text-[#6D4FC2]',
        'shipped'       => 'bg-[#E3EDFB] text-[#2563EB]',
        'delivered'     => 'bg-[#DFF3EE] text-[#0F766E]',
        'cancelled'     => 'bg-[#FDE3E3] text-[#C0392B]',
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
        if (str_contains($m, 'mtn') || str_contains($m, 'momo'))            return ['smartphone', 'bg-[#FFCC00] text-[#3A2A03]'];
        if (str_contains($m, 'orange'))                                     return ['smartphone', 'bg-[#F97316] text-white'];
        if (str_contains($m, 'carte') || str_contains($m, 'card'))          return ['credit-card', 'bg-[#2563EB] text-white'];
        if (str_contains($m, 'virement') || str_contains($m, 'transfer') || str_contains($m, 'bancaire') || str_contains($m, 'bank')) return ['landmark', 'bg-[#0F766E] text-white'];
        return ['banknote', 'bg-[#E9E4D8] text-[#3B382F]'];
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
                <a href="{{ route('admin.reports', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    {{ $isFr ? 'Exporter' : 'Export' }}
                </a>
            </div>

            {{-- Status tab band --}}
            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                <div class="grid grid-cols-3 sm:grid-cols-6 divide-x divide-[#F5F1E8]">
                    @foreach($statusTabs as $tabKey => [$tabLabel, $tabCount])
                    <a href="{{ request()->fullUrlWithQuery(['statut' => $tabKey === 'toutes' ? null : $tabKey, 'page' => null]) }}"
                       class="block px-4 py-3.5 border-t-[3px] {{ $activeTab === $tabKey ? 'border-[#14652F] bg-[#F8F4EC]' : 'border-transparent hover:bg-[#FCFAF5]' }}">
                        <span class="block text-[11.5px] {{ $activeTab === $tabKey ? 'font-semibold text-[#14652F]' : 'text-[#6F6B60]' }}">{{ $tabLabel }}</span>
                        <span class="block mt-0.5 text-[19px] font-bold text-[#1B1B18]">{{ number_format($tabCount) }}</span>
                    </a>
                    @endforeach
                </div>
            </section>

            {{-- Search + filters --}}
            <form method="GET" action="{{ route('admin.orders') }}" class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl p-3 flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex items-center gap-2.5 bg-[#FCFAF5] border border-[#E9E4D8] rounded-lg px-3.5 h-[38px] flex-1 min-w-[210px]">
                    <i data-lucide="search" class="w-[15px] h-[15px] shrink-0 text-[#8A857A]"></i>
                    <input type="text" name="q" value="{{ request()->query('q') }}" placeholder="{{ $isFr ? 'Rechercher une commande...' : 'Search an order...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A857A]">
                </div>
                <select name="statut" class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12.5px] text-[#3B382F] focus:outline-none">
                    <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                    @foreach($statutOptions as $optKey => $optLabel)
                    <option value="{{ $optKey }}" @selected(request()->query('statut') === $optKey)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                <select name="paiement" class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12.5px] text-[#3B382F] focus:outline-none">
                    <option value="">{{ $isFr ? 'Méthode de paiement' : 'Payment method' }}</option>
                    @foreach($paymentMethods as $pm)
                    <option value="{{ $pm }}" @selected(request()->query('paiement') === $pm)>{{ $pm }}</option>
                    @endforeach
                </select>
                <select name="date" class="h-[38px] bg-white border border-[#E9E4D8] rounded-lg px-3 text-[12.5px] text-[#3B382F] focus:outline-none">
                    <option value="">Date</option>
                    @foreach($dateOptions as $optKey => $optLabel)
                    <option value="{{ $optKey }}" @selected(request()->query('date') === $optKey)>{{ $optLabel }}</option>
                    @endforeach
                </select>
                <button type="submit" class="inline-flex items-center gap-2 border border-[#14652F] text-[#14652F] hover:bg-[#F0F7F2] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold transition-colors">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    {{ $isFr ? 'Filtrer' : 'Filter' }}
                </button>
            </form>

            {{-- Orders table --}}
            <section class="mt-4 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[860px]">
                        <thead>
                            <tr class="bg-[#F8F4EC] text-left">
                                <th class="pl-5 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Commande' : 'Order' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Client</th>
                                <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Montant' : 'Amount' }}</th>
                                <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Paiement' : 'Payment' }}</th>
                                <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Date</th>
                                <th class="px-2 pr-5 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F5F1E8]">
                            @forelse($adminOrders as $o)
                            <tr>
                                <td class="pl-5 pr-2 py-3">
                                    <p class="text-[12.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $o->reference ?? ('PO-' . $o->id) }}</p>
                                </td>
                                <td class="px-2 py-3">
                                    <p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $o->client_name ?? '—' }}</p>
                                    @if($o->business_name)
                                    <p class="text-[11px] text-[#8A857A]">{{ $o->business_name }}</p>
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-right text-[12.5px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ number_format($o->total, 0, ',', ' ') }} FCFA</td>
                                <td class="px-2 py-3 text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold {{ $statusPills[$o->status] ?? 'bg-[#F1EFE9] text-[#55524A]' }}">{{ $statusLabels[$o->status] ?? $o->status }}</span>
                                </td>
                                <td class="px-2 py-3">
                                    @if($o->payment_method)
                                        @php [$payIcon, $payClasses] = $payBadge($o->payment_method); @endphp
                                        <span class="inline-flex items-center gap-2">
                                            <span class="w-[22px] h-[22px] rounded-md flex items-center justify-center {{ $payClasses }}"><i data-lucide="{{ $payIcon }}" class="w-[13px] h-[13px]"></i></span>
                                            <span class="text-[12px] text-[#3B382F]">{{ $o->payment_method }}</span>
                                        </span>
                                        @if($o->invoice_status)
                                        <span class="ml-1.5 inline-block rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $o->invoice_status === 'paid' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDF0DC] text-[#C97A16]' }}">{{ $o->invoice_status === 'paid' ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}</span>
                                        @endif
                                    @elseif($o->invoice_status)
                                        <span class="text-[12px] text-[#8A857A]">—</span>
                                        <span class="ml-1.5 inline-block rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $o->invoice_status === 'paid' ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDF0DC] text-[#C97A16]' }}">{{ $o->invoice_status === 'paid' ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}</span>
                                    @else
                                        <span class="text-[12px] text-[#8A857A]">—</span>
                                    @endif
                                </td>
                                <td class="px-2 py-3 text-[12px] text-[#6F6B60] whitespace-nowrap">{{ $fmtDate($o->created_at) }}</td>
                                <td class="px-2 pr-5 py-3 text-right">
                                    <a href="{{ route('quotes.po', ['po' => $o->id, 'lang' => $lang]) }}" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] text-[#55524A] hover:text-[#14652F] hover:border-[#14652F] transition-colors" title="{{ $isFr ? 'Voir la commande' : 'View order' }}">
                                        <i data-lucide="eye" class="w-[15px] h-[15px]"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune commande.' : 'No orders.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($adminOrders->total() > 0)
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 border-t border-[#F5F1E8]">
                    <p class="text-[12px] text-[#6F6B60]">
                        {{ $isFr
                            ? 'Affichage de ' . $adminOrders->firstItem() . ' à ' . $adminOrders->lastItem() . ' sur ' . number_format($adminOrders->total()) . ' commandes'
                            : 'Showing ' . $adminOrders->firstItem() . ' to ' . $adminOrders->lastItem() . ' of ' . number_format($adminOrders->total()) . ' orders' }}
                    </p>
                    @if($pgLast > 1)
                    <nav class="flex items-center gap-1.5">
                        <a href="{{ $pgCurrent > 1 ? $adminOrders->url($pgCurrent - 1) : '#' }}" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] text-[#55524A] {{ $pgCurrent > 1 ? 'hover:border-[#14652F] hover:text-[#14652F]' : 'opacity-40 pointer-events-none' }}">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </a>
                        @foreach($pgItems as $pg)
                            @if($pg === '…')
                            <span class="inline-flex items-center justify-center w-[30px] h-[30px] text-[12px] text-[#8A857A]">…</span>
                            @else
                            <a href="{{ $adminOrders->url($pg) }}" class="inline-flex items-center justify-center min-w-[30px] h-[30px] px-1.5 rounded-lg text-[12px] font-semibold {{ $pg === $pgCurrent ? 'bg-[#0F4824] text-white' : 'border border-[#E9E4D8] text-[#55524A] hover:border-[#14652F] hover:text-[#14652F]' }}">{{ $pg }}</a>
                            @endif
                        @endforeach
                        <a href="{{ $pgCurrent < $pgLast ? $adminOrders->url($pgCurrent + 1) : '#' }}" class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-[#E9E4D8] text-[#55524A] {{ $pgCurrent < $pgLast ? 'hover:border-[#14652F] hover:text-[#14652F]' : 'opacity-40 pointer-events-none' }}">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    </nav>
                    @endif
                </div>
                @endif
            </section>

            <p class="mt-6 text-center text-[11.5px] text-[#8A857A]">
                {{ $isFr
                    ? '© 2025 Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.'
                    : '© 2025 National Virtual Gallery of Cameroonian Handicrafts. All rights reserved.' }}
            </p>
@endsection
