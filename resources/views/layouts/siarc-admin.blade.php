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
    // robust href: fall back to '#' if a route name is not registered
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // 13-item SIARC sidebar. Each: [label, icon, route, [active route patterns]]
    $nav = [
        ['Tableau de bord',        'Dashboard',              'layout-dashboard', 'siarc.admin.dashboard', ['siarc.admin.dashboard']],
        ['Exposants',              'Exhibitors',             'store',            'siarc.admin.exhibitors', ['siarc.admin.exhibitor*','siarc.mobile.exhibitor-checkin']],
        ['Pavillons & Stands',     'Pavilions & Stands',     'building-2',       'siarc.admin.pavilions', ['siarc.admin.pavilion*','siarc.admin.stand*','siarc.admin.floorplan']],
        ['Visiteurs',              'Visitors',               'users',            'siarc.admin.visitors', ['siarc.admin.visitor*','siarc.admin.vip']],
        ['Accréditations',         'Accreditation',          'id-card',          'siarc.admin.entry', ['siarc.admin.entry','siarc.admin.badges','siarc.admin.checkin','siarc.mobile.scanner']],
        ['B2B Matchmaking',        'B2B Matchmaking',        'handshake',        'siarc.admin.b2b', ['siarc.admin.b2b','siarc.admin.meeting','siarc.admin.matchmaking']],
        ['Programme & Activités',  'Programme',              'calendar-days',    'siarc.admin.programme', ['siarc.admin.programme','siarc.admin.session']],
        ['Conférences & Ateliers', 'Talks & Workshops',      'presentation',     'siarc.admin.calendar', ['siarc.admin.calendar','siarc.admin.workshop']],
        ['Intervenants',           'Speakers',               'mic',              'siarc.admin.speakers', ['siarc.admin.speaker*']],
        ['Opérations',             'Operations',             'clipboard-list',   'siarc.admin.live', ['siarc.admin.live','siarc.admin.incidents']],
        ['Analytics & Rapports',   'Analytics & Reports',    'bar-chart-3',      'siarc.admin.analytics', ['siarc.admin.analytics','siarc.admin.attendance','siarc.admin.reports']],
        ['Communications',         'Communications',         'megaphone',        'messages.inbox', ['messages.*']],
        ['Paramètres',             'Settings',               'settings',         'admin.settings', ['admin.settings*']],
    ];
    $labelIdx = $isFr ? 0 : 1;
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
    <style>
        body{font-family:'Poppins',system-ui,sans-serif}
        html,body{overflow-x:clip}
        #si-side{display:none}
        #si-side.open{display:flex;position:fixed;inset:0 auto 0 0;width:264px;z-index:60}
        @media (min-width:1024px){#si-side,#si-side.open{display:flex;position:sticky;top:0;height:100vh;width:264px}}
    </style>
</head>
<body class="bg-[#F5F3EE] text-[#1D1B16] antialiased">
<div class="flex min-h-screen">

    {{-- ══════════════════ SIDEBAR ══════════════════ --}}
    <aside id="si-side" class="siarc-scroll flex-col shrink-0 bg-gradient-to-b from-[#0B3A1E] to-[#042B15] text-white overflow-y-auto">
        <div class="px-5 pt-5 pb-4 border-b border-white/10">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => true])
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1">
            @foreach($nav as [$lblFr,$lblEn,$icon,$route,$patterns])
                @php $active = request()->routeIs(...$patterns); @endphp
                <a href="{{ $h($route) }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors
                   {{ $active ? 'bg-siarc-green text-white shadow-[0_6px_16px_-6px_rgba(21,122,67,.8)]' : 'text-[#B7C6BC] hover:bg-white/8 hover:text-white' }}">
                    <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] shrink-0" style="stroke-width:{{ $active ? 2.1 : 1.8 }}"></i>
                    <span class="truncate">{{ ${'labelIdx'} === 0 ? $lblFr : $lblEn }}</span>
                </a>
            @endforeach
        </nav>

        {{-- heritage motif + support card --}}
        <div class="px-4 pb-5 mt-auto">
            <div class="siarc-adire rounded-2xl h-20 mb-3 opacity-80 border border-white/10 flex items-center justify-center">
                <i data-lucide="drama" class="w-9 h-9 text-siarc-gold/80"></i>
            </div>
            <div class="rounded-2xl border border-white/12 bg-white/5 px-4 py-4">
                <p class="text-[13px] font-semibold">{{ $isFr ? 'Besoin d\'aide ?' : 'Need help?' }}</p>
                <p class="text-[11.5px] text-white/60 mt-0.5 mb-3">{{ $isFr ? 'Contactez le support SIARC' : 'Contact SIARC support' }}</p>
                <a href="{{ $h('admin.support') }}" class="siarc-btn w-full justify-center bg-white/10 hover:bg-white/15 text-white text-[12.5px] py-2">
                    <i data-lucide="headphones" class="w-4 h-4"></i>{{ $isFr ? 'Contacter le support' : 'Contact support' }}
                </a>
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
@stack('scripts')
</body>
</html>
