@php
    // ══ Security Operations — Incidents (verbatim from the approved design) ══
    $stats = [
        ['shield','#C0010C','#FDE8E8','Total Incidents','48',['up','#C0010C','18% from yesterday']],
        ['alert-triangle','#C97A16','#FDF3E0','Open Incidents','15',['up','#C97A16','3 from yesterday']],
        ['clock','#E6B201','#FDF6DC','In Progress','9',['t','#8A857A','— No change']],
        ['check-circle-2','#157A43','#E2F3E8','Resolved (Today)','21',['up','#157A43','11 from yesterday']],
        ['x','#3565DE','#E8EFFB','Closed (Today)','12',['down','#157A43','4 from yesterday']],
        ['bar-chart-3','#7C4FE0','#F0EAFB','Avg. Response Time','12m 45s',['down','#157A43','15% from yesterday']],
    ];
    // [id, title, sub, cat, catC, loc, locSub, sev, sevC, status, stC, stBg, at, ago, team, teamSub]
    $rows = [
        ['INC-2026-0048','Fight / Physical Altercation','Two persons involved in a fight','Security','#C0010C','Pavilion A','Main Entrance','High','#C0010C','Open','#C0010C','#FDE8E8','Today, 10:22 AM','2 min ago','Team Alpha','J. Essomba'],
        ['INC-2026-0047','Suspicious Bag','Unattended bag detected','Suspicious Activity','#C97A16','Food Court','Main Area','Medium','#C97A16','In Progress','#3565DE','#E8EFFB','Today, 10:15 AM','9 min ago','Team Bravo','M. Abena'],
        ['INC-2026-0046','Access Control Violation','Unauthorized access attempt','Access Control','#3565DE','Pavilion B','VIP Entrance','High','#C0010C','In Progress','#3565DE','#E8EFFB','Today, 10:08 AM','16 min ago','Team Charlie','P. Nguema'],
        ['INC-2026-0045','Medical Assistance','Visitor reported feeling unwell','Medical','#157A43','Central Square','Near Stage','Medium','#C97A16','Open','#C0010C','#FDE8E8','Today, 09:52 AM','32 min ago','Medical Team','N. Marie'],
        ['INC-2026-0044','Theft Report','Mobile phone reported stolen','Theft','#7C4FE0','Pavilion C','Craft Section','Low','#157A43','Open','#C0010C','#FDE8E8','Today, 09:40 AM','44 min ago','Team Bravo','A. Fomukong'],
    ];
@endphp

<div class="mb-5">
    <h1 class="text-[24px] font-bold text-[#131313] tracking-tight">SECURITY OPERATIONS</h1>
    <p class="text-[12px] text-[#8A857A] mt-0.5 flex items-center gap-1.5">Incidents <i data-lucide="chevron-right" class="w-3 h-3"></i> Manage and respond to security incidents in real time</p>
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

