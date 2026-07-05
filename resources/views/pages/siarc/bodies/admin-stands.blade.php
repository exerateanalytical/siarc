@php
    use Illuminate\Support\Facades\Route as R;
    $lang  = $lang ?? 'fr'; $isFr = $isFr ?? ($lang === 'fr');
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang' => $lang], $params)) : null;

    // Navigational fallback for the manual-allocation / detail actions.
    $planLink = $h('siarc.admin.pavilions') ?? $h('siarc.admin.dashboard') ?? null;

    // ── Hall-map zones (transcribed verbatim from the approved design) ──────────
    // tone => [tileBg, ink] (matches legend chips)
    $tone = [
        'green' => ['#D8EEDF', '#157A43'],   // Attribué
        'white' => ['#FFFFFF', '#D6D3CB'],   // Disponible
        'gold'  => ['#FCE9BE', '#C97A16'],   // En attente
        'red'   => ['#FAD4D6', '#C0010C'],   // Maintenance
        'blue'  => ['#DCE8FB', '#3565DE'],   // Réservé
        'grey'  => ['#EDEBE6', '#9A958A'],   // Non attribué
    ];
    // Each hall zone: label, header colour, orientation, and its stand chips.
    $nord = ['N-01'=>'white','N-02'=>'green','N-03'=>'white','N-04'=>'white','N-05'=>'green','N-06'=>'gold','N-07'=>'white','N-08'=>'green','N-09'=>'white','N-10'=>'white'];
    $ouest = ['O-01'=>'blue','O-02'=>'blue','O-03'=>'blue','O-04'=>'green','O-05'=>'white','O-06'=>'white','O-07'=>'green','O-07b'=>'blue','O-09'=>'blue','O-10'=>'blue'];
    $ouestLabels = ['O-01','O-02','O-03','O-04','O-05','O-06','O-07','O-07','O-09','O-10'];
    $centre = ['C-01'=>'white','C-02'=>'white','C-03'=>'green','C-04'=>'white','C-05'=>'white','C-06'=>'white','C-07'=>'white','C-08'=>'white','C-09'=>'green','C-10'=>'white','C-11'=>'white','C-12'=>'white','C-13'=>'white','C-14'=>'white','C-15'=>'green','C-16'=>'blue','C-17'=>'white','C-18'=>'white','C-19'=>'white','C-20'=>'white','C-21'=>'white','C-22'=>'blue','C-23'=>'gold','C-24'=>'white'];
    $est = ['E-01'=>'blue','E-02'=>'blue','E-03'=>'green','E-04'=>'blue','E-06'=>'blue','E-07'=>'gold','E-07b'=>'blue','E-09'=>'blue','E-10'=>'blue'];
    $estLabels = ['E-01','E-02','E-03','E-04','E-06','E-07','E-07','E-09','E-10'];
    $sud = ['S-01'=>'red','S-02'=>'green','S-03'=>'red','S-04'=>'red','S-05'=>'red','S-06'=>'green','S-07'=>'red','S-08'=>'red','S-09'=>'red','S-10'=>'red'];

    // ── Legend (verbatim order) ────────────────────────────────────────────────
    $legend = [
        ['green','Attribué'], ['white','Disponible'], ['gold','En attente'],
        ['red','Maintenance'], ['blue','Réservé'], ['grey','Non attribué'],
    ];

    // ── Donut segments (verbatim figures) ──────────────────────────────────────
    $donut = [
        ['#157A43', 'Attribués',   '378', '73.8%'],
        ['#E2DFD8', 'Disponibles', '134', '26.2%'],
        ['#E6B201', 'En attente',  '28',  '5.5%'],
        ['#EF6C6C', 'Maintenance', '12',  '2.3%'],
    ];
    // Arc lengths on the donut (circ for r=52). Proportional to the four figures.
    $circ = 2 * 3.14159 * 52;
    $donutPct = [73.8, 26.2, 5.5, 2.3];   // shown values; normalise for the ring
    $sum = array_sum($donutPct);

    // ── Répartition par pavillon (verbatim) ────────────────────────────────────
    $pavRows = [
        ['Pavillon Centre', '160 / 216', 74.1],
        ['Pavillon Nord',   '68 / 80',   85.0],
        ['Pavillon Sud',    '52 / 80',   65.0],
        ['Pavillon Ouest',  '70 / 100',  70.0],
        ['Pavillon Est',    '68 / 100',  68.0],
    ];

    // ── Activités récentes (verbatim) ──────────────────────────────────────────
    $activity = [
        ['user-round-check', 'green', 'Stand C-09 attribué à Art Bois Précieux SARL', 'Par Marie Claire', 'Il y a 10 min'],
        ['calendar-check',   'green', 'Paiement confirmé - Stand O-04', "Tissages du Soleil\n450,000 FCFA", 'Il y a 35 min'],
        ['clock',            'blue',  'Stand E-07 en attente de paiement', "Bijoux d'Afrique", 'Il y a 1 h'],
        ['file-text',        'purple','Stand S-03 libéré', 'Par Admin', 'Il y a 2 h'],
    ];
