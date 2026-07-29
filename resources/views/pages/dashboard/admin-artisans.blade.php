@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'artisans';
    $pageTitle = $isFr ? 'Gestion des Artisans' : 'Artisan Management';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], ['Artisans', null]];

    $statusMeta = [
        'published' => ['label' => $isFr ? 'Approuvé' : 'Approved',  'pill' => 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        'draft'     => ['label' => $isFr ? 'En attente' : 'Pending', 'pill' => 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]'],
        'suspended' => ['label' => $isFr ? 'Suspendu' : 'Suspended', 'pill' => 'bg-[#FDE8E8] dark:bg-[#3A1013] text-[#B42025] dark:text-[#F0555C]'],
        'rejected'  => ['label' => $isFr ? 'Rejeté' : 'Rejected',    'pill' => 'bg-[#F0EEE8] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'],
    ];

    $kycMeta = function ($tier) use ($isFr) {
        if (in_array($tier, ['verified', 'certified'], true)) {
            return ['label' => $isFr ? 'Vérifié' : 'Verified', 'pill' => 'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'];
        }
        if ($tier === 'basic') {
            return ['label' => $isFr ? 'En cours' : 'In progress', 'pill' => 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]'];
        }
        return ['label' => $isFr ? 'En attente' : 'Pending', 'pill' => 'bg-[#F0EEE8] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'];
    };

    $frMonths = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $fmtDate = function ($date) use ($isFr, $frMonths) {
        $d = \Carbon\Carbon::parse($date);
        return $isFr
            ? sprintf('%02d %s %d', $d->day, $frMonths[$d->month], $d->year)
            : $d->format('d M Y');
    };

    $fmtNum = fn ($n) => $isFr ? number_format($n, 0, ',', ' ') : number_format($n);

    // Status tabs (design: Tous / Approuvés / En attente / Suspendus / Rejetés)
    $tabs = [
        ['',           $isFr ? 'Tous' : 'All',            $artisanStatusCounts['all']],
        ['approuves',  $isFr ? 'Approuvés' : 'Approved',  $artisanStatusCounts['published']],
        ['en-attente', $isFr ? 'En attente' : 'Pending',  $artisanStatusCounts['draft']],
        ['suspendus',  $isFr ? 'Suspendus' : 'Suspended', $artisanStatusCounts['suspended']],
        ['rejetes',    $isFr ? 'Rejetés' : 'Rejected',    $artisanStatusCounts['rejected']],
    ];
    $curStatut = request('statut', '');

    // Stat chips (design: STATISTIQUES ARTISANS)
    $statChips = [
        ['users',        $artisanStatusCounts['all'],       $isFr ? 'Total artisans' : 'Total artisans', 'bg-[#E7F0EA] dark:bg-[#1A1E16] text-[#0F4824] dark:text-[#339B56]'],
        ['badge-check',  $artisanStatusCounts['published'], $isFr ? 'Approuvés' : 'Approved',            'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        ['clock',        $artisanStatusCounts['draft'],     $isFr ? 'En attente' : 'Pending',            'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]'],
        ['ban',          $artisanStatusCounts['suspended'], $isFr ? 'Suspendus' : 'Suspended',           'bg-[#FDE8E8] dark:bg-[#3A1013] text-[#B42025] dark:text-[#F0555C]'],
        ['x-circle',     $artisanStatusCounts['rejected'],  $isFr ? 'Rejetés' : 'Rejected',              'bg-[#F0EEE8] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'],
    ];

    $maxMonthCount = max(1, collect($artisansPerMonth)->max('count') ?? 1);
    $topMetierTotal = max(1, $artisanStatusCounts['all']);
    $metierDots = ['#157A43', '#C97A16', '#B42025', '#8A6D1F', '#0B3B20'];

    $avatarHues = ['bg-[#E7F0EA] dark:bg-[#1A1E16] text-[#0F4824] dark:text-[#339B56]', 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#8A6D1F] dark:text-[#EDB33A]', 'bg-[#FDE8E8] dark:bg-[#3A1013] text-[#B42025] dark:text-[#F0555C]', 'bg-[#EBE7F5] dark:bg-[#1E1733] text-[#4A3B8A] dark:text-[#8FB6F5]', 'bg-[#E2F0F5] dark:bg-[#1A1E16] text-[#15607A] dark:text-[#B4B5A6]'];
@endphp

@section('content')

            <!-- Status tabs -->
            <div class="mt-4 flex flex-wrap items-center gap-1.5 border-b border-[#EAE5D8] dark:border-[#262B21]">
                @foreach($tabs as [$tabKey, $tabLabel, $tabCount])
                @php $tabActive = $curStatut === $tabKey; @endphp
                <a href="{{ route('admin.artisans', array_filter(['lang' => $lang, 'statut' => $tabKey])) }}"
                   class="inline-flex items-center gap-1.5 rounded-t-lg px-3.5 py-2 min-h-[44px] md:min-h-0 text-[14px] md:text-[12.5px] border border-b-0 {{ $tabActive ? 'bg-white dark:bg-[#12150F] border-[#EAE5D8] dark:border-[#262B21] font-bold text-[#0F4824] dark:text-[#339B56] ' : 'bg-transparent border-transparent text-[#6F6B60] dark:text-[#868778] hover:text-[#1B1B18] dark:hover:text-[#F3EFE7] ' }}">
                    {{ $tabLabel }}
                    <span class="text-[13px] md:text-[11px] {{ $tabActive ? 'text-[#157A43] dark:text-[#339B56] ' : 'text-[#8A857A] dark:text-[#868778] ' }}">({{ $fmtNum($tabCount) }})</span>
                </a>
                @endforeach
            </div>

            <!-- Search + filters -->
            <section class="bg-white dark:bg-[#12150F] border border-t-0 border-[#EFEBE2] dark:border-[#262B21] rounded-b-2xl rounded-tr-2xl">
                <form method="GET" action="{{ route('admin.artisans') }}" class="flex flex-wrap items-center gap-2 px-4 pt-4 pb-3">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @if($curStatut)<input type="hidden" name="statut" value="{{ $curStatut }}">@endif
                    <div class="ui-field-group flex-1 min-w-[190px] max-w-[280px]">
                        <i data-lucide="search" class="w-[14px] h-[14px] shrink-0 text-[#8A857A] dark:text-[#868778]"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Rechercher un artisan...' : 'Search an artisan...' }}" class="ui-field-bare flex-1 min-w-0">
                    </div>
                    <select name="region" class="ui-field ui-select">
                        <option value="">{{ $isFr ? 'Région' : 'Region' }}</option>
                        @foreach($artisanRegions as $r)
                        <option value="{{ $r->code }}" {{ request('region') === $r->code ? 'selected' : '' }}>{{ $isFr ? $r->name_fr : $r->name_en }}</option>
                        @endforeach
                    </select>
                    <select name="metier" class="ui-field ui-select">
                        <option value="">{{ $isFr ? 'Métier' : 'Craft' }}</option>
                        @foreach($artisanMetiers as $m)
                        <option value="{{ $m->slug }}" {{ request('metier') === $m->slug ? 'selected' : '' }}>{{ $isFr ? $m->name_fr : $m->name_en }}</option>
                        @endforeach
                    </select>
                    <select name="statut" class="ui-field ui-select">
                        <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                        <option value="approuves" {{ $curStatut === 'approuves' ? 'selected' : '' }}>{{ $isFr ? 'Approuvés' : 'Approved' }}</option>
                        <option value="en-attente" {{ $curStatut === 'en-attente' ? 'selected' : '' }}>{{ $isFr ? 'En attente' : 'Pending' }}</option>
                        <option value="suspendus" {{ $curStatut === 'suspendus' ? 'selected' : '' }}>{{ $isFr ? 'Suspendus' : 'Suspended' }}</option>
                        <option value="rejetes" {{ $curStatut === 'rejetes' ? 'selected' : '' }}>{{ $isFr ? 'Rejetés' : 'Rejected' }}</option>
                    </select>
                    <select name="kyc" class="ui-field ui-select">
                        <option value="">{{ $isFr ? 'Vérification KYC' : 'KYC Verification' }}</option>
                        <option value="verifie" {{ request('kyc') === 'verifie' ? 'selected' : '' }}>{{ $isFr ? 'Vérifié' : 'Verified' }}</option>
                        <option value="en-cours" {{ request('kyc') === 'en-cours' ? 'selected' : '' }}>{{ $isFr ? 'En cours' : 'In progress' }}</option>
                        <option value="en-attente" {{ request('kyc') === 'en-attente' ? 'selected' : '' }}>{{ $isFr ? 'En attente' : 'Pending' }}</option>
                    </select>
                    <button type="submit" class="ui-btn ui-btn-secondary ml-auto">
                        <i data-lucide="filter" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Filtrer' : 'Filter' }}
                    </button>
                </form>

                <!-- Table -->
                <div class="ui-table-wrap">
                    <table class="ui-table min-w-[860px]">
                        <thead>
                            <tr>
                                <th>{{ $isFr ? 'Artisan' : 'Artisan' }}</th>
                                <th>{{ $isFr ? 'Région' : 'Region' }}</th>
                                <th>{{ $isFr ? 'Métier' : 'Craft' }}</th>
                                <th class="text-center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                <th class="text-center">KYC</th>
                                <th>{{ $isFr ? 'Inscrit le' : 'Registered on' }}</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($artisans as $a)
                            @php
                                $st = $statusMeta[$a->status] ?? $statusMeta['draft'];
                                $ky = $kycMeta($a->verification_tier);
                                $ref = sprintf('ART-%s-%04d', \Carbon\Carbon::parse($a->created_at)->year, $a->business_id);
                                $hue = $avatarHues[$a->business_id % count($avatarHues)];
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($a->logo)
                                        <img src="{{ asset('storage/' . $a->logo) }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                        @else
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 text-[13px] font-bold {{ $hue }}">
                                            {{ strtoupper(substr($a->user_name ?? $a->business_name ?? '?', 0, 1)) }}
                                        </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-[15px] md:text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $a->user_name ?? $a->business_name }}</p>
                                            <p class="text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">#{{ $ref }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $isFr ? ($a->region_fr ?? '—') : ($a->region_en ?? $a->region_fr ?? '—') }}</td>
                                <td>{{ $isFr ? ($a->metier_fr ?? '—') : ($a->metier_en ?? $a->metier_fr ?? '—') }}</td>
                                <td class="text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[12px] md:text-[10.5px] font-semibold {{ $st['pill'] }}">{{ $st['label'] }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-block rounded-md px-2.5 py-1 text-[12px] md:text-[10.5px] font-semibold {{ $ky['pill'] }}">{{ $ky['label'] }}</span>
                                </td>
                                <td class="whitespace-nowrap">{{ $fmtDate($a->created_at) }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('admin.businesses.detail', ['id' => $a->business_id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="inline-flex p-1.5 rounded-md text-[#157A43] dark:text-[#339B56] hover:bg-[#E2F3E8] dark:hover:bg-[#0C3D1D]"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                    <a href="{{ route('admin.businesses.detail', ['id' => $a->business_id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}" class="inline-flex p-1.5 rounded-md text-[#C97A16] dark:text-[#EDB33A] hover:bg-[#FDF0DC] dark:hover:bg-[#3A2B06]"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                    <a href="{{ route('admin.businesses.detail', ['id' => $a->business_id, 'lang' => $lang]) }}" title="{{ $isFr ? 'Plus d\'options' : 'More options' }}" class="inline-flex p-1.5 rounded-md text-[#8A857A] dark:text-[#868778] hover:bg-[#F5F1E8] dark:hover:bg-[#242A1E]"><i data-lucide="more-horizontal" class="w-4 h-4"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="ui-empty">{{ $isFr ? 'Aucun artisan ne correspond à ces critères.' : 'No artisan matches these criteria.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3.5 border-t border-[#F5F1E8] dark:border-[#262B21]">
                    <p class="text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">
                        {{ $isFr
                            ? 'Affichage de ' . ($artisans->firstItem() ?? 0) . ' à ' . ($artisans->lastItem() ?? 0) . ' sur ' . $fmtNum($artisans->total()) . ' artisans'
                            : 'Showing ' . ($artisans->firstItem() ?? 0) . ' to ' . ($artisans->lastItem() ?? 0) . ' of ' . $fmtNum($artisans->total()) . ' artisans' }}
                    </p>
                    @if($artisans->lastPage() > 1)
                    @php
                        $pgCur = $artisans->currentPage();
                        $pgLast = $artisans->lastPage();
                        $pgStart = max(1, min($pgCur - 2, $pgLast - 4));
                        $pgEnd = min($pgLast, $pgStart + 4);
                    @endphp
                    <nav class="flex items-center gap-1.5" aria-label="Pagination">
                        @if($artisans->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9] dark:text-[#868778]"><i data-lucide="chevron-left" class="w-4 h-4"></i></span>
                        @else
                        <a href="{{ $artisans->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md" aria-label="{{ $isFr ? 'Page précédente' : 'Previous page' }}"><i data-lucide="chevron-left" class="w-4 h-4"></i></a>
                        @endif

                        @if($pgStart > 1)
                        <a href="{{ $artisans->url(1) }}" class="w-8 h-8 flex items-center justify-center text-[14px] md:text-[12.5px] text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md">1</a>
                        @if($pgStart > 2)<span class="px-1 text-[14px] md:text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">…</span>@endif
                        @endif

                        @foreach(range($pgStart, $pgEnd) as $pageNum)
                        @if($pageNum === $pgCur)
                        <span class="w-8 h-8 flex items-center justify-center bg-[#0B3D28] dark:bg-[#0C3B1E] text-white text-[14px] md:text-[12.5px] font-semibold rounded-md" aria-current="page">{{ $pageNum }}</span>
                        @else
                        <a href="{{ $artisans->url($pageNum) }}" class="w-8 h-8 flex items-center justify-center text-[14px] md:text-[12.5px] text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md">{{ $pageNum }}</a>
                        @endif
                        @endforeach

                        @if($pgEnd < $pgLast)
                        @if($pgEnd < $pgLast - 1)<span class="px-1 text-[14px] md:text-[12.5px] text-[#B9B4A9] dark:text-[#868778]">…</span>@endif
                        <a href="{{ $artisans->url($pgLast) }}" class="w-8 h-8 flex items-center justify-center text-[14px] md:text-[12.5px] text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md">{{ $fmtNum($pgLast) }}</a>
                        @endif

                        @if($artisans->hasMorePages())
                        <a href="{{ $artisans->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center text-[#3A3A35] dark:text-[#B4B5A6] hover:bg-[#F2F5F2] dark:hover:bg-[#242A1E] rounded-md" aria-label="{{ $isFr ? 'Page suivante' : 'Next page' }}"><i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                        @else
                        <span class="w-8 h-8 flex items-center justify-center text-[#B9B4A9] dark:text-[#868778]"><i data-lucide="chevron-right" class="w-4 h-4"></i></span>
                        @endif

                        <span class="ml-2 text-[14px] md:text-[12px] text-[#6F6B60] dark:text-[#868778]">10 / page</span>
                    </nav>
                    @endif
                </div>
            </section>

            <!-- STATISTIQUES ARTISANS -->
            <section class="ui-card mt-5">
                <p class="ui-eyebrow">{{ $isFr ? 'Statistiques artisans' : 'Artisan statistics' }}</p>
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    @foreach($statChips as [$chipIcon, $chipNum, $chipLabel, $chipHue])
                    <div class="flex items-center gap-3 border border-[#EFEBE2] dark:border-[#262B21] rounded-xl px-3.5 py-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ $chipHue }}">
                            <i data-lucide="{{ $chipIcon }}" class="w-[17px] h-[17px]"></i>
                        </div>
                        <div class="leading-tight min-w-0">
                            <p class="text-[15px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $fmtNum($chipNum) }}</p>
                            <p class="text-[13px] md:text-[11px] text-[#6F6B60] dark:text-[#868778] truncate">{{ $chipLabel }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>

            <!-- Bottom cards -->
            <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-5">
                <!-- Nouveaux artisans par mois -->
                <section class="ui-card">
                    <p class="ui-eyebrow">{{ $isFr ? 'Nouveaux artisans par mois' : 'New artisans per month' }}</p>
                    <div class="mt-4 flex items-end gap-1.5 h-[150px]">
                        @foreach($artisansPerMonth as $mo)
                        <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end" title="{{ $mo['label'] }} : {{ $fmtNum($mo['count']) }}">
                            <div class="w-full max-w-[22px] bg-[#14652F] dark:bg-[#2E9250] rounded-t-[3px]" style="height: {{ max(2, round($mo['count'] / $maxMonthCount * 100)) }}%"></div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-1.5 flex gap-1.5">
                        @foreach($artisansPerMonth as $mo)
                        <p class="flex-1 text-center text-[12px] md:text-[9.5px] text-[#8A857A] dark:text-[#868778]">{{ $mo['label'] }}</p>
                        @endforeach
                    </div>
                </section>

                <!-- Artisans par métier -->
                <section class="ui-card">
                    <div class="flex items-center justify-between">
                        <p class="ui-eyebrow">{{ $isFr ? 'Artisans par métier' : 'Artisans by craft' }}</p>
                        <a href="{{ route('admin.industries', ['lang' => $lang]) }}" class="text-[13px] md:text-[11.5px] font-semibold text-[#157A43] dark:text-[#339B56] hover:text-[#14532D] dark:hover:text-[#339B56]">{{ $isFr ? 'Voir tout' : 'View all' }}</a>
                    </div>
                    <div class="mt-3 divide-y divide-[#F5F1E8] dark:divide-[#262B21]">
                        @forelse($topMetiers as $tm)
                        <div class="flex items-center gap-2.5 py-2.5">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $metierDots[$loop->index % count($metierDots)] }}"></span>
                            <p class="flex-1 text-[15px] md:text-[12.5px] text-[#1B1B18] dark:text-[#F3EFE7] truncate">{{ $isFr ? $tm->name_fr : ($tm->name_en ?? $tm->name_fr) }}</p>
                            <p class="text-[14px] md:text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $fmtNum($tm->artisan_count) }} <span class="font-normal text-[#8A857A] dark:text-[#868778]">({{ number_format($tm->artisan_count / $topMetierTotal * 100, 1, $isFr ? ',' : '.', '') }}%)</span></p>
                        </div>
                        @empty
                        <p class="ui-empty">{{ $isFr ? 'Aucune donnée.' : 'No data.' }}</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <p class="mt-6 text-center text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">
                {{ $isFr ? '© 2025 Artisan Hub 237. Tous droits réservés.' : '© 2025 Artisan Hub 237. All rights reserved.' }}
            </p>
@endsection
