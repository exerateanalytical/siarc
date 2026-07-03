@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // [key, label, badge, url]
    $tabs = [
        ['details',    $isFr ? 'Détails de la proposition' : 'Proposal details', null, null],
        ['messages',   $isFr ? 'Messages & Négociation' : 'Messages & Negotiation', '2', route('quotes.negotiation', ['lang' => $lang])],
        ['fichiers',   $isFr ? 'Fichiers joints' : 'Attached files', '3', route('quotes.negotiation', ['lang' => $lang])],
        ['historique', $isFr ? 'Historique' : 'History', '4', route('quotes.index', ['lang' => $lang])],
    ];

    // [thumb, name, desc, qty, unit, price, discount, tax, total]
    $rows = [
        ['qv-prod-1.png', $isFr ? 'Mobilier en bois massif pour hôtel' : 'Solid wood furniture for a hotel', $isFr ? 'Bois massif (Ayous), finition vernie naturelle, style moderne.' : 'Solid wood (Ayous), natural varnished finish, modern style.', '10', '180,000', '5%', '171,000', '1,710,000'],
        ['qv-prod-2.png', $isFr ? 'Masque traditionnel Bamiléké' : 'Traditional Bamileke mask',              $isFr ? 'Bois de fromager sculpté à la main, origine Ouest Cameroun.' : 'Hand-carved fromager wood, West Cameroon origin.', '20', '75,000', '0%', '145,000', '1,500,000'],
        ['qv-prod-3.png', $isFr ? 'Table basse décorative en bois' : 'Decorative wooden coffee table',       $isFr ? 'Bois massif, motif traditionnel camerounais, vernis naturel.' : 'Solid wood, traditional Cameroonian pattern, natural varnish.', '5', '95,000', '3%', '88,250', '461,750'],
        ['qv-prod-4.png', $isFr ? 'Chaise artisanale en bois' : 'Artisanal wooden chair',                    $isFr ? 'Bois durable, assise tressée à la main.' : 'Durable wood, hand-woven seat.', '15', '60,000', '0%', '115,500', '1,080,000'],
    ];

    // [label, value, color]
    $totals = [
        [$isFr ? 'Sous-total' : 'Subtotal',                        '4,751,750 FCFA', '#1B1B18'],
        [$isFr ? 'Remise globale (2%)' : 'Global discount (2%)',   '-95,035 FCFA',   '#E5484D'],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', '4,656,715 FCFA', '#1B1B18'],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',      '895,543 FCFA',   '#1B1B18'],
        [$isFr ? 'Frais de livraison (est.)' : 'Delivery costs (est.)', '250,000 FCFA', '#1B1B18'],
        [$isFr ? 'Assurance (est.)' : 'Insurance (est.)',          '150,000 FCFA',   '#1B1B18'],
    ];

    // [icon, label]
    $importantInfo = [
        ['calendar-days', $isFr ? 'Valide jusqu\'au 25 Juin 2024' : 'Valid until 25 June 2024'],
        ['badge-percent', $isFr ? 'Les prix incluent la TVA (19.25%)' : 'Prices include VAT (19.25%)'],
        ['truck',         $isFr ? 'Livraison à Marseille, France' : 'Delivery to Marseille, France'],
        ['shield-check',  $isFr ? 'Paiement sécurisé via notre plateforme' : 'Secure payment via our platform'],
        ['badge-check',   $isFr ? 'Protection acheteur incluse' : 'Buyer protection included'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Proposition de devis — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Quote proposal — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
            <a href="{{ route('quotes.index', ['lang' => $lang]) }}" class="font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Mes demandes & devis' : 'My requests & quotes' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#55524A]"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Détail de la proposition' : 'Proposal detail' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[22px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Proposition de devis' : 'Quote proposal' }}
                    <span class="bg-[#FDF3E0] rounded-md px-3 py-1 text-[11px] font-bold tracking-[0.03em] text-[#C97A16] uppercase">{{ $isFr ? 'En attente de réponse' : 'Awaiting response' }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Proposée par' : 'Proposed by' }} <span class="font-semibold text-[#1B1B18]">Art Bois Nature</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Envoyée le' : 'Sent on' }} <span class="font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 14:32</span>
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
                </button>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="share-2" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Partager' : 'Share' }}
                </button>
                <a href="{{ route('quotes.index', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="ellipsis" class="w-4 h-4"></i>
                    {{ $isFr ? 'Plus d\'options' : 'More options' }}
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Artisan / reference / validity card -->
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 flex flex-col lg:flex-row gap-6 lg:divide-x divide-[#F0F1F0]">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="Art Bois Nature" class="w-[88px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Proposée par' : 'Proposed by' }}</p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-2">
                                <span class="text-[14.5px] font-bold text-[#1B1B18]">Art Bois Nature</span>
                                <span class="inline-flex items-center gap-1 bg-[#E2F3E8] rounded-md px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="check" class="w-2.5 h-2.5" style="stroke-width:3.4"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                            </p>
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $isFr ? 'Yaoundé, Centre, Cameroun' : 'Yaounde, Centre, Cameroon' }}</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> +237 6 70 12 34 56</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> contact@artbois.cm</p>
                            <a href="{{ $vendorUrl }}" class="mt-2 inline-block text-[12px] font-semibold text-[#1B1B18] underline underline-offset-4 hover:text-[#14652F]">{{ $isFr ? 'Voir le profil de l\'artisan' : 'View the artisan\'s profile' }}</a>
                        </div>
                    </div>
                    <div class="lg:px-6">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Référence de la demande' : 'Request reference' }}</p>
                        <p class="mt-1 text-[14px] font-bold text-[#1B1B18]">RFQ-2024-000189</p>
                        <p class="mt-4 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date de réponse souhaitée' : 'Desired response date' }}</p>
                        <p class="mt-1 text-[13.5px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024</p>
                    </div>
                    <div class="lg:pl-6 flex items-start gap-3.5">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="text-[12px] text-[#55524A]">{{ $isFr ? 'Valide jusqu\'au' : 'Valid until' }}</p>
                            <p class="mt-0.5 text-[14px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Juin' : 'June' }} 2024</p>
                            <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">(30 {{ $isFr ? 'jours restants' : 'days left' }})</p>
                        </div>
                    </div>
                </section>

                <!-- Tabs + details -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl">
                    <div class="px-5 pt-1 border-b border-[#F0F1F0] overflow-x-auto">
                        <div class="flex items-center gap-7 min-w-max">
                            @foreach($tabs as [$tKey, $tLabel, $tBadge, $tUrl])
                            @if($tKey === 'details')
                            <span class="relative py-4 text-[13px] font-bold text-[#14652F] whitespace-nowrap">
                                {{ $tLabel }}
                                <span class="absolute left-0 right-0 -bottom-px h-[3px] rounded-t bg-[#14652F]"></span>
                            </span>
                            @else
                            <a href="{{ $tUrl }}" class="py-4 text-[13px] text-[#55524A] hover:text-[#1B1B18] whitespace-nowrap inline-flex items-center gap-2">
                                {{ $tLabel }}
                                @if($tBadge)<span class="bg-[#EFF0EF] rounded-full px-2 py-0.5 text-[10.5px] font-bold text-[#3B382F]">{{ $tBadge }}</span>@endif
                            </a>
                            @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="px-5 py-5">
                        <h2 class="text-[14.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Articles proposés' : 'Proposed items' }} <span class="font-normal text-[#55524A]">(4 articles)</span></h2>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[880px]">
                                <thead>
                                    <tr class="bg-[#F7F8F7] text-left">
                                        <th class="pl-4 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">#</th>
                                        <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                        <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Description</th>
                                        <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                                        <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Unité' : 'Unit' }}</th>
                                        <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                        <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Remise' : 'Discount' }}</th>
                                        <th class="px-2 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Taxes (19.25%)</th>
                                        <th class="px-2 pr-4 py-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Total (FCFA)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F1F2F1]">
                                    @foreach($rows as $ri => [$rImg, $rName, $rDesc, $rQty, $rPrice, $rDisc, $rTax, $rTotal])
                                    <tr>
                                        <td class="pl-4 pr-2 py-4 align-top text-[13px] text-[#1B1B18]">{{ $ri + 1 }}</td>
                                        <td class="px-2 py-4 align-top">
                                            <div class="flex items-start gap-3">
                                                <img src="{{ asset('images/landing/' . $rImg) }}" alt="" class="w-[40px] h-[40px] shrink-0 rounded-lg object-cover">
                                                <p class="text-[12.5px] font-bold text-[#1B1B18] max-w-[150px] leading-snug">{{ $rName }}</p>
                                            </div>
                                        </td>
                                        <td class="px-2 py-4 align-top text-[12px] text-[#3B382F] leading-relaxed max-w-[190px]">{{ $rDesc }}</td>
                                        <td class="px-2 py-4 align-top text-center text-[13px] font-semibold text-[#1B1B18]">{{ $rQty }}</td>
                                        <td class="px-2 py-4 align-top text-center text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Pièces' : 'Pieces' }}</td>
                                        <td class="px-2 py-4 align-top text-right text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $rPrice }}</td>
                                        <td class="px-2 py-4 align-top text-center text-[12.5px] text-[#3B382F]">{{ $rDisc }}</td>
                                        <td class="px-2 py-4 align-top text-right text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $rTax }}</td>
                                        <td class="px-2 pr-4 py-4 align-top text-right text-[13px] font-bold text-[#14652F] whitespace-nowrap">{{ $rTotal }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 border-t border-[#F0F1F0] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Conditions de paiement' : 'Payment terms' }}</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $isFr ? '50% à la commande, 50% avant expédition' : '50% on order, 50% before shipment' }}</p>
                                <h3 class="mt-4 text-[13px] font-bold text-[#1B1B18]">Incoterms</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">FOB - Free On Board</p>
                            </div>
                            <div>
                                <h3 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Délais de production' : 'Production time' }}</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $isFr ? '7 - 10 jours ouvrables' : '7 - 10 working days' }}</p>
                                <h3 class="mt-4 text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Délais de livraison' : 'Delivery time' }}</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $isFr ? '15 - 20 jours ouvrables' : '15 - 20 working days' }}</p>
                            </div>
                            <div>
                                <dl class="space-y-2.5">
                                    @foreach($totals as [$ttLabel, $ttValue, $ttColor])
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-[12.5px] text-[#3B382F]">{{ $ttLabel }}</dt>
                                        <dd class="text-[12.5px] font-semibold" style="color:{{ $ttColor }}">{{ $ttValue }}</dd>
                                    </div>
                                    @endforeach
                                </dl>
                                <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                                    <span class="text-[14.5px] font-bold text-[#157A43] uppercase">{{ $isFr ? 'Total général' : 'Grand total' }}</span>
                                    <span class="text-[15.5px] font-bold text-[#157A43]">5,952,258 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="mt-4 bg-[#EDF3FC] rounded-xl px-5 py-3.5 flex items-center gap-3">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 text-[#3565DE]"></i>
                    <p class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Cette proposition est confidentielle et destinée uniquement à l\'acheteur.' : 'This proposal is confidential and intended only for the buyer.' }}</p>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Statut de la proposition' : 'Proposal status' }}</h2>
                    <p class="mt-3.5 flex items-center gap-2.5">
                        <span class="w-[22px] h-[22px] shrink-0 rounded-full bg-[#F5B301] flex items-center justify-center"><i data-lucide="clock" class="w-3 h-3 text-white" style="stroke-width:2.6"></i></span>
                        <span class="bg-[#FDF3E0] rounded-md px-3 py-1 text-[11px] font-bold tracking-[0.03em] text-[#C97A16] uppercase">{{ $isFr ? 'En attente de réponse' : 'Awaiting response' }}</span>
                    </p>
                    <p class="mt-3 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'L\'artisan attend votre réponse.' : 'The artisan awaits your reply.' }}<br>{{ $isFr ? 'Délai estimé : 1 à 3 jours ouvrables' : 'Estimated time: 1 to 3 working days' }}</p>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-3.5 space-y-3">
                        <a href="{{ route('quotes.accept', ['lang' => $lang]) }}" class="flex items-center justify-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-3 text-[13px] font-semibold text-white transition-colors">
                            <i data-lucide="circle-check" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                            {{ $isFr ? 'Accepter la proposition' : 'Accept the proposal' }}
                        </a>
                        <a href="{{ route('quotes.negotiation', ['lang' => $lang]) }}" class="flex items-center justify-center gap-2.5 bg-white border border-[#EFCF9E] hover:border-[#C97A16] rounded-lg px-4 py-3 text-[13px] font-semibold text-[#C97A16] transition-colors">
                            <i data-lucide="square-pen" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                            {{ $isFr ? 'Demander des modifications' : 'Request modifications' }}
                        </a>
                        <a href="{{ route('quotes.index', ['lang' => $lang]) }}" class="flex items-center justify-center gap-2.5 bg-white border border-[#F5C9C9] hover:border-[#E5484D] rounded-lg px-4 py-3 text-[13px] font-semibold text-[#E5484D] transition-colors">
                            <i data-lucide="x" class="w-[17px] h-[17px]" style="stroke-width:2"></i>
                            {{ $isFr ? 'Refuser la proposition' : 'Refuse the proposal' }}
                        </a>
                        <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="flex items-center justify-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-3 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                            <i data-lucide="message-circle" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                            {{ $isFr ? 'Poser une question' : 'Ask a question' }}
                        </a>
                    </div>
                </section>

                <section class="bg-[#EFF6F1] rounded-2xl px-5 py-4 flex items-start gap-3">
                    <i data-lucide="piggy-bank" class="w-[22px] h-[22px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.7"></i>
                    <p class="text-[12px] leading-relaxed">
                        <span class="font-bold text-[#157A43]">{{ $isFr ? 'Vous économisez' : 'You save' }}</span><br>
                        <span class="text-[#3B382F]">95,035 FCFA {{ $isFr ? 'sur cette proposition' : 'on this proposal' }}</span>
                    </p>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[13.5px] font-bold text-[#3565DE]">{{ $isFr ? 'Informations importantes' : 'Important information' }}</h2>
                    <ul class="mt-3.5 space-y-3">
                        @foreach($importantInfo as [$iiIcon, $iiLabel])
                        <li class="flex items-center gap-3 text-[12px] text-[#3B382F]">
                            <i data-lucide="{{ $iiIcon }}" class="w-[17px] h-[17px] shrink-0 text-[#55524A]" style="stroke-width:1.6"></i>
                            {{ $iiLabel }}
                        </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
