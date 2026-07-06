@php
    // ══ Reprint History — verbatim from the approved design ══
    $stats = [
        ['file-check-2', '#157A43', '#E2F3E8', '582',   'Réimpressions totales', 'depuis le début'],
        ['user-round',   '#3565DE', '#E8EFFB', '245',   'Visiteurs concernés',   'uniques'],
        ['calendar',     '#C97A16', '#FDF3E0', '46',    'Réimpressions',         'ce mois'],
        ['printer',      '#7C4FE0', '#F0EAFB', '98.6%', 'Taux de succès',        'ce mois'],
        ['alert-triangle','#C0010C','#FDE8E8', '3',     'Échecs',                'ce mois'],
    ];
    // [date, time, name, email, badgeId, uid, type, tCol, tTile, reason, reasonSub, by, byRole, ok, okNote, face]
    $rows = [
        ['27/07/2026','11:32:18','Jean Paul Essomba','jean.essomba@minart.gov.cm','VIS-000356','04 A3 B2 7F 91 6E 80','Visiteur','#157A43','#E8F5EC','Perte de badge','Badge perdu','Marie Anguissa','Admin',true,null,1],
        ['27/07/2026','10:45:33','Marie Anguissa','marie.anguissa@craft.cm','EXP-000089','04 A3 B2 7F 91 6E 81','Exposant','#C97A16','#FDF3E0','Dommage','Badge abîmé','David Esono','Admin',true,null,2],
        ['27/07/2026','09:14:21','Alain Mbarga','alain.mbarga@company.com','VIS-000412','04 A3 B2 7F 91 6E 82','Visiteur','#157A43','#E8F5EC',"Erreur d'impression",'Mauvaise photo','Samuel Ndongo','Staff',true,null,3],
        ['26/07/2026','17:45:10','Brenda Tcham','brenda.tcham@press.cm','PRE-000078','04 A3 B2 7F 91 6E 83','Presse','#3565DE','#E8EFFB','Perte de badge','Badge perdu','Marie Anguissa','Admin',true,null,4],
        ['26/07/2026','15:22:08','David Esono','david.esono@organization.org','INT-000034','04 A3 B2 7F 91 6E 84','Intervenant','#7C4FE0','#F0EAFB','Dommage','Badge abîmé','Samuel Ndongo','Staff',true,null,5],
        ['26/07/2026','11:05:54','Samuel Ndongo','samuel.ndongo@staff.cm','STF-00122','04 A3 B2 7F 91 6E 85','Staff','#3B382F','#EFEDE6','Mise à jour photo','Photo mise à jour','David Esono','Admin',true,null,6],
        ['25/07/2026','16:30:11','Priska Nguimatsia','priska.nguimatsia@ngo.cm','VOL-000056','04 A3 B2 7F 91 6E 86','Bénévole','#0E8F83','#DFF3F1',"Erreur d'impression",'Données incorrectes','Marie Anguissa','Admin',false,'Imprimante hors ligne',7],
        ['25/07/2026','09:06:18','Joseph Fokam','joseph.fokam@security.cm','SEC-000031','04 A3 B2 7F 91 6E 87','Sécurité','#C0010C','#FDE8E8','Perte de badge','Badge perdu','Samuel Ndongo','Staff',true,null,8],
    ];
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="mb-5">
    <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">Reprint History</h1>
    <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Reprint History</span></p>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$value,$label,$sub])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-center gap-3.5">
        <span class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[18px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11px] text-[#8A857A] leading-snug">{{ $label }}<br><span class="text-[#B0AB9F]">{{ $sub }}</span></span>
        </span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_300px] gap-5 items-start">

    {{-- ══ LEFT: filters + table ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[190px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#rpScope" placeholder="Rechercher (nom, badge ID, RFID, email...)" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            <div class="min-w-[190px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">Date</label>
                <span class="flex items-center justify-between gap-2 text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">01/06/2026 - 31/07/2026<i data-lucide="chevrons-up-down" class="w-3.5 h-3.5 text-[#B0AB9F]"></i></span>
            </div>
            @foreach([['Type de badge',['visiteur','exposant','presse','intervenant','staff','bénévole','sécurité']],['Raison',['perte de badge','dommage','erreur d\'impression','mise à jour photo']],['Statut',['succès','échec']]] as [$flabel,$opts])
            <div class="min-w-[110px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $flabel }}</label>
                <select data-filter-select="#rpScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    <option>Tous</option>
                    @foreach($opts as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach
                </select>
            </div>
            @endforeach
            <button data-toast="Filtres avancés à venir…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[860px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['DATE / HEURE','VISITEUR','BADGE ID / RFID','TYPE DE BADGE','RAISON','IMPRIMÉ PAR','STATUT','ACTIONS'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last ? 'text-right' : '' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="rpScope">
                    @foreach($rows as [$date,$time,$name,$email,$bid,$uid,$type,$tCol,$tTile,$reason,$rSub,$by,$byRole,$ok,$okNote,$face])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($type) }} {{ Str::lower($reason) }} {{ $ok ? 'succès' : 'échec' }}" data-filter-text="{{ $name }} {{ $email }} {{ $bid }} {{ $uid }} {{ $type }} {{ $reason }} {{ $by }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors" style="box-shadow:inset 3px 0 0 {{ $ok ? '#157A43' : '#C0010C' }}">
                        <td class="px-4 py-3 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $date }}<br><span class="text-[11px] text-[#8A857A]">{{ $time }}</span></td>
                        <td class="px-4 py-3">
                            <span class="flex items-center gap-2.5">
                                <img src="{{ asset('images/siarc/accred-face-'.$face.'.png') }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                <span><span class="block text-[13px] font-semibold text-[#131313]">{{ $name }}</span><span class="block text-[11px] text-[#8A857A]">{{ $email }}</span></span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-[12px] font-medium text-[#3B382F] whitespace-nowrap">{{ $bid }}<br><span class="text-[11px] font-normal text-[#8A857A]">{{ $uid }}</span></td>
                        <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md" style="color:{{ $tCol }};background:{{ $tTile }}">{{ $type }}</span></td>
                        <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $reason }}<br><span class="text-[11px] text-[#8A857A]">{{ $rSub }}</span></td>
                        <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $by }}<br><span class="text-[11px] text-[#8A857A]">{{ $byRole }}</span></td>
                        <td class="px-4 py-3">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md {{ $ok ? 'bg-[#E8F5EC] text-[#157A43]' : 'bg-[#FDE8E8] text-[#C0010C]' }}">{{ $ok ? 'Succès' : 'Échec' }}</span>
                            @if($okNote)<span class="block mt-0.5 text-[10.5px] text-[#8A857A]">{{ $okNote }}</span>@endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button data-toast="Détail « {{ $bid }} » — voir le panneau de droite" class="w-7 h-7 rounded-md border border-[#EFEDE6] hover:bg-[#F1F1EF] inline-flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div data-page="#rpScope" data-page-size="10" class="px-5 py-3.5 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[12px] text-[#8A857A]">Affichage de 1 à 8 sur 582 réimpressions</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button data-page-num="1" class="is-active w-8 h-8 rounded-lg text-[12px] font-semibold flex items-center justify-center">1</button>
                @foreach(['2','3'] as $p)<button data-page-num="{{ $p }}" class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] font-semibold text-[#3B382F] flex items-center justify-center hover:bg-[#F5F3EE]">{{ $p }}</button>@endforeach
                <span class="text-[12px] text-[#8A857A] px-1">…</span>
                <button data-page-num="73" class="w-8 h-8 rounded-lg border border-[#EFEDE6] text-[12px] font-semibold text-[#3B382F] flex items-center justify-center hover:bg-[#F5F3EE]">73</button>
                <button data-page-next class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                <span class="ml-2 text-[12px] text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1.5 flex items-center gap-1">10 / page<i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></span>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT RAIL: reprint detail ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Détail de la réimpression</p>
            <button data-toast="Panneau fermé (démo)" class="w-6 h-6 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="x" class="w-4 h-4 text-[#8A857A]"></i></button>
        </div>
        <div class="flex justify-center mb-4">
            @include('pages.siarc.partials.accred-badge', ['bScale' => '188px'])
        </div>
        <dl class="space-y-2.5 text-[12px]">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[#8A857A]">Badge ID</dt>
                <dd class="font-semibold text-[#131313] flex items-center gap-1.5">VIS-000356<button data-toast="Badge ID copié (démo)" class="w-5 h-5 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="copy" class="w-3 h-3 text-[#8A857A]"></i></button></dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-[#8A857A]">RFID (UID)</dt>
                <dd class="font-semibold text-[#131313] flex items-center gap-1.5 text-[11px]">04 A3 B2 7F 91 6E 80<button data-toast="UID copié (démo)" class="w-5 h-5 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="copy" class="w-3 h-3 text-[#8A857A]"></i></button></dd>
            </div>
            @foreach([['Type de badge','Visiteur'],['Date de réimpression','27/07/2026 11:32:18']] as [$k,$v])
            <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313]">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A]">Raison</dt><dd class="font-semibold text-[#131313] text-right">Perte de badge<br><span class="text-[11px] font-normal text-[#8A857A]">Badge perdu</span></dd></div>
            @foreach([['Imprimé par','Marie Anguissa (Admin)'],['Imprimante','Zebra ZD420 - Pavillon Officiel']] as [$k,$v])
            <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#131313] text-right">{{ $v }}</dd></div>
            @endforeach
            <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">Statut</dt><dd><span class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-[#157A43]"><span class="w-2 h-2 rounded-full bg-[#157A43]"></span>Succès</span></dd></div>
            <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">Notes</dt><dd class="text-[#8A857A]">Aucune note</dd></div>
        </dl>
        <button data-toast="Badge ajouté à la file d'impression (démo)" class="mt-4 w-full siarc-btn justify-center text-[13px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 hover:bg-[#F4FAF6]"><i data-lucide="printer" class="w-4 h-4"></i>Réimprimer ce badge</button>
        <button data-toast="Historique complet du visiteur à venir…" class="mt-2 w-full siarc-btn justify-center text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="history" class="w-4 h-4 text-[#8A857A]"></i>Voir l'historique complet du visiteur</button>
    </div>
</div>
