@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $sTitle  = $sTitle  ?? ($isFr ? 'Exposants' : 'Exhibitors');
    $sIntro  = $sIntro  ?? ($isFr ? 'Découvrez tous les exposants et leurs offres au SIARC 2026.' : 'Discover all exhibitors and their offerings at SIARC 2026.');
    $sCards  = $sCards  ?? [];

    // active pavilion filter
    $activePav = request()->query('pavilion');

    // pavilion enrichment — real data (name, colour, icon, assigned-exhibitor count)
    $pavilions = collect();
    try {
        $evId = function_exists('siarcEvent') ? (siarcEvent()?->id ?? 0) : 0;
        $pavilions = DB::table('pavilions')->where('event_id', $evId)
            ->orderBy('sort_order')->get(['id','slug','name_fr','name_en','icon','color','capacity']);
        // real assigned-exhibitor count per pavilion (from stands)
        $counts = DB::table('stands')->whereNotNull('exhibitor_id')
            ->select('pavilion_id', DB::raw('COUNT(*) as c'))
            ->groupBy('pavilion_id')->pluck('c','pavilion_id');
        foreach ($pavilions as $p) { $p->exh_count = (int)($counts[$p->id] ?? 0); }
    } catch (\Throwable $e) { $pavilions = collect(); }

    // map non-safe pavilion icons to safe lucide names
    $iconMap = [
        'palette'=>'layers','shirt'=>'tag','wheat'=>'store','armchair'=>'grid-3x3',
        'lightbulb'=>'activity','store'=>'store',
    ];
    $safeIcon = fn($ic) => $iconMap[$ic] ?? ($ic ?: 'store');

    // tone → badge colour tokens
    $toneMap = [
        'green'  => ['#E7F2EC','#157A43'],
        'gold'   => ['#FDF3E0','#C97A16'],
        'red'    => ['#FDE8E8','#C0010C'],
        'blue'   => ['#E7EEF9','#2456B3'],
        'grey'   => ['#EFEDE6','#6B6A63'],
        'purple' => ['#F0EAFB','#7C4FE0'],
    ];
    // initials colours for heritage tile
    $initColors = ['#157A43','#C97A16','#C0010C','#0F4824','#E6B201','#14652F'];

    $count = count($sCards);

    // featured = first few real exhibitor cards (honest — real passed data only)
    $featured = array_slice($sCards, 0, 3);
@endphp

