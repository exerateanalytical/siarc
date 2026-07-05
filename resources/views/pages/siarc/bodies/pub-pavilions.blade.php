@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Real data enrichment (read-only) ──────────────────────────────────────
    $eid  = function_exists('siarcEvent') ? (siarcEvent()?->id ?? 0) : 0;
    $pavs = collect();
    try {
        if (Schema::hasTable('pavilions')) {
            $pavs = DB::table('pavilions')->where('event_id', $eid)
                ->orderBy('sort_order')
                ->get(['id','name_fr','name_en','description_fr','color','icon','capacity','code']);
        }
    } catch (\Throwable $e) { $pavs = collect(); }

    $exCount = [];
    try {
        if (Schema::hasTable('event_exhibitors') && $pavs->isNotEmpty()) {
            $exCount = DB::table('event_exhibitors')
                ->whereIn('pavilion_id', $pavs->pluck('id'))
                ->selectRaw('pavilion_id, COUNT(*) as c')
                ->groupBy('pavilion_id')->pluck('c','pavilion_id')->all();
        }
    } catch (\Throwable $e) { $exCount = []; }

    // Approved headline figures (baked into the design)
    $kpis = [
        ['globe','24','Pays représentés'],
        ['layout-grid','12','Pavillons thématiques'],
        ['store','286','Stands occupés'],
        ['users','580+','Exposants inscrits'],
    ];

    // Regions strip (approved figures from design)
    $regions = [
        ['Afrique Centrale',56],['Afrique de l\'Ouest',78],['Afrique du Nord',34],
        ['Afrique de l\'Est',42],['Afrique Australe',28],['International',48],
    ];

    // Palette fallback for pavilions without a colour
    $palette = ['#157A43','#C97A16','#E6B201','#C0010C','#0F4824','#7C4FE0','#2E8B57'];
@endphp

