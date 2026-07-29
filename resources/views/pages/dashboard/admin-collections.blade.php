@extends('layouts.admin')

@php
    $isFr = $lang === 'fr';
    $adminActive = 'collections';
    $pageTitle = $isFr ? 'Collections Héritage' : 'Heritage Collections';
    $pageBreadcrumb = [['Accueil', route('dashboard.admin', ['lang' => $lang])], [$isFr ? 'Collections Héritage' : 'Heritage Collections', null]];

    // Cropped design artwork fallbacks (used only when a collection has no cover_image)
    $hcArt = [
        'bronzes-royaux-bamoun'            => 'hc-bronzes.png',
        'tissus-traditionnels-bamileke'    => 'hc-tissus.png',
        'poteries-de-ladamaoua'            => 'hc-poteries.png',
        'masques-traditionnels-bassa'      => 'hc-masques.png',
        'vannerie-du-nord'                 => 'hc-vannerie.png',
        'bijoux-traditionnels-grassfields' => 'hc-bijoux.png',
        'sculptures-sur-pierre-de-lest'    => 'hc-pierre.png',
        'cuirs-et-peaux-du-sud'            => 'hc-cuirs.png',
    ];

    $hcStatusMeta = [
        'published' => [$isFr ? 'Publié' : 'Published',    'bg-[#E2F3E8] dark:bg-[#0C3D1D] text-[#157A43] dark:text-[#339B56]'],
        'in_review' => [$isFr ? 'En révision' : 'In review', 'bg-[#FDF0DC] dark:bg-[#3A2B06] text-[#C97A16] dark:text-[#EDB33A]'],
        'draft'     => [$isFr ? 'Brouillon' : 'Draft',      'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]'],
    ];

    $hcMonthsFr = [1 => 'Jan', 2 => 'Fév', 3 => 'Mars', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin', 7 => 'Juil', 8 => 'Août', 9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'];
    $hcDate = function ($value) use ($isFr, $hcMonthsFr) {
        $d = \Carbon\Carbon::parse($value);
        return $isFr ? sprintf('%02d %s %d', $d->day, $hcMonthsFr[$d->month], $d->year) : $d->format('d M Y');
    };

    // Donut palette per category (fallback: sand)
    $hcPalette = [
        'Sculpture & Bois'    => '#14652F',
        'Sculpture & Bronze'  => '#8A6D1F',
        'Textiles & Tissus'   => '#E9B23C',
        'Poterie & Céramique' => '#1D4ED8',
        'Vannerie & Rotin'    => '#E07B2A',
        'Bijouterie & Métal'  => '#9B1C31',
        'Sculpture & Pierre'  => '#6B7280',
        'Cuir & Peaux'        => '#C86A4A',
    ];
    $hcDonutTotal = max(1, array_sum($hcByCategory));
    $hcSegments = [];
    $hcAngle = 0;
    foreach ($hcByCategory as $hcCat => $hcCount) {
        $hcColor = $hcPalette[$hcCat] ?? '#C9BFA8';
        $hcTo = $hcAngle + ($hcCount / $hcDonutTotal) * 360;
        $hcSegments[] = sprintf('%s %.2fdeg %.2fdeg', $hcColor, $hcAngle, $hcTo);
        $hcAngle = $hcTo;
    }
    $hcConic = 'conic-gradient(' . implode(', ', $hcSegments) . ')';

    // Category EN labels for the legend
    $hcCatEn = collect($collections)->pluck('category_en', 'category_fr')->all();
@endphp

