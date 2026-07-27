@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // Sidebar variant: procurement nav, Commandes group expanded, "Bons de cornmande" [sic] active
    $qbNavOverride = [
        ['house',          $isFr ? 'Tableau de bord' : 'Dashboard', route('dashboard.buyer', ['lang' => $lang]), false, null, null],
        ['search',         $isFr ? 'Demandes (RFQ)' : 'Requests (RFQ)', route('quotes.index', ['lang' => $lang]), false, '5', 'green'],
        ['square-pen',     'Propositions', route('quotes.index', ['lang' => $lang, 'tab' => 'propositions']), false, null, null],
        ['message-circle', $isFr ? 'Commandes' : 'Orders', 'group', [
            [$isFr ? 'Toutes les commandes' : 'All orders', route('messages.inbox', ['lang' => $lang]), false, null, null],
            [$isFr ? 'Bons de cornmande' : 'Purchase orders', route('orders.index', ['lang' => $lang]),    true,  null, null],
            [$isFr ? 'En production' : 'In production',     route('messages.inbox', ['lang' => $lang]), false, null, null],
            [$isFr ? 'Expéditions' : 'Shipments',           route('support.index', ['lang' => $lang]),  false, null, null],
            [$isFr ? 'Livraisons' : 'Deliveries',           route('support.index', ['lang' => $lang]),  false, null, null],
        ]],
        ['message-circle', 'Messages',                       route('messages.inbox', ['lang' => $lang]), false, '8', 'green'],
        ['users',          $isFr ? 'Fournisseurs' : 'Suppliers', route('businesses.index', ['lang' => $lang]), false, null, null],
        ['package',        $isFr ? 'Produits' : 'Products',  route('products.index', ['lang' => $lang]), false, null, null],
        ['credit-card',    $isFr ? 'Paiements' : 'Payments', route('profile.show', ['lang' => $lang]), false, null, null],
        ['file-text',      'Documents',                      route('membership.certificate', ['lang' => $lang]), false, null, null],
        ['settings',       $isFr ? 'Paramètres' : 'Settings', route('profile.show', ['lang' => $lang]), false, null, null],
    ];

    // Real purchase order threading (?po=ID, authorized in the route)
    $rpo   = $realPo;
    $rpp   = $rpo->proposal;
    $rq    = $rpp->request;
    $biz   = $rq?->business;
    $buyer = $rq?->buyer;

    // [thumb, name, desc, qty, unit, total]
    $poRows = $rpp->items->map(fn ($it, $i) => [
        'qv-prod-' . (($i % 4) + 1) . '.png',
        $it->name,
        $it->description ?? '',
        (string) $it->quantity,
        number_format($it->unit_price),
        number_format($it->total),
    ])->all();

    $realPoRef     = $rpo->reference;
    $realQuoRef    = $rpp->reference;
    $realOrderDate = $rpo->created_at->format('d/m/Y');
    $realConfirmAt = $rpo->updated_at->format('d/m/Y H:i');
    $realDelivery  = $rpo->expected_delivery_date?->format('d/m/Y');
    $realDeliveryIn = $rpo->expected_delivery_date
        ? max((int) now()->startOfDay()->diffInDays($rpo->expected_delivery_date, false), 0)
        : null;
    $realBizName   = $biz->name_fr ?? '—';
    $realBizLogo   = $biz?->logo ? asset('storage/' . $biz->logo) : asset('images/landing/logo.png');
    $realBuyerName = $buyer->name ?? '—';
    $realItemCount = $rpp->items->count();
    $realTotalQty  = $rpp->items->sum('quantity');
    $realTotal     = number_format($rpo->total) . ' FCFA';

    $poStatusLabels = $isFr
        ? ['created' => 'Créé', 'confirmed' => 'Confirmé', 'in_production' => 'En production', 'shipped' => 'Expédié', 'delivered' => 'Livré', 'cancelled' => 'Annulé']
        : ['created' => 'Created', 'confirmed' => 'Confirmed', 'in_production' => 'In production', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
    $poStatusLabel = $poStatusLabels[$rpo->status] ?? $rpo->status;

    // [bold label, rest] — only conditions the record actually carries
    $poConditions = array_values(array_filter([
        $realDelivery ? [($isFr ? 'Livraison:' : 'Delivery:'), $realDelivery] : null,
        $rpp->delivery_location ? [($isFr ? 'Lieu de livraison:' : 'Delivery location:'), $rpp->delivery_location] : null,
        $rpp->incoterms ? ['Incoterm:', $rpp->incoterms] : null,
        $rpp->payment_terms ? [($isFr ? 'Paiement:' : 'Payment:'), $rpp->payment_terms] : null,
        $rpp->production_delay ? [($isFr ? 'Production:' : 'Production:'), $rpp->production_delay] : null,
        $rpp->delivery_delay ? [($isFr ? 'Délai de livraison:' : 'Delivery time:'), $rpp->delivery_delay] : null,
    ]));

    // [label, value, color, bold]
    $poTotals = [
        [$isFr ? 'Sous-total' : 'Subtotal', number_format($rpp->subtotal) . ' FCFA', '#1B1B18', false],
        [($isFr ? 'Remise globale' : 'Global discount') . ' (' . rtrim(rtrim(number_format($rpp->global_discount_pct, 2), '0'), '.') . '%)', '-' . number_format($rpp->discount_amount) . ' FCFA', '#E5484D', false],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)', number_format($rpp->tax_amount) . ' FCFA', '#1B1B18', false],
        [$isFr ? 'Frais de livraison' : 'Delivery costs', number_format($rpp->delivery_fee) . ' FCFA', '#1B1B18', false],
        [$isFr ? 'Assurance' : 'Insurance', number_format($rpp->insurance_fee) . ' FCFA', '#1B1B18', false],
    ];

    // [icon, title, sub, done] — driven by the order status
    $poStage = ['created' => 0, 'confirmed' => 1, 'in_production' => 2, 'shipped' => 3, 'delivered' => 4][$rpo->status] ?? 0;
    $poTimeline = collect([
        ['check',    $isFr ? 'Bon de commande créé' : 'Purchase order created'],
        ['check',    $isFr ? 'Confirmé par le fournisseur' : 'Confirmed by the supplier'],
        ['settings', $isFr ? 'En production' : 'In production'],
        ['truck',    $isFr ? 'Expédié' : 'Shipped'],
        ['package',  $isFr ? 'Livré' : 'Delivered'],
    ])->map(function ($step, $i) use ($poStage, $rpo, $realConfirmAt, $isFr) {
        $done = $i <= $poStage;
        $sub  = ! $done
            ? ($isFr ? 'En attente' : 'Pending')
            : ($i === 0 ? $rpo->created_at->format('d/m/Y H:i') : ($i === $poStage ? $realConfirmAt : ''));

        return [$step[0], $step[1], $sub, $done];
    })->all();

    // [icon (img|lucide), title, file]
    $poDocs = array_values(array_filter([
        ['img', $isFr ? 'Devis' : 'Quote', $realQuoRef . '.pdf'],
        $rpo->invoice ? ['img', $isFr ? 'Facture' : 'Invoice', $rpo->invoice->reference . '.pdf'] : null,
    ]));

    // [icon, label, url, danger]
    $poActions = [
        ['send',       $isFr ? 'Envoyer un message au fournisseur' : 'Send a message to the supplier', route('messages.inbox', ['lang' => $lang]), false],
        ['square-pen', $isFr ? 'Demander une modification' : 'Request a modification',                 route('messages.inbox', ['lang' => $lang]), false],
        ['circle-x',   $isFr ? 'Annuler la commande' : 'Cancel the order',                             route('quotes.index', ['lang' => $lang]),   true],
        ['copy',       $isFr ? 'Dupliquer la commande' : 'Duplicate the order',                        route('quotes.create', ['lang' => $lang]),  false],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Bon de commande — Artisan Hub 237' : 'Purchase order — Artisan Hub 237' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        #qb-sidebar { display: none; }
        #qb-sidebar.open { display: block; position: fixed; inset: 0 auto 0 0; width: 290px; z-index: 60; overflow-y: auto; background: #fff; }
        @media (min-width: 1024px) { #qb-sidebar, #qb-sidebar.open { display: block; position: static; width: 264px; overflow-y: visible; } }
    </style>
    @include('pages.partials.ui-kit')
</head>
<body class="bg-[#F7F8F7] text-[#1B1B18] antialiased">

@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        @if(session('success'))
        <div class="mb-4 ui-alert ui-alert-ok">
            <i data-lucide="circle-check" class="w-4 h-4"></i>
            {{ session('success') }}
            @if($rpo->invoice)
            — <a href="{{ route('quotes.invoice', ['lang' => $lang, 'invoice' => $rpo->invoice->id]) }}" class="font-bold underline underline-offset-2">{{ $isFr ? 'Voir la facture' : 'View the invoice' }} {{ $rpo->invoice->reference }}</a>
            @endif
        </div>
        @endif

        <!-- Breadcrumb + title -->
        <nav class="flex items-center gap-2 text-[12.5px] text-[#55524A]">
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Commandes' : 'Orders' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('orders.index', ['lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Bons de commande' : 'Purchase orders' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Détails du bon de commande' : 'Purchase order details' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[22px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Bon de commande' : 'Purchase order' }}
                    <span class="ui-pill ui-pill-ok">{{ $poStatusLabel }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    PO N°: <span class="font-semibold text-[#1B1B18]">{{ $realPoRef }}</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Basé sur' : 'Based on' }}: <span class="font-semibold text-[#1B1B18]">{{ $realQuoRef }}</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Date de commande' : 'Order date' }}: <span class="font-semibold text-[#1B1B18]">{{ $realOrderDate }}</span>
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
                </button>
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="printer" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Imprimer' : 'Print' }}
                </button>
                <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">
                    <i data-lucide="badge-check" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Partager le bon de commande' : 'Share the purchase order' }}
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Supplier / buyer / dates -->
                <section class="ui-card grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex items-start gap-4">
                        <img src="{{ $realBizLogo }}" alt="{{ $realBizName }}" class="w-[84px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="ui-dt">{{ $isFr ? 'Fournisseur' : 'Supplier' }}</p>
                            <p class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="text-[14.5px] font-bold text-[#1B1B18]">{{ $realBizName }}</span>
                                @if(in_array($biz?->verification_tier, ['verified', 'certified'], true))
                                <span class="inline-flex items-center gap-1 bg-[#E2F3E8] rounded-md px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="check" class="w-2.5 h-2.5" style="stroke-width:3.4"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                                @endif
                            </p>
                            @if($biz?->address_fr)
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $isFr ? $biz->address_fr : ($biz->address_en ?: $biz->address_fr) }}</p>
                            @endif
                            @if($biz?->phone)
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $biz->phone }}</p>
                            @endif
                            @if($biz?->email)
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $biz->email }}</p>
                            @endif
                            <a href="{{ $vendorUrl }}" class="mt-2 inline-block text-[12px] font-semibold text-[#1B1B18] underline underline-offset-4 hover:text-[#14652F]">{{ $isFr ? 'Voir le profil du fournisseur' : 'View the supplier profile' }}</a>
                        </div>
                    </div>
                    <div>
                        <p class="ui-dt">{{ $isFr ? 'Acheteur' : 'Buyer' }}</p>
                        <p class="ui-dd text-[14.5px]">{{ $realBuyerName }}</p>
                        @if($buyer?->email)
                        <p class="mt-2 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $buyer->email }}</p>
                        @endif
                        @if($buyer?->phone)
                        <p class="mt-2 text-[12.5px] text-[#3B382F]">{{ $buyer->phone }}</p>
                        @endif
                    </div>
                    <div class="flex items-start gap-3.5">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="ui-card-title">{{ $isFr ? 'Dates importantes' : 'Important dates' }}</p>
                            <p class="mt-2.5 ui-dt">{{ $isFr ? 'Date de commande' : 'Order date' }}</p>
                            <p class="ui-dd">{{ $realOrderDate }}</p>
                            <p class="mt-2 ui-dt">{{ $isFr ? 'Date de livraison prévue' : 'Expected delivery date' }}</p>
                            <p class="ui-dd text-[#E5484D]">{{ $realDelivery ?? '—' }} @if($realDeliveryIn !== null)<span class="font-semibold">({{ $realDeliveryIn }} {{ $isFr ? 'jours' : 'days' }})</span>@endif</p>
                            <p class="mt-2 ui-dt">{{ $isFr ? 'Statut' : 'Status' }}</p>
                            <p class="ui-dd text-[#157A43]">{{ $poStatusLabel }}</p>
                        </div>
                    </div>
                </section>

                <!-- Articles table -->
                <section class="mt-4 ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Articles commandés' : 'Ordered items' }}</h2>
                    <div class="mt-3 ui-table-wrap">
                        <table class="ui-table min-w-[820px]">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ $isFr ? 'Produit' : 'Product' }}</th>
                                    <th>Description</th>
                                    <th class="text-center">{{ $isFr ? 'Qté commandée' : 'Ordered qty' }}</th>
                                    <th class="text-right">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                    <th class="text-right">Total (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($poRows as $ri => [$pImg, $pName, $pDesc, $pQty, $pUnit, $pTotal])
                                <tr>
                                    <td class="align-top text-[#1B1B18]">{{ $ri + 1 }}</td>
                                    <td class="align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $pImg) }}" alt="" class="w-[42px] h-[42px] shrink-0 rounded-lg object-cover">
                                            <p class="text-[13px] font-bold text-[#1B1B18] max-w-[180px]">{{ $pName }}</p>
                                        </div>
                                    </td>
                                    <td class="align-top text-[#3565DE] leading-relaxed max-w-[240px]">{{ $pDesc }}</td>
                                    <td class="align-top text-center font-bold text-[#1B1B18] whitespace-nowrap">{{ $pQty }}<br><span class="font-normal text-[11.5px] text-[#3565DE]">{{ $isFr ? 'Pièces' : 'Pieces' }}</span></td>
                                    <td class="align-top text-right font-semibold text-[#1B1B18] whitespace-nowrap">{{ $pUnit }}</td>
                                    <td class="align-top text-right font-bold text-[#1B1B18] whitespace-nowrap">{{ $pTotal }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Conditions / instructions / totals -->
                    <div class="mt-5 border-t border-[#F0F1F0] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="ui-card-title">{{ $isFr ? 'Conditions de commande' : 'Order conditions' }}</h3>
                            <ul class="mt-3 space-y-2.5">
                                @foreach($poConditions as [$pcLabel, $pcRest])
                                <li class="flex items-start gap-2.5 text-[12px] text-[#3B382F] leading-relaxed">
                                    <i data-lucide="circle-check" class="w-4 h-4 shrink-0 mt-0.5 text-[#1F8A4C]" style="stroke-width:2"></i>
                                    <span><span class="font-bold">{{ $pcLabel }}</span> {{ $pcRest }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h3 class="ui-card-title">{{ $isFr ? 'Instructions spéciales' : 'Special instructions' }}</h3>
                            <p class="mt-3 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $rpp->notes ?: '—' }}</p>
                            <p class="mt-4 text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Personne de contact:' : 'Contact person:' }}</p>
                            <p class="mt-1 text-[12.5px] text-[#3B382F]">{{ $realBuyerName }}</p>
                            @if($buyer?->phone)
                            <p class="mt-1.5 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $buyer->phone }}</p>
                            @endif
                            @if($buyer?->email)
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $buyer->email }}</p>
                            @endif
                        </div>
                        <div>
                            <dl class="space-y-2.5">
                                @foreach($poTotals as [$ptLabel, $ptValue, $ptColor])
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[12.5px] text-[#3B382F]">{{ $ptLabel }}</dt>
                                    <dd class="text-[12.5px] font-semibold" style="color:{{ $ptColor }}">{{ $ptValue }}</dd>
                                </div>
                                @endforeach
                            </dl>
                            <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                                <span class="text-[14.5px] font-bold text-[#157A43]">{{ $isFr ? 'TOTAL À PAYER' : 'TOTAL TO PAY' }}</span>
                                <span class="text-[15.5px] font-bold text-[#157A43]">{{ $realTotal }}</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Order status timeline -->
                <section class="mt-4 ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Statut de la commande' : 'Order status' }}</h2>
                    <div class="mt-6 grid grid-cols-5">
                        @foreach($poTimeline as $tlIdx => [$tlIcon, $tlTitle, $tlSub, $tlDone])
                        <div class="text-center">
                            <div class="flex items-center">
                                <span class="flex-1 h-px {{ $tlIdx === 0 ? '' : ($poTimeline[$tlIdx - 1][3] && $tlDone ? 'bg-[#1F8A4C]' : 'bg-[#E0E4E0]') }} {{ $tlIdx === 0 ? 'bg-transparent' : '' }}"></span>
                                <span class="w-[38px] h-[38px] shrink-0 rounded-full flex items-center justify-center {{ $tlDone ? 'bg-[#1F8A4C]' : 'bg-white border border-[#DDE2DD]' }}">
                                    <i data-lucide="{{ $tlIcon }}" class="w-[17px] h-[17px] {{ $tlDone ? 'text-white' : 'text-[#8A8F98]' }}" style="stroke-width:{{ $tlDone ? 2.8 : 1.7 }}"></i>
                                </span>
                                <span class="flex-1 h-px {{ $tlIdx === count($poTimeline) - 1 ? 'bg-transparent' : ($tlDone && $poTimeline[$tlIdx + 1][3] ? 'bg-[#1F8A4C]' : 'bg-[#E0E4E0]') }}"></span>
                            </div>
                            <p class="mt-3 text-[12px] font-bold text-[#1B1B18] leading-snug px-1">{{ $tlTitle }}</p>
                            <p class="mt-1 text-[11px] text-[#6F6B60] leading-snug">{{ $tlSub }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Résumé de la commande' : 'Order summary' }}</h2>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Nombre d\'articles' : 'Number of items' }}</dt>
                            <dd class="text-[12.5px] font-bold text-[#1B1B18]">{{ $realItemCount }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Quantité totale' : 'Total quantity' }}</dt>
                            <dd class="text-[12.5px] font-bold text-[#1B1B18]">{{ $realTotalQty }} {{ $isFr ? 'Pièces' : 'Pieces' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Montant total' : 'Total amount' }}</dt>
                            <dd class="mt-1 text-right text-[21px] font-bold text-[#157A43]">{{ $realTotal }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 border-t border-[#F0F1F0] pt-4">
                        <div class="bg-[#EFF6F1] rounded-xl px-4 py-3.5 flex items-start gap-3">
                            <i data-lucide="badge-check" class="w-[22px] h-[22px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.7"></i>
                            <p class="text-[12px] leading-relaxed">
                                <span class="font-bold text-[#157A43]">{{ $poStatusLabel }}</span><br>
                                <span class="text-[#3B382F]">{{ $isFr ? 'Le fournisseur a confirmé ce bon de commande.' : 'The supplier has confirmed this purchase order.' }}</span>
                            </p>
                        </div>
                        <p class="mt-2.5 text-[11.5px] text-[#6F6B60]">{{ $realConfirmAt }}</p>
                    </div>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Documents liés' : 'Related documents' }}</h2>
                    <div class="mt-3 space-y-3.5">
                        @foreach($poDocs as [$pdType, $pdTitle, $pdFile])
                        <div class="flex items-center gap-3.5">
                            @if($pdType === 'img')
                            <img src="{{ asset('images/landing/qv-pdf.png') }}" alt="" class="w-[26px] h-[30px] shrink-0" aria-hidden="true">
                            @else
                            <span class="w-[26px] h-[30px] shrink-0 flex items-center justify-center"><i data-lucide="file-text" class="w-[22px] h-[22px] text-[#55524A]" style="stroke-width:1.5"></i></span>
                            @endif
                            <span class="min-w-0">
                                <span class="block text-[12.5px] font-bold text-[#1B1B18]">{{ $pdTitle }}</span>
                                <span class="block text-[11.5px] text-[#6F6B60]">{{ $pdFile }}</span>
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3.5 text-right"><a href="{{ route('membership.certificate', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#3565DE]">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }}</a></p>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">Actions</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($poActions as [$paIcon, $paLabel, $paUrl, $paDanger])
                        <a href="{{ $paUrl }}" class="flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $paIcon }}" class="w-[18px] h-[18px] shrink-0 {{ $paDanger ? 'text-[#E5484D]' : 'text-[#3B382F]' }}" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] {{ $paDanger ? 'text-[#E5484D]' : 'text-[#3B382F] group-hover:text-[#14652F]' }}">{{ $paLabel }}</span>
                        </a>
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
