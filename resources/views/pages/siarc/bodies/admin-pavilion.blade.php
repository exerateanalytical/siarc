@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;

    // Navigational hrefs only (design content is transcribed verbatim below).
    $lang = $lang ?? 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;

    // Real ids so detail links never 404
    $eid       = siarcEvent()?->id ?? 0;
    $pavilionId = DB::table('pavilions')->where('event_id',$eid)->value('id');
    $standId    = DB::table('stands')->where('event_id',$eid)->value('id');

    $pavilionsHref = $h('siarc.admin.pavilions');
    $floorplanHref = $h('siarc.admin.floorplan');
    $standsHref    = $h('siarc.admin.stands');
    $exhibitorsHref= $h('siarc.admin.exhibitors');

    // "Modifier" → detail route (guard for null id)
    $editHref = ($pavilionId && $h('siarc.admin.pavilion', ['id'=>$pavilionId]))
        ? $h('siarc.admin.pavilion', ['id'=>$pavilionId])
        : $pavilionsHref;
    // "Ajouter un stand" → admin stands list
    $addHref  = $standsHref;
    // Back / "Voir toutes" → parent list
    $backHref = $pavilionsHref;

    // ── KPI tile row (verbatim from design) ────────────────────────────────────
    $kpis = [
        ['store',       '#157A43','#E2F3E8', 'Stands totaux',     '80',                 '100%', '#8A857A'],
        ['layout-grid', '#7C4FE0','#F0EAFB', 'Stands occupés',    '68',                 '85%',  '#8A857A'],
        ['grid-3x3',    '#157A43','#E2F3E8', 'Stands disponibles','12',                 '15%',  '#8A857A'],
        ['users-round', '#3565DE','#E8EFFB', 'Exposants',         '118',                'Dans ce pavillon', '#8A857A'],
    ];

    // Tab strip (Aperçu active; rest wrap distinct panels)
    $tabs = [
        ['apercu',    'Aperçu',            true],
        ['stands',    'Stands (80)',       false],
        ['exposants', 'Exposants (118)',   false],
        ['plan',      'Plan du pavillon',  false],
        ['equipements','Équipements',      false],
        ['services',  'Services',          false],
        ['documents', 'Documents',         false],
        ['activites', 'Activités',         false],
        ['historique','Historique',        false],
    ];

    // Floorplan stand cells — [code, status] status: occ|avail|reserved|maint
    // Row A (top strip)
    $planTop  = ['C-05','C-02','C-04','C-06','C-06','C-05'];
    // Category donut (Répartition par catégorie)
    $donut = [
        ['Institutionnel',       34, '28.8%', '#157A43'],
        ['Culture & Traditions', 26, '22.0%', '#C97A16'],
        ['Design & Innovation',  20, '16.9%', '#7C4FE0'],
        ['Artisanat d\'Art',     18, '15.3%', '#3565DE'],
        ['Autres',               20, '16.9%', '#9CA3AF'],
    ];

    // Services disponibles — two columns
    $servicesL = ['Sécurité 24/7','Connexion Wi-Fi haut débit','Climatisation centrale','Service de nettoyage'];
    $servicesR = ['Électricité (220V)','Espace de stockage','Espace VIP & Réunions','Restauration'];

    // Activités récentes
    $activities = [
        ['layout-grid', 'Stand C-15 attribué à "Art Bois Précieux"', null, 'Il y a 25 min'],
        ['banknote',    'Paiement reçu de "Tissages du Soleil"', '450,000 FCFA', 'Il y a 1 h'],
        ['file-text',   'Nouveau document ajouté : Règlement intérieur', null, 'Il y a 3 h'],
        ['calendar',    'Visite technique du pavillon programmée', '05 Juin 2026 - 10:00', 'Il y a 5 h'],
    ];
@endphp

