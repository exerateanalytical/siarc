@php
    // ══ Security Operations — Lost Person Detail (verbatim from the approved design) ══
    $listHref = route('siarc.admin.secops.lost', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="flex items-center gap-2.5 text-[22px] font-bold text-[#131313] tracking-tight"><i data-lucide="user-search" class="w-5 h-5 text-[#3B382F]"></i>Lost Person Detail <span class="text-[10px] font-bold px-2 py-1 rounded-md bg-[#E8F5EC] text-[#157A43] inline-flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-[#157A43]"></span>Active</span></h1>
        <p class="text-[12px] text-[#8A857A] mt-0.5">Real-time information and management of missing persons cases.</p>
    </div>
    <div class="flex items-center gap-2.5">
        <button data-toast="Poster sent to printer (demo)" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="printer" class="w-4 h-4 text-[#8A857A]"></i>Print Poster</button>
        <button data-toast="Case shared with partners (demo)" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="share-2" class="w-4 h-4 text-[#8A857A]"></i>Share Case</button>
        <button data-toast="Report update — coming soon" class="siarc-btn text-[12.5px] px-3.5 py-2 rounded-lg text-white" style="background:#C0010C"><i data-lucide="megaphone" class="w-4 h-4"></i>Report Update</button>
    </div>
</div>

{{-- ══ CASE HEADER ══ --}}
<div class="grid grid-cols-1 xl:grid-cols-[1fr_260px_360px] gap-5 items-start mb-5">
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <a href="{{ $listHref }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#3565DE] mb-3"><i data-lucide="arrow-left" class="w-4 h-4"></i>Back to List</a>
        <div class="flex items-start gap-4">
            <img src="{{ asset('images/siarc/secops-lp-detail.png') }}" alt="" class="w-[150px] h-[150px] rounded-xl object-cover border border-[#EFEDE6]">
            <div class="min-w-0">
                <p class="flex items-center gap-2.5 text-[19px] font-bold text-[#131313]">LP-2026-0032 <span class="text-[10px] font-bold px-2.5 py-1 rounded-md bg-[#FDE8E8] text-[#C0010C]">MISSING</span></p>
                <p class="text-[15px] font-semibold text-[#131313]">Amina B.</p>
                <p class="text-[12px] text-[#8A857A]">Female, 12 years old</p>
                <dl class="mt-3 space-y-1.5 text-[12px]">
                    @foreach([['megaphone','Reported:','May 4, 2026 – 09:15 AM (1h 9m ago)'],['clock','Last Seen:','May 4, 2026 – 08:30 AM'],['map-pin','Location:','Food Court, Central Square, Yaoundé'],['user-round','Reported By:','Fatou B. (Mother)   +237 675 123 456']] as [$ic,$k,$v])
                    <div class="flex items-center gap-2"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A] shrink-0"></i><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#131313]">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-[#F5CFCF] siarc-shadow p-5" style="background:#FDF7F7">
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">Case Status</p>
        <p class="text-[19px] font-extrabold text-[#C0010C] mt-1">Missing</p>
        <p class="text-[11px] text-[#3B382F]">Case is active and under investigation.</p>
        <p class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase mt-4 mb-1.5">Priority Level</p>
        <span class="flex items-center gap-1.5">
            @foreach([1,1,1,0,0] as $on)<span class="w-3.5 h-3.5 rounded-full {{ $on ? 'bg-[#C0010C]' : 'bg-[#EAD8D8]' }}"></span>@endforeach
        </span>
        <p class="text-[12.5px] font-bold text-[#131313] mt-1.5">High Priority</p>
    </div>

    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[10.5px] font-bold tracking-wide text-[#3B382F] uppercase mb-3">Actions</p>
        <div class="grid grid-cols-2 gap-2.5">
            <button data-toast="Status update — coming soon" class="siarc-btn justify-center text-[12px] font-bold text-white rounded-lg px-3 py-2.5" style="background:#3565DE">Update Status<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
            <button data-toast="Officer assignment — coming soon" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="user-check" class="w-3.5 h-3.5 text-[#8A857A]"></i>Assign to Officer</button>
            <button data-toast="Note added (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="file-plus-2" class="w-3.5 h-3.5 text-[#8A857A]"></i>Add Note</button>
            <button data-toast="Family contacted (demo)" class="siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#8A857A]"></i>Contact Family</button>
        </div>
        <button data-toast="More actions — coming soon" class="mt-2.5 w-full siarc-btn justify-center text-[12px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]">More Actions<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
    </div>
</div>

{{-- ══ TABS ══ --}}
<div data-tabs="lpdetail" class="flex items-center gap-6 border-b border-[#E6E3DB] mb-5 overflow-x-auto bg-white rounded-t-xl px-5 pt-2">
    @foreach([['Overview',true],['Person Information',false],['Last Seen',false],['Case Timeline',false],['Search & Alerts',false],['Notes (2)',false],['Media (3)',false],['Documents (1)',false]] as [$lbl,$on])
    <button data-tab="{{ $lbl }}" class="si-tab {{ $on ? 'is-active' : '' }} whitespace-nowrap text-[12.5px]">{{ $lbl }}</button>
    @endforeach
</div>

<div data-panel="Overview" data-tabs-for="lpdetail" class="grid grid-cols-1 xl:grid-cols-[300px_1fr_1fr_400px] gap-5 items-start">
    {{-- person info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Person Information</p>
            <dl class="space-y-2 text-[12px]">
                @foreach([['Full Name','Amina B.'],['Gender','Female'],['Date of Birth','March 14, 2014'],['Age','12 years'],['Nationality','Cameroonian'],['Height','1.45 m'],['Weight','38 kg'],['Complexion','Brown'],['Hair','Black, Afro (two puffs)'],['Eyes','Brown'],['Language','French, English'],['Clothing (Last Seen)','White T-shirt, Blue jeans, White sneakers'],['Other Info','Carries a pink school backpack']] as [$k,$v])
                <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#131313] text-right">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <div class="mt-3.5 rounded-lg bg-[#EFF4FD] border border-[#CBDCF8] px-3.5 py-2.5">
                <p class="flex items-center gap-1.5 text-[11.5px] font-bold text-[#3565DE]"><i data-lucide="heart-pulse" class="w-3.5 h-3.5"></i>Medical Info</p>
                <p class="text-[11px] text-[#3B382F] mt-0.5">No known medical conditions.<br><b>Allergies:</b> None</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Recent Notes</p>
            <div class="space-y-3 text-[11.5px]">
                <p><b class="text-[#131313]">May 4, 2026 – 10:10 AM</b><br><span class="text-[#3B382F]">Search team deployed around Central Square and Avenue Kennedy.</span><br><span class="text-[#8A857A]">By Officer Daniel N.</span></p>
                <p><b class="text-[#131313]">May 4, 2026 – 09:30 AM</b><br><span class="text-[#3B382F]">Family interviewed. No suspect or clue at this time.</span><br><span class="text-[#8A857A]">By Officer Daniel N.</span></p>
            </div>
            <button data-toast="All notes — coming soon" class="mt-2 text-[11.5px] font-semibold text-[#3565DE]">View All Notes (2) »</button>
        </div>
    </div>

    {{-- last seen + physical --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Last Seen Information</p>
            <dl class="space-y-2.5 text-[12px]">
                @foreach([['map-pin','Date & Time','May 4, 2026 – 08:30 AM'],['map-pin','Location','Food Court, Central Square, Yaoundé'],['users-round','Circumstances','Walking towards the main exit after school break.'],['user-round','Accompanied By','No one'],['navigation','Direction','Towards Avenue Kennedy'],['footprints','Transport','On foot']] as [$ic,$k,$v])
                <div class="flex items-start gap-2.5"><i data-lucide="{{ $ic }}" class="w-4 h-4 text-[#8A857A] shrink-0 mt-0.5"></i><span><span class="block text-[11px] text-[#8A857A]">{{ $k }}</span><span class="block font-medium text-[#131313] leading-snug">{{ $v }}</span></span></div>
                @endforeach
            </dl>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Physical Description</p>
            <div class="grid grid-cols-2 gap-3 text-[12px]">
                @foreach([['ruler','Height','1.45 m'],['scale','Weight','38 kg'],['palette','Complexion','Brown'],['user-round','Hair','Black, Afro (two puffs)'],['eye','Eyes','Brown'],['sparkles','Distinctive Marks','Small scar on left knee']] as [$ic,$k,$v])
                <span class="flex items-start gap-2"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A] shrink-0 mt-0.5"></i><span><span class="block text-[10.5px] text-[#8A857A]">{{ $k }}</span><span class="block font-medium text-[#131313]">{{ $v }}</span></span></span>
                @endforeach
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="flex items-center gap-2 text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-1.5"><i data-lucide="megaphone" class="w-4 h-4 text-[#7C4FE0]"></i>Case Alerts</p>
            <p class="text-[11.5px] text-[#8A857A] mb-3">Alerts are active and visible to all security partners in your area.</p>
            <div class="grid grid-cols-4 gap-2 text-center">
                @foreach([['Alerts Sent','25'],['Partners Notified','18'],['People Reached','12,430'],['Tips Received','0']] as [$k,$v])
                <span><span class="block text-[10px] text-[#8A857A] leading-tight">{{ $k }}</span><span class="block text-[16px] font-bold text-[#131313] mt-0.5">{{ $v }}</span></span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- case progress --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Case Progress</p>
        <div class="space-y-4">
            @foreach([['#157A43','May 4, 2026 – 09:15 AM','Case reported','Reported by mother via web portal'],['#3565DE','May 4, 2026 – 09:25 AM','Case validated','Verified by Control Center'],['#7C4FE0','May 4, 2026 – 09:40 AM','Alert broadcasted','Distributed to all units and partners'],['#C97A16','May 4, 2026 – 10:10 AM','Search units deployed','Teams assigned to last known location']] as [$c,$dt,$t,$s])
            <div class="flex items-start gap-2.5 relative">
                @unless($loop->last)<span class="absolute left-[5px] top-5 bottom-[-16px] w-px bg-[#EFEDE6]"></span>@endunless
                <span class="w-[11px] h-[11px] rounded-full shrink-0 mt-1 relative z-10" style="background:{{ $c }}"></span>
                <span><span class="block text-[10.5px] text-[#8A857A]">{{ $dt }}</span><span class="block text-[12.5px] font-bold text-[#131313]">{{ $t }}</span><span class="block text-[11px] text-[#8A857A] leading-snug">{{ $s }}</span></span>
            </div>
            @endforeach
        </div>
        <button data-toast="Full timeline — coming soon" class="mt-4 text-[11.5px] font-semibold text-[#3565DE] inline-flex items-center gap-1">View Full Timeline <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></button>
    </div>

    {{-- map + quick info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Location Map (Last Seen)</p>
            <div class="relative rounded-xl overflow-hidden border border-[#EFEDE6]">
                <img src="{{ asset('images/siarc/secops-map-lp.png') }}" alt="" class="w-full h-auto block">
            </div>
        </div>
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mb-3">Quick Information</p>
            <dl class="space-y-2 text-[12px]">
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Case ID</dt><dd class="font-semibold text-[#131313]">LP-2026-0032</dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Status</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">Missing</span></dd></div>
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Priority</dt><dd><span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#FDE8E8] text-[#C0010C]">High</span></dd></div>
                @foreach([['Assigned To','Officer Daniel N.'],['Assigned Unit','Central District Unit'],['Reunification','Not Reunited'],['Days Missing','0 days'],['Case Created','May 4, 2026 – 09:15 AM']] as [$k,$v])
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#131313]">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>
</div>

@foreach(['Person Information','Last Seen','Case Timeline','Search & Alerts','Notes (2)','Media (3)','Documents (1)'] as $t)
<div data-panel="{{ $t }}" data-tabs-for="lpdetail" hidden>
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-8 text-center max-w-[520px] mx-auto">
        <span class="w-12 h-12 rounded-xl bg-[#E8EFFB] flex items-center justify-center mx-auto mb-3"><i data-lucide="folder-open" class="w-6 h-6 text-[#3565DE]"></i></span>
        <h3 class="text-[15px] font-bold text-[#131313]">{{ $t }}</h3>
        <p class="text-[12px] text-[#8A857A] mt-1.5">Full details are summarised on the Overview tab of this demo case.</p>
    </div>
</div>
@endforeach

<div class="mt-5 rounded-xl bg-[#FDF9EA] border border-[#F3E5B2] px-5 py-3.5 flex items-center justify-between gap-4">
    <p class="flex items-center gap-2.5 text-[12.5px] text-[#7A5E00]"><i data-lucide="alert-triangle" class="w-4 h-4 text-[#C97A16] shrink-0"></i>This case is still active. Please continue sharing information that may help locate Amina B.</p>
    <button data-toast="Information report — coming soon" class="shrink-0 siarc-btn text-[12.5px] font-bold text-white rounded-lg px-4 py-2.5" style="background:#C0010C">Report New Information</button>
</div>
