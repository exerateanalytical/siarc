@php
    $id = request()->route()?->parameter('id');
    $x  = DB::table('event_exhibitors as ee')
        ->leftJoin('businesses as b', 'b.id', '=', 'ee.business_id')
        ->where('ee.id', $id)
        ->first(['ee.status', 'ee.booth_number', 'ee.badge_code', 'b.name_fr', 'b.description_fr', 'b.logo', 'b.slug']);

    $name    = $x->name_fr ?? ($isFr ? 'Exposant' : 'Exhibitor');
    $status  = $x->status ?? 'pending';
    $desc    = $x->description_fr ?? null;
    $initials = collect(preg_split('/\s+/', trim($name)))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // status → French label + tone colours
    $statusMap = [
        'confirmed' => [$isFr ? 'Confirmé' : 'Confirmed', '#157A43', '#E2F3E8'],
        'allocated' => [$isFr ? 'Alloué' : 'Allocated', '#157A43', '#E2F3E8'],
        'completed' => [$isFr ? 'Terminé' : 'Completed', '#157A43', '#E2F3E8'],
        'checked_in'=> [$isFr ? 'Enregistré' : 'Checked-in', '#157A43', '#E2F3E8'],
        'pending'   => [$isFr ? 'En attente' : 'Pending', '#C97A16', '#FDF3E0'],
        'requested' => [$isFr ? 'Demandé' : 'Requested', '#C97A16', '#FDF3E0'],
        'cancelled' => [$isFr ? 'Annulé' : 'Cancelled', '#C0010C', '#FDE8E8'],
        'declined'  => [$isFr ? 'Refusé' : 'Declined', '#C0010C', '#FDE8E8'],
        'reserved'  => [$isFr ? 'Réservé' : 'Reserved', '#3565DE', '#E8EFFB'],
        'registered'=> [$isFr ? 'Inscrit' : 'Registered', '#3565DE', '#E8EFFB'],
    ];
    [$stLabel, $stFg, $stBg] = $statusMap[$status] ?? [ucfirst($status), '#55524A', '#EFEDE6'];

    $publicLink = collect($sLinks ?? [])->first(fn ($l) => ($l['icon'] ?? '') === 'external-link');

    // Header chips built from real salon fields
    $booth = collect($sStats ?? [])->firstWhere(4, 'Stand')[3] ?? ($x->booth_number ?? null);
    $badge = collect($sStats ?? [])->first(fn ($s) => in_array($s[4] ?? '', ['Badge']))[3] ?? ($x->badge_code ?? null);

    // tabs (visual — Aperçu active). Real sub-data only where we store it.
    $tabs = [
        ['home',           $isFr ? 'Aperçu' : 'Overview', true],
        ['clipboard-list', $isFr ? 'Informations' : 'Information', false],
        ['store',          $isFr ? 'Stand & Pavillon' : 'Stand & Pavilion', false],
        ['tag',            $isFr ? 'Produits' : 'Products', false],
        ['clipboard-list', $isFr ? 'Documents' : 'Documents', false],
        ['id-card',        $isFr ? 'Accréditations' : 'Accreditations', false],
        ['activity',       $isFr ? 'Activités' : 'Activities', false],
        ['clock',          $isFr ? 'Historique' : 'History', false],
    ];
@endphp

