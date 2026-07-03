@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $pressFacts = [
        [number_format($pressStats['businesses']), $isFr ? 'Artisans & entreprises' : 'Artisans & businesses'],
        [number_format($pressStats['products']),   $isFr ? 'Produits publiés' : 'Published products'],
        [number_format($pressStats['events']),     $isFr ? 'Événements' : 'Events'],
        [number_format($pressStats['regions']),    $isFr ? 'Régions couvertes' : 'Regions covered'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Presse — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Press — National Virtual Gallery of Cameroonian Crafts' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#164C28', gold: '#D9A439', goldbt: '#E9A830' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }</style>
</head>
<body class="bg-[#F8F6F2] text-[#1B1B18] antialiased">

@include('pages.partials.directory-header')

<section class="bg-[#0B2C1E]">
    <div class="max-w-[900px] mx-auto px-5 py-10 text-center">
        <h1 class="text-[26px] font-bold text-white">{{ $isFr ? 'Espace presse' : 'Press room' }}</h1>
        <p class="mt-2 text-[13.5px] text-[#B9C4BC]">{{ $isFr ? 'Informations, chiffres clés et contact média de la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun.' : 'Information, key figures and media contact for the National Virtual Gallery of Cameroonian Crafts.' }}</p>
    </div>
</section>

<main class="max-w-[900px] mx-auto px-5 py-10 pb-20">
    <section class="bg-white border border-[#ECECEA] rounded-2xl p-6">
        <h2 class="text-[15px] font-bold text-[#1B1B18]">{{ $isFr ? 'À propos de la plateforme' : 'About the platform' }}</h2>
        <p class="mt-2.5 text-[13px] text-[#55524A] leading-relaxed">
            {{ $isFr
                ? "La Galerie Virtuelle Nationale de l'Artisanat du Cameroun est la plateforme officielle de promotion et de valorisation du savoir-faire artisanal camerounais à travers le digital. Elle connecte artisans, coopératives et entreprises à des acheteurs locaux et internationaux : vitrines vérifiées, demandes de devis, événements et certification des membres."
                : 'The National Virtual Gallery of Cameroonian Crafts is the official platform promoting Cameroonian craftsmanship through digital. It connects artisans, cooperatives and businesses with local and international buyers: verified showcases, quote requests, events and member certification.'
            }}
        </p>
    </section>

    <section class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach($pressFacts as [$pfValue, $pfLabel])
        <div class="bg-white border border-[#ECECEA] rounded-2xl p-5 text-center">
            <p class="text-[24px] font-bold text-[#14652F]">{{ $pfValue }}</p>
            <p class="mt-1 text-[12px] text-[#55524A]">{{ $pfLabel }}</p>
        </div>
        @endforeach
    </section>

    <section class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white border border-[#ECECEA] rounded-2xl p-6">
            <h2 class="flex items-center gap-2.5 text-[14px] font-bold text-[#1B1B18]">
                <i data-lucide="mail" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i>
                {{ $isFr ? 'Contact média' : 'Media contact' }}
            </h2>
            <p class="mt-2.5 text-[13px] text-[#55524A] leading-relaxed">
                {{ $isFr ? 'Pour toute demande d\'interview, de reportage ou d\'information :' : 'For any interview, report or information request:' }}
            </p>
            <p class="mt-2 text-[13px] font-semibold text-[#14652F]">contact@artisanatcameroun.cm</p>
            <p class="mt-1 text-[13px] text-[#3B382F]">+237 670 416 238 — {{ $isFr ? 'Lun - Ven : 8h00 - 17h00' : 'Mon - Fri: 8am - 5pm' }}</p>
            <a href="{{ route('contact', ['lang' => $lang]) }}" class="mt-4 inline-flex items-center gap-2.5 bg-[#0A3020] hover:bg-[#14652F] text-white text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors">
                {{ $isFr ? 'Écrire au service presse' : 'Write to the press office' }}
            </a>
        </div>
        <div class="bg-white border border-[#ECECEA] rounded-2xl p-6">
            <h2 class="flex items-center gap-2.5 text-[14px] font-bold text-[#1B1B18]">
                <i data-lucide="download" class="w-[18px] h-[18px] text-[#14652F]" style="stroke-width:1.7"></i>
                {{ $isFr ? 'Identité visuelle' : 'Brand assets' }}
            </h2>
            <div class="mt-3 flex items-center gap-4">
                <img src="{{ asset('images/landing/logo.png') }}" alt="Logo" class="w-[56px] h-[61px] object-contain">
                <p class="text-[12.5px] text-[#55524A] leading-relaxed">
                    {{ $isFr ? 'Le logo officiel de la plateforme. Merci de ne pas le modifier ni le déformer.' : 'The official platform logo. Please do not alter or distort it.' }}
                </p>
            </div>
            <a href="{{ asset('images/landing/logo.png') }}" download class="mt-4 inline-flex items-center gap-2.5 border border-[#DBDFDC] hover:border-leaf hover:text-leaf text-[13px] font-semibold text-[#1D1B16] px-5 py-2.5 rounded-lg transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                {{ $isFr ? 'Télécharger le logo' : 'Download the logo' }}
            </a>
        </div>
    </section>
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
