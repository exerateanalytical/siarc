@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';
    // Real ids so detail links never 404
    $eid = siarcEvent()?->id ?? 0;
    $speakerId = DB::table('speakers')->where('event_id',$eid)->value('id');
    $sessionId = DB::table('programme_sessions')->where('event_id',$eid)->value('id');
    $exhibitorSlug = DB::table('event_exhibitors as ee')->join('businesses as b','b.id','=','ee.business_id')->where('ee.event_id',$eid)->value('b.slug');
    // Resolved routes (fall back to real list routes when detail id/slug is null)
    $speakersList  = $h('siarc.speakers');
    $programme     = $h('siarc.programme');
    $sessionUrl    = $sessionId ? $h('siarc.admin.session', ['id'=>$sessionId]) : $programme;
    $companyUrl    = $exhibitorSlug ? $h('siarc.exhibitor', ['slug'=>$exhibitorSlug]) : $h('siarc.exhibitors');
    // Cameroon flag (green/red/yellow with star) — inline so it matches the PNG exactly.
    $flag = '<span class="inline-flex items-center align-middle rounded-[2px] overflow-hidden border border-black/10 shrink-0" style="width:20px;height:13px"><span style="width:6.66px;height:13px;background:#007A5E"></span><span style="width:6.66px;height:13px;background:#CE1126;display:flex;align-items:center;justify-content:center;color:#FCD116;font-size:9px;line-height:1">★</span><span style="width:6.66px;height:13px;background:#FCD116"></span></span>';
@endphp

{{-- ══════════════════ IN-BODY HEADER (title + breadcrumb) ══════════════════ --}}
<section class="bg-[#F5F3EE] border-b border-[#ECEAE3]">
    <div class="max-w-[1360px] mx-auto px-6 sm:px-10 pt-8 pb-5">
        <div class="flex items-start justify-between gap-6 flex-wrap">
            <div>
                <h1 class="font-display text-[28px] font-bold text-[#1A1712] leading-tight">{{ $isFr ? 'Détails de l\'intervenant' : 'Speaker details' }}</h1>
                <nav class="flex items-center gap-2 text-[13px] text-[#8A857A] mt-1.5">
                    <a href="{{ $speakersList }}" class="hover:text-siarc-green transition-colors">{{ $isFr ? 'Intervenants' : 'Speakers' }}</a>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    <span class="text-[#55524A]">Dr. Alain Mbarga</span>
                </nav>
            </div>
            {{-- action buttons --}}
            <div class="flex items-center gap-2.5">
                <a href="{{ $speakersList }}" class="inline-flex items-center gap-2 bg-white border border-[#E4E1D8] text-[#3A362E] text-[13px] font-medium px-4 py-2.5 rounded-xl hover:bg-[#FAF8F3] transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la liste' : 'Back to list' }}
                </a>
                <button type="button" data-toast="{{ $isFr ? 'Téléchargement du badge en préparation…' : 'Badge download in progress…' }}" class="inline-flex items-center gap-2 bg-white border border-[#E4E1D8] text-[#3A362E] text-[13px] font-medium px-4 py-2.5 rounded-xl hover:bg-[#FAF8F3] transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Télécharger le badge' : 'Download badge' }}
                </button>
                <a href="{{ $companyUrl }}" class="inline-flex items-center gap-2 bg-[#0B3A1E] text-white text-[13px] font-semibold px-4 py-2.5 rounded-xl hover:brightness-110 transition-all">
                    <i data-lucide="square-pen" class="w-4 h-4"></i>{{ $isFr ? 'Modifier l\'intervenant' : 'Edit speaker' }}
                </a>
                <button type="button" data-toast="{{ $isFr ? 'Plus d\'options bientôt disponibles' : 'More options coming soon' }}" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-[#E4E1D8] text-[#3A362E] rounded-xl hover:bg-[#FAF8F3] transition-colors">
                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ BODY ══════════════════ --}}
