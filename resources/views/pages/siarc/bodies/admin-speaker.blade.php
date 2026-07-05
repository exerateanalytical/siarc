@php
    use Illuminate\Support\Facades\Route as R;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    $id = request()->route()?->parameter('id');
    $s = null;
    if ($id && Schema::hasTable('speakers')) {
        $s = DB::table('speakers')->where('id',$id)->first(['name','role_fr','organization','photo','bio_fr','is_featured']);
    }

    $name    = $s->name         ?? 'Dr. Alain Mbarga';
    $role    = $s->role_fr      ?? 'Expert en Innovation & Transformation Digitale';
    $org     = $s->organization ?? 'TechCraft Africa';
    $bio     = $s->bio_fr       ?? "Dr. Alain Mbarga est expert en innovation et transformation digitale avec plus de 15 ans d'expérience dans l'accompagnement des PME africaines vers la digitalisation. Fondateur et CEO de TechCraft Africa, il conseille des gouvernements et organisations sur les stratégies d'innovation et le développement de l'écosystème numérique en Afrique.";
    $feat    = (bool)($s->is_featured ?? true);

    // initials for the avatar tile
    $parts = preg_split('/\s+/', trim($name));
    $initials = strtoupper(mb_substr($parts[0] ?? 'S',0,1) . mb_substr(end($parts) ?: '',0,1));

    // Interventions table (real passed data)
    $sTables = $sTables ?? [];
    $intTable = $sTables[0] ?? null;
    $backHref = ($sLinks[0]['href'] ?? null) ?? $h('siarc.admin.speakers');
@endphp

