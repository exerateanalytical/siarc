@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Static fallback shown when no FAQs are managed in the CMS yet (admin.cms)
    $fallbackFaqs = $isFr ? [
        ['Comment créer mon compte artisan ou entreprise ?', "Cliquez sur « Devenir membre », choisissez votre type de compte (artisan individuel, coopérative, PME ou grande entreprise) et suivez les étapes du formulaire d'inscription."],
        ['La plateforme est-elle gratuite ?', "L'inscription et la création de votre vitrine sont gratuites. Des offres premium avec plus de visibilité et des statistiques avancées sont proposées aux membres."],
        ['Comment obtenir le badge « Vérifié » ?', "Soumettez vos documents officiels (registre de commerce, attestation fiscale, carte d'artisan) depuis votre tableau de bord, rubrique Vérification. Notre équipe traite chaque dossier sous 24 à 72 heures."],
        ['Comment demander un devis à un artisan ?', "Depuis la fiche d'un produit ou d'une entreprise, cliquez sur « Demander un devis » et décrivez votre besoin. L'artisan vous répond directement dans votre messagerie."],
        ['Comment les paiements fonctionnent-ils ?', "Les conditions de paiement sont convenues entre l'acheteur et l'artisan dans le devis (généralement 50% à la commande, 50% avant expédition). La plateforme n'encaisse pas les paiements à votre place."],
        ['Puis-je vérifier l\'authenticité d\'un certificat ?', "Oui : la page « Vérifier un certificat » vous permet de contrôler tout certificat de membre à partir de son numéro ou de son QR code."],
        ['Comment participer aux événements et salons ?', "Consultez la page Événements, ouvrez l'événement qui vous intéresse et cliquez sur « S'inscrire »."],
    ] : [
        ['How do I create my artisan or business account?', 'Click "Become a member", choose your account type (individual artisan, cooperative, SME or large company) and follow the sign-up steps.'],
        ['Is the platform free?', 'Signing up and creating your showcase are free. Premium offers with more visibility and advanced statistics are available to members.'],
        ['How do I get the "Verified" badge?', 'Submit your official documents (trade register, tax certificate, artisan card) from your dashboard under Verification. Each file is processed within 24 to 72 hours.'],
        ['How do I request a quote from an artisan?', 'From a product or business page, click "Request a quote" and describe your need. The artisan replies directly in your inbox.'],
        ['How do payments work?', 'Payment terms are agreed between the buyer and the artisan in the quote (usually 50% on order, 50% before shipment). The platform does not collect payments on your behalf.'],
        ['Can I check the authenticity of a certificate?', 'Yes: the "Verify a certificate" page lets you check any member certificate from its number or QR code.'],
        ['How do I take part in events and fairs?', 'Browse the Events page, open the event you are interested in and click "Register".'],
    ];

    $hasDbFaqs = $faqCategories->isNotEmpty() || $uncategorizedFaqs->isNotEmpty();
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'FAQ — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'FAQ — National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#164C28', gold: '#D9A439', goldbt: '#E9A830' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-chev { transform: rotate(180deg); }
    </style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[820px] mx-auto px-5 py-10 text-center">
        <h1 class="text-[26px] font-bold text-white">{{ $isFr ? 'Foire aux questions' : 'Frequently asked questions' }}</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC]">{{ $isFr ? 'Les réponses aux questions les plus fréquentes sur la Galerie Virtuelle.' : 'Answers to the most common questions about the Virtual Gallery.' }}</p>
    </div>
</section>

<main class="max-w-[820px] mx-auto px-5 py-10 pb-20">
    @if($hasDbFaqs)
        @foreach($faqCategories as $cat)
        <h2 class="mt-6 first:mt-0 mb-3 text-[14px] font-bold tracking-[0.06em] text-[#14652F] uppercase">{{ $isFr ? $cat->name_fr : ($cat->name_en ?? $cat->name_fr) }}</h2>
        <div class="space-y-3">
            @foreach($cat->faqs as $faq)
            <details class="bg-white border border-[#ECECEA] rounded-xl px-5 py-4">
                <summary class="flex items-center justify-between gap-3 text-[13.5px] font-bold text-[#1B1B18]">
                    {{ $isFr ? $faq->question_fr : ($faq->question_en ?? $faq->question_fr) }}
                    <i data-lucide="chevron-down" class="faq-chev w-4 h-4 shrink-0 text-[#14652F] transition-transform"></i>
                </summary>
                <p class="mt-3 text-[13px] text-[#55524A] leading-relaxed">{{ $isFr ? $faq->answer_fr : ($faq->answer_en ?? $faq->answer_fr) }}</p>
            </details>
            @endforeach
        </div>
        @endforeach
        @if($uncategorizedFaqs->isNotEmpty())
        <div class="mt-6 space-y-3">
            @foreach($uncategorizedFaqs as $faq)
            <details class="bg-white border border-[#ECECEA] rounded-xl px-5 py-4">
                <summary class="flex items-center justify-between gap-3 text-[13.5px] font-bold text-[#1B1B18]">
                    {{ $isFr ? $faq->question_fr : ($faq->question_en ?? $faq->question_fr) }}
                    <i data-lucide="chevron-down" class="faq-chev w-4 h-4 shrink-0 text-[#14652F] transition-transform"></i>
                </summary>
                <p class="mt-3 text-[13px] text-[#55524A] leading-relaxed">{{ $isFr ? $faq->answer_fr : ($faq->answer_en ?? $faq->answer_fr) }}</p>
            </details>
            @endforeach
        </div>
        @endif
    @else
        <div class="space-y-3">
            @foreach($fallbackFaqs as [$fq, $fa])
            <details class="bg-white border border-[#ECECEA] rounded-xl px-5 py-4">
                <summary class="flex items-center justify-between gap-3 text-[13.5px] font-bold text-[#1B1B18]">
                    {{ $fq }}
                    <i data-lucide="chevron-down" class="faq-chev w-4 h-4 shrink-0 text-[#14652F] transition-transform"></i>
                </summary>
                <p class="mt-3 text-[13px] text-[#55524A] leading-relaxed">{{ $fa }}</p>
            </details>
            @endforeach
        </div>
    @endif

    <div class="mt-8 bg-[#EFF5F0] rounded-2xl px-6 py-5 flex flex-wrap items-center gap-4">
        <i data-lucide="message-circle" class="w-[28px] h-[28px] shrink-0 text-[#14652F]" style="stroke-width:1.6"></i>
        <p class="flex-1 min-w-[240px] text-[13px] text-[#3B382F]">
            <span class="font-bold">{{ $isFr ? 'Vous ne trouvez pas votre réponse ?' : 'Can\'t find your answer?' }}</span>
            {{ $isFr ? 'Écrivez-nous, nous répondons rapidement.' : 'Write to us — we reply quickly.' }}
        </p>
        <a href="{{ route('contact', ['lang' => $lang]) }}" class="shrink-0 inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </a>
    </div>
</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
