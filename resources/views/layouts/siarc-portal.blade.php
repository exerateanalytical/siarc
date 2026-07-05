@php
    use Illuminate\Support\Facades\Route as R;
    $lang = request()->query('lang', request()->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr','en']) ? $lang : 'fr';
    $isFr = $lang === 'fr';
    $u = session('siac_user') ?? [];
    $uName = is_array($u) ? ($u['name'] ?? 'Visiteur') : ($u->name ?? 'Visiteur');
    $uRole = $isFr ? 'Visiteur' : 'Visitor';
    $ev = function_exists('siarcEvent') ? siarcEvent() : null;
    $evDates = $isFr ? '27 Juillet – 05 Août 2026' : '27 July – 05 August 2026';
    $evVenue = $ev->location_fr ?? 'Musée National de Yaoundé';
    // portal hrefs never resolve to '#': fall back to the visitor dashboard.
    $ph = fn($route, $params = []) => R::has($route) ? route($route, array_merge(['lang'=>$lang], $params)) : route('siarc.visitor.dashboard', ['lang'=>$lang]);
    $home = route('siarc.home', ['lang'=>$lang]);

    // Visitor sidebar — verbatim from the Visitor Dashboard design.
    // Item: [labelFr, labelEn, icon, route, [active patterns], badge|null, liveTag]
    $navSections = [
        ['VISITEUR', 'VISITOR', [
            ['Tableau de bord',       'Dashboard',         'layout-dashboard', 'siarc.visitor.dashboard', ['siarc.visitor.dashboard'], null, false],
            ['Programme',             'Programme',         'calendar-days',    'siarc.programme', ['siarc.programme'], null, false],
            ['Intervenants',          'Speakers',          'mic',              'siarc.speakers', ['siarc.speaker*'], null, false],
            ['Exposants',             'Exhibitors',        'store',            'siarc.exhibitors', ['siarc.exhibitor*'], null, false],
            ['Pavillons',             'Pavilions',         'building-2',       'siarc.pavilions', ['siarc.pavilions'], null, false],
            ['Plan du salon',         'Floor plan',        'map',              'siarc.pavilions', [], null, false],
            ['Mes favoris',           'My favourites',     'heart',            'siarc.visitor.dashboard', [], null, false],
            ['Mes rendez-vous',       'My meetings',       'handshake',        'siarc.visitor.dashboard', [], null, false],
            ['Live & Actualités',     'Live & News',       'radio',            'siarc.visitor.dashboard', [], null, true],
            ['Informations pratiques','Practical info',    'info',             'siarc.register', ['siarc.register'], null, false],
        ]],
        ['MON COMPTE', 'MY ACCOUNT', [
            ['Mon profil',            'My profile',        'user',             'siarc.visitor.dashboard', [], null, false],
            ['Mes billets',           'My tickets',        'ticket',           'siarc.visitor.dashboard', [], '2', false],
            ['Mes réservations',      'My bookings',       'calendar-check',   'siarc.visitor.dashboard', [], null, false],
            ['Paramètres',            'Settings',          'settings',         'siarc.visitor.dashboard', [], null, false],
        ]],
    ];
    $labelIdx = $isFr ? 0 : 1;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($sTitle ?? 'Mon espace') }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { siarc:{green:'#157A43',dark:'#0B3A1E',darker:'#042B15',gold:'#E6B201',ochre:'#C97A16',red:'#C0010C'} },
            fontFamily: { sans:['Poppins','system-ui','sans-serif'], display:['"Playfair Display"','Georgia','serif'] },
        } } }
    </script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.siarc.partials.tokens')
    @stack('head')
    <style>
        body{font-family:'Poppins',system-ui,sans-serif}
        html,body{overflow-x:clip}
        #si-side{display:none}
        #si-side.open{display:flex;position:fixed;inset:0 auto 0 0;width:266px;z-index:60}
        @media (min-width:1024px){#si-side,#si-side.open{display:flex;position:sticky;top:0;height:100vh;width:266px}}
    </style>
</head>
<body class="bg-[#F5F3EE] text-[#1D1B16] antialiased">
<div class="flex min-h-screen">

    <aside id="si-side" class="siarc-scroll flex-col shrink-0 bg-gradient-to-b from-[#0B3A1E] to-[#042B15] text-white overflow-y-auto">
        <div class="px-5 pt-5 pb-4 border-b border-white/10">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => true])
        </div>
        <nav class="flex-1 px-3 py-4 space-y-5">
            @foreach($navSections as [$secFr,$secEn,$items])
            <div>
                <p class="px-3.5 mb-2 text-[10px] font-bold tracking-[0.14em] text-white/35">{{ $labelIdx === 0 ? $secFr : $secEn }}</p>
                <div class="space-y-1">
                    @foreach($items as [$lblFr,$lblEn,$icon,$route,$patterns,$badge,$live])
                        @php $active = !empty($patterns) && request()->routeIs(...$patterns); @endphp
                        <a href="{{ $ph($route) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors
                           {{ $active ? 'bg-siarc-green text-white shadow-[0_6px_16px_-6px_rgba(21,122,67,.8)]' : 'text-[#B7C6BC] hover:bg-white/8 hover:text-white' }}">
                            <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] shrink-0" style="stroke-width:{{ $active ? 2.1 : 1.8 }}"></i>
                            <span class="truncate">{{ $labelIdx === 0 ? $lblFr : $lblEn }}</span>
                            @if($live)<span class="ml-auto shrink-0 text-[8.5px] font-bold px-1.5 py-0.5 rounded bg-siarc-red text-white tracking-wide">LIVE</span>
                            @elseif($badge)<span class="ml-auto shrink-0 min-w-[18px] h-[18px] px-1 rounded-full bg-siarc-gold text-[#3a2a00] text-[10px] font-bold flex items-center justify-center">{{ $badge }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="px-4 pb-5 mt-auto">
            <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-[#12522B] to-[#04240F] border border-white/10 min-h-[132px] flex flex-col justify-center">
                <div class="relative z-10 px-4 py-4 max-w-[66%]">
                    <p class="font-display text-[15px] font-bold text-siarc-gold leading-tight">{{ $isFr ? 'Célébrons l\'Artisanat Africain' : 'Celebrating African Craft' }}</p>
                    <p class="text-[11.5px] text-white/75 mt-1.5">{{ $isFr ? 'Entre tradition, innovation et excellence.' : 'Tradition, innovation and excellence.' }}</p>
                </div>
                <img src="{{ asset('images/siarc/sidebar-statue.png') }}" alt="" class="absolute right-0 bottom-0 h-[132px] w-auto object-contain object-bottom">
            </div>
            <a href="{{ $home }}" class="mt-3 flex items-center justify-center gap-2 text-white/60 hover:text-white text-[12px]">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Retour au site public' : 'Back to public site' }}
            </a>
        </div>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-[#ECEAE3]">
            <div class="h-[68px] px-4 sm:px-7 flex items-center gap-4">
                <button id="si-burger" class="lg:hidden w-9 h-9 -ml-1 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center shrink-0">
                    <i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i>
                </button>
                <div class="min-w-0">
                    <h1 class="font-display text-[20px] sm:text-[23px] font-bold text-[#1D1B16] truncate leading-tight">{{ $isFr ? 'Bienvenue' : 'Welcome' }}, {{ $uName }} 👋</h1>
                    <p class="text-[12px] text-[#8A857A] truncate">{{ $isFr ? 'Votre espace visiteur pour le SIARC 2026' : 'Your visitor space for SIARC 2026' }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3 sm:gap-5">
                    <div class="hidden md:flex items-center gap-2.5">
                        <i data-lucide="calendar" class="w-[18px] h-[18px] text-siarc-ochre"></i>
                        <div class="leading-tight">
                            <p class="text-[12.5px] font-semibold text-[#1D1B16]">{{ $evDates }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $evVenue }}</p>
                        </div>
                    </div>
                    <div class="hidden sm:flex items-center gap-1.5 text-[13px] font-medium text-[#3B382F]">
                        <i data-lucide="globe" class="w-[17px] h-[17px] text-[#8A857A]"></i>
                        <a href="?lang=fr" class="{{ $isFr ? 'text-siarc-green font-semibold' : '' }}">FR</a>
                        <span class="text-[#D5D2CA]">·</span>
                        <a href="?lang=en" class="{{ !$isFr ? 'text-siarc-green font-semibold' : '' }}">EN</a>
                    </div>
                    <button class="relative w-9 h-9 rounded-full hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="bell" class="w-[19px] h-[19px] text-[#3B382F]"></i>
                    </button>
                    <div class="flex items-center gap-2.5 pl-1">
                        <span class="w-9 h-9 rounded-full bg-siarc-green text-white text-[13px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($uName,0,1)) }}</span>
                        <div class="hidden sm:block leading-tight">
                            <p class="text-[13px] font-semibold text-[#1D1B16]">{{ $uName }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $uRole }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 sm:px-7 py-6">
            @yield('content')
        </main>

        <footer class="px-4 sm:px-7 py-4 border-t border-[#ECEAE3] bg-white flex flex-col sm:flex-row items-center justify-between gap-2 text-[12px] text-[#8A857A]">
            <span>SIARC 2026 — {{ $isFr ? 'Salon International de l\'Artisanat du Cameroun' : 'Cameroon International Craft Fair' }}</span>
            <span>© 2026 {{ $isFr ? 'Tous droits réservés' : 'All rights reserved' }}</span>
        </footer>
    </div>
</div>

<div id="si-overlay" class="hidden lg:hidden fixed inset-0 bg-black/40 z-50"></div>
<script>
    lucide.createIcons();
    (function(){
        var b=document.getElementById('si-burger'),s=document.getElementById('si-side'),o=document.getElementById('si-overlay');
        function toggle(){s.classList.toggle('open');o.classList.toggle('hidden');}
        if(b)b.addEventListener('click',toggle); if(o)o.addEventListener('click',toggle);
    })();
</script>
<script src="{{ asset('vendor/siarc-ui.js') }}"></script>
@stack('scripts')
</body>
</html>
