@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $u = function_exists('webUser') ? webUser() : ($u ?? null);
    $visitorName = $u->name ?? 'Jean Dupont';
    $visitorEmail = $u->email ?? null;

    // Badge code — from $sStats if present, else honest default
    $badgeCode = null;
    if (isset($sStats) && is_iterable($sStats)) {
        foreach ($sStats as $st) {
            $val = is_array($st) ? ($st['value'] ?? $st['code'] ?? null) : (is_object($st) ? ($st->value ?? $st->code ?? null) : null);
            $lbl = is_array($st) ? ($st['label'] ?? '') : (is_object($st) ? ($st->label ?? '') : '');
            if ($val && (stripos((string)$lbl, 'badge') !== false || stripos((string)$lbl, 'code') !== false)) { $badgeCode = $val; break; }
        }
    }
    $badgeCode = $badgeCode ?: 'SIARC26-VIS-84217';

    // Access status
    $accessStatus = null;
    if (isset($sStats) && is_iterable($sStats)) {
        foreach ($sStats as $st) {
            $val = is_array($st) ? ($st['value'] ?? $st['status'] ?? null) : (is_object($st) ? ($st->value ?? $st->status ?? null) : null);
            $lbl = is_array($st) ? ($st['label'] ?? '') : (is_object($st) ? ($st->label ?? '') : '');
            if ($val && stripos((string)$lbl, 'accès') !== false) { $accessStatus = $val; break; }
        }
    }
    $accessStatus = $accessStatus ?: ($isFr ? 'Actif' : 'Active');

    // Quick-link cards — from $sLinks if provided, else the canonical set
    $quicklinks = [
        ['calendar-days', $isFr ? 'Programme complet' : 'Full programme', 'siarc.programme', '#157A43'],
        ['mic', $isFr ? 'Intervenants' : 'Speakers', 'siarc.speakers', '#7C4FE0'],
        ['store', $isFr ? 'Exposants' : 'Exhibitors', 'siarc.exhibitors', '#C97A16'],
        ['map', $isFr ? 'Plan interactif' : 'Interactive map', 'siarc.pavilions', '#2563EB'],
        ['flag', $isFr ? 'Pavillons' : 'Pavilions', 'siarc.pavilions', '#157A43'],
        ['star', $isFr ? 'Favoris' : 'Favourites', 'siarc.visitor.dashboard', '#E6B201'],
        ['info', $isFr ? 'Infos pratiques' : 'Practical info', 'siarc.register', '#0E7A5F'],
        ['headphones', $isFr ? 'Nous contacter' : 'Contact us', 'siarc.register', '#7C4FE0'],
    ];

    // KPI tiles (approved headline figures from the design)
    $tiles = [
        ['ticket', '2', $isFr ? 'Billet(s) actif(s)' : 'Active ticket(s)', $isFr ? 'Voir mes billets' : 'View my tickets', 'siarc.register', '#157A43', '#E7F1EA'],
        ['calendar-days', '5', $isFr ? 'Sessions favorites' : 'Favourite sessions', $isFr ? 'Voir mes favoris' : 'View favourites', 'siarc.programme', '#7C4FE0', '#F0EAFB'],
        ['calendar-days', '2', $isFr ? 'RDV planifiés' : 'Planned meetings', $isFr ? 'Voir mes rendez-vous' : 'View my meetings', 'siarc.register', '#C97A16', '#FCEFE0'],
        ['map-pin', '32', $isFr ? 'Exposants suivis' : 'Followed exhibitors', $isFr ? 'Voir mes exposants' : 'View exhibitors', 'siarc.exhibitors', '#2563EB', '#E4EDFD'],
    ];

    // Upcoming sessions — approved sample programme (from tokens exemplar)
    $sessions = [
        ['09:30','10:30','CONFÉRENCE','green','Innovation & Technologie au service de l\'artisanat','Par Dr. Alain Mbarga','Salle de Conférence A','sess-1.png', false],
        ['11:00','12:30','ATELIER','purple','Design Thinking pour artisans et créateurs','Par Awa Diop','Salle de Formation 1','sess-2.png', false],
        ['14:00','15:30','PANEL','gold','Jeunes artisans & transmission des savoir-faire','Modérateur : P. Essomba','Salle de Conférence B','sess-3.png', false],
        ['16:00','17:30','CONFÉRENCE','blue','E-commerce & plateformes digitales pour artisans','Par Jumia Consulting','Espace Innovation','sess-4.png', true],
    ];
    $badge = ['green'=>['#E7F1EA','#157A43'],'purple'=>['#F0EAFB','#7C4FE0'],'gold'=>['#FBF0DE','#B07A18'],'blue'=>['#E4EDFD','#2563EB'],'red'=>['#FDE8E8','#C0010C']];

    $recommended = [
        ['Artisanat d\'Excellence Cameroun','Bois sculpté, Décoration','Pavillon Cameroun','reco-1.png'],
        ['Afrique Naturelle SARL','Cosmétiques naturels','Pavillon Sénégal','reco-2.png'],
        ['Design Afrika','Mobilier & Décoration','Pavillon Innovation','reco-3.png'],
        ['Tissages du Sahel','Textile traditionnel','Pavillon Mali','reco-4.png'],
        ['Terre & Tradition','Poterie, Céramique','Pavillon Maroc','reco-5.png'],
    ];
