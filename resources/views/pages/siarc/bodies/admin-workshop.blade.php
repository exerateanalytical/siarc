@php
    $isFr = $isFr ?? true;

    // Normalize passed vars to safe defaults
    $sTitle  = $sTitle  ?? ($isFr ? 'Atelier' : 'Workshop');
    $sStats  = $sStats  ?? [];
    $sTables = $sTables ?? [];
    $sLinks  = $sLinks  ?? [];

    // Clean the title: strip a leading "Atelier ·" prefix for the H1, keep a kicker.
    $rawTitle = trim($sTitle);
    $wTitle   = preg_replace('/^\s*Atelier\s*[·:>\-]\s*/u', '', $rawTitle);
    if ($wTitle === '') { $wTitle = $rawTitle; }

    // --- Parse the two stat tiles: [0] Inscriptions "n/capacity", [1] Horaire ---
    $inscTile = $sStats[0] ?? null;   // ['label'=>?, 'value'=>'n/capacity'] OR ['Inscriptions','n/capacity']
    $horTile  = $sStats[1] ?? null;

    $tileVal = function ($t) {
        if (is_array($t)) {
            return $t['value'] ?? ($t[1] ?? ($t['val'] ?? null));
        }
        return $t;
    };
    $tileLabel = function ($t, $fallback) {
        if (is_array($t)) {
            return $t['label'] ?? ($t[0] ?? $fallback);
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

    // --- Participants table ---
    $pTable = $sTables[0] ?? null;
    $pCols  = $pTable['cols']  ?? $pTable['columns'] ?? ($isFr ? ['Nom','Email','Inscrit le'] : ['Name','Email','Registered on']);
    $pRows  = $pTable['rows']  ?? [];
    $pTitle = $pTable['title'] ?? ($isFr ? 'Participants inscrits' : 'Registered participants');

    // --- Public registration link ---
    $regLink = null; $regLinkLabel = $isFr ? 'Inscription publique' : 'Public registration';
    if (!empty($sLinks)) {
        $first = $sLinks[0];
        if (is_array($first)) {
            $regLink      = $first['url'] ?? ($first['href'] ?? ($first[1] ?? null));
            $regLinkLabel = $first['label'] ?? ($first[0] ?? $regLinkLabel);
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
@endphp

<div class="siarc-in">

    {{-- ══ BACK LINK ══ --}}
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#55524A] hover:text-siarc-green mb-4 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>{{ $isFr ? 'Retour au programme' : 'Back to programme' }}
    </a>

    {{-- ══ HEADER ══ --}}
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4 mb-5">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold tracking-wide uppercase px-3 py-1 rounded-full bg-[#FDF3E0] text-siarc-ochre">
                    <i data-lucide="presentation" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Atelier pratique' : 'Practical workshop' }}
                </span>
                @if($isFull)
                    <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-[#FDE8E8] text-siarc-red">{{ $isFr ? 'Complet' : 'Full' }}</span>
                @elseif($spotsLeft !== null && $regCapacity !== null && $spotsLeft <= max(1, round($regCapacity*0.2)))
                    <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-[#FDF3E0] text-siarc-ochre">{{ $isFr ? 'Places limitées' : 'Limited seats' }}</span>
                @endif
            </div>
            <p class="siarc-kicker text-siarc-ochre mb-2">{{ $isFr ? "Détail de l'atelier" : 'Workshop detail' }}</p>
            <h1 class="font-display text-[26px] md:text-[30px] font-extrabold text-[#161513] leading-tight max-w-2xl">{{ $wTitle }}</h1>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            @if($regLink)
            <a href="{{ $regLink }}" target="_blank" rel="noopener" class="siarc-btn border border-[#E1DED5] bg-white text-[#3B382F] px-4 py-2.5 text-[13px] hover:bg-[#FBFAF6]">
                <i data-lucide="external-link" class="w-4 h-4"></i>{{ $regLinkLabel }}
            </a>
            @endif
            <button type="button" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[13px] siarc-shadow">
                <i data-lucide="user-plus" class="w-4 h-4"></i>{{ $isFr ? 'Ajouter un participant' : 'Add participant' }}
            </button>
        </div>
    </div>

    {{-- ══ MAIN GRID ══ --}}
    <div class="grid lg:grid-cols-3 gap-5">

        {{-- ── LEFT: schedule tile + capacity + participants ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Schedule / info tile (adire heritage band) --}}
            <div class="siarc-adire siarc-shadow rounded-2xl p-6 text-white">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="clock" class="w-5 h-5 text-siarc-gold"></i></span>
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-white/60 font-semibold">{{ $horLabel }}</p>
                            <p class="text-[16px] font-bold mt-0.5">{{ $horValue ?: ($isFr ? 'À venir' : 'To be announced') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0"><i data-lucide="users-round" class="w-5 h-5 text-siarc-gold"></i></span>
                        <div>
                            <p class="text-[11px] uppercase tracking-wide text-white/60 font-semibold">{{ $inscLabel }}</p>
                            <p class="text-[16px] font-bold mt-0.5">{{ $inscValue ?: ($isFr ? 'Aucune donnée' : 'No data') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Capacity / registration progress --}}
            <div class="siarc-card siarc-shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[14px] font-bold text-[#1A1712]">{{ $isFr ? 'Capacité & inscriptions' : 'Capacity & registrations' }}</h3>
                    @if($regCapacity !== null)
                        <span class="text-[12px] font-semibold {{ $isFull ? 'text-siarc-red' : 'text-siarc-green' }}">{{ $fillPct }}%</span>
                    @endif
                </div>

                @if($regCapacity !== null)
                    <div class="h-3 rounded-full bg-[#EFEDE6] overflow-hidden mb-3">
                        <div class="h-full rounded-full {{ $isFull ? 'bg-siarc-red' : 'bg-gradient-to-r from-siarc-green to-siarc-ochre' }}" style="width:{{ max(3,$fillPct) }}%"></div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mt-4">
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

            {{-- Participants table --}}
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
                                        $name  = is_array($row) ? ($row['name']  ?? $row['nom']   ?? $row[0] ?? '—') : $row;
                                        $email = is_array($row) ? ($row['email'] ?? $row[1] ?? '—') : '—';
                                        $when  = is_array($row) ? ($row['registered_at'] ?? $row['inscrit_le'] ?? $row['date'] ?? $row[2] ?? '—') : '—';
                                    @endphp
                                    <tr class="border-b border-[#F2F0EA] last:border-0 hover:bg-[#FBFAF6] transition-colors">
                                        <td class="px-6 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <span class="w-9 h-9 rounded-full bg-siarc-green text-white flex items-center justify-center text-[12px] font-bold shrink-0">{{ $initials($name) }}</span>
                                                <span class="text-[13px] font-semibold text-[#1A1712]">{{ $name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3.5 text-[12.5px] text-[#55524A]">{{ $email }}</td>
                                        <td class="px-6 py-3.5 text-[12.5px] text-[#8A857A] whitespace-nowrap">{{ $when }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── RIGHT: quick facts + public registration ── --}}
        <div class="space-y-5">

            {{-- Quick facts --}}
            <div class="siarc-card siarc-shadow p-6">
                <h3 class="text-[14px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations pratiques' : 'Practical information' }}</h3>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <i data-lucide="clock" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $horLabel }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $horValue ?: ($isFr ? 'À venir' : 'To be announced') }}</p>
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
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Statut' : 'Status' }}</p>
                            <p class="text-[13px] font-semibold {{ $isFull ? 'text-siarc-red' : 'text-siarc-green' }}">{{ $isFull ? ($isFr?'Complet':'Full') : ($isFr?'Inscriptions ouvertes':'Registrations open') }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="ticket" class="w-4 h-4 text-[#8A857A] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Inscription' : 'Registration' }}</p>
                            <p class="text-[13px] font-semibold text-[#1A1712]">{{ $isFr ? 'Gratuite (obligatoire)' : 'Free (required)' }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- Public registration card --}}
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
