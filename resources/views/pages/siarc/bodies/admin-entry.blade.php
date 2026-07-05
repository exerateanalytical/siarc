@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Normalise passed data ────────────────────────────────────────────────
    $stats  = collect($sStats ?? [])->values();
    $table  = collect($sTables ?? [])->first();
    $tCols  = data_get($table, 'cols', ['Type','ID','Porte','Heure']);
    $tRows  = collect(data_get($table, 'rows', []));
    $links  = collect($sLinks ?? []);

    // KPI tiles — driven by $sStats (approved headline figures where present)
    $kpiTiles = [
        ['users-round',   '#157A43', '#E2F3E8'],
        ['id-card',       '#3565DE', '#E8EFFB'],
        ['scan-line',     '#7C4FE0', '#F0EAFB'],
    ];

    // Badge tone by passage type
    $tone = function($t){
        $t = strtolower((string)$t);
        if (str_contains($t,'refus') || str_contains($t,'declin')) return ['#FDE8E8','#C0010C'];
        if (str_contains($t,'exposant') || str_contains($t,'vip'))  return ['#E8EFFB','#3565DE'];
        if (str_contains($t,'staff'))                                return ['#F1F1EF','#55524A'];
        return ['#E2F3E8','#157A43'];
    };
@endphp

{{-- ══ PAGE HEADER ══ --}}
<div class="mb-5 siarc-in">
    <h1 class="font-display text-[26px] font-extrabold text-[#0B3A1E] leading-tight">Contrôle d'accès</h1>
    <p class="text-[13px] text-[#55524A] mt-0.5">Scannez les badges et surveillez les entrées en temps réel au SIARC 2026.</p>
</div>

{{-- ══ KPI TILES (from $sStats) ══ --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
    @foreach($stats as $i => $st)
        @php [$icon,$color,$tile] = $kpiTiles[$i % count($kpiTiles)]; @endphp
        <div class="siarc-card siarc-shadow p-4">
            <div class="flex items-start justify-between">
                <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                    <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
                </span>
                @if(data_get($st,'trend'))
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-green">
                        <i data-lucide="arrow-up" class="w-3 h-3"></i>{{ data_get($st,'trend') }}
                    </span>
                @endif
            </div>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ data_get($st,4) }}</p>
            <p class="text-[26px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ data_get($st,3) }}</p>
        </div>
    @endforeach
</div>