<div class="siarc-in">

    {{-- ══ ACTION BAR ══ --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-5">
        <a href="{{ $backHref }}" class="siarc-btn bg-white border border-[#ECEAE3] text-[#3B382F] text-[13px] px-4 py-2.5 hover:bg-[#FBFAF6]">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour à la liste' : 'Back to list' }}
        </a>
        <button type="button" class="siarc-btn bg-white border border-[#ECEAE3] text-[#3B382F] text-[13px] px-4 py-2.5 hover:bg-[#FBFAF6]">
            <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Télécharger le badge' : 'Download badge' }}
        </button>
        <button type="button" class="siarc-btn siarc-btn-green text-[13px] px-4 py-2.5">
            <i data-lucide="settings" class="w-4 h-4"></i>{{ $isFr ? "Modifier l'intervenant" : 'Edit speaker' }}
        </button>
        <button type="button" class="siarc-btn bg-white border border-[#ECEAE3] text-[#8A857A] w-10 h-10 justify-center px-0">
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- ═══════════════ LEFT / MAIN COLUMN ═══════════════ --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- ── PROFILE HEADER CARD ── --}}
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex flex-col sm:flex-row gap-6">
                    {{-- avatar tile (initials on heritage green) --}}
                    <div class="shrink-0">
                        <div class="relative w-[190px] h-[210px] rounded-2xl siarc-adire overflow-hidden flex items-center justify-center">
                            @if(!empty($s->photo))
                                <img src="{{ asset($s->photo) }}" alt="{{ $name }}" class="absolute inset-0 w-full h-full object-cover">
                            @else
                                <span class="font-display text-[52px] font-extrabold text-[#E6B201]">{{ $initials }}</span>
                            @endif
                            <span class="absolute bottom-2.5 left-1/2 -translate-x-1/2 inline-flex items-center gap-1.5 bg-white/95 text-[11px] font-semibold text-[#157A43] rounded-full px-2.5 py-1 shadow-sm">
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Photo vérifiée' : 'Verified photo' }}
                            </span>
                        </div>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-3 flex-wrap">
                            <h1 class="font-display text-[26px] font-extrabold text-[#161513] leading-tight">{{ $name }}</h1>
                            <span class="inline-flex items-center rounded-full bg-[#E2F3E8] text-[#157A43] text-[11px] font-semibold px-3 py-1 mt-1.5">{{ $isFr ? 'Confirmé' : 'Confirmed' }}</span>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-[#F0EAFB] text-[#7C4FE0] text-[11px] font-semibold px-3 py-1 mt-2">{{ $isFr ? 'Conférencier' : 'Speaker' }}</span>

                        <p class="text-[14px] font-semibold text-[#2B2822] mt-4">{{ $role }}</p>
                        <p class="text-[13px] text-[#55524A] mt-0.5">{{ $org }}</p>
                        <p class="text-[13px] text-[#55524A] mt-0.5 inline-flex items-center gap-1.5">Yaoundé, Cameroun <span class="text-[13px]">🇨🇲</span></p>

                        <p class="text-[13px] text-[#55524A] leading-relaxed mt-4">{{ $bio }}</p>
                    </div>
                </div>

                {{-- meta chips row --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-6">
                    @foreach([
                        ['users-round','#157A43','#E2F3E8','Pays','Cameroun'],
                        ['globe','#3565DE','#E8EFFB','Langues','Français, Anglais'],
                        ['calendar-days','#C97A16','#FDF3E0','Sessions', ($intTable && !empty($intTable['rows']) ? count($intTable['rows']).' sessions' : '2 sessions')],
                        ['check-circle-2','#157A43','#E2F3E8','Statut','Confirmé'],
                        ['calendar','#7C4FE0','#F0EAFB','Inscription le','12 Mai 2026'],
                    ] as [$icon,$col,$tile,$lbl,$val])
                    <div class="rounded-xl border border-[#ECEAE3] p-3 flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="{{ $icon }}" class="w-4 h-4" style="color:{{ $col }}"></i></span>
                        <div class="min-w-0">
                            <p class="text-[10.5px] text-[#8A857A]">{{ $lbl }}</p>
                            <p class="text-[12px] font-semibold text-[#2B2822] truncate">{{ $val }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── TABS ── --}}
            <div class="siarc-card siarc-shadow px-6">
                <nav class="flex items-center gap-6 overflow-x-auto text-[13px] font-medium text-[#8A857A]">
                    @php
                        $sessCount = $intTable && !empty($intTable['rows']) ? count($intTable['rows']) : 2;
                        $tabs = ['Aperçu'=>true,'Sessions ('.$sessCount.')'=>false,'Biographie'=>false,'Médias'=>false,'Documents'=>false,'Informations pratiques'=>false,'Historique'=>false];
                    @endphp
                    @foreach($tabs as $tab=>$active)
                        <button type="button" class="py-4 whitespace-nowrap border-b-2 {{ $active ? 'border-siarc-green text-siarc-green font-semibold' : 'border-transparent hover:text-[#3B382F]' }}">{{ $tab }}</button>
                    @endforeach
                </nav>
            </div>

            {{-- ── APERÇU: À propos + Sessions assignées ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- À propos --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[14px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'À propos' : 'About' }}</h3>
                    <p class="text-[12.5px] text-[#55524A] leading-relaxed">{{ $bio }}</p>

                    <h4 class="text-[13px] font-bold text-[#1A1712] mt-5 mb-3">{{ $isFr ? "Domaines d'expertise" : 'Areas of expertise' }}</h4>
                    <ul class="space-y-2">
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

                    <h4 class="text-[13px] font-bold text-[#1A1712] mt-5 mb-3">{{ $isFr ? 'Réseaux sociaux' : 'Social networks' }}</h4>
                    <div class="flex items-center gap-2.5">
                        <span class="w-9 h-9 rounded-lg bg-[#0A66C2] text-white flex items-center justify-center text-[12px] font-bold">in</span>
                        <span class="w-9 h-9 rounded-lg bg-[#161513] text-white flex items-center justify-center text-[13px] font-bold">X</span>
                        <span class="w-9 h-9 rounded-lg bg-[#E2F3E8] flex items-center justify-center"><i data-lucide="globe" class="w-4 h-4 text-siarc-green"></i></span>
                        <span class="w-9 h-9 rounded-lg bg-[#FDE8E8] flex items-center justify-center"><i data-lucide="presentation" class="w-4 h-4 text-siarc-red"></i></span>
                    </div>
                </div>

                {{-- Sessions assignées (real $sTables data) --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">
                        {{ $isFr ? 'Sessions assignées' : 'Assigned sessions' }} ({{ $sessCount }})
                    </h3>

                    @if($intTable && !empty($intTable['rows']))
                        <div class="space-y-3">
                            @foreach($intTable['rows'] as $i=>$row)
                                @php
                                    $cells = $row['cells'] ?? [];
                                    $sessionName = $cells[0] ?? '—';
                                    $horaire = $cells[1] ?? '';
                                    $lieu = $cells[2] ?? '';
                                @endphp
                                <a href="{{ $row['href'] ?? $h('siarc.admin.programme') }}" class="block rounded-xl border border-[#ECEAE3] p-4 siarc-lift">
                                    <div class="flex items-start gap-3">
                                        <span class="w-9 h-9 rounded-lg bg-[#F0EAFB] flex items-center justify-center shrink-0"><i data-lucide="presentation" class="w-4 h-4 text-[#7C4FE0]"></i></span>
                                        <div class="min-w-0 flex-1">
                                            <span class="inline-block text-[10px] font-bold tracking-wide text-[#157A43] mb-1">{{ $isFr ? 'SESSION' : 'SESSION' }}</span>
                                            <p class="text-[12.5px] font-semibold text-[#1A1712] leading-tight">{{ $sessionName }}</p>
                                            @if($horaire)
                                            <p class="text-[11.5px] text-[#8A857A] mt-1.5 inline-flex items-center gap-1.5"><i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>{{ $horaire }}</p>
                                            @endif
                                            @if($lieu)
                                            <p class="text-[11.5px] text-[#8A857A] mt-1 inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $lieu }}</p>
                                            @endif
                                        </div>
                                        <span class="inline-flex items-center rounded-full bg-[#E2F3E8] text-[#157A43] text-[10.5px] font-semibold px-2.5 py-1 whitespace-nowrap">{{ $isFr ? 'Intervenant' : 'Speaker' }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-[#ECEAE3] p-8 text-center">
                            <i data-lucide="calendar-clock" class="w-7 h-7 text-[#B0AB9F] mx-auto mb-2"></i>
                            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune session assignée' : 'No assigned sessions' }}</p>
                        </div>
                    @endif

                    <a href="{{ $h('siarc.admin.programme') }}" class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-[#ECEAE3] py-3 text-[12.5px] font-semibold text-[#3B382F] hover:bg-[#FBFAF6]">
                        {{ $isFr ? 'Voir toutes les sessions' : 'View all sessions' }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            {{-- ── NOTES ADMIN ── --}}
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-[14px] font-bold text-[#1A1712] mb-2">{{ $isFr ? "Notes de l'administrateur" : 'Administrator notes' }}</h3>
                        <p class="text-[12.5px] text-[#55524A] leading-relaxed">
                            Excellent intervenant, très apprécié lors des éditions précédentes. Prévoir un temps d'échange avec le public. Arrivée prévue le 27 juillet. Besoin assistance technique pour présentation (vidéo + slides).
                        </p>
                        <p class="text-[11px] text-[#B0AB9F] mt-3">{{ $isFr ? 'Ajoutée par' : 'Added by' }} Jean Dupont · 12 Mai 2026</p>
                    </div>
                    <button type="button" class="w-9 h-9 rounded-lg border border-[#ECEAE3] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6] shrink-0"><i data-lucide="settings" class="w-4 h-4"></i></button>
                </div>
            </div>
        </div>

        {{-- ═══════════════ RIGHT / SIDE COLUMN ═══════════════ --}}
        <div class="space-y-5">

            {{-- Informations de contact --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations de contact' : 'Contact information' }}</h3>
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
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Organisation</h3>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-9 h-9 rounded-lg bg-[#E2F3E8] flex items-center justify-center shrink-0"><i data-lucide="building-2" class="w-4 h-4 text-siarc-green"></i></span>
                        <span class="text-[13px] font-semibold text-[#2B2822] truncate">{{ $org }}</span>
                    </div>
                    <a href="{{ $h('siarc.admin.exhibitors') }}" class="text-[11.5px] font-semibold text-[#3B382F] border border-[#ECEAE3] rounded-lg px-3 py-1.5 hover:bg-[#FBFAF6] whitespace-nowrap">{{ $isFr ? "Voir l'entreprise" : 'View company' }}</a>
                </div>
            </div>

            {{-- Documents --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Documents</h3>
                <ul class="space-y-3">
                    @foreach([
                        ['#157A43','#E2F3E8','Photo de profil','JPG · 245 Ko'],
                        ['#C0010C','#FDE8E8','CV - Alain Mbarga','PDF · 1.2 Mo'],
                        ['#C97A16','#FDF3E0','Présentation - SIARC 2026','PPTX · 3.4 Mo'],
                        ['#3565DE','#E8EFFB','Bio courte','PDF · 180 Ko'],
                    ] as [$col,$tile,$title,$meta])
                    <li class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:{{ $tile }}"><i data-lucide="clipboard-list" class="w-4 h-4" style="color:{{ $col }}"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-[12.5px] font-semibold text-[#2B2822] truncate">{{ $title }}</p>
                            <p class="text-[11px] text-[#8A857A]">{{ $meta }}</p>
                        </div>
                        <button type="button" class="text-[#8A857A] hover:text-siarc-green shrink-0"><i data-lucide="download" class="w-4 h-4"></i></button>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ $h('siarc.admin.speakers') }}" class="mt-4 flex items-center justify-end gap-1.5 text-[12px] font-semibold text-siarc-green hover:gap-2 transition-all">{{ $isFr ? 'Voir tous les documents' : 'View all documents' }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>

            {{-- Badge & Accréditation --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">Badge & Accréditation</h3>
                <ul class="space-y-3 text-[12.5px]">
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">{{ $isFr ? 'Type de badge' : 'Badge type' }}</span>
                        <span class="inline-flex items-center rounded-full bg-[#E2F3E8] text-[#157A43] text-[11px] font-semibold px-2.5 py-1">Intervenant</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">{{ $isFr ? 'N° de badge' : 'Badge no.' }}</span>
                        <span class="font-semibold text-[#2B2822]">SPK-026-0058</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">Accès</span>
                        <span class="font-semibold text-[#2B2822]">Toutes zones</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-[#8A857A]">Statut</span>
                        <span class="inline-flex items-center gap-1.5 font-semibold text-siarc-green"><i data-lucide="check-circle-2" class="w-4 h-4"></i>{{ $isFr ? 'Badge généré' : 'Badge generated' }}</span>
                    </li>
                </ul>
                <button type="button" class="mt-5 w-full siarc-btn bg-[#F6F4EF] border border-[#ECEAE3] text-[#3B382F] text-[12.5px] py-3 justify-center hover:bg-[#EFEDE6]">
                    <i data-lucide="download" class="w-4 h-4"></i>{{ $isFr ? 'Voir / Télécharger le badge' : 'View / download badge' }}
                </button>
            </div>
        </div>
    </div>
</div>