{{-- ════════════════════ PROFILE HEADER + QUICK ACTIONS ════════════════════ --}}
<div class="grid xl:grid-cols-3 gap-5 mb-5 siarc-in">
    {{-- Profile card --}}
    <div class="xl:col-span-2 siarc-card siarc-shadow p-6">
        <div class="flex flex-col sm:flex-row gap-5">
            {{-- heritage logo tile --}}
            @if($x->logo ?? false)
                <div class="w-[120px] h-[120px] rounded-2xl overflow-hidden border border-[#ECEAE3] shrink-0">
                    <img src="{{ asset($x->logo) }}" alt="{{ $name }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="w-[120px] h-[120px] rounded-2xl siarc-adire flex items-center justify-center shrink-0 border border-[#0B3A1E]">
                    <span class="font-display font-extrabold text-[34px] text-siarc-gold tracking-tight">{{ $initials ?: 'EX' }}</span>
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-display text-[26px] font-extrabold text-[#1A1712] leading-tight tracking-tight">{{ $name }}</h1>
                    <span class="inline-flex items-center gap-1.5 text-[11.5px] font-semibold rounded-full px-3 py-1" style="color:{{ $stFg }};background:{{ $stBg }}">
                        <i data-lucide="circle-dot" class="w-3 h-3"></i>{{ $stLabel }}
                    </span>
                </div>

                {{-- salon meta chips --}}
                <div class="flex flex-wrap items-center gap-x-2 gap-y-2 mt-4">
                    @if($booth && $booth !== '—')
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#ECEAE3] rounded-lg px-3 py-1.5">
                        <i data-lucide="grid-3x3" class="w-4 h-4 text-[#7C4FE0]"></i>{{ $isFr ? 'Stand' : 'Booth' }} {{ $booth }}
                    </span>
                    @endif
                    @if($badge && $badge !== '—')
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#ECEAE3] rounded-lg px-3 py-1.5">
                        <i data-lucide="id-card" class="w-4 h-4 text-siarc-ochre"></i>{{ $isFr ? 'Badge' : 'Badge' }} {{ $badge }}
                    </span>
                    @endif
                    @if(($x->slug ?? false))
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-[#3B382F] bg-[#F5F3EE] border border-[#ECEAE3] rounded-lg px-3 py-1.5">
                        <i data-lucide="globe" class="w-4 h-4 text-siarc-green"></i>/{{ $x->slug }}
                    </span>
                    @endif
                </div>

                {{-- À propos --}}
                @if($desc)
                <p class="mt-4 text-[13.5px] text-[#55524A] leading-relaxed max-w-[560px]">{{ $desc }}</p>
                @else
                <p class="mt-4 text-[13px] text-[#B0AB9F] italic">{{ $isFr ? 'Aucune description fournie pour cet exposant.' : 'No description provided for this exhibitor.' }}</p>
                @endif

                {{-- public link --}}
                @if($publicLink)
                <a href="{{ $publicLink['href'] }}" class="siarc-btn siarc-btn-green px-4 py-2.5 text-[12.5px] mt-5">
                    <i data-lucide="external-link" class="w-4 h-4"></i>{{ $publicLink['label'] }}
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick actions + status panel --}}
    <div class="siarc-card siarc-shadow p-5">
        <p class="text-[13px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</p>
        <a href="{{ route('siarc.admin.stands') }}" class="siarc-btn siarc-btn-green w-full justify-center px-4 py-2.5 text-[12.5px] mb-2.5">
            <i data-lucide="grid-3x3" class="w-4 h-4"></i>{{ $isFr ? 'Assigner un stand' : 'Assign a booth' }}
        </a>
        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ route('siarc.admin.badges') }}" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] justify-center px-3 py-2.5 text-[12px] hover:bg-[#FBFAF6]">
                <i data-lucide="id-card" class="w-4 h-4 text-siarc-ochre"></i>{{ $isFr ? 'Badge' : 'Badge' }}
            </a>
            <a href="{{ route('siarc.admin.b2b') }}" class="siarc-btn border border-[#ECEAE3] text-[#3B382F] justify-center px-3 py-2.5 text-[12px] hover:bg-[#FBFAF6]">
                <i data-lucide="handshake" class="w-4 h-4 text-[#0D9488]"></i>{{ $isFr ? 'B2B' : 'B2B' }}
            </a>
        </div>

        <div class="h-px bg-[#ECEAE3] my-4"></div>

        <p class="text-[13px] font-bold text-[#1A1712] mb-3">{{ $isFr ? 'Statut de l\'exposant' : 'Exhibitor status' }}</p>
        <dl class="space-y-2.5">
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Statut' : 'Status' }}</dt>
                <dd><span class="inline-flex items-center text-[11px] font-semibold rounded-full px-2.5 py-0.5" style="color:{{ $stFg }};background:{{ $stBg }}">{{ $stLabel }}</span></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Référence' : 'Reference' }}</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712]">EXP-{{ str_pad((string) $id, 5, '0', STR_PAD_LEFT) }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Pavillon' : 'Pavilion' }}</dt>
                <dd class="text-[12.5px] font-semibold text-[#1A1712]">{{ collect($sStats ?? [])->firstWhere(4, $isFr ? 'Pavillon' : 'Pavilion')[3] ?? '—' }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-[12px] text-[#8A857A]">{{ $isFr ? 'Fiche vendeur' : 'Vendor page' }}</dt>
                <dd class="text-[12.5px] font-semibold {{ ($x->slug ?? false) ? 'text-siarc-green' : 'text-[#B0AB9F]' }}">{{ ($x->slug ?? false) ? ($isFr ? 'Reliée' : 'Linked') : ($isFr ? 'Non reliée' : 'Not linked') }}</dd>
            </div>
        </dl>
    </div>
</div>

{{-- ════════════════════ TABS ════════════════════ --}}
<div class="siarc-card siarc-shadow px-2 sm:px-4 mb-5 overflow-x-auto">
    <div class="flex items-center gap-1 min-w-max">
        @foreach($tabs as [$icon, $label, $active])
        <span class="inline-flex items-center gap-2 text-[13px] font-semibold px-3.5 py-4 whitespace-nowrap {{ $active ? 'text-siarc-green border-b-2 border-siarc-green' : 'text-[#8A857A] border-b-2 border-transparent' }}">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>{{ $label }}
        </span>
        @endforeach
    </div>
</div>

{{-- ════════════════════ STAT TILES ════════════════════ --}}
@if(!empty($sStats))
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    @foreach($sStats as [$stIcon, $stColor, $stTile, $stValue, $stStatLabel, $stSub])
    <div class="siarc-card siarc-shadow p-5">
        <span class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:{{ $stTile }}">
            <i data-lucide="{{ $stIcon }}" class="w-[22px] h-[22px]" style="color:{{ $stColor }}"></i>
        </span>
        <p class="mt-3 text-[12px] text-[#8A857A] font-medium">{{ $stStatLabel }}</p>
        <p class="text-[24px] font-extrabold text-[#161513] leading-tight tracking-tight">{{ $stValue }}</p>
        @if($stSub)<p class="text-[11px] text-[#B0AB9F] mt-0.5">{{ $stSub }}</p>@endif
    </div>
    @endforeach
</div>
@endif

{{-- ════════════════════ CONTENT GRID ════════════════════ --}}
<div class="grid lg:grid-cols-3 gap-5">
    {{-- Informations sur l'entreprise --}}
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="text-[14.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Informations sur l\'entreprise' : 'Business information' }}</h3>
        <dl class="space-y-3.5">
            <div>
                <dt class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Raison sociale' : 'Legal name' }}</dt>
                <dd class="text-[13px] font-semibold text-[#1A1712] mt-0.5">{{ $name }}</dd>
            </div>
            <div>
                <dt class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Statut au salon' : 'Salon status' }}</dt>
                <dd class="mt-1"><span class="inline-flex items-center text-[11px] font-semibold rounded-full px-2.5 py-0.5" style="color:{{ $stFg }};background:{{ $stBg }}">{{ $stLabel }}</span></dd>
            </div>
            <div>
                <dt class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Numéro de stand' : 'Booth number' }}</dt>
                <dd class="text-[13px] font-semibold text-[#1A1712] mt-0.5">{{ ($booth && $booth !== '—') ? $booth : ($isFr ? 'Non assigné' : 'Unassigned') }}</dd>
            </div>
            <div>
                <dt class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Code badge' : 'Badge code' }}</dt>
                <dd class="text-[13px] font-semibold text-[#1A1712] mt-0.5">{{ ($badge && $badge !== '—') ? $badge : ($isFr ? 'À générer' : 'To generate') }}</dd>
            </div>
            <div>
                <dt class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Fiche vendeur' : 'Vendor slug' }}</dt>
                <dd class="text-[13px] font-semibold text-[#1A1712] mt-0.5">{{ ($x->slug ?? false) ? '/'.$x->slug : ($isFr ? 'Aucune' : 'None') }}</dd>
            </div>
        </dl>
    </div>

    {{-- À propos / description --}}
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="text-[14.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'À propos' : 'About' }}</h3>
        @if($desc)
        <p class="text-[13px] text-[#55524A] leading-relaxed">{{ $desc }}</p>
        @else
        <div class="flex flex-col items-center justify-center text-center py-8">
            <span class="w-12 h-12 rounded-xl bg-[#F5F3EE] flex items-center justify-center mb-3"><i data-lucide="store" class="w-6 h-6 text-[#B0AB9F]"></i></span>
            <p class="text-[13px] font-semibold text-[#8A857A]">{{ $isFr ? 'Aucune description' : 'No description' }}</p>
            <p class="text-[12px] text-[#B0AB9F] mt-1 max-w-[220px]">{{ $isFr ? 'La fiche entreprise ne comporte pas encore de présentation.' : 'The business record has no presentation yet.' }}</p>
        </div>
        @endif
    </div>

    {{-- Produits phares — honest empty (products not linked here) --}}
    <div class="siarc-card siarc-shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14.5px] font-bold text-[#1A1712]">{{ $isFr ? 'Produits phares' : 'Featured products' }}</h3>
        </div>
        <div class="flex flex-col items-center justify-center text-center py-8">
            <span class="w-12 h-12 rounded-xl bg-[#F5F3EE] flex items-center justify-center mb-3"><i data-lucide="tag" class="w-6 h-6 text-[#B0AB9F]"></i></span>
            <p class="text-[13px] font-semibold text-[#8A857A]">{{ $isFr ? 'À venir' : 'Coming soon' }}</p>
            <p class="text-[12px] text-[#B0AB9F] mt-1 max-w-[220px]">{{ $isFr ? 'Les produits phares seront affichés une fois le catalogue exposant renseigné.' : 'Featured products will appear once the exhibitor catalogue is populated.' }}</p>
            @if(($x->slug ?? false))
            <a href="{{ $publicLink['href'] ?? '#' }}" class="inline-flex items-center gap-1.5 text-[12px] font-semibold text-siarc-green mt-4 hover:gap-2 transition-all">{{ $isFr ? 'Voir la fiche vendeur' : 'View vendor page' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i></a>
            @endif
        </div>
    </div>
</div>

{{-- ════════════════════ SECONDARY ROW ════════════════════ --}}
<div class="grid lg:grid-cols-3 gap-5 mt-5">
    {{-- Documents — honest empty --}}
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="text-[14.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Documents' : 'Documents' }}</h3>
        <div class="flex items-center gap-3 rounded-xl border border-dashed border-[#ECEAE3] px-4 py-5">
            <span class="w-9 h-9 rounded-lg bg-[#F5F3EE] flex items-center justify-center shrink-0"><i data-lucide="clipboard-list" class="w-[18px] h-[18px] text-[#B0AB9F]"></i></span>
            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun document déposé pour cet exposant.' : 'No documents uploaded for this exhibitor.' }}</p>
        </div>
    </div>

    {{-- Notes internes — honest empty --}}
    <div class="siarc-card siarc-shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-[14.5px] font-bold text-[#1A1712]">{{ $isFr ? 'Notes internes' : 'Internal notes' }}</h3>
            <i data-lucide="clipboard-list" class="w-4 h-4 text-[#B0AB9F]"></i>
        </div>
        <div class="rounded-xl border border-dashed border-[#ECEAE3] px-4 py-5 text-center">
            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune note interne pour le moment.' : 'No internal notes yet.' }}</p>
        </div>
    </div>

    {{-- Liens & navigation --}}
    <div class="siarc-card siarc-shadow p-6">
        <h3 class="text-[14.5px] font-bold text-[#1A1712] mb-4">{{ $isFr ? 'Liens' : 'Links' }}</h3>
        @if(!empty($sLinks))
        <div class="space-y-2.5">
            @foreach($sLinks as $lnk)
            <a href="{{ $lnk['href'] }}" class="flex items-center gap-3 rounded-xl border border-[#ECEAE3] px-4 py-3 hover:border-[#D8E5DC] hover:bg-[#FBFAF6] transition-colors">
                <span class="w-8 h-8 rounded-lg bg-[#F5F3EE] flex items-center justify-center shrink-0"><i data-lucide="{{ $lnk['icon'] ?? 'arrow-right' }}" class="w-4 h-4 text-siarc-green"></i></span>
                <span class="text-[12.5px] font-semibold text-[#3B382F] flex-1">{{ $lnk['label'] }}</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-[#B0AB9F]"></i>
            </a>
            @endforeach
        </div>
        @else
        <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucun lien disponible.' : 'No links available.' }}</p>
        @endif
    </div>
</div>
