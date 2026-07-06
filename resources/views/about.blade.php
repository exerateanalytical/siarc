@php
    $isFr = $lang === 'fr';

    $navLinks = [
        [$isFr ? 'Explorer' : 'Explore',        route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Collections' : 'Collections', route('industries.index', ['lang' => $lang]), false],
        [$isFr ? 'Artisans' : 'Artisans',       route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Régions' : 'Regions',         route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Secteurs' : 'Sectors',        route('industries.index', ['lang' => $lang]), false],
        [$isFr ? 'Événements' : 'Events',       route('events.index'), false],
        [$isFr ? 'À propos' : 'About',          route('about'), true],
    ];

    $heroItems = $isFr ? [
        ['shield', "Plateforme officielle\nsoutenue par le Ministère\ndes Arts et de la Culture"],
        ['users',  "Préserver notre patrimoine\nimmatériel pour les\ngénérations futures"],
        ['globe',  "Connecter le Cameroun\nau monde à travers son\nartisanat"],
    ] : [
        ['shield', "Official platform\nsupported by the Ministry\nof Arts and Culture"],
        ['users',  "Preserving our intangible\nheritage for future\ngenerations"],
        ['globe',  "Connecting Cameroon\nto the world through its\ncraftsmanship"],
    ];

    $aboutStats = $isFr ? [
        ['map-pin',      '10',      'Régions',      'aux cultures uniques'],
        ['users',        '250+',    'Communautés',  'ethniques et culturelles'],
        ['user-round',   '10 000+', 'Artisans',     'talentueux'],
        ['shopping-bag', '50 000+', 'Produits',     'authentiques'],
        ['globe',        '100+',    'Partenaires',  'locaux et internationaux'],
    ] : [
        ['map-pin',      '10',      'Regions',      'with unique cultures'],
        ['users',        '250+',    'Communities',  'ethnic and cultural'],
        ['user-round',   '10,000+', 'Artisans',     'talented'],
        ['shopping-bag', '50,000+', 'Products',     'authentic'],
        ['globe',        '100+',    'Partners',     'local and international'],
    ];

    $values = $isFr ? [
        ['diamond',      '#B42025', 'Authenticité', "Nous valorisons l'originalité et le respect des traditions."],
        ['flower-2',     '#1F5231', 'Qualité',      "Nous encourageons l'excellence et le souci du détail."],
        ['handshake',    '#C9942E', 'Équité',       "Nous soutenons des conditions justes pour tous les artisans."],
        ['shield-check', '#1F4030', 'Durabilité',   "Nous promouvons des pratiques responsables et durables."],
        ['users',        '#B42025', 'Fierté',       "Nous célébrons notre identité culturelle camerounaise."],
        ['lightbulb',    '#C9942E', 'Innovation',   "Nous encourageons la créativité et l'adaptation au monde moderne."],
    ] : [
        ['diamond',      '#B42025', 'Authenticity',   "We value originality and respect for traditions."],
        ['flower-2',     '#1F5231', 'Quality',        "We encourage excellence and attention to detail."],
        ['handshake',    '#C9942E', 'Fairness',       "We support fair conditions for all artisans."],
        ['shield-check', '#1F4030', 'Sustainability', "We promote responsible and sustainable practices."],
        ['users',        '#B42025', 'Pride',          "We celebrate our Cameroonian cultural identity."],
        ['lightbulb',    '#C9942E', 'Innovation',     "We encourage creativity and adaptation to the modern world."],
    ];

    $weDo = $isFr ? [
        ['megaphone',       "Promouvoir l'artisanat",   "Offrir une visibilité nationale et internationale aux artisans,\nà leurs créations et à leurs histoires."],
        ['heart-handshake', "Soutenir les artisans",    "Mettre à leur disposition des outils, des formations et des\nopportunités économiques."],
        ['landmark',        "Préserver le patrimoine",  "Documenter, sauvegarder et transmettre les savoir-faire\net techniques traditionnels."],
        ['link-2',          "Créer des connexions",     "Faciliter les échanges entre artisans, acheteurs, institutions\net partenaires du monde entier."],
    ] : [
        ['megaphone',       "Promote craftsmanship",  "Give national and international visibility to artisans,\ntheir creations and their stories."],
        ['heart-handshake', "Support artisans",       "Provide them with tools, training and\neconomic opportunities."],
        ['landmark',        "Preserve heritage",      "Document, safeguard and pass on traditional\nknow-how and techniques."],
        ['link-2',          "Create connections",     "Facilitate exchanges between artisans, buyers, institutions\nand partners around the world."],
    ];

    $audiences = $isFr ? [
        ['users',        '#1F5231', "Artisans et\nCoopératives",       "Valorisez vos créations\net développez votre\nactivité."],
        ['shopping-bag', '#C9942E', "Acheteurs et\nCollectionneurs",   "Découvrez des pièces\nauthentiques directement\nauprès des artisans."],
        ['landmark',     '#C9942E', "Institutions et\nPartenaires",    "Collaborez avec nous\npour des projets culturels\net économiques."],
        ['camera',       '#B42025', "Touristes et\nVisiteurs",         "Explorez la richesse\nartisanale et culturelle\ndu Cameroun."],
        ['rocket',       '#1F5231', "Jeunes et\nEntrepreneurs",        "Inspirez-vous, apprenez\net bâtissez l'avenir de\nl'artisanat."],
        ['newspaper',    '#C9942E', "Médias et\nChercheurs",           "Accédez à des ressources\nfiables sur l'artisanat\ncamerounais."],
    ] : [
        ['users',        '#1F5231', "Artisans and\nCooperatives",      "Showcase your creations\nand grow your\nbusiness."],
        ['shopping-bag', '#C9942E', "Buyers and\nCollectors",          "Discover authentic pieces\ndirectly from the\nartisans."],
        ['landmark',     '#C9942E', "Institutions and\nPartners",      "Collaborate with us\non cultural and economic\nprojects."],
        ['camera',       '#B42025', "Tourists and\nVisitors",          "Explore Cameroon's\ncraft and cultural\nrichness."],
        ['rocket',       '#1F5231', "Youth and\nEntrepreneurs",        "Get inspired, learn\nand build the future of\ncraftsmanship."],
        ['newspaper',    '#C9942E', "Media and\nResearchers",          "Access reliable resources\non Cameroonian\ncraftsmanship."],
    ];

    $siacUser = session('siac_user');

    $socialIcons = [
        'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
        'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
        'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
        'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
        'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'À propos de la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun — notre vision, notre mission et nos valeurs.' : 'About the National Virtual Gallery of Cameroonian Crafts — our vision, our mission and our values.' }}">
    <title>{{ $isFr ? 'À propos — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'About — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream:  '#F7F2EC',
                        parch:  '#FDFBF7',
                        sand:   '#E7E1D4',
                        leaf:   '#164C28',
                        pine:   '#0E1D13',
                        inkgr:  '#0F2D19',
                        gold:   '#C9942E',
                        goldlt: '#D9A439',
                        goldbt: '#E0A52F',
                        flagg:  '#125527',
                        flagr:  '#C10913',
                        flagy:  '#EBAB1A',
                        muted:  '#8A857A',
                        sage:   '#A8B8AC',
                    },
                    fontFamily: {
                        sans:  ['Poppins', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-cream text-[#1D1B16] antialiased">

{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">

<!-- Hero -->
<section class="relative bg-pine overflow-hidden">
    <div class="absolute inset-0 opacity-90 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
    <img src="{{ asset('images/landing/about-hero.png') }}" alt=""
        class="absolute right-0 top-0 h-full w-[55%] object-cover hidden md:block" style="mask-image:linear-gradient(90deg,transparent 0%,black 22%);-webkit-mask-image:linear-gradient(90deg,transparent 0%,black 22%)">

    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-9 lg:py-11">
        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-[11.5px] text-white/65" aria-label="Breadcrumb">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3 h-3 text-white/40"></i>
            <span class="text-white/90">{{ $isFr ? 'À propos' : 'About' }}</span>
        </nav>

        <div class="mt-9 max-w-xl">
            <p class="text-[11.5px] font-semibold tracking-[0.2em] text-goldlt uppercase">{{ $isFr ? 'À propos de nous' : 'About us' }}</p>

            <h1 class="mt-4 font-serif text-[30px] sm:text-[36px] lg:text-[40px] leading-[1.2] text-white font-medium">
                {{ $isFr ? 'À propos de la Galerie' : 'About the National' }}<br>
                {{ $isFr ? 'Virtuelle Nationale' : 'Virtual Gallery' }}<br>
                {{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}
            </h1>

            <div class="mt-6 h-[2px] w-10 bg-goldlt"></div>

            <p class="mt-6 text-[13.5px] text-white/85 leading-relaxed max-w-md">
                {{ $isFr
                    ? "Une plateforme officielle dédiée à la promotion, à la préservation et à la valorisation de l'artisanat camerounais, héritage vivant de notre diversité culturelle et de notre créativité."
                    : "An official platform dedicated to promoting, preserving and showcasing Cameroonian craftsmanship, the living heritage of our cultural diversity and creativity."
                }}
            </p>
        </div>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-3 gap-7 max-w-2xl">
            @foreach($heroItems as [$hiIcon, $hiText])
            <div>
                <i data-lucide="{{ $hiIcon }}" class="w-8 h-8 text-gold" stroke-width="1.4"></i>
                <p class="mt-3.5 text-[12.5px] text-white/90 leading-relaxed whitespace-pre-line">{{ $hiText }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Vision / Mission -->
<section class="bg-cream">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8 py-12 lg:py-14">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_170px_1fr] gap-10 items-start">
            <div>
                <p class="text-[11.5px] font-semibold tracking-[0.2em] text-gold uppercase">{{ $isFr ? 'Notre vision' : 'Our vision' }}</p>
                <h2 class="mt-4 font-serif text-[24px] lg:text-[27px] leading-snug text-[#1D1B16] font-medium">
                    {{ $isFr ? 'Un artisanat camerounais reconnu, valorisé et durable' : 'Cameroonian craftsmanship that is recognised, valued and sustainable' }}
                </h2>
                <p class="mt-5 text-[13px] text-[#6B665C] leading-relaxed">
                    {{ $isFr
                        ? "Nous aspirons à un Cameroun où chaque artisan, chaque création et chaque tradition est reconnue, protégée et transmise, contribuant au rayonnement culturel, économique et touristique du pays."
                        : "We aspire to a Cameroon where every artisan, every creation and every tradition is recognised, protected and passed on, contributing to the country's cultural, economic and tourism influence."
                    }}
                </p>
            </div>

            <div class="hidden lg:flex items-center justify-center pt-8">
                <img src="{{ asset('images/landing/about-motif.png') }}" alt="" class="w-[140px] h-auto">
            </div>

            <div>
                <p class="text-[11.5px] font-semibold tracking-[0.2em] text-gold uppercase">{{ $isFr ? 'Notre mission' : 'Our mission' }}</p>
                <h2 class="mt-4 font-serif text-[24px] lg:text-[27px] leading-snug text-[#1D1B16] font-medium">
                    {{ $isFr ? 'Valoriser, connecter, préserver et promouvoir' : 'Showcase, connect, preserve and promote' }}
                </h2>
                <p class="mt-5 text-[13px] text-[#6B665C] leading-relaxed">
                    {{ $isFr
                        ? "Nous valorisons le savoir-faire artisanal, connectons les acteurs aux opportunités locales et internationales, préservons notre patrimoine et encourageons l'innovation pour un développement durable et inclusif."
                        : "We showcase artisan know-how, connect stakeholders to local and international opportunities, preserve our heritage and encourage innovation for sustainable, inclusive development."
                    }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Stats band -->
<section class="relative bg-pine overflow-hidden">
    <div class="absolute inset-0 opacity-60 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 sm:divide-x divide-gold/25 py-10">
            @foreach($aboutStats as [$asIcon, $asNum, $asLabel, $asCap])
            <div class="flex items-start gap-3.5 px-2 lg:px-6 py-3 sm:py-0">
                <i data-lucide="{{ $asIcon }}" class="w-7 h-7 text-gold shrink-0 mt-1" stroke-width="1.4"></i>
                <div class="leading-tight">
                    <p class="font-serif text-[27px] text-goldlt whitespace-nowrap">{{ $asNum }}</p>
                    <p class="mt-1 text-[11.5px] font-semibold tracking-[0.08em] text-white uppercase whitespace-nowrap">{{ $asLabel }}</p>
                    <p class="mt-1.5 text-[10.5px] text-white/60 whitespace-nowrap">{{ $asCap }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Nos valeurs -->
<section class="max-w-[1280px] mx-auto px-5 lg:px-8 pt-12 pb-4">
    <h2 class="text-center text-[15px] font-semibold tracking-[0.25em] text-gold uppercase">{{ $isFr ? 'Nos valeurs' : 'Our values' }}</h2>
    <div class="mt-2.5 mx-auto h-[2px] w-8 bg-gold"></div>

    <div class="mt-9 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach($values as [$vIcon, $vColor, $vTitle, $vDesc])
        <div class="bg-parch border border-sand rounded-xl shadow-[0_1px_3px_rgba(30,25,15,0.05)] px-3.5 pt-8 pb-6 text-center">
            <i data-lucide="{{ $vIcon }}" class="w-10 h-10 mx-auto" stroke-width="1.4" style="color:{{ $vColor }}"></i>
            <h3 class="mt-6 text-[13.5px] font-semibold text-[#1D1B16]">{{ $vTitle }}</h3>
            <div class="mt-2.5 mx-auto h-[2px] w-5 bg-gold/70"></div>
            <p class="mt-3.5 text-[11.5px] text-[#6B665C] leading-relaxed">{{ $vDesc }}</p>
        </div>
        @endforeach
    </div>
</section>

<!-- Ce que nous faisons -->
<section class="mt-12 relative">
    <div class="absolute right-0 top-0 bottom-0 w-[100px] bg-repeat-y bg-right hidden xl:block" style="background-image:url('{{ asset('images/landing/about-gold-strip.png') }}')"></div>
    <div class="grid grid-cols-1 lg:grid-cols-2 items-stretch">
        <img src="{{ asset('images/landing/about-potter.png') }}" alt="{{ $isFr ? 'Artisane potière camerounaise' : 'Cameroonian artisan potter' }}"
            class="w-full h-full min-h-[280px] object-cover">

        <div class="py-9 px-5 lg:pl-12 lg:pr-8 xl:pr-[120px] flex flex-col justify-center">
            <h2 class="text-[13.5px] font-semibold tracking-[0.18em] text-gold uppercase">{{ $isFr ? 'Ce que nous faisons' : 'What we do' }}</h2>

            <div class="mt-6 space-y-6">
                @foreach($weDo as [$wdIcon, $wdTitle, $wdDesc])
                <div class="flex items-start gap-4">
                    <span class="w-10 h-10 rounded-full bg-[#1F4030] flex items-center justify-center shrink-0">
                        <i data-lucide="{{ $wdIcon }}" class="w-[18px] h-[18px] text-gold" stroke-width="1.5"></i>
                    </span>
                    <div>
                        <h3 class="text-[13.5px] font-semibold text-[#1D1B16]">{{ $wdTitle }}</h3>
                        <p class="mt-1.5 text-[12px] text-[#6B665C] leading-relaxed whitespace-pre-line">{{ $wdDesc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- À qui nous nous adressons -->
<section class="max-w-[1280px] mx-auto px-5 lg:px-8 pt-12 pb-14">
    <h2 class="text-center text-[15px] font-semibold tracking-[0.22em] text-gold uppercase">{{ $isFr ? 'À qui nous nous adressons' : 'Who we serve' }}</h2>

    <div class="mt-9 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">
        @foreach($audiences as [$auIcon, $auColor, $auTitle, $auDesc])
        <div class="text-center">
            <i data-lucide="{{ $auIcon }}" class="w-9 h-9 mx-auto" stroke-width="1.4" style="color:{{ $auColor }}"></i>
            <h3 class="mt-4 text-[12.5px] font-semibold text-[#1D1B16] leading-snug whitespace-pre-line">{{ $auTitle }}</h3>
            <p class="mt-2.5 text-[11px] text-[#6B665C] leading-relaxed whitespace-pre-line">{{ $auDesc }}</p>
        </div>
        @endforeach
    </div>
</section>

<!-- CTA -->
<section class="relative bg-pine overflow-hidden">
    <div class="absolute inset-0 opacity-60 bg-repeat" style="background-image:url('{{ asset('images/landing/about-pattern-tile.png') }}')"></div>
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-11 text-center">
        <h2 class="font-serif text-[22px] sm:text-[27px] text-white font-medium">
            {{ $isFr ? 'Rejoignez-nous pour faire rayonner l\'artisanat camerounais !' : 'Join us in making Cameroonian craftsmanship shine!' }}
        </h2>
        <a href="{{ route('industries.index', ['lang' => $lang]) }}"
            class="mt-7 inline-flex items-center gap-3 bg-goldbt hover:bg-[#c9942e] text-[#4a3a0b] text-[13px] font-semibold px-6 py-3 rounded-md transition-colors">
            {{ $isFr ? 'Découvrir nos collections' : 'Discover our collections' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>

</main>

<!-- Footer -->
<div class="h-[42px] bg-pine bg-repeat-x" style="background-image:url('{{ asset('images/landing/about-footer-band.png') }}')"></div>
{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-footer')

<!-- Mobile bottom navigation -->
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @php
        $bottomTabs = [
            ['href' => route('home', ['lang' => $lang]), 'icon' => 'home', 'label' => $isFr ? 'Accueil' : 'Home', 'active' => false],
            ['href' => route('businesses.index', ['lang' => $lang]), 'icon' => 'compass', 'label' => $isFr ? 'Explorer' : 'Explore', 'active' => false],
            ['href' => $siacUser ? route('saved.index') : '/login', 'icon' => 'bookmark', 'label' => $isFr ? 'Favoris' : 'Saved', 'active' => false],
            ['href' => $siacUser ? route('messages.inbox') : '/login', 'icon' => 'message-circle', 'label' => 'Messages', 'active' => false],
            ['href' => $siacUser ? '/tableau-de-bord' : '/login', 'icon' => 'user', 'label' => $isFr ? 'Profil' : 'Profile', 'active' => false],
        ];
    @endphp
    @foreach($bottomTabs as $tab)
    <a href="{{ $tab['href'] }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 {{ $tab['active'] ? 'text-leaf' : 'text-gray-400' }}">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5"></i>
        <span class="text-[10px] font-medium">{{ $tab['label'] }}</span>
    </a>
    @endforeach
</nav>

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