{{-- ══════════════════ HEADER BAND ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-11 lg:py-14 siarc-in">
        <div class="flex items-center gap-2 text-[12px] text-[#8A857A] mb-4">
            <a href="{{ $h('siarc.home') }}" class="hover:text-siarc-green">{{ $isFr ? 'Accueil' : 'Home' }}</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            <span class="font-semibold text-siarc-ochre">{{ $sCrumb ?? ($isFr ? 'Pavillons' : 'Pavilions') }}</span>
        </div>
        <div class="flex items-center gap-1.5 mb-5">
            @for($i=0;$i<4;$i++)<span class="w-3 h-3 rotate-45" style="background:{{ ['#157A43','#C0010C','#E6B201','#0F4824'][$i] }}"></span>@endfor
        </div>
        <h1 class="font-display font-extrabold leading-[1.05] text-[38px] sm:text-[50px] tracking-tight text-[#1A1712]">
            {{ $sTitle ?? ($isFr ? 'Explorer les pavillons' : 'Explore the pavilions') }}
        </h1>
        <p class="mt-5 text-[15px] text-[#55524A] leading-relaxed max-w-[620px]">
            {{ $isFr
                ? 'Un voyage à travers la créativité, le savoir-faire et la diversité culturelle des pavillons nationaux, régionaux et thématiques du SIARC 2026.'
                : 'A journey through the creativity, craftsmanship and cultural diversity of the national, regional and thematic pavilions of SIARC 2026.' }}
        </p>
        <div class="flex flex-wrap items-center gap-x-8 gap-y-3 mt-7">
            <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="calendar" class="w-5 h-5 text-siarc-ochre"></i>27 JUILLET – 05 AOÛT 2026</span>
            <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="map-pin" class="w-5 h-5 text-siarc-ochre"></i>MUSÉE NATIONAL DE YAOUNDÉ</span>
        </div>
    </div>
</section>

{{-- ══════════════════ KPI BAND ══════════════════ --}}
<section class="relative bg-gradient-to-r from-[#0B3A1E] to-[#0F4824] text-white overflow-hidden">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0"></div>
    <div class="siarc-kente-v absolute right-0 top-0 bottom-0" style="transform:scaleX(-1)"></div>
    <div class="max-w-[1180px] mx-auto px-10 py-8 grid grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($kpis as [$icon,$num,$label])
        <div class="flex items-center gap-3.5 justify-center lg:justify-start">
            <i data-lucide="{{ $icon }}" class="w-8 h-8 text-siarc-gold shrink-0" style="stroke-width:1.6"></i>
            <div>
                <p class="font-display text-[28px] font-extrabold leading-none">{{ $num }}</p>
                <p class="text-[10.5px] font-semibold tracking-wide text-white/70 mt-1">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ══════════════════ PAVILION GRID ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 py-16">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-9">
            <div>
                <span class="siarc-kicker text-siarc-ochre">{{ $isFr ? 'TOUS LES PAVILLONS' : 'ALL PAVILIONS' }}</span>
                <h2 class="font-display text-[26px] font-bold text-[#1A1712] mt-3">{{ $isFr ? 'Pavillons du SIARC 2026' : 'SIARC 2026 pavilions' }}</h2>
            </div>
            <a href="{{ $h('siarc.exhibitors') }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-siarc-green hover:gap-3 transition-all">
                {{ $isFr ? 'Voir tous les exposants' : 'See all exhibitors' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>

        @php
            // Prefer real DB pavilions; else the cards the route already passed.
            $usingDb = $pavs->isNotEmpty();
            $items   = $usingDb ? $pavs : collect($sCards ?? []);
        @endphp

        @if($items->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $i => $p)
                @php
                    $name  = $usingDb ? ($isFr ? ($p->name_fr ?: $p->name_en) : ($p->name_en ?: $p->name_fr)) : (is_array($p) ? ($p['name'] ?? '') : ($p->name ?? ''));
                    $desc  = $usingDb ? ($p->description_fr ?? '') : (is_array($p) ? ($p['description'] ?? '') : ($p->description ?? ''));
                    $code  = $usingDb ? ($p->code ?? '') : (is_array($p) ? ($p['code'] ?? '') : ($p->code ?? ''));
                    $icon  = ($usingDb ? ($p->icon ?? null) : (is_array($p) ? ($p['icon'] ?? null) : ($p->icon ?? null))) ?: 'layout-grid';
                    $cap   = $usingDb ? ($p->capacity ?? null) : (is_array($p) ? ($p['capacity'] ?? null) : ($p->capacity ?? null));
                    $pid   = $usingDb ? $p->id : (is_array($p) ? ($p['id'] ?? null) : ($p->id ?? null));
                    $color = ($usingDb ? ($p->color ?? null) : (is_array($p) ? ($p['color'] ?? null) : ($p->color ?? null))) ?: $palette[$i % count($palette)];
                    $count = $pid !== null ? ($exCount[$pid] ?? null) : null;
                    $href  = $pid !== null ? $h('siarc.exhibitors', ['pavilion'=>$pid]) : $h('siarc.exhibitors');
                @endphp
                <a href="{{ $href }}" class="siarc-card siarc-shadow siarc-lift overflow-hidden flex flex-col group">
                    {{-- Tinted accent header --}}
                    <div class="relative px-6 pt-6 pb-5" style="background:linear-gradient(135deg,{{ $color }}14,{{ $color }}05);border-bottom:1px solid {{ $color }}22;">
                        <div class="absolute top-0 left-0 right-0 h-[4px]" style="background:{{ $color }}"></div>
                        <div class="flex items-start justify-between gap-3">
                            <span class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 siarc-shadow" style="background:{{ $color }}">
                                <i data-lucide="{{ $icon }}" class="w-7 h-7 text-white" style="stroke-width:1.8"></i>
                            </span>
                            @if($code)
                            <span class="inline-flex items-center gap-1 text-[10.5px] font-bold tracking-wide px-2.5 py-1 rounded-full" style="background:{{ $color }}1A;color:{{ $color }}">
                                <i data-lucide="tag" class="w-3 h-3"></i>{{ $code }}
                            </span>
                            @endif
                        </div>
                    </div>
                    {{-- Body --}}
                    <div class="px-6 pt-5 pb-6 flex flex-col flex-1">
                        <h3 class="font-display text-[19px] font-bold text-[#1A1712] leading-snug mb-2">{{ $name }}</h3>
                        @if($desc)
                        <p class="text-[12.5px] text-[#7A756A] leading-relaxed mb-5 line-clamp-3">{{ $desc }}</p>
                        @else
                        <p class="text-[12.5px] text-[#A8A498] italic leading-relaxed mb-5">{{ $isFr ? 'Description à venir.' : 'Description coming soon.' }}</p>
                        @endif

                        <div class="flex items-center gap-5 mb-5 mt-auto">
                            <span class="inline-flex items-center gap-2 text-[12px] font-semibold text-[#2A271F]">
                                <i data-lucide="users" class="w-4 h-4 text-siarc-green"></i>
                                {{ $count !== null ? $count.' '.($isFr ? 'exposants' : 'exhibitors') : ($isFr ? 'Exposants à venir' : 'Exhibitors coming') }}
                            </span>
                            @if($cap)
                            <span class="inline-flex items-center gap-2 text-[12px] font-semibold text-[#2A271F]">
                                <i data-lucide="store" class="w-4 h-4 text-siarc-ochre"></i>{{ $cap }} {{ $isFr ? 'stands' : 'stands' }}
                            </span>
                            @endif
                        </div>

                        <span class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold group-hover:gap-2.5 transition-all" style="color:{{ $color }}">
                            {{ $isFr ? 'Explorer les exposants' : 'Explore exhibitors' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        @else
        {{-- Honest empty state --}}
        <div class="siarc-card siarc-shadow p-12 text-center">
            <span class="w-16 h-16 mx-auto rounded-2xl bg-[#F3F0E7] flex items-center justify-center mb-4">
                <i data-lucide="layout-grid" class="w-8 h-8 text-siarc-green"></i>
            </span>
            <h3 class="font-display text-[20px] font-bold text-[#1A1712] mb-2">{{ $isFr ? 'Pavillons à venir' : 'Pavilions coming soon' }}</h3>
            <p class="text-[13px] text-[#7A756A] max-w-[420px] mx-auto">
                {{ $isFr ? 'Les pavillons du SIARC 2026 seront bientôt annoncés. Revenez prochainement.' : 'SIARC 2026 pavilions will be announced soon. Please check back later.' }}
            </p>
        </div>
        @endif
    </div>
</section>

{{-- ══════════════════ EXPLORE BY REGION ══════════════════ --}}
<section class="bg-white border-t border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 py-16">
        <div class="text-center mb-11"><span class="siarc-kicker text-siarc-ochre">{{ $isFr ? 'EXPLORER PAR RÉGION' : 'EXPLORE BY REGION' }}</span></div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($regions as $ri => [$rname,$rc])
            @php $rcolor = $palette[$ri % count($palette)]; @endphp
            <a href="{{ $h('siarc.exhibitors') }}" class="siarc-card siarc-shadow siarc-lift p-5 text-center">
                <span class="w-11 h-11 mx-auto rounded-xl flex items-center justify-center mb-3" style="background:{{ $rcolor }}18">
                    <i data-lucide="map-pin" class="w-5 h-5" style="color:{{ $rcolor }}"></i>
                </span>
                <p class="text-[12.5px] font-bold text-[#1A1712] leading-snug mb-1">{{ $rname }}</p>
                <p class="font-display text-[20px] font-extrabold" style="color:{{ $rcolor }}">{{ $rc }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ CTA STRIP ══════════════════ --}}
<section class="bg-[#0B3A1E] text-white relative overflow-hidden">
    <div class="siarc-kente absolute top-0 left-0 right-0 opacity-80"></div>
    <div class="max-w-[1240px] mx-auto px-6 py-12 flex flex-col md:flex-row items-center justify-between gap-6 mt-2">
        <div>
            <h3 class="font-display text-[24px] font-bold mb-2">{{ $isFr ? 'Besoin d\'informations ?' : 'Need information?' }}</h3>
            <p class="text-[13.5px] text-white/70 leading-relaxed max-w-[480px]">
                {{ $isFr ? 'Notre équipe est à votre disposition pour vous accompagner dans la découverte des pavillons.' : 'Our team is available to help you discover the pavilions.' }}
            </p>
        </div>
        <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-primary px-6 py-3.5 text-[13px] shrink-0">
            {{ $isFr ? 'Contacter l\'équipe SIARC' : 'Contact the SIARC team' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>
</section>
