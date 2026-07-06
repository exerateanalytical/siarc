@php
    // ══ Visitor Check-In — security operations kiosk (verbatim from the design) ══
    $recent = [
        ['Sarah Lum','ABC Construction','10:20 AM',1],
        ['David Tan','Tech Solutions Ltd.','10:15 AM',2],
        ['Amina Yusuf','Global Services','10:10 AM',3],
        ['Robert Brown','Supply Chain Co.','10:05 AM',4],
        ['Linda Nkwain','Event Planner Ltd.','09:58 AM',5],
    ];
    $kente = 'repeating-linear-gradient(90deg,#157A43 0 10px,#E6B201 10px 20px,#C0010C 20px 30px,#131313 30px 34px)';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Check-In — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.siarc.partials.tokens')
    <style>body{font-family:'Poppins',system-ui,sans-serif}</style>
</head>
<body class="bg-[#0B0B0A] text-[#1D1B16] antialiased min-h-screen flex items-stretch">
<div class="flex-1 flex min-h-screen bg-[#FBF7EE] rounded-none lg:m-4 lg:rounded-[26px] overflow-hidden shadow-2xl">

    {{-- ══ SIDEBAR ══ --}}
    <aside class="hidden md:flex w-[250px] shrink-0 flex-col text-white" style="background:linear-gradient(180deg,#0A3A1E 0%,#062814 100%)">
        <div class="px-5 pt-6 pb-3">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => false])
            <p class="text-[10px] font-bold tracking-[0.14em] text-white/80 mt-1">SECURITY OPERATIONS</p>
        </div>
        <div class="h-[52px] mx-0 mb-3" style="background:linear-gradient(180deg,#C7A24A33,#0A3A1E00),url('{{ asset('images/siarc/secops-art.png') }}') center/cover"></div>
        <nav class="px-3 space-y-1.5">
            @foreach([['user-plus','VISITOR CHECK-IN','Check-in New Visitor',true,null],['search','QUICK LOOKUP','Find Visitor',false,null],['calendar-check','PRE-REGISTERED','Appointments',false,null],['history','CHECK-IN HISTORY','Recent Visitors',false,null],['watch','ISSUE WRISTBAND','Print Wristband',false,null],['printer','PRINT BADGE','Visitor Badge',false,null],['bell','ALERTS','Active Alerts',false,'2'],['settings','SETTINGS','System Settings',false,null]] as [$ic,$t,$s,$on,$badge])
            <button data-toast="{{ $t }} — coming soon" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-left {{ $on ? 'bg-[#157A43] text-white' : 'text-[#C9D6CD] hover:bg-white/5' }}">
                <i data-lucide="{{ $ic }}" class="w-[18px] h-[18px] shrink-0"></i>
                <span class="min-w-0"><b class="block text-[12px] tracking-wide">{{ $t }}</b><span class="block text-[10px] opacity-75">{{ $s }}</span></span>
                @if($badge)<span class="ml-auto w-5 h-5 rounded-full bg-[#C0010C] text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ $badge }}</span>@endif
            </button>
            @endforeach
        </nav>
        <div class="h-[8px] mt-4" style="background:{{ $kente }}"></div>
        <div class="mx-4 mt-auto mb-5 rounded-xl border border-white/15 bg-white/5 px-3.5 py-3 flex items-center gap-2.5">
            <i data-lucide="shield-check" class="w-5 h-5 text-[#35C46A]"></i>
            <span><b class="block text-[11.5px]">SYSTEM STATUS</b><span class="flex items-center gap-1.5 text-[10.5px] text-white/75">All systems operational<span class="w-2 h-2 rounded-full bg-[#35C46A]"></span></span></span>
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="bg-[#FDFBF4] border-b border-[#EDE7D8] px-5 py-3 flex items-center gap-4">
            <span class="hidden lg:block w-24 h-[14px] rounded-sm" style="background:{{ $kente }}"></span>
            <span class="mx-auto text-center">
                <b class="block text-[26px] text-[#123B22] leading-tight tracking-tight">VISITOR CHECK-IN</b>
                <span class="text-[11px] font-bold tracking-[0.1em]"><span class="text-[#123B22]">WELCOME</span> · <span class="text-[#C0010C]">VERIFY</span> · <span class="text-[#123B22]">ISSUE</span> · <span class="text-[#C0010C]">SECURE</span></span>
            </span>
            <span class="hidden lg:block w-24 h-[14px] rounded-sm" style="background:{{ $kente }}"></span>
            <span class="hidden sm:flex items-center gap-1.5 text-[11.5px] font-bold text-[#157A43]"><i data-lucide="wifi" class="w-4 h-4"></i>ONLINE</span>
            <span class="hidden sm:block text-right leading-tight"><b class="block text-[13px] text-[#131313]">10:24 AM</b><span class="text-[10px] text-[#8A857A]">Mon, May 5, 2026</span></span>
            <button data-toast="3 alerts — coming soon" class="relative w-9 h-9 rounded-full hover:bg-[#F1EDE0] flex items-center justify-center"><i data-lucide="bell" class="w-[18px] h-[18px] text-[#3B382F]"></i><span class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-[#C0010C] text-white text-[9px] font-bold flex items-center justify-center">3</span></button>
            <button data-toast="Logged out (demo)" class="siarc-btn text-[11.5px] font-bold text-[#3B382F] border border-[#E6E0D0] rounded-lg px-3 py-2 bg-white"><i data-lucide="log-out" class="w-3.5 h-3.5"></i>LOG OUT</button>
        </header>

        <main class="flex-1 p-5 grid grid-cols-1 xl:grid-cols-[1fr_330px] gap-5 items-start overflow-auto">
            <div class="space-y-5">
                {{-- quick lookup --}}
                <div class="bg-white rounded-2xl border border-[#EDE7D8] shadow-sm p-5">
                    <p class="text-[14px] font-bold text-[#131313]">QUICK LOOKUP</p>
                    <p class="text-[11.5px] text-[#8A857A] mb-3">Search visitor by name, ID, phone or company</p>
                    <div class="flex items-stretch gap-3">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" placeholder="Type name, ID, phone or company..." class="w-full h-[46px] text-[13px] rounded-xl border border-[#E6E0D0] pl-10 pr-3 focus:outline-none focus:border-[#157A43] bg-white text-[#3B382F]">
                            <button data-toast="Search (demo)" class="absolute right-1 top-1 bottom-1 w-[44px] rounded-lg bg-[#123B22] text-white flex items-center justify-center"><i data-lucide="search" class="w-4.5 h-4.5"></i></button>
                        </div>
                        <button data-toast="ID scan — coming soon" class="shrink-0 rounded-xl border border-[#E6E0D0] bg-[#FBF7EE] px-4 flex items-center gap-2.5 text-left">
                            <i data-lucide="scan-line" class="w-5 h-5 text-[#123B22]"></i>
                            <span><b class="block text-[11.5px] text-[#131313]">SCAN ID CARD</b><span class="block text-[10px] text-[#8A857A]">Scan visitor ID or QR code</span></span>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 mt-4">
                        @foreach([['user-plus','WALK-IN','New Visitor','#EAF3EA','#157A43'],['calendar-check','APPOINTMENT','Pre-Registered','#FBF3D9','#8A6A00'],['id-card','SCAN ID','Scan ID Card','#FBF0DC','#9A5F12'],['car','VEHICLE CHECK-IN','Register Vehicle','#FBE4E4','#C0010C']] as [$ic,$t,$s,$bg,$c])
                        <button data-toast="{{ $t }} — {{ $s }} (demo)" class="rounded-xl border border-[#EDE7D8] overflow-hidden text-center hover:opacity-95" style="background:{{ $bg }}">
                            <span class="block px-3 pt-4 pb-3">
                                <i data-lucide="{{ $ic }}" class="w-8 h-8 mx-auto mb-1.5" style="color:{{ $c }}"></i>
                                <b class="block text-[12.5px] text-[#131313] tracking-wide">{{ $t }}</b>
                                <span class="block text-[10.5px] text-[#6B6759]">{{ $s }}</span>
                            </span>
                            <span class="block h-[8px]" style="background:{{ $kente }}"></span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- visitor details + actions --}}
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-5 items-start">
                    <div class="bg-white rounded-2xl border border-[#EDE7D8] shadow-sm overflow-hidden">
                        <p class="px-5 py-3 text-[13px] font-bold text-white" style="background:#123B22">VISITOR DETAILS</p>
                        <div class="p-5 flex items-start gap-4">
                            <span class="text-center shrink-0">
                                <img src="{{ asset('images/siarc/accred-holder.png') }}" class="w-[110px] h-[110px] rounded-full object-cover border-4 border-[#EDE7D8]" alt="">
                                <span class="mt-2 inline-block text-[10px] font-bold px-2.5 py-1 rounded-md bg-[#EAF3EA] text-[#157A43]">NEW VISITOR</span>
                            </span>
                            <span class="min-w-0">
                                <b class="block text-[17px] text-[#131313]">John Michael Ndong</b>
                                <dl class="mt-2 space-y-1.5 text-[12px]">
                                    @foreach([['phone','Phone','+237 691 234 567'],['building-2','Company','ABC Logistics Ltd'],['briefcase','Purpose','Business Meeting'],['user-round','Visiting','Mr. Patrick Akwa (Logistics Dept.)']] as [$ic,$k,$v])
                                    <div class="flex items-center gap-2"><i data-lucide="{{ $ic }}" class="w-3.5 h-3.5 text-[#8A857A] shrink-0"></i><dt class="text-[#8A857A] w-[64px] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#131313]">{{ $v }}</dd></div>
                                    @endforeach
                                </dl>
                                <span class="flex items-center gap-2.5 mt-3.5">
                                    <button data-toast="Edit details — coming soon" class="siarc-btn text-[11.5px] font-bold text-[#3B382F] border border-[#E6E0D0] rounded-lg px-3.5 py-2 bg-white"><i data-lucide="pencil" class="w-3.5 h-3.5 text-[#8A857A]"></i>EDIT DETAILS</button>
                                    <button data-toast="Cleared (demo)" class="siarc-btn text-[11.5px] font-bold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3.5 py-2 bg-white"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i>CLEAR</button>
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="space-y-3.5">
                        @foreach([['printer','PRINT BADGE','Print Visitor Badge','#123B22'],['watch','ISSUE WRISTBAND','Print & Issue Wristband','#1D3E7E'],['check-circle-2','COMPLETE CHECK-IN','Finish Check-in Process','#4C2F8F']] as [$ic,$t,$s,$bg])
                        <button data-toast="{{ $t }} (demo)" class="w-full rounded-2xl overflow-hidden text-left text-white shadow-lg hover:opacity-95 flex items-center" style="background:{{ $bg }}">
                            <span class="flex items-center gap-3.5 px-4 py-4 flex-1">
                                <span class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center shrink-0"><i data-lucide="{{ $ic }}" class="w-5.5 h-5.5"></i></span>
                                <span><b class="block text-[14.5px] tracking-wide">{{ $t }}</b><span class="block text-[11px] opacity-85">{{ $s }}</span></span>
                            </span>
                            <span class="self-stretch w-[10px]" style="background:{{ $kente }}"></span>
                            <span class="px-3"><i data-lucide="arrow-right" class="w-5 h-5"></i></span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- recent check-ins --}}
            <div class="bg-white rounded-2xl border border-[#EDE7D8] shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[12.5px] font-bold tracking-[0.06em] text-[#131313] uppercase">Recent Check-Ins</p>
                    <button data-toast="Full history — coming soon" class="text-[11px] font-bold text-[#157A43]">VIEW ALL</button>
                </div>
                <div class="divide-y divide-[#F2EEE2]">
                    @foreach($recent as [$n,$co,$time,$f])
                    <button data-toast="{{ $n }} — check-in detail (demo)" class="w-full flex items-center gap-3 py-3 text-left hover:bg-[#FBF7EE] rounded-lg px-1">
                        <img src="{{ asset('images/siarc/accred-face-'.$f.'.png') }}" class="w-10 h-10 rounded-full object-cover" alt="">
                        <span class="min-w-0 flex-1">
                            <b class="block text-[13px] text-[#131313]">{{ $n }}</b>
                            <span class="block text-[11px] text-[#8A857A]">{{ $co }}</span>
                            <span class="mt-0.5 inline-block text-[9px] font-bold px-2 py-0.5 rounded-md bg-[#EAF3EA] text-[#157A43]">CHECKED-IN</span>
                        </span>
                        <span class="text-[10.5px] text-[#8A857A] shrink-0">{{ $time }}</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C5BA] shrink-0"></i>
                    </button>
                    @endforeach
                </div>
                <button data-toast="More history — coming soon" class="mt-2 w-full siarc-btn justify-center text-[11.5px] font-bold text-[#3B382F] border border-[#E6E0D0] rounded-xl px-3 py-2.5 bg-[#FBF7EE]">MORE HISTORY<i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></button>
            </div>
        </main>

        <footer class="bg-white border-t border-[#EDE7D8] px-5 py-3.5 grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach([['users-round','#157A43','128',"TODAY'S VISITORS",'↑ 12% from yesterday'],['check-circle-2','#2563EB','96','CHECKED-IN','Today'],['clock','#E6A800','20','PENDING','Awaiting completion'],['user-minus','#C0010C','12','CHECKED-OUT','Today'],['calendar-check','#3565DE','8','APPOINTMENTS','Today']] as [$ic,$c,$v,$k,$s])
            <span class="flex items-center gap-2.5">
                <i data-lucide="{{ $ic }}" class="w-6 h-6" style="color:{{ $c }}"></i>
                <span><b class="block text-[19px] text-[#131313] leading-tight">{{ $v }}</b><span class="block text-[9.5px] font-bold tracking-wide text-[#3B382F]">{{ $k }}</span><span class="block text-[9px] text-[#8A857A]">{{ $s }}</span></span>
            </span>
            @endforeach
        </footer>
        <div class="h-[10px]" style="background:{{ $kente }}"></div>
    </div>
</div>
<script>lucide.createIcons();</script>
<script src="{{ asset('vendor/siarc-ui.js') }}"></script>
</body>
</html>
