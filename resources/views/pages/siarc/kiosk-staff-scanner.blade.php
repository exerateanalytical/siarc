@php
    // ══ Mobile Staff Scanner — security operations kiosk (verbatim from the design) ══
    $scans = [
        ['John M. Ndong','STF-2026-4587','Pavilion A - Logistics','GRANTED','#157A43','10:24 AM',1],
        ['Marie Tchoua','STF-2026-3321','Pavilion B - Technical','DENIED','#C0010C','10:18 AM',2],
        ['Patrick Akoa','STF-2026-1054','Pavilion C - Security','PENDING','#C97A16','10:12 AM',3],
        ['Emile Biloa','STF-2026-7789','Pavilion A - Logistics','GRANTED','#157A43','10:05 AM',4],
        ['Grace Fouda','STF-2026-5922','Pavilion D - Hospitality','GRANTED','#157A43','09:58 AM',5],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Staff Scanner — SIARC 2026</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    @include('pages.siarc.partials.tokens')
    <style>body{font-family:'Poppins',system-ui,sans-serif}</style>
</head>
<body class="bg-[#0B0B0A] text-[#1D1B16] antialiased min-h-screen flex items-stretch">
<div class="flex-1 flex min-h-screen bg-[#F4F4F2] rounded-none lg:m-4 lg:rounded-[26px] overflow-hidden shadow-2xl">

    {{-- ══ SIDEBAR ══ --}}
    <aside class="hidden md:flex w-[230px] shrink-0 flex-col text-white" style="background:linear-gradient(180deg,#06331B 0%,#04250F 100%)">
        <div class="px-5 pt-6 pb-4">
            @include('pages.siarc.partials.logo', ['onDark' => true, 'tag' => false])
            <p class="text-[10px] font-bold tracking-[0.14em] text-white/80 mt-1">SECURITY OPERATIONS</p>
        </div>
        <div class="mx-4 mb-4 rounded-xl border border-white/15 bg-white/5 px-3.5 py-3 flex items-center gap-2.5">
            <span class="w-9 h-9 rounded-full bg-[#123E22] flex items-center justify-center"><i data-lucide="shield-check" class="w-4.5 h-4.5 text-[#35C46A]"></i></span>
            <span><b class="block text-[13px]">Gate 1</b><span class="block text-[10.5px] text-white/70">Main Entrance</span><span class="flex items-center gap-1 text-[10px] text-[#35C46A]"><span class="w-1.5 h-1.5 rounded-full bg-[#35C46A]"></span>Active</span></span>
        </div>
        <nav class="px-3 space-y-1">
            @foreach([['qr-code','Scanner',true,null],['clock','History',false,null],['users','Staff Directory',false,null],['bell','Alerts',false,'2'],['settings','Settings',false,null],['cloud','Sync Status',false,null]] as [$ic,$lbl,$on,$badge])
            <button data-toast="{{ $lbl }} — coming soon" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-medium {{ $on ? 'bg-[#157A43] text-white' : 'text-[#C9D6CD] hover:bg-white/5' }}">
                <i data-lucide="{{ $ic }}" class="w-[17px] h-[17px]"></i>{{ $lbl }}
                @if($badge)<span class="ml-auto w-5 h-5 rounded-full bg-[#C0010C] text-white text-[10px] font-bold flex items-center justify-center">{{ $badge }}</span>@endif
            </button>
            @endforeach
        </nav>
        <div class="mx-4 mt-5 rounded-xl border border-white/15 bg-white/5 px-3.5 py-3">
            <p class="text-[11px] font-bold text-white/85 mb-1.5">Sync Status</p>
            <p class="flex items-center gap-2 text-[11px] text-[#35C46A]"><i data-lucide="cloud-check" class="w-4 h-4"></i>All systems operational</p>
            <p class="text-[10px] text-white/60 mt-0.5">Last sync: 10:24 AM</p>
        </div>
        <a href="{{ route('siarc.admin.secops.overview', ['lang' => $lang ?? 'fr']) }}" class="mx-4 mt-auto mb-5 flex items-center justify-center gap-2 rounded-xl border border-white/20 py-3 text-[12.5px] font-bold hover:bg-white/5"><i data-lucide="log-out" class="w-4 h-4"></i>LOG OUT</a>
    </aside>

    {{-- ══ MAIN ══ --}}
    <div class="flex-1 min-w-0 flex flex-col">
        <header class="bg-white border-b border-[#ECEAE3] px-5 py-3 flex items-center gap-4">
            <button data-toast="Menu" class="w-9 h-9 rounded-lg hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="menu" class="w-5 h-5 text-[#3B382F]"></i></button>
            <span class="mx-auto text-center">
                <b class="block text-[22px] text-[#131313] leading-tight">Mobile Staff Scanner</b>
                <span class="text-[12px] font-semibold text-[#157A43]">Scan · Verify · Authorize</span>
            </span>
            <span class="hidden sm:flex items-center gap-2 text-[12px] font-semibold text-[#157A43]"><i data-lucide="wifi" class="w-4 h-4"></i>Online</span>
            <span class="hidden sm:block text-right leading-tight"><b class="block text-[13px] text-[#131313]">10:24 AM</b><span class="text-[10.5px] text-[#8A857A]">Mon, May 5, 2026</span></span>
            <button data-toast="3 alerts — coming soon" class="relative w-9 h-9 rounded-full hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="bell" class="w-[18px] h-[18px] text-[#3B382F]"></i><span class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-[#C0010C] text-white text-[9px] font-bold flex items-center justify-center">3</span></button>
            <span class="w-9 h-9 rounded-full bg-[#157A43] flex items-center justify-center"><i data-lucide="user-round" class="w-4.5 h-4.5 text-white"></i></span>
        </header>

        <main class="flex-1 p-5 grid grid-cols-1 xl:grid-cols-[1fr_360px] gap-5 items-start overflow-auto">
            <div>
                {{-- scan area --}}
                <div class="bg-white rounded-2xl border border-[#ECEAE3] shadow-sm p-6 text-center">
                    <h2 class="text-[19px] font-bold text-[#131313] tracking-wide">SCAN STAFF CREDENTIAL</h2>
                    <p class="text-[12.5px] text-[#8A857A] mb-4">Align QR code or ID card in the frame</p>
                    <div class="relative mx-auto max-w-[540px] aspect-[8/5] rounded-2xl overflow-hidden" style="background:radial-gradient(circle at 50% 45%, #0E2B18 0%, #071A0E 70%)">
                        @foreach([['top-4 left-4','border-t-4 border-l-4 rounded-tl-lg'],['top-4 right-4','border-t-4 border-r-4 rounded-tr-lg'],['bottom-4 left-4','border-b-4 border-l-4 rounded-bl-lg'],['bottom-4 right-4','border-b-4 border-r-4 rounded-br-lg']] as [$pos,$bd])
                        <span class="absolute {{ $pos }} w-10 h-10 {{ $bd }} border-[#35C46A]"></span>
                        @endforeach
                        <i data-lucide="user-round" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 text-white/15"></i>
                        <span class="absolute left-[10%] right-[10%] top-1/2 h-[3px] bg-[#35C46A] shadow-[0_0_16px_#35C46A]"></span>
                    </div>
                    <button data-toast="Light on (demo)" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-[#ECEAE3] bg-[#FBFAF7] px-4 py-2 text-[12.5px] font-medium text-[#3B382F]"><i data-lucide="flashlight" class="w-4 h-4"></i>Tap to turn on light</button>
                </div>

                {{-- quick actions --}}
                <p class="text-[12px] font-bold tracking-[0.08em] text-[#3B382F] uppercase mt-5 mb-3">Quick Actions</p>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach([['check-circle-2','VALID ENTRY','Authorize Access','#157A43'],['x-circle','INVALID ENTRY','Deny Access','#C0010C'],['history','PENDING REVIEW','Manual Verification','#E6A800'],['users','STAFF DIRECTORY','Search Staff','#2563EB']] as [$ic,$t,$s,$bg])
                    <button data-toast="{{ $t }} — {{ $s }} (demo)" class="rounded-2xl px-4 py-6 text-center text-white shadow-lg hover:opacity-95" style="background:{{ $bg }}">
                        <span class="mx-auto w-12 h-12 rounded-full border-[3px] border-white/90 flex items-center justify-center mb-3"><i data-lucide="{{ $ic }}" class="w-6 h-6"></i></span>
                        <b class="block text-[15px] tracking-wide">{{ $t }}</b>
                        <span class="block text-[11.5px] opacity-90">{{ $s }}</span>
                    </button>
                    @endforeach
                </div>
                <p class="mt-5 flex items-center justify-center gap-2 text-[12px] text-[#8A857A]"><i data-lucide="lock" class="w-3.5 h-3.5"></i>Protected by SIARC Security Protocol</p>
            </div>

            {{-- recent scans --}}
            <div class="bg-white rounded-2xl border border-[#ECEAE3] shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-[12.5px] font-bold tracking-[0.06em] text-[#131313] uppercase">Recent Scans</p>
                    <button data-toast="Full history — coming soon" class="text-[11.5px] font-semibold text-[#2563EB]">View All</button>
                </div>
                <div class="divide-y divide-[#F2F1EC]">
                    @foreach($scans as [$n,$id,$loc,$st,$c,$time,$f])
                    <button data-toast="{{ $n }} — scan detail (demo)" class="w-full flex items-center gap-3 py-3 text-left hover:bg-[#FBFAF6] rounded-lg px-1">
                        <img src="{{ asset('images/siarc/accred-face-'.$f.'.png') }}" class="w-11 h-11 rounded-full object-cover" alt="">
                        <span class="min-w-0 flex-1">
                            <b class="block text-[13.5px] text-[#131313]">{{ $n }}</b>
                            <span class="block text-[11px] text-[#8A857A]">{{ $id }}</span>
                            <span class="block text-[11px] text-[#8A857A]">{{ $loc }}</span>
                            <span class="mt-1 inline-block text-[9.5px] font-bold px-2 py-0.5 rounded-md" style="color:{{ $c }};background:{{ $c }}18">{{ $st }}</span>
                        </span>
                        <span class="text-[11px] text-[#8A857A] shrink-0">{{ $time }}</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-[#C9C5BA] shrink-0"></i>
                    </button>
                    @endforeach
                </div>
            </div>
        </main>

        <footer class="bg-white border-t border-[#ECEAE3] px-5 py-3 grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach([['users-round','#157A43',"TODAY'S SCANS",'128'],['shield-check','#2563EB','ACCESS GRANTED','96'],['x-circle','#C0010C','ACCESS DENIED','12'],['clock','#E6A800','PENDING REVIEW','20'],['users','#7C4FE0','STAFF ON SITE','356']] as [$ic,$c,$k,$v])
            <span class="flex items-center gap-2.5">
                <i data-lucide="{{ $ic }}" class="w-6 h-6" style="color:{{ $c }}"></i>
                <span><span class="block text-[9.5px] font-bold tracking-wide text-[#8A857A]">{{ $k }}</span><b class="block text-[19px] text-[#131313] leading-tight">{{ $v }}</b></span>
            </span>
            @endforeach
        </footer>
    </div>
</div>
<script>lucide.createIcons();</script>
<script src="{{ asset('vendor/siarc-ui.js') }}"></script>
</body>
</html>
