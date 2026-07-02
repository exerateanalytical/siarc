@php
    $isFr = $lang === 'fr';

    $frMonths = [1=>'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $enMonths = [1=>'January','February','March','April','May','June','July','August','September','October','November','December'];
    $monthName = fn ($m) => $isFr ? $frMonths[$m] : $enMonths[$m];

    // "15 – 20 Novembre 2026" / "10 Octobre – 19 Novembre 2025"
    $dateRange = function ($start, $end) use ($monthName) {
        $s = \Illuminate\Support\Carbon::parse($start);
        $e = \Illuminate\Support\Carbon::parse($end);
        if ($s->month === $e->month && $s->year === $e->year) {
            return $s->day . ' – ' . $e->day . ' ' . $monthName($s->month) . ' ' . $s->year;
        }
        return $s->day . ' ' . $monthName($s->month) . ($s->year !== $e->year ? ' ' . $s->year : '')
            . ' – ' . $e->day . ' ' . $monthName($e->month) . ' ' . $e->year;
    };

    $navLinks = [
        [$isFr ? 'Explorer' : 'Explore',        route('businesses.index', ['lang' => $lang])],
        [$isFr ? 'Collections' : 'Collections', route('industries.index', ['lang' => $lang])],
        [$isFr ? 'Artisans' : 'Artisans',       route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        [$isFr ? 'Régions' : 'Regions',         route('businesses.index', ['lang' => $lang])],
        [$isFr ? 'Événements' : 'Events',       route('events.index')],
        [$isFr ? 'Entreprises' : 'Businesses',  route('businesses.index', ['lang' => $lang])],
        [$isFr ? 'À propos' : 'About',          route('about')],
    ];

    $heroSlides = $isFr ? [
        ['l1' => 'Le patrimoine vivant', 'l2' => 'du Cameroun,', 'gold' => 'accessible au monde entier.',
         'sub' => "Découvrez la richesse de notre artisanat,\nrencontrez nos artisans, explorez nos régions\net soutenez un héritage séculaire."],
        ['l1' => 'Des artisans d\'exception,', 'l2' => 'dans les 10 régions,', 'gold' => 'un savoir-faire unique.',
         'sub' => "Sculpteurs, tisserands, potiers, bijoutiers :\ndes talents authentiques à découvrir\net à contacter directement."],
        ['l1' => 'Des créations authentiques,', 'l2' => 'faites main,', 'gold' => 'avec fierté et passion.',
         'sub' => "Chaque pièce raconte une histoire.\nParcourez des milliers de produits uniques\nissus de nos terroirs."],
        ['l1' => 'SIAC 2026,', 'l2' => 'le grand rendez-vous,', 'gold' => 'de nos filières artisanales.',
         'sub' => "Salon international, expositions, rencontres :\nvivez les événements qui font rayonner\nle savoir-faire camerounais."],
        ['l1' => 'Un héritage séculaire,', 'l2' => 'une vitrine nationale,', 'gold' => 'ouverte sur le monde.',
         'sub' => "La galerie virtuelle officielle qui connecte\nles artisans camerounais aux acheteurs\net partenaires du monde entier."],
    ] : [
        ['l1' => 'The living heritage', 'l2' => 'of Cameroon,', 'gold' => 'open to the whole world.',
         'sub' => "Discover the richness of our craftsmanship,\nmeet our artisans, explore our regions\nand support an ancestral heritage."],
        ['l1' => 'Exceptional artisans,', 'l2' => 'across all 10 regions,', 'gold' => 'a unique know-how.',
         'sub' => "Sculptors, weavers, potters, jewellers:\nauthentic talents to discover\nand contact directly."],
        ['l1' => 'Authentic creations,', 'l2' => 'entirely handmade,', 'gold' => 'with pride and passion.',
         'sub' => "Every piece tells a story.\nBrowse thousands of unique products\nfrom our regions."],
        ['l1' => 'SIAC 2026,', 'l2' => 'the flagship event,', 'gold' => 'of our craft industries.',
         'sub' => "International fair, exhibitions, meetings:\nexperience the events that showcase\nCameroonian craftsmanship."],
        ['l1' => 'An ancestral heritage,', 'l2' => 'a national showcase,', 'gold' => 'open to the world.',
         'sub' => "The official virtual gallery connecting\nCameroonian artisans with buyers\nand partners worldwide."],
    ];

    $statItems = [
        ['icon' => 'map-pin',      'color' => '#114023', 'n' => (string) ($stats['regions'] ?? 10), 'label' => $isFr ? 'Régions' : 'Regions',        'cap' => $isFr ? 'Une diversité culturelle unique' : 'A unique cultural diversity'],
        ['icon' => 'users',        'color' => '#A51717', 'n' => '250+',    'label' => $isFr ? 'Communautés' : 'Communities', 'cap' => $isFr ? 'Ethniques et culturelles' : 'Ethnic and cultural'],
        ['icon' => 'palette',      'color' => '#D99B2E', 'n' => $isFr ? '10 000+' : '10,000+', 'label' => $isFr ? 'Artisans' : 'Artisans',    'cap' => $isFr ? 'Talents et savoir-faire d\'exception' : 'Exceptional talents and know-how'],
        ['icon' => 'shopping-bag', 'color' => '#114023', 'n' => $isFr ? '50 000+' : '50,000+', 'label' => $isFr ? 'Produits' : 'Products',    'cap' => $isFr ? 'Créations authentiques et uniques' : 'Authentic and unique creations'],
        ['icon' => 'globe',        'color' => '#A51717', 'n' => $isFr ? 'Ouvert' : 'Open',     'label' => $isFr ? 'au monde' : 'to the world','cap' => $isFr ? 'Un impact local, une portée globale' : 'Local impact, global reach'],
    ];

    $missionItems = $isFr ? [
        ['medal',           'Valoriser',              'le savoir-faire local'],
        ['shopping-basket', 'Promouvoir',             'les produits camerounais'],
        ['award',           'Créer des opportunités', 'pour les artisans'],
        ['lightbulb',       'Encourager',             'l\'innovation et la créativité'],
        ['sprout',          'Contribuer',             'au développement durable'],
    ] : [
        ['medal',           'Promote',                'local craftsmanship'],
        ['shopping-basket', 'Showcase',               'Cameroonian products'],
        ['award',           'Create opportunities',   'for artisans'],
        ['lightbulb',       'Encourage',              'innovation and creativity'],
        ['sprout',          'Contribute',             'to sustainable development'],
    ];

    $siacUser = session('siac_user');
    $bizFallbacks = ['biz-1.png','biz-2.png','biz-3.png','biz-4.png','biz-5.png','biz-6.png'];
    $eventFallbacks = ['event-1.png','event-2.png','event-3.png'];

    // The 10 categories of the official design, mapped onto real gallery filters
    $sectorCards = [
        ['flower',    $isFr ? "Arts &\nDécoration"          : "Arts &\nDecoration",        route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['shirt',     $isFr ? "Mode &\nTextile"             : "Fashion &\nTextile",        route('businesses.index', ['lang' => $lang, 'industry' => 'textile-mode'])],
        ['trees',     $isFr ? "Bois &\nSculpture"           : "Wood &\nSculpture",         route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['amphora',   $isFr ? "Poterie &\nCéramique"        : "Pottery &\nCeramics",       route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['gem',       $isFr ? "Bijouterie &\nAccessoires"   : "Jewellery &\nAccessories",  route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['guitar',    $isFr ? "Musique &\nInstruments"      : "Music &\nInstruments",      route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['briefcase', $isFr ? "Cuir &\nMaroquinerie"        : "Leather &\nLeatherwork",    route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat'])],
        ['sprout',    $isFr ? "Produits\nNaturels"          : "Natural\nProducts",         route('businesses.index', ['lang' => $lang, 'industry' => 'agriculture'])],
        ['salad',     $isFr ? "Agroalimentaire"             : "Agri-food",                 route('businesses.index', ['lang' => $lang, 'industry' => 'agroalimentaire'])],
        ['atom',      $isFr ? "Technologies\n& Innovation"  : "Technology &\nInnovation",  route('businesses.index', ['lang' => $lang])],
    ];

    // Partner logo tiles cropped from the official design, keyed by name_fr
    $partnerTiles = [
        'MINCOMMERCE'                       => 'partner-mincommerce.png',
        'MINAC'                             => 'partner-minac.png',
        'UNESCO'                            => 'partner-unesco.png',
        'ITC'                               => 'partner-itc.png',
        'CEPII Cameroun'                    => 'partner-cepii.png',
        'OAPI'                              => 'partner-oapi.png',
        'Banque Africaine de Développement' => 'partner-bad.png',
        'AFD'                               => 'partner-afd.png',
        'Union Européenne'                  => 'partner-ue.png',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun — Notre héritage, notre fierté, notre avenir. Découvrez les artisans et producteurs camerounais.' : 'National Virtual Gallery of Cameroonian Crafts — Our heritage, our pride, our future. Discover Cameroonian artisans and producers.' }}">
    <title>{{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream:  '#F8F3ED',
                        parch:  '#FBF9F3',
                        sand:   '#E7E1D4',
                        leaf:   '#164C28',
                        pineur: '#0E261C',
                        deepgr: '#0A1F10',
                        inkgr:  '#0F2D19',
                        night:  '#0D0F0D',
                        gold:   '#E5A82E',
                        golddk: '#B0821A',
                        goldic: '#D79326',
                        flagg:  '#125527',
                        flagr:  '#C10913',
                        flagy:  '#EBAB1A',
                        cocoa:  '#4A2E1E',
                        muted:  '#8A857A',
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .hero-fade { transition: opacity .45s ease; }
        html, body { overflow-x: clip; }
    </style>
</head>
<body class="bg-cream text-[#1D1B16] antialiased">

<!-- Tricolor top bar -->
<div class="flex h-1.5">
    <div class="flex-1 bg-flagg"></div>
    <div class="flex-1 bg-flagr"></div>
    <div class="flex-1 bg-flagy"></div>
</div>

<!-- Header -->
<header class="bg-cream">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8">
        <div class="flex items-center justify-between gap-4 py-3.5">
            <!-- Brand -->
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-10 h-11 xl:w-11 xl:h-12 object-contain">
                <span class="leading-tight">
                    <span class="block text-[11px] xl:text-[12.5px] font-bold tracking-[0.06em] text-[#262521] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                    <span class="block text-[11px] xl:text-[12.5px] font-bold tracking-[0.06em] text-[#262521] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                    <span class="block text-[9.5px] xl:text-[10.5px] text-muted mt-0.5 whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                </span>
            </a>

            <!-- Nav -->
            <nav class="hidden lg:flex items-center gap-3.5 xl:gap-[18px]">
                @foreach($navLinks as [$navLabel, $navHref])
                <a href="{{ $navHref }}" class="whitespace-nowrap text-[12px] xl:text-[13px] font-medium text-[#262521] hover:text-leaf transition-colors">{{ $navLabel }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3 shrink-0">
                <!-- Search -->
                <form action="{{ route('gallery.search', ['lang' => $lang]) }}" method="GET" class="relative hidden lg:block">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="q" type="search" placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}"
                        class="w-[104px] xl:w-40 bg-white border border-[#DDD6C6] rounded-md pl-3 pr-7 py-2 text-[12px] text-[#262521] placeholder-[#A29D91] focus:outline-none focus:border-leaf transition-colors">
                    <button type="submit" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#8A857A]" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- Language -->
                <div class="relative group hidden sm:block">
                    <button class="flex items-center gap-1 px-1.5 py-2 text-[13px] font-semibold text-[#262521]">
                        {{ strtoupper($lang) }}
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                    </button>
                    <div class="absolute right-0 top-full w-28 bg-white rounded-lg shadow-lg border border-sand py-1 hidden group-hover:block z-50">
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-leaf' : 'text-[#262521] hover:bg-cream' }}">FR — Français</a>
                        <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-leaf' : 'text-[#262521] hover:bg-cream' }}">EN — English</a>
                    </div>
                </div>

                <!-- Auth -->
                @if($siacUser)
                <a href="/tableau-de-bord" class="hidden sm:inline-flex items-center whitespace-nowrap bg-inkgr hover:bg-leaf text-white text-[12px] xl:text-[13px] font-medium px-3.5 xl:px-4 py-2.5 rounded-md transition-colors">
                    {{ $isFr ? 'Tableau de bord' : 'Dashboard' }}
                </a>
                @else
                <a href="/login?lang={{ $lang }}" class="hidden sm:inline-flex items-center whitespace-nowrap bg-inkgr hover:bg-leaf text-white text-[12px] xl:text-[13px] font-medium px-3.5 xl:px-4 py-2.5 rounded-md transition-colors">
                    {{ $isFr ? 'Connexion' : 'Sign in' }}
                </a>
                @endif

                <!-- Mobile menu button -->
                <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-md hover:bg-sand/50" aria-label="Menu">
                    <i data-lucide="menu" class="w-5 h-5 text-[#262521]"></i>
                </button>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="hidden lg:hidden pb-4 border-t border-sand pt-3">
            <form action="{{ route('gallery.search', ['lang' => $lang]) }}" method="GET" class="relative mb-2">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <input name="q" type="search" placeholder="{{ $isFr ? 'Rechercher...' : 'Search...' }}"
                    class="w-full bg-white border border-[#DDD6C6] rounded-md pl-3.5 pr-9 py-2 text-[13px] placeholder-[#A29D91] focus:outline-none focus:border-leaf">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8A857A]" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </form>
            @foreach($navLinks as [$navLabel, $navHref])
            <a href="{{ $navHref }}" class="block px-2 py-2 text-[14px] font-medium text-[#262521] hover:bg-sand/40 rounded">{{ $navLabel }}</a>
            @endforeach
            <div class="border-t border-sand mt-2 pt-2 flex items-center justify-between px-2">
                @if($siacUser)
                <a href="/tableau-de-bord" class="inline-flex items-center bg-inkgr text-white text-[13px] font-medium px-4 py-2 rounded-md">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                @else
                <a href="/login?lang={{ $lang }}" class="inline-flex items-center bg-inkgr text-white text-[13px] font-medium px-4 py-2 rounded-md">{{ $isFr ? 'Connexion' : 'Sign in' }}</a>
                @endif
                <span class="flex items-center gap-2 text-[13px] font-semibold">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="{{ $isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">FR</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="{{ !$isFr ? 'text-leaf underline' : 'text-[#8A857A]' }}">EN</a>
                </span>
            </div>
        </div>
    </div>
</header>

<main class="pb-16 sm:pb-0">

<!-- Hero -->
<section class="relative bg-[#090B09] overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-[center_right]" style="background-image:url('{{ asset('images/landing/hero-bg.png') }}')"></div>
    <div class="absolute inset-0 bg-black/50 sm:bg-transparent"></div>
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8">
        <div class="py-16 lg:py-20 min-h-[480px] flex flex-col justify-between">
            <div id="hero-slide" class="hero-fade max-w-2xl">
                <h1 class="font-serif text-[30px] sm:text-[37px] xl:text-[44px] leading-[1.14] text-white font-medium">
                    <span id="hero-l1">{{ $heroSlides[0]['l1'] }}</span><br>
                    <span id="hero-l2">{{ $heroSlides[0]['l2'] }}</span><br>
                    <span id="hero-gold" class="text-gold italic">{{ $heroSlides[0]['gold'] }}</span>
                </h1>
                <div class="mt-6 h-px w-full max-w-[600px]" style="background:linear-gradient(90deg,#B0821A 0%,#B0821A 16%,rgba(18,85,39,0.55) 30%,rgba(9,11,9,0) 45%,rgba(9,11,9,0) 55%,rgba(193,9,19,0.45) 72%,#B0821A 84%,#B0821A 100%)"></div>
                <p id="hero-sub" class="mt-6 text-[14px] text-white/85 leading-relaxed whitespace-pre-line">{{ $heroSlides[0]['sub'] }}</p>
                <div class="mt-8 flex flex-wrap items-center gap-3.5">
                    <a href="{{ route('businesses.index', ['lang' => $lang]) }}"
                        class="inline-flex items-center gap-2.5 bg-leaf hover:bg-[#1B5E33] text-white text-[13px] font-medium px-6 py-3 rounded transition-colors">
                        {{ $isFr ? 'Explorer la galerie' : 'Explore the gallery' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']) }}"
                        class="inline-flex items-center gap-2.5 border border-golddk text-gold hover:bg-gold/10 text-[13px] font-medium px-5 py-3 rounded transition-colors">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        {{ $isFr ? 'Découvrir les artisans' : 'Discover the artisans' }}
                    </a>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-center gap-6 mt-12">
                @foreach($heroSlides as $i => $slide)
                <button data-slide="{{ $i }}" class="hero-dot text-[13px] tracking-wider pb-1.5 border-b-2 transition-colors {{ $i === 0 ? 'text-white border-gold' : 'text-white/55 border-transparent hover:text-white' }}">
                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Arrows -->
    <button id="hero-prev" aria-label="{{ $isFr ? 'Précédent' : 'Previous' }}"
        class="absolute left-4 lg:left-7 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full border border-white/40 text-white flex items-center justify-center hover:bg-white/10 transition-colors">
        <i data-lucide="chevron-left" class="w-5 h-5"></i>
    </button>
    <button id="hero-next" aria-label="{{ $isFr ? 'Suivant' : 'Next' }}"
        class="absolute right-4 lg:right-7 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full border border-white/40 text-white flex items-center justify-center hover:bg-white/10 transition-colors">
        <i data-lucide="chevron-right" class="w-5 h-5"></i>
    </button>
</section>

<!-- Stats band -->
<section class="max-w-[1280px] mx-auto px-5 lg:px-8">
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 sm:divide-x divide-[#E2DCCF] py-9">
        @foreach($statItems as $stat)
        <div class="flex items-center gap-3.5 px-2 lg:px-4 py-3 sm:py-1">
            <div class="w-16 h-16 rounded-full flex items-center justify-center shrink-0" style="background:{{ $stat['color'] }}">
                <i data-lucide="{{ $stat['icon'] }}" class="w-6 h-6 text-white"></i>
            </div>
            <div class="leading-tight">
                <p class="text-[19px] font-semibold text-[#1D1B16] whitespace-nowrap">{{ $stat['n'] }}</p>
                <p class="text-[13px] font-semibold text-[#1D1B16] whitespace-nowrap">{{ $stat['label'] }}</p>
                <p class="text-[11px] text-muted mt-1 leading-snug max-w-[130px]">{{ $stat['cap'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- Industry sectors -->
<section class="max-w-[1280px] mx-auto px-5 lg:px-8 pt-8 pb-4">
    <h2 class="text-center text-[15px] font-semibold tracking-[0.25em] text-[#1D1B16] uppercase">
        {{ $isFr ? 'Industry Sectors' : 'Industry Sectors' }}
    </h2>

    <div class="mt-8 flex flex-wrap justify-center gap-2">
        @foreach($sectorCards as [$scIcon, $scLabel, $scHref])
        <a href="{{ $scHref }}"
            class="relative w-[46%] sm:w-[140px] lg:w-[86px] bg-parch border border-sand rounded-xl shadow-[0_1px_3px_rgba(30,25,15,0.06)] pt-5 pb-4 px-1.5 text-center overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col items-center">
            <i data-lucide="{{ $scIcon }}" class="w-9 h-9 text-cocoa" stroke-width="1.5"></i>
            <p class="mt-2.5 text-[11px] font-medium text-[#1D1B16] leading-[1.35] whitespace-pre-line grow flex items-center justify-center">{{ $scLabel }}</p>
            <span class="absolute bottom-0 inset-x-0 flex h-[3px]">
                <span class="flex-1 bg-flagg"></span><span class="flex-1 bg-flagr"></span><span class="flex-1 bg-flagy"></span>
            </span>
        </a>
        @endforeach
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('industries.index', ['lang' => $lang]) }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-leaf hover:text-[#1B5E33]">
            {{ $isFr ? 'Voir tous les secteurs' : 'View all sectors' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>

<!-- Featured businesses -->
<section class="max-w-[1280px] mx-auto px-5 lg:px-8 pt-10 pb-4">
    <h2 class="text-center text-[15px] font-semibold tracking-[0.25em] text-[#1D1B16] uppercase">
        {{ $isFr ? 'Featured Businesses' : 'Featured Businesses' }}
    </h2>

    <div class="relative mt-8">
        <button id="biz-prev" aria-label="{{ $isFr ? 'Précédent' : 'Previous' }}"
            class="hidden lg:flex absolute -left-7 top-[45%] -translate-y-1/2 w-7 h-9 items-center justify-center text-[#8A857A] hover:text-[#1D1B16]">
            <i data-lucide="chevron-left" class="w-7 h-7"></i>
        </button>
        <button id="biz-next" aria-label="{{ $isFr ? 'Suivant' : 'Next' }}"
            class="hidden lg:flex absolute -right-7 top-[45%] -translate-y-1/2 w-7 h-9 items-center justify-center text-[#8A857A] hover:text-[#1D1B16]">
            <i data-lucide="chevron-right" class="w-7 h-7"></i>
        </button>

        <div id="biz-track" class="flex gap-4 overflow-x-auto no-scrollbar scroll-smooth snap-x">
            @forelse($featured as $business)
            <a href="{{ route('businesses.show', ['slug' => $business->slug, 'lang' => $lang]) }}"
                class="snap-start shrink-0 w-[188px] bg-white border border-sand rounded-xl shadow-[0_1px_3px_rgba(30,25,15,0.06)] p-1.5 hover:shadow-md transition-shadow group">
                <div class="h-[128px] rounded-lg overflow-hidden bg-sand/40">
                    <img src="{{ $business->cover_url ?? asset('images/landing/' . $bizFallbacks[$loop->index % 6]) }}"
                        alt="{{ $business->name_fr }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-300">
                </div>
                <div class="px-2.5 pt-3 pb-2.5">
                    <h3 class="text-[14px] font-semibold text-[#1D1B16] leading-snug line-clamp-1">
                        {{ $isFr ? $business->name_fr : ($business->name_en ?? $business->name_fr) }}
                    </h3>
                    <p class="mt-0.5 text-[11.5px] text-muted line-clamp-1">
                        {{ $isFr ? ($business->industry->name_fr ?? '') : ($business->industry->name_en ?? $business->industry->name_fr ?? '') }}
                    </p>
                    <p class="mt-2 flex items-center gap-1 text-[11.5px] text-[#6B665C]">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 shrink-0"></i>
                        <span class="line-clamp-1">{{ $business->region->name_fr ?? ($business->city->name_fr ?? 'Cameroun') }} {{ $business->region ? 'Region' : '' }}</span>
                    </p>
                </div>
            </a>
            @empty
            <p class="text-[13px] text-muted py-8 mx-auto">{{ $isFr ? 'Aucune entreprise en vedette pour le moment.' : 'No featured businesses yet.' }}</p>
            @endforelse
        </div>
    </div>

    <div class="mt-8 text-center">
        <a href="{{ route('businesses.index', ['lang' => $lang, 'featured' => 1]) }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-leaf hover:text-[#1B5E33]">
            {{ $isFr ? 'Voir toutes les entreprises' : 'View all businesses' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>

<!-- Event spotlight / current events / partners -->
<section class="max-w-[1280px] mx-auto px-5 lg:px-8 pt-10 pb-14">
    <div class="grid grid-cols-1 lg:grid-cols-[1.75fr_0.95fr_1.1fr] gap-5 items-stretch">

        <!-- Big event card -->
        @if($currentEvent)
        @php
            $evOngoing = now()->between($currentEvent->starts_at, $currentEvent->ends_at);
            $evYear = \Illuminate\Support\Carbon::parse($currentEvent->starts_at)->year;
            $evIndustry = $currentEvent->industry ? ($isFr ? $currentEvent->industry->name_fr : ($currentEvent->industry->name_en ?? $currentEvent->industry->name_fr)) : null;
        @endphp
        <div class="relative rounded-xl bg-gradient-to-br from-deepgr to-inkgr p-6 lg:p-7 flex flex-col overflow-hidden">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-[11px] font-semibold tracking-[0.18em] text-gold uppercase">
                            SIAC {{ $isFr ? 'Focus' : 'Focus' }}{{ $evIndustry ? ' — ' . mb_strtoupper(\Illuminate\Support\Str::before($evIndustry, ' &')) : '' }}
                        </span>
                        <span class="border border-white/25 rounded px-2.5 py-1 text-[9.5px] font-medium tracking-[0.12em] text-white/85 uppercase">
                            {{ $evOngoing ? ($isFr ? 'Événement actuel' : 'Current event') : ($isFr ? 'Événement à venir' : 'Upcoming event') }}
                        </span>
                    </div>

                    <h2 class="mt-5 font-serif text-[28px] text-white font-medium leading-none">SIAC {{ $evYear }}</h2>
                    <p class="mt-3 text-[14.5px] font-semibold text-white leading-snug">
                        {{ $isFr ? $currentEvent->name_fr : ($currentEvent->name_en ?? $currentEvent->name_fr) }}
                    </p>

                    <p class="mt-4 flex items-center gap-2 text-[13px] text-white/90">
                        <i data-lucide="calendar" class="w-4 h-4 text-goldic shrink-0"></i>
                        {{ $dateRange($currentEvent->starts_at, $currentEvent->ends_at) }}
                    </p>
                    <p class="mt-2 flex items-center gap-2 text-[13px] text-white/90">
                        <i data-lucide="map-pin" class="w-4 h-4 text-goldic shrink-0"></i>
                        <span class="line-clamp-1">{{ $isFr ? $currentEvent->location_fr : ($currentEvent->location_en ?? $currentEvent->location_fr) }}</span>
                    </p>

                    <p class="mt-4 text-[12.5px] text-white/70 leading-relaxed line-clamp-2">
                        {{ $isFr ? $currentEvent->description_fr : ($currentEvent->description_en ?? $currentEvent->description_fr) }}
                    </p>

                    <a href="{{ route('events.show', $currentEvent->slug) }}"
                        class="mt-5 inline-flex items-center gap-2.5 bg-leaf hover:bg-[#1B5E33] text-white text-[12.5px] font-medium px-5 py-2.5 rounded transition-colors self-start">
                        {{ $isFr ? 'Découvrir l\'événement' : 'Discover the event' }}
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                <img src="{{ $currentEvent->cover_url ?? asset('images/landing/event-aqua.png') }}" alt=""
                    class="hidden sm:block w-[150px] lg:w-[165px] self-stretch max-h-[250px] object-cover rounded-lg shrink-0">
            </div>
        </div>
        @else
        <div class="rounded-xl bg-gradient-to-br from-deepgr to-inkgr p-7 flex flex-col items-start justify-center">
            <span class="text-[11px] font-semibold tracking-[0.18em] text-gold uppercase">SIAC</span>
            <h2 class="mt-4 font-serif text-[28px] text-white font-medium">{{ $isFr ? 'Événements à venir' : 'Upcoming events' }}</h2>
            <p class="mt-3 text-[13px] text-white/70">{{ $isFr ? 'Les prochains événements seront annoncés bientôt.' : 'Upcoming events will be announced soon.' }}</p>
            <a href="{{ route('events.index') }}" class="mt-5 inline-flex items-center gap-2.5 bg-leaf hover:bg-[#1B5E33] text-white text-[12.5px] font-medium px-5 py-2.5 rounded transition-colors">
                {{ $isFr ? 'Voir les événements' : 'View events' }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        @endif

        <!-- Current events -->
        <div class="bg-[#FDFBF6] border border-sand rounded-xl p-5 flex flex-col">
            <h3 class="text-[13px] font-bold tracking-[0.15em] text-[#1D1B16] uppercase">{{ $isFr ? 'Current Events' : 'Current Events' }}</h3>

            <div class="mt-2 flex-1 divide-y divide-[#EEE8DB]">
                @forelse($upcomingEvents as $ev)
                <a href="{{ route('events.show', $ev->slug) }}" class="flex gap-3 py-3.5 group">
                    <img src="{{ $ev->cover_url ?? asset('images/landing/' . $eventFallbacks[$loop->index % 3]) }}" alt=""
                        class="w-14 h-14 rounded-md object-cover shrink-0">
                    <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-[#1D1B16] leading-snug line-clamp-2 group-hover:text-leaf transition-colors">
                            {{ $isFr ? $ev->name_fr : ($ev->name_en ?? $ev->name_fr) }}
                        </p>
                        <p class="mt-1 text-[11.5px] text-muted">{{ $dateRange($ev->starts_at, $ev->ends_at) }}</p>
                        <p class="text-[11.5px] text-muted line-clamp-1">{{ $isFr ? $ev->location_fr : ($ev->location_en ?? $ev->location_fr) }}</p>
                    </div>
                </a>
                @empty
                <p class="py-6 text-[12.5px] text-muted">{{ $isFr ? 'Aucun événement programmé pour le moment.' : 'No scheduled events at the moment.' }}</p>
                @endforelse
            </div>

            <a href="{{ route('events.index') }}" class="mt-3 pt-3.5 border-t border-[#EEE8DB] flex items-center justify-center gap-2 text-[13px] font-semibold text-leaf hover:text-[#1B5E33]">
                {{ $isFr ? 'Voir tous les événements' : 'View all events' }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        <!-- Partners -->
        <div class="bg-[#F3EDE1] rounded-xl p-5 flex flex-col">
            <h3 class="text-[13px] font-bold tracking-[0.15em] text-[#1D1B16] uppercase">{{ $isFr ? 'Our Partners' : 'Our Partners' }}</h3>

            <div class="mt-4 flex-1 grid grid-cols-4 gap-2.5 content-start">
                @forelse($partners as $partner)
                @php $tile = $partnerTiles[$partner->name_fr] ?? null; @endphp
                @if($tile)
                <div class="{{ $partner->name_fr === 'Union Européenne' ? 'col-span-2' : '' }} bg-white rounded-lg overflow-hidden flex items-center justify-center" title="{{ $isFr ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}">
                    <img src="{{ asset('images/landing/' . $tile) }}" alt="{{ $partner->name_fr }}" class="w-full h-auto">
                </div>
                @else
                <div class="bg-white rounded-lg flex flex-col items-center justify-center gap-1.5 py-3 px-1 min-h-[50px]" title="{{ $partner->name_fr }}">
                    @if($partner->logo_url ?? false)
                    <img src="{{ $partner->logo_url }}" alt="{{ $partner->name_fr }}" class="h-8 w-full object-contain px-1">
                    @else
                    <span class="w-7 h-7 rounded-full bg-leaf/10 text-leaf text-[10px] font-bold flex items-center justify-center">
                        {{ mb_strtoupper(mb_substr($partner->name_fr, 0, 2)) }}
                    </span>
                    @endif
                    <span class="text-[8.5px] text-muted text-center leading-tight w-full px-0.5 truncate">
                        {{ $isFr ? $partner->name_fr : ($partner->name_en ?? $partner->name_fr) }}
                    </span>
                </div>
                @endif
                @empty
                <p class="col-span-4 py-6 text-[12.5px] text-muted">{{ $isFr ? 'Nos partenaires seront annoncés bientôt.' : 'Our partners will be announced soon.' }}</p>
                @endforelse
            </div>

            <a href="{{ route('partners.index') }}" class="mt-3 pt-3.5 flex items-center justify-center gap-2 text-[13px] font-semibold text-leaf hover:text-[#1B5E33]">
                {{ $isFr ? 'Voir tous les partenaires' : 'View all partners' }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

</main>

<!-- Mission strip + footer (pattern frame) -->
<div class="relative bg-night">
    <div class="absolute left-0 top-0 bottom-0 w-12 bg-repeat-y hidden lg:block" style="background-image:url('{{ asset('images/landing/pattern-left.png') }}')"></div>
    <div class="absolute right-0 top-0 bottom-0 w-12 bg-repeat-y hidden lg:block" style="background-image:url('{{ asset('images/landing/pattern-right.png') }}')"></div>

    <div class="lg:mx-12 relative">
        <!-- Mission strip -->
        <div class="bg-pineur">
            <div class="max-w-[1200px] mx-auto px-5 lg:px-8 py-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
                @foreach($missionItems as [$mIcon, $mTitle, $mSub])
                <div class="flex items-center gap-3">
                    <i data-lucide="{{ $mIcon }}" class="w-8 h-8 text-goldic shrink-0" stroke-width="1.4"></i>
                    <div class="leading-tight">
                        <p class="text-[13px] font-semibold text-white">{{ $mTitle }}</p>
                        <p class="text-[11.5px] text-white/65 mt-0.5">{{ $mSub }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-night">
            <div class="max-w-[1200px] mx-auto px-5 lg:px-8 pt-10 pb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1.35fr_0.7fr_0.95fr_0.85fr_1.15fr] gap-7">
                    <!-- Brand -->
                    <div>
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-10 h-11 object-contain">
                            <span class="text-[11.5px] font-bold tracking-[0.08em] text-white uppercase leading-snug">
                                {{ $isFr ? 'Galerie Virtuelle' : 'National Virtual' }}<br>
                                {{ $isFr ? 'Nationale de l\'Artisanat' : 'Gallery of Crafts' }}<br>
                                {{ $isFr ? 'du Cameroun' : 'of Cameroon' }}
                            </span>
                        </div>
                        <p class="mt-4 text-[12px] text-[#9A978E] leading-relaxed max-w-[280px]">
                            {{ $isFr
                                ? 'Plateforme officielle de promotion et de valorisation de l\'artisanat camerounais. Notre mission est de préserver notre patrimoine, soutenir nos artisans et connecter le Cameroun au monde.'
                                : 'Official platform for the promotion of Cameroonian craftsmanship. Our mission is to preserve our heritage, support our artisans and connect Cameroon to the world.'
                            }}
                        </p>
                        @php
                            $socialIcons = [
                                'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
                                'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
                                'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
                                'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
                                'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
                            ];
                        @endphp
                        <div class="mt-5 flex items-center gap-2">
                            @foreach($socialIcons as $socialName => $socialPath)
                            <a href="#" aria-label="{{ $socialName }}" class="w-8 h-8 rounded-full border border-white/25 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                                <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">{!! $socialPath !!}</svg>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Explorer -->
                    <div>
                        <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">{{ $isFr ? 'Explorer' : 'Explore' }}</h4>
                        <ul class="space-y-2.5 text-[12.5px] text-[#9A978E] whitespace-nowrap">
                            <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Collections' : 'Collections' }}</a></li>
                            <li><a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Artisans' : 'Artisans' }}</a></li>
                            <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Régions' : 'Regions' }}</a></li>
                            <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Entreprises' : 'Businesses' }}</a></li>
                            <li><a href="{{ route('gallery.search', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Produits' : 'Products' }}</a></li>
                            <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Actualités' : 'News' }}</a></li>
                        </ul>
                    </div>

                    <!-- Ressources -->
                    <div>
                        <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">{{ $isFr ? 'Ressources' : 'Resources' }}</h4>
                        <ul class="space-y-2.5 text-[12.5px] text-[#9A978E] whitespace-nowrap">
                            <li><a href="{{ route('support.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Centre d\'aide' : 'Help center' }}</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Guide de l\'artisan' : 'Artisan guide' }}</a></li>
                            <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Formations' : 'Training' }}</a></li>
                            <li><a href="{{ route('partners.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Financements' : 'Funding' }}</a></li>
                            <li><a href="{{ url('/docs/api') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Documents utiles' : 'Useful documents' }}</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">FAQ</a></li>
                        </ul>
                    </div>

                    <!-- À propos -->
                    <div>
                        <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">{{ $isFr ? 'À propos' : 'About' }}</h4>
                        <ul class="space-y-2.5 text-[12.5px] text-[#9A978E] whitespace-nowrap">
                            <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Notre mission' : 'Our mission' }}</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Équipe' : 'Team' }}</a></li>
                            <li><a href="{{ route('partners.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Partenaires' : 'Partners' }}</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Carrières' : 'Careers' }}</a></li>
                            <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Presse' : 'Press' }}</a></li>
                            <li><a href="{{ route('support.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a></li>
                        </ul>
                    </div>

                    <!-- Newsletter -->
                    <div>
                        <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">{{ $isFr ? 'Restez informé' : 'Stay informed' }}</h4>
                        <p class="text-[12px] text-[#9A978E] leading-relaxed">
                            {{ $isFr
                                ? 'Inscrivez-vous à notre newsletter pour recevoir nos actualités et événements.'
                                : 'Subscribe to our newsletter to receive our news and events.'
                            }}
                        </p>
                        <form action="/inscription" method="GET" class="mt-4 flex gap-2">
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            <input name="email" type="email" required placeholder="{{ $isFr ? 'Votre email' : 'Your email' }}"
                                class="flex-1 min-w-0 bg-[#121412] border border-[#2E2F2C] rounded-md px-3 py-2.5 text-[12px] text-white placeholder-[#6F6D66] focus:outline-none focus:border-leaf">
                            <button type="submit" class="bg-leaf hover:bg-[#1B5E33] text-white text-[12px] font-semibold px-4 py-2.5 rounded-md transition-colors shrink-0">
                                {{ $isFr ? 'S\'inscrire' : 'Subscribe' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Legal bar -->
                <div class="mt-9 pt-5 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-[#7C7A72]">
                    <span>&copy; {{ date('Y') }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</span>
                    <span class="flex items-center gap-3 whitespace-nowrap">
                        <a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Mentions légales' : 'Legal notice' }}</a>
                        <span class="text-white/20">|</span>
                        <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Politique de confidentialité' : 'Privacy policy' }}</a>
                        <span class="text-white/20">|</span>
                        <a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Conditions d\'utilisation' : 'Terms of use' }}</a>
                    </span>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- Mobile bottom navigation -->
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @php
        $bottomTabs = [
            ['href' => route('home', ['lang' => $lang]), 'icon' => 'home', 'label' => $isFr ? 'Accueil' : 'Home', 'active' => true],
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

    // Mobile menu
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    // Hero carousel
    const slides = @json($heroSlides);
    let heroIdx = 0;
    let heroTimer = null;
    const slideEl = document.getElementById('hero-slide');
    const dots = Array.from(document.querySelectorAll('.hero-dot'));

    function showSlide(i) {
        heroIdx = (i + slides.length) % slides.length;
        slideEl.style.opacity = 0;
        setTimeout(() => {
            document.getElementById('hero-l1').textContent = slides[heroIdx].l1;
            document.getElementById('hero-l2').textContent = slides[heroIdx].l2;
            document.getElementById('hero-gold').textContent = slides[heroIdx].gold;
            document.getElementById('hero-sub').textContent = slides[heroIdx].sub;
            slideEl.style.opacity = 1;
        }, 300);
        dots.forEach((d, di) => {
            d.classList.toggle('text-white', di === heroIdx);
            d.classList.toggle('border-gold', di === heroIdx);
            d.classList.toggle('text-white/55', di !== heroIdx);
            d.classList.toggle('border-transparent', di !== heroIdx);
        });
    }

    function resetTimer() {
        clearInterval(heroTimer);
        heroTimer = setInterval(() => showSlide(heroIdx + 1), 8000);
    }

    document.getElementById('hero-prev').addEventListener('click', () => { showSlide(heroIdx - 1); resetTimer(); });
    document.getElementById('hero-next').addEventListener('click', () => { showSlide(heroIdx + 1); resetTimer(); });
    dots.forEach(d => d.addEventListener('click', () => { showSlide(parseInt(d.dataset.slide)); resetTimer(); }));
    resetTimer();

    // Featured businesses scroll
    const track = document.getElementById('biz-track');
    const bizPrev = document.getElementById('biz-prev');
    const bizNext = document.getElementById('biz-next');
    if (track && bizPrev && bizNext) {
        bizPrev.addEventListener('click', () => track.scrollBy({ left: -440, behavior: 'smooth' }));
        bizNext.addEventListener('click', () => track.scrollBy({ left: 440, behavior: 'smooth' }));
    }
</script>
</body>
</html>
