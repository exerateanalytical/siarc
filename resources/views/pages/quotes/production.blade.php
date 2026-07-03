@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // Sidebar variant: procurement nav, Commandes expanded, "En production" active
    $qbNavOverride = [
        ['house',          $isFr ? 'Tableau de bord' : 'Dashboard', route('dashboard.buyer', ['lang' => $lang]), false, null, null],
        ['search',         $isFr ? 'Demandes (RFQ)' : 'Requests (RFQ)', route('quotes.index', ['lang' => $lang]), false, '5', 'green'],
        ['square-pen',     'Propositions', route('quotes.index', ['lang' => $lang, 'tab' => 'propositions']), false, null, null],
        ['message-circle', $isFr ? 'Commandes' : 'Orders', 'group', [
            [$isFr ? 'Toutes les commandes' : 'All orders', route('messages.inbox', ['lang' => $lang]), false, null, null],
            [$isFr ? 'Bons de commande' : 'Purchase orders', route('quotes.po', ['lang' => $lang]),     false, null, null],
            [$isFr ? 'En production' : 'In production',     route('quotes.production', ['lang' => $lang]), true, null, null],
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

    // [thumb, name, qtyOrdered, qtyProd, stage, stageSub, pct, dueDate, done]
    $prodRows = [
        ['qv-prod-1.png', $isFr ? 'Mobilier en bois massif pour hôtel' : 'Solid wood furniture for a hotel', '10', '7 / 10',  $isFr ? 'Assemblage' : 'Assembly',          $isFr ? 'Assemblage des parties en bois' : 'Assembly of the wooden parts', 70,  '10 ' . ($isFr ? 'Juin' : 'June') . ' 2024', false],
        ['qv-prod-2.png', $isFr ? 'Masque traditionnel Bamiléké' : 'Traditional Bamileke mask',              '20', '15 / 20', $isFr ? 'Sculpture finale' : 'Final carving', $isFr ? 'Finition et détails sculptés' : 'Finishing and carved details',   75,  '12 ' . ($isFr ? 'Juin' : 'June') . ' 2024', false],
        ['qv-prod-3.png', $isFr ? 'Table basse décorative en bois' : 'Decorative wooden coffee table',       '5',  '5 / 5',   $isFr ? 'Vernissage' : 'Varnishing',         $isFr ? 'Application du vernis de protection' : 'Application of the protective varnish', 100, '20 ' . ($isFr ? 'Mai' : 'May') . ' 2024', true],
        ['qv-prod-4.png', $isFr ? 'Chaise artisanale en bois' : 'Artisanal wooden chair',                    '15', '8 / 15',  $isFr ? 'Tressage assise' : 'Seat weaving',   $isFr ? "Tressage de l'assise à la main" : 'Hand-weaving of the seat',      53,  '18 ' . ($isFr ? 'Juin' : 'June') . ' 2024', false],
    ];

    // [when, what, done]
    $activities = [
        ['20 ' . ($isFr ? 'Mai' : 'May') . ' 2024 à 14:32', $isFr ? 'Production de la table basse terminée' : 'Coffee-table production finished', true],
        ['18 ' . ($isFr ? 'Mai' : 'May') . ' 2024 à 10:15', $isFr ? 'Assemblage du mobilier en cours' : 'Furniture assembly in progress',          false],
        ['17 ' . ($isFr ? 'Mai' : 'May') . ' 2024 à 16:45', $isFr ? 'Sculpture finale des masques en cours' : 'Final carving of the masks in progress', false],
        ['16 ' . ($isFr ? 'Mai' : 'May') . ' 2024 à 09:30', $isFr ? 'Bois découpé et prêt pour production' : 'Wood cut and ready for production',  true],
    ];

    // [name, size]
    $prodDocs = [
        ['Planning de production.pdf',   '245 KB'],
        ['Validation des matériaux.pdf', '312 KB'],
        ["Rapport d'avancement.pdf",     '198 KB'],
    ];

    // [icon, label, url]
    $prodActions = [
        ['send',       $isFr ? 'Envoyer un message à l\'artisan' : 'Send a message to the artisan', route('messages.inbox', ['lang' => $lang])],
        ['rotate-ccw', $isFr ? 'Demander une mise à jour' : 'Request an update',                    route('messages.inbox', ['lang' => $lang])],
        ['bell',       $isFr ? 'Signaler un problème' : 'Report a problem',                         route('support.index', ['lang' => $lang])],
        ['download',   $isFr ? 'Télécharger le rapport de production' : 'Download the production report', null],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Suivi de production — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Production tracking — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Commandes' : 'Orders' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#55524A]"></i>
            <a href="{{ route('quotes.production', ['lang' => $lang]) }}" class="font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'En production' : 'In production' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#55524A]"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Suivi de production' : 'Production tracking' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[24px] font-bold text-[#1B1B18]">{{ $isFr ? 'Suivi de production' : 'Production tracking' }}</h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    PO N°: <span class="font-semibold text-[#1B1B18]">PO-2024-00045</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Fournisseur' : 'Supplier' }}: <span class="font-semibold text-[#1B1B18]">Art Bois Nature</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Date de commande' : 'Order date' }}: <span class="font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024</span>
                </p>
            </div>
            <a href="{{ route('quotes.po', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
            <i data-lucide="file-text" class="w-4 h-4" style="stroke-width:1.7"></i>
            {{ $isFr ? 'Voir le bon de commande' : 'View the purchase order' }}
            </a>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Header status card -->
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 flex flex-col lg:flex-row gap-6 lg:divide-x divide-[#F0F1F0]">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="Art Bois Nature" class="w-[84px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Artisan' : 'Artisan' }}</p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-2">
                                <span class="text-[14px] font-bold text-[#1B1B18]">Art Bois Nature</span>
                                <span class="inline-flex items-center gap-1 bg-[#E2F3E8] rounded-md px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]"><i data-lucide="check" class="w-2.5 h-2.5" style="stroke-width:3.4"></i> {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}</span>
                            </p>
                            <p class="mt-2 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#55524A]"></i> {{ $isFr ? 'Yaoundé, Centre, Cameroun' : 'Yaounde, Centre, Cameroon' }}</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#55524A]"></i> +237 6 70 12 34 56</p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#55524A]"></i> contact@artbois.cm</p>
                            <a href="{{ $vendorUrl }}" class="mt-2 inline-block text-[12px] font-semibold text-[#1B1B18] underline underline-offset-4 hover:text-[#14652F]">{{ $isFr ? 'Voir le profil du furnisseur' : 'View the supplier profile' }}</a>
                        </div>
                    </div>
                    <div class="lg:px-5">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Statut global' : 'Global status' }}</p>
                        <p class="mt-2"><span class="inline-flex items-center gap-1.5 bg-[#E2F3E8] rounded-md px-3 py-1 text-[11.5px] font-semibold text-[#157A43]"><i data-lucide="settings" class="w-3 h-3" style="stroke-width:2"></i> {{ $isFr ? 'En production' : 'In production' }}</span></p>
                        <div class="mt-3 w-[130px] h-[7px] rounded-full bg-[#EEF0EE]"><div class="h-full w-[65%] rounded-full bg-[#1F8A4C]"></div></div>
                        <p class="mt-1.5 text-[11.5px] text-[#55524A]">65% {{ $isFr ? 'terminé' : 'complete' }}</p>
                    </div>
                    <div class="lg:px-5">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Articles en production' : 'Items in production' }}</p>
                        <p class="mt-1.5 text-[21px] font-bold text-[#1B1B18]">4 / 4</p>
                        <p class="mt-1 text-[11.5px] text-[#55524A]">{{ $isFr ? 'Tous les articles en cours' : 'All items in progress' }}</p>
                    </div>
                    <div class="lg:px-5">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date de livraison prévue' : 'Expected delivery date' }}</p>
                        <p class="mt-1.5 text-[17px] font-bold text-[#E5484D]">24 {{ $isFr ? 'Juin' : 'June' }} 2024</p>
                        <p class="mt-1 text-[11.5px] text-[#55524A]">(30 {{ $isFr ? 'jours restants' : 'days left' }})</p>
                    </div>
                    <div class="lg:pl-5 text-center">
                        <p class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Progression globale' : 'Global progress' }}</p>
                        <div class="relative mt-2 mx-auto w-[84px] h-[84px] rounded-full" style="background:conic-gradient(#1F8A4C 0 65%, #E7EAE7 65% 100%)">
                            <div class="absolute inset-[9px] rounded-full bg-white flex items-center justify-center">
                                <span class="text-[17px] font-bold text-[#1B1B18]">65%</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Per-article progress table -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'Progression de production par article' : 'Production progress by item' }}</h2>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full min-w-[900px]">
                            <thead>
                                <tr class="bg-[#F7F8F7] text-left">
                                    <th class="pl-4 pr-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">#</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Produit' : 'Product' }}</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Qté commandée' : 'Ordered qty' }}</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Qté en production' : 'Qty in production' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Étape actuelle' : 'Current stage' }}</th>
                                    <th class="px-2 py-3 text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Progression' : 'Progress' }}</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Date de fin prévue' : 'Expected end date' }}</th>
                                    <th class="px-2 pr-4 py-3 text-center text-[11px] font-bold tracking-[0.05em] text-[#6F6B60] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F1F2F1]">
                                @foreach($prodRows as $ri => [$pImg, $pName, $pQty, $pProd, $pStage, $pStageSub, $pPct, $pDue, $pDone])
                                <tr>
                                    <td class="pl-4 pr-2 py-4 align-top text-[13px] text-[#1B1B18]">{{ $ri + 1 }}</td>
                                    <td class="px-2 py-4 align-top">
                                        <div class="flex items-start gap-3">
                                            <img src="{{ asset('images/landing/' . $pImg) }}" alt="" class="w-[40px] h-[40px] shrink-0 rounded-lg object-cover">
                                            <p class="text-[12.5px] font-bold text-[#1B1B18] max-w-[160px] leading-snug">{{ $pName }}</p>
                                        </div>
                                    </td>
                                    <td class="px-2 py-4 align-top text-center text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $pQty }}<br><span class="font-normal text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Pièces' : 'Pieces' }}</span></td>
                                    <td class="px-2 py-4 align-top text-center text-[13px] font-semibold text-[#1B1B18] whitespace-nowrap">{{ $pProd }}<br><span class="font-normal text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Pièces' : 'Pieces' }}</span></td>
                                    <td class="px-2 py-4 align-top">
                                        <p class="text-[12.5px] font-bold text-[#1B1B18]">{{ $pStage }}</p>
                                        <p class="mt-0.5 text-[11.5px] text-[#6F6B60] leading-snug max-w-[150px]">{{ $pStageSub }}</p>
                                    </td>
                                    <td class="px-2 py-4 align-top min-w-[130px]">
                                        <p class="text-[12px] font-bold text-[#1B1B18]">{{ $pPct }}%</p>
                                        <div class="mt-1.5 h-[7px] rounded-full bg-[#EEF0EE]"><div class="h-full rounded-full bg-[#1F8A4C]" style="width:{{ $pPct }}%"></div></div>
                                    </td>
                                    <td class="px-2 py-4 align-top text-center text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $pDue }}</td>
                                    <td class="px-2 pr-4 py-4 align-top text-center">
                                        <span class="inline-block rounded-md px-3 py-1.5 text-[11.5px] font-semibold whitespace-nowrap {{ $pDone ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#E8F2EC] text-[#157A43]' }}">{{ $pDone ? ($isFr ? 'Terminé' : 'Done') : ($isFr ? 'En cours' : 'In progress') }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Activities + photos -->
                <div class="mt-4 flex flex-col lg:flex-row gap-4 items-stretch">
                    <section class="lg:w-[380px] shrink-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Activités récentes' : 'Recent activities' }}</h2>
                        <ol class="mt-4">
                            @foreach($activities as $avIdx => [$avWhen, $avWhat, $avDone])
                            <li class="relative flex items-start gap-3.5 {{ $avIdx > 0 ? 'mt-4' : '' }}">
                                @if($avIdx < count($activities) - 1)<span class="absolute left-[7px] top-[18px] h-[46px] w-px bg-[#DCE7DF]"></span>@endif
                                <span class="relative z-10 mt-1 w-[15px] h-[15px] shrink-0 rounded-full border-2 {{ $avDone ? 'bg-[#1F8A4C] border-[#1F8A4C]' : 'bg-white border-[#1F8A4C]' }}"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[11.5px] text-[#6F6B60]">{{ $avWhen }}</p>
                                    <p class="mt-0.5 text-[12.5px] font-semibold text-[#1B1B18] leading-snug">{{ $avWhat }}</p>
                                </div>
                                <span class="shrink-0 rounded-md px-2.5 py-1 text-[10.5px] font-semibold {{ $avDone ? 'bg-[#E2F3E8] text-[#157A43]' : 'bg-[#E8F2EC] text-[#157A43]' }}">{{ $avDone ? ($isFr ? 'Terminé' : 'Done') : ($isFr ? 'En cours' : 'In progress') }}</span>
                            </li>
                            @endforeach
                        </ol>
                    </section>

                    <section class="flex-1 min-w-0 bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                        <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Photos de production récentes' : 'Recent production photos' }}</h2>
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @for($ph = 1; $ph <= 4; $ph++)
                            <img src="{{ asset('images/landing/qv-photo-' . $ph . '.png') }}" alt="" class="w-full h-[110px] rounded-xl object-cover">
                            @endfor
                        </div>
                        <button type="button" onclick="window.print()" class="mt-4 inline-flex items-center border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2 text-[12.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Voir toutes les photos' : 'View all the photos' }}</button>
                    </section>
                </div>

                <div class="mt-4 bg-[#EDF3FC] rounded-xl px-5 py-3.5 flex items-center gap-3">
                    <i data-lucide="info" class="w-4 h-4 shrink-0 text-[#3565DE]"></i>
                    <p class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'Vous serez notifié automatiquement pour chaque mise à jour importante de la production.' : 'You will be automatically notified of every important production update.' }}</p>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Informations de production' : 'Production information' }}</h2>
                    <dl class="mt-4 space-y-3.5">
                        <div>
                            <dt class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Date de début de production' : 'Production start date' }}</dt>
                            <dd class="mt-0.5 text-[13px] font-semibold text-[#1B1B18]">15 {{ $isFr ? 'Mai' : 'May' }} 2024</dd>
                        </div>
                        <div>
                            <dt class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Dernière mise à jour' : 'Last update' }}</dt>
                            <dd class="mt-0.5 text-[13px] font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 16:35</dd>
                        </div>
                        <div>
                            <dt class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Responsable production' : 'Production manager' }}</dt>
                            <dd class="mt-0.5 text-[13px] font-semibold text-[#1B1B18]">Martin Nguimatsia</dd>
                        </div>
                        <div>
                            <dt class="text-[12px] text-[#6F6B60]">{{ $isFr ? 'Contact atelier' : 'Workshop contact' }}</dt>
                            <dd class="mt-0.5 text-[13px] font-semibold text-[#1B1B18]">+237 6 80 45 67 89</dd>
                        </div>
                    </dl>
                    <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="mt-5 flex items-center justify-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-4 py-3 text-[13px] font-semibold text-white transition-colors">
                        <i data-lucide="message-circle" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Contacter l\'atelier' : 'Contact the workshop' }}
                    </a>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Documents liés' : 'Related documents' }}</h2>
                    <div class="mt-3 space-y-3.5">
                        @foreach($prodDocs as [$dcName, $dcSize])
                        <div class="flex items-center gap-3.5">
                            <img src="{{ asset('images/landing/qv-pdf.png') }}" alt="" class="w-[24px] h-[28px] shrink-0" aria-hidden="true">
                            <span class="flex-1 min-w-0">
                                <span class="block text-[12.5px] font-bold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ $dcName }}</span>
                                <span class="block text-[11px] text-[#6F6B60]">{{ $dcSize }}</span>
                            </span>
                            <button type="button" onclick="window.print()" class="shrink-0 text-[#3B382F] hover:text-[#14652F]"><i data-lucide="download" class="w-4 h-4" style="stroke-width:1.7"></i></button>
                        </div>
                        @endforeach
                    </div>
                    <p class="mt-3.5 text-right"><a href="{{ route('quotes.po', ['lang' => $lang]) }}" class="text-[12.5px] font-semibold text-[#3565DE]">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }}</a></p>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($prodActions as [$paIcon, $paLabel, $paUrl])
                        @if($paUrl)
                        <a href="{{ $paUrl }}" class="flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $paIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $paLabel }}</span>
                        </a>
                        @else
                        <button type="button" onclick="window.print()" class="w-full flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $paIcon }}" class="w-[18px] h-[18px] shrink-0 text-[#3B382F]" style="stroke-width:1.7"></i>
                            <span class="flex-1 text-left text-[13px] text-[#3B382F] group-hover:text-[#14652F]">{{ $paLabel }}</span>
                        </button>
                        @endif
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
