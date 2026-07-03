{{-- Admin panel replica topbar (design: "admin panel.png").
     Expects: $lang, $isFr, $siacUser. --}}
<header class="flex items-center gap-4 px-5 lg:px-7 py-4">
    <button type="button" onclick="document.getElementById('ad-sidebar').classList.toggle('ad-open')" class="shrink-0 w-[42px] h-[42px] bg-[#0F4824] hover:bg-[#14652F] rounded-lg flex items-center justify-center text-white transition-colors">
        <i data-lucide="menu" class="w-5 h-5"></i>
    </button>

    <form action="{{ route('gallery.search') }}" method="GET" class="flex-1 max-w-[460px] hidden md:block">
        <input type="hidden" name="lang" value="{{ $lang }}">
        <div class="flex items-center gap-3 bg-white border border-[#E9E4D8] rounded-lg px-4 h-[42px]">
            <i data-lucide="search" class="w-[16px] h-[16px] shrink-0 text-[#8A857A]"></i>
            <input type="text" name="q" placeholder="{{ $isFr ? 'Rechercher un artisan, un produit, une entreprise, une région...' : 'Search an artisan, a product, a business, a region...' }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A857A]">
            <button type="submit" class="shrink-0 text-[#55524A]"><i data-lucide="search" class="w-[15px] h-[15px]"></i></button>
        </div>
    </form>

    <div class="flex items-center gap-4 shrink-0 ml-auto">
        <div class="relative group">
            <button class="flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[42px] text-[13px] font-semibold text-white transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                {{ $isFr ? 'Ajouter' : 'Add' }}
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-80"></i>
            </button>
            <div class="absolute right-0 top-full w-56 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1.5 hidden group-hover:block z-50">
                <a href="{{ route('admin.businesses') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Ajouter un artisan' : 'Add an artisan' }}</a>
                <a href="{{ route('products.web-create') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Ajouter un produit' : 'Add a product' }}</a>
                <a href="{{ route('business.create') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Ajouter une entreprise' : 'Add a business' }}</a>
                <a href="{{ route('admin.events') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Créer un événement' : 'Create an event' }}</a>
            </div>
        </div>

        <a href="{{ route('notifications.index') }}" class="relative" title="Notifications">
            <i data-lucide="bell" class="w-[21px] h-[21px] text-[#3B382F]" style="stroke-width:1.7"></i>
            <span class="absolute -top-2 -right-2 min-w-[18px] h-[17px] px-1 rounded-full bg-[#E9B23C] text-[#3A2A03] text-[10px] font-bold flex items-center justify-center">12</span>
        </a>
        <a href="{{ route('messages.inbox') }}" class="relative" title="Messages">
            <i data-lucide="mail" class="w-[21px] h-[21px] text-[#3B382F]" style="stroke-width:1.7"></i>
            <span class="absolute -top-2 -right-2 min-w-[18px] h-[17px] px-1 rounded-full bg-[#DC2626] text-white text-[10px] font-bold flex items-center justify-center">5</span>
        </a>

        <div class="relative group hidden sm:block">
            <button class="flex items-center gap-1.5 text-[13px] font-semibold text-[#1B1B18]">
                <i data-lucide="globe" class="w-[17px] h-[17px] text-[#3B382F]" style="stroke-width:1.7"></i>
                {{ strtoupper($lang) }}
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
            </button>
            <div class="absolute right-0 top-full w-32 bg-white rounded-lg shadow-lg border border-[#E7E7E5] py-1 hidden group-hover:block z-50">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-[#14652F]' : 'text-[#262521]' }}">FR — Français</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-[#14652F]' : 'text-[#262521]' }}">EN — English</a>
            </div>
        </div>

        <div class="relative group">
            <button class="flex items-center gap-2.5">
                <img src="{{ asset('images/landing/ad-avatar.png') }}" alt="" class="w-[38px] h-[38px] rounded-full object-cover">
                <span class="leading-tight text-left hidden sm:block">
                    <span class="block text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $siacUser['name'] ?? 'Admin Super' }}</span>
                    <span class="block text-[11px] text-[#6F6B60] whitespace-nowrap">{{ $isFr ? 'Administrateur Principal' : 'Principal Administrator' }}</span>
                </span>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A]"></i>
            </button>
            <div class="absolute right-0 top-full w-52 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1.5 hidden group-hover:block z-50">
                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                <a href="{{ route('security.show') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                <div class="my-1 border-t border-[#EFEFED]"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-[12.5px] text-[#B42025] hover:bg-[#FDE8E8]">{{ $isFr ? 'Déconnexion' : 'Log out' }}</button>
                </form>
            </div>
        </div>
    </div>
</header>
