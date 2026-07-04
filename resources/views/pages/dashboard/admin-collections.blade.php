@php
    $isFr = $lang === 'fr';
    $adminActive = 'collections';

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
        'published' => [$isFr ? 'Publié' : 'Published',    'bg-[#E2F3E8] text-[#157A43]'],
        'in_review' => [$isFr ? 'En révision' : 'In review', 'bg-[#FDF0DC] text-[#C97A16]'],
        'draft'     => [$isFr ? 'Brouillon' : 'Draft',      'bg-[#EEECE6] text-[#6F6B60]'],
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
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isFr ? 'Collections Héritage — Administration' : 'Heritage Collections — Administration' }}</title>
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { colors: { leaf: '#14652F' }, fontFamily: { sans: ['Poppins', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="{{ asset('vendor/lucide.min.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; } html, body { overflow-x: clip; }
        #ad-sidebar { display: none; }
        #ad-sidebar.ad-open { display: flex; position: fixed; inset: 0 auto 0 0; width: 270px; z-index: 60; overflow-y: auto; }
        @media (min-width: 1024px) { #ad-sidebar, #ad-sidebar.ad-open { display: flex; position: sticky; top: 0; height: 100vh; width: 250px; } }
    </style>
</head>
<body class="bg-[#F8F4EC] text-[#1B1B18] antialiased">
<img src="{{ asset('images/landing/ad-kente-top.png') }}" alt="" class="w-full h-[8px] object-cover" aria-hidden="true">

<div class="flex items-stretch min-h-screen">
    @include('pages.partials.admin-sidebar')
    <div class="flex-1 min-w-0">
        @include('pages.partials.admin-topbar')

        <main class="px-5 lg:px-7 pb-8">
            {{-- Title / breadcrumb + actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-[20px] font-bold text-[#1B1B18]">{{ $isFr ? 'Collections Héritage' : 'Heritage Collections' }}</h1>
                    <p class="mt-0.5 text-[12.5px] text-[#6F6B60]">
                        <a href="{{ route('dashboard.admin') }}" class="hover:text-[#157A43]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
                        <span class="mx-1">/</span>
                        <span>{{ $isFr ? 'Collections Héritage' : 'Heritage Collections' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2.5 shrink-0">
                    <a href="{{ route('admin.collections.create', ['lang' => $lang]) }}" class="inline-flex items-center gap-2 bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-white transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        {{ $isFr ? 'Ajouter une collection' : 'Add a collection' }}
                    </a>
                    <a href="{{ route('admin.cms') }}" class="inline-flex items-center gap-2 bg-white border border-[#DCD6C8] hover:border-[#157A43] rounded-lg px-4 h-[38px] text-[12.5px] font-semibold text-[#157A43] transition-colors">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        {{ $isFr ? 'Importer' : 'Import' }}
                    </a>
                </div>
            </div>

            {{-- Stat chips --}}
            @php
                $hcChips = [
                    ['users-round',  '#157A43', '#E8F2EC', $isFr ? 'Total collections' : 'Total collections',            number_format($hcTotal),            '+12.5%', true],
                    ['badge-check',  '#157A43', '#E8F2EC', $isFr ? 'Collections publiées' : 'Published collections',    number_format($hcPublished),        '-9.8%',  false],
                    ['file-pen',     '#C97A16', '#FDF0DC', $isFr ? 'Collections en brouillon' : 'Draft collections',    number_format($hcDraft),            '-4.3%',  false],
                    ['eye',          '#157A43', '#E8F2EC', $isFr ? 'Visites totales' : 'Total visits',                  number_format($hcVisits),           '+21.6%', true],
                    ['users',        '#157A43', '#E8F2EC', $isFr ? 'Artisans impliqués' : 'Artisans involved',          number_format($hcArtisans),         '+14.7%', true],
                ];
            @endphp
            <section class="mt-4 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3">
                @foreach($hcChips as [$hcIcon, $hcIconColor, $hcTile, $hcLabel, $hcValue, $hcTrend, $hcUp])
                <div class="bg-white border border-[#EFEBE2] rounded-2xl px-4 py-3.5 flex items-start gap-3">
                    <span class="shrink-0 w-[38px] h-[38px] rounded-full flex items-center justify-center" style="background: {{ $hcTile }}">
                        <i data-lucide="{{ $hcIcon }}" class="w-[18px] h-[18px]" style="color: {{ $hcIconColor }}"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10.5px] font-bold tracking-[0.05em] text-[#8A857A] uppercase leading-snug">{{ $hcLabel }}</p>
                        <p class="mt-0.5 text-[19px] font-bold text-[#1B1B18] leading-none">{{ $hcValue }}
                            <span class="ml-1 text-[11px] font-semibold {{ $hcUp ? 'text-[#157A43]' : 'text-[#C0392B]' }}">
                                <i data-lucide="{{ $hcUp ? 'arrow-up' : 'arrow-down' }}" class="inline w-3 h-3 -mt-0.5"></i>{{ ltrim($hcTrend, '+-') }}
                            </span>
                        </p>
                        <p class="mt-1 text-[10.5px] text-[#8A857A]">{{ $isFr ? 'vs mois dernier' : 'vs last month' }}</p>
                    </div>
                </div>
                @endforeach
            </section>

            <div class="mt-4 grid grid-cols-1 xl:grid-cols-[1fr_355px] gap-4 items-start">
                {{-- LEFT: filters + table + pagination + performance/activity --}}
                <div class="min-w-0">
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl overflow-hidden">
                        {{-- Filter bar --}}
                        <form method="GET" action="{{ route('admin.collections') }}" class="p-4 flex flex-wrap items-center gap-2.5 border-b border-[#F5F1E8]">
                            <input type="hidden" name="lang" value="{{ $lang }}">
                            <div class="flex items-center gap-2 bg-[#FBF9F4] border border-[#E9E4D8] rounded-lg px-3 h-[36px] flex-1 min-w-[180px]">
                                <i data-lucide="search" class="w-[15px] h-[15px] text-[#8A857A] shrink-0"></i>
                                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="{{ $isFr ? 'Rechercher une collection...' : 'Search a collection...' }}" class="flex-1 min-w-0 bg-transparent text-[12px] focus:outline-none placeholder-[#8A857A]">
                            </div>
                            <select name="region" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                                <option value="">{{ $isFr ? 'Région' : 'Region' }}</option>
                                @foreach($hcRegions as $hcRegion)
                                <option value="{{ $hcRegion }}" @selected($filters['region'] === $hcRegion)>{{ $hcRegion }}</option>
                                @endforeach
                            </select>
                            <select name="categorie" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                                <option value="">{{ $isFr ? 'Catégorie' : 'Category' }}</option>
                                @foreach($hcCategories as $hcCategory)
                                <option value="{{ $hcCategory }}" @selected($filters['categorie'] === $hcCategory)>{{ $hcCategory }}</option>
                                @endforeach
                            </select>
                            <select name="statut" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                                <option value="">{{ $isFr ? 'Statut' : 'Status' }}</option>
                                @foreach($hcStatusMeta as $hcKey => [$hcLabel2])
                                <option value="{{ $hcKey }}" @selected($filters['statut'] === $hcKey)>{{ $hcLabel2 }}</option>
                                @endforeach
                            </select>
                            <select name="visibilite" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                                <option value="">{{ $isFr ? 'Visibilité' : 'Visibility' }}</option>
                                <option value="public" @selected($filters['visibilite'] === 'public')>{{ $isFr ? 'Publique' : 'Public' }}</option>
                                <option value="private" @selected($filters['visibilite'] === 'private')>{{ $isFr ? 'Privée' : 'Private' }}</option>
                            </select>
                            <select name="date" class="h-[36px] bg-white border border-[#E9E4D8] rounded-lg px-2.5 text-[12px] text-[#3B382F] focus:outline-none">
                                <option value="">{{ $isFr ? 'Date de création' : 'Creation date' }}</option>
                                <option value="recent" @selected($filters['date'] === 'recent')>{{ $isFr ? 'Plus récentes' : 'Most recent' }}</option>
                                <option value="ancienne" @selected($filters['date'] === 'ancienne')>{{ $isFr ? 'Plus anciennes' : 'Oldest' }}</option>
                            </select>
                            <button type="submit" class="inline-flex items-center gap-1.5 h-[36px] bg-[#0F4824] hover:bg-[#14652F] rounded-lg px-3.5 text-[12px] font-semibold text-white transition-colors">
                                <i data-lucide="filter" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Filtrer' : 'Filter' }}
                            </button>
                            <span class="hidden lg:flex items-center gap-1 ml-auto">
                                <span class="w-[32px] h-[32px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#8A857A]"><i data-lucide="layout-grid" class="w-4 h-4"></i></span>
                                <span class="w-[32px] h-[32px] rounded-lg bg-[#0F4824] flex items-center justify-center text-white"><i data-lucide="list" class="w-4 h-4"></i></span>
                            </span>
                        </form>

                        {{-- Collections table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px]">
                                <thead>
                                    <tr class="bg-[#F8F4EC] text-left">
                                        <th class="pl-5 pr-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Collection</th>
                                        <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Région' : 'Region' }}</th>
                                        <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Catégorie' : 'Category' }}</th>
                                        <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Artisans</th>
                                        <th class="px-2 py-3 text-center text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Produits' : 'Products' }}</th>
                                        <th class="px-2 py-3 text-center text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Statut' : 'Status' }}</th>
                                        <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Visibilité' : 'Visibility' }}</th>
                                        <th class="px-2 py-3 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">{{ $isFr ? 'Visites' : 'Visits' }}</th>
                                        <th class="px-2 py-3 text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase whitespace-nowrap">{{ $isFr ? 'Créé le' : 'Created' }}</th>
                                        <th class="px-2 pr-5 py-3 text-right text-[10.5px] font-bold tracking-[0.05em] text-[#8A6D1F] uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#F5F1E8]">
                                    @forelse($collections as $c)
                                    @php [$hcStatusLabel, $hcStatusCls] = $hcStatusMeta[$c->status] ?? [$c->status, 'bg-[#EEECE6] text-[#6F6B60]']; @endphp
                                    <tr>
                                        <td class="pl-5 pr-2 py-3">
                                            <div class="flex items-center gap-3 min-w-[210px]">
                                                @if($c->cover_image)
                                                <img src="{{ asset('storage/' . $c->cover_image) }}" alt="" class="w-[34px] h-[34px] rounded-lg object-cover shrink-0">
                                                @elseif(isset($hcArt[$c->slug]))
                                                <img src="{{ asset('images/landing/' . $hcArt[$c->slug]) }}" alt="" class="w-[34px] h-[34px] rounded-lg object-cover shrink-0">
                                                @else
                                                <span class="w-[34px] h-[34px] rounded-lg bg-[#EEECE6] flex items-center justify-center shrink-0"><i data-lucide="layers" class="w-4 h-4 text-[#8A857A]"></i></span>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="text-[12.5px] font-bold text-[#1B1B18] leading-snug">{{ $isFr ? $c->name_fr : ($c->name_en ?? $c->name_fr) }}</p>
                                                    <p class="text-[11px] text-[#8A857A] truncate">{{ $isFr ? $c->description_fr : ($c->description_en ?? $c->description_fr) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-3">
                                            <p class="text-[12px] font-semibold text-[#3B382F]">{{ $isFr ? $c->region_fr : ($c->region_en ?? $c->region_fr) }}</p>
                                            <p class="text-[11px] text-[#8A857A]">{{ $c->city }}</p>
                                        </td>
                                        <td class="px-2 py-3 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $isFr ? $c->category_fr : ($c->category_en ?? $c->category_fr) }}</td>
                                        <td class="px-2 py-3">
                                            <div class="flex items-center">
                                                @foreach(['#C9DCCF', '#E9C25A', '#D8A47F'] as $hcJ => $hcTone)
                                                <span class="w-[22px] h-[22px] rounded-full border-2 border-white flex items-center justify-center {{ $hcJ > 0 ? '-ml-2' : '' }}" style="background: {{ $hcTone }}"><i data-lucide="user" class="w-[11px] h-[11px] text-[#0B3B20]"></i></span>
                                                @endforeach
                                                <span class="-ml-1.5 min-w-[26px] h-[22px] px-1 rounded-full bg-[#F1EDE2] border-2 border-white text-[10px] font-bold text-[#55524A] flex items-center justify-center">+{{ $c->artisans_count }}</span>
                                            </div>
                                        </td>
                                        <td class="px-2 py-3 text-center text-[12.5px] font-semibold text-[#1B1B18]">{{ $c->products_count }}</td>
                                        <td class="px-2 py-3 text-center"><span class="inline-block rounded-md px-2.5 py-1 text-[10.5px] font-semibold whitespace-nowrap {{ $hcStatusCls }}">{{ $hcStatusLabel }}</span></td>
                                        <td class="px-2 py-3">
                                            <span class="inline-flex items-center gap-1.5 text-[12px] text-[#3B382F] whitespace-nowrap">
                                                <i data-lucide="{{ $c->visibility === 'public' ? 'globe' : 'lock' }}" class="w-3.5 h-3.5 text-[#8A857A]"></i>
                                                {{ $c->visibility === 'public' ? ($isFr ? 'Publique' : 'Public') : ($isFr ? 'Privée' : 'Private') }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-3 text-right text-[12px] text-[#3B382F]">{{ number_format($c->visits_count) }}</td>
                                        <td class="px-2 py-3 text-[12px] text-[#3B382F] whitespace-nowrap">{{ $hcDate($c->created_at) }}</td>
                                        <td class="px-2 pr-5 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('products.index', ['region' => $c->region_fr, 'lang' => $lang]) }}" title="{{ $isFr ? 'Voir' : 'View' }}" class="inline-flex w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#157A43] hover:border-[#157A43]"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                            <a href="{{ route('admin.cms') }}" title="{{ $isFr ? 'Modifier' : 'Edit' }}" class="ml-1 inline-flex w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#C97A16] hover:border-[#C97A16]"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></a>
                                            <a href="{{ route('admin.products') }}" title="{{ $isFr ? 'Plus d\'options' : 'More options' }}" class="ml-1 inline-flex w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] items-center justify-center text-[#55524A] hover:text-[#1B1B18]"><i data-lucide="more-vertical" class="w-3.5 h-3.5"></i></a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="10" class="px-5 py-10 text-center text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune collection.' : 'No collections.' }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="px-5 py-3.5 border-t border-[#F5F1E8] flex flex-wrap items-center justify-between gap-3">
                            <p class="text-[11.5px] text-[#8A857A]">
                                {{ $isFr
                                    ? 'Affichage de 1 à ' . count($collections) . ' sur ' . $hcTotal . ' collections'
                                    : 'Showing 1 to ' . count($collections) . ' of ' . $hcTotal . ' collections' }}
                            </p>
                            <div class="flex items-center gap-1.5">
                                <span class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#C9C3B5]"><i data-lucide="chevron-left" class="w-3.5 h-3.5"></i></span>
                                <span class="w-[28px] h-[28px] rounded-lg bg-[#0F4824] text-white text-[11.5px] font-bold flex items-center justify-center">1</span>
                                <span class="w-[28px] h-[28px] rounded-lg border border-[#E9E4D8] flex items-center justify-center text-[#C9C3B5]"><i data-lucide="chevron-right" class="w-3.5 h-3.5"></i></span>
                                <span class="ml-2 h-[28px] rounded-lg border border-[#E9E4D8] px-2.5 text-[11.5px] text-[#55524A] flex items-center">10 / page</span>
                            </div>
                        </div>
                    </section>

                    {{-- Bottom row: performances + recent activity --}}
                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-[1.35fr_1fr] gap-4 items-start">
                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Performances des collections' : 'Collection performance' }}</h2>
                                <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                            </div>
                            <div class="mt-4 grid grid-cols-2 xl:grid-cols-4 gap-4">
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#F2E8D5] flex items-center justify-center"><i data-lucide="award" class="w-[18px] h-[18px] text-[#8A6D1F]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Meilleure collection' : 'Best collection' }}</p>
                                    @if($hcBest)
                                    <p class="mt-1 text-[12.5px] font-bold text-[#1B1B18] leading-snug">{{ $isFr ? $hcBest->name_fr : ($hcBest->name_en ?? $hcBest->name_fr) }}</p>
                                    <p class="mt-0.5 text-[11px] text-[#6F6B60]">{{ number_format($hcBest->visits_count) }} {{ $isFr ? 'visites' : 'visits' }}</p>
                                    @else
                                    <p class="mt-1 text-[12.5px] font-bold text-[#1B1B18]">—</p>
                                    @endif
                                </div>
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#E8F2EC] flex items-center justify-center"><i data-lucide="target" class="w-[18px] h-[18px] text-[#157A43]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Taux d\'engagement moyen' : 'Average engagement rate' }}</p>
                                    <p class="mt-1 text-[17px] font-bold text-[#1B1B18]">68.4%</p>
                                    <p class="mt-0.5 text-[11px] font-semibold text-[#157A43]"><i data-lucide="arrow-up" class="inline w-3 h-3 -mt-0.5"></i> 12.3%</p>
                                </div>
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#FDF0DC] flex items-center justify-center"><i data-lucide="clock" class="w-[18px] h-[18px] text-[#C97A16]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Temps moyen de visite' : 'Average visit time' }}</p>
                                    <p class="mt-1 text-[17px] font-bold text-[#1B1B18]">04m 32s</p>
                                    <p class="mt-0.5 text-[11px] font-semibold text-[#157A43]"><i data-lucide="arrow-up" class="inline w-3 h-3 -mt-0.5"></i> 8.7%</p>
                                </div>
                                <div>
                                    <span class="w-[38px] h-[38px] rounded-full bg-[#E8F2EC] flex items-center justify-center"><i data-lucide="trending-up" class="w-[18px] h-[18px] text-[#157A43]"></i></span>
                                    <p class="mt-2.5 text-[10px] font-bold tracking-[0.05em] text-[#8A857A] uppercase">{{ $isFr ? 'Taux de conversion' : 'Conversion rate' }}</p>
                                    <p class="mt-1 text-[17px] font-bold text-[#1B1B18]">12.6%</p>
                                    <p class="mt-0.5 text-[11px] font-semibold text-[#157A43]"><i data-lucide="arrow-up" class="inline w-3 h-3 -mt-0.5"></i> 9.2%</p>
                                </div>
                            </div>
                        </section>

                        <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                            <div class="flex items-center justify-between gap-3">
                                <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Activités récentes' : 'Recent activity' }}</h2>
                                <a href="{{ route('admin.audit-log') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir toutes' : 'See all' }} →</a>
                            </div>
                            @php
                                $hcActivities = [
                                    ['plus',        '#157A43', '#E8F2EC', $isFr ? 'Nouvelle collection ajoutée' : 'New collection added',            'Tissus Traditionnels Bamileke', $isFr ? 'Il y a 15 min' : '15 min ago'],
                                    ['badge-check', '#157A43', '#E8F2EC', $isFr ? 'Collection publiée' : 'Collection published',                     'Bronzes Royaux Bamoun',         $isFr ? 'Il y a 1 h' : '1 h ago'],
                                    ['pencil',      '#B42025', '#FDE9E9', $isFr ? 'Collection mise à jour' : 'Collection updated',                   'Poteries de l\'Adamaoua',       $isFr ? 'Il y a 3 h' : '3 h ago'],
                                    ['send',        '#C97A16', '#FDF0DC', $isFr ? 'Collection envoyée en révision' : 'Collection sent for review',   'Masques Traditionnels Bassa',   $isFr ? 'Il y a 5 h' : '5 h ago'],
                                ];
                            @endphp
                            <ul class="mt-3 divide-y divide-[#F5F1E8]">
                                @foreach($hcActivities as [$hcAIcon, $hcAColor, $hcATile, $hcATitle, $hcASub, $hcAWhen])
                                <li class="py-2.5 flex items-start gap-3">
                                    <span class="shrink-0 w-[30px] h-[30px] rounded-lg flex items-center justify-center" style="background: {{ $hcATile }}"><i data-lucide="{{ $hcAIcon }}" class="w-[14px] h-[14px]" style="color: {{ $hcAColor }}"></i></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-bold text-[#1B1B18] leading-snug">{{ $hcATitle }}</p>
                                        <p class="text-[11px] text-[#6F6B60] truncate">{{ $hcASub }}</p>
                                    </div>
                                    <span class="shrink-0 text-[10.5px] text-[#8A857A] whitespace-nowrap">{{ $hcAWhen }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </section>
                    </div>
                </div>

                {{-- RIGHT rail: donut + map + featured artisan --}}
                <div class="space-y-4">
                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Répartition par catégorie' : 'Breakdown by category' }}</h2>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <div class="mt-4 flex items-center gap-4">
                            <div class="shrink-0 w-[118px] h-[118px] rounded-full flex items-center justify-center" style="background: {{ $hcConic }}">
                                <div class="w-[76px] h-[76px] bg-white rounded-full flex flex-col items-center justify-center leading-tight">
                                    <span class="text-[19px] font-bold text-[#1B1B18]">{{ $hcTotal }}</span>
                                    <span class="text-[10px] text-[#8A857A]">Total</span>
                                </div>
                            </div>
                            <ul class="flex-1 min-w-0 space-y-1.5">
                                @foreach($hcByCategory as $hcCat => $hcCount)
                                <li class="flex items-center gap-2">
                                    <span class="shrink-0 w-[9px] h-[9px] rounded-[3px]" style="background: {{ $hcPalette[$hcCat] ?? '#C9BFA8' }}"></span>
                                    <span class="flex-1 min-w-0 truncate text-[11px] text-[#3B382F]">{{ $isFr ? $hcCat : ($hcCatEn[$hcCat] ?? $hcCat) }}</span>
                                    <span class="shrink-0 text-[11px] font-semibold text-[#1B1B18]">{{ $hcCount }} ({{ number_format($hcCount / $hcDonutTotal * 100, 1) }}%)</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Collections par région' : 'Collections by region' }}</h2>
                            <a href="{{ route('admin.regions') }}" class="shrink-0 text-[11px] font-semibold text-[#157A43]">{{ $isFr ? 'Voir le rapport' : 'View report' }} →</a>
                        </div>
                        <img src="{{ asset('images/landing/hc-map.png') }}" alt="{{ $isFr ? 'Carte des collections par région' : 'Map of collections by region' }}" class="mt-3 w-full object-contain">
                    </section>

                    <section class="bg-white border border-[#EFEBE2] rounded-2xl p-5">
                        <div class="flex items-center justify-between gap-3">
                            <h2 class="text-[12.5px] font-bold tracking-[0.04em] text-[#1B1B18] uppercase">{{ $isFr ? 'Artisan à l\'honneur' : 'Featured artisan' }}</h2>
                            <a href="{{ route('admin.businesses') }}" class="shrink-0 text-[11px] font-semibold text-[#C97A16]">{{ $isFr ? 'Voir le profil' : 'View profile' }} →</a>
                        </div>
                        <div class="mt-3.5 flex items-start gap-4">
                            <img src="{{ asset('images/landing/hc-artisan.png') }}" alt="Emmanuel Tchana" class="w-[96px] h-[104px] rounded-xl object-cover shrink-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-[14.5px] font-bold text-[#1B1B18]">Emmanuel Tchana</p>
                                <p class="mt-0.5 text-[11.5px] font-semibold text-[#8A6D1F]">{{ $isFr ? 'Maître Sculpteur sur Bronze' : 'Master Bronze Sculptor' }}</p>
                                <p class="mt-1.5 flex items-center gap-1.5 text-[11.5px] text-[#6F6B60]"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Foumban, Ouest</p>
                                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                                    <div><p class="text-[10px] text-[#8A857A]">Collections</p><p class="text-[14px] font-bold text-[#1B1B18]">3</p></div>
                                    <div><p class="text-[10px] text-[#8A857A]">{{ $isFr ? 'Produits' : 'Products' }}</p><p class="text-[14px] font-bold text-[#1B1B18]">28</p></div>
                                    <div><p class="text-[10px] text-[#8A857A]">{{ $isFr ? 'Visites' : 'Visits' }}</p><p class="text-[14px] font-bold text-[#1B1B18]">12,450</p></div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-3.5 bg-[#F2E8D5] rounded-xl px-4 py-2.5 text-center text-[11.5px] font-semibold text-[#55524A]">
                            {{ $isFr ? '“Préserver notre héritage, créer notre avenir”' : '“Preserving our heritage, creating our future”' }}
                        </p>
                    </section>
                </div>
            </div>
        </main>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
