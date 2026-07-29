{{-- Shared bottom of the auth replica pages: "Pourquoi rejoindre" band + footer + mobile nav.
     Expects: $isFr, $lang, $socialIcons, $siacUser --}}

@php
    $whyItems = $isFr ? [
        ['globe',       "Visibilité nationale\net internationale", "Faites découvrir votre savoir-\nfaire au monde entier."],
        ['shield-check', "Artisans\nvérifiés", "Identité et documents de métier\ncontrôlés avant qu'un profil\nne soit marqué vérifié."],
        ['users',       "Communauté\nengagée",                     "Connectez-vous avec des milliers\nd'artisans, acheteurs et partenaires."],
        ['trending-up', "Croissance et\ndéveloppement",            "Accédez à des outils pour\ndévelopper votre activité\net vos opportunités."],
        ['headset',     "Support\ndédié",                          "Notre équipe est là pour vous\naccompagner à chaque étape."],
    ] : [
        ['globe',       "National and\ninternational visibility",  "Show your know-how\nto the whole world."],
        ['shield-check', "Verified\nartisans", "Identity and trade documents\nare checked before a profile\nis marked verified."],
        ['users',       "Engaged\ncommunity",                      "Connect with thousands of\nartisans, buyers and partners."],
        ['trending-up', "Growth and\ndevelopment",                 "Access tools to grow\nyour business and\nyour opportunities."],
        ['headset',     "Dedicated\nsupport",                      "Our team is here to support\nyou every step of the way."],
    ];
@endphp

<!-- Pourquoi rejoindre -->
<section class="relative bg-[#F2EDE7] dark:bg-[#0A0C09] mt-4 overflow-hidden">
    {{-- Corner motifs. These are opaque cream tiles that live in the empty
         margins either side of the five-across desktop band. At two columns the
         cards reach those bottom corners, and the tiles were painting straight
         over the fifth card — "Support dédié" was hidden behind one and its body
         copy sat on cream. They are decoration with no room to be, so they are
         `lg` and up only. Dimmed in dark, where cream at full strength reads as
         a lit slab rather than a texture (docs/DARK-MODE-CONTRACT.md). --}}
    <img src="{{ asset('images/landing/auth-band-motif-left.png') }}" alt="" class="hidden lg:block absolute left-0 bottom-0 w-[140px] dark:opacity-25 pointer-events-none select-none" aria-hidden="true">
    <img src="{{ asset('images/landing/auth-band-motif-right.png') }}" alt="" class="hidden lg:block absolute right-0 bottom-0 w-[140px] dark:opacity-25 pointer-events-none select-none" aria-hidden="true">
    <div class="relative max-w-[1280px] mx-auto px-5 lg:px-8 pt-8 pb-10">
        <h2 class="text-center font-serif text-[24px] sm:text-[27px] text-[#1D1B16] dark:text-[#F3EFE7]">
            {{ $isFr ? 'Pourquoi rejoindre Artisan Hub 237 ?' : 'Why join Artisan Hub 237?' }}
        </h2>
        {{-- Two columns from the narrowest phone up (owner request). The hard
             newlines baked into the copy are for the five-across desktop band and
             would shred a 160px-wide phone column, so `whitespace-pre-line` is
             desktop-only and mobile wraps naturally. Icon shrinks and moves above
             the text below `sm` so the title keeps the full column width. --}}
        <div class="mt-8 grid grid-cols-2 lg:grid-cols-5 gap-x-4 gap-y-8 lg:divide-x divide-[#DDD6CB] dark:divide-[#262B21]">
            @foreach($whyItems as [$whyIcon, $whyTitle, $whyDesc])
            <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-3.5 px-2 sm:px-5 min-w-0">
                <i data-lucide="{{ $whyIcon }}" class="w-8 h-8 sm:w-10 sm:h-10 shrink-0 text-[#B07E23] dark:text-[#EDB33A]" style="stroke-width:1.4"></i>
                <div class="min-w-0">
                    <h3 class="text-[16px] md:text-[13.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] leading-snug lg:whitespace-pre-line">{{ $whyTitle }}</h3>
                    <p class="mt-2 text-[14px] md:text-[12px] text-[#7A756A] dark:text-[#868778] leading-relaxed lg:whitespace-pre-line">{{ $whyDesc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Footer -->
{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-footer')

<!-- Mobile bottom navigation -->
<nav class="sm:hidden fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-[#12150F] border-t border-gray-200 dark:border-[#262B21] flex items-stretch" style="padding-bottom: env(safe-area-inset-bottom)">
    @php
        $bottomTabs = [
            ['href' => route('home', ['lang' => $lang]), 'icon' => 'home', 'label' => $isFr ? 'Accueil' : 'Home', 'active' => false],
            ['href' => route('businesses.index', ['lang' => $lang]), 'icon' => 'compass', 'label' => $isFr ? 'Explorer' : 'Explore', 'active' => false],
            ['href' => $siacUser ? route('saved.index') : '/login', 'icon' => 'bookmark', 'label' => $isFr ? 'Favoris' : 'Saved', 'active' => false],
            ['href' => $siacUser ? route('messages.inbox') : '/login', 'icon' => 'message-circle', 'label' => 'Messages', 'active' => false],
            ['href' => $siacUser ? route('dashboard.siac') : route('login'), 'icon' => 'user', 'label' => $isFr ? 'Profil' : 'Profile', 'active' => false],
        ];
    @endphp
    @foreach($bottomTabs as $tab)
    <a href="{{ $tab['href'] }}" class="flex-1 min-w-0 flex flex-col items-center justify-center gap-0.5 py-2 {{ $tab['active'] ? 'text-leaf dark:text-[#339B56]' : 'text-gray-400' }}">
        <i data-lucide="{{ $tab['icon'] }}" class="w-5 h-5"></i>
        <span class="text-[16px] md:text-[10px] font-medium self-stretch text-center leading-tight break-words">{{ $tab['label'] }}</span>
    </a>
    @endforeach
</nav>
