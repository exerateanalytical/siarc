@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // [icon, title, description, ctaLabel, ctaHref]
    $guideSteps = [
        ['user-plus', $isFr ? '1. Créez votre compte' : '1. Create your account',
            $isFr ? "Choisissez votre type de compte (artisan individuel, coopérative, PME ou grande entreprise) et renseignez votre identité en quelques minutes." : 'Choose your account type (individual artisan, cooperative, SME or large company) and fill in your identity in a few minutes.',
            $isFr ? 'Créer mon compte' : 'Create my account', route('onboarding', ['lang' => $lang])],
        ['building-2', $isFr ? '2. Créez votre boutique' : '2. Create your shop',
            $isFr ? "Présentez votre atelier : nom, secteur d'activité, localisation, photos et description de votre savoir-faire." : 'Present your workshop: name, sector, location, photos and a description of your know-how.',
            $isFr ? 'Créer ma boutique' : 'Create my shop', route('business.create', ['lang' => $lang])],
        ['badge-check', $isFr ? '3. Faites vérifier votre profil' : '3. Get your profile verified',
            $isFr ? 'Soumettez vos documents (registre de commerce, carte d\'artisan…) pour obtenir le badge vérifié et renforcer la confiance des acheteurs.' : 'Submit your documents (trade register, artisan card…) to earn the verified badge and strengthen buyer trust.',
            $isFr ? 'Vérification' : 'Verification', route('verification.show', ['lang' => $lang])],
        ['package', $isFr ? '4. Publiez vos produits' : '4. Publish your products',
            $isFr ? 'Ajoutez vos créations avec de belles photos, des descriptions précises et vos prix indicatifs.' : 'Add your creations with beautiful photos, precise descriptions and indicative prices.',
            $isFr ? 'Ajouter un produit' : 'Add a product', route('products.web-create', ['lang' => $lang])],
        ['message-circle', $isFr ? '5. Répondez aux demandes de devis' : '5. Respond to quote requests',
            $isFr ? 'Les acheteurs vous contactent directement. Répondez rapidement pour maximiser vos chances de conversion.' : 'Buyers contact you directly. Reply quickly to maximise your conversion chances.',
            'Messages', route('messages.inbox', ['lang' => $lang])],
        ['calendar-days', $isFr ? '6. Participez aux événements' : '6. Take part in events',
            $isFr ? 'Foires, salons et expositions : gagnez en visibilité et rencontrez de nouveaux clients.' : 'Fairs, shows and exhibitions: gain visibility and meet new clients.',
            $isFr ? 'Voir les événements' : 'See the events', route('events.index', ['lang' => $lang])],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Guide de l\'artisan — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Artisan guide — National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#164C28', gold: '#D9A439', goldbt: '#E9A830' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }</style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[1024px] mx-auto px-5 py-10 text-center">
        <h1 class="text-[26px] font-bold text-white">{{ $isFr ? 'Guide de l\'artisan' : 'The artisan guide' }}</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC]">{{ $isFr ? 'De l\'inscription à vos premières ventes : tout ce qu\'il faut savoir pour réussir sur la Galerie Virtuelle.' : 'From signing up to your first sales: everything you need to succeed on the Virtual Gallery.' }}</p>
    </div>
</section>

<main class="max-w-[1024px] mx-auto px-5 py-10 pb-20">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($guideSteps as [$gsIcon, $gsTitle, $gsDesc, $gsCta, $gsHref])
        <article class="bg-white border border-[#ECECEA] rounded-2xl p-6 flex flex-col">
            <span class="w-[46px] h-[46px] rounded-xl bg-[#E4F1E8] flex items-center justify-center">
                <i data-lucide="{{ $gsIcon }}" class="w-[22px] h-[22px] text-[#14652F]" style="stroke-width:1.7"></i>
            </span>
            <h2 class="mt-4 text-[15px] font-bold text-[#1B1B18]">{{ $gsTitle }}</h2>
            <p class="mt-2 text-[13px] text-[#55524A] leading-relaxed flex-1">{{ $gsDesc }}</p>
            <a href="{{ $gsHref }}" class="mt-4 inline-flex items-center gap-2 text-[13px] font-semibold text-[#14652F] hover:text-[#14532D]">
                {{ $gsCta }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </article>
        @endforeach
    </div>

    <div class="mt-8 bg-[#EFF5F0] rounded-2xl px-6 py-5 flex flex-wrap items-center gap-4">
        <i data-lucide="headphones" class="w-[30px] h-[30px] shrink-0 text-[#14652F]" style="stroke-width:1.6"></i>
        <p class="flex-1 min-w-[240px] text-[13px] text-[#3B382F]">
            <span class="font-bold">{{ $isFr ? 'Besoin d\'aide pour démarrer ?' : 'Need help getting started?' }}</span>
            {{ $isFr ? 'Notre équipe vous accompagne à chaque étape.' : 'Our team supports you at every step.' }}
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
