@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // Same sidebar variant as the accept page (Propositions expanded, En négociation active)
    $qbNavOverride = [
        ['house',          $isFr ? 'Tableau de bord' : 'Dashboard', route('dashboard.buyer', ['lang' => $lang]), false, null, null],
        ['search',         $isFr ? 'Demandes (RFQ)' : 'Requests (RFQ)', route('quotes.index', ['lang' => $lang]), false, '5', 'green'],
        ['square-pen',     'Propositions', 'group', [
            [$isFr ? 'Toutes les propositions' : 'All proposals', route('quotes.index', ['lang' => $lang, 'tab' => 'propositions']), false, null, null],
            [$isFr ? 'En attente' : 'Pending',       route('quotes.index', ['lang' => $lang]),                          false, '2', 'orange'],
            [$isFr ? 'En négociation' : 'In negotiation', route('quotes.index', ['lang' => $lang, 'tab' => 'negociation']), true, null, null],
            [$isFr ? 'Acceptées' : 'Accepted',       route('quotes.index', ['lang' => $lang, 'tab' => 'acceptees']),    false, null, null],
            [$isFr ? 'Refusées' : 'Refused',         route('quotes.index', ['lang' => $lang, 'tab' => 'refusees']),     false, null, null],
        ]],
        ['clipboard-list', $isFr ? 'Commandes' : 'Orders', route('messages.inbox', ['lang' => $lang]), false, '7', 'green'],
        ['message-circle', 'Messages',                     route('messages.inbox', ['lang' => $lang]), false, '8', 'green'],
        ['package',        $isFr ? 'Produits' : 'Products', route('products.index', ['lang' => $lang]), false, null, null],
        ['hand-coins',     'Finances',                     route('dashboard.buyer', ['lang' => $lang]), false, null, null],
        ['users',          'Clients',                      route('messages.inbox', ['lang' => $lang]), false, null, null],
        ['settings',       $isFr ? 'Paramètres' : 'Settings', route('profile.show', ['lang' => $lang]), false, null, null],
    ];

    // [thumb, product, desc, qty, v1Unit, v1Total, chUnit, chUnitPct, chTotal, chTotalPct, up, color, v2Unit, v2Total]
    $cmpRows = [
        ['qv-prod-1.png', $isFr ? 'Mobilier en bois massif pour hôtel' : 'Solid wood furniture for a hotel', $isFr ? 'Bois massif (Ayous), finition vernie naturelle' : 'Solid wood (Ayous), natural varnished finish', '10', '180,000', '1,800,000', '-5,000', '(-2.78%)', '-50,000', '(-2.78%)', false, '#157A43', '175,000', '1,750,000'],
        ['qv-prod-2.png', $isFr ? 'Masque traditionnel Bamiléké' : 'Traditional Bamileke mask',              $isFr ? 'Bois de fromager sculpté à la main' : 'Hand-carved fromager wood',                       '20', '75,000',  '1,500,000', '-5,000', '(-6.67%)', '-100,000', '(-6.67%)', false, '#E5484D', '70,000', '1,400,000'],
        ['qv-prod-3.png', $isFr ? 'Table basse décorative en bois' : 'Decorative wooden coffee table',       $isFr ? 'Bois massif, motif traditionnel camerounais' : 'Solid wood, traditional Cameroonian pattern', '5', '95,000', '475,000',  '+3,000', '(+3.16%)', '+15,000', '(+3.16%)', true,  '#E5484D', '98,000', '490,000'],
        ['qv-prod-4.png', $isFr ? 'Chaise artisanale en bois' : 'Artisanal wooden chair',                    $isFr ? 'Bois durable, assise tressée à la main' : 'Durable wood, hand-woven seat',                '15', '60,000',  '900,000',  '-2,000', '(-3.33%)', '-30,000', '(-3.33%)', false, '#E5484D', '58,000', '870,000'],
    ];

    // [icon dir, label, extra, value, color]
    $changeSummary = [
        ['down', $isFr ? 'Baisse de prix' : 'Price decrease', $isFr ? '2 articles' : '2 items', '-180,000 FCFA', '#3565DE'],
        ['up',   $isFr ? 'Hausse de prix' : 'Price increase', null,                             '+15,000 FCFA',  '#E5484D'],
        ['down', $isFr ? 'Total baisse' : 'Total decrease',   null,                             '-165,000 FCFA', '#3565DE'],
        ['up',   $isFr ? 'Total hausse' : 'Total increase',   null,                             '+15,000 FCFA',  '#E5484D'],
    ];

    $mainChanges = $isFr ? [
        'Le prix du mobilier pour hôtel a été réduit de 2.78%',
        'Le prix des masques traditionnels a été réduit de 6.67%',
        'Le prix de la table basse a augmenté de 3.16%',
        'Le prix des chaises a été réduit de 3.33%',
    ] : [
        'The price of the hotel furniture was reduced by 2.78%',
        'The price of the traditional masks was reduced by 6.67%',
        'The price of the coffee table increased by 3.16%',
        'The price of the chairs was reduced by 3.33%',
    ];

    // [icon, label, url, danger]
    $quickActions = [
        ['send',       $isFr ? 'Envoyer un message' : 'Send a message',   route('messages.inbox', ['lang' => $lang]), false],
        ['paperclip',  $isFr ? 'Joindre un fichier' : 'Attach a file',    route('messages.inbox', ['lang' => $lang]), false],
        ['clock',      $isFr ? 'Voir l\'historique' : 'View the history', route('quotes.index', ['lang' => $lang, 'tab' => 'negociation']), false],
        ['download',   $isFr ? 'Télécharger PDF' : 'Download PDF',        null, false],
        ['log-out',    $isFr ? 'Quitter la négociation' : 'Leave the negotiation', route('quotes.index', ['lang' => $lang]), true],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Comparaison des versions — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Version comparison — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
            <a href="{{ route('quotes.index', ['lang' => $lang, 'tab' => 'propositions']) }}" class="hover:text-[#14652F]">Propositions</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <a href="{{ route('quotes.index', ['lang' => $lang, 'tab' => 'negociation']) }}" class="hover:text-[#14652F]">{{ $isFr ? 'Négociation' : 'Negotiation' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Comparaison des versions' : 'Version comparison' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[22px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Comparaison des versions' : 'Version comparison' }}
                    <span class="bg-[#E8EFFB] rounded-md px-3 py-1 text-[12px] font-semibold text-[#3565DE]">Version 1 vs Version 2</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Artisan' : 'Artisan' }}: <span class="font-semibold text-[#1B1B18]">Art Bois Nature</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Référence' : 'Reference' }}: <span class="font-semibold text-[#1B1B18]">QUO-2024-000189</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Demande' : 'Request' }}: <span class="font-semibold text-[#1B1B18]">RFQ-2024-000189</span>
                </p>
            </div>
            <button type="button" onclick="window.print()" class="shrink-0 inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                {{ $isFr ? 'Télécharger la comparaison' : 'Download the comparison' }}
            </button>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- V1 vs V2 cards -->
                <div class="flex flex-col lg:flex-row items-stretch gap-4">
                    <div class="flex-1 bg-white border border-[#EFF0EF] border-l-[4px] border-l-[#3565DE] rounded-2xl px-5 py-4 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-[14px] font-bold text-[#1B1B18]">Version 1 <span class="font-normal text-[12px] text-[#55524A]">({{ $isFr ? 'Envoyée' : 'Sent' }})</span></p>
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#55524A]"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i> 25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 14:10</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#55524A]"><i data-lucide="file-text" class="w-3.5 h-3.5"></i> 4 articles</p>
                        </div>
                        <p class="text-[19px] font-bold text-[#1B1B18]">4,751,750 FCFA</p>
                    </div>
                    <div class="shrink-0 self-center text-center px-2">
                        <span class="inline-flex w-[44px] h-[44px] rounded-full bg-white border border-[#E5E7E5] items-center justify-center text-[13px] font-bold text-[#1B1B18]">VS</span>
                        <p class="mt-2 text-[11.5px] text-[#55524A]">{{ $isFr ? 'Écart total' : 'Total gap' }}</p>
                        <p class="text-[13px] font-bold text-[#E5484D]">+876,503 FCFA</p>
                        <p class="text-[11.5px] font-semibold text-[#E5484D]">(+18.44%)</p>
                    </div>
                    <div class="flex-1 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-4 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-[14px] font-bold text-[#1B1B18]">Version 2 <span class="font-normal text-[12px] text-[#55524A]">({{ $isFr ? 'Brouillon' : 'Draft' }})</span></p>
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#55524A]"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i> 25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 16:05</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#55524A]"><i data-lucide="file-text" class="w-3.5 h-3.5"></i> 4 articles</p>
                        </div>
                        <p class="text-[19px] font-bold text-[#14652F]">5,628,253 FCFA</p>
                    </div>
                </div>

                <!-- Comparison table -->
                <div class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl overflow-x-auto">
                    <table class="w-full min-w-[980px]">
                        <thead>
                            <tr>
                                <th colspan="3" class="py-3"></th>
                                <th colspan="2" class="px-2 py-3 bg-[#EEF3FC] text-center text-[12px] font-bold tracking-[0.04em] text-[#3565DE] uppercase">Version 1</th>
                                <th colspan="2" class="px-2 py-3 text-center text-[12px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Changement' : 'Change' }}</th>
                                <th colspan="2" class="px-2 py-3 bg-[#EFF6F1] text-center text-[12px] font-bold tracking-[0.04em] text-[#157A43] uppercase">Version 2</th>
                            </tr>
                            <tr class="border-b border-[#F0F1F0]">
                                <th class="pl-5 pr-2 pb-3 text-left text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">#</th>
                                <th class="px-2 pb-3 text-left text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                <th class="px-2 pb-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                                <th class="px-2 pb-3 bg-[#EEF3FC]/60 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                <th class="px-2 pb-3 bg-[#EEF3FC]/60 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Total (FCFA)</th>
                                <th class="px-2 pb-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }}</th>
                                <th class="px-2 pb-3 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Total</th>
                                <th class="px-2 pb-3 bg-[#EFF6F1]/60 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                <th class="px-2 pr-5 pb-3 bg-[#EFF6F1]/60 text-right text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">Total (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F2F1]">
                            @foreach($cmpRows as $ri => [$cImg, $cName, $cDesc, $cQty, $v1u, $v1t, $chu, $chup, $cht, $chtp, $cUp, $cColor, $v2u, $v2t])
                            <tr>
                                <td class="pl-5 pr-2 py-4 align-top text-[13px] text-[#1B1B18]">{{ $ri + 1 }}</td>
                                <td class="px-2 py-4 align-top">
                                    <div class="flex items-start gap-3">
                                        <img src="{{ asset('images/landing/' . $cImg) }}" alt="" class="w-[42px] h-[42px] shrink-0 rounded-lg object-cover">
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold text-[#1B1B18]">{{ $cName }}</p>
                                            <p class="mt-0.5 text-[11.5px] text-[#3565DE] leading-snug">{{ $cDesc }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-4 align-top text-center text-[13px] text-[#1B1B18] whitespace-nowrap">{{ $cQty }}<br><span class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Pièces' : 'Pieces' }}</span></td>
                                <td class="px-2 py-4 align-top bg-[#EEF3FC]/30 text-right text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $v1u }}</td>
                                <td class="px-2 py-4 align-top bg-[#EEF3FC]/30 text-right text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $v1t }}</td>
                                <td class="px-2 py-4 align-top text-right whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-[13px] font-bold" style="color:{{ $cColor }}"><i data-lucide="{{ $cUp ? 'arrow-up' : 'arrow-down' }}" class="w-3.5 h-3.5"></i>{{ $chu }}</span>
                                    <br><span class="text-[11.5px] font-semibold" style="color:{{ $cColor }}">{{ $chup }}</span>
                                </td>
                                <td class="px-2 py-4 align-top text-right whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-[13px] font-bold" style="color:{{ $cColor }}"><i data-lucide="{{ $cUp ? 'arrow-up' : 'arrow-down' }}" class="w-3.5 h-3.5"></i>{{ $cht }}</span>
                                    <br><span class="text-[11.5px] font-semibold" style="color:{{ $cColor }}">{{ $chtp }}</span>
                                </td>
                                <td class="px-2 py-4 align-top bg-[#EFF6F1]/30 text-right text-[13px] font-bold text-[#14652F] whitespace-nowrap">{{ $v2u }}</td>
                                <td class="px-2 pr-5 py-4 align-top bg-[#EFF6F1]/30 text-right text-[13px] font-bold text-[#14652F] whitespace-nowrap">{{ $v2t }}</td>
                            </tr>
                            @endforeach
                            <tr class="border-t-2 border-[#E8EAE8]">
                                <td colspan="3" class="pl-5 pr-2 py-4 text-[14px] font-bold text-[#1B1B18] uppercase">Total</td>
                                <td class="px-2 py-4 bg-[#EEF3FC]/30"></td>
                                <td class="px-2 py-4 bg-[#EEF3FC]/30 text-right text-[13.5px] font-bold text-[#1B1B18] whitespace-nowrap">4,675,000</td>
                                <td class="px-2 py-4"></td>
                                <td class="px-2 py-4 text-right whitespace-nowrap">
                                    <span class="text-[13.5px] font-bold text-[#E5484D]">+835,000</span>
                                    <br><span class="text-[11.5px] font-semibold text-[#E5484D]">(+17.86%)</span>
                                </td>
                                <td class="px-2 py-4 bg-[#EFF6F1]/30"></td>
                                <td class="px-2 pr-5 py-4 bg-[#EFF6F1]/30 text-right text-[13.5px] font-bold text-[#14652F] whitespace-nowrap">5,510,000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Change summary + main changes + donut -->
                <div class="mt-4 flex flex-col lg:flex-row gap-4 items-stretch">
                    <section class="lg:w-[280px] shrink-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé des changements' : 'Summary of changes' }}</h2>
                        <div class="mt-3.5 space-y-3">
                            @foreach($changeSummary as [$csDir, $csLabel, $csExtra, $csValue, $csColor])
                            <div class="flex items-center gap-3">
                                <i data-lucide="arrow-{{ $csDir }}" class="w-4 h-4 shrink-0" style="color:{{ $csColor }}"></i>
                                <span class="flex-1 text-[12.5px] font-semibold text-[#1B1B18]">{{ $csLabel }}</span>
                                <span class="text-right">
                                    @if($csExtra)<span class="block text-[11px] text-[#6F6B60]">{{ $csExtra }}</span>@endif
                                    <span class="block text-[12.5px] font-bold" style="color:{{ $csColor }}">{{ $csValue }}</span>
                                </span>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 bg-[#FDF3E0] rounded-lg px-4 py-2.5 flex items-center justify-between gap-3">
                            <span class="text-[12px] font-bold text-[#1B1B18]">{{ $isFr ? 'Écart total' : 'Total gap' }}:</span>
                            <span class="text-[12.5px] font-bold text-[#E5484D]">+835,000 FCFA (+17.86%)</span>
                        </div>
                    </section>

                    <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[13.5px] font-bold text-[#1B1B18]">{{ $isFr ? 'Principaux changements' : 'Main changes' }}</h2>
                        <ul class="mt-3.5 space-y-2.5">
                            @foreach($mainChanges as $mc)
                            <li class="flex items-start gap-2.5 text-[12.5px] text-[#3B382F] leading-relaxed">
                                <span class="mt-[7px] w-[5px] h-[5px] shrink-0 rounded-full bg-[#14652F]"></span>
                                {{ $mc }}
                            </li>
                            @endforeach
                        </ul>
                        <div class="mt-4 bg-[#EDF3FC] rounded-lg px-4 py-3 flex items-start gap-3">
                            <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5 text-[#3565DE]"></i>
                            <p class="text-[11.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? "Les changements de prix peuvent affecter le budget total. Veuillez examiner les détails avant d'accepter la nouvelle version." : 'Price changes can affect the total budget. Please review the details before accepting the new version.' }}</p>
                        </div>
                    </section>

                    <section class="lg:w-[280px] shrink-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5 flex flex-col items-center justify-center">
                        <div class="relative w-[130px] h-[130px] rounded-full" style="background:conic-gradient(#DC2626 0 7.7%, #1F8A4C 7.7% 100%)">
                            <div class="absolute inset-[15px] rounded-full bg-white flex flex-col items-center justify-center text-center">
                                <span class="text-[14px] font-bold text-[#1B1B18] leading-tight">-165,000<br>FCFA</span>
                                <span class="mt-0.5 text-[10.5px] text-[#6F6B60]">{{ $isFr ? 'Baisse nette' : 'Net decrease' }}</span>
                            </div>
                        </div>
                        <ul class="mt-4 space-y-2 self-stretch">
                            <li class="flex items-center gap-2.5 text-[12px] text-[#3B382F]"><span class="w-[11px] h-[11px] shrink-0 rounded-sm bg-[#1F8A4C]"></span> {{ $isFr ? 'Baisse' : 'Decrease' }}: 180,000 FCFA</li>
                            <li class="flex items-center gap-2.5 text-[12px] text-[#3B382F]"><span class="w-[11px] h-[11px] shrink-0 rounded-sm bg-[#DC2626]"></span> {{ $isFr ? 'Hausse' : 'Increase' }}: 15,000 FCFA</li>
                        </ul>
                    </section>
                </div>

                <!-- Bottom actions -->
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <a href="{{ route('quotes.proposal', ['lang' => $lang]) }}" class="inline-flex items-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-6 py-3 text-[13.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Voir version 1' : 'View version 1' }}</a>
                    <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-6 py-3 text-[13.5px] font-semibold text-[#1B1B18] transition-colors">
                        <i data-lucide="square-pen" class="w-4 h-4" style="stroke-width:1.7"></i>
                        {{ $isFr ? 'Demander des modifications' : 'Request modifications' }}
                    </a>
                    <a href="{{ route('quotes.accept', ['lang' => $lang]) }}" class="ml-auto inline-flex items-center gap-3 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-6 py-3 text-[13.5px] font-semibold text-white transition-colors">
                        <i data-lucide="circle-check" class="w-[18px] h-[18px]" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Accepter la version 2' : 'Accept version 2' }}
                    </a>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations de la proposition' : 'Proposal information' }}</h2>
                    <dl class="mt-4 space-y-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Artisan' : 'Artisan' }}</dt>
                            <dd><a href="{{ $vendorUrl }}" class="text-[12.5px] font-semibold text-[#3565DE] underline underline-offset-2">Art Bois Nature</a></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Statut' : 'Status' }}</dt>
                            <dd><span class="bg-[#FDF0DC] rounded-md px-2.5 py-1 text-[11px] font-semibold text-[#E8890C]">{{ $isFr ? 'En négociation' : 'In negotiation' }}</span></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Valide jusqu\'au' : 'Valid until' }}</dt>
                            <dd class="text-[12.5px] font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Juin' : 'June' }} 2024 (30 {{ $isFr ? 'jours' : 'days' }})</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Dernière activité' : 'Last activity' }}</dt>
                            <dd class="text-[12.5px] font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 16:05</dd>
                        </div>
                    </dl>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Notes de l\'artisan' : 'Artisan\'s notes' }} <span class="font-normal text-[#55524A]">(Version 2)</span></h2>
                    <div class="mt-3.5 bg-[#EFF6F1] border-l-[3px] border-[#1F8A4C] rounded-r-xl px-4 py-3.5">
                        <p class="text-[12.5px] text-[#14532D] leading-relaxed">{{ $isFr ? 'Nous avons ajusté nos prix pour rester compétitifs et vous offrir le meilleur rapport qualité-prix. Les délais de livraison restent inchangés.' : 'We have adjusted our prices to stay competitive and offer you the best value for money. Delivery times remain unchanged.' }}</p>
                        <p class="mt-2 text-[12px] font-semibold text-[#14532D] text-right">– Art Bois Nature</p>
                    </div>
                    <p class="mt-2.5 text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 16:05</p>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($quickActions as [$qaIcon, $qaLabel, $qaUrl, $qaDanger])
                        @if($qaUrl)
                        <a href="{{ $qaUrl }}" class="flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $qaIcon }}" class="w-[18px] h-[18px] shrink-0 {{ $qaDanger ? 'text-[#E5484D]' : 'text-[#3B382F]' }}" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] {{ $qaDanger ? 'text-[#E5484D]' : 'text-[#3B382F] group-hover:text-[#14652F]' }}">{{ $qaLabel }}</span>
                        </a>
                        @else
                        <button type="button" onclick="window.print()" class="w-full flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $qaIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-left text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $qaLabel }}</span>
                        </button>
                        @endif
                        @endforeach
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Documents joints' : 'Attached documents' }} <span class="font-normal text-[#55524A]">(Version 2)</span></h2>
                    <div class="mt-3 space-y-2.5">
                        @foreach([['Devis_Detaille_V2.pdf', '1.2 MB'], ['Conditions_Livraison_V2.pdf', '856 KB']] as [$djName, $djSize])
                        <div class="border border-[#EFF0EF] rounded-xl px-3.5 py-3 flex items-center gap-3">
                            <img src="{{ asset('images/landing/qv-pdf.png') }}" alt="" class="w-[24px] h-[28px] shrink-0" aria-hidden="true">
                            <span class="flex-1 min-w-0">
                                <span class="block text-[12px] font-bold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ $djName }}</span>
                                <span class="block text-[11px] text-[#6F6B60]">{{ $djSize }}</span>
                            </span>
                            <button type="button" onclick="window.print()" class="shrink-0 text-[#3B382F] hover:text-[#14652F]"><i data-lucide="download" class="w-4 h-4" style="stroke-width:1.7"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-right"><a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#3565DE]">{{ $isFr ? 'Voir tous les fichiers' : 'View all files' }}</a></p>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
