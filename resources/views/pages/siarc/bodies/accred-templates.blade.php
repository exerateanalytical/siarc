@php
    // ══ Badge Templates — verbatim from the approved design ══
    $accActive = 'Badge Templates'; $accArt = 'accred-art-templates.png';
    $accTop = ['fr' => true, 'logoRight' => true, 'avatar' => false];

    $stats = [
        ['file-check-2',  '#157A43', '#E2F3E8', '6',   'Templates actifs'],
        ['file-clock',    '#E6B201', '#FDF6DC', '2',   'Templates inactifs'],
        ['file-badge',    '#C0010C', '#FDE8E8', '4',   'Types de badges'],
        ['files',         '#3565DE', '#E8EFFB', '245', 'Badges générés'],
        ['printer',       '#0E8F83', '#DFF3F1', '18',  'Réimpressions'],
    ];
    $rows = [
        ['accred-tpl-1.png', 'Template Visiteur Standard', 'Template pour visiteurs généraux',           'Visiteur',    '#157A43', '#E2F3E8', 'user-round',   'Actif',   true,  '15 Mai 2026'],
        ['accred-tpl-2.png', 'Template Exposant',          'Template pour exposants',                    'Exposant',    '#C97A16', '#FDF3E0', 'users-round',  'Actif',   true,  '12 Mai 2026'],
        ['accred-tpl-3.png', 'Template VIP',               'Template pour invités VIP',                  'VIP',         '#C0010C', '#FDE8E8', 'heart',        'Actif',   true,  '10 Mai 2026'],
        ['accred-tpl-4.png', 'Template Intervenant',       'Template pour intervenants & conférenciers', 'Intervenant', '#7C4FE0', '#F0EAFB', 'mic',          'Actif',   true,  '08 Mai 2026'],
        ['accred-tpl-5.png', 'Template Presse',            'Template pour presse & médias',              'Presse',      '#3565DE', '#E8EFFB', 'newspaper',    'Inactif', false, '05 Mai 2026'],
        ['accred-tpl-6.png', 'Template Staff',             'Template pour staff & organisateurs',        'Staff',       '#3B382F', '#EFEDE6', 'briefcase',    'Inactif', false, '02 Mai 2026'],
    ];
    $previewHref = route('siarc.admin.accred.preview', ['lang' => $lang]);
@endphp

