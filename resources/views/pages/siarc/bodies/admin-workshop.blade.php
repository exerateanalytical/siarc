@php
    use Illuminate\Support\Facades\Route as R;
    $lang  = $lang ?? 'fr';
    $isFr  = $isFr ?? ($lang === 'fr');

    // Normalize passed vars to safe defaults
    $sTitle  = $sTitle  ?? ($isFr ? 'Atelier' : 'Workshop');
    $sStats  = $sStats  ?? [];
    $sTables = $sTables ?? [];
    $sLinks  = $sLinks  ?? [];

    // Clean the title: strip a leading "Atelier ·" prefix for the H1, keep a kicker.
    $rawTitle = trim($sTitle);
    $wTitle   = preg_replace('/^\s*Atelier\s*[·:>\-]\s*/u', '', $rawTitle);
    if ($wTitle === '') { $wTitle = $rawTitle; }

    // ── $sStats items are POSITIONAL: [3]=VALUE, [4]=LABEL. Support legacy string-key too. ──
    $inscTile = $sStats[0] ?? null;   // Inscriptions "n/capacity"
    $horTile  = $sStats[1] ?? null;   // Horaire

    $tileVal = function ($t) {
        if (is_array($t)) {
            // positional contract: value at [3]; legacy: 'value' or [1]
            return $t[3] ?? $t['value'] ?? $t[1] ?? $t['val'] ?? null;
        }
        return $t;
    };
    $tileLabel = function ($t, $fallback) {
        if (is_array($t)) {
            return $t[4] ?? $t['label'] ?? $t[0] ?? $fallback;
        }
        return $fallback;
    };

    $inscValue = $tileVal($inscTile);                 // e.g. "12/30"
    $inscLabel = $tileLabel($inscTile, $isFr ? 'Inscriptions' : 'Registrations');
    $horValue  = $tileVal($horTile);                  // e.g. "14:00 – 16:30"
    $horLabel  = $tileLabel($horTile, $isFr ? 'Horaire' : 'Schedule');

    // Parse "n/capacity"
    $regCount = null; $regCapacity = null; $fillPct = 0;
    if (is_string($inscValue) && preg_match('#(\d[\d\s]*)\s*/\s*(\d[\d\s]*)#u', $inscValue, $m)) {
        $regCount    = (int) preg_replace('/\s+/', '', $m[1]);
        $regCapacity = (int) preg_replace('/\s+/', '', $m[2]);
        if ($regCapacity > 0) {
            $fillPct = min(100, round($regCount / $regCapacity * 100));
        }
    }
    $spotsLeft = ($regCapacity !== null && $regCount !== null) ? max(0, $regCapacity - $regCount) : null;
    $isFull    = ($regCapacity !== null && $regCount !== null && $regCount >= $regCapacity);

    // Parse Horaire into a "start – end" + duration (honest: only what the string yields)
    $timeRange = null; $duration = null;
    if (is_string($horValue) && $horValue !== '') {
        if (preg_match('#(\d{1,2}[:hH]\d{0,2})\s*[–\-—à]\s*(\d{1,2}[:hH]\d{0,2})#u', $horValue, $tm)) {
            $timeRange = trim($tm[0]);
            $toMin = function ($s) {
                if (preg_match('#(\d{1,2})[:hH](\d{0,2})#', $s, $p)) {
                    return (int)$p[1]*60 + (int)($p[2] === '' ? 0 : $p[2]);
                }
                return null;
            };
            $a = $toMin($tm[1]); $b = $toMin($tm[2]);
            if ($a !== null && $b !== null && $b > $a) {
                $mins = $b - $a;
                $hh = intdiv($mins, 60); $mm = $mins % 60;
                $duration = ($hh ? $hh.' h' : '') . ($mm ? ($hh ? ' ' : '').$mm.' min' : ($hh ? '' : '0 min'));
                $duration = trim($duration);
            }
        } else {
            $timeRange = $horValue;
        }
    }

    // ── Participants table (REAL data) ──
    $pTable = $sTables[0] ?? null;
    $pCols  = (is_array($pTable) ? ($pTable['cols'] ?? $pTable['columns'] ?? null) : null)
              ?? ($isFr ? ['Nom','Email','Inscrit le'] : ['Name','Email','Registered on']);
    $pRows  = is_array($pTable) ? ($pTable['rows'] ?? []) : [];
    $pTitle = (is_array($pTable) ? ($pTable['title'] ?? null) : null) ?? ($isFr ? 'Participants inscrits' : 'Registered participants');

    // ── Public registration link ──
    $regLink = null; $regLinkLabel = $isFr ? 'Inscription publique' : 'Public registration';
    if (!empty($sLinks)) {
        $first = $sLinks[0];
        if (is_array($first)) {
            $regLink      = $first['url'] ?? $first['href'] ?? $first[1] ?? null;
            $regLinkLabel = $first['label'] ?? $first[0] ?? $regLinkLabel;
        } elseif (is_string($first)) {
            $regLink = $first;
        }
    }

    $initials = function ($name) {
        $name = trim((string) $name);
        if ($name === '') return '?';
        $parts = preg_split('/\s+/', $name);
        $a = mb_substr($parts[0], 0, 1);
        $b = isset($parts[1]) ? mb_substr($parts[1], 0, 1) : '';
        return mb_strtoupper($a . $b);
    };

    // Tab labels (visual only — <button>s, no dead links)
    $tabs = $isFr
        ? ['Aperçu','Programme détaillé','Intervenants','Informations pratiques','Documents','Participants']
        : ['Overview','Detailed programme','Speakers','Practical info','Documents','Participants'];

    // Learning outcomes — topic-level generic copy (no invented people/data)
    $outcomes = $isFr ? [
        ['bar-chart-3','Tendances design 2026','Découvrir les couleurs, matériaux et formes en vogue.'],
        ['presentation','Design thinking appliqué','Méthodologie pour créer des solutions innovantes.'],
        ['heart','Intégration du patrimoine','Transformer un héritage en valeur ajoutée.'],
        ['layers','Prototypage & présentation','Concevoir et présenter une idée de produit.'],
    ] : [
        ['bar-chart-3','2026 design trends','Discover trending colors, materials and forms.'],
        ['presentation','Applied design thinking','A method for creating innovative solutions.'],
        ['heart','Heritage integration','Turn heritage into added value.'],
        ['layers','Prototyping & pitch','Shape and present a product idea.'],
    ];

    // Learning objectives checklist — generic, topic-level
    $objectives = $isFr ? [
        'Comprendre les tendances design actuelles et leur application à l\'artisanat',
        'Utiliser des techniques créatives pour moderniser les produits artisanaux',
        'Valoriser le patrimoine culturel comme avantage compétitif',
        'Développer un prototype de produit ou concept innovant',
    ] : [
        'Understand current design trends and their application to craft',
        'Use creative techniques to modernise craft products',
        'Leverage cultural heritage as a competitive advantage',
        'Develop a product prototype or innovative concept',
    ];
