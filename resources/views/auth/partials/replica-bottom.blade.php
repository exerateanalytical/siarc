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
{{-- Canonical platform chrome (consolidated 2026-07-03) --}}
@include('pages.partials.directory-footer')

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