<section class="bg-[#F5F3EE]">
    <div class="max-w-[1360px] mx-auto px-6 sm:px-10 py-6 grid lg:grid-cols-[1fr_352px] gap-6 items-start">

        {{-- ══════════ MAIN COLUMN ══════════ --}}
        <div class="space-y-6 min-w-0">

            {{-- ── Identity card ── --}}
            <div class="siarc-card siarc-shadow p-6 sm:p-7">
                <div class="grid sm:grid-cols-[200px_1fr] gap-6">
                    {{-- photo --}}
                    <div class="relative w-[200px]">
                        <img src="{{ asset('images/siarc/speaker-alain-mbarga.png') }}" alt="Dr. Alain Mbarga"
                             class="w-[200px] h-[200px] object-cover rounded-2xl">
                        <span class="absolute bottom-2.5 left-2.5 inline-flex items-center gap-1.5 bg-white text-[#3A362E] text-[11px] font-medium px-2.5 py-1.5 rounded-lg shadow-md">
                            {{ $isFr ? 'Photo vérifiée' : 'Photo verified' }}
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-siarc-green"></i>
                        </span>
                    </div>
                    {{-- identity --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="font-display text-[26px] font-bold text-[#1A1712] leading-tight">Dr. Alain Mbarga</h2>
                            <span class="inline-flex items-center bg-[#E7F4EC] text-siarc-green text-[12px] font-semibold px-3 py-1 rounded-full">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</span>
                        </div>
                        <span class="inline-flex items-center bg-[#F0EBFB] text-[#7C4DD1] text-[12px] font-semibold px-3 py-1 rounded-full mt-2.5">{{ $isFr ? 'Conférencier' : 'Speaker' }}</span>

                        <p class="text-[14.5px] font-semibold text-[#2A2721] mt-4">{{ $isFr ? 'Expert en Innovation & Transformation Digitale' : 'Innovation & Digital Transformation Expert' }}</p>
                        <p class="text-[14px] text-[#55524A] mt-1">TechCraft Africa</p>
                        <p class="inline-flex items-center gap-2 text-[14px] text-[#55524A] mt-1">Yaoundé, Cameroun {!! $flag !!}</p>

                        <p class="text-[13.5px] text-[#55524A] leading-[1.7] mt-4">Dr. Alain Mbarga est expert en innovation et transformation digitale avec plus de 15 ans d'expérience dans l'accompagnement des PME africaines vers la digitalisation. Fondateur et CEO de TechCraft Africa, il conseille des gouvernements et organisations sur les stratégies d'innovation et le développement de l'écosystème numérique en Afrique.</p>
                    </div>
                </div>

                {{-- meta chips --}}
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-6">
                    {{-- Pays --}}
                    <div class="rounded-xl border border-[#EDEAE2] bg-white p-3.5">
                        <div class="flex items-center gap-2 text-[11px] text-[#8A857A]">
                            <i data-lucide="users-round" class="w-4 h-4 text-siarc-green"></i>{{ $isFr ? 'Pays' : 'Country' }}
                        </div>
                        <p class="inline-flex items-center gap-1.5 text-[13.5px] font-semibold text-[#1A1712] mt-1.5">Cameroun {!! $flag !!}</p>
                    </div>
                    {{-- Langues --}}
                    <div class="rounded-xl border border-[#EDEAE2] bg-white p-3.5">
                        <div class="flex items-center gap-2 text-[11px] text-[#8A857A]">
                            <i data-lucide="globe" class="w-4 h-4 text-[#3B6FD4]"></i>{{ $isFr ? 'Langues' : 'Languages' }}
                        </div>
                        <p class="text-[13.5px] font-semibold text-[#1A1712] mt-1.5">{{ $isFr ? 'Français, Anglais' : 'French, English' }}</p>
                    </div>
                    {{-- Sessions --}}
                    <div class="rounded-xl border border-[#EDEAE2] bg-white p-3.5">
                        <div class="flex items-center gap-2 text-[11px] text-[#8A857A]">
                            <i data-lucide="calendar-days" class="w-4 h-4 text-siarc-ochre"></i>Sessions
                        </div>
                        <p class="text-[13.5px] font-semibold text-[#1A1712] mt-1.5">2 sessions</p>
                    </div>
                    {{-- Statut --}}
                    <div class="rounded-xl border border-[#EDEAE2] bg-white p-3.5">
                        <div class="flex items-center gap-2 text-[11px] text-[#8A857A]">
                            <i data-lucide="shield-check" class="w-4 h-4 text-siarc-green"></i>{{ $isFr ? 'Statut' : 'Status' }}
                        </div>
                        <p class="text-[13.5px] font-semibold text-siarc-green mt-1.5">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</p>
                    </div>
                    {{-- Inscription le --}}
                    <div class="rounded-xl border border-[#EDEAE2] bg-white p-3.5">
                        <div class="flex items-center gap-2 text-[11px] text-[#8A857A]">
                            <i data-lucide="calendar" class="w-4 h-4 text-[#7C4DD1]"></i>{{ $isFr ? 'Inscription le' : 'Registered on' }}
                        </div>
                        <p class="text-[13.5px] font-semibold text-[#1A1712] mt-1.5">12 Mai 2026</p>
                    </div>
                </div>
            </div>

            {{-- ── Tabs ── --}}
            <div class="border-b border-[#ECEAE3] -mt-2" data-tabs="speakerTabs">
                <div class="flex items-center gap-8 text-[14px] overflow-x-auto">
                    <button type="button" class="si-tab is-active relative pb-3 whitespace-nowrap" data-tab="overview">
                        {{ $isFr ? 'Aperçu' : 'Overview' }}
                    </button>
                    <button type="button" class="si-tab pb-3 whitespace-nowrap" data-tab="sessions">Sessions (2)</button>
                    <button type="button" class="si-tab pb-3 whitespace-nowrap" data-tab="bio">Biographie</button>
                    <button type="button" class="si-tab pb-3 whitespace-nowrap" data-tab="media">Médias</button>
                    <button type="button" class="si-tab pb-3 whitespace-nowrap" data-tab="documents">Documents</button>
                    <button type="button" class="si-tab pb-3 whitespace-nowrap" data-tab="practical">{{ $isFr ? 'Informations pratiques' : 'Practical information' }}</button>
                    <button type="button" class="si-tab pb-3 whitespace-nowrap" data-tab="history">{{ $isFr ? 'Historique' : 'History' }}</button>
                </div>
            </div>

            {{-- ── Overview panel: À propos + Sessions assignées ── --}}
            <div data-panel="overview" data-tabs-for="speakerTabs" class="space-y-6">
            <div class="grid lg:grid-cols-2 gap-6">
                {{-- À propos --}}
                <div class="siarc-card siarc-shadow p-6 sm:p-7">
                    <h3 class="font-display text-[19px] font-bold text-[#1A1712]">{{ $isFr ? 'À propos' : 'About' }}</h3>
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7] mt-3">Spécialiste de la transformation digitale et de l'innovation dans les PME en Afrique. Passionné par le numérique au service du développement économique inclusif. Intervenant régulier dans des conférences internationales et auteur de plusieurs publications sur l'innovation.</p>

                    <h4 class="text-[15px] font-bold text-[#1A1712] mt-6 mb-3">{{ $isFr ? 'Domaines d\'expertise' : 'Areas of expertise' }}</h4>
                    <ul class="space-y-2.5">
                        @foreach([
                            'Transformation digitale des PME',
                            'Innovation & Technologies émergentes',
                            'Stratégie & Management de l\'innovation',
                            'Écosystèmes d\'innovation en Afrique',
                            'Intelligence artificielle & impact social',
                        ] as $exp)
                        <li class="flex items-center gap-2.5 text-[13.5px] text-[#3A362E]">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0"></i>{{ $exp }}
                        </li>
                        @endforeach
                    </ul>

                    <h4 class="text-[15px] font-bold text-[#1A1712] mt-6 mb-3">{{ $isFr ? 'Réseaux sociaux' : 'Social networks' }}</h4>
                    <div class="flex items-center gap-2.5">
                        <a href="{{ $companyUrl }}" aria-label="LinkedIn" class="w-9 h-9 rounded-lg flex items-center justify-center bg-[#0A66C2] text-white hover:brightness-110 transition">
                            <svg viewBox="0 0 24 24" class="w-4.5 h-4.5" fill="currentColor"><path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.06 2.06 0 1 1 0-4.13 2.06 2.06 0 0 1 0 4.13zM7.12 20.45H3.55V9h3.57v11.45zM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0z"/></svg>
                        </a>
                        <a href="{{ $companyUrl }}" aria-label="X" class="w-9 h-9 rounded-lg flex items-center justify-center bg-[#111111] text-white hover:brightness-125 transition">
                            <svg viewBox="0 0 24 24" class="w-4 h-4" fill="currentColor"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.6l5.24 6.93 6.06-6.93zm-1.29 19.5h2.04L6.48 3.24H4.29L17.61 20.65z"/></svg>
                        </a>
                        <a href="{{ $companyUrl }}" aria-label="Site web" class="w-9 h-9 rounded-lg flex items-center justify-center bg-white border border-[#E4E1D8] text-[#3A362E] hover:bg-[#FAF8F3] transition">
                            <i data-lucide="globe" class="w-4.5 h-4.5"></i>
                        </a>
                        <a href="{{ $companyUrl }}" aria-label="YouTube" class="w-9 h-9 rounded-lg flex items-center justify-center bg-[#FF0000] text-white hover:brightness-110 transition">
                            <svg viewBox="0 0 24 24" class="w-5 h-5" fill="currentColor"><path d="M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2C0 8.08 0 12 0 12s0 3.92.5 5.8a3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14C24 15.92 24 12 24 12s0-3.92-.5-5.8zM9.6 15.6V8.4l6.2 3.6-6.2 3.6z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Sessions assignées --}}
                <div class="siarc-card siarc-shadow p-6 sm:p-7">
                    <h3 class="font-display text-[19px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Sessions assignées' : 'Assigned sessions' }} (2)</h3>

                    {{-- Session 1 --}}
                    <a href="{{ $sessionUrl }}" class="flex gap-3.5 group">
                        <span class="w-10 h-10 rounded-xl bg-[#F0EBFB] flex items-center justify-center shrink-0"><i data-lucide="presentation" class="w-5 h-5 text-[#7C4DD1]"></i></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="text-[10.5px] font-bold tracking-wide text-[#C97A16]">CONFÉRENCE</span>
                                    <p class="text-[14px] font-semibold text-[#1A1712] leading-snug mt-1 group-hover:text-siarc-green transition-colors">L'artisanat africain à l'ère de l'innovation et du numérique</p>
                                </div>
                                <span class="inline-flex items-center bg-[#E7F4EC] text-siarc-green text-[11px] font-semibold px-2.5 py-1 rounded-md whitespace-nowrap shrink-0">{{ $isFr ? 'Intervenant principal' : 'Lead speaker' }}</span>
                            </div>
                            <div class="mt-2 space-y-1.5">
                                <span class="flex items-center gap-2 text-[12.5px] text-[#8A857A]"><i data-lucide="calendar" class="w-4 h-4"></i>29 Juil. 2026 &nbsp;•&nbsp; 10:30 – 12:00</span>
                                <span class="flex items-center gap-2 text-[12.5px] text-[#8A857A]"><i data-lucide="map-pin" class="w-4 h-4"></i>Salle de Conférence A</span>
                            </div>
                        </div>
                    </a>

                    <div class="border-t border-[#ECEAE3] my-4"></div>

                    {{-- Session 2 --}}
                    <a href="{{ $sessionUrl }}" class="flex gap-3.5 group">
                        <span class="w-10 h-10 rounded-xl bg-[#EAF1FC] flex items-center justify-center shrink-0"><i data-lucide="users" class="w-5 h-5 text-[#3B6FD4]"></i></span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <span class="text-[10.5px] font-bold tracking-wide text-[#3B6FD4]">PANEL</span>
                                    <p class="text-[14px] font-semibold text-[#1A1712] leading-snug mt-1 group-hover:text-siarc-green transition-colors">Financement et accompagnement des artisans innovants</p>
                                </div>
                                <span class="inline-flex items-center bg-[#EAF1FC] text-[#3B6FD4] text-[11px] font-semibold px-2.5 py-1 rounded-md whitespace-nowrap shrink-0">{{ $isFr ? 'Paneliste' : 'Panelist' }}</span>
                            </div>
                            <div class="mt-2 space-y-1.5">
                                <span class="flex items-center gap-2 text-[12.5px] text-[#8A857A]"><i data-lucide="calendar" class="w-4 h-4"></i>31 Juil. 2026 &nbsp;•&nbsp; 16:00 – 17:30</span>
                                <span class="flex items-center gap-2 text-[12.5px] text-[#8A857A]"><i data-lucide="map-pin" class="w-4 h-4"></i>Salle de Conférence B</span>
                            </div>
                        </div>
                    </a>

                    <a href="{{ $programme }}" class="mt-5 flex items-center justify-center gap-2 w-full bg-white border border-[#E4E1D8] text-[#3A362E] text-[13px] font-semibold py-3 rounded-xl hover:bg-[#FAF8F3] transition-colors">
                        {{ $isFr ? 'Voir toutes les sessions' : 'View all sessions' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- ── Notes de l'administrateur ── --}}
            <div class="siarc-card siarc-shadow p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="font-display text-[19px] font-bold text-[#1A1712]">{{ $isFr ? 'Notes de l\'administrateur' : 'Administrator notes' }}</h3>
                        <p class="text-[13.5px] text-[#55524A] leading-[1.7] mt-3">Excellent intervenant, très apprécié lors des éditions précédentes. Prévoir un temps d'échange avec le public. Arrivée prévue le 27 juillet. Besoin assistance technique pour présentation (vidéo + slides).</p>
                        <p class="text-[12.5px] text-[#8A857A] mt-4">{{ $isFr ? 'Ajoutée par' : 'Added by' }} Jean Dupont &nbsp;•&nbsp; 12 Mai 2026</p>
                    </div>
                    <button type="button" data-toast="{{ $isFr ? 'Édition de la note bientôt disponible' : 'Note editing coming soon' }}" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-[#E4E1D8] text-[#3A362E] rounded-xl hover:bg-[#FAF8F3] transition-colors shrink-0">
                        <i data-lucide="square-pen" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
            </div>{{-- /overview panel --}}

            {{-- ── Other tab panels (honest placeholders) ── --}}
            <div data-panel="sessions" data-tabs-for="speakerTabs" hidden>
                <div class="siarc-card siarc-shadow p-6 sm:p-7 text-center">
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7]">{{ $isFr ? 'Cet intervenant participe à 2 sessions du programme.' : 'This speaker takes part in 2 programme sessions.' }}</p>
                    <a href="{{ $programme }}" class="mt-5 inline-flex items-center justify-center gap-2 bg-white border border-[#E4E1D8] text-[#3A362E] text-[13px] font-semibold px-5 py-3 rounded-xl hover:bg-[#FAF8F3] transition-colors">
                        {{ $isFr ? 'Voir le programme complet' : 'View full programme' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
            <div data-panel="bio" data-tabs-for="speakerTabs" hidden>
                <div class="siarc-card siarc-shadow p-6 sm:p-7">
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7]">Dr. Alain Mbarga est expert en innovation et transformation digitale avec plus de 15 ans d'expérience dans l'accompagnement des PME africaines vers la digitalisation. Fondateur et CEO de TechCraft Africa, il conseille des gouvernements et organisations sur les stratégies d'innovation et le développement de l'écosystème numérique en Afrique.</p>
                </div>
            </div>
            <div data-panel="media" data-tabs-for="speakerTabs" hidden>
                <div class="siarc-card siarc-shadow p-6 sm:p-7 text-center">
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7]">{{ $isFr ? 'Aucun média public disponible pour le moment.' : 'No public media available yet.' }}</p>
                </div>
            </div>
            <div data-panel="documents" data-tabs-for="speakerTabs" hidden>
                <div class="siarc-card siarc-shadow p-6 sm:p-7 text-center">
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7]">{{ $isFr ? 'Les documents de l\'intervenant sont listés dans le panneau latéral.' : 'The speaker documents are listed in the side panel.' }}</p>
                </div>
            </div>
            <div data-panel="practical" data-tabs-for="speakerTabs" hidden>
                <div class="siarc-card siarc-shadow p-6 sm:p-7 text-center">
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7]">{{ $isFr ? 'Informations pratiques à venir.' : 'Practical information coming soon.' }}</p>
                </div>
            </div>
            <div data-panel="history" data-tabs-for="speakerTabs" hidden>
                <div class="siarc-card siarc-shadow p-6 sm:p-7 text-center">
                    <p class="text-[13.5px] text-[#55524A] leading-[1.7]">{{ $isFr ? 'Inscription le 12 Mai 2026 · Statut confirmé.' : 'Registered on 12 May 2026 · Status confirmed.' }}</p>
                </div>
            </div>
        </div>

        {{-- ══════════ RIGHT RAIL ══════════ --}}
        <aside class="space-y-6">
            {{-- Informations de contact --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations de contact' : 'Contact information' }}</h3>
                <div class="space-y-3.5 text-[13.5px] text-[#3A362E]">
                    <a href="mailto:alain.mbarga@techcraft.cm" class="flex items-center gap-3 hover:text-siarc-green transition-colors"><i data-lucide="mail" class="w-4 h-4 text-[#8A857A] shrink-0"></i>alain.mbarga@techcraft.cm</a>
                    <span class="flex items-center gap-3"><i data-lucide="phone" class="w-4 h-4 text-[#8A857A] shrink-0"></i>+237 6XX XXX XXX</span>
                    <a href="{{ $companyUrl }}" class="flex items-center gap-3 hover:text-siarc-green transition-colors"><i data-lucide="globe" class="w-4 h-4 text-[#8A857A] shrink-0"></i>www.techcraftafrica.com</a>
                    <a href="{{ $companyUrl }}" class="flex items-center gap-3 hover:text-siarc-green transition-colors"><i data-lucide="linkedin" class="w-4 h-4 text-[#8A857A] shrink-0"></i>linkedin.com/in/alainmbarga</a>
                </div>
            </div>

            {{-- Organisation --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">Organisation</h3>
                <div class="flex items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-3 text-[14px] font-semibold text-[#1A1712]">
                        <i data-lucide="users-round" class="w-5 h-5 text-[#55524A]"></i>TechCraft Africa
                    </span>
                    <a href="{{ $companyUrl }}" class="inline-flex items-center bg-white border border-[#7C4DD1]/40 text-[#7C4DD1] text-[12px] font-semibold px-3 py-1.5 rounded-lg hover:bg-[#F7F3FE] transition-colors">{{ $isFr ? 'Voir l\'entreprise' : 'View company' }}</a>
                </div>
            </div>

            {{-- Documents --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">Documents</h3>
                <div class="space-y-4">
                    @foreach([
                        ['Photo de profil','JPG • 245 Ko','image','#7C4DD1','#F0EBFB'],
                        ['CV - Alain Mbarga','PDF • 1.2 Mo','file-text','#C0010C','#FBEBEC'],
                        ['Présentation - SIARC 2026','PPTX • 3.4 Mo','presentation','#C97A16','#FBF0E1'],
                        ['Bio courte','PDF • 180 Ko','file-text','#3B6FD4','#EAF1FC'],
                    ] as $doc)
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $doc[4] }}"><i data-lucide="{{ $doc[2] }}" class="w-4 h-4" style="color:{{ $doc[3] }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13.5px] font-semibold text-[#1A1712] leading-tight truncate">{{ $doc[0] }}</p>
                            <p class="text-[12px] text-[#8A857A] mt-0.5">{{ $doc[1] }}</p>
                        </div>
                        <button type="button" data-toast="{{ $isFr ? 'Téléchargement en préparation…' : 'Download in progress…' }}" class="text-[#8A857A] hover:text-siarc-green transition-colors shrink-0"><i data-lucide="download" class="w-4.5 h-4.5"></i></button>
                    </div>
                    @endforeach
                </div>
                <a href="{{ $speakersList }}" class="mt-5 flex items-center justify-end gap-2 text-[13px] font-semibold text-siarc-green hover:gap-3 transition-all">
                    {{ $isFr ? 'Voir tous les documents' : 'View all documents' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            {{-- Badge & Accréditation --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="font-display text-[17px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Badge & Accréditation' : 'Badge & Accreditation' }}</h3>
                <div class="space-y-3.5 text-[13.5px]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8A857A]">{{ $isFr ? 'Type de badge' : 'Badge type' }}</span>
                        <span class="inline-flex items-center bg-[#E7F4EC] text-siarc-green text-[12px] font-semibold px-3 py-1 rounded-md">Intervenant</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8A857A]">N° de badge</span>
                        <span class="font-semibold text-[#1A1712]">SPK-026-0058</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8A857A]">Accès</span>
                        <span class="font-semibold text-[#1A1712]">Toutes zones</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-[#8A857A]">Statut</span>
                        <span class="inline-flex items-center gap-1.5 font-semibold text-siarc-green"><i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ $isFr ? 'Badge généré' : 'Badge generated' }}</span>
                    </div>
                </div>
                <button type="button" data-toast="{{ $isFr ? 'Téléchargement du badge en préparation…' : 'Badge download in progress…' }}" class="mt-5 flex items-center justify-center gap-2 w-full bg-white border border-[#E4E1D8] text-[#3A362E] text-[13px] font-semibold py-3 rounded-xl hover:bg-[#FAF8F3] transition-colors">
                    <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Voir / Télécharger le badge' : 'View / Download badge' }}
                </button>
            </div>
        </aside>
    </div>
</section>
