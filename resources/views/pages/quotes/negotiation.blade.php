@php
    $isFr = $lang === 'fr';
    $vendorUrl = $quoteVendor
        ? route('businesses.show', ['slug' => $quoteVendor->slug, 'lang' => $lang])
        : route('businesses.index', ['lang' => $lang]);
    $buyerName = $siacUser['name'] ?? 'Jean Dupont';
    $buyerInitials = strtoupper(collect(explode(' ', trim($buyerName)))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));

    // [key, label, badge, url]
    $tabs = [
        ['conversation', 'Conversation', null, null],
        ['articles',     $isFr ? 'Articles & Modifications' : 'Items & Modifications', '2', route('quotes.compare', ['lang' => $lang])],
        ['fichiers',     $isFr ? 'Fichiers' : 'Files', '3', route('quotes.detail', ['lang' => $lang])],
        ['propositions', 'Propositions', '1', route('quotes.detail', ['lang' => $lang])],
        ['historique',   $isFr ? 'Historique' : 'History', null, route('quotes.index', ['lang' => $lang])],
    ];

    // [label, value, color, bold]
    $financeRows = [
        [$isFr ? 'Sous-total' : 'Subtotal',                        '4,751,750 FCFA', '#1B1B18', false],
        [$isFr ? 'Remise globale (2%)' : 'Global discount (2%)',   '-95,035 FCFA',   '#E5484D', false],
        [$isFr ? 'Sous-total après remise' : 'Subtotal after discount', '4,656,715 FCFA', '#1B1B18', true],
        [$isFr ? 'Taxes (TVA 19.25%)' : 'Taxes (VAT 19.25%)',      '895,543 FCFA',   '#1B1B18', false],
        [$isFr ? 'Frais de livraison (est.)' : 'Delivery costs (est.)', '250,000 FCFA', '#1B1B18', false],
        [$isFr ? 'Assurance (est.)' : 'Insurance (est.)',          '150,000 FCFA',   '#1B1B18', false],
    ];

    // [icon, label, url, style]
    $quickActions = [
        ['square-pen',   $isFr ? 'Demander des modifications' : 'Request modifications', route('messages.inbox', ['lang' => $lang]), 'orange'],
        ['circle-check', $isFr ? 'Accepter la proposition' : 'Accept the proposal',      route('quotes.accept', ['lang' => $lang]),  'green'],
        ['x',            $isFr ? 'Refuser la proposition' : 'Refuse the proposal',       route('quotes.index', ['lang' => $lang]),   'red'],
        ['download',     $isFr ? 'Télécharger la proposition' : 'Download the proposal', null,                                       'plain'],
        ['log-out',      $isFr ? 'Quitter la négociation' : 'Leave the negotiation',     route('quotes.index', ['lang' => $lang]),   'plain'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Négociation en cours — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Negotiation in progress — National Virtual Gallery of Cameroonian Crafts' }}</title>

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
            <a href="{{ route('quotes.detail', ['lang' => $lang]) }}" class="font-semibold text-[#157A43] hover:text-[#14532D]">{{ $isFr ? 'Détail de la proposition' : 'Proposal detail' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#55524A]"></i>
            <span class="font-semibold text-[#1B1B18]">{{ $isFr ? 'Négociation' : 'Negotiation' }}</span>
        </nav>
        <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="flex flex-wrap items-center gap-3 text-[22px] font-bold text-[#1B1B18]">
                    {{ $isFr ? 'Négociation en cours' : 'Negotiation in progress' }}
                    <span class="bg-[#FDF3E0] rounded-md px-3 py-1 text-[11px] font-bold tracking-[0.03em] text-[#C97A16] uppercase">{{ $isFr ? 'En attente de réponse vendeur' : 'Awaiting seller response' }}</span>
                </h1>
                <p class="mt-1.5 text-[13px] text-[#55524A]">
                    {{ $isFr ? 'Référence' : 'Reference' }}: <span class="font-semibold text-[#1B1B18]">QUO-2024-000189</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Artisans' : 'Artisans' }}: <span class="font-semibold text-[#1B1B18]">Art Bois Nature</span>
                    &nbsp;•&nbsp; {{ $isFr ? 'Demande' : 'Request' }}: <span class="font-semibold text-[#1B1B18]">RFQ-2024-000189</span>
                </p>
            </div>
            <div class="shrink-0 flex flex-wrap items-center gap-3">
                <a href="{{ route('quotes.detail', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="eye" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $isFr ? 'Voir la proposition' : 'View the proposal' }}
                </a>
                <a href="{{ route('quotes.compare', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="repeat" class="w-4 h-4" style="stroke-width:1.7"></i>
                    {{ $isFr ? 'Comparer les versions' : 'Compare the versions' }}
                </a>
                <a href="{{ route('quotes.index', ['lang' => $lang]) }}" class="inline-flex items-center gap-2.5 bg-white border border-[#E5E7E5] hover:border-[#14532D] rounded-lg px-4 py-2.5 text-[13px] font-semibold text-[#1B1B18] transition-colors">
                    <i data-lucide="ellipsis" class="w-4 h-4"></i>
                    {{ $isFr ? 'Plus d\'options' : 'More options' }}
                </a>
            </div>
        </div>

        <div class="mt-5 flex flex-col 2xl:flex-row gap-5 items-start">
            <div class="flex-1 min-w-0 w-full">

                <!-- Artisan / status / validity card -->
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-6 py-5 flex flex-col lg:flex-row gap-6 lg:divide-x divide-[#F0F1F0]">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="Art Bois Nature" class="w-[86px] shrink-0 object-contain">
                        <div class="min-w-0">
                            <p class="text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Artisan' : 'Artisan' }}</p>
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
                        <p class="text-[12.5px] font-semibold text-[#1B1B18]">{{ $isFr ? 'Statut de la négociation' : 'Negotiation status' }}</p>
                        <p class="mt-2 flex items-center gap-2.5">
                            <span class="w-[20px] h-[20px] shrink-0 rounded-full border-2 border-[#E8890C] flex items-center justify-center"><span class="w-[6px] h-[6px] rounded-full bg-[#E8890C]"></span></span>
                            <span class="text-[13px] font-bold text-[#C97A16]">{{ $isFr ? 'En attente de réponse vendeur' : 'Awaiting seller response' }}</span>
                        </p>
                        <p class="mt-1 ml-[30px] text-[11.5px] text-[#6F6B60]">{{ $isFr ? 'Délai estimé: 1 à 3 jours ouvrables' : 'Estimated time: 1 to 3 working days' }}</p>
                        <p class="mt-4 text-[12px] text-[#6F6B60]">{{ $isFr ? 'Dernière activité' : 'Last activity' }}</p>
                        <p class="mt-0.5 text-[13px] font-bold text-[#1B1B18]">25 {{ $isFr ? 'Mai' : 'May' }} 2024 à 14:32</p>
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

                <!-- Tabs + conversation -->
                <section class="mt-4 bg-white border border-[#EFF0EF] rounded-2xl">
                    <div class="px-5 pt-1 border-b border-[#F0F1F0] overflow-x-auto">
                        <div class="flex items-center gap-7 min-w-max">
                            @foreach($tabs as [$tKey, $tLabel, $tBadge, $tUrl])
                            @if($tKey === 'conversation')
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

                    <div class="px-5 py-5 space-y-4">
                        <!-- Buyer message -->
                        <div class="bg-[#FBFBFA] border border-[#F0F1F0] rounded-2xl px-5 py-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="w-[40px] h-[40px] shrink-0 rounded-full bg-[#DFEDE3] flex items-center justify-center text-[14px] font-semibold text-[#14652F]">{{ $buyerInitials }}</span>
                                <p class="text-[13px] font-bold text-[#1B1B18]">{{ $buyerName }} <span class="font-normal text-[#6F6B60]">({{ $isFr ? 'Acheteur' : 'Buyer' }})</span></p>
                                <span class="bg-[#E8EFFB] rounded-md px-2.5 py-1 text-[11px] font-semibold text-[#3565DE]">{{ $isFr ? 'Demande de modification' : 'Modification request' }}</span>
                                <span class="ml-auto flex items-center gap-2 text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024, 14:32 <i data-lucide="check-check" class="w-4 h-4 text-[#157A43]"></i></span>
                            </div>
                            <div class="mt-3 ml-[52px] text-[13px] text-[#3B382F] leading-relaxed">
                                <p>{{ $isFr ? 'Bonjour,' : 'Hello,' }}</p>
                                <p>{{ $isFr ? 'Pourrions-nous réduire le prix de la table basse décorative en bois à 85,000 FCFA ?' : 'Could we reduce the price of the decorative wooden coffee table to 85,000 FCFA?' }}</p>
                                <p>{{ $isFr ? 'Également, pouvez-vous confirmer un délai de livraison en 12 jours ouvrables ?' : 'Also, can you confirm a delivery time of 12 working days?' }}</p>
                                <p>{{ $isFr ? 'Merci.' : 'Thank you.' }}</p>
                                <div class="mt-3 inline-flex items-center gap-3 bg-white border border-[#EDEEED] rounded-xl px-3.5 py-2.5">
                                    <img src="{{ asset('images/landing/qv-pdf.png') }}" alt="" class="w-[22px] h-[26px] shrink-0" aria-hidden="true">
                                    <span>
                                        <span class="block text-[12px] font-bold text-[#1B1B18]">Ref_Bois_Requirements.pdf</span>
                                        <span class="block text-[11px] text-[#6F6B60]">1.2 MB</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Vendor reply -->
                        <div class="flex items-start gap-3">
                            <span class="w-[44px] h-[44px] shrink-0 rounded-full border border-[#E5DED2] bg-white flex items-center justify-center overflow-hidden">
                                <img src="{{ asset('images/landing/qv-abn-logo.png') }}" alt="" class="w-[32px] object-contain">
                            </span>
                            <div class="flex-1 min-w-0 bg-[#EFF6F1] border border-[#DCEBE0] rounded-2xl px-5 py-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-[13px] font-bold text-[#1B1B18]">Art Bois Nature <span class="font-normal text-[#6F6B60]">({{ $isFr ? 'Vendeur' : 'Seller' }})</span></p>
                                    <span class="bg-[#DCEFE2] rounded-md px-2.5 py-1 text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Réponse' : 'Reply' }}</span>
                                    <span class="ml-auto text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024, 15:47</span>
                                </div>
                                <div class="mt-2.5 text-[13px] text-[#3B382F] leading-relaxed">
                                    <p>{{ $isFr ? 'Bonjour Monsieur,' : 'Hello Sir,' }}</p>
                                    <p>{{ $isFr ? 'Merci pour votre message. Nous pouvons ajuster le prix à 88,000 FCFA pour la table basse décorative avec un délai de 15 jours ouvrables.' : 'Thank you for your message. We can adjust the price to 88,000 FCFA for the decorative coffee table with a lead time of 15 working days.' }}</p>
                                    <p>{{ $isFr ? 'Cordialement.' : 'Best regards.' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- System message -->
                        <div class="flex items-start gap-3">
                            <span class="w-[44px] h-[44px] shrink-0 rounded-full bg-[#FDF3E0] flex items-center justify-center text-[12px] font-bold text-[#C97A16]">SYS</span>
                            <div class="flex-1 min-w-0 bg-[#FDF6E7] border border-[#F5E8C8] rounded-2xl px-5 py-3.5">
                                <div class="flex flex-wrap items-center gap-3">
                                    <p class="text-[13px] font-bold text-[#1B1B18]">{{ $isFr ? 'Système' : 'System' }}</p>
                                    <span class="ml-auto text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024, 16:05</span>
                                </div>
                                <p class="mt-1.5 text-[13px] text-[#3B382F]">{{ $isFr ? 'Une nouvelle proposition (Version 2) a été envoyée par Art Bois Nature.' : 'A new proposal (Version 2) was sent by Art Bois Nature.' }}</p>
                            </div>
                        </div>

                        <!-- Composer — a REAL message into the Art Bois Nature conversation -->
                        <form method="POST" action="{{ route('messages.send') }}" class="pt-2">
                            @csrf
                            <input type="hidden" name="business_slug" value="{{ $quoteVendor->slug ?? 'art-bois-nature' }}">
                            <input type="hidden" name="return_to" value="{{ route('quotes.negotiation', ['lang' => $lang]) }}">
                            <p class="text-[12.5px] font-semibold text-[#3B382F]">{{ $isFr ? 'Envoyer un message' : 'Send a message' }}</p>
                            <textarea name="body" rows="3" required placeholder="{{ $isFr ? 'Écrivez votre message ici...' : 'Write your message here...' }}" class="mt-2 w-full border border-[#E5E7E5] rounded-xl px-4 py-3 text-[13px] text-[#1B1B18] leading-relaxed focus:outline-none focus:border-[#14532D] focus:ring-1 focus:ring-[#14532D]/30 transition resize-y"></textarea>
                            <div class="mt-2.5 flex flex-wrap items-center gap-x-6 gap-y-3">
                                @foreach([['paperclip', $isFr ? 'Joindre un fichier' : 'Attach a file'], ['file-text', $isFr ? 'Joindre un document' : 'Attach a document'], ['package', $isFr ? 'Insérer un article' : 'Insert an item']] as [$cIcon, $cLabel])
                                <button type="button" class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F] hover:text-[#14652F]">
                                    <i data-lucide="{{ $cIcon }}" class="w-4 h-4" style="stroke-width:1.7"></i>
                                    {{ $cLabel }}
                                </button>
                                @endforeach
                                <button type="submit" class="ml-auto inline-flex items-center gap-2.5 bg-[#0E5A2D] hover:bg-[#14652F] rounded-lg px-5 py-2.5 text-[13px] font-semibold text-white transition-colors">
                                    <i data-lucide="send" class="w-4 h-4" style="stroke-width:1.8"></i>
                                    {{ $isFr ? 'Envoyer' : 'Send' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <!-- Right rail -->
            <aside class="w-full 2xl:w-[300px] shrink-0 space-y-4">
                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Résumé de la proposition' : 'Proposal summary' }}</h2>
                    <dl class="mt-4 space-y-3">
                        @foreach($financeRows as [$fnLabel, $fnValue, $fnColor, $fnBold])
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-[12px] text-[#3B382F]">{{ $fnLabel }}</dt>
                            <dd class="text-[12px] {{ $fnBold ? 'font-bold' : 'font-semibold' }}" style="color:{{ $fnColor }}">{{ $fnValue }}</dd>
                        </div>
                        @endforeach
                    </dl>
                    <div class="mt-4 border-t border-[#F0F1F0] pt-4 flex items-center justify-between gap-3">
                        <span class="text-[13.5px] font-bold text-[#1B1B18] uppercase">{{ $isFr ? 'Total général' : 'Grand total' }}</span>
                        <span class="text-[14.5px] font-bold text-[#14652F]">5,952,258 FCFA</span>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Versions de la proposition' : 'Proposal versions' }}</h2>
                    <div class="mt-3.5 space-y-4">
                        <div class="flex items-center gap-3.5">
                            <span class="w-[30px] h-[30px] shrink-0 rounded-full bg-[#E2F3E8] flex items-center justify-center text-[11px] font-bold text-[#157A43]">V2</span>
                            <div class="flex-1 min-w-0">
                                <p class="flex flex-wrap items-center gap-2 text-[12.5px] font-bold text-[#1B1B18]">Version 2 <span class="bg-[#FDF3E0] rounded px-2 py-0.5 text-[10.5px] font-semibold text-[#C97A16]">{{ $isFr ? 'En attente' : 'Pending' }}</span></p>
                                <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024, 16:05</p>
                            </div>
                            <a href="{{ route('quotes.detail', ['lang' => $lang]) }}" class="shrink-0 text-[12px] font-semibold text-[#157A43] underline underline-offset-2">{{ $isFr ? 'Voir' : 'View' }}</a>
                        </div>
                        <div class="flex items-center gap-3.5">
                            <span class="w-[30px] h-[30px] shrink-0 rounded-full bg-[#F0F1F0] flex items-center justify-center text-[11px] font-bold text-[#55524A]">V1</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-[12.5px] font-bold text-[#1B1B18]">Version 1</p>
                                <p class="mt-0.5 text-[11.5px] text-[#6F6B60]">25 {{ $isFr ? 'Mai' : 'May' }} 2024, 14:10</p>
                            </div>
                            <a href="{{ route('quotes.compare', ['lang' => $lang]) }}" class="shrink-0 text-[12px] font-semibold text-[#157A43] underline underline-offset-2">{{ $isFr ? 'Voir' : 'View' }}</a>
                        </div>
                    </div>
                </section>

                <section class="bg-white border border-[#EFF0EF] rounded-2xl px-5 py-5">
                    <h2 class="text-[14px] font-bold text-[#1B1B18]">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                    <div class="mt-2 divide-y divide-[#F1F2F1]">
                        @foreach($quickActions as [$qaIcon, $qaLabel, $qaUrl, $qaStyle])
                        @php $qaColor = ['orange' => '#C97A16', 'green' => '#157A43', 'red' => '#E5484D', 'plain' => '#3B382F'][$qaStyle]; @endphp
                        @if($qaUrl)
                        <a href="{{ $qaUrl }}" class="flex items-center gap-3.5 py-3 group">
                            <i data-lucide="{{ $qaIcon }}" class="w-[18px] h-[18px] shrink-0" style="stroke-width:1.7;color:{{ $qaColor }}"></i>
                            <span class="flex-1 text-[13px] group-hover:text-[#14652F]" style="color:{{ $qaStyle === 'plain' ? '#3B382F' : $qaColor }}">{{ $qaLabel }}</span>
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

                <section class="bg-[#EDF3FC] rounded-2xl px-5 py-4 flex items-start gap-3">
                    <i data-lucide="lightbulb" class="w-[20px] h-[20px] shrink-0 text-[#3565DE]" style="stroke-width:1.7"></i>
                    <p class="text-[12px] leading-relaxed">
                        <span class="font-bold text-[#3565DE]">{{ $isFr ? 'Conseil' : 'Tip' }}</span><br>
                        <span class="text-[#3B382F]">{{ $isFr ? 'Soyez précis dans vos demandes de modification pour accélérer le processus de négociation.' : 'Be precise in your modification requests to speed up the negotiation process.' }}</span>
                    </p>
                </section>
            </aside>
        </div>
    </main>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
