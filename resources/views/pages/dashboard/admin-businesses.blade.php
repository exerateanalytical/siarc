@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'businesses';

    $pageTitle = $isFr ? 'Gestion des Entreprises & Boutiques (Vendeurs)' : 'Businesses & Shops Management (Vendors)';
    $pageBreadcrumb = [
        [$isFr ? 'Accueil' : 'Home', route('dashboard.admin', ['lang' => $lang])],
        [$isFr ? 'Entreprises & Boutiques' : 'Businesses & Shops', null],
    ];

    // Numbers rendered with the design's thousands separator
    $fmtNum = fn ($n) => number_format((int) $n);

    // Dates like the design ("12 Jan 2025")
    $vbMonthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $vbDate = function ($value) use ($isFr, $vbMonthsFr) {
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $vbMonthsFr[$d->month], $d->year) : $d->format('d M Y');
    };

    // Status pills — Actif green / En attente orange / Suspendu red (design)
    $vbStatusMeta = [
        'actif'    => ['label' => $isFr ? 'Actif' : 'Active',        'pill' => 'bg-[#E2F3E8] text-[#157A43]'],
        'attente'  => ['label' => $isFr ? 'En attente' : 'Pending',  'pill' => 'bg-[#FDF0DC] text-[#C97A16]'],
        'suspendu' => ['label' => $isFr ? 'Suspendu' : 'Suspended',  'pill' => 'bg-[#FDE8E8] text-[#DC2626]'],
        'rejete'   => ['label' => $isFr ? 'Rejeté' : 'Rejected',     'pill' => 'bg-[#F0EEE8] text-[#6F6B60]'],
    ];
    $vbStatusKey = fn ($status) => ['published' => 'actif', 'draft' => 'attente', 'suspended' => 'suspendu', 'rejected' => 'rejete'][$status] ?? 'attente';

    // KYC pills — Vérifié green / En cours blue / Non vérifié red (design)
    $vbKycMeta = [
        'verifie'    => ['label' => $isFr ? 'Vérifié' : 'Verified',          'pill' => 'bg-[#E2F3E8] text-[#157A43]'],
        'encours'    => ['label' => $isFr ? 'En cours' : 'In progress',      'pill' => 'bg-[#E3EDFB] text-[#1D4ED8]'],
        'nonverifie' => ['label' => $isFr ? 'Non vérifié' : 'Not verified',  'pill' => 'bg-[#FDE8E8] text-[#DC2626]'],
    ];
    $vbKycKey = function ($tier) {
        if (in_array($tier, ['verified', 'certified'], true)) return 'verifie';
        if ($tier === 'basic') return 'encours';
        return 'nonverifie';
    };

    // Type pills — Entreprise blue / Artisan green (design)
    $vbTypeMeta = [
        'entreprise' => ['label' => $isFr ? 'Entreprise' : 'Business', 'pill' => 'bg-[#E3EDFB] text-[#1D4ED8]'],
        'artisan'    => ['label' => 'Artisan',                          'pill' => 'bg-[#E2F3E8] text-[#157A43]'],
    ];
    $vbTypeKey = fn ($ownership) => in_array($ownership, ['individual', 'sole_proprietor', 'sole_proprietorship', 'artisan'], true) ? 'artisan' : 'entreprise';

    // Colored-initial tiles when a business has no logo (same pattern as admin-artisans)
    $vbHues = ['bg-[#E7F0EA] text-[#0F4824]', 'bg-[#FDF0DC] text-[#8A6D1F]', 'bg-[#FDE8E8] text-[#B42025]', 'bg-[#EBE7F5] text-[#4A3B8A]', 'bg-[#E2F0F5] text-[#15607A]'];

    // ── Stat cards — real counts from the controller. The design's month-over-month
    // percentages had no prior-period source, so no trend is rendered.
    $vbCards = [
        ['users-round', '#157A43', '#E8F2EC', $isFr ? 'Vendeurs totaux' : 'Total vendors', $vendorStats['total']],
        ['user-check',  '#157A43', '#E8F2EC', $isFr ? 'Actifs' : 'Active',                  $vendorStats['active']],
        ['clock',       '#C97A16', '#FDF0DC', $isFr ? 'En attente' : 'Pending',             $vendorStats['pending']],
        ['circle-x',    '#DC2626', '#FDE8E8', $isFr ? 'Suspendus' : 'Suspended',            $vendorStats['suspended']],
        ['badge-check', '#157A43', '#E8F2EC', $isFr ? 'Vérifiés (KYC)' : 'Verified (KYC)',  $vendorStats['verified']],
        ['user-plus',   '#157A43', '#E8F2EC', $isFr ? 'Nouveaux ce mois' : 'New this month', $vendorStats['new_month']],
    ];

    // ── Répartition par statut (donut) — real breakdown
    $vbBreakdown = [
        ['#157A43', $isFr ? 'Actifs' : 'Active',        $statusBreakdown['active']],
        ['#E9B23C', $isFr ? 'En attente' : 'Pending',   $statusBreakdown['pending']],
        ['#DC2626', $isFr ? 'Suspendus' : 'Suspended',  $statusBreakdown['suspended']],
        ['#C9C3B5', $isFr ? 'Inactifs' : 'Inactive',    $statusBreakdown['inactive']],
    ];
    $vbDonutTotal = max(1, array_sum(array_column($vbBreakdown, 2)));
    $vbSegments = [];
    $vbAngle = 0;
    foreach ($vbBreakdown as [$vbC, $vbL, $vbN]) {
        $vbTo = $vbAngle + ($vbN / $vbDonutTotal) * 360;
        $vbSegments[] = sprintf('%s %.2fdeg %.2fdeg', $vbC, $vbAngle, $vbTo);
        $vbAngle = $vbTo;
    }
    $vbConic = 'conic-gradient(' . implode(', ', $vbSegments) . ')';

    // ── Nouveaux vendeurs — the 5 most recently registered businesses
    $vbNewVendors = collect($newVendors)
        ->map(fn ($v) => ['name' => $isFr ? $v->name_fr : ($v->name_en ?? $v->name_fr), 'date' => $vbDate($v->created_at), 'logo' => $v->logo ?? null])
        ->all();

    // ── Top catégories — real business counts per industry
    $vbTopCats = collect($topCategories)
        ->map(fn ($c) => [$isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr), (int) $c->total])
        ->all();
    $vbTopCatMax = $vbTopCats ? max(1, max(array_column($vbTopCats, 1))) : 1;

    // Filter dropdown data — regions are the 10 administrative regions of Cameroon;
    // the category list mirrors the industries actually attached to vendors.
    $vbRegions = ['Adamaoua', 'Centre', 'Est', 'Extrême-Nord', 'Littoral', 'Nord', 'Nord-Ouest', 'Ouest', 'Sud', 'Sud-Ouest'];
    $vbCategories = collect($topCategories)->pluck('name_fr')->filter()->all();

    // The sidebar links here with ?statut=en-attente — preselect the matching status
    $vbStatutMap = ['actifs' => 'published', 'en-attente' => 'draft', 'suspendus' => 'suspended', 'rejetes' => 'rejected'];
    $vbCurStatus = request('status', $vbStatutMap[request('statut')] ?? '');
