@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $features = [
        ['building-2','EXPOSANTS & PAVILLONS',"Découvrez des centaines d'exposants et pavillons venus du Cameroun.",'Explorer','siarc.exhibitors'],
        ['calendar-days','PROGRAMME & ACTIVITÉS','Conférences, ateliers, panels et démonstrations tout au long de l\'événement.','Voir le programme','siarc.programme'],
        ['handshake','BUSINESS MATCHMAKING','Rencontrez, échangez et créez des opportunités d\'affaires.','Se connecter','siarc.register'],
        ['map','PLAN INTERACTIF','Trouvez facilement les stands, pavillons et services grâce à notre plan interactif.','Voir le plan','siarc.pavilions'],
        ['info','INFORMATIONS PRATIQUES','Préparez votre visite avec toutes les informations utiles et l\'accès en temps réel.','En savoir plus','siarc.register'],
    ];
    $stats = [
        ['users','300+','EXPOSANTS'],['globe','24','RÉGIONS DU CAMEROUN'],['building-2','20+','PAVILLONS THÉMATIQUES'],
        ['mic','120+','SESSIONS & ATELIERS'],['users-round','15 000+','VISITEURS ATTENDUS'],
    ];
    $modules = [
        ['01','SIARC DASHBOARD','Vue d\'ensemble en temps réel de l\'événement et statistiques clés.','siarc.admin.dashboard'],
        ['02','EXPOSANTS','Gestion complète des exposants et de leurs informations.','siarc.exhibitors'],
        ['03','PAVILLONS & STANDS','Explorez les pavillons thématiques et les stands d\'exposition.','siarc.pavilions'],
        ['04','PLAN INTERACTIF','Plan du salon interactif et allocation des stands.','siarc.pavilions'],
        ['05','INSCRIPTIONS','Enregistrement des visiteurs et contrôle d\'accès.','siarc.register'],
        ['06','B2B MATCHMAKING','Mise en relation d\'affaires et opportunités de partenariats.','siarc.register'],
        ['07','PROGRAMME','Programme complet des activités et événements.','siarc.programme'],
        ['08','INTERVENANTS','Gestion des intervenants et experts du SIARC.','siarc.speakers'],
        ['09','OPÉRATIONS','Suivi des opérations et monitoring en direct.','siarc.admin.live'],
        ['10','ANNUAIRE PUBLIC','Annuaire des exposants, pavillons et intervenants.','siarc.exhibitors'],
        ['11','TABLEAU VISITEUR','Espace personnalisé pour les visiteurs et favoris.','siarc.visitor.dashboard'],
        ['12','API & INTÉGRATIONS','Connectivité et intégrations avec d\'autres systèmes.','siarc.home'],
    ];
    $regions = [['Adamaoua','#E6B201'],['Centre','#157A43'],['Est','#C97A16'],['Extrême-Nord','#C0010C'],['Littoral','#E6B201'],['Nord','#C97A16'],['Nord-Ouest','#157A43'],['Ouest','#C0010C'],['Sud','#E6B201'],['Sud-Ouest','#157A43']];
    $programme = [
        ['09:30','10:30','CONFÉRENCE','purple','Innovation & Technologie au service de l\'artisanat','Par Dr. Alain Mbarga','Salle de Conférence A'],
        ['11:00','12:30','ATELIER','gold','Design Thinking pour artisans et créateurs','Par Awa Diop','Salle de Formation 1'],
        ['14:00','15:30','PANEL','red','Jeunes artisans & transmission des savoir-faire','Modérateur : P. Essomba','Salle de Conférence B'],
    ];
    $badge = ['purple'=>['#F0EAFB','#7C4FE0'],'gold'=>['#FDF3E0','#C97A16'],'red'=>['#FDE8E8','#C0010C']];
@endphp

