@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── KPI row — approved design figures (verbatim) ────────────────────────────
    $kpis = [
        ['users-round','#157A43','#E2F3E8','Entreprises inscrites','1 248','18.6%',true],
        ['handshake','#3565DE','#E8EFFB','Rendez-vous confirmés','156','24.3%',true],
        ['calendar-clock','#7C4FE0','#F0EAFB','Demandes en attente','28','4.2%',false],
        ['activity','#C97A16','#FDF3E0','Vues de profil','1 893','31.7%',true],
        ['circle-dot','#157A43','#E2F3E8','Taux de correspondance','87%','Excellent',null],
    ];

    // Fallbacks so the page is robust if a var is absent (never fabricated rows)
    $sStats  = $sStats  ?? [];
    $sTables = $sTables ?? [];
    $sLinks  = $sLinks  ?? [];
    $meetTable = $sTables[0] ?? null;

    // Status badge tone map
    $tone = function($status){
        $s = mb_strtolower(trim((string)$status));
        if(str_contains($s,'confirm')||str_contains($s,'allou')||str_contains($s,'complé')||str_contains($s,'termin')) return ['#E2F3E8','#157A43'];
        if(str_contains($s,'attente')||str_contains($s,'demand')||str_contains($s,'pending')||str_contains($s,'requested')) return ['#FBF0D6','#9A6B00'];
        if(str_contains($s,'annul')||str_contains($s,'refus')||str_contains($s,'declin')||str_contains($s,'cancel')) return ['#FBE3E3','#B0121B'];
        if(str_contains($s,'réserv')||str_contains($s,'reserv')||str_contains($s,'registr')) return ['#E8EFFB','#2B4F9E'];
        return ['#EFEDE6','#6B6558'];
    };
@endphp

