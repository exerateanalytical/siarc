@php
    // ══ Security Operations — Medical Emergency (verbatim from the approved design) ══
    $stats = [
        ['plus-square','#C0010C','#FDE8E8','Total Emergencies (Today)','16',['up','#C0010C','3 from yesterday']],
        ['activity','#C97A16','#FDF3E0','Active Emergencies','5',['t','#C97A16','Currently being handled']],
        ['file-check-2','#3565DE','#E8EFFB','Resolved (Today)','11',['up','#157A43','4 from yesterday']],
        ['users-round','#157A43','#E2F3E8','Patients Treated','18',['up','#157A43','5 from yesterday']],
        ['clock','#7C4FE0','#F0EAFB','Avg. Response Time','6m 24s',['down','#157A43','1m 12s from yesterday']],
        ['heart-pulse','#C0010C','#FDE8E8','Critical Cases','2',['t','#C0010C','Requires close monitoring']],
    ];
    $caseHref = route('siarc.admin.secops.medical.case', ['lang' => $lang]);
    // [id, patient, pSub, type, tIc, tc, loc, locSub, status, stC, at, ago, team, teamSub, face]
    $rows = [
        ['ME-2026-0016','Koffi A.','Male, 45 years','Cardiac Issue','heart','#C0010C','Pavilion A','Main Entrance','Active','#C0010C','Today, 10:18 AM','6 min ago','Medical Team 1','Dr. Ngoa S.',1],
        ['ME-2026-0015','Marie N.','Female, 29 years','Breathing Difficulty','wind','#C97A16','Food Court','Near Stage','Active','#C0010C','Today, 10:12 AM','12 min ago','Medical Team 2','Dr. Essomba J.',2],
        ['ME-2026-0014','Mohamed T.','Male, 63 years','High Blood Pressure','droplet','#C0010C','Pavilion B','VIP Lounge','In Progress','#C97A16','Today, 09:58 AM','26 min ago','Medical Team 1','Nurse A. M.',3],
        ['ME-2026-0013','Amina B.','Female, 12 years','Fainting','zap','#7C4FE0','Central Square','Information Desk','Resolved','#157A43','Today, 09:30 AM','54 min ago','Medical Team 3','Dr. Biloa P.',4],
        ['ME-2026-0012','Jean P.','Male, 34 years','Injury','bandage','#157A43','Pavilion C','Craft Section','Resolved','#157A43','Today, 08:45 AM','1h 39m ago','Medical Team 2','Nurse M. D.',5],
    ];
@endphp

