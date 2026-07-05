@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Real data from the route ──────────────────────────────────────────────
    $sStats  = $sStats  ?? [];
    $sTables = $sTables ?? [];

    // KPI figures (Jours / Sessions). Fall back to derived counts.
    $nDays = count($sTables);
    $nSessions = 0;
    foreach ($sTables as $d) { $nSessions += count($d['rows'] ?? []); }
    $stats = [];
    if (!empty($sStats)) {
        foreach ($sStats as $s) {
            $stats[] = [
                $s[4] ?? '',
                (string)($s[3] ?? '0'),
            ];
        }
    } else {
        $stats = [['Jours', (string)$nDays], ['Sessions', (string)$nSessions]];
    }
    $statMeta = [
        ['calendar-days','#157A43','#E2F3E8'],
        ['presentation','#C97A16','#FDF3E0'],
    ];

    // ── Type → colour map (matches the design legend) ─────────────────────────
    $typeColor = function ($type) {
        $t = mb_strtolower(trim((string)$type));
        if (str_contains($t,'conférence') || str_contains($t,'conference') || str_contains($t,'panel') === false && str_contains($t,'confé')) {}
        return match (true) {
            str_contains($t,'panel') || str_contains($t,'table')            => ['#2E8B57','#EAF6EF','#DCEFE3'], // Panel / Table ronde
            str_contains($t,'atelier')                                       => ['#E6B201','#FEF6DC','#FBEDBE'], // Atelier
            str_contains($t,'b2b') || str_contains($t,'matchmaking')         => ['#0B3A1E','#E6EEE9','#D2E2D8'], // B2B / Matchmaking
            str_contains($t,'cérémonie') || str_contains($t,'ceremonie') || str_contains($t,'ouverture') || str_contains($t,'clôture') || str_contains($t,'cloture') => ['#157A43','#E2F3E8','#CDE9D7'], // Cérémonie
            str_contains($t,'animation') || str_contains($t,'culture') || str_contains($t,'soirée') || str_contains($t,'cocktail') || str_contains($t,'défilé') || str_contains($t,'defile') => ['#3565DE','#E8EFFB','#D4E2F8'], // Animation / Culture
            str_contains($t,'conf')                                          => ['#7C4FE0','#F0EAFB','#E2D6F6'], // Conférence
            default                                                          => ['#8A857A','#F0EFEC','#E4E2DB'], // Autre
        };
    };

    $legend = [
        ['Conférence','#7C4FE0'],
        ['Atelier','#E6B201'],
        ['Panel / Table ronde','#2E8B57'],
        ['B2B / Matchmaking','#0B3A1E'],
        ['Cérémonie','#157A43'],
        ['Animation / Culture','#3565DE'],
        ['Autre','#8A857A'],
    ];

    // ── Build day cards for the agenda grid ───────────────────────────────────
    // Each $sTables entry: ['title'=>"lundi 27 juillet 2026",'cols'=>[...],'rows'=>[{cells:[HH:MM,title,Type]}]]
    $frDow = ['Mon'=>'LUN','Tue'=>'MAR','Wed'=>'MER','Thu'=>'JEU','Fri'=>'VEN','Sat'=>'SAM','Sun'=>'DIM'];
    $days = [];
    foreach ($sTables as $d) {
        $title = trim((string)($d['title'] ?? ''));
        // Parse "lundi 27 juillet 2026" → dow label + "27 JUIL." style short.
        $ts = strtotime($title);
        $dow = ''; $shortDate = $title;
        if ($ts) {
            $dow = $frDow[date('D',$ts)] ?? strtoupper(date('D',$ts));
            $moFr = [1=>'JANV.',2=>'FÉVR.',3=>'MARS',4=>'AVR.',5=>'MAI',6=>'JUIN',7=>'JUIL.',8=>'AOÛT',9=>'SEPT.',10=>'OCT.',11=>'NOV.',12=>'DÉC.'];
            $shortDate = (int)date('j',$ts).' '.($moFr[(int)date('n',$ts)] ?? '');
        } else {
            // Fallback: first word may be day-of-week in FR.
            $parts = preg_split('/\s+/', $title);
            $dow = strtoupper(mb_substr($parts[0] ?? '', 0, 3));
            $shortDate = trim(preg_replace('/^\S+\s*/', '', $title));
        }
        $sessions = [];
        foreach (($d['rows'] ?? []) as $r) {
            $c = $r['cells'] ?? $r;
            $sessions[] = [
                'time'  => $c[0] ?? '',
                'title' => $c[1] ?? '',
                'type'  => $c[2] ?? '',
            ];
        }
        // Sort by time.
        usort($sessions, fn($a,$b) => strcmp($a['time'],$b['time']));
        $days[] = ['dow'=>$dow, 'short'=>$shortDate, 'full'=>$title, 'sessions'=>$sessions, 'ts'=>$ts];
    }

    // Period label for the toolbar (first → last day).
    $periodLabel = 'Juillet – Août 2026';
    if (!empty($days)) {
        $first = $days[0]['short'] ?? '';
        $last  = end($days)['short'] ?? '';
        if ($first && $last) $periodLabel = ucfirst(mb_strtolower($first)).' – '.ucfirst(mb_strtolower($last)).' 2026';
    }
    reset($days);
