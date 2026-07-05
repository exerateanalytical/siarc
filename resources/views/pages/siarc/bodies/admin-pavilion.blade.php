@php
    // ── Real data passed by the route ──────────────────────────────────────────
    $sTitle  = $sTitle  ?? 'Pavillon';
    $sStats  = $sStats  ?? [];
    $sTables = $sTables  ?? [];
    $sLinks  = $sLinks  ?? [];

    // Primary table = "Stands du pavillon"
    $standsTable = $sTables[0] ?? ['cols'=>['Stand','Statut','Exposant'], 'rows'=>[]];
    $standRows   = $standsTable['rows'] ?? [];

    // Derive pavilion name for the header (strip "Pavillon · " prefix if present)
    $pavName = trim(preg_replace('/^Pavillon\s*[·:]\s*/u', '', $sTitle));
    if ($pavName === '') $pavName = 'Pavillon';

    // Status tone → chip colours for the visual stand grid + legend
    $chipTones = [
        'green'  => ['bg'=>'#E2F3E8','bd'=>'#B9E2C7','fg'=>'#0F4824'],  // alloué / occupé
        'grey'   => ['bg'=>'#F1F1EF','bd'=>'#E1E0DA','fg'=>'#6B6459'],  // disponible
        'blue'   => ['bg'=>'#E8EFFB','bd'=>'#C6D8F5','fg'=>'#254C9C'],  // réservé
        'gold'   => ['bg'=>'#FDF3E0','bd'=>'#F3DFB0','fg'=>'#8A5E12'],  // en attente
        'red'    => ['bg'=>'#FDE8E8','bd'=>'#F3C4C4','fg'=>'#9A1B1B'],  // maintenance / annulé
        'purple' => ['bg'=>'#F0EAFB','bd'=>'#D9CBF3','fg'=>'#5A3AAE'],
    ];
    $toneOf = function($tone) use ($chipTones){
        return $chipTones[$tone] ?? $chipTones['grey'];
    };

    // Badge label → tailwind tone classes (for the detailed table)
    $badgeTone = [
        'green'  => 'bg-[#E2F3E8] text-[#0F4824]',
        'grey'   => 'bg-[#F1F1EF] text-[#6B6459]',
        'blue'   => 'bg-[#E8EFFB] text-[#254C9C]',
        'gold'   => 'bg-[#FDF3E0] text-[#8A5E12]',
        'red'    => 'bg-[#FDE8E8] text-[#9A1B1B]',
        'purple' => 'bg-[#F0EAFB] text-[#5A3AAE]',
    ];

    // Live counts for the stat tiles derived from the real rows
    $totalStands = count($standRows);
    $allocCount  = 0;
    foreach ($standRows as $r) {
        $t = $r['cells'][1]['tone'] ?? '';
        if ($t === 'green') $allocCount++;
    }
    $occPct = $totalStands > 0 ? round($allocCount / $totalStands * 100) : 0;

    // Stat tiles: prefer the route-provided $sStats, else fall back to derived
    $statTiles = [];
    if (!empty($sStats)) {
        $tileMeta = [
            ['grid-3x3','#157A43','#E2F3E8'],
            ['layout-grid','#7C4FE0','#F0EAFB'],
        ];
        foreach (array_values($sStats) as $i => $st) {
            $label = is_array($st) ? ($st[4] ?? '') : ($i === 0 ? 'Stands' : 'Alloués');
            $value = is_array($st) ? ($st[3] ?? '') : $st;
            $m = $tileMeta[$i] ?? $tileMeta[0];
            $statTiles[] = [$m[0], $m[1], $m[2], $label, $value];
        }
    }
    if (empty($statTiles)) {
        $statTiles = [
            ['grid-3x3','#157A43','#E2F3E8','Stands',   (string) $totalStands],
            ['layout-grid','#7C4FE0','#F0EAFB','Alloués', (string) $allocCount],
        ];
    }

    // Back link
    $backHref = $sLinks[0]['href'] ?? ($sLinks['back']['href'] ?? '#');
    if (is_array($sLinks) && isset($sLinks[0]) && is_string($sLinks[0])) $backHref = $sLinks[0];
@endphp

