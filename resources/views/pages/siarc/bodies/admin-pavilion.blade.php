@php
    use Illuminate\Support\Facades\Route as R;

    // ── Real data passed by the route ──────────────────────────────────────────
    $lang    = $lang    ?? 'fr';
    $isFr    = $isFr    ?? ($lang === 'fr');
    $sTitle  = $sTitle  ?? 'Pavillon';
    $sStats  = $sStats  ?? [];
    $sTables = $sTables  ?? [];
    $sLinks  = $sLinks  ?? [];

    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;

    // Primary table = "Stands du pavillon" → cols [Stand, Statut, Exposant]
    $standsTable = $sTables[0] ?? ['cols'=>['Stand','Statut','Exposant'], 'rows'=>[]];
    $standCols   = $standsTable['cols'] ?? ['Stand','Statut','Exposant'];
    $standRows   = $standsTable['rows'] ?? [];

    // Pavilion name (strip "Pavillon · " prefix if present)
    $pavName = trim(preg_replace('/^Pavillon\s*[·:]\s*/u', '', $sTitle));
    if ($pavName === '') $pavName = $isFr ? 'Pavillon' : 'Pavilion';

    // ── Status tone → chip colours (grid + legend + table badges) ──────────────
    $chipTones = [
        'green'  => ['bg'=>'#E2F3E8','bd'=>'#B9E2C7','fg'=>'#0F4824'],  // alloué / occupé
        'grey'   => ['bg'=>'#F4F4F1','bd'=>'#E4E3DD','fg'=>'#6B6459'],  // disponible
        'blue'   => ['bg'=>'#E8EFFB','bd'=>'#C6D8F5','fg'=>'#254C9C'],  // réservé
        'gold'   => ['bg'=>'#FDF3E0','bd'=>'#F3DFB0','fg'=>'#8A5E12'],  // en attente
        'red'    => ['bg'=>'#FDE8E8','bd'=>'#F3C4C4','fg'=>'#9A1B1B'],  // maintenance
        'purple' => ['bg'=>'#F0EAFB','bd'=>'#D9CBF3','fg'=>'#5A3AAE'],
    ];
    $toneOf = fn($tone) => $chipTones[$tone] ?? $chipTones['grey'];

    // ── Live counts derived from the REAL rows ─────────────────────────────────
    $totalStands = count($standRows);
    $allocCount  = 0; $availCount = 0; $reservedCount = 0; $maintCount = 0;
    foreach ($standRows as $r) {
        $t = is_array($r['cells'][1] ?? null) ? ($r['cells'][1]['tone'] ?? '') : '';
        if ($t === 'green') $allocCount++;
        elseif ($t === 'blue') $reservedCount++;
        elseif ($t === 'red')  $maintCount++;
        else $availCount++;
    }

    // Prefer route-provided $sStats (POSITIONAL: [3]=value, [4]=label) for the two
    // headline figures, else fall back to derived counts.
    $statVal = function($i, $fallback) use ($sStats) {
        $st = array_values($sStats)[$i] ?? null;
        if (is_array($st) && isset($st[3])) return $st[3];
        if (is_string($st) || is_numeric($st)) return $st;
        return $fallback;
    };
    $standsTotal = $statVal(0, $totalStands);
    $standsAlloc = $statVal(1, $allocCount);

    // Numeric extraction for the occupancy %
    $numTotal = (int) preg_replace('/[^0-9]/', '', (string) $standsTotal) ?: $totalStands;
    $numAlloc = (int) preg_replace('/[^0-9]/', '', (string) $standsAlloc) ?: $allocCount;
    $numAvail = max(0, $numTotal - $numAlloc);
    $occPct   = $numTotal > 0 ? round($numAlloc / $numTotal * 100) : 0;
    $availPct = $numTotal > 0 ? round($numAvail / $numTotal * 100) : 0;

    // ── KPI tile row (design layout; figures computed from real counts) ────────
    $kpis = [
        ['grid-3x3',    '#157A43','#E2F3E8', $isFr ? 'Stands totaux'    : 'Total stands',    $standsTotal, '100%'],
        ['layout-grid', '#7C4FE0','#F0EAFB', $isFr ? 'Stands occupés'   : 'Occupied stands', $standsAlloc, $occPct.'%'],
        ['grid-3x3',    '#157A43','#E2F3E8', $isFr ? 'Stands disponibles':'Available stands', (string)$numAvail, $availPct.'%'],
        ['activity',    '#C97A16','#FDF3E0', $isFr ? 'Taux d\'occupation':'Occupancy rate',  $occPct.'%', $isFr ? 'du pavillon' : 'of pavilion'],
    ];

    // ── Metadata row — honest placeholders where unknown ───────────────────────
    $dash = '—';
    $metaRow = [
        ['ruler',      $isFr ? 'Étage'        : 'Floor',        $dash],
        ['maximize',   $isFr ? 'Superficie'   : 'Area',         $isFr ? 'Non renseignée' : 'Not set'],
        ['arrow-up',   $isFr ? 'Hauteur'      : 'Height',       $dash],
        ['users',      $isFr ? 'Capacité'     : 'Capacity',     $numTotal.' '.($isFr ? 'stands' : 'stands')],
    ];

    // ── Tab strip (Aperçu active; rest are non-navigational buttons) ───────────
    $tabs = [
        [$isFr ? 'Aperçu' : 'Overview', true],
        [($isFr ? 'Stands' : 'Stands').' ('.$totalStands.')', false],
        [$isFr ? 'Plan du pavillon' : 'Pavilion map', false],
        [$isFr ? 'Services' : 'Services', false],
        [$isFr ? 'Historique' : 'History', false],
    ];

    // ── Illustrative donut: category split (aggregate figures, allowed) ────────
    $catSplit = [
        [$isFr ? 'Institutionnel'      : 'Institutional',   '#157A43'],
        [$isFr ? 'Culture & Traditions': 'Culture & Heritage','#C97A16'],
        [$isFr ? 'Design & Innovation' : 'Design & Innovation','#7C4FE0'],
        [$isFr ? 'Artisanat d\'Art'    : 'Fine crafts',     '#3565DE'],
        [$isFr ? 'Autres'              : 'Others',          '#9CA3AF'],
    ];

    // ── Services checklist (design items — feature list, not record data) ──────
    $services = [
        $isFr ? 'Sécurité 24/7'          : '24/7 security',
        $isFr ? 'Électricité (220V)'     : 'Electricity (220V)',
        $isFr ? 'Connexion Wi-Fi'        : 'Wi-Fi connection',
        $isFr ? 'Espace de stockage'     : 'Storage space',
        $isFr ? 'Climatisation'          : 'Air conditioning',
        $isFr ? 'Espace VIP & Réunions'  : 'VIP & meeting area',
        $isFr ? 'Service de nettoyage'   : 'Cleaning service',
        $isFr ? 'Restauration'           : 'Catering',
    ];

    // Back link
    $backHref = $h('siarc.admin.pavilions') ?? ($sLinks[0]['href'] ?? null);
    if (is_array($sLinks) && isset($sLinks[0]) && is_string($sLinks[0])) $backHref = $sLinks[0];

    // Quick-action routes (guarded; href omitted when unavailable)
    $editHref = $h('siarc.admin.pavilions');
    $addHref  = $h('siarc.admin.stands');
