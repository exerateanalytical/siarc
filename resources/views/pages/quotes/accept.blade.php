@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);

    // Sidebar variant: Propositions group expanded, "En négociation" active
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

    $acceptConditions = $isFr ? [
        ["J'accepte les prix, quantités, remises et conditions de livraison de cette proposition.", null],
        ["J'accepte les conditions générales de vente de l'artisan.", 'terms'],
        ["J'accepte le délai de livraison de 15 jours ouvrables à compter de la confirmation de commande.", null],
        ["J'accepte les conditions de paiement: 50% à la commande, 50% avant expédition.", null],
        ["J'ai lu et j'accepte les documents joints (devis, conditions de livraison, etc.).", null],
        ['Je confirme que les informations fournies sont exactes et complètes.', null],
    ] : [
        ['I accept the prices, quantities, discounts and delivery conditions of this proposal.', null],
        ["I accept the artisan's general terms of sale.", 'terms'],
        ['I accept the delivery time of 15 working days from order confirmation.', null],
        ['I accept the payment terms: 50% on order, 50% before shipment.', null],
        ['I have read and accept the attached documents (quote, delivery conditions, etc.).', null],
        ['I confirm that the information provided is accurate and complete.', null],
    ];

    // [label, value, valueColor, bold]
    $financeRows = [
        [$isFr ? 'Sous-total' : 'Subtotal',                        '4,575,000 FCFA', '#1B1B18', false],
        [$isFr ? 'Remise globale (2.33%)' : 'Global discount (2.33%)', '-105,750 FCFA', '#E5484D', false],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', '4,469,250 FCFA', '#1B1B18', true],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',      '859,603 FCFA', '#1B1B18', false],
        [$isFr ? 'Frais de livraison (est.)' : 'Delivery costs (est.)', '180,000 FCFA', '#1B1B18', false],
        [$isFr ? 'Assurance (est.)' : 'Insurance (est.)',          '120,000 FCFA', '#1B1B18', false],
    ];

    // [icon, title, sub, done]
    $nextSteps = [
        ['circle-check', $isFr ? 'Proposition acceptée' : 'Proposal accepted',      $isFr ? 'Vous acceptez cette proposition.' : 'You accept this proposal.', true],
        ['file-text',    $isFr ? 'Bon de commande généré' : 'Purchase order generated', $isFr ? 'Un bon de commande vous sera envoyé.' : 'A purchase order will be sent to you.', false],
        ['hand-coins',   $isFr ? "Paiement de l'acompte" : 'Deposit payment',       $isFr ? '50% du montant total à payer.' : '50% of the total amount to pay.', false],
        ['settings',     $isFr ? 'Production en cours' : 'Production in progress',  $isFr ? "L'artisan commence la production." : 'The artisan starts production.', false],
        ['truck',        $isFr ? 'Livraison' : 'Delivery',                          $isFr ? 'Réception et confirmation de la commande.' : 'Receipt and confirmation of the order.', false],
    ];

    $securityRows = $isFr
        ? ['Vos données sont protégées', 'Transaction sécurisée', 'Plateforme certifiée et vérifiée']
        : ['Your data is protected', 'Secure transaction', 'Certified and verified platform'];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Accepter la proposition — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Accept the proposal — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Accepter la proposition' : 'Accept the proposal' }}</span>
        </nav>
        <h1 class="mt-2 text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Accepter la proposition' : 'Accept the proposal' }}</h1>
        <p class="mt-1 text-[13px] text-[#55524A]">
            {{ $isFr ? 'Artisan' : 'Artisan' }}: <span class="font-semibold text-[#1B1B18]">Art Bois Nature</span>
            &nbsp;•&nbsp; {{ $isFr ? 'Référence de la proposition' : 'Proposal reference' }}: <span class="font-semibold text-[#1B1B18]">QUO-2024-000189</span>
            &nbsp;•&nbsp; Version: <span class="font-semibold text-[#1B1B18]">2 ({{ $isFr ? 'Brouillon' : 'Draft' }})</span>
        </p>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Artisan / validity / amount card -->
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 flex flex-col lg:flex-row gap-6">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="Art Bois Nature" class="w-[88px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="flex flex-wrap items-center gap-2.5">
                                <span class="text-[15px] font-bold text-[#1B1B18]">Art Bois Nature</span>
                                <span class="inline-flex items-center gap-1.5 bg-[#E2F3E8] rounded-md px-2.5 py-1 text-[11px] font-semibold text-[#157A43]">
                                    <i data-lucide="check" class="w-3 h-3" style="stroke-width:3"></i>
                                    {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}
                                </span>
                            </p>
                            <p class="mt-2.5 flex items-center gap-2.5 text-[12.5px] text-[#3B382F]"><i data-lucide="map-pin" class="w-4 h-4 text-[#55524A]"></i> {{ $isFr ? 'Yaoundé, Centre, Cameroun' : 'Yaounde, Centre, Cameroon' }}</p>
                            <p class="mt-1.5 flex items-center gap-2.5 text-[12.5px] text-[#3B382F]"><i data-lucide="phone" class="w-4 h-4 text-[#55524A]"></i> +237 6 70 12 34 56</p>
                            <p class="mt-1.5 flex items-center gap-2.5 text-[12.5px] text-[#3B382F]"><i data-lucide="mail" class="w-4 h-4 text-[#55524A]"></i> contact@artbois.cm</p>
                            <a href="{{ $vendorUrl }}" class="mt-2.5 inline-block text-[12.5px] font-semibold text-[#1B1B18] underline underline-offset-4 hover:text-[#14652F]">{{ $isFr ? 'Voir le profil de l\'artisan' : 'View the artisan\'s profile' }}</a>
                        </div>
                    </div>
                    <div class="shrink-0 lg:w-[210px]">
                        <p class="flex items-start gap-3">
                            <span class="w-[38px] h-[38px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                            <span>
                                <span class="block text-[12px] text-[#55524A]">{{ $isFr ? 'Valide jusqu\'au' : 'Valid until' }}</span>
                                <span class="block text-[14px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Juin' : 'June' }} 2024</span>
                                <span class="block text-[11.5px] text-[#6F6B60]">(30 {{ $isFr ? 'jours restants' : 'days left' }})</span>
                            </span>
                        </p>
                        <p class="mt-4 text-[12px] text-[#55524A]">{{ $isFr ? 'Dernière activité' : 'Last activity' }}</p>
                        <p class="mt-0.5 text-[13px] font-semibold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 16:05</p>
                    </div>
                    <div class="shrink-0 lg:w-[250px] bg-[#F4F8F5] rounded-xl px-5 py-4">
                        <p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Montant total de la proposition' : 'Total amount of the proposal' }}</p>
                        <p class="mt-1.5 text-[23px] font-bold text-[#14652F]">5,628,253 FCFA</p>
                        <p class="mt-0.5 text-[11.5px] text-[#55524A]">{{ $isFr ? 'TVA incluse (19.25%)' : 'VAT included (19.25%)' }}</p>
                        <a href="{{ route('quotes.proposal', ['lang' => $lang]) }}" class="mt-3 inline-flex items-center gap-2.5 bg-white border border-[#DCE7DF] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[12.5px] font-semibold text-[#1B1B18] transition-colors">
                            <i data-lucide="eye" class="w-4 h-4" style="stroke-width:1.7"></i>
                            {{ $isFr ? 'Voir le détail de la proposition' : 'View the proposal detail' }}
                        </a>
                    </div>
                </section>

                <!-- Acceptance conditions -->
                <section class="mt-4 bg-[#F1F7F2] border border-[#DCEBE0] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#14652F]">{{ $isFr ? 'Conditions d\'acceptation' : 'Acceptance conditions' }}</h2>
                    <p class="mt-1 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Veuillez lire et accepter toutes les conditions ci-dessous avant de confirmer:' : 'Please read and accept all the conditions below before confirming:' }}</p>
                    <div class="mt-4 space-y-3.5">
                        @foreach($acceptConditions as [$acText, $acLink])
                        <label class="flex items-start gap-3.5 cursor-pointer select-none">
                            <input type="checkbox" checked class="accept-check sr-only">
                            <span class="accept-box mt-0.5 w-[19px] h-[19px] shrink-0 rounded bg-[#14652F] flex items-center justify-center">
                                <i data-lucide="check" class="w-3 h-3 text-white" style="stroke-width:3.4"></i>
                            </span>
                            <span class="text-[13px] text-[#1B1B18] leading-relaxed">
                                {{ $acText }}
                                @if($acLink)<br><a href="{{ route('terms', ['lang' => $lang]) }}" class="font-semibold text-[#14652F] underline underline-offset-4">{{ $isFr ? 'Voir les conditions générales' : 'See the general terms' }}</a>@endif
                            </span>
                        </label>
                        @endforeach
                    </div>
                </section>

                <!-- Electronic signature -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5">
                    <h2 class="text-[15px] font-bold text-[#14652F]">{{ $isFr ? 'Signature électronique' : 'Electronic signature' }}</h2>
                    <p class="mt-1 text-[12.5px] text-[#55524A]">{{ $isFr ? 'Signez électroniquement pour confirmer votre acceptation de cette proposition:' : 'Sign electronically to confirm your acceptance of this proposal:' }}</p>
                    <div class="mt-4 flex flex-col lg:flex-row gap-8">
                        <div class="flex-1 min-w-0">
                            <div class="border border-[#EDEEED] rounded-xl overflow-hidden">
                                <div class="flex items-center gap-8 px-5 border-b border-[#EFF0EF]">
                                    @foreach([$isFr ? 'Dessiner' : 'Draw', $isFr ? 'Téléverser' : 'Upload', $isFr ? 'Taper' : 'Type'] as $sgIdx => $sgTab)
                                    <button type="button" data-sig-tab="{{ $sgIdx }}" class="sig-tab relative py-3.5 text-[13px] {{ $sgIdx === 0 ? 'font-bold text-[#14652F]' : 'text-[#55524A]' }}">
                                        {{ $sgTab }}
                                        <span class="sig-bar absolute left-0 right-0 -bottom-px h-[3px] rounded-t bg-[#14652F] {{ $sgIdx === 0 ? '' : 'hidden' }}"></span>
                                    </button>
                                    @endforeach
                                </div>
                                <div class="h-[150px] flex items-center justify-center px-5">
                                    <img id="sig-draw" src="{{ asset('images/landing/qv-signature.png') }}" alt="" class="max-h-[92px] object-contain">
                                    <label id="sig-upload" class="hidden cursor-pointer border border-[#9DBFA9] hover:border-[#14652F] rounded-lg px-5 py-2.5 text-[13px] font-semibold text-[#14652F] transition-colors">
                                        {{ $isFr ? 'Choisir une image de signature' : 'Choose a signature image' }}
                                        <input type="file" accept="image/*" class="hidden" onchange="if(this.files[0]){const i=document.getElementById('sig-draw');i.src=URL.createObjectURL(this.files[0]);sigShow(0);}">
                                    </label>
                                    <input id="sig-type" type="text" value="Jean Dupont" class="hidden w-full max-w-[380px] text-center text-[34px] italic font-semibold text-[#1B1B18] border-b border-[#D9DDD9] focus:outline-none focus:border-[#14652F]" style="font-family:'Playfair Display', serif;">
                                </div>
                            </div>
                            <button type="button" onclick="document.getElementById('sig-draw').classList.add('invisible')" class="mt-2.5 text-[12.5px] font-semibold text-[#3B382F] hover:text-[#DC2626]">{{ $isFr ? 'Effacer' : 'Clear' }}</button>
                        </div>
                        <dl class="shrink-0 lg:w-[240px] space-y-4">
                            @foreach([
                                [$isFr ? 'Signé par' : 'Signed by', $siacUser['name'] ?? 'Jean Dupont'],
                                [$isFr ? 'Fonction' : 'Role', $isFr ? 'Directeur Général' : 'Managing Director'],
                                [$isFr ? 'Entreprise' : 'Company', 'Achat Pro SARL'],
                                [$isFr ? 'Date et heure' : 'Date and time', '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 à 16:25 (GMT+1)'],
                            ] as [$sgLabel, $sgValue])
                            <div>
                                <dt class="text-[12px] text-[#6F6B60]">{{ $sgLabel }}</dt>
                                <dd class="mt-0.5 text-[13.5px] font-semibold text-[#1B1B18]">{{ $sgValue }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                </section>

                <!-- Notice + actions -->
                <div class="mt-4 bg-[#FDF3E0] border border-[#F5E3BC] rounded-xl px-5 py-3.5 flex items-center gap-3.5">
                    <i data-lucide="shield" class="w-5 h-5 shrink-0 text-[#C97A16]" style="stroke-width:1.8"></i>
                    <p class="text-[12.5px] text-[#3B382F]">{{ $isFr ? 'En acceptant, un bon de commande sera généré et vous sera envoyé par email.' : 'Upon acceptance, a purchase order will be generated and sent to you by email.' }}</p>
                </div>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('quotes.index', ['lang' => $lang, 'tab' => 'negociation']) }}" class="inline-flex items-center border border-[#E5E7E5] hover:border-[#14532D] bg-white rounded-lg px-6 py-3 text-[13.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Annuler' : 'Cancel' }}</a>
                    <a href="{{ route('quotes.po', ['lang' => $lang]) }}" class="inline-flex items-center gap-3 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-6 py-3.5 text-[13.5px] font-semibold text-white transition-colors">
                        <i data-lucide="circle-check" class="w-[18px] h-[18px]" style="stroke-width:1.8"></i>
                        {{ $isFr ? 'Accepter la proposition et générer le bon de commande' : 'Accept the proposal and generate the purchase order' }}
                    </a>
                </div>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[320px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de la proposition' : 'Proposal summary' }} <span class="font-normal text-[#55524A]">(Version 2)</span></h2>
                    <dl class="mt-4 space-y-3">
                        @foreach($financeRows as [$fnLabel, $fnValue, $fnColor, $fnBold])
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12.5px] text-[#3B382F]">{{ $fnLabel }}</dt>
                            <dd class="text-[12.5px] {{ $fnBold ? 'font-bold' : 'font-semibold' }}" style="color:{{ $fnColor }}">{{ $fnValue }}</dd>
                        </div>
                        @endforeach
                    </dl>
                    <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                        <span class="text-[14px] font-bold text-[#1B1B18]">TOTAL {{ $isFr ? 'GÉNÉRAL' : '' }}</span>
                        <span class="text-[15.5px] font-bold text-[#14652F]">5,628,253 FCFA</span>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Étapes suivantes' : 'Next steps' }}</h2>
                    <ol class="mt-4">
                        @foreach($nextSteps as $nsIdx => [$nsIcon, $nsTitle, $nsSub, $nsDone])
                        <li class="relative flex items-start gap-3.5 {{ $nsIdx > 0 ? 'mt-5' : '' }}">
                            @if($nsIdx < 4)<span class="absolute left-[16px] top-[38px] h-[34px] w-px bg-[#E8ECE9]"></span>@endif
                            <span class="relative z-10 w-[33px] h-[33px] shrink-0 rounded-full flex items-center justify-center {{ $nsDone ? 'bg-[#14652F]' : 'bg-white border border-[#DDE2DD]' }}">
                                <i data-lucide="{{ $nsDone ? 'check' : $nsIcon }}" class="w-4 h-4 {{ $nsDone ? 'text-white' : 'text-[#55524A]' }}" style="stroke-width:{{ $nsDone ? 3 : 1.7 }}"></i>
                            </span>
                            <div class="min-w-0 pt-0.5">
                                <p class="flex flex-wrap items-center gap-2">
                                    <span class="text-[13px] font-bold {{ $nsDone ? 'text-[#14652F]' : 'text-[#1B1B18]' }}">{{ $nsTitle }}</span>
                                    @if($nsDone)<span class="bg-[#E2F3E8] rounded px-2 py-0.5 text-[10.5px] font-semibold text-[#157A43]">{{ $isFr ? 'En cours' : 'In progress' }}</span>@endif
                                </p>
                                <p class="mt-0.5 text-[11.5px] text-[#6F6B60] leading-snug">{{ $nsSub }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#14652F]">{{ $isFr ? 'Sécurité & confiance' : 'Security & trust' }}</h2>
                    <ul class="mt-3.5 space-y-3">
                        @foreach($securityRows as $sr)
                        <li class="flex items-center gap-3 text-[12.5px] text-[#3B382F]">
                            <i data-lucide="shield-check" class="w-[18px] h-[18px] shrink-0 text-[#14652F]" style="stroke-width:1.7"></i>
                            {{ $sr }}
                        </li>
                        @endforeach
                    </ul>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();

    // Acceptance checkboxes
    document.querySelectorAll('.accept-check').forEach(c => c.addEventListener('change', () => {
        const box = c.nextElementSibling;
        box.style.background = c.checked ? '#14652F' : '#FFFFFF';
        box.style.border = c.checked ? 'none' : '1.5px solid #C9CFC9';
        box.querySelector('svg, i').style.visibility = c.checked ? 'visible' : 'hidden';
    }));

    // Signature tabs
    function sigShow(n) {
        document.querySelectorAll('.sig-tab').forEach((t, i) => {
            t.classList.toggle('font-bold', i === n);
            t.classList.toggle('text-[#14652F]', i === n);
            t.classList.toggle('text-[#55524A]', i !== n);
            t.querySelector('.sig-bar').classList.toggle('hidden', i !== n);
        });
        document.getElementById('sig-draw').classList.toggle('hidden', n !== 0);
        document.getElementById('sig-draw').classList.remove('invisible');
        document.getElementById('sig-upload').classList.toggle('hidden', n !== 1);
        document.getElementById('sig-type').classList.toggle('hidden', n !== 2);
    }
    document.querySelectorAll('.sig-tab').forEach((t, i) => t.addEventListener('click', () => sigShow(i)));
</script>
</body>
</html>
