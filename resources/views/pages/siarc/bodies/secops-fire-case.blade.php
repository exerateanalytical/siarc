@php
    // ══ Security Operations — Fire Alert Detail (verbatim from the approved design) ══
    $listHref = route('siarc.admin.secops.fire', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="flex items-center gap-2.5 text-[22px] font-bold text-[#131313] tracking-tight"><i data-lucide="flame" class="w-5 h-5 text-[#C0010C]"></i>Fire Alert Detail <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-[#FDF3E0] text-[#C97A16]">Under Response</span></h1>
        <p class="text-[12px] text-[#8A857A] mt-0.5">View comprehensive information about the fire alert, response status and related activities.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <button data-toast="Report sent to printer (demo)" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="printer" class="w-4 h-4 text-[#8A857A]"></i>Print Report</button>
        <button data-toast="Alert shared (demo)" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="share-2" class="w-4 h-4 text-[#8A857A]"></i>Share Alert</button>
        <button data-toast="Status update — coming soon" class="siarc-btn text-[12.5px] font-bold text-white rounded-lg px-3.5 py-2" style="background:#C0010C">Update Status<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
    </div>
</div>

{{-- ══ CASE HEADER ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-[1fr_250px_360px] gap-5 items-start mb-5">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5 flex items-start gap-4">
        <span class="relative shrink-0">
            <img src="{{ asset('images/siarc/secops-fire-photo.png') }}" alt="" class="w-[180px] h-[160px] rounded-xl object-cover border border-[#EFEDE6]">
            <span class="absolute bottom-2 left-2 text-[9.5px] font-bold text-white bg-[#C0010C] rounded-md px-2 py-1 inline-flex items-center gap-1"><i data-lucide="flame" class="w-3 h-3"></i>High Severity</span>
        </span>
        <div class="min-w-0">
            <p class="text-[19px] font-bold text-[#131313]">FA-2026-0012</p>
            <p class="text-[13.5px] font-semibold text-[#131313]">Structural Fire – Pavilion C – Craft Section</p>
            <dl class="mt-2.5 space-y-1.5 text-[11.5px]">
                @foreach([['map-pin','Location','Pavilion C – Craft Section, Main Exhibition Area'],['clock','Reported At','May 4, 2026 – 10:18 AM (6 min ago)'],['flame','Alert Type','Structural Fire'],['user-round','Reported By','Visitor (Mobile App) – ID: VIS-45892'],['cloud-sun','Weather','28°C, Clear, Wind 8 km/h'],['maximize','Total Area Affected','~ 350 m² (Estimated)']] as [$ic,$k,$v])
                <div class="flex items-center gap-2"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A] shrink-0"></i><dt class="text-[#8A857A] shrink-0">{{ $k }}:</dt><dd class="font-medium text-[#131313] truncate">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-[#F5CFCF] siarc-shadow p-5" style="background:#FDF7F7">
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">Status</p>
        <p class="text-[18px] font-extrabold text-[#C0010C] mt-1">Under Response</p>
        <p class="text-[11px] text-[#3B382F]">Fire unit is on route and response is in progress.</p>
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase mt-4 mb-1.5">Priority Level</p>
        <span class="flex items-center gap-1.5">
            @foreach([1,1,1,0,0] as $on)<span class="w-3.5 h-3.5 rounded-full {{ $on ? 'bg-[#C0010C]' : 'bg-[#EAD8D8]' }}"></span>@endforeach
        </span>
        <p class="text-[12.5px] font-bold text-[#131313] mt-1.5">High Priority</p>
    </div>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[10.5px] font-bold tracking-wide text-[#3B382F] uppercase mb-3">Actions</p>
        <div class="grid grid-cols-2 gap-2.5">
            <button data-toast="Status update — coming soon" class="siarc-btn justify-center text-[12px] font-bold text-white rounded-lg px-3 py-2.5" style="background:#C0010C">Update Status<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
            <button data-toast="Additional unit dispatched (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="truck" class="w-3.5 h-3.5 text-[#8A857A]"></i>Dispatch Additional Unit</button>
            <button data-toast="Reporter contacted (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#8A857A]"></i>Contact Reporter</button>
            <button data-toast="Note added (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="file-plus-2" class="w-3.5 h-3.5 text-[#8A857A]"></i>Add Note</button>
            <button data-toast="Media upload — coming soon" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="upload" class="w-3.5 h-3.5 text-[#8A857A]"></i>Upload Media</button>
            <button data-toast="Shared with agencies (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="share-2" class="w-3.5 h-3.5 text-[#8A857A]"></i>Share with Agencies</button>
        </div>
        <button data-toast="More actions — coming soon" class="mt-2.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]">More Actions<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
    </div>
</div>

{{-- ══ TABS ══ --}}
<div data-tabs="fadetail" class="flex items-center gap-6 border-b border-[#E6E3DB] mb-5 overflow-x-auto bg-white rounded-t-xl px-5 pt-2">
    @foreach([['Overview',true],['Response & Units',false],['Timeline',false],['Media (4)',false],['Affected Area',false],['Notes (2)',false],['Documents (1)',false]] as [$lbl,$on])
    <button data-tab="{{ $lbl }}" class="si-tab {{ $on ? 'is-active' : '' }} whitespace-nowrap text-[12.5px]">{{ $lbl }}</button>
    @endforeach
</div>

<div data-panel="Overview" data-tabs-for="fadetail" class="grid grid-cols-1 xl:grid-cols-[280px_1fr_440px] gap-5 items-start">
    {{-- incident info + reported by --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Incident Information</p>
            <dl class="space-y-2 text-[12px]">
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Alert ID</dt><dd class="font-semibold text-[#131313]">FA-2026-0012</dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Alert Type</dt><dd class="font-medium text-[#131313]">Structural Fire</dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Severity</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">High</span></dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Status</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDF3E0] text-[#C97A16]">Under Response</span></dd></div>
                @foreach([['Reported At','May 4, 2026 – 10:18 AM'],['Source','Mobile App'],['Exact Location','Pavilion C – Craft Section'],['Landmark','Near Main Entrance Gate 2'],['Assigned Unit','Fire Unit 2'],['In Charge','Captain Alain T.'],['Est. Area Affected','~ 350 m²'],['Potential Impact','High – Multiple booths'],['Hazard Type','Open Flame / Smoke'],['Last Updated','May 4, 2026 – 10:22 AM']] as [$k,$v])
                <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#131313] text-right leading-snug">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Reported By</p>
            <p class="flex items-center gap-2.5 mb-2.5">
                <span class="w-9 h-9 rounded-full bg-[#F1F0EB] flex items-center justify-center"><i data-lucide="user-round" class="w-4.5 h-4.5 text-[#8A857A]"></i></span>
                <span><b class="block text-[12.5px] text-[#131313]">Visitor (Mobile App)</b><span class="block text-[10.5px] text-[#8A857A]">VIS-45892</span></span>
            </p>
            <dl class="space-y-1.5 text-[11.5px]">
                @foreach([['phone','+237 691 23 45 67'],['mail','visitor45892@example.com']] as [$ic,$v])
                <div class="flex items-center gap-2"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A]"></i><dd class="text-[#3B382F]">{{ $v }}</dd></div>
                @endforeach
                @foreach([['Report Method','Mobile Application'],['Device','iPhone 13'],['Report Time','May 4, 2026 – 10:18 AM']] as [$k,$v])
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#131313]">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <button data-toast="Reporter contacted (demo)" class="mt-3 w-full siarc-btn justify-center text-[12px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2 hover:bg-[#FDF3F3]"><i data-lucide="phone" class="w-3.5 h-3.5"></i>Contact Reporter</button>
        </div>
    </div>

    {{-- situation + response --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Situation Overview</p>
            <p class="text-[12.5px] text-[#3B382F] leading-relaxed mb-4">Fire reported in Pavilion C (Craft Section). Thick black smoke observed coming from the central area of the pavilion. Evacuation of nearby visitors in progress. Fire unit dispatched and on route.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-lg border border-[#EFEDE6] bg-[#FBFAF6] p-3.5">
                    <p class="text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-2">Possible Causes (Preliminary)</p>
                    @foreach(['Electrical fault','Short circuit','Overheating equipment','Flammable materials'] as $s)
                    <p class="text-[12px] text-[#3B382F] leading-relaxed">• {{ $s }}</p>
                    @endforeach
                </div>
                <div class="rounded-lg border border-[#EFEDE6] bg-[#FBFAF6] p-3.5">
                    <p class="text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-2">Current Risks</p>
                    @foreach([['flame','Smoke inhalation risk'],['building','Structure damage'],['move','Spread to adjacent booths'],['zap','Electrical hazards']] as [$ic,$s])
                    <p class="flex items-center gap-2 text-[12px] text-[#3B382F] leading-relaxed"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#C0010C]"></i>{{ $s }}</p>
                    @endforeach
                </div>
            </div>
            <p class="text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mt-4 mb-2">Initial Action Taken</p>
            <div class="space-y-1.5">
                @foreach([['Alert received and verified','10:18 AM'],['Security team dispatched to scene','10:19 AM'],['Evacuation of nearby area initiated','10:20 AM'],['Fire unit dispatched','10:20 AM']] as [$t,$time])
                <p class="flex items-center justify-between text-[12px]"><span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#157A43]"></i>{{ $t }}</span><span class="text-[10.5px] text-[#8A857A]">{{ $time }}</span></p>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Response Overview</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                @foreach([['truck','Units Dispatched','1','#C0010C'],['truck','On Route','1','#C97A16'],['users','On Scene','0','#157A43'],['check-circle-2','Completed','0','#3565DE']] as [$ic,$k,$v,$c])
                <span class="rounded-lg border border-[#EFEDE6] px-3 py-2.5 text-center">
                    <i data-lucide="{{ $ic }}" class="w-4 h-4 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[16px] font-bold text-[#131313]">{{ $v }}</span>
                    <span class="block text-[9.5px] text-[#8A857A] leading-tight">{{ $k }}</span>
                </span>
                @endforeach
            </div>
            <div class="grid grid-cols-3 gap-3 text-[12px]">
                <span><span class="block text-[10px] text-[#8A857A] uppercase font-bold tracking-wide">Estimated Arrival Time</span><b class="text-[20px] text-[#131313]">3 min</b><br><span class="text-[10.5px] text-[#8A857A]">(10:24 AM)</span></span>
                <span><span class="block text-[10px] text-[#8A857A] uppercase font-bold tracking-wide">Fire Unit</span><b class="text-[#131313]">Fire Unit 2</b></span>
                <span><span class="block text-[10px] text-[#8A857A] uppercase font-bold tracking-wide">Team Leader</span><b class="text-[#131313]">Captain Alain T.</b></span>
            </div>
            <button data-toast="Unit details — coming soon" class="mt-3.5 w-full max-w-[240px] siarc-btn justify-center text-[12px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2 hover:bg-[#FDF3F3]">View Unit Details</button>
        </div>
    </div>

    {{-- map + weather + quick info + risk --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">Location Map</p>
                <span class="text-[10.5px] font-semibold text-[#C0010C] inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#C0010C]"></span>Live</span>
            </div>
            <img src="{{ asset('images/siarc/secops-map-fire.png') }}" alt="" class="w-full h-auto rounded-xl border border-[#EFEDE6]">
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Weather on Site</p>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-2.5"><i data-lucide="sun" class="w-9 h-9 text-[#E6B201]"></i><span><b class="block text-[19px] text-[#131313]">28°C</b><span class="block text-[10.5px] text-[#8A857A]">Clear</span></span></span>
                <dl class="flex-1 space-y-1 text-[11.5px]">
                    @foreach([['droplets','Humidity','48%'],['wind','Wind','8 km/h'],['eye','Visibility','10 km']] as [$ic,$k,$v])
                    <div class="flex items-center justify-between"><dt class="flex items-center gap-1.5 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5"></i>{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
                <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Quick Information</p>
                <dl class="space-y-1.5 text-[11px]">
                    @foreach([['Total People Affected','36'],['Evacuated','28'],['Injured','0'],['Fatalities','0'],['Nearby Facilities','Food Court, Pavilion B'],['Power Status','Normal (Backup Active)'],['Water Supply','Available'],['Communication','Stable']] as [$k,$v])
                    <div class="flex items-start justify-between gap-2"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313] text-right">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
            <div class="space-y-4">
                <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4">
                    <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2">Attached Documents</p>
                    <p class="flex items-center gap-2 text-[11.5px]"><i data-lucide="file-text" class="w-4 h-4 text-[#C0010C]"></i><span><b class="block text-[#131313]">Floorplan_Pavilion_C.pdf</b><span class="text-[10px] text-[#8A857A]">PDF – 1.2 MB</span></span><button data-toast="Download — coming soon" class="ml-auto"><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i></button></p>
                </div>
                <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-4 text-center">
                    <p class="text-[10.5px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2">Risk Level Assessment</p>
                    <svg viewBox="0 0 120 68" class="w-[120px] h-auto mx-auto">
                        @foreach([['#157A43',180,225],['#E6B201',225,270],['#C97A16',270,315],['#C0010C',315,360]] as [$c,$a1,$a2])
                        @php $r = 46; $x1 = 60 + $r * cos(deg2rad($a1)); $y1 = 60 + $r * sin(deg2rad($a1)); $x2 = 60 + $r * cos(deg2rad($a2)); $y2 = 60 + $r * sin(deg2rad($a2)); @endphp
                        <path d="M{{ $x1 }} {{ $y1 }} A{{ $r }} {{ $r }} 0 0 1 {{ $x2 }} {{ $y2 }}" fill="none" stroke="{{ $c }}" stroke-width="10" stroke-linecap="round"/>
                        @endforeach
                        <line x1="60" y1="60" x2="88" y2="26" stroke="#131313" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="60" cy="60" r="4" fill="#131313"/>
                    </svg>
                    <p class="text-[12.5px] font-bold text-[#C0010C]">High Risk</p>
                    <p class="text-[10px] text-[#8A857A]">Immediate attention required</p>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach(['Response & Units','Timeline','Media (4)','Affected Area','Notes (2)','Documents (1)'] as $t)
<div data-panel="{{ $t }}" data-tabs-for="fadetail" hidden>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-8 text-center max-w-[520px] mx-auto">
        <span class="w-12 h-12 rounded-xl bg-[#FDE8E8] flex items-center justify-center mx-auto mb-3"><i data-lucide="flame" class="w-6 h-6 text-[#C0010C]"></i></span>
        <h3 class="text-[15px] font-bold text-[#131313]">{{ $t }}</h3>
        <p class="text-[12px] text-[#8A857A] mt-1.5">Full details are summarised on the Overview tab of this demo case.</p>
    </div>
</div>
@endforeach

<div class="mt-5 rounded-xl bg-[#FDF7F7] border border-[#F5CFCF] px-5 py-3.5 flex items-center justify-between gap-4">
    <p class="flex items-center gap-2.5 text-[12.5px] text-[#8A1A1A]"><i data-lucide="flame" class="w-4 h-4 text-[#C0010C] shrink-0"></i>This is a high severity fire alert. Ensure safety protocols are followed and provide regular updates.</p>
    <span class="flex items-center gap-2.5 shrink-0">
        <a href="{{ $listHref }}" class="siarc-btn text-[12.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 bg-white hover:bg-[#FBFAF6]"><i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A]"></i>Back to List</a>
        <button data-toast="Escalation — coming soon" class="siarc-btn text-[12.5px] font-bold text-white rounded-lg px-4 py-2.5" style="background:#C0010C"><i data-lucide="alert-triangle" class="w-4 h-4"></i>Escalate to Major Incident</button>
    </span>
</div>
