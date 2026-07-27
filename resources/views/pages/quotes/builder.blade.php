@php
    $isFr = $lang === 'fr';

    // [num, title, sub]
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

    // [thumb, name, ref, spec, qty, price, discount, total]
    // Line items only ever come from the RFQ being answered; with no RFQ in
    // scope the table renders empty rather than inventing a proposal.
    $rows = [];

    // Real mode: the builder answers an actual RFQ (single prefilled row)
    $isReal = isset($builderRfq) && $builderRfq;
    if ($isReal) {
        $rows = [[
            'qv-prod-' . (($builderRfq->id % 4) + 1) . '.png',
            $builderRfq->title,
            $builderRfq->reference,
            \Illuminate\Support\Str::limit($builderRfq->description ?? '', 120),
            '1', '0', '0', '0',
        ]];
    }

    // Live-computed client side from the item rows (see the recompute() script
    // at the bottom). Rendered at zero so nothing is ever invented — the design
    // shipped a fixed 4,751,750 / 5,952,258 breakdown here.
    // [label, id, color, bold]
    $financeRows = [
        [$isFr ? 'Sous-total' : 'Subtotal',                             'fin-subtotal', '#1B1B18', false],
        [$isFr ? 'Remise globale' : 'Global discount',                  'fin-discount', '#E5484D', false],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', 'fin-net',      '#1B1B18', true],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',           'fin-tax',      '#1B1B18', false],
        [$isFr ? 'Frais de livraison' : 'Delivery costs',               'fin-delivery', '#1B1B18', false],
        [$isFr ? 'Assurance' : 'Insurance',                             'fin-insurance','#1B1B18', false],
    ];

    $included = $isFr
        ? ['Produits de haute qualité', 'Emballage sécurisé', 'Assistance après-vente', "Documentation d'exportation"]
        : ['High-quality products', 'Secure packaging', 'After-sales support', 'Export documentation'];

    $inputCls = 'ui-field';
    $panelLabel = 'ui-label';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Créer une proposition de devis — Articles — Artisan Hub 237' : 'Create a quote proposal — Items — Artisan Hub 237' }}</title>

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

    <main class="flex-1 min-w-0 px-4 lg:px-6 py-6">

        <!-- Title row -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Créer une proposition de devis' : 'Create a quote proposal' }}</h1>
                <p class="mt-1 text-[13px] text-[#55524A]">{{ $isFr ? 'Construisez et personnalisez votre proposition pour l\'envoyer à l\'artisan.' : 'Build and customise your proposal to send it to the artisan.' }}</p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <button type="submit" form="bld-form" class="ui-btn ui-btn-primary">
                    {{ $isFr ? 'Envoyer la proposition' : 'Send the proposal' }}
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Stepper (step 2 active) -->
        <div class="mt-5 ui-card overflow-x-auto">
            <div class="flex items-center gap-3 min-w-[860px]">
                @foreach($steps as $stIdx => [$stNum, $stTitle, $stSub])
                <div class="relative flex items-center gap-3.5 shrink-0 {{ $stIdx === 1 ? 'pb-1' : '' }}">
                    <span class="w-[36px] h-[36px] shrink-0 rounded-full flex items-center justify-center text-[14px] font-bold {{ $stIdx === 1 ? 'bg-[#0E5A2D] text-white' : 'bg-white border border-[#D9DDD9] text-[#55524A]' }}">{{ $stNum }}</span>
                    <span>
                        <span class="block text-[13px] font-bold {{ $stIdx === 1 ? 'text-[#14652F]' : 'text-[#1B1B18]' }}">{{ $stTitle }}</span>
                        <span class="block mt-0.5 text-[11.5px] text-[#6F6B60]">{{ $stSub }}</span>
                    </span>
                    @if($stIdx === 1)<span class="absolute left-0 right-0 -bottom-3 h-[3px] rounded-full bg-[#14652F]"></span>@endif
                </div>
                @if($stIdx < count($steps) - 1)
                <span class="flex-1 min-w-[36px] h-px bg-[#E0E4E0]"></span>
                @endif
                @endforeach
            </div>
        </div>

        @if($isReal)
        <div class="mt-4 ui-alert ui-alert-ok">
            <i data-lucide="badge-check" class="w-4 h-4"></i>
            <span>{{ $isFr ? 'Vous répondez à la demande' : 'You are answering request' }}
            <span class="font-bold">{{ $builderRfq->reference }}</span> — {{ $builderRfq->title }}
            ({{ $isFr ? 'acheteur' : 'buyer' }} : {{ $builderRfq->buyer->name ?? '—' }})</span>
        </div>
        @endif
        @if($errors->any())
        <div class="mt-4 ui-alert ui-alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="mt-5 flex flex-col xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">
                @if($isReal)
                <form id="bld-form" method="POST" action="{{ route('quotes.store-proposal', ['quoteRequest' => $builderRfq->id, 'lang' => $lang]) }}">
                @csrf
                <input type="hidden" name="payment_terms" value="{{ $isFr ? '50% à la commande, 50% avant expédition' : '50% on order, 50% before shipment' }}">
                @endif

                <!-- Products table -->
                <section class="ui-card">
                    <div class="ui-card-head">
                        <div>
                            <h2 class="ui-card-title">{{ $isFr ? 'Produits demandés' : 'Requested products' }}</h2>
                            <p class="ui-card-sub">{{ $isFr ? 'Sélectionnez les articles et définissez vos prix et conditions.' : 'Select the items and define your prices and conditions.' }}</p>
                        </div>
                        <button type="button" id="bld-add" class="shrink-0 ui-btn ui-btn-secondary">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            {{ $isFr ? 'Ajouter un autre produit' : 'Add another product' }}
                        </button>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <div class="min-w-[920px]">
                            <div class="grid grid-cols-[minmax(220px,1.4fr)_90px_110px_120px_110px_130px_120px_80px] gap-3 bg-[#F7F8F7] rounded-lg px-3 py-2.5">
                                @foreach([$isFr ? 'Produit demandé' : 'Requested product', $isFr ? 'Quantité demandée' : 'Requested quantity', $isFr ? 'Unité' : 'Unit', $isFr ? 'Prix unitaire (FCFA)' : 'Unit price (FCFA)', $isFr ? 'Remise (%)' : 'Discount (%)', 'Taxes', 'Total (FCFA)', 'Actions'] as $hIdx => $h)
                                <span class="text-[10.5px] font-bold tracking-[0.04em] text-[#6F6B60] uppercase {{ $hIdx > 0 && $hIdx < 7 ? 'text-center' : '' }} self-center leading-tight">{{ $h }}</span>
                                @endforeach
                            </div>
                            <div id="bld-rows">
                                @foreach($rows as $rIdx => [$rImg, $rName, $rRef, $rSpec, $rQty, $rPrice, $rDisc, $rTotal])
                                <div class="bld-row border-b border-[#F1F2F1] py-3.5">
                                    <div class="grid grid-cols-[minmax(220px,1.4fr)_90px_110px_120px_110px_130px_120px_80px] gap-3 items-center">
                                        <div class="min-w-0">
                                            {{-- Editable: a seller quoting an RFQ names each line themselves.
                                                 Prefilled from the request so the common single-line case is one click. --}}
                                            <input type="text" @if($isReal) name="items[{{ $rIdx }}][name]" required @endif value="{{ $rName }}"
                                                   placeholder="{{ $isFr ? 'Nom de l\'article' : 'Item name' }}"
                                                   class="{{ $inputCls }}">
                                            <p class="ui-hint">{{ $rRef }}</p>
                                        </div>
                                        <input type="text" @if($isReal) name="items[{{ $rIdx }}][quantity]" @endif value="{{ $rQty }}" class="bld-qty {{ $inputCls }} text-center">
                                        {{-- Wrapper stays so the select keeps its own grid cell. --}}
                                        <div>
                                            <select @if($isReal) name="items[{{ $rIdx }}][unit]" @endif class="{{ $inputCls }} ui-select">
                                                <option value="{{ $isFr ? 'Pièces' : 'Pieces' }}">{{ $isFr ? 'Pièces' : 'Pieces' }}</option>
                                                <option value="Lot">Lot</option>
                                                <option value="Kg">Kg</option>
                                            </select>
                                        </div>
                                        <input type="text" @if($isReal) name="items[{{ $rIdx }}][unit_price]" @endif value="{{ $rPrice }}" class="bld-price {{ $inputCls }} text-right">
                                        <div class="flex items-center gap-1.5">
                                            <input type="text" @if($isReal) name="items[{{ $rIdx }}][discount_pct]" @endif value="{{ $rDisc }}" class="bld-disc flex-1 min-w-0 {{ $inputCls }} text-center">
                                            <span class="shrink-0 text-[12.5px] text-[#55524A]">%</span>
                                        </div>
                                        {{-- TVA is applied once on the proposal total, not per line, so
                                             this reports the rate rather than pretending to be a choice. --}}
                                        <p class="text-[12.5px] text-[#55524A] text-center">TVA 19.25%</p>
                                        <p class="bld-total text-[13px] font-bold text-[#14652F] text-right whitespace-nowrap">{{ $rTotal }}</p>
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" class="bld-del ui-btn ui-btn-danger w-[34px] h-[34px] px-0"><i data-lucide="trash-2" class="w-4 h-4" style="stroke-width:1.7"></i></button>
                                        </div>
                                    </div>
                                    <input type="text" @if($isReal) name="items[{{ $rIdx }}][description]" @endif value="{{ $rSpec }}"
                                           placeholder="{{ $isFr ? 'Spécifications (matière, finition, dimensions…)' : 'Specifications (material, finish, dimensions…)' }}"
                                           class="mt-2 {{ $inputCls }} ui-field--sm">
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <p id="bld-count" class="text-[12.5px] text-[#3B382F]"><span class="font-bold">0 articles</span> &nbsp;•&nbsp; {{ $isFr ? 'Quantité totale' : 'Total quantity' }}: <span class="font-bold">0 {{ $isFr ? 'unités' : 'units' }}</span></p>
                        <p class="text-[12.5px] text-[#55524A]">{{ $isFr ? 'Sous-total' : 'Subtotal' }} &nbsp; <span id="bld-subtotal" class="text-[15.5px] font-bold text-[#14652F]">0 FCFA</span></p>
                    </div>
                </section>

                <!-- Option panels -->
                <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-4 items-stretch">
                    {{-- Selects lost their chevron markup: the kit draws it in the field itself. --}}
                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Options de remise globale' : 'Global discount options' }}</h2>
                        <label class="{{ $panelLabel }} mt-3.5">{{ $isFr ? 'Remise globale sur tous les articles' : 'Global discount on all items' }}</label>
                        <div class="flex items-end gap-2">
                            <input type="text" @if($isReal) name="global_discount_pct" @endif value="{{ $isReal ? 0 : 2 }}" class="w-[90px] {{ $inputCls }} text-center">
                            <span class="pb-2.5 text-[13px] text-[#55524A]">%</span>
                            <div class="flex-1 min-w-0 ml-2">
                                <label class="{{ $panelLabel }}">{{ $isFr ? 'Montant de remise' : 'Discount amount' }}</label>
                                <input type="text" id="bld-discount-amount" value="0 FCFA" readonly class="w-full {{ $inputCls }}">
                            </div>
                        </div>
                        <h2 class="mt-6 ui-card-title">{{ $isFr ? 'Devise de la proposition' : 'Proposal currency' }}</h2>
                        <div class="mt-3.5 grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $panelLabel }}">{{ $isFr ? 'Devise' : 'Currency' }}</label>
                                <select class="w-full {{ $inputCls }} ui-select">
                                    <option>FCFA - Franc CFA</option>
                                    <option>EUR - Euro</option>
                                    <option>USD - Dollar</option>
                                </select>
                            </div>
                            <div>
                                <label class="{{ $panelLabel }}">{{ $isFr ? 'Taux de change (si applicable)' : 'Exchange rate (if applicable)' }}</label>
                                <input type="text" value="1.0000" class="w-full {{ $inputCls }}">
                            </div>
                        </div>
                    </section>

                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Validité de la proposition' : 'Proposal validity' }}</h2>
                        <label class="{{ $panelLabel }} mt-3.5">{{ $isFr ? 'Cette proposition sera valable jusqu\'au' : 'This proposal will be valid until' }}</label>
                        <div class="flex items-end gap-3">
                            <div class="relative flex-1 min-w-0">
                                {{-- Validity is the seller's to set; blank rather than a made-up deadline. --}}
                                <input type="text" value="" placeholder="{{ $isFr ? 'JJ/MM/AAAA' : 'DD/MM/YYYY' }}" class="w-full {{ $inputCls }} pr-9">
                                <i data-lucide="calendar" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#55524A] pointer-events-none"></i>
                            </div>
                            <div class="w-[110px]">
                                <label class="{{ $panelLabel }}">{{ $isFr ? 'Jours de validité' : 'Validity days' }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="text" value="30" class="flex-1 min-w-0 {{ $inputCls }} text-center">
                                    <span class="text-[12px] text-[#55524A]">{{ $isFr ? 'jours' : 'days' }}</span>
                                </div>
                            </div>
                        </div>
                        <h2 class="mt-6 ui-card-title">Incoterms</h2>
                        <label class="{{ $panelLabel }} mt-3.5">{{ $isFr ? 'Conditions de livraison' : 'Delivery terms' }}</label>
                        <select class="w-full {{ $inputCls }} ui-select">
                            <option>FOB - Free On Board</option>
                            <option>DDP - {{ $isFr ? 'Livré droits acquittés' : 'Delivered Duty Paid' }}</option>
                            <option>CIF - Cost Insurance Freight</option>
                        </select>
                    </section>

                    <section class="ui-card">
                        <h2 class="ui-card-title">{{ $isFr ? 'Délais de production & livraison' : 'Production & delivery times' }}</h2>
                        <label class="{{ $panelLabel }} mt-3.5">{{ $isFr ? 'Délai de production' : 'Production time' }}</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach([$isFr ? '7 - 10 jours ouvrables' : '7 - 10 working days', $isFr ? '15 - 20 jours ouvrables' : '15 - 20 working days'] as $delay)
                            <select class="w-full {{ $inputCls }} ui-select">
                                <option>{{ $delay }}</option>
                                <option>{{ $isFr ? '30 jours ouvrables' : '30 working days' }}</option>
                            </select>
                            @endforeach
                        </div>
                        <h2 class="mt-6 ui-card-title">{{ $isFr ? 'Lieu de livraison' : 'Delivery location' }}</h2>
                        <div class="mt-3.5 grid grid-cols-2 gap-3">
                            <div>
                                <label class="{{ $panelLabel }}">{{ $isFr ? 'Pays de livraison' : 'Delivery country' }}</label>
                                <select class="w-full {{ $inputCls }} ui-select">
                                    <option>France</option>
                                    <option>{{ $isFr ? 'Cameroun' : 'Cameroon' }}</option>
                                    <option>{{ $isFr ? 'Autre' : 'Other' }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="{{ $panelLabel }}">{{ $isFr ? 'Ville / Port de destination' : 'City / Port of destination' }}</label>
                                <input type="text" value="Marseille" class="w-full {{ $inputCls }}">
                            </div>
                        </div>
                    </section>
                </div>
                @if($isReal)
                </form>
                @endif
            </div>

            <!-- Right rail -->
            <aside class="w-full xl:w-[290px] shrink-0 space-y-4">
                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Résumé de la proposition' : 'Proposal summary' }}</h2>
                    @if($isReal)
                    <div class="mt-3.5 border border-[#EDEEED] rounded-xl px-4 py-3 grid grid-cols-2 divide-x divide-[#EDEEED]">
                        <div class="pr-3">
                            <p class="ui-dt">{{ $isFr ? 'Référence de la demande' : 'Request reference' }}</p>
                            <a href="{{ route('quotes.create', ['lang' => $lang]) }}" class="ui-dd inline-block text-[#14652F] underline underline-offset-2">{{ $builderRfq->reference }}</a>
                        </div>
                        <div class="pl-3">
                            <p class="ui-dt">Date</p>
                            <p class="ui-dd">{{ optional($builderRfq->created_at)->translatedFormat('d M Y') ?: '—' }}</p>
                        </div>
                    </div>
                    @endif
                    <dl class="mt-4 space-y-3">
                        @foreach($financeRows as [$fnLabel, $fnId, $fnColor, $fnBold])
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12px] text-[#3B382F]">{{ $fnLabel }}</dt>
                            <dd id="{{ $fnId }}" class="text-[12px] {{ $fnBold ? 'font-bold' : 'font-semibold' }}" style="color:{{ $fnColor }}">0 FCFA</dd>
                        </div>
                        @endforeach
                    </dl>
                    <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                        <span class="text-[13.5px] font-bold text-[#1B1B18] uppercase">{{ $isFr ? 'Total général' : 'Grand total' }}</span>
                        <span id="fin-grand" class="text-[14.5px] font-bold text-[#14652F]">0 FCFA</span>
                    </div>
                </section>

                <section class="ui-card">
                    <h2 class="ui-card-title">{{ $isFr ? 'Inclus dans cette proposition' : 'Included in this proposal' }}</h2>
                    <ul class="mt-3 space-y-2.5">
                        @foreach($included as $inc)
                        <li class="flex items-center gap-2.5 text-[12px] text-[#3B382F]">
                            <i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#1F8A4C]" style="stroke-width:2"></i>
                            {{ $inc }}
                        </li>
                        @endforeach
                    </ul>
                </section>

                <section class="bg-[#FDF8EC] border border-[#F2E8CE] rounded-2xl px-5 py-4">
                    <h2 class="text-[12.5px] font-bold text-[#C97A16]">{{ $isFr ? 'Note interne' : 'Internal note' }} <span class="font-semibold">({{ $isFr ? 'non visible par l\'acheteur' : 'not visible to the buyer' }})</span></h2>
                    <input type="text" placeholder="{{ $isFr ? 'Ajoutez une note interne pour votre équipe...' : 'Add an internal note for your team...' }}" class="mt-3 w-full ui-field">
                </section>

                <section class="bg-[#EDF3FC] rounded-2xl px-5 py-4">
                    <h2 class="text-[12.5px] font-bold text-[#3565DE]">{{ $isFr ? 'Informations importantes' : 'Important information' }}</h2>
                    <p class="mt-2 text-[11.5px] text-[#3B382F] leading-relaxed">{{ $isFr ? "Cette proposition est confidentielle et destinée uniquement à l'acheteur. Les prix peuvent varier selon les conditions du marché." : 'This proposal is confidential and intended only for the buyer. Prices may vary according to market conditions.' }}</p>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

    const fmt = n => Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    const num = s => parseFloat((s || '0').toString().replace(/[^\d.]/g, '')) || 0;

    // Everything the seller sees is computed from what they typed — no figure
    // on this page is pre-baked. Mirrors QuoteProposal::recalculateTotals().
    const TAX_RATE = 0.1925;
    const fcfa = n => fmt(n) + ' FCFA';
    const byId = id => document.getElementById(id);

    function recompute() {
        let sub = 0, qty = 0, count = 0;
        document.querySelectorAll('.bld-row').forEach(row => {
            count++;
            const q = num(row.querySelector('.bld-qty').value);
            const p = num(row.querySelector('.bld-price').value);
            const d = num(row.querySelector('.bld-disc').value);
            qty += q;
            sub += q * p * (1 - d / 100);
        });
        byId('bld-subtotal').textContent = fcfa(sub);
        byId('bld-count').innerHTML = '<span class="font-bold">' + count + ' articles</span> &nbsp;•&nbsp; ' + @json($isFr ? 'Quantité totale' : 'Total quantity') + ': <span class="font-bold">' + fmt(qty) + ' ' + @json($isFr ? 'unités' : 'units') + '</span>';

        const gPct     = num((document.querySelector('[name="global_discount_pct"]') || {}).value);
        const delivery = num((document.querySelector('[name="delivery_fee"]') || {}).value);
        const insurance= num((document.querySelector('[name="insurance_fee"]') || {}).value);
        const discount = Math.round(sub * gPct / 100);
        const net      = sub - discount;
        const tax      = Math.round(net * TAX_RATE);

        const set = (id, v) => { const el = byId(id); if (el) el.textContent = v; };
        set('fin-subtotal', fcfa(sub));
        set('fin-discount', '-' + fcfa(discount));
        set('fin-net',      fcfa(net));
        set('fin-tax',      fcfa(tax));
        set('fin-delivery', fcfa(delivery));
        set('fin-insurance',fcfa(insurance));
        set('fin-grand',    fcfa(net + tax + delivery + insurance));

        const dAmount = document.getElementById('bld-discount-amount');
        if (dAmount) dAmount.value = fcfa(discount);
    }
    ['global_discount_pct', 'delivery_fee', 'insurance_fee'].forEach(n => {
        const el = document.querySelector('[name="' + n + '"]');
        if (el) el.addEventListener('input', recompute);
    });
    function bindRow(row) {
        ['.bld-qty', '.bld-price', '.bld-disc'].forEach(sel => row.querySelector(sel).addEventListener('input', () => {
            const q = num(row.querySelector('.bld-qty').value);
            const p = num(row.querySelector('.bld-price').value);
            const d = num(row.querySelector('.bld-disc').value);
            row.querySelector('.bld-total').textContent = fmt(q * p * (1 - d / 100));
            recompute();
        }));
        row.querySelector('.bld-del').addEventListener('click', () => { row.remove(); recompute(); });
    }
    document.querySelectorAll('.bld-row').forEach(bindRow);
    recompute();

    let bldCounter = document.querySelectorAll('.bld-row').length;
    document.getElementById('bld-add').addEventListener('click', () => {
        const rows = document.getElementById('bld-rows');
        const first = rows.querySelector('.bld-row');
        if (!first) return;
        const clone = first.cloneNode(true);
        clone.querySelector('.bld-qty').value = '1';
        clone.querySelector('.bld-disc').value = '0';
        clone.querySelector('.bld-total').textContent = clone.querySelector('.bld-price').value;
        clone.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/items\[\d+\]/, 'items[' + bldCounter + ']');
        });
        bldCounter++;
        rows.appendChild(clone);
        bindRow(clone);
        lucide.createIcons();
        recompute();
    });

    // Real mode: strip thousands separators so the server receives integers
    const bldForm = document.getElementById('bld-form');
    if (bldForm) bldForm.addEventListener('submit', () => {
        document.querySelectorAll('.bld-row').forEach(row => {
            const price = row.querySelector('.bld-price');
            const qty = row.querySelector('.bld-qty');
            price.value = (price.value || '0').replace(/[^0-9]/g, '') || '0';
            qty.value = (qty.value || '1').replace(/[^0-9]/g, '') || '1';
        });
    });

</script>
</body>
</html>
