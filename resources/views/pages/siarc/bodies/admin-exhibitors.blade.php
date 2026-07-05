@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';
    $exhUrl = $h('siarc.admin.exhibitors');

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
        ['Tous les exposants','842', true],
        ['Confirmés','715', false],
        ['En attente','47', false],
        ["En liste d'attente",'32', false],
        ['Annulés','8', false],
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
            <div class="flex flex-wrap items-center gap-6 flex-1 min-w-0">
                @foreach($tabs as [$t,$n,$active])
                <button class="relative pb-3 text-[13px] font-semibold transition-colors {{ $active ? 'text-siarc-green' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                    {{ $t }} ({{ $n }})
                    @if($active)<span class="absolute left-0 -bottom-px h-[2.5px] w-full rounded-full bg-siarc-green"></span>@endif
                </button>
                @endforeach
            </div>
            <div class="flex items-center gap-2.5 pb-3">
                <button class="siarc-btn text-[12.5px] px-3.5 py-2 border border-[#E1DED5] text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="download" class="w-4 h-4"></i>Exporter</button>
                <a href="{{ $exhUrl }}" class="siarc-btn siarc-btn-green text-[12.5px] px-4 py-2"><i data-lucide="plus" class="w-4 h-4"></i>Ajouter un exposant</a>
            </div>
        </div>

        {{-- Search + filters --}}
        <div class="flex flex-wrap items-end gap-3 px-5 py-4">
            <label class="relative flex-1 min-w-[220px]">
                <i data-lucide="search" class="w-4 h-4 text-[#B0AB9F] absolute left-3 top-1/2 -translate-y-1/2"></i>
                <input type="text" placeholder="Rechercher un exposant, entreprise, pays..." class="w-full h-[44px] rounded-xl border border-[#ECEAE3] bg-[#FBFAF7] pl-9 pr-3 text-[13px] text-[#3B382F] placeholder-[#B0AB9F] focus:outline-none focus:border-[#D8E5DC] focus:bg-white">
            </label>
            @foreach(['Pavillon','Catégorie','Pays','Statut'] as $f)
            <div class="min-w-[130px]">
                <p class="text-[10.5px] font-semibold text-[#8A857A] mb-1.5">{{ $f }}</p>
                <button class="w-full h-[44px] flex items-center justify-between gap-2 rounded-xl border border-[#ECEAE3] bg-white px-3 text-[13px] text-[#3B382F] hover:border-[#D8E5DC]">
                    Tous <i data-lucide="chevron-down" class="w-4 h-4 text-[#B0AB9F]"></i>
                </button>
            </div>
            @endforeach
            <button class="h-[44px] siarc-btn text-[12.5px] px-3.5 border border-[#ECEAE3] text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="filter" class="w-4 h-4"></i>Filtres</button>
            <button class="h-[44px] siarc-btn text-[12.5px] px-3.5 text-siarc-green hover:bg-[#E2F3E8] rounded-xl"><i data-lucide="rotate-cw" class="w-4 h-4"></i>Réinitialiser</button>
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
                <tbody>
                    @foreach($rows as $r)
                    @php
                        [$img,$name,$contact,$org,$pays,$pav,$stand,$cat,$stLabel,$stTone,$date] = $r;
                        [$bBg,$bFg] = $toneMap[$stTone] ?? $toneMap['grey'];
                        $flag = $flags[$pays] ?? $flagCM;
                    @endphp
                    <tr class="border-b border-[#F4F2EC] hover:bg-[#FBFAF6] transition-colors group">
                        <td class="pl-5 py-3.5"><span class="w-4 h-4 inline-block rounded border border-[#D8D5CC] align-middle group-hover:border-siarc-green"></span></td>
                        {{-- Exposant (image + name + contact) --}}
                        <td class="px-3 py-3.5">
                            <a href="{{ $exhUrl }}" class="flex items-center gap-3">
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
                                <a href="{{ $exhUrl }}" title="Voir" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-siarc-green hover:bg-[#E2F3E8]"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="{{ $exhUrl }}" title="Modifier" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-siarc-ochre hover:bg-[#FDF3E0]"><i data-lucide="square-pen" class="w-4 h-4"></i></a>
                                <button title="Plus" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:text-[#3B382F] hover:bg-[#F1F1EF]"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-t border-[#F1EFE8]">
            <p class="text-[12px] text-[#8A857A]">Affichage de 1 à 10 sur <span class="font-semibold text-[#3B382F]">842</span> exposants</p>
            <div class="flex items-center gap-1.5">
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#CFC9C0] hover:bg-[#FBFAF6]"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                <button class="w-8 h-8 rounded-lg bg-siarc-green text-white text-[12.5px] font-semibold">1</button>
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">2</button>
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">3</button>
                <span class="px-1 text-[13px] text-[#B0AB9F]">...</span>
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">85</button>
                <button class="w-8 h-8 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#3B382F] hover:bg-[#FBFAF6]"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
            </div>
            <div class="flex items-center gap-2 text-[12px] text-[#8A857A]">
                <span>Lignes par page</span>
                <button class="h-8 flex items-center gap-1.5 rounded-lg border border-[#ECEAE3] px-2.5 text-[#3B382F] hover:bg-[#FBFAF6]">10 <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></button>
            </div>
        </div>
    </div>

</section>