@endphp

@section('content')
            {{-- Header actions (design topbar buttons live here, chrome is canonical) --}}
            <div class="flex flex-wrap items-center justify-end gap-3">
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('business.create') }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Ajouter un vendeur' : 'Add a vendor' }}
                    </a>
                    <a href="{{ route('admin.reports') }}#exports" class="inline-flex items-center gap-2 bg-white border border-[#DCD6C8] hover:border-[#157A43] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#157A43] transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        {{ $isFr ? 'Exporter' : 'Export' }}
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 opacity-80"></i>
                    </a>
                </div>
            </div>

            @if(session('success'))
            <div class="mt-3 bg-[#E2F3E8] border border-[#BFE3CC] text-[#157A43] text-[12.5px] rounded-xl px-4 py-3 flex items-start gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>{{ session('success') }}
            </div>
            @endif

            {{-- 6 stat cards --}}
            <section class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                @foreach($vbCards as [$vbIcon, $vbIconColor, $vbTile, $vbLabel, $vbValue])
                <div class="bg-white border border-[#EFEBE2] rounded-2xl px-4 py-3.5 flex items-start gap-3">
                    <span class="shrink-0 w-[38px] h-[38px] rounded-full flex items-center justify-center" style="background: {{ $vbTile }}">
                        <i data-lucide="{{ $vbIcon }}" class="w-[18px] h-[18px]" style="color: {{ $vbIconColor }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase leading-snug">{{ $vbLabel }}</p>
                        <p class="mt-0.5 text-[19px] font-bold text-[#1B1B18] leading-none">{{ $fmtNum($vbValue) }}</p>
                    </div>
                </div>
                @endforeach
            </section>

            <div class="mt-4 grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-4 items-start">
                {{-- LEFT: filter bar + vendors table + pagination --}}
                <section class="min-w-0 bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                    {{-- Filter bar --}}
                    <form method="GET" action="{{ route('admin.businesses') }}" class="p-4 flex flex-wrap items-center gap-2.5 border-b border-[#F5F1E8]">
                        <input type="hidden" name="lang" value="{{ $lang }}">
                        <div class="flex items-center gap-2 bg-[#FBF9F4] border border-[#E9E4D8] rounded-lg px-3 h-[36px] flex-1 min-w-[170px]">
                            <i data-lucide="search" class="w-[15px] h-[15px] text-[#8A857A] shrink-0"></i>
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Rechercher un vendeur...' : 'Search a vendor...' }}" class="flex-1 min-w-0 bg-transparent text-[12px] focus:outline-none placeholder-[#8A857A]">
                        </div>
                        <select name="status" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                            <option value="published" @selected($vbCurStatus === 'published')>{{ $isFr ? 'Actif' : 'Active' }}</option>
                            <option value="draft" @selected($vbCurStatus === 'draft')>{{ $isFr ? 'En attente' : 'Pending' }}</option>
                            <option value="suspended" @selected($vbCurStatus === 'suspended')>{{ $isFr ? 'Suspendu' : 'Suspended' }}</option>
                            <option value="rejected" @selected($vbCurStatus === 'rejected')>{{ $isFr ? 'Rejeté' : 'Rejected' }}</option>
                        </select>
                        <select name="type" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">Type</option>
                            <option value="entreprise" @selected(request('type') === 'entreprise')>{{ $isFr ? 'Entreprise' : 'Business' }}</option>
                            <option value="artisan" @selected(request('type') === 'artisan')>Artisan</option>
                        </select>
                        <select name="region" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Région' : 'Region' }}</option>
                            @foreach($vbRegions as $vbRegion)
                            <option value="{{ $vbRegion }}" @selected(request('region') === $vbRegion)>{{ $vbRegion }}</option>
                            @endforeach
                        </select>
                        <select name="categorie" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Catégorie' : 'Category' }}</option>
                            @foreach($vbCategories as $vbCategory)
                            <option value="{{ $vbCategory }}" @selected(request('categorie') === $vbCategory)>{{ $vbCategory }}</option>
                            @endforeach
                        </select>
                        <select name="date" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                            <option value="">{{ $isFr ? 'Date d\'inscription' : 'Registration date' }}</option>
                            <option value="recent" @selected(request('date') === 'recent')>{{ $isFr ? 'Plus récents' : 'Most recent' }}</option>
                            <option value="ancienne" @selected(request('date') === 'ancienne')>{{ $isFr ? 'Plus anciens' : 'Oldest' }}</option>
                        </select>
                        <button type="submit" class="inline-flex items-center gap-1.5 h-[36px] bg-white border border-[#E9E4D8] hover:border-[#157A43] hover:text-[#157A43] rounded-lg px-3.5 text-[12px] font-semibold text-[#3B382F] transition-colors">
                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Filtres' : 'Filters' }}
                        </button>
                        <span class="hidden lg:flex items-center gap-1 ml-auto">
                            <span class="w-[32px] h-[32px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#8A857A]"><i data-lucide="layout-grid" class="w-4 h-4"></i></span>
                            <span class="w-[32px] h-[32px] rounded-lg bg-[#0F4824] flex items-center justify-center text-white"><i data-lucide="list" class="w-4 h-4"></i></span>
                        </span>
                    </form>

                    {{-- Vendors table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px]">
                            <thead>
                                <tr class="bg-[#F8F4EC] text-left">
                                    <th class="pl-5 pr-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Vendeur' : 'Vendor' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Type</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Catégorie principale' : 'Main category' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Région' : 'Region' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">KYC</th>
                                    <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Inscription' : 'Registration' }}</th>
                                    <th class="px-2 pr-5 py-3 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#F5F1E8]">
                                @forelse($businesses as $b)
                                @php
                                    $vbSt = $vbStatusMeta[$vbStatusKey($b->status)];
                                    $vbKy = $vbKycMeta[$vbKycKey($b->verification_tier)];
                                    $vbTy = $vbTypeMeta[$vbTypeKey($b->ownership_type)];
                                    $vbHue = $vbHues[abs(crc32($b->name_fr ?? '')) % count($vbHues)];
                                @endphp
                                <tr>
                                    <td class="pl-5 pr-2 py-3">
                                        <div class="flex items-center gap-3 min-w-[210px]">
                                            @if($b->logo)
                                            <img src="{{ asset('storage/' . $b->logo) }}" alt="" class="w-[38px] h-[38px] rounded-lg object-cover shrink-0">
                                            @else
                                            <span class="w-[38px] h-[38px] rounded-lg flex items-center justify-center shrink-0 text-[14px] font-bold {{ $vbHue }}">{{ strtoupper(mb_substr($b->name_fr ?? '?', 0, 1)) }}</span>
                                            @endif
                                            <div class="min-w-0">
                                                <a href="{{ route('admin.businesses.detail', ['id' => $b->id, 'lang' => $lang]) }}" class="block text-[12.5px] font-bold text-[#1B1B18] leading-snug truncate hover:text-[#157A43]">{{ $isFr ? $b->name_fr : ($b->name_en ?? $b->name_fr) }}</a>
                                                <p class="text-[11px] text-[#8A857A] truncate">{{ $b->email ?? $b->user?->email }}</p>
                                                @if($b->phone)<p class="text-[11px] text-[#8A857A] truncate">{{ $b->phone }}</p>@endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3"><span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold whitespace-nowrap {{ $vbTy['pill'] }}">{{ $vbTy['label'] }}</span></td>
                                    <td class="px-2 py-3 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $isFr ? ($b->industry->name_fr ?? '—') : ($b->industry->name_en ?? $b->industry->name_fr ?? '—') }}</td>
                                    <td class="px-2 py-3">
                                        <p class="text-[12px] font-semibold text-[#3B382F]">{{ $isFr ? ($b->region?->name_fr ?? '—') : ($b->region?->name_en ?? $b->region?->name_fr ?? '—') }}</p>
                                        @if($b->city)<p class="text-[11px] text-[#8A857A]">{{ $b->city->name_fr ?? $b->city->name ?? '' }}</p>@endif
                                    </td>
                                    <td class="px-2 py-3"><span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold whitespace-nowrap {{ $vbSt['pill'] }}">{{ $vbSt['label'] }}</span></td>
                                    <td class="px-2 py-3"><span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold whitespace-nowrap {{ $vbKy['pill'] }}">{{ $vbKy['label'] }}</span></td>
                                    <td class="px-2 py-3 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $vbDate($b->created_at) }}</td>
                                    <td class="px-2 pr-5 py-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admin.businesses.detail', ['id' => $b->id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="inline-flex w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#157A43] hover:border-[#157A43] align-middle"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                        <a href="{{ route('admin.businesses.detail', ['id' => $b->id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}" class="ml-1 inline-flex w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#C97A16] hover:border-[#C97A16] align-middle"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                        <span class="relative group ml-1 inline-flex align-middle">
                                            <button type="button" title="{{ $isFr ? 'Plus d\'options' : 'More options' }}" class="inline-flex w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#1B1B18]"><i data-lucide="more-vertical" class="w-3.5 h-3.5"></i></button>
                                            <span class="absolute right-0 top-full w-44 bg-white rounded-xl shadow-lg border border-[#E7E7E5] py-1.5 hidden group-hover:block group-focus-within:block z-30 text-left">
                                                @if($b->status !== 'published')
                                                <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $b->id]) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="published">
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-[12px] text-[#157A43] hover:bg-[#F2F8F4] flex items-center gap-2"><i data-lucide="check" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Publier' : 'Publish' }}</button>
                                                </form>
                                                @endif
                                                @if($b->status !== 'suspended')
                                                <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $b->id]) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="suspended">
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-[12px] text-[#B42025] hover:bg-[#FDF2F2] flex items-center gap-2"><i data-lucide="ban" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Suspendre' : 'Suspend' }}</button>
                                                </form>
                                                @endif
                                                @if($b->status !== 'rejected')
                                                <form method="POST" action="{{ route('admin.businesses.update-status', ['id' => $b->id]) }}">
                                                    @csrf
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="w-full text-left px-3.5 py-2 text-[12px] text-[#6F6B60] hover:bg-[#F6F5F1] flex items-center gap-2"><i data-lucide="x" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Rejeter' : 'Reject' }}</button>
                                                </form>
                                                @endif
                                                <a href="{{ route('businesses.show', ['lang' => $lang, 'slug' => $b->slug]) }}" target="_blank" class="block px-3.5 py-2 text-[12px] text-[#3B382F] hover:bg-[#F6F5F1]"><i data-lucide="external-link" class="inline w-3.5 h-3.5 mr-2 -mt-0.5"></i>{{ $isFr ? 'Voir la vitrine' : 'View storefront' }}</a>
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                {{-- The design shipped 8 invented companies here; an honest empty state replaces them --}}
                                <tr><td colspan="8" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée pour le moment.' : 'No data yet.' }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-5 py-3.5 border-t border-[#F5F1E8] flex flex-wrap items-center justify-between gap-3">
                        <p class="text-[11.5px] text-[#8A857A]">
                            {{ $isFr
                                ? 'Affichage de ' . ($businesses->firstItem() ?? 0) . ' à ' . ($businesses->lastItem() ?? 0) . ' sur ' . $fmtNum($businesses->total()) . ' vendeurs'
                                : 'Showing ' . ($businesses->firstItem() ?? 0) . ' to ' . ($businesses->lastItem() ?? 0) . ' of ' . $fmtNum($businesses->total()) . ' vendors' }}
                        </p>
                        <div class="flex items-center gap-1.5">
                            @if($businesses->onFirstPage())
                            <span class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#C9C3B5]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></span>
                            @else
                            <a href="{{ $businesses->previousPageUrl() }}" class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#3B382F] hover:border-[#157A43]" aria-label="{{ $isFr ? 'Page précédente' : 'Previous page' }}"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></a>
                            @endif
                            @php
                                $vbCur = $businesses->currentPage();
                                $vbLast = $businesses->lastPage();
                                $vbStart = max(1, min($vbCur - 2, $vbLast - 4));
                                $vbEnd = min($vbLast, $vbStart + 4);
                            @endphp
                            @foreach(range($vbStart, $vbEnd) as $vbPage)
                            @if($vbPage === $vbCur)
                            <span class="w-[28px] h-[28px] rounded-lg bg-[#0F4824] text-white text-[11.5px] font-bold flex items-center justify-center" aria-current="page">{{ $vbPage }}</span>
                            @else
                            <a href="{{ $businesses->url($vbPage) }}" class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] text-[11.5px] text-[#3B382F] flex items-center justify-center hover:border-[#157A43]">{{ $vbPage }}</a>
                            @endif
                            @endforeach
                            @if($vbEnd < $vbLast)
                            <span class="px-0.5 text-[11.5px] text-[#8A857A]">…</span>
                            <a href="{{ $businesses->url($vbLast) }}" class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] text-[11.5px] text-[#3B382F] flex items-center justify-center hover:border-[#157A43]">{{ $vbLast }}</a>
                            @endif
                            @if($businesses->hasMorePages())
                            <a href="{{ $businesses->nextPageUrl() }}" class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#3B382F] hover:border-[#157A43]" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></a>
                            @else
                            <span class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#C9C3B5]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></span>
                            @endif
                            <span class="ml-2 h-[28px] rounded-lg border border-[#E9E4D8] px-2.5 text-[11.5px] text-[#55524A] flex items-center gap-1">{{ $businesses->perPage() }} / page</span>
                        </div>
                    </div>
                </section>

                {{-- RIGHT rail --}}
                <div class="space-y-4">
                    {{-- Répartition par statut (donut) --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Répartition par statut' : 'Breakdown by status' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <div class="mt-4 flex justify-center">
                            <div class="w-[130px] h-[130px] rounded-full flex items-center justify-center" style="background: {{ $vbConic }}">
                                <div class="w-[84px] h-[84px] bg-white rounded-full flex flex-col items-center justify-center leading-tight">
                                    <span class="text-[19px] font-bold text-[#1B1B18]">{{ $fmtNum($vbDonutTotal) }}</span>
                                    <span class="text-[10px] text-[#8A857A]">Total</span>
                                </div>
                            </div>
                        </div>
                        <ul class="mt-4 space-y-2">
                            @foreach($vbBreakdown as [$vbC, $vbL, $vbN])
                            <li class="flex items-center gap-2">
                                <span class="shrink-0 w-[9px] h-[9px] rounded-[3px]" style="background: {{ $vbC }}"></span>
                                <span class="flex-1 min-w-0 truncate text-[11.5px] text-[#3B382F]">{{ $vbL }}</span>
                                <span class="shrink-0 text-[11.5px] font-semibold text-[#1B1B18]">{{ $fmtNum($vbN) }} ({{ number_format($vbN / $vbDonutTotal * 100, 1) }}%)</span>
                            </li>
                            @endforeach
                        </ul>
                    </section>

                    {{-- Nouveaux vendeurs --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Nouveaux vendeurs' : 'New vendors' }}</h2>
                            <a href="{{ route('admin.businesses', ['lang' => $lang]) }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir tout' : 'View all' }} →</a>
                        </div>
                        <ul class="mt-3 divide-y divide-[#F5F1E8]">
                            @forelse($vbNewVendors as $vbI => $vbNv)
                            <li class="py-2.5 flex items-center gap-3">
                                @if(!empty($vbNv['logo']))
                                <img src="{{ asset('storage/' . $vbNv['logo']) }}" alt="" class="w-[32px] h-[32px] rounded-lg object-cover shrink-0">
                                @else
                                <span class="w-[32px] h-[32px] rounded-lg flex items-center justify-center shrink-0 text-[12.5px] font-bold {{ $vbHues[$vbI % count($vbHues)] }}">{{ strtoupper(mb_substr($vbNv['name'], 0, 1)) }}</span>
                                @endif
                                <p class="flex-1 min-w-0 truncate text-[12px] font-bold text-[#1B1B18]">{{ $vbNv['name'] }}</p>
                                <span class="shrink-0 text-[10.5px] text-[#8A857A] whitespace-nowrap">{{ $vbNv['date'] }}</span>
                            </li>
                            @empty
                            <li class="py-3 text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée pour le moment.' : 'No data yet.' }}</li>
                            @endforelse
                        </ul>
                    </section>

                    {{-- Top catégories --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Top catégories' : 'Top categories' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <ul class="mt-3.5 space-y-3">
                            @forelse($vbTopCats as [$vbCatName, $vbCatCount])
                            <li>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="min-w-0 truncate text-[11.5px] text-[#3B382F]">{{ $vbCatName }}</p>
                                    <p class="shrink-0 text-[11.5px] font-semibold text-[#1B1B18]">{{ $fmtNum($vbCatCount) }}</p>
                                </div>
                                <div class="mt-1 h-[7px] rounded-full bg-[#F1EDE2] overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-[#0F4824] to-[#157A43]" style="width: {{ round($vbCatCount / $vbTopCatMax * 100) }}%"></div>
                                </div>
                            </li>
                            @empty
                            <li class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Aucune donnée pour le moment.' : 'No data yet.' }}</li>
                            @endforelse
                        </ul>
                    </section>

                    {{-- Actions rapides --}}
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Actions rapides' : 'Quick actions' }}</h2>
                        <div class="mt-3.5 grid grid-cols-2 gap-2.5">
                            <a href="{{ route('business.create') }}" class="inline-flex items-center justify-center gap-1.5 h-[40px] rounded-lg bg-[#157A43] hover:bg-[#14652F] text-white text-[11.5px] font-semibold px-2 transition-colors">
                                <i data-lucide="plus" class="w-3.5 h-3.5 shrink-0"></i><span class="truncate">{{ $isFr ? 'Ajouter un vendeur' : 'Add a vendor' }}</span>
                            </a>
                            <a href="{{ route('admin.reports') }}#exports" class="inline-flex items-center justify-center gap-1.5 h-[40px] rounded-lg bg-[#3B82F6] hover:bg-[#2F6FDB] text-white text-[11.5px] font-semibold px-2 transition-colors">
                                <i data-lucide="download" class="w-3.5 h-3.5 shrink-0"></i><span class="truncate">{{ $isFr ? 'Exportation liste' : 'Export list' }}</span>
                            </a>
                            <a href="{{ route('admin.verifications') }}" class="inline-flex items-center justify-center gap-1.5 h-[40px] rounded-lg bg-[#8B5CF6] hover:bg-[#7C4DEB] text-white text-[11.5px] font-semibold px-2 transition-colors">
                                <i data-lucide="clipboard-check" class="w-3.5 h-3.5 shrink-0"></i><span class="truncate">{{ $isFr ? 'Vérifications KYC' : 'KYC verifications' }}</span>
                            </a>
                            <a href="{{ route('admin.reports') }}" class="inline-flex items-center justify-center gap-1.5 h-[40px] rounded-lg bg-[#E9B23C] hover:bg-[#D9A22C] text-white text-[11.5px] font-semibold px-2 transition-colors">
                                <i data-lucide="download" class="w-3.5 h-3.5 shrink-0"></i><span class="truncate">{{ $isFr ? 'Rapport vendeurs' : 'Vendor report' }}</span>
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <p class="flex-1 text-center text-[11.5px] text-[#8A857A]">
                    {{ $isFr ? '© 2025 Artisan Hub 237. Tous droits réservés.' : '© 2025 Artisan Hub 237. All rights reserved.' }}
                </p>
                <div class="flex items-center gap-4 shrink-0">
                    <a href="{{ route('admin.support') }}" class="inline-flex items-center gap-1.5 text-[11.5px] text-[#3B382F] hover:text-[#14652F]"><i data-lucide="circle-help" class="w-[14px] h-[14px]"></i>{{ $isFr ? 'Centre d\'assistance' : 'Help centre' }}</a>
                    <a href="{{ route('contact', ['lang' => $lang]) }}" class="inline-flex items-center gap-1.5 text-[11.5px] text-[#3B382F] hover:text-[#14652F]"><i data-lucide="mail" class="w-[14px] h-[14px]"></i>{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a>
                </div>
            </div>
@endsection