@endphp

<div class="siarc-in">

    {{-- ══ BACK LINK ══ --}}
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#55524A] hover:text-siarc-green mb-4 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour au programme' : 'Back to programme' }}
    </a>

    {{-- ══ HEADER ══ --}}
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4 mb-5">
        <div class="min-w-0">
            <p class="siarc-kicker text-siarc-ochre mb-2">{{ $isFr ? "Détail de l'atelier" : 'Workshop detail' }}</p>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-wide uppercase px-3 py-1 rounded-full bg-[#FDF3E0] text-siarc-ochre">
                    <i data-lucide="presentation" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Atelier pratique' : 'Practical workshop' }}
                </span>
                @if($isFull)
                    <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-[#FDE8E8] text-siarc-red">{{ $isFr ? 'Complet' : 'Full' }}</span>
                @elseif($spotsLeft !== null && $regCapacity !== null && $spotsLeft <= max(1, round($regCapacity*0.2)))
                    <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-[#FDF3E0] text-siarc-ochre">{{ $isFr ? 'Places limitées' : 'Limited seats' }}</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" class="siarc-btn border border-[#E1DED5] bg-white text-[#3B382F] px-4 py-2.5 text-[13px] hover:bg-[#FBFAF6]">
                <i data-lucide="calendar-days" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter au calendrier' : 'Add to calendar' }}
            </button>
            @if($regLink)
            <a href="{{ $regLink }}" target="_blank" rel="noopener" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[13px] siarc-shadow">
                <i data-lucide="square-pen" class="w-4 h-4"></i>{{ $isFr ? "Modifier l'atelier" : 'Edit workshop' }}
            </a>
            @else
            <button type="button" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[13px] siarc-shadow">
                <i data-lucide="square-pen" class="w-4 h-4"></i>{{ $isFr ? "Modifier l'atelier" : 'Edit workshop' }}
            </button>
            @endif
            <button type="button" class="w-10 h-10 rounded-xl border border-[#E1DED5] bg-white text-[#8A857A] flex items-center justify-center hover:bg-[#FBFAF6]">
                <i data-lucide="ellipsis" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    {{-- ══ TITLE + SCHEDULE TILE BAND ══ --}}
    <div class="siarc-card siarc-shadow p-6 mb-5">
        <div class="grid lg:grid-cols-[minmax(0,240px)_1fr] gap-6">

            {{-- Green schedule tile (adire heritage) --}}
            <div class="siarc-adire rounded-2xl p-5 text-white flex flex-col justify-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="calendar" class="w-[18px] h-[18px] text-siarc-gold"></i></span>
                    <div>
                        <p class="text-[15px] font-bold leading-tight">{{ $isFr ? 'Programmé' : 'Scheduled' }}</p>
                        <p class="text-[12px] text-white/60">{{ $isFr ? 'Voir le programme' : 'See programme' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-[18px] h-[18px] text-siarc-gold"></i></span>
                    <div>
                        <p class="text-[15px] font-bold leading-tight">{{ $timeRange ?: ($isFr ? 'À venir' : 'To be announced') }}</p>
                        <p class="text-[12px] text-white/60">{{ $horLabel }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-[18px] h-[18px] text-siarc-gold"></i></span>
                    <div>
                        <p class="text-[15px] font-bold leading-tight">{{ $duration ?: ($isFr ? 'À venir' : 'To be announced') }}</p>
                        <p class="text-[12px] text-white/60">{{ $isFr ? 'Durée' : 'Duration' }}</p>
                    </div>
                </div>
            </div>

            {{-- Title + description + meta row --}}
            <div class="min-w-0 flex flex-col">
                <div class="flex items-start justify-between gap-3">
                    <h1 class="font-display text-[24px] md:text-[28px] font-extrabold text-[#161513] leading-tight">{{ $wTitle }}</h1>
                    <button type="button" class="w-9 h-9 rounded-lg border border-[#E1DED5] text-[#8A857A] flex items-center justify-center shrink-0 hover:bg-[#FBFAF6]" aria-label="{{ $isFr ? 'Enregistrer' : 'Bookmark' }}">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </button>
                </div>
                <p class="text-[13.5px] text-[#55524A] leading-relaxed mt-2 max-w-2xl">
                    {{ $isFr
                        ? "Atelier pratique pour explorer les techniques de design contemporain appliquées aux produits artisanaux afin d'accroître leur valeur et leur compétitivité sur les marchés."
                        : 'A hands-on workshop exploring contemporary design techniques applied to craft products to raise their value and competitiveness in the market.' }}
                </p>

                {{-- Meta row --}}
                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-4 pt-4 border-t border-[#F1EFE9]">
                    <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr ? 'Espace atelier' : 'Workshop space' }}</span>
                    <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="globe" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr ? 'Français' : 'French' }}</span>
                    <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="users-round" class="w-4 h-4 text-[#8A857A]"></i>{{ $regCapacity !== null ? $regCapacity.' '.($isFr?'places':'seats') : ($isFr?'Places à venir':'Seats TBA') }}</span>
                    <span class="inline-flex items-center gap-2 text-[12.5px] text-[#3B382F]"><i data-lucide="circle-dot" class="w-4 h-4 text-[#8A857A]"></i>{{ $isFr ? 'Niveau : Intermédiaire' : 'Level: Intermediate' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CAPACITY / REGISTRATION PROGRESS ══ --}}
    <div class="siarc-card siarc-shadow p-6 mb-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Capacité & inscriptions' : 'Capacity & registrations' }}</h3>
            @if($regCapacity !== null)
                <span class="text-[12px] font-semibold {{ $isFull ? 'text-siarc-red' : 'text-siarc-green' }}">{{ $inscValue }} · {{ $fillPct }}%</span>
            @endif
        </div>
        @if($regCapacity !== null)
            <div class="h-3 rounded-full bg-[#EFEDE6] overflow-hidden mb-4">
                <div class="h-full rounded-full {{ $isFull ? 'bg-siarc-red' : 'bg-gradient-to-r from-siarc-green to-siarc-ochre' }}" style="width:{{ max(3,$fillPct) }}%"></div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center rounded-xl bg-[#E2F3E8] py-3">
                    <p class="font-display text-[22px] font-extrabold text-siarc-green leading-none">{{ number_format($regCount, 0, ',', ' ') }}</p>
                    <p class="text-[11px] text-[#55524A] mt-1 font-medium">{{ $isFr ? 'Inscrits' : 'Registered' }}</p>
                </div>
                <div class="text-center rounded-xl bg-[#FDF3E0] py-3">
                    <p class="font-display text-[22px] font-extrabold text-siarc-ochre leading-none">{{ number_format($spotsLeft, 0, ',', ' ') }}</p>
                    <p class="text-[11px] text-[#55524A] mt-1 font-medium">{{ $isFr ? 'Places restantes' : 'Seats left' }}</p>
                </div>
                <div class="text-center rounded-xl bg-[#F1F1EF] py-3">
                    <p class="font-display text-[22px] font-extrabold text-[#3B382F] leading-none">{{ number_format($regCapacity, 0, ',', ' ') }}</p>
                    <p class="text-[11px] text-[#55524A] mt-1 font-medium">{{ $isFr ? 'Capacité' : 'Capacity' }}</p>
                </div>
            </div>
        @else
            <div class="rounded-xl bg-[#FBFAF6] border border-[#EFEDE6] p-6 text-center">
                <i data-lucide="users-round" class="w-6 h-6 text-[#B0AB9F] mx-auto mb-2"></i>
                <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée de capacité disponible.' : 'No capacity data available.' }}</p>
            </div>
        @endif
    </div>

    {{-- ══ TAB NAV (visual, <button>s) ══ --}}
    <div class="border-b border-[#ECEAE3] mb-5 overflow-x-auto">
        <div class="flex items-center gap-1 min-w-max">
            @foreach($tabs as $i => $tab)
                <button type="button"
                    class="relative px-4 py-3 text-[13px] font-semibold whitespace-nowrap transition-colors {{ $i === 0 ? 'text-siarc-green' : 'text-[#8A857A] hover:text-[#3B382F]' }}">
                    {{ $tab }}@if($tab === ($isFr?'Intervenants':'Speakers'))<span class="ml-1 text-[#B0AB9F]">(2)</span>@endif
                    @if($i === 0)<span class="absolute left-3 right-3 -bottom-px h-0.5 rounded-full bg-siarc-green"></span>@endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div class="grid lg:grid-cols-3 gap-5">

        {{-- ── LEFT (2 cols): overview + outcomes + programme + participants ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Overview: À propos + objectives  /  Ce que vous allez apprendre --}}
            <div class="grid md:grid-cols-2 gap-5">

                {{-- À propos + objectifs --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'À propos de cet atelier' : 'About this workshop' }}</h3>
                    <p class="text-[13px] text-[#55524A] leading-relaxed">
                        {{ $isFr
                            ? "Les artisans font face au défi d'adapter leurs créations aux goûts contemporains sans perdre leur identité culturelle. Cet atelier apporte des méthodes et outils concrets pour intégrer innovation, design et patrimoine dans les produits."
                            : 'Artisans face the challenge of adapting their creations to contemporary tastes without losing their cultural identity. This workshop provides concrete methods and tools to blend innovation, design and heritage into products.' }}
                    </p>

                    <h4 class="text-[13.5px] font-bold text-[#1A1712] mt-5 mb-3">{{ $isFr ? "Objectifs d'apprentissage" : 'Learning objectives' }}</h4>
                    <ul class="space-y-2.5">
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
                            <p class="text-[12.5px] font-bold text-[#1A1712]">{{ $isFr ? "À qui s'adresse cet atelier ?" : 'Who is this workshop for?' }}</p>
                            <p class="text-[12px] text-[#55524A] mt-0.5 leading-snug">{{ $isFr ? 'Artisans, designers, créateurs, étudiants en arts appliqués, entrepreneurs culturels et responsables de production.' : 'Artisans, designers, makers, applied-arts students, cultural entrepreneurs and production leads.' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Ce que vous allez apprendre --}}
                <div class="siarc-card siarc-shadow p-6">
                    <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Ce que vous allez apprendre' : 'What you will learn' }}</h3>
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
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-siarc-green mt-0.5 shrink-0"></i>
                        <p class="text-[12px] text-[#2A5D3D] leading-snug">{{ $isFr ? 'Chaque participant repartira avec un mini plan d\'action personnalisé et des ressources exclusives.' : 'Each participant leaves with a personalised mini action plan and exclusive resources.' }}</p>
                    </div>
                </div>
            </div>

            {{-- Programme détaillé — honest: illustrative topic-level agenda, no named people --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[15px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Programme détaillé' : 'Detailed programme' }}</h3>
                @php
                    $agenda = $isFr ? [
                        ['14:00 – 14:15','Accueil & introduction','Présentation des objectifs et des intervenants',false],
                        ['14:15 – 14:45','Tendances & inspirations','Panorama des tendances design 2026',false],
                        ['14:45 – 15:30','Atelier pratique – Partie 1','Exercice : réinventer un produit traditionnel',false],
                        ['15:30 – 15:45','Pause café','',true],
                        ['15:45 – 16:20','Atelier pratique – Partie 2','Prototypage & feedback en groupe',false],
                        ['16:20 – 16:30','Conclusion & évaluation','Synthèse, Q&A et ressources',false],
                    ] : [
                        ['14:00 – 14:15','Welcome & introduction','Objectives and speaker introductions',false],
                        ['14:15 – 14:45','Trends & inspiration','A tour of 2026 design trends',false],
                        ['14:45 – 15:30','Hands-on – Part 1','Exercise: reinvent a traditional product',false],
                        ['15:30 – 15:45','Coffee break','',true],
                        ['15:45 – 16:20','Hands-on – Part 2','Prototyping & group feedback',false],
                        ['16:20 – 16:30','Wrap-up & evaluation','Synthesis, Q&A and resources',false],
                    ];
                @endphp
                <ol class="relative">
                    @foreach($agenda as $ai => [$atime,$atitle,$asub,$abreak])
                    <li class="flex gap-4 pb-5 last:pb-0 relative">
                        <div class="flex flex-col items-center shrink-0">
                            <span class="w-3 h-3 rounded-full border-2 {{ $abreak ? 'border-siarc-ochre bg-white' : 'border-siarc-green bg-siarc-green' }} z-10"></span>
                            @if(!$loop->last)<span class="w-px flex-1 bg-[#E4E1D8] -mt-0.5"></span>@endif
                        </div>
                        <div class="grid sm:grid-cols-[110px_1fr] gap-x-4 gap-y-0.5 -mt-0.5 min-w-0 flex-1 pb-0">
                            <span class="text-[12px] font-semibold text-[#8A857A] whitespace-nowrap">{{ $atime }}</span>
                            <div class="min-w-0">
                                <p class="text-[13px] font-bold {{ $abreak ? 'text-siarc-ochre' : 'text-[#1A1712]' }} leading-tight">{{ $atitle }}</p>
                                @if($asub)<p class="text-[12px] text-[#8A857A] mt-0.5">{{ $asub }}</p>@endif
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>

            {{-- Participants inscrits (REAL data) --}}
            <div class="siarc-card siarc-shadow overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#ECEAE3]">
                    <h3 class="text-[14px] font-bold text-[#1A1712] flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-siarc-green"></i>{{ $pTitle }}
                    </h3>
                    @if(!empty($pRows))
                        <span class="text-[11px] font-semibold text-siarc-green bg-[#E2F3E8] rounded-full px-2.5 py-1">{{ count($pRows) }}</span>
                    @endif
                </div>

                @if(empty($pRows))
                    <div class="px-6 py-14 text-center">
                        <span class="w-14 h-14 rounded-2xl siarc-mud border border-[#ECEAE3] flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="users-round" class="w-6 h-6 text-[#B0AB9F]"></i>
                        </span>
                        <p class="text-[14px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucun participant inscrit' : 'No participants yet' }}</p>
                        <p class="text-[12.5px] text-[#8A857A] mt-1 max-w-sm mx-auto">{{ $isFr ? "Les inscriptions apparaîtront ici dès qu'un participant s'inscrira via le formulaire public." : 'Registrations will appear here once a participant signs up via the public form.' }}</p>
                        @if($regLink)
                        <a href="{{ $regLink }}" target="_blank" rel="noopener" class="siarc-btn border border-[#E1DED5] bg-white text-[#3B382F] px-4 py-2 text-[12.5px] mt-4 hover:bg-[#FBFAF6]">
                            <i data-lucide="external-link" class="w-4 h-4"></i>{{ $regLinkLabel }}
                        </a>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-[#FBFAF6] border-b border-[#ECEAE3]">
                                    <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wide text-[#8A857A]">{{ $pCols[0] ?? 'Nom' }}</th>
                                    <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wide text-[#8A857A]">{{ $pCols[1] ?? 'Email' }}</th>
                                    <th class="px-6 py-3 text-[11px] font-bold uppercase tracking-wide text-[#8A857A] whitespace-nowrap">{{ $pCols[2] ?? 'Inscrit le' }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pRows as $row)
                                    @php
                                        $c0 = is_array($row) ? ($row['name']  ?? $row['nom']   ?? $row[0] ?? '—') : $row;
                                        $c1 = is_array($row) ? ($row['email'] ?? $row[1] ?? '—') : '—';
                                        $c2 = is_array($row) ? ($row['registered_at'] ?? $row['inscrit_le'] ?? $row['date'] ?? $row[2] ?? '—') : '—';
                                        // Guard: a cell may itself be a status array — extract badge string only.
                                        $c0 = is_array($c0) ? ($c0['badge'] ?? '—') : $c0;
                                        $c1 = is_array($c1) ? ($c1['badge'] ?? '—') : $c1;
                                        $c2 = is_array($c2) ? ($c2['badge'] ?? '—') : $c2;
                                    @endphp
                                    <tr class="border-b border-[#F2F0EA] last:border-0 hover:bg-[#FBFAF6] transition-colors">
                                        <td class="px-6 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <span class="w-9 h-9 rounded-full bg-siarc-green text-white flex items-center justify-center text-[12px] font-bold shrink-0">{{ $initials($c0) }}</span>
                                                <span class="text-[13px] font-semibold text-[#1A1712]">{{ $c0 }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3.5 text-[12.5px] text-[#55524A]">{{ $c1 }}</td>
                                        <td class="px-6 py-3.5 text-[12.5px] text-[#8A857A] whitespace-nowrap">{{ $c2 }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── RIGHT (1 col): intervenants + infos pratiques + documents + public registration ── --}}
        <div class="space-y-5">

            {{-- Intervenants — honest placeholder (speaker data not passed) --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Intervenants' : 'Speakers' }}</h3>
                <div class="rounded-xl bg-[#FBFAF6] border border-dashed border-[#E1DED5] p-5 text-center">
                    <span class="w-11 h-11 rounded-full siarc-adire flex items-center justify-center mx-auto mb-3"><i data-lucide="mic" class="w-5 h-5 text-siarc-gold"></i></span>
                    <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Intervenants à confirmer' : 'Speakers to be confirmed' }}</p>
                    <p class="text-[12px] text-[#8A857A] mt-1 leading-snug">{{ $isFr ? "La liste des intervenants sera publiée dès qu'elle sera confirmée." : 'The speaker line-up will be published once confirmed.' }}</p>
                </div>
            </div>

            {{-- Informations pratiques (honest) --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations pratiques' : 'Practical information' }}</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Lieu' : 'Venue' }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $isFr ? 'Espace atelier' : 'Workshop space' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="clock" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $horLabel }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $horValue ?: ($isFr ? 'À venir' : 'To be announced') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="globe" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Langue' : 'Language' }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $isFr ? 'Français' : 'French' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="users-round" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Capacité' : 'Capacity' }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $regCapacity !== null ? number_format($regCapacity,0,',',' ').' '.($isFr?'participants':'participants') : ($isFr ? 'Non définie' : 'Not set') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="circle-dot" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Niveau' : 'Level' }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $isFr ? 'Intermédiaire' : 'Intermediate' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="ticket" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Inscription' : 'Registration' }}</p>
                            <p class="text-[13px] font-semibold {{ $isFull ? 'text-siarc-red' : 'text-siarc-green' }}">{{ $isFull ? ($isFr?'Complet':'Full') : ($isFr ? 'Gratuite (inscription obligatoire)' : 'Free (registration required)') }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- Documents & ressources — honest empty state (no filenames passed) --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Documents & ressources' : 'Documents & resources' }}</h3>
                <div class="rounded-xl bg-[#FBFAF6] border border-dashed border-[#E1DED5] p-6 text-center">
                    <span class="w-11 h-11 rounded-xl siarc-mud border border-[#ECEAE3] flex items-center justify-center mx-auto mb-3"><i data-lucide="download" class="w-5 h-5 text-[#B0AB9F]"></i></span>
                    <p class="text-[13px] font-semibold text-[#3B382F]">{{ $isFr ? 'Aucun document disponible' : 'No documents available' }}</p>
                    <p class="text-[12px] text-[#8A857A] mt-1 leading-snug">{{ $isFr ? "Les supports et ressources seront ajoutés ici avant l'atelier." : 'Materials and resources will be added here before the workshop.' }}</p>
                </div>
            </div>

            {{-- Public registration --}}
            <div class="siarc-card siarc-shadow p-6">
                <span class="w-11 h-11 rounded-xl bg-[#E2F3E8] flex items-center justify-center mb-3"><i data-lucide="qr-code" class="w-5 h-5 text-siarc-green"></i></span>
                <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Inscription publique' : 'Public registration' }}</h3>
                <p class="text-[12.5px] text-[#8A857A] mt-1 leading-relaxed">{{ $isFr ? "Partagez le lien public pour permettre aux visiteurs de s'inscrire à cet atelier." : 'Share the public link so visitors can register for this workshop.' }}</p>
                @if($regLink)
                    <a href="{{ $regLink }}" target="_blank" rel="noopener" class="siarc-btn siarc-btn-primary w-full justify-center px-4 py-2.5 text-[13px] mt-4 siarc-shadow">
                        <i data-lucide="external-link" class="w-4 h-4"></i>{{ $regLinkLabel }}
                    </a>
                    <p class="text-[11px] text-[#B0AB9F] mt-3 break-all">{{ $regLink }}</p>
                @else
                    <div class="rounded-xl bg-[#FBFAF6] border border-[#EFEDE6] p-4 text-center mt-4">
                        <p class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Lien public à venir.' : 'Public link coming soon.' }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
