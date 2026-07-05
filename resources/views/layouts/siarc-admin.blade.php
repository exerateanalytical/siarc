@php
    use Illuminate\Support\Facades\Route as R;
    $lang = request()->query('lang', request()->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr','en']) ? $lang : 'fr';
    $isFr = $lang === 'fr';
    $u = session('siac_user') ?? [];
    $uName = is_array($u) ? ($u['name'] ?? 'Admin') : ($u->name ?? 'Admin');
    $uRole = $isFr ? 'Administrateur' : 'Administrator';
    $ev = function_exists('siarcEvent') ? siarcEvent() : null;
    $evDates = $isFr ? '27 Juillet – 05 Août 2026' : '27 July – 05 August 2026';
    $evVenue = $ev->location_fr ?? 'Musée National de Yaoundé';
    // nav href never resolves to '#': fall back to the SIARC dashboard when a route is absent.
    $navHref = fn($route) => R::has($route) ? route($route, ['lang'=>$lang]) : route('siarc.admin.dashboard', ['lang'=>$lang]);
    $home = route('siarc.home', ['lang'=>$lang]);

    // Sectioned sidebar (matches the SIARC management design). Item: [labelFr,labelEn,icon,route,[active patterns]]
    $navSections = [
        ['SIARC MANAGEMENT', 'SIARC MANAGEMENT', [
            ['Tableau de bord',        'Dashboard',          'layout-dashboard', 'siarc.admin.dashboard', ['siarc.admin.dashboard']],
            ['Exposants',              'Exhibitors',         'store',            'siarc.admin.exhibitors', ['siarc.admin.exhibitor*','siarc.mobile.exhibitor-checkin']],
            ['Pavillons & Stands',     'Pavilions & Stands', 'building-2',       'siarc.admin.pavilions', ['siarc.admin.pavilion*','siarc.admin.stand*']],
            ['Plan du salon',          'Floor Plan',         'map',              'siarc.admin.floorplan', ['siarc.admin.floorplan']],
            ['Visiteurs',              'Visitors',           'users',            'siarc.admin.visitors', ['siarc.admin.visitor*','siarc.admin.vip']],
            ['Accréditations',         'Accreditation',      'id-card',          'siarc.admin.entry', ['siarc.admin.entry','siarc.admin.badges','siarc.admin.checkin','siarc.mobile.scanner']],
            ['B2B Matchmaking',        'B2B Matchmaking',    'handshake',        'siarc.admin.b2b', ['siarc.admin.b2b','siarc.admin.meeting','siarc.admin.matchmaking']],
            ['Programme & Activités',  'Programme',          'calendar-days',    'siarc.admin.programme', ['siarc.admin.programme','siarc.admin.session']],
            ['Conférences & Ateliers', 'Talks & Workshops',  'presentation',     'siarc.admin.calendar', ['siarc.admin.calendar','siarc.admin.workshop']],
            ['Intervenants',           'Speakers',           'mic',              'siarc.admin.speakers', ['siarc.admin.speaker*']],
        ]],
        ['OPÉRATIONS', 'OPERATIONS', [
            ['Opérations',             'Operations',         'clipboard-list',   'siarc.admin.live', ['siarc.admin.live','siarc.admin.incidents']],
            ['Communications',         'Communications',     'megaphone',        'messages.inbox', ['messages.*']],
            ['Analytics & Rapports',   'Analytics & Reports','bar-chart-3',      'siarc.admin.analytics', ['siarc.admin.analytics','siarc.admin.attendance','siarc.admin.reports']],
        ]],
        ['PARAMÈTRES', 'SETTINGS', [
            ['Mode plateforme',        'Platform Mode',      'toggle-right',     'siarc.admin.mode', ['siarc.admin.mode']],
            ['Utilisateurs',           'Users',              'users-round',      'admin.users', ['admin.users*']],
            ['Paramètres',             'Settings',           'settings',         'admin.settings', ['admin.settings*']],
            ['Journal d\'activité',    'Activity Log',       'history',          'admin.audit-log', ['admin.audit-log*']],
        ]],
    ];
    $labelIdx = $isFr ? 0 : 1;
    $socials = [
        ['facebook','M14 8.5h2.2V5.6C15.8 5.5 14.9 5.4 13.9 5.4c-2.1 0-3.5 1.3-3.5 3.6v2h-2.3v3h2.3V21h2.9v-6.9h2.2l.4-3h-2.6V9.3c0-.5.3-.8 1-.8Z'],
        ['instagram',null],['linkedin',null],
        ['youtube','M21 8.5a2.8 2.8 0 0 0-2-2C17.2 6 12 6 12 6s-5.2 0-7 .5a2.8 2.8 0 0 0-2 2A29 29 0 0 0 2.7 12 29 29 0 0 0 3 15.5a2.8 2.8 0 0 0 2 2c1.8.5 7 .5 7 .5s5.2 0 7-.5a2.8 2.8 0 0 0 2-2 29 29 0 0 0 .3-3.5 29 29 0 0 0-.3-3.5ZM10.5 14.5v-5l4 2.5-4 2.5Z'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($sTitle ?? 'SIARC') }} — SIARC 2026</title>
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

    {{-- ══════════════════ SIDEBAR ══════════════════ --}}
    <aside id="si-side" class="siarc-scroll flex-col shrink-0 bg-gradient-to-b from-[#0B3A1E] to-[#042B15] text-white overflow-y-auto">
        <div class="px-5 pt-5 pb-4 border-b border-white/10">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => true])
        </div>

        <nav class="flex-1 px-3 py-4 space-y-5">
            @foreach($navSections as [$secFr,$secEn,$items])
            <div>
                <p class="px-3.5 mb-2 text-[10px] font-bold tracking-[0.14em] text-white/35">{{ $labelIdx === 0 ? $secFr : $secEn }}</p>
                <div class="space-y-1">
                    @foreach($items as [$lblFr,$lblEn,$icon,$route,$patterns])
                        @php $active = request()->routeIs(...$patterns); @endphp
                        <a href="{{ $navHref($route) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors
                           {{ $active ? 'bg-siarc-green text-white shadow-[0_6px_16px_-6px_rgba(21,122,67,.8)]' : 'text-[#B7C6BC] hover:bg-white/8 hover:text-white' }}">
                            <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] shrink-0" style="stroke-width:{{ $active ? 2.1 : 1.8 }}"></i>
                            <span class="truncate">{{ $labelIdx === 0 ? $lblFr : $lblEn }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endforeach
        </nav>

        {{-- heritage statue + quote (matches the design) --}}
        <div class="px-4 pb-5 mt-auto">
            <div class="relative rounded-2xl overflow-hidden bg-gradient-to-br from-[#12522B] to-[#04240F] border border-white/10 min-h-[116px] flex items-center">
                <p class="text-[12.5px] leading-snug text-white/90 font-medium relative z-10 px-4 py-4 max-w-[62%]">Entre tradition et modernité : comment stimuler l'innovation artisanale ?</p>
                <img src="{{ asset('images/siarc/sidebar-statue.png') }}" alt="" class="absolute right-0 bottom-0 h-[116px] w-auto object-contain object-bottom">
            </div>
        </div>
    </aside>

    {{-- ══════════════════ MAIN ══════════════════ --}}
    <div class="flex-1 min-w-0 flex flex-col">
        {{-- topbar --}}
        <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-[#ECEAE3]">
            <div class="h-[68px] px-4 sm:px-7 flex items-center gap-4">
                <button id="si-burger" class="lg:hidden w-9 h-9 -ml-1 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center shrink-0">
                    <i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i>
                </button>
                <h1 class="font-display text-[21px] sm:text-[23px] font-bold text-[#1D1B16] truncate">{{ $sTitle ?? 'Tableau de bord' }}</h1>

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
                        <span class="absolute -top-0.5 -right-0.5 min-w-[17px] h-[17px] px-1 rounded-full bg-siarc-red text-white text-[10px] font-bold flex items-center justify-center">12</span>
                    </button>
                    <div class="flex items-center gap-2.5 pl-1">
                        <span class="w-9 h-9 rounded-full bg-siarc-green text-white text-[13px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($uName,0,1)) }}</span>
                        <div class="hidden sm:block leading-tight">
                            <p class="text-[13px] font-semibold text-[#1D1B16]">{{ $uName }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $uRole }}</p>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-[#B4B0A6] hidden sm:block"></i>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 sm:px-7 py-6">
            @yield('content')
        </main>

        {{-- MINPMEESA / MADE IN CAMEROON footer (matches the design) --}}
        <footer class="border-t border-[#ECEAE3] bg-white">
            <div class="px-4 sm:px-7 py-4 flex flex-col lg:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 lg:w-[34%]">
                    <span class="w-10 h-10 rounded-full bg-[#E7F1EA] flex items-center justify-center shrink-0"><i data-lucide="landmark" class="w-5 h-5 text-siarc-green"></i></span>
                    <div class="leading-tight text-[10.5px] text-[#8A857A] max-w-[280px]">
                        <p class="font-bold text-[#3B382F] text-[11.5px]">MINPMEESA</p>
                        <p>Ministère des Petites et Moyennes Entreprises, de l'Économie Sociale et de l'Artisanat</p>
                    </div>
                </div>
                <div class="text-center text-[11px] text-[#8A857A] leading-tight">
                    <p>SIARC 2026 – {{ $isFr ? 'Salon International de l\'Artisanat du Cameroun' : 'Cameroon International Craft Fair' }}</p>
                    <p>© 2026 {{ $isFr ? 'Tous droits réservés' : 'All rights reserved' }}</p>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        @foreach($socials as [$net,$path])
                        <a href="{{ $home }}" aria-label="{{ $net }}" class="w-7 h-7 rounded-full bg-[#F1F1EF] hover:bg-siarc-green hover:text-white text-[#8A857A] flex items-center justify-center transition-colors">
                            @if($net === 'instagram')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="3.6"/><circle cx="17" cy="7" r="1.1" fill="currentColor" stroke="none"/></svg>
                            @elseif($net === 'linkedin')
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6.2 9H3.5v11h2.7V9ZM4.85 4.5A1.6 1.6 0 1 0 4.85 7.7a1.6 1.6 0 0 0 0-3.2ZM20.5 20v-6c0-3.2-1.7-4.7-4-4.7-1.8 0-2.6 1-3.1 1.7V9H10.7v11h2.7v-6.1c0-1.6.9-2.2 1.9-2.2s1.8.7 1.8 2.2V20h2.7Z"/></svg>
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="{{ $path }}"/></svg>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-2.5 lg:w-[34%] justify-end">
                    <div class="text-right text-[10.5px] text-[#8A857A] leading-tight">
                        <p class="font-bold text-[#3B382F] text-[11.5px]">SIARC 2026</p>
                        <p>{{ $isFr ? 'MADE IN CAMEROON' : 'MADE IN CAMEROON' }}<br>CONNECTING AFRICA TO THE WORLD</p>
                    </div>
                    <img src="{{ asset('images/siarc/africa-emblem.png') }}" alt="" class="w-12 h-auto shrink-0">
                </div>
            </div>
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
@stack('scripts')
</body>
</html>
