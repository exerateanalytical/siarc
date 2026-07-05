@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $eid = siarcEvent()?->id ?? 0;
    $speakerId  = DB::table('speakers')->where('event_id',$eid)->value('id');
    $sessionId  = DB::table('programme_sessions')->where('event_id',$eid)->value('id');
    $panelId    = DB::table('programme_sessions')->where('event_id',$eid)->where('type','panel')->value('id') ?: $sessionId;
    $standId    = DB::table('stands')->where('event_id',$eid)->value('id');

    // Robust route helpers with real fallbacks
    $speakersList = $h('siarc.admin.speakers');
    $programmeList = $h('siarc.admin.programme');
    $exhibitorsList = $h('siarc.admin.exhibitors');
    $editHref = $speakerId ? $h('siarc.admin.speaker', ['id'=>$speakerId]) : $speakersList;
    $session1Href = $sessionId ? $h('siarc.admin.session', ['id'=>$sessionId]) : $programmeList;
    $session2Href = $panelId ? $h('siarc.admin.session', ['id'=>$panelId]) : $programmeList;
    $backHref = $speakersList;
@endphp

<div class="siarc-in">

    {{-- ══ ACTION BAR ══ --}}
    <div class="flex flex-wrap items-center justify-end gap-2.5 mb-5">
        <a href="{{ $backHref }}" class="siarc-btn bg-white border border-[#ECEAE3] text-[#3B382F] text-[13px] px-4 py-2.5 hover:bg-[#FBFAF6]">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la liste' : 'Back to list' }}
        </a>
        <button type="button" data-toast="{{ $isFr ? 'Téléchargement du badge en préparation…' : 'Badge download in preparation…' }}" class="siarc-btn bg-white border border-[#ECEAE3] text-[#3B382F] text-[13px] px-4 py-2.5 hover:bg-[#FBFAF6]">
            <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Télécharger le badge' : 'Download badge' }}
        </button>
        <a href="{{ $editHref }}" class="siarc-btn bg-[#0B3A1E] text-white text-[13px] px-4 py-2.5 hover:bg-[#0F4824]">
            <i data-lucide="square-pen" class="w-4 h-4"></i>{{ $isFr ? "Modifier l'intervenant" : 'Edit speaker' }}
        </a>
        <button type="button" data-toast="{{ $isFr ? 'Plus d\'actions' : 'More actions' }}" class="siarc-btn bg-white border border-[#ECEAE3] text-[#8A857A] w-10 h-10 justify-center px-0 hover:bg-[#FBFAF6]">
            <i data-lucide="ellipsis-vertical" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ═══════════════ LEFT / MAIN COLUMN ═══════════════ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- ── PROFILE HEADER CARD ── --}}
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex flex-col sm:flex-row gap-6">
                    {{-- portrait (cropped from design) --}}
                    <div class="shrink-0">
                        <div class="relative w-[190px] h-[210px] rounded-2xl overflow-hidden bg-[#EDEBE4]">
                            <img src="{{ asset('images/siarc/speaker-alain-1.png') }}" alt="Dr. Alain Mbarga" class="absolute inset-0 w-full h-full object-cover">
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">Dr. Alain Mbarga</h1>
                            <span class="inline-flex items-center rounded-full bg-[#E2F3E8] text-[#157A43] text-[11px] font-semibold px-3 py-1">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</span>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-[#F0EAFB] text-[#7C4FE0] text-[11px] font-semibold px-3 py-1 mt-2.5">Conférencier</span>

                        <p class="text-[14px] font-semibold text-[#2B2822] mt-4">Expert en Innovation &amp; Transformation Digitale</p>
                        <p class="text-[13px] text-[#55524A] mt-1">TechCraft Africa</p>
                        <p class="text-[13px] text-[#55524A] mt-1 inline-flex items-center gap-1.5">Yaoundé, Cameroun <span class="text-[13px]">🇨🇲</span></p>

                        <p class="text-[13px] text-[#55524A] leading-relaxed mt-4">Dr. Alain Mbarga est expert en innovation et transformation digitale avec plus de 15 ans d'expérience dans l'accompagnement des PME africaines vers la digitalisation. Fondateur et CEO de TechCraft Africa, il conseille des gouvernements et organisations sur les stratégies d'innovation et le développement de l'écosystème numérique en Afrique.</p>
                    </div>
                </div>

                {{-- meta chips row --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-6">
                    @foreach([
                        ['users-round','#157A43','#E2F3E8','Pays','Cameroun',true],
                        ['globe','#3565DE','#E8EFFB','Langues','Français, Anglais',false],
                        ['calendar-days','#C97A16','#FDF3E0','Sessions','2 sessions',false],
                        ['shield-check','#157A43','#E2F3E8','Statut','Confirmé',false],
                        ['calendar','#7C4FE0','#F0EAFB','Inscription le','12 Mai 2026',false],
                    ] as [$icon,$col,$tile,$lbl,$val,$flag])
                    <div class="rounded-xl border border-[#ECEAE3] p-3 flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
                        <div class="min-w-0">
                            <p class="text-[10.5px] text-[#8A857A]">{{ $lbl }}</p>
                            <p class="text-[12px] font-semibold text-[#2B2822] truncate">{{ $val }}@if($flag) <span>🇨🇲</span>@endif</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── TABS ── --}}
            <div class="siarc-card siarc-shadow px-6" data-tabs="spk-detail">
                <nav class="flex items-center gap-6 overflow-x-auto text-[13px] font-medium text-[#8A857A]">
                    @foreach(['apercu'=>'Aperçu','sessions'=>'Sessions (2)','bio'=>'Biographie','medias'=>'Médias','documents'=>'Documents','pratique'=>'Informations pratiques','historique'=>'Historique'] as $key=>$label)
                        <button type="button" class="si-tab py-4 whitespace-nowrap {{ $loop->first ? 'is-active' : '' }}" data-tab="{{ $key }}">{{ $label }}</button>
                    @endforeach
                </nav>
            </div>

            {{-- ── APERÇU PANEL: À propos + Sessions assignées ── --}}
            <div data-panel="apercu" data-tabs-for="spk-detail" class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- À propos --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-3">À propos</h3>
                    <p class="text-[12.5px] text-[#55524A] leading-relaxed">Spécialiste de la transformation digitale et de l'innovation dans les PME en Afrique. Passionné par le numérique au service du développement économique inclusif. Intervenant régulier dans des conférences internationales et auteur de plusieurs publications sur l'innovation.</p>

                    <h4 class="text-[14px] font-bold text-[#1A1712] mt-5 mb-3">Domaines d'expertise</h4>
                    <ul class="space-y-2.5">
                        @foreach([
                            'Transformation digitale des PME',
                            'Innovation & Technologies émergentes',
                            'Stratégie & Management de l\'innovation',
                            'Écosystèmes d\'innovation en Afrique',
                            'Intelligence artificielle & impact social',
                        ] as $exp)
                        <li class="flex items-center gap-2 text-[12.5px] text-[#3B382F]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0"></i>{{ $exp }}
                        </li>
                        @endforeach
                    </ul>

                    <h4 class="text-[14px] font-bold text-[#1A1712] mt-6 mb-3">Réseaux sociaux</h4>
                    <div class="flex items-center gap-2.5">
                        <button type="button" data-toast="{{ $isFr ? 'Ouverture du profil LinkedIn…' : 'Opening LinkedIn profile…' }}" class="w-9 h-9 rounded-lg bg-[#0A66C2] text-white flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M4.98 3.5C4.98 4.88 3.87 6 2.5 6S0 4.88 0 3.5 1.12 1 2.5 1s2.48 1.12 2.48 2.5zM.24 8h4.52v14H.24V8zm7.5 0h4.33v1.92h.06c.6-1.14 2.08-2.34 4.28-2.34 4.58 0 5.42 3.02 5.42 6.95V22h-4.5v-6.6c0-1.57-.03-3.6-2.2-3.6-2.2 0-2.54 1.72-2.54 3.49V22h-4.5V8z"/></svg>
                        </button>
                        <button type="button" data-toast="{{ $isFr ? 'Ouverture du profil X…' : 'Opening X profile…' }}" class="w-9 h-9 rounded-lg bg-[#161513] text-white flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5"><path d="M18.244 2H21.5l-7.5 8.57L22.5 22h-6.75l-5.28-6.9L4.4 22H1.14l8.02-9.17L1 2h6.9l4.78 6.32L18.244 2zm-1.18 18h1.83L7.02 3.9H5.06l12 16.1z"/></svg>
                        </button>
                        <button type="button" data-toast="{{ $isFr ? 'Ouverture du site web…' : 'Opening website…' }}" class="w-9 h-9 rounded-lg bg-[#E2F3E8] flex items-center justify-center"><i data-lucide="globe" class="w-4 h-4 text-siarc-green"></i></button>
                        <button type="button" data-toast="{{ $isFr ? 'Ouverture de la chaîne YouTube…' : 'Opening YouTube channel…' }}" class="w-9 h-9 rounded-lg bg-[#FF0000] text-white flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2C0 8.1 0 12 0 12s0 3.9.5 5.8a3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14C24 15.9 24 12 24 12s0-3.9-.5-5.8zM9.6 15.6V8.4l6.24 3.6-6.24 3.6z"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Sessions assignées --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Sessions assignées (2)</h3>

                    <div class="space-y-3">
                        {{-- Session 1: CONFÉRENCE --}}
                        <a href="{{ $session1Href }}" class="block rounded-xl border border-[#ECEAE3] p-4 siarc-lift">
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-[#F0EAFB] flex items-center justify-center shrink-0"><i data-lucide="presentation" class="w-4 h-4 text-[#7C4FE0]"></i></span>
                                <div class="min-w-0 flex-1">
                                    <span class="inline-block text-[10px] font-bold tracking-wide text-[#7C4FE0] mb-1.5">CONFÉRENCE</span>
                                    <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">L'artisanat africain à l'ère de l'innovation et du numérique</p>
                                    <p class="text-[11.5px] text-[#8A857A] mt-2 inline-flex items-center gap-1.5"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>29 Juil. 2026 &nbsp;·&nbsp; 10:30 – 12:00</p>
                                    <p class="text-[11.5px] text-[#8A857A] mt-1 inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Salle de Conférence A</p>
                                </div>
                                <span class="inline-flex items-center rounded-md bg-[#E2F3E8] text-[#157A43] text-[10.5px] font-semibold px-2.5 py-1 whitespace-nowrap">Intervenant principal</span>
                            </div>
                        </a>

                        {{-- Session 2: PANEL --}}
                        <a href="{{ $session2Href }}" class="block rounded-xl border border-[#ECEAE3] p-4 siarc-lift">
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 rounded-lg bg-[#E8EFFB] flex items-center justify-center shrink-0"><i data-lucide="presentation" class="w-4 h-4 text-[#3565DE]"></i></span>
                                <div class="min-w-0 flex-1">
                                    <span class="inline-block text-[10px] font-bold tracking-wide text-[#3565DE] mb-1.5">PANEL</span>
                                    <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">Financement et accompagnement des artisans innovants</p>
                                    <p class="text-[11.5px] text-[#8A857A] mt-2 inline-flex items-center gap-1.5"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>31 Juil. 2026 &nbsp;·&nbsp; 16:00 – 17:30</p>
                                    <p class="text-[11.5px] text-[#8A857A] mt-1 inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>Salle de Conférence B</p>
                                </div>
                                <span class="inline-flex items-center rounded-md bg-[#E8EFFB] text-[#3565DE] text-[10.5px] font-semibold px-2.5 py-1 whitespace-nowrap">Panéliste</span>
                            </div>
                        </a>
                    </div>

                    <a href="{{ $programmeList }}" class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-[#ECEAE3] py-3 text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
                        Voir toutes les sessions <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- ── SESSIONS PANEL ── --}}
            <div data-panel="sessions" data-tabs-for="spk-detail" hidden class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Sessions assignées (2)</h3>
                <div class="space-y-3">
                    <a href="{{ $session1Href }}" class="block rounded-xl border border-[#ECEAE3] p-4 siarc-lift">
                        <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">L'artisanat africain à l'ère de l'innovation et du numérique</p>
                        <p class="text-[11.5px] text-[#8A857A] mt-2 inline-flex items-center gap-1.5"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>29 Juil. 2026 &nbsp;·&nbsp; 10:30 – 12:00 &nbsp;·&nbsp; Salle de Conférence A</p>
                    </a>
                    <a href="{{ $session2Href }}" class="block rounded-xl border border-[#ECEAE3] p-4 siarc-lift">
                        <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">Financement et accompagnement des artisans innovants</p>
                        <p class="text-[11.5px] text-[#8A857A] mt-2 inline-flex items-center gap-1.5"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>31 Juil. 2026 &nbsp;·&nbsp; 16:00 – 17:30 &nbsp;·&nbsp; Salle de Conférence B</p>
                    </a>
                </div>
                <a href="{{ $programmeList }}" class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-[#ECEAE3] py-3 text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
                    Voir toutes les sessions <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- ── BIOGRAPHIE PANEL ── --}}
            <div data-panel="bio" data-tabs-for="spk-detail" hidden class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-3">Biographie</h3>
                <p class="text-[12.5px] text-[#55524A] leading-relaxed">Dr. Alain Mbarga est expert en innovation et transformation digitale avec plus de 15 ans d'expérience dans l'accompagnement des PME africaines vers la digitalisation. Fondateur et CEO de TechCraft Africa, il conseille des gouvernements et organisations sur les stratégies d'innovation et le développement de l'écosystème numérique en Afrique.</p>
            </div>

            {{-- ── MÉDIAS PANEL ── --}}
            <div data-panel="medias" data-tabs-for="spk-detail" hidden class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Médias</h3>
                <p class="text-[12.5px] text-[#55524A]">Aucun média supplémentaire n'est associé à cet intervenant pour le moment.</p>
            </div>

            {{-- ── DOCUMENTS PANEL ── --}}
            <div data-panel="documents" data-tabs-for="spk-detail" hidden class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Documents</h3>
                <p class="text-[12.5px] text-[#55524A]">Les documents de l'intervenant sont listés dans la colonne latérale.</p>
            </div>

            {{-- ── INFORMATIONS PRATIQUES PANEL ── --}}
            <div data-panel="pratique" data-tabs-for="spk-detail" hidden class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Informations pratiques</h3>
                <p class="text-[12.5px] text-[#55524A]">Arrivée prévue le 27 juillet. Besoin d'une assistance technique pour la présentation (vidéo + slides).</p>
            </div>

            {{-- ── HISTORIQUE PANEL ── --}}
            <div data-panel="historique" data-tabs-for="spk-detail" hidden class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Historique</h3>
                <p class="text-[12.5px] text-[#55524A]">Inscription le 12 Mai 2026 · Badge généré · Confirmé.</p>
            </div>

            {{-- ── NOTES ADMIN ── --}}
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-[15px] font-bold text-[#1A1712] mb-2">Notes de l'administrateur</h3>
                        <p class="text-[12.5px] text-[#55524A] leading-relaxed">
                            Excellent intervenant, très apprécié lors des éditions précédentes. Prévoir un temps d'échange avec le public. Arrivée prévue le 27 juillet. Besoin assistance technique pour présentation (vidéo + slides).
                        </p>
                        <p class="text-[11px] text-[#B0AB9F] mt-3">Ajoutée par Jean Dupont · 12 Mai 2026</p>
                    </div>
                    <button type="button" data-toast="{{ $isFr ? 'Édition de la note…' : 'Edit note…' }}" class="w-9 h-9 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6] shrink-0"><i data-lucide="square-pen" class="w-4 h-4"></i></button>
                </div>
            </div>
        </div>

        {{-- ═══════════════ RIGHT / SIDE COLUMN ═══════════════ --}}
        <div class="space-y-5">

            {{-- Informations de contact --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Informations de contact</h3>
                <ul class="space-y-3.5">
                    @foreach([
                        ['mail','alain.mbarga@techcraft.cm'],
                        ['phone','+237 6XX XXX XXX'],
                        ['globe','www.techcraftafrica.com'],
                        ['external-link','linkedin.com/in/alainmbarga'],
                    ] as [$icon,$val])
                    <li class="flex items-center gap-3 text-[12.5px] text-[#3B382F]">
                        <span class="w-9 h-9 rounded-lg bg-[#F6F4EF] flex items-center justify-center shrink-0"><i data-lucide="{{ $icon }}" class="w-4 h-4 text-[#8A857A]"></i></span>
                        <span class="truncate">{{ $val }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- Organisation --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Organisation</h3>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="building-2" class="w-4 h-4 text-siarc-green"></i></span>
                        <span class="text-[13px] font-semibold text-[#2B2822] truncate">TechCraft Africa</span>
                    </div>
                    <a href="{{ $exhibitorsList }}" class="text-[11.5px] font-semibold text-[#3B382F] border border-[#ECEAE3] rounded-lg px-3 py-1.5 hover:bg-[#FBFAF6] whitespace-nowrap">Voir l'entreprise</a>
                </div>
            </div>

            {{-- Documents --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Documents</h3>
                <ul class="space-y-3.5">
                    @foreach([
                        ['image','#157A43','#E2F3E8','Photo de profil','JPG · 245 Ko'],
                        ['file-text','#C0010C','#FDE8E8','CV - Alain Mbarga','PDF · 1.2 Mo'],
                        ['presentation','#C97A16','#FDF3E0','Présentation - SIARC 2026','PPTX · 3.4 Mo'],
                        ['file-text','#3565DE','#E8EFFB','Bio courte','PDF · 180 Ko'],
                    ] as [$icon,$col,$tile,$title,$meta])
                    <li class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-semibold text-[#2B2822] truncate">{{ $title }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $meta }}</p>
                        </div>
                        <button type="button" data-toast="{{ $isFr ? 'Téléchargement en préparation…' : 'Download in preparation…' }}" class="text-[#8A857A] hover:text-siarc-green shrink-0"><i data-lucide="download" class="w-4 h-4"></i></button>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $speakersList }}" class="mt-4 flex items-center justify-end gap-1.5 text-[12px] font-semibold text-siarc-green hover:gap-2 transition-all">Voir tous les documents <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>

            {{-- Badge & Accréditation --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">Badge &amp; Accréditation</h3>
                <ul class="space-y-3.5 text-[12.5px]">
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">Type de badge</span>
                        <span class="inline-flex items-center rounded-full bg-[#E2F3E8] text-[#157A43] text-[11px] font-semibold px-2.5 py-1">Intervenant</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">N° de badge</span>
                        <span class="font-semibold text-[#2B2822]">SPK-026-0058</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">Accès</span>
                        <span class="font-semibold text-[#2B2822]">Toutes zones</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">Statut</span>
                        <span class="inline-flex items-center gap-1.5 font-semibold text-siarc-green"><i data-lucide="check-circle-2" class="w-4 h-4"></i>Badge généré</span>
                    </li>
                </ul>
                <button type="button" data-toast="{{ $isFr ? 'Téléchargement du badge en préparation…' : 'Badge download in preparation…' }}" class="mt-5 w-full siarc-btn bg-[#F6F4EF] border border-[#ECEAE3] text-[#3B382F] text-[12.5px] py-3 justify-center hover:bg-[#EFEDE6]">
                    <i data-lucide="download" class="w-4 h-4"></i>Voir / Télécharger le badge
                </button>
            </div>
        </div>
    </div>
</div>
