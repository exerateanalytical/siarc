@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang  = $lang ?? 'fr';
    $isFr  = $isFr ?? ($lang === 'fr');
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : null;

    // ── Real IDs so detail links never 404 ──
    $eid        = siarcEvent()?->id ?? 0;
    $workshopId = DB::table('programme_sessions')->where('event_id',$eid)->where('type','workshop')->value('id')
                  ?: DB::table('programme_sessions')->where('event_id',$eid)->value('id');
    $speakerId  = DB::table('speakers')->where('event_id',$eid)->value('id');

    // ── Navigational targets ──
    $backHref     = $h('siarc.admin.programme') ?? $h('siarc.admin.dashboard') ?? url()->previous();
    $editHref     = ($workshopId ? $h('siarc.admin.workshop', ['id'=>$workshopId]) : null)
                    ?? ($workshopId ? $h('siarc.admin.session', ['id'=>$workshopId]) : null)
                    ?? $h('siarc.admin.programme') ?? $backHref;
    $speakersHref = $h('siarc.admin.speakers') ?? $backHref;
    $speakerHref  = fn($i) => ($speakerId ? $h('siarc.admin.speaker', ['id'=>$speakerId]) : null) ?? $speakersHref;

    // Tabs — [label, count|null, key]
    $tabs = [
        ['Aperçu', null, 'apercu'],
        ['Programme détaillé', null, 'programme'],
        ['Intervenants', '2', 'intervenants'],
        ['Informations pratiques', null, 'infos'],
        ['Documents', null, 'documents'],
        ['Participants', null, 'participants'],
    ];

    // "Ce que vous allez apprendre" outcomes
    $outcomes = [
        ['line-chart',  'Tendances design 2026',            'Découvrir les couleurs, matériaux et formes en vogue.'],
        ['presentation','Design thinking appliqué à l’artisanat', 'Méthodologie pour créer des solutions innovantes.'],
        ['shield-check','Intégration du patrimoine culturel',     'Transformer l’héritage en valeur ajoutée.'],
        ['layers',      'Prototypage & présentation',            'Concevoir et présenter une idée de produit.'],
    ];

    // Learning objectives checklist
    $objectives = [
        'Comprendre les tendances design actuelles et leur application à l’artisanat',
        'Utiliser des techniques créatives pour moderniser les produits artisanaux',
        'Valoriser le patrimoine culturel comme avantage compétitif',
        'Développer un prototype de produit ou concept innovant',
    ];

    // Detailed programme (timeline)
    $agenda = [
        ['14:00 – 14:15','Accueil & introduction','Présentation des objectifs et des intervenants',false],
        ['14:15 – 14:45','Tendances & inspirations','Panorama des tendances design 2026',false],
        ['14:45 – 15:30','Atelier pratique – Partie 1','Exercice : réinventer un produit traditionnel',false],
        ['15:30 – 15:45','Pause café','',true],
        ['15:45 – 16:20','Atelier pratique – Partie 2','Prototypage & feedback en groupe',false],
        ['16:20 – 16:30','Conclusion & évaluation','Synthèse, Q&A et ressources',false],
    ];

    // Speakers
    $speakers = [
        ['workshop-speaker-1.png','Marie Claire Nguimatsia','Animatrice','#F0EAFB','#7C4FE0','Designer & Consultante','Cameroun','Spécialiste du design de produits et de la valorisation de l’artisanat africain. 10+ ans d’expérience.'],
        ['workshop-speaker-2.png','Paul Tchameni','Expert invité','#E8EFFB','#3565DE','CEO, TechCraft Africa','Cameroun','Expert en innovation et développement de produits artisanaux pour le marché international.'],
    ];

    // Informations pratiques rows (order matches the design, top to bottom)
    $infos = [
        ['door-open',       'Lieu',               'Atelier 2 – Espace Créatif'],
        ['package',         'Matériel fourni',    'Oui (outils de design, échantillons)'],
        ['briefcase',       'Matériel à apporter','Carnet, crayon, ordinateur portable (facultatif)'],
        ['users-round',     'Capacité',           '30 participants'],
        ['ticket',          'Inscription',        'Gratuite (inscription obligatoire)'],
    ];

    // Documents & ressources
    $docs = [
        ['file-text','Guide des tendances design 2026','PDF · 3.2 Mo'],
        ['file-text','Fiche d’exercices - Atelier Design','PDF · 1.8 Mo'],
        ['file-text','Exemples de produits innovants','PDF · 2.6 Mo'],
    ];
