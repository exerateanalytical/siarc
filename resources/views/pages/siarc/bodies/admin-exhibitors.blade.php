@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── KPI row (approved design figures) ──────────────────────────────────────
    $sStats = $sStats ?? [
        ['users-round','#157A43','#E2F3E8','842','Total Exposants','↑ 18% vs dernier mois'],
        ['tag','#C97A16','#FDF3E0','128','Nouveaux Exposants','↑ 24% vs dernier mois'],
        ['check-circle-2','#3565DE','#E8EFFB','715','Exposants confirmés','85% du total'],
        ['clock','#7C4FE0','#F0EAFB','47','En attente de validation','5.6% du total'],
        ['grid-3x3','#C97A16','#FDF3E0','78%','Pavillons occupés','↑ 12% vs dernier mois'],
        ['banknote','#157A43','#E2F3E8','128,450,000','Revenus générés','↑ 22% vs dernier mois'],
    ];

    // ── Table (real passed data expected) ──────────────────────────────────────
    $toneMap = [
        'green'  => ['#E2F3E8','#157A43'],
        'gold'   => ['#FDF3E0','#C97A16'],
        'red'    => ['#FDE8E8','#C0010C'],
        'blue'   => ['#E8EFFB','#3565DE'],
        'grey'   => ['#F1F1EF','#8A857A'],
        'purple' => ['#F0EAFB','#7C4FE0'],
    ];
    $sTables = $sTables ?? [[
        'cols' => ['Exposant','Entreprise / Organisation','Pays','Pavillon / Stand','Catégorie principale','Statut','Date d\'inscription'],
        'rows' => [
            ['href'=>'#','cells'=>['Art Bois Précieux','Paul Tchameni','Art Bois Précieux SARL','Cameroun','Pavillon Centre','C-12','Bois & Sculpture',['badge'=>'Confirmé','tone'=>'green'],'15 Mai 2026']],
            ['href'=>'#','cells'=>['Tissages du Soleil','Marie Claire Abena','Tissages du Soleil','Côte d\'Ivoire','Pavillon Ouest','O-08','Textiles & Tissus',['badge'=>'Confirmé','tone'=>'green'],'18 Mai 2026']],
            ['href'=>'#','cells'=>['Poteries de Maroua','Issa Abdou','Poteries de Maroua','Cameroun','Pavillon Nord','N-15','Poterie & Céramique',['badge'=>'Confirmé','tone'=>'green'],'20 Mai 2026']],
            ['href'=>'#','cells'=>['Bijoux d\'Afrique','Awa Diarra','Bijoux d\'Afrique','Sénégal','Pavillon Sud','S-21','Bijouterie & Métal',['badge'=>'En attente','tone'=>'gold'],'22 Mai 2026']],
            ['href'=>'#','cells'=>['Cuir Excellence','Michel Koffi','Cuir Excellence SARL','Cameroun','Pavillon Centre','C-05','Cuir & Peaux',['badge'=>'Confirmé','tone'=>'green'],'23 Mai 2026']],
            ['href'=>'#','cells'=>['Vannerie du Littoral','Rose Ekomo','Vannerie du Littoral','Cameroun','Pavillon Est','E-11','Vannerie & Bambou',['badge'=>'En attente','tone'=>'gold'],'24 Mai 2026']],
            ['href'=>'#','cells'=>['Peintures Bana','Joseph Bana','Peintures Bana','Cameroun','Pavillon Nord','N-03','Arts & Traditions',['badge'=>'Confirmé','tone'=>'green'],'25 Mai 2026']],
            ['href'=>'#','cells'=>['Mode & Traditions','Grace Etoundi','Mode & Traditions','Cameroun','Pavillon Ouest','O-17','Mode & Accessoires',['badge'=>'En attente','tone'=>'gold'],'26 Mai 2026']],
            ['href'=>'#','cells'=>['Nature & Bien-être','François Mvondo','Nature & Bien-être SARL','Cameroun','Pavillon Sud','S-09','Cosmétiques naturels',['badge'=>'Confirmé','tone'=>'green'],'27 Mai 2026']],
            ['href'=>'#','cells'=>['Art Numérique 237','Boris Nguimatsia','Art Numérique 237','Cameroun','Pavillon Innovation','I-02','Art numérique & Design',['badge'=>'En liste d\'attente','tone'=>'blue'],'28 Mai 2026']],
        ],
    ]];
    $tbl = $sTables[0];

    // Tab counts derived from the REAL passed $sStats (value at positional index 3)
    $statVal = fn($i) => isset($sStats[$i]) && is_array($sStats[$i]) ? ($sStats[$i][3] ?? '—') : '—';
    $totalExh = $statVal(0);
    $tabs = [
        ['Tous les exposants', $totalExh, true],
        ['Confirmés', $statVal(1), false],
        ['En attente', $statVal(2), false],
        ['Enregistrés', $statVal(3), false],
    ];

    $sLinks = $sLinks ?? [
        ['building-2','Pavillons & Stands','siarc.admin.pavilions'],
        ['map','Plan du salon','siarc.admin.floorplan'],
        ['id-card','Accréditations','siarc.admin.badges'],
        ['handshake','B2B Matchmaking','siarc.admin.b2b'],
        ['bar-chart-3','Analytics & Rapports','siarc.admin.analytics'],
    ];
