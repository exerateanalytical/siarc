{{-- Heritage-branded admin header (designs: "KYC Centre.png", "Subscriptions.png", …).
     Expects: $lang, $isFr, $siacUser, $pageTitle. Optional: $pageSubtitle,
     $pageSearchPlaceholder, $pageBellCount. --}}
@php
    $pageSubtitle = $pageSubtitle ?? '';
    $pageSearchPlaceholder = $pageSearchPlaceholder ?? ($isFr ? 'Rechercher...' : 'Search...');
    // No caller-supplied count means no count to show — the badge hides rather
    // than displaying a placeholder number of unread notifications.
    $pageBellCount = $pageBellCount ?? 0;
@endphp
{{-- The admin header is the densest bar on the platform: burger, mask, title,
     search, language, bell and account all sit on one 1280px row. The theme
     control therefore renders icon-only below 1536px -- the word "Thème" is
     ~46px that the page title needs more. --}}
<style>
    @media (max-width: 1535.98px) { .ad-theme-toggle .theme-toggle__label { display: none; } }
</style>
<header class="px-5 lg:px-7 pt-4">
    <div class="flex items-center gap-4">
        <button type="button" onclick="document.getElementById('ad-sidebar').classList.toggle('ad-open')" class="shrink-0 w-[46px] h-[46px] bg-[#F5EEDD] dark:bg-[#1A1E16] border border-[#E7DDC3] dark:border-[#4A3A12] hover:border-[#C9942E] dark:hover:border-[#E9A81E] rounded-xl flex items-center justify-center text-[#3B382F] dark:text-[#B4B5A6] transition-colors">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <img src="{{ asset('images/landing/hh-mask.png') }}" alt="" class="w-[44px] h-[46px] object-contain shrink-0 hidden sm:block" aria-hidden="true">

        {{-- `flex-1 min-w-0` + `truncate`: the actions row on the right is
             `shrink-0`, so anything added to it steals width from this column.
             Without these the title has no width to lose and simply overflows
             underneath the controls -- which is exactly what the theme toggle
             did when it landed here. --}}
        <div class="flex-1 min-w-0 basis-[220px]">
            <h1 class="text-[22px] lg:text-[26px] font-extrabold tracking-tight text-[#0E3D22] dark:text-[#339B56] leading-none uppercase truncate">{{ $pageTitle }}</h1>
            @if($pageSubtitle)
            <p class="mt-1 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">{{ $pageSubtitle }}</p>
            @endif
            @if(!empty($pageBreadcrumb))
            <p class="mt-1 flex items-center gap-1.5 text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">
                @foreach($pageBreadcrumb as $bi => [$bLabel, $bUrl])
                    @if($bi > 0)<i data-lucide="chevron-right" class="w-3 h-3 text-[#B9B4A9] dark:text-[#868778]"></i>@endif
                    @if($bUrl)<a href="{{ $bUrl }}" class="hover:text-[#14652F] dark:hover:text-[#339B56]">{{ $bLabel }}</a>@else<span class="text-[#55524A] dark:text-[#B4B5A6]">{{ $bLabel }}</span>@endif
                @endforeach
            </p>
            @endif
        </div>

        <div class="flex items-center gap-3.5 min-w-0 ml-auto">
            {{-- Dark-mode control. Its presence suppresses the floating
                 fallback mounted by pages.partials.theme. --}}
            <span class="ad-theme-toggle">@include('pages.partials.theme-toggle')</span>
            <form action="{{ route('gallery.search') }}" method="GET" class="hidden lg:block min-w-0 shrink">
                <input type="hidden" name="lang" value="{{ $lang }}">
                <div class="flex items-center gap-2.5 bg-white dark:bg-[#12150F] border border-[#E7DDC3] dark:border-[#4A3A12] rounded-lg px-4 h-[44px] w-[300px] max-w-full">
                    <input type="text" name="q" placeholder="{{ $pageSearchPlaceholder }}" class="flex-1 min-w-0 bg-transparent text-[12.5px] focus:outline-none placeholder-[#8A857A] dark:placeholder-[#868778]">
                    <button type="submit" class="shrink-0 text-[#55524A] dark:text-[#B4B5A6]"><i data-lucide="search" class="w-[16px] h-[16px]"></i></button>
                </div>
            </form>

            <div class="relative group hidden sm:block">
                <button class="flex items-center gap-1.5 text-[13px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">
                    <i data-lucide="globe" class="w-[18px] h-[18px] text-[#3B382F] dark:text-[#B4B5A6]" style="stroke-width:1.7"></i>
                    {{ strtoupper($lang) }}
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A] dark:text-[#868778]"></i>
                </button>
                <div class="absolute right-0 top-full w-32 bg-white dark:bg-[#12150F] rounded-lg shadow-lg border border-[#E7E7E5] dark:border-[#262B21] py-1 hidden group-hover:block z-50">
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'fr']) }}" class="block px-3 py-1.5 text-[14px] md:text-[12.5px] {{ $isFr ? 'font-semibold text-[#14652F] dark:text-[#339B56]' : 'text-[#262521] dark:text-[#F3EFE7] ' }}">FR — Français</a>
                    <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="block px-3 py-1.5 text-[14px] md:text-[12.5px] {{ !$isFr ? 'font-semibold text-[#14652F] dark:text-[#339B56]' : 'text-[#262521] dark:text-[#F3EFE7] ' }}">EN — English</a>
                </div>
            </div>

            <a href="{{ route('notifications.index') }}" class="relative" title="Notifications">
                <i data-lucide="bell" class="w-[21px] h-[21px] text-[#3B382F] dark:text-[#B4B5A6]" style="stroke-width:1.7"></i>
                @if($pageBellCount)
                <span class="absolute -top-2 -right-2 min-w-[18px] h-[17px] px-1 rounded-full bg-[#DC2626] dark:bg-[#C0343B] text-white text-[12px] md:text-[10px] font-bold flex items-center justify-center">{{ $pageBellCount }}</span>
                @endif
            </a>

            <div class="relative group">
                <button class="flex items-center gap-2.5">
                    <img src="{{ asset('images/landing/ad-avatar.png') }}" alt="" class="w-[40px] h-[40px] rounded-full object-cover">
                    <span class="leading-tight text-left hidden md:block">
                        {{-- Real operator name or nothing; never a placeholder identity. --}}
                        @if(! empty($siacUser['name']))
                        <span class="block max-w-[190px] truncate text-[13px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] whitespace-nowrap">{{ $siacUser['name'] }}</span>
                        @endif
                        <span class="block text-[11px] text-[#6F6B60] dark:text-[#868778] whitespace-nowrap">{{ $isFr ? 'Super Administrateur' : 'Super Administrator' }}</span>
                    </span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-[#8A857A] dark:text-[#868778] hidden md:block"></i>
                </button>
                <div class="absolute right-0 top-full w-52 bg-white dark:bg-[#12150F] rounded-xl shadow-lg border border-[#E7E7E5] dark:border-[#262B21] py-1.5 hidden group-hover:block z-50">
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-[14px] md:text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E]">{{ $isFr ? 'Mon profil' : 'My profile' }}</a>
                    <a href="{{ route('security.show') }}" class="block px-4 py-2 text-[14px] md:text-[12.5px] text-[#262521] dark:text-[#F3EFE7] hover:bg-[#F6F7F6] dark:hover:bg-[#242A1E]">{{ $isFr ? 'Sécurité' : 'Security' }}</a>
                    <div class="my-1 border-t border-[#EFEFED] dark:border-[#262B21]"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-[14px] md:text-[12.5px] text-[#B42025] dark:text-[#F0555C] hover:bg-[#FDE8E8] dark:hover:bg-[#3A1013]">{{ $isFr ? 'Déconnexion' : 'Log out' }}</button>
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
