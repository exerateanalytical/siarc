{{-- Buyer quote-flow header (designs: "create un demande.png" / "quote propositions.png").
     Expects: $lang, $isFr, $siacUser. Options: $qbSearchPlaceholder. --}}
<header class="bg-white dark:bg-[#12150F] border-b border-[#EEEFEE] dark:border-[#262B21]">
    <div class="max-w-[1536px] mx-auto px-4 lg:px-6 flex items-center gap-4 py-3">
        <button type="button" onclick="document.getElementById('qb-sidebar').classList.toggle('open')" class="lg:hidden shrink-0 text-[#1B1B18] dark:text-[#F3EFE7]">
            <i data-lucide="menu" class="w-6 h-6" style="stroke-width:1.7"></i>
        </button>
        <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center gap-3 shrink-0">
            <img src="{{ brand_asset('mark') }}" alt="" class="w-[52px] h-[52px] object-contain">
            <span class="leading-tight hidden sm:block">
                <span class="block text-[14px] md:text-[13px] font-bold tracking-[0.02em] text-[#1B1B18] dark:text-[#F3EFE7] uppercase whitespace-nowrap">{{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</span>
                <span class="block text-[14px] md:text-[13px] font-bold tracking-[0.02em] text-[#1B1B18] dark:text-[#F3EFE7] uppercase whitespace-nowrap">{{ $isFr ? 'Marketplace des artisans' : 'Artisan Marketplace' }}</span>
                <span class="block text-[14px] leading-tight md:text-[10.5px] md:leading-normal font-semibold text-[#157A43] dark:text-[#339B56] whitespace-normal md:whitespace-nowrap">{{ $isFr ? 'Notre héritage, notre fierté, notre avenir' : 'Our heritage, our pride, our future' }}</span>
            </span>
        </a>
        <form action="{{ route('gallery.search') }}" method="GET" class="flex-1 max-w-[560px] mx-auto hidden md:block">
            <input type="hidden" name="lang" value="{{ $lang }}">
            <div class="ui-field-group">
                <i data-lucide="search" class="w-[18px] h-[18px] shrink-0 text-[#8A857A] dark:text-[#868778]"></i>
                <input type="text" name="q" placeholder="{{ $qbSearchPlaceholder ?? ($isFr ? 'Rechercher (produits, artisans, entreprises...)' : 'Search (products, artisans, businesses...)') }}" class="ui-field-bare">
            </div>
        </form>
        <div class="flex items-center gap-5 shrink-0 ml-auto">
            <a href="{{ route('messages.inbox', ['lang' => $lang]) }}" class="relative" title="Messages">
                <i data-lucide="message-circle" class="w-6 h-6 text-[#3B382F] dark:text-[#B4B5A6]" style="stroke-width:1.7"></i>
                @if(($messageCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 min-w-[20px] h-5 md:w-[17px] md:h-[17px] md:min-w-0 px-0.5 md:px-0 rounded-full bg-[#157A43] dark:bg-[#2E9250] text-white dark:text-[#04150A] text-[14px] md:text-[10px] font-bold flex items-center justify-center">{{ min(99, $messageCount) }}</span>
                @endif
            </a>
            <a href="{{ route('notifications.index', ['lang' => $lang]) }}" class="relative" title="Notifications">
                <i data-lucide="bell" class="w-6 h-6 text-[#3B382F] dark:text-[#B4B5A6]" style="stroke-width:1.7"></i>
                @if(($notificationCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-2 min-w-[20px] h-5 md:min-w-[19px] md:h-[17px] rounded-full bg-[#E01E1E] dark:bg-[#C0343B] text-white text-[14px] md:text-[10px] font-bold flex items-center justify-center px-1">{{ min(99, $notificationCount) }}</span>
                @endif
            </a>
            <div class="relative group">
                <button class="flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/qb-avatar.png') }}" alt="" class="w-[44px] h-[44px] rounded-full object-cover">
                    <span class="leading-tight text-left hidden sm:block">
                        <span class="block text-[14px] md:text-[13.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] truncate max-w-[140px]">{{ $siacUser['name'] ?? '' }}</span>
                        <span class="block text-[14px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778] whitespace-nowrap">{{ $isFr ? 'Acheteur Professionnel' : 'Professional Buyer' }}</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i>
                </button>
                <div class="absolute right-0 top-full w-56 bg-white dark:bg-[#12150F] rounded-xl shadow-lg border border-[#E7E7E5] dark:border-[#262B21] py-1.5 hidden group-hover:block z-50">
                    <a href="{{ route('profile.show', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                    <a href="{{ route('security.show', ['lang' => $lang]) }}" class="block px-4 py-2 text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                    <div class="my-1 border-t border-[#EFEFED] dark:border-[#262B21]"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-[12.5px] text-[#B42025] dark:text-[#F0555C] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E]">{{ $isFr ? 'Déconnexion' : 'Log out' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
