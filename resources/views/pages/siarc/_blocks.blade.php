{{-- Shared SIARC block renderer — wired to real salon data, styled to the SIARC
     design system. Consumes: $lang, $sIntro, $sStats, $sTables, $sCards, $sLinks. --}}
@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $tone = [
        'green' => ['#E2F3E8', '#157A43'], 'red' => ['#FDE8E8', '#DC2626'],
        'blue' => ['#E8EFFB', '#3565DE'], 'gold' => ['#FDF3E0', '#C97A16'],
        'grey' => ['#F1F1EF', '#6F6B60'], 'purple' => ['#F0EAFB', '#7C4FE0'],
    ];
@endphp

@if(!empty($sIntro))
<p class="text-[13.5px] text-[#55524A] leading-relaxed max-w-[840px] mb-6">{{ $sIntro }}</p>
@endif

@if(session('siarc_checkin_ok'))
<div class="mb-5 flex items-center gap-2.5 rounded-xl border border-[#CFE8D8] bg-[#EAF6EE] px-4 py-3">
    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#157A43] shrink-0"></i>
    <p class="text-[13px] font-semibold text-[#155B33]">{{ $isFr ? 'Check-in enregistré :' : 'Check-in recorded:' }} {{ session('siarc_checkin_ok') }}</p>
</div>
@elseif(session('siarc_checkin_ko'))
<div class="mb-5 flex items-center gap-2.5 rounded-xl border border-[#F5CFCF] bg-[#FDF0F0] px-4 py-3">
    <i data-lucide="x-circle" class="w-4 h-4 text-[#C0010C] shrink-0"></i>
    <p class="text-[13px] font-semibold text-[#8A1015]">{{ $isFr ? 'Badge refusé ou inconnu :' : 'Badge refused or unknown:' }} {{ session('siarc_checkin_ko') }}</p>
</div>
@endif

@if(!empty($sForm))
<form method="POST" action="{{ $sForm['action'] }}" class="mb-6 siarc-card siarc-shadow p-4 flex flex-wrap items-end gap-3 max-w-[640px]">
    @csrf
    <div class="flex-1 min-w-[220px]">
        <label class="block text-[11px] text-[#8A857A] mb-1">{{ $sForm['label'] }}</label>
        <input name="{{ $sForm['name'] ?? 'code' }}" required maxlength="120" placeholder="{{ $sForm['placeholder'] ?? '' }}"
               class="w-full text-[13px] rounded-lg border border-[#EFEDE6] px-3.5 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
    </div>
    <button type="submit" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="scan-line" class="w-4 h-4"></i>{{ $sForm['button'] }}</button>
</form>
@endif

@if(!empty($sStats))
<section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-{{ min(5, max(1, count($sStats))) }} gap-4 mb-6">
    @foreach($sStats as [$stIcon, $stColor, $stTile, $stValue, $stLabel, $stSub])
    <div class="siarc-card siarc-shadow px-5 py-5">
        <span class="w-[42px] h-[42px] rounded-xl flex items-center justify-center" style="background-color: {{ $stTile }}"><i data-lucide="{{ $stIcon }}" class="w-[20px] h-[20px]" style="color: {{ $stColor }};stroke-width:1.9"></i></span>
        <p class="mt-3.5 text-[24px] font-extrabold text-[#161513] leading-none tracking-tight">{{ $stValue }}</p>
        <p class="mt-1.5 text-[12px] font-semibold text-[#3B382F]">{{ $stLabel }}</p>
        @if($stSub)<p class="mt-0.5 text-[11px] text-[#8A857A]">{{ $stSub }}</p>@endif
    </div>
    @endforeach
</section>
@endif

