@php
    // ══ Security Operations — Crowd Alerts (verbatim from the approved design) ══
    $stats = [
        ['users-round','#C0010C','#FDE8E8','Active Crowd Alerts','12',['up','5 from last hour']],
        ['users','#C97A16','#FDF3E0','High Density Zones','8',['up','2 from last hour']],
        ['users-round','#157A43','#E2F3E8','Total People Count','18,456',['t','Live count']],
        ['line-chart','#3565DE','#E8EFFB','Average Density','3.4 ppl/m²',['t','Moderate']],
        ['alert-circle','#7C4FE0','#F0EAFB','Critical Zones','2',['t','Requires attention']],
        ['shield-check','#157A43','#E2F3E8','Resolved Alerts (Today)','15',['down','8 from yesterday']],
    ];
    // [level, sub, zone, zSub, density, pct, dColor, count, sev, sc, time, ago]
    $rows = [
        ['Very High Density','Crowd approaching capacity limit','Pavilion A','Main Entrance','6.1 ppl/m²',95,'#C0010C','1,524','Critical','#C0010C','10:24 AM','Just now'],
        ['High Density','Crowd density is high','Pavilion B','Craft Section','4.8 ppl/m²',75,'#C97A16','2,312','High','#C97A16','10:22 AM','2 min ago'],
        ['High Density','Crowd density is high','Food Court','Main Area','4.5 ppl/m²',70,'#C97A16','1,876','High','#C97A16','10:20 AM','4 min ago'],
        ['Moderate Density','Monitoring recommended','Pavilion C','Textile Section','3.1 ppl/m²',48,'#E6B201','1,203','Medium','#E6B201','10:18 AM','6 min ago'],
        ['Moderate Density','Monitoring recommended','Outdoor Area','Central Square','2.8 ppl/m²',43,'#E6B201','3,645','Medium','#E6B201','10:16 AM','8 min ago'],
        ['Normal Density','No action required','Parking P1','North Gate','1.2 ppl/m²',18,'#157A43','892','Low','#157A43','10:15 AM','9 min ago'],
    ];
@endphp