{{-- ══ TITLE ROW ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5">
    <div>
        <h1 class="text-[26px] font-bold text-[#131313] tracking-tight">Badge Templates</h1>
        <p class="text-[12px] text-[#8A857A] mt-1 flex items-center gap-1.5">Accréditation <i data-lucide="chevron-right" class="w-3 h-3"></i> <span class="text-[#3B382F]">Badge Templates</span></p>
    </div>
    <a href="{{ $previewHref }}" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg"><i data-lucide="plus" class="w-4 h-4"></i>Nouveau template</a>
</div>

{{-- ══ STAT CARDS ══ --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-5">
    @foreach($stats as [$icon,$col,$tile,$value,$label])
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow px-4 py-4 flex items-center gap-3.5">
        <span class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $col }}"></i></span>
        <span>
            <span class="block text-[20px] font-bold text-[#131313] leading-tight">{{ $value }}</span>
            <span class="block text-[11.5px] text-[#8A857A]">{{ $label }}</span>
        </span>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-[1fr_310px] gap-5 items-start">

    {{-- ══ LEFT: filters + table ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow overflow-hidden">
        <div class="px-5 py-4 flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[190px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#tplScope" placeholder="Rechercher un template..." class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] pl-9 pr-3 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            <div class="min-w-[130px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">Type de badge</label>
                <select data-filter-select="#tplScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    <option>Tous</option><option value="visiteur">Visiteur</option><option value="exposant">Exposant</option><option value="vip">VIP</option><option value="intervenant">Intervenant</option><option value="presse">Presse</option><option value="staff">Staff</option>
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-[11px] text-[#8A857A] mb-1">Statut</label>
                <select data-filter-select="#tplScope" class="w-full text-[12.5px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    <option>Tous</option><option value="actif">Actif</option><option value="inactif">Inactif</option>
                </select>
            </div>
            <button data-toast="Filtres avancés à venir…" class="siarc-btn text-[12.5px] text-[#3B382F] border border-[#EFEDE6] px-3.5 py-2.5 rounded-lg bg-white hover:border-[#D8E5DC] ml-auto"><i data-lucide="filter" class="w-4 h-4 text-[#8A857A]"></i>Filtres</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[760px]">
                <thead>
                    <tr class="bg-[#FBFAF6] border-y border-[#EFEDE6]">
                        @foreach(['APERÇU','NOM DU TEMPLATE','TYPE DE BADGE','FORMAT','DIMENSIONS','STATUT','CRÉÉ LE','ACTIONS'] as $h)
                        <th class="px-4 py-3 text-[10px] font-bold tracking-wide text-[#8A857A] uppercase {{ $loop->last ? 'text-right' : '' }}">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="tplScope">
                    @foreach($rows as [$thumb,$name,$sub,$type,$tCol,$tTile,$tIcon,$statut,$on,$date])
                    <tr data-filter-item data-filter-tags="{{ Str::lower($type) }} {{ Str::lower($statut) }}" data-filter-text="{{ $name }} {{ $sub }} {{ $type }} {{ $statut }}" class="border-b border-[#F2F1EC] hover:bg-[#FBFAF6] transition-colors">
                        <td class="px-4 py-2.5"><img src="{{ asset('images/siarc/'.$thumb) }}" alt="" class="w-[62px] h-[40px] object-cover rounded-md border border-[#EFEDE6]"></td>
                        <td class="px-4 py-2.5">
                            <p class="text-[13px] font-semibold text-[#131313] whitespace-nowrap">{{ $name }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $sub }}</p>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium" style="color:{{ $tCol }}">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background:{{ $tTile }}"><i data-lucide="{{ $tIcon }}" class="w-3.5 h-3.5" style="color:{{ $tCol }}"></i></span>{{ $type }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 text-[12px] text-[#3B382F]"><i data-lucide="smartphone" class="w-4 h-4 text-[#8A857A]"></i>Vertical</span></td>
                        <td class="px-4 py-2.5 text-[12px] text-[#3B382F] whitespace-nowrap">85.6 x 54 mm<br><span class="text-[#8A857A]">PVC</span></td>
                        <td class="px-4 py-2.5"><span class="text-[11px] font-semibold px-2.5 py-1 rounded-md {{ $on ? 'bg-[#E8F5EC] text-[#157A43]' : 'bg-[#F1F0EB] text-[#8A857A]' }}">{{ $statut }}</span></td>
                        <td class="px-4 py-2.5 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $date }}<br><span class="text-[11px] text-[#8A857A]">par Jude Nshome</span></td>
                        <td class="px-4 py-2.5">
                            <span class="flex items-center justify-end gap-1">
                                <a href="{{ $previewHref }}" title="Aperçu" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="eye" class="w-4 h-4 text-[#8A857A]"></i></a>
                                <button data-toast="Éditeur de template à venir…" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="pencil" class="w-4 h-4 text-[#8A857A]"></i></button>
                                <button data-toast="Template dupliqué (démo)" class="w-7 h-7 rounded-md hover:bg-[#F1F1EF] flex items-center justify-center"><i data-lucide="copy" class="w-4 h-4 text-[#8A857A]"></i></button>
                                <button data-toast="Suppression désactivée sur la démo" class="w-7 h-7 rounded-md hover:bg-[#FDE8E8] flex items-center justify-center"><i data-lucide="trash-2" class="w-4 h-4 text-[#C0010C]"></i></button>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div data-page="#tplScope" data-page-size="10" class="px-5 py-3.5 flex items-center justify-between border-t border-[#EFEDE6]">
            <p data-page-info class="text-[12px] text-[#8A857A]">Affichage 1 à 6 sur 6 templates</p>
            <span class="flex items-center gap-1.5">
                <button data-page-prev class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button data-page-num="1" class="is-active w-8 h-8 rounded-lg text-[12px] font-semibold flex items-center justify-center">1</button>
                <button data-page-next class="w-8 h-8 rounded-lg border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#F5F3EE]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
            </span>
        </div>
    </div>

    {{-- ══ RIGHT RAIL: template preview ══ --}}
    <div class="bg-white rounded-xl border border-[#EFEDE6] siarc-shadow p-5">
        <p class="text-[11px] font-bold tracking-[0.1em] text-[#3B382F] uppercase mb-4">Aperçu du template</p>
        <div class="flex justify-center mb-4">
            @include('pages.siarc.partials.accred-badge', ['bScale' => '196px'])
        </div>
        <h3 class="text-[14.5px] font-bold text-[#131313] mb-3">Template Visiteur Standard</h3>
        <dl class="space-y-2.5 text-[12px]">
            @foreach([['Type de badge','Visiteur','user-round','#157A43'],['Format','Vertical',null,null],['Dimensions','85.6 x 54 mm',null,null],['Matériau','PVC',null,null]] as [$k,$v,$ic,$icCol])
            <div class="flex items-center justify-between">
                <dt class="text-[#8A857A]">{{ $k }}</dt>
                <dd class="font-medium text-[#3B382F] flex items-center gap-1.5">@if($ic)<i data-lucide="{{ $ic }}" class="w-3.5 h-3.5" style="color:{{ $icCol }}"></i>@endif{{ $v }}</dd>
            </div>
            @endforeach
            <div class="flex items-center justify-between">
                <dt class="text-[#8A857A]">Couleurs</dt>
                <dd class="flex items-center gap-1.5">
                    @foreach(['#157A43','#E6B201','#C0010C','#3B382F'] as $c)<span class="w-4 h-4 rounded-full" style="background:{{ $c }}"></span>@endforeach
                </dd>
            </div>
        </dl>
        <p class="text-[11.5px] text-[#8A857A] mt-4 mb-2">Éléments inclus</p>
        <div class="flex flex-wrap gap-1.5">
            @foreach(['Photo','QR Code','Nom','Pays','Date'] as $chip)
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-[#157A43] bg-[#E8F5EC] border border-[#CFE8D8] rounded-md px-2 py-1"><i data-lucide="check-circle-2" class="w-3 h-3"></i>{{ $chip }}</span>
            @endforeach
        </div>
        <a href="{{ $previewHref }}" class="mt-5 w-full siarc-btn text-[12.5px] font-semibold text-[#157A43] border border-[#CFE8D8] rounded-lg px-3 py-2.5 justify-center hover:bg-[#F4FAF6]"><i data-lucide="eye" class="w-4 h-4"></i>Voir en taille réelle</a>
    </div>
</div>
