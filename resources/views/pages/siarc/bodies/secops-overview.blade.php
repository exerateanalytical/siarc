@php
    // ══ Security Operations — Overview (verbatim from the approved design) ══
    $stats = [
        ['shield',        '#C0010C', '#FDE8E8', 'Total Incidents',    '28',    ['up', '12% from yesterday']],
        ['alert-triangle','#C97A16', '#FDF3E0', 'Active Incidents',   '7',     ['link', 'View all']],
        ['users',         '#157A43', '#E2F3E8', 'Security Personnel', '156',   ['t', 'On duty']],
        ['video',         '#3565DE', '#E8EFFB', 'CCTV Cameras',       '128',   ['t', 'Online 96%']],
        ['users-round',   '#7C4FE0', '#F0EAFB', 'Visitors Today',     '4,782', ['up', '8% from yesterday']],
        ['shield-check',  '#157A43', '#E2F3E8', 'Security Score',     '92%',   ['bar', 'Good']],
    ];
    $tiles = [
        ['users-round','Crowd Alerts','12','#C0010C','#FDE8E8'], ['heart-pulse','Medical Emergency','6','#157A43','#E2F3E8'],
        ['user-search','Lost Persons','3','#3565DE','#E8EFFB'], ['shield','Police Requests','4','#7C4FE0','#F0EAFB'],
        ['flame','Fire Alerts','2','#C97A16','#FDF3E0'], ['circle-ellipsis','Other Incidents','1','#8A857A','#F1F0EB'],
    ];
    $active = [
        ['users-round','#C0010C','#FDE8E8','Crowd Congestion','Pavilion A - Main Entrance','High','#C0010C','10:20 AM','2 min ago'],
        ['heart-pulse','#157A43','#E2F3E8','Medical Emergency','Hall 3 - Near Food Court','Medium','#C97A16','10:15 AM','7 min ago'],
        ['user-search','#3565DE','#E8EFFB','Lost Child','Pavilion B - Craft Section','Low','#3565DE','10:05 AM','17 min ago'],
        ['alert-triangle','#C97A16','#FDF3E0','Suspicious Activity','Parking Area P2','Medium','#C97A16','09:58 AM','24 min ago'],
        ['flame','#C0010C','#FDE8E8','Fire Alarm','Hall 1 - Electrical Room','High','#C0010C','09:45 AM','37 min ago'],
        ['shield','#3565DE','#E8EFFB','Police Assistance','VIP Parking Zone','Low','#3565DE','09:30 AM','52 min ago'],
    ];
    $lostHref = route('siarc.admin.secops.lost', ['lang' => $lang]);
@endphp

