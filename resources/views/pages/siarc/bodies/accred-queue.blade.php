@php
    // ══ Print Queue — verbatim from the approved design ══

    $stats = [
        ['file-plus-2',    '#C97A16', '#FDF3E0', 'Jobs en attente',      '12',    '254 badges'],
        ['printer',        '#157A43', '#E2F3E8', 'En impression',        '3',     '78 badges'],
        ['file-check-2',   '#3565DE', '#E8EFFB', "Imprimés aujourd'hui", '452',   'Badges'],
        ['alert-circle',   '#C0010C', '#FDE8E8', 'Erreurs',              '1',     'À vérifier'],
        ['monitor-check',  '#0E8F83', '#DFF3F1', 'Imprimantes actives',  '4 / 6', 'Connectées'],
    ];
    // [jobId, name, by, type, tCol, tTile, tIcon, qty, printer, sub, status, sCol, progress|null, note, time]
    $rows = [
        ['PQ-2026-00045', 'Délégation MINAC - VIP',            'Par Marie Anguissa', 'VIP',         '#C0010C', '#FDE8E8', 'heart',       '24',  'Zebra ZC350',     'Imprimante 01', 'En impression', '#157A43', 65,   null,              '09:15', 'pause'],
        ['PQ-2026-00044', 'Exposants - Pavillon Centre',       'Par Alain Mbarga',   'Exposant',    '#C97A16', '#FDF3E0', 'users-round', '50',  'Zebra ZD620',     'Imprimante 02', 'En attente',    '#C97A16', null, 'Dans la file (2)', '09:10', 'play'],
        ['PQ-2026-00043', 'Visiteurs - Enregistrement en ligne','Par System',        'Visiteur',    '#157A43', '#E2F3E8', 'user-round',  '120', 'Zebra ZC350',     'Imprimante 01', 'En attente',    '#C97A16', null, 'Dans la file (3)', '09:05', 'play'],
        ['PQ-2026-00042', 'Intervenants & Conférenciers',      'Par David Esono',    'Intervenant', '#7C4FE0', '#F0EAFB', 'mic',         '18',  'Evolis Primacy 2','Imprimante 03', 'Imprimé',       '#157A43', 100,  null,              '08:45', 'download'],
        ['PQ-2026-00041', 'Presse & Médias',                   'Par Brenda Tcham',   'Presse',      '#3565DE', '#E8EFFB', 'newspaper',   '30',  'Zebra ZD620',     'Imprimante 02', 'Imprimé',       '#157A43', 100,  null,              '08:30', 'download'],
        ['PQ-2026-00040', 'Staff & Organisation',              'Par System',         'Staff',       '#3B382F', '#EFEDE6', 'briefcase',   '42',  'Evolis Primacy 2','Imprimante 04', 'Imprimé',       '#157A43', 100,  null,              '08:20', 'download'],
        ['PQ-2026-00039', 'Délégation - Région Sud-Ouest',     'Par Joseph Fokam',   'VIP',         '#C0010C', '#FDE8E8', 'heart',       '15',  'Zebra ZC350',     'Imprimante 01', 'Erreur',        '#C0010C', null, 'Bac vide',        '08:10', 'alert-triangle'],
    ];
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">Print Queue</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> Impression <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">File d'impression</span></p>
    </div>
    <div class="flex items-center gap-2.5">
        <button onclick="window.location.reload()" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="refresh-cw" class="w-4 h-4 text-[#8A857A]"></i>Actualiser</button>
        <button data-toast="Export de la file en préparation…" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i>Exporter<i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
        <button data-toast="Assistant de nouveau job à venir…" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="plus" class="w-4 h-4"></i>Nouveau job</button>
    </div>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$label,$value,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-start gap-3.5">
        <span class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[11.5px] text-[#8A857A]">{{ $label }}</span>
            <span class="block text-[20px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11px] text-[#8A857A]">{{ $sub }}</span>
        </span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_330px] gap-5 items-start">

    {{-- ══ LEFT: filters + jobs table ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden" id="accScope">
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[160px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#pqScope" placeholder="Rechercher un job..." class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach([['Statut','Tous',['en impression','en attente','imprimé','erreur']],['Type de badge','Tous',['vip','exposant','visiteur','intervenant','presse','staff']],['Imprimante','Toutes',['imprimante 01','imprimante 02','imprimante 03','imprimante 04']]] as [$flabel,$fall,$opts])
            <div class="min-w-[120px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $flabel }}</label>
                <select data-filter-select="#pqScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    <option>{{ $fall }}</option>
                    @foreach($opts as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach
                </select>
            </div>
            @endforeach
            <div class="min-w-[120px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">Date</label>
                <span class="flex items-center justify-between gap-2 text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">Aujourd'hui<i data-lucide="calendar" class="w-4 h-4 text-[#B0AB9F]"></i></span>
            </div>
            <button data-toast="Filtres avancés à venir…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[820px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['JOB ID','NOM DU JOB','TYPE DE BADGE','QUANTITÉ','IMPRIMANTE','STATUT','CRÉÉ LE','ACTIONS'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last ? 'text-right' : '' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="pqScope">
                    @foreach($rows as [$jid,$name,$by,$type,$tCol,$tTile,$tIcon,$qty,$printer,$psub,$status,$sCol,$prog,$note,$time,$act])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($status) }} {{ Str::lower($type) }} {{ Str::lower($psub) }}" data-filter-text="{{ $jid }} {{ $name }} {{ $by }} {{ $type }} {{ $printer }} {{ $status }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-4 py-3 text-[12px] font-medium text-[#3B382F] whitespace-nowrap">{{ $jid }}</td>
                        <td class="px-4 py-3">
                            <p class="text-[13px] font-semibold text-[#131313]">{{ $name }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $by }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium" style="color:{{ $tCol }}">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background:{{ $tTile }}"><i data-lucide="{{ $tIcon }}" class="w-3.5 h-3.5" style="color:{{ $tCol }}"></i></span>{{ $type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-[13px] text-[#3B382F]">{{ $qty }}</td>
                        <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $printer }}<br><span class="text-[11px] text-[#8A857A]">{{ $psub }}</span></td>
                        <td class="px-4 py-3 min-w-[150px]">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-semibold" style="color:{{ $sCol }}"><span class="w-2 h-2 rounded-full" style="background:{{ $sCol }}"></span>{{ $status }}</span>
                            @if($prog !== null)
                            <span class="mt-1.5 flex items-center gap-2">
                                <span class="flex-1 h-[5px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full" style="width:{{ $prog }}%;background:{{ $sCol }}"></span></span>
                                <span class="text-[10.5px] text-[#8A857A]">{{ $prog }}%</span>
                            </span>
                            @elseif($note)
                            <span class="block mt-0.5 text-[11px] text-[#8A857A]">{{ $note }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-[12px] text-[#3B382F]">Aujourd'hui<br><span class="text-[11px] text-[#8A857A]">{{ $time }}</span></td>
                        <td class="px-4 py-3">
                            <span class="flex items-center justify-end gap-1">
                                <button data-toast="{{ $act === 'pause' ? 'Job mis en pause (démo)' : ($act === 'play' ? 'Job relancé (démo)' : ($act === 'download' ? 'Téléchargement du rapport de job (démo)' : 'Erreur : bac vide — vérifier l\'imprimante')) }}" class="w-7 h-7 rounded-md {{ $act === 'alert-triangle' ? 'bg-[#FDE8E8]' : 'border border-[#EFEDE6]' }} hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="{{ $act }}" class="w-4 h-4 {{ $act === 'alert-triangle' ? 'text-[#C0010C]' : 'text-[#8A857A]' }}"></i></button>
                                <button data-toast="Détail du job {{ $jid }} — voir le panneau de droite" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></button>
                                <button data-toast="Menu contextuel à venir…" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="more-vertical" class="w-4 h-4 text-[#8A857A]"></i></button>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div data-page="#pqScope" data-page-size="10" class="px-5 py-3.5 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[12px] text-[#8A857A]">Affichage 1 à 7 sur 12 jobs</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button data-page-num="1" class="is-active w-8 h-8 rounded-lg text-[12px] font-semibold flex items-center justify-center">1</button>
                <button data-page-num="2" class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] font-semibold text-[#3B382F] flex items-center justify-center hover:bg-[#F5F3EE]">2</button>
                <button data-page-next class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                <span class="ml-2 text-[12px] text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 flex items-center gap-1">10 / page<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></span>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT RAIL: job detail ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Détail du job</p>
            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">En impression</span>
        </div>
        <dl class="space-y-2.5 text-[12px]">
            @foreach([['JOB ID','PQ-2026-00045'],['NOM DU JOB','Délégation MINAC - VIP'],['CRÉÉ PAR','Marie Anguissa'],['CRÉÉ LE',"Aujourd'hui à 09:15"]] as [$k,$v])
            <div class="flex items-center justify-between gap-3"><dt class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $k }}</dt><dd class="font-medium text-[#3B382F] text-right">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">TYPE DE BADGE</dt>
                <dd class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#C0010C]"><span class="w-5 h-5 rounded-full bg-[#FDE8E8] flex items-center justify-center"><i data-lucide="heart" class="w-3 h-3 text-[#C0010C]"></i></span>VIP</dd>
            </div>
            <div class="flex items-center justify-between gap-3"><dt class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">QUANTITÉ</dt><dd class="font-medium text-[#3B382F]">24 badges</dd></div>
            <div class="flex items-center justify-between gap-3"><dt class="text-[10.5px] font-bold tracking-wide text-[#8A857A] uppercase">IMPRIMANTE</dt><dd class="font-medium text-[#3B382F] text-right">Zebra ZC350 (Imprimante 01)</dd></div>
        </dl>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-2">Progression</p>
        <div class="flex items-center gap-2">
            <span class="flex-1 h-[7px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full bg-[#157A43]" style="width:65%"></span></span>
            <span class="text-[12px] font-semibold text-[#157A43]">65%</span>
        </div>
        <div class="grid grid-cols-3 gap-2 mt-2.5 text-center">
            @foreach([['Imprimés','16'],['Restants','8'],['Estimé restant','00:02:15']] as [$k,$v])
            <span><span class="block text-[10.5px] text-[#8A857A]">{{ $k }}</span><span class="block text-[13px] font-bold text-[#131313]">{{ $v }}</span></span>
            @endforeach
        </div>

        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mt-5 mb-3">Aperçu du badge</p>
        <div class="flex justify-center">
            @include('pages.siarc.partials.accred-badge', ['bVariant' => 'red', 'bVip' => true, 'bOrg' => 'MINISTÈRE DES ARTS ET DE LA CULTURE', 'bId' => 'VIP-00024', 'bScale' => '212px'])
        </div>
        <div class="flex flex-wrap items-center gap-1.5 mt-3">
            @foreach(['85.6 x 54 mm','PVC','Vertical'] as $chip)
            <span class="text-[11px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#EFEDE6] rounded-md px-2 py-1">{{ $chip }}</span>
            @endforeach
            <a href="{{ route('siarc.admin.accred.templates', ['lang' => $lang]) }}" class="text-[11px] font-semibold text-[#C0010C] ml-auto underline underline-offset-2">Voir le template</a>
        </div>
        <div class="grid grid-cols-2 gap-2.5 mt-4">
            <button data-toast="Job mis en pause (démo)" class="siarc-btn justify-center text-[13px] font-semibold text-[#C97A16] border border-[#F3DFC2] rounded-lg px-3 py-2.5 hover:bg-[#FDF6EC]"><i data-lucide="pause" class="w-4 h-4"></i>Pause</button>
        <button data-toast="Annulation désactivée sur la démo" class="siarc-btn justify-center text-[13px] font-semibold text-[#C0010C] border border-[#F5CFCF] rounded-lg px-3 py-2.5 hover:bg-[#FDF3F3]"><i data-lucide="trash-2" class="w-4 h-4"></i>Annuler le job</button>
        </div>
    </div>
</div>
