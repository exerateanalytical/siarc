@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // [key, label, badge, url]
    $tabs = [
        ['details',    $isFr ? 'Détails de la proposition' : 'Proposal details', null, null],
        ['messages',   $isFr ? 'Messages & Négociation' : 'Messages & Negotiation', null, route('messages.inbox', ['lang' => $lang])],
        ['historique', $isFr ? 'Historique' : 'History', null, route('quotes.index', ['lang' => $lang])],
    ];

    // Real proposal threading (?proposal=ID, authorized in the route)
    $rp  = $realProposal;
    $rq  = $rp->request;
    $biz = $rq?->business;

    // [thumb, name, desc, qty, unit, price, discount, tax, total]
    $rows = $rp->items->map(fn ($it, $i) => [
        'qv-prod-' . (($i % 4) + 1) . '.png',
        $it->name,
        $it->description ?? '',
        (string) $it->quantity,
        number_format($it->unit_price),
        rtrim(rtrim(number_format($it->discount_pct, 2), '0'), '.') . '%',
        number_format((int) round($it->total * 0.1925)),
        number_format($it->total),
    ])->all();

    $realRef      = $rp->reference;
    $realBizName  = $biz->name_fr ?? '—';
    $realBizLogo  = $biz?->logo ? asset('storage/' . $biz->logo) : asset('images/landing/logo.png');
    $realSentAt   = $rp->created_at->format('d/m/Y H:i');
    $realValid    = $rp->valid_until?->format('d/m/Y');
    $realDaysLeft = $rp->valid_until ? (int) now()->startOfDay()->diffInDays($rp->valid_until, false) : null;
    $realWanted   = $rq?->desired_response_date?->format('d/m/Y');
    $realStatusFr = ['draft' => 'BROUILLON', 'sent' => 'EN ATTENTE DE RÉPONSE', 'accepted' => 'ACCEPTÉE', 'refused' => 'REFUSÉE'][$rp->status] ?? strtoupper($rp->status);

    // [label, value, color]
    $totals = [
        [$isFr ? 'Sous-total' : 'Subtotal', number_format($rp->subtotal) . ' FCFA', '#1B1B18'],
        [($isFr ? 'Remise globale' : 'Global discount') . ' (' . rtrim(rtrim(number_format($rp->global_discount_pct, 2), '0'), '.') . '%)', '-' . number_format($rp->discount_amount) . ' FCFA', '#E5484D'],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)', number_format($rp->tax_amount) . ' FCFA', '#1B1B18'],
        [$isFr ? 'Frais de livraison' : 'Delivery costs', number_format($rp->delivery_fee) . ' FCFA', '#1B1B18'],
        [$isFr ? 'Assurance' : 'Insurance', number_format($rp->insurance_fee) . ' FCFA', '#1B1B18'],
    ];

    // [icon, label]
    $importantInfo = array_values(array_filter([
        $realValid ? ['calendar-days', ($isFr ? 'Valide jusqu\'au ' : 'Valid until ') . $realValid] : null,
        ['badge-percent', $isFr ? 'Les prix incluent la TVA (19.25%)' : 'Prices include VAT (19.25%)'],
        $rp->delivery_location ? ['truck', ($isFr ? 'Livraison à ' : 'Delivery to ') . $rp->delivery_location] : null,
        ['shield-check',  $isFr ? 'Paiement sécurisé via notre plateforme' : 'Secure payment via our platform'],
        ['badge-check',   $isFr ? 'Protection acheteur incluse' : 'Buyer protection included'],
    ]));
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Proposition de devis — Artisan Hub 237' : 'Quote proposal — Artisan Hub 237' }}</title>

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
        </div>
        @endif

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
                    <span class="ui-pill ui-pill-warn">{{ $realStatusFr }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Proposée par' : 'Proposed by' }} <span class="font-semibold text-[#1B1B18]">{{ $realBizName }}</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Envoyée le' : 'Sent on' }} <span class="font-semibold text-[#1B1B18]">{{ $realSentAt }}</span>
                    &nbsp;•&nbsp; <span class="font-semibold text-[#1B1B18]">{{ $realRef }}</span>
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="download" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Télécharger PDF' : 'Download PDF' }}
                </button>
                <button type="button" onclick="window.print()" class="ui-btn ui-btn-secondary">
                    <i data-lucide="share-2" class="w-4 h-4" style="stroke-width:1.8"></i>
                    {{ $isFr ? 'Partager' : 'Share' }}
                </button>
                <a href="{{ route('quotes.index', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary">
                    <i data-lucide="ellipsis" class="w-4 h-4"></i>
                    {{ $isFr ? 'Plus d\'options' : 'More options' }}
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Artisan / reference / validity card -->
                <section class="ui-card flex flex-col lg:flex-row gap-6 lg:divide-x divide-[#F0F1F0]">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <img src="{{ $realBizLogo }}" alt="{{ $realBizName }}" class="w-[88px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="ui-dt">{{ $isFr ? 'Proposée par' : 'Proposed by' }}</p>
                            <p class="mt-0.5 flex flex-wrap items-center gap-2">
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
                            <a href="{{ $vendorUrl }}" class="mt-2 inline-block text-[12px] font-semibold text-[#1B1B18] underline underline-offset-4 hover:text-[#14652F]">{{ $isFr ? 'Voir le profil de l\'artisan' : 'View the artisan\'s profile' }}</a>
                        </div>
                    </div>
                    <div class="lg:px-6">
                        <p class="ui-dt">{{ $isFr ? 'Référence de la demande' : 'Request reference' }}</p>
                        <p class="ui-dd">{{ $rq?->reference ?? '—' }}</p>
                        <p class="mt-4 ui-dt">{{ $isFr ? 'Date de réponse souhaitée' : 'Desired response date' }}</p>
                        <p class="ui-dd">{{ $realWanted ?? '—' }}</p>
                    </div>
                    <div class="lg:pl-6 flex items-start gap-3.5">
                        <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="ui-dt">{{ $isFr ? 'Valide jusqu\'au' : 'Valid until' }}</p>
                            <p class="ui-dd">{{ $realValid ?? '—' }}</p>
                            @if($realDaysLeft !== null)
                            <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">({{ max($realDaysLeft, 0) }} {{ $isFr ? 'jours restants' : 'days left' }})</p>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Tabs + details -->
                <section class="mt-4 ui-card ui-card--flush">
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
                        <h2 class="ui-card-title">{{ $isFr ? 'Articles proposés' : 'Proposed items' }} <span class="font-normal text-[#55524A]">({{ count($rows) }} {{ $isFr ? 'articles' : 'items' }})</span></h2>
                        <div class="mt-3 ui-table-wrap">
                            <table class="ui-table min-w-[880px]">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ $isFr ? 'Produit' : 'Product' }}</th>
                                        <th>Description</th>
                                        <th class="text-center">{{ $isFr ? 'Qté' : 'Qty' }}</th>
                                        <th class="text-center">{{ $isFr ? 'Unité' : 'Unit' }}</th>
                                        <th class="text-right">{{ $isFr ? 'Prix unitaire' : 'Unit price' }} (FCFA)</th>
                                        <th class="text-center">{{ $isFr ? 'Remise' : 'Discount' }}</th>
                                        <th class="text-right">Taxes (19.25%)</th>
                                        <th class="text-right">Total (FCFA)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $ri => [$rImg, $rName, $rDesc, $rQty, $rPrice, $rDisc, $rTax, $rTotal])
                                    <tr>
                                        <td class="align-top text-[#1B1B18]">{{ $ri + 1 }}</td>
                                        <td class="align-top">
                                            <div class="flex items-start gap-3">
                                                <img src="{{ asset('images/landing/' . $rImg) }}" alt="" class="w-[40px] h-[40px] shrink-0 rounded-lg object-cover">
                                                <p class="text-[12.5px] font-bold text-[#1B1B18] max-w-[150px] leading-snug">{{ $rName }}</p>
                                            </div>
                                        </td>
                                        <td class="align-top text-[#3B382F] leading-relaxed max-w-[190px]">{{ $rDesc }}</td>
                                        <td class="align-top text-center font-semibold text-[#1B1B18]">{{ $rQty }}</td>
                                        <td class="align-top text-center text-[#3B382F]">{{ $isFr ? 'Pièces' : 'Pieces' }}</td>
                                        <td class="align-top text-right font-semibold text-[#1B1B18] whitespace-nowrap">{{ $rPrice }}</td>
                                        <td class="align-top text-center text-[#3B382F]">{{ $rDisc }}</td>
                                        <td class="align-top text-right text-[#3B382F] whitespace-nowrap">{{ $rTax }}</td>
                                        <td class="align-top text-right font-bold text-[#14652F] whitespace-nowrap">{{ $rTotal }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 border-t border-[#F0F1F0] pt-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div>
                                <h3 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Conditions de paiement' : 'Payment terms' }}</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $rp->payment_terms ?: '—' }}</p>
                                <h3 class="mt-4 text-[13px] font-bold text-[#1B1B18]">Incoterms</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $rp->incoterms ?: '—' }}</p>
                            </div>
                            <div>
                                <h3 class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Délais de production' : 'Production time' }}</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $rp->production_delay ?: '—' }}</p>
                                <h3 class="mt-4 text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Délais de livraison' : 'Delivery time' }}</h3>
                                <p class="mt-1.5 text-[12.5px] text-[#3B382F]">{{ $rp->delivery_delay ?: '—' }}</p>
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
                                    <span class="text-[15.5px] font-bold text-[#157A43]">{{ number_format($rp->total) }} FCFA</span>
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
                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Statut de la proposition' : 'Proposal status' }}</h2>
                    <p class="mt-3.5 flex items-center gap-2.5">
                        <span class="w-[22px] h-[22px] shrink-0 rounded-full bg-[#F5B301] flex items-center justify-center"><i data-lucide="clock" class="w-3 h-3 text-white" style="stroke-width:2.6"></i></span>
                        <span class="ui-pill ui-pill-warn">{{ $realStatusFr }}</span>
                    </p>
                    <p class="mt-3 text-[12.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? 'L\'artisan attend votre réponse.' : 'The artisan awaits your reply.' }}<br>{{ $isFr ? 'Délai estimé : 1 à 3 jours ouvrables' : 'Estimated time: 1 to 3 working days' }}</p>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-3.5 space-y-3">
                        @if(in_array($rp->status, ['sent', 'draft']))
                        <form method="POST" action="{{ route('quotes.accept-proposal', ['proposal' => $rp->id, 'lang' => $lang]) }}">
                            @csrf
                            <button type="submit" class="ui-btn ui-btn-primary ui-btn-block">
                                <i data-lucide="circle-check" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                                {{ $isFr ? 'Accepter la proposition' : 'Accept the proposal' }}
                            </button>
                        </form>
                        @elseif($rp->purchaseOrder)
                        <a href="{{ route('quotes.po', ['lang' => $lang, 'po' => $rp->purchaseOrder->id]) }}" class="ui-btn ui-btn-primary ui-btn-block">
                            <i data-lucide="file-text" class="w-[17px] h-[17px]" style="stroke-width:1.8"></i>
                            {{ $isFr ? 'Voir le bon de commande' : 'View the purchase order' }}
                        </a>
                        @endif
                        {{-- Negotiation happens in the conversation thread opened with the RFQ. --}}
                        <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-block">
                            <i data-lucide="square-pen" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                            {{ $isFr ? 'Demander des modifications' : 'Request modifications' }}
                        </a>
                        @if(in_array($rp->status, ['sent', 'draft']))
                        <form method="POST" action="{{ route('quotes.refuse-proposal', ['proposal' => $rp->id, 'lang' => $lang]) }}">
                            @csrf
                            <button type="submit" class="ui-btn ui-btn-danger ui-btn-block">
                                <i data-lucide="x" class="w-[17px] h-[17px]" style="stroke-width:2"></i>
                                {{ $isFr ? 'Refuser la proposition' : 'Refuse the proposal' }}
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="ui-btn ui-btn-secondary ui-btn-block">
                            <i data-lucide="message-circle" class="w-[17px] h-[17px]" style="stroke-width:1.7"></i>
                            {{ $isFr ? 'Poser une question' : 'Ask a question' }}
                        </a>
                    </div>
                </section>

                @if((int) $rp->discount_amount > 0)
                <section class="bg-[#EFF6F1] rounded-2xl px-5 py-4 flex items-start gap-3">
                    <i data-lucide="piggy-bank" class="w-[22px] h-[22px] shrink-0 text-[#1F8A4C]" style="stroke-width:1.7"></i>
                    <p class="text-[12px] leading-relaxed">
                        <span class="font-bold text-[#157A43]">{{ $isFr ? 'Vous économisez' : 'You save' }}</span><br>
                        <span class="text-[#3B382F]">{{ number_format((int) $rp->discount_amount) }} FCFA {{ $isFr ? 'sur cette proposition' : 'on this proposal' }}</span>
                    </p>
                </section>
                @endif

                <section class="ui-card">
                    <h2 class="ui-card-title text-[#3565DE]">{{ $isFr ? 'Informations importantes' : 'Important information' }}</h2>
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
