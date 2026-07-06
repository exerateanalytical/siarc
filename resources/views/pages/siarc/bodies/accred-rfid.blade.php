@php
    // ══ RFID Support — verbatim from the approved design ══

    $stats = [
        ['radio',        '#157A43', '#E2F3E8', '12 850', 'Badges RFID émis',  'au total'],
        ['smartphone',   '#7C4FE0', '#F0EAFB', '234',    'Cartes actives',    "aujourd'hui"],
        ['badge-check',  '#C97A16', '#FDF3E0', '98.6%',  'Taux de lecture',   'ce mois'],
        ['shield-check', '#3565DE', '#E8EFFB', '3',      'Lecteurs actifs',   'en ligne'],
        ['alert-triangle','#C0010C','#FDE8E8', '2',      'Alertes',           "aujourd'hui"],
    ];
    // [uid, name, sub, type, tCol, tTile, tIcon, zone, zSub, statut, on, read, readSub, face]
    $rows = [
        ['04 A3 B2 7F 91 6E 80', 'Jean Paul Essomba', 'VIP-00024', 'VIP',         '#C0010C', '#FDE8E8', 'heart',       'Pavillon Officiel',  'PO-001',   'Active',   true,  '27/07/2026 09:15:32', 'Entrée Porte A',  1],
        ['04 A3 B2 7F 91 6E 81', 'Marie Anguissa',    'EXP-00089', 'Exposant',    '#C97A16', '#FDF3E0', 'users-round', 'Pavillon Centre',    'PC-045',   'Active',   true,  '27/07/2026 09:12:07', 'Entrée Porte C',  2],
        ['04 A3 B2 7F 91 6E 82', 'Alain Mbarga',      'VIS-00356', 'Visiteur',    '#157A43', '#E2F3E8', 'user-round',  'Pavillon Est',       'PE-112',   'Active',   true,  '27/07/2026 09:11:45', 'Entrée Porte B',  3],
        ['04 A3 B2 7F 91 6E 83', 'Brenda Tcham',      'PRE-00078', 'Presse',      '#3565DE', '#E8EFFB', 'newspaper',   'Centre Médias',      'CM-012',   'Active',   true,  '27/07/2026 09:10:21', 'Entrée Presse',   4],
        ['04 A3 B2 7F 91 6E 84', 'David Esono',       'INT-00034', 'Intervenant', '#7C4FE0', '#F0EAFB', 'mic',         'Salle Conférence A', 'SC-A-03',  'Active',   true,  '27/07/2026 09:08:59', 'Entrée Porte D',  5],
        ['04 A3 B2 7F 91 6E 85', 'Samuel Ndongo',     'STF-00122', 'Staff',       '#3B382F', '#EFEDE6', 'briefcase',   'Zone Staff',         'ZS-001',   'Active',   true,  '27/07/2026 09:07:33', 'Entrée Staff',    6],
        ['04 A3 B2 7F 91 6E 86', 'Priska Nguimatsia', 'VOL-00056', 'Bénévole',    '#0E8F83', '#DFF3F1', 'hand',        'Zone Bénévoles',     'ZB-002',   'Inactive', false, '26/07/2026 17:45:10', 'Sortie Porte A',  7],
        ['04 A3 B2 7F 91 6E 87', 'Joseph Fokam',      'SEC-00031', 'Sécurité',    '#C0010C', '#FDE8E8', 'shield',      'Zone Sécurité',      'ZS-SEC-01','Active',   true,  '27/07/2026 09:06:18', 'Entrée Sécurité', 8],
    ];
    $cardHref = fn($uid) => route('siarc.admin.accred.rfid.card', ['uid' => str_replace(' ', '', $uid), 'lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="mb-5">
    <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">RFID Support</h1>
    <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">RFID Support</span></p>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$value,$label,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-center gap-3.5">
        <span class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[19px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11.5px] text-[#8A857A]">{{ $label }} <span class="text-[#B0AB9F]">{{ $sub }}</span></span>
        </span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5 items-start">

    {{-- ══ LEFT: tabs + cards table ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden" id="accScope">
        <div data-tabs="rfidtabs" class="flex items-center gap-6 px-5 pt-4 border-b border-[#EFEDE6] overflow-x-auto">
            @foreach([['credit-card','Cartes RFID','cards',true],['scan-line','Lecteurs','readers',false],['cpu','Encodage','encode',false],['map-pin',"Zones d'accès",'zones',false],['settings','Paramètres','params',false]] as [$ic,$lbl,$key,$on])
            <button data-tab="{{ $key }}" class="si-tab {{ $on ? 'is-active' : '' }} inline-flex items-center gap-2 whitespace-nowrap"><i data-lucide="{{ $ic }}" class="w-4 h-4"></i>{{ $lbl }}</button>
            @endforeach
        </div>

        <div data-panel="cards" data-tabs-for="rfidtabs">
            <div class="px-5 pt-4 flex items-center justify-between">
                <p class="text-[12px] font-bold tracking-[0.08em] text-[#131313] uppercase">Cartes RFID enregistrées</p>
                <button data-toast="Export des cartes en préparation…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i>Exporter</button>
            </div>
            <div class="px-5 py-4 flex flex-wrap items-end gap-3">
                <div class="relative flex-1 min-w-[170px]">
                    <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" data-filter="#rfScope" placeholder="Rechercher (nom, ID carte, badge...)" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
                </div>
                @foreach([['Statut',['active','inactive']],['Type de badge',['vip','exposant','visiteur','presse','intervenant','staff','bénévole','sécurité']],['Pavillon',['pavillon officiel','pavillon centre','pavillon est']]] as [$flabel,$opts])
                <div class="min-w-[115px]">
                    <label class="block text-[11px] text-[#8A857A] mb-1">{{ $flabel }}</label>
                    <select data-filter-select="#rfScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                        <option>Tous</option>
                        @foreach($opts as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach
                    </select>
                </div>
                @endforeach
                <button data-toast="Filtres avancés à venir…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[820px]">
                    <thead>
                        <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                            @foreach(['ID CARTE RFID','NOM DU TITULAIRE','TYPE DE BADGE','PAVILLON / ZONE','STATUT','DERNIÈRE LECTURE','ACTIONS'] as $h)
                            <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last ? 'text-right' : '' }}">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="rfScope">
                        @foreach($rows as [$uid,$name,$sub,$type,$tCol,$tTile,$tIcon,$zone,$zSub,$statut,$on,$read,$readSub,$face])
                        <tr data-filter-item data-filter-tags="{{ Str::lower($statut) }} {{ Str::lower($type) }} {{ Str::lower($zone) }}" data-filter-text="{{ $uid }} {{ $name }} {{ $sub }} {{ $type }} {{ $zone }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                            <td class="px-4 py-3 text-[12px] font-medium text-[#3B382F] whitespace-nowrap">{{ $uid }}</td>
                            <td class="px-4 py-3">
                                <span class="flex items-center gap-2.5">
                                    <img src="{{ asset('images/siarc/accred-face-'.$face.'.png') }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                    <span><span class="block text-[13px] font-semibold text-[#131313]">{{ $name }}</span><span class="block text-[11px] text-[#8A857A]">{{ $sub }}</span></span>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 text-[12px] font-medium" style="color:{{ $tCol }}">
                                    <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background:{{ $tTile }}"><i data-lucide="{{ $tIcon }}" class="w-3.5 h-3.5" style="color:{{ $tCol }}"></i></span>{{ $type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $zone }}<br><span class="text-[11px] text-[#8A857A]">{{ $zSub }}</span></td>
                            <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md {{ $on ? 'bg-[#E8F5EC] text-[#157A43]' : 'bg-[#FDE8E8] text-[#C0010C]' }}">{{ $statut }}</span></td>
                            <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $read }}<br><span class="text-[11px] text-[#8A857A]">{{ $readSub }}</span></td>
                            <td class="px-4 py-3">
                                <span class="flex items-center justify-end gap-1">
                                    <a href="{{ $cardHref($uid) }}" title="Détail" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></a>
                                    <button data-toast="Édition de carte à venir…" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="pencil" class="w-4 h-4 text-[#8A857A]"></i></button>
                                    <button data-toast="Menu contextuel à venir…" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="more-vertical" class="w-4 h-4 text-[#8A857A]"></i></button>
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div data-page="#rfScope" data-page-size="10" class="px-5 py-3.5 flex items-center justify-between border-t border-[#EFEDE6]">
                <p data-page-info class="text-[12px] text-[#8A857A]">Affichage de 1 à 8 sur 250 cartes RFID</p>
                <span class="flex items-center gap-1.5">
                    <button data-page-prev class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                    <button data-page-num="1" class="is-active w-8 h-8 rounded-lg text-[12px] font-semibold flex items-center justify-center">1</button>
                    @foreach(['2','3'] as $p)<button data-page-num="{{ $p }}" class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] font-semibold text-[#3B382F] flex items-center justify-center hover:bg-[#F5F3EE]">{{ $p }}</button>@endforeach
                    <span class="text-[12px] text-[#8A857A] px-1">…</span>
                    <button data-page-num="32" class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] font-semibold text-[#3B382F] flex items-center justify-center hover:bg-[#F5F3EE]">32</button>
                    <button data-page-next class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                    <span class="ml-2 text-[12px] text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 flex items-center gap-1">10 / page<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></span>
                </span>
            </div>
        </div>

        {{-- secondary tabs (functional, informative) --}}
        @foreach([['readers','Lecteurs','Trois lecteurs fixes (Porte A, B, C) et deux lecteurs portatifs sont appairés au système.'],['encode','Encodage','Posez une carte vierge sur l\'encodeur puis utilisez « Encoder une carte » dans les actions rapides.'],['zones',"Zones d'accès","Pavillons Officiels, Zones Exposition, Salles de Conférence, Zones VIP, Backstage et Zones Techniques."],['params','Paramètres','Fréquence 13.56 MHz (HF) · MIFARE DESFire EV2 · AES-256 · NDEF + AES.']] as [$key,$title,$text])
        <div data-panel="{{ $key }}" data-tabs-for="rfidtabs" hidden>
            <div class="p-8 text-center max-w-[560px] mx-auto">
                <span class="w-12 h-12 rounded-xl bg-[#E2F3E8] flex items-center justify-center mx-auto mb-3"><i data-lucide="radio" class="w-6 h-6 text-[#157A43]"></i></span>
                <h3 class="text-[16px] font-bold text-[#131313]">{{ $title }}</h3>
                <p class="text-[12.5px] text-[#8A857A] mt-2 leading-relaxed">{{ $text }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ RIGHT RAIL ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Aperçu RFID</p>
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">Actif</span>
            </div>
            <div class="flex items-start gap-3">
                <span class="w-11 h-11 rounded-full bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="radio" class="w-5 h-5 text-[#157A43]"></i></span>
                <span>
                    <span class="block text-[13px] font-bold text-[#157A43]">SYSTÈME RFID ACTIF</span>
                    <span class="block text-[11px] text-[#8A857A]">Tous les systèmes fonctionnent normalement</span>
                </span>
            </div>
            <dl class="mt-3.5 space-y-2 text-[12px]">
                @foreach([['Fréquence','13.56 MHz (HF)'],['Technologie','MIFARE DESFire EV2'],['Sécurité','AES-256'],['Encodage','NDEF + AES']] as [$k,$v])
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#3B382F]">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <button data-toast="Système RFID testé : tous les lecteurs répondent (démo)" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="wifi" class="w-4 h-4"></i>Tester le système RFID</button>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Gestion des lecteurs</p>
                <button data-toast="Liste complète des lecteurs à venir…" class="text-[11px] font-semibold text-[#157A43] underline underline-offset-2">Voir tout</button>
            </div>
            <div class="space-y-3">
                @foreach([['Lecteur Porte A','Entrée Principale','27/07/2026 09:15:32'],['Lecteur Porte B','Entrée Secondaire','27/07/2026 09:14:21'],['Lecteur Porte C','Pavillon Centre','27/07/2026 09:13:11']] as [$n,$loc,$ts])
                <div class="flex items-start gap-2.5">
                    <span class="w-9 h-9 rounded-lg bg-[#F5F3EE] border border-[#EFEDE6] flex items-center justify-center shrink-0"><i data-lucide="smartphone" class="w-4 h-4 text-[#3B382F]"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="text-[12.5px] font-semibold text-[#131313] truncate">{{ $n }}</span>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-[#E8F5EC] text-[#157A43] shrink-0">En ligne</span>
                        </span>
                        <span class="block text-[11px] text-[#8A857A]">{{ $loc }}</span>
                        <span class="block text-[10.5px] text-[#B0AB9F]">Dernière activité : {{ $ts }}</span>
                    </span>
                    <button data-toast="Options du lecteur à venir…" class="w-6 h-6 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center shrink-0"><i data-lucide="more-vertical" class="w-3.5 h-3.5 text-[#8A857A]"></i></button>
                </div>
                @endforeach
            </div>
            <button data-toast="Appairage d'un nouveau lecteur à venir…" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un lecteur RFID</button>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Actions rapides</p>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([['plus-square','Encoder une carte','#157A43','#E8F5EC','#CFE8D8'],['ban','Désactiver une carte','#C0010C','#FDF3F3','#F5CFCF'],['refresh-ccw','Remplacer une carte','#C97A16','#FDF6EC','#F3DFC2'],['printer','Imprimer étiquette','#3565DE','#F0F5FE','#CBDCF8']] as [$ic,$lbl,$c,$bg,$bd])
                <button data-toast="{{ $lbl }} (démo)" class="rounded-lg border px-2.5 py-3 text-center hover:opacity-90" style="background:{{ $bg }};border-color:{{ $bd }}">
                    <i data-lucide="{{ $ic }}" class="w-4.5 h-4.5 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[11px] font-semibold leading-tight" style="color:{{ $c }}">{{ $lbl }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
