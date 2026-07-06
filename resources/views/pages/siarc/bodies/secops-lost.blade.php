@php
    // ══ Security Operations — Lost Persons (verbatim from the approved design) ══
    $stats = [
        ['user-search','#157A43','#E2F3E8','Total Cases','32',['up','#157A43','8 from yesterday']],
        ['search','#C97A16','#FDF3E0','Missing (Open)','21',['up','#C0010C','5 from yesterday']],
        ['map-pin','#3565DE','#E8EFFB','Found & Reunited','10',['up','#157A43','3 from yesterday']],
        ['users-round','#7C4FE0','#F0EAFB','Reunification Rate','31.3%',['up','#157A43','6.2% from yesterday']],
        ['clock','#E6B201','#FDF6DC','Avg. Resolution Time','1h 45m',['down','#157A43','20m from yesterday']],
        ['file-text','#157A43','#E2F3E8','Active Reports Today','14',['up','#C0010C','2 from yesterday']],
    ];
    $caseHref = route('siarc.admin.secops.lost.case', ['lang' => $lang]);
    // [id, name, sub, type, tc, loc, locSub, status, stC, at, ago, face]
    $rows = [
        ['LP-2026-0032','Amina B.','Female, 6 years','Missing','#C0010C','Pavilion A','Main Entrance','Open','#C0010C','Today, 10:15 AM','9 min ago',1],
        ['LP-2026-0031','Jean P. M.','Male, 34 years','Missing','#C0010C','Food Court','Near Stage','Open','#C0010C','Today, 09:52 AM','32 min ago',2],
        ['LP-2026-0030','Mohammed T.','Male, 8 years','Found','#157A43','Central Square','Information Desk','Reunited','#157A43','Today, 09:10 AM','1h 14m ago',3],
        ['LP-2026-0029','Marie L.','Female, 27 years','Found','#157A43','Pavilion B','VIP Lounge','Reunited','#157A43','Today, 08:35 AM','1h 49m ago',4],
        ['LP-2026-0028','Alphonse N.','Male, 67 years','Missing','#C0010C','Parking P1','North Gate','Open','#C0010C','Yesterday, 06:40 PM','15h ago',5],
    ];
@endphp

