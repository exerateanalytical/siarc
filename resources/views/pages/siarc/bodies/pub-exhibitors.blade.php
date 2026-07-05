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

    // pavilion chips (read-only enrichment)
    $pavilions = collect();
    try {
        $evId = function_exists('siarcEvent') ? (siarcEvent()?->id ?? 0) : 0;
        $pavilions = DB::table('pavilions')->where('event_id', $evId)
            ->orderBy('sort_order')->get(['id','name_fr','icon','color']);
    } catch (\Throwable $e) { $pavilions = collect(); }

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
                ['globe',       '#2456B3', '#E7EEF9', '24',    $isFr ? 'Pays représentés'      : 'Countries',          $isFr ? 'sur 3 continents'         : 'across 3 continents'],
                ['grid-2x2',    '#7C4FE0', '#F0EAFB', '12',    $isFr ? "Secteurs d'activité"   : 'Sectors',            $isFr ? 'filières artisanales'     : 'craft sectors'],
                ['star',        '#C0010C', '#FDE8E8', '38',    $isFr ? 'Exposants premium'     : 'Premium exhibitors', $isFr ? 'pavillons signature'      : 'signature pavilions'],
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

{{-- ══════════════════ SEARCH + FILTER CHIPS ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-7">
        {{-- search bar (visual) --}}
        <div class="flex flex-col md:flex-row gap-3 md:items-center">
            <label class="relative flex-1">
                <i data-lucide="search" class="w-5 h-5 text-[#A8A498] absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input type="text" disabled
                    placeholder="{{ $isFr ? 'Rechercher un exposant, produit, service…' : 'Search an exhibitor, product, service…' }}"
                    class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-[#E7E4DB] bg-[#FBFAF7] text-[13.5px] text-[#55524A] placeholder-[#A8A498] focus:outline-none focus:border-siarc-green">
            </label>
            <button type="button" class="siarc-btn px-5 py-3.5 text-[13px] border border-[#E7E4DB] text-[#55524A] bg-white hover:border-siarc-green">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>{{ $isFr ? 'Plus de filtres' : 'More filters' }}
            </button>
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
                    @if($pav->icon)<i data-lucide="{{ $pav->icon }}" class="w-4 h-4"></i>@else<i data-lucide="store" class="w-4 h-4"></i>@endif
                    {{ $pav->name_fr }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ RESULTS GRID ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-12">
        {{-- result count --}}
        <div class="flex items-center justify-between mb-7">
            <p class="text-[14px] font-semibold text-[#1A1712]">
                <span class="text-siarc-green">{{ $count }}</span>
                {{ $count > 1 ? ($isFr ? 'exposants trouvés' : 'exhibitors found') : ($isFr ? 'exposant trouvé' : 'exhibitor found') }}
            </p>
        </div>

        @if($count)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sCards as $i => $c)
                @php
                    $name  = $c['title'] ?? ($isFr ? 'Exposant' : 'Exhibitor');
                    $sub   = $c['sub']   ?? '';
                    $tone  = $c['tone']  ?? 'green';
                    $icon  = $c['icon']  ?? 'store';
                    $href  = $c['href']  ?? '#';
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
                <a href="{{ $href }}" class="siarc-card siarc-shadow siarc-lift p-5 flex flex-col group">
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

                    {{-- booth chip + tone badge --}}
                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        @if($booth)
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-lg" style="background:{{ $bt[0] }};color:{{ $bt[1] }}">
                            <i data-lucide="map-pin" class="w-3 h-3"></i>{{ $booth }}
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-lg bg-[#F3F0E7] text-[#8A857A]">
                            <i data-lucide="store" class="w-3 h-3"></i>{{ $isFr ? 'Exposant' : 'Exhibitor' }}
                        </span>
                    </div>

                    {{-- CTA --}}
                    <span class="mt-auto inline-flex items-center gap-1 text-[13px] font-semibold text-siarc-green group-hover:gap-2 transition-all">
                        {{ $isFr ? 'Voir le profil' : 'View profile' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </a>
            @endforeach
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
</section>