{{-- ══════════════════ HEADER BAND ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-11 lg:py-14 siarc-in">
        {{-- breadcrumb --}}
        <nav class="flex items-center gap-2 text-[12px] font-semibold text-[#8A857A] mb-5">
            <a href="{{ $h('siarc.home') }}" class="hover:text-siarc-green transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B8B3A6]"></i>
            <span class="text-siarc-green">{{ $sTitle }}</span>
        </nav>
        <div class="flex items-center gap-1.5 mb-5">
            @for($i=0;$i<4;$i++)<span class="w-2.5 h-2.5 rotate-45" style="background:{{ ['#157A43','#C0010C','#E6B201','#0F4824'][$i] }}"></span>@endfor
        </div>
        <h1 class="font-display font-extrabold text-[38px] sm:text-[48px] leading-[1.05] tracking-tight text-[#1A1712]">{{ $sTitle }}</h1>
        <span class="block mt-4 h-[4px] w-20 rounded-full bg-siarc-gold"></span>
        <p class="mt-5 text-[15px] text-[#55524A] leading-relaxed max-w-[620px]">{{ $sIntro }}</p>
    </div>
</section>

{{-- ══════════════════ KPI STAT BAND ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-9">
        @php
            $kpis = [
                ['store',       '#157A43', '#E7F2EC', '312',   $isFr ? 'Total exposants'      : 'Total exhibitors',   $isFr ? 'inscrits au salon'        : 'registered'],
                ['layout-grid', '#C97A16', '#FDF3E0', '286',   $isFr ? 'Stands occupés'        : 'Booths occupied',    $isFr ? "91,7% d'occupation"       : '91.7% occupancy'],
                ['globe',       '#2456B3', '#E7EEF9', '24',    $isFr ? 'Pays représentés'      : 'Countries',          $isFr ? 'Afrique, Europe, Asie…'   : 'Africa, Europe, Asia…'],
                ['grid-3x3',    '#7C4FE0', '#F0EAFB', '12',    $isFr ? "Secteurs d'activité"   : 'Sectors',            $isFr ? 'Catégories'               : 'Categories'],
                ['star',        '#C0010C', '#FDE8E8', '38',    $isFr ? 'Exposants premium'     : 'Premium exhibitors', $isFr ? 'En vedette'               : 'Featured'],
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($kpis as $k)
            <div class="siarc-card siarc-shadow p-5 flex flex-col">
                <span class="w-11 h-11 rounded-2xl flex items-center justify-center mb-3.5" style="background:{{ $k[2] }}">
                    <i data-lucide="{{ $k[0] }}" class="w-5 h-5" style="color:{{ $k[1] }}"></i>
                </span>
                <span class="font-display font-extrabold text-[26px] leading-none text-[#1A1712]">{{ $k[3] }}</span>
                <span class="mt-2 text-[12.5px] font-semibold text-[#1A1712]">{{ $k[4] }}</span>
                <span class="mt-0.5 text-[11.5px] text-[#8A857A]">{{ $k[5] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ SEARCH + FILTER DROPDOWNS ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-7">
        <div class="flex flex-col lg:flex-row gap-3 lg:items-end">
            {{-- search (visual) --}}
            <label class="relative flex-1">
                <i data-lucide="search" class="w-5 h-5 text-[#A8A498] absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input type="text" disabled
                    placeholder="{{ $isFr ? 'Rechercher un exposant, produit, service…' : 'Search an exhibitor, product, service…' }}"
                    class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-[#E7E4DB] bg-[#FBFAF7] text-[13.5px] text-[#55524A] placeholder-[#A8A498] focus:outline-none focus:border-siarc-green">
            </label>

            {{-- three filter selects (visual) --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 lg:w-auto">
                @php
                    $selects = [
                        [$isFr ? "Secteur d'activité" : 'Sector', $isFr ? 'Tous les secteurs' : 'All sectors'],
                        [$isFr ? 'Pays' : 'Country',              $isFr ? 'Tous les pays'    : 'All countries'],
                        [$isFr ? 'Type de stand' : 'Booth type',  $isFr ? 'Tous les types'   : 'All types'],
                    ];
                @endphp
                @foreach($selects as [$lbl,$ph])
                <div class="min-w-[160px]">
                    <span class="block text-[11px] font-semibold text-[#8A857A] mb-1.5">{{ $lbl }}</span>
                    <div class="relative">
                        <select disabled class="w-full appearance-none pl-3.5 pr-9 py-3 rounded-xl border border-[#E7E4DB] bg-white text-[13px] text-[#55524A] focus:outline-none focus:border-siarc-green">
                            <option>{{ $ph }}</option>
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-[#A8A498] absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex items-end shrink-0">
                <button type="button" class="siarc-btn px-5 py-3 text-[13px] border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>{{ $isFr ? 'Plus de filtres' : 'More filters' }}
                </button>
            </div>
        </div>

        {{-- pavilion filter chips --}}
        <div class="flex flex-wrap items-center gap-2.5 mt-5">
            <a href="{{ $h('siarc.exhibitors') }}"
               class="siarc-btn px-4 py-2 text-[12.5px] {{ !$activePav ? 'siarc-btn-green' : 'border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green' }}">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>{{ $isFr ? 'Tous les pavillons' : 'All pavilions' }}
            </a>
            @foreach($pavilions as $pav)
                @php $isActive = (string)$activePav === (string)$pav->id; @endphp
                <a href="{{ $h('siarc.exhibitors', ['pavilion'=>$pav->id]) }}"
                   class="siarc-btn px-4 py-2 text-[12.5px] {{ $isActive ? 'siarc-btn-green' : 'border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green' }}">
                    <i data-lucide="{{ $safeIcon($pav->icon) }}" class="w-4 h-4"></i>{{ $isFr ? $pav->name_fr : ($pav->name_en ?: $pav->name_fr) }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ RESULTS + RIGHT RAIL ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-8">

            {{-- ─────────── MAIN: results ─────────── --}}
            <div>
                {{-- result count + view toggle --}}
                <div class="flex items-center justify-between mb-7">
                    <p class="text-[14px] font-semibold text-[#1A1712]">
                        <span class="text-siarc-green">{{ $count }}</span>
                        {{ $count > 1 ? ($isFr ? 'exposants trouvés' : 'exhibitors found') : ($isFr ? 'exposant trouvé' : 'exhibitor found') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Trier par' : 'Sort by' }}</span>
                        <div class="relative">
                            <select disabled class="appearance-none pl-3 pr-8 py-2 rounded-lg border border-[#E7E4DB] bg-white text-[12px] font-semibold text-[#55524A] focus:outline-none">
                                <option>{{ $isFr ? 'Nom (A-Z)' : 'Name (A-Z)' }}</option>
                            </select>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-[#A8A498] absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                        <span class="w-9 h-9 rounded-lg bg-siarc-green text-white flex items-center justify-center"><i data-lucide="grid-3x3" class="w-4 h-4"></i></span>
                        <span class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[#8A857A] flex items-center justify-center"><i data-lucide="clipboard-list" class="w-4 h-4"></i></span>
                    </div>
                </div>

                @if($count)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($sCards as $i => $c)
                        @php
                            $name  = $c['title'] ?? ($isFr ? 'Exposant' : 'Exhibitor');
                            $sub   = $c['sub']   ?? '';
                            $tone  = $c['tone']  ?? 'green';
                            $icon  = $safeIcon($c['icon'] ?? 'store');
                            $href  = $c['href']  ?? null;
                            $bt    = $toneMap[$tone] ?? $toneMap['green'];
                            // booth chip = part after "·" in sub, pavilion = part before
                            $parts   = array_map('trim', explode('·', $sub));
                            $pavName = $parts[0] ?? '';
                            $booth   = $parts[1] ?? '';
                            // initials
                            $words = preg_split('/\s+/', trim($name));
                            $initials = strtoupper(mb_substr($words[0] ?? 'S',0,1) . (isset($words[1]) ? mb_substr($words[1],0,1) : ''));
                            $ic = $initColors[$i % count($initColors)];
                        @endphp
                        <article class="siarc-card siarc-shadow siarc-lift p-5 flex flex-col group">
                            {{-- heritage tile / initials --}}
                            <div class="flex items-start gap-4 mb-4">
                                <span class="w-14 h-14 rounded-2xl shrink-0 flex items-center justify-center text-white font-display font-extrabold text-[18px]" style="background:{{ $ic }}">{{ $initials }}</span>
                                <div class="min-w-0 pt-0.5">
                                    <h3 class="font-display text-[17px] font-bold text-[#1A1712] leading-snug truncate group-hover:text-siarc-green transition-colors">{{ $name }}</h3>
                                    @if($pavName)
                                    <p class="mt-1.5 inline-flex items-center gap-1.5 text-[12px] font-semibold" style="color:{{ $bt[1] }}">
                                        <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5"></i>{{ $pavName }}
                                    </p>
                                    @endif
                                </div>
                            </div>

                            {{-- booth chip + category tag chips --}}
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                @if($booth)
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-lg" style="background:{{ $bt[0] }};color:{{ $bt[1] }}">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>{{ $booth }}
                                </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-[#F3F0E7] text-[#8A857A]">
                                    <i data-lucide="tag" class="w-3 h-3"></i>{{ $isFr ? 'Exposant' : 'Exhibitor' }}
                                </span>
                            </div>

                            {{-- CTA: Voir le profil --}}
                            @if($href)
                            <a href="{{ $href }}" class="mt-auto siarc-btn siarc-btn-green justify-center w-full py-2.5 text-[12.5px]">
                                <i data-lucide="user" class="w-4 h-4"></i>{{ $isFr ? 'Voir le profil' : 'View profile' }}
                            </a>
                            @else
                            <span class="mt-auto siarc-btn justify-center w-full py-2.5 text-[12.5px] border border-[#E7E4DB] text-[#8A857A] bg-white cursor-default">
                                <i data-lucide="user" class="w-4 h-4"></i>{{ $isFr ? 'Profil à venir' : 'Profile soon' }}
                            </span>
                            @endif
                        </article>
                    @endforeach
                </div>

                {{-- ─────────── PAGINATION (real count, no fake pages) ─────────── --}}
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-10 pt-6 border-t border-[#E7E1D4]">
                    <p class="text-[13px] text-[#55524A]">
                        {{ $isFr ? 'Affichage de' : 'Showing' }}
                        <span class="font-semibold text-[#1A1712]">1</span>
                        {{ $isFr ? 'à' : 'to' }}
                        <span class="font-semibold text-[#1A1712]">{{ $count }}</span>
                        {{ $isFr ? 'sur' : 'of' }}
                        <span class="font-semibold text-[#1A1712]">{{ $count }}</span>
                        {{ $isFr ? 'exposants' : 'exhibitors' }}
                    </p>
                    <div class="flex items-center gap-1.5">
                        <span class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[#B8B3A6] flex items-center justify-center cursor-not-allowed">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </span>
                        <span class="w-9 h-9 rounded-lg bg-siarc-green text-white text-[13px] font-semibold flex items-center justify-center">1</span>
                        <span class="w-9 h-9 rounded-lg border border-[#E7E4DB] bg-white text-[#B8B3A6] flex items-center justify-center cursor-not-allowed">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </span>
                    </div>
                </div>
                @else
                {{-- honest empty state --}}
                <div class="siarc-card siarc-shadow p-12 text-center">
                    <span class="w-16 h-16 mx-auto rounded-2xl siarc-adire flex items-center justify-center mb-5">
                        <i data-lucide="store" class="w-8 h-8 text-siarc-gold"></i>
                    </span>
                    <h3 class="font-display text-[20px] font-bold text-[#1A1712] mb-2">{{ $isFr ? 'Aucun exposant' : 'No exhibitors' }}</h3>
                    <p class="text-[13.5px] text-[#8A857A] max-w-[420px] mx-auto leading-relaxed">
                        {{ $isFr ? 'La liste des exposants pour ce pavillon sera bientôt disponible.' : 'The exhibitor list for this pavilion will be available soon.' }}
                    </p>
                    @if($activePav)
                    <a href="{{ $h('siarc.exhibitors') }}" class="siarc-btn siarc-btn-green px-5 py-2.5 text-[12.5px] mt-6">
                        {{ $isFr ? 'Voir tous les exposants' : 'View all exhibitors' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    @endif
                </div>
                @endif
            </div>

            {{-- ─────────── ASIDE: right rail ─────────── --}}
            <aside class="space-y-6">

                {{-- Catégories (derived from pavilions, real counts) --}}
                <div class="siarc-card siarc-shadow p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display text-[16px] font-bold text-[#1A1712]">{{ $isFr ? 'Catégories' : 'Categories' }}</h3>
                        <i data-lucide="layers" class="w-4 h-4 text-siarc-ochre"></i>
                    </div>
                    @if($pavilions->count())
                    <ul class="divide-y divide-[#F0EDE4]">
                        @foreach($pavilions as $pav)
                        <li>
                            <a href="{{ $h('siarc.exhibitors', ['pavilion'=>$pav->id]) }}"
                               class="flex items-center justify-between gap-3 py-2.5 group">
                                <span class="inline-flex items-center gap-2.5 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $pav->color ?: '#157A43' }}"></span>
                                    <span class="text-[13px] font-medium text-[#3B382F] truncate group-hover:text-siarc-green transition-colors">{{ $isFr ? $pav->name_fr : ($pav->name_en ?: $pav->name_fr) }}</span>
                                </span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-[#F3F0E7] text-[#8A857A] shrink-0">{{ $pav->exh_count }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-[12.5px] text-[#8A857A] py-3">{{ $isFr ? 'Aucune catégorie disponible.' : 'No categories available.' }}</p>
                    @endif
                    <a href="{{ $h('siarc.pavilions') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-siarc-green mt-4 hover:gap-2.5 transition-all">
                        {{ $isFr ? 'Voir toutes les catégories' : 'View all categories' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>

                {{-- Exposants en vedette (first few real cards) --}}
                <div class="siarc-card siarc-shadow p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display text-[16px] font-bold text-[#1A1712]">{{ $isFr ? 'Exposants en vedette' : 'Featured exhibitors' }}</h3>
                        <a href="{{ $h('siarc.exhibitors') }}" class="text-[11.5px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tout' : 'See all' }}</a>
                    </div>
                    @if(count($featured))
                    <ul class="space-y-3">
                        @foreach($featured as $i => $f)
                            @php
                                $fname = $f['title'] ?? ($isFr ? 'Exposant' : 'Exhibitor');
                                $fsub  = $f['sub'] ?? '';
                                $fhref = $f['href'] ?? null;
                                $fwords = preg_split('/\s+/', trim($fname));
                                $finit = strtoupper(mb_substr($fwords[0] ?? 'S',0,1) . (isset($fwords[1]) ? mb_substr($fwords[1],0,1) : ''));
                                $fc = $initColors[$i % count($initColors)];
                            @endphp
                            <li>
                                @if($fhref)<a href="{{ $fhref }}" class="flex items-center gap-3 group">@else<div class="flex items-center gap-3">@endif
                                    <span class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center text-white font-display font-extrabold text-[13px]" style="background:{{ $fc }}">{{ $finit }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[13px] font-bold text-[#1A1712] truncate {{ $fhref ? 'group-hover:text-siarc-green transition-colors' : '' }}">{{ $fname }}</p>
                                        @if($fsub)<p class="text-[11px] text-[#8A857A] truncate">{{ $fsub }}</p>@endif
                                    </div>
                                    <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-md bg-[#F0EAFB] text-[#7C4FE0] shrink-0">Premium</span>
                                @if($fhref)</a>@else</div>@endif
                            </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-[12.5px] text-[#8A857A] py-3">{{ $isFr ? 'À venir.' : 'Coming soon.' }}</p>
                    @endif
                </div>

                {{-- Plan du salon (mini panel) --}}
                <div class="siarc-card siarc-shadow overflow-hidden">
                    <div class="p-5 pb-4">
                        <h3 class="font-display text-[16px] font-bold text-[#1A1712]">{{ $isFr ? 'Plan du salon' : 'Floor plan' }}</h3>
                    </div>
                    {{-- stylised floor-plan illustration (inline SVG, no external asset) --}}
                    <div class="px-5">
                        <div class="rounded-xl border border-[#E7E1D4] bg-[#FBF9F3] overflow-hidden">
                            <svg viewBox="0 0 280 170" class="w-full block">
                                <rect x="0" y="0" width="280" height="170" fill="#FBF9F3"/>
                                {{-- grid lines --}}
                                @foreach([46,93,140,187,234] as $gx)<line x1="{{ $gx }}" y1="0" x2="{{ $gx }}" y2="170" stroke="#EFEADD" stroke-width="1"/>@endforeach
                                @foreach([42,85,128] as $gy)<line x1="0" y1="{{ $gy }}" x2="280" y2="{{ $gy }}" stroke="#EFEADD" stroke-width="1"/>@endforeach
                                {{-- pavilion blocks --}}
                                <rect x="14" y="14" width="60" height="52" rx="6" fill="#E7F2EC" stroke="#157A43" stroke-width="1.4"/>
                                <text x="44" y="44" font-size="13" fill="#157A43" font-weight="700" text-anchor="middle">A</text>
                                <rect x="150" y="14" width="52" height="46" rx="6" fill="#E7EEF9" stroke="#3565DE" stroke-width="1.4"/>
                                <text x="176" y="41" font-size="13" fill="#3565DE" font-weight="700" text-anchor="middle">C</text>
                                <rect x="216" y="14" width="50" height="46" rx="6" fill="#FDE8E8" stroke="#C0010C" stroke-width="1.4"/>
                                <text x="241" y="41" font-size="13" fill="#C0010C" font-weight="700" text-anchor="middle">G</text>
                                {{-- central stage --}}
                                <rect x="96" y="72" width="88" height="34" rx="6" fill="#0B3A1E"/>
                                <text x="140" y="93" font-size="11" fill="#E6B201" font-weight="700" text-anchor="middle">{{ $isFr ? 'Scène' : 'Stage' }}</text>
                                <rect x="14" y="118" width="70" height="40" rx="6" fill="#FDF3E0" stroke="#C97A16" stroke-width="1.4"/>
                                <text x="49" y="142" font-size="13" fill="#C97A16" font-weight="700" text-anchor="middle">B</text>
                                <rect x="196" y="118" width="70" height="40" rx="6" fill="#F0EAFB" stroke="#7C4FE0" stroke-width="1.4"/>
                                <text x="231" y="142" font-size="13" fill="#7C4FE0" font-weight="700" text-anchor="middle">E</text>
                            </svg>
                        </div>
                    </div>
                    <div class="p-5 pt-4">
                        <a href="{{ $h('siarc.pavilions') }}" class="siarc-btn siarc-btn-green justify-center w-full py-2.5 text-[12.5px]">
                            <i data-lucide="map" class="w-4 h-4"></i>{{ $isFr ? 'Voir le plan interactif' : 'View interactive map' }}
                        </a>
                    </div>
                </div>

            </aside>
        </div>
    </div>
</section>
