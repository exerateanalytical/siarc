@extends('layouts.dashboard')

@php
$isFr = $lang === 'fr';
$pageTitle = $isFr ? ($isSeller ? 'Commandes reçues' : 'Mes commandes') : ($isSeller ? 'Received orders' : 'My orders');

// Lifecycle, in order. The seller advances an order one step at a time; the
// buyer only reads it. Kept in sync with QuoteWebController::ORDER_STATUSES.
$flow = ['confirmed', 'in_production', 'shipped', 'delivered'];
$statusMeta = [
    'confirmed'     => [$isFr ? 'Confirmée' : 'Confirmed',         'bg-[#E2F3E8] text-[#157A43]'],
    'in_production' => [$isFr ? 'En production' : 'In production', 'bg-[#FBF1DD] text-[#8A6D1F]'],
    'shipped'       => [$isFr ? 'Expédiée' : 'Shipped',            'bg-[#E4EDF7] text-[#2C5C8F]'],
    'delivered'     => [$isFr ? 'Livrée' : 'Delivered',            'bg-[#E2F3E8] text-[#14532D]'],
    'cancelled'     => [$isFr ? 'Annulée' : 'Cancelled',           'bg-[#FDE8E8] text-[#B42025]'],
    'created'       => [$isFr ? 'Créée' : 'Created',               'bg-[#F2F5F2] text-[#55524A]'],
];
$tabs = ['' => $isFr ? 'Toutes' : 'All'] + collect($flow)->mapWithKeys(fn ($s) => [$s => $statusMeta[$s][0]])->all();
@endphp

@section('content')
<div class="max-w-5xl space-y-5">

    @if(session('success'))
        <div class="flex items-start gap-2 bg-[#E2F3E8] border border-[#BFDCC8] rounded-lg px-4 py-3 text-sm text-[#14532D]">
            <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-1.5">
        @foreach($tabs as $val => $label)
        <a href="{{ route('orders.index', array_filter(['status' => $val ?: null])) }}"
           class="px-3 py-1.5 rounded-lg text-[12.5px] font-semibold border transition-colors
                  {{ (string) ($status ?? '') === $val
                     ? 'bg-[#14532D] border-[#14532D] text-white'
                     : 'bg-white border-[#ECECEA] text-[#55524A] hover:border-[#14652F] hover:text-[#14652F]' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($orders->isEmpty())
    <div class="bg-white border border-[#ECECEA] rounded-xl text-center py-14 px-4">
        <i data-lucide="clipboard-list" class="w-9 h-9 text-[#DCE7DF] mx-auto mb-3"></i>
        <p class="text-sm text-[#8A857A]">
            {{ $isSeller
               ? ($isFr ? 'Aucune commande pour le moment. Les commandes apparaissent ici quand un acheteur accepte une de vos propositions.'
                        : 'No orders yet. Orders appear here when a buyer accepts one of your proposals.')
               : ($isFr ? 'Aucune commande pour le moment. Acceptez une proposition de devis pour en créer une.'
                        : 'No orders yet. Accept a quote proposal to create one.') }}
        </p>
        <a href="{{ route('dashboard.quotes') }}" class="inline-flex items-center gap-1.5 mt-4 text-sm font-semibold text-[#14652F] hover:text-[#14532D]">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            {{ $isFr ? 'Voir les devis' : 'View quotes' }}
        </a>
    </div>
    @else
    <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
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
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $meta[1] }}">{{ $meta[0] }}</span>
                        @if($o->invoice)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $paid ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#FDE8E8] text-[#B42025]' }}">
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
                <a href="{{ route('quotes.po', ['po' => $o->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#ECECEA] text-[12px] font-semibold text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Bon de commande' : 'Purchase order' }}
                </a>
                @if($o->invoice)
                <a href="{{ route('quotes.invoice', ['invoice' => $o->invoice->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#ECECEA] text-[12px] font-semibold text-[#55524A] hover:border-[#14652F] hover:text-[#14652F] transition-colors">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Facture' : 'Invoice' }}
                </a>
                @endif

                @if($isSeller && $next)
                <form method="POST" action="{{ route('orders.update-status', ['order' => $o->id]) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $next }}">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#14652F] hover:bg-[#14532D] text-white text-[12px] font-semibold transition-colors">
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        {{ $isFr ? 'Passer à' : 'Move to' }} « {{ $statusMeta[$next][0] }} »
                    </button>
                </form>
                @endif
                @if($isSeller && $o->status !== 'delivered' && $o->status !== 'cancelled')
                <form method="POST" action="{{ route('orders.update-status', ['order' => $o->id]) }}"
                      onsubmit="return confirm('{{ $isFr ? 'Annuler cette commande ?' : 'Cancel this order?' }}')">
                    @csrf
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#ECECEA] text-[12px] font-semibold text-[#B42025] hover:border-[#B42025] hover:bg-[#FDE8E8] transition-colors">
                        {{ $isFr ? 'Annuler' : 'Cancel' }}
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