@endphp

{{-- Welcome header is rendered by the portal topbar (layouts/siarc-portal). --}}

{{-- ══════════════════ MAIN GRID ══════════════════ --}}
<section>
    <div class="grid lg:grid-cols-[1fr_452px] gap-6 items-start">

        {{-- ══════ LEFT / MAIN COLUMN ══════ --}}
        <div class="space-y-6 min-w-0">

            {{-- Hero band --}}
            <div class="relative rounded-2xl overflow-hidden siarc-adire text-white siarc-shadow-lg">
                <div class="relative grid md:grid-cols-2 gap-0 items-stretch">
                    <div class="p-8 flex flex-col justify-center">
                        <h2 class="font-display text-[26px] sm:text-[30px] font-bold leading-[1.12] mb-3">
                            {{ $isFr ? "Vivez l'excellence artisanale africaine au SIARC 2026" : 'Live African craft excellence at SIARC 2026' }}
                        </h2>
                        <p class="text-[13.5px] text-white/75 leading-relaxed mb-6 max-w-[380px]">{{ $isFr ? 'Découvrez, échangez et créez des opportunités.' : 'Discover, connect and create opportunities.' }}</p>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ $h('siarc.programme') }}" class="siarc-btn siarc-btn-green px-5 py-3 text-[13px]">{{ $isFr ? 'Voir le programme' : 'View programme' }}</a>
                            <a href="{{ $h('siarc.exhibitors') }}" class="siarc-btn siarc-btn-outline px-5 py-3 text-[13px]">{{ $isFr ? 'Explorer les exposants' : 'Explore exhibitors' }}</a>
                        </div>
                    </div>
                    <div class="hidden md:block relative min-h-[210px]">
                        <img src="{{ asset('images/siarc/vdash-hero.png') }}" alt="SIARC 2026" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0B3A1E] via-transparent to-transparent"></div>
                    </div>
                </div>
            </div>

            {{-- KPI tiles --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($tiles as [$icon,$num,$label,$cta,$route,$color,$bg])
                <div class="siarc-card siarc-shadow siarc-lift p-5">
                    <span class="w-11 h-11 rounded-xl flex items-center justify-center mb-3" style="background:{{ $bg }}">
                        <i data-lucide="{{ $icon }}" class="w-5 h-5" style="color:{{ $color }}"></i>
                    </span>
                    <p class="font-display text-[28px] font-extrabold text-[#1A1712] leading-none">{{ $num }}</p>
                    <p class="text-[12px] text-[#8A857A] mt-1.5 mb-2.5">{{ $label }}</p>
                    <a href="{{ $h($route) }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green hover:gap-2 transition-all">{{ $cta }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
                </div>
                @endforeach
            </div>

            {{-- Prochaines sessions --}}
            <div class="siarc-card siarc-shadow p-6 sm:p-7">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-[20px] font-bold text-[#1A1712]">{{ $isFr ? 'Prochaines sessions pour vous' : 'Upcoming sessions for you' }}</h3>
                    <a href="{{ $h('siarc.programme') }}" class="text-[12px] font-semibold text-siarc-green hover:underline whitespace-nowrap">{{ $isFr ? 'Voir tout' : 'View all' }} →</a>
                </div>
                <div class="divide-y divide-[#F1EFE8]">
                    @foreach($sessions as [$start,$end,$type,$tone,$title,$by,$venue,$avatar,$fav])
                    @php $bt = $badge[$tone]; @endphp
                    <div class="flex items-start gap-4 py-4 first:pt-0 last:pb-0">
                        <div class="text-center shrink-0 w-14">
                            <p class="text-[13px] font-bold text-[#1A1712] leading-tight">{{ $start }}</p>
                            <p class="text-[11px] text-[#A8A498]">–</p>
                            <p class="text-[11px] text-[#A8A498] leading-tight">{{ $end }}</p>
                        </div>
                        <span class="shrink-0 w-11 h-11 rounded-full overflow-hidden bg-[#F1EFE8] border border-[#ECEAE3]">
                            <img src="{{ asset('images/siarc/'.$avatar) }}" alt="" class="w-full h-full object-cover">
                        </span>
                        <div class="min-w-0 flex-1">
                            <span class="inline-block text-[9.5px] font-bold px-2 py-0.5 rounded mb-1.5" style="background:{{ $bt[0] }};color:{{ $bt[1] }}">{{ $type }}</span>
                            <p class="text-[13.5px] font-semibold text-[#1A1712] leading-snug">{{ $title }}</p>
                            <p class="text-[11.5px] text-[#8A857A] mt-1">{{ $by }}</p>
                        </div>
                        <div class="shrink-0 flex flex-col items-end gap-2 text-right">
                            <button type="button" aria-label="{{ $isFr ? 'Favori' : 'Favourite' }}" data-toast="{{ $isFr ? 'Ajouté à vos favoris' : 'Added to favourites' }}">
                                <i data-lucide="star" class="w-[18px] h-[18px]" style="{{ $fav ? 'color:#E6B201;fill:#E6B201' : 'color:#C9C4B8' }}"></i>
                            </button>
                            <span class="hidden sm:inline-flex items-center gap-1 text-[11.5px] text-[#8A857A] whitespace-nowrap"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $venue }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Réservez vos rencontres d'affaires --}}
            <div class="relative rounded-2xl overflow-hidden siarc-adire text-white siarc-shadow">
                <img src="{{ asset('images/siarc/vdash-handshake.png') }}" alt="" class="hidden sm:block absolute top-0 right-0 h-full w-[300px] object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0B3A1E] via-[#0B3A1E]/85 to-transparent"></div>
                <div class="relative p-7">
                    <h3 class="font-display text-[20px] font-bold mb-1.5">{{ $isFr ? "Réservez vos rencontres d'affaires" : 'Book your business meetings' }}</h3>
                    <p class="text-[13px] text-white/75 leading-relaxed mb-5">{{ $isFr ? 'Rencontrez des exposants, partenaires et investisseurs.' : 'Meet exhibitors, partners and investors.' }}</p>
                    <a href="{{ $h('siarc.register') }}" class="siarc-btn px-5 py-3 text-[13px] bg-white text-[#0B3A1E]"><i data-lucide="calendar-check" class="w-4 h-4"></i>{{ $isFr ? 'Planifier un rendez-vous' : 'Schedule a meeting' }}</a>
                </div>
            </div>

            {{-- Exposants recommandés --}}
            <div class="siarc-card siarc-shadow p-6 sm:p-7">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-display text-[20px] font-bold text-[#1A1712]">{{ $isFr ? 'Exposants recommandés pour vous' : 'Exhibitors recommended for you' }}</h3>
                    <a href="{{ $h('siarc.exhibitors') }}" class="text-[12px] font-semibold text-siarc-green hover:underline whitespace-nowrap">{{ $isFr ? 'Voir tous' : 'View all' }} →</a>
                </div>
                <div class="flex items-stretch gap-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 flex-1 min-w-0">
                        @foreach($recommended as [$name,$cat,$pav,$logo])
                        <a href="{{ $h('siarc.exhibitors') }}" class="siarc-card siarc-lift p-3.5 bg-[#FBFAF7] flex items-start gap-3">
                            <span class="shrink-0 w-11 h-11 rounded-full overflow-hidden bg-white border border-[#ECEAE3] flex items-center justify-center">
                                <img src="{{ asset('images/siarc/'.$logo) }}" alt="" class="w-full h-full object-cover">
                            </span>
                            <div class="min-w-0">
                                <p class="text-[12px] font-bold text-[#1A1712] leading-snug">{{ $name }}</p>
                                <p class="text-[10.5px] text-[#8A857A] leading-tight mt-0.5">{{ $cat }}</p>
                                <p class="text-[10.5px] text-[#8A857A] leading-tight mt-2">{{ $pav }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    <button type="button" aria-label="{{ $isFr ? 'Suivant' : 'Next' }}" data-toast="{{ $isFr ? 'Voir tous les exposants recommandés' : 'View all recommended exhibitors' }}" class="hidden xl:flex shrink-0 self-center w-9 h-9 rounded-full bg-white border border-[#ECEAE3] siarc-shadow items-center justify-center text-[#55524A] hover:bg-[#F1EFE8]">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════ RIGHT / SIDEBAR COLUMN ══════ --}}
        <div class="space-y-6">

            {{-- Mon billet --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Mon billet' : 'My ticket' }}</h3>
                        <a href="{{ $h('siarc.register') }}" class="text-[12px] font-semibold text-siarc-green hover:underline whitespace-nowrap">{{ $isFr ? 'Voir tous' : 'View all' }} →</a>
                    </div>

                    <div>
                        <span class="inline-block text-[9.5px] font-bold tracking-wide px-2.5 py-1 rounded bg-[#E7F1EA] text-siarc-green mb-4">{{ $isFr ? 'VISITEUR STANDARD' : 'STANDARD VISITOR' }}</span>
                        <div class="flex gap-4">
                            <div class="shrink-0">
                                <div id="siarc-badge-qr" class="w-[100px] h-[100px] flex items-center justify-center"></div>
                            </div>
                            <div class="min-w-0">
                                <p class="font-display text-[17px] font-extrabold text-siarc-green leading-tight">SIARC 2026</p>
                                <p class="text-[11px] text-[#8A857A] leading-snug mt-1">{{ $isFr ? "Salon International de l'Artisanat du Cameroun" : 'International Craft Fair of Cameroon' }}</p>
                                <p class="mt-3 inline-flex items-center gap-1.5 text-[11px] text-[#55524A]"><i data-lucide="calendar-days" class="w-3.5 h-3.5 text-siarc-ochre"></i>27 Juil – 05 Août 2026</p>
                                <p class="mt-1 inline-flex items-center gap-1.5 text-[11px] text-[#55524A]"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-siarc-ochre"></i>Musée National de Yaoundé</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4 pt-4 border-t border-[#ECEAE3]">
                            <span class="font-mono text-[12px] font-semibold text-[#55524A] tracking-wide">#{{ $badgeCode }}</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#E7F1EA] text-siarc-green"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $accessStatus }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Accès rapides --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[18px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Accès rapides' : 'Quick access' }}</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($quicklinks as [$icon,$label,$route,$color])
                    <a href="{{ $h($route) }}" class="siarc-card siarc-lift bg-[#FBFAF7] px-2 py-4 flex flex-col items-center text-center gap-2.5">
                        <i data-lucide="{{ $icon }}" class="w-6 h-6" style="color:{{ $color }}"></i>
                        <span class="text-[11px] font-semibold text-[#2A271F] leading-tight">{{ $label }}</span>
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Live & Actualités --}}
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-display text-[18px] font-bold text-[#1A1712]">{{ $isFr ? 'Live & Actualités' : 'Live & News' }}</h3>
                    @if(R::has('siarc.live'))
                        <a href="{{ $h('siarc.live') }}" class="text-[12px] font-semibold text-siarc-green hover:underline whitespace-nowrap">{{ $isFr ? 'Voir tout' : 'View all' }} →</a>
                    @endif
                </div>
                <div class="space-y-4">
                    {{-- LIVE item --}}
                    <div class="flex items-center gap-3 rounded-xl border border-[#ECEAE3] bg-[#FBFAF7] p-3">
                        <span class="shrink-0 w-11 h-11 rounded-lg overflow-hidden border border-[#ECEAE3] bg-[#F1EFE8]">
                            <img src="{{ asset('images/siarc/vdash-news-live.png') }}" alt="" class="w-full h-full object-cover">
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="inline-flex items-center gap-1 text-[9px] font-bold px-1.5 py-0.5 rounded bg-[#C0010C] text-white tracking-wide">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>LIVE
                                </span>
                                <p class="text-[12.5px] font-semibold text-[#1A1712] leading-snug truncate">{{ $isFr ? "Cérémonie d'ouverture en direct" : 'Opening ceremony live' }}</p>
                            </div>
                            <p class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#C0010C]"><span class="w-1.5 h-1.5 rounded-full bg-[#C0010C]"></span>{{ $isFr ? 'En cours maintenant' : 'Happening now' }}</p>
                        </div>
                        <button type="button" aria-label="{{ $isFr ? 'Regarder' : 'Watch' }}" data-toast="{{ $isFr ? 'La diffusion en direct démarrera bientôt' : 'Live stream starting soon' }}" class="shrink-0 w-8 h-8 rounded-full bg-[#0B3A1E] text-white flex items-center justify-center">
                            <i data-lucide="play" class="w-4 h-4" style="fill:#fff"></i>
                        </button>
                    </div>
                    {{-- News item --}}
                    <div class="flex items-center gap-3">
                        <span class="shrink-0 w-11 h-11 rounded-lg overflow-hidden border border-[#ECEAE3] bg-[#F1EFE8]">
                            <img src="{{ asset('images/siarc/vdash-news-pavillon.png') }}" alt="" class="w-full h-full object-cover">
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-semibold text-[#1A1712] leading-snug">{{ $isFr ? 'Nouveau pavillon Afrique Centrale' : 'New Central Africa pavilion' }}</p>
                            <p class="text-[11px] text-[#8A857A] leading-snug">{{ $isFr ? 'Découvrez les artisans de la région' : 'Discover the region\'s artisans' }}</p>
                            <p class="inline-flex items-center gap-1 text-[10.5px] text-[#A8A498] mt-0.5"><i data-lucide="clock" class="w-3 h-3"></i>{{ $isFr ? 'Il y a 2 heures' : '2 hours ago' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ B2B MEETING REQUEST (live form) ══════════════════ --}}
<section class="mt-6">
    @if(session('siarc_b2b_ok'))
    <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-[#CFE8D8] bg-[#EAF6EE] px-4 py-3">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-[#157A43] shrink-0"></i>
        <p class="text-[13px] font-semibold text-[#155B33]">{{ $isFr ? 'Votre demande de rendez-vous B2B a été envoyée. L\'équipe matchmaking vous confirmera le créneau.' : 'Your B2B meeting request has been sent. The matchmaking team will confirm the slot.' }}</p>
    </div>
    @elseif(session('siarc_b2b_ko'))
    <div class="mb-4 flex items-center gap-2.5 rounded-xl border border-[#F5CFCF] bg-[#FDF0F0] px-4 py-3">
        <i data-lucide="x-circle" class="w-4 h-4 text-[#C0010C] shrink-0"></i>
        <p class="text-[13px] font-semibold text-[#8A1015]">{{ $isFr ? 'Adresse email inconnue — utilisez l\'email de votre inscription visiteur.' : 'Unknown email address — use the email from your visitor registration.' }}</p>
    </div>
    @endif
    <div class="siarc-card siarc-shadow p-5">
        <h2 class="flex items-center gap-2 text-[16px] font-bold text-[#1A1712] mb-1"><i data-lucide="handshake" class="w-4.5 h-4.5 text-siarc-green"></i>{{ $isFr ? 'Demander un rendez-vous B2B' : 'Request a B2B meeting' }}</h2>
        <p class="text-[12px] text-[#8A857A] mb-4">{{ $isFr ? 'Rencontrez un exposant en tête-à-tête à l\'Espace B2B du Musée National.' : 'Meet an exhibitor one-on-one at the B2B Space of the Musée National.' }}</p>
        <form method="POST" action="{{ route('siarc.b2b.request') }}" class="grid grid-cols-1 md:grid-cols-[1fr_1fr_auto] gap-3 items-end">
            @csrf
            <div>
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $isFr ? 'Email de votre inscription' : 'Your registration email' }}</label>
                <input type="email" name="email" required value="{{ old('email', $visitorEmail) }}" placeholder="vous@exemple.cm" class="w-full text-[13px] rounded-lg border border-[#EFEDE6] px-3.5 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">
            </div>
            <div>
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $isFr ? 'Exposant à rencontrer' : 'Exhibitor to meet' }}</label>
                <select name="exhibitor_id" required class="w-full text-[13px] rounded-lg border border-[#EFEDE6] px-3 py-2.5 bg-white text-[#3B382F]">
                    @foreach(\Illuminate\Support\Facades\DB::table('event_exhibitors as ee')->join('businesses as b','b.id','=','ee.business_id')->where('ee.event_id', function_exists('siarcEvent') ? (siarcEvent()?->id ?? 0) : 0)->orderBy('b.name_fr')->limit(50)->get(['ee.id','b.name_fr']) as $exOpt)
                    <option value="{{ $exOpt->id }}">{{ $exOpt->name_fr }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5 rounded-lg whitespace-nowrap"><i data-lucide="send" class="w-4 h-4"></i>{{ $isFr ? 'Envoyer la demande' : 'Send request' }}</button>
            <div class="md:col-span-3">
                <label class="block text-[11px] text-[#8A857A] mb-1">{{ $isFr ? 'Message (optionnel)' : 'Message (optional)' }}</label>
                <textarea name="message" rows="2" maxlength="500" placeholder="{{ $isFr ? 'Objet de la rencontre, produits qui vous intéressent…' : 'Purpose of the meeting, products of interest…' }}" class="w-full text-[13px] rounded-lg border border-[#EFEDE6] px-3.5 py-2.5 focus:outline-none focus:border-[#D8E5DC] bg-white text-[#3B382F]">{{ old('message') }}</textarea>
            </div>
        </form>
    </div>
</section>

{{-- ══════════════════ QR CODE RENDER ══════════════════ --}}
<script src="{{ asset('vendor/qrcode.min.js') }}"></script>
<script>
    (function(){
        var el = document.getElementById('siarc-badge-qr');
        if (el && window.QRCode) {
            try {
                new QRCode(el, {
                    text: @json('SIARC26|'.$badgeCode),
                    width: 96, height: 96,
                    colorDark: '#0B3A1E', colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {}
        }
    })();
</script>
