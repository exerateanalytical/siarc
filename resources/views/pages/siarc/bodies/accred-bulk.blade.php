@php
    // ══ Bulk Printing — verbatim from the approved design ══

    // [name, sub, type, tCol, tTile, tIcon, entity, entitySub, statut, sOn, badgeId, checked, face]
    $rows = [
        ['Jean Paul Essomba', 'Ministère des Arts et de la Culture', 'VIP',         '#C0010C', '#FDE8E8', 'heart',       'Pavillon Officiel', 'Stand PO-001',    'Approuvé',   true,  'VIP-00024', true,  1],
        ['Marie Anguissa',    'Délégation MINAC',                    'VIP',         '#C0010C', '#FDE8E8', 'heart',       'Pavillon Officiel', 'Stand PO-002',    'Approuvé',   true,  'VIP-00025', true,  2],
        ['Alain Mbarga',      'Entreprise MB Solutions',             'Exposant',    '#C97A16', '#FDF3E0', 'users-round', 'Pavillon Centre',   'Stand C-045',     'Approuvé',   true,  'EXP-00126', true,  3],
        ['David Esono',       'Artisan - Sculptures Bois',           'Visiteur',    '#157A43', '#E2F3E8', 'user-round',  'Pavillon Est',      'Stand E-112',     'Approuvé',   true,  'VIS-00356', true,  4],
        ['Brenda Tcham',      'Journaliste - CRTV',                  'Presse',      '#3565DE', '#E8EFFB', 'newspaper',   'Centre Médias',     'Accred CM-12',    'Approuvé',   true,  'PRE-00078', true,  5],
        ['Joseph Fokam',      'Délégation Région Sud-Ouest',         'VIP',         '#C0010C', '#FDE8E8', 'heart',       'Pavillon Régions',  'Stand R-SO-07',   'Approuvé',   true,  'VIP-00089', true,  6],
        ['Prisca Nguimatsia', "Designer - Artisanat d'Art",          'Exposant',    '#C97A16', '#FDF3E0', 'users-round', 'Pavillon Art & Décor','Stand AD-021',  'En attente', false, 'EXP-00210', false, 7],
        ['Samuel Ndongo',     'Intervenant - Conférencier',          'Intervenant', '#7C4FE0', '#F0EAFB', 'mic',         'Salle Conférence A','Session SC-A-03', 'Approuvé',   true,  'INT-00034', false, 8],
    ];
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">Bulk Printing</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> Impression <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Bulk Printing</span></p>
    </div>
    <div class="flex items-center gap-2.5">
        <button data-toast="Export de la sélection en préparation…" class="siarc-btn text-[13px] text-[#3B382F] border border-[#EFEDE6] px-4 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="download" class="w-4 h-4 text-[#8A857A]"></i>Exporter la liste</button>
        <button data-toast="Étape 2 — Configuration (démo)" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg">Suivant<i data-lucide="arrow-right" class="w-4 h-4"></i></button>
    </div>
</div>

{{-- ══ STEPPER ══ --}}
<div class="flex flex-wrap items-center gap-2 mb-6">
    @foreach([['1','Sélection','Choisir badges',true],['2','Configuration',"Paramètres d'impression",false],['3','Aperçu','Vérification',false],['4','Impression',"Lancer l'impression",false]] as [$n,$t,$s,$on])
    <span class="flex items-center gap-2.5 {{ $loop->first ? '' : 'ml-1' }}">
        <span class="w-9 h-9 rounded-full flex items-center justify-center text-[13px] font-bold {{ $on ? 'bg-[#157A43] text-white' : 'bg-white border border-[#E6E3DB] text-[#8A857A]' }}">{{ $n }}</span>
        <span class="leading-tight">
            <span class="block text-[13px] font-semibold {{ $on ? 'text-[#131313]' : 'text-[#8A857A]' }}">{{ $t }}</span>
            <span class="block text-[10.5px] text-[#B0AB9F]">{{ $s }}</span>
        </span>
    </span>
    @unless($loop->last)<span class="hidden md:block flex-1 min-w-[40px] h-px bg-[#E6E3DB] mx-2"></span>@endunless
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_320px] gap-5 items-start">

    {{-- ══ LEFT: selection card ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden" id="accScope">
        <p class="px-5 pt-5 text-[12px] font-bold tracking-[0.08em] text-[#131313] uppercase">Sélectionner les badges à imprimer</p>
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            @foreach([['Type de badge','Tous',['vip','exposant','visiteur','presse','intervenant']],['Statut','Approuvés',['approuvé','en attente']],['Pavillon','Tous',['pavillon officiel','pavillon centre','pavillon est','pavillon régions','pavillon art & décor']],['Catégorie','Toutes',[]]] as [$flabel,$fall,$opts])
            <div class="min-w-[125px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $flabel }}</label>
                <select data-filter-select="#bpScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    <option>{{ $fall }}</option>
                    @foreach($opts as $o)<option value="{{ $o }}">{{ Str::title($o) }}</option>@endforeach
                </select>
            </div>
            @endforeach
            <div class="relative flex-1 min-w-[170px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#bpScope" placeholder="Rechercher un visiteur, exposant..." class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            <button data-toast="Filtres avancés à venir…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC]"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
        </div>

        <div class="mx-5 mb-4 rounded-lg bg-[#EAF6EE] border border-[#CFE8D8] px-4 py-3 flex items-center justify-between gap-3">
            <span>
                <span class="block text-[13px] font-bold text-[#157A43]">250 badges sélectionnés</span>
                <span class="block text-[11.5px] text-[#3B382F]">Vous pouvez sélectionner jusqu'à 500 badges par impression.</span>
            </span>
            <button data-toast="Sélection complète appliquée (démo)" class="inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#157A43]">Tout sélectionner<i data-lucide="check-square" class="w-4 h-4"></i></button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[780px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        <th class="px-4 py-3 w-10"><i data-lucide="check-square" class="w-4 h-4 text-[#157A43]"></i></th>
                        @foreach(['NOM','TYPE DE BADGE','ENTITÉ / PAVILLON','STATUT','ID BADGE','SÉLECTION'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last ? 'text-right' : '' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="bpScope">
                    @foreach($rows as [$name,$sub,$type,$tCol,$tTile,$tIcon,$entity,$eSub,$statut,$sOn,$bid,$checked,$face])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($type) }} {{ Str::lower($statut) }} {{ Str::lower($entity) }}" data-filter-text="{{ $name }} {{ $sub }} {{ $type }} {{ $entity }} {{ $bid }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-4 py-3"><i data-lucide="{{ $checked ? 'check-square' : 'square' }}" class="w-4 h-4 {{ $checked ? 'text-[#157A43]' : 'text-[#C9C5BA]' }}"></i></td>
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
                        <td class="px-4 py-3 text-[12px] text-[#3B382F]">{{ $entity }}<br><span class="text-[11px] text-[#8A857A]">{{ $eSub }}</span></td>
                        <td class="px-4 py-3"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md {{ $sOn ? 'bg-[#E8F5EC] text-[#157A43]' : 'bg-[#FDF6DC] text-[#C97A16]' }}">{{ $statut }}</span></td>
                        <td class="px-4 py-3 text-[12px] font-medium text-[#3B382F]">{{ $bid }}</td>
                        <td class="px-4 py-3 text-right"><i data-lucide="{{ $checked ? 'check-circle-2' : 'minus-circle' }}" class="w-5 h-5 inline {{ $checked ? 'text-[#157A43]' : 'text-[#C9C5BA]' }}"></i></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div data-page="#bpScope" data-page-size="10" class="px-5 py-3.5 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[12px] text-[#8A857A]">Affichage 1 à 8 sur 250 badges sélectionnés</p>
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

    {{-- ══ RIGHT RAIL: print summary ══ --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Résumé de l'impression</p>
            <dl class="space-y-2.5 text-[12px]">
                @foreach([['Nombre de badges sélectionnés','250'],['Type de badges','VIP, Exposant, Visiteur, Presse'],['Imprimante','Zebra ZC350 (Imprimante 01)'],['Format','Vertical (85.6 x 54 mm)'],['Matériau','PVC'],['Copies par badge','1']] as [$k,$v])
                <div class="flex items-start justify-between gap-3"><dt class="text-[#8A857A]">{{ $k }}</dt><dd class="font-semibold text-[#3B382F] text-right">{{ $v }}</dd></div>
                @endforeach
            </dl>
            <div class="mt-4 rounded-lg bg-[#FDF9EA] border border-[#F3E5B2] px-3.5 py-3 flex items-start gap-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-[#C97A16] shrink-0 mt-0.5"></i>
                <p class="text-[11.5px] text-[#7A5E00] leading-snug">Assurez-vous que le stock de cartes et le ruban sont suffisants avant de lancer l'impression.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-3">Aperçu du badge</p>
            <div class="flex justify-center">
                @include('pages.siarc.partials.accred-badge', ['bVariant' => 'green', 'bVip' => true, 'bOrg' => 'MINISTÈRE DES ARTS ET DE LA CULTURE', 'bId' => 'VIP-00024', 'bScale' => '212px'])
            </div>
            <div class="flex flex-wrap items-center gap-1.5 mt-3">
                @foreach(['85.6 x 54 mm','PVC','Vertical'] as $chip)
                <span class="text-[11px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#EFEDE6] rounded-md px-2 py-1">{{ $chip }}</span>
                @endforeach
                <a href="{{ route('siarc.admin.accred.templates', ['lang' => $lang]) }}" class="text-[11px] font-semibold text-[#157A43] ml-auto underline underline-offset-2">Voir le template</a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
            <p class="flex items-center gap-2 text-[12px] font-semibold text-[#3B382F]"><i data-lucide="info" class="w-4 h-4 text-[#157A43]"></i>Stock disponible</p>
            <div class="mt-2 flex items-center justify-between text-[12px]">
                <span class="text-[#3B382F] font-medium">1 250 cartes PVC</span>
                <span class="text-[#8A857A]">78%</span>
            </div>
            <span class="mt-1.5 block h-[7px] rounded-full bg-[#EFEDE6] overflow-hidden"><span class="block h-full rounded-full bg-[#157A43]" style="width:78%"></span></span>
        </div>
    </div>
</div>
