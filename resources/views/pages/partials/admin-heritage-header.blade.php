{{-- Heritage-branded admin header (designs: "KYC Centre.png", "Subscriptions.png", …).
     Expects: $lang, $isFr, $siacUser, $pageTitle. Optional: $pageSubtitle,
     $pageSearchPlaceholder, $pageBellCount. --}}
@php
    $pageSubtitle = $pageSubtitle ?? '';
    $pageSearchPlaceholder = $pageSearchPlaceholder ?? ($isFr ? 'Rechercher...' : 'Search...');
    $pageBellCount = $pageBellCount ?? 6;
@endphp
<header class="px-5 lg:px-7 pt-4">
    <div class="flex items-center gap-4">
        <button type="button" onclick="document.getElementById('ad-sidebar').classList.toggle('ad-open')" class="shrink-0 w-[46px] h-[46px] bg-[#F5EEDD] border border-[#E7DDC3] hover:border-[#C9942E] rounded-xl flex items-center justify-center text-[#3B382F] transition-colors">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <img src="{{ asset('images/landing/hh-mask.png') }}" alt="" class="w-[44px] h-[46px] object-contain shrink-0 hidden sm:block" aria-hidden="true">

        <div class="min-w-0">
            <h1 class="text-[22px] lg:text-[26px] font-extrabold tracking-tight text-[#0E3D22] leading-none uppercase">{{ $pageTitle }}</h1>
            @if($pageSubtitle)
            <p class="mt-1 text-[12px] text-[#6F6B60]">{{ $pageSubtitle }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3.5 shrink-0 ml-auto">
            <form action="{{ route('gallery.search') }}" method="GET" class="hidden lg:block">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex items-center gap-2.5 bg-white border border-[#E7DDC3] rounded-lg px-4 h-[44px] w-[300px]">
                    <input type="text" name="q" placeholder="{{ $pageSearchPlaceholder }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A857A]">
                    <button type="submit" class="shrink-0 text-[#55524A]"><i data-lucide="search" class="w-[16px] h-[16px]"></i></button>
                </div>
            </form>

            <div class="relative group hidden sm:block">
                <button class="flex items-center gap-1.5 text-[13px] font-semibold text-[#1B1B18]">
                    <i data-lucide="globe" class="w-[18px] h-[18px] text-[#3B382F]" style="stroke-width:1.7"></i>
                    {{ strtoupper($lang) }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                </button>
                <div class="absolute right-0 top-full w-32 bg-white rounded-lg shadow-lg border border-[#E7E7E5] py-1 hidden group-hover:block z-50">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[12.5px] {{ $isFr ? 'font-semibold text-[#14652F]' : 'text-[#262521]' }}">FR — Français</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[12.5px] {{ !$isFr ? 'font-semibold text-[#14652F]' : 'text-[#262521]' }}">EN — English</a>
                </div>
            </div>

            <a href="{{ route('notifications.index') }}" class="relative" title="Notifications">
                <i data-lucide="bell" class="w-[21px] h-[21px] text-[#3B382F]" style="stroke-width:1.7"></i>
                <span class="absolute -top-2 -right-2 min-w-[18px] h-[17px] px-1 rounded-full bg-[#DC2626] text-white text-[10px] font-bold flex items-center justify-center">{{ $pageBellCount }}</span>
            </a>

            <div class="relative group">
                <button class="flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/ad-avatar.png') }}" alt="" class="w-[40px] h-[40px] rounded-full object-cover">
                    <span class="leading-tight text-left hidden md:block">
                        <span class="block text-[13px] font-bold text-[#1B1B18] whitespace-nowrap">{{ $siacUser['name'] ?? 'Admin Super' }}</span>
                        <span class="block text-[11px] text-[#6F6B60] whitespace-nowrap">{{ $isFr ? 'Super Administrateur' : 'Super Administrator' }}</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A] hidden md:block"></i>
                </button>
                <div class="absolute right-0 top-full w-52 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1.5 hidden group-hover:block z-50">
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                    <a href="{{ route('security.show') }}" class="block px-4 py-2 text-[12.5px] text-[#262521] hover:bg-[#F6F7F6]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                    <div class="my-1 border-t border-[#EFEFED]"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-[12.5px] text-[#B42025] hover:bg-[#FDE8E8]">{{ $isFr ? 'Déconnexion' : 'Log out' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Kente divider with central medallion --}}
    <div class="relative mt-3 h-[30px] flex items-center justify-center">
        <span class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-[24px]" style="background-image: url('{{ asset('images/landing/hh-kente.png') }}'); background-repeat: repeat-x; background-size: auto 24px; opacity: 0.92;" aria-hidden="true"></span>
        <img src="{{ asset('images/landing/hh-medallion.png') }}" alt="" class="relative h-[46px] w-auto" aria-hidden="true">
    </div>
</header>
