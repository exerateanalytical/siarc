@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    // Filter pills, verbatim from the design
    $eventTypes = [
        ['',              $isFr ? 'Tous les événements' : 'All events'],
        ['salons',        $isFr ? 'Salons & Expositions' : 'Fairs & Exhibitions'],
        ['festivals',     $isFr ? 'Festivals Culturels' : 'Cultural Festivals'],
        ['ateliers',      $isFr ? 'Ateliers & Formations' : 'Workshops & Training'],
        ['conferences',   $isFr ? 'Conférences & Séminaires' : 'Conferences & Seminars'],
        ['marches',       $isFr ? 'Marchés & Foires' : 'Markets & Fairs'],
        ['concours',      $isFr ? 'Concours & Prix' : 'Competitions & Awards'],
        ['autres',        $isFr ? 'Autres' : 'Other'],
    ];

    // Event tickets come from the real events table; the design's ticket
    // artwork (icon / stub colour / badge / tags) is keyed by event_type.
    $typeMeta = [
        //             icon                stub     badge   badge label FR / EN                                  tags
        'salons'      => ['event-icon-1.png', 'green', 'red',    $isFr ? 'Événement national' : 'National event',
                          [['calendar-days', 'Expositions'], ['message-square', $isFr ? 'Conférences' : 'Conferences'], ['users', 'Ateliers'], ['share-2', 'Networking']]],
        'festivals'   => ['event-icon-2.png', 'red',   'gold',   $isFr ? 'Festival culturel' : 'Cultural festival',
                          [['palette', 'Arts'], ['music', 'Musique'], ['sparkles', $isFr ? 'Danse' : 'Dance'], ['hand', 'Artisanat']]],
        'ateliers'    => ['event-icon-3.png', 'green', 'greenb', $isFr ? 'Atelier & Formation' : 'Workshop & Training',
                          [['graduation-cap', 'Formation'], ['hand', 'Pratique'], ['layers', $isFr ? 'Matériaux' : 'Materials'], ['badge-check', 'Certification']]],
        'marches'     => ['event-icon-4.png', 'gold',  'gold',   $isFr ? 'Marché & Foire' : 'Market & Fair',
                          [['leaf', $isFr ? 'Produits Éco' : 'Eco products'], ['users', 'Rencontres'], ['hand', 'Ateliers'], ['shopping-bag', 'Ventes']]],
        'conferences' => ['event-icon-5.png', 'green', 'red',    $isFr ? 'Conférence' : 'Conference',
                          [['message-square', $isFr ? 'Conférence' : 'Conference'], ['users', 'Panel'], ['repeat', 'Échanges'], ['share-2', 'Réseautage']]],
        'concours'    => ['event-icon-6.png', 'red',   'gold',   $isFr ? 'Concours & Prix' : 'Competition & Award',
                          [['trophy', 'Compétition'], ['lightbulb', 'Innovation'], ['users', $isFr ? 'Jeunes Talents' : 'Young talents'], ['award', 'Récompenses']]],
        'autres'      => ['event-icon-1.png', 'green', 'red',    $isFr ? 'Événement' : 'Event',
                          [['calendar-days', 'Programme'], ['users', 'Rencontres'], ['share-2', 'Networking']]],
    ];
    $monthShort = $isFr
        ? [1 => 'JAN', 'FÉV', 'MARS', 'AVR', 'MAI', 'JUIN', 'JUIL', 'AOÛT', 'SEPT', 'OCT', 'NOV', 'DÉC']
        : [1 => 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

    $allEvents = $upcoming->concat($past);
    $designEvents = $allEvents->map(function ($ev) use ($isFr, $typeMeta, $monthShort) {
        [$icon, $stub, $badgeColor, $badge, $tags] = $typeMeta[$ev->event_type] ?? $typeMeta['autres'];
        $ends = $ev->ends_at ?? $ev->starts_at->copy()->addHours(8);
        return [
            'slug'      => $ev->slug,
            'type'      => $ev->event_type ?? 'autres',
            'regionKey' => $ev->region_key ?? '',
            'color'     => $stub,
            'icon'      => $icon,
            'day'       => $ev->starts_at->format('d'),
            'month'     => $monthShort[(int) $ev->starts_at->format('n')],
            'year'      => $ev->starts_at->format('Y'),
            'from'      => $ev->starts_at->format('H:i'),
            'to'        => $ends->format('H:i'),
            'badge'     => $badge,
            'badgeColor'=> $badgeColor,
            'title'     => $isFr ? $ev->name_fr : ($ev->name_en ?? $ev->name_fr),
            'city'      => $ev->city_fr ?? \Illuminate\Support\Str::afterLast($ev->location_fr ?? '', ', ') ?: 'Cameroun',
            'venue'     => $isFr ? ($ev->location_fr ?? '') : ($ev->location_en ?? $ev->location_fr ?? ''),
            'desc'      => \Illuminate\Support\Str::limit($isFr ? ($ev->description_fr ?? '') : ($ev->description_en ?? $ev->description_fr ?? ''), 140),
            'tags'      => $tags,
            'price'     => $isFr ? ($ev->price_fr ?? 'Entrée libre') : ($ev->price_en ?? 'Free entry'),
        ];
    })->all();

    // ?type= and ?region= narrow the real ticket list (sidebar/pill links)
    $activeType = (string) request('type', '');
    $activeRegion = (string) request('region', '');
    $shownEvents = array_values(array_filter($designEvents, fn ($e) =>
        ($activeType === '' || $e['type'] === $activeType)
        && ($activeRegion === '' || $e['regionKey'] === $activeRegion)));
    if (request('sort') === 'name') {
        usort($shownEvents, fn ($a, $b) => strcoll($a['title'], $b['title']));
    }

    // Real counts per type / region for the browse rails
    $typeTally = $allEvents->countBy('event_type');
    $regionTally = $allEvents->countBy('region_key');

    $stubColors = ['green' => '#06301A', 'red' => '#C1272D', 'gold' => '#EFA912'];
    $badgeColors = ['red' => '#C1272D', 'gold' => '#E9A825', 'greenb' => '#0E5A2F'];

    $browseCats = [
        ['calendar-days', $isFr ? 'Salons & Expositions' : 'Fairs & Exhibitions', '(' . ($typeTally['salons'] ?? 0) . ')', 'salons'],
        ['sparkles',      $isFr ? 'Festivals Culturels' : 'Cultural Festivals', '(' . ($typeTally['festivals'] ?? 0) . ')', 'festivals'],
        ['hand',          $isFr ? 'Ateliers & Formations' : 'Workshops & Training', '(' . ($typeTally['ateliers'] ?? 0) . ')', 'ateliers'],
        ['message-square',$isFr ? 'Conférences & Séminaires' : 'Conferences & Seminars', '(' . ($typeTally['conferences'] ?? 0) . ')', 'conferences'],
        ['store',         $isFr ? 'Marchés & Foires' : 'Markets & Fairs', '(' . ($typeTally['marches'] ?? 0) . ')', 'marches'],
        ['trophy',        $isFr ? 'Concours & Prix' : 'Competitions & Awards', '(' . ($typeTally['concours'] ?? 0) . ')', 'concours'],
        ['layout-grid',   $isFr ? 'Autres' : 'Other', '(' . ($typeTally['autres'] ?? 0) . ')', 'autres'],
    ];
    $browseRegions = collect([
        ['Centre', 'centre'], ['Littoral', 'littoral'], ['Ouest', 'ouest'],
        ['Nord-Ouest', 'nord-ouest'], ['Sud-Ouest', 'sud-ouest'], ['Extrême-Nord', 'extreme-nord'],
        ['Nord', 'nord'], ['Est', 'est'], ['Sud', 'sud'], ['Adamaoua', 'adamaoua'],
    ])->map(fn ($r) => [$r[0], '(' . ($regionTally[$r[1]] ?? 0) . ')', $r[1]])->all();

    $regions = ['Centre','Littoral','Ouest','Nord-Ouest','Sud-Ouest','Nord','Adamaoua','Est','Sud','Extrême-Nord'];

    // Header options
    $dirIconVariant = 'vdetail';
    $dirMsgCount = 2;
    $dirMsgBadgeColor = '#C1272D';
    $dirNavActive = 'events';
    $dirSearchPlaceholder = $isFr ? 'Rechercher un artisan, une entreprise, un produit, un événement...' : 'Search an artisan, a business, a product, an event...';

    // Footer options (events design variant)
    $dfShowHelp = true;
    $dfSocialStyle = 'outline';
    $dfShowLegalLinks = false;
    $dfShowPayments = true;
    $dfBgColor = '#021A0D';
    $dfBrandParagraph = $isFr
        ? 'Plateforme officielle de promotion et de valorisation de l\'artisanat camerounais à travers le digital.'
        : 'Official platform for promoting Cameroonian craftsmanship through digital.';
    $dfNewsletterText = $isFr ? 'Recevez nos nouveautés et offres exclusives.' : 'Receive our new arrivals and exclusive offers.';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr ? 'Événements — Participez aux événements qui valorisent l\'artisanat camerounais.' : 'Events — Take part in the events that celebrate Cameroonian craftsmanship.' }}">
    <title>{{ $isFr ? 'Événements — Galerie Virtuelle Nationale de l\'Artisanat du Cameroun' : 'Events — National Virtual Gallery of Cameroonian Crafts' }}</title>

    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        leaf:   '#164C28',
                        deepfc: '#02301B',
                        gold:   '#E5A82E',
                        goldbt: '#F0B93E',
                    },
                    fontFamily: {
                        sans:  ['Poppins', 'system-ui', 'sans-serif'],
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                    },
                }
            }
        }
    </script>

    <script src="{{ asset('vendor/lucide.min.js') }}"></script>

    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .barcode {
            background-image: repeating-linear-gradient(90deg,
                rgba(255,255,255,0.95) 0 2px, transparent 2px 4px,
                rgba(255,255,255,0.95) 4px 5px, transparent 5px 9px,
                rgba(255,255,255,0.95) 9px 12px, transparent 12px 14px);
        }
    </style>
