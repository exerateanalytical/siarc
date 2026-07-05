{{-- Reusable SIARC lockup: SVG emblem (a stylised artisan figure in flag colours)
     + wordmark. Params: $onDark (bool) light text for dark chrome; $tag (bool) show tagline. --}}
@php $onDark = $onDark ?? false; $tag = $tag ?? true; @endphp
<a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="inline-flex items-center gap-2.5 group shrink-0">
    <svg width="40" height="44" viewBox="0 0 40 44" fill="none" class="shrink-0 transition-transform group-hover:scale-105">
        {{-- head --}}
        <circle cx="20" cy="7.5" r="5" fill="#E6B201"/>
        {{-- raised arms (red / green) --}}
        <path d="M20 14 L6 6" stroke="#C0010C" stroke-width="4.4" stroke-linecap="round"/>
        <path d="M20 14 L34 9" stroke="#157A43" stroke-width="4.4" stroke-linecap="round"/>
        {{-- torso --}}
        <path d="M20 13 C25 20 25 28 20 30 C15 28 15 20 20 13 Z" fill="#0F4824"/>
        {{-- legs (gold / green) --}}
        <path d="M20 29 L11 41" stroke="#C97A16" stroke-width="4.4" stroke-linecap="round"/>
        <path d="M20 29 L29 41" stroke="#14652F" stroke-width="4.4" stroke-linecap="round"/>
        {{-- flame accent --}}
        <path d="M20 3 c2 2 2 4 0 5 c-2-1-2-3 0-5Z" fill="#C0010C"/>
    </svg>
    <span class="leading-none">
        <span class="block font-display font-extrabold tracking-tight text-[23px] {{ $onDark ? 'text-white' : 'text-[#0F4824]' }}">SIARC</span>
        @if($tag)
        <span class="block text-[8px] font-semibold tracking-[0.14em] mt-0.5 {{ $onDark ? 'text-white/70' : 'text-[#8A857A]' }}">SALON INTERNATIONAL<br>DE L'ARTISANAT DU CAMEROUN</span>
        @endif
    </span>
</a>
