@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';

    // ── Type → badge tone (Conférence=purple, Atelier=gold, Panel=red, Innovation=blue, Culture=green, other=grey)
    $typeTone = function ($t) {
        $k = mb_strtolower(trim((string) $t));
        if (str_contains($k, 'conf'))                                  return ['#F0EAFB', '#7C4FE0', '#7C4FE0']; // purple
        if (str_contains($k, 'atelier') || str_contains($k, 'format')) return ['#FDF3E0', '#C97A16', '#E6B201']; // gold
        if (str_contains($k, 'panel'))                                 return ['#FDE8E8', '#C0010C', '#C0010C']; // red
        if (str_contains($k, 'innov') || str_contains($k, 'tech'))     return ['#E6F0FB', '#1E6FD0', '#1E6FD0']; // blue
        if (str_contains($k, 'cult') || str_contains($k, 'patrim'))    return ['#E7F3EC', '#157A43', '#157A43']; // green
        return ['#F0EFEA', '#6B675E', '#A8A498']; // grey / autre
    };

    $legend = [
        ['Conférence', '#7C4FE0'],
        ['Atelier', '#E6B201'],
        ['Panel', '#C0010C'],
        ['Innovation', '#1E6FD0'],
        ['Culture', '#157A43'],
        ['Autre', '#A8A498'],
    ];

    $tables = collect($sTables ?? []);
@endphp

