@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';
    $exhUrl = $h('siarc.admin.exhibitors');

    // ── Real detail id (guard 404: fall back to list route when null) ───────────
    $eid = siarcEvent()?->id ?? 0;
    $exhibitorId = DB::table('event_exhibitors')->where('event_id', $eid)->value('id');
    $floorUrl = $h('siarc.admin.floorplan');
    $detailUrl = ($exhibitorId && R::has('siarc.admin.exhibitor'))
        ? route('siarc.admin.exhibitor', ['lang'=>$lang, 'id'=>$exhibitorId])
        : $exhUrl;

    // ── KPI row (approved design figures — verbatim) ───────────────────────────
    $kpis = [
        ['users-round',    '#157A43','#E2F3E8','Total Exposants',        '842',        '↑ 18% vs dernier mois', true],
        ['tag',            '#C97A16','#FDF3E0','Nouveaux Exposants',     '128',        '↑ 24% vs dernier mois', true],
        ['clipboard-check','#3565DE','#E8EFFB','Exposants confirmés',    '715',        '85% du total',          false],
        ['clock',          '#7C4FE0','#F0EAFB','En attente de validation','47',        '5.6% du total',         false],
        ['ticket',         '#C97A16','#FDF3E0','Pavillons occupés',      '78%',        '↑ 12% vs dernier mois', true],
        ['banknote',       '#157A43','#E2F3E8','Revenus générés',        '128,450,000','↑ 22% vs dernier mois', true],
    ];

    // ── Tabs ───────────────────────────────────────────────────────────────────
    $tabs = [
        ['all',       'Tous les exposants','842'],
        ['confirmed', 'Confirmés','715'],
        ['pending',   'En attente','47'],
        ['waitlist',  "En liste d'attente",'32'],
        ['cancelled', 'Annulés','8'],
    ];

    // ── Country flag SVGs (inline) ─────────────────────────────────────────────
    $flagCM = '<svg viewBox="0 0 30 20" class="w-[22px] h-[15px] rounded-[2px] shrink-0"><rect width="10" height="20" fill="#007A5E"/><rect x="10" width="10" height="20" fill="#CE1126"/><rect x="20" width="10" height="20" fill="#FCD116"/><path d="M15 7.2l.9 2.7h2.8l-2.3 1.7.9 2.7-2.3-1.7-2.3 1.7.9-2.7-2.3-1.7h2.8z" fill="#FCD116"/></svg>';
    $flagCI = '<svg viewBox="0 0 30 20" class="w-[22px] h-[15px] rounded-[2px] shrink-0"><rect width="10" height="20" fill="#F77F00"/><rect x="10" width="10" height="20" fill="#fff"/><rect x="20" width="10" height="20" fill="#009E60"/></svg>';
    $flagSN = '<svg viewBox="0 0 30 20" class="w-[22px] h-[15px] rounded-[2px] shrink-0"><rect width="10" height="20" fill="#00853F"/><rect x="10" width="10" height="20" fill="#FDEF42"/><rect x="20" width="10" height="20" fill="#E31B23"/><path d="M15 7.2l.9 2.7h2.8l-2.3 1.7.9 2.7-2.3-1.7-2.3 1.7.9-2.7-2.3-1.7h2.8z" fill="#00853F"/></svg>';
    $flags = ['Cameroun'=>$flagCM,'Côte d\'Ivoire'=>$flagCI,'Sénégal'=>$flagSN];

    // ── Table rows (approved design content — verbatim) ────────────────────────
    // [img, name, contact, entreprise, pays, pavillon, stand, catégorie, statut-label, statut-tone, date]
    $rows = [
        [1,'Art Bois Précieux','Paul Tchameni','Art Bois Précieux SARL','Cameroun','Pavillon Centre','C-12','Bois & Sculpture','Confirmé','green','15 Mai 2026'],
        [2,'Tissages du Soleil','Marie Claire Abena','Tissages du Soleil',"Côte d'Ivoire",'Pavillon Ouest','O-08','Textiles & Tissus','Confirmé','green','18 Mai 2026'],
        [3,'Poteries de Maroua','Issa Abdou','Poteries de Maroua','Cameroun','Pavillon Nord','N-15','Poterie & Céramique','Confirmé','green','20 Mai 2026'],
        [4,"Bijoux d'Afrique",'Awa Diarra',"Bijoux d'Afrique",'Sénégal','Pavillon Sud','S-21','Bijouterie & Métal','En attente','gold','22 Mai 2026'],
        [5,'Cuir Excellence','Michel Koffi','Cuir Excellence SARL','Cameroun','Pavillon Centre','C-05','Cuir & Peaux','Confirmé','green','23 Mai 2026'],
        [6,'Vannerie du Littoral','Rose Ekomo','Vannerie du Littoral','Cameroun','Pavillon Est','E-11','Vannerie & Bambou','En attente','gold','24 Mai 2026'],
        [7,'Peintures Bana','Joseph Bana','Peintures Bana','Cameroun','Pavillon Nord','N-03','Arts & Traditions','Confirmé','green','25 Mai 2026'],
        [8,'Mode & Traditions','Grace Etoundi','Mode & Traditions','Cameroun','Pavillon Ouest','O-17','Mode & Accessoires','En attente','gold','26 Mai 2026'],
        [9,'Nature & Bien-être','François Mvondo','Nature & Bien-être SARL','Cameroun','Pavillon Sud','S-09','Cosmétiques naturels','Confirmé','green','27 Mai 2026'],
        [10,'Art Numérique 237','Boris Nguimatsia','Art Numérique 237','Cameroun','Pavillon Innovation','I-02','Art numérique & Design',"En liste d'attente",'blue','28 Mai 2026'],
    ];
    $toneMap = [
        'green' => ['#E2F3E8','#157A43'],
        'gold'  => ['#FDF3E0','#C97A16'],
        'blue'  => ['#E8EFFB','#3565DE'],
        'grey'  => ['#F1F1EF','#8A857A'],
    ];
