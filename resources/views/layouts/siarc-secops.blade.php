@php
    use Illuminate\Support\Facades\Route as R;
    $lang = request()->query('lang', request()->cookie('lang', 'fr'));
    $lang = in_array($lang, ['fr','en']) ? $lang : 'fr';
    $navHref = fn($route) => R::has($route) ? route($route, ['lang'=>$lang]) : route('siarc.admin.secops.overview', ['lang'=>$lang]);

    // Chrome per route (active item + accent colour of the active pill).
    $secChrome = [
        'siarc.admin.secops.overview'       => ['Overview',          '#157A43'],
        'siarc.admin.secops.crowd'          => ['Crowd Alerts',      '#157A43'],
        'siarc.admin.secops.incidents'      => ['Incidents',         '#157A43'],
        'siarc.admin.secops.lost'           => ['Lost Persons',      '#157A43'],
        'siarc.admin.secops.lost.case'      => ['Lost Persons',      '#157A43'],
        'siarc.admin.secops.medical'        => ['Medical Emergency', '#157A43'],
        'siarc.admin.secops.medical.case'   => ['Medical Emergency', '#157A43'],
        'siarc.admin.secops.police.case'    => ['Police Requests',   '#3565DE'],
        'siarc.admin.secops.fire'           => ['Fire Alerts',       '#C0010C'],
        'siarc.admin.secops.fire.case'      => ['Fire Alerts',       '#C0010C'],
    ];
    [$secActive, $secAccent] = $secChrome[request()->route()?->getName() ?? ''] ?? ['Overview', '#157A43'];

    $secNav = [
        ['NAVIGATION', [
            ['Dashboard',      'layout-dashboard', 'siarc.admin.dashboard'],
            ['Accreditation',  'id-card',          'siarc.admin.accred.templates'],
            ['Exhibitors',     'store',            'siarc.admin.exhibitors'],
            ['Pavilions',      'landmark',         'siarc.admin.pavilions'],
            ['Visitors',       'users-round',      'siarc.admin.visitors'],
            ['Programme',      'calendar-days',    'siarc.admin.programme'],
            ['Communications', 'megaphone',        'messages.inbox'],
        ]],
        ['SECURITY OPERATIONS', [
            ['Overview',          'shield-check',  'siarc.admin.secops.overview'],
            ['Crowd Alerts',      'users-round',   'siarc.admin.secops.crowd'],
            ['Incidents',         'alert-triangle','siarc.admin.secops.incidents'],
            ['Lost Persons',      'user-search',   'siarc.admin.secops.lost'],
            ['Medical Emergency', 'heart-pulse',   'siarc.admin.secops.medical'],
            ['Police Requests',   'shield',        'siarc.admin.secops.police.case'],
            ['Fire Alerts',       'flame',         'siarc.admin.secops.fire'],
        ]],
        ['SECURITY MANAGEMENT', [
            ['Access Control',     'key-round',    'siarc.admin.accred.rules'],
            ['CCTV Monitoring',    'video',        'siarc.admin.live'],
            ['Security Personnel', 'users',        'siarc.admin.secops.overview'],
            ['Assets & Equipment', 'package',      'siarc.admin.secops.overview'],
        ]],
        ['REPORTS & ANALYTICS', [
            ['Reports',   'file-text',   'siarc.admin.reports'],
            ['Analytics', 'bar-chart-3', 'siarc.admin.analytics'],
        ]],
        ['SYSTEM', [
            ['System Settings', 'settings',    'siarc.admin.mode'],
            ['Users & Roles',   'users-round', 'admin.users'],
            ['Audit Logs',      'history',     'admin.audit-log'],
        ]],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ($sTitle ?? 'Security Operations') }} — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = { theme: { extend: {
            colors: { siarc:{green:'#157A43',dark:'#0B3A1E',darker:'#042B15',gold:'#E6B201',ochre:'#C97A16',red:'#C0010C'} },
            fontFamily: { sans:['Poppins','system-ui','sans-serif'] },
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
        #si-side.open{display:flex;position:fixed;inset:0 auto 0 0;width:210px;z-index:60}
        @media (min-width:1024px){#si-side,#si-side.open{display:flex;position:sticky;top:0;height:100vh;width:210px}}
        .sec-item{display:flex;align-items:center;gap:10px;padding:8px 14px;font-size:12.5px;font-weight:500;color:#C9D6CD;border-radius:10px;margin:0 8px;transition:color .15s,background .15s}
        .sec-item:hover{color:#fff;background:rgba(255,255,255,.06)}
    </style>
</head>
<body class="bg-[#F6F5F2] text-[#1D1B16] antialiased">
<div class="flex min-h-screen">

    {{-- ══ SIDEBAR (210px, dark green, English sections) ══ --}}
    <aside id="si-side" class="siarc-scroll flex-col shrink-0 text-white overflow-y-auto" style="background:linear-gradient(180deg,#06331B 0%,#04250F 100%)">
        <div class="px-4 pt-4 pb-3">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => true])
        </div>
        <div class="mx-4 mb-3 flex items-center gap-2 border-y border-white/10 py-2.5">
            <span class="inline-block w-[22px] h-[15px] rounded-[2px] shrink-0" style="background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)"></span>
            <span class="text-[10.5px] font-semibold tracking-wide text-white/90">YAOUNDÉ, CAMEROUN</span>
        </div>

        @foreach($secNav as [$section,$items])
            <p class="px-4 mt-3 mb-1.5 text-[9.5px] font-bold tracking-[0.16em] {{ $section === 'SECURITY OPERATIONS' ? 'text-[#35C46A]' : 'text-white/35' }}">{{ $section }}</p>
            <nav class="space-y-0.5">
                @foreach($items as [$lbl,$icon,$route])
                    @php $on = $section === 'SECURITY OPERATIONS' && $lbl === $secActive; @endphp
                    <a href="{{ $navHref($route) }}" class="sec-item {{ $on ? 'text-white font-semibold' : '' }}" @if($on) style="background:{{ $secAccent }};box-shadow:0 6px 16px -6px {{ $secAccent }}cc" @endif>
                        <i data-lucide="{{ $icon }}" class="w-[15px] h-[15px] shrink-0" style="stroke-width:1.8"></i>{{ $lbl }}
                    </a>
                @endforeach
            </nav>
        @endforeach

        <div class="mt-auto">
            <img src="{{ asset('images/siarc/secops-art.png') }}" alt="" class="w-full block">
            <button data-toast="Sidebar collapse — coming soon" class="w-full flex items-center gap-2 px-4 py-3 text-[12px] text-white/70 hover:text-white border-t border-white/10"><i data-lucide="chevrons-left" class="w-4 h-4"></i>Collapse</button>
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="sticky top-0 z-40 bg-white border-b border-[#ECEAE3]">
            <div class="h-[60px] px-5 sm:px-7 flex items-center gap-4">
                <button id="si-burger" data-toast="Menu" class="w-9 h-9 -ml-1 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center shrink-0">
                    <i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i>
                </button>
                <p class="hidden md:flex items-center gap-1.5 text-[12.5px] font-bold tracking-wide text-[#131313]">SECURITY OPERATIONS
                    @hasSection('crumb')<i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B0AB9F]"></i><span class="font-medium text-[#8A857A]">@yield('crumb')</span>@endif
                </p>
                <div class="flex items-center gap-3 ml-auto shrink-0">
                    <span class="hidden sm:flex items-center gap-2 rounded-full border border-[#E9E6DE] bg-white px-3.5 py-1.5 text-[12px] font-semibold text-[#3B382F]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>Live<span class="text-[#8A857A] font-medium">10:24:35</span></span>
                    <button data-toast="12 unread notifications — notification center coming soon" class="relative w-9 h-9 rounded-full hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="bell" class="w-[18px] h-[18px] text-[#3B382F]"></i>
                        <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-[16px] px-1 rounded-full bg-siarc-red text-white text-[9.5px] font-bold flex items-center justify-center">12</span>
                    </button>
                    <button data-toast="Help center coming soon" class="w-9 h-9 rounded-full border border-[#E9E6DE] hover:bg-[#F1F1EF] flex items-center justify-center">
                        <i data-lucide="circle-help" class="w-[18px] h-[18px] text-[#3B382F]"></i>
                    </button>
                    <span class="flex items-center gap-2.5 pl-1">
                        <img src="{{ asset('images/siarc/accred-jude.png') }}" alt="" class="w-9 h-9 rounded-full object-cover">
                        <span class="hidden sm:block leading-tight">
                            <span class="block text-[13px] font-semibold text-[#1D1B16]">Jude Nshome</span>
                            <span class="block text-[10.5px] text-[#8A857A]">Security Manager</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-[#B4B0A6] hidden sm:block"></i>
                    </span>
                </div>
            </div>
        </header>

        <main class="flex-1 px-5 sm:px-7 py-6">
            @yield('content')
        </main>

        <footer class="px-5 sm:px-7 py-4 flex items-center justify-between text-[11.5px] text-[#8A857A]">
            <span>© 2026 SIARC – Salon International de l'Artisanat du Cameroun. All rights reserved.</span>
            <span class="flex items-center gap-2.5">Security Operations System v1.0.0
                <i data-lucide="component" class="w-6 h-6 text-[#157A43]"></i>
            </span>
        </footer>
    </div>
</div>
<div id="si-overlay" class="hidden fixed inset-0 bg-black/45 z-50 lg:hidden"></div>
<script>
    lucide.createIcons();
    (function(){
        var b=document.getElementById('si-burger'),s=document.getElementById('si-side'),o=document.getElementById('si-overlay');
        function toggle(){s.classList.toggle('open');o.classList.toggle('hidden');}
        if(b)b.addEventListener('click',function(e){ if(window.innerWidth<1024){e.stopImmediatePropagation();toggle();} });
        if(o)o.addEventListener('click',toggle);
    })();
</script>
<script src="{{ asset('vendor/siarc-ui.js') }}"></script>
@stack('scripts')
</body>
</html>
