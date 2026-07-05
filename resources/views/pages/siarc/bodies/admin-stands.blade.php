@php
    // Tone → [tile bg, ink] — mirrors _blocks.blade.php so chips/badges match the system.
    $toneMap = [
        'green'  => ['#E2F3E8', '#157A43'], 'red'    => ['#FDE8E8', '#DC2626'],
        'blue'   => ['#E8EFFB', '#3565DE'], 'gold'   => ['#FDF3E0', '#C97A16'],
        'grey'   => ['#F1F1EF', '#6F6B60'], 'purple' => ['#F0EAFB', '#7C4FE0'],
    ];

    // Legend as shown in the design: Alloué / Disponible / Réservé.
    $legend = [
        ['green', $isFr ? 'Alloué' : 'Allocated'],
        ['grey',  $isFr ? 'Disponible' : 'Available'],
        ['blue',  $isFr ? 'Réservé' : 'Reserved'],
    ];

    // Unpack the single stands table.
    $tbl  = $sTables[0] ?? ['title' => 'Stands', 'cols' => [], 'rows' => []];
    $rows = $tbl['rows'] ?? [];
@endphp

{{-- ══ TITLE ══ --}}
<div class="mb-6 siarc-in">
    <p class="siarc-kicker text-siarc-ochre mb-2">{{ $isFr ? 'Pavillons & Stands' : 'Pavilions & Stands' }}</p>
    <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $sTitle ?? ($isFr ? 'Allocation des Stands' : 'Stand Allocation') }}</h1>
    <p class="text-[13px] text-[#8A857A] mt-1">{{ $sIntro ?? ($isFr ? 'Attribuez les stands disponibles aux exposants confirmés.' : 'Assign available stands to confirmed exhibitors.') }}</p>
</div>

{{-- ══ KPI CARDS ══ --}}
@if(!empty($sStats))
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    @foreach($sStats as [$stIcon, $stColor, $stTile, $stValue, $stLabel, $stSub])
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $stTile }}"><i data-lucide="{{ $stIcon }}" class="w-[22px] h-[22px]" style="color:{{ $stColor }}"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">{{ $stLabel }}</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">{{ $stValue }}</p>
            </div>
        </div>
        @if($stSub)<p class="text-[11px] text-[#B0AB9F] mt-2">{{ $stSub }}</p>@endif
    </div>
    @endforeach
</div>
@endif

{{-- ══ FILTER / ACTION BAR + LEGEND ══ --}}
<div class="siarc-card siarc-shadow p-4 mb-6">
    <div class="flex flex-wrap items-center gap-3">
        {{-- Status legend --}}
        <div class="flex flex-wrap items-center gap-4">
            @foreach($legend as [$lt, $label])
            @php $lc = $toneMap[$lt]; @endphp
            <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#3B382F]">
                <span class="w-3 h-3 rounded-[4px]" style="background:{{ $lc[0] }};border:1.5px solid {{ $lc[1] }}"></span>{{ $label }}
            </span>
            @endforeach
        </div>
        <div class="hidden md:block h-6 w-px bg-[#ECEAE3] mx-1"></div>
        {{-- Search (visual) --}}
        <div class="relative flex-1 min-w-[180px]">
            <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" placeholder="{{ $isFr ? 'Rechercher un stand ou exposant…' : 'Search a stand or exhibitor…' }}" class="w-full pl-9 pr-3 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] placeholder:text-[#B0AB9F] focus:outline-none focus:border-[#D7E4DB] bg-[#FBFAF6]">
        </div>
        {{-- Links (Plan interactif) --}}
        @if(!empty($sLinks))
        @foreach($sLinks as $lnk)
        <a href="{{ $lnk['href'] }}" class="siarc-btn siarc-btn-green px-4 py-2 text-[12.5px]">
            <i data-lucide="{{ $lnk['icon'] ?? 'map' }}" class="w-4 h-4"></i>{{ $lnk['label'] }}
        </a>
        @endforeach
        @endif
    </div>
</div>

{{-- ══ STAND CHIP GRID ══ --}}
<div class="siarc-card siarc-shadow p-5 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Plan des stands' : 'Stand map' }}</h3>
        <span class="text-[11.5px] text-[#8A857A]">{{ count($rows) }} {{ $isFr ? 'stands' : 'stands' }}</span>
    </div>
    @if(count($rows))
    <div class="flex flex-wrap gap-2">
        @foreach($rows as $row)
            @php
                $code    = $row['cells'][0] ?? '—';
                $statusC = $row['cells'][2] ?? [];
                $t       = is_array($statusC) ? ($statusC['tone'] ?? 'grey') : 'grey';
                $c       = $toneMap[$t] ?? $toneMap['grey'];
            @endphp
            <a href="{{ $row['href'] ?? '#' }}" title="{{ is_array($statusC) ? ($statusC['badge'] ?? '') : '' }}"
               class="siarc-lift w-[68px] h-[46px] rounded-lg flex items-center justify-center text-[12px] font-bold border"
               style="background:{{ $c[0] }};color:{{ $c[1] }};border-color:{{ $c[1] }}33">{{ $code }}</a>
        @endforeach
    </div>
    @else
    <div class="py-10 text-center">
        <span class="w-12 h-12 mx-auto rounded-2xl flex items-center justify-center mb-3 siarc-adire"><i data-lucide="grid-3x3" class="w-6 h-6 text-siarc-gold"></i></span>
        <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucun stand' : 'No stands' }}</p>
        <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'À venir' : 'Coming soon' }}</p>
    </div>
    @endif
