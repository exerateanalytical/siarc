{{-- Shared SIARC block renderer (design-pending but fully wired to real data).
     Consumes: $lang, $sIntro, $sStats, $sTables, $sCards, $sLinks, $sPending. --}}
@php
    $isFr = ($lang ?? 'fr') === 'fr';
    $tone = [
        'green' => ['#E2F3E8', '#157A43'], 'red' => ['#FDE8E8', '#DC2626'],
        'blue' => ['#E8EFFB', '#3565DE'], 'gold' => ['#FDF3E0', '#C97A16'],
        'grey' => ['#F1F1EF', '#6F6B60'], 'purple' => ['#F0EAFB', '#7C4FE0'],
    ];
@endphp

@if(!empty($sIntro))
<p class="text-[13px] text-[#55524A] leading-relaxed max-w-[820px] mb-5">{{ $sIntro }}</p>
@endif

@if(!empty($sStats))
<section class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-{{ min(5, max(1, count($sStats))) }} gap-4 mb-5">
    @foreach($sStats as [$stIcon, $stColor, $stTile, $stValue, $stLabel, $stSub])
    <div class="bg-white border border-[#EFF0EF] rounded-2xl px-4 py-4">
        <span class="w-[40px] h-[40px] rounded-xl flex items-center justify-center" style="background-color: {{ $stTile }}"><i data-lucide="{{ $stIcon }}" class="w-[19px] h-[19px]" style="color: {{ $stColor }};stroke-width:1.8"></i></span>
        <p class="mt-3 text-[22px] font-extrabold text-[#1B1B18] leading-none">{{ $stValue }}</p>
        <p class="mt-1 text-[11.5px] font-semibold text-[#3B382F]">{{ $stLabel }}</p>
        @if($stSub)<p class="mt-0.5 text-[10.5px] text-[#8A857A]">{{ $stSub }}</p>@endif
    </div>
    @endforeach
</section>
@endif

@if(!empty($sCards))
<section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
    @foreach($sCards as $c)
    @php $cTone = $tone[$c['tone'] ?? 'green'] ?? $tone['green']; @endphp
    <a href="{{ $c['href'] ?? '#' }}" class="block bg-white border border-[#EFF0EF] rounded-2xl p-4 hover:shadow-md hover:border-[#D8E5DC] transition-all">
        <div class="flex items-center gap-3">
            <span class="w-[42px] h-[42px] rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $cTone[0] }}"><i data-lucide="{{ $c['icon'] ?? 'box' }}" class="w-5 h-5" style="color: {{ $cTone[1] }}"></i></span>
            <div class="min-w-0">
                <p class="text-[13.5px] font-bold text-[#1B1B18] truncate">{{ $c['title'] }}</p>
                @if(!empty($c['sub']))<p class="text-[11.5px] text-[#8A857A] truncate">{{ $c['sub'] }}</p>@endif
            </div>
            @if(!empty($c['badge']))<span class="ml-auto shrink-0 text-[10.5px] font-semibold px-2 py-0.5 rounded-full" style="background-color: {{ $cTone[0] }};color: {{ $cTone[1] }}">{{ $c['badge'] }}</span>@endif
        </div>
        @if(!empty($c['meta']))<p class="mt-3 text-[12px] text-[#6F6B60]">{{ $c['meta'] }}</p>@endif
    </a>
    @endforeach
</section>
@endif

@if(!empty($sTables))
@foreach($sTables as $tbl)
<div class="bg-white border border-[#EFF0EF] rounded-2xl overflow-hidden mb-5">
    @if(!empty($tbl['title']))
    <div class="px-5 py-3.5 border-b border-[#F1F1EF] flex items-center justify-between gap-3">
        <h2 class="text-[13px] font-bold text-[#1B1B18] tracking-wide">{{ $tbl['title'] }}</h2>
        @if(!empty($tbl['action']))<a href="{{ $tbl['action'][1] }}" class="text-[12px] font-semibold text-[#157A43] hover:underline">{{ $tbl['action'][0] }}</a>@endif
    </div>
    @endif
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead><tr class="text-[10.5px] font-bold text-[#8A857A]">
                @foreach($tbl['cols'] as $col)<th class="px-5 py-2.5 whitespace-nowrap">{{ strtoupper($col) }}</th>@endforeach
            </tr></thead>
            <tbody>
                @forelse($tbl['rows'] as $row)
                <tr class="border-t border-[#F1F1EF] hover:bg-[#FAFAF8]">
                    @foreach($row['cells'] as $ci => $cell)
                    <td class="px-5 py-3 text-[12.5px] text-[#3B382F]">
                        @if(is_array($cell))
                            @php $ct = $tone[$cell['tone'] ?? 'grey'] ?? $tone['grey']; @endphp
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" style="background-color: {{ $ct[0] }};color: {{ $ct[1] }}">{{ $cell['badge'] }}</span>
                        @elseif($ci === 0 && !empty($row['href']))
                            <a href="{{ $row['href'] }}" class="font-semibold text-[#1B1B18] hover:text-[#157A43]">{{ $cell }}</a>
                        @else
                            {{ $cell }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="{{ count($tbl['cols']) }}" class="text-center py-10 text-[13px] text-[#8A857A]">{{ $tbl['empty'] ?? ($isFr ? 'Aucune donnée.' : 'No data.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endif

@if(!empty($sLinks))
<div class="bg-white border border-[#EFF0EF] rounded-2xl p-5 mb-5">
    <h3 class="text-[13px] font-bold text-[#1B1B18] tracking-wide mb-3">{{ $isFr ? 'PAGES LIÉES' : 'RELATED PAGES' }}</h3>
    <div class="flex flex-wrap gap-2">
        @foreach($sLinks as $lnk)
        <a href="{{ $lnk['href'] }}" class="inline-flex items-center gap-2 bg-[#F6F7F6] hover:bg-[#E2F3E8] text-[12.5px] font-medium text-[#3B382F] px-3.5 py-2 rounded-lg transition-colors">
            <i data-lucide="{{ $lnk['icon'] ?? 'arrow-right' }}" class="w-4 h-4 text-[#157A43]"></i>{{ $lnk['label'] }}
        </a>
        @endforeach
    </div>
</div>
@endif

@if($sPending ?? true)
<div class="flex items-start gap-2.5 bg-[#FBF7EC] border border-[#EFE4C8] rounded-xl px-4 py-3 text-[12px] text-[#7A5A12]">
    <i data-lucide="palette" class="w-4 h-4 mt-0.5 shrink-0"></i>
    <span>{{ $isFr ? 'Page fonctionnelle et connectée aux données réelles — le design SIARC définitif sera appliqué dès réception de la maquette.' : 'Page is functional and wired to real data — the final SIARC design will be applied once the mockup is provided.' }}</span>
</div>
@endif