@endphp

<div class="siarc-in">

    {{-- ══ BREADCRUMB ══ --}}
    <div class="flex items-center gap-2 text-[13px] mb-5">
        @if($backHref)
        <a href="{{ $backHref }}" class="inline-flex items-center gap-1.5 font-semibold text-siarc-green hover:gap-2 transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Pavillons' : 'Pavilions' }}
        </a>
        @else
        <span class="inline-flex items-center gap-1.5 font-semibold text-siarc-green">
            <i data-lucide="building-2" class="w-4 h-4"></i>{{ $isFr ? 'Pavillons' : 'Pavilions' }}
        </span>
        @endif
        <i data-lucide="chevron-right" class="w-4 h-4 text-[#B0AB9F]"></i>
        <span class="text-[#55524A]">{{ $isFr ? 'Détails du Pavillon' : 'Pavilion Details' }}</span>
    </div>

    {{-- ══ TOP: HEADER CARD (2/3) + STATUS PANEL (1/3) ══ --}}
    <div class="grid xl:grid-cols-[1fr_320px] gap-5 mb-5 items-start">

        {{-- ── HEADER CARD ── --}}
        <div class="siarc-card siarc-shadow overflow-hidden">
            <div class="siarc-kente siarc-kente-thin"></div>
            <div class="p-6 grid lg:grid-cols-[150px_1fr_1fr] gap-6 items-start">

                {{-- pavilion thumbnail (heritage tile — no external photo) --}}
                <div class="w-[150px] h-[150px] rounded-2xl siarc-adire shrink-0 flex flex-col items-center justify-center siarc-shadow relative overflow-hidden">
                    <i data-lucide="building-2" class="w-9 h-9 text-siarc-gold mb-1"></i>
                    <span class="font-display text-[26px] font-extrabold text-white leading-none">
                        {{ mb_strtoupper(mb_substr($pavName,0,2)) }}
                    </span>
                </div>

                {{-- identity --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $pavName }}</h1>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#E2F3E8] text-[#0F4824]">
                            <i data-lucide="circle-dot" class="w-3 h-3"></i>{{ $isFr ? 'Actif' : 'Active' }}
                        </span>
                    </div>

                    <p class="mt-3 text-[13px] text-[#55524A] flex items-center gap-2">
                        <span class="siarc-kicker !text-[10px] !gap-1 !tracking-wide text-[#8A857A] before:hidden after:hidden">{{ $isFr ? 'Code' : 'Code' }}</span>
                        <span class="font-semibold text-[#161513]">{{ $sTitle && str_contains($sTitle,'·') ? '—' : '—' }}</span>
                        <span class="text-[#D8D4C9]">·</span>
                        <span class="text-[#55524A]">{{ $isFr ? 'Pavillon Institutionnel' : 'Institutional Pavilion' }}</span>
                    </p>
                    <p class="mt-2 text-[13px] text-[#55524A] flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-siarc-ochre"></i>
                        {{ $isFr ? 'Musée National de Yaoundé' : 'National Museum of Yaoundé' }}
                    </p>
                    <p class="mt-1.5 text-[13px] text-[#55524A] flex items-center gap-1.5">
                        <i data-lucide="globe" class="w-4 h-4 text-siarc-green"></i>
                        {{ $isFr ? 'Cameroun' : 'Cameroon' }}
                    </p>
                    <p class="mt-3 text-[13px] leading-relaxed text-[#55524A]">
                        {{ $isFr
                            ? 'Espace d\'exposition dédié aux exposants et artisans du salon. La répartition des stands du pavillon est présentée ci-dessous.'
                            : 'Exhibition space dedicated to the fair\'s exhibitors and artisans. The pavilion\'s stand allocation is shown below.' }}
                    </p>
                </div>

                {{-- heritage banner (right, replaces photo) --}}
                <div class="rounded-2xl overflow-hidden siarc-adire relative min-h-[150px] flex items-center justify-center siarc-shadow">
                    <div class="relative text-center px-5 py-6">
                        <span class="w-12 h-12 mx-auto mb-2.5 rounded-2xl bg-white/10 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6 text-siarc-gold"></i>
                        </span>
                        <p class="font-display text-[19px] font-extrabold text-white tracking-wide uppercase leading-tight">{{ $pavName }}</p>
                        <p class="mt-1 text-[11px] font-semibold text-white/70 tracking-[0.22em] uppercase">SIARC 2026</p>
                    </div>
                </div>
            </div>

            {{-- metadata row --}}
            <div class="px-6 pb-6">
                <div class="border-t border-[#ECEAE3] pt-5 grid grid-cols-2 sm:grid-cols-4 gap-5">
                    @foreach($metaRow as [$mi,$ml,$mv])
                    <div class="flex items-start gap-2.5">
                        <span class="w-9 h-9 rounded-lg bg-[#F5F3EE] flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $mi }}" class="w-[18px] h-[18px] text-[#8A857A]"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wide text-[#8A857A]">{{ $ml }}</p>
                            <p class="text-[13.5px] font-bold text-[#161513] leading-tight mt-0.5 truncate">{{ $mv }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ── STATUS PANEL + QUICK ACTIONS ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Statut du pavillon' : 'Pavilion status' }}</h3>
            <dl class="space-y-3 text-[13px]">
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">{{ $isFr ? 'Statut' : 'Status' }}</dt>
                    <dd><span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#E2F3E8] text-[#0F4824]"><i data-lucide="circle-dot" class="w-3 h-3"></i>{{ $isFr ? 'Actif' : 'Active' }}</span></dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">{{ $isFr ? 'Stands alloués' : 'Allocated stands' }}</dt>
                    <dd class="font-semibold text-[#161513]">{{ $standsAlloc }} / {{ $standsTotal }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">{{ $isFr ? 'Responsable' : 'Manager' }}</dt>
                    <dd class="font-semibold text-[#B0AB9F]">{{ $isFr ? 'Non assigné' : 'Unassigned' }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">{{ $isFr ? 'Téléphone' : 'Phone' }}</dt>
                    <dd class="text-[#B0AB9F]">{{ $dash }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">{{ $isFr ? 'Email' : 'Email' }}</dt>
                    <dd class="text-[#B0AB9F]">{{ $dash }}</dd>
                </div>
            </dl>

            <p class="siarc-kicker !text-[10px] text-[#8A857A] mt-5 mb-3 before:hidden">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</p>
            <div class="flex items-center gap-2">
                @if($editHref)
                <a href="{{ $editHref }}" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2 flex-1 justify-center">
                    <i data-lucide="square-pen" class="w-4 h-4"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
                </a>
                @else
                <button type="button" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2 flex-1 justify-center opacity-60 cursor-not-allowed">
                    <i data-lucide="square-pen" class="w-4 h-4"></i>{{ $isFr ? 'Modifier' : 'Edit' }}
                </button>
                @endif
                @if($addHref)
                <a href="{{ $addHref }}" class="siarc-btn siarc-btn-outline !border-[#D8E5DC] !text-siarc-green text-[12.5px] px-3.5 py-2 flex-1 justify-center hover:bg-[#E2F3E8]">
                    <i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un stand' : 'Add a stand' }}
                </a>
                @else
                <button type="button" class="siarc-btn siarc-btn-outline !border-[#D8E5DC] !text-siarc-green text-[12.5px] px-3.5 py-2 flex-1 justify-center hover:bg-[#E2F3E8]">
                    <i data-lucide="plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un stand' : 'Add a stand' }}
                </button>
                @endif
                <button type="button" class="siarc-btn !border !border-[#EFEDE6] text-[#8A857A] px-2.5 py-2 hover:bg-[#FBFAF6]" aria-label="{{ $isFr ? 'Plus d\'actions' : 'More actions' }}">
                    <i data-lucide="ellipsis" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══ KPI TILE ROW ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
        @foreach($kpis as [$icon,$color,$tile,$label,$value,$sub])
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $value }}</p>
            <p class="mt-1.5 text-[11px] font-semibold" style="color:{{ $color }}">{{ $sub }}</p>
        </div>
        @endforeach
    </div>

    {{-- ══ TAB STRIP ══ --}}
    <div class="border-b border-[#ECEAE3] mb-5 overflow-x-auto">
        <div class="flex items-center gap-6 min-w-max">
            @foreach($tabs as [$tLabel,$active])
            <button type="button" @class([
                'relative pb-3 text-[13.5px] font-semibold whitespace-nowrap transition-colors',
                'text-siarc-green' => $active,
                'text-[#8A857A] hover:text-[#3B382F]' => !$active,
            ])>
                {{ $tLabel }}
                @if($active)<span class="absolute left-0 -bottom-px h-[2.5px] w-full rounded-full bg-siarc-green"></span>@endif
            </button>
            @endforeach
        </div>
    </div>

    {{-- ══ MAIN: STANDS (2/3) + RIGHT COLUMN (1/3) ══ --}}
    <div class="grid xl:grid-cols-[1fr_360px] gap-5">

        {{-- ═══════════ LEFT: STAND GRID + TABLE ═══════════ --}}
        <div class="space-y-5">

            {{-- visual stand grid + legend --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                    <h3 class="text-[14px] font-bold text-[#1A1712] flex items-center gap-2">
                        <i data-lucide="grid-3x3" class="w-4 h-4 text-siarc-green"></i>
                        {{ $isFr ? 'Plan du pavillon' : 'Pavilion map' }}
                    </h3>
                    <div class="flex items-center flex-wrap gap-x-5 gap-y-2 text-[11.5px]">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[5px]" style="background:#E2F3E8;border:1px solid #B9E2C7"></span><span class="text-[#3B382F] font-medium">{{ $isFr ? 'Occupé' : 'Occupied' }}</span></span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[5px]" style="background:#F4F4F1;border:1px solid #E4E3DD"></span><span class="text-[#3B382F] font-medium">{{ $isFr ? 'Disponible' : 'Available' }}</span></span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[5px]" style="background:#E8EFFB;border:1px solid #C6D8F5"></span><span class="text-[#3B382F] font-medium">{{ $isFr ? 'Réservé' : 'Reserved' }}</span></span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[5px]" style="background:#FDE8E8;border:1px solid #F3C4C4"></span><span class="text-[#3B382F] font-medium">{{ $isFr ? 'Maintenance' : 'Maintenance' }}</span></span>
                    </div>
                </div>

                @if($totalStands)
                <div class="grid grid-cols-[repeat(auto-fill,minmax(82px,1fr))] gap-2.5">
                    @foreach($standRows as $row)
                        @php
                            $cells  = $row['cells'] ?? [];
                            $code   = is_string($cells[0] ?? null) ? $cells[0] : ($dash);
                            $badge  = $cells[1] ?? [];
                            $tone   = is_array($badge) ? ($badge['tone'] ?? 'grey') : 'grey';
                            $bLabel = is_array($badge) ? ($badge['badge'] ?? '') : (is_string($badge) ? $badge : '');
                            $exhRaw = $cells[2] ?? null;
                            $exh    = is_string($exhRaw) ? $exhRaw : $dash;
                            $c      = $toneOf($tone);
                            $href   = $row['href'] ?? null;
                        @endphp
                        @if($href)
                        <a href="{{ $href }}"
                           class="rounded-xl px-2 py-2.5 text-center siarc-lift block"
                           style="background:{{ $c['bg'] }};border:1px solid {{ $c['bd'] }}"
                           @if($exh !== $dash && $exh !== '') title="{{ $exh }}" @endif>
                            <p class="font-display text-[15px] font-extrabold leading-none" style="color:{{ $c['fg'] }}">{{ $code }}</p>
                            <p class="mt-1.5 text-[9px] font-semibold uppercase tracking-wide truncate" style="color:{{ $c['fg'] }};opacity:.75">{{ $bLabel }}</p>
                        </a>
                        @else
                        <div class="rounded-xl px-2 py-2.5 text-center block"
                             style="background:{{ $c['bg'] }};border:1px solid {{ $c['bd'] }}"
                             @if($exh !== $dash && $exh !== '') title="{{ $exh }}" @endif>
                            <p class="font-display text-[15px] font-extrabold leading-none" style="color:{{ $c['fg'] }}">{{ $code }}</p>
                            <p class="mt-1.5 text-[9px] font-semibold uppercase tracking-wide truncate" style="color:{{ $c['fg'] }};opacity:.75">{{ $bLabel }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
                @else
                <div class="text-center py-12 rounded-xl bg-[#FBFAF6] border border-dashed border-[#E4E3DD]">
                    <span class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-white flex items-center justify-center siarc-shadow">
                        <i data-lucide="grid-3x3" class="w-6 h-6 text-[#B0AB9F]"></i>
                    </span>
                    <p class="text-[13px] font-semibold text-[#55524A]">{{ $isFr ? 'Aucun stand' : 'No stands' }}</p>
                    <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'Aucun stand n\'est encore rattaché à ce pavillon.' : 'No stand is attached to this pavilion yet.' }}</p>
                </div>
                @endif
            </div>

            {{-- detailed table --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="px-5 py-4 border-b border-[#ECEAE3] flex items-center justify-between">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $standsTable['title'] ?? ($isFr ? 'Stands du pavillon' : 'Pavilion stands') }}</h3>
                    <span class="text-[11.5px] font-medium text-[#8A857A]">{{ $totalStands }} {{ $isFr ? 'stands' : 'stands' }}</span>
                </div>
                @if($totalStands)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[11px] font-semibold uppercase tracking-wide text-[#8A857A] bg-[#FBFAF6]">
                                @foreach($standCols as $col)
                                <th class="px-5 py-3">{{ is_string($col) ? $col : '' }}</th>
                                @endforeach
                                <th class="px-5 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F0EB]">
                            @foreach($standRows as $row)
                                @php
                                    $cells  = $row['cells'] ?? [];
                                    $code   = is_string($cells[0] ?? null) ? $cells[0] : $dash;
                                    $badge  = $cells[1] ?? [];
                                    $tone   = is_array($badge) ? ($badge['tone'] ?? 'grey') : 'grey';
                                    $bLabel = is_array($badge) ? ($badge['badge'] ?? '') : (is_string($badge) ? $badge : '');
                                    $exhRaw = $cells[2] ?? null;
                                    $exh    = is_string($exhRaw) ? $exhRaw : $dash;
                                    $href   = $row['href'] ?? null;
                                    $c      = $toneOf($tone);
                                @endphp
                                <tr class="hover:bg-[#FBFAF6] transition-colors">
                                    <td class="px-5 py-3.5">
                                        <span class="font-display text-[14px] font-extrabold text-[#161513]">{{ $code }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full"
                                              style="background:{{ $c['bg'] }};color:{{ $c['fg'] }}">
                                            <i data-lucide="circle-dot" class="w-3 h-3"></i>{{ $bLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($exh !== $dash && $exh !== '')
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-full bg-siarc-green text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                                    {{ mb_strtoupper(mb_substr($exh,0,2)) }}
                                                </span>
                                                <span class="text-[13px] font-medium text-[#3B382F]">{{ $exh }}</span>
                                            </div>
                                        @else
                                            <span class="text-[13px] text-[#B0AB9F]">{{ $dash }}</span>
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
                    <p class="text-[12px] text-[#8A857A] mt-1">{{ $isFr ? 'Les stands de ce pavillon apparaîtront ici.' : 'This pavilion\'s stands will appear here.' }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════ RIGHT COLUMN ═══════════ --}}
        <div class="space-y-5">

            {{-- Répartition par catégorie (illustrative donut) --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Répartition par catégorie' : 'Breakdown by category' }}</h3>
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <svg viewBox="0 0 120 120" class="w-[120px] h-[120px] -rotate-90">
                            @php $circ=326.7; $off=0; $donut=[[29,'#157A43'],[22,'#C97A16'],[17,'#7C4FE0'],[15,'#3565DE'],[17,'#9CA3AF']]; @endphp
                            @foreach($donut as [$pct,$col])
                                <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $col }}" stroke-width="15"
                                    stroke-dasharray="{{ round($circ*$pct/100,1) }} {{ round($circ-($circ*$pct/100),1) }}"
                                    stroke-dashoffset="{{ -round($off,1) }}"/>
                                @php $off += $circ*$pct/100; @endphp
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-[20px] font-extrabold text-[#1A1712] leading-none">{{ $standsAlloc }}</span>
                            <span class="text-[9.5px] text-[#8A857A] mt-0.5">{{ $isFr ? 'occupés' : 'occupied' }}</span>
                        </div>
                    </div>
                    <ul class="flex-1 space-y-2">
                        @foreach($catSplit as $i => [$name,$col])
                        <li class="flex items-center gap-2 text-[11.5px]">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span>
                            <span class="text-[#3B382F] font-medium truncate">{{ $name }}</span>
                            <span class="ml-auto text-[#8A857A] whitespace-nowrap">{{ $donut[$i][0] }}%</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Services disponibles --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Services disponibles' : 'Available services' }}</h3>
                <ul class="grid grid-cols-1 gap-2.5">
                    @foreach($services as $svc)
                    <li class="flex items-center gap-2 text-[12.5px] text-[#3B382F]">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0"></i>
                        <span>{{ $svc }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Performance (illustrative line chart) --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Performance du pavillon' : 'Pavilion performance' }}</h3>
                    <span class="text-[11px] font-medium text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1 inline-flex items-center gap-1">
                        {{ $isFr ? '30 derniers jours' : 'Last 30 days' }}<i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </span>
                </div>
                <div class="flex items-center gap-2 mb-2 text-[11px] text-[#8A857A]">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-0.5 bg-siarc-green"></span>{{ $isFr ? 'Occupation' : 'Occupancy' }}</span>
                </div>
                <svg viewBox="0 0 320 160" class="w-full">
                    @foreach([0,40,80,120] as $gy)<line x1="24" y1="{{ 16+$gy/1 * 0.9 }}" x2="315" y2="{{ 16+$gy * 0.9 }}" stroke="#F1F1EF"/>@endforeach
                    @foreach([[30,120],[70,110],[110,88],[150,96],[190,70],[230,78],[270,52],[315,40]] as $p)@endforeach
                    <polygon points="30,140 30,120 70,110 110,88 150,96 190,70 230,78 270,52 315,40 315,140" fill="#157A43" opacity="0.08"/>
                    <polyline points="30,120 70,110 110,88 150,96 190,70 230,78 270,52 315,40" fill="none" stroke="#157A43" stroke-width="2.2"/>
                    @foreach([[30,120],[110,88],[190,70],[270,52],[315,40]] as [$cx,$cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="2.8" fill="#157A43"/>@endforeach
                    <circle cx="315" cy="40" r="4.2" fill="#157A43"/>
                    @foreach(['06','13','20','27',($isFr?'Auj.':'Now')] as $i=>$d)<text x="{{ 30+$i*71 }}" y="155" font-size="8" fill="#B0AB9F" text-anchor="middle">{{ $d }}</text>@endforeach
                </svg>
            </div>

            {{-- Activités récentes — honest empty state (no invented records) --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Activités récentes' : 'Recent activity' }}</h3>
                </div>
                <div class="text-center py-8 rounded-xl bg-[#FBFAF6] border border-dashed border-[#E4E3DD]">
                    <span class="w-11 h-11 mx-auto mb-3 rounded-2xl bg-white flex items-center justify-center siarc-shadow">
                        <i data-lucide="activity" class="w-5 h-5 text-[#B0AB9F]"></i>
                    </span>
                    <p class="text-[12.5px] font-semibold text-[#55524A]">{{ $isFr ? 'Aucune activité récente' : 'No recent activity' }}</p>
                    <p class="text-[11.5px] text-[#8A857A] mt-1 px-4">{{ $isFr ? 'Les mouvements de stands et paiements du pavillon s\'afficheront ici.' : 'The pavilion\'s stand movements and payments will appear here.' }}</p>
                </div>
            </div>
        </div>
    </div>

</div>
