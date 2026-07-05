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

    $contactLink = $sLinks['storefront'] ?? ($sLinks[0]['href'] ?? $h('siarc.exhibitors'));
    $backLink    = $sLinks['exhibitors'] ?? ($sLinks[1]['href'] ?? $h('siarc.exhibitors'));

    // ── Verbatim design content (approved PNG) ──────────────────────────────
    $title = "Artisanat d'Excellence Cameroun";

    $kpis = [
        ['package',      'Produits exposés', '56'],
        ['layout-grid',  'Catégories',       '5'],
        ['users-round',  'Employés',         '24'],
        ['globe',        'Pays desservis',   '12'],
        ['award',        'Certifications',   '3'],
    ];

    $tabs = ['À propos','Produits & Services','Documents','Actualités','Galerie','Vidéos','Équipe'];

    $abouts = [
        'Produits 100% fabriqués au Cameroun',
        'Matériaux locaux et écoresponsables',
        'Design innovant et finitions haut de gamme',
        'Respect des traditions et du commerce équitable',
    ];

    $sectors = ['Arts décoratifs','Décoration intérieure','Textile & Mode','Bois & Sculpture','Accessoires & Bijoux'];

    $products = [
        ['exhibitor-prod-1','prod-1','Sculpture gardien Bamiléké','Bois'],
        ['exhibitor-prod-2','prod-2','Sac tissé motif Bamoun','Raphia & cuir'],
        ['exhibitor-prod-3','prod-3','Tabouret royal Bandjoun','Bois massif'],
        ['exhibitor-prod-4','prod-4','Panier décoratif fait main','Fibres naturelles'],
        ['exhibitor-prod-5','prod-5','Masque ancestral Fang-Beti','Bois sculpté'],
        ['exhibitor-prod-6','prod-6','Lampe artisanale ajourée','Calebasse & bois'],
    ];

    $docs = [
        ['Catalogue produits 2026','PDF · 4.2 Mo'],
        ["Présentation de l'entreprise",'PDF · 2.1 Mo'],
        ['Brochure institutionnelle','PDF · 3.8 Mo'],
    ];
@endphp