@endphp

<section class="siarc-in">

    {{-- ══ PAGE HEADING ══ --}}
    <div class="mb-5">
        <h1 class="font-display text-[27px] font-extrabold text-[#161513] leading-tight">Gestion des Exposants</h1>
        <p class="text-[13px] text-[#8A857A] mt-1">Gérez les exposants, leurs informations, pavillons et stands.</p>
    </div>

    {{-- ══ KPI CARDS ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
        @foreach($kpis as [$icon,$color,$tile,$label,$val,$sub,$up])
        <div class="siarc-card siarc-shadow p-4">
            <div class="flex items-start justify-between">
                <p class="text-[11.5px] text-[#8A857A] font-medium pr-2">{{ $label }}</p>
                <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-[18px] h-[18px]" style="color:{{ $color }}"></i></span>
            </div>
            <p class="mt-2 text-[24px] font-extrabold text-[#161513] leading-none tracking-tight">{{ $val }}@if($label==='Revenus générés')<span class="text-[11px] font-semibold text-[#8A857A] tracking-normal"> FCFA</span>@endif</p>
            <div class="mt-2.5">
                <span class="inline-flex items-center gap-1 text-[11px] font-semibold {{ $up ? 'text-siarc-green' : 'text-[#8A857A]' }}">
                    @if($up)<i data-lucide="arrow-up" class="w-3 h-3"></i>@endif{{ $sub }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ MAIN CARD ══ --}}
    <div class="siarc-card siarc-shadow overflow-hidden">

        {{-- Tabs + primary actions --}}
        <div class="flex flex-wrap items-center gap-3 px-5 pt-4 border-b border-[#F1EFE8]">
            <div data-tabs="exhStatus" class="flex flex-wrap items-center gap-6 flex-1 min-w-0">
                @foreach($tabs as [$key,$t,$n])
                <button class="si-tab relative pb-3 text-[13px] font-semibold transition-colors {{ $loop->first ? 'is-active' : '' }}" data-tab="{{ $key }}">
                    {{ $t }} ({{ $n }})
                </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2.5 pb-3">
                <button data-toast="Export en préparation…" class="siarc-btn text-[12.5px] px-3.5 py-2 border border-[#E1DED5] text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="download" class="w-4 h-4"></i>Exporter</button>
                <a href="{{ $exhUrl }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un exposant</a>
            </div>
        </div>

        {{-- Tab panels: first tab shows the full working table; others honest placeholders --}}
        <div data-panel="all" data-tabs-for="exhStatus">

        {{-- Search + filters --}}
        <div class="flex flex-wrap items-end gap-3 px-5 py-4">
            <label class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" data-filter="#exhTable" placeholder="Rechercher un exposant, entreprise, pays..." class="w-full h-[44px] rounded-xl border border-[#ECEAE3] bg-[#FBFAF7] pl-9 pr-3 text-[13px] text-[#3B382F] placeholder-[#B0AB9F] focus:outline-none focus:border-[#D8E5DC] focus:bg-white">
            </label>
            @foreach(['Pavillon','Catégorie','Pays','Statut'] as $f)
            <div class="min-w-[130px]">
                <p class="text-[10.5px] font-semibold text-[#8A857A] mb-1.5">{{ $f }}</p>
                <button data-toast="Filtre {{ $f }} — bientôt disponible" class="w-full h-[44px] flex items-center justify-between gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[13px] text-[#3B382F] hover:border-[#D8E5DC]">
                    Tous <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
                </button>
            </div>
            @endforeach
            <button data-toast="Filtres avancés — bientôt disponible" class="h-[44px] siarc-btn text-[12.5px] px-3.5 border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="filter" class="w-4 h-4"></i>Filtres</button>
            <button data-toast="Filtres réinitialisés" class="h-[44px] siarc-btn text-[12.5px] px-3.5 text-siarc-green hover:bg-[#E2F3E8] rounded-xl"><i data-lucide="rotate-cw" class="w-4 h-4"></i>Réinitialiser</button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1080px] text-left">
                <thead>
                    <tr class="border-t border-b border-[#F1EFE8] bg-[#FBFAF7]">
                        <th class="w-10 pl-5 py-3"><span class="w-4 h-4 inline-block rounded border border-[#D8D5CC] align-middle"></span></th>
                        @foreach(['Exposant','Entreprise / Organisation','Pays','Pavillon / Stand','Catégorie principale','Statut',"Date d'inscription"] as $c)
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide uppercase text-[#8A857A] whitespace-nowrap">{{ $c }}</th>
                        @endforeach
                        <th class="px-3 py-3 text-[10.5px] font-bold tracking-wide uppercase text-[#8A857A] whitespace-nowrap text-right pr-5">Actions</th>
                    </tr>
                </thead>
                <tbody id="exhTable">
                    @foreach($rows as $r)
                    @php
                        [$img,$name,$contact,$org,$pays,$pav,$stand,$cat,$stLabel,$stTone,$date] = $r;
                        [$bBg,$bFg] = $toneMap[$stTone] ?? $toneMap['grey'];
                        $flag = $flags[$pays] ?? $flagCM;
                    @endphp
                    <tr data-filter-item data-filter-text="{{ $name }} {{ $contact }} {{ $org }} {{ $pays }} {{ $pav }} {{ $stand }} {{ $cat }} {{ $stLabel }}" class="border-b border-[#F4F2EC] hover:bg-[#FBFAF6] transition-colors group">
                        <td class="pl-5 py-3.5"><span class="w-4 h-4 inline-block rounded border border-[#D8D5CC] align-middle group-hover:border-siarc-green"></span></td>
                        {{-- Exposant (image + name + contact) --}}
                        <td class="px-3 py-3.5">
                            <a href="{{ $detailUrl }}" class="flex items-center gap-3">
                                <img src="{{ asset('images/siarc/exh-'.$img.'.png') }}" alt="" class="w-9 h-9 rounded-lg object-cover shrink-0">
                                <span class="min-w-0">
                                    <span class="block text-[13px] font-semibold text-[#161513] leading-tight group-hover:text-siarc-green">{{ $name }}</span>
                                    <span class="block text-[11.5px] text-[#8A857A] leading-tight mt-0.5">{{ $contact }}</span>
                                </span>
                            </a>
                        </td>
                        {{-- Entreprise / Organisation --}}
                        <td class="px-3 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $org }}</td>
                        {{-- Pays --}}
                        <td class="px-3 py-3.5">
                            <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F] whitespace-nowrap">{!! $flag !!}{{ $pays }}</span>
                        </td>
                        {{-- Pavillon / Stand --}}
                        <td class="px-3 py-3.5 whitespace-nowrap">
                            <span class="block text-[12.5px] text-[#3B382F] leading-tight">{{ $pav }}</span>
                            <span class="block text-[12px] text-[#8A857A] leading-tight mt-0.5">{{ $stand }}</span>
                        </td>
                        {{-- Catégorie principale --}}
                        <td class="px-3 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $cat }}</td>
                        {{-- Statut --}}
                        <td class="px-3 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11.5px] font-semibold" style="background:{{ $bBg }};color:{{ $bFg }}">{{ $stLabel }}</span>
                        </td>
                        {{-- Date d'inscription --}}
                        <td class="px-3 py-3.5 text-[12.5px] text-[#3B382F] whitespace-nowrap">{{ $date }}</td>
                        {{-- Actions --}}
                        <td class="px-3 py-3.5 pr-5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ $detailUrl }}" title="Voir" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-siarc-green hover:bg-[#E2F3E8]"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="{{ $detailUrl }}" title="Modifier" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-siarc-ochre hover:bg-[#FDF3E0]"><i data-lucide="square-pen" class="w-4 h-4"></i></a>
                                <button data-toast="Plus d'actions — bientôt disponible" title="Plus" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-[#3B382F] hover:bg-[#F1F1EF]"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div data-page="#exhTable" data-page-size="10" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-[#F1EFE8]">
            <p class="text-[12px] text-[#8A857A]"><span data-page-info>Affichage de 1 à 10 sur <span class="font-semibold text-[#3B382F]">842</span> exposants</span></p>
            <div class="flex items-center gap-1.5">
                <button data-page-prev class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#CFC9C0] hover:bg-[#FBFAF6]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button data-page-num="1" class="w-8 h-8 rounded-lg bg-siarc-green text-white text-[12.5px] font-semibold">1</button>
                <button data-page-num="2" class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">2</button>
                <button data-page-num="3" class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">3</button>
                <span class="px-1 text-[13px] text-[#B0AB9F]">...</span>
                <button data-page-num="85" class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">85</button>
                <button data-page-next class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
            </div>
            <div class="flex items-center gap-2 text-[12px] text-[#8A857A]">
                <span>Lignes par page</span>
                <button data-toast="Modifier le nombre de lignes — bientôt disponible" class="h-8 flex items-center gap-1.5 rounded-lg border border-[#ECEAE3] px-2.5 text-[#3B382F] hover:bg-[#FBFAF6]">10 <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
            </div>
        </div>

        </div>{{-- /panel all --}}

        {{-- Placeholder panels for the remaining status tabs --}}
        @foreach($tabs as [$key,$t,$n])
        @if(!$loop->first)
        <div data-panel="{{ $key }}" data-tabs-for="exhStatus" hidden>
            <div class="px-5 py-14 text-center">
                <span class="mx-auto w-12 h-12 rounded-xl bg-[#F1F1EF] flex items-center justify-center mb-3"><i data-lucide="filter" class="w-5 h-5 text-[#8A857A]"></i></span>
                <p class="text-[14px] font-semibold text-[#161513]">{{ $t }} ({{ $n }})</p>
                <p class="text-[12.5px] text-[#8A857A] mt-1 mb-4">Filtrez la liste complète pour retrouver ces exposants.</p>
                <a href="{{ $exhUrl }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2 inline-flex"><i data-lucide="list" class="w-4 h-4"></i>Voir tous les exposants</a>
            </div>
        </div>
        @endif
        @endforeach

    </div>

</section>
