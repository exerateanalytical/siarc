@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang' => $lang], $params)) : '#';
    // ── real IDs so detail links never 404 ──
    $eid = siarcEvent()?->id ?? 0;
    $sessionId  = DB::table('programme_sessions')->where('event_id', $eid)->value('id');
    $speakerId  = DB::table('speakers')->where('event_id', $eid)->value('id');
    // route helpers with graceful fallbacks
    $speakerLink   = $speakerId && R::has('siarc.admin.speaker') ? route('siarc.admin.speaker', ['lang'=>$lang,'id'=>$speakerId]) : $h('siarc.speakers');
    $speakersList  = $h('siarc.admin.speakers') !== '#' ? $h('siarc.admin.speakers') : $h('siarc.speakers');
    $sessionEdit   = $sessionId && R::has('siarc.admin.session') ? route('siarc.admin.session', ['lang'=>$lang,'id'=>$sessionId]) : $h('siarc.admin.programme');
@endphp

{{-- ══ TOP: BACK LINK + PAGE TITLE + BADGES + RIGHT META/ACTIONS ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5 siarc-in">
    <div>
        <a href="{{ $h('siarc.admin.programme') }}" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-[#55524A] hover:text-siarc-green transition-colors mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>Retour au programme
        </a>
        <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">Détail de la session</h1>
        <div class="flex items-center gap-2 mt-2.5">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wide" style="background:#EFE9FB;color:#7C4FE0">CONFÉRENCE</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold" style="background:#E2F3E8;color:#157A43">
                <span class="w-1.5 h-1.5 rounded-full bg-[#157A43]"></span>Session confirmée
            </span>
        </div>
    </div>

    <div class="flex flex-col items-end gap-3">
        {{-- date + venue --}}
        <div class="flex items-center gap-2.5 text-right">
            <i data-lucide="calendar-days" class="w-5 h-5 text-[#8A857A]"></i>
            <div class="leading-tight">
                <p class="text-[13px] font-semibold text-[#161513]">27 Juillet – 05 Août 2026</p>
                <p class="text-[11.5px] text-[#8A857A]">Musée National de Yaoundé</p>
            </div>
        </div>
    </div>
</div>

{{-- action buttons row (right aligned under the header, matching PNG) --}}
<div class="flex items-center justify-end gap-2.5 mb-5 -mt-1">
    <button data-toast="Ajouté à votre calendrier" class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#E1DFD8] px-3.5 py-2 text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6] siarc-shadow">
        <i data-lucide="calendar-days" class="w-4 h-4"></i>Ajouter au calendrier
    </button>
    <a href="{{ $sessionEdit }}" class="inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-[12.5px] font-semibold text-white siarc-shadow" style="background:#157A43">
        <i data-lucide="square-pen" class="w-4 h-4"></i>Modifier la session
    </a>
    <button data-toast="Plus d'actions" class="w-9 h-9 rounded-xl bg-white border border-[#E1DFD8] flex items-center justify-center text-[#55524A] hover:bg-[#FBFAF6] siarc-shadow" aria-label="Plus d'actions">
        <i data-lucide="ellipsis-vertical" class="w-4 h-4"></i>
    </button>
</div>

{{-- ══ HERO ROW: WHEN CARD + TITLE/DESC/META + INTERVENANT PRINCIPAL ══ --}}
<div class="grid lg:grid-cols-12 gap-5 mb-5">
    {{-- purple "when" card --}}
    <div class="lg:col-span-3 rounded-2xl p-6 siarc-shadow text-white flex flex-col justify-center gap-4" style="background:linear-gradient(150deg,#8B5CF6,#6D3FD6)">
        <div class="flex items-start gap-3">
            <span class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center shrink-0"><i data-lucide="calendar-days" class="w-[18px] h-[18px]"></i></span>
            <div class="leading-tight">
                <p class="text-[13px] text-white/85">Mardi</p>
                <p class="text-[17px] font-bold">29 Juillet 2026</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-[18px] h-[18px]"></i></span>
            <p class="text-[17px] font-bold">10:30 – 12:00</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-[18px] h-[18px]"></i></span>
            <p class="text-[17px] font-bold">1h 30 min</p>
        </div>
    </div>

    {{-- title + description + meta --}}
    <div class="lg:col-span-6 siarc-card siarc-shadow p-6 relative">
        <button data-toast="Session enregistrée" class="absolute top-5 right-5 w-9 h-9 rounded-xl border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]" aria-label="Enregistrer">
            <i data-lucide="bookmark" class="w-4 h-4"></i>
        </button>
        <h2 class="font-display text-[23px] font-extrabold text-[#161513] leading-snug pr-12">L'artisanat africain à l'ère de l'innovation et du numérique</h2>
        <p class="text-[13.5px] text-[#55524A] leading-relaxed mt-3">Cette conférence explore comment les artisans africains intègrent les technologies numériques pour innover, accéder à de nouveaux marchés et préserver leur patrimoine culturel.</p>
        <div class="flex flex-wrap items-center gap-x-7 gap-y-2.5 mt-6">
            <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#3B382F]"><i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A]"></i>Salle de Conférence A</span>
            <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#3B382F]"><i data-lucide="globe" class="w-4 h-4 text-[#8A857A]"></i>Français</span>
            <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#3B382F]"><i data-lucide="users-round" class="w-4 h-4 text-[#8A857A]"></i>Public</span>
            <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#3B382F]"><i data-lucide="ticket" class="w-4 h-4 text-[#8A857A]"></i>120 places</span>
        </div>
    </div>

    {{-- Intervenant principal --}}
    <div class="lg:col-span-3 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[15px] font-bold text-[#161513] mb-4">Intervenant principal</h3>
        <div class="flex items-start gap-3">
            <img src="{{ asset('images/siarc/session-speaker.png') }}" alt="Dr. Alain Mbarga" class="w-12 h-12 rounded-full object-cover shrink-0">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-[13.5px] font-bold text-[#161513]">Dr. Alain Mbarga</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold" style="background:#EFE9FB;color:#7C4FE0">Modérateur</span>
                </div>
                <p class="text-[12px] text-[#55524A] mt-1">Expert en Innovation &amp; Transformation Digitale</p>
                <p class="text-[12px] text-[#55524A] inline-flex items-center gap-1.5 mt-0.5">Cameroun <span class="text-[13px]">🇨🇲</span></p>
            </div>
        </div>
        <p class="text-[12px] text-[#55524A] leading-relaxed mt-4">Spécialiste de la transformation digitale et de l'innovation dans les PME en Afrique.</p>
        <a href="{{ $speakerLink }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-4 hover:gap-1.5 transition-all">
            Voir le profil complet <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>

{{-- ══ TABS ══ --}}
<div class="border-b border-[#EAE7DF] mb-5" data-tabs="sessionDetail">
    <nav class="flex flex-wrap items-center gap-8 text-[13px]">
        @foreach([['apercu','Aperçu'],['intervenants','Intervenants (3)'],['infos','Informations pratiques'],['documents','Documents'],['stats','Statistiques']] as [$key,$tab])
        <button class="si-tab py-3.5 -mb-px {{ $loop->first ? 'is-active' : '' }}" data-tab="{{ $key }}">{{ $tab }}</button>
        @endforeach
    </nav>
</div>

{{-- ══ PANEL: APERÇU — MAIN GRID: DESCRIPTION + INFOS PRATIQUES + INTERVENANTS ══ --}}
<div data-panel="apercu" data-tabs-for="sessionDetail">
<div class="grid lg:grid-cols-12 gap-5 mb-5">
    {{-- Description détaillée --}}
    <div class="lg:col-span-5 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-3">Description détaillée</h3>
        <p class="text-[13px] text-[#55524A] leading-relaxed">Le numérique transforme en profondeur le secteur artisanal en Afrique. Cette session mettra en lumière les outils digitaux, les plateformes de vente en ligne, la traçabilité, le design assisté par ordinateur et les nouvelles stratégies marketing qui permettent aux artisans de se réinventer tout en valorisant leur authenticité.</p>

        <h4 class="text-[13px] font-bold text-[#161513] mt-6 mb-3">Points clés abordés :</h4>
        <ul class="space-y-2.5">
            @foreach([
                'Les tendances numériques dans l\'artisanat africain',
                'Outils et plateformes pour booster la visibilité',
                'Financement et accompagnement des artisans innovants',
                'Études de cas et retours d\'expérience',
            ] as $point)
            <li class="flex items-start gap-2.5 text-[13px] text-[#3B382F]">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-siarc-green shrink-0 mt-0.5"></i>{{ $point }}
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Informations pratiques --}}
    <div class="lg:col-span-4 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Informations pratiques</h3>
        <dl class="text-[12.5px]">
            <div class="flex items-start justify-between gap-4 py-2.5">
                <dt class="text-[#8A857A] shrink-0">Catégorie</dt>
                <dd class="text-right"><span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold" style="background:#E7F0FB;color:#2563EB">Innovation &amp; Technologie</span></dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-2.5">
                <dt class="text-[#8A857A] shrink-0">Niveau</dt>
                <dd class="text-[#3B382F] font-medium text-right">Intermédiaire</dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-2.5">
                <dt class="text-[#8A857A] shrink-0">Type de session</dt>
                <dd class="text-[#3B382F] font-medium text-right">Conférence</dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-2.5">
                <dt class="text-[#8A857A] shrink-0">Public cible</dt>
                <dd class="text-[#3B382F] font-medium text-right">Artisans, Entrepreneurs,<br>Étudiants, Institutions</dd>
            </div>
        </dl>

        <p class="text-[12.5px] text-[#8A857A] mt-4 mb-3">Mots-clés</p>
        <div class="flex flex-wrap gap-2">
            @foreach(['Innovation','Numérique','Artisanat','Afrique','Transformation digitale'] as $tag)
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium" style="background:#EAF4EE;color:#157A43">{{ $tag }}</span>
            @endforeach
        </div>
    </div>

    {{-- Intervenants (panelists) --}}
    <div class="lg:col-span-3 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Intervenants</h3>
        <div class="space-y-5">
            @foreach([
                ['Marie Claire Nguimatsia','Designer &amp; Consultante','Cameroun','🇨🇲'],
                ['Paul Tchameni','CEO, TechCraft Africa','Cameroun','🇨🇲'],
                ['Fatou Diop','Fondatrice, Digital Artisans Hub','Sénégal','🇸🇳'],
            ] as [$name,$role,$country,$flag])
            <a href="{{ $speakerLink }}" class="flex items-start gap-3 group">
                <span class="w-11 h-11 rounded-full siarc-adire flex items-center justify-center shrink-0"></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-[13px] font-bold text-[#161513] group-hover:text-siarc-green transition-colors">{!! $name !!}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold" style="background:#E7F0FB;color:#2563EB">Panéliste</span>
                    </div>
                    <p class="text-[11.5px] text-[#55524A] mt-0.5">{!! $role !!}</p>
                    <p class="text-[11.5px] text-[#55524A] inline-flex items-center gap-1 mt-0.5">{{ $country }} <span>{{ $flag }}</span></p>
                </div>
            </a>
            @endforeach
        </div>
        <a href="{{ $speakersList }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-5 hover:gap-1.5 transition-all">
            Voir tous les intervenants (3) <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>

{{-- ══ BOTTOM GRID: DOCUMENTS + LIEU + ACTIONS + SESSIONS SIMILAIRES ══ --}}
<div class="grid lg:grid-cols-12 gap-5">
    {{-- Documents associés --}}
    <div class="lg:col-span-4 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Documents associés</h3>
        <div class="space-y-3">
            @foreach([
                ['#E7352C','PDF','Présentation de la session','PDF · 2.4 Mo'],
                ['#E6801A','PPT','Supports de présentation','PPTX · 5.8 Mo'],
                ['#157A43','DOC','Étude de cas - Artisans numériques','PDF · 3.1 Mo'],
            ] as [$col,$ext,$dtitle,$meta])
            <div class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-4 py-3">
                <span class="w-9 h-11 rounded-md flex flex-col items-center justify-center text-white text-[8px] font-bold shrink-0" style="background:{{ $col }}">
                    <i data-lucide="file-text" class="w-4 h-4 mb-0.5"></i>{{ $ext }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[12.5px] font-semibold text-[#161513] truncate">{{ $dtitle }}</p>
                    <p class="text-[11px] text-[#8A857A] mt-0.5">{{ $meta }}</p>
                </div>
                <button data-toast="Téléchargement en préparation…" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6] shrink-0" aria-label="Télécharger">
                    <i data-lucide="download" class="w-4 h-4"></i>
                </button>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Lieu --}}
    <div class="lg:col-span-3 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Lieu</h3>
        <div class="rounded-xl h-28 overflow-hidden mb-3">
            <img src="{{ asset('images/siarc/session-venue.png') }}" alt="Salle de Conférence A" class="w-full h-full object-cover">
        </div>
        <p class="text-[14px] font-bold text-[#161513]">Salle de Conférence A</p>
        <p class="text-[12px] text-[#8A857A] mt-0.5">Capacité : 200 places</p>
        <a href="{{ $h('siarc.admin.floorplan') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-3 hover:gap-1.5 transition-all">
            Voir le plan <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    {{-- Actions rapides --}}
    <div class="lg:col-span-5 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Actions rapides</h3>
        <div class="space-y-2.5">
            @foreach([
                ['layers','Dupliquer la session','Session dupliquée'],
                ['share-2','Partager la session','Lien de partage copié'],
                ['download','Exporter la session (PDF)','Export en préparation…'],
            ] as [$icon,$label,$toast])
            <button data-toast="{{ $toast }}" class="w-full flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-4 py-3 text-left hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                <i data-lucide="{{ $icon }}" class="w-[18px] h-[18px] text-[#8A857A]"></i>
                <span class="text-[12.5px] font-semibold text-[#3B382F]">{{ $label }}</span>
            </button>
            @endforeach
            <button data-toast="Session annulée" class="w-full flex items-center gap-3 rounded-xl border border-[#F4D5D5] px-4 py-3 text-left hover:bg-[#FDF4F4] transition-colors">
                <i data-lucide="x" class="w-[18px] h-[18px] text-siarc-red"></i>
                <span class="text-[12.5px] font-semibold text-siarc-red">Annuler la session</span>
            </button>
        </div>
    </div>
</div>

{{-- ══ SESSIONS SIMILAIRES (right column card in PNG spans full-height beside bottom row) ══ --}}
<div class="grid lg:grid-cols-12 gap-5 mt-5">
    <div class="lg:col-span-12 siarc-card siarc-shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-[16px] font-bold text-[#161513]">Sessions similaires</h3>
            <a href="{{ $h('siarc.admin.programme') }}" class="text-[12px] font-semibold text-siarc-green hover:underline">Voir tout</a>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach([
                ['session-sim-1.png','ATELIER','#C97A16','#FDF3E0','Design Thinking pour artisans','30 Juillet 2026 · 14:00 – 15:30','Salle Atelier 1'],
                ['session-sim-2.png','PANEL','#7C4FE0','#EFE9FB','E-commerce et artisanat','31 Juillet 2026 · 11:00 – 12:30','Salle de Conférence B'],
            ] as [$img,$badge,$bcol,$bbg,$stitle,$sdate,$sroom])
            <div class="flex items-start gap-3 rounded-xl border border-[#EFEDE6] p-3 relative">
                <img src="{{ asset('images/siarc/'.$img) }}" alt="{{ $stitle }}" class="w-16 h-14 rounded-lg object-cover shrink-0">
                <a href="{{ $sessionEdit }}" class="min-w-0 flex-1 pr-7 group">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold tracking-wide mb-1.5" style="background:{{ $bbg }};color:{{ $bcol }}">{{ $badge }}</span>
                    <p class="text-[13px] font-bold text-[#161513] leading-tight group-hover:text-siarc-green transition-colors">{{ $stitle }}</p>
                    <p class="text-[11.5px] text-[#8A857A] mt-1">{{ $sdate }}</p>
                    <p class="text-[11.5px] text-[#8A857A]">{{ $sroom }}</p>
                </a>
                <button data-toast="Session enregistrée" class="absolute top-3 right-3 text-[#B8B3A8] hover:text-siarc-green" aria-label="Enregistrer">
                    <i data-lucide="bookmark" class="w-4 h-4"></i>
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>{{-- /panel apercu --}}

{{-- ══ PANEL: INTERVENANTS ══ --}}
<div data-panel="intervenants" data-tabs-for="sessionDetail" hidden>
    <div class="siarc-card siarc-shadow p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="font-display text-[16px] font-bold text-[#161513]">Intervenants de la session</h3>
            <a href="{{ $speakersList }}" class="text-[12px] font-semibold text-siarc-green hover:underline">Voir tous les intervenants</a>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach([
                ['Dr. Alain Mbarga','Modérateur','Expert en Innovation &amp; Transformation Digitale','Cameroun','🇨🇲'],
                ['Marie Claire Nguimatsia','Panéliste','Designer &amp; Consultante','Cameroun','🇨🇲'],
                ['Paul Tchameni','Panéliste','CEO, TechCraft Africa','Cameroun','🇨🇲'],
                ['Fatou Diop','Panéliste','Fondatrice, Digital Artisans Hub','Sénégal','🇸🇳'],
            ] as [$name,$badge,$role,$country,$flag])
            <a href="{{ $speakerLink }}" class="flex items-start gap-3 rounded-xl border border-[#EFEDE6] p-4 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors group">
                <span class="w-11 h-11 rounded-full siarc-adire flex items-center justify-center shrink-0"></span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-[13px] font-bold text-[#161513] group-hover:text-siarc-green transition-colors">{!! $name !!}</p>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold" style="background:#E7F0FB;color:#2563EB">{{ $badge }}</span>
                    </div>
                    <p class="text-[11.5px] text-[#55524A] mt-0.5">{!! $role !!}</p>
                    <p class="text-[11.5px] text-[#55524A] inline-flex items-center gap-1 mt-0.5">{{ $country }} <span>{{ $flag }}</span></p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ PANEL: INFORMATIONS PRATIQUES ══ --}}
<div data-panel="infos" data-tabs-for="sessionDetail" hidden>
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Informations pratiques</h3>
        <dl class="text-[12.5px] max-w-xl">
            <div class="flex items-start justify-between gap-4 py-2.5 border-b border-[#F2F0EA]">
                <dt class="text-[#8A857A] shrink-0">Catégorie</dt>
                <dd class="text-right"><span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold" style="background:#E7F0FB;color:#2563EB">Innovation &amp; Technologie</span></dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-2.5 border-b border-[#F2F0EA]">
                <dt class="text-[#8A857A] shrink-0">Niveau</dt>
                <dd class="text-[#3B382F] font-medium text-right">Intermédiaire</dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-2.5 border-b border-[#F2F0EA]">
                <dt class="text-[#8A857A] shrink-0">Type de session</dt>
                <dd class="text-[#3B382F] font-medium text-right">Conférence</dd>
            </div>
            <div class="flex items-start justify-between gap-4 py-2.5">
                <dt class="text-[#8A857A] shrink-0">Public cible</dt>
                <dd class="text-[#3B382F] font-medium text-right">Artisans, Entrepreneurs, Étudiants, Institutions</dd>
            </div>
        </dl>
        <a href="{{ $h('siarc.admin.floorplan') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-5 hover:gap-1.5 transition-all">
            Voir le plan du salon <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>

{{-- ══ PANEL: DOCUMENTS ══ --}}
<div data-panel="documents" data-tabs-for="sessionDetail" hidden>
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Documents associés</h3>
        <div class="space-y-3 max-w-2xl">
            @foreach([
                ['#E7352C','PDF','Présentation de la session','PDF · 2.4 Mo'],
                ['#E6801A','PPT','Supports de présentation','PPTX · 5.8 Mo'],
                ['#157A43','DOC','Étude de cas - Artisans numériques','PDF · 3.1 Mo'],
            ] as [$col,$ext,$dtitle,$meta])
            <div class="flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-4 py-3">
                <span class="w-9 h-11 rounded-md flex flex-col items-center justify-center text-white text-[8px] font-bold shrink-0" style="background:{{ $col }}">
                    <i data-lucide="file-text" class="w-4 h-4 mb-0.5"></i>{{ $ext }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[12.5px] font-semibold text-[#161513] truncate">{{ $dtitle }}</p>
                    <p class="text-[11px] text-[#8A857A] mt-0.5">{{ $meta }}</p>
                </div>
                <button data-toast="Téléchargement en préparation…" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6] shrink-0" aria-label="Télécharger">
                    <i data-lucide="download" class="w-4 h-4"></i>
                </button>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══ PANEL: STATISTIQUES ══ --}}
<div data-panel="stats" data-tabs-for="sessionDetail" hidden>
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">Statistiques de la session</h3>
        <div class="grid sm:grid-cols-3 gap-4">
            @foreach([['Places réservées','87 / 120'],['Taux de remplissage','72 %'],['Intervenants','4']] as [$slabel,$sval])
            <div class="rounded-xl border border-[#EFEDE6] p-5">
                <p class="text-[12px] text-[#8A857A]">{{ $slabel }}</p>
                <p class="text-[22px] font-extrabold text-[#161513] mt-1">{{ $sval }}</p>
            </div>
            @endforeach
        </div>
        <a href="{{ $h('siarc.admin.analytics') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-5 hover:gap-1.5 transition-all">
            Voir les analyses détaillées <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>
</div>