{{-- ══════════════════ HEADER BAND ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-12 lg:py-14 siarc-in">
        @if(!empty($sCrumb))
            <nav class="text-[12px] text-[#8A857A] mb-4">{!! $sCrumb !!}</nav>
        @endif
        <div class="flex items-center gap-1.5 mb-5">
            @for($i=0;$i<4;$i++)<span class="w-3 h-3 rotate-45" style="background:{{ ['#157A43','#C0010C','#E6B201','#0F4824'][$i] }}"></span>@endfor
        </div>
        <span class="siarc-kicker text-siarc-ochre mb-4">{{ $isFr ? 'PROGRAMME & ACTIVITÉS' : 'PROGRAMME & ACTIVITIES' }}</span>
        <h1 class="font-display font-extrabold leading-[1.05] text-[38px] sm:text-[50px] tracking-tight text-[#1A1712] mt-3">
            {{ $sTitle ?? ($isFr ? 'Programme du SIARC 2026' : 'SIARC 2026 Programme') }}
        </h1>
        @if(!empty($sIntro))
            <p class="mt-5 text-[15px] text-[#55524A] leading-relaxed max-w-[640px]">{{ $sIntro }}</p>
        @endif
        <div class="flex flex-wrap items-center gap-x-8 gap-y-3 mt-7">
            <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="calendar-days" class="w-5 h-5 text-siarc-ochre"></i>27 JUILLET – 05 AOÛT 2026</span>
            <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="map-pin" class="w-5 h-5 text-siarc-ochre"></i>MUSÉE NATIONAL DE YAOUNDÉ</span>
        </div>
    </div>
</section>

@if($tables->isEmpty())
{{-- ══════════════════ EMPTY STATE ══════════════════ --}}
<section class="bg-white">
    <div class="max-w-[1240px] mx-auto px-6 py-24 text-center">
        <span class="w-16 h-16 mx-auto rounded-2xl bg-[#F3F0E7] flex items-center justify-center mb-5"><i data-lucide="calendar-clock" class="w-8 h-8 text-siarc-green"></i></span>
        <h2 class="font-display text-[24px] font-bold text-[#1A1712] mb-2">{{ $isFr ? 'Programme à venir' : 'Programme coming soon' }}</h2>
        <p class="text-[14px] text-[#8A857A]">{{ $isFr ? "Le programme détaillé sera bientôt disponible." : 'The detailed programme will be available soon.' }}</p>
    </div>
</section>
@else

{{-- ══════════════════ DAY TABS ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5] sticky top-0 z-20">
    <div class="max-w-[1240px] mx-auto px-6">
        <div class="flex items-center gap-2 overflow-x-auto py-4 siarc-scroll">
            <span class="text-[11px] font-bold tracking-wide text-[#A8A498] uppercase shrink-0 mr-2">{{ $isFr ? 'Jours' : 'Days' }} :</span>
            @foreach($tables as $di => $day)
                <a href="#jour-{{ $di }}"
                   class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[12.5px] font-semibold text-[#0F4824] border-[1.5px] border-[#DDE6DF] hover:bg-[#0F4824] hover:text-white hover:border-[#0F4824] transition-colors">
                    <i data-lucide="calendar" class="w-4 h-4"></i>{{ $day['title'] ?? ($isFr ? 'Jour' : 'Day').' '.($di+1) }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ AGENDA (one section per day) ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 py-14 space-y-14">
        @foreach($tables as $di => $day)
        @php $rows = collect($day['rows'] ?? []); @endphp
        <div id="jour-{{ $di }}" class="scroll-mt-24 siarc-in">

            {{-- Day header --}}
            <div class="flex items-center gap-4 mb-6">
                <span class="w-11 h-11 rounded-2xl bg-siarc-green text-white flex items-center justify-center font-display text-[18px] font-extrabold shrink-0">{{ $di+1 }}</span>
                <div>
                    <h2 class="font-display text-[24px] sm:text-[28px] font-extrabold text-siarc-dark leading-none capitalize">{{ $day['title'] ?? ($isFr ? 'Jour' : 'Day').' '.($di+1) }}</h2>
                    <p class="text-[12px] text-[#8A857A] mt-1.5">{{ $rows->count() }} {{ $isFr ? ($rows->count() > 1 ? 'activités' : 'activité') : ($rows->count() > 1 ? 'activities' : 'activity') }}</p>
                </div>
                <span class="hidden sm:block flex-1 h-px bg-[#E4DFD3] ml-2"></span>
            </div>

            {{-- Agenda list --}}
            @if($rows->isEmpty())
                <div class="siarc-card siarc-shadow p-8 text-center text-[13px] text-[#8A857A]">{{ $isFr ? 'Aucune activité programmée ce jour.' : 'No activity scheduled that day.' }}</div>
            @else
            <div class="siarc-card siarc-shadow overflow-hidden">
                @foreach($rows as $ri => $row)
                @php
                    $cells = $row['cells'] ?? [];
                    $rTitle = $cells[0] ?? '';
                    $rTime  = $cells[1] ?? '';
                    $rType  = $cells[2] ?? '';
                    $rRoom  = $cells[3] ?? '';
                    $tone   = $typeTone($rType);
                    $href   = $row['href'] ?? null;
                @endphp
                <div class="flex gap-4 sm:gap-6 px-5 sm:px-7 py-5 {{ !$loop->last ? 'border-b border-[#F0EEE7]' : '' }} hover:bg-[#FBFAF6] transition-colors">

                    {{-- Time (left) --}}
                    <div class="w-[64px] sm:w-[76px] shrink-0 text-right pt-0.5">
                        <p class="text-[14px] font-bold text-[#1A1712] leading-tight inline-flex items-center gap-1 justify-end">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-siarc-ochre"></i>{{ $rTime ?: '—' }}
                        </p>
                    </div>

                    {{-- Vertical accent --}}
                    <div class="w-1 rounded-full shrink-0 self-stretch" style="background:{{ $tone[2] }}"></div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        @if($rType)
                            <span class="inline-block text-[10px] font-bold tracking-wide uppercase px-2.5 py-0.5 rounded-full mb-1.5" style="background:{{ $tone[0] }};color:{{ $tone[1] }}">{{ $rType }}</span>
                        @endif
                        <p class="text-[15px] font-semibold text-[#1A1712] leading-snug">
                            @if($href)
                                <a href="{{ $href }}" class="hover:text-siarc-green transition-colors inline-flex items-start gap-1.5 group">
                                    <span>{{ $rTitle }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 mt-0.5 text-siarc-green opacity-0 group-hover:opacity-100 transition-opacity shrink-0"></i>
                                </a>
                            @else
                                {{ $rTitle }}
                            @endif
                        </p>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 mt-2 text-[12px] text-[#8A857A]">
                            @if($rRoom)
                                <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-siarc-ochre"></i>{{ $rRoom }}</span>
                            @endif
                            @if($href)
                                <a href="{{ $href }}" class="inline-flex items-center gap-1 font-semibold text-siarc-green hover:gap-2 transition-all">{{ $isFr ? 'S\'inscrire' : 'Register' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach

        {{-- Legend --}}
        <div class="siarc-card siarc-shadow px-6 py-5 flex flex-wrap items-center gap-x-7 gap-y-3">
            <span class="text-[12px] font-bold tracking-wide text-[#1A1712] uppercase">{{ $isFr ? 'Types de session' : 'Session types' }} :</span>
            @foreach($legend as [$lname, $lcolor])
                <span class="inline-flex items-center gap-2 text-[12.5px] text-[#55524A]"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $lcolor }}"></span>{{ $lname }}</span>
            @endforeach
        </div>
    </div>
</section>
@endif
