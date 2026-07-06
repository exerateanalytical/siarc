@php
    // ══ Security Operations — Police Request Details (verbatim from the approved design) ══
    $listHref = route('siarc.admin.secops.overview', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <a href="{{ $listHref }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#3565DE] mb-1"><i data-lucide="arrow-left" class="w-4 h-4"></i>Back to Requests List</a>
        <h1 class="flex items-center gap-2.5 text-[22px] font-bold text-[#131313] tracking-tight"><i data-lucide="shield" class="w-5 h-5 text-[#3B382F]"></i>Police Request Details <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-[#E8EFFB] text-[#3565DE]">Open</span></h1>
        <p class="text-[12px] text-[#8A857A] mt-0.5">View and manage details of police requests and their status.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <button data-toast="Request sent to printer (demo)" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="printer" class="w-4 h-4 text-[#8A857A]"></i>Print Request</button>
        <button data-toast="Request shared (demo)" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="share-2" class="w-4 h-4 text-[#8A857A]"></i>Share Request</button>
        <button data-toast="Status update — coming soon" class="siarc-btn text-[12.5px] font-bold text-white rounded-lg px-3.5 py-2" style="background:#3565DE">Update Status<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
    </div>
</div>

{{-- ══ CASE HEADER ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-[1fr_250px_340px] gap-5 items-start mb-5">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5 flex items-start gap-4">
        <img src="{{ asset('images/siarc/secops-police-crest.png') }}" alt="" class="w-[92px] h-[104px] object-contain shrink-0">
        <div class="min-w-0">
            <p class="flex items-center gap-2.5 text-[19px] font-bold text-[#131313]">PR-2026-0058 <span class="text-[10px] font-bold px-2.5 py-1 rounded-md bg-[#E8EFFB] text-[#3565DE]">Open</span></p>
            <dl class="mt-2 space-y-1.5 text-[11.5px]">
                @foreach([['Request Type','Investigation Assistance'],['Requested By','Central Police Station, Yaoundé'],['Officer In Charge','Chief Inspector Daniel N.'],['Contact','+237 6 75 98 76 54'],['Request Date','May 4, 2026 – 09:15 AM'],['Required By','May 5, 2026 – 06:00 PM  (1 day left)'],['Related Location','Pavilion C – Craft Section']] as [$k,$v])
                <div class="flex items-center gap-2"><dt class="text-[#8A857A] w-[112px] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#131313]">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-[#F5CFCF] siarc-shadow p-5" style="background:#FDF7F7">
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">Priority Level</p>
        <p class="flex items-center gap-1.5 text-[18px] font-extrabold text-[#C0010C] mt-1"><i data-lucide="alert-circle" class="w-4.5 h-4.5"></i>High</p>
        <p class="text-[11px] text-[#3B382F]">High priority request that requires prompt attention.</p>
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase mt-4">Status</p>
        <p class="flex items-center gap-1.5 text-[15px] font-bold text-[#3565DE] mt-0.5"><span class="w-2.5 h-2.5 rounded-full bg-[#3565DE]"></span>Open</p>
        <p class="text-[11px] text-[#3B382F]">Request is open and awaiting action.</p>
    </div>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[10.5px] font-bold tracking-wide text-[#3B382F] uppercase mb-3">Actions</p>
        <button data-toast="Status update — coming soon" class="w-full siarc-btn justify-center text-[12.5px] font-bold text-white rounded-lg px-3 py-2.5 mb-2" style="background:#3565DE">Update Status<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
        <div class="space-y-2">
            @foreach([['user-check','Assign to Officer'],['file-plus-2','Add Note'],['circle-help','Request Additional Info'],['upload','Upload Document']] as [$ic,$lbl])
            <button data-toast="{{ $lbl }} — coming soon" class="w-full siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A]"></i>{{ $lbl }}</button>
            @endforeach
            <button data-toast="Closing reserved for production" class="w-full siarc-btn justify-center text-[12px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2.5 hover:bg-[#FDF3F3]"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i>Close Request</button>
        </div>
    </div>
</div>

{{-- ══ TABS ══ --}}
<div data-tabs="prdetail" class="flex items-center gap-6 border-b border-[#E6E3DB] mb-5 overflow-x-auto bg-white rounded-t-xl px-5 pt-2">
    @foreach([['Overview',true],['Request Details',false],['Documents (2)',false],['Communications (3)',false],['Assignments',false],['Activity Log',false]] as [$lbl,$on])
    <button data-tab="{{ $lbl }}" class="si-tab {{ $on ? 'is-active' : '' }} whitespace-nowrap text-[12.5px]">{{ $lbl }}</button>
    @endforeach
</div>

<div data-panel="Overview" data-tabs-for="prdetail">
<div class="grid grid-cols-1 xl:grid-cols-[1fr_1fr_1fr_320px] gap-5 items-start">
    {{-- request summary --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Request Summary</p>
        <dl class="space-y-2.5 text-[12px]">
            <div><dt class="text-[#8A857A]">Purpose</dt><dd class="font-medium text-[#131313]">Investigation assistance for theft case</dd></div>
            <div><dt class="text-[#8A857A]">Description</dt><dd class="font-medium text-[#131313] leading-snug">Requesting CCTV footage and access records of Pavilion C – Craft Section between May 3, 2026 – 6:00 PM and May 4, 2026 – 8:00 AM.</dd></div>
            <div>
                <dt class="text-[#8A857A] mb-1">Information Needed</dt>
                <dd class="space-y-1">
                    @foreach(['CCTV footage (Pavilion C entrances and exits)','Access control logs','Identification of persons of interest'] as $s)
                    <p class="text-[11.5px] text-[#3B382F]">• {{ $s }}</p>
                    @endforeach
                </dd>
            </div>
            @foreach([['Reference Case No.','CP/YDE/2026/1147'],['Legal Basis','Judicial Authorization No. 1147/2026']] as [$k,$v])
            <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#131313] text-right">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Confidentiality</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDF6DC] text-[#C97A16]">Confidential</span></dd></div>
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Request Channel</dt><dd class="font-medium text-[#131313]">Official Letter</dd></div>
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Attachments</dt><dd class="font-semibold text-[#3565DE] flex items-center gap-1.5"><i data-lucide="file-text" class="w-3.5 h-3.5"></i>Police_Request_Letter.pdf</dd></div>
        </dl>
    </div>

    {{-- location info --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Location Information</p>
        <dl class="space-y-2.5 text-[12px]">
            @foreach([['Venue','SIARC 2026 Main Site'],['Area','Pavilion C – Craft Section'],['Address','Palais des Congrès, Boulevard du 20 Mai, Yaoundé'],['Access Points','Main Entrance C1, Service Gate C2']] as [$k,$v])
            <div><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#131313] leading-snug">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Zone Type</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#E8F5EC] text-[#157A43]">Restricted Area</span></dd></div>
        </dl>
        <button data-toast="Map view — coming soon" class="mt-4 w-full siarc-btn justify-center text-[12px] font-semibold text-[#3565DE] border border-[#CBDCF8] rounded-lg px-3 py-2.5 hover:bg-[#F0F5FE]"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>View Location on Map</button>
    </div>

    {{-- request progress --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Request Progress</p>
        <div class="space-y-4">
            @foreach([['#157A43','Request Submitted','Request submitted by Chief Inspector Daniel N.','May 4, 2026 – 09:15 AM',true],['#157A43','Acknowledged','Request acknowledged by Security Operations','May 4, 2026 – 09:32 AM',true],['#3565DE','Under Review','Request is being reviewed by security team','May 4, 2026 – 09:45 AM',true],['#B0AB9F','Action Pending','Awaiting approval and assignment','',false],['#B0AB9F','Completed','Request has been fulfilled','',false]] as [$c,$t,$s,$dt,$done])
            <div class="flex items-start gap-2.5 relative">
                @unless($loop->last)<span class="absolute left-[8px] top-6 bottom-[-14px] w-px bg-[#EFEDE6]"></span>@endunless
                <span class="w-[17px] h-[17px] rounded-full shrink-0 mt-0.5 relative z-10 flex items-center justify-center" style="background:{{ $done ? $c : '#F1F0EB' }}">@if($done)<i data-lucide="check" class="w-2.5 h-2.5 text-white"></i>@endif</span>
                <span class="min-w-0 flex-1"><span class="flex items-center justify-between gap-2"><b class="text-[12px] text-[#131313]">{{ $t }}</b>@if($dt)<span class="text-[9.5px] text-[#8A857A] shrink-0">{{ $dt }}</span>@endif</span><span class="block text-[10.5px] text-[#8A857A] leading-snug">{{ $s }}</span></span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- status information --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Status Information</p>
        <dl class="space-y-2 text-[12px]">
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Current Status</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#E8EFFB] text-[#3565DE]">Open</span></dd></div>
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Priority Level</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">High</span></dd></div>
            @foreach([['Assigned To','Lt. Serge M.'],['Assigned Unit','Security Operations Unit'],['Days Open','0 day(s)'],['Last Updated','May 4, 2026 – 10:20 AM'],['Updated By','Lt. Serge M.']] as [$k,$v])
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#131313]">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[420px_1fr_420px] gap-5 items-start mt-5">
    {{-- assigned team --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Assigned Team</p>
        <div class="space-y-3">
            @foreach([['Lt. Serge M.','Team Lead','+237 6 70 12 34 56','serge.m@siarc2026.cm',1],['Officer Linda K.','Security Officer','+237 6 71 23 45 67','linda.k@siarc2026.cm',2],['Operator David T.','CCTV Operator','+237 6 72 34 56 78','david.t@siarc2026.cm',3]] as [$n,$r,$tel,$mail,$f])
            <div class="flex items-start gap-2.5">
                <img src="{{ asset('images/siarc/secops-pat-'.$f.'.png') }}" class="w-9 h-9 rounded-full object-cover" alt="">
                <span class="min-w-0 flex-1">
                    <b class="block text-[12px] text-[#131313]">{{ $n }}</b><span class="block text-[10.5px] text-[#8A857A]">{{ $r }}</span>
                    <span class="block text-[10.5px] text-[#3B382F] mt-0.5"><i data-lucide="phone" class="w-3 h-3 inline text-[#8A857A]"></i> {{ $tel }} · <i data-lucide="mail" class="w-3 h-3 inline text-[#8A857A]"></i> {{ $mail }}</span>
                </span>
                <span class="text-[10px] font-semibold text-[#157A43] inline-flex items-center gap-1 shrink-0"><span class="w-1.5 h-1.5 rounded-full bg-[#157A43]"></span>Online</span>
            </div>
            @endforeach
        </div>
        <button data-toast="Full team — coming soon" class="mt-3.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#3565DE] border border-[#CBDCF8] rounded-lg px-3 py-2 hover:bg-[#F0F5FE]">View Full Team</button>
    </div>

    {{-- related resources --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Related Resources</p>
        <div class="space-y-3">
            @foreach([['video','CCTV Cameras','12 Cameras in Pavilion C','View Cameras'],['key-round','Access Points','2 Access Points','View Access Logs'],['users','Security Personnel','3 Personnel Assigned','View Personnel'],['alert-triangle','Nearby Incidents','1 Incident Reported','View Incidents']] as [$ic,$k,$v,$btn])
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-lg bg-[#F5F3EE] border border-[#EFEDE6] flex items-center justify-center shrink-0"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-[#3B382F]"></i></span>
                <span class="min-w-0 flex-1"><b class="block text-[12px] text-[#131313]">{{ $k }}</b><span class="block text-[10.5px] text-[#8A857A]">{{ $v }}</span></span>
                <button data-toast="{{ $btn }} — coming soon" class="shrink-0 siarc-btn text-[11px] font-semibold text-[#3565DE] border border-[#CBDCF8] rounded-lg px-3 py-1.5 hover:bg-[#F0F5FE]">{{ $btn }}</button>
            </div>
            @endforeach
        </div>
    </div>

    {{-- recent activity --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Recent Activity</p>
        <div class="space-y-3">
            @foreach([['pencil','#3565DE','May 4, 10:20 AM','Request status updated to Open','By: Lt. Serge M.'],['clock','#C97A16','May 4, 09:45 AM','Request is under review','By: Security Operations'],['info','#3565DE','May 4, 09:32 AM','Request acknowledged','By: Security Operations'],['plus','#157A43','May 4, 09:15 AM','Request submitted','By: Chief Inspector Daniel N.']] as [$ic,$c,$dt,$t,$by])
            <div class="flex items-start gap-2.5">
                <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background:{{ $c }}1a"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5" style="color:{{ $c }}"></i></span>
                <span><span class="block text-[10.5px] font-semibold text-[#8A857A]">{{ $dt }}</span><span class="block text-[12px] font-medium text-[#131313] leading-snug">{{ $t }}</span><span class="block text-[10.5px] text-[#8A857A]">{{ $by }}</span></span>
            </div>
            @endforeach
        </div>
        <button data-toast="Full activity log — coming soon" class="mt-3 text-[11.5px] font-semibold text-[#3565DE] inline-flex items-center gap-1">View All Activity <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
    </div>
</div>
</div>

@foreach(['Request Details','Documents (2)','Communications (3)','Assignments','Activity Log'] as $t)
<div data-panel="{{ $t }}" data-tabs-for="prdetail" hidden>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-8 text-center max-w-[520px] mx-auto">
        <span class="w-12 h-12 rounded-xl bg-[#E8EFFB] flex items-center justify-center mx-auto mb-3"><i data-lucide="shield" class="w-6 h-6 text-[#3565DE]"></i></span>
        <h3 class="text-[15px] font-bold text-[#131313]">{{ $t }}</h3>
        <p class="text-[12px] text-[#8A857A] mt-1.5">Full details are summarised on the Overview tab of this demo case.</p>
    </div>
</div>
@endforeach

<div class="mt-5 rounded-xl bg-[#FDF9EA] border border-[#F3E5B2] px-5 py-3.5 flex items-center gap-2.5">
    <i data-lucide="shield-check" class="w-4 h-4 text-[#C97A16] shrink-0"></i>
    <p class="text-[12.5px] text-[#7A5E00]">This is an official police request. Please handle all information in accordance with security protocols and legal requirements.</p>
</div>