@section('content')
            {{-- Title / breadcrumb + actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('admin.collections.create', ['lang' => $lang]) }}" class="ui-btn ui-btn-primary">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Ajouter une collection' : 'Add a collection' }}
                    </a>
                    <a href="{{ route('admin.cms') }}" class="ui-btn ui-btn-secondary">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        {{ $isFr ? 'Importer' : 'Import' }}
                    </a>
                </div>
            </div>

            {{-- Stat chips --}}
            @php
                // Real counts. The design's "vs mois dernier" deltas had no prior-period source.
                $hcChips = [
                    ['users-round',  '#157A43', '#E8F2EC', $isFr ? 'Total collections' : 'Total collections',            number_format($hcTotal)],
                    ['badge-check',  '#157A43', '#E8F2EC', $isFr ? 'Collections publiées' : 'Published collections',    number_format($hcPublished)],
                    ['file-pen',     '#C97A16', '#FDF0DC', $isFr ? 'Collections en brouillon' : 'Draft collections',    number_format($hcDraft)],
                    ['eye',          '#157A43', '#E8F2EC', $isFr ? 'Visites totales' : 'Total visits',                  number_format($hcVisits)],
                    ['users',        '#157A43', '#E8F2EC', $isFr ? 'Artisans impliqués' : 'Artisans involved',          number_format($hcArtisans)],
                ];
            @endphp
            <section class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                @foreach($hcChips as [$hcIcon, $hcIconColor, $hcTile, $hcLabel, $hcValue])
                <div class="ui-card flex items-start gap-3">
                    <span class="shrink-0 w-[38px] h-[38px] rounded-full flex items-center justify-center" style="background: {{ $hcTile }}">
                        <i data-lucide="{{ $hcIcon }}" class="w-[18px] h-[18px]" style="color: {{ $hcIconColor }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] dark:text-[#868778] uppercase leading-snug">{{ $hcLabel }}</p>
                        <p class="mt-0.5 text-[19px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-none">{{ $hcValue }}</p>
                    </div>
                </div>
                @endforeach
            </section>

            <div class="mt-4 grid grid-cols-1 xl:grid-cols-[1fr_355px] gap-4 items-start">
                {{-- LEFT: filters + table + pagination + performance/activity --}}
                <div class="min-w-0">
                    <section class="ui-card ui-card--flush">
                        {{-- Filter bar --}}
                        <form method="GET" action="{{ route('admin.collections') }}" class="p-4 flex flex-wrap items-center gap-2.5 border-b border-[#F5F1E8] dark:border-[#262B21]">
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            <div class="ui-field-group flex-1 min-w-[180px]">
                                <i data-lucide="search" class="w-[15px] h-[15px] text-[#8A857A] dark:text-[#868778] shrink-0"></i>
                                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher une collection...' : 'Search a collection...' }}" class="ui-field-bare flex-1 min-w-0">
                            </div>
                            <select name="region" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Région' : 'Region' }}</option>
                                @foreach($hcRegions as $hcRegion)
                                <option value="{{ $hcRegion }}" @selected($filters['region'] === $hcRegion)>{{ $hcRegion }}</option>
                                @endforeach
                            </select>
                            <select name="categorie" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Catégorie' : 'Category' }}</option>
                                @foreach($hcCategories as $hcCategory)
                                <option value="{{ $hcCategory }}" @selected($filters['categorie'] === $hcCategory)>{{ $hcCategory }}</option>
                                @endforeach
                            </select>
                            <select name="statut" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                                @foreach($hcStatusMeta as $hcKey => [$hcLabel2])
                                <option value="{{ $hcKey }}" @selected($filters['statut'] === $hcKey)>{{ $hcLabel2 }}</option>
                                @endforeach
                            </select>
                            <select name="visibilite" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Visibilité' : 'Visibility' }}</option>
                                <option value="public" @selected($filters['visibilite'] === 'public')>{{ $isFr ? 'Publique' : 'Public' }}</option>
                                <option value="private" @selected($filters['visibilite'] === 'private')>{{ $isFr ? 'Privée' : 'Private' }}</option>
                            </select>
                            <select name="date" class="ui-field ui-select">
                                <option value="">{{ $isFr ? 'Date de création' : 'Creation date' }}</option>
                                <option value="recent" @selected($filters['date'] === 'recent')>{{ $isFr ? 'Plus récentes' : 'Most recent' }}</option>
                                <option value="ancienne" @selected($filters['date'] === 'ancienne')>{{ $isFr ? 'Plus anciennes' : 'Oldest' }}</option>
                            </select>
                            <button type="submit" class="ui-btn ui-btn-primary">
                                <i data-lucide="filter" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Filtrer' : 'Filter' }}
                            </button>
                            <span class="hidden lg:flex items-center gap-1 ml-auto">
                                <span class="w-[32px] h-[32px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[#8A857A] dark:text-[#868778]"><i data-lucide="layout-grid" class="w-4 h-4"></i></span>
                                <span class="w-[32px] h-[32px] rounded-lg bg-[#0F4824] dark:bg-[#2E9250] flex items-center justify-center text-white dark:text-[#04150A]"><i data-lucide="list" class="w-4 h-4"></i></span>
                            </span>
                        </form>

                        {{-- Collections table --}}
                        <div class="ui-table-wrap">
                            <table class="ui-table min-w-[900px]">
                                <thead>
                                    <tr>
                                        <th>Collection</th>
                                        <th>{{ $isFr ? 'Région' : 'Region' }}</th>
                                        <th>{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                        <th>Artisans</th>
                                        <th class="text-center">{{ $isFr ? 'Produits' : 'Products' }}</th>
                                        <th class="text-center">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                        <th>{{ $isFr ? 'Visibilité' : 'Visibility' }}</th>
                                        <th class="text-right">{{ $isFr ? 'Visites' : 'Visits' }}</th>
                                        <th class="whitespace-nowrap">{{ $isFr ? 'Créé le' : 'Created' }}</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($collections as $c)
                                    @php [$hcStatusLabel, $hcStatusCls] = $hcStatusMeta[$c->status] ?? [$c->status, 'bg-[#EEECE6] dark:bg-[#1A1E16] text-[#6F6B60] dark:text-[#868778]']; @endphp
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3 min-w-[210px]">
                                                @if($c->cover_image)
                                                <img src="{{ asset('storage/' . $c->cover_image) }}" alt="" class="w-[34px] h-[34px] rounded-lg object-cover shrink-0">
                                                @elseif(isset($hcArt[$c->slug]))
                                                <img src="{{ asset('images/landing/' . $hcArt[$c->slug]) }}" alt="" class="w-[34px] h-[34px] rounded-lg object-cover shrink-0">
                                                @else
                                                <span class="w-[34px] h-[34px] rounded-lg bg-[#EEECE6] dark:bg-[#1A1E16] flex items-center justify-center shrink-0"><i data-lucide="layers" class="w-4 h-4 text-[#8A857A] dark:text-[#868778]"></i></span>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-snug">{{ $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr) }}</p>
                                                    <p class="text-[11px] text-[#8A857A] dark:text-[#868778] truncate">{{ $isFr ? $c->description_fr : ($c->description_en ?? $c->description_fr) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-[12px] font-semibold text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? $c->region_fr : ($c->region_en ?? $c->region_fr) }}</p>
                                            <p class="text-[11px] text-[#8A857A] dark:text-[#868778]">{{ $c->city }}</p>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $isFr ? $c->category_fr : ($c->category_en ?? $c->category_fr) }}</td>
                                        <td>
                                            <div class="flex items-center">
                                                @foreach(['#C9DCCF', '#E9C25A', '#D8A47F'] as $hcJ => $hcTone)
                                                <span class="w-[22px] h-[22px] rounded-full border-2 border-white flex items-center justify-center {{ $hcJ > 0 ? '-ml-2' : '' }}" style="background: {{ $hcTone }}"><i data-lucide="user" class="w-[11px] h-[11px] text-[#0B3B20] dark:text-[#339B56]"></i></span>
                                                @endforeach
                                                <span class="-ml-1.5 min-w-[26px] h-[22px] px-1 rounded-full bg-[#F1EDE2] dark:bg-[#1A1E16] border-2 border-white text-[10px] font-bold text-[#55524A] dark:text-[#B4B5A6] flex items-center justify-center">+{{ $c->artisans_count }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $c->products_count }}</td>
                                        <td class="text-center"><span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold whitespace-nowrap {{ $hcStatusCls }}">{{ $hcStatusLabel }}</span></td>
                                        <td>
                                            <span class="inline-flex items-center gap-1.5 text-[12px] text-[#3B382F] dark:text-[#B4B5A6] whitespace-nowrap">
                                                <i data-lucide="{{ $c->visibility === 'public' ? 'globe' : 'lock' }}" class="w-3.5 h-3.5 text-[#8A857A] dark:text-[#868778]"></i>
                                                {{ $c->visibility === 'public' ? ($isFr ? 'Publique' : 'Public') : ($isFr ? 'Privée' : 'Private') }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ number_format($c->visits_count) }}</td>
                                        <td class="whitespace-nowrap">{{ $hcDate($c->created_at) }}</td>
                                        <td class="text-right whitespace-nowrap">
                                            <a href="{{ route('products.index', ['region' => $c->region_fr, 'lang' => $lang]) }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="inline-flex w-[28px] h-[28px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] items-center justify-center text-[#55524A] dark:text-[#B4B5A6] hover:text-[#157A43] dark:hover:text-[#339B56] hover:border-[#157A43] dark:hover:border-[#2E9250]"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                            <a href="{{ route('admin.cms') }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}" class="ml-1 inline-flex w-[28px] h-[28px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] items-center justify-center text-[#55524A] dark:text-[#B4B5A6] hover:text-[#C97A16] dark:hover:text-[#EDB33A] hover:border-[#C97A16] dark:hover:border-[#E9A81E]"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                            <a href="{{ route('admin.products') }}" title="{{ $isFr ? 'Plus d\'options' : 'More options' }}" class="ml-1 inline-flex w-[28px] h-[28px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] items-center justify-center text-[#55524A] dark:text-[#B4B5A6] hover:text-[#1B1B18] dark:hover:text-[#F3EFE7]"><i data-lucide="more-vertical" class="w-3.5 h-3.5"></i></a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="10" class="ui-empty">{{ $isFr ? 'Aucune collection.' : 'No collections.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="px-5 py-3.5 border-t border-[#F5F1E8] dark:border-[#262B21] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[11.5px] text-[#8A857A] dark:text-[#868778]">
                                {{ $isFr
                                    ? 'Affichage de 1 à ' . count($collections) . ' sur ' . $hcTotal . ' collections'
                                    : 'Showing 1 to ' . count($collections) . ' of ' . $hcTotal . ' collections' }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-[28px] h-[28px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[#C9C3B5] dark:text-[#868778]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></span>
                                <span class="w-[28px] h-[28px] rounded-lg bg-[#0F4824] dark:bg-[#2E9250] text-white dark:text-[#04150A] text-[11.5px] font-bold flex items-center justify-center">1</span>
                                <span class="w-[28px] h-[28px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] flex items-center justify-center text-[#C9C3B5] dark:text-[#868778]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></span>
                                <span class="ml-2 h-[28px] rounded-lg border border-[#EAE5D8] dark:border-[#262B21] px-2.5 text-[11.5px] text-[#55524A] dark:text-[#B4B5A6] flex items-center">10 / page</span>
                            </div>
                        </div>
                    </section>

                    {{-- Bottom row: performances + recent activity --}}
                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-[1.35fr_1fr] gap-4 items-start">
                        <section class="ui-card">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="ui-card-title">{{ $isFr ? 'Performances des collections' : 'Collection performance' }}</h2>
                                <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16] dark:text-[#EDB33A]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                            </div>
                            <div class="mt-4 grid grid-cols-2 xl:grid-cols-4 gap-4">
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#F2E8D5] dark:bg-[#3A2B06] flex items-center justify-center"><i data-lucide="award" class="w-[18px] h-[18px] text-[#8A6D1F] dark:text-[#EDB33A]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] dark:text-[#868778] uppercase">{{ $isFr ? 'Meilleure collection' : 'Best collection' }}</p>
                                    @if($hcBest)
                                    <p class="mt-1 text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-snug">{{ $isFr ? $hcBest->name_fr : ($hcBest->name_en ?? $hcBest->name_fr) }}</p>
                                    <p class="mt-0.5 text-[11px] text-[#6F6B60] dark:text-[#868778]">{{ number_format($hcBest->visits_count) }} {{ $isFr ? 'visites' : 'visits' }}</p>
                                    @else
                                    <p class="mt-1 text-[12.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">—</p>
                                    @endif
                                </div>
                                {{-- The design's engagement rate, average visit time and conversion rate had
                                     no analytics table behind them; these three tiles now read the collections table. --}}
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#FDF0DC] dark:bg-[#3A2B06] flex items-center justify-center"><i data-lucide="eye" class="w-[18px] h-[18px] text-[#C97A16] dark:text-[#EDB33A]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] dark:text-[#868778] uppercase">{{ $isFr ? 'Visites totales' : 'Total visits' }}</p>
                                    <p class="mt-1 text-[17px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($hcVisits) }}</p>
                                </div>
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#E8F2EC] dark:bg-[#1A1E16] flex items-center justify-center"><i data-lucide="users" class="w-[18px] h-[18px] text-[#157A43] dark:text-[#339B56]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] dark:text-[#868778] uppercase">{{ $isFr ? 'Artisans mis en avant' : 'Featured artisans' }}</p>
                                    <p class="mt-1 text-[17px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($hcArtisans) }}</p>
                                </div>
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#E8F2EC] dark:bg-[#1A1E16] flex items-center justify-center"><i data-lucide="badge-check" class="w-[18px] h-[18px] text-[#157A43] dark:text-[#339B56]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] dark:text-[#868778] uppercase">{{ $isFr ? 'Collections publiées' : 'Published collections' }}</p>
                                    <p class="mt-1 text-[17px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($hcPublished) }} / {{ number_format($hcTotal) }}</p>
                                </div>
                            </div>
                        </section>

                        <section class="ui-card">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="ui-card-title">{{ $isFr ? 'Activités récentes' : 'Recent activity' }}</h2>
                                <a href="{{ route('admin.audit-log') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16] dark:text-[#EDB33A]">{{ $isFr ? 'Voir toutes' : 'See all' }} →</a>
                            </div>
                            @php
                                // Real recently-updated collections (no dedicated activity log exists)
                                $hcStatusIcons = ['published' => ['badge-check', '#157A43', '#E8F2EC'], 'in_review' => ['send', '#C97A16', '#FDF0DC'], 'draft' => ['pencil', '#6F6B60', '#EEECE6']];
                                $hcActivities = collect($collections)->sortByDesc('updated_at')->take(4)->map(function ($c) use ($isFr, $hcStatusIcons) {
                                    [$icon, $color, $tile] = $hcStatusIcons[$c->status] ?? ['pencil', '#B42025', '#FDE9E9'];
                                    $name = $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr);
                                    return [$icon, $color, $tile, $isFr ? 'Collection mise à jour' : 'Collection updated', $name, \Illuminate\Support\Carbon::parse($c->updated_at)->diffForHumans(null, true, false, 1)];
                                });
                            @endphp
                            <ul class="mt-3 divide-y divide-[#F5F1E8] dark:divide-[#262B21]">
                                @forelse($hcActivities as [$hcAIcon, $hcAColor, $hcATile, $hcATitle, $hcASub, $hcAWhen])
                                <li class="py-2.5 flex items-start gap-3">
                                    <span class="shrink-0 w-[30px] h-[30px] rounded-lg flex items-center justify-center" style="background: {{ $hcATile }}"><i data-lucide="{{ $hcAIcon }}" class="w-[14px] h-[14px]" style="color: {{ $hcAColor }}"></i></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-bold text-[#1B1B18] dark:text-[#F3EFE7] leading-snug">{{ $hcATitle }}</p>
                                        <p class="text-[11px] text-[#6F6B60] dark:text-[#868778] truncate">{{ $hcASub }}</p>
                                    </div>
                                    <span class="shrink-0 text-[10.5px] text-[#8A857A] dark:text-[#868778] whitespace-nowrap">{{ $hcAWhen }}</span>
                                </li>
                                @empty
                                <li class="py-2.5 text-[11.5px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Aucune activité récente.' : 'No recent activity.' }}</li>
                                @endforelse
                            </ul>
                        </section>
                    </div>
                </div>

                {{-- RIGHT rail: donut + map + featured artisan --}}
                <div class="space-y-4">
                    <section class="ui-card">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="ui-card-title">{{ $isFr ? 'Répartition par catégorie' : 'Breakdown by category' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16] dark:text-[#EDB33A]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="shrink-0 w-[118px] h-[118px] rounded-full flex items-center justify-center" style="background: {{ $hcConic }}">
                                <div class="w-[76px] h-[76px] bg-white dark:bg-[#12150F] rounded-full flex flex-col items-center justify-center leading-tight">
                                    <span class="text-[19px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $hcTotal }}</span>
                                    <span class="text-[10px] text-[#8A857A] dark:text-[#868778]">Total</span>
                                </div>
                            </div>
                            <ul class="flex-1 min-w-0 space-y-1.5">
                                @foreach($hcByCategory as $hcCat => $hcCount)
                                <li class="flex items-center gap-2">
                                    <span class="shrink-0 w-[9px] h-[9px] rounded-[3px]" style="background: {{ $hcPalette[$hcCat] ?? '#C9BFA8' }}"></span>
                                    <span class="flex-1 min-w-0 truncate text-[11px] text-[#3B382F] dark:text-[#B4B5A6]">{{ $isFr ? $hcCat : ($hcCatEn[$hcCat] ?? $hcCat) }}</span>
                                    <span class="shrink-0 text-[11px] font-semibold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $hcCount }} ({{ number_format($hcCount / $hcDonutTotal * 100, 1) }}%)</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>

                    <section class="ui-card">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="ui-card-title">{{ $isFr ? 'Fiches artisans par région' : 'Artisan records by region' }}</h2>
                            <a href="{{ route('admin.regions') }}" class="shrink-0 text-[11px] font-semibold text-[#157A43] dark:text-[#339B56]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        @include('pages.dashboard.partials.cameroon-choropleth')
                    </section>

                    <section class="ui-card">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="ui-card-title">{{ $isFr ? 'Artisan à l\'honneur' : 'Featured artisan' }}</h2>
                            @if($hcFeatured)<a href="{{ route('admin.businesses.detail', ['id' => $hcFeatured->id, 'lang' => $lang]) }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16] dark:text-[#EDB33A]">{{ $isFr ? 'Voir le profil' : 'View profile' }} →</a>@endif
                        </div>
                        {{-- The design named an artisan and gave him invented collection/product/visit
                             counts; this now reads the most-viewed published vendor. --}}
                        @if($hcFeatured)
                        <div class="mt-3.5 flex items-start gap-4">
                            <img src="{{ $hcFeatured->cover_image ? asset('storage/' . $hcFeatured->cover_image) : asset('images/landing/hc-artisan.png') }}" alt="" class="w-[96px] h-[104px] rounded-xl object-cover shrink-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-[14.5px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ $isFr ? $hcFeatured->name_fr : ($hcFeatured->name_en ?? $hcFeatured->name_fr) }}</p>
                                @if($hcFeatured->industry_fr)<p class="mt-0.5 text-[11.5px] font-semibold text-[#8A6D1F] dark:text-[#EDB33A]">{{ $isFr ? $hcFeatured->industry_fr : ($hcFeatured->industry_en ?? $hcFeatured->industry_fr) }}</p>@endif
                                @if($hcFeatured->region_fr)<p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#6F6B60] dark:text-[#868778]"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i> {{ $isFr ? $hcFeatured->region_fr : ($hcFeatured->region_en ?? $hcFeatured->region_fr) }}</p>@endif
                                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                                    <div><p class="text-[10px] text-[#8A857A] dark:text-[#868778]">Collections</p><p class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($hcFeaturedStats['collections']) }}</p></div>
                                    <div><p class="text-[10px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Produits' : 'Products' }}</p><p class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($hcFeaturedStats['products']) }}</p></div>
                                    <div><p class="text-[10px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Visites' : 'Visits' }}</p><p class="text-[14px] font-bold text-[#1B1B18] dark:text-[#F3EFE7]">{{ number_format($hcFeaturedStats['views']) }}</p></div>
                                </div>
                            </div>
                        </div>
                        @else
                        <p class="mt-3.5 text-[12px] text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Aucune donnée pour le moment.' : 'No data yet.' }}</p>
                        @endif
                        <p class="mt-3.5 bg-[#F2E8D5] dark:bg-[#3A2B06] rounded-xl px-4 py-2.5 text-center text-[11.5px] font-semibold text-[#55524A] dark:text-[#B4B5A6]">
                            {{ $isFr ? '“Préserver notre héritage, créer notre avenir”' : '“Preserving our heritage, creating our future”' }}
                        </p>
                    </section>
                </div>
            </div>
@endsection
