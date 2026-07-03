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
            [$isFr ? 'Bons de cornmande' : 'Purchase orders', route('quotes.po', ['lang' => $lang]),    true,  null, null],
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

    // [thumb, name, desc, qty, unit, total]
    $poRows = [
        ['qv-prod-1.png', $isFr ? 'Mobilier en bois massif pour hôtel' : 'Solid wood furniture for a hotel', $isFr ? 'Bois massif (Ayous), finition vernie naturelle, style moderne.' : 'Solid wood (Ayous), natural varnished finish, modern style.', '10', '175,000', '1,750,000'],
        ['qv-prod-2.png', $isFr ? 'Masque traditionnel Bamiléké' : 'Traditional Bamileke mask',              $isFr ? 'Bois de fromager sculpté à la main, origine Ouest Cameroun.' : 'Hand-carved fromager wood, West Cameroon origin.', '20', '70,000', '1,400,000'],
        ['qv-prod-3.png', $isFr ? 'Table basse décorative en bois' : 'Decorative wooden coffee table',       $isFr ? 'Bois massif, motif traditionnel camerounais, vernis naturel.' : 'Solid wood, traditional Cameroonian pattern, natural varnish.', '5', '92,000', '460,000'],
        ['qv-prod-4.png', $isFr ? 'Chaise artisanale en bois' : 'Artisanal wooden chair',                    $isFr ? 'Bois durable, assise tressée à la main.' : 'Durable wood, hand-woven seat.', '15', '58,000', '870,000'],
    ];

    // [bold label, rest]
    $poConditions = $isFr ? [
        ['Livraison:', '24 Juin 2024 (± 2 jours)'],
        ['Lieu de livraison:', 'Entrepôt Achat Pro SARL, Bonamoussadi, Douala'],
        ['Incoterm:', 'DDP (Livré droits acquittés)'],
        ['Paiement:', '50% acompte, 50% à la livraison'],
        ['Validité du bon:', '15 jours'],
        ['Garantie:', '12 mois sur tous les produits'],
    ] : [
        ['Delivery:', '24 June 2024 (± 2 days)'],
        ['Delivery location:', 'Achat Pro SARL warehouse, Bonamoussadi, Douala'],
        ['Incoterm:', 'DDP (Delivered Duty Paid)'],
        ['Payment:', '50% deposit, 50% on delivery'],
        ['Order validity:', '15 days'],
        ['Warranty:', '12 months on all products'],
    ];

    // [label, value, color, bold]
    $poTotals = [
        [$isFr ? 'Sous-total' : 'Subtotal',                        '4,480,000 FCFA', '#1B1B18', false],
        [$isFr ? 'Remise (2.33%)' : 'Discount (2.33%)',            '-104,250 FCFA',  '#E5484D', false],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', '4,375,750 FCFA', '#1B1B18', false],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',      '842,503 FCFA',   '#1B1B18', false],
        [$isFr ? 'Frais de livraison' : 'Delivery costs',          '150,000 FCFA',   '#1B1B18', false],
    ];

    // [icon, title, sub, done]
    $poTimeline = [
        ['check',    $isFr ? 'Bon de commande créé' : 'Purchase order created',        '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 16:35', true],
        ['check',    $isFr ? 'Confirmé par le fournisseur' : 'Confirmed by the supplier', '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 17:20', true],
        ['settings', $isFr ? 'En production' : 'In production', $isFr ? 'En attente' : 'Pending', false],
        ['truck',    $isFr ? 'Expédié' : 'Shipped',             $isFr ? 'En attente' : 'Pending', false],
        ['package',  $isFr ? 'Livré' : 'Delivered',             $isFr ? 'En attente' : 'Pending', false],
    ];

    // [icon (img|lucide), title, file]
    $poDocs = [
        ['img',    $isFr ? 'Devis (Version 2)' : 'Quote (Version 2)',            'QUO-2024-000189-V2.pdf'],
        ['img',    $isFr ? 'Facture proforma' : 'Proforma invoice',              'INV-2024-00056.pdf'],
        ['lucide', $isFr ? 'Conditions de livraison' : 'Delivery conditions',    'CDC-2024-00045.pdf'],
    ];

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
    <title>{{ $isFr ? 'Bon de commande — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Purchase order — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
</head>
<body class="bg-[#F7F8F7] text-[#1B1B18] antialiased">

@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        <!-- Breadcrumb + title -->
        <nav class="flex items-center gap-2 text-[12.5px] text-[#55524A]">
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Commandes' : 'Orders' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('quotes.po', ['lang' => $lang]) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Bons de commande' : 'Purchase orders' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Détails du bon de commande' : 'Purchase order details' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[22px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Bon de commande' : 'Purchase order' }}
                    <span class="bg-[#E2F3E8] rounded-md px-3 py-1 text-[11.5px] font-bold tracking-[0.03em] text-[#157A43] uppercase">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    PO N°: <span class="font-semibold text-[#1B1B18]">PO-2024-00045</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Basé sur' : 'Based on' }}: <span class="font-semibold text-[#1B1B18]">QUO-2024-000189 (Version 2)</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Date de commande' : 'Order date' }}: <span class="font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024</span>
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
                </button>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="printer" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Imprimer' : 'Print' }}
                </button>
                <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-white transition-colors">
                    <i data-lucide="badge-check" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Partager le bon de commande' : 'Share the purchase order' }}
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Supplier / buyer / dates -->
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="Art Bois Nature" class="w-[84px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Fournisseur' : 'Supplier' }}</p>
                            <p class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="text-[14.5px] font-bold text-[#1B1B18]">Art Bois Nature</span>
                                <span class="inline-flex items-center gap-1 bg-[#E2F3E8] rounded-md px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="check" class="w-2.5 h-2.5" style="stroke-width:3.4"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                            </p>
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $isFr ? 'Yaoundé, Centre, Cameroun' : 'Yaounde, Centre, Cameroon' }}</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> +237 6 70 12 34 56</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> contact@artbois.cm</p>
                            <a href="{{ $vendorUrl }}" class="mt-2 inline-block text-[12px] font-semibold text-[#1B1B18] underline underline-offset-4 hover:text-[#14652F]">{{ $isFr ? 'Voir le profil du fournisseur' : 'View the supplier profile' }}</a>
                        </div>
                    </div>
                    <div>
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Acheteur' : 'Buyer' }}</p>
                        <p class="mt-1 text-[14.5px] font-bold text-[#1B1B18]">Achat Pro SARL</p>
                        <p class="mt-2 text-[12.5px] text-[#3B382F] leading-relaxed">Bonamoussadi, Douala<br>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</p>
                        <p class="mt-2 text-[12.5px] text-[#3B382F]">NIU: P098765432109876</p>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Dates importantes' : 'Important dates' }}</p>
                            <p class="mt-2.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date de commande' : 'Order date' }}</p>
                            <p class="text-[12.5px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024</p>
                            <p class="mt-2 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date de livraison prévue' : 'Expected delivery date' }}</p>
                            <p class="text-[12.5px] font-bold text-[#E5484D]">24 {{ $isFr ? 'Juin' : 'June' }} 2024 <span class="font-semibold">(30 {{ $isFr ? 'jours' : 'days' }})</span></p>
                            <p class="mt-2 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Statut' : 'Status' }}</p>
                            <p class="text-[12.5px] font-bold text-[#157A43]">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Articles table -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Articles commandés' : 'Ordered items' }}</h2>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[820px]">
                            <thead>
                                <tr class="bg-[#F7F8F7] text-left">
                                    <th class="pl-4 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">#</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Description</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Qté commandée' : 'Ordered qty' }}</th>
                                    <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                    <th class="px-2 pr-4 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Total (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F2F1]">
                                @foreach($poRows as $ri => [$pImg, $pName, $pDesc, $pQty, $pUnit, $pTotal])
                                <tr>
                                    <td class="pl-4 pr-2 py-4 align-top text-[13px] text-[#1B1B18]">{{ $ri + 1 }}</td>
                                    <td class="px-2 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $pImg) }}" alt="" class="w-[42px] h-[42px] shrink-0 rounded-lg object-cover">
                                            <p class="text-[13px] font-bold text-[#1B1B18] max-w-[180px]">{{ $pName }}</p>
                                        </div>
                                    </td>
                                    <td class="px-2 py-4 align-top text-[12.5px] text-[#3565DE] leading-relaxed max-w-[240px]">{{ $pDesc }}</td>
                                    <td class="px-2 py-4 align-top text-center text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $pQty }}<br><span class="font-normal text-[11.5px] text-[#3565DE]">{{ $isFr ? 'Pièces' : 'Pieces' }}</span></td>
                                    <td class="px-2 py-4 align-top text-right text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $pUnit }}</td>
                                    <td class="px-2 pr-4 py-4 align-top text-right text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $pTotal }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Conditions / instructions / totals -->
                    <div class="mt-5 border-t border-[#F0F1F0] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Conditions de commande' : 'Order conditions' }}</h3>
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
                            <h3 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Instructions spéciales' : 'Special instructions' }}</h3>
                            <p class="mt-3 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? "Veuillez assurer un emballage renforcé pour l'export et apposer le logo Achat Pro SARL sur les caisses." : 'Please ensure reinforced packaging for export and affix the Achat Pro SARL logo on the crates.' }}</p>
                            <p class="mt-4 text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Personne de contact:' : 'Contact person:' }}</p>
                            <p class="mt-1 text-[12.5px] text-[#3B382F]">{{ $siacUser['name'] ?? 'Jean Dupont' }}</p>
                            <p class="mt-1.5 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> +237 6 80 00 11 22</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> jean.dupont@achatpro.cm</p>
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
                                <span class="text-[15.5px] font-bold text-[#157A43]">5,368,253 FCFA</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Order status timeline -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Statut de la commande' : 'Order status' }}</h2>
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
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de la commande' : 'Order summary' }}</h2>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Nombre d\'articles' : 'Number of items' }}</dt>
                            <dd class="text-[12.5px] font-bold text-[#1B1B18]">4</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Quantité totale' : 'Total quantity' }}</dt>
                            <dd class="text-[12.5px] font-bold text-[#1B1B18]">50 {{ $isFr ? 'Pièces' : 'Pieces' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Montant total' : 'Total amount' }}</dt>
                            <dd class="mt-1 text-right text-[21px] font-bold text-[#157A43]">5,368,253 FCFA</dd>
                        </div>
                    </dl>
                    <div class="mt-4 border-t border-[#F0F1F0] pt-4">
                        <div class="bg-[#EFF6F1] rounded-xl px-4 py-3.5 flex items-start gap-3">
                            <i data-lucide="badge-check" class="w-[22px] h-[22px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.7"></i>
                            <p class="text-[12px] leading-relaxed">
                                <span class="font-bold text-[#157A43]">{{ $isFr ? 'Commande confirmée' : 'Order confirmed' }}</span><br>
                                <span class="text-[#3B382F]">{{ $isFr ? 'Le fournisseur a confirmé ce bon de commande.' : 'The supplier has confirmed this purchase order.' }}</span>
                            </p>
                        </div>
                        <p class="mt-2.5 text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 17:20</p>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Documents liés' : 'Related documents' }}</h2>
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

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">Actions</h2>
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