</head>
<body class="bg-[#FEFEFE] text-[#1D1B16] antialiased">

@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-4 pb-12">

    <nav class="flex items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] hover:text-leaf transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16]">{{ $isFr ? 'Événements' : 'Events' }}</span>
    </nav>

    <div class="mt-4 flex flex-col lg:flex-row gap-7">

        <!-- Sidebar -->
        <aside class="hidden lg:block w-full lg:w-[300px] shrink-0 space-y-4">
            <!-- Filter panel -->
            <div class="bg-white border border-[#ECECEA] rounded-xl overflow-hidden">
                <div class="flex items-center gap-2.5 bg-[#04291A] px-4 h-[40px]">
                    <i data-lucide="filter" class="w-4 h-4 text-white"></i>
                    <span class="text-[11.5px] font-bold tracking-[0.1em] text-white uppercase">{{ $isFr ? 'Filtrer les événements' : 'Filter events' }}</span>
                </div>
                <form method="GET" action="{{ route('events.index') }}" class="p-4">
                    <input type="hidden" name="lang" value="{{ $lang }}">

                    <p class="text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Recherche' : 'Search' }}</p>
                    <div class="relative mt-2">
                        <input name="q" type="search" value="{{ request('q') }}" placeholder="{{ $isFr ? 'Nom de l\'événement, lieu...' : 'Event name, venue...' }}"
                            class="w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md pl-3 pr-8 text-[12px] text-[#3A3A35] placeholder-[#8A857A] focus:outline-none focus:border-gold">
                        <i data-lucide="search" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-[#6F6B60]"></i>
                    </div>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Catégorie' : 'Category' }}</p>
                    <select name="categorie" class="mt-2 w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les catégories' : 'All categories' }}</option>
                        @foreach(array_slice($eventTypes, 1) as [$etKey, $etLabel])
                        <option value="{{ $etKey }}">{{ $etLabel }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Type d\'événement' : 'Event type' }}</p>
                    <select name="type" class="mt-2 w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Tous les types' : 'All types' }}</option>
                        @foreach(array_slice($eventTypes, 1) as [$etKey, $etLabel])
                        <option value="{{ $etKey }}" {{ $activeType === $etKey ? 'selected' : '' }}>{{ $etLabel }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Région' : 'Region' }}</p>
                    <select name="region" class="mt-2 w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les régions' : 'All regions' }}</option>
                        @foreach($regions as $regionName)
                        @php $regionSlug = \Illuminate\Support\Str::slug($regionName); @endphp
                        <option value="{{ $regionSlug }}" {{ $activeRegion === $regionSlug ? 'selected' : '' }}>{{ $regionName }}</option>
                        @endforeach
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Département / Ville' : 'Department / City' }}</p>
                    <select name="ville" class="mt-2 w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md px-3 text-[12px] text-[#3A3A35] focus:outline-none focus:border-gold cursor-pointer">
                        <option value="">{{ $isFr ? 'Toutes les villes' : 'All cities' }}</option>
                    </select>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">Date</p>
                    <div class="relative mt-2">
                        <input name="debut" type="text" onfocus="this.type='date'" placeholder="{{ $isFr ? 'Date de début' : 'Start date' }}"
                            class="w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md pl-3 pr-8 text-[12px] text-[#3A3A35] placeholder-[#8A857A] focus:outline-none focus:border-gold">
                        <i data-lucide="calendar" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-[#6F6B60] pointer-events-none"></i>
                    </div>
                    <div class="relative mt-2.5">
                        <input name="fin" type="text" onfocus="this.type='date'" placeholder="{{ $isFr ? 'Date de fin' : 'End date' }}"
                            class="w-full h-[38px] bg-white border border-[#E3E3E1] rounded-md pl-3 pr-8 text-[12px] text-[#3A3A35] placeholder-[#8A857A] focus:outline-none focus:border-gold">
                        <i data-lucide="calendar" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-[14px] h-[14px] text-[#6F6B60] pointer-events-none"></i>
                    </div>

                    <p class="mt-4 text-[12px] font-semibold text-[#1D1B16]">{{ $isFr ? 'Événements en ligne' : 'Online events' }}</p>
                    <label class="mt-2 flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="en_ligne" value="1" class="w-4 h-4 rounded border-[#CFC9BF] text-leaf focus:ring-gold/40">
                        <span class="text-[12px] text-[#3A3A35]">{{ $isFr ? 'Inclure les événements en ligne' : 'Include online events' }}</span>
                    </label>

                    <button type="submit" class="mt-5 w-full h-[40px] bg-[#02331C] hover:bg-leaf text-white text-[12.5px] font-semibold rounded-lg flex items-center justify-center gap-2 transition-colors">
                        <i data-lucide="filter" class="w-[14px] h-[14px]"></i>
                        {{ $isFr ? 'Appliquer les filtres' : 'Apply filters' }}
                    </button>
                </form>
            </div>

            <!-- Browse by category -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-4">
                <h2 class="text-[11.5px] font-bold tracking-[0.1em] text-[#1D1B16] uppercase">{{ $isFr ? 'Parcourir par catégorie' : 'Browse by category' }}</h2>
                <ul class="mt-3 space-y-1">
                    @foreach($browseCats as [$bcIcon, $bcLabel, $bcCount, $bcKey])
                    <li>
                        <a href="{{ route('events.index', ['lang' => $lang, 'type' => $bcKey]) }}" class="flex items-center gap-2.5 px-1.5 py-[7px] rounded-md hover:bg-[#FAFAF8]">
                            <span class="w-6 h-6 rounded border border-[#E7E3DA] flex items-center justify-center shrink-0">
                                <i data-lucide="{{ $bcIcon }}" class="w-3 h-3 text-[#3A3A35]"></i>
                            </span>
                            <span class="flex-1 text-[12px] text-[#3A3A35]">{{ $bcLabel }}</span>
                            <span class="text-[11px] text-[#8A857A]">{{ $bcCount }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Browse by region -->
            <div class="bg-white border border-[#ECECEA] rounded-xl p-4">
                <h2 class="text-[11.5px] font-bold tracking-[0.1em] text-[#1D1B16] uppercase">{{ $isFr ? 'Explorer par région' : 'Explore by region' }}</h2>
                <ul class="mt-3 space-y-1">
                    @foreach($browseRegions as [$brLabel, $brCount, $brKey])
                    <li>
                        <a href="{{ route('events.index', ['lang' => $lang, 'region' => $brKey]) }}" class="flex items-center justify-between px-1.5 py-[6px] rounded-md hover:bg-[#FAFAF8]">
                            <span class="text-[12px] text-[#3A3A35]">{{ $brLabel }}</span>
                            <span class="text-[11px] text-[#8A857A]">{{ $brCount }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="mt-2 flex items-center gap-2 px-1.5 text-[12px] font-semibold text-[#14532D] hover:underline">
                    {{ $isFr ? 'Voir toutes les régions' : 'See all regions' }}
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
                <img src="{{ asset('images/landing/event-map.png') }}" alt="" class="mt-3 w-[170px] mx-auto" aria-hidden="true">
            </div>
        </aside>

        <!-- Main -->
        <section class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-serif text-[30px] sm:text-[34px] leading-tight text-[#12432A] font-semibold">{{ $isFr ? 'Événements' : 'Events' }}</h1>
                    <p class="mt-1.5 text-[13.5px] font-semibold text-[#C9862B]">{{ $isFr ? 'Participez aux événements qui valorisent l\'artisanat camerounais' : 'Take part in the events that celebrate Cameroonian craftsmanship' }}</p>
                </div>
                <a href="{{ ($siacUser['is_admin'] ?? false) ? route('admin.events') : route('contact', ['lang' => $lang]) }}"
                    class="inline-flex items-center gap-2 bg-[#02301B] hover:bg-leaf text-white text-[12.5px] font-semibold px-4 h-[42px] rounded-lg transition-colors">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    {{ $isFr ? 'Soumettre un événement' : 'Submit an event' }}
                </a>
            </div>

            <!-- Type pills -->
            <div class="mt-5 flex items-center gap-2 overflow-x-auto no-scrollbar">
                @foreach($eventTypes as [$etKey, $etLabel])
                @php $pillActive = $etKey === $activeType; @endphp
                <a href="{{ route('events.index', array_filter(['lang' => $lang, 'type' => $etKey])) }}"
                    class="shrink-0 px-3.5 h-[34px] rounded-lg border text-[12px] font-medium flex items-center transition-colors {{ $pillActive ? 'bg-[#02301B] border-[#02301B] text-white' : 'bg-white border-[#E3E3E1] text-[#3A3A35] hover:border-leaf hover:text-leaf' }}">
                    {{ $etLabel }}
                </a>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                <p class="text-[13px] font-semibold text-[#1D1B16]">{{ $isFr ? '128 événements trouvés' : '128 events found' }}</p>
                <form method="GET" action="{{ route('events.index') }}" class="flex items-center gap-2 h-[38px] bg-white border border-[#E3E3E1] rounded-lg px-3.5">
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    @if($activeType !== '')<input type="hidden" name="type" value="{{ $activeType }}">@endif
                    <label for="sort-select" class="text-[12px] text-[#55524A] whitespace-nowrap">{{ $isFr ? 'Trier par :' : 'Sort by:' }}</label>
                    <select id="sort-select" name="sort" onchange="this.form.submit()"
                        class="text-[12px] font-medium text-[#1D1B16] bg-transparent focus:outline-none cursor-pointer">
                        <option value="recents" {{ request('sort', 'recents') === 'recents' ? 'selected' : '' }}>{{ $isFr ? 'Plus récents' : 'Most recent' }}</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ $isFr ? 'Nom (A–Z)' : 'Name (A–Z)' }}</option>
                    </select>
                </form>
            </div>

            <!-- Event tickets -->
            <div class="mt-4 space-y-5">
                @foreach($shownEvents as $event)
                @php $stub = $stubColors[$event['color']]; $onGold = $event['color'] === 'gold'; @endphp
                <article class="relative flex flex-col md:flex-row rounded-xl overflow-hidden shadow-[0_1px_4px_rgba(0,0,0,0.08)] border border-[#EFEDEA]">
                    <!-- Left stub -->
                    <div class="relative md:w-[128px] shrink-0 flex md:flex-col items-center justify-center gap-2 md:gap-0 py-4 md:py-6 px-4 text-center" style="background-color: {{ $stub }}">
                        <p class="text-[30px] font-bold leading-none {{ $onGold ? 'text-[#1D1B16]' : 'text-white' }}">{{ $event['day'] }}</p>
                        <p class="mt-0 md:mt-1 text-[13px] font-bold tracking-[0.08em] {{ $onGold ? 'text-[#1D1B16]' : 'text-white' }}">{{ $event['month'] }}</p>
                        <p class="text-[12.5px] font-bold {{ $onGold ? 'text-[#5A3E06]' : 'text-[#E5A82E]' }}">{{ $event['year'] }}</p>
                        <p class="mt-0 md:mt-3 text-[12.5px] font-semibold {{ $onGold ? 'text-[#1D1B16]' : 'text-white' }}">{{ $event['from'] }}</p>
                        <p class="text-[11px] {{ $onGold ? 'text-[#5A4A18]' : 'text-white/80' }}">à {{ $event['to'] }}</p>
                        <span class="hidden md:block mt-3 w-[64px] h-[18px] barcode" aria-hidden="true"></span>
                        <span class="hidden md:block absolute -right-[9px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] bg-[#FEFEFE] rounded-full border border-[#EFEDEA]" aria-hidden="true"></span>
                    </div>
                    <!-- Body -->
                    <div class="flex-1 bg-white px-5 py-4 flex flex-col sm:flex-row gap-5 items-start">
                        <img src="{{ asset('images/landing/' . $event['icon']) }}" alt="" class="hidden sm:block w-[104px] h-auto object-contain shrink-0 self-center" aria-hidden="true">
                        <div class="min-w-0">
                            <span class="inline-block text-[9.5px] font-bold tracking-[0.08em] uppercase text-white rounded-md px-2.5 py-1" style="background-color: {{ $badgeColors[$event['badgeColor']] }}">{{ $event['badge'] }}</span>
                            <h3 class="mt-2 flex items-start gap-2 text-[17px] font-bold text-[#1D1B16] leading-snug">
                                <a href="{{ route('events.show', ['slug' => $event['slug']]) }}" class="hover:text-leaf transition-colors">{{ $event['title'] }}</a>
                                <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0 mt-1"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </h3>
                            <p class="mt-2 flex items-center gap-2 text-[12.5px] font-semibold text-[#1D1B16]">
                                <i data-lucide="map-pin" class="w-[13px] h-[13px] text-[#C9862B]"></i>
                                {{ $event['city'] }}
                            </p>
                            <p class="mt-1 flex items-center gap-2 text-[12px] text-[#6F6B60]">
                                <i data-lucide="landmark" class="w-[13px] h-[13px] text-[#C9862B]"></i>
                                {{ $event['venue'] }}
                            </p>
                            <p class="mt-2 text-[12px] text-[#55524A] leading-relaxed">{{ $event['desc'] }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5">
                                @foreach($event['tags'] as [$tagIcon, $tagLabel])
                                <span class="flex items-center gap-1.5 text-[11px] text-[#55524A]">
                                    <i data-lucide="{{ $tagIcon }}" class="w-[12px] h-[12px]"></i>
                                    {{ $tagLabel }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- Right stub -->
                    <div class="relative md:w-[132px] shrink-0 flex md:flex-col items-center justify-center gap-3 py-4 px-4 text-center" style="background-color: {{ $stub }}">
                        <span class="hidden md:block absolute -left-[9px] top-1/2 -translate-y-1/2 w-[18px] h-[18px] bg-white rounded-full border border-[#EFEDEA]" aria-hidden="true"></span>
                        <svg viewBox="0 0 24 24" class="w-7 h-7 {{ $onGold ? 'fill-[#0E3D26]' : 'fill-[#E5A82E]' }}"><path d="M12 2.5 14.9 9l7.1.4-5.5 4.6 1.8 6.9L12 17l-6.3 3.9 1.8-6.9L2 9.4 9.1 9z"/></svg>
                        <p class="text-[12px] font-bold uppercase tracking-[0.04em] {{ $onGold ? 'text-[#1D1B16]' : 'text-white' }}">{{ $event['price'] }}</p>
                        <a href="{{ route('events.show', ['slug' => $event['slug']]) }}"
                            class="inline-flex items-center justify-center px-4 h-[32px] rounded-md text-[11.5px] font-bold transition-colors {{ $onGold ? 'bg-white text-[#1D1B16] hover:bg-[#F6F1E7]' : 'bg-[#E9A825] text-[#3A2E08] hover:bg-goldbt' }}">
                            {{ $isFr ? 'Voir détails' : 'View details' }}
                        </a>
                        <a href="{{ $siacUser ? route('saved.index') : '/login?lang=' . $lang }}" aria-label="{{ $isFr ? 'Sauvegarder' : 'Bookmark' }}" class="{{ $onGold ? 'text-[#1D1B16]' : 'text-white' }} hover:opacity-80">
                            <i data-lucide="bookmark" class="w-[18px] h-[18px]"></i>
                        </a>
                    </div>
                </article>
                @endforeach
            </div>

            <div class="mt-7 flex justify-center">
                <a href="{{ route('events.index', ['lang' => $lang]) }}"
                    class="inline-flex items-center gap-2.5 bg-white border border-[#E3E3E1] hover:border-leaf hover:text-leaf rounded-lg px-5 h-[40px] text-[12.5px] font-semibold text-[#3A3A35] transition-colors">
                    {{ $isFr ? 'Charger plus d\'événements' : 'Load more events' }}
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </a>
            </div>
        </section>
    </div>
</div>
</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
