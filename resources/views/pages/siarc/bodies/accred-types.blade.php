@php
    // ══ Types de Badges — verbatim from the approved design ══

    $stats = [
        ['id-card',   '#157A43', '#E2F3E8', '8',     'Types de badges', 'au total'],
        ['user-check','#3565DE', '#E8EFFB', '6',     'Types actifs',    '75%'],
        ['files',     '#C97A16', '#FDF3E0', '4 850', 'Badges émis',     'tous types confondus'],
        ['printer',   '#7C4FE0', '#F0EAFB', '98.6%', "Taux d'utilisation", 'ce mois'],
    ];
    // [type, tCol, tTile, tIcon, description, access, aCol, aTile, dots, statut, emitted]
    $rows = [
        ['Visiteur',    '#157A43', '#E2F3E8', 'user-round',  "Accès général aux pavillons\net espaces d'exposition", 'Pavillons',            '#157A43', '#E8F5EC', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '2 150'],
        ['Exposant',    '#C97A16', '#FDF3E0', 'users-round', 'Pour tous les exposants\net leurs représentants',      'Pavillons + Stands',   '#C97A16', '#FDF3E0', ['#157A43','#C0010C','#131313'],           'Actif', '1 280'],
        ['VIP',         '#C0010C', '#FDE8E8', 'heart',       'Invités VIP, officiels et\ndélégations spéciales',     'Accès complet',        '#C0010C', '#FDE8E8', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '620'],
        ['Intervenant', '#7C4FE0', '#F0EAFB', 'mic',         'Conférenciers, panélistes\net animateurs',             'Zones de conférences', '#7C4FE0', '#F0EAFB', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '410'],
        ['Presse',      '#3565DE', '#E8EFFB', 'newspaper',   'Journalistes et\nreprésentants des médias',            'Zones presse',         '#3565DE', '#E8EFFB', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '210'],
        ['Staff',       '#3B382F', '#EFEDE6', 'briefcase',   "Personnel d'organisation\net staff technique",         'Accès complet',        '#3B382F', '#EFEDE6', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '120'],
        ['Sécurité',    '#C0010C', '#FDE8E8', 'shield',      "Personnel de sécurité\net contrôle d'accès",           'Zones autorisées',     '#3B382F', '#EFEDE6', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '45'],
        ['Bénévole',    '#0E8F83', '#DFF3F1', 'hand',        "Bénévoles et volontaires\nde l'événement",             'Zones assignées',      '#0E8F83', '#DFF3F1', ['#157A43','#E6B201','#C0010C','#131313'], 'Actif', '15'],
    ];
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">Types de Badges</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Types de Badges</span></p>
    </div>
    <button data-toast="Assistant de nouveau type à venir…" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="plus" class="w-4 h-4"></i>Nouveau type de badge</button>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$value,$label,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-center gap-3.5">
        <span class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[20px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11.5px] text-[#8A857A]">{{ $label }} <span class="text-[#B0AB9F]">{{ $sub }}</span></span>
        </span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_330px] gap-5 items-start">

    {{-- ══ LEFT: filters + table ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden" id="accScope">
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[190px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#btScope" placeholder="Rechercher un type de badge..." class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            @foreach([['Statut',['actif','inactif']],['Accès',['pavillons','stands','accès complet','conférences','presse','autorisées','assignées']]] as [$flabel,$opts])
            <div class="min-w-[125px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $flabel }}</label>
                <select data-filter-select="#btScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    <option>Tous</option>
                    @foreach($opts as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach
                </select>
            </div>
            @endforeach
            <button data-toast="Filtres avancés à venir…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC] ml-auto"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['TYPE DE BADGE','DESCRIPTION','ACCÈS','COULEURS','STATUT','BADGES ÉMIS','ACTIONS'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last ? 'text-right' : ($h === 'BADGES ÉMIS' ? 'text-right' : '') }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="btScope">
                    @foreach($rows as [$type,$tCol,$tTile,$tIcon,$desc,$access,$aCol,$aTile,$dots,$statut,$emitted])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($statut) }} {{ Str::lower($access) }}" data-filter-text="{{ $type }} {{ $desc }} {{ $access }} {{ $statut }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#131313]">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $tTile }}"><i data-lucide="{{ $tIcon }}" class="w-4 h-4" style="color:{{ $tCol }}"></i></span>{{ $type }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-[11.5px] text-[#8A857A] whitespace-pre-line leading-snug">{{ $desc }}</td>
                        <td class="px-4 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md" style="color:{{ $aCol }};background:{{ $aTile }}">{{ $access }}</span></td>
                        <td class="px-4 py-3.5"><span class="flex items-center gap-1">@foreach($dots as $d)<span class="w-3.5 h-3.5 rounded-full" style="background:{{ $d }}"></span>@endforeach</span></td>
                        <td class="px-4 py-3.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">{{ $statut }}</span></td>
                        <td class="px-4 py-3.5 text-right text-[13px] font-semibold text-[#131313]">{{ $emitted }}</td>
                        <td class="px-4 py-3.5">
                            <span class="flex items-center justify-end gap-1">
                                <button data-toast="Détail « {{ $type }} » — voir le panneau de droite" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></button>
                                <button data-toast="Éditeur de type à venir…" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="pencil" class="w-4 h-4 text-[#8A857A]"></i></button>
                                <button data-toast="Type dupliqué (démo)" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="copy" class="w-4 h-4 text-[#8A857A]"></i></button>
                                <button data-toast="Suppression désactivée sur la démo" class="w-7 h-7 rounded-md hover:bg-[#FDE8E8] flex items-center justify-center"><i data-lucide="trash-2" class="w-4 h-4 text-[#C0010C]"></i></button>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div data-page="#btScope" data-page-size="10" class="px-5 py-3.5 border-t border-[#EFEDE6]">
            <p data-page-info class="text-[12px] text-[#8A857A]">Affichage de 1 à 8 sur 8 types de badges</p>
        </div>
    </div>

    {{-- ══ RIGHT RAIL: badge type detail ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Détails du type de badge</p>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-[#157A43]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>Actif</span>
        </div>
        <div class="flex justify-center mb-4">
            @include('pages.siarc.partials.accred-badge', ['bScale' => '208px'])
        </div>
        <dl class="space-y-2.5 text-[12px]">
            @foreach([['Nom du type','Visiteur'],['Description',"Accès général aux pavillons\net espaces d'exposition"],['Accès','Pavillons uniquement'],['Validité',"Valable pour toute la durée\nde l'événement"]] as [$k,$v])
            <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A] shrink-0">{{ $k }}</dt><dd class="font-medium text-[#3B382F] text-right whitespace-pre-line leading-snug">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[#8A857A]">Couleurs</dt>
                <dd class="flex items-center gap-1.5">@foreach(['#157A43','#E6B201','#C0010C','#131313'] as $c)<span class="w-4 h-4 rounded-full" style="background:{{ $c }}"></span>@endforeach</dd>
            </div>
        </dl>
        <p class="text-[11.5px] text-[#8A857A] mt-4 mb-2">Éléments inclus</p>
        <div class="flex flex-wrap gap-1.5">
            @foreach(['Photo','Nom','QR Code','ID Badge','Pays'] as $chip)
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#157A43] bg-[#E8F5EC] border border-[#CFE8D8] rounded-md px-2 py-1"><i data-lucide="check-circle-2" class="w-3 h-3"></i>{{ $chip }}</span>
            @endforeach
        </div>
        <div class="flex items-center gap-2 mt-3.5">
            <span class="text-[11.5px] text-[#8A857A]">Technologies</span>
            <span class="text-[11px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#EFEDE6] rounded-md px-2 py-1">QR Code</span>
            <span class="text-[11px] font-medium text-[#C97A16] bg-[#FDF3E0] border border-[#F3DFC2] rounded-md px-2 py-1">RFID (optionnel)</span>
        </div>
        <dl class="space-y-2 text-[12px] mt-3.5">
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Badge émis</dt><dd class="font-semibold text-[#131313]">2 150</dd></div>
            <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Dernière mise à jour</dt><dd class="font-medium text-[#3B382F]">15 Mai 2026 <span class="text-[#8A857A]">par Jude Nshome</span></dd></div>
        </dl>
        <button data-toast="Éditeur de type à venir…" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="pencil" class="w-4 h-4"></i>Modifier ce type de badge</button>
    </div>
</div>
