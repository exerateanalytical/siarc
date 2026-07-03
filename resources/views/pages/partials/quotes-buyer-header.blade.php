{{-- Buyer quote-flow header (designs: "create un demande.png" / "quote propositions.png").
     Expects: $lang, $isFr, $siacUser. Options: $qbSearchPlaceholder. --}}
<header class="bg-white border-b border-[#EEEFEE]">
    <div class="max-w-[1536px] mx-auto px-4 lg:px-6 flex items-center gap-4 py-3">
        <button type="button" onclick="document.getElementById('qb-sidebar').classList.toggle('open')" class="shrink-0 text-[#1B1B18]">
            <i data-lucide="menu" class="w-6 h-6" style="stroke-width:1.7"></i>
        </button>
        <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
            <img src="{{ asset('images/landing/logo.png') }}" alt="" class="w-[48px] h-[52px] object-contain">
            <span class="leading-tight hidden sm:block">
                <span class="block text-[13px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'Galerie Virtuelle Nationale' : 'National Virtual Gallery' }}</span>
                <span class="block text-[13px] font-bold tracking-[0.02em] text-[#1B1B18] uppercase whitespace-nowrap">{{ $isFr ? 'de l\'Artisanat du Cameroun' : 'of Cameroonian Crafts' }}</span>
                <span class="block text-[10.5px] font-semibold text-[#157A43] whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </a>
        <form action="{{ route('gallery.search') }}" method="GET" class="flex-1 max-w-[560px] mx-auto hidden md:block">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="flex items-center gap-3 border border-[#E7E7E5] rounded-xl px-4 h-[46px]">
                <i data-lucide="search" class="w-[18px] h-[18px] shrink-0 text-[#8A857A]"></i>
                <input type="text" name="q" placeholder="{{ $qbSearchPlaceholder ?? ($isFr ? 'Rechercher (produits, artisans, entreprises...)' : 'Search (products, artisans, businesses...)') }}" class="flex-1 min-w-0 bg-transparent text-[13px] focus:outline-none placeholder-[#8A857A]">
            </div>
        </form>
        <div class="flex items-center gap-5 shrink-0 ml-auto">
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="relative" title="Messages">
                <i data-lucide="message-circle" class="w-6 h-6 text-[#3B382F]" style="stroke-width:1.7"></i>
                <span class="absolute -top-1.5 -right-1.5 w-[17px] h-[17px] rounded-full bg-[#157A43] text-white text-[10px] font-bold flex items-center justify-center">3</span>
            </a>
            <a href="{{ route('notifications.index', ['lang' => $lang]) }}" class="relative" title="Notifications">
                <i data-lucide="bell" class="w-6 h-6 text-[#3B382F]" style="stroke-width:1.7"></i>
                <span class="absolute -top-1.5 -right-2 min-w-[19px] h-[17px] rounded-full bg-[#E01E1E] text-white text-[10px] font-bold flex items-center justify-center px-1">12</span>
            </a>
            <div class="relative group">
                <button class="flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/qb-avatar.png') }}" alt="" class="w-[44px] h-[44px] rounded-full object-cover">
                    <span class="leading-tight text-left hidden sm:block">
                        <span class="block text-[13.5px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $siacUser['name'] ?? 'Jean Dupont' }}</span>
                        <span class="block text-[11.5px] text-[#6F6B60] whitespace-nowrap">{{ $isFr ? 'Acheteur Professionnel' : 'Professional Buyer' }}</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i>
                </button>
                <div class="absolute right-0 top-full w-56 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1.5 hidden group-hover:block z-50">
                    <a href="{{ route('profile.show', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                    <a href="{{ route('security.show', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                    <div class="my-1 border-t border-[#EFEFED]"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-[12.5px] text-[#B42025] hover:bg-[#F6F7F6]">{{ $isFr ? 'Déconnexion' : 'Log out' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
