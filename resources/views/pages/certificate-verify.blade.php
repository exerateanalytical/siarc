@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $certNumber = ($numero ?? '') !== '' ? $numero : 'GVN-2025-0002587';

    $navLinks = [
        [$isFr ? 'Accueil' : 'Home',          route('home', ['lang' => $lang])],
        [$isFr ? 'À propos' : 'About',        route('about')],
        [$isFr ? 'Artisans' : 'Artisans',     route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        [$isFr ? 'Produits' : 'Products',     route('products.index', ['lang' => $lang])],
        [$isFr ? 'Événements' : 'Events',     route('events.index')],
        [$isFr ? 'Actualités' : 'News',       route('events.index')],
        ['Contact',                            route('contact')],
    ];

    $certInfo = [
        ['file-text',      $isFr ? 'Numéro de certificat' : 'Certificate number',   'GVN-2025-0002587', false],
        ['user-round',     $isFr ? 'Nom de l\'entrepreneur' : 'Entrepreneur name',  'Artisan Ndop', false],
        ['file-check',     $isFr ? 'Type d\'entreprise' : 'Business type',          'Entreprise Individuelle', false],
        ['shield-check',   $isFr ? 'Catégorie principale' : 'Main category',        'Sculpture & Art traditionnel', false],
        ['briefcase',      $isFr ? 'Secteur d\'activité' : 'Activity sector',       "Artisanat d'Art / Arts & Crafts", false],
        ['calendar',       $isFr ? 'Date d\'adhésion' : 'Membership date',          '15 Mai 2025', false],
        ['calendar-clock', $isFr ? 'Date d\'expiration' : 'Expiry date',            '14 Mai 2026', false],
        ['shield-check',   $isFr ? 'Statut' : 'Status',                             $isFr ? 'MEMBRE ACTIF' : 'ACTIVE MEMBER', true],
    ];

    $securityChecks = $isFr
        ? ['Filigrane invisible', 'Hologramme 3D GVN', 'Encre UV invisible', 'Microtexte de sécurité', 'Numéro unique infalsifiable']
        : ['Invisible watermark', '3D GVN hologram', 'Invisible UV ink', 'Security microtext', 'Tamper-proof unique number'];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Vérifiez l\'authenticité d\'un certificat d\'adhésion de la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun.' : 'Verify the authenticity of a membership certificate of the National Virtual Gallery of Cameroonian Crafts.' }}">
    <title>{{ $isFr ? 'Vérification de certificat — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Certificate verification — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        certgr:  '#012B19',
                        leaf:    '#164C28',
                        deep:    '#0A331C',
                        goldlt:  '#D9A439',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <script src="{{ asset('vendor/qrcode.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
    </style>
</head>
<body class="bg-[#FEFEFE] text-[#1B1B18] antialiased">

<!-- Header -->
{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-header')

<div class="max-w-[1024px] mx-auto px-4 lg:px-0">

    <!-- Hero banner -->
    <section class="relative bg-certgr rounded-2xl overflow-hidden mt-2">
        <img src="{{ asset('images/landing/cert-hero-art.png') }}" alt="" class="absolute right-0 inset-y-0 h-full hidden sm:block pointer-events-none select-none" aria-hidden="true">
        <div class="absolute inset-0 opacity-10 bg-repeat sm:right-[45%]" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
        <div class="relative px-6 sm:px-12 py-10 max-w-[520px]">
            <h1 class="text-[26px] sm:text-[30px] font-bold text-white tracking-wide uppercase leading-tight">{{ $isFr ? 'Vérification de certificat' : 'Certificate verification' }}</h1>
            <p class="mt-1 text-[16px] sm:text-[18px] font-semibold tracking-[0.18em] text-goldlt uppercase">{{ $isFr ? 'Certificate Verification' : 'Vérification de certificat' }}</p>
            <p class="mt-4 text-[13.5px] text-[#D4E0D6] leading-relaxed">
                {{ $isFr
                    ? "Entrez le numéro du certificat ou scannez le QR Code pour vérifier l'authenticité du certificat d'adhésion."
                    : 'Enter the certificate number or scan the QR Code to verify the authenticity of the membership certificate.'
                }}
            </p>
        </div>
    </section>

    <!-- Verify card -->
    <section class="mt-6 bg-white border border-[#EDEDEB] rounded-2xl shadow-sm overflow-hidden">
        <div class="grid grid-cols-2 border-b border-[#EDEDEB]">
            <button type="button" id="tab-num" class="flex items-center justify-center gap-2.5 py-4 text-[13px] font-bold tracking-[0.06em] uppercase text-[#14532D] border-b-2 border-[#14532D] bg-white">
                <i data-lucide="file-text" class="w-[18px] h-[18px]"></i>
                {{ $isFr ? 'Vérifier par numéro' : 'Verify by number' }}
            </button>
            <button type="button" id="tab-qr" class="flex items-center justify-center gap-2.5 py-4 text-[13px] font-bold tracking-[0.06em] uppercase text-[#8A857A] bg-[#FAFAF8]">
                <i data-lucide="qr-code" class="w-[18px] h-[18px]"></i>
                {{ $isFr ? 'Vérifier par QR Code' : 'Verify by QR Code' }}
            </button>
        </div>
        <div id="panel-num" class="px-6 sm:px-10 py-7">
            <form method="GET" action="{{ route('certificate.verify') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input name="numero" type="text" value="{{ $numero ?? '' }}" placeholder="Ex: GVN-2025-0002587"
                    class="flex-1 h-[52px] border border-[#E4E2DD] rounded-lg px-5 text-[14px] placeholder-[#A09B8F] focus:outline-none focus:border-goldlt focus:ring-1 focus:ring-goldlt/40 transition">
                <button type="submit" class="h-[52px] bg-deep hover:bg-leaf text-white text-[14px] font-semibold px-8 rounded-lg transition-colors whitespace-nowrap">
                    {{ $isFr ? 'Vérifier le certificat' : 'Verify the certificate' }}
                </button>
            </form>
            <p class="mt-5 flex items-center justify-center gap-2.5 text-[12.5px] text-[#55524A]">
                <i data-lucide="shield-check" class="w-[18px] h-[18px] text-[#14532D]" style="fill:#14532D;color:white"></i>
                {{ $isFr
                    ? "Ce service est sécurisé et certifié par la Galerie Virtuelle Nationale de l'Artisanat du Cameroun."
                    : 'This service is secured and certified by the National Virtual Gallery of Cameroonian Crafts.'
                }}
            </p>
        </div>
        <div id="panel-qr" class="hidden px-6 sm:px-10 py-7 text-center">
            <div id="qr-tab-code" class="inline-block bg-white p-2 border border-[#E4E2DD] rounded-lg"></div>
            <p class="mt-4 text-[13px] text-[#55524A] max-w-md mx-auto">
                {{ $isFr
                    ? 'Scannez le QR Code présent sur le certificat avec l\'appareil photo de votre téléphone pour vérifier instantanément son authenticité.'
                    : 'Scan the QR Code on the certificate with your phone camera to instantly verify its authenticity.'
                }}
            </p>
        </div>
    </section>

    <!-- Result card -->
    <section class="mt-6 bg-white border border-[#EDEDEB] rounded-2xl shadow-sm overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 px-6 sm:px-8 py-4 bg-[#F7FAF6] border-b border-[#EDEFEA]">
            <div class="flex items-center gap-4">
                <span class="w-[46px] h-[46px] rounded-full bg-[#157A43] flex items-center justify-center shrink-0">
                    <i data-lucide="check" class="w-6 h-6 text-white" style="stroke-width:3.2"></i>
                </span>
                <div>
                    <h2 class="text-[17px] font-bold tracking-[0.03em] text-[#157A43] uppercase">{{ $isFr ? 'Certificat valide' : 'Valid certificate' }}</h2>
                    <p class="text-[13px] text-[#2E7D4F]">This certificate is authentic and verified</p>
                </div>
            </div>
            <div class="text-right">
                <p class="flex items-center justify-end gap-2 text-[12px] text-[#55524A]">
                    {{ $isFr ? 'Vérifié le 15 Mai 2025 à 14:35' : 'Verified on 15 May 2025 at 14:35' }}
                    <span class="w-2 h-2 rounded-full bg-[#157A43]"></span>
                </p>
                <span class="mt-1.5 inline-block bg-[#DFF2E2] border border-[#BEE3C5] text-[#14532D] text-[12px] font-semibold px-3 py-1 rounded-md">
                    {{ $isFr ? 'Statut :' : 'Status:' }} <span class="font-bold">{{ $isFr ? 'ACTIF' : 'ACTIVE' }}</span>
                </span>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-[1.35fr_1fr] gap-8 px-6 sm:px-8 py-7">
            <img src="{{ asset('images/landing/cert-image.png') }}" alt="{{ $isFr ? 'Certificat d\'adhésion' : 'Membership certificate' }}" class="w-full rounded-lg shadow-md self-start">
            <div>
                <h3 class="text-[16.5px] font-bold text-[#14532D] pb-3 border-b border-[#EDEDEB]">{{ $isFr ? 'Informations du certificat' : 'Certificate information' }}</h3>
                <dl class="mt-4 space-y-4">
                    @foreach($certInfo as [$ciIcon, $ciLabel, $ciValue, $ciPill])
                    <div class="flex items-start gap-3.5">
                        <i data-lucide="{{ $ciIcon }}" class="w-[22px] h-[22px] shrink-0 text-[#2E7D4F] mt-0.5" style="stroke-width:1.6"></i>
                        <div>
                            <dt class="text-[12px] text-[#8A857A]">{{ $ciLabel }}</dt>
                            @if($ciPill)
                            <dd class="mt-1"><span class="inline-block bg-[#DFF2E2] text-[#14532D] text-[12.5px] font-bold px-3 py-1 rounded-md">{{ $ciValue }}</span></dd>
                            @else
                            <dd class="text-[14.5px] font-semibold text-[#1B1B18]">{{ $ciValue }}</dd>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    <!-- Info cards -->
    <section class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white border border-[#EDEDEB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/landing/cert-card-icon-1.png') }}" alt="" class="w-[46px] h-[46px]" aria-hidden="true">
                <h3 class="text-[13.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Authenticité garantie' : 'Guaranteed authenticity' }}</h3>
            </div>
            <p class="mt-3.5 text-[12.5px] text-[#55524A] leading-relaxed">
                {{ $isFr
                    ? "Ce certificat est émis et certifié par la Galerie Virtuelle Nationale de l'Artisanat du Cameroun. Il est infalsifiable et entièrement vérifiable en ligne."
                    : 'This certificate is issued and certified by the National Virtual Gallery of Cameroonian Crafts. It is tamper-proof and fully verifiable online.'
                }}
            </p>
        </div>
        <div class="bg-white border border-[#EDEDEB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/landing/cert-card-icon-2.png') }}" alt="" class="w-[46px] h-[46px]" aria-hidden="true">
                <h3 class="text-[13.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Éléments de sécurité' : 'Security features' }}</h3>
            </div>
            <ul class="mt-3.5 space-y-2">
                @foreach($securityChecks as $check)
                <li class="flex items-center gap-2.5 text-[12.5px] text-[#55524A]">
                    <i data-lucide="check" class="w-4 h-4 text-[#157A43] shrink-0" style="stroke-width:2.6"></i>
                    {{ $check }}
                </li>
                @endforeach
            </ul>
        </div>
        <div class="bg-white border border-[#EDEDEB] rounded-2xl shadow-sm p-6">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/landing/cert-card-icon-3.png') }}" alt="" class="w-[46px] h-[46px]" aria-hidden="true">
                <h3 class="text-[13.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'À propos de ce certificat' : 'About this certificate' }}</h3>
            </div>
            <p class="mt-3.5 text-[12.5px] text-[#55524A] leading-relaxed">
                {{ $isFr
                    ? "Ce certificat prouve que l'artisan ou entrepreneur mentionné fait partie du réseau officiel des artisans du Cameroun et bénéficie des avantages et services de la plateforme."
                    : 'This certificate proves that the mentioned artisan or entrepreneur is part of the official network of Cameroonian artisans and benefits from the platform\'s advantages and services.'
                }}
            </p>
        </div>
    </section>

    <!-- QR + help -->
    <section class="mt-6 mb-10 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white border border-[#EDEDEB] rounded-2xl shadow-sm p-6 flex items-center gap-5">
            <div id="qr-code" class="shrink-0 bg-white"></div>
            <div>
                <h3 class="text-[13.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Scannez pour vérifier' : 'Scan to verify' }}</h3>
                <p class="mt-2 text-[12.5px] text-[#55524A] leading-relaxed">
                    {{ $isFr
                        ? 'Scannez le QR Code présent sur le certificat pour vérifier instantanément son authenticité.'
                        : 'Scan the QR Code on the certificate to instantly verify its authenticity.'
                    }}
                </p>
            </div>
        </div>
        <div class="relative bg-certgr rounded-2xl overflow-hidden p-6 flex items-center justify-between gap-5">
            <div class="absolute inset-0 opacity-10 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
            <div class="relative">
                <h3 class="text-[15px] font-bold text-white">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</h3>
                <p class="mt-1.5 text-[12px] text-[#C6D4C9] leading-relaxed max-w-[300px]">
                    {{ $isFr
                        ? 'Contactez notre équipe d\'assistance pour toute question concernant la vérification de certificat.'
                        : 'Contact our support team for any question about certificate verification.'
                    }}
                </p>
            </div>
            <a href="{{ route('contact') }}" class="relative shrink-0 border border-white/70 hover:bg-white/10 text-white text-[13px] font-semibold px-5 py-2.5 rounded-lg transition-colors whitespace-nowrap">
                {{ $isFr ? 'Nous contacter' : 'Contact us' }}
            </a>
        </div>
    </section>
</div>

<!-- Footer -->
{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();

    // Live QR codes encoding this verification URL
    const verifyUrl = @json(route('certificate.verify', ['numero' => $certNumber]));
    new QRCode(document.getElementById('qr-code'), { text: verifyUrl, width: 88, height: 88, correctLevel: QRCode.CorrectLevel.M });
    new QRCode(document.getElementById('qr-tab-code'), { text: verifyUrl, width: 132, height: 132, correctLevel: QRCode.CorrectLevel.M });

    // Tabs
    const tabNum = document.getElementById('tab-num');
    const tabQr = document.getElementById('tab-qr');
    const panelNum = document.getElementById('panel-num');
    const panelQr = document.getElementById('panel-qr');
    function setTab(isNum) {
        panelNum.classList.toggle('hidden', !isNum);
        panelQr.classList.toggle('hidden', isNum);
        tabNum.classList.toggle('text-[#14532D]', isNum);
        tabNum.classList.toggle('border-b-2', isNum);
        tabNum.classList.toggle('border-[#14532D]', isNum);
        tabNum.classList.toggle('text-[#8A857A]', !isNum);
        tabNum.classList.toggle('bg-[#FAFAF8]', !isNum);
        tabQr.classList.toggle('text-[#14532D]', !isNum);
        tabQr.classList.toggle('border-b-2', !isNum);
        tabQr.classList.toggle('border-[#14532D]', !isNum);
        tabQr.classList.toggle('text-[#8A857A]', isNum);
        tabQr.classList.toggle('bg-[#FAFAF8]', isNum);
    }
    tabNum.addEventListener('click', () => setTab(true));
    tabQr.addEventListener('click', () => setTab(false));
</script>
</body>
</html>
