@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $navLinks = [
        [$isFr ? 'Explorer' : 'Explore',        route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Collections' : 'Collections', route('industries.index', ['lang' => $lang]), false],
        [$isFr ? 'Artisans' : 'Artisans',       route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']), false],
        [$isFr ? 'Régions' : 'Regions',         route('businesses.index', ['lang' => $lang]), false],
        [$isFr ? 'Secteurs' : 'Sectors',        route('industries.index', ['lang' => $lang]), false],
        [$isFr ? 'Événements' : 'Events',       route('events.index'), true],
        [$isFr ? 'À propos' : 'About',          route('about'), false],
    ];

    // Filled dark-green circles in the "Suivez-nous" column (design order: FB, IG, LinkedIn, X)
    $followIcons = [
        'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
        'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
        'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
        'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
    ];

    // Outline circles in the footer (design order: FB, IG, LinkedIn, YouTube, X)
    $footerIcons = $followIcons;
    $footerIcons = [
        'Facebook'  => $followIcons['Facebook'],
        'Instagram' => $followIcons['Instagram'],
        'LinkedIn'  => $followIcons['LinkedIn'],
        'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
        'X'         => $followIcons['X'],
    ];

    $infoCards = [
        ['icon' => 'contact-info-1.png', 'title' => $isFr ? 'Adresse' : 'Address',
         'lines' => $isFr ? ['Ministère des Arts et de la Culture', 'Immeuble de la Culture,', 'Yaoundé, Cameroun']
                          : ['Ministry of Arts and Culture', 'Immeuble de la Culture,', 'Yaoundé, Cameroon']],
        ['icon' => 'contact-info-2.png', 'title' => $isFr ? 'Téléphone' : 'Phone',
         'lines' => ['+237 222 22 22 22', '+237 677 00 11 22']],
        ['icon' => 'contact-info-3.png', 'title' => 'Email',
         'lines' => ['contact@gvnac.cm', 'info@gvnac.cm']],
        ['icon' => 'contact-info-4.png', 'title' => $isFr ? "Heures d'ouverture" : 'Opening hours',
         'lines' => $isFr ? ['Lundi - Vendredi : 8h00 - 17h00', 'Samedi : 9h00 - 13h00']
                          : ['Monday - Friday: 8am - 5pm', 'Saturday: 9am - 1pm']],
    ];

    $helpCards = [
        ['contact-help-1.png', $isFr ? 'Partenariats' : 'Partnerships',
         $isFr ? "Collaborations, sponsoring\net projets communs" : "Collaborations, sponsoring\nand joint projects"],
        ['contact-help-2.png', $isFr ? 'Exposer vos produits' : 'Showcase your products',
         $isFr ? "Devenez artisan ou entreprise\nréférencée sur notre plateforme" : "Become an artisan or business\nlisted on our platform"],
        ['contact-help-3.png', $isFr ? 'Assistance' : 'Assistance',
         $isFr ? "Besoin d'aide ? Notre équipe\nvous accompagne" : "Need help? Our team\nis here for you"],
        ['contact-help-4.png', $isFr ? 'Presse et médias' : 'Press and media',
         $isFr ? "Demandes d'interview,\ncommuniqués et ressources" : "Interview requests,\npress releases and resources"],
        ['contact-help-5.png', $isFr ? 'Autres demandes' : 'Other requests',
         $isFr ? "Toute autre demande\nou suggestion" : "Any other request\nor suggestion"],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Contactez la Galerie Virtuelle Nationale de l\'Artisanat du Cameroun — une question, une suggestion ou un partenariat ? Notre équipe est à votre écoute.' : 'Contact the National Virtual Gallery of Cameroonian Crafts — a question, a suggestion or a partnership? Our team is here to listen.' }}">
    <title>{{ $isFr ? 'Nous contacter — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Contact us — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream:  '#FAF7F2',
                        sand:   '#E7E1D4',
                        leaf:   '#164C28',
                        pinefc: '#0A2415',
                        night:  '#131110',
                        gold:   '#E5A82E',
                        goldbt: '#E9AC33',
                        sage:   '#A8B8AC',
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
        html, body { overflow-x: clip; }
    </style>
</head>
<body class="bg-cream text-[#1D1B16] antialiased">

<!-- Tricolor top bar -->
<div class="relative flex h-[18px] overflow-hidden">
    <div class="w-[41%] bg-[#015D38]"></div>
    <div class="relative w-[18.6%] bg-[#C10B1B]">
        <svg viewBox="0 0 24 24" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 fill-[#F2AE09]" aria-hidden="true">
            <path d="M12 1.5 14.7 8.6l7.6.3-6 4.7 2.1 7.3L12 16.6 5.6 20.9l2.1-7.3-6-4.7 7.6-.3z"/>
        </svg>
    </div>
    <div class="flex-1 bg-[#EBAC23]"></div>
</div>

<!-- Header -->
<header class="bg-[#FBF8F5]">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8">
        <div class="flex items-center justify-between gap-4 py-4">
            <!-- Brand -->
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
                <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-11 h-12 xl:w-12 xl:h-13 object-contain">
                <span class="leading-tight">
                    <span class="block text-[12px] xl:text-[13px] font-bold tracking-[0.06em] text-[#262521] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                    <span class="block text-[12px] xl:text-[13px] font-bold tracking-[0.06em] text-[#262521] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                    <span class="block text-[10px] xl:text-[10.5px] text-muted mt-0.5 whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
                </span>
            </a>

            <!-- Nav -->
            <nav class="hidden lg:flex items-center gap-5 xl:gap-[34px]">
                @foreach($navLinks as [$navLabel, $navHref, $navUnderline])
                <a href="{{ $navHref }}" class="relative whitespace-nowrap text-[13px] xl:text-[14px] font-medium text-[#262521] hover:text-leaf transition-colors pb-0.5">
                    {{ $navLabel }}
                    @if($navUnderline)<span class="absolute left-0 right-0 -bottom-[7px] h-[2px] bg-[#E8C98F]"></span>@endif
                </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3 xl:gap-5 shrink-0">
                <!-- Search -->
                <a href="{{ route('gallery.search', ['lang' => $lang]) }}" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}"
                    class="hidden lg:inline-flex text-[#262521] hover:text-leaf transition-colors">
                    <i data-lucide="search" class="w-[19px] h-[19px]"></i>
                </a>

                <!-- Language -->
                <div class="relative group hidden sm:block">
                    <button class="flex items-center gap-1 px-1 py-2 text-[13.5px] font-semibold text-[#262521]">
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
                <a href="/tableau-de-bord" class="hidden sm:inline-flex items-center whitespace-nowrap bg-[#0A3020] hover:bg-leaf text-white text-[13px] font-semibold px-5 h-[42px] rounded-lg transition-colors">
                    {{ $isFr ? 'Tableau de bord' : 'Dashboard' }}
                </a>
                @else
                <a href="/login?lang={{ $lang }}" class="hidden sm:inline-flex items-center whitespace-nowrap bg-[#0A3020] hover:bg-leaf text-white text-[13px] font-semibold px-5 h-[42px] rounded-lg transition-colors">
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
            @foreach($navLinks as [$navLabel, $navHref, $navUnderline])
            <a href="{{ $navHref }}" class="block px-2 py-2 text-[14px] font-medium text-[#262521] hover:bg-sand/40 rounded">{{ $navLabel }}</a>
            @endforeach
            <div class="border-t border-sand mt-2 pt-2 flex items-center justify-between px-2">
                @if($siacUser)
                <a href="/tableau-de-bord" class="inline-flex items-center bg-[#0A3020] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Tableau de bord' : 'Dashboard' }}</a>
                @else
                <a href="/login?lang={{ $lang }}" class="inline-flex items-center bg-[#0A3020] text-white text-[13px] font-medium px-4 py-2 rounded-lg">{{ $isFr ? 'Connexion' : 'Sign in' }}</a>
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
<section class="relative bg-[#0C1C11] overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-[center_right]" style="background-image:url('{{ asset('images/landing/contact-hero.png') }}')"></div>
    <div class="absolute inset-0 bg-black/40 sm:bg-transparent"></div>
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-9 lg:py-11 min-h-[300px] lg:min-h-[364px]">
        <nav class="flex items-center gap-2 text-[13px]" aria-label="Breadcrumb">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#D9A439] hover:text-gold transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-white/60"></i>
            <span class="text-white/85">Contact</span>
        </nav>
        <h1 class="mt-7 font-serif text-[42px] sm:text-[52px] lg:text-[58px] leading-[1.05] text-white font-medium">
            {{ $isFr ? 'Nous contacter' : 'Contact us' }}
        </h1>
        <div class="mt-5 h-[3px] w-[88px] bg-[#D9991F]"></div>
        <p class="mt-6 text-[15px] lg:text-[16.5px] text-white/90 leading-[1.9]">
            {{ $isFr ? 'Une question, une suggestion ou un partenariat ?' : 'A question, a suggestion or a partnership?' }}<br>
            {{ $isFr ? 'Notre équipe est à votre écoute.' : 'Our team is here to listen.' }}
        </p>
        <img src="{{ asset('images/landing/contact-ornament.png') }}" alt="" class="mt-8 h-[34px] w-auto" aria-hidden="true">
    </div>
</section>

<!-- Contact info strip -->
<section class="bg-cream">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8 py-11">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.05fr_1fr_0.95fr_1.25fr_1fr] gap-y-10 lg:divide-x lg:divide-[#E9E4DB]">
            @foreach($infoCards as $card)
            <div class="flex flex-col items-center text-center px-3">
                <img src="{{ asset('images/landing/' . $card['icon']) }}" alt="" class="h-[38px] w-auto object-contain">
                <h3 class="mt-4 text-[16.5px] font-semibold text-[#20211D]">{{ $card['title'] }}</h3>
                <div class="mt-3 space-y-1.5 text-[12.5px] text-[#4A4840] leading-relaxed">
                    @foreach($card['lines'] as $line)
                    <p>{{ $line }}</p>
                    @endforeach
                </div>
                <span class="mt-auto pt-5 block"><span class="block w-[46px] h-[2.5px] bg-[#E0980F]"></span></span>
            </div>
            @endforeach
            <!-- Suivez-nous -->
            <div class="flex flex-col items-center text-center px-3">
                <img src="{{ asset('images/landing/contact-info-5.png') }}" alt="" class="h-[38px] w-auto object-contain">
                <h3 class="mt-4 text-[16.5px] font-semibold text-[#20211D]">{{ $isFr ? 'Suivez-nous' : 'Follow us' }}</h3>
                <div class="mt-5 flex items-center gap-3">
                    @foreach($followIcons as $socialName => $socialPath)
                    <a href="#" aria-label="{{ $socialName }}" class="w-[38px] h-[38px] rounded-full bg-[#14472B] hover:bg-leaf flex items-center justify-center text-white transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">{!! $socialPath !!}</svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Form + map -->
<section class="bg-cream">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-[0.8fr_1fr] gap-5 items-stretch">
            <!-- Form card -->
            <div class="bg-[#FCF9F5] border border-[#ECE7DF] rounded-xl p-6 sm:p-9">
                <h2 class="font-serif text-[26px] sm:text-[30px] text-[#20211D] font-medium">{{ $isFr ? 'Envoyez-nous un message' : 'Send us a message' }}</h2>
                <div class="mt-3.5 h-[3px] w-[70px] bg-[#E0980F]"></div>

                @if(session('success'))
                <div class="mt-6 bg-[#EAF3EC] border border-[#BFDCC7] text-leaf text-[13px] rounded-lg px-4 py-3">
                    {{ session('success') }}
                </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}" class="mt-7">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="contact-name" class="sr-only">{{ $isFr ? 'Votre nom' : 'Your name' }}</label>
                            <input id="contact-name" name="name" type="text" required value="{{ old('name') }}" placeholder="{{ $isFr ? 'Votre nom *' : 'Your name *' }}"
                                class="w-full h-[54px] bg-[#FCF9F5] border border-[#E0DCD5] rounded-lg px-4 text-[13.5px] text-[#262521] placeholder-[#8A857A] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/40 transition">
                            @error('name')<p class="mt-1.5 text-[12px] text-[#B42025]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="contact-email" class="sr-only">{{ $isFr ? 'Votre email' : 'Your email' }}</label>
                            <input id="contact-email" name="email" type="email" required value="{{ old('email') }}" placeholder="{{ $isFr ? 'Votre email *' : 'Your email *' }}"
                                class="w-full h-[54px] bg-[#FCF9F5] border border-[#E0DCD5] rounded-lg px-4 text-[13.5px] text-[#262521] placeholder-[#8A857A] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/40 transition">
                            @error('email')<p class="mt-1.5 text-[12px] text-[#B42025]">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="contact-subject" class="sr-only">{{ $isFr ? 'Sujet' : 'Subject' }}</label>
                        <input id="contact-subject" name="subject" type="text" required value="{{ old('subject') }}" placeholder="{{ $isFr ? 'Sujet *' : 'Subject *' }}"
                            class="w-full h-[54px] bg-[#FCF9F5] border border-[#E0DCD5] rounded-lg px-4 text-[13.5px] text-[#262521] placeholder-[#8A857A] focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/40 transition">
                        @error('subject')<p class="mt-1.5 text-[12px] text-[#B42025]">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-4">
                        <label for="contact-message" class="sr-only">{{ $isFr ? 'Votre message' : 'Your message' }}</label>
                        <textarea id="contact-message" name="message" required rows="7" placeholder="{{ $isFr ? 'Votre message *' : 'Your message *' }}"
                            class="w-full bg-[#FCF9F5] border border-[#E0DCD5] rounded-lg px-4 py-3.5 text-[13.5px] text-[#262521] placeholder-[#8A857A] leading-relaxed resize-y focus:outline-none focus:border-gold focus:ring-1 focus:ring-gold/40 transition">{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1.5 text-[12px] text-[#B42025]">{{ $message }}</p>@enderror
                    </div>
                    <label class="mt-4 flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="consent" value="1" required {{ old('consent') ? 'checked' : '' }}
                            class="mt-0.5 w-[18px] h-[18px] rounded border-[#CFC9BF] text-leaf focus:ring-gold/50">
                        <span class="text-[13px] text-[#33322D]">{{ $isFr ? 'J\'accepte d\'être contacté(e) par l\'équipe de la GVNAC.' : 'I agree to be contacted by the GVNAC team.' }}</span>
                    </label>
                    @error('consent')<p class="mt-1.5 text-[12px] text-[#B42025]">{{ $message }}</p>@enderror
                    <button type="submit"
                        class="mt-6 inline-flex items-center gap-3 bg-[#123D27] hover:bg-leaf text-white text-[14px] font-semibold px-7 h-[54px] rounded-lg transition-colors">
                        <i data-lucide="send" class="w-[18px] h-[18px] text-[#E0A52F]"></i>
                        {{ $isFr ? 'Envoyer le message' : 'Send the message' }}
                    </button>
                </form>
            </div>

            <!-- Map (location card is part of the artwork; a real link covers its button) -->
            <div class="relative rounded-xl overflow-hidden border border-[#ECE7DF] min-h-[320px]">
                <img src="{{ asset('images/landing/contact-map.png') }}" alt="{{ $isFr ? 'Carte — Ministère des Arts et de la Culture, Yaoundé' : 'Map — Ministry of Arts and Culture, Yaoundé' }}"
                    class="absolute inset-0 w-full h-full object-cover">
                <a href="https://www.google.com/maps/dir/?api=1&destination=Minist%C3%A8re+des+Arts+et+de+la+Culture%2C+Yaound%C3%A9%2C+Cameroun"
                    target="_blank" rel="noopener"
                    aria-label="{{ $isFr ? 'Itinéraire vers le Ministère des Arts et de la Culture' : 'Directions to the Ministry of Arts and Culture' }}"
                    class="absolute left-[58.6%] top-[51.1%] w-[16%] h-[8.5%] rounded-lg"></a>
            </div>
        </div>
    </div>
</section>

<!-- How can we help -->
<section class="relative bg-[#F6F1EB] overflow-hidden">
    <img src="{{ asset('images/landing/contact-help-left.png') }}" alt="" class="absolute left-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/contact-help-right.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-12">
        <h2 class="font-serif text-[27px] sm:text-[31px] text-[#20211D] font-medium text-center">{{ $isFr ? 'Comment pouvons-nous vous aider ?' : 'How can we help you?' }}</h2>
        <div class="mt-4 h-[2.5px] w-[62px] bg-[#D9991F] mx-auto"></div>
        <div class="mt-11 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-y-9 lg:divide-x lg:divide-[#E5DFD3]">
            @foreach($helpCards as [$helpIcon, $helpTitle, $helpSub])
            <div class="flex flex-col items-center text-center px-4">
                <img src="{{ asset('images/landing/' . $helpIcon) }}" alt="" class="h-[46px] w-auto object-contain">
                <h3 class="mt-5 text-[15.5px] font-semibold text-[#20211D]">{{ $helpTitle }}</h3>
                <p class="mt-2.5 text-[12.5px] text-[#5D5A51] leading-relaxed whitespace-pre-line">{{ $helpSub }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA band -->
<section class="relative bg-pinefc overflow-hidden">
    <img src="{{ asset('images/landing/contact-cta-left.png') }}" alt="" class="absolute left-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/contact-cta-right.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto hidden md:block pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-9 text-center">
        <p class="font-serif text-[22px] sm:text-[26px] text-[#F4F1E8] font-medium">
            {{ $isFr ? 'Restons connectés pour valoriser ensemble l\'artisanat camerounais.' : 'Let\'s stay connected to promote Cameroonian craftsmanship together.' }}
        </p>
        <a href="{{ route('industries.index', ['lang' => $lang]) }}"
            class="mt-6 inline-flex items-center gap-2.5 bg-goldbt hover:bg-[#D99B22] text-[#3A2E08] text-[13.5px] font-semibold px-6 h-[46px] rounded-lg transition-colors">
            {{ $isFr ? 'Découvrir nos collections' : 'Discover our collections' }}
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>

<!-- Kente strip -->
<img src="{{ asset('images/landing/contact-kente.png') }}" alt="" class="block w-full h-[30px] object-cover" aria-hidden="true">

</main>

<!-- Footer -->
<footer class="bg-night" style="background-image:url('{{ asset('images/landing/contact-footer-tile.png') }}')">
    <div class="max-w-[1280px] mx-auto px-5 lg:px-8 pt-11 pb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1.35fr_0.75fr_0.95fr_0.8fr_1.25fr] gap-7">
            <!-- Brand -->
            <div>
                <div class="flex items-center gap-3.5">
                    <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[52px] h-[56px] object-contain">
                    <span class="text-[12px] font-bold tracking-[0.08em] text-white uppercase leading-snug">
                        {{ $isFr ? 'Galerie Virtuelle' : 'National Virtual' }}<br>
                        {{ $isFr ? 'Nationale de l\'Artisanat' : 'Gallery of the Crafts' }}<br>
                        {{ $isFr ? 'du Cameroun' : 'of Cameroon' }}
                    </span>
                </div>
                <p class="mt-4 text-[12px] text-[#9A978E] leading-relaxed max-w-[280px]">
                    {{ $isFr
                        ? 'Plateforme officielle de promotion et de valorisation de l\'artisanat camerounais. Notre mission est de préserver notre patrimoine, soutenir nos artisans et connecter le Cameroun au monde.'
                        : 'Official platform for the promotion of Cameroonian craftsmanship. Our mission is to preserve our heritage, support our artisans and connect Cameroon to the world.'
                    }}
                </p>
                <div class="mt-5 flex items-center gap-2.5">
                    @foreach($footerIcons as $socialName => $socialPath)
                    <a href="#" aria-label="{{ $socialName }}" class="w-9 h-9 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">{!! $socialPath !!}</svg>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Explorer -->
            <div>
                <h4 class="text-[12.5px] font-bold tracking-[0.12em] text-white uppercase mb-4">{{ $isFr ? 'Explorer' : 'Explore' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-[#9A978E] whitespace-nowrap">
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">Collections</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']) }}" class="hover:text-white transition-colors">Artisans</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Régions' : 'Regions' }}</a></li>
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Secteurs' : 'Sectors' }}</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Entreprises' : 'Businesses' }}</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Actualités' : 'News' }}</a></li>
                </ul>
            </div>

            <!-- Ressources -->
            <div>
                <h4 class="text-[12.5px] font-bold tracking-[0.12em] text-white uppercase mb-4">{{ $isFr ? 'Ressources' : 'Resources' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-[#9A978E] whitespace-nowrap">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Guide de l\'artisan' : 'Artisan guide' }}</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Formations' : 'Training' }}</a></li>
                    <li><a href="{{ route('partners.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Financements' : 'Funding' }}</a></li>
                    <li><a href="{{ url('/docs/api') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Documents utiles' : 'Useful documents' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">FAQ</a></li>
                </ul>
            </div>

            <!-- À propos -->
            <div>
                <h4 class="text-[12.5px] font-bold tracking-[0.12em] text-white uppercase mb-4">{{ $isFr ? 'À propos' : 'About' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-[#9A978E] whitespace-nowrap">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Notre mission' : 'Our mission' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Équipe' : 'Team' }}</a></li>
                    <li><a href="{{ route('partners.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Partenaires' : 'Partners' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Carrières' : 'Careers' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Presse' : 'Press' }}</a></li>
                </ul>
            </div>

            <!-- Restez informé -->
            <div>
                <h4 class="text-[12.5px] font-bold tracking-[0.12em] text-white uppercase mb-4">{{ $isFr ? 'Restez informé' : 'Stay informed' }}</h4>
                <p class="text-[12px] text-[#9A978E] leading-relaxed">
                    {{ $isFr
                        ? 'Inscrivez-vous à notre newsletter pour recevoir nos actualités et événements.'
                        : 'Subscribe to our newsletter to receive our news and events.'
                    }}
                </p>
                <form action="/inscription" method="GET" class="mt-4 flex gap-2.5">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="email" type="email" required placeholder="{{ $isFr ? 'Votre email' : 'Your email' }}"
                        class="flex-1 min-w-0 bg-[#303230]/60 border border-[#4A4B45] rounded-md px-3.5 py-3 text-[12.5px] text-white placeholder-[#8F8D85] focus:outline-none focus:border-leaf">
                    <button type="submit" class="bg-[#1B5B3C] hover:bg-leaf text-white text-[12.5px] font-semibold px-4 py-3 rounded-md transition-colors shrink-0">
                        {{ $isFr ? 'S\'inscrire' : 'Subscribe' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Legal bar -->
        <div class="mt-9 pt-5 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11.5px] text-[#7C7A72]">
            <span>&copy; 2025 {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</span>
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
