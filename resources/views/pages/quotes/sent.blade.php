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

    $afterChecks = $isFr ? [
        'Vous serez notifié par email et dans votre espace.',
        'Vous pourrez suivre l\'état de cette proposition en temps réel.',
        'Vous pouvez discuter avec l\'artisan via la messagerie intégrée.',
    ] : [
        'You will be notified by email and in your space.',
        'You will be able to track the status of this proposal in real time.',
        'You can chat with the artisan via the built-in messaging.',
    ];

    // [label, value, color, bold]
    $summaryRows = [
        [$isFr ? 'Sous-total' : 'Subtotal',                        '4,751,750 FCFA', '#1B1B18', false],
        [$isFr ? 'Remise globale (2%)' : 'Global discount (2%)',   '-95,035 FCFA',   '#E5484D', false],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', '4,656,715 FCFA', '#1B1B18', false],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',      '895,543 FCFA',   '#1B1B18', false],
        [$isFr ? 'Frais de livraison (est.)' : 'Delivery costs (est.)', '250,000 FCFA', '#1B1B18', false],
        [$isFr ? 'Assurance (est.)' : 'Insurance (est.)',          '150,000 FCFA',   '#1B1B18', false],
    ];

    // [title, sub, state: done|current|upcoming]
    $nextSteps = [
        [$isFr ? 'Proposition envoyée' : 'Proposal sent',                 '25 ' . ($isFr ? 'Mai' : 'May') . ' 2024 - 14:32', 'done'],
        [$isFr ? 'En attente de réponse de l\'artisan' : 'Awaiting the artisan\'s reply', $isFr ? 'Délai estimé : 1 à 3 jours ouvrables' : 'Estimated time: 1 to 3 working days', 'current'],
        [$isFr ? 'Réponse de l\'artisan' : 'Artisan\'s reply',            $isFr ? 'Acceptation, modification ou refus' : 'Acceptance, modification or refusal', 'upcoming'],
        [$isFr ? 'Négociation (si nécessaire)' : 'Negotiation (if needed)', $isFr ? 'Discussions et ajustements' : 'Discussions and adjustments', 'upcoming'],
        [$isFr ? 'Commande' : 'Order',                                    $isFr ? 'Si la proposition est acceptée' : 'If the proposal is accepted', 'upcoming'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Proposition envoyée — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Proposal sent — National Virtual Gallery of Cameroonian Crafts' }}</title>

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

@php $qbSearchPlaceholder = $isFr ? 'Rechercher (produits, artisans, demandes...)' : 'Search (products, artisans, requests...)'; @endphp
@include('pages.partials.quotes-buyer-header')

<div class="max-w-[1536px] mx-auto flex items-stretch">
    @include('pages.partials.quotes-buyer-sidebar')

    <main class="flex-1 min-w-0 px-4 lg:px-7 py-6">

        <h1 class="text-[22px] font-bold text-[#1B1B18]">{{ $isFr ? 'Envoyer la proposition' : 'Send the proposal' }}</h1>
        <p class="mt-1 text-[13px] text-[#55524A]">{{ $isFr ? 'Votre proposition a été envoyée avec succès à l\'artisan.' : 'Your proposal was successfully sent to the artisan.' }}</p>

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
                <span class="flex-1 min-w-[36px] h-px bg-[#157A43]"></span>
                @endif
                @endforeach
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">

            <!-- Success card -->
            <section class="flex-1 min-w-0 w-full bg-white border border-[#EFF0EF] rounded-2xl px-6 py-10">
                <img src="{{ asset('images/landing/qv-plane.png') }}" alt="" class="w-[132px] mx-auto" aria-hidden="true">
                <h2 class="mt-6 text-center text-[21px] font-bold text-[#1B1B18]">{{ $isFr ? 'Proposition envoyée avec succès !' : 'Proposal sent successfully!' }}</h2>
                <p class="mt-2 text-center text-[13px] text-[#55524A]">{{ $isFr ? 'Votre proposition a été envoyée à l\'artisan avec succès.' : 'Your proposal was sent to the artisan successfully.' }}</p>

                <div class="mt-8 border border-[#EDEEED] rounded-2xl px-5 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-5 lg:divide-x divide-[#F0F1F0]">
                    <div class="flex items-start gap-3 lg:pr-4">
                        <span class="w-[36px] h-[36px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="mail" class="w-[17px] h-[17px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div class="min-w-0">
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Envoyé à' : 'Sent to' }}</p>
                            <p class="mt-0.5 text-[13px] font-bold text-[#1B1B18]">Art Bois Nature</p>
                            <p class="mt-0.5 text-[11.5px] text-[#6F6B60] leading-relaxed">{{ $isFr ? 'Yaoundé, Centre' : 'Yaounde, Centre' }}<br>{{ $isFr ? 'Cameroun' : 'Cameroon' }}<br>contact@artbois.cm</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 lg:px-4">
                        <span class="w-[36px] h-[36px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="calendar-days" class="w-[17px] h-[17px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Date d\'envoi' : 'Sending date' }}</p>
                            <p class="mt-0.5 text-[13px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024</p>
                            <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">14:32 (GMT+1)</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 lg:px-4">
                        <span class="w-[36px] h-[36px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="file-text" class="w-[17px] h-[17px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Devis n°' : 'Quote no.' }}</p>
                            <p class="mt-0.5 text-[13px] font-bold text-[#1B1B18]">QUO-2024-000189</p>
                            <span class="mt-1.5 inline-block bg-[#E8EFFB] rounded-md px-2.5 py-1 text-[10.5px] font-bold tracking-[0.03em] text-[#3565DE] uppercase">{{ $isFr ? 'Envoyé' : 'Sent' }}</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 lg:pl-4">
                        <span class="w-[36px] h-[36px] shrink-0 rounded-lg bg-[#EFF5F0] flex items-center justify-center"><i data-lucide="hourglass" class="w-[17px] h-[17px] text-[#14652F]" style="stroke-width:1.7"></i></span>
                        <div>
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Valide jusqu\'au' : 'Valid until' }}</p>
                            <p class="mt-0.5 text-[13px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Juin' : 'June' }} 2024</p>
                            <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">(30 {{ $isFr ? 'jours restants' : 'days left' }})</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 bg-[#EDF3FC] rounded-2xl px-5 py-4 flex items-start gap-3.5">
                    <i data-lucide="info" class="w-5 h-5 shrink-0 mt-0.5 text-[#3565DE]"></i>
                    <div>
                        <p class="text-[13px] font-bold text-[#3565DE]">{{ $isFr ? 'Et après ?' : 'What next?' }}</p>
                        <p class="mt-1 text-[12.5px] text-[#3B382F]">{{ $isFr ? 'L\'artisan va examiner votre proposition et vous répondra dans les plus brefs délais.' : 'The artisan will review your proposal and reply as soon as possible.' }}</p>
                        <ul class="mt-2.5 space-y-1.5">
                            @foreach($afterChecks as $ac)
                            <li class="flex items-center gap-2.5 text-[12px] text-[#3B382F]">
                                <i data-lucide="circle-check" class="w-4 h-4 shrink-0 text-[#1F8A4C]" style="stroke-width:2"></i>
                                {{ $ac }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ route('quotes.detail', ['lang' => $lang]) }}" class="inline-flex items-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-5 py-3 text-[13.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Voir la proposition' : 'View the proposal' }}</a>
                    <a href="{{ route('quotes.index', ['lang' => $lang]) }}" class="ml-auto inline-flex items-center bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-5 py-3 text-[13.5px] font-semibold text-[#1B1B18] transition-colors">{{ $isFr ? 'Retour à mes demandes' : 'Back to my requests' }}</a>
                    <a href="{{ route('quotes.negotiation', ['lang' => $lang]) }}" class="inline-flex items-center gap-3 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-5 py-3 text-[13.5px] font-semibold text-white transition-colors">
                        {{ $isFr ? 'Suivre cette proposition' : 'Track this proposal' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </section>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de la proposition' : 'Proposal summary' }}</h2>
                    <dl class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Référence de la demande' : 'Request reference' }}</dt>
                            <dd><a href="{{ route('quotes.create', ['lang' => $lang]) }}" class="text-[12px] font-bold text-[#3565DE] underline underline-offset-2">RFQ-2024-000189</a></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Nombre d\'articles' : 'Number of items' }}</dt>
                            <dd class="text-[12px] font-semibold text-[#1B1B18]">4 articles</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12px] text-[#3B382F]">{{ $isFr ? 'Quantité totale' : 'Total quantity' }}</dt>
                            <dd class="text-[12px] font-semibold text-[#1B1B18]">50 {{ $isFr ? 'unités' : 'units' }}</dd>
                        </div>
                    </dl>
                    <dl class="mt-4 border-t border-[#F0F1F0] pt-4 space-y-3">
                        @foreach($summaryRows as [$smLabel, $smValue, $smColor])
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12px] text-[#3B382F]">{{ $smLabel }}</dt>
                            <dd class="text-[12px] font-semibold" style="color:{{ $smColor }}">{{ $smValue }}</dd>
                        </div>
                        @endforeach
                    </dl>
                    <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                        <span class="text-[13.5px] font-bold text-[#1B1B18] uppercase">{{ $isFr ? 'Total général' : 'Grand total' }}</span>
                        <span class="text-[14.5px] font-bold text-[#14652F]">5,952,258 FCFA</span>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Prochaines étapes' : 'Next steps' }}</h2>
                    <ol class="mt-4">
                        @foreach($nextSteps as $nsIdx => [$nsTitle, $nsSub, $nsState])
                        <li class="relative flex items-start gap-3.5 {{ $nsIdx > 0 ? 'mt-5' : '' }}">
                            @if($nsIdx < 4)<span class="absolute left-[15px] top-[36px] h-[34px] w-px bg-[#E8ECE9]"></span>@endif
                            <span class="relative z-10 w-[31px] h-[31px] shrink-0 rounded-full flex items-center justify-center
                                {{ $nsState === 'done' ? 'bg-[#14652F]' : ($nsState === 'current' ? 'bg-white border-2 border-[#E8890C]' : 'bg-white border border-[#DDE2DD]') }}">
                                @if($nsState === 'done')
                                <i data-lucide="send" class="w-3.5 h-3.5 text-white" style="stroke-width:2"></i>
                                @elseif($nsState === 'current')
                                <span class="w-[7px] h-[7px] rounded-full bg-[#E8890C]"></span>
                                @else
                                <span class="w-[7px] h-[7px] rounded-full bg-[#C9CFC9]"></span>
                                @endif
                            </span>
                            <div class="flex-1 min-w-0 pt-0.5">
                                <p class="flex flex-wrap items-center gap-2">
                                    <span class="text-[12.5px] font-bold {{ $nsState === 'done' ? 'text-[#14652F]' : 'text-[#1B1B18]' }}">{{ $nsTitle }}</span>
                                    @if($nsState === 'done')<span class="bg-[#E2F3E8] rounded px-2 py-0.5 text-[10px] font-semibold text-[#157A43]">{{ $isFr ? 'Terminé' : 'Done' }}</span>@endif
                                    @if($nsState === 'current')<span class="bg-[#FDF3E0] rounded px-2 py-0.5 text-[10px] font-semibold text-[#C97A16]">{{ $isFr ? 'En cours' : 'In progress' }}</span>@endif
                                </p>
                                <p class="mt-0.5 text-[11px] text-[#6F6B60] leading-snug">{{ $nsSub }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