<div class="mb-5">
    <h1 class="text-[24px] font-bold text-[#131313] tracking-tight">SECURITY OPERATIONS</h1>
    <p class="text-[14px] font-semibold text-[#3B382F] mt-0.5">Crowd Alerts</p>
    <p class="text-[12px] text-[#8A857A]">Real-time monitoring and alerts for crowd density and potential congestion.</p>
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
        @if($sub[0] === 'up')<span class="text-[10.5px] font-semibold text-[#C0010C] inline-flex items-center gap-0.5"><i data-lucide="arrow-up" class="w-3 h-3"></i>{{ $sub[1] }}</span>
        @elseif($sub[0] === 'down')<span class="text-[10.5px] font-semibold text-[#157A43] inline-flex items-center gap-0.5"><i data-lucide="arrow-down" class="w-3 h-3"></i>{{ $sub[1] }}</span>
        @else<span class="text-[10.5px] text-[#8A857A]">{{ $sub[1] }}</span>@endif
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_400px] gap-5 items-start mb-5">
    {{-- ══ ACTIVE CROWD ALERTS TABLE ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <div class="px-5 pt-4 pb-3 flex flex-wrap items-center justify-between gap-3">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Active Crowd Alerts</p>
            <span class="flex items-center gap-2">
                @foreach(['All Zones','All Severity'] as $f)
                <select data-filter-select="#caScope" class="text-[11.5px] rounded-lg border border-[#EFEDE6] px-2.5 py-1.5 bg-white text-[#3B382F]"><option>{{ $f }}</option>@foreach(['critical','high','medium','low'] as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach</select>
                @endforeach
                <button data-toast="Advanced filters — coming soon" class="siarc-btn text-[11.5px] text-[#3B382F] border border-[#EFEDE6] px-2.5 py-1.5 rounded-lg bg-white"><i data-lucide="filter" class="w-3.5 h-3.5 text-[#8A857A]"></i>Filters</button>
                <button onclick="window.location.reload()" class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i></button>
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[720px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['ALERT','ZONE / LOCATION','DENSITY','PEOPLE COUNT','SEVERITY','DETECTED','ACTIONS'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="caScope">
                    @foreach($rows as [$lvl,$sub,$zone,$zSub,$density,$pct,$dc,$count,$sev,$sc,$time,$ago])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($sev) }}" data-filter-text="{{ $lvl }} {{ $zone }} {{ $zSub }} {{ $sev }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $sc }}1a"><i data-lucide="users-round" class="w-4 h-4" style="color:{{ $sc }}"></i></span>
                                <span><span class="block text-[12.5px] font-semibold text-[#131313]">{{ $lvl }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $sub }}</span></span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $zone }}<br><span class="text-[10.5px] text-[#8A857A]">{{ $zSub }}</span></td>
                        <td class="px-4 py-3 min-w-[110px]">
                            <span class="block text-[12px] font-bold" style="color:{{ $dc }}">{{ $density }}</span>
                            <span class="mt-1 block h-[5px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $pct }}%;background:{{ $dc }}"></span></span>
                        </td>
                        <td class="px-4 py-3 text-[12.5px] font-semibold text-[#131313]">{{ $count }}</td>
                        <td class="px-4 py-3"><span class="text-[10px] font-bold px-2.5 py-1 rounded-md" style="color:{{ $sc }};background:{{ $sc }}18">{{ $sev }}</span></td>
                        <td class="px-4 py-3 text-[11.5px] text-[#3B382F]">{{ $time }}<br><span class="text-[10.5px] text-[#8A857A]">{{ $ago }}</span></td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-1">
                                <button data-toast="Zone camera view — coming soon" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-[#3565DE]"><i data-lucide="eye" class="w-3.5 h-3.5"></i>View</button>
                                <button data-toast="Context menu — coming soon" class="w-6 h-6 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="more-vertical" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div data-page="#caScope" data-page-size="10" class="px-5 py-3 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[11.5px] text-[#8A857A]">Showing 1 to 6 of 12 alerts</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></button>
                <button data-page-num="1" class="is-active w-7 h-7 rounded-lg text-[11.5px] font-semibold flex items-center justify-center">1</button>
                <button data-page-num="2" class="w-7 h-7 rounded-lg border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] flex items-center justify-center">2</button>
                <button data-page-next class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT: MAP + RECENT ALERTS ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Alert Map (Live Density)</p>
                <button data-toast="Full map view — coming soon" class="siarc-btn text-[11.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] px-3 py-1.5 rounded-lg bg-white">View Full Map</button>
            </div>
            <img src="{{ asset('images/siarc/secops-map-crowd.png') }}" alt="" class="w-full h-auto rounded-xl border border-[#EFEDE6]">
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Recent Alerts</p>
                <button data-toast="All alerts — coming soon" class="text-[11.5px] font-semibold text-[#157A43] inline-flex items-center gap-1">View All Alerts <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
            </div>
            <div class="space-y-3">
                @foreach([['10:24 AM','Very high crowd density detected','Pavilion A – Main Entrance','Active','#C0010C','by CCTV-01'],['10:22 AM','High crowd density detected','Pavilion B – Craft Section','Active','#C97A16','by CCTV-05'],['10:20 AM','High crowd density detected','Food Court – Main Area','Active','#C97A16','by CCTV-03'],['10:12 AM','Crowd density back to normal','Central Square','Resolved','#157A43','by System'],['10:08 AM','Moderate crowd density','Pavilion C – Textile Section','Active','#E6B201','by CCTV-02']] as [$time,$t,$loc,$st,$c,$by])
                <div class="flex items-start gap-2.5">
                    <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="{{ $st === 'Resolved' ? 'check-circle-2' : 'users-round' }}" class="w-3.5 h-3.5" style="color:{{ $c }}"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-2"><span class="text-[11px] font-semibold text-[#8A857A]">{{ $time }}</span><span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $c }};background:{{ $c }}18">{{ $st }}</span></span>
                        <span class="block text-[12px] font-medium text-[#131313] leading-snug">{{ $t }}</span>
                        <span class="block text-[10.5px] text-[#8A857A]">{{ $loc }}</span>
                    </span>
                    <span class="text-[10px] text-[#B0AB9F] shrink-0">{{ $by }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ══ DENSITY TREND ══ --}}
<div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
    <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Density Trend (Last 6 Hours)</p>
    <div class="flex items-start gap-6">
        <svg viewBox="0 0 640 170" class="flex-1 h-auto min-w-0">
            @foreach([20,55,90,125,160] as $y)<line x1="30" y1="{{ $y }}" x2="640" y2="{{ $y }}" stroke="#F2F1EC"/>@endforeach
            @foreach([['0',160],['2',125],['4',90],['6',55],['8',20]] as [$lbl,$y])<text x="18" y="{{ $y + 4 }}" font-size="9" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>@endforeach
            @foreach([
                ['#C0010C', [92,88,84,78,72,66,58,52,48,44,42,40,44,48]],
                ['#C97A16', [110,106,102,98,92,86,80,74,70,68,66,64,68,72]],
                ['#E6B201', [128,124,122,118,114,110,106,102,100,98,96,96,98,102]],
                ['#157A43', [150,148,148,146,144,142,142,140,140,138,138,138,140,142]],
            ] as [$c,$pts])
            <polyline fill="none" stroke="{{ $c }}" stroke-width="2" points="@foreach($pts as $i => $y){{ 30 + $i * 47 }},{{ $y }} @endforeach"/>
            @foreach($pts as $i => $y)<circle cx="{{ 30 + $i * 47 }}" cy="{{ $y }}" r="2.4" fill="{{ $c }}"/>@endforeach
            @endforeach
            @foreach([['04:00',30],['05:00',124],['06:00',218],['07:00',312],['08:00',406],['09:00',500],['10:00',594]] as [$lbl,$x])<text x="{{ $x }}" y="169" font-size="9" fill="#B0AB9F" text-anchor="middle">{{ $lbl }}</text>@endforeach
        </svg>
        <div class="shrink-0 space-y-2 pt-2">
            @foreach([['Pavilion A (Main Entrance)','#C0010C'],['Pavilion B (Craft Section)','#C97A16'],['Food Court (Main Area)','#E6B201'],['Central Square','#157A43'],['Parking P1 (North Gate)','#157A43']] as [$lbl,$c])
            <p class="flex items-center gap-2 text-[11px] text-[#3B382F]"><span class="w-4 h-[3px] rounded-full" style="background:{{ $c }}"></span>{{ $lbl }}</p>
            @endforeach
        </div>
    </div>
</div>