{{-- ══ MAIN GRID : scan panel + live feed ══ --}}
<div class="grid lg:grid-cols-3 gap-5">

    {{-- ── LEFT : SCAN PANEL (2 cols) ── --}}
    <div class="lg:col-span-2 siarc-card siarc-shadow overflow-hidden">
        <div class="siarc-kente"></div>
        <div class="p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="siarc-kicker text-siarc-green">Point de contrôle</p>
                    <h2 class="font-display text-[19px] font-bold text-[#1A1712] mt-1.5">Scannez un badge</h2>
                </div>
                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#8A857A] border border-[#EFEDE6] rounded-lg px-3 py-1.5">
                    <i data-lucide="door-open" class="w-3.5 h-3.5"></i> Entrée Principale
                </span>
            </div>

            {{-- dashed scan frame --}}
            <div class="relative rounded-2xl border-2 border-dashed border-[#CBD8CF] bg-[#F7FAF8] px-6 py-10 text-center">
                <span class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background:#E2F3E8">
                    <i data-lucide="qr-code" class="w-8 h-8" style="color:#157A43"></i>
                </span>
                <p class="text-[14px] font-semibold text-[#1A1712]">Présentez le badge devant la caméra</p>
                <p class="text-[12px] text-[#8A857A] mt-1">La vérification QR s'effectue automatiquement à l'approche du code.</p>
                <span class="inline-flex items-center gap-1.5 mt-4 text-[11px] font-semibold text-siarc-green">
                    <span class="w-2 h-2 rounded-full bg-siarc-green siarc-pulse"></span> Lecteur prêt
                </span>
            </div>

            {{-- manual code entry --}}
            <div class="mt-5">
                <label class="block text-[12px] font-semibold text-[#3B382F] mb-1.5">Ou saisissez le code du badge</label>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <i data-lucide="scan-line" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#B0AB9F]"></i>
                        <input type="text" placeholder="Ex. CM-24876" aria-label="Code du badge"
                            class="w-full pl-9 pr-3 py-3 rounded-xl border border-[#E4E1D8] text-[13px] text-[#1A1712] placeholder-[#B0AB9F] focus:outline-none focus:border-siarc-green focus:ring-2 focus:ring-[#157A43]/15 bg-white">
                    </div>
                    <button type="button" class="siarc-btn siarc-btn-green px-6 py-3 text-[13px] justify-center">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Valider
                    </button>
                </div>
                <p class="text-[11px] text-[#B0AB9F] mt-2 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> Chaque validation est enregistrée dans le journal d'activité.
                </p>
            </div>

            {{-- quick links (from $sLinks) --}}
            @if($links->isNotEmpty())
            <div class="mt-6 pt-5 border-t border-[#F1F0EB]">
                <p class="text-[11px] font-bold uppercase tracking-wide text-[#8A857A] mb-3">Accès rapides</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($links as $lk)
                        @php
                            $lkLabel = data_get($lk,'label', data_get($lk,0, 'Ouvrir'));
                            $lkUrl   = data_get($lk,'url',   data_get($lk,1, '#'));
                            $lkIcon  = data_get($lk,'icon',  str_contains(strtolower($lkLabel),'mobile') ? 'scan-line' : 'qr-code');
                        @endphp
                        <a href="{{ $lkUrl }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] p-3 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#E2F3E8">
                                <i data-lucide="{{ $lkIcon }}" class="w-[18px] h-[18px]" style="color:#157A43"></i>
                            </span>
                            <span class="text-[12.5px] font-semibold text-[#3B382F] leading-tight">{{ $lkLabel }}</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-auto text-[#B0AB9F]"></i>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── RIGHT : LIVE FEED (Derniers passages) ── --}}
    <div class="siarc-card siarc-shadow p-5 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-[#1A1712]">Derniers passages</h3>
            <span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold tracking-wide text-siarc-red bg-[#FDE8E8] rounded-full px-2.5 py-1">
                <span class="w-1.5 h-1.5 rounded-full bg-siarc-red siarc-pulse"></span> EN DIRECT
            </span>
        </div>

        @if($tRows->isNotEmpty())
            <ul class="space-y-2.5 flex-1">
                @foreach($tRows as $row)
                    @php
                        $type  = data_get($row,'type', data_get($row, $tCols[0] ?? 'Type', data_get($row,0)));
                        $id    = data_get($row,'id',   data_get($row, $tCols[1] ?? 'ID',   data_get($row,1)));
                        $porte = data_get($row,'porte',data_get($row, $tCols[2] ?? 'Porte',data_get($row,2)));
                        $heure = data_get($row,'heure',data_get($row, $tCols[3] ?? 'Heure',data_get($row,3)));
                        $coerce = fn($c) => is_array($c) ? (string)($c['badge'] ?? '') : (string)$c;
                        $type  = $coerce($type);
                        $id    = $coerce($id);
                        $porte = $coerce($porte);
                        $heure = $coerce($heure);
                        [$bg,$fg] = $tone($type);
                    @endphp
                    <li class="flex items-center gap-3 rounded-xl border border-[#F1F0EB] p-3 hover:border-[#E4E1D8] transition-colors">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $bg }}">
                            <i data-lucide="user" class="w-[18px] h-[18px]" style="color:{{ $fg }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight truncate">{{ $id ?: '—' }}</p>
                                <span class="text-[10px] font-semibold rounded-full px-2 py-0.5 shrink-0" style="background:{{ $bg }};color:{{ $fg }}">{{ $type ?: '—' }}</span>
                            </div>
                            <p class="text-[11px] text-[#8A857A] truncate flex items-center gap-1 mt-0.5">
                                <i data-lucide="map-pin" class="w-3 h-3"></i>{{ $porte ?: 'Porte —' }}
                            </p>
                        </div>
                        <span class="text-[11px] font-medium text-[#B0AB9F] whitespace-nowrap">{{ $heure ?: '—' }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center py-12">
                <span class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3" style="background:#F1F1EF">
                    <i data-lucide="circle-dashed" class="w-7 h-7 text-[#B0AB9F]"></i>
                </span>
                <p class="text-[13px] font-semibold text-[#55524A]">Aucun passage pour le moment</p>
                <p class="text-[12px] text-[#8A857A] mt-1">Les entrées apparaîtront ici dès le premier scan.</p>
            </div>
        @endif

        <a href="{{ $h('siarc.admin.activity') }}" class="mt-4 pt-4 border-t border-[#F1F0EB] text-[12px] font-semibold text-siarc-green inline-flex items-center gap-1 hover:gap-1.5 transition-all">
            Voir tout le journal <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>
