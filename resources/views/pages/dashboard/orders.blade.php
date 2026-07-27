@extends('layouts.dashboard')

@php
$isFr = $lang === 'fr';
$pageTitle = $isFr ? ($isSeller ? 'Commandes reçues' : 'Mes commandes') : ($isSeller ? 'Received orders' : 'My orders');

// Lifecycle, in order. The seller advances an order one step at a time; the
// buyer only reads it. Kept in sync with QuoteWebController::ORDER_STATUSES.
$flow = ['confirmed', 'in_production', 'shipped', 'delivered'];
// Second element is the kit pill variant. "Shipped" had a bespoke blue with no
// kit equivalent, so it reads as neutral until delivery turns it green.
$statusMeta = [
    'confirmed'     => [$isFr ? 'Confirmée' : 'Confirmed',         'ui-pill-ok'],
    'in_production' => [$isFr ? 'En production' : 'In production', 'ui-pill-warn'],
    'shipped'       => [$isFr ? 'Expédiée' : 'Shipped',            'ui-pill-neutral'],
    'delivered'     => [$isFr ? 'Livrée' : 'Delivered',            'ui-pill-ok'],
    'cancelled'     => [$isFr ? 'Annulée' : 'Cancelled',           'ui-pill-danger'],
    'created'       => [$isFr ? 'Créée' : 'Created',               'ui-pill-neutral'],
];
$tabs = ['' => $isFr ? 'Toutes' : 'All'] + collect($flow)->mapWithKeys(fn ($s) => [$s => $statusMeta[$s][0]])->all();
@endphp

@section('content')
<div class="max-w-5xl space-y-5">

    @if(session('success'))
        <div class="ui-alert ui-alert-ok">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-1.5">
        @foreach($tabs as $val => $label)
        <a href="{{ route('orders.index', array_filter(['status' => $val ?: null])) }}"
           class="ui-btn ui-btn-sm {{ (string) ($status ?? '') === $val ? 'ui-btn-primary' : 'ui-btn-secondary' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($orders->isEmpty())
    <div class="ui-card text-center py-14 px-4">
        <i data-lucide="clipboard-list" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="text-[12.5px] text-[#8A857A]">
            {{ $isSeller
               ? ($isFr ? 'Aucune commande pour le moment. Les commandes apparaissent ici quand un acheteur accepte une de vos propositions.'
                        : 'No orders yet. Orders appear here when a buyer accepts one of your proposals.')
               : ($isFr ? 'Aucune commande pour le moment. Acceptez une proposition de devis pour en créer une.'
                        : 'No orders yet. Accept a quote proposal to create one.') }}
        </p>
        <a href="{{ route('dashboard.quotes') }}" class="ui-btn ui-btn-secondary ui-btn-sm mt-4">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            {{ $isFr ? 'Voir les devis' : 'View quotes' }}
        </a>
    </div>
    @else
    <div class="ui-card ui-card--flush">
        @foreach($orders as $o)
        @php
            $req   = $o->proposal?->request;
            $meta  = $statusMeta[$o->status] ?? $statusMeta['created'];
            $idx   = array_search($o->status, $flow, true);
            $next  = ($idx !== false && $idx < count($flow) - 1) ? $flow[$idx + 1] : null;
            $paid  = $o->invoice && $o->invoice->status === 'paid';
        @endphp
        <div class="px-4 py-4 border-b border-[#F0F1F0] last:border-0">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('quotes.po', ['po' => $o->id]) }}" class="text-[13.5px] font-semibold text-[#1B1B18] hover:text-[#14652F]">
                            {{ $o->reference }}
                        </a>
                        <span class="ui-pill {{ $meta[1] }}">{{ $meta[0] }}</span>
                        @if($o->invoice)
                        <span class="ui-pill {{ $paid ? 'ui-pill-ok' : 'ui-pill-danger' }}">
                            {{ $paid ? ($isFr ? 'Payée' : 'Paid') : ($isFr ? 'Impayée' : 'Unpaid') }}
                        </span>
                        @endif
                    </div>
                    <p class="text-[12px] text-[#55524A] mt-1 truncate">{{ $req?->title }}</p>
                    <p class="text-[11.5px] text-[#8A857A] mt-0.5">
                        {{ $isSeller
                           ? ($isFr ? 'Acheteur' : 'Buyer') . ' · ' . ($req?->buyer?->name ?? '—')
                           : ($isFr ? 'Vendeur' : 'Seller') . ' · ' . ($req?->business?->name_fr ?? '—') }}
                        · {{ $o->created_at?->translatedFormat('d M Y') }}
                        @if($o->expected_delivery_date)
                            · {{ $isFr ? 'Livraison prévue' : 'Expected' }} {{ $o->expected_delivery_date->translatedFormat('d M Y') }}
                        @endif
                    </p>
                </div>

                <div class="text-left sm:text-right shrink-0">
                    <p class="text-[14px] font-bold text-[#1B1B18] whitespace-nowrap">{{ number_format((int) $o->total, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 mt-3">
                <a href="{{ route('quotes.po', ['po' => $o->id]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Bon de commande' : 'Purchase order' }}
                </a>
                @if($o->invoice)
                <a href="{{ route('quotes.invoice', ['invoice' => $o->invoice->id]) }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Facture' : 'Invoice' }}
                </a>
                @endif

                @if($isSeller && $next)
                <form method="POST" action="{{ route('orders.update-status', ['order' => $o->id]) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $next }}">
                    <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        {{ $isFr ? 'Passer à' : 'Move to' }} « {{ $statusMeta[$next][0] }} »
                    </button>
                </form>
                @endif
                {{-- Either side can pull out, but only before anything ships;
                     after that it is a return, which they settle between them. --}}
                @php $canCancel = ! in_array($o->status, ['delivered', 'cancelled'], true)
                                  && ($isSeller || ! in_array($o->status, ['shipped'], true)); @endphp
                @if($canCancel)
                <form method="POST" action="{{ route('orders.update-status', ['order' => $o->id]) }}"
                      onsubmit="return confirm('{{ $isFr ? 'Annuler cette commande ? L\'autre partie sera prévenue.' : 'Cancel this order? The other party will be notified.' }}')"
                      class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <input type="text" name="reason" maxlength="500"
                           placeholder="{{ $isFr ? 'Motif (facultatif)' : 'Reason (optional)' }}"
                           class="ui-field ui-field--sm w-[190px] max-w-full">
                    <button type="submit" class="ui-btn ui-btn-danger ui-btn-sm">
                        {{ $isFr ? 'Annuler la commande' : 'Cancel the order' }}
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if($orders->hasPages())
    <div class="pt-1">{{ $orders->links() }}</div>
    @endif
    @endif
</div>
@endsection