@if(!empty($sCards))
<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    @foreach($sCards as $c)
    @php $cTone = $tone[$c['tone'] ?? 'green'] ?? $tone['green']; @endphp
    <a href="{{ $c['href'] ?? '#' }}" class="siarc-card siarc-shadow siarc-lift block p-5">
        <div class="flex items-center gap-3.5">
            <span class="w-[46px] h-[46px] rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $cTone[0] }}"><i data-lucide="{{ $c['icon'] ?? 'box' }}" class="w-[22px] h-[22px]" style="color: {{ $cTone[1] }}"></i></span>
            <div class="min-w-0">
                <p class="text-[14px] font-bold text-[#161513] truncate">{{ $c['title'] }}</p>
                @if(!empty($c['sub']))<p class="text-[12px] text-[#8A857A] truncate">{{ $c['sub'] }}</p>@endif
            </div>
            @if(!empty($c['badge']))<span class="ml-auto shrink-0 text-[10.5px] font-semibold px-2.5 py-1 rounded-full" style="background-color: {{ $cTone[0] }};color: {{ $cTone[1] }}">{{ $c['badge'] }}</span>@else<i data-lucide="arrow-right" class="ml-auto w-4 h-4 text-[#C7C3B8] shrink-0"></i>@endif
        </div>
        @if(!empty($c['meta']))<p class="mt-3 text-[12px] text-[#6F6B60]">{{ $c['meta'] }}</p>@endif
    </a>
    @endforeach
</section>
@endif

@if(!empty($sTables))
@foreach($sTables as $tbl)
<div class="siarc-card siarc-shadow overflow-hidden mb-6">
    @if(!empty($tbl['title']))
    <div class="px-6 py-4 border-b border-[#F1F1EF] flex items-center justify-between gap-3">
        <h2 class="text-[14px] font-bold text-[#161513]">{{ $tbl['title'] }}</h2>
        @if(!empty($tbl['action']))<a href="{{ $tbl['action'][1] }}" class="text-[12.5px] font-semibold text-siarc-green hover:underline">{{ $tbl['action'][0] }}</a>@endif
    </div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead><tr class="text-[10.5px] font-bold text-[#8A857A] bg-[#FAFAF7]">
                @foreach($tbl['cols'] as $col)<th class="px-6 py-3 whitespace-nowrap tracking-wide">{{ strtoupper($col) }}</th>@endforeach
            </tr></thead>
            <tbody>
                @forelse($tbl['rows'] as $row)
                <tr class="border-t border-[#F1F1EF] hover:bg-[#FBFAF6] transition-colors">
                    @foreach($row['cells'] as $ci => $cell)
                    <td class="px-6 py-3.5 text-[12.5px] text-[#3B382F]">
                        @if(is_array($cell))
                            @php $ct = $tone[$cell['tone'] ?? 'grey'] ?? $tone['grey']; @endphp
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full" style="background-color: {{ $ct[0] }};color: {{ $ct[1] }}"><span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $ct[1] }}"></span>{{ $cell['badge'] }}</span>
                        @elseif($ci === 0 && !empty($row['href']))
                            <a href="{{ $row['href'] }}" class="font-semibold text-[#161513] hover:text-siarc-green">{{ $cell }}</a>
                        @else
                            {{ $cell }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($tbl['cols']) }}" class="text-center py-12 text-[13px] text-[#8A857A]">{{ $tbl['empty'] ?? ($isFr ? 'Aucune donnée.' : 'No data.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endif

@if(!empty($sLinks))
<div class="siarc-card siarc-shadow p-6 mb-2">
    <h3 class="text-[12px] font-bold text-[#8A857A] tracking-[0.1em] mb-4">{{ $isFr ? 'PAGES LIÉES' : 'RELATED PAGES' }}</h3>
    <div class="flex flex-wrap gap-2.5">
        @foreach($sLinks as $lnk)
        <a href="{{ $lnk['href'] }}" class="inline-flex items-center gap-2 bg-[#F6F7F6] hover:bg-[#E2F3E8] text-[12.5px] font-medium text-[#3B382F] px-4 py-2.5 rounded-xl transition-colors">
            <i data-lucide="{{ $lnk['icon'] ?? 'arrow-right' }}" class="w-4 h-4 text-siarc-green"></i>{{ $lnk['label'] }}
        </a>
        @endforeach
    </div>
</div>
@endif
