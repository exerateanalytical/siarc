@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $slug = request()->route()?->parameter('slug');
    $b = null; $prods = collect();
    try {
        $b = DB::table('businesses')->where('slug',$slug)->first(['id','name_fr','description_fr','logo','region_key','industry_id','slug']);
    } catch (\Throwable $e) { $b = null; }
    if ($b) {
        try {
            $prods = DB::table('products')->where('business_id',$b->id)->whereNull('deleted_at')
                ->limit(6)->get(['name_fr','price','main_image']);
        } catch (\Throwable $e) { $prods = collect(); }
    }

    $title   = $sTitle   ?? ($b->name_fr ?? "Artisanat d'Excellence Cameroun");
    $intro   = $sIntro   ?? ($b->description_fr ?? null);
    $desc    = $b->description_fr ?? null;
    $region  = $b->region_key ?? 'Cameroun';
    $logo    = $b->logo ?? null;

    // initials for heritage tile
    $words = preg_split('/\s+/', trim($title));
    $initials = strtoupper(mb_substr($words[0] ?? 'A',0,1) . (mb_substr($words[1] ?? '',0,1)));

    // $sStats tiles (Pavillon, Stand). Approved headline figures from design.
    $stand   = 'Stand A12';
    $pavilion= 'Pavillon Artisanat';
    if (!empty($sStats) && is_array($sStats)) {
        foreach ($sStats as $st) {
            $l = strtolower($st['label'] ?? ($st[4] ?? ''));
            $v = $st['value'] ?? ($st[3] ?? '');
            if (str_contains($l,'stand') && $v) $stand = $v;
            if (str_contains($l,'pavillon') && $v) $pavilion = $v;
        }
    }

    // approved headline KPI tiles (baked into design)
    $kpis = [
        ['package','Produits exposés','56'],
        ['layout-grid','Catégories','5'],
        ['users-round','Employés','24'],
        ['globe','Pays desservis','12'],
        ['star','Certifications','3'],
    ];

    $tabs = ['À propos','Produits & Services','Documents','Actualités','Galerie','Vidéos','Équipe'];
    $sectors = ['Arts décoratifs','Décoration intérieure','Textile & Mode','Bois & Sculpture','Accessoires & Bijoux'];
    $certs = ['Made in Cameroon','Commerce équitable','ISO 9001:2015'];
    $abouts = [
        'Produits 100% fabriqués au Cameroun',
        'Matériaux locaux et écoresponsables',
        'Design innovant et finitions haut de gamme',
        'Respect des traditions et du commerce équitable',
    ];
    $docs = [
        ['Catalogue produits 2026','PDF · 4.2 Mo'],
        ["Présentation de l'entreprise",'PDF · 2.1 Mo'],
        ['Brochure institutionnelle','PDF · 3.8 Mo'],
    ];
    $contactLink = $sLinks['storefront'] ?? ($sLinks[0]['href'] ?? $h('siarc.exhibitors'));
    $backLink    = $sLinks['exhibitors'] ?? ($sLinks[1]['href'] ?? $h('siarc.exhibitors'));
@endphp