{{-- ══ KPI ROW ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($kpis as [$icon,$color,$tile,$label,$val,$chg,$isUp])
    <div class="siarc-card siarc-shadow p-4 siarc-in">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
        <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
        <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}</p>
        @if($isUp === null)
            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold text-siarc-green">{{ $chg }}</span>
        @else
            <span class="inline-flex items-center gap-1 mt-2 text-[11px] font-semibold {{ $isUp ? 'text-siarc-green' : 'text-siarc-red' }}">
                <i data-lucide="{{ $isUp ? 'arrow-up' : 'arrow-down' }}" class="w-3 h-3"></i>{{ $chg }}<span class="text-[#B0AB9F] font-normal">vs dernier salon</span>
            </span>
        @endif
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- ══ MAIN COLUMN ══ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- ── Matchmaking concept: Acheteurs ↔ Exposants ─────────────────────── --}}
        <div class="siarc-card siarc-shadow overflow-hidden siarc-in">
            <div class="siarc-kente"></div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="siarc-kicker text-siarc-ochre">Mise en relation</p>
                        <h2 class="font-display text-[20px] font-extrabold text-[#161513] mt-1.5">Acheteurs <span class="text-siarc-ochre">↔</span> Exposants</h2>
                    </div>
                    @foreach($sLinks as $lk)
                        @if(mb_strtolower($lk['label'] ?? '') === 'matchmaking' || ($loop->first))
                        <a href="{{ $lk['href'] ?? '#' }}" class="siarc-btn siarc-btn-green px-4 py-2 text-[12.5px]"><i data-lucide="handshake" class="w-4 h-4"></i>{{ $lk['label'] ?? 'Matchmaking' }}</a>
                        @break
                        @endif
                    @endforeach
                </div>

                <div class="grid grid-cols-2 gap-5 items-stretch relative">
                    {{-- Acheteurs --}}
                    <div class="rounded-2xl border border-[#E8EFFB] bg-[#F6F9FE] p-5">
                        <div class="flex items-center gap-2.5 mb-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#E8EFFB]"><i data-lucide="users" class="w-[18px] h-[18px]" style="color:#3565DE"></i></span>
                            <div>
                                <p class="text-[12.5px] font-bold text-[#1A1712]">Acheteurs</p>
                                <p class="text-[10.5px] text-[#8A857A]">Distributeurs & importateurs</p>
                            </div>
                        </div>
                        <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none">640</p>
                        <p class="text-[11px] text-[#8A857A] mt-1">profils acheteurs actifs</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach(['Mobilier','Textile','Décoration','Agro'] as $t)
                            <span class="text-[10.5px] font-medium px-2 py-0.5 rounded-full bg-white border border-[#E8EFFB] text-[#2B4F9E]">{{ $t }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- connector --}}
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10 w-11 h-11 rounded-full bg-white siarc-shadow flex items-center justify-center border border-[#ECEAE3]">
                        <i data-lucide="handshake" class="w-5 h-5 text-siarc-ochre"></i>
                    </div>

                    {{-- Exposants --}}
                    <div class="rounded-2xl border border-[#E2F3E8] bg-[#F5FBF7] p-5">
                        <div class="flex items-center gap-2.5 mb-3">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#E2F3E8]"><i data-lucide="store" class="w-[18px] h-[18px]" style="color:#157A43"></i></span>
                            <div>
                                <p class="text-[12.5px] font-bold text-[#1A1712]">Exposants</p>
                                <p class="text-[10.5px] text-[#8A857A]">Artisans & coopératives</p>
                            </div>
                        </div>
                        <p class="font-display text-[26px] font-extrabold text-[#161513] leading-none">842</p>
                        <p class="text-[11px] text-[#8A857A] mt-1">profils exposants actifs</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach(['Bois','Tissus','Bijouterie','Poterie'] as $t)
                            <span class="text-[10.5px] font-medium px-2 py-0.5 rounded-full bg-white border border-[#E2F3E8] text-siarc-green">{{ $t }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between rounded-xl bg-[#FBFAF6] border border-[#EFEDE6] px-4 py-3">
                    <div class="flex items-center gap-2 text-[12px] text-[#3B382F]">
                        <i data-lucide="circle-dot" class="w-4 h-4 text-siarc-green"></i>
                        <span><span class="font-bold text-[#161513]">87%</span> de taux de correspondance moyen</span>
                    </div>
                    <span class="text-[11.5px] font-semibold text-siarc-green">1 248 mises en relation</span>
                </div>
            </div>
        </div>

        {{-- ── Meetings schedule table (REAL data) ────────────────────────────── --}}
        <div class="siarc-card siarc-shadow overflow-hidden siarc-in">
            <div class="flex items-center justify-between p-5 pb-4 border-b border-[#EFEDE6]">
                <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $meetTable['title'] ?? "Rendez-vous d'affaires" }}</h3>
                {{-- filter chips (visual) --}}
                <div class="flex items-center gap-1.5">
                    <button class="text-[11.5px] font-semibold px-3 py-1.5 rounded-lg bg-siarc-green text-white">Tous</button>
                    <button class="text-[11.5px] font-semibold px-3 py-1.5 rounded-lg border border-[#EFEDE6] text-[#55524A] hover:bg-[#FBFAF6]">Confirmés</button>
                    <button class="text-[11.5px] font-semibold px-3 py-1.5 rounded-lg border border-[#EFEDE6] text-[#55524A] hover:bg-[#FBFAF6]">Demandés</button>
                </div>
            </div>

            @php $cols = $meetTable['cols'] ?? ['Demandeur','Exposant hôte','Horaire','Statut']; $rows = $meetTable['rows'] ?? []; @endphp

            @if(count($rows))
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-wide text-[#8A857A] bg-[#FBFAF6]">
                            @foreach($cols as $c)
                            <th class="px-5 py-3 font-semibold {{ $loop->last ? 'text-right' : '' }}">{{ $c }}</th>
                            @endforeach
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1EFE9]">
                        @foreach($rows as $row)
                        @php $cells = $row['cells'] ?? []; $href = $row['href'] ?? null; $last = count($cells)-1; @endphp
                        <tr class="hover:bg-[#FBFAF6] transition-colors">
                            @foreach($cells as $i => $cell)
                                @php $cellTxt = is_array($cell) ? ($cell['badge'] ?? '') : (string)$cell; @endphp
                                @if($i === $last && is_array($cell))
                                    @php [$bg,$fg] = $tone($cellTxt); @endphp
                                    <td class="px-5 py-3.5 text-right">
                                        <span class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold px-2.5 py-1 rounded-full" style="background:{{ $bg }};color:{{ $fg }}">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $fg }}"></span>{{ $cellTxt }}
                                        </span>
                                    </td>
                                @elseif($i === 0)
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-8 h-8 rounded-lg siarc-green-tile flex items-center justify-center text-[11px] font-bold text-white shrink-0" style="background:#157A43">{{ mb_strtoupper(mb_substr(trim($cellTxt),0,2)) }}</span>
                                            <span class="text-[13px] font-semibold text-[#1A1712]">{{ $cellTxt }}</span>
                                        </div>
                                    </td>
                                @else
                                    <td class="px-5 py-3.5 text-[12.5px] text-[#55524A]">{{ $cellTxt }}</td>
                                @endif
                            @endforeach
                            <td class="px-5 py-3.5 text-right">
                                @if($href)
                                <a href="{{ $href }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-siarc-green hover:gap-1.5 transition-all">Détails <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-10 text-center">
                <span class="w-12 h-12 mx-auto rounded-xl bg-[#F0EAFB] flex items-center justify-center mb-3"><i data-lucide="calendar-clock" class="w-6 h-6 text-[#7C4FE0]"></i></span>
                <p class="text-[13px] font-semibold text-[#1A1712]">Aucun rendez-vous pour le moment</p>
                <p class="text-[12px] text-[#8A857A] mt-1">Les rendez-vous d'affaires planifiés apparaîtront ici.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ══ SIDEBAR ══ --}}
    <div class="space-y-5">

        {{-- Live stats from $sStats (real) --}}
        <div class="siarc-card siarc-shadow p-5 siarc-in">
            <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">Aperçu des rendez-vous</h3>
            @if(count($sStats))
            <ul class="space-y-3">
                @php $palette = [['#E2F3E8','#157A43','handshake'],['#E8EFFB','#3565DE','check-circle-2'],['#FBF0D6','#9A6B00','calendar-clock']]; @endphp
                @foreach($sStats as $i => $st)
                @php [$tile,$fg,$ic] = $palette[$i % count($palette)]; @endphp
                <li class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $ic }}" class="w-[18px] h-[18px]" style="color:{{ $fg }}"></i></span>
                    <span class="text-[12.5px] text-[#3B382F] font-medium">{{ is_array($st) ? ($st[4] ?? $st['label'] ?? '') : $st }}</span>
                    <span class="ml-auto font-display text-[18px] font-extrabold text-[#161513]">{{ is_array($st) ? ($st[3] ?? $st['value'] ?? '—') : '—' }}</span>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-[12px] text-[#8A857A]">Aucune donnée disponible.</p>
            @endif
        </div>

        {{-- Quick links from $sLinks (real) --}}
        <div class="siarc-card siarc-shadow p-5 siarc-in">
            <h3 class="text-[13.5px] font-bold text-[#1A1712] mb-4">Accès rapide</h3>
            <div class="space-y-2.5">
                @php $linkIcons = ['matchmaking'=>['handshake','#E2F3E8','#157A43'],'messagerie'=>['mail','#E8EFFB','#3565DE'],'messages'=>['mail','#E8EFFB','#3565DE']]; @endphp
                @forelse($sLinks as $lk)
                    @php $key = mb_strtolower($lk['label'] ?? ''); [$ic,$tile,$fg] = $linkIcons[$key] ?? ['arrow-right','#EFEDE6','#55524A']; @endphp
                    <a href="{{ $lk['href'] ?? '#' }}" class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] p-3 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors group">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $ic }}" class="w-[18px] h-[18px]" style="color:{{ $fg }}"></i></span>
                        <span class="text-[12.5px] font-semibold text-[#3B382F]">{{ $lk['label'] ?? '' }}</span>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-[#B0AB9F] ml-auto group-hover:translate-x-0.5 transition-transform"></i>
                    </a>
                @empty
                    <p class="text-[12px] text-[#8A857A]">Aucun lien disponible.</p>
                @endforelse
            </div>
        </div>

        {{-- Filters (visual concept) --}}
        <div class="siarc-card siarc-shadow p-5 siarc-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[13.5px] font-bold text-[#1A1712]">Filtres rapides</h3>
                <button class="text-[11px] font-semibold text-siarc-green hover:underline">Réinitialiser</button>
            </div>
            <div class="space-y-3">
                @foreach(['Type de partenariat'=>'Tous','Secteur d\'activité'=>'Tous','Pays / Région'=>'Tous'] as $flabel => $fval)
                <div>
                    <label class="block text-[11px] font-semibold text-[#8A857A] mb-1.5">{{ $flabel }}</label>
                    <div class="flex items-center justify-between rounded-xl border border-[#EFEDE6] px-3 py-2.5 text-[12.5px] text-[#3B382F] bg-white">
                        {{ $fval }}<i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
                    </div>
                </div>
                @endforeach
                <label class="flex items-center gap-2 text-[12px] text-[#3B382F] pt-1">
                    <span class="w-4 h-4 rounded bg-siarc-green flex items-center justify-center"><i data-lucide="check-circle-2" class="w-3 h-3 text-white"></i></span>
                    Disponibles pour des RDV
                </label>
                <button class="w-full siarc-btn siarc-btn-green justify-center py-2.5 text-[12.5px] mt-1">Appliquer les filtres</button>
            </div>
        </div>
    </div>
</div>