</div>

{{-- ══ ALLOCATION TABLE ══ --}}
<div class="siarc-card siarc-shadow overflow-hidden">
    <div class="px-5 py-4 border-b border-[#ECEAE3] flex items-center justify-between">
        <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $tbl['title'] ?? ($isFr ? 'Stands' : 'Stands') }}</h3>
        <span class="text-[11.5px] text-[#8A857A]">{{ count($rows) }} {{ $isFr ? 'lignes' : 'rows' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="text-[11px] font-semibold uppercase tracking-wide text-[#8A857A] bg-[#FBFAF6]">
                    @foreach(($tbl['cols'] ?? []) as $col)
                    <th class="px-5 py-3 whitespace-nowrap">{{ $col }}</th>
                    @endforeach
                    <th class="px-5 py-3 text-right whitespace-nowrap">{{ $isFr ? 'Action' : 'Action' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#F1EFEA]">
                @forelse($rows as $row)
                    @php
                        $statusC = $row['cells'][2] ?? [];
                        $t       = is_array($statusC) ? ($statusC['tone'] ?? 'grey') : 'grey';
                        $badge   = is_array($statusC) ? ($statusC['badge'] ?? '') : (string) $statusC;
                        $bc      = $toneMap[$t] ?? $toneMap['grey'];
                        $isAvail = $t === 'grey';
                    @endphp
                    <tr class="hover:bg-[#FBFAF6] transition-colors">
                        {{-- Stand code --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <a href="{{ $row['href'] ?? '#' }}" class="font-bold text-[13px] text-[#161513] hover:text-siarc-green">{{ $row['cells'][0] ?? '—' }}</a>
                        </td>
                        {{-- Pavillon --}}
                        <td class="px-5 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $row['cells'][1] ?? '—' }}</td>
                        {{-- Statut badge --}}
                        <td class="px-5 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full" style="background:{{ $bc[0] }};color:{{ $bc[1] }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $bc[1] }}"></span>{{ $badge }}
                            </span>
                        </td>
                        {{-- Exposant --}}
                        <td class="px-5 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $row['cells'][3] ?? '—' }}</td>
                        {{-- Action --}}
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            @if($isAvail)
                            <a href="{{ $row['href'] ?? '#' }}" class="siarc-btn siarc-btn-primary px-3 py-1.5 text-[11.5px]">
                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Attribuer' : 'Assign' }}
                            </a>
                            @else
                            <a href="{{ $row['href'] ?? '#' }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-siarc-green hover:gap-1.5 transition-all">
                                {{ $isFr ? 'Détails' : 'Details' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucune donnée' : 'No data' }}</p>
                            <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'À venir' : 'Coming soon' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
