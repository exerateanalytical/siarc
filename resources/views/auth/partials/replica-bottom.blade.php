{{-- Shared bottom of the auth replica pages: "Pourquoi rejoindre" band + footer + mobile nav.
     Expects: $isFr, $lang, $socialIcons, $siacUser --}}

@php
    $whyItems = $isFr ? [
        ['globe',       "Visibilité nationale\net internationale", "Faites découvrir votre savoir-\nfaire au monde entier."],
        ['shield-check', "Transactions\nsécurisées",               "Achetez et vendez en toute\nconfiance sur notre plateforme\nsécurisée."],
        ['users',       "Communauté\nengagée",                     "Connectez-vous avec des milliers\nd'artisans, acheteurs et partenaires."],
        ['trending-up', "Croissance et\ndéveloppement",            "Accédez à des outils pour\ndévelopper votre activité\net vos opportunités."],
        ['headset',     "Support\ndédié",                          "Notre équipe est là pour vous\naccompagner à chaque étape."],
    ] : [
        ['globe',       "National and\ninternational visibility",  "Show your know-how\nto the whole world."],
        ['shield-check', "Secure\ntransactions",                   "Buy and sell with confidence\non our secure platform."],
        ['users',       "Engaged\ncommunity",                      "Connect with thousands of\nartisans, buyers and partners."],
        ['trending-up', "Growth and\ndevelopment",                 "Access tools to grow\nyour business and\nyour opportunities."],
        ['headset',     "Dedicated\nsupport",                      "Our team is here to support\nyou every step of the way."],
    ];
@endphp

<!-- Pourquoi rejoindre -->
<section class="relative bg-[#F2EDE7] mt-4 overflow-hidden">
    <img src="{{ asset('images/landing/auth-band-motif-left.png') }}" alt="" class="absolute left-0 bottom-0 w-[140px] pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/auth-band-motif-right.png') }}" alt="" class="absolute right-0 bottom-0 w-[140px] pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 pt-8 pb-10">
        <h2 class="text-center font-serif text-[24px] sm:text-[27px] text-[#1D1B16]">
            {{ $isFr ? 'Pourquoi rejoindre la Galerie Virtuelle ?' : 'Why join the Virtual Gallery?' }}
        </h2>
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-y-8 lg:divide-x divide-[#DDD6CB]">
            @foreach($whyItems as [$whyIcon, $whyTitle, $whyDesc])
            <div class="flex items-start gap-3.5 px-5">
                <i data-lucide="{{ $whyIcon }}" class="w-10 h-10 shrink-0 text-[#B07E23]" style="stroke-width:1.4"></i>
                <div>
                    <h3 class="text-[13.5px] font-semibold text-[#1D1B16] leading-snug whitespace-pre-line">{{ $whyTitle }}</h3>
                    <p class="mt-2 text-[11.5px] text-[#7A756A] leading-relaxed whitespace-pre-line">{{ $whyDesc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="relative bg-[#0B2014] overflow-hidden">
    <img src="{{ asset('images/landing/auth-footer-motif.png') }}" alt="" class="absolute right-0 top-0 hidden xl:block pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 pt-10 pb-9">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1.35fr_0.7fr_0.95fr_0.85fr_1.15fr_auto] gap-7">
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
                <p class="mt-4 text-[12px] text-sage leading-relaxed">
                    {{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}
                </p>
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
                <ul class="space-y-2.5 text-[12.5px] text-sage whitespace-nowrap">
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Collections' : 'Collections' }}</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Artisans' : 'Artisans' }}</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Régions' : 'Regions' }}</a></li>
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Secteurs' : 'Sectors' }}</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Événements' : 'Events' }}</a></li>
                </ul>
            </div>

            <!-- Ressources -->
            <div>
                <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">{{ $isFr ? 'Ressources' : 'Resources' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-sage whitespace-nowrap">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Guide de l\'artisan' : 'Artisan guide' }}</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                    <li><a href="{{ route('support.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Centre d\'aide' : 'Help center' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Conditions d\'utilisation' : 'Terms of use' }}</a></li>
                </ul>
            </div>

            <!-- À propos -->
            <div>
                <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">{{ $isFr ? 'À propos' : 'About' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-sage whitespace-nowrap">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Notre mission' : 'Our mission' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Équipe' : 'Team' }}</a></li>
                    <li><a href="{{ route('partners.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Partenaires' : 'Partners' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Carrières' : 'Careers' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Presse' : 'Press' }}</a></li>
                    <li><a href="{{ route('contact', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div>
                <h4 class="text-[12px] font-bold tracking-[0.15em] text-white uppercase mb-4">Newsletter</h4>
                <p class="text-[12px] text-sage leading-relaxed">
                    {{ $isFr ? "Restez informé de nos actualités\net opportunités." : "Stay informed of our news\nand opportunities." }}
                </p>
                <form action="/inscription" method="GET" class="mt-4 flex gap-2">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="email" type="email" required placeholder="{{ $isFr ? 'Votre email' : 'Your email' }}"
                        class="flex-1 min-w-0 w-[150px] bg-transparent border border-white/25 rounded-md px-3 py-2.5 text-[12px] text-white placeholder-sage/70 focus:outline-none focus:border-gold">
                    <button type="submit" class="bg-[#E9A830] hover:bg-goldbt text-[#3A2E08] text-[12.5px] font-semibold px-4 py-2.5 rounded-md transition-colors shrink-0">
                        {{ $isFr ? 'S\'abonner' : 'Subscribe' }}
                    </button>
                </form>
            </div>

            <!-- Cameroon map -->
            <div class="hidden lg:flex items-center">
                <img src="{{ asset('images/landing/auth-footer-map.png') }}" alt="" class="w-[112px]" aria-hidden="true">
            </div>
        </div>
    </div>

    <!-- Ornament band -->
    <div class="relative h-[26px] bg-repeat-x" style="background-image:url('{{ asset('images/landing/auth-footer-band.png') }}')"></div>

    <!-- Legal bar -->
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11.5px] text-sage/80">
        <span>&copy; {{ date('Y') }} {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</span>
        <span class="flex items-center gap-3 whitespace-nowrap">
            <a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Mentions légales' : 'Legal notice' }}</a>
            <span class="text-white/20">|</span>
            <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Politique de confidentialité' : 'Privacy policy' }}</a>
        </span>
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
