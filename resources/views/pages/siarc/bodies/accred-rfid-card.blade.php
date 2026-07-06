@php
    // ══ RFID Card Detail — verbatim from the approved design ══
    $listHref = route('siarc.admin.accred.rfid', ['lang' => $lang]);
    // The design's reference card; a real UID in the URL is displayed spaced.
    $uid = strtoupper(trim(chunk_split(preg_replace('/[^0-9a-f]/i', '', $rfidUid ?? '04A3B27F916E80'), 2, ' ')));
    $uid = $uid !== '' ? $uid : '04 A3 B2 7F 91 6E 80';
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">RFID Card Detail</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <a href="{{ $listHref }}" class="hover:text-[#3B382F]">RFID Support</a> <i data-lucide="chevron-right" class="w-3 h-3"></i> Cartes RFID <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Détail</span></p>
    </div>
    <div class="flex items-center gap-2.5">
        <a href="{{ $listHref }}" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="arrow-left" class="w-4 h-4 text-[#8A857A]"></i>Retour à la liste</a>
        <button data-toast="Impression du dossier de carte (démo)" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="printer" class="w-4 h-4 text-[#8A857A]"></i>Imprimer</button>
        <button data-toast="Menu d'actions à venir…" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg">Actions<i data-lucide="chevron-down" class="w-4 h-4"></i></button>
    </div>
</div>

{{-- ══ SUMMARY BAND ══ --}}
<div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-5 py-4 mb-5">
    <div class="flex flex-wrap items-start gap-x-8 gap-y-4">
        <span class="flex items-start gap-3 min-w-[240px]">
            <span class="w-11 h-11 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="radio" class="w-5 h-5 text-[#157A43]"></i></span>
            <span>
                <span class="block text-[10px] font-bold tracking-wide text-[#8A857A] uppercase">ID Carte RFID</span>
                <span class="flex items-center gap-2 text-[16px] font-bold text-[#131313]">{{ $uid }}<button data-toast="UID copié (démo)" class="w-6 h-6 rounded hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="copy" class="w-3.5 h-3.5 text-[#8A857A]"></i></button></span>
                <span class="block text-[10.5px] text-[#8A857A]">Hex: {{ str_replace(' ', '', $uid) }} · Décimal: 784623456789120</span>
            </span>
        </span>
        @foreach([['STATUT',null],['TYPE DE BADGE',null],['PAVILLON / ZONE','Pavillon Officiel|PO-001'],["NIVEAU D'ACCÈS",null],["DATE D'ÉMISSION",'27/07/2026 09:15:32|'],['DERNIÈRE LECTURE','27/07/2026 11:32:18|']] as [$k,$v])
        <span>
            <span class="block text-[10px] font-bold tracking-wide text-[#8A857A] uppercase mb-1">{{ $k }}</span>
            @if($k === 'STATUT')
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">Active</span>
            @elseif($k === 'TYPE DE BADGE')
                <span class="inline-flex items-center gap-1.5 text-[12.5px] font-medium text-[#157A43]"><span class="w-5 h-5 rounded-full bg-[#E2F3E8] flex items-center justify-center"><i data-lucide="user-round" class="w-3 h-3 text-[#157A43]"></i></span>Visiteur</span>
            @elseif($k === "NIVEAU D'ACCÈS")
                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-md bg-[#F0EAFB] text-[#7C4FE0]">Standard</span>
            @else
                @php [$l1,$l2] = explode('|', $v); @endphp
                <span class="block text-[12.5px] font-semibold text-[#131313]">{{ $l1 }}</span>
                @if($l2)<span class="block text-[11px] text-[#8A857A]">{{ $l2 }}</span>@endif
                @if($k === 'DERNIÈRE LECTURE')<span class="mt-1 inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md bg-[#E8F5EC] text-[#157A43]">En ligne</span>@endif
            @endif
        </span>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_1fr_360px] gap-5 items-start">

    {{-- ══ COL 1: holder + read history ══ --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Titulaire de la carte</p>
            <div class="flex items-start gap-4">
                <img src="{{ asset('images/siarc/accred-holder.png') }}" alt="" class="w-[96px] h-[112px] rounded-lg object-cover border border-[#EFEDE6]">
                <div class="min-w-0">
                    <p class="flex items-center gap-2 text-[15px] font-bold text-[#131313]">Jean Paul Essomba <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-[#E8F5EC] text-[#157A43] inline-flex items-center gap-1"><i data-lucide="gem" class="w-2.5 h-2.5"></i>VIP</span></p>
                    <p class="text-[12px] text-[#8A857A]">Ministère des Arts et de la Culture</p>
                    <p class="mt-2 flex items-center gap-1.5 text-[12px] text-[#3B382F]"><i data-lucide="mail" class="w-3.5 h-3.5 text-[#8A857A]"></i>jean.essomba@minart.gov.cm</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[12px] text-[#3B382F]"><i data-lucide="phone" class="w-3.5 h-3.5 text-[#8A857A]"></i>+237 6 78 90 12 34</p>
                    <p class="mt-1 flex items-center gap-1.5 text-[12px] text-[#3B382F]"><span class="inline-block w-[16px] h-[10px] rounded-[1px]" style="background:linear-gradient(90deg,#157A43 33%,#C0010C 33% 66%,#E6B201 66%)"></span>Cameroun</p>
                    <p class="mt-2 text-[12px] text-[#8A857A]">ID Visiteur: <span class="font-semibold text-[#3B382F]">VIS-000356</span></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
            <p class="px-5 pt-5 text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase">Historique des lectures</p>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                            @foreach(['DATE / HEURE','LECTEUR','ZONE / ACCÈS','TYPE','RÉSULTAT'] as $h)
                            <th class="px-4 py-2.5 text-[9.5px] font-bold tracking-wide text-[#8A857A] uppercase">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['27/07/2026 11:32:18','PORTE-A-01','Entrée Principale','Pavillon Officiel','Entrée Porte A','Entrée','#157A43','#E8F5EC'],
                            ['27/07/2026 10:45:33','PAV-PO-01','Pavillon Officiel','Zone Exposition','Allée 3','Accès','#3565DE','#E8EFFB'],
                            ['27/07/2026 09:40:12','SALLE-CONF-1','Salle Conférence A','Conférence A',"Session d'ouverture",'Entrée','#157A43','#E8F5EC'],
                            ['27/07/2026 09:15:32','PORTE-A-01','Entrée Principale','Pavillon Officiel','Entrée Porte A','Entrée','#157A43','#E8F5EC'],
                        ] as [$dt,$reader,$rSub,$zone,$zSub,$type,$tc,$tb])
                        <tr class="border-b border-[#F2F1EC]">
                            <td class="px-4 py-2.5 text-[11.5px] text-[#3B382F]">{{ $dt }}<br><span class="text-[10.5px] text-[#8A857A]">1</span></td>
                            <td class="px-4 py-2.5 text-[11.5px] text-[#3B382F]">{{ $reader }}<br><span class="text-[10.5px] text-[#8A857A]">{{ $rSub }}</span></td>
                            <td class="px-4 py-2.5 text-[11.5px] text-[#3B382F]">{{ $zone }}<br><span class="text-[10.5px] text-[#8A857A]">{{ $zSub }}</span></td>
                            <td class="px-4 py-2.5"><span class="text-[10px] font-semibold px-2 py-0.5 rounded-md" style="color:{{ $tc }};background:{{ $tb }}">{{ $type }}</span></td>
                            <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#157A43]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>Autorisé</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 flex items-center justify-between">
                <p class="text-[11.5px] text-[#8A857A]">Affichage de 1 à 4 sur 4 lectures</p>
                <button data-toast="Historique complet à venir…" class="siarc-btn text-[11.5px] font-semibold text-[#3B382F] border border-[#EFEDE6] px-3 py-1.5 rounded-lg hover:bg-[#FBFAF6]"><i data-lucide="history" class="w-3.5 h-3.5 text-[#8A857A]"></i>Voir tout l'historique</button>
            </div>
        </div>
    </div>

    {{-- ══ COL 2: RFID info + access control ══ --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Informations RFID</p>
            <dl class="space-y-2.5 text-[12px]">
                @foreach([['Technologie','MIFARE DESFire EV2'],['UID (Hex)',$uid],['UID (Décimal)','784623456789120'],['Mémoire','8 KB'],['Fréquence','13.56 MHz (HF)'],['Encodage','NDEF + AES']] as [$k,$v])
                <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#3B382F] text-right">{{ $v }}</dd></div>
                @endforeach
                <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">Clé d'application</dt><dd><span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-md bg-[#E8EFFB] text-[#3565DE]">AES-256</span></dd></div>
                @foreach([['Numéro de série IC','04 88 9F 23 11 07 80'],['Fabricant de la puce','NXP Semiconductors'],["Date d'encodage",'27/07/2026 09:10:21'],['Encodé par','Système SIARC 2026']] as [$k,$v])
                <div class="flex items-center justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#3B382F] text-right">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Contrôle d'accès</p>
            <dl class="space-y-3 text-[12px]">
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Niveau d'accès</dt><dd class="font-medium text-[#3B382F]">Standard</dd></div>
                <div>
                    <dt class="text-[#8A857A] mb-1.5">Zones autorisées</dt>
                    <dd class="flex flex-wrap gap-1.5">
                        @foreach(['Pavillons Officiels','Zones Exposition','Salles de Conférence'] as $z)
                        <span class="text-[10.5px] font-semibold px-2 py-1 rounded-md bg-[#E8F5EC] text-[#157A43]">{{ $z }}</span>
                        @endforeach
                    </dd>
                </div>
                <div>
                    <dt class="text-[#8A857A] mb-1.5">Zones restreintes</dt>
                    <dd class="flex flex-wrap gap-1.5">
                        @foreach(['Zones VIP','Backstage','Zones Techniques'] as $z)
                        <span class="text-[10.5px] font-semibold px-2 py-1 rounded-md bg-[#FDE8E8] text-[#C0010C]">{{ $z }}</span>
                        @endforeach
                    </dd>
                </div>
                @foreach([['Plages horaires','07:00 - 20:00'],['Validité','27/07/2026 - 05/08/2026']] as [$k,$v])
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-medium text-[#3B382F]">{{ $v }}</dd></div>
                @endforeach
                <div class="flex items-center justify-between"><dt class="text-[#8A857A]">Statut actuel</dt><dd><span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#157A43]"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>Actif</span></dd></div>
            </dl>
        </div>
    </div>

    {{-- ══ COL 3: card preview + actions ══ --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Aperçu de la carte</p>
            <div class="flex justify-center">
                @include('pages.siarc.partials.accred-badge', ['bScale' => '236px'])
            </div>
            <div class="flex flex-wrap items-center gap-1.5 mt-3">
                <span class="text-[10.5px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#EFEDE6] rounded-md px-2 py-1">Format: 85.6 x 54 mm</span>
                <span class="text-[10.5px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#EFEDE6] rounded-md px-2 py-1">Technologie: MIFARE DESFire EV2</span>
            </div>
            <a href="{{ route('siarc.admin.accred.preview', ['lang' => $lang]) }}" class="mt-3.5 w-full siarc-btn justify-center text-[13px] font-semibold text-[#3B382F] border border-[#EFEDE6] rounded-lg px-3 py-2.5 hover:bg-[#FBFAF6]"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i>Voir le badge complet</a>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Actions RFID</p>
            <div class="grid grid-cols-2 gap-2.5">
                @foreach([['ban','Désactiver la carte','#C0010C','#FDF3F3','#F5CFCF'],['refresh-ccw','Remplacer la carte','#C97A16','#FDF6EC','#F3DFC2'],['cpu','Réencoder la carte','#3565DE','#F0F5FE','#CBDCF8'],['scan-line','Test de lecture','#157A43','#F4FAF6','#CFE8D8'],['copy','Cloner (réservé)','#7C4FE0','#F7F3FE','#E2D6F9'],['trash-2','Supprimer','#C0010C','#FDF3F3','#F5CFCF']] as [$ic,$lbl,$c,$bg,$bd])
                <button data-toast="{{ $lbl }} — action réservée à la production (démo)" class="rounded-lg border px-2.5 py-2.5 text-center hover:opacity-90" style="background:{{ $bg }};border-color:{{ $bd }}">
                    <i data-lucide="{{ $ic }}" class="w-4 h-4 mx-auto mb-1" style="color:{{ $c }}"></i>
                    <span class="block text-[10.5px] font-semibold leading-tight" style="color:{{ $c }}">{{ $lbl }}</span>
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>