<div class="mb-5">
    <h1 class="text-[24px] font-bold text-[#131313] tracking-tight">SECURITY OPERATIONS</h1>
    <p class="text-[12.5px] text-[#8A857A]">Real-time overview of security activities and incident management</p>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$label,$value,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4">
        <span class="flex items-start justify-between">
            <span class="text-[11px] text-[#8A857A] leading-snug">{{ $label }}</span>
            <span class="w-9 h-9 -mt-1 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
        </span>
        <span class="block text-[22px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
        @if($sub[0] === 'up')
            <span class="text-[10.5px] font-semibold text-[#157A43] inline-flex items-center gap-0.5"><i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $sub[1] }}</span>
        @elseif($sub[0] === 'link')
            <a href="{{ route('siarc.admin.secops.incidents', ['lang' => $lang]) }}" class="text-[10.5px] font-semibold text-[#C97A16] inline-flex items-center gap-0.5">{{ $sub[1] }} <i data-lucide="arrow-right" class="w-3 h-3"></i></a>
        @elseif($sub[0] === 'bar')
            <span class="flex items-center gap-2"><span class="text-[10.5px] font-semibold text-[#157A43]">{{ $sub[1] }}</span><span class="flex-1 h-[5px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full bg-[#157A43]" style="width:92%"></span></span></span>
        @else
            <span class="text-[10.5px] text-[#8A857A]">{{ $sub[1] }}</span>
        @endif
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_1fr_300px] gap-5 items-start mb-5">
    {{-- ══ INCIDENTS OVERVIEW (chart + tiles) ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Incidents Overview</p>
            <span class="text-[11.5px] text-[#3B382F] border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 flex items-center gap-1">Today<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></span>
        </div>
        <svg viewBox="0 0 400 130" class="w-full h-auto">
            <defs><linearGradient id="secArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#157A43" stop-opacity=".28"/><stop offset="1" stop-color="#157A43" stop-opacity="0"/></linearGradient></defs>
            @foreach([26,52,78,104] as $y)<line x1="0" y1="{{ $y }}" x2="400" y2="{{ $y }}" stroke="#F2F1EC"/>@endforeach
            <path d="M0 118 L33 108 66 96 100 92 133 78 166 82 200 30 233 55 266 70 300 96 333 104 366 92 400 84 L400 130 0 130 Z" fill="url(#secArea)"/>
            <path d="M0 118 L33 108 66 96 100 92 133 78 166 82 200 30 233 55 266 70 300 96 333 104 366 92 400 84" fill="none" stroke="#157A43" stroke-width="2.2"/>
            @foreach([[0,118],[66,96],[133,78],[200,30],[266,70],[333,104],[400,84]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="3" fill="#fff" stroke="#157A43" stroke-width="2"/>@endforeach
        </svg>
        <div class="flex justify-between text-[10px] text-[#B0AB9F] mt-1 mb-4"><span>00:00</span><span>04:00</span><span>08:00</span><span>12:00</span><span>12:00</span><span>20:00</span><span>24:00</span></div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            @foreach($tiles as [$ic,$lbl,$v,$c,$bg])
            <span class="flex items-center gap-2.5 rounded-lg border border-[#EFEDE6] px-3 py-2.5">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $bg }}"><i data-lucide="{{ $ic }}" class="w-4 h-4" style="color:{{ $c }}"></i></span>
                <span><span class="block text-[11px] text-[#8A857A] leading-tight">{{ $lbl }}</span><span class="block text-[16px] font-bold text-[#131313]">{{ $v }}</span></span>
            </span>
            @endforeach
        </div>
    </div>

    {{-- ══ ACTIVE INCIDENTS ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Active Incidents</p>
            <a href="{{ route('siarc.admin.secops.incidents', ['lang' => $lang]) }}" class="text-[11.5px] font-semibold text-[#157A43] inline-flex items-center gap-1">View all <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>
        <div class="space-y-1">
            @foreach($active as [$ic,$c,$bg,$title,$loc,$sev,$sc,$time,$ago])
            <a href="{{ route('siarc.admin.secops.incidents', ['lang' => $lang]) }}" class="flex items-center gap-3 rounded-lg px-2 py-2.5 hover:bg-[#FBFAF6]">
                <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $bg }}"><i data-lucide="{{ $ic }}" class="w-4 h-4" style="color:{{ $c }}"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2"><span class="text-[12.5px] font-semibold text-[#131313] truncate">{{ $title }}</span><span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md shrink-0" style="color:{{ $sc }};background:{{ $sc }}18">{{ $sev }}</span></span>
                    <span class="block text-[11px] text-[#8A857A] truncate">{{ $loc }}</span>
                </span>
                <span class="text-right shrink-0"><span class="block text-[11px] font-semibold text-[#3B382F]">{{ $time }}</span><span class="block text-[10px] text-[#B0AB9F]">{{ $ago }}</span></span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C5BA] shrink-0"></i>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ══ QUICK ACTIONS + SECURITY STATUS ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Quick Actions</p>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([['alert-triangle','Report Incident','#C0010C','siarc.admin.secops.incidents'],['user-search','Lost Person','#3565DE','siarc.admin.secops.lost'],['heart-pulse','Medical Emergency','#157A43','siarc.admin.secops.medical'],['shield','Police Request','#7C4FE0','siarc.admin.secops.police.case'],['flame','Fire Alert','#C0010C','siarc.admin.secops.fire'],['megaphone','Public Announcement','#3565DE',null]] as [$ic,$lbl,$c,$rt])
                @if($rt)
                <a href="{{ route($rt, ['lang' => $lang]) }}" class="rounded-lg border border-[#EFEDE6] px-2.5 py-3 text-center hover:bg-[#FBFAF6]">
                    <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[10.5px] font-semibold text-[#3B382F] leading-tight">{{ $lbl }}</span>
                </a>
                @else
                <button data-toast="{{ $lbl }} — coming soon" class="rounded-lg border border-[#EFEDE6] px-2.5 py-3 text-center hover:bg-[#FBFAF6]">
                    <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[10.5px] font-semibold text-[#3B382F] leading-tight">{{ $lbl }}</span>
                </button>
                @endif
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Security Status</p>
            <div class="space-y-2.5">
                @foreach([['shield-check','Perimeter Security','Secure'],['key-round','Access Points','Normal'],['video','CCTV System','Online'],['flame','Fire System','Normal'],['heart-pulse','Medical Stations','Ready'],['radio','Communication','Normal']] as [$ic,$k,$v])
                <p class="flex items-center justify-between text-[12px]">
                    <span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-[#8A857A]"></i>{{ $k }}</span>
                    <span class="flex items-center gap-1.5 font-semibold text-[#157A43]">{{ $v }}<span class="w-2 h-2 rounded-full bg-[#157A43]"></span></span>
                </p>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[300px_1fr_340px] gap-5 items-start">
    {{-- ══ INCIDENTS BY LOCATION ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Incidents by Location</p>
            <span class="text-[11px] text-[#3B382F] border border-[#EFEDE6] rounded-lg px-2 py-1 flex items-center gap-1">Today<i data-lucide="chevron-down" class="w-3 h-3 text-[#8A857A]"></i></span>
        </div>
        <div class="space-y-3">
            @foreach([['Pavilion A',8,'#C0010C'],['Pavilion B',6,'#C97A16'],['Food Court',4,'#E6B201'],['Main Entrance',3,'#157A43'],['Parking Area',3,'#3565DE'],['Other Areas',4,'#B0AB9F']] as [$lbl,$v,$c])
            <div class="flex items-center gap-3">
                <span class="w-[92px] text-[11.5px] text-[#3B382F] shrink-0">{{ $lbl }}</span>
                <span class="flex-1 h-[7px] rounded-full bg-[#F1F0EB] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $v * 12 }}%;background:{{ $c }}"></span></span>
                <span class="text-[11.5px] font-semibold text-[#3B382F] w-4 text-right">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══ LIVE INCIDENTS MAP ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Live Incidents Map</p>
        <div class="relative rounded-xl overflow-hidden border border-[#EFEDE6]">
            <img src="{{ asset('images/siarc/secops-map-overview.png') }}" alt="" class="w-full h-auto block">
            <button data-toast="Full map view — coming soon" class="absolute bottom-3 right-3 rounded-lg bg-white/95 border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] px-3 py-1.5 siarc-shadow">View Full Map</button>
        </div>
    </div>

    {{-- ══ RECENT ACTIVITIES ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Recent Activities</p>
            <button data-toast="Full activity log — coming soon" class="text-[11.5px] font-semibold text-[#157A43] inline-flex items-center gap-1">View all <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
        </div>
        <div class="space-y-3">
            @foreach([['alert-triangle','#C0010C','#FDE8E8','10:20 AM','Crowd alert reported at Pavilion A','by Officer John D.'],['heart-pulse','#157A43','#E2F3E8','10:15 AM','Medical team dispatched to Hall 3','by Nurse Marie K.'],['user-search','#3565DE','#E8EFFB','10:05 AM','Lost child reported at Pavilion B','by Visitor'],['users','#8A857A','#F1F0EB','09:58 AM','Security patrol assigned to Parking P2','by Supervisor Paul'],['flame','#C97A16','#FDF3E0','09:45 AM','Fire alarm triggered in Hall 1','by System']] as [$ic,$c,$bg,$time,$t,$by])
            <div class="flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $bg }}"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5" style="color:{{ $c }}"></i></span>
                <span class="min-w-0"><span class="block text-[11px] font-semibold text-[#8A857A]">{{ $time }}</span><span class="block text-[12px] font-medium text-[#131313] leading-snug">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $by }}</span></span>
            </div>
            @endforeach
        </div>
    </div>
</div>