@endphp

{{-- ══ KPI ROW ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5 siarc-in">
    {{-- Stands totaux --}}
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#E2F3E8"><i data-lucide="grid-3x3" class="w-[22px] h-[22px]" style="color:#157A43"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">Stands totaux</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">512</p>
            </div>
        </div>
        <p class="text-[11px] font-semibold text-[#8A857A] mt-2">100%</p>
    </div>
    {{-- Stands attribués --}}
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#E8EFFB"><i data-lucide="store" class="w-[22px] h-[22px]" style="color:#3565DE"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">Stands attribués</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">378</p>
            </div>
        </div>
        <p class="text-[11px] font-semibold text-[#8A857A] mt-2">73.8%</p>
    </div>
    {{-- Stands disponibles --}}
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#F0EAFB"><i data-lucide="layout-grid" class="w-[22px] h-[22px]" style="color:#7C4FE0"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">Stands disponibles</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">134</p>
            </div>
        </div>
        <p class="text-[11px] font-semibold text-[#8A857A] mt-2">26.2%</p>
    </div>
    {{-- En attente d'attribution --}}
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#FDF3E0"><i data-lucide="clock" class="w-[22px] h-[22px]" style="color:#C97A16"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">En attente d'attribution</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">28</p>
            </div>
        </div>
        <p class="text-[11px] font-semibold text-[#8A857A] mt-2">5.5%</p>
    </div>
    {{-- Taux d'occupation --}}
    <div class="siarc-card siarc-shadow p-4">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#E2F3E8"><i data-lucide="chart-scatter" class="w-[22px] h-[22px]" style="color:#157A43"></i></span>
            <div class="min-w-0">
                <p class="text-[11.5px] text-[#8A857A] font-medium leading-tight">Taux d'occupation</p>
                <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none mt-0.5">73.8%</p>
            </div>
        </div>
        <p class="text-[11px] font-semibold text-siarc-green mt-2 inline-flex items-center gap-1"><i data-lucide="arrow-up" class="w-3 h-3"></i>12.6% vs dernier mois</p>
    </div>
</div>

{{-- ══ FILTER / ACTION BAR ══ --}}
<div class="siarc-card siarc-shadow p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        {{-- Pavillon --}}
        <div class="min-w-[160px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">Pavillon</label>
            <div class="relative">
                <select class="w-full appearance-none px-3 pr-8 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                    <option>Tous les pavillons</option>
                    <option>Pavillon Centre</option><option>Pavillon Nord</option><option>Pavillon Sud</option><option>Pavillon Ouest</option><option>Pavillon Est</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </div>
        {{-- Catégorie --}}
        <div class="min-w-[130px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">Catégorie</label>
            <div class="relative">
                <select class="w-full appearance-none px-3 pr-8 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                    <option>Tous</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </div>
        {{-- Statut d'attribution --}}
        <div class="min-w-[140px]">
            <label class="block text-[10.5px] font-semibold text-[#8A857A] mb-1">Statut d'attribution</label>
            <div class="relative">
                <select class="w-full appearance-none px-3 pr-8 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                    <option>Tous</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </div>
        {{-- Statut de paiement --}}
        <div class="min-w-[130px]">
            <label class="flex items-center gap-1 text-[10.5px] font-semibold text-[#8A857A] mb-1">Statut de paiement<i data-lucide="info" class="w-3 h-3 text-[#B0AB9F]"></i></label>
            <div class="relative">
                <select class="w-full appearance-none px-3 pr-8 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] bg-[#FBFAF6] focus:outline-none focus:border-[#D7E4DB]">
                    <option>Tous</option>
                </select>
                <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            </div>
        </div>
        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <label class="block text-[10.5px] font-semibold text-transparent mb-1">.</label>
            <input type="text" placeholder="Rechercher un stand ou exposant…" class="w-full pl-3.5 pr-9 py-2 rounded-xl border border-[#ECEAE3] text-[12.5px] text-[#3B382F] placeholder:text-[#B0AB9F] focus:outline-none focus:border-[#D7E4DB] bg-[#FBFAF6]">
            <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute right-3 top-[30px]"></i>
        </div>
        {{-- Filtres --}}
        <button type="button" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2 text-[12.5px] bg-white"><i data-lucide="filter" class="w-4 h-4 text-[#6F6B60]"></i>Filtres</button>
        {{-- Réinitialiser --}}
        <button type="button" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2 text-[12.5px] bg-white"><i data-lucide="rotate-ccw" class="w-4 h-4 text-[#6F6B60]"></i>Réinitialiser</button>
        {{-- Attribution manuelle --}}
        <a @if($planLink)href="{{ $planLink }}"@endif class="siarc-btn siarc-btn-green px-4 py-2 text-[12.5px]"><i data-lucide="plus" class="w-4 h-4"></i>Attribution manuelle</a>
    </div>
</div>

{{-- ══ MAIN GRID: HALL MAP  +  RIGHT COLUMN ══ --}}
<div class="grid xl:grid-cols-3 gap-5 mb-5">

    {{-- ── LEFT: hall map (2 cols) ──────────────────────────────────────────── --}}
    <div class="xl:col-span-2 siarc-card siarc-shadow p-5">
        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mb-4">
            @foreach($legend as [$lt, $label])
            @php $lc = $tone[$lt]; @endphp
            <span class="inline-flex items-center gap-1.5 text-[11.5px] font-medium text-[#3B382F]">
                <span class="w-3.5 h-3.5 rounded-[4px]" style="background:{{ $lc[0] }};border:1.5px solid {{ $lc[1] }}"></span>{{ $label }}
            </span>
            @endforeach
        </div>

        {{-- Floorplan --}}
        <div class="rounded-2xl border border-[#ECEAE3] bg-[#FBFAF6] p-4 overflow-x-auto relative">
            {{-- Zoom / pan controls (left rail) --}}
            <div class="absolute left-6 top-6 flex flex-col gap-2 z-10">
                <button type="button" title="Déplacer" class="w-9 h-9 rounded-lg bg-siarc-green text-white flex items-center justify-center siarc-shadow"><i data-lucide="hand" class="w-4 h-4"></i></button>
                <div class="rounded-lg border border-[#ECEAE3] bg-white flex flex-col overflow-hidden siarc-shadow">
                    <button type="button" title="Zoom avant" class="w-9 h-9 flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6] border-b border-[#ECEAE3]"><i data-lucide="plus" class="w-4 h-4"></i></button>
                    <button type="button" title="Zoom arrière" class="w-9 h-9 flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6]"><i data-lucide="minus" class="w-4 h-4"></i></button>
                </div>
                <button type="button" title="Ajuster" class="w-9 h-9 rounded-lg border border-[#ECEAE3] bg-white flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6] siarc-shadow"><i data-lucide="maximize" class="w-4 h-4"></i></button>
                <button type="button" title="Réinitialiser" class="w-9 h-9 rounded-lg border border-[#ECEAE3] bg-white flex items-center justify-center text-[#6F6B60] hover:bg-[#FBFAF6] siarc-shadow"><i data-lucide="rotate-ccw" class="w-4 h-4"></i></button>
            </div>

            <div class="min-w-[640px] relative rounded-xl bg-white border border-[#E8E6DF] p-3">
                {{-- ENTRÉE PRINCIPALE --}}
                <div class="flex flex-col items-center text-[9.5px] font-bold tracking-wide text-[#8A857A] mb-2">
                    <span>ENTRÉE PRINCIPALE</span><i data-lucide="arrow-down" class="w-3.5 h-3.5 text-siarc-green"></i>
                </div>

                {{-- top row : Nord band + conf/atelier space on the right --}}
                <div class="flex gap-3">
                    <div class="flex-1">
                        <div class="text-center mb-1.5"><span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-white px-3 py-1 rounded-md bg-siarc-green">PAVILLON NORD</span></div>
                        <div class="grid grid-cols-10 gap-1">
                            @foreach($nord as $code => $t)
                            @php $c = $tone[$t]; @endphp
                            <div class="h-8 rounded flex items-center justify-center text-[9px] font-semibold border" style="background:{{ $c[0] }};color:{{ $c[1] }};border-color:{{ $c[1] }}66">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="w-[120px] shrink-0 rounded-lg bg-[#F1F0EC] border border-[#E3E0D8] flex flex-col items-center justify-center text-center p-2">
                        <i data-lucide="presentation" class="w-6 h-6 text-[#9A958A] mb-1"></i>
                        <p class="text-[9px] font-bold text-[#6F6B60] leading-tight">ESPACE<br>CONFÉRENCES<br>&amp; ATELIERS</p>
                    </div>
                </div>

                {{-- middle band : Ouest | Centre | Est --}}
                <div class="flex gap-3 mt-3 items-stretch">
                    {{-- Ouest (vertical rail) --}}
                    <div class="flex gap-1.5">
                        <div class="w-6 rounded-md bg-[#3565DE] flex items-center justify-center">
                            <span class="text-white text-[10px] font-bold tracking-wide" style="writing-mode:vertical-rl;transform:rotate(180deg)">PAVILLON OUEST</span>
                        </div>
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($ouestLabels as $i => $code)
                            @php $t = array_values($ouest)[$i]; $c = $tone[$t]; @endphp
                            <div class="w-14 h-6 rounded flex items-center justify-center text-[9px] font-semibold border" style="background:{{ $c[0] }};color:{{ $c[1] }};border-color:{{ $c[1] }}66">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Centre --}}
                    <div class="flex-1 rounded-lg bg-[#F7F6F1] border border-[#E8E6DF] p-3">
                        <div class="text-center mb-2"><span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-white px-3 py-1 rounded-md bg-siarc-ochre">PAVILLON CENTRE</span></div>
                        <div class="grid grid-cols-6 gap-1.5">
                            @foreach($centre as $code => $t)
                            @php $c = $tone[$t]; @endphp
                            <div class="h-8 rounded flex items-center justify-center text-[9.5px] font-semibold border" style="background:{{ $c[0] }};color:{{ $c[1] }};border-color:{{ $c[1] }}66">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Est (vertical rail) --}}
                    <div class="flex gap-1.5">
                        <div class="grid grid-cols-1 gap-1">
                            @foreach($estLabels as $i => $code)
                            @php $t = array_values($est)[$i]; $c = $tone[$t]; @endphp
                            <div class="w-14 h-6 rounded flex items-center justify-center text-[9px] font-semibold border" style="background:{{ $c[0] }};color:{{ $c[1] }};border-color:{{ $c[1] }}66">{{ $code }}</div>
                            @endforeach
                        </div>
                        <div class="w-6 rounded-md bg-[#7C4FE0] flex items-center justify-center">
                            <span class="text-white text-[10px] font-bold tracking-wide" style="writing-mode:vertical-rl">PAVILLON EST</span>
                        </div>
                    </div>
                </div>

                {{-- bottom band : Innovation | Sud | Restauration --}}
                <div class="flex gap-3 mt-3 items-stretch">
                    <div class="w-[120px] shrink-0 rounded-lg bg-[#FDF6E3] border border-[#EFE3BE] flex flex-col items-center justify-center text-center p-2">
                        <i data-lucide="lightbulb" class="w-6 h-6 text-siarc-ochre mb-1"></i>
                        <p class="text-[9px] font-bold text-[#B07A16] leading-tight">ESPACE<br>INNOVATION</p>
                    </div>
                    <div class="flex-1">
                        <div class="text-center mb-1.5"><span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-white px-3 py-1 rounded-md bg-siarc-red">PAVILLON SUD</span></div>
                        <div class="grid grid-cols-10 gap-1">
                            @foreach($sud as $code => $t)
                            @php $c = $tone[$t]; @endphp
                            <div class="h-8 rounded flex items-center justify-center text-[9px] font-semibold border" style="background:{{ $c[0] }};color:{{ $c[1] }};border-color:{{ $c[1] }}66">{{ $code }}</div>
                            @endforeach
                        </div>
                    </div>
                    <div class="w-[120px] shrink-0 rounded-lg bg-[#FDF6E3] border border-[#EFE3BE] flex flex-col items-center justify-center text-center p-2">
                        <i data-lucide="utensils" class="w-6 h-6 text-siarc-ochre mb-1"></i>
                        <p class="text-[9px] font-bold text-[#B07A16] leading-tight">ESPACE<br>RESTAURATION</p>
                    </div>
                </div>

                {{-- ENTRÉE SECONDAIRE --}}
                <div class="flex flex-col items-center text-[9.5px] font-bold tracking-wide text-[#8A857A] mt-2">
                    <i data-lucide="arrow-up" class="w-3.5 h-3.5 text-siarc-green"></i><span>ENTRÉE SECONDAIRE</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── RIGHT column ─────────────────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Donut: Statut des attributions --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Statut des attributions</h3>
            <div class="flex items-center gap-4">
                <div class="relative w-[128px] h-[128px] shrink-0">
                    <svg viewBox="0 0 120 120" class="w-[128px] h-[128px] -rotate-90">
                        @php $off = 0; @endphp
                        @foreach($donut as $i => [$dc, $dlbl, $dval, $dp])
                            @php $len = $circ * $donutPct[$i] / $sum; @endphp
                            <circle cx="60" cy="60" r="52" fill="none" stroke="{{ $dc }}" stroke-width="15"
                                stroke-dasharray="{{ round($len, 1) }} {{ round($circ - $len, 1) }}"
                                stroke-dashoffset="{{ -round($off, 1) }}"/>
                            @php $off += $len; @endphp
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-display text-[24px] font-extrabold text-[#1A1712] leading-none">512</span>
                        <span class="text-[10px] text-[#8A857A]">Stands</span>
                    </div>
                </div>
                <ul class="flex-1 space-y-2.5">
                    @foreach($donut as [$dc, $dlbl, $dval, $dp])
                    <li class="flex items-center gap-2 text-[11.5px]">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $dc }}"></span>
                        <span class="text-[#3B382F] font-medium">{{ $dlbl }}</span>
                        <span class="ml-auto text-[#8A857A]">{{ $dval }} ({{ $dp }})</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Répartition par pavillon --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Répartition par pavillon</h3>
            <ul class="space-y-3">
                @foreach($pavRows as [$pName, $pFrac, $pPct])
                <li class="flex items-center gap-3 text-[12px]">
                    <span class="text-[#3B382F] font-medium w-[100px] shrink-0">{{ $pName }}</span>
                    <span class="text-[#8A857A] w-[54px] shrink-0 whitespace-nowrap">{{ $pFrac }}</span>
                    <div class="h-2 flex-1 rounded-full bg-[#F1F1EF] overflow-hidden"><div class="h-full rounded-full bg-siarc-green" style="width:{{ $pPct }}%"></div></div>
                    <span class="text-[11px] font-semibold text-[#8A857A] w-[42px] text-right shrink-0">{{ number_format($pPct, 1) }}%</span>
                </li>
                @endforeach
            </ul>
            <div class="flex items-center gap-3 mt-4 pt-3 border-t border-[#ECEAE3] text-[12px]">
                <span class="font-bold text-[#1A1712] w-[100px]">Total</span>
                <span class="text-[#8A857A] w-[54px]">378 / 512</span>
                <span class="flex-1"></span>
                <span class="font-bold text-[#1A1712] w-[42px] text-right">73.8%</span>
            </div>
        </div>

        {{-- Activités récentes --}}
        <div class="siarc-card siarc-shadow p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[14px] font-bold text-[#1A1712]">Activités récentes</h3>
                <a @if($planLink)href="{{ $planLink }}"@endif class="text-[11.5px] font-semibold text-siarc-green hover:underline">Voir tout</a>
            </div>
            <ul class="space-y-4">
                @php $actTone = ['green'=>['#E2F3E8','#157A43'],'blue'=>['#E8EFFB','#3565DE'],'purple'=>['#F0EAFB','#7C4FE0']]; @endphp
                @foreach($activity as [$aIcon, $aTone, $aTitle, $aSub, $aTime])
                @php $at = $actTone[$aTone]; @endphp
                <li class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:{{ $at[0] }}"><i data-lucide="{{ $aIcon }}" class="w-4 h-4" style="color:{{ $at[1] }}"></i></span>
                    <div class="min-w-0 flex-1">
                        <p class="text-[12px] font-semibold text-[#1F1D18] leading-snug">{{ $aTitle }}</p>
                        <p class="text-[11px] text-[#8A857A] mt-0.5 whitespace-pre-line leading-snug">{{ $aSub }}</p>
                    </div>
                    <span class="text-[10.5px] text-[#A6A196] whitespace-nowrap mt-0.5">{{ $aTime }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

{{-- ══ STAND SÉLECTIONNÉ (detail panel) ══ --}}
<div class="siarc-card siarc-shadow p-5 mb-2">
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Column 1 : Stand sélectionné --}}
        <div>
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">Stand sélectionné</h3>
            <div class="flex gap-3">
                <span class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 font-display text-[15px] font-extrabold border" style="background:#D8EEDF;color:#157A43;border-color:#157A4366">C-09</span>
                <div class="text-[12px] leading-relaxed">
                    <p class="font-semibold text-[#161513]">Pavillon Centre</p>
                    <p class="text-[#6F6B60]">Catégorie : Bois &amp; Sculpture</p>
                    <p class="text-[#6F6B60]">Surface : 9 m² (3m x 3m)</p>
                    <p class="text-[#6F6B60]">Statut :
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full align-middle" style="background:#D8EEDF;color:#157A43">
                            <span class="w-1.5 h-1.5 rounded-full" style="background:#157A43"></span>Attribué
                        </span>
                    </p>
                    <p class="text-[#6F6B60]">Type : Standard</p>
                </div>
            </div>
        </div>

        {{-- Column 2 : Exposant attribué --}}
        <div>
            <p class="text-[13px] font-bold text-[#1A1712] mb-3">Exposant attribué</p>
            <div class="flex gap-3">
                <img src="{{ asset('images/siarc/stand-alloc-exhibitor-1.png') }}" alt="Art Bois Précieux SARL" class="w-14 h-14 rounded-xl object-cover shrink-0 bg-[#F5EFE2] border border-[#ECE6D8]">
                <div class="text-[12px] leading-relaxed min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-[13px] font-bold text-[#161513]">Art Bois Précieux SARL</p>
                        <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full" style="background:#D8EEDF;color:#157A43">Confirmé</span>
                    </div>
                    <p class="text-[#3B382F] mt-0.5 flex items-center gap-1.5">Paul Tchameni
                        <span class="inline-flex items-center gap-1 text-[#6F6B60]">
                            <span class="inline-block w-3.5 h-2.5 rounded-[1px] overflow-hidden align-middle" style="background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)"></span>Cameroun
                        </span>
                    </p>
                    <p class="text-[#6F6B60]">+237 6 98 76 54 32</p>
                    <p class="text-[#6F6B60]">contact@artboisprecieux.cm</p>
                </div>
            </div>
        </div>

        {{-- Column 3 : Informations financières --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <p class="text-[13px] font-bold text-[#1A1712]">Informations financières</p>
                <button type="button" class="w-7 h-7 rounded-lg flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]"><i data-lucide="ellipsis-vertical" class="w-4 h-4"></i></button>
            </div>
            <div class="space-y-2.5 text-[12px]">
                <div class="flex items-center justify-between">
                    <span class="text-[#8A857A]">Montant total</span>
                    <span class="font-semibold text-[#161513]">450,000 FCFA</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[#8A857A]">Montant payé</span>
                    <span class="font-semibold text-siarc-green">450,000 FCFA</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[#8A857A]">Statut de paiement</span>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" style="background:#D8EEDF;color:#157A43">Payé intégralement</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Action row --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-5 pt-5 border-t border-[#ECEAE3]">
        <a @if($planLink)href="{{ $planLink }}"@endif class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2.5 text-[12.5px] justify-center bg-white"><i data-lucide="eye" class="w-4 h-4 text-[#6F6B60]"></i>Voir le détail du stand</a>
        <a @if($planLink)href="{{ $planLink }}"@endif class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2.5 text-[12.5px] justify-center bg-white"><i data-lucide="users-round" class="w-4 h-4 text-[#6F6B60]"></i>Changer d'exposant</a>
        <a @if($planLink)href="{{ $planLink }}"@endif class="siarc-btn border border-[#ECEAE3] text-[#3B382F] px-3.5 py-2.5 text-[12.5px] justify-center bg-white"><i data-lucide="rotate-ccw" class="w-4 h-4 text-[#6F6B60]"></i>Historique</a>
        <button type="button" class="siarc-btn border border-[#F2C7C9] text-siarc-red px-3.5 py-2.5 text-[12.5px] justify-center bg-white"><i data-lucide="trash-2" class="w-4 h-4"></i>Libérer le stand</button>
    </div>
</div>
