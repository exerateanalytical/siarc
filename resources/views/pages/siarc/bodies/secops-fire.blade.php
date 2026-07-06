@php
    // ══ Security Operations — Fire Alerts (verbatim from the approved design) ══
    $stats = [
        ['flame','#C0010C','#FDE8E8','Active Fire Alerts','8',['up','#C0010C','3 from yesterday']],
        ['flag','#C97A16','#FDF3E0','Under Response','5',['t','#C97A16','Requires immediate action']],
        ['check-circle-2','#157A43','#E2F3E8','Resolved (Today)','12',['up','#157A43','4 from yesterday']],
        ['clock','#E6B201','#FDF6DC','Avg. Response Time','7m 42s',['down','#157A43','1m 18s from yesterday']],
        ['file-text','#7C4FE0','#F0EAFB','Total Reports (Today)','20',['up','#C0010C','6 from yesterday']],
        ['users-round','#3565DE','#E8EFFB','People Affected (Today)','36',['down','#157A43','8 from yesterday']],
    ];
    $caseHref = route('siarc.admin.secops.fire.case', ['lang' => $lang]);
    // [id, loc, locSub, type, tIc, tc, sev, sevC, at, status, stC, unit, unitSub]
    $rows = [
        ['FA-2026-0012','Pavilion C – Craft Section','Main Exhibition Area','Structural Fire','flame','#C0010C','High','#C0010C','May 4, 2026  10:18 AM','Under Response','#C97A16','Fire Unit 2','ETA: 3 min'],
        ['FA-2026-0011','Food Court','Near Stage','Electrical Fire','zap','#C97A16','Medium','#C97A16','May 4, 2026  09:52 AM','En Route','#C97A16','Fire Unit 1','ETA: 5 min'],
        ['FA-2026-0010','Parking P2','North Gate','Vehicle Fire','car','#C0010C','High','#C0010C','May 4, 2026  09:35 AM','Under Response','#C97A16','Fire Unit 3','ETA: 4 min'],
        ['FA-2026-0009','Pavilion A – Main Hall','West Wing','Structural Fire','flame','#157A43','Low','#157A43','May 4, 2026  08:47 AM','Monitoring','#3565DE','Fire Unit 4','On Standby'],
        ['FA-2026-0008','Workshop Area','Wood Craft Section','Smoke Alert','cloud','#8A857A','Low','#157A43','May 4, 2026  08:12 AM','Under Investigation','#7C4FE0','Fire Unit 5','ETA: 10 min'],
    ];
@endphp

<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="flex items-center gap-2.5 text-[22px] font-bold text-[#131313] tracking-tight"><i data-lucide="flame" class="w-5 h-5 text-[#C0010C]"></i>Fire Alerts</h1>
        <p class="text-[12px] text-[#8A857A] mt-0.5">Monitor and manage fire incidents and alerts in real time.</p>
    </div>
    <button data-toast="Raise fire alert — coming soon" class="siarc-btn text-[12.5px] font-bold text-white rounded-lg px-4 py-2.5" style="background:#C0010C"><i data-lucide="plus" class="w-4 h-4"></i>Raise Fire Alert</button>
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

