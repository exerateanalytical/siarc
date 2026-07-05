@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $features = [
        ['landmark','EXPOSANTS & PAVILLONS',"Découvrez des centaines d'exposants et pavillons venus du Cameroun.",'Explorer','siarc.exhibitors'],
        ['calendar-days','PROGRAMME & ACTIVITÉS','Conférences, ateliers, panels et démonstrations tout au long de l\'événement.','Voir le programme','siarc.programme'],
        ['handshake','BUSINESS MATCHMAKING','Rencontrez, échangez et créez des opportunités d\'affaires.','Se connecter','siarc.register'],
        ['map','PLAN INTERACTIF','Trouvez facilement les stands, pavillons et services grâce à notre plan interactif.','Voir le plan','siarc.pavilions'],
        ['info','INFORMATIONS PRATIQUES','Préparez votre visite avec toutes les informations utiles et l\'accès en temps réel.','En savoir plus','siarc.register'],
    ];
    $stats = [
        ['users','300+','EXPOSANTS'],['globe','24','RÉGIONS DU CAMEROUN'],['landmark','20+','PAVILLONS THÉMATIQUES'],
        ['mic','120+','SESSIONS & ATELIERS'],['users-round','15 000+','VISITEURS ATTENDUS'],
    ];
    // [num, icon, iconColor, title, desc, route]
    $modules = [
        ['01','trending-up','#157A43','SIARC DASHBOARD','Vue d\'ensemble en temps réel de l\'événement et statistiques clés.','siarc.admin.dashboard'],
        ['02','store','#C97A16','EXPOSANTS','Gestion complète des exposants et de leurs informations.','siarc.exhibitors'],
        ['03','landmark','#1A1712','PAVILLONS & STANDS','Explorez les pavillons thématiques et les stands d\'exposition.','siarc.pavilions'],
        ['04','map-pin','#C97A16','PLAN INTERACTIF','Plan du salon interactif et allocation des stands.','siarc.pavilions'],
        ['05','id-card','#157A43','INSCRIPTIONS','Enregistrement des visiteurs et contrôle d\'accès.','siarc.register'],
        ['06','handshake','#C0010C','B2B MATCHMAKING','Mise en relation d\'affaires et opportunités de partenariats.','siarc.register'],
        ['07','calendar-days','#C0010C','PROGRAMME','Programme complet des activités et événements.','siarc.programme'],
        ['08','mic','#C97A16','INTERVENANTS','Gestion des intervenants et experts du SIARC.','siarc.speakers'],
        ['09','activity','#157A43','OPÉRATIONS','Suivi des opérations et monitoring en direct.','siarc.admin.live'],
        ['10','book-open','#1A1712','ANNUAIRE PUBLIC','Annuaire des exposants, pavillons et intervenants.','siarc.exhibitors'],
        ['11','user','#C0010C','TABLEAU VISITEUR','Espace personnalisé pour les visiteurs et favoris.','siarc.visitor.dashboard'],
        ['12','settings','#C97A16','API & INTÉGRATIONS','Connectivité et intégrations avec d\'autres systèmes.','siarc.home'],
    ];
    $regions = [['Adamaoua','#157A43'],['Centre','#C97A16'],['Est','#157A43'],['Extrême-Nord','#C0010C'],['Littoral','#E6B201'],['Nord','#C97A16'],['Nord-Ouest','#157A43'],['Ouest','#C0010C'],['Sud','#C0010C'],['Sud-Ouest','#157A43']];
    // [start, end, type, tone, title, by, venue, avatar]
    $programme = [
        ['09:30','10:30','CONFÉRENCE','green','Innovation & Technologie au service de l\'artisanat','Par Dr. Alain Mbarga','Salle de Conférence A','home4-spk1.png'],
        ['11:00','12:30','ATELIER','purple','Design Thinking pour artisans et créateurs','Par Awa Diop','Salle de Formation 1','home4-spk2.png'],
        ['14:00','15:30','PANEL','red','Jeunes artisans & transmission des savoir-faire','Modérateur : P. Essomba','Salle de Conférence B','home4-spk3.png'],
    ];
    $badge = [
        'green'=>['#E7F1EA','#157A43'],
        'purple'=>['#F0EAFB','#7C4FE0'],
        'red'=>['#FDE8E8','#C0010C'],
    ];
