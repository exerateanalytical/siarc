@php
    use Illuminate\Support\Facades\Route as R;
    $lang = $lang ?? 'fr'; $isFr = $lang === 'fr';
    $h = fn($name, $params = []) => R::has($name) ? route($name, array_merge(['lang'=>$lang], $params)) : '#';

    // ── Passed vars (with graceful fallbacks so the shell never breaks) ──────────
    $sTitle = $sTitle ?? "L'artisanat africain à l'ère de l'innovation et du numérique";
    $sStats = $sStats ?? [
        ['calendar-days','#7C4FE0','#F0EAFB','Conférence','Type',null],
        ['calendar-clock','#3565DE','#E8EFFB','À venir','Début',null],
        ['map-pin','#157A43','#E2F3E8','À venir','Lieu',null],
        ['users','#C97A16','#FDF3E0','À venir','Inscrits',null],
    ];
    $sIntro = $sIntro ?? "Intervenant(s) : Dr. Alain Mbarga (Modérateur)";
    $sLinks = $sLinks ?? [];

    // Icon map for stat tiles (in case $sStats rows carry only [label,value])
    $statIcon = ['Type'=>'presentation','Début'=>'calendar-clock','Lieu'=>'map-pin','Inscrits'=>'users','Durée'=>'clock','Langue'=>'globe','Public'=>'users-round','Places'=>'ticket'];

    // Pull the "Inscrits" tile for the registrations summary
    $inscritsVal = null;
    foreach ($sStats as $row) {
        $vals = array_values((array) $row);
        $value = $vals[3] ?? end($vals);
        $label = $vals[4] ?? ($vals[1] ?? '');
        if (mb_stripos((string) $label, 'inscrit') !== false || mb_stripos((string) $label, 'places') !== false) {
            $inscritsVal = $value;
        }
    }
    $capacityNum = null;
    if ($inscritsVal && preg_match('/\d[\d\s]*/', (string) $inscritsVal, $m)) {
        $capacityNum = trim($m[0]);
    }
@endphp

{{-- ══ TOP: BACK LINK + PAGE TITLE + ACTIONS ══ --}}
<div class="flex flex-wrap items-start justify-between gap-4 mb-5 siarc-in">
    <div>
        <a href="{{ $h('siarc.admin.programme') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-[#55524A] hover:text-siarc-green transition-colors mb-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour au programme' : 'Back to programme' }}
        </a>
        <h1 class="font-display text-[28px] font-extrabold text-[#161513] leading-tight">{{ $isFr ? 'Détail de la session' : 'Session detail' }}</h1>
        <div class="flex items-center gap-2 mt-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold tracking-wide" style="background:#F0EAFB;color:#7C4FE0">CONFÉRENCE</span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold" style="background:#E2F3E8;color:#157A43">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Session confirmée' : 'Confirmed session' }}
            </span>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <button class="siarc-btn siarc-btn-outline !border-[#E1DFD8] !text-[#3B382F] bg-white px-3.5 py-2 text-[12.5px] siarc-shadow">
            <i data-lucide="calendar" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter au calendrier' : 'Add to calendar' }}
        </button>
        <button class="siarc-btn siarc-btn-green px-3.5 py-2 text-[12.5px] siarc-shadow">
            <i data-lucide="settings" class="w-4 h-4"></i>{{ $isFr ? 'Modifier la session' : 'Edit session' }}
        </button>
        <button class="w-9 h-9 rounded-xl bg-white border border-[#E1DFD8] flex items-center justify-center text-[#55524A] hover:bg-[#FBFAF6] siarc-shadow" aria-label="Plus">
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
        </button>
    </div>
</div>