<div class="mb-5">
    <h1 class="text-[24px] font-bold text-[#131313] tracking-tight">SECURITY OPERATIONS</h1>
    <p class="text-[14px] font-semibold text-[#3B382F] mt-0.5">Medical Emergency</p>
    <p class="text-[12px] text-[#8A857A]">Real-time monitoring and management of medical emergencies during the event.</p>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$label,$value,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4">
        <span class="flex items-start justify-between">
            <span class="text-[11px] text-[#8A857A] leading-snug">{{ $label }}</span>
            <span class="w-9 h-9 -mt-1 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
        </span>
        <span class="block text-[20px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
        <span class="text-[10.5px] font-semibold inline-flex items-center gap-0.5" style="color:{{ $sub[1] }}">@if($sub[0] !== 't')<i data-lucide="arrow-{{ $sub[0] }}" class="w-3 h-3"></i>@endif{{ $sub[2] }}</span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_460px] gap-5 items-start mb-5">
    {{-- ══ EMERGENCY LIST ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <p class="px-5 pt-4 text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Medical Emergency List</p>
        <div class="px-5 py-3 flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#meScope" placeholder="Search by ID, patient name, location..." class="w-full text-[12px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach(['All Status','All Types','All Locations'] as $f)
            <select data-filter-select="#meScope" class="text-[11.5px] rounded-lg border border-[#EFEDE6] px-2.5 py-2 bg-white text-[#3B382F]"><option>{{ $f }}</option>@foreach(['active','in progress','resolved'] as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach</select>
            @endforeach
            <button data-toast="Advanced filters — coming soon" class="siarc-btn text-[11.5px] text-[#3B382F] border border-[#EFEDE6] px-2.5 py-2 rounded-lg bg-white"><i data-lucide="filter" class="w-3.5 h-3.5 text-[#8A857A]"></i>Filters</button>
            <a href="{{ $caseHref }}" class="siarc-btn siarc-btn-green text-[11.5px] px-3 py-2 rounded-lg"><i data-lucide="plus" class="w-3.5 h-3.5"></i>New Emergency</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[740px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['ID','PATIENT','TYPE','LOCATION','STATUS','REPORTED AT','RESPONSE TEAM','ACTIONS'] as $h)
                        <th class="px-3.5 py-3 text-[9.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="meScope">
                    @foreach($rows as [$id,$patient,$pSub,$type,$tIc,$tc,$loc,$locSub,$status,$stC,$at,$ago,$team,$teamSub,$face])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($status) }}" data-filter-text="{{ $id }} {{ $patient }} {{ $type }} {{ $loc }} {{ $team }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-3.5 py-3 text-[11px] font-medium text-[#3B382F] whitespace-nowrap">{{ $id }}</td>
                        <td class="px-3.5 py-3">
                            <span class="flex items-center gap-2.5">
                                <img src="{{ asset('images/siarc/secops-pat-'.$face.'.png') }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                <span><span class="block text-[12px] font-semibold text-[#131313]">{{ $patient }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $pSub }}</span></span>
                            </span>
                        </td>
                        <td class="px-3.5 py-3"><span class="inline-flex items-center gap-1.5 text-[11.5px] font-medium" style="color:{{ $tc }}"><span class="w-6 h-6 rounded-lg flex items-center justify-center" style="background:{{ $tc }}18"><i data-lucide="{{ $tIc }}" class="w-3.5 h-3.5" style="color:{{ $tc }}"></i></span>{{ $type }}</span></td>
                        <td class="px-3.5 py-3 text-[11.5px] text-[#3B382F]">{{ $loc }}<br><span class="text-[10px] text-[#8A857A]">{{ $locSub }}</span></td>
                        <td class="px-3.5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $stC }};background:{{ $stC }}18">{{ $status }}</span></td>
                        <td class="px-3.5 py-3 text-[11px] text-[#3B382F]">{{ $at }}<br><span class="text-[10px] text-[#8A857A]">{{ $ago }}</span></td>
                        <td class="px-3.5 py-3 text-[11px]"><span class="font-semibold text-[#3565DE]">{{ $team }}</span><br><span class="text-[10px] text-[#8A857A]">{{ $teamSub }}</span></td>
                        <td class="px-3.5 py-3">
                            <span class="flex items-center gap-1">
                                <a href="{{ $caseHref }}" class="w-6 h-6 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-3.5 h-3.5 text-[#8A857A]"></i></a>
                                <button data-toast="Context menu — coming soon" class="w-6 h-6 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="more-vertical" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div data-page="#meScope" data-page-size="10" class="px-5 py-3 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[11.5px] text-[#8A857A]">Showing 1 to 5 of 16 emergencies</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></button>
                <button data-page-num="1" class="is-active w-7 h-7 rounded-lg text-[11.5px] font-semibold flex items-center justify-center">1</button>
                @foreach(['2','3','4'] as $p)<button data-page-num="{{ $p }}" class="w-7 h-7 rounded-lg border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] flex items-center justify-center">{{ $p }}</button>@endforeach
                <button data-page-next class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT: live map ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Emergency Locations (Live)</p>
            <button data-toast="Full map view — coming soon" class="siarc-btn text-[11.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] px-3 py-1.5 rounded-lg bg-white">View Full Map</button>
        </div>
        <img src="{{ asset('images/siarc/secops-map-medical.png') }}" alt="" class="w-full h-auto rounded-xl border border-[#EFEDE6]">
        <div class="flex items-center gap-4 mt-3 text-[11px] text-[#3B382F]">
            @foreach([['Active (5)','#C0010C'],['In Progress (3)','#C97A16'],['Resolved (8)','#157A43']] as [$lbl,$c])
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $c }}"></span>{{ $lbl }}</span>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[440px_1fr_400px] gap-5 items-start">
    {{-- ══ CURRENT ACTIVE EMERGENCY ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Current Active Emergency</p>
            <a href="{{ $caseHref }}" class="text-[11.5px] font-semibold text-[#157A43]">View Details</a>
        </div>
        <div class="rounded-xl bg-[#FDF7F7] border border-[#F5CFCF] p-4">
            <p class="flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-full bg-[#FDE8E8] flex items-center justify-center"><i data-lucide="heart" class="w-5 h-5 text-[#C0010C]"></i></span>
                <span><span class="flex items-center gap-2 text-[12px] font-medium text-[#8A857A]">ME-2026-0016 <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">Active</span></span><span class="block text-[15px] font-bold text-[#131313]">Cardiac Issue</span></span>
            </p>
            <p class="mt-1.5 text-[11.5px] text-[#3B382F] flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#8A857A]"></i>Pavilion A - Main Entrance</p>
            <p class="text-[11px] text-[#8A857A] flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5"></i>Reported: Today, 10:18 AM (6 min ago)</p>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-4 text-[11.5px]">
            <span><span class="block text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-1">Patient</span><span class="flex items-center gap-2"><img src="{{ asset('images/siarc/secops-pat-1.png') }}" class="w-7 h-7 rounded-full object-cover" alt=""><span><b class="text-[#131313]">Koffi A.</b><br><span class="text-[#8A857A] text-[10px]">Male, 45 years<br>ID: VIS-000366</span></span></span></span>
            <span><span class="block text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-1">Condition</span><b class="text-[#C0010C]">Critical</b><br><span class="text-[#3B382F] text-[10.5px]">Chest pain, difficulty breathing</span></span>
            <span><span class="block text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-1">Response Team</span><b class="text-[#131313]">Medical Team 1</b><br><span class="text-[#3B382F] text-[10.5px]">Dr. Ngoa S.<br><b class="text-[#157A43]">ETA: 2 min</b></span></span>
        </div>
        <div class="grid grid-cols-2 gap-2.5 mt-4">
            <a href="{{ $caseHref }}" class="siarc-btn justify-center text-[12px] font-bold text-white rounded-lg px-3 py-2.5" style="background:#C0010C">View Full Case</a>
            <button data-toast="Status update — coming soon" class="siarc-btn justify-center text-[12px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2.5 hover:bg-[#FDF3F3]">Update Status</button>
        </div>
    </div>

    {{-- ══ RESPONSE TIME CHART ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Average Response Time (Today)</p>
            <span class="text-[11px] text-[#3B382F] border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 flex items-center gap-1">Today<i data-lucide="chevron-down" class="w-3 h-3 text-[#8A857A]"></i></span>
        </div>
        <p class="text-[26px] font-bold text-[#131313]">6m 24s</p>
        <p class="text-[10.5px] font-semibold text-[#157A43] inline-flex items-center gap-0.5 mb-2"><i data-lucide="arrow-down" class="w-3 h-3"></i>1m 12s from yesterday</p>
        <svg viewBox="0 0 480 130" class="w-full h-auto">
            <defs><linearGradient id="meArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#157A43" stop-opacity=".25"/><stop offset="1" stop-color="#157A43" stop-opacity="0"/></linearGradient></defs>
            @foreach([['15m',16],['10m',52],['5m',88],['0m',124]] as [$lbl,$y])
            <line x1="30" y1="{{ $y }}" x2="480" y2="{{ $y }}" stroke="#F2F1EC"/><text x="24" y="{{ $y + 3 }}" font-size="8.5" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>
            @endforeach
            <path d="M30 100 L67 92 104 84 141 60 178 42 215 38 252 46 289 60 326 76 363 88 400 96 437 100 474 104 L474 130 30 130 Z" fill="url(#meArea)"/>
            <path d="M30 100 L67 92 104 84 141 60 178 42 215 38 252 46 289 60 326 76 363 88 400 96 437 100 474 104" fill="none" stroke="#157A43" stroke-width="2.2"/>
            @foreach([[30,100],[104,84],[178,42],[252,46],[326,76],[400,96],[474,104]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="2.6" fill="#fff" stroke="#157A43" stroke-width="2"/>@endforeach
            @foreach([['00:00',30],['04:00',104],['08:00',178],['12:00',252],['16:00',326],['20:00',400],['24:00',474]] as [$lbl,$x])<text x="{{ $x }}" y="129" font-size="8.5" fill="#B0AB9F" text-anchor="middle">{{ $lbl }}</text>@endforeach
        </svg>
        <p class="text-center text-[10.5px] text-[#8A857A] mt-1 flex items-center justify-center gap-1.5"><span class="w-4 h-[3px] rounded-full bg-[#157A43]"></span>Response Time (min)</p>
    </div>

    {{-- ══ RECENT ACTIVITY ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Recent Activity</p>
            <button data-toast="Full activity log — coming soon" class="text-[11.5px] font-semibold text-[#157A43]">View All Activity</button>
        </div>
        <div class="space-y-3">
            @foreach([['heart','#C0010C','10:18 AM','New emergency reported','Cardiac issue at Pavilion A','Active','#C0010C'],['wind','#C97A16','10:12 AM','New emergency reported','Breathing difficulty at Food Court','Active','#C0010C'],['droplet','#C97A16','09:58 AM','Emergency status updated','High blood pressure at Pavilion B','In Progress','#C97A16'],['check-circle-2','#157A43','09:30 AM','Emergency resolved','Fainting case at Central Square','Resolved','#157A43'],['check-circle-2','#157A43','08:45 AM','Emergency resolved','Injury case at Pavilion C','Resolved','#157A43']] as [$ic,$c,$time,$t,$s,$st,$sc])
            <div class="flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5" style="color:{{ $c }}"></i></span>
                <span class="min-w-0 flex-1"><span class="block text-[10.5px] font-semibold text-[#8A857A]">{{ $time }}</span><span class="block text-[12px] font-semibold text-[#131313] leading-snug">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $s }}</span></span>
                <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md shrink-0" style="color:{{ $sc }};background:{{ $sc }}18">{{ $st }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-5 rounded-xl bg-[#EFF4FD] border border-[#CBDCF8] px-5 py-3.5 flex items-center justify-between gap-4">
    <p class="flex items-center gap-2.5 text-[12.5px] text-[#1D3E7E]"><i data-lucide="info" class="w-4 h-4 text-[#3565DE] shrink-0"></i>In case of a medical emergency, contact the nearest medical team or call the control center immediately.</p>
    <button data-toast="Control center call — coming soon" class="shrink-0 siarc-btn text-[12.5px] font-bold text-white rounded-lg px-4 py-2.5" style="background:#3565DE"><i data-lucide="phone" class="w-4 h-4"></i>Call Control Center</button>
</div>