{{-- ══════════════════ HERO ══════════════════ --}}
<section class="siarc-mud relative overflow-hidden border-b border-[#EDE7DA]">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0 opacity-70"></div>
    <div class="max-w-[1240px] mx-auto px-6 sm:px-10 py-12 lg:py-16 grid lg:grid-cols-2 gap-10 items-center">
        <div class="siarc-in">
            <div class="flex items-center gap-1.5 mb-6">
                @for($i=0;$i<4;$i++)<span class="w-3 h-3 rotate-45" style="background:{{ ['#157A43','#C0010C','#E6B201','#0F4824'][$i] }}"></span>@endfor
            </div>
            <h1 class="font-display font-extrabold leading-[1.04] text-[42px] sm:text-[54px] tracking-tight text-[#1A1712]">
                L'ARTISANAT<br><span class="text-siarc-green">CAMEROUNAIS</span><br>NOTRE FIERTÉ,<br>NOTRE AVENIR
            </h1>
            <p class="mt-6 text-[15px] text-[#55524A] leading-relaxed max-w-[480px]">Le rendez-vous international qui valorise la créativité, le savoir-faire et l'excellence de l'artisanat camerounais.</p>
            <div class="flex flex-wrap items-center gap-x-8 gap-y-3 mt-7">
                <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="calendar" class="w-5 h-5 text-siarc-ochre"></i>27 JUILLET – 05 AOÛT 2026</span>
                <span class="inline-flex items-center gap-2.5 text-[13px] font-semibold text-[#2A271F]"><i data-lucide="map-pin" class="w-5 h-5 text-siarc-ochre"></i>MUSÉE NATIONAL DE YAOUNDÉ</span>
            </div>
            <div class="flex flex-wrap gap-3.5 mt-9">
                <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-green px-6 py-3.5 text-[13px]">S'INSCRIRE EN TANT QUE VISITEUR <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="{{ $h('siarc.pavilions') }}" class="siarc-btn px-6 py-3.5 text-[13px] border-[1.5px] border-[#0F4824] text-[#0F4824] hover:bg-[#0F4824] hover:text-white">EXPLORER LES PAVILLONS <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
        </div>
        <div class="relative siarc-in">
            <img src="{{ asset('images/siarc/home-hero.png') }}" alt="SIARC 2026 — Musée National de Yaoundé" class="w-full rounded-2xl siarc-shadow-lg">
        </div>
    </div>
</section>

{{-- ══════════════════ FEATURE ROW ══════════════════ --}}
<section class="bg-white border-b border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y lg:divide-y-0 lg:divide-x divide-[#EFEDE6]">
        @foreach($features as [$icon,$title,$desc,$cta,$route])
        <div class="px-5 py-8 text-center">
            <span class="w-14 h-14 mx-auto rounded-2xl bg-[#F3F0E7] flex items-center justify-center mb-4"><i data-lucide="{{ $icon }}" class="w-7 h-7 text-siarc-green"></i></span>
            <h3 class="text-[12.5px] font-bold tracking-wide text-[#1A1712] mb-2">{{ $title }}</h3>
            <p class="text-[12px] text-[#7A756A] leading-relaxed mb-3">{{ $desc }}</p>
            <a href="{{ $h($route) }}" class="inline-flex items-center gap-1 text-[12.5px] font-semibold text-siarc-ochre hover:gap-2 transition-all">{{ $cta }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
        </div>
        @endforeach
    </div>
</section>

{{-- ══════════════════ STATS BAND ══════════════════ --}}
<section class="relative bg-gradient-to-r from-[#0B3A1E] to-[#0F4824] text-white overflow-hidden">
    <div class="siarc-kente-v absolute left-0 top-0 bottom-0"></div>
    <div class="siarc-kente-v absolute right-0 top-0 bottom-0" style="transform:scaleX(-1)"></div>
    <div class="max-w-[1180px] mx-auto px-10 py-8 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
        @foreach($stats as [$icon,$num,$label])
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

{{-- ══════════════════ 12 MODULES ══════════════════ --}}
<section class="siarc-mud">
    <div class="max-w-[1240px] mx-auto px-6 py-16">
        <div class="text-center mb-11"><span class="siarc-kicker text-siarc-ochre">UNE PLATEFORME COMPLÈTE À VOTRE SERVICE</span></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            @foreach($modules as [$num,$title,$desc,$route])
            <a href="{{ $h($route) }}" class="siarc-card siarc-shadow siarc-lift p-5 relative overflow-hidden">
                <span class="absolute top-3 right-4 siarc-num text-[26px] opacity-90">{{ $num }}</span>
                <h3 class="text-[12px] font-bold text-[#1A1712] pr-8 mb-2 leading-snug">{{ $title }}</h3>
                <p class="text-[11px] text-[#8A857A] leading-relaxed">{{ $desc }}</p>
                <span class="block mt-3 h-[3px] w-8 rounded-full bg-siarc-gold"></span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ 3-COLUMN: PAVILIONS / PROGRAMME / ACTEUR ══════════════════ --}}