<div class="siarc-in">

    {{-- ══ TOP: HEADER CARD (2/3) + STATUS PANEL (1/3) ══ --}}
    <div class="grid xl:grid-cols-[1fr_320px] gap-5 mb-5 items-start">

        {{-- ── HEADER CARD ── --}}
        <div class="siarc-card siarc-shadow p-6">
            <div class="grid lg:grid-cols-[150px_1fr_360px] gap-6 items-start">

                {{-- pavilion thumbnail photo --}}
                <div class="w-[150px] h-[150px] rounded-2xl overflow-hidden shrink-0 siarc-shadow">
                    <img src="{{ asset('images/siarc/pavilion-thumb.png') }}" alt="Pavillon Centre" class="w-full h-full object-cover">
                </div>

                {{-- identity --}}
                <div class="min-w-0">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">Pavillon Centre</h1>
                        <span class="inline-flex items-center gap-1 text-[11.5px] font-semibold px-2.5 py-1 rounded-full bg-[#E2F3E8] text-[#157A43]">Actif</span>
                    </div>

                    <p class="mt-2.5 text-[13px] text-[#55524A]">
                        <span class="font-semibold text-[#161513]">Code : P-01</span>
                        <span class="text-[#D8D4C9] mx-1.5">·</span>
                        <span class="text-[#55524A]">Pavillon Institutionnel</span>
                    </p>
                    <p class="mt-2 text-[13px] text-[#55524A] flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-siarc-ochre shrink-0"></i>
                        Hall Principal – Musée National de Yaoundé
                    </p>
                    <p class="mt-1.5 text-[13px] text-[#55524A] flex items-center gap-1.5">
                        <span class="text-[13px] leading-none">🇨🇲</span>
                        Cameroun
                    </p>
                    <p class="mt-3 text-[13px] leading-relaxed text-[#55524A]">
                        Pavillon principal dédié aux institutions publiques, ministères, organismes et programmes de promotion de l'artisanat.
                    </p>
                </div>

                {{-- banner photo --}}
                <div class="rounded-2xl overflow-hidden siarc-shadow min-h-[150px]">
                    <img src="{{ asset('images/siarc/pavilion-banner.png') }}" alt="Pavillon Centre" class="w-full h-full object-cover">
                </div>
            </div>

            {{-- metadata row --}}
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-5 gap-4">
                @foreach([
                    ['ruler',        'Étage',        'RDC'],
                    ['maximize',     'Superficie',   '2,400 m²'],
                    ['arrow-up',     'Hauteur',      '8 m'],
                    ['wind',         'Climatisation','Oui'],
                    ['users',        'Capacité',     '120 <span class="text-[10px] font-medium text-[#8A857A]">Exposants</span>'],
                ] as [$mi,$ml,$mv])
                <div class="flex items-start gap-2.5">
                    <i data-lucide="{{ $mi }}" class="w-[18px] h-[18px] text-[#8A857A] mt-0.5 shrink-0"></i>
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium text-[#8A857A]">{{ $ml }}</p>
                        <p class="text-[13.5px] font-bold text-[#161513] leading-tight mt-0.5">{!! $mv !!}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── STATUS PANEL + QUICK ACTIONS ── --}}
        <div class="siarc-card siarc-shadow p-5">
            <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Statut du pavillon</h3>
            <dl class="space-y-3.5 text-[13px]">
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">Statut</dt>
                    <dd><span class="inline-flex items-center text-[11.5px] font-semibold px-2.5 py-1 rounded-full bg-[#E2F3E8] text-[#157A43]">Actif</span></dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">Mis en service le</dt>
                    <dd class="font-semibold text-[#161513]">15 Mai 2026</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">Responsable</dt>
                    <dd class="font-semibold text-[#161513]">Marie Claire ABESSO</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">Téléphone</dt>
                    <dd class="font-semibold text-[#161513]">+237 6 98 76 54 32</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">Email</dt>
                    <dd class="font-semibold text-[#161513]">mc.abesso@siarc2026.cm</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-[#8A857A]">Dernière mise à jour</dt>
                    <dd class="font-semibold text-[#161513]">Il y a 2 heures</dd>
                </div>
            </dl>

            <p class="text-[13px] font-bold text-[#1A1712] mt-6 mb-3">Actions rapides</p>
            <div class="flex items-center gap-2">
                <a href="{{ $editHref }}" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2 flex-1 justify-center">
                    <i data-lucide="square-pen" class="w-4 h-4"></i>Modifier
                </a>
                <a href="{{ $addHref }}" class="siarc-btn siarc-btn-outline !border !border-[#E4E3DD] !text-[#3B382F] text-[12.5px] px-3.5 py-2 flex-1 justify-center hover:bg-[#FBFAF6]">
                    <i data-lucide="plus" class="w-4 h-4"></i>Ajouter un stand
                </a>
                <button type="button" data-toast="Plus d'actions bientôt disponibles" class="siarc-btn !border !border-[#E4E3DD] text-[#8A857A] px-2.5 py-2 hover:bg-[#FBFAF6]" aria-label="Plus d'actions">
                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══ KPI TILE ROW (6 cards) ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">
        @foreach($kpis as [$icon,$color,$tile,$label,$value,$sub,$subCol])
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $tile }}">
                <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">{{ $label }}</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $value }}</p>
            <p class="mt-1 text-[11px] font-semibold" style="color:{{ $subCol }}">{{ $sub }}</p>
        </div>
        @endforeach

        {{-- Revenus générés --}}
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FDF3E0">
                <i data-lucide="banknote" class="w-5 h-5" style="color:#E6B201"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">Revenus générés</p>
            <p class="text-[20px] font-extrabold text-[#161513] leading-tight tracking-tight">48,750,000 <span class="text-[12px]">FCFA</span></p>
            <p class="mt-1 text-[11px] font-semibold text-[#157A43] flex items-center gap-1"><i data-lucide="arrow-up" class="w-3 h-3"></i>18.4% <span class="text-[#8A857A] font-normal">vs dernier mois</span></p>
        </div>

        {{-- Taux d'occupation --}}
        <div class="siarc-card siarc-shadow p-4">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FDF3E0">
                <i data-lucide="activity" class="w-5 h-5" style="color:#C97A16"></i>
            </span>
            <p class="mt-3 text-[11.5px] text-[#8A857A] font-medium">Taux d'occupation</p>
            <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">85%</p>
            <p class="mt-1 text-[11px] font-semibold text-[#157A43] flex items-center gap-1"><i data-lucide="arrow-up" class="w-3 h-3"></i>12.6% <span class="text-[#8A857A] font-normal">vs dernier mois</span></p>
        </div>
    </div>

    {{-- ══ TAB STRIP ══ --}}
    <div data-tabs="pavilion" class="border-b border-[#ECEAE3] mb-5 overflow-x-auto">
        <div class="flex items-center gap-6 min-w-max">
            @foreach($tabs as [$tKey,$tLabel,$active])
            <button type="button" data-tab="{{ $tKey }}" class="si-tab relative pb-3 text-[13.5px] whitespace-nowrap transition-colors {{ $active ? 'is-active' : '' }}">
                {{ $tLabel }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Aperçu panel (default active) --}}
    <div data-panel="apercu" data-tabs-for="pavilion">
    {{-- ══ MAIN: FLOORPLAN + CATEGORY/SERVICES (2/3) + RIGHT COLUMN (1/3) ══ --}}
    <div class="grid xl:grid-cols-[1fr_360px] gap-5">

        {{-- ═══════════ LEFT: FLOORPLAN + CATEGORY/SERVICES ═══════════ --}}
        <div class="space-y-5">

            {{-- Plan du pavillon (floorplan) --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">Plan du pavillon</h3>
                    @if($floorplanHref)
                    <a href="{{ $floorplanHref }}" class="text-[11.5px] font-semibold text-siarc-green inline-flex items-center gap-1 hover:gap-1.5 transition-all">Plan interactif<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                    @endif
                </div>

                <div class="rounded-xl border border-[#E4E3DD] bg-[#FCFBF8] p-4">
                    <svg viewBox="0 0 620 340" class="w-full" font-family="Poppins, sans-serif">
                        {{-- exit top --}}
                        <rect x="255" y="8" width="34" height="20" rx="3" fill="#E2F3E8" stroke="#B9E2C7"/>
                        <text x="272" y="21" font-size="9" fill="#2E8B57" text-anchor="middle">🏃</text>
                        <text x="272" y="6" font-size="7.5" fill="#8A857A" text-anchor="middle">Sortie</text>
                        <text x="272" y="42" font-size="7.5" fill="#8A857A" text-anchor="middle" style="display:none">de secours</text>

                        {{-- outer walls --}}
                        <rect x="60" y="45" width="500" height="270" fill="none" stroke="#D8D4C9" stroke-width="1.5"/>

                        {{-- helper stand cell drawn via loops below --}}
                        @php
                            $occ = ['bg'=>'#E2F3E8','bd'=>'#B9E2C7','fg'=>'#0F4824'];
                            $ava = ['bg'=>'#FFFFFF','bd'=>'#E4E3DD','fg'=>'#8A857A'];
                            $res = ['bg'=>'#FDF3E0','bd'=>'#F3DFB0','fg'=>'#8A5E12'];
                            $mnt = ['bg'=>'#FDE8E8','bd'=>'#F3C4C4','fg'=>'#9A1B1B'];
                            $cell = function($x,$y,$w,$hh,$label,$t){
                                echo '<rect x="'.$x.'" y="'.$y.'" width="'.$w.'" height="'.$hh.'" rx="4" fill="'.$t['bg'].'" stroke="'.$t['bd'].'"/>';
                                echo '<text x="'.($x+$w/2).'" y="'.($y+$hh/2+3.5).'" font-size="10" font-weight="600" fill="'.$t['fg'].'" text-anchor="middle">'.$label.'</text>';
                            };
                        @endphp

                        {{-- Top row: C-03 C-02 C-04 C-06 C-06 C-05 + C-08 --}}
                        @php $x=118; foreach(['C-03','C-02','C-04','C-06','C-06','C-05'] as $lab){ $cell($x,58,54,32,$lab,$occ); $x+=59; } @endphp
                        @php $cell(478,58,64,32,'C-08',$occ); @endphp

                        {{-- Left column C-11..C-14 --}}
                        @php $y=104; foreach(['C-11','C-12','C-13','C-14'] as $lab){ $cell(66,$y,44,42,$lab,$occ); $y+=48; } @endphp

                        {{-- Middle block row 1: C-15..C-18 --}}
                        @php $x=170; foreach(['C-15','C-16','C-17','C-18'] as $lab){ $cell($x,110,54,34,$lab,$occ); $x+=59; } @endphp
                        {{-- Middle block row 2: C-19..C-22 --}}
                        @php $x=170; foreach(['C-19','C-20','C-21','C-22'] as $lab){ $cell($x,152,54,34,$lab,$occ); $x+=59; } @endphp

                        {{-- Right column C-29 C-30 C-32 --}}
                        @php $cell(460,110,60,34,'C-29',$occ); $cell(460,158,60,34,'C-30',$occ); $cell(460,214,60,34,'C-32',$occ); @endphp

                        {{-- Bottom row: C-23 C-24 C-26 C-27(maint) C-28 --}}
                        @php
                            $cell(170,214,54,34,'C-23',$occ);
                            $cell(229,214,54,34,'C-24',$occ);
                            $cell(288,214,54,34,'C-26',$occ);
                            $cell(347,214,54,34,'C-27',$mnt);
                            $cell(406,214,54,34,'C-28',$occ);
                        @endphp

                        {{-- exit bottom-left --}}
                        <rect x="118" y="270" width="34" height="20" rx="3" fill="#E2F3E8" stroke="#B9E2C7"/>
                        <text x="135" y="283" font-size="9" fill="#2E8B57" text-anchor="middle">🏃</text>
                        <text x="135" y="303" font-size="7.5" fill="#8A857A" text-anchor="middle">Sortie</text>

                        {{-- main entrance bottom-right --}}
                        <path d="M470 300 l0 -22 l-7 0 l10 -13 l10 13 l-7 0 l0 22 z" fill="#157A43"/>
                        <text x="473" y="316" font-size="7.5" fill="#55524A" text-anchor="middle">Entrée principale</text>
                    </svg>

                    {{-- legend --}}
                    <div class="flex items-center flex-wrap gap-x-6 gap-y-2 text-[11.5px] mt-3 pt-3 border-t border-[#EFEDE6]">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[4px]" style="background:#E2F3E8;border:1px solid #B9E2C7"></span><span class="text-[#3B382F] font-medium">Occupé</span></span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[4px]" style="background:#FFFFFF;border:1px solid #E4E3DD"></span><span class="text-[#3B382F] font-medium">Disponible</span></span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[4px]" style="background:#FDF3E0;border:1px solid #F3DFB0"></span><span class="text-[#3B382F] font-medium">Réservé</span></span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-[4px]" style="background:#FDE8E8;border:1px solid #F3C4C4"></span><span class="text-[#3B382F] font-medium">Maintenance</span></span>
                    </div>
                </div>
            </div>

            {{-- Répartition par catégorie + Services disponibles --}}
            <div class="siarc-card siarc-shadow p-5">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-5">Répartition par catégorie</h3>
                <div class="flex items-center gap-6 flex-wrap">
                    <div class="relative shrink-0">
                        <svg viewBox="0 0 140 140" class="w-[150px] h-[150px] -rotate-90">
                            @php $circ = 2*M_PI*54; $off = 0; @endphp
                            @foreach($donut as [$name,$cnt,$pctLabel,$col])
                                @php $pct = $cnt/118; $len = $circ*$pct; @endphp
                                <circle cx="70" cy="70" r="54" fill="none" stroke="{{ $col }}" stroke-width="18"
                                    stroke-dasharray="{{ round($len,1) }} {{ round($circ-$len,1) }}"
                                    stroke-dashoffset="{{ -round($off,1) }}"/>
                                @php $off += $len; @endphp
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-[24px] font-extrabold text-[#1A1712] leading-none">118</span>
                            <span class="text-[10px] text-[#8A857A] mt-1">Exposants</span>
                        </div>
                    </div>
                    <ul class="flex-1 min-w-[180px] space-y-2.5">
                        @foreach($donut as [$name,$cnt,$pctLabel,$col])
                        <li class="flex items-center gap-2 text-[12.5px]">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $col }}"></span>
                            <span class="text-[#3B382F] font-medium">{{ $name }}</span>
                            <span class="ml-auto text-[#55524A] whitespace-nowrap">{{ $cnt }} ({{ $pctLabel }})</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <h3 class="text-[14px] font-bold text-[#1A1712] mt-7 mb-4">Services disponibles</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3">
                    @foreach($servicesL as $svc)
                    <div class="flex items-center gap-2 text-[12.5px] text-[#3B382F]">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0"></i><span>{{ $svc }}</span>
                    </div>
                    @endforeach
                    @foreach($servicesR as $svc)
                    <div class="flex items-center gap-2 text-[12.5px] text-[#3B382F]">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0"></i><span>{{ $svc }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════ RIGHT COLUMN ═══════════ --}}
        <div class="space-y-5">

            {{-- Performance du pavillon (line chart) --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">Performance du pavillon</h3>
                    <button type="button" data-toast="Période : 30 derniers jours" class="text-[11px] font-medium text-[#8A857A] border border-[#EFEDE6] rounded-lg px-2.5 py-1 inline-flex items-center gap-1">
                        30 derniers jours<i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </button>
                </div>
                <svg viewBox="0 0 340 190" class="w-full">
                    {{-- y-axis labels --}}
                    <text x="6" y="20" font-size="8" fill="#B0AB9F">60M</text>
                    <text x="6" y="48" font-size="8" fill="#B0AB9F">50M</text>
                    <text x="6" y="76" font-size="8" fill="#B0AB9F">40M</text>
                    <text x="6" y="104" font-size="8" fill="#B0AB9F">30M</text>
                    <text x="6" y="132" font-size="8" fill="#B0AB9F">20M</text>
                    <text x="6" y="160" font-size="8" fill="#B0AB9F">10M</text>
                    <text x="10" y="14" font-size="8" fill="#B0AB9F">FCFA</text>
                    @foreach([16,44,72,100,128,156] as $gy)<line x1="34" y1="{{ $gy }}" x2="330" y2="{{ $gy }}" stroke="#F1F1EF"/>@endforeach

                    {{-- area + line: 06 Mai → 03 Juin --}}
                    <polygon points="44,150 44,150 92,152 130,110 168,120 206,132 244,90 282,100 320,44 320,168 44,168" fill="#157A43" opacity="0.07"/>
                    <polyline points="44,150 92,152 130,110 168,120 206,132 244,90 282,100 320,44" fill="none" stroke="#157A43" stroke-width="2.2" stroke-linejoin="round"/>
                    @foreach([[44,150],[92,152],[130,110],[168,120],[206,132],[244,90],[282,100]] as [$cx,$cy])<circle cx="{{ $cx }}" cy="{{ $cy }}" r="2.6" fill="#157A43"/>@endforeach

                    {{-- highlighted endpoint + tooltip --}}
                    <circle cx="320" cy="44" r="4" fill="#157A43"/>
                    <rect x="232" y="20" width="96" height="26" rx="5" fill="#0B3A1E"/>
                    <text x="280" y="31" font-size="7.5" fill="#fff" text-anchor="middle" font-weight="600">48,750,000 FCFA</text>
                    <text x="280" y="41" font-size="7" fill="#B9E2C7" text-anchor="middle">03 Juin 2026</text>

                    {{-- x-axis labels --}}
                    @foreach(['06 Mai','13 Mai','20 Mai','27 Mai','03 Juin'] as $i=>$d)<text x="{{ 44+$i*69 }}" y="184" font-size="7.5" fill="#B0AB9F" text-anchor="middle">{{ $d }}</text>@endforeach
                </svg>
            </div>

            {{-- Activités récentes --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">Activités récentes</h3>
                    @if($backHref)
                    <a href="{{ $backHref }}" class="text-[11.5px] font-semibold text-siarc-green inline-flex items-center gap-1 hover:gap-1.5 transition-all">Voir toutes<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                    @endif
                </div>
                <ul class="space-y-4">
                    @foreach($activities as [$icon,$title,$meta,$time])
                    <li class="flex items-start gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#F5F3EE] flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="{{ $icon }}" class="w-4 h-4 text-[#8A857A]"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] text-[#161513] leading-snug">{{ $title }}</p>
                            @if($meta)<p class="text-[11.5px] text-[#8A857A] mt-0.5">{{ $meta }}</p>@endif
                        </div>
                        <span class="text-[11px] text-[#B0AB9F] whitespace-nowrap shrink-0 mt-0.5">{{ $time }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    </div>{{-- /apercu panel --}}

    {{-- Secondary tab panels (honest placeholders linking to the real list/detail pages) --}}
    <div data-panel="stands" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Stands du pavillon</p>
            <p class="mb-4">80 stands sont rattachés à ce pavillon.</p>
            @if($standsHref)<a href="{{ $standsHref }}" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2 inline-flex"><i data-lucide="store" class="w-4 h-4"></i>Gérer les stands</a>@endif
        </div>
    </div>

    <div data-panel="exposants" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Exposants du pavillon</p>
            <p class="mb-4">118 exposants présents dans ce pavillon.</p>
            @if($exhibitorsHref)<a href="{{ $exhibitorsHref }}{{ $pavilionId ? '?pavilion='.$pavilionId : '' }}" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2 inline-flex"><i data-lucide="users-round" class="w-4 h-4"></i>Voir les exposants</a>@endif
        </div>
    </div>

    <div data-panel="plan" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Plan du pavillon</p>
            <p class="mb-4">Consultez le plan interactif du salon pour visualiser l'implantation.</p>
            @if($floorplanHref)<a href="{{ $floorplanHref }}" class="siarc-btn siarc-btn-green text-[12.5px] px-3.5 py-2 inline-flex"><i data-lucide="layout-grid" class="w-4 h-4"></i>Plan interactif</a>@endif
        </div>
    </div>

    <div data-panel="equipements" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Équipements</p>
            <p>La liste des équipements du pavillon sera affichée ici.</p>
        </div>
    </div>

    <div data-panel="services" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Services</p>
            <p>Les services disponibles sont listés dans l'onglet Aperçu.</p>
        </div>
    </div>

    <div data-panel="documents" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Documents</p>
            <p>Aucun document n'est disponible pour le moment.</p>
        </div>
    </div>

    <div data-panel="activites" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Activités</p>
            <p>Les activités récentes sont affichées dans l'onglet Aperçu.</p>
        </div>
    </div>

    <div data-panel="historique" data-tabs-for="pavilion" hidden>
        <div class="siarc-card siarc-shadow p-6 text-[13px] text-[#55524A]">
            <p class="font-semibold text-[#161513] mb-2">Historique</p>
            <p>L'historique des modifications du pavillon sera affiché ici.</p>
        </div>
    </div>

</div>