<div class="mb-5">
    <h1 class="text-[24px] font-bold text-[#131313] tracking-tight">SECURITY OPERATIONS</h1>
    <p class="text-[14px] font-semibold text-[#3B382F] mt-0.5">Lost Persons</p>
    <p class="text-[12px] text-[#8A857A]">Track, manage and help reunite missing persons during the event.</p>
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
        <span class="text-[10.5px] font-semibold inline-flex items-center gap-0.5" style="color:{{ $sub[1] }}"><i data-lucide="arrow-{{ $sub[0] }}" class="w-3 h-3"></i>{{ $sub[2] }}</span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_400px] gap-5 items-start mb-5">
    {{-- ══ LOST PERSONS LIST ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <p class="px-5 pt-4 text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase">Lost Persons List</p>
        <div class="px-5 py-3 flex flex-wrap items-center gap-2.5">
            <div class="relative flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#lpScope" placeholder="Search by name, description, ID..." class="w-full text-[12px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach(['All Status','All Categories','All Locations'] as $f)
            <select data-filter-select="#lpScope" class="text-[11.5px] rounded-lg border border-[#EFEDE6] px-2.5 py-2 bg-white text-[#3B382F]"><option>{{ $f }}</option>@foreach(['open','reunited','missing','found'] as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach</select>
            @endforeach
            <button data-toast="Advanced filters — coming soon" class="siarc-btn text-[11.5px] text-[#3B382F] border border-[#EFEDE6] px-2.5 py-2 rounded-lg bg-white"><i data-lucide="filter" class="w-3.5 h-3.5 text-[#8A857A]"></i>Filters</button>
            <a href="{{ $caseHref }}" class="siarc-btn siarc-btn-green text-[11.5px] px-3 py-2 rounded-lg"><i data-lucide="plus" class="w-3.5 h-3.5"></i>New Report</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['ID','PERSON','TYPE','LOCATION','STATUS','REPORTED AT','ACTIONS'] as $h)
                        <th class="px-4 py-3 text-[9.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="lpScope">
                    @foreach($rows as [$id,$name,$sub,$type,$tc,$loc,$locSub,$status,$stC,$at,$ago,$face])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($status) }} {{ Str::lower($type) }}" data-filter-text="{{ $id }} {{ $name }} {{ $sub }} {{ $loc }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-4 py-3 text-[11px] font-medium text-[#3B382F] whitespace-nowrap">{{ $id }}</td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-2.5">
                                <img src="{{ asset('images/siarc/secops-lp-'.$face.'.png') }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                <span><span class="block text-[12.5px] font-semibold text-[#131313]">{{ $name }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $sub }}</span></span>
                            </span>
                        </td>
                        <td class="px-4 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $tc }};background:{{ $tc }}18">{{ $type }}</span></td>
                        <td class="px-4 py-3 text-[11.5px] text-[#3B382F]">{{ $loc }}<br><span class="text-[10px] text-[#8A857A]">{{ $locSub }}</span></td>
                        <td class="px-4 py-3"><span class="text-[10px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $stC }};background:{{ $stC }}18">{{ $status }}</span></td>
                        <td class="px-4 py-3 text-[11px] text-[#3B382F]">{{ $at }}<br><span class="text-[10px] text-[#8A857A]">{{ $ago }}</span></td>
                        <td class="px-4 py-3">
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
        <div data-page="#lpScope" data-page-size="10" class="px-5 py-3 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[11.5px] text-[#8A857A]">Showing 1 to 5 of 32 cases</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></button>
                <button data-page-num="1" class="is-active w-7 h-7 rounded-lg text-[11.5px] font-semibold flex items-center justify-center">1</button>
                @foreach(['2','3','4','5','6','7'] as $p)<button data-page-num="{{ $p }}" class="w-7 h-7 rounded-lg border border-[#EFEDE6] text-[11.5px] font-semibold text-[#3B382F] flex items-center justify-center">{{ $p }}</button>@endforeach
                <button data-page-next class="w-7 h-7 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT: donuts ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-4">Cases by Status</p>
            <div class="flex items-center gap-5">
                <svg viewBox="0 0 120 120" class="w-[125px] h-[125px] shrink-0">
                    @php $parts = [[21,'#C0010C'],[1,'#C97A16'],[10,'#157A43']]; $tot = 32; $acc = 0; @endphp
                    @foreach($parts as [$v,$c])
                        @php $r = 46; $circ = 2 * pi() * $r; $len = $v / $tot * $circ; $off = -$acc / $tot * $circ; $acc += $v; @endphp
                        <circle cx="60" cy="60" r="{{ $r }}" fill="none" stroke="{{ $c }}" stroke-width="16" stroke-dasharray="{{ max($len - 2, 1) }} {{ $circ - $len + 2 }}" stroke-dashoffset="{{ $off - 25 }}" transform="rotate(-90 60 60)"/>
                    @endforeach
                    <text x="60" y="58" text-anchor="middle" font-size="22" font-weight="700" fill="#131313">32</text>
                    <text x="60" y="74" text-anchor="middle" font-size="9" fill="#8A857A">Total</text>
                </svg>
                <div class="space-y-2 flex-1">
                    @foreach([['Missing (Open)','21 (65.6%)','#C0010C'],['Found (Pending Reunification)','1 (3.1%)','#C97A16'],['Found & Reunited','10 (31.3%)','#157A43']] as [$lbl,$v,$c])
                    <p class="flex items-center justify-between text-[11.5px] gap-2"><span class="flex items-center gap-2 text-[#3B382F]"><span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $c }}"></span>{{ $lbl }}</span><span class="font-semibold text-[#131313] shrink-0">{{ $v }}</span></p>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-4">Cases by Location</p>
            <div class="space-y-3">
                @foreach([['Pavilion A – Main Entrance','8 (25.0%)',84,'#C0010C'],['Food Court','6 (18.8%)',62,'#C97A16'],['Central Square','5 (15.6%)',52,'#157A43'],['Pavilion B – VIP Lounge','4 (12.5%)',42,'#3565DE'],['Parking P1 – North Gate','3 (9.4%)',31,'#7C4FE0'],['Other Locations','6 (18.8%)',62,'#B0AB9F']] as [$lbl,$v,$pct,$c])
                <div class="flex items-center gap-3">
                    <span class="w-[140px] text-[11px] text-[#3B382F] shrink-0 truncate">{{ $lbl }}</span>
                    <span class="flex-1 h-[6px] rounded-full bg-[#F1F0EB] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $pct }}%;background:{{ $c }}"></span></span>
                    <span class="text-[10.5px] font-semibold text-[#3B382F] w-[62px] text-right shrink-0">{{ $v }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Quick Actions</p>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([['user-search','Report Lost Person','#C0010C'],['user-check','Report Found Person','#157A43'],['megaphone','Broadcast Alert','#3565DE'],['file-text','View All Cases','#3B382F'],['download','Export Report','#157A43'],['history','Reunification Log','#7C4FE0']] as [$ic,$lbl,$c])
                <button data-toast="{{ $lbl }} — coming soon" class="rounded-lg border border-[#EFEDE6] px-2.5 py-3 text-center hover:bg-[#FBFAF6]">
                    <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[10.5px] font-semibold text-[#3B382F] leading-tight">{{ $lbl }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[420px_1fr] gap-5 items-start">
    {{-- ══ RECENT REPORTS ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Recent Reports</p>
        <div class="space-y-3">
            @foreach([['10:15 AM','Missing person reported','Amina B., Female, 6 years — Pavilion A – Main Entrance','Open','#C0010C','search'],['09:52 AM','Missing person reported','Jean P. M., Male, 34 years — Food Court – Near Stage','Open','#C0010C','clock'],['09:10 AM','Person found','Mohammed T., Male, 8 years — Central Square – Info Desk','Reunited','#157A43','user-check'],['08:35 AM','Person reunited','Marie L., Female, 27 years — Pavilion B – VIP Lounge','Reunited','#157A43','users-round'],['Yesterday, 06:40 PM','Missing person reported','Alphonse N., Male, 67 years — Parking P1 – North Gate','Open','#C0010C','search']] as [$time,$t,$s,$st,$c,$ic])
            <div class="flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5" style="color:{{ $c }}"></i></span>
                <span class="min-w-0 flex-1"><span class="block text-[10.5px] font-semibold text-[#8A857A]">{{ $time }}</span><span class="block text-[12px] font-semibold text-[#131313]">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A] leading-snug">{{ $s }}</span></span>
                <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md shrink-0" style="color:{{ $c }};background:{{ $c }}18">{{ $st }}</span>
            </div>
            @endforeach
        </div>
        <button data-toast="All reports — coming soon" class="mt-3 w-full text-center text-[11.5px] font-semibold text-[#157A43] inline-flex items-center justify-center gap-1">View All Reports <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
    </div>

    {{-- ══ AGE GROUP CHART ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[12px] font-bold tracking-[0.06em] text-[#131313] uppercase mb-3">Age Group (Missing Persons)</p>
        <svg viewBox="0 0 560 190" class="w-full h-auto">
            @foreach([[0,168],[5,116],[10,64],[15,12]] as [$lbl,$y])
            <line x1="30" y1="{{ $y }}" x2="560" y2="{{ $y }}" stroke="#F2F1EC"/><text x="20" y="{{ $y + 3 }}" font-size="9" fill="#B0AB9F" text-anchor="end">{{ $lbl }}</text>
            @endforeach
            @foreach([['0 – 5 years',6,'#157A43'],['6 – 12 years',7,'#C97A16'],['13 – 17 years',3,'#3565DE'],['18 – 59 years',4,'#7C4FE0'],['60+ years',1,'#C0010C']] as $i => [$lbl,$v,$c])
            @php $x = 70 + $i * 100; $h = $v * 10.4; @endphp
            <rect x="{{ $x }}" y="{{ 168 - $h }}" width="34" height="{{ $h }}" rx="3" fill="{{ $c }}"/>
            <text x="{{ $x + 17 }}" y="{{ 160 - $h }}" font-size="11" font-weight="700" fill="#131313" text-anchor="middle">{{ $v }}</text>
            <text x="{{ $x + 17 }}" y="182" font-size="9" fill="#8A857A" text-anchor="middle">{{ $lbl }}</text>
            @endforeach
        </svg>
    </div>
</div>
