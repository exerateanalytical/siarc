@php
    $isFr = $lang === 'fr';

    $steps = $isFr ? [
        ['1', 'Informations',         'Détails généraux'],
        ['2', 'Articles',             'Produits demandés'],
        ['3', 'Détails & Conditions', 'Livraison, paiement...'],
        ['4', 'Aperçu',               'Vérification'],
        ['5', 'Envoyer',              'Confirmation'],
    ] : [
        ['1', 'Information',          'General details'],
        ['2', 'Items',                'Requested products'],
        ['3', 'Details & Conditions', 'Delivery, payment...'],
        ['4', 'Preview',              'Verification'],
        ['5', 'Send',                 'Confirmation'],
    ];

    // [thumb, name, desc, qty, price, discount, tax, total]
    $docRows = [
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

    $docNotes = $isFr ? [
        'Les délais sont donnés à titre indicatif et peuvent varier selon le planning.',
        "Les frais de livraison, d'assurance et d'emballage sont inclus dans cette proposition.",
        "Cette proposition est confidentielle et destinée uniquement à l'acheteur.",
    ] : [
        'Lead times are indicative and may vary depending on the schedule.',
        'Delivery, insurance and packaging costs are included in this proposal.',
        'This proposal is confidential and intended only for the buyer.',
    ];

    // [label, value, link]
    $summaryRows = [
        [$isFr ? 'Référence de la demande' : 'Request reference', 'RFQ-2024-000189', true],
        [$isFr ? 'Artisan / Entreprise' : 'Artisan / Business',   'Art Bois Nature', false],
        [$isFr ? 'Date de réponse' : 'Response date',             $isFr ? '25 Mai 2024' : '25 May 2024', false],
        [$isFr ? 'Validité de l\'offre' : 'Offer validity',       '25/06/2024 (30 ' . ($isFr ? 'jours' : 'days') . ')', false],
        [$isFr ? 'Nombre d\'articles' : 'Number of items',        '4 articles', false],
        [$isFr ? 'Quantité totale' : 'Total quantity',            '50 ' . ($isFr ? 'unités' : 'units'), false],
    ];

    // [icon, label]
    $docActions = [
        ['download',   $isFr ? 'Télécharger en PDF' : 'Download as PDF'],
        ['file-text',  $isFr ? 'Télécharger en Excel' : 'Download as Excel'],
        ['printer',    $isFr ? 'Imprimer' : 'Print'],
        ['share-2',    $isFr ? 'Partager le devis' : 'Share the quote'],
        ['maximize-2', $isFr ? 'Prévisualiser en plein écran' : 'Preview full screen'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Aperçu de la proposition de devis — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Quote proposal preview — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcode.min.js') }}"></script>
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

    <main class="flex-1 min-w-0 px-4 lg:px-6 py-6">

        <!-- Title row -->
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('quotes.builder', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                {{ $isFr ? 'Retour' : 'Back' }}
            </a>
            <div class="min-w-0">
                <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Aperçu de la proposition de devis' : 'Quote proposal preview' }}</h1>
                <p class="mt-0.5 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Vérifiez les détails de votre proposition avant de l\'envoyer à l\'artisan.' : 'Check the details of your proposal before sending it to the artisan.' }}</p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3 ml-auto">
                <a href="{{ route('quotes.builder', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="square-pen" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $isFr ? 'Modifier la proposition' : 'Edit the proposal' }}
                </a>
                <button type="button" id="rv-draft" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="file-text" class="w-4 h-4" style="stroke-width:1.7"></i>
                    <span id="rv-draft-label">{{ $isFr ? 'Enregistrer brouillon' : 'Save draft' }}</span>
                </button>
                <a href="{{ route('quotes.sent', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-white transition-colors">
                    <i data-lucide="send" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Envoyer la proposition' : 'Send the proposal' }}
                </a>
            </div>
        </div>

        <!-- Stepper (step 5 active) -->
        <div class="mt-5 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-4 overflow-x-auto">
            <div class="flex items-center gap-3 min-w-[860px]">
                @foreach($steps as $stIdx => [$stNum, $stTitle, $stSub])
                <div class="flex items-center gap-3.5 shrink-0">
                    <span class="w-[36px] h-[36px] shrink-0 rounded-full flex items-center justify-center text-[14px] font-bold {{ $stIdx === 4 ? 'bg-[#0E5A2D] text-white' : 'bg-white border border-[#D9DDD9] text-[#55524A]' }}">{{ $stNum }}</span>
                    <span>
                        <span class="block text-[13px] font-bold {{ $stIdx === 4 ? 'text-[#14652F]' : 'text-[#1B1B18]' }}">{{ $stTitle }}</span>
                        <span class="block mt-0.5 text-[11.5px] text-[#6F6B60]">{{ $stSub }}</span>
                    </span>
                </div>
                @if($stIdx < count($steps) - 1)
                <span class="flex-1 min-w-[36px] h-px bg-[#E0E4E0]"></span>
                @endif
                @endforeach
            </div>
        </div>

        <div class="mt-5 flex flex-col xl:flex-row gap-5 items-start">

            <!-- Devis document -->
            <div class="flex-1 min-w-0 w-full bg-white border border-[#ECEDEC] rounded-2xl shadow-sm px-7 py-7">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[#EFF0EF] pb-5">
                    <div class="flex items-center gap-3.5">
                        <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[46px] h-[50px] object-contain">
                        <div>
                            <p class="text-[12.5px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase leading-snug">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}<br>{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</p>
                            <p class="mt-1 text-[13.5px] font-bold tracking-[0.03em] text-[#157A43] uppercase">{{ $isFr ? 'Devis / Proposition' : 'Quote / Proposal' }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-[13px] font-bold text-[#1B1B18]">DEVIS N° QUO-2024-000189</p>
                        <p class="mt-1 text-[11.5px] text-[#55524A]">Date : 25 {{ $isFr ? 'Mai' : 'May' }} 2024</p>
                        <p class="text-[11.5px] text-[#55524A]">{{ $isFr ? 'Valide jusqu\'au' : 'Valid until' }} : 25 {{ $isFr ? 'Juin' : 'June' }} 2024</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="" class="w-[78px] shrink-0 object-contain">
                        <div class="text-[12px] text-[#3B382F] leading-relaxed">
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Proposé par' : 'Proposed by' }}</p>
                            <p class="font-bold text-[#1B1B18]">Art Bois Nature</p>
                            <p>{{ $isFr ? 'Yaoundé, Centre' : 'Yaounde, Centre' }}<br>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</p>
                            <p>Tél : +237 6 70 12 34 56</p>
                            <p>Email : contact@artbois.cm</p>
                            <p class="mt-1.5 inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-[#157A43]"><i data-lucide="badge-check" class="w-3.5 h-3.5"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</p>
                        </div>
                    </div>
                    <div class="text-[12px] text-[#3B382F] leading-relaxed sm:pl-6">
                        <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Proposé à' : 'Proposed to' }}</p>
                        <p class="font-bold text-[#1B1B18]">Achat Pro SARL</p>
                        <p>{{ $siacUser['name'] ?? 'Jean Dupont' }}</p>
                        <p>Marseille, France</p>
                        <p>Tél : +33 6 12 34 56 78</p>
                        <p>Email : jean.dupont@achatpro.com</p>
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead>
                            <tr class="bg-[#F3F6F3] text-left">
                                <th class="pl-3 pr-2 py-2.5 text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">#</th>
                                <th class="px-2 py-2.5 text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                <th class="px-2 py-2.5 text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">Description</th>
                                <th class="px-2 py-2.5 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                                <th class="px-2 py-2.5 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">{{ $isFr ? 'Unité' : 'Unit' }}</th>
                                <th class="px-2 py-2.5 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                <th class="px-2 py-2.5 text-center text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">{{ $isFr ? 'Remise' : 'Discount' }}</th>
                                <th class="px-2 py-2.5 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">Taxes (19.25%)</th>
                                <th class="px-2 pr-3 py-2.5 text-right text-[10.5px] font-bold tracking-[0.04em] text-[#55524A] uppercase">Total (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F2F1]">
                            @foreach($docRows as $ri => [$dImg, $dName, $dDesc, $dQty, $dUnitP, $dDisc, $dTax, $dTotal])
                            <tr>
                                <td class="pl-3 pr-2 py-3.5 align-top text-[12px] text-[#1B1B18]">{{ $ri + 1 }}</td>
                                <td class="px-2 py-3.5 align-top">
                                    <div class="flex items-start gap-2.5">
                                        <img src="{{ asset('images/landing/' . $dImg) }}" alt="" class="w-[34px] h-[34px] shrink-0 rounded-md object-cover">
                                        <p class="text-[12px] font-bold text-[#1B1B18] max-w-[130px] leading-snug">{{ $dName }}</p>
                                    </div>
                                </td>
                                <td class="px-2 py-3.5 align-top text-[11.5px] text-[#55524A] leading-relaxed max-w-[170px]">{{ $dDesc }}</td>
                                <td class="px-2 py-3.5 align-top text-center text-[12px] font-semibold text-[#1B1B18]">{{ $dQty }}</td>
                                <td class="px-2 py-3.5 align-top text-center text-[12px] text-[#3B382F]">{{ $isFr ? 'Pièces' : 'Pieces' }}</td>
                                <td class="px-2 py-3.5 align-top text-right text-[12px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $dUnitP }}</td>
                                <td class="px-2 py-3.5 align-top text-center text-[12px] text-[#3B382F]">{{ $dDisc }}</td>
                                <td class="px-2 py-3.5 align-top text-right text-[12px] text-[#3B382F] whitespace-nowrap">{{ $dTax }}</td>
                                <td class="px-2 pr-3 py-3.5 align-top text-right text-[12px] font-bold text-[#14652F] whitespace-nowrap">{{ $dTotal }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 border-t border-[#EFF0EF] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <h3 class="text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Conditions de paiement' : 'Payment terms' }}</h3>
                        <p class="mt-1.5 text-[12px] text-[#3B382F]">{{ $isFr ? '50% à la commande, 50% avant expédition.' : '50% on order, 50% before shipment.' }}</p>
                        <h3 class="mt-4 text-[12.5px] font-bold text-[#1B1B18]">Incoterms</h3>
                        <p class="mt-1.5 text-[12px] text-[#3B382F]">FOB - Free On Board</p>
                        <h3 class="mt-4 text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Devise' : 'Currency' }}</h3>
                        <p class="mt-1.5 text-[12px] text-[#3B382F]">FCFA - Franc CFA</p>
                    </div>
                    <div>
                        <h3 class="text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Délais' : 'Lead times' }}</h3>
                        <p class="mt-1.5 text-[12px] text-[#3B382F]">Production : {{ $isFr ? '7 - 10 jours ouvrables' : '7 - 10 working days' }}<br>{{ $isFr ? 'Livraison' : 'Delivery' }} : {{ $isFr ? '15 - 20 jours ouvrables' : '15 - 20 working days' }}</p>
                        <h3 class="mt-4 text-[12.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Lieu de livraison' : 'Delivery location' }}</h3>
                        <p class="mt-1.5 text-[12px] text-[#3B382F]">Marseille, France</p>
                    </div>
                    <div>
                        <dl class="space-y-2">
                            @foreach($totals as [$ttLabel, $ttValue, $ttColor])
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[12px] text-[#3B382F]">{{ $ttLabel }}</dt>
                                <dd class="text-[12px] font-semibold" style="color:{{ $ttColor }}">{{ $ttValue }}</dd>
                            </div>
                            @endforeach
                        </dl>
                        <div class="mt-3.5 border-t border-[#EFF0EF] pt-3.5 flex items-center justify-between gap-3">
                            <span class="text-[14px] font-bold text-[#157A43] uppercase">{{ $isFr ? 'Total général' : 'Grand total' }}</span>
                            <span class="text-[15px] font-bold text-[#157A43]">5,952,258 FCFA</span>
                        </div>
                    </div>
                </div>

                <div class="mt-7 flex flex-wrap items-end justify-between gap-6 border-t border-[#EFF0EF] pt-5">
                    <div class="max-w-[420px]">
                        <h3 class="text-[12.5px] font-bold text-[#1B1B18]">Notes</h3>
                        <ul class="mt-1.5 space-y-1.5">
                            @foreach($docNotes as $dn)
                            <li class="flex items-start gap-2 text-[11.5px] text-[#55524A] leading-relaxed">
                                <span class="mt-[7px] w-[3px] h-[3px] shrink-0 rounded-full bg-[#55524A]"></span>
                                {{ $dn }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="text-center">
                        <img src="{{ asset('images/landing/qv-sign-abn.png') }}" alt="" class="h-[30px] mx-auto object-contain">
                        <p class="mt-1.5 pt-1.5 border-t border-[#D9DDD9] text-[11px] text-[#55524A]">{{ $isFr ? 'Signature autorisée' : 'Authorised signature' }}<br>Art Bois Nature</p>
                    </div>
                    <div class="flex items-center gap-3.5">
                        <span id="rv-qr" class="inline-block w-[62px] h-[62px]"></span>
                        <p class="text-[10.5px] text-[#55524A] leading-snug max-w-[110px]">{{ $isFr ? 'Scanner pour vérifier l\'authenticité de ce devis' : 'Scan to verify the authenticity of this quote' }}</p>
                    </div>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full xl:w-[290px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de la proposition' : 'Proposal summary' }}</h2>
                    <dl class="mt-4 space-y-3">
                        @foreach($summaryRows as [$smLabel, $smValue, $smLink])
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-[12px] text-[#55524A]">{{ $smLabel }}</dt>
                            <dd class="text-right">
                                @if($smLink)
                                <a href="{{ route('quotes.create', ['lang' => $lang]) }}" class="text-[12px] font-bold text-[#3565DE] underline underline-offset-2">{{ $smValue }}</a>
                                @else
                                <span class="text-[12px] font-semibold text-[#1B1B18]">{{ $smValue }}</span>
                                @endif
                            </dd>
                        </div>
                        @endforeach
                    </dl>
                    <div class="mt-4 bg-[#EFF6F1] rounded-xl px-4 py-3.5 flex items-start gap-3">
                        <i data-lucide="badge-percent" class="w-[19px] h-[19px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.7"></i>
                        <p class="text-[12px] leading-relaxed">
                            <span class="font-bold text-[#157A43]">{{ $isFr ? 'Vous économisez' : 'You save' }}</span><br>
                            <span class="text-[#3B382F]">95,035 FCFA {{ $isFr ? 'sur cette proposition' : 'on this proposal' }}</span>
                        </p>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions du document' : 'Document actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($docActions as [$daIcon, $daLabel])
                        <button type="button" onclick="window.print()" class="w-full flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $daIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-left text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $daLabel }}</span>
                        </button>
                        @endforeach
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Prêt à envoyer ?' : 'Ready to send?' }}</h2>
                    <p class="mt-2 text-[12px] text-[#55524A] leading-relaxed">{{ $isFr ? 'Une fois envoyée, l\'artisan sera notifié et pourra répondre à votre proposition.' : 'Once sent, the artisan will be notified and can reply to your proposal.' }}</p>
                    <a href="{{ route('quotes.sent', ['lang' => $lang]) }}" class="mt-4 flex items-center justify-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-3 text-[13px] font-semibold text-white transition-colors">
                        <i data-lucide="send" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Envoyer la proposition' : 'Send the proposal' }}
                    </a>
                    <button type="button" onclick="document.getElementById('rv-draft').click()" class="mt-3 w-full flex items-center justify-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-3 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                        {{ $isFr ? 'Enregistrer comme brouillon' : 'Save as draft' }}
                    </button>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

    new QRCode(document.getElementById('rv-qr'), {
        text: @json(route('quotes.review', ['lang' => $lang]) . '?devis=QUO-2024-000189'),
        width: 62, height: 62, correctLevel: QRCode.CorrectLevel.M,
    });

    document.getElementById('rv-draft').addEventListener('click', () => {
        localStorage.setItem('rvDraft', 'QUO-2024-000189');
        document.getElementById('rv-draft-label').textContent = @json($isFr ? 'Brouillon enregistré ✓' : 'Draft saved ✓');
    });
</script>
</body>
</html>