@endphp

<div class="siarc-in">

    {{-- ══ BACK LINK ══ --}}
    <a href="{{ $backHref }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#55524A] hover:text-siarc-green mb-3 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>Retour au programme
    </a>

    {{-- ══ HEADER ROW ══ --}}
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h1 class="font-display text-[26px] md:text-[30px] font-extrabold text-[#161513] leading-tight">Détail de l’atelier</h1>
            <div class="flex flex-wrap items-center gap-2.5 mt-3">
                <span class="inline-flex items-center text-[11px] font-bold tracking-wide uppercase px-3 py-1.5 rounded-full bg-[#FDF3E0] text-siarc-ochre">Atelier pratique</span>
                <span class="inline-flex items-center text-[11px] font-bold tracking-wide uppercase px-3 py-1.5 rounded-full bg-[#E2F3E8] text-siarc-green">Places limitées</span>
            </div>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" data-toast="Atelier ajouté à votre calendrier" class="siarc-btn border border-[#E1DED5] bg-white text-[#3B382F] px-4 py-2.5 text-[13px] hover:bg-[#FBFAF6]">
                <i data-lucide="calendar-days" class="w-4 h-4"></i>Ajouter au calendrier
            </button>
            <a href="{{ $editHref }}" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[13px] siarc-shadow">
                <i data-lucide="square-pen" class="w-4 h-4"></i>Modifier l’atelier
            </a>
            <button type="button" data-toast="Plus d’options bientôt disponibles" class="w-10 h-10 rounded-xl border border-[#E1DED5] bg-white text-[#8A857A] flex items-center justify-center hover:bg-[#FBFAF6]">
                <i data-lucide="more-vertical" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div class="grid xl:grid-cols-3 gap-5 items-start">

        {{-- ── LEFT (2 cols) ── --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Title band: green schedule tile + title/desc/meta --}}
            <div class="siarc-card siarc-shadow p-5">
                <div class="grid md:grid-cols-[minmax(0,200px)_1fr] gap-5">

                    {{-- Green schedule tile --}}
                    <div class="siarc-adire rounded-2xl p-5 text-white flex flex-col justify-center gap-4">
                        <div class="flex items-center gap-3">
                            <i data-lucide="calendar-days" class="w-[18px] h-[18px] text-siarc-gold shrink-0"></i>
                            <div class="leading-tight">
                                <p class="text-[13px] font-bold">Mercredi</p>
                                <p class="text-[13px] font-bold">30 Juillet 2026</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="clock" class="w-[18px] h-[18px] text-siarc-gold shrink-0"></i>
                            <p class="text-[13px] font-bold">14:00 – 16:30</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <i data-lucide="hourglass" class="w-[18px] h-[18px] text-siarc-gold shrink-0"></i>
                            <p class="text-[13px] font-bold">2h 30 min</p>
                        </div>
                    </div>

                    {{-- Title + description + meta --}}
                    <div class="min-w-0 flex flex-col">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="font-display text-[22px] md:text-[25px] font-extrabold text-[#161513] leading-snug">Design &amp; tendances : valoriser le patrimoine culturel à travers l’innovation</h2>
                            <button type="button" data-toast="Atelier enregistré dans vos favoris" class="w-9 h-9 rounded-lg border border-[#E1DED5] text-[#8A857A] flex items-center justify-center shrink-0 hover:bg-[#FBFAF6]" aria-label="Enregistrer">
                                <i data-lucide="bookmark" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="text-[13.5px] text-[#55524A] leading-relaxed mt-2.5">Atelier pratique pour explorer les techniques de design contemporain appliquées aux produits artisanaux afin d'accroître leur valeur et leur compétitivité sur les marchés.</p>

                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2.5 mt-4 pt-4 border-t border-[#F1EFE9]">
                            <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A]"></i>Atelier 2 – Espace Créatif</span>
                            <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="globe" class="w-4 h-4 text-[#8A857A]"></i>Français</span>
                            <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="users-round" class="w-4 h-4 text-[#8A857A]"></i>30 places</span>
                            <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]">
                                <span class="flex items-center gap-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-siarc-green"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-siarc-green"></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#D8D4C8]"></span>
                                </span>
                                Niveau : Intermédiaire
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab nav --}}
            <div data-tabs="workshop" class="border-b border-[#ECEAE3] overflow-x-auto">
                <div class="flex items-center gap-1 min-w-max">
                    @foreach($tabs as [$label,$count,$key])
                        <button type="button" class="si-tab {{ $loop->first ? 'is-active' : '' }} relative px-4 py-3 text-[13px] font-semibold whitespace-nowrap transition-colors" data-tab="{{ $key }}">
                            {{ $label }}@if($count)<span class="ml-1">({{ $count }})</span>@endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Panel: Aperçu --}}
            <div data-panel="apercu" data-tabs-for="workshop" class="grid md:grid-cols-2 gap-5">

                {{-- À propos de cet atelier --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-3">À propos de cet atelier</h3>
                    <p class="text-[13px] text-[#55524A] leading-relaxed">Les artisans font face au défi d'adapter leurs créations aux goûts contemporains sans perdre leur identité culturelle. Cet atelier vous apportera des méthodes et outils concrets pour intégrer innovation, design et patrimoine dans vos produits.</p>

                    <h4 class="text-[14px] font-bold text-[#1A1712] mt-5 mb-3">Objectifs d'apprentissage</h4>
                    <ul class="space-y-3">
                        @foreach($objectives as $obj)
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green mt-0.5 shrink-0"></i>
                            <span class="text-[12.5px] text-[#3B382F] leading-snug">{{ $obj }}</span>
                        </li>
                        @endforeach
                    </ul>

                    <div class="rounded-xl siarc-mud border border-[#ECEAE3] p-4 mt-5 flex items-start gap-3">
                        <i data-lucide="users-round" class="w-5 h-5 text-siarc-ochre mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[12.5px] font-bold text-[#1A1712]">À qui s'adresse cet atelier ?</p>
                            <p class="text-[12px] text-[#55524A] mt-1 leading-snug">Artisans, designers, créateurs, étudiants en arts appliqués, entrepreneurs culturels et responsables de production.</p>
                        </div>
                    </div>
                </div>

                {{-- Ce que vous allez apprendre --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Ce que vous allez apprendre</h3>
                    <ul class="space-y-4">
                        @foreach($outcomes as [$oi,$ot,$osub])
                        <li class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="{{ $oi }}" class="w-[18px] h-[18px] text-siarc-green"></i></span>
                            <div>
                                <p class="text-[13px] font-bold text-[#1A1712] leading-tight">{{ $ot }}</p>
                                <p class="text-[12px] text-[#8A857A] mt-0.5 leading-snug">{{ $osub }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <div class="rounded-xl bg-[#E9F5EE] border border-[#CFE7D8] p-4 mt-5 flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-siarc-green mt-0.5 shrink-0"></i>
                        <p class="text-[12px] text-[#2A5D3D] leading-snug">Chaque participant repartira avec un mini plan d'action personnalisé et des ressources exclusives.</p>
                    </div>
                </div>
            </div>

            {{-- Panel: Programme détaillé --}}
            <div data-panel="programme" data-tabs-for="workshop" hidden>
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-5">Programme détaillé</h3>
                    <ol class="relative">
                        @foreach($agenda as $ai => [$atime,$atitle,$asub,$abreak])
                        <li class="flex gap-4 pb-5 last:pb-0 relative">
                            <div class="flex flex-col items-center shrink-0 pt-0.5">
                                <span class="w-3 h-3 rounded-full border-2 {{ $abreak ? 'border-siarc-ochre bg-white' : 'border-siarc-green bg-siarc-green' }} z-10"></span>
                                @if(!$loop->last)<span class="w-px flex-1 bg-[#E4E1D8] mt-1"></span>@endif
                            </div>
                            <div class="grid sm:grid-cols-[120px_1fr] gap-x-6 gap-y-0.5 min-w-0 flex-1">
                                <span class="text-[12.5px] font-semibold text-[#8A857A] whitespace-nowrap">{{ $atime }}</span>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-bold {{ $abreak ? 'text-siarc-ochre' : 'text-[#1A1712]' }} leading-tight">{{ $atitle }}</p>
                                    @if($asub)<p class="text-[12px] text-[#8A857A] mt-0.5">{{ $asub }}</p>@endif
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- Panel: Intervenants (placeholder — full list lives in the sidebar card) --}}
            <div data-panel="intervenants" data-tabs-for="workshop" hidden>
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Intervenants</h3>
                    <p class="text-[13px] text-[#55524A] leading-relaxed">Retrouvez le profil complet des 2 intervenants de cet atelier dans le panneau latéral.</p>
                    <a href="{{ $speakersHref }}" class="inline-flex items-center gap-2 mt-4 text-[13px] font-bold text-siarc-green">
                        <span>Voir tous les intervenants</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- Panel: Informations pratiques (placeholder — full card in sidebar) --}}
            <div data-panel="infos" data-tabs-for="workshop" hidden>
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Informations pratiques</h3>
                    <p class="text-[13px] text-[#55524A] leading-relaxed">Lieu, matériel, capacité et modalités d'inscription sont détaillés dans le panneau latéral.</p>
                </div>
            </div>

            {{-- Panel: Documents (placeholder — full card in sidebar) --}}
            <div data-panel="documents" data-tabs-for="workshop" hidden>
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Documents &amp; ressources</h3>
                    <p class="text-[13px] text-[#55524A] leading-relaxed">Les supports et ressources de l'atelier sont disponibles dans le panneau latéral.</p>
                </div>
            </div>

            {{-- Panel: Participants (placeholder) --}}
            <div data-panel="participants" data-tabs-for="workshop" hidden>
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Participants</h3>
                    <p class="text-[13px] text-[#55524A] leading-relaxed">30 places disponibles. La liste des participants inscrits sera affichée ici une fois les inscriptions ouvertes.</p>
                </div>
            </div>
        </div>

        {{-- ── RIGHT (1 col) ── --}}
        <div class="space-y-5">

            {{-- Intervenants --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Intervenants</h3>
                <div class="space-y-5">
                    @foreach($speakers as [$img,$name,$role,$roleBg,$roleFg,$job,$country,$bio])
                    <a href="{{ $speakerHref($loop->index) }}" class="block {{ !$loop->last ? 'pb-5 border-b border-[#F1EFE9]' : '' }}">
                        <div class="flex items-start gap-3">
                            <img src="{{ asset('images/siarc/'.$img) }}" alt="{{ $name }}" class="w-12 h-12 rounded-full object-cover shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center flex-wrap gap-2">
                                    <p class="text-[13.5px] font-bold text-[#1A1712]">{{ $name }}</p>
                                    <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full" style="background:{{ $roleBg }};color:{{ $roleFg }}">{{ $role }}</span>
                                </div>
                                <p class="text-[12px] text-[#55524A] mt-0.5">{{ $job }}</p>
                                <p class="text-[12px] text-[#8A857A]">{{ $country }}</p>
                            </div>
                        </div>
                        <p class="text-[12px] text-[#55524A] leading-snug mt-2.5">{{ $bio }}</p>
                    </a>
                    @endforeach
                </div>
                <a href="{{ $speakersHref }}" class="flex items-center justify-between w-full mt-4 pt-1 text-[13px] font-bold text-siarc-green">
                    <span>Voir tous les intervenants (2)</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- Informations pratiques --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Informations pratiques</h3>
                <ul class="space-y-4">
                    @foreach($infos as [$icon,$k,$v])
                    <li class="flex items-start gap-3">
                        <i data-lucide="{{ $icon }}" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div class="grid grid-cols-[minmax(0,120px)_1fr] gap-3 flex-1 min-w-0">
                            <p class="text-[12.5px] text-[#8A857A]">{{ $k }}</p>
                            <p class="text-[12.5px] font-semibold text-[#1A1712]">{{ $v }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Documents & ressources --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Documents &amp; ressources</h3>
                <ul class="space-y-3">
                    @foreach($docs as [$icon,$name,$meta])
                    <li>
                        <button type="button" data-toast="Téléchargement de « {{ $name }} »…" class="w-full text-left flex items-center gap-3 rounded-xl border border-[#ECEAE3] p-3 hover:bg-[#FBFAF6] transition-colors">
                            <span class="w-9 h-9 rounded-lg bg-[#FDE8E8] flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] text-siarc-red"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-[12.5px] font-bold text-[#1A1712] truncate">{{ $name }}</p>
                                <p class="text-[11.5px] text-[#8A857A] mt-0.5">{{ $meta }}</p>
                            </div>
                            <i data-lucide="download" class="w-4 h-4 text-[#8A857A] shrink-0"></i>
                        </button>
                    </li>
                    @endforeach
                </ul>
                <button type="button" data-toast="Toutes les ressources bientôt disponibles" class="flex items-center justify-end gap-2 w-full mt-4 text-[13px] font-bold text-siarc-green">
                    <span>Voir toutes les ressources</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</div>