<section class="bg-white border-t border-[#EEECE5]">
    <div class="max-w-[1240px] mx-auto px-6 py-16 grid lg:grid-cols-3 gap-6">
        {{-- pavilions --}}
        <div class="rounded-2xl overflow-hidden siarc-adire text-white p-7 flex flex-col">
            <h3 class="font-display text-[20px] font-bold mb-2">EXPLORER LES PAVILLONS</h3>
            <p class="text-[12.5px] text-white/70 leading-relaxed mb-5">Voyagez à travers la richesse culturelle et créative des régions du Cameroun.</p>
            <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 mb-6">
                @foreach($regions as [$rname,$rcolor])
                <span class="inline-flex items-center gap-2 text-[12px] text-white/85"><span class="w-2 h-2 rounded-full" style="background:{{ $rcolor }}"></span>{{ $rname }}</span>
                @endforeach
            </div>
            <a href="{{ $h('siarc.pavilions') }}" class="siarc-btn siarc-btn-primary px-5 py-2.5 text-[12.5px] mt-auto self-start">DÉCOUVRIR <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
        {{-- programme --}}
        <div class="siarc-card siarc-shadow p-7">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-display text-[20px] font-bold text-[#1A1712]">PROGRAMME À NE PAS MANQUER</h3>
                <a href="{{ $h('siarc.programme') }}" class="text-[12px] font-semibold text-siarc-green hover:underline whitespace-nowrap">Voir tout →</a>
            </div>
            <div class="space-y-4">
                @foreach($programme as [$start,$end,$type,$tone,$title,$by,$venue])
                @php $bt = $badge[$tone]; @endphp
                <div class="flex gap-4">
                    <div class="text-center shrink-0 w-12">
                        <p class="text-[13px] font-bold text-[#1A1712] leading-tight">{{ $start }}</p>
                        <p class="text-[11px] text-[#A8A498]">{{ $end }}</p>
                    </div>
                    <div class="border-l border-[#EFEDE6] pl-4 min-w-0">
                        <span class="inline-block text-[9.5px] font-bold px-2 py-0.5 rounded mb-1" style="background:{{ $bt[0] }};color:{{ $bt[1] }}">{{ $type }}</span>
                        <p class="text-[13px] font-semibold text-[#1A1712] leading-snug">{{ $title }}</p>
                        <p class="text-[11.5px] text-[#8A857A] mt-1">{{ $by }} · {{ $venue }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        {{-- acteur --}}
        <div class="rounded-2xl overflow-hidden bg-gradient-to-br from-[#14652F] to-[#042B15] text-white p-7 flex flex-col relative">
            <div class="siarc-kente absolute top-0 left-0 right-0 opacity-80"></div>
            <h3 class="font-display text-[20px] font-bold mb-3 mt-3">DEVENEZ ACTEUR DU CHANGEMENT</h3>
            <span class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center mb-4"><i data-lucide="hand-heart" class="w-7 h-7 text-siarc-gold"></i></span>
            <p class="text-[12.5px] text-white/75 leading-relaxed mb-6">Partagez votre expertise et inspirez des milliers d'acteurs de l'artisanat camerounais.</p>
            <a href="{{ $h('siarc.register') }}" class="siarc-btn siarc-btn-primary px-5 py-2.5 text-[12.5px] mt-auto self-start">PROPOSER UNE INTERVENTION <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
        </div>
    </div>
</section>

{{-- ══════════════════ APP SECTION ══════════════════ --}}
<section class="bg-[#0B3A1E] text-white relative overflow-hidden">
    <div class="max-w-[1240px] mx-auto px-6 py-12 flex flex-col md:flex-row items-center gap-8">
        <div class="md:w-[55%]">
            <h3 class="font-display text-[26px] font-bold mb-3">L'EXPÉRIENCE SIARC DANS VOTRE POCHE</h3>
            <p class="text-[13.5px] text-white/70 leading-relaxed mb-6 max-w-[440px]">Accédez à toutes les informations, plan interactif, programme et networking partout, tout le temps.</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="inline-flex items-center gap-3 bg-black rounded-xl px-5 py-2.5 border border-white/15 hover:border-white/40 transition-colors">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="white"><path d="M16.5 12.6c0-2 1.6-3 1.7-3.05a3.7 3.7 0 0 0-2.9-1.57c-1.24-.13-2.42.72-3 .72-.61 0-1.58-.7-2.6-.68a3.87 3.87 0 0 0-3.27 2C3.6 15.2 5 19 6.9 21c.9 1 1.98 2.1 3.4 2.05 1.36-.06 1.88-.88 3.53-.88 1.63 0 2.1.88 3.54.85 1.46-.03 2.4-1 3.3-2 .68-.77 1.2-1.62 1.5-2.55-.04-.02-2.66-1.02-2.68-4.06Zm-2.4-7.44a3.6 3.6 0 0 0 .83-2.6 3.68 3.68 0 0 0-2.38 1.23 3.44 3.44 0 0 0-.85 2.5 3.05 3.05 0 0 0 2.4-1.13Z"/></svg>
                    <span class="text-left leading-tight"><span class="block text-[9px] text-white/60">Télécharger dans</span><span class="block text-[15px] font-semibold">l'App Store</span></span>
                </a>
                <a href="{{ route('siarc.home', ['lang' => $lang ?? 'fr']) }}" class="inline-flex items-center gap-3 bg-black rounded-xl px-5 py-2.5 border border-white/15 hover:border-white/40 transition-colors">
                    <svg width="20" height="22" viewBox="0 0 24 24"><path d="M3.6 2.3 13 12 3.6 21.7c-.3-.2-.5-.6-.5-1V3.3c0-.4.2-.8.5-1Z" fill="#00E676"/><path d="M16.4 8.9 5.9 2.9 14.2 11l2.2-2.1Z" fill="#FF3D00"/><path d="M16.4 15.1 14.2 13l-8.3 8.1 10.5-6Z" fill="#FFC400"/><path d="M20.4 11.3 16.4 9l-2.2 2.1 2.2 2.1 4-2.3c.5-.3.5-1.3 0-1.6Z" fill="#00B0FF"/></svg>
                    <span class="text-left leading-tight"><span class="block text-[9px] text-white/60">Disponible sur</span><span class="block text-[15px] font-semibold">Google Play</span></span>
                </a>
            </div>
        </div>
        <div class="md:w-[45%] flex justify-center">
            <div class="w-[220px] h-[150px] rounded-2xl siarc-adire border border-white/10 flex items-center justify-center">
                <i data-lucide="smartphone" class="w-16 h-16 text-siarc-gold/70"></i>
            </div>
        </div>
    </div>
</section>