@endphp

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 pt-12 lg:pt-14 pb-14 grid lg:grid-cols-2 gap-10 lg:gap-8 items-center">
        <div class="siarc-in">
            <div class="flex items-center gap-1.5 mb-6">
                @foreach(['#157A43','#C0010C','#E6B201','#0F4824'] as $dc)<span class="w-3 h-3 rotate-45" style="background:{{ $dc }}"></span>@endforeach
            </div>
            <h1 class="font-display font-extrabold leading-[1.02] text-[44px] sm:text-[56px] tracking-tight text-[#1A1712]">
                L'ARTISANAT<br><span class="text-siarc-green">CAMEROUNAIS</span><br>NOTRE FIERTÉ,<br>NOTRE AVENIR
            </h1>
            <p class="mt-6 text-[15px] text-[#55524A] leading-relaxed max-w-[430px]">Le rendez-vous international qui valorise la créativité, le savoir-faire et l'excellence de l'artisanat camerounais.</p>
            <div class="flex flex-wrap items-center gap-x-9 gap-y-3 mt-8">
                <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="calendar" class="w-5 h-5 text-siarc-ochre"></i>27 JUILLET – 05 AOÛT 2026</span>
                <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="map-pin" class="w-5 h-5 text-siarc-ochre"></i>MUSÉE NATIONAL<br>DE YAOUNDÉ</span>
            </div>
            <div class="flex flex-wrap gap-3.5 mt-9">
                <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-green px-6 py-3.5 text-[12.5px] tracking-wide">S'INSCRIRE EN TANT QUE VISITEUR <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="{{ $h('siarc.pavilions') }}" class="siarc-btn px-6 py-3.5 text-[12.5px] tracking-wide border-[1.5px] border-[#0F4824] text-[#0F4824] hover:bg-[#0F4824] hover:text-white">EXPLORER LES PAVILLONS <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
        </div>
        <div class="relative siarc-in">
            <img src="{{ asset('images/siarc/home4-hero.png') }}" alt="SIARC 2026 — Artisanat & Musée National de Yaoundé" class="w-full rounded-2xl siarc-shadow-lg">
            {{-- video play overlay --}}
            <button type="button" data-toast="Vidéo bientôt disponible" class="absolute left-5 bottom-5 flex items-center gap-3.5 text-left">
                <span class="w-12 h-12 rounded-full bg-[#0B3A1E]/85 backdrop-blur flex items-center justify-center ring-2 ring-white/25">
                    <i data-lucide="play" class="w-5 h-5 text-white ml-0.5" style="fill:#fff"></i>
                </span>
                <span class="leading-tight text-white drop-shadow">
                    <span class="block text-[13px] font-bold tracking-wide">DÉCOUVRIR LE SIARC 2026</span>
                    <span class="block text-[12px] text-white/85">Regarder la vidéo</span>
                </span>
            </button>
            {{-- carousel dots --}}
            <div class="absolute left-5 bottom-2 flex items-center gap-1.5">
                <button type="button" data-toast="Diapositive 1" class="w-4 h-1.5 rounded-full bg-siarc-gold"></button>
                <button type="button" data-toast="Diapositive 2" class="w-1.5 h-1.5 rounded-full bg-white/60"></button>
                <button type="button" data-toast="Diapositive 3" class="w-1.5 h-1.5 rounded-full bg-siarc-green"></button>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ FEATURE ROW ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-[#EFEDE6]">
        @foreach($features as [$icon,$title,$desc,$cta,$route])
        <div class="px-6 py-9 text-center">
            <span class="w-14 h-14 mx-auto rounded-full bg-[#F3F0E7] flex items-center justify-center mb-4">
                @if($loop->last)
                    <i data-lucide="{{ $icon }}" class="w-7 h-7 text-siarc-red"></i>
                @elseif($loop->index === 1)
                    <i data-lucide="{{ $icon }}" class="w-7 h-7 text-siarc-red"></i>
                @elseif($loop->index === 2)
                    <i data-lucide="{{ $icon }}" class="w-7 h-7 text-siarc-gold"></i>
                @else
                    <i data-lucide="{{ $icon }}" class="w-7 h-7 text-siarc-green"></i>
                @endif
            </span>
            <h3 class="text-[12.5px] font-bold tracking-wide text-[#1A1712] mb-2">{{ $title }}</h3>
            <p class="text-[12px] text-[#7A756A] leading-relaxed mb-3">{{ $desc }}</p>
            <a href="{{ $h($route) }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-bold text-[#2A271F] hover:gap-2.5 transition-all">{{ $cta }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>
        @endforeach
    </div>
</section>

{{-- ══════════════════ STATS BAND ══════════════════ --}}
<section class="relative bg-gradient-to-r from-[#0B3A1E] to-[#0F4824] text-white overflow-hidden">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0"></div>
    <div class="siarc-kente-v absolute right-0 top-0 bottom-0" style="transform:scaleX(-1)"></div>
    <div class="max-w-[1180px] mx-auto px-12 py-8 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
        @foreach($stats as [$icon,$num,$label])
        <div class="flex items-center gap-3.5 justify-center lg:justify-start">
            <i data-lucide="{{ $icon }}" class="w-9 h-9 text-siarc-gold shrink-0" style="stroke-width:1.5"></i>
            <div>
                <p class="font-display text-[30px] font-extrabold leading-none text-siarc-gold">{{ $num }}</p>
                <p class="text-[10.5px] font-semibold tracking-wide text-white/80 mt-1">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- ══════════════════ 12 MODULES ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 py-16">
        <div class="text-center mb-11"><span class="siarc-kicker text-[#1A1712]" style="font-size:17px;">UNE PLATEFORME COMPLÈTE À VOTRE SERVICE</span></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach($modules as [$num,$icon,$icolor,$title,$desc,$route])
            <a href="{{ $h($route) }}" class="rounded-[14px] bg-[#FCFBF8] border border-[#EDEAE1] siarc-lift p-5 relative overflow-hidden">
                <div class="flex items-center gap-2.5 mb-3">
                    <i data-lucide="{{ $icon }}" class="w-6 h-6" style="color:{{ $icolor }};stroke-width:1.8"></i>
                    <span class="siarc-num text-[22px]">{{ $num }}</span>
                </div>
                <h3 class="text-[11.5px] font-bold text-[#1A1712] mb-2 leading-snug tracking-wide">{{ $title }}</h3>
                <p class="text-[11px] text-[#8A857A] leading-relaxed">{{ $desc }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ 3-COLUMN: PAVILIONS / PROGRAMME / ACTEUR ══════════════════ --}}
<section class="bg-white border-t border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 py-16 grid lg:grid-cols-3 gap-6">
        {{-- pavilions --}}
        <div class="siarc-card siarc-shadow p-7 flex flex-col">
            <h3 class="font-display text-[19px] font-bold text-[#1A1712] mb-2">EXPLORER LES PAVILLONS</h3>
            <p class="text-[12.5px] text-[#7A756A] leading-relaxed mb-5">Voyagez à travers la richesse culturelle et créative des régions du Cameroun.</p>
            <div class="flex gap-5">
                <a href="{{ $h('siarc.pavilions') }}" class="siarc-btn siarc-btn-green px-5 py-2.5 text-[12px] self-start whitespace-nowrap">DÉCOUVRIR</a>
                <div class="grid grid-cols-1 gap-y-1.5">
                    @foreach($regions as [$rname,$rcolor])
                    <span class="inline-flex items-center gap-2 text-[12px] text-[#4A463E]"><span class="w-2 h-2 rounded-full" style="background:{{ $rcolor }}"></span>{{ $rname }}</span>
                    @endforeach
                </div>
            </div>
            <img src="{{ asset('images/siarc/home4-pavilions.png') }}" alt="Pavillons régionaux du Cameroun" class="w-full rounded-xl mt-5 object-cover">
        </div>
        {{-- programme --}}
        <div class="siarc-card siarc-shadow p-7">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-[19px] font-bold text-[#1A1712]">PROGRAMME À NE PAS MANQUER</h3>
                <a href="{{ $h('siarc.programme') }}" class="text-[12px] font-semibold text-[#1A1712] hover:text-siarc-green whitespace-nowrap">Voir tout →</a>
            </div>
            <div class="space-y-5">
                @foreach($programme as [$start,$end,$type,$tone,$title,$by,$venue,$avatar])
                @php $bt = $badge[$tone]; @endphp
                <div class="flex gap-3.5">
                    <div class="text-center shrink-0 w-11 pt-0.5">
                        <p class="text-[12.5px] font-bold text-[#1A1712] leading-tight">{{ $start }}</p>
                        <p class="text-[11px] text-[#A8A498]">{{ $end }}</p>
                    </div>
                    <img src="{{ asset('images/siarc/'.$avatar) }}" alt="{{ $by }}" class="w-10 h-10 rounded-full object-cover shrink-0">
                    <div class="min-w-0">
                        <span class="inline-block text-[9px] font-bold px-2 py-0.5 rounded mb-1.5 tracking-wide" style="background:{{ $bt[0] }};color:{{ $bt[1] }}">{{ $type }}</span>
                        <p class="text-[13px] font-semibold text-[#1A1712] leading-snug">{{ $title }}</p>
                        <p class="text-[11px] text-[#8A857A] mt-1 flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1"><i data-lucide="user" class="w-3 h-3"></i>{{ $by }}</span>
                            <span class="inline-flex items-center gap-1"><i data-lucide="map-pin" class="w-3 h-3"></i>{{ $venue }}</span>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            <a href="{{ $h('siarc.programme') }}" class="inline-flex items-center gap-1.5 mt-5 text-[12px] font-semibold text-siarc-green hover:gap-2.5 transition-all">Voir tout le programme <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>
        {{-- acteur --}}
        <div class="siarc-card siarc-shadow p-7 flex flex-col">
            <h3 class="font-display text-[19px] font-bold text-[#1A1712] mb-4">DEVENEZ ACTEUR DU CHANGEMENT</h3>
            <img src="{{ asset('images/siarc/home4-acteur.png') }}" alt="Artisan camerounais au travail" class="w-full rounded-xl object-cover mb-5">
            <p class="text-[12.5px] text-[#7A756A] leading-relaxed mb-5">Partagez votre expertise et inspirer des milliers d'acteurs de l'artisanat camerounais.</p>
            <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-green px-5 py-2.5 text-[12px] mt-auto self-start">PROPOSER UNE INTERVENTION</a>
        </div>
    </div>
</section>

{{-- ══════════════════ APP SECTION ══════════════════ --}}
<section class="bg-[#0B0B08] text-white relative overflow-hidden">
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-9 flex items-center gap-8">
        <div class="hidden sm:block shrink-0 w-[70px] self-stretch siarc-adire rounded-r-xl -ml-10 -my-9"></div>
        <div class="flex-1 min-w-0">
            <h3 class="font-display text-[24px] font-bold mb-2.5">L'EXPÉRIENCE SIARC DANS VOTRE POCHE</h3>
            <p class="text-[13px] text-white/70 leading-relaxed max-w-[430px]">Accédez à toutes les informations, plan interactif, programme et networking partout, tout le temps.</p>
        </div>
        <div class="hidden md:flex flex-col gap-3 shrink-0">
            <a href="{{ $h('siarc.home') }}" class="inline-flex items-center gap-3 bg-black rounded-xl px-5 py-2.5 border border-white/15 hover:border-white/40 transition-colors">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="white"><path d="M16.5 12.6c0-2 1.6-3 1.7-3.05a3.7 3.7 0 0 0-2.9-1.57c-1.24-.13-2.42.72-3 .72-.61 0-1.58-.7-2.6-.68a3.87 3.87 0 0 0-3.27 2C3.6 15.2 5 19 6.9 21c.9 1 1.98 2.1 3.4 2.05 1.36-.06 1.88-.88 3.53-.88 1.63 0 2.1.88 3.54.85 1.46-.03 2.4-1 3.3-2 .68-.77 1.2-1.62 1.5-2.55-.04-.02-2.66-1.02-2.68-4.06Zm-2.4-7.44a3.6 3.6 0 0 0 .83-2.6 3.68 3.68 0 0 0-2.38 1.23 3.44 3.44 0 0 0-.85 2.5 3.05 3.05 0 0 0 2.4-1.13Z"/></svg>
                <span class="text-left leading-tight"><span class="block text-[9px] text-white/60">Télécharger dans</span><span class="block text-[15px] font-semibold">l'App Store</span></span>
            </a>
            <a href="{{ $h('siarc.home') }}" class="inline-flex items-center gap-3 bg-black rounded-xl px-5 py-2.5 border border-white/15 hover:border-white/40 transition-colors">
                <svg width="22" height="24" viewBox="0 0 24 24"><path d="M3.6 2.3 13 12 3.6 21.7c-.3-.2-.5-.6-.5-1V3.3c0-.4.2-.8.5-1Z" fill="#00E676"/><path d="M16.4 8.9 5.9 2.9 14.2 11l2.2-2.1Z" fill="#FF3D00"/><path d="M16.4 15.1 14.2 13l-8.3 8.1 10.5-6Z" fill="#FFC400"/><path d="M20.4 11.3 16.4 9l-2.2 2.1 2.2 2.1 4-2.3c.5-.3.5-1.3 0-1.6Z" fill="#00B0FF"/></svg>
                <span class="text-left leading-tight"><span class="block text-[9px] text-white/60">DISPONIBLE SUR</span><span class="block text-[15px] font-semibold">Google Play</span></span>
            </a>
        </div>
        <div class="hidden lg:block shrink-0 self-end -mb-9">
            <img src="{{ asset('images/siarc/home4-phone.png') }}" alt="Application mobile SIARC 2026" class="w-[160px]">
        </div>
    </div>
</section>

{{-- ══════════════════ NEWSLETTER BAND ══════════════════ --}}
<section class="bg-gradient-to-r from-[#C97A16] to-[#E6B201] text-white relative overflow-hidden">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-90"></div>
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-7 flex flex-col md:flex-row items-center gap-6">
        <div class="flex items-center gap-4 md:flex-1">
            <span class="w-12 h-12 rounded-xl bg-white/15 flex items-center justify-center shrink-0"><i data-lucide="mail" class="w-6 h-6 text-white"></i></span>
            <div>
                <h3 class="font-display text-[19px] font-bold leading-tight">RESTEZ INFORMÉ</h3>
                <p class="text-[12.5px] text-white/90 leading-snug mt-0.5">Abonnez-vous à notre newsletter pour recevoir toutes les actualités du SIARC 2026.</p>
            </div>
        </div>
        <form action="{{ $h('siarc.register') }}" method="GET" class="flex w-full md:w-auto shrink-0">
            <input type="email" name="email" placeholder="Votre adresse e-mail" class="rounded-l-lg px-4 py-3 text-[13px] text-[#1A1712] bg-white w-full md:w-[280px] outline-none border-0" aria-label="Votre adresse e-mail">
            <button type="submit" class="rounded-r-lg px-6 py-3 text-[12.5px] font-bold tracking-wide bg-[#8A0B0B] text-white hover:bg-[#A01010] transition-colors whitespace-nowrap">S'ABONNER</button>
        </form>
    </div>
</section>