{{-- ══════════════════ BREADCRUMB + PAGE HEAD ══════════════════ --}}
<section class="siarc-mud border-b border-[#EDE7DA]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 pt-9 pb-6">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="siarc-in">
                <h1 class="font-display text-[30px] font-extrabold text-[#1A1712] leading-tight">{{ $isFr ? "Profil de l'exposant" : 'Exhibitor profile' }}</h1>
                <nav class="flex items-center gap-2 text-[13px] text-[#8A857A] mt-1.5">
                    <a href="{{ $backLink }}" class="hover:text-siarc-green">{{ $isFr ? 'Exposants' : 'Exhibitors' }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="text-[#2A271F] font-medium">{{ $title }}</span>
                </nav>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ $backLink }}" class="siarc-btn px-4 py-2.5 text-[12.5px] bg-white border border-[#ECEAE3] text-[#2A271F] hover:border-[#D7E4DB]"><i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la liste' : 'Back to list' }}</a>
                <a @if(R::has('siarc.exhibitors')) href="{{ route('siarc.exhibitors',['lang'=>$lang]) }}" @endif class="siarc-btn px-4 py-2.5 text-[12.5px] bg-white border border-[#ECEAE3] text-[#2A271F] hover:border-[#D7E4DB]"><i data-lucide="external-link" class="w-4 h-4"></i>{{ $isFr ? 'Partager' : 'Share' }}</a>
                <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[12.5px]"><i data-lucide="calendar-clock" class="w-4 h-4"></i>{{ $isFr ? 'Planifier un RDV' : 'Book a meeting' }}</a>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ MAIN GRID ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-8 grid lg:grid-cols-[1fr_340px] gap-6 items-start">

        {{-- ── LEFT COLUMN ─────────────────────────────────────────────── --}}
        <div class="space-y-6 min-w-0">

            {{-- HERO CARD --}}
            <div class="rounded-2xl overflow-hidden siarc-shadow-lg siarc-adire text-white relative siarc-in">
                <div class="siarc-kente absolute top-0 left-0 right-0 opacity-70 z-10"></div>
                <div class="relative flex flex-col sm:flex-row gap-6 p-7 pt-9">
                    {{-- logo tile --}}
                    <div class="shrink-0">
                        @if($logo)
                            <img src="{{ asset('storage/'.$logo) }}" alt="{{ $title }}" class="w-[130px] h-[130px] rounded-2xl object-cover bg-white siarc-shadow">
                        @else
                            <div class="w-[130px] h-[130px] rounded-2xl bg-white flex flex-col items-center justify-center text-center siarc-shadow px-3">
                                <span class="font-display font-extrabold text-[34px] text-siarc-ochre leading-none">{{ $initials }}</span>
                                <span class="mt-2 text-[8.5px] font-bold tracking-wide text-[#8A6A2A] leading-tight uppercase">{{ mb_strimwidth($title, 0, 34, '') }}</span>
                            </div>
                        @endif
                    </div>
                    {{-- headline --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h2 class="font-display text-[26px] font-extrabold leading-tight">{{ $title }}</h2>
                            <span class="text-[10.5px] font-bold px-2.5 py-1 rounded-full bg-siarc-gold/20 text-siarc-gold border border-siarc-gold/30">Premium</span>
                        </div>
                        <p class="text-[13px] text-white/85 font-medium mb-3">{{ $region }} <span class="mx-1.5 text-white/40">·</span> {{ $stand }} <span class="mx-1.5 text-white/40">·</span> {{ $pavilion }}</p>
                        <p class="text-[13px] text-white/70 leading-relaxed max-w-[440px]">{{ $intro ?? 'Promotion et commercialisation des produits artisanaux haut de gamme du Cameroun.' }}</p>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-5 text-[12px] text-white/75">
                            <span class="inline-flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-siarc-gold"></i>{{ $isFr ? 'Membre depuis' : 'Member since' }} : Mars 2024</span>
                            <span class="inline-flex items-center gap-2"><i data-lucide="activity" class="w-4 h-4 text-siarc-gold"></i>{{ $isFr ? 'Visites' : 'Views' }} : 1 256</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI TILES --}}
            <div class="siarc-card siarc-shadow px-6 py-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-y-5 gap-x-4">
                    @foreach($kpis as [$icon,$label,$val])
                    <div class="flex items-center gap-3">
                        <span class="w-11 h-11 rounded-xl bg-[#F3F0E7] flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-5 h-5 text-siarc-green"></i></span>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A] leading-tight">{{ $label }}</p>
                            <p class="font-display text-[22px] font-extrabold text-[#1A1712] leading-none mt-0.5">{{ $val }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- TABS --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="flex items-center gap-6 px-6 border-b border-[#ECEAE3] overflow-x-auto">
                    @foreach($tabs as $i => $t)
                    <span class="py-4 text-[13px] whitespace-nowrap {{ $i===0 ? 'font-semibold text-siarc-green border-b-2 border-siarc-green' : 'text-[#8A857A] border-b-2 border-transparent' }}">{{ $t }}</span>
                    @endforeach
                </div>

                {{-- À PROPOS PANEL --}}
                <div class="p-6 grid lg:grid-cols-[1fr_300px] gap-7">
                    <div class="min-w-0">
                        <h3 class="font-display text-[18px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'À propos de nous' : 'About us' }}</h3>
                        @if($desc)
                            <p class="text-[13px] text-[#55524A] leading-relaxed mb-5">{{ $desc }}</p>
                        @else
                            <p class="text-[13px] text-[#55524A] leading-relaxed mb-5">Artisanat d'Excellence Cameroun regroupe plus de 200 artisans talentueux provenant de toutes les régions du Cameroun. Nous valorisons le savoir-faire traditionnel à travers des créations uniques alliant authenticité, qualité et design contemporain.</p>
                        @endif

                        <ul class="space-y-2.5 mb-6">
                            @foreach($abouts as $a)
                            <li class="flex items-start gap-2.5 text-[13px] text-[#2A271F]"><i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0 mt-0.5"></i>{{ $a }}</li>
                            @endforeach
                        </ul>

                        {{-- video / heritage tile --}}
                        <div class="rounded-xl siarc-adire border border-[#0F4824]/20 aspect-[16/9] flex items-center justify-center mb-2 relative overflow-hidden">
                            <span class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center siarc-shadow"><i data-lucide="play" class="w-7 h-7 text-siarc-green ml-1"></i></span>
                        </div>

                        <a @if(R::has('siarc.exhibitors')) href="{{ route('siarc.exhibitors',['lang'=>$lang]) }}" @endif class="siarc-btn px-5 py-2.5 text-[12.5px] mt-4 bg-white border border-[#ECEAE3] text-[#2A271F] hover:border-[#D7E4DB]">{{ $isFr ? 'Voir plus' : 'See more' }}</a>
                    </div>

                    {{-- sidebar within panel --}}
                    <div class="space-y-6">
                        <div class="rounded-xl bg-[#FBFAF7] border border-[#ECEAE3] p-5">
                            <h4 class="font-display text-[15px] font-bold text-[#1A1712] mb-3">{{ $isFr ? "Secteurs d'activité" : 'Business sectors' }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($sectors as $s)
                                <span class="text-[11px] font-medium px-3 py-1.5 rounded-lg bg-[#EAF2EC] text-siarc-green">{{ $s }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="rounded-xl bg-[#FBFAF7] border border-[#ECEAE3] p-5">
                            <h4 class="font-display text-[15px] font-bold text-[#1A1712] mb-3">Certifications</h4>
                            <ul class="space-y-3">
                                @foreach($certs as $c)
                                <li class="flex items-center gap-3 text-[12.5px] text-[#2A271F]">
                                    <span class="w-8 h-8 rounded-lg bg-[#F3F0E7] flex items-center justify-center shrink-0"><i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green"></i></span>
                                    {{ $c }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PRODUITS PHARES --}}
            @php
                $fallbackProds = [
                    ['Sculpture gardien Bamiléké','Bois'],
                    ['Sac tissé motif Bamoun','Raphia & cuir'],
                    ['Tabouret royal Bandjoun','Bois massif'],
                    ['Panier décoratif fait main','Fibres naturelles'],
                    ['Masque ancestral Fang-Beti','Bois sculpté'],
                    ['Lampe artisanale ajourée','Calebasse & bois'],
                ];
            @endphp
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Produits phares' : 'Featured products' }}</h3>
                    <a href="{{ $contactLink }}" class="text-[12.5px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tous les produits' : 'View all products' }} →</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @if($prods->count())
                        @foreach($prods as $p)
                        @php
                            $pname = $p->name_fr ?? '—';
                            $pimg  = $p->main_image ?? null;
                            $pinit = strtoupper(mb_substr($pname,0,1));
                        @endphp
                        <div class="group">
                            @if($pimg)
                                <div class="rounded-xl overflow-hidden aspect-square mb-2.5 siarc-lift"><img src="{{ asset('storage/'.$pimg) }}" alt="{{ $pname }}" class="w-full h-full object-cover"></div>
                            @else
                                <div class="rounded-xl aspect-square mb-2.5 siarc-adire flex items-center justify-center siarc-lift"><span class="font-display text-[26px] font-extrabold text-siarc-gold/80">{{ $pinit }}</span></div>
                            @endif
                            <p class="text-[12px] font-semibold text-[#1A1712] leading-snug line-clamp-2">{{ $pname }}</p>
                            <p class="text-[11px] text-[#8A857A] mt-0.5">
                                @if(isset($p->price) && $p->price !== null && $p->price !== '')
                                    {{ number_format((float)$p->price, 0, ',', ' ') }} FCFA
                                @else
                                    <span class="inline-block mt-1 text-[10px] font-medium px-2 py-0.5 rounded bg-[#EAF2EC] text-siarc-green">Sur demande</span>
                                @endif
                            </p>
                        </div>
                        @endforeach
                    @else
                        @foreach($fallbackProds as [$pname,$pmat])
                        <div class="group">
                            <div class="rounded-xl aspect-square mb-2.5 siarc-adire flex items-center justify-center siarc-lift"><span class="font-display text-[26px] font-extrabold text-siarc-gold/80">{{ strtoupper(mb_substr($pname,0,1)) }}</span></div>
                            <p class="text-[12px] font-semibold text-[#1A1712] leading-snug line-clamp-2">{{ $pname }}</p>
                            <p class="text-[11px] text-[#8A857A] mt-0.5">{{ $pmat }}</p>
                            <span class="inline-block mt-1 text-[10px] font-medium px-2 py-0.5 rounded bg-[#EAF2EC] text-siarc-green">Sur demande</span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        {{-- ── RIGHT COLUMN ────────────────────────────────────────────── --}}
        <aside class="space-y-6 lg:sticky lg:top-6">

            {{-- CONTACT --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations de contact' : 'Contact information' }}</h3>
                <ul class="space-y-3.5 text-[12.5px] text-[#2A271F]">
                    <li class="flex items-center gap-3"><i data-lucide="map-pin" class="w-4 h-4 text-siarc-ochre shrink-0"></i>{{ $region }}</li>
                    <li class="flex items-center gap-3"><i data-lucide="phone" class="w-4 h-4 text-siarc-ochre shrink-0"></i>+237 677 123 456</li>
                    <li class="flex items-center gap-3"><i data-lucide="mail" class="w-4 h-4 text-siarc-ochre shrink-0"></i>contact@artisanatexcellence.cm</li>
                    <li class="flex items-center gap-3"><i data-lucide="globe" class="w-4 h-4 text-siarc-ochre shrink-0"></i>www.artisanatexcellence.cm</li>
                </ul>
                <a href="{{ $contactLink }}" class="siarc-btn w-full justify-center mt-5 px-5 py-3 text-[13px] text-white" style="background:var(--si-green-800)"><i data-lucide="mail" class="w-4 h-4"></i>{{ $isFr ? "Contacter l'exposant" : 'Contact exhibitor' }}</a>
                <a href="{{ $h('siarc.register') }}" class="siarc-btn w-full justify-center mt-3 px-5 py-3 text-[13px] bg-white border border-[#ECEAE3] text-[#2A271F] hover:border-[#D7E4DB]"><i data-lucide="calendar-clock" class="w-4 h-4"></i>{{ $isFr ? 'Planifier un rendez-vous' : 'Book a meeting' }}</a>
            </div>

            {{-- CONTACT PERSON --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Personne de contact' : 'Contact person' }}</h3>
                <div class="flex items-center gap-3.5 pb-4 border-b border-[#ECEAE3]">
                    <span class="w-12 h-12 rounded-full bg-siarc-green flex items-center justify-center text-white font-display font-bold text-[16px] shrink-0">PT</span>
                    <div>
                        <p class="text-[14px] font-semibold text-[#1A1712] leading-tight">Paul Tchameni</p>
                        <p class="text-[12px] text-[#8A857A]">Responsable Commercial</p>
                    </div>
                </div>
                <ul class="space-y-3 text-[12.5px] text-[#2A271F] mt-4">
                    <li class="flex items-center gap-3"><i data-lucide="phone" class="w-4 h-4 text-siarc-ochre shrink-0"></i>+237 677 123 456</li>
                    <li class="flex items-center gap-3"><i data-lucide="mail" class="w-4 h-4 text-siarc-ochre shrink-0"></i>paul.tchameni@artisanatexcellence.cm</li>
                </ul>
            </div>

            {{-- DOCUMENTS --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Documents à télécharger' : 'Downloadable documents' }}</h3>
                <ul class="space-y-3">
                    @foreach($docs as [$dname,$dmeta])
                    <li class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-[#FDE8E8] flex items-center justify-center shrink-0"><i data-lucide="clipboard-list" class="w-4 h-4 text-siarc-red"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight truncate">{{ $dname }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $dmeta }}</p>
                        </div>
                        <a @if(R::has('siarc.exhibitors')) href="{{ route('siarc.exhibitors',['lang'=>$lang]) }}" @endif class="text-[#8A857A] hover:text-siarc-green shrink-0"><i data-lucide="download" class="w-4 h-4"></i></a>
                    </li>
                    @endforeach
                </ul>
                <a @if(R::has('siarc.exhibitors')) href="{{ route('siarc.exhibitors',['lang'=>$lang]) }}" @endif class="block text-center mt-4 text-[12.5px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }} →</a>
            </div>

            {{-- SOCIAL --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Réseaux sociaux' : 'Social networks' }}</h3>
                <div class="flex items-center gap-3">
                    @foreach(['#1877F2','#E4405F','#0A66C2','#FF0000','#25D366'] as $sc)
                    <a @if(R::has('siarc.exhibitors')) href="{{ route('siarc.exhibitors',['lang'=>$lang]) }}" @endif class="w-10 h-10 rounded-xl flex items-center justify-center text-white siarc-lift" style="background:{{ $sc }}">
                        <i data-lucide="globe" class="w-4 h-4"></i>
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</section>
