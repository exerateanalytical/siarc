{{-- SIARC 2026 badge card (recto), rebuilt in HTML so it prints crisp.
     Params: $bVariant green|red|gold|blue (default green), $bName, $bRole, $bRoleColor,
     $bCountry (default CAMEROUN), $bId (code under QR), $bVip (bool: black VIP tag),
     $bPhoto, $bQr (asset paths), $bOrg (line under name, replaces role band when set),
     $bScale (css width, default 232px) --}}
@php
    $bVariant = $bVariant ?? 'green';
    $head = ['green' => 'linear-gradient(160deg,#0B3A1E 0%,#07351C 60%,#052A15 100%)',
             'red'   => 'linear-gradient(160deg,#7E0E14 0%,#8F1218 55%,#6C0A10 100%)',
             'gold'  => 'linear-gradient(160deg,#8A6A00 0%,#B08900 55%,#7A5E00 100%)',
             'blue'  => 'linear-gradient(160deg,#0E2F63 0%,#123A7A 55%,#0B2650 100%)'][$bVariant];
    $bName = $bName ?? 'JEAN PAUL ESSOMBA';
    $bRole = $bRole ?? 'VISITEUR';
    $bRoleColor = $bRoleColor ?? '#E6B201';
    $bCountry = $bCountry ?? 'CAMEROUN';
    $bId = $bId ?? 'VIS-000356';
    $bVip = $bVip ?? false;
    $bPhoto = $bPhoto ?? asset('images/siarc/accred-portrait.png');
    $bQr = $bQr ?? asset('images/siarc/accred-qr.png');
    $bScale = $bScale ?? '232px';
@endphp
<div class="rounded-[14px] overflow-hidden bg-white shadow-[0_10px_28px_-10px_rgba(6,43,21,.45)] border border-black/5 shrink-0" style="width:{{ $bScale }}">
    <div class="relative pt-4 pb-9 text-center" style="background:{{ $head }}">
        <div class="flex items-center justify-center gap-1.5">
            <svg width="22" height="24" viewBox="0 0 40 44" fill="none">
                <circle cx="20" cy="7.5" r="5" fill="#E6B201"/>
                <path d="M20 14 L6 6" stroke="#C0010C" stroke-width="4.4" stroke-linecap="round"/>
                <path d="M20 14 L34 9" stroke="#157A43" stroke-width="4.4" stroke-linecap="round"/>
                <path d="M20 13 C25 20 25 28 20 30 C15 28 15 20 20 13 Z" fill="#fff"/>
                <path d="M20 29 L11 41" stroke="#C97A16" stroke-width="4.4" stroke-linecap="round"/>
                <path d="M20 29 L29 41" stroke="#14652F" stroke-width="4.4" stroke-linecap="round"/>
            </svg>
            <span class="text-left leading-none">
                <span class="block text-white font-extrabold text-[17px] tracking-tight">SIARC</span>
                <span class="block font-bold text-[11px] tracking-wide" style="color:#E6B201">2026</span>
            </span>
        </div>
        <p class="mt-0.5 text-[5.5px] font-semibold tracking-[0.14em] text-white/75">SALON INTERNATIONAL DE L'ARTISANAT DU CAMEROUN</p>
        {{-- curved bottom of the header --}}
        <svg class="absolute -bottom-px left-0 w-full" viewBox="0 0 232 26" preserveAspectRatio="none" style="height:26px"><path d="M0 26 L0 12 Q116 -14 232 12 L232 26 Z" fill="#fff"/></svg>
    </div>
    <div class="relative -mt-11 flex justify-center">
        <img src="{{ $bPhoto }}" alt="" class="w-[78px] h-[78px] rounded-full object-cover ring-4 ring-white relative z-10">
        @if($bVip)
        <span class="absolute z-20 top-4 right-6 bg-[#111] text-white text-[11px] font-bold px-4 py-1 rounded-md tracking-wide">VIP</span>
        @endif
    </div>
    <div class="px-4 pt-2 pb-3 text-center">
        <p class="text-[14.5px] font-extrabold text-[#131313] tracking-tight leading-tight">{{ $bName }}</p>
        @if(!empty($bOrg))
            <p class="text-[7.5px] font-bold tracking-wide text-[#4A473F] mt-0.5">{{ $bOrg }}</p>
        @else
            <p class="text-[11px] font-bold tracking-[0.08em] mt-0.5" style="color:{{ $bRoleColor }}">{{ $bRole }}</p>
        @endif
        <div class="mt-1.5 pt-1.5 border-t border-[#EDEBE4] flex items-center justify-center gap-1.5">
            <span class="inline-block w-[14px] h-[9px] rounded-[1px]" style="background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)"></span>
            <span class="text-[9px] font-bold tracking-[0.06em] text-[#3B382F]">{{ $bCountry }}</span>
        </div>
        <div class="mt-2 flex flex-col items-center">
            <img src="{{ $bQr }}" alt="QR" class="w-[62px] h-[62px] object-contain">
            <p class="text-[8.5px] font-semibold text-[#3B382F] mt-1 tracking-wide">{{ $bId }}</p>
        </div>
    </div>
    <div class="text-center py-2 px-2" style="background:{{ $head }}">
        <p class="text-[8.5px] font-bold text-white tracking-wide">27 JUILLET - 05 AOÛT 2026</p>
        <p class="text-[7.5px] font-semibold text-white/80 tracking-wide">MUSÉE NATIONAL DE YAOUNDÉ</p>
    </div>
</div>