{{-- ══════════════════ PAGE HEAD ══════════════════ --}}
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
                <a href="{{ $contactLink }}" class="siarc-btn px-4 py-2.5 text-[12.5px] bg-white border border-[#ECEAE3] text-[#2A271F] hover:border-[#D7E4DB]"><i data-lucide="share-2" class="w-4 h-4"></i>{{ $isFr ? 'Partager' : 'Share' }}</a>
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

            {{-- HERO CARD (logo tile + headline + photo banner) --}}
            <div class="rounded-2xl overflow-hidden siarc-shadow-lg siarc-adire text-white relative siarc-in">
                {{-- photo banner behind, right side --}}
                <div class="absolute inset-y-0 right-0 w-[46%] hidden sm:block">
                    <img src="{{ asset('images/siarc/exhibitor-hero.png') }}" alt="" class="w-full h-full object-cover">
                    <div class="absolute inset-0" style="background:linear-gradient(90deg,var(--si-green-800) 0%,rgba(11,58,30,.65) 30%,transparent 62%)"></div>
                </div>
                <div class="relative flex flex-col sm:flex-row gap-6 p-7">
                    {{-- logo tile --}}
                    <div class="shrink-0">
                        <img src="{{ asset('images/siarc/exhibitor-logo.png') }}" alt="{{ $title }}" class="w-[130px] h-[130px] rounded-2xl object-contain bg-white siarc-shadow p-1">
                    </div>
                    {{-- headline --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h2 class="font-display text-[26px] font-extrabold leading-tight">{{ $title }}</h2>
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#7BC47F]/25 text-[#A6E6A9] border border-[#7BC47F]/40">Premium</span>
                        </div>
                        <p class="text-[13px] text-white/90 font-medium mb-3 flex flex-wrap items-center gap-x-2">
                            <span>Cameroun</span>
                            <span class="inline-block w-[18px] h-[12px] rounded-[2px] overflow-hidden align-middle" title="Cameroun">
                                <span class="flex h-full w-full"><span class="w-1/3 h-full bg-[#007A5E]"></span><span class="w-1/3 h-full bg-[#CE1126] flex items-center justify-center"><span class="text-[#FCD116] text-[9px] leading-none">★</span></span><span class="w-1/3 h-full bg-[#FCD116]"></span></span>
                            </span>
                            <span class="text-white/40">·</span> <span>Stand A12</span>
                            <span class="text-white/40">·</span> <span>Pavillon Artisanat</span>
                        </p>
                        <p class="text-[13px] text-white/75 leading-relaxed max-w-[440px]">Promotion et commercialisation des produits artisanaux haut de gamme du Cameroun.</p>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-5 text-[12px] text-white/75">
                            <span class="inline-flex items-center gap-2"><i data-lucide="calendar-days" class="w-4 h-4 text-siarc-gold"></i>Membre depuis : Mars 2024</span>
                            <span class="inline-flex items-center gap-2"><i data-lucide="eye" class="w-4 h-4 text-siarc-gold"></i>Visites : 1 256</span>
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
                <div class="p-6 grid lg:grid-cols-[1fr_290px] gap-7">
                    <div class="min-w-0">
                        <h3 class="font-display text-[18px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'À propos de nous' : 'About us' }}</h3>
                        <p class="text-[13px] text-[#55524A] leading-relaxed mb-5">Artisanat d'Excellence Cameroun regroupe plus de 200 artisans talentueux provenant de toutes les régions du Cameroun. Nous valorisons le savoir-faire traditionnel à travers des créations uniques alliant authenticité, qualité et design contemporain.</p>

                        <ul class="space-y-2.5 mb-6">
                            @foreach($abouts as $a)
                            <li class="flex items-start gap-2.5 text-[13px] text-[#2A271F]"><i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0 mt-0.5"></i>{{ $a }}</li>
                            @endforeach
                        </ul>

                        {{-- video tile --}}
                        <div class="rounded-xl overflow-hidden aspect-[16/9] flex items-center justify-center relative">
                            <img src="{{ asset('images/siarc/exhibitor-video.png') }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                            <span class="relative w-16 h-16 rounded-full bg-white/90 flex items-center justify-center siarc-shadow"><i data-lucide="play" class="w-7 h-7 text-siarc-green ml-1"></i></span>
                        </div>

                        <a href="{{ $contactLink }}" class="siarc-btn px-5 py-2.5 text-[12.5px] mt-4 bg-white border border-[#ECEAE3] text-[#2A271F] hover:border-[#D7E4DB]">{{ $isFr ? 'Voir plus' : 'See more' }}</a>
                    </div>

                    {{-- sidebar within panel --}}
                    <div class="space-y-6">
                        <div>
                            <h4 class="font-display text-[16px] font-bold text-[#1A1712] mb-3">{{ $isFr ? "Secteurs d'activité" : 'Business sectors' }}</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($sectors as $s)
                                <span class="text-[11px] font-medium px-3 py-1.5 rounded-lg bg-[#EAF2EC] text-siarc-green">{{ $s }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <h4 class="font-display text-[16px] font-bold text-[#1A1712] mb-3">Certifications</h4>
                            <ul class="space-y-3.5">
                                <li class="flex items-center gap-3 text-[12.5px] text-[#2A271F]">
                                    <span class="w-8 h-8 rounded-full bg-[#EAF2EC] flex items-center justify-center shrink-0"><i data-lucide="leaf" class="w-4 h-4 text-siarc-green"></i></span>
                                    Made in Cameroon
                                </li>
                                <li class="flex items-center gap-3 text-[12.5px] text-[#2A271F]">
                                    <span class="w-8 h-8 rounded-full bg-[#EAF2EC] flex items-center justify-center shrink-0"><i data-lucide="shield-check" class="w-4 h-4 text-siarc-green"></i></span>
                                    Commerce équitable
                                </li>
                                <li class="flex items-center gap-3 text-[12.5px] text-[#2A271F]">
                                    <span class="w-8 h-8 rounded-full bg-[#E7EEF6] flex items-center justify-center shrink-0 text-[9px] font-extrabold text-[#2764B0]">ISO</span>
                                    ISO 9001:2015
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PRODUITS PHARES --}}
            <div class="siarc-card siarc-shadow p-6 relative">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Produits phares' : 'Featured products' }}</h3>
                    <a href="{{ $contactLink }}" class="text-[12.5px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tous les produits' : 'View all products' }}</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($products as [$imgFile,$crop,$pname,$pmat])
                    <div class="group">
                        <div class="rounded-xl overflow-hidden aspect-square mb-2.5 siarc-lift bg-[#F3F0E7]">
                            <img src="{{ asset('images/siarc/'.$crop.'.png') }}" alt="{{ $pname }}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-[12px] font-semibold text-[#1A1712] leading-snug line-clamp-2">{{ $pname }}</p>
                        <p class="text-[11px] text-[#8A857A] mt-0.5">{{ $pmat }}</p>
                        <span class="inline-block mt-1.5 text-[10px] font-medium px-2 py-0.5 rounded bg-[#EAF2EC] text-siarc-green">Sur demande</span>
                    </div>
                    @endforeach
                </div>
                {{-- carousel next arrow --}}
                <button type="button" class="hidden lg:flex absolute -right-3 top-[58%] w-9 h-9 rounded-full bg-white siarc-shadow border border-[#ECEAE3] items-center justify-center text-[#55524A] hover:text-siarc-green"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
            </div>
        </div>

        {{-- ── RIGHT COLUMN ────────────────────────────────────────────── --}}
        <aside class="space-y-6">

            {{-- CONTACT --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations de contact' : 'Contact information' }}</h3>
                <ul class="space-y-3.5 text-[12.5px] text-[#2A271F]">
                    <li class="flex items-center gap-3"><i data-lucide="map-pin" class="w-4 h-4 text-siarc-ochre shrink-0"></i>Yaoundé, Cameroun</li>
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
                        <span class="w-9 h-9 rounded-lg bg-[#FDE8E8] flex items-center justify-center shrink-0"><i data-lucide="file-text" class="w-4 h-4 text-siarc-red"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight truncate">{{ $dname }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $dmeta }}</p>
                        </div>
                        <a href="{{ $contactLink }}" class="text-[#8A857A] hover:text-siarc-green shrink-0"><i data-lucide="download" class="w-4 h-4"></i></a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $contactLink }}" class="block text-center mt-4 text-[12.5px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }} →</a>
            </div>

            {{-- SOCIAL --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Réseaux sociaux' : 'Social networks' }}</h3>
                <div class="flex items-center gap-3">
                    {{-- Facebook --}}
                    <a href="{{ $contactLink }}" aria-label="Facebook" class="w-10 h-10 rounded-xl flex items-center justify-center text-white siarc-lift" style="background:#1877F2">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-[18px] h-[18px]"><path d="M22 12.06C22 6.48 17.52 2 11.94 2 6.36 2 1.88 6.48 1.88 12.06c0 5.02 3.68 9.19 8.49 9.94v-7.03H7.82v-2.91h2.55V9.85c0-2.52 1.5-3.91 3.8-3.91 1.1 0 2.25.2 2.25.2v2.47h-1.27c-1.25 0-1.64.78-1.64 1.57v1.88h2.79l-.45 2.91h-2.34V22c4.81-.75 8.49-4.92 8.49-9.94z"/></svg>
                    </a>
                    {{-- Instagram --}}
                    <a href="{{ $contactLink }}" aria-label="Instagram" class="w-10 h-10 rounded-xl flex items-center justify-center text-white siarc-lift" style="background:linear-gradient(45deg,#F58529,#DD2A7B,#8134AF)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-[18px] h-[18px]"><rect x="2" y="2" width="20" height="20" rx="5.5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.6" cy="6.4" r="1.1" fill="currentColor" stroke="none"/></svg>
                    </a>
                    {{-- LinkedIn --}}
                    <a href="{{ $contactLink }}" aria-label="LinkedIn" class="w-10 h-10 rounded-xl flex items-center justify-center text-white siarc-lift" style="background:#0A66C2">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-[18px] h-[18px]"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.4c0-1.29-.02-2.95-1.8-2.95-1.8 0-2.08 1.4-2.08 2.85V21H9z"/></svg>
                    </a>
                    {{-- YouTube --}}
                    <a href="{{ $contactLink }}" aria-label="YouTube" class="w-10 h-10 rounded-xl flex items-center justify-center text-white siarc-lift" style="background:#FF0000">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-[19px] h-[19px]"><path d="M23 12s0-3.2-.41-4.74a2.5 2.5 0 0 0-1.76-1.77C19.29 5.07 12 5.07 12 5.07s-7.29 0-8.83.42A2.5 2.5 0 0 0 1.41 7.26C1 8.8 1 12 1 12s0 3.2.41 4.74a2.5 2.5 0 0 0 1.76 1.77c1.54.42 8.83.42 8.83.42s7.29 0 8.83-.42a2.5 2.5 0 0 0 1.76-1.77C23 15.2 23 12 23 12zM9.75 15.02V8.98L15.5 12z"/></svg>
                    </a>
                    {{-- WhatsApp --}}
                    <a href="{{ $contactLink }}" aria-label="WhatsApp" class="w-10 h-10 rounded-xl flex items-center justify-center text-white siarc-lift" style="background:#25D366">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-[18px] h-[18px]"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35zM12.05 21.5h-.01a9.4 9.4 0 0 1-4.79-1.31l-.34-.2-3.56.93.95-3.47-.22-.36a9.38 9.38 0 0 1-1.44-5.01c0-5.18 4.22-9.4 9.41-9.4 2.51 0 4.87.98 6.64 2.75a9.34 9.34 0 0 1 2.75 6.65c0 5.18-4.22 9.4-9.4 9.4z"/></svg>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</section>
