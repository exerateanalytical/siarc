{{-- Directory replica footer (product/vendor directory design): deep green with kente side
     borders, white social circles, Cameroon map with caption, 2-link legal bar.
     Expects: $lang, $isFr, $siacUser --}}

@php
    $dfSocialIcons = [
        'Facebook'  => '<path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/>',
        'Instagram' => '<rect x="2.5" y="2.5" width="15" height="15" rx="4.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="10" cy="10" r="3.4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14.6" cy="5.4" r="1"/>',
        'LinkedIn'  => '<path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/>',
        'YouTube'   => '<path d="M18.2 6.3a2.1 2.1 0 0 0-1.5-1.5C15.4 4.4 10 4.4 10 4.4s-5.4 0-6.7.4A2.1 2.1 0 0 0 1.8 6.3 22 22 0 0 0 1.5 10a22 22 0 0 0 .3 3.7 2.1 2.1 0 0 0 1.5 1.5c1.3.4 6.7.4 6.7.4s5.4 0 6.7-.4a2.1 2.1 0 0 0 1.5-1.5A22 22 0 0 0 18.5 10a22 22 0 0 0-.3-3.7zM8.3 12.5v-5l4.4 2.5z"/>',
        'X'         => '<path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/>',
    ];
@endphp

<footer class="relative bg-[#012B1C] overflow-hidden">
    <img src="{{ asset('images/landing/product-kente-left.png') }}" alt="" class="absolute inset-y-0 left-0 w-[64px] h-full object-cover hidden md:block pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/product-kente-right.png') }}" alt="" class="absolute inset-y-0 right-0 w-[64px] h-full object-cover hidden md:block pointer-events-none select-none" aria-hidden="true">

    <div class="relative max-w-[1340px] mx-auto px-5 lg:px-8 pt-8 pb-5">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1.3fr_0.7fr_0.95fr_0.7fr_1.05fr_1.1fr] gap-7 items-start">
            <!-- Brand -->
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-10 h-11 object-contain">
                    <span class="text-[11.5px] font-bold tracking-[0.08em] text-white uppercase leading-snug">
                        {{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}<br>
                        {{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}
                    </span>
                </div>
                <p class="mt-2 text-[11px] text-[#9DB3A6]">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</p>
                <div class="mt-5 flex items-center gap-2.5">
                    @foreach($dfSocialIcons as $socialName => $socialPath)
                    <a href="#" aria-label="{{ $socialName }}" class="w-[34px] h-[34px] rounded-full bg-white flex items-center justify-center text-[#0B2C1E] hover:bg-[#EBD8A9] transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">{!! $socialPath !!}</svg>
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Explorer -->
            <div>
                <h4 class="text-[12px] font-bold tracking-[0.14em] text-white uppercase mb-4">{{ $isFr ? 'Explorer' : 'Explore' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-[#B9C4BC] whitespace-nowrap">
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">Collections</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang, 'industry' => 'artisanat']) }}" class="hover:text-white transition-colors">Artisans</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Régions' : 'Regions' }}</a></li>
                    <li><a href="{{ route('industries.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Catégories' : 'Categories' }}</a></li>
                    <li><a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="hover:text-white transition-colors">{{ $isFr ? 'Entreprises' : 'Businesses' }}</a></li>
                </ul>
            </div>

            <!-- Ressources -->
            <div>
                <h4 class="text-[12px] font-bold tracking-[0.14em] text-white uppercase mb-4">{{ $isFr ? 'Ressources' : 'Resources' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-[#B9C4BC] whitespace-nowrap">
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Guide de l\'artisan' : 'Artisan guide' }}</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="{{ route('support.index') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Centre d\'aide' : 'Help center' }}</a></li>
                    <li><a href="{{ route('events.index') }}" class="hover:text-white transition-colors">Blog</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Conditions d\'utilisation' : 'Terms of use' }}</a></li>
                </ul>
            </div>

            <!-- À propos -->
            <div>
                <h4 class="text-[12px] font-bold tracking-[0.14em] text-white uppercase mb-4">{{ $isFr ? 'À propos' : 'About' }}</h4>
                <ul class="space-y-2.5 text-[12.5px] text-[#B9C4BC] whitespace-nowrap">
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
                <h4 class="text-[12px] font-bold tracking-[0.14em] text-white uppercase mb-4">Newsletter</h4>
                <p class="text-[12px] text-[#B9C4BC] leading-relaxed">
                    {{ $isFr ? 'Restez informé de nos actualités et de nos nouveautés.' : 'Stay informed of our news and new arrivals.' }}
                </p>
                <form action="/inscription" method="GET" class="mt-4 flex gap-2">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="email" type="email" required placeholder="{{ $isFr ? 'Votre email' : 'Your email' }}"
                        class="flex-1 min-w-0 w-[120px] bg-transparent border border-[#2E5240] rounded-md px-3 py-2.5 text-[12px] text-white placeholder-[#8FA79A] focus:outline-none focus:border-gold">
                    <button type="submit" class="bg-[#EBA405] hover:bg-goldbt text-[#3A2E08] text-[12.5px] font-semibold px-4 py-2.5 rounded-md transition-colors shrink-0">
                        {{ $isFr ? 'S\'abonner' : 'Subscribe' }}
                    </button>
                </form>
            </div>

            <!-- Cameroon map + caption -->
            <div class="hidden lg:flex items-center gap-4">
                <img src="{{ asset('images/landing/product-footer-map.png') }}" alt="" class="w-[112px]" aria-hidden="true">
                <p class="text-[13px] text-[#D8E2DC] leading-relaxed">
                    {{ $isFr ? 'Cameroun,' : 'Cameroon,' }}<br>
                    {{ $isFr ? 'terre de créativité' : 'land of creativity' }}<br>
                    {{ $isFr ? 'et d\'innovation' : 'and innovation' }}
                </p>
            </div>
        </div>

        <!-- Legal bar -->
        <div class="mt-7 pt-4 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11.5px] text-[#93A79B]">
            <span>&copy; 2025 {{ $isFr ? 'Galerie Virtuelle Nationale de l\'Artisanat du Cameroun. Tous droits réservés.' : 'National Virtual Gallery of Cameroonian Crafts. All rights reserved.' }}</span>
            <span class="flex items-center gap-3 whitespace-nowrap">
                <a href="{{ route('terms') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Mentions légales' : 'Legal notice' }}</a>
                <span class="text-white/20">|</span>
                <a href="{{ route('privacy') }}" class="hover:text-white transition-colors">{{ $isFr ? 'Politique de confidentialité' : 'Privacy policy' }}</a>
            </span>
        </div>
    </div>
</footer>

<!-- Mobile bottom navigation -->
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white border-t border-gray-200 flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @php
        $dfBottomTabs = [
            ['href' => route('home', ['lang' => $lang]), 'icon' => 'home', 'label' => $isFr ? 'Accueil' : 'Home', 'active' => false],
            ['href' => route('businesses.index', ['lang' => $lang]), 'icon' => 'compass', 'label' => $isFr ? 'Explorer' : 'Explore', 'active' => false],
            ['href' => $siacUser ? route('saved.index') : '/login', 'icon' => 'bookmark', 'label' => $isFr ? 'Favoris' : 'Saved', 'active' => false],
            ['href' => $siacUser ? route('messages.inbox') : '/login', 'icon' => 'message-circle', 'label' => 'Messages', 'active' => false],
            ['href' => $siacUser ? '/tableau-de-bord' : '/login', 'icon' => 'user', 'label' => $isFr ? 'Profil' : 'Profile', 'active' => false],
        ];
    @endphp
    @foreach($dfBottomTabs as $tab)
    <a href="{{ $tab['href'] }}" class="flex-1 flex flex-col items-center justify-center gap-0.5 py-2 {{ $tab['active'] ? 'text-leaf' : 'text-gray-400' }}">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5"></i>
        <span class="text-[10px] font-medium">{{ $tab['label'] }}</span>
    </a>
    @endforeach
</nav>