<div class="siarc-in">

    {{-- ══ BREADCRUMB + BACK ══ --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-2 text-[13px]">
            <a href="{{ $backHref }}" class="inline-flex items-center gap-1.5 font-semibold text-siarc-green hover:gap-2 transition-all">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Pavillons' : 'Pavilions' }}
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4 text-[#B0AB9F]"></i>
            <span class="text-[#55524A]">{{ $isFr ? 'Détails du Pavillon' : 'Pavilion Details' }}</span>
        </div>
    </div>

    {{-- ══ HEADER CARD ══ --}}
    <div class="siarc-card siarc-shadow overflow-hidden mb-5">
        <div class="siarc-kente siarc-kente-thin"></div>
        <div class="p-6 grid lg:grid-cols-[1fr_1.15fr] gap-6 items-start">
            {{-- left: identity --}}
            <div class="flex gap-5">
                <div class="w-36 h-36 rounded-2xl siarc-adire shrink-0 flex items-center justify-center siarc-shadow">
                    <span class="font-display text-[34px] font-extrabold text-siarc-gold">
                        {{ mb_strtoupper(mb_substr($pavName,0,2)) }}
                    </span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $pavName }}</h1>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#E2F3E8] text-[#0F4824]">
                            <i data-lucide="circle-dot" class="w-3 h-3"></i>{{ $isFr ? 'Actif' : 'Active' }}
                        </span>
                    </div>
                    <p class="mt-2 text-[13px] text-[#55524A] flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-siarc-ochre"></i>
                        {{ $isFr ? 'Musée National de Yaoundé' : 'National Museum of Yaoundé' }}
                    </p>
                    <p class="mt-3 text-[13px] leading-relaxed text-[#55524A]">
                        {{ $isFr
                            ? 'Espace d\'exposition dédié aux artisans et exposants du salon. Retrouvez ci-dessous la répartition des stands du pavillon.'
                            : 'Exhibition space dedicated to the fair\'s artisans and exhibitors. Find the pavilion\'s stand allocation below.' }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-x-8 gap-y-3">
                        <div>
                            <p class="siarc-kicker !text-[10px] text-[#8A857A] mb-0.5">{{ $isFr ? 'Stands' : 'Stands' }}</p>
                            <p class="font-display text-[18px] font-extrabold text-[#161513]">{{ $totalStands }}</p>
                        </div>
                        <div>
                            <p class="siarc-kicker !text-[10px] text-[#8A857A] mb-0.5">{{ $isFr ? 'Alloués' : 'Allocated' }}</p>
                            <p class="font-display text-[18px] font-extrabold text-siarc-green">{{ $allocCount }}</p>
                        </div>
                        <div>
                            <p class="siarc-kicker !text-[10px] text-[#8A857A] mb-0.5">{{ $isFr ? 'Occupation' : 'Occupancy' }}</p>
                            <p class="font-display text-[18px] font-extrabold text-siarc-ochre">{{ $occPct }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- right: kente / heritage banner --}}
            <div class="rounded-2xl overflow-hidden siarc-adire relative min-h-[200px] flex items-center justify-center siarc-shadow">
                <div class="absolute inset-0 opacity-90"></div>
                <div class="relative text-center px-6 py-8">
                    <span class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-white/10 flex items-center justify-center">
                        <i data-lucide="building-2" class="w-7 h-7 text-siarc-gold"></i>
                    </span>
                    <p class="font-display text-[22px] font-extrabold text-white tracking-wide uppercase leading-tight">{{ $pavName }}</p>
                    <p class="mt-1 text-[12px] font-semibold text-white/70 tracking-[0.2em] uppercase">SIARC 2026</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ STAT TILES ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        @foreach($statTiles as [$icon,$color,$tile,$label,$value])
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $value }}</p>
        </div>
        @endforeach
        {{-- occupancy tile --}}
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FDF3E0">
                <i data-lucide="activity" class="w-5 h-5" style="color:#C97A16"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $isFr ? 'Taux d\'occupation' : 'Occupancy rate' }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $occPct }}%</p>
        </div>
    </div>

    {{-- ══ VISUAL STAND GRID + LEGEND ══ --}}
    <div class="siarc-card siarc-shadow p-5 mb-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <h3 class="text-[14px] font-bold text-[#1A1712] flex items-center gap-2">
                <i data-lucide="grid-3x3" class="w-4 h-4 text-siarc-green"></i>
                {{ $isFr ? 'Plan des stands' : 'Stand map' }}
            </h3>
            {{-- Legend --}}
            <div class="flex items-center flex-wrap gap-x-5 gap-y-2 text-[11.5px]">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-[5px]" style="background:#E2F3E8;border:1px solid #B9E2C7"></span>
                    <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Alloué' : 'Allocated' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-[5px]" style="background:#F1F1EF;border:1px solid #E1E0DA"></span>
                    <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Disponible' : 'Available' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-[5px]" style="background:#E8EFFB;border:1px solid #C6D8F5"></span>
                    <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Réservé' : 'Reserved' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded-[5px]" style="background:#FDE8E8;border:1px solid #F3C4C4"></span>
                    <span class="text-[#3B382F] font-medium">{{ $isFr ? 'Maintenance' : 'Maintenance' }}</span>
                </span>
            </div>
        </div>

        @if(count($standRows))
        <div class="grid grid-cols-[repeat(auto-fill,minmax(84px,1fr))] gap-2.5">
            @foreach($standRows as $row)
                @php
                    $cells   = $row['cells'] ?? [];
                    $code    = $cells[0] ?? '—';
                    $badge   = $cells[1] ?? [];
                    $tone    = is_array($badge) ? ($badge['tone'] ?? 'grey') : 'grey';
                    $bLabel  = is_array($badge) ? ($badge['badge'] ?? '') : (string) $badge;
                    $exh     = $cells[2] ?? '—';
                    $c       = $toneOf($tone);
                    $href    = $row['href'] ?? null;
                    $tag     = $href ? 'a' : 'div';
                @endphp
                <{{ $tag }} @if($href) href="{{ $href }}" @endif
                    class="rounded-xl px-2 py-2.5 text-center siarc-lift block group"
                    style="background:{{ $c['bg'] }};border:1px solid {{ $c['bd'] }}"
                    @if(is_string($exh) && $exh !== '—') title="{{ $exh }}" @endif>
                    <p class="font-display text-[15px] font-extrabold leading-none" style="color:{{ $c['fg'] }}">{{ $code }}</p>
                    <p class="mt-1.5 text-[9.5px] font-semibold uppercase tracking-wide truncate" style="color:{{ $c['fg'] }};opacity:.75">{{ $bLabel }}</p>
                </{{ $tag }}>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <span class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-[#F1F1EF] flex items-center justify-center">
                <i data-lucide="grid-3x3" class="w-6 h-6 text-[#B0AB9F]"></i>
            </span>
            <p class="text-[13px] font-semibold text-[#55524A]">{{ $isFr ? 'Aucun stand' : 'No stands' }}</p>
            <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'Aucun stand n\'est encore rattaché à ce pavillon.' : 'No stand is attached to this pavilion yet.' }}</p>
        </div>
        @endif
    </div>

    {{-- ══ DETAILED TABLE ══ --}}
    <div class="siarc-card siarc-shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-[#ECEAE3] flex items-center justify-between">
            <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $standsTable['title'] ?? ($isFr ? 'Stands du pavillon' : 'Pavilion stands') }}</h3>
            <span class="text-[11.5px] font-medium text-[#8A857A]">{{ $totalStands }} {{ $isFr ? 'stands' : 'stands' }}</span>
        </div>
        @if(count($standRows))
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[11px] font-semibold uppercase tracking-wide text-[#8A857A] bg-[#FBFAF6]">
                        @foreach(($standsTable['cols'] ?? ['Stand','Statut','Exposant']) as $col)
                        <th class="px-5 py-3">{{ $col }}</th>
                        @endforeach
                        <th class="px-5 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F0EB]">
                    @foreach($standRows as $row)
                        @php
                            $cells  = $row['cells'] ?? [];
                            $code   = $cells[0] ?? '—';
                            $badge  = $cells[1] ?? [];
                            $tone   = is_array($badge) ? ($badge['tone'] ?? 'grey') : 'grey';
                            $bLabel = is_array($badge) ? ($badge['badge'] ?? '') : (string) $badge;
                            $exh    = $cells[2] ?? '—';
                            $href   = $row['href'] ?? null;
                            $cls    = $badgeTone[$tone] ?? $badgeTone['grey'];
                        @endphp
                        <tr class="hover:bg-[#FBFAF6] transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="font-display text-[14px] font-extrabold text-[#161513]">{{ $code }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $cls }}">
                                    <i data-lucide="circle-dot" class="w-3 h-3"></i>{{ $bLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if(is_string($exh) && $exh !== '—' && $exh !== '')
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-7 h-7 rounded-full bg-siarc-green text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                            {{ mb_strtoupper(mb_substr($exh,0,2)) }}
                                        </span>
                                        <span class="text-[13px] font-medium text-[#3B382F]">{{ $exh }}</span>
                                    </div>
                                @else
                                    <span class="text-[13px] text-[#B0AB9F]">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($href)
                                <a href="{{ $href }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-siarc-green hover:gap-1.5 transition-all">
                                    {{ $isFr ? 'Voir' : 'View' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <p class="text-[13px] font-semibold text-[#55524A]">{{ $isFr ? 'Aucune donnée' : 'No data' }}</p>
        </div>
        @endif
    </div>

</div>