<div class="grid grid-cols-1 xl:grid-cols-[1fr_440px] gap-5 items-start mb-5">
    {{-- ══ ACTIVE FIRE ALERTS ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <p class="px-5 pt-4 text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Active Fire Alerts</p>
        <div class="px-5 py-3 flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#faScope" placeholder="Search by location, type, or alert ID..." class="w-full text-[12px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach(['All Status','All Severity','All Locations'] as $f)
            <select data-filter-select="#faScope" class="text-[11.5px] rounded-lg border border-[#EFEDE6] px-2.5 py-2 bg-white text-[#3B382F]"><option>{{ $f }}</option>@foreach(['under response','en route','monitoring','high','medium','low'] as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach</select>
            @endforeach
            <button data-toast="Advanced filters — coming soon" class="siarc-btn text-[11.5px] text-[#3B382F] border border-[#EFEDE6] px-2.5 py-2 rounded-lg bg-white"><i data-lucide="filter" class="w-3.5 h-3.5 text-[#8A857A]"></i>Filters</button>
            <button onclick="window.location.reload()" class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i></button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[740px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['ALERT ID','LOCATION','TYPE','SEVERITY','REPORTED AT','STATUS','RESPONDING UNIT','ACTIONS'] as $h)
                        <th class="px-3.5 py-3 text-[9.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="faScope">
                    @foreach($rows as [$id,$loc,$locSub,$type,$tIc,$tc,$sev,$sevC,$at,$status,$stC,$unit,$unitSub])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($status) }} {{ Str::lower($sev) }}" data-filter-text="{{ $id }} {{ $loc }} {{ $type }} {{ $status }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-3.5 py-3 text-[11px] font-medium text-[#3B382F] whitespace-nowrap">{{ $id }}</td>
                        <td class="px-3.5 py-3 text-[11.5px] font-semibold text-[#131313]">{{ $loc }}<br><span class="font-normal text-[10px] text-[#8A857A]">{{ $locSub }}</span></td>
                        <td class="px-3.5 py-3"><span class="inline-flex items-center gap-1.5 text-[11.5px] text-[#3B382F]"><span class="w-6 h-6 rounded-lg flex items-center justify-center" style="background:{{ $tc }}18"><i data-lucide="{{ $tIc }}" class="w-3.5 h-3.5" style="color:{{ $tc }}"></i></span>{{ $type }}</span></td>
                        <td class="px-3.5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $sevC }};background:{{ $sevC }}18">{{ $sev }}</span></td>
                        <td class="px-3.5 py-3 text-[11px] text-[#3B382F] whitespace-nowrap">{{ $at }}</td>
                        <td class="px-3.5 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $stC }};background:{{ $stC }}18">{{ $status }}</span></td>
                        <td class="px-3.5 py-3 text-[11px] text-[#3B382F]">{{ $unit }}<br><span class="text-[10px] text-[#8A857A]">{{ $unitSub }}</span></td>
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
        <div data-page="#faScope" data-page-size="10" class="px-5 py-3 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[11.5px] text-[#8A857A]">Showing 1 to 5 of 8 alerts</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></button>
                <button data-page-num="1" class="is-active w-7 h-7 rounded-lg text-[11.5px] font-semibold flex items-center justify-center">1</button>
                <button data-page-num="2" class="w-7 h-7 rounded-lg border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] flex items-center justify-center">2</button>
                <button data-page-next class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT: severity donut + location bars ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-4">Alerts by Severity</p>
            <div class="flex items-center gap-5">
                <svg viewBox="0 0 120 120" class="w-[125px] h-[125px] shrink-0">
                    @php $parts = [[3,'#C0010C'],[2,'#C97A16'],[2,'#E6B201'],[1,'#3565DE']]; $tot = 8; $acc = 0; @endphp
                    @foreach($parts as [$v,$c])
                        @php $r = 46; $circ = 2 * pi() * $r; $len = $v / $tot * $circ; $off = -$acc / $tot * $circ; $acc += $v; @endphp
                        <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="{{ $c }}" stroke-width="16" stroke-dasharray="{{ max($len - 2, 1) }} {{ $circ - $len + 2 }}" stroke-dashoffset="{{ $off - 25 }}" transform="rotate(-90 60 60)"/>
                    @endforeach
                    <text x="60" y="58" text-anchor="middle" font-size="22" font-weight="700" fill="#131313">8</text>
                    <text x="60" y="74" text-anchor="middle" font-size="9" fill="#8A857A">Total</text>
                </svg>
                <div class="space-y-2 flex-1">
                    @foreach([['High','3 (37.5%)','#C0010C'],['Medium','2 (25%)','#C97A16'],['Low','2 (25%)','#E6B201'],['Info','1 (12.5%)','#3565DE']] as [$lbl,$v,$c])
                    <p class="flex items-center justify-between text-[11.5px]"><span class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $c }}"></span>{{ $lbl }}</span><span class="font-semibold text-[#131313]">{{ $v }}</span></p>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-4">Alerts by Location</p>
            <div class="space-y-3">
                @foreach([['Pavilion C – Craft Section','2 (25%)',84,'#C0010C'],['Food Court','2 (25%)',84,'#C97A16'],['Parking P2','1 (12.5%)',42,'#E6B201'],['Pavilion A – Main Hall','1 (12.5%)',42,'#157A43'],['Workshop Area','1 (12.5%)',42,'#3565DE']] as [$lbl,$v,$pct,$c])
                <div class="flex items-center gap-3">
                    <span class="w-[128px] text-[11px] text-[#3B382F] shrink-0 truncate">{{ $lbl }}</span>
                    <span class="flex-1 h-[6px] rounded-full bg-[#F1F0EB] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $pct }}%;background:{{ $c }}"></span></span>
                    <span class="text-[10.5px] font-semibold text-[#3B382F] w-[58px] text-right shrink-0">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Quick Actions</p>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([['plus','Raise Fire Alert','#C0010C'],['megaphone','Alert Broadcast','#3565DE'],['truck','Dispatch Unit','#C0010C'],['file-text','View All Alerts','#3B382F'],['shield-check','Fire Safety Checklist','#157A43'],['download','Export Report','#157A43'],['users','Resource Availability','#7C4FE0'],['history','Incident Log','#3565DE']] as [$ic,$lbl,$c])
                <button data-toast="{{ $lbl }} — coming soon" class="rounded-lg border border-[#EFEDE6] px-2.5 py-3 text-center hover:bg-[#FBFAF6]">
                    <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[10.5px] font-semibold text-[#3B382F] leading-tight">{{ $lbl }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[380px_1fr] gap-5 items-start">
    {{-- ══ RECENT FIRE REPORTS ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Recent Fire Reports</p>
        <div class="space-y-3">
            @foreach([['10:18 AM','Fire reported at Pavilion C – Craft Section','Structural fire near main entrance. Smoke visible.','High','#C0010C'],['09:52 AM','Electrical fire reported at Food Court','Electrical panel overheated. No injuries reported.','Medium','#C97A16'],['09:35 AM','Vehicle fire at Parking P2','Car fire spreading to nearby vehicles.','High','#C0010C'],['08:47 AM','Fire reported at Pavilion A – West Wing','Minor fire contained. No damage reported.','Low','#157A43'],['08:12 AM','Smoke detected at Workshop Area','Investigation in progress.','Low','#157A43']] as [$time,$t,$s,$sev,$c])
            <div class="flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="flame" class="w-3.5 h-3.5" style="color:{{ $c }}"></i></span>
                <span class="min-w-0 flex-1"><span class="block text-[10.5px] font-semibold text-[#8A857A]">{{ $time }}</span><span class="block text-[12px] font-semibold text-[#131313] leading-snug">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $s }}</span></span>
                <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md shrink-0" style="color:{{ $c }};background:{{ $c }}18">{{ $sev }}</span>
            </div>
            @endforeach
        </div>
        <button data-toast="All reports — coming soon" class="mt-3 text-[11.5px] font-semibold text-[#157A43] inline-flex items-center gap-1">View All Reports <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
    </div>

    {{-- ══ RESPONSE OVERVIEW ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Response Overview</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            @foreach([['truck','On Scene','2','#C0010C'],['truck','En Route','2','#C97A16'],['clock','On Standby','3','#E6B201'],['check-circle-2','Completed (Today)','12','#157A43']] as [$ic,$k,$v,$c])
            <span class="flex items-center gap-2.5 rounded-lg border border-[#EFEDE6] px-3 py-2.5">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="{{ $ic }}" class="w-4 h-4" style="color:{{ $c }}"></i></span>
                <span><span class="block text-[10px] text-[#8A857A] leading-tight">{{ $k }}</span><span class="block text-[16px] font-bold text-[#131313]">{{ $v }}</span></span>
            </span>
            @endforeach
        </div>
        <p class="text-[11px] font-semibold text-[#3B382F] mb-1.5">Response Timeline (Today)</p>
        <svg viewBox="0 0 620 130" class="w-full h-auto">
            <defs><linearGradient id="faArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C0010C" stop-opacity=".22"/><stop offset="1" stop-color="#C0010C" stop-opacity="0"/></linearGradient></defs>
            @foreach([['15',14],['10',48],['5',82],['0',116]] as [$lbl,$y])
            <line x1="24" y1="{{ $y }}" x2="620" y2="{{ $y }}" stroke="#F2F1EC"/><text x="18" y="{{ $y + 3 }}" font-size="8.5" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>
            @endforeach
            <path d="M24 96 L74 68 124 58 174 66 224 80 274 74 324 62 374 70 424 58 474 42 524 30 574 38 620 46 L620 130 24 130 Z" fill="url(#faArea)"/>
            <path d="M24 96 L74 68 124 58 174 66 224 80 274 74 324 62 374 70 424 58 474 42 524 30 574 38 620 46" fill="none" stroke="#C0010C" stroke-width="2"/>
            @foreach([[24,96],[124,58],[224,80],[324,62],[424,58],[524,30],[620,46]] as [$x,$y])<circle cx="{{ $x }}" cy="{{ $y }}" r="2.6" fill="#fff" stroke="#C0010C" stroke-width="2"/>@endforeach
            @foreach([['00:00',24],['04:00',124],['08:00',224],['12:00',324],['16:00',424],['20:00',524],['24:00',608]] as [$lbl,$x])<text x="{{ $x }}" y="129" font-size="8.5" fill="#B0AB9F" text-anchor="middle">{{ $lbl }}</text>@endforeach
        </svg>
        <p class="text-center text-[10.5px] text-[#8A857A] mt-1 flex items-center justify-center gap-1.5"><span class="w-4 h-[3px] rounded-full bg-[#C0010C]"></span>Alerts Handled</p>
    </div>
</div>

<div class="mt-5 rounded-xl bg-[#FDF7F7] border border-[#F5CFCF] px-5 py-3.5 flex items-center justify-between gap-4">
    <p class="flex items-center gap-2.5 text-[12.5px] text-[#8A1A1A]"><i data-lucide="flame" class="w-4 h-4 text-[#C0010C] shrink-0"></i>In case of an emergency, contact Control Center immediately or call 112.</p>
    <button data-toast="Control center call — coming soon" class="shrink-0 siarc-btn text-[12.5px] font-bold text-white rounded-lg px-4 py-2.5" style="background:#C0010C"><i data-lucide="phone" class="w-4 h-4"></i>Contact Control Center</button>
</div>