{{-- ══ HERO ROW: WHEN CARD + TITLE/INTRO + SPEAKER ══ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">
    {{-- purple "when" card --}}
    <div class="rounded-2xl p-6 siarc-shadow text-white flex flex-col justify-center" style="background:linear-gradient(150deg,#7C4FE0,#5B34B8)">
        @php
            // Drive date + time from the real "Début"/"Start" stat (positional: 3=value, 4=label)
            $whenDate = $isFr ? 'À venir' : 'To be announced';
            $whenTime = null;
            foreach ($sStats as $row) {
                $vals  = array_values((array) $row);
                $value = $vals[3] ?? end($vals);
                $label = $vals[4] ?? ($vals[1] ?? '');
                if (mb_stripos((string) $label, 'début') !== false || mb_stripos((string) $label, 'start') !== false) {
                    // Value may be "29 Juillet 2026 · 10:30" — split date from time if present
                    $parts = preg_split('/\s*[·|–-]\s*(?=\d{1,2}[:h]\d)/u', (string) $value, 2);
                    $whenDate = trim($parts[0] ?? (string) $value);
                    $whenTime = isset($parts[1]) ? trim($parts[1]) : null;
                }
            }
        @endphp
        <div class="flex items-start gap-3 mb-4">
            <span class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i data-lucide="calendar-days" class="w-5 h-5"></i></span>
            <div class="leading-tight">
                <p class="text-[18px] font-bold">{{ $whenDate }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-5 h-5"></i></span>
            <p class="text-[18px] font-bold">{{ $whenTime ?? ($isFr ? 'À venir' : 'To be announced') }}</p>
        </div>
    </div>

    {{-- title + intro + stat chips --}}
    <div class="lg:col-span-2 siarc-card siarc-shadow p-6 relative">
        <button class="absolute top-5 right-5 w-9 h-9 rounded-xl border border-[#EFEDE6] flex items-center justify-center text-[#8A857A] hover:bg-[#FBFAF6]" aria-label="Enregistrer">
            <i data-lucide="heart" class="w-4 h-4"></i>
        </button>
        <h2 class="font-display text-[24px] font-extrabold text-[#161513] leading-snug pr-12">{{ $sTitle }}</h2>
        @if(!empty($sIntro))
        <p class="text-[13.5px] text-[#55524A] leading-relaxed mt-3 max-w-2xl">{{ $sIntro }}</p>
        @endif
        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-5 pt-4 border-t border-[#EFEDE6]">
            @php
                $chips = [];
                foreach ($sStats as $row) {
                    $vals  = array_values((array) $row);
                    $icon  = $vals[0] ?? ($statIcon[$vals[4] ?? ''] ?? 'circle-dot');
                    $value = $vals[3] ?? end($vals);
                    $label = $vals[4] ?? ($vals[1] ?? '');
                    $chips[] = [is_string($icon) ? $icon : 'circle-dot', $value, $label];
                }
            @endphp
            @forelse($chips as [$icon,$value,$label])
            <span class="inline-flex items-center gap-1.5 text-[12.5px] text-[#3B382F]">
                <i data-lucide="{{ $icon }}" class="w-4 h-4 text-[#8A857A]"></i>{{ $value }}
            </span>
            @empty
            <span class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'À venir' : 'To be announced' }}</span>
            @endforelse
        </div>
    </div>
</div>

{{-- ══ TABS (visual, Aperçu active) ══ --}}
<div class="siarc-card siarc-shadow px-5 pt-1 mb-5">
    <nav class="flex flex-wrap items-center gap-6 text-[13px]">
        @foreach([['Aperçu',true],['Intervenants',false],['Informations pratiques',false],['Documents',false],['Statistiques',false]] as [$tab,$active])
        <span class="py-3.5 -mb-px border-b-2 {{ $active ? 'border-siarc-green text-siarc-green font-semibold' : 'border-transparent text-[#8A857A] hover:text-[#3B382F]' }} cursor-default">{{ $tab }}</span>
        @endforeach
    </nav>
</div>

{{-- ══ MAIN GRID: DESCRIPTION + INFOS PRATIQUES + INTERVENANT PANELS ══ --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">
    {{-- Description détaillée --}}
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-3">{{ $isFr ? 'Description détaillée' : 'Detailed description' }}</h3>
        @if(!empty($sIntro))
        <p class="text-[13px] text-[#55524A] leading-relaxed">{{ $sIntro }}</p>
        @else
        <p class="text-[13px] text-[#8A857A] leading-relaxed">{{ $isFr ? 'Description à venir.' : 'Description to be announced.' }}</p>
        @endif
    </div>

    {{-- Informations pratiques --}}
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">{{ $isFr ? 'Informations pratiques' : 'Practical information' }}</h3>
        <dl class="space-y-3.5 text-[12.5px]">
            @forelse($sStats as $row)
                @php $v = array_values((array) $row); $rowVal = $v[3] ?? end($v); $rowLbl = $v[4] ?? ($v[1] ?? ''); @endphp
                @if($rowLbl !== '')
                <div class="flex items-start justify-between gap-4">
                    <dt class="text-[#8A857A] shrink-0">{{ $rowLbl }}</dt>
                    <dd class="text-[#3B382F] font-medium text-right">{{ $rowVal }}</dd>
                </div>
                @endif
            @empty
                <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'À venir' : 'To be announced' }}</p>
            @endforelse
        </dl>
    </div>

    {{-- Intervenant principal + Intervenants list --}}
    <div class="flex flex-col gap-5">
        {{-- Intervenant principal (driven from $sIntro) --}}
        @php
            // Parse the lead speaker out of $sIntro, e.g. "Intervenant(s) : Dr. Alain Mbarga (Modérateur)".
            $speakerRaw = trim(preg_replace('/^[^:]*:\s*/u', '', (string) $sIntro));
            $leadName = $leadRole = null; $leadInit = '?';
            if ($speakerRaw !== '') {
                $first = trim(preg_split('/\s*[,;]\s*/u', $speakerRaw)[0]);
                if (preg_match('/^(.*?)\s*\(([^)]*)\)\s*$/u', $first, $mm)) {
                    $leadName = trim($mm[1]); $leadRole = trim($mm[2]);
                } else {
                    $leadName = $first;
                }
                $words = preg_split('/\s+/u', preg_replace('/^(Dr\.?|Pr\.?|M\.?|Mme\.?|Mr\.?)\s+/ui', '', $leadName));
                $leadInit = mb_strtoupper(mb_substr($words[0] ?? '', 0, 1) . (isset($words[1]) ? mb_substr($words[1], 0, 1) : ''));
                if ($leadInit === '') { $leadInit = '?'; }
            }
        @endphp
        <div class="siarc-card siarc-shadow p-6">
            <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">{{ $isFr ? 'Intervenant principal' : 'Lead speaker' }}</h3>
            @if($leadName)
            <div class="flex items-start gap-3">
                <span class="w-12 h-12 rounded-full bg-siarc-green text-white flex items-center justify-center font-display font-bold text-[15px] shrink-0">{{ $leadInit }}</span>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-[14px] font-bold text-[#161513]">{{ $leadName }}</p>
                        @if($leadRole)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold" style="background:#F0EAFB;color:#7C4FE0">{{ $leadRole }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ $h('siarc.speakers') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-4 hover:gap-1.5 transition-all">
                {{ $isFr ? 'Voir le profil complet' : 'View full profile' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
            @else
            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Intervenant à venir.' : 'Speaker to be announced.' }}</p>
            @endif
        </div>

        {{-- Intervenants (panelists) --}}
        <div class="siarc-card siarc-shadow p-6">
            <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">{{ $isFr ? 'Intervenants' : 'Speakers' }}</h3>
            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Liste complète des intervenants à venir.' : 'Full speaker list to be announced.' }}</p>
            <a href="{{ $h('siarc.speakers') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-4 hover:gap-1.5 transition-all">
                {{ $isFr ? 'Voir tous les intervenants' : 'View all speakers' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</div>

{{-- ══ BOTTOM GRID: DOCUMENTS + LIEU + ACTIONS + SIMILAIRES ══ --}}
<div class="grid lg:grid-cols-12 gap-5">
    {{-- Documents associés --}}
    <div class="lg:col-span-4 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">{{ $isFr ? 'Documents associés' : 'Related documents' }}</h3>
        <div class="rounded-xl border border-dashed border-[#EFEDE6] p-6 text-center">
            <i data-lucide="clipboard-list" class="w-6 h-6 text-[#C9C4B8] mx-auto mb-2"></i>
            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun document pour le moment.' : 'No documents yet.' }}</p>
        </div>
    </div>

    {{-- Lieu --}}
    <div class="lg:col-span-3 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">{{ $isFr ? 'Lieu' : 'Venue' }}</h3>
        <div class="rounded-xl h-32 siarc-adire flex items-center justify-center mb-3 overflow-hidden">
            <i data-lucide="presentation" class="w-9 h-9 text-white/70"></i>
        </div>
        @php
            $lieuVal = null;
            foreach ($sStats as $row) {
                $v = array_values((array) $row);
                if (mb_stripos((string) ($v[4] ?? ''), 'lieu') !== false || mb_stripos((string) ($v[4] ?? ''), 'venue') !== false) {
                    $lieuVal = $v[3] ?? end($v);
                }
            }
        @endphp
        <p class="text-[14px] font-bold text-[#161513]">{{ $lieuVal ?: ($isFr ? 'À venir' : 'To be announced') }}</p>
        @if($inscritsVal)
        <p class="text-[12px] text-[#8A857A] mt-0.5">{{ $isFr ? 'Capacité' : 'Capacity' }} : {{ $inscritsVal }}</p>
        @endif
        <a href="{{ $h('siarc.admin.floorplan') }}" class="inline-flex items-center gap-1 text-[12px] font-semibold text-siarc-green mt-3 hover:gap-1.5 transition-all">
            {{ $isFr ? 'Voir le plan' : 'View map' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    {{-- Actions rapides --}}
    <div class="lg:col-span-5 siarc-card siarc-shadow p-6">
        <h3 class="font-display text-[16px] font-bold text-[#161513] mb-4">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h3>
        <div class="space-y-2.5">
            @foreach([
                ['copy','Dupliquer la session','#3B382F'],
                ['megaphone','Partager la session','#3B382F'],
                ['download','Exporter la session (PDF)','#3B382F'],
            ] as [$icon,$label,$col])
            <button class="w-full flex items-center gap-3 rounded-xl border border-[#EFEDE6] px-4 py-3 text-left hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                <i data-lucide="{{ $icon === 'copy' ? 'layout-grid' : $icon }}" class="w-[18px] h-[18px] text-[#8A857A]"></i>
                <span class="text-[12.5px] font-semibold" style="color:{{ $col }}">{{ $label }}</span>
            </button>
            @endforeach
            <button class="w-full flex items-center gap-3 rounded-xl border border-[#F4D5D5] px-4 py-3 text-left hover:bg-[#FDF4F4] transition-colors">
                <i data-lucide="circle-dashed" class="w-[18px] h-[18px] text-siarc-red"></i>
                <span class="text-[12.5px] font-semibold text-siarc-red">{{ $isFr ? 'Annuler la session' : 'Cancel session' }}</span>
            </button>
        </div>
    </div>
</div>

{{-- ══ SESSIONS SIMILAIRES ══ --}}
<div class="siarc-card siarc-shadow p-6 mt-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-display text-[16px] font-bold text-[#161513]">{{ $isFr ? 'Sessions similaires' : 'Similar sessions' }}</h3>
        <a href="{{ $h('siarc.admin.programme') }}" class="text-[12px] font-semibold text-siarc-green hover:underline">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
    </div>
    <div class="rounded-xl border border-dashed border-[#EFEDE6] p-6 text-center">
        <i data-lucide="calendar" class="w-6 h-6 text-[#C9C4B8] mx-auto mb-2"></i>
        <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune session similaire pour le moment.' : 'No similar sessions yet.' }}</p>
    </div>
</div>