@endphp

<section class="siarc-in">

    {{-- ══ PAGE HEADING ══ --}}
    <div class="mb-5">
        <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">Gestion des Exposants</h1>
        <p class="text-[13px] text-[#8A857A] mt-1">Gérez les exposants, leurs informations, pavillons et stands.</p>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
        @foreach($sStats as [$icon,$color,$tile,$val,$label,$sub])
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i></span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
            <p class="text-[22px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $val }}@if($label==='Revenus générés')<span class="text-[11px] font-semibold text-[#8A857A]"> FCFA</span>@endif</p>
            <div class="mt-2">
                @php $up = str_starts_with($sub,'↑'); @endphp
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $up ? 'text-siarc-green' : 'text-[#8A857A]' }}">
                    @if($up)<i data-lucide="arrow-up" class="w-3 h-3"></i>@endif{{ ltrim($sub,'↑ ') }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ MAIN CARD ══ --}}
    <div class="siarc-card siarc-shadow overflow-hidden">

        {{-- Tabs + primary actions --}}
        <div class="flex flex-wrap items-center gap-3 px-5 pt-4 border-b border-[#F1EFE8]">
            <div class="flex flex-wrap items-center gap-5 flex-1 min-w-0">
                @foreach($tabs as [$t,$n,$active])
                <button class="relative pb-3 text-[13px] font-semibold transition-colors {{ $active ? 'text-siarc-green' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                    {{ $t }} <span class="{{ $active ? 'text-siarc-green' : 'text-[#B0AB9F]' }}">({{ $n }})</span>
                    @if($active)<span class="absolute left-0 -bottom-px h-[2.5px] w-full rounded-full bg-siarc-green"></span>@endif
                </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2.5 pb-3">
                <button class="siarc-btn siarc-btn-outline !border-[#E1DED5] !text-[#3B382F] text-[12.5px] px-3.5 py-2 hover:bg-[#FBFAF6]"><i data-lucide="download" class="w-4 h-4"></i>Exporter</button>
                <a href="{{ $h('siarc.admin.exhibitors') }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un exposant</a>
            </div>
        </div>

        {{-- Search + filters --}}
        <div class="flex flex-wrap items-end gap-3 px-5 py-4">
            <label class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" placeholder="Rechercher un exposant, entreprise, pays..." class="w-full h-[42px] rounded-xl border border-[#ECEAE3] bg-[#FBFAF7] pl-9 pr-3 text-[13px] text-[#3B382F] placeholder-[#B0AB9F] focus:outline-none focus:border-[#D8E5DC] focus:bg-white">
            </label>
            @foreach(['Pavillon','Catégorie','Pays','Statut'] as $f)
            <div class="min-w-[130px]">
                <p class="text-[10.5px] font-semibold text-[#8A857A] mb-1.5">{{ $f }}</p>
                <button class="w-full h-[42px] flex items-center justify-between gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[13px] text-[#3B382F] hover:border-[#D8E5DC]">
                    Tous <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
                </button>
            </div>
            @endforeach
            <button class="h-[42px] siarc-btn text-[12.5px] px-3.5 border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="filter" class="w-4 h-4"></i>Filtres</button>
            <button class="h-[42px] siarc-btn text-[12.5px] px-3.5 text-siarc-green hover:bg-[#E2F3E8] rounded-xl"><i data-lucide="activity" class="w-4 h-4"></i>Réinitialiser</button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left">
                <thead>
                    <tr class="border-t border-b border-[#F1EFE8] bg-[#FBFAF7]">
                        <th class="w-10 pl-5 py-3"><span class="w-4 h-4 inline-block rounded border border-[#D8D5CC] align-middle"></span></th>
                        @foreach($tbl['cols'] as $c)
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide uppercase text-[#8A857A] whitespace-nowrap">{{ $c }}</th>
                        @endforeach
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide uppercase text-[#8A857A] whitespace-nowrap text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tbl['rows'] as $row)
                    @php
                        // Real contract cells: [Exposant, Pavillon, Stand, {badge,tone status}, Check-in]
                        $c = $row['cells'];
                        $status = is_array($c[3] ?? null) ? $c[3] : ['badge' => (string)($c[3] ?? '—'), 'tone' => 'grey'];
                        [$bBg,$bFg] = $toneMap[$status['tone']] ?? $toneMap['grey'];
                        $checkin = is_array($c[4] ?? null) ? ($c[4]['badge'] ?? '—') : ($c[4] ?? '—');
                        $done = trim((string)$checkin) === '✓';
                    @endphp
                    <tr class="border-b border-[#F4F2EC] hover:bg-[#FBFAF6] transition-colors group">
                        <td class="pl-5 py-3.5"><span class="w-4 h-4 inline-block rounded border border-[#D8D5CC] align-middle group-hover:border-siarc-green"></span></td>
                        {{-- Exposant --}}
                        <td class="px-3 py-3.5">
                            <a href="{{ $row['href'] ?? '#' }}" class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg siarc-adire shrink-0 flex items-center justify-center font-display text-[13px] font-extrabold text-siarc-gold">{{ mb_strtoupper(mb_substr((string)$c[0],0,1)) }}</span>
                                <span class="block text-[13px] font-semibold text-[#161513] leading-tight group-hover:text-siarc-green">{{ $c[0] }}</span>
                            </a>
                        </td>
                        {{-- Pavillon --}}
                        <td class="px-3 py-3.5 text-[12.5px] text-[#3B382F]">{{ $c[1] ?? '—' }}</td>
                        {{-- Stand --}}
                        <td class="px-3 py-3.5 text-[12.5px] font-medium text-[#3B382F] whitespace-nowrap">{{ $c[2] ?? '—' }}</td>
                        {{-- Statut --}}
                        <td class="px-3 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-semibold" style="background:{{ $bBg }};color:{{ $bFg }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $bFg }}"></span>{{ $status['badge'] }}
                            </span>
                        </td>
                        {{-- Check-in --}}
                        <td class="px-3 py-3.5 whitespace-nowrap">
                            @if($done)
                                <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-siarc-green"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Enregistré</span>
                            @else
                                <span class="text-[12px] text-[#B0AB9F]">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3.5 pr-5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ $row['href'] ?? '#' }}" title="Voir" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-siarc-green hover:bg-[#E2F3E8]"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="{{ $row['href'] ?? '#' }}" title="Modifier" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-siarc-ochre hover:bg-[#FDF3E0]"><i data-lucide="square-pen" class="w-4 h-4"></i></a>
                                <button title="Plus" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-[#3B382F] hover:bg-[#F1F1EF]"><i data-lucide="ellipsis" class="w-4 h-4"></i></button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-10 text-[13px] text-[#8A857A]">Aucun exposant.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-[#F1EFE8]">
            <p class="text-[12px] text-[#8A857A]">Affichage de 1 à {{ count($tbl['rows']) }} sur <span class="font-semibold text-[#3B382F]">{{ $totalExh }}</span> exposants</p>
            <div class="flex items-center gap-1.5">
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#CFCcC2]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button class="w-8 h-8 rounded-lg bg-siarc-green text-white text-[12.5px] font-semibold">1</button>
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#CFCcC2]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
            </div>
            <div class="flex items-center gap-2 text-[12px] text-[#8A857A]">
                <span>Lignes par page</span>
                <button class="h-8 flex items-center gap-1.5 rounded-lg border border-[#ECEAE3] px-2.5 text-[#3B382F] hover:bg-[#FBFAF6]">10 <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
            </div>
        </div>
    </div>

    {{-- ══ RELATED PAGES STRIP ══ --}}
    <div class="mt-6">
        <p class="siarc-kicker text-siarc-ochre mb-3">Gestion liée</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($sLinks as $lk)
            @php [$li,$lt,$lr] = [$lk[0] ?? 'arrow-right', $lk[1] ?? ($lk['label'] ?? ''), $lk[2] ?? ($lk['route'] ?? null)]; @endphp
            <a href="{{ $lr ? $h($lr) : '#' }}" class="siarc-card siarc-shadow siarc-lift p-4 flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="{{ $li }}" class="w-5 h-5 text-siarc-green"></i></span>
                <span class="min-w-0">
                    <span class="block text-[12.5px] font-semibold text-[#161513] leading-tight truncate">{{ $lt }}</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-siarc-ochre">Ouvrir <i data-lucide="arrow-right" class="w-3 h-3"></i></span>
                </span>
            </a>
            @endforeach
        </div>
    </div>

</section>