<div class="grid grid-cols-1 xl:grid-cols-[1fr_400px] gap-5 items-start mb-5">
    {{-- ══ INCIDENTS LIST ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <p class="px-5 pt-4 text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Incidents List</p>
        <div class="px-5 py-3 flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#incScope" placeholder="Search incidents..." class="w-full text-[12px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach(['All Status','All Categories','All Locations'] as $f)
            <select data-filter-select="#incScope" class="text-[11.5px] rounded-lg border border-[#EFEDE6] px-2.5 py-2 bg-white text-[#3B382F]"><option>{{ $f }}</option>@foreach(['open','in progress','security','medical','theft','pavilion a','pavilion b','food court'] as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach</select>
            @endforeach
            <button data-toast="Advanced filters — coming soon" class="siarc-btn text-[11.5px] text-[#3B382F] border border-[#EFEDE6] px-2.5 py-2 rounded-lg bg-white"><i data-lucide="filter" class="w-3.5 h-3.5 text-[#8A857A]"></i>Filters</button>
            <button data-toast="Export in progress… (demo)" class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="download" class="w-3.5 h-3.5"></i></button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[760px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['ID','INCIDENT','CATEGORY','LOCATION','SEVERITY','STATUS','REPORTED AT','ASSIGNED TO','ACTIONS'] as $h)
                        <th class="px-3.5 py-3 text-[9.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="incScope">
                    @foreach($rows as [$id,$title,$sub,$cat,$catC,$loc,$locSub,$sev,$sevC,$status,$stC,$stBg,$at,$ago,$team,$teamSub])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($status) }} {{ Str::lower($cat) }} {{ Str::lower($loc) }}" data-filter-text="{{ $id }} {{ $title }} {{ $cat }} {{ $loc }} {{ $team }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-3.5 py-3 text-[11px] font-medium text-[#3B382F] whitespace-nowrap">{{ $id }}</td>
                        <td class="px-3.5 py-3">
                            <span class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $catC }}1a"><i data-lucide="alert-triangle" class="w-4 h-4" style="color:{{ $catC }}"></i></span>
                                <span><span class="block text-[12px] font-semibold text-[#131313]">{{ $title }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $sub }}</span></span>
                            </span>
                        </td>
                        <td class="px-3.5 py-3 text-[11.5px] text-[#3B382F]">{{ $cat }}</td>
                        <td class="px-3.5 py-3 text-[11.5px] text-[#3B382F]">{{ $loc }}<br><span class="text-[10px] text-[#8A857A]">{{ $locSub }}</span></td>
                        <td class="px-3.5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $sevC }};background:{{ $sevC }}18">{{ $sev }}</span></td>
                        <td class="px-3.5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $stC }};background:{{ $stBg }}">{{ $status }}</span></td>
                        <td class="px-3.5 py-3 text-[11px] text-[#3B382F]">{{ $at }}<br><span class="text-[10px] text-[#8A857A]">{{ $ago }}</span></td>
                        <td class="px-3.5 py-3 text-[11px] text-[#3B382F]">{{ $team }}<br><span class="text-[10px] text-[#8A857A]">{{ $teamSub }}</span></td>
                        <td class="px-3.5 py-3"><button data-toast="Incident file — coming soon" class="w-6 h-6 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="more-vertical" class="w-3.5 h-3.5 text-[#8A857A]"></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div data-page="#incScope" data-page-size="10" class="px-5 py-3 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[11.5px] text-[#8A857A]">Showing 1 to 5 of 48 incidents</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></button>
                <button data-page-num="1" class="is-active w-7 h-7 rounded-lg text-[11.5px] font-semibold flex items-center justify-center">1</button>
                @foreach(['2','3','4','5'] as $p)<button data-page-num="{{ $p }}" class="w-7 h-7 rounded-lg border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] flex items-center justify-center">{{ $p }}</button>@endforeach
                <span class="text-[11.5px] text-[#8A857A]">…</span>
                <button data-page-num="10" class="w-7 h-7 rounded-lg border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] flex items-center justify-center">10</button>
                <button data-page-next class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT: donut + severity ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-4">Incidents by Category (Today)</p>
            <div class="flex items-center gap-5">
                <svg viewBox="0 0 120 120" class="w-[130px] h-[130px] shrink-0">
                    @php $cats = [['Security',18,'#C0010C'],['Medical',9,'#157A43'],['Access Control',8,'#3565DE'],['Suspicious Activity',7,'#C97A16'],['Theft',4,'#7C4FE0'],['Other',2,'#B0AB9F']]; $tot = 48; $acc = 0; @endphp
                    @foreach($cats as [$lbl,$v,$c])
                        @php $r = 46; $circ = 2 * pi() * $r; $len = $v / $tot * $circ; $off = -$acc / $tot * $circ; $acc += $v; @endphp
                        <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="{{ $c }}" stroke-width="16" stroke-dasharray="{{ $len - 2 }} {{ $circ - $len + 2 }}" stroke-dashoffset="{{ $off - 25 }}" transform="rotate(-90 60 60)"/>
                    @endforeach
                    <text x="60" y="58" text-anchor="middle" font-size="22" font-weight="700" fill="#131313">48</text>
                    <text x="60" y="74" text-anchor="middle" font-size="9" fill="#8A857A">Total</text>
                </svg>
                <div class="space-y-1.5 flex-1">
                    @foreach([['Security','18 (37.5%)','#C0010C'],['Medical','9 (18.8%)','#157A43'],['Access Control','8 (16.7%)','#3565DE'],['Suspicious Activity','7 (14.6%)','#C97A16'],['Theft','4 (8.3%)','#7C4FE0'],['Other','2 (4.1%)','#B0AB9F']] as [$lbl,$v,$c])
                    <p class="flex items-center justify-between text-[11.5px]"><span class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $c }}"></span>{{ $lbl }}</span><span class="font-semibold text-[#131313]">{{ $v }}</span></p>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-4">Incidents by Severity (Today)</p>
            <div class="space-y-3">
                @foreach([['High',18,'37.5%','#C0010C'],['Medium',17,'35.4%','#C97A16'],['Low',11,'22.9%','#157A43'],['Info',2,'4.2%','#3565DE']] as [$lbl,$v,$pct,$c])
                <div class="flex items-center gap-3">
                    <span class="w-[52px] text-[11.5px] text-[#3B382F] shrink-0">{{ $lbl }}</span>
                    <span class="flex-1 h-[7px] rounded-full bg-[#F1F0EB] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $pct }};background:{{ $c }}"></span></span>
                    <span class="text-[11px] font-semibold text-[#3B382F] w-[62px] text-right">{{ $v }} ({{ $pct }})</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Quick Actions</p>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([['alert-triangle','Report Incident','#C0010C'],['user-check','Assign Incident','#157A43'],['megaphone','Broadcast Alert','#3565DE'],['bell','Notify Team','#7C4FE0'],['file-text','Incident Log','#3B382F'],['download','Export Report','#157A43']] as [$ic,$lbl,$c])
                <button data-toast="{{ $lbl }} — coming soon" class="rounded-lg border border-[#EFEDE6] px-2.5 py-3 text-center hover:bg-[#FBFAF6]">
                    <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[10.5px] font-semibold text-[#3B382F] leading-tight">{{ $lbl }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_380px] gap-5 items-start">
    {{-- ══ TREND CHART ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Incidents Trend</p>
            <span class="text-[11px] text-[#3B382F] border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 flex items-center gap-1">Last 7 Days<i data-lucide="chevron-down" class="w-3 h-3 text-[#8A857A]"></i></span>
        </div>
        <div class="flex items-start gap-5">
            <svg viewBox="0 0 520 160" class="flex-1 min-w-0 h-auto">
                @foreach([16,50,84,118,152] as $y)<line x1="26" y1="{{ $y }}" x2="520" y2="{{ $y }}" stroke="#F2F1EC"/>@endforeach
                @foreach([['0',152],['10',118],['20',84],['30',50],['40',16]] as [$lbl,$y])<text x="16" y="{{ $y + 3 }}" font-size="9" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>@endforeach
                @foreach([
                    ['#3565DE',[42,36,32,28,24,20,22]], ['#C0010C',[92,86,80,72,66,60,62]],
                    ['#C97A16',[112,108,102,96,92,88,90]], ['#157A43',[128,126,122,118,116,112,114]], ['#7C4FE0',[142,142,140,138,138,136,138]],
                ] as [$c,$pts])
                <polyline fill="none" stroke="{{ $c }}" stroke-width="2" points="@foreach($pts as $i => $y){{ 40 + $i * 78 }},{{ $y }} @endforeach"/>
                @foreach($pts as $i => $y)<circle cx="{{ 40 + $i * 78 }}" cy="{{ $y }}" r="2.4" fill="{{ $c }}"/>@endforeach
                @endforeach
                @foreach(['May 16','May 17','May 18','May 19','May 20','May 21','May 22'] as $i => $lbl)<text x="{{ 40 + $i * 78 }}" y="160" font-size="9" fill="#B0AB9F" text-anchor="middle">{{ $lbl }}</text>@endforeach
            </svg>
            <div class="shrink-0 space-y-2 pt-1">
                @foreach([['Total','#3565DE'],['Open','#C0010C'],['In Progress','#C97A16'],['Resolved','#157A43'],['Closed','#7C4FE0']] as [$lbl,$c])
                <p class="flex items-center gap-2 text-[11px] text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $c }}"></span>{{ $lbl }}</p>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══ BY LOCATION MAP ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Incidents by Location (Today)</p>
            <button data-toast="Map view — coming soon" class="siarc-btn text-[11.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] px-3 py-1.5 rounded-lg bg-white">View Map</button>
        </div>
        <img src="{{ asset('images/siarc/secops-map-incidents.png') }}" alt="" class="w-full h-auto rounded-xl border border-[#EFEDE6]">
    </div>
</div>

{{-- ══ RECENT ACTIVITY STRIP ══ --}}
<div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5 mt-5">
    <div class="flex items-center justify-between mb-3">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Recent Incident Activity</p>
        <button data-toast="Full activity log — coming soon" class="text-[11.5px] font-semibold text-[#157A43] inline-flex items-center gap-1">View All Activity <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
        @foreach([['alert-triangle','#C0010C','10:24 AM','New incident reported','Fight / Physical Altercation','Pavilion A - Main Entrance'],['refresh-cw','#3565DE','10:18 AM','Incident updated','Suspicious Bag','Status changed to In Progress'],['check-circle-2','#157A43','10:12 AM','Incident resolved','Access Control Violation','Pavilion B - VIP Entrance'],['heart-pulse','#C97A16','09:55 AM','New incident reported','Medical Assistance','Central Square - Near Stage'],['file-text','#7C4FE0','09:40 AM','New incident reported','Theft Report','Pavilion C - Craft Section']] as [$ic,$c,$time,$t,$s1,$s2])
        <div class="flex items-start gap-2.5">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="{{ $ic }}" class="w-4 h-4" style="color:{{ $c }}"></i></span>
            <span><span class="block text-[10.5px] font-semibold text-[#8A857A]">{{ $time }}</span><span class="block text-[11.5px] font-bold text-[#131313] leading-snug">{{ $t }}</span><span class="block text-[10.5px] text-[#3B382F]">{{ $s1 }}</span><span class="block text-[10px] text-[#8A857A]">{{ $s2 }}</span></span>
        </div>
        @endforeach
    </div>
</div>