@endphp

<div class="siarc-in">

    {{-- ═══ KPI ROW ═══ --}}
    <div class="grid grid-cols-2 sm:max-w-md gap-4 mb-5">
        @foreach($stats as $i => [$label,$val])
        @php [$icon,$color,$tile] = $statMeta[$i] ?? $statMeta[0]; @endphp
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}</p>
        </div>
        @endforeach
    </div>

    {{-- ═══ TOOLBAR ═══ --}}
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="inline-flex rounded-xl border border-[#E6E3DB] bg-white p-1 siarc-shadow">
            <span class="siarc-btn siarc-btn-green px-3.5 py-2 text-[12.5px]"><i data-lucide="layout-grid" class="w-4 h-4"></i>Vue calendrier</span>
            <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 text-[12.5px] font-semibold text-[#55524A] rounded-lg hover:bg-[#F5F3EE]"><i data-lucide="clipboard-list" class="w-4 h-4"></i>Vue liste</a>
        </div>
        <div class="inline-flex items-center gap-2 rounded-xl border border-[#E6E3DB] bg-white px-3.5 py-2.5 text-[12.5px] font-medium text-[#55524A] siarc-shadow">
            <i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A]"></i>Tous les lieux<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
        </div>
        <div class="ml-auto flex items-center gap-3">
            <a href="{{ $h('siarc.admin.reports') }}" class="siarc-btn px-3.5 py-2.5 text-[12.5px] font-semibold text-[#55524A] bg-white border border-[#E6E3DB] siarc-shadow"><i data-lucide="download" class="w-4 h-4"></i>Exporter</a>
            <a href="{{ $h('siarc.admin.programme') }}" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[12.5px]"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un événement</a>
        </div>
    </div>

    {{-- ═══ MAIN GRID: calendar (left) + sidebar (right) ═══ --}}
    <div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5">

        {{-- ── LEFT: month/agenda calendar ── --}}
        <div class="siarc-card siarc-shadow overflow-hidden">
            {{-- month nav bar --}}
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-[#EEECE5]">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE] cursor-pointer"><i data-lucide="arrow-left" class="w-4 h-4"></i></span>
                    <span class="px-3.5 h-8 rounded-lg border border-[#E6E3DB] flex items-center text-[12px] font-semibold text-[#55524A] hover:bg-[#F5F3EE] cursor-pointer">Aujourd'hui</span>
                    <span class="w-8 h-8 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE] cursor-pointer"><i data-lucide="arrow-right" class="w-4 h-4"></i></span>
                </div>
                <h2 class="font-display text-[16px] font-bold text-[#1A1712] flex items-center gap-1.5">{{ $periodLabel }}<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i></h2>
                <span class="inline-flex items-center gap-1.5 px-3.5 h-8 rounded-lg border border-[#E6E3DB] text-[12px] font-semibold text-[#55524A] hover:bg-[#F5F3EE] cursor-pointer"><i data-lucide="filter" class="w-3.5 h-3.5"></i>Filtres</span>
            </div>

            {{-- weekday header --}}
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 border-b border-[#EEECE5] bg-[#FBFAF6]">
                @foreach(['LUN','MAR','MER','JEU','VEN','SAM','DIM'] as $wd)
                <div class="px-3 py-2.5 text-[11px] font-bold tracking-wide text-[#8A857A] text-center border-r border-[#F1EFE9] last:border-r-0">{{ $wd }}</div>
                @endforeach
            </div>

            {{-- day cells --}}
            @if(empty($days))
                <div class="p-10 text-center">
                    <span class="w-12 h-12 mx-auto rounded-xl bg-[#F0EFEC] flex items-center justify-center mb-3"><i data-lucide="calendar" class="w-6 h-6 text-[#B0AB9F]"></i></span>
                    <p class="text-[13px] font-semibold text-[#55524A]">Aucune session programmée</p>
                    <p class="text-[12px] text-[#8A857A] mt-1">À venir</p>
                </div>
            @else
            <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7">
                @foreach($days as $day)
                @php $isToday = $day['ts'] && date('Y-m-d',$day['ts']) === '2026-07-27'; @endphp
                <div class="min-h-[190px] p-2.5 border-r border-b border-[#F1EFE9] last:border-r-0 xl:[&:nth-child(7n)]:border-r-0 align-top">
                    {{-- date header --}}
                    <div class="flex items-center gap-1.5 mb-2">
                        @if($isToday)
                        <span class="w-6 h-6 rounded-full bg-siarc-green text-white text-[11px] font-bold flex items-center justify-center">{{ preg_replace('/\D.*$/','',$day['short']) }}</span>
                        <span class="text-[11px] font-bold text-[#8A857A]">{{ trim(preg_replace('/^\d+\s*/','',$day['short'])) }}</span>
                        @else
                        <span class="text-[12px] font-bold text-[#3B382F]">{{ $day['short'] }}</span>
                        @endif
                    </div>

                    {{-- sessions (max 3 shown, rest as +N) --}}
                    @php $shown = array_slice($day['sessions'], 0, 3); $extra = count($day['sessions']) - count($shown); @endphp
                    <div class="space-y-1.5">
                        @foreach($shown as $s)
                        @php [$ink,$bg,$bd] = $typeColor($s['type']); @endphp
                        <div class="rounded-lg px-2 py-1.5 border-l-[3px]" style="background:{{ $bg }};border-color:{{ $ink }}">
                            <p class="text-[10.5px] font-bold" style="color:{{ $ink }}">{{ $s['time'] }}</p>
                            <p class="text-[11px] font-semibold text-[#2A2822] leading-tight line-clamp-2">{{ $s['title'] }}</p>
                            @if(!empty($s['type']))
                            <p class="mt-1 flex items-center gap-1 text-[10px] text-[#8A857A]"><i data-lucide="tag" class="w-3 h-3"></i>{{ $s['type'] }}</p>
                            @endif
                        </div>
                        @endforeach
                        @if($extra > 0)
                        <p class="text-[11px] font-semibold text-[#8A857A] pl-1">+{{ $extra }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- legend --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 px-4 py-3.5 border-t border-[#EEECE5] bg-[#FBFAF6]">
                @foreach($legend as [$lbl,$col])
                <span class="inline-flex items-center gap-1.5 text-[11.5px] text-[#55524A] font-medium"><span class="w-2.5 h-2.5 rounded-full" style="background:{{ $col }}"></span>{{ $lbl }}</span>
                @endforeach
            </div>
        </div>

        {{-- ── RIGHT: sidebar ── --}}
        <div class="space-y-5">

            {{-- mini calendar --}}
            <div class="siarc-card siarc-shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-7 h-7 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE] cursor-pointer"><i data-lucide="chevron-right" class="w-4 h-4 rotate-180"></i></span>
                    <p class="text-[13px] font-bold text-[#1A1712]">{{ $periodLabel }}</p>
                    <span class="w-7 h-7 rounded-lg border border-[#E6E3DB] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE] cursor-pointer"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                </div>
                <div class="grid grid-cols-7 gap-0.5 text-center mb-1">
                    @foreach(['L','M','M','J','V','S','D'] as $d)<span class="text-[10.5px] font-bold text-[#B0AB9F] py-1">{{ $d }}</span>@endforeach
                </div>
                @php
                    // Static July→August grid matching the design (starts Mon 20 Jul).
                    $miniCells = [
                        [20,21,22,23,24,25,26],
                        [27,28,29,30,31,1,2],
                        [3,4,5,6,7,8,9],
                        [10,11,12,13,14,15,16],
                    ];
                    $eventDays = [23,24,25,27,28,29,30,31,1,2,3,4,5]; // days that have sessions
                @endphp
                <div class="grid grid-cols-7 gap-0.5 text-center">
                    @foreach($miniCells as $wk)
                        @foreach($wk as $ci => $num)
                        @php
                            $isTodayMini = ($num === 27);
                            $hasEvent = in_array($num, $eventDays, true);
                        @endphp
                        <span class="relative aspect-square flex items-center justify-center text-[11.5px] rounded-lg
                            {{ $isTodayMini ? 'bg-siarc-green text-white font-bold' : ($hasEvent ? 'text-[#1A1712] font-semibold hover:bg-[#E2F3E8]' : 'text-[#B0AB9F]') }} cursor-pointer">
                            {{ $num }}
                            @if($hasEvent && !$isTodayMini)<span class="absolute bottom-1 w-1 h-1 rounded-full bg-siarc-ochre"></span>@endif
                        </span>
                        @endforeach
                    @endforeach
                </div>
            </div>

            {{-- événements du jour --}}
            <div class="siarc-card siarc-shadow p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[13.5px] font-bold text-[#1A1712]">Événements du jour</h3>
                    <span class="w-6 h-6 rounded-full bg-[#E2F3E8] text-siarc-green text-[11px] font-bold flex items-center justify-center">{{ count($days[0]['sessions'] ?? []) }}</span>
                </div>
                @php $todaySessions = $days[0]['sessions'] ?? []; @endphp
                @if(empty($todaySessions))
                    <p class="text-[12px] text-[#8A857A] py-4 text-center">Aucun événement — À venir</p>
                @else
                <ul class="relative pl-4 space-y-4">
                    <span class="absolute left-[3px] top-1.5 bottom-1.5 w-px bg-[#EEECE5]"></span>
                    @foreach($todaySessions as $s)
                    @php [$ink] = $typeColor($s['type']); @endphp
                    <li class="relative">
                        <span class="absolute -left-4 top-1 w-[7px] h-[7px] rounded-full ring-2 ring-white" style="background:{{ $ink }}"></span>
                        <p class="text-[11px] font-bold text-[#8A857A]">{{ $s['time'] }}</p>
                        <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">{{ $s['title'] }}</p>
                        @if(!empty($s['type']))<p class="text-[11px] text-[#8A857A] mt-0.5 flex items-center gap-1"><i data-lucide="tag" class="w-3 h-3"></i>{{ $s['type'] }}</p>@endif
                    </li>
                    @endforeach
                </ul>
                @endif
                <a href="{{ $h('siarc.admin.programme') }}" class="mt-4 block text-center text-[12px] font-semibold text-siarc-green border border-[#D8E5DC] rounded-xl px-3 py-2.5 hover:bg-[#E2F3E8]">Voir le programme complet</a>
            </div>

            {{-- téléchargements --}}
            <div class="siarc-card siarc-shadow p-4">
                <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-3">Téléchargements</h3>
                <ul class="space-y-2.5">
                    @foreach(['Programme complet (PDF)','Plan des salles (PDF)','Brochure des activités (PDF)'] as $doc)
                    <li>
                        <a href="{{ $h('siarc.admin.reports') }}" class="flex items-center gap-2.5 rounded-lg px-2 py-2 hover:bg-[#FBFAF6] transition-colors">
                            <span class="w-8 h-8 rounded-lg bg-[#FDF3E0] flex items-center justify-center shrink-0"><i data-lucide="clipboard-list" class="w-4 h-4 text-siarc-ochre"></i></span>
                            <span class="text-[12px] font-medium text-[#3B382F] flex-1">{{ $doc }}</span>
                            <i data-lucide="download" class="w-4 h-4 text-[#B0AB9F]"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.admin.reports') }}" class="mt-3 block text-center text-[12px] font-semibold text-siarc-green border border-[#D8E5DC] rounded-xl px-3 py-2.5 hover:bg-[#E2F3E8]">Voir tous les téléchargements</a>
            </div>

        </div>
    </div>
</div>
