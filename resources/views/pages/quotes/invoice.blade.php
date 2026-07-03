@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // Sidebar variant: procurement nav with Factures active
    $qbNavOverride = [
        ['house',          $isFr ? 'Tableau de bord' : 'Dashboard', route('dashboard.buyer', ['lang' => $lang]), false, null, null],
        ['search',         $isFr ? 'Demandes (RFQ)' : 'Requests (RFQ)', route('quotes.index', ['lang' => $lang]), false, '5', 'green'],
        ['square-pen',     'Propositions', route('quotes.index', ['lang' => $lang, 'tab' => 'propositions']), false, null, null],
        ['message-circle', $isFr ? 'Commandes' : 'Orders', route('quotes.po', ['lang' => $lang]), false, '7', 'green'],
        ['message-circle', 'Messages',                     route('messages.inbox', ['lang' => $lang]), false, '8', 'green'],
        ['package',        $isFr ? 'Produits' : 'Products', route('products.index', ['lang' => $lang]), false, null, null],
        ['file-text',      'Factures',                     route('quotes.invoice', ['lang' => $lang]), true, null, null],
        ['credit-card',    $isFr ? 'Paiements' : 'Payments', route('profile.show', ['lang' => $lang]), false, null, null],
        ['users',          $isFr ? 'Fournisseurs' : 'Suppliers', route('businesses.index', ['lang' => $lang]), false, null, null],
        ['map-pin',        $isFr ? 'Adresses' : 'Addresses', route('profile.show', ['lang' => $lang]), false, null, null],
        ['settings',       $isFr ? 'Paramètres' : 'Settings', route('profile.show', ['lang' => $lang]), false, null, null],
    ];

    // [thumb, name, desc, qty, unit, total]
    $invRows = [
        ['qv-prod-1.png', $isFr ? 'Mobilier en bois massif pour hôtel' : 'Solid wood furniture for a hotel', $isFr ? 'Bois massif (Ayous), finition vernie naturelle, style moderne.' : 'Solid wood (Ayous), natural varnished finish, modern style.', '10', '175,000', '1,750,000'],
        ['qv-prod-2.png', $isFr ? 'Masque traditionnel Bamiléké' : 'Traditional Bamileke mask',              $isFr ? 'Bois de fromager sculpté à la main, origine Ouest Cameroun.' : 'Hand-carved fromager wood, West Cameroon origin.', '20', '70,000', '1,400,000'],
        ['qv-prod-3.png', $isFr ? 'Table basse décorative en bois' : 'Decorative wooden coffee table',       $isFr ? 'Bois massif, motif traditionnel camerounais, vernis naturel.' : 'Solid wood, traditional Cameroonian pattern, natural varnish.', '5', '92,000', '460,000'],
        ['qv-prod-4.png', $isFr ? 'Chaise artisanale en bois' : 'Artisanal wooden chair',                    $isFr ? 'Bois durable, assise tressée à la main.' : 'Durable wood, hand-woven seat.', '15', '58,000', '870,000'],
    ];

    // [label, value, color]
    $invTotals = [
        [$isFr ? 'Sous-total' : 'Subtotal',                        '4,480,000 FCFA', '#1B1B18'],
        [$isFr ? 'Remise (2.33%)' : 'Discount (2.33%)',            '-104,250 FCFA',  '#E5484D'],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', '4,375,750 FCFA', '#1B1B18'],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',      '842,503 FCFA',   '#1B1B18'],
        [$isFr ? 'Frais de livraison' : 'Delivery costs',          '150,000 FCFA',   '#1B1B18'],
    ];

    $bankRows = [
        [$isFr ? 'Titulaire' : 'Account holder', 'Art Bois Nature'],
        [$isFr ? 'Banque' : 'Bank',              'BICEC'],
        [$isFr ? 'Compte' : 'Account',           '10015 00023 4587 89654 23'],
        ['IBAN',                                  'CM21 10015 00023 4587 89654 23'],
        ['SWIFT',                                 'BICECMCX'],
    ];

    // [title, timestamp]
    $invHistory = [
        [$isFr ? 'Facture créée' : 'Invoice created',      '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 14:32'],
        [$isFr ? 'Envoyée au client' : 'Sent to the client', '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 14:35'],
        [$isFr ? 'Vue par le client' : 'Viewed by the client', '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 15:10'],
        [$isFr ? 'Paiement reçu' : 'Payment received',     '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 16:35'],
        [$isFr ? 'Facture payée' : 'Invoice paid',         '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 16:35'],
    ];

    // [icon, label, print]
    $invActions = [
        ['send',       $isFr ? 'Envoyer par email' : 'Send by email',        route('messages.inbox', ['lang' => $lang])],
        ['rotate-ccw', $isFr ? 'Créer un avoir' : 'Create a credit note',    route('messages.inbox', ['lang' => $lang])],
        ['printer',    $isFr ? 'Imprimer la facture' : 'Print the invoice',  null],
        ['download',   $isFr ? 'Télécharger PDF' : 'Download PDF',           null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Détails de la facture — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Invoice details — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
        <nav class="flex items-center gap-2 text-[12.5px]">
            <a href="{{ route('quotes.invoice', ['lang' => $lang]) }}" class="font-semibold text-[#3565DE] hover:text-[#14652F]">Factures</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#55524A]"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Détails de la facture' : 'Invoice details' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[24px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Facture' : 'Invoice' }}
                    <span id="inv-pill" class="bg-[#E2F3E8] rounded-md px-3 py-1 text-[11.5px] font-bold tracking-[0.03em] text-[#157A43] uppercase">{{ $isFr ? 'Payée' : 'Paid' }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Facture' : 'Invoice' }} #: <span class="font-semibold text-[#1B1B18]">INV-2024-00056</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Basée sur' : 'Based on' }}: <span class="font-semibold text-[#1B1B18]">QUO-2024-000189 (Version 2)</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Artisan' : 'Artisan' }}: <span class="font-semibold text-[#1B1B18]">Art Bois Nature</span>
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
                <button type="button" id="inv-toggle" class="inline-flex items-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-white transition-colors">
                    <i data-lucide="rotate-ccw" class="w-4 h-4" style="stroke-width:1.8"></i>
                    <span id="inv-toggle-label">{{ $isFr ? 'Marquer comme impayée' : 'Mark as unpaid' }}</span>
                </button>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Parties / dates card -->
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="Art Bois Nature" class="w-[86px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2">
                                <span class="text-[14px] font-bold text-[#1B1B18]">Art Bois Nature</span>
                                <span class="inline-flex items-center gap-1 bg-[#E2F3E8] rounded-md px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="check" class="w-2.5 h-2.5" style="stroke-width:3.4"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                            </p>
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $isFr ? 'Yaoundé, Centre, Cameroun' : 'Yaounde, Centre, Cameroon' }}</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> +237 6 70 12 34 56</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> contact@artbois.cm</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="file-text" class="w-3.5 h-3.5 text-[#55524A]"></i> RCCM: RC/YAO/2020/B/1234</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="file-badge" class="w-3.5 h-3.5 text-[#55524A]"></i> NIU: P123456789012345</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Facturé à' : 'Billed to' }}</p>
                        <p class="mt-1.5 text-[14px] font-bold text-[#1B1B18]">Achat Pro SARL</p>
                        <p class="mt-2 text-[12.5px] text-[#3B382F] leading-relaxed">Bonamoussadi, Douala<br>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</p>
                        <p class="mt-2 text-[12.5px] text-[#3B382F]">NIU: P098765432109876</p>
                    </div>
                    <div class="flex items-start gap-3.5">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date de facture' : 'Invoice date' }}</p>
                            <p class="text-[12.5px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024</p>
                            <p class="mt-2.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date d\'échéance' : 'Due date' }}</p>
                            <p class="text-[12.5px] font-bold text-[#E5484D]">08 {{ $isFr ? 'Juin' : 'June' }} 2024 <span class="font-semibold">(14 {{ $isFr ? 'jours' : 'days' }})</span></p>
                            <p class="mt-2.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Statut' : 'Status' }}</p>
                            <p id="inv-statut" class="text-[12.5px] font-bold tracking-[0.03em] text-[#157A43] uppercase">{{ $isFr ? 'Payée' : 'Paid' }}</p>
                        </div>
                    </div>
                </section>

                <!-- Invoice details -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Détails de la facture' : 'Invoice details' }}</h2>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[820px]">
                            <thead>
                                <tr class="bg-[#F7F8F7] text-left">
                                    <th class="pl-4 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">#</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Description</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Unité' : 'Unit' }}</th>
                                    <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                    <th class="px-2 pr-4 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Total (FCFA)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F2F1]">
                                @foreach($invRows as $ri => [$vImg, $vName, $vDesc, $vQty, $vUnit, $vTotal])
                                <tr>
                                    <td class="pl-4 pr-2 py-4 align-top text-[13px] text-[#1B1B18]">{{ $ri + 1 }}</td>
                                    <td class="px-2 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $vImg) }}" alt="" class="w-[40px] h-[40px] shrink-0 rounded-lg object-cover">
                                            <p class="text-[13px] font-bold text-[#1B1B18] max-w-[170px]">{{ $vName }}</p>
                                        </div>
                                    </td>
                                    <td class="px-2 py-4 align-top text-[12.5px] text-[#3B382F] leading-relaxed max-w-[230px]">{{ $vDesc }}</td>
                                    <td class="px-2 py-4 align-top text-center text-[13px] font-semibold text-[#1B1B18]">{{ $vQty }}</td>
                                    <td class="px-2 py-4 align-top text-center text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Pièces' : 'Pieces' }}</td>
                                    <td class="px-2 py-4 align-top text-right text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $vUnit }}</td>
                                    <td class="px-2 pr-4 py-4 align-top text-right text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $vTotal }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-5 border-t border-[#F0F1F0] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-[13.5px] font-bold text-[#1B1B18]">Notes</h3>
                            <p class="mt-2.5 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'Merci pour votre confiance.' : 'Thank you for your trust.' }}<br>{{ $isFr ? 'Paiement par virement bancaire ou mobile money.' : 'Payment by bank transfer or mobile money.' }}</p>
                        </div>
                        <div>
                            <h3 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations bancaires' : 'Bank information' }}</h3>
                            <dl class="mt-2.5 space-y-1.5">
                                @foreach($bankRows as [$bkLabel, $bkValue])
                                <div class="text-[12px] text-[#3B382F]"><span class="font-semibold">{{ $bkLabel }}:</span> {{ $bkValue }}</div>
                                @endforeach
                            </dl>
                        </div>
                        <div>
                            <dl class="space-y-2.5">
                                @foreach($invTotals as [$itLabel, $itValue, $itColor])
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-[12.5px] text-[#3B382F]">{{ $itLabel }}</dt>
                                    <dd class="text-[12.5px] font-semibold" style="color:{{ $itColor }}">{{ $itValue }}</dd>
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

                <!-- Invoice history -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Historique de la facture' : 'Invoice history' }}</h2>
                    <div class="mt-6 grid grid-cols-5">
                        @foreach($invHistory as $ihIdx => [$ihTitle, $ihWhen])
                        <div class="text-center">
                            <div class="flex items-center">
                                <span class="flex-1 h-px {{ $ihIdx === 0 ? 'bg-transparent' : 'bg-[#CBE3D2]' }}"></span>
                                <span class="w-[34px] h-[34px] shrink-0 rounded-full flex items-center justify-center {{ $ihIdx === count($invHistory) - 1 ? 'bg-[#1F8A4C]' : 'bg-white border-2 border-[#1F8A4C]' }}">
                                    <i data-lucide="check" class="w-4 h-4 {{ $ihIdx === count($invHistory) - 1 ? 'text-white' : 'text-[#1F8A4C]' }}" style="stroke-width:2.8"></i>
                                </span>
                                <span class="flex-1 h-px {{ $ihIdx === count($invHistory) - 1 ? 'bg-transparent' : 'bg-[#CBE3D2]' }}"></span>
                            </div>
                            <p class="mt-3 text-[12px] font-bold text-[#1B1B18] leading-snug px-1">{{ $ihTitle }}</p>
                            <p class="mt-1 text-[11px] text-[#6F6B60]">{{ $ihWhen }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé du paiement' : 'Payment summary' }}</h2>
                    <p class="mt-3.5 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Montant total' : 'Total amount' }}</p>
                    <p class="mt-0.5 text-[22px] font-bold text-[#157A43]">5,368,253 FCFA</p>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Montant payé' : 'Amount paid' }}</dt>
                            <dd class="text-[12.5px] font-bold text-[#1B1B18]">5,368,253 FCFA</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Mode de paiement' : 'Payment method' }}</dt>
                            <dd class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Virement bancaire' : 'Bank transfer' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Date de paiement' : 'Payment date' }}</dt>
                            <dd class="text-[12.5px] font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 16:35</dd>
                        </div>
                    </dl>
                    <div class="mt-4 bg-[#EFF6F1] rounded-xl px-4 py-3.5 flex items-start gap-3">
                        <i data-lucide="circle-check" class="w-[21px] h-[21px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.8"></i>
                        <p class="text-[12px] leading-relaxed">
                            <span class="font-bold text-[#157A43]">{{ $isFr ? 'Paiement confirmé' : 'Payment confirmed' }}</span><br>
                            <span class="text-[#3B382F]">{{ $isFr ? 'Cette facture a été entièrement payée.' : 'This invoice has been fully paid.' }}</span>
                        </p>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Documents associés' : 'Associated documents' }}</h2>
                    <div class="mt-3.5 space-y-3.5">
                        @foreach([[$isFr ? 'Devis (Version 2)' : 'Quote (Version 2)', 'QUO-2024-000189-V2.pdf'], [$isFr ? 'Bon de commande' : 'Purchase order', 'PO-2024-00045.pdf']] as [$adTitle, $adFile])
                        <div class="flex items-center gap-3.5">
                            <img src="{{ asset('images/landing/qv-pdf-green.png') }}" alt="" class="w-[28px] h-[32px] shrink-0" aria-hidden="true">
                            <span class="min-w-0">
                                <span class="block text-[12.5px] font-bold text-[#1B1B18]">{{ $adTitle }}</span>
                                <span class="block text-[11.5px] text-[#3565DE]">{{ $adFile }}</span>
                            </span>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3.5 text-right"><a href="{{ route('quotes.po', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#3565DE]">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }}</a></p>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($invActions as [$iaIcon, $iaLabel, $iaUrl])
                        @if($iaUrl)
                        <a href="{{ $iaUrl }}" class="flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $iaIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $iaLabel }}</span>
                        </a>
                        @else
                        <button type="button" onclick="window.print()" class="w-full flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $iaIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-left text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $iaLabel }}</span>
                        </button>
                        @endif
                        @endforeach
                    </div>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

    // "Marquer comme impayée" toggles the paid state client-side (no invoice backend)
    let invPaid = true;
    document.getElementById('inv-toggle').addEventListener('click', () => {
        invPaid = !invPaid;
        const pill = document.getElementById('inv-pill');
        const statut = document.getElementById('inv-statut');
        pill.textContent = invPaid ? @json($isFr ? 'Payée' : 'Paid') : @json($isFr ? 'Impayée' : 'Unpaid');
        pill.style.background = invPaid ? '#E2F3E8' : '#FDE8E8';
        pill.style.color = invPaid ? '#157A43' : '#E5484D';
        statut.textContent = pill.textContent;
        statut.style.color = invPaid ? '#157A43' : '#E5484D';
        document.getElementById('inv-toggle-label').textContent = invPaid
            ? @json($isFr ? 'Marquer comme impayée' : 'Mark as unpaid')
            : @json($isFr ? 'Marquer comme payée' : 'Mark as paid');
    });
</script>
</body>
</html>
