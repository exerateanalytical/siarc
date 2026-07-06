@php
    // ══ Security Operations — Medical Emergency Detail (verbatim from the approved design) ══
    $listHref = route('siarc.admin.secops.medical', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="flex items-center gap-2.5 text-[22px] font-bold text-[#131313] tracking-tight"><i data-lucide="heart-pulse" class="w-5 h-5 text-[#3B382F]"></i>Medical Emergency Detail <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-[#E8F5EC] text-[#157A43] inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-[#157A43]"></span>Active</span></h1>
        <p class="text-[12px] text-[#8A857A] mt-0.5">Real-time information and management of the medical emergency.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <button data-toast="Status update — coming soon" class="siarc-btn text-[12.5px] font-bold text-white rounded-lg px-3.5 py-2" style="background:#C0010C"><i data-lucide="refresh-cw" class="w-4 h-4"></i>Update Status<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
        <button data-toast="Resource assignment — coming soon" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="users" class="w-4 h-4 text-[#8A857A]"></i>Assign Resources</button>
    </div>
</div>

{{-- ══ CASE HEADER ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-[1fr_250px_360px] gap-5 items-start mb-5">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5 flex items-start gap-4">
        <span class="w-16 h-16 rounded-full bg-[#FDE8E8] flex items-center justify-center shrink-0"><i data-lucide="heart" class="w-8 h-8 text-[#C0010C]"></i></span>
        <div>
            <p class="flex items-center gap-2.5 text-[19px] font-bold text-[#131313]">ME-2026-0016 <span class="text-[10px] font-bold px-2.5 py-1 rounded-md bg-[#FDE8E8] text-[#C0010C]">Active</span></p>
            <p class="text-[15px] font-semibold text-[#131313]">Cardiac Issue</p>
            <p class="mt-1.5 text-[12px] text-[#3B382F] flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#8A857A]"></i>Pavilion A – Main Entrance</p>
            <p class="text-[12px] text-[#3B382F] flex items-center gap-1.5"><i data-lucide="clock" class="w-3.5 h-3.5 text-[#8A857A]"></i>Reported: Today, May 4, 2026 – 10:18 AM (6 min ago)</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-[#F5CFCF] siarc-shadow p-5" style="background:#FDF7F7">
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">Severity Level</p>
        <p class="text-[19px] font-extrabold text-[#C0010C] mt-1">Critical</p>
        <span class="flex items-center gap-1.5 mt-1.5">
            @foreach([1,1,1,0,1] as $on)<span class="w-3.5 h-3.5 rounded-full {{ $on ? 'bg-[#C0010C]' : 'bg-[#EAD8D8]' }}"></span>@endforeach
        </span>
        <p class="text-[11px] text-[#3B382F] mt-1.5">Requires immediate attention</p>
    </div>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[10.5px] font-bold tracking-wide text-[#3B382F] uppercase mb-3">Actions</p>
        <div class="grid grid-cols-2 gap-2.5">
            <button data-toast="Status update — coming soon" class="siarc-btn justify-center text-[12px] font-bold text-white rounded-lg px-3 py-2.5" style="background:#C0010C"><i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>Update Status</button>
            <button data-toast="Resource assignment — coming soon" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="users" class="w-3.5 h-3.5 text-[#8A857A]"></i>Assign Resources</button>
            <button data-toast="Patient / reporter contacted (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#8A857A]"></i>Contact Patient / Reporter</button>
            <button data-toast="Note added (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="file-plus-2" class="w-3.5 h-3.5 text-[#8A857A]"></i>Add Notes</button>
        </div>
        <button data-toast="More actions — coming soon" class="mt-2.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]">More Actions<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
    </div>
</div>

{{-- ══ TABS ══ --}}
<div data-tabs="medetail" class="flex items-center gap-6 border-b border-[#E6E3DB] mb-5 overflow-x-auto bg-white rounded-t-xl px-5 pt-2">
    @foreach([['Overview',true],['Patient Information',false],['Timeline',false],['Response Team',false],['Location',false],['Notes & Updates',false],['Documents',false]] as [$lbl,$on])
    <button data-tab="{{ $lbl }}" class="si-tab {{ $on ? 'is-active' : '' }} whitespace-nowrap text-[12.5px]">{{ $lbl }}</button>
    @endforeach
</div>

<div data-panel="Overview" data-tabs-for="medetail" class="grid grid-cols-1 xl:grid-cols-[300px_1fr_440px] gap-5 items-start">
    {{-- summary + vitals --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Emergency Summary</p>
            <dl class="space-y-2 text-[12px]">
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Emergency ID</dt><dd class="font-semibold text-[#131313]">ME-2026-0016</dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Type</dt><dd class="font-medium text-[#131313]">Cardiac Issue</dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Status</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">Active</span></dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Severity</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">Critical</span></dd></div>
                @foreach([['Reported At','May 4, 2026 – 10:18 AM'],['Location','Pavilion A – Main Entrance'],['Exact Location','Near Entrance Gate 2'],['Reported By','Visitor (Mobile App)  +237 6XX XXX XXX'],['Assigned Team','Medical Team 1  Dr. Ngao S.'],['Response Time','2 min'],['Last Update','May 4, 2026 – 10:22 AM (2 min ago)']] as [$k,$v])
                <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#131313] text-right leading-snug">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Vital Signs (On Scene)</p>
            <div class="space-y-2.5">
                @foreach([['Blood Pressure','160 / 95 mmHg','High','#C0010C'],['Heart Rate','112 bpm','High','#C0010C'],['Respiratory Rate','24 /min','High','#C0010C'],['Oxygen Saturation','91 %','Low','#C97A16'],['Temperature','36.9 °C','Normal','#157A43']] as [$k,$v,$lvl,$c])
                <p class="flex items-center justify-between text-[12px]">
                    <span class="text-[#8A857A]">{{ $k }}</span>
                    <span class="flex items-center gap-2"><b class="text-[#131313]">{{ $v }}</b><span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $c }};background:{{ $c }}18">{{ $lvl }}</span></span>
                </p>
                @endforeach
            </div>
            <p class="flex items-center justify-between text-[10.5px] text-[#8A857A] mt-3 pt-2.5 border-t border-[#F5F3EE]"><span>Recorded by: Nurse A. M.</span><span>10:19 AM</span></p>
        </div>
    </div>

    {{-- situation + response team --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-2.5">Situation Description</p>
            <p class="text-[12.5px] text-[#3B382F] leading-relaxed mb-4">Visitor reported chest pain and difficulty breathing while walking towards the main entrance.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-lg border border-[#EFEDE6] bg-[#FBFAF6] p-3.5">
                    <p class="text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-2">Symptoms reported</p>
                    @foreach(['Chest pain','Shortness of breath','Sweating','Dizziness'] as $s)
                    <p class="text-[12px] text-[#3B382F] leading-relaxed">• {{ $s }}</p>
                    @endforeach
                </div>
                <div class="rounded-lg border border-[#EFEDE6] bg-[#FBFAF6] p-3.5">
                    <p class="text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-2">Condition assessed</p>
                    <p class="flex items-center gap-2 text-[13px] font-bold text-[#C0010C]"><span class="w-6 h-6 rounded-full bg-[#FDE8E8] flex items-center justify-center"><i data-lucide="heart" class="w-3.5 h-3.5 text-[#C0010C]"></i></span>Critical</p>
                    <p class="text-[11.5px] text-[#3B382F] mt-1.5">Patient is conscious</p>
                    <p class="text-[11.5px] text-[#3B382F]">Requires immediate medical attention</p>
                </div>
            </div>
            <p class="text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mt-4 mb-2">Initial Action Taken</p>
            <div class="space-y-1.5">
                @foreach([['Patient assessed on scene','10:20 AM'],['Oxygen support provided',''],['Vital signs monitored',''],['Preparing for transport to Medical Station','']] as [$t,$time])
                <p class="flex items-center justify-between text-[12px]"><span class="flex items-center gap-2 text-[#3B382F]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-[#157A43]"></i>{{ $t }}</span>@if($time)<span class="text-[10.5px] text-[#8A857A]">{{ $time }}</span>@endif</p>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">Response Team</p>
                <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-[#E8F5EC] text-[#157A43] inline-flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>On Scene</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2.5">
                    @foreach([['Dr. Ngao S.','Team Lead / Paramedic',1],['Nurse A. M.','Paramedic',2],['Emergency Technician','Support Staff',3]] as [$n,$r,$f])
                    <p class="flex items-center gap-2.5"><img src="{{ asset('images/siarc/secops-pat-'.$f.'.png') }}" class="w-8 h-8 rounded-full object-cover" alt=""><span><b class="block text-[12px] text-[#131313]">{{ $n }}</b><span class="block text-[10.5px] text-[#8A857A]">{{ $r }}</span></span></p>
                    @endforeach
                </div>
                <dl class="space-y-2 text-[12px]">
                    @foreach([['ETA to Destination','2 min'],['Destination','Medical Station – Level 1'],['Transport','Medical Cart']] as [$k,$v])
                    <div><dt class="text-[10.5px] text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
            <button data-toast="Team details — coming soon" class="mt-3.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]">View Team Details</button>
        </div>
    </div>

    {{-- map + weather + timeline --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase">Location Map</p>
                <span class="text-[10.5px] font-semibold text-[#157A43] inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>Live</span>
            </div>
            <img src="{{ asset('images/siarc/secops-map-medical-detail.png') }}" alt="" class="w-full h-auto rounded-xl border border-[#EFEDE6]">
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Weather Conditions</p>
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-2.5"><i data-lucide="cloud-sun" class="w-9 h-9 text-[#C97A16]"></i><span><b class="block text-[19px] text-[#131313]">28°C</b><span class="block text-[10.5px] text-[#8A857A]">Partly Cloudy</span></span></span>
                <dl class="flex-1 space-y-1 text-[11.5px]">
                    @foreach([['droplets','Humidity','62%'],['wind','Wind','14 km/h'],['eye','Visibility','8 km']] as [$ic,$k,$v])
                    <div class="flex items-center justify-between"><dt class="flex items-center gap-1.5 text-[#8A857A]"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5"></i>{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Timeline</p>
            <div class="space-y-3.5">
                @foreach([['megaphone','#C0010C','10:18 AM','Emergency reported','Visitor reported cardiac issue at Pavilion A','Reported'],['send','#C97A16','10:19 AM','Medical team dispatched','Medical Team 1 dispatched to location','Dispatched'],['users','#3565DE','10:20 AM','Team arrived on scene','Initial assessment started','On Scene'],['activity','#7C4FE0','10:22 AM','Patient stabilized','Oxygen support and monitoring in progress','In Progress']] as [$ic,$c,$time,$t,$s,$chip])
                <div class="flex items-start gap-2.5 relative">
                    @unless($loop->last)<span class="absolute left-[15px] top-9 bottom-[-14px] w-px bg-[#EFEDE6]"></span>@endunless
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 relative z-10" style="background:{{ $c }}1a"><i data-lucide="{{ $ic }}" class="w-4 h-4" style="color:{{ $c }}"></i></span>
                    <span class="min-w-0 flex-1"><span class="block text-[10.5px] text-[#8A857A]">{{ $time }}</span><span class="block text-[12px] font-bold text-[#131313]">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A] leading-snug">{{ $s }}</span></span>
                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-md shrink-0" style="color:{{ $c }};background:{{ $c }}18">{{ $chip }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@foreach(['Patient Information','Timeline','Response Team','Location','Notes & Updates','Documents'] as $t)
<div data-panel="{{ $t }}" data-tabs-for="medetail" hidden>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-8 text-center max-w-[520px] mx-auto">
        <span class="w-12 h-12 rounded-xl bg-[#FDE8E8] flex items-center justify-center mx-auto mb-3"><i data-lucide="heart-pulse" class="w-6 h-6 text-[#C0010C]"></i></span>
        <h3 class="text-[15px] font-bold text-[#131313]">{{ $t }}</h3>
        <p class="text-[12px] text-[#8A857A] mt-1.5">Full details are summarised on the Overview tab of this demo case.</p>
    </div>
</div>
@endforeach

<div class="mt-5 rounded-xl bg-[#FDF7F7] border border-[#F5CFCF] px-5 py-3.5 flex items-center justify-between gap-4">
    <p class="flex items-center gap-2.5 text-[12.5px] text-[#8A1A1A]"><i data-lucide="alert-triangle" class="w-4 h-4 text-[#C0010C] shrink-0"></i>This is a critical medical emergency. Continue monitoring and update status regularly.</p>
    <span class="flex items-center gap-2.5 shrink-0">
        <a href="{{ $listHref }}" class="siarc-btn text-[12.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-4 py-2.5 bg-white hover:bg-[#FBFAF6]"><i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A]"></i>Back to List</a>
        <button data-toast="Escalation — coming soon" class="siarc-btn text-[12.5px] font-bold text-white rounded-lg px-4 py-2.5" style="background:#C0010C"><i data-lucide="alert-triangle" class="w-4 h-4"></i>Escalate Emergency</button>
    </span>
</div>
