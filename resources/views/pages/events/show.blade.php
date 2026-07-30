@php
    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    $name = $isFr ? $event->name_fr : ($event->name_en ?? $event->name_fr);
    $descriptionText = $isFr ? $event->description_fr : ($event->description_en ?? $event->description_fr);
    $location = $isFr ? $event->location_fr : ($event->location_en ?? $event->location_fr);

    $frMonths = [1 => 'JANV', 'FÉVR', 'MARS', 'AVR', 'MAI', 'JUIN', 'JUIL', 'AOÛT', 'SEPT', 'OCT', 'NOV', 'DÉC'];
    $enMonths = [1 => 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
    $frMonthsFull = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    $day = $event->starts_at->format('d');
    $monthAbbr = ($isFr ? $frMonths : $enMonths)[(int) $event->starts_at->format('n')];
    $year = $event->starts_at->format('Y');
    $timeFrom = $event->starts_at->format('H:i');
    $timeTo = $event->ends_at?->format('H:i');
    $dateLong = $isFr
        ? $day . ' ' . ucfirst(mb_strtolower($frMonthsFull[(int) $event->starts_at->format('n')])) . ' ' . $year
        : $event->starts_at->format('d F Y');

    // Badge shown per real event_type column — same badge for every event of that type
    $typeBadges = [
        'salons'      => [$isFr ? 'Salon' : 'Trade fair', '#0E5A2F'],
        'festivals'   => [$isFr ? 'Festival culturel' : 'Cultural festival', '#E9A825'],
        'ateliers'    => [$isFr ? 'Atelier & Formation' : 'Workshop & Training', '#0E5A2F'],
        'marches'     => [$isFr ? 'Marché & Foire' : 'Market & Fair', '#E9A825'],
        'conferences' => [$isFr ? 'Conférence' : 'Conference', '#C1272D'],
        'concours'    => [$isFr ? 'Concours & Prix' : 'Competition & Award', '#E9A825'],
    ];
    [$badge, $badgeColor] = $typeBadges[$event->event_type] ?? [$isFr ? 'Événement' : 'Event', '#0E5A2F'];

    $locParts = array_map('trim', explode(',', (string) ($event->city_fr ?? $location)));
    $meta = [
        'city'  => $event->city_fr ?: (count($locParts) > 1 ? end($locParts) : ($location ?: '—')),
        'venue' => $locParts[0] ?? ($location ?: '—'),
        'badge' => $badge, 'badgeColor' => $badgeColor,
        // Unpriced means unstated, not free — the booking panel drops the line.
        'price' => ($isFr ? $event->price_fr : ($event->price_en ?? $event->price_fr)) ?: null,
    ];

    $exhibitorRegionsCount = $event->exhibitingBusinesses->pluck('region_id')->filter()->unique()->count();
    $eventStats = array_values(array_filter([
        ['users',      number_format($attendeeCount), $isFr ? 'Participants inscrits' : 'Registered participants'],
        $event->exhibitingBusinesses->count() > 0
            ? ['briefcase', number_format($event->exhibitingBusinesses->count()), $isFr ? 'Exposants' : 'Exhibitors']
            : null,
        $exhibitorRegionsCount > 0
            ? ['settings-2', number_format($exhibitorRegionsCount), $isFr ? 'Régions représentées' : 'Regions represented']
            : null,
    ]));

    $tabs = [
        ['apropos',     $isFr ? 'À propos' : 'About',           'info'],
        ['exposants',   ($isFr ? 'Exposants' : 'Exhibitors') . ' (' . $event->exhibitingBusinesses->count() . ')', 'store'],
        ['programme',   'Programme',                            'calendar-days'],
        ['faq',         'FAQ',                                  'help-circle'],
    ];

    $objectifs = $isFr ? [
        'Valoriser l\'excellence et la diversité de l\'artisanat camerounais',
        'Favoriser les échanges et partenariats entre acteurs',
        'Renforcer les capacités des artisans',
        'Promouvoir l\'innovation et le design local',
        'Stimuler la commercialisation des produits artisanaux',
    ] : [
        'Celebrate the excellence and diversity of Cameroonian craftsmanship',
        'Foster exchanges and partnerships between stakeholders',
        'Strengthen artisans\' capacities',
        'Promote local innovation and design',
        'Boost the commercialisation of artisanal products',
    ];

    $highlights = [
        ['edetail-pf-1.png', 'Expositions',
         $isFr ? "Découvrez le meilleur de l'artisanat sur des stands régionaux et thématiques." : "Discover the best of craftsmanship on regional and themed stands."],
        ['edetail-pf-2.png', $isFr ? 'Ateliers pratiques' : 'Hands-on workshops',
         $isFr ? "Participez à des ateliers et formations animés par des experts." : "Take part in workshops and training led by experts."],
        ['edetail-pf-3.png', $isFr ? 'Conférences' : 'Conferences',
         $isFr ? "Des leaders et experts partagent leur vision et leur expérience." : "Leaders and experts share their vision and experience."],
        ['edetail-pf-4.png', 'Networking',
         $isFr ? "Rencontrez des partenaires, investisseurs et acheteurs potentiels." : "Meet potential partners, investors and buyers."],
        ['edetail-pf-5.png', $isFr ? 'Concours & Prix' : 'Competitions & Awards',
         $isFr ? "Des récompenses pour les meilleurs artisans et innovations." : "Awards for the best artisans and innovations."],
    ];

    $participatingRegions = [
        ['edetail-region-1.png', 'Centre'], ['edetail-region-2.png', 'Littoral'], ['edetail-region-3.png', 'Ouest'],
        ['edetail-region-4.png', 'Nord-Ouest'], ['edetail-region-5.png', 'Sud-Ouest'], ['edetail-region-6.png', 'Extrême-Nord'],
    ];

    $partners = [
        ['edetail-partner-2.png', 'ONUDI', $isFr ? 'Organisation des Nations Unies pour le Développement Industriel' : 'United Nations Industrial Development Organization'],
        ['edetail-partner-3.png', 'BANGE', $isFr ? 'Banque Nationale pour le Développement Économique' : 'National Bank for Economic Development'],
        ['edetail-partner-4.png', 'CAMPOST', $isFr ? 'Société Nationale des Postes du Cameroun' : 'National Postal Company of Cameroon'],
        ['edetail-partner-5.png', 'AFC', 'African Finance Corporation'],
    ];

    $downloads = [
        $isFr ? 'Programme officiel (PDF)' : 'Official programme (PDF)',
        $isFr ? 'Dossier de partenariat (PDF)' : 'Partnership pack (PDF)',
        $isFr ? 'Règlement des concours (PDF)' : 'Competition rules (PDF)',
    ];

    $shareUrl = urlencode(url()->current());
    $shareTitle = urlencode($name);
    $gcalDates = $event->starts_at->format('Ymd\THis') . '/' . ($event->ends_at ?? $event->starts_at->copy()->addHours(8))->format('Ymd\THis');

    // Header options
    $dirTopBar = true;
    $dirIconVariant = 'vdetail';
    $dirMsgCount = 3;
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
        ? 'Plateforme privée de promotion et de valorisation de l\'artisanat camerounais à travers le digital.'
        : 'Private platform for promoting Cameroonian craftsmanship through digital.';
    $dfNewsletterText = $isFr ? 'Recevez nos nouveautés et offres exclusives.' : 'Receive our new arrivals and exclusive offers.';
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags((string) $descriptionText), 150) }}">
    <title>{{ $name }} — {{ $isFr ? 'Artisan Hub 237' : 'Artisan Hub 237' }}</title>

    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 229 168 46;
            --c-goldbt: 240 185 62;
            --c-leaf: 22 76 40;
            --f-serif: "Playfair Display", Georgia, serif;
        }
    </style>

    @include('pages.partials.icons')


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
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
@include('pages.partials.seo-head')
</head>
<body class="bg-[#FEFEFE] dark:bg-[#12150F] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<main class="pb-16 sm:pb-0">
<div class="max-w-[1472px] mx-auto px-4 sm:px-6 pt-4 pb-12">

    <nav class="flex flex-wrap items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('events.index') }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $isFr ? 'Événements' : 'Events' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16] dark:text-[#F3EFE7]">{{ $name }}</span>
    </nav>

    <a href="{{ route('events.index') }}" class="mt-3 inline-flex items-center gap-2 text-[12.5px] font-semibold text-[#14532D] dark:text-[#339B56] hover:underline">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ $isFr ? 'Retour aux événements' : 'Back to events' }}
    </a>

    <div class="mt-4 grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_310px] gap-7">

        <!-- Main column -->
        <section class="min-w-0">
            <!-- Hero ticket -->
            <div class="flex flex-col md:flex-row rounded-xl overflow-hidden shadow-[0_2px_8px_rgba(0,0,0,0.10)] border border-[#EFEDEA] dark:border-[#262B21]">
                <!-- Stub -->
                <div class="relative md:w-[168px] shrink-0 bg-[#06301A] flex md:flex-col items-center justify-center gap-3 md:gap-0 py-5 px-5 text-center">
                    <p class="text-[40px] font-bold leading-none text-white">{{ $day }}</p>
                    <p class="md:mt-1.5 text-[15px] font-bold tracking-[0.1em] text-white">{{ $monthAbbr }}</p>
                    <p class="text-[14px] font-bold text-[#E5A82E]">{{ $year }}</p>
                    <p class="md:mt-4 text-[14px] font-semibold text-white">{{ $timeFrom }}</p>
                    @if($timeTo)<p class="text-[12px] text-white/80">à {{ $timeTo }}</p>@endif
                    <p class="hidden md:flex items-start gap-1.5 mt-4 text-[13px] md:text-[11px] text-white/90 leading-snug">
                        <i data-lucide="map-pin" class="w-3 h-3 mt-0.5 shrink-0"></i>
                        <span>{{ $meta['city'] }}<br>{{ $meta['venue'] }}</span>
                    </p>
                    <span class="hidden md:block mt-4 w-[76px] h-[20px] barcode" aria-hidden="true"></span>
                    <span class="hidden md:block absolute -right-[10px] top-1/2 -translate-y-1/2 w-[20px] h-[20px] bg-[#FEFEFE] dark:bg-[#12150F] rounded-full border border-[#EFEDEA] dark:border-[#262B21]" aria-hidden="true"></span>
                </div>
                <!-- Panel -->
                <div class="relative flex-1 bg-[#FAF5EC] dark:bg-[#0A0C09] overflow-hidden">
                    <img src="{{ asset('images/landing/edetail-art.png') }}" alt="" class="absolute right-0 inset-y-0 h-full w-auto object-cover hidden sm:block pointer-events-none select-none" aria-hidden="true">
                    <div class="relative p-6 max-w-[480px]">
                        <span class="inline-block text-[12px] md:text-[10px] font-bold tracking-[0.08em] uppercase text-white rounded-md px-3 py-1.5" style="background-color: {{ $meta['badgeColor'] }}">{{ $meta['badge'] }}</span>
                        <h1 class="mt-3.5 font-serif text-[27px] sm:text-[31px] leading-tight text-[#12432A] dark:text-[#339B56] font-semibold">{{ $name }}</h1>
                        @if($descriptionText)
                        <p class="mt-3.5 text-[12.5px] text-[#55524A] dark:text-[#B4B5A6] leading-relaxed">{{ $descriptionText }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats strip -->
            <div class="mt-4 bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl px-5 py-4">
                {{-- 2-up on phones. The divider only appears once the columns are
                     wide enough to have something to divide, and the label floor is
                     12px — "Participants inscrits" at 10px was unreadable. --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:divide-x sm:divide-[#EFEDEA] sm:dark:divide-[#262B21]">
                    @foreach($eventStats as $esIdx => [$esIcon, $esValue, $esLabel])
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 {{ $esIdx > 0 ? 'sm:pl-4' : '' }}">
                        <i data-lucide="{{ $esIcon }}" class="w-[22px] h-[22px] text-[#3A3A35] dark:text-[#F3EFE7] shrink-0" stroke-width="1.6"></i>
                        <div class="leading-tight min-w-0">
                            <p class="text-[15px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $esValue }}</p>
                            <p class="mt-0.5 text-[12px] text-[#6F6B60] dark:text-[#868778] leading-snug">{{ $esLabel }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-4 bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl overflow-hidden">
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar border-b border-[#EFEDEA] dark:border-[#262B21] px-2">
                    @foreach($tabs as $ti => [$tabKey, $tabLabel, $tabIcon])
                    <button type="button" data-tab="{{ $tabKey }}"
                        class="tab-btn relative shrink-0 flex items-center gap-2 px-3.5 py-3.5 text-[12.5px] {{ $ti === 0 ? 'font-semibold text-[#14532D] dark:text-[#339B56]' : 'font-medium text-[#55524A] dark:text-[#B4B5A6] hover:text-[#1D1B16] hover:dark:text-[#F3EFE7]' }} transition-colors">
                        <i data-lucide="{{ $tabIcon }}" class="w-[13px] h-[13px]"></i>
                        {{ $tabLabel }}
                        <span class="tab-bar absolute left-2 right-2 bottom-0 h-[3px] bg-[#14532D] {{ $ti === 0 ? '' : 'hidden' }}"></span>
                    </button>
                    @endforeach
                </div>
                <div class="p-6">
                    <div class="tab-panel" data-panel="apropos">
                        <div class="grid grid-cols-1 md:grid-cols-[1fr_240px] gap-6">
                            <div>
                                <h2 class="text-[15.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'À propos de l\'événement' : 'About the event' }}</h2>
                                <p class="mt-3 text-[12.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
                                    {{ $descriptionText ?: ($isFr ? 'Aucune description disponible pour cet événement.' : 'No description available for this event.') }}
                                </p>
                                <h3 class="mt-5 text-[13.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Objectifs' : 'Objectives' }}</h3>
                                <ul class="mt-3 space-y-2.5">
                                    @foreach($objectifs as $objectif)
                                    <li class="flex items-start gap-2.5 text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">
                                        <svg viewBox="0 0 16 16" class="w-4 h-4 shrink-0 mt-px"><circle cx="8" cy="8" r="7.2" fill="none" stroke="#17A34A" stroke-width="1.3"/><path d="M4.9 8.2 7 10.2l4-4.3" fill="none" stroke="#17A34A" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        {{ $objectif }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <a href="{{ config('legal.social.youtube') ?: route('events.index', ['lang' => $lang]) }}" target="_blank" rel="noopener" class="block relative rounded-xl overflow-hidden group self-start" aria-label="{{ $isFr ? 'Découvrez l\'édition 2024 en vidéo' : 'Discover the 2024 edition in video' }}">
                                <img src="{{ asset('images/landing/edetail-video.png') }}" alt="" class="w-full h-auto group-hover:opacity-95 transition-opacity">
                            </a>
                        </div>
                    </div>
                    <div class="tab-panel hidden" data-panel="exposants">
                        @if($event->exhibitingBusinesses->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($event->exhibitingBusinesses as $exBiz)
                            <a href="{{ route('businesses.show', ['slug' => $exBiz->slug, 'lang' => $lang]) }}" class="flex items-center gap-3 border border-[#EDE6D6] dark:border-[#262B21] rounded-lg p-3 hover:border-[#14652F] transition-colors">
                                <span class="w-9 h-9 rounded-lg bg-[#F6F1E4] dark:bg-[#0A0C09] flex items-center justify-center shrink-0"><i data-lucide="store" class="w-4 h-4 text-[#C9942E]"></i></span>
                                <span>
                                    <span class="block text-[12.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? $exBiz->name_fr : ($exBiz->name_en ?? $exBiz->name_fr) }}</span>
                                    @if($exBiz->industry)<span class="block text-[13px] md:text-[11px] text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? $exBiz->industry->name_fr : ($exBiz->industry->name_en ?? $exBiz->industry->name_fr) }}</span>@endif
                                </span>
                            </a>
                            @endforeach
                        </div>
                        @else
                        <p class="text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'La liste des exposants sera annoncée prochainement.' : 'The list of exhibitors will be announced soon.' }}
                            <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="font-semibold text-[#14532D] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Découvrir les artisans & entreprises' : 'Discover the artisans & businesses' }}</a></p>
                        @endif
                    </div>
                    <div class="tab-panel hidden" data-panel="programme">
                        <p class="text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Le programme détaillé (ateliers, conférences, concours) sera publié prochainement. Inscrivez-vous pour être notifié.' : 'The detailed programme (workshops, conferences, competitions) will be published soon. Sign up to be notified.' }}</p>
                    </div>
                    <div class="tab-panel hidden" data-panel="faq">
                        <p class="text-[13px] text-[#55524A] dark:text-[#B4B5A6]">{{ $isFr ? 'Une question sur cet événement ? Contactez-nous via la page contact.' : 'A question about this event? Reach us via the contact page.' }}
                            <a href="{{ route('contact', ['lang' => $lang]) }}" class="font-semibold text-[#14532D] dark:text-[#339B56] hover:underline">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</a></p>
                    </div>
                </div>
            </div>

            <!-- Highlights -->
            <h2 class="mt-7 text-[16px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Points forts de l\'événement' : 'Event highlights' }}</h2>
            <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($highlights as [$hlImg, $hlTitle, $hlSub])
                <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-4 text-center">
                    <img src="{{ asset('images/landing/' . $hlImg) }}" alt="" class="h-[42px] w-auto mx-auto object-contain">
                    <p class="mt-3 text-[12px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $hlTitle }}</p>
                    <p class="mt-1.5 text-[12px] md:text-[10.5px] text-[#6F6B60] dark:text-[#868778] leading-relaxed">{{ $hlSub }}</p>
                </div>
                @endforeach
            </div>

            <!-- Participating regions -->
            <div class="mt-7 flex items-center justify-between">
                <h2 class="text-[16px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Régions participantes' : 'Participating regions' }}</h2>
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="flex items-center gap-2 text-[12px] font-semibold text-[#14532D] dark:text-[#339B56] hover:underline">
                    {{ $isFr ? 'Voir toutes les régions' : 'See all regions' }}
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
            <div class="mt-3 grid grid-cols-3 sm:grid-cols-6 gap-3">
                @foreach($participatingRegions as [$prImg, $prLabel])
                <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] hover:border-leaf rounded-xl p-3 text-center transition-colors">
                    <img src="{{ asset('images/landing/' . $prImg) }}" alt="" class="h-[46px] w-auto mx-auto object-contain opacity-70">
                    <p class="mt-2 text-[13px] md:text-[11px] font-semibold text-[#3A3A35] dark:text-[#F3EFE7]">{{ $prLabel }}</p>
                </a>
                @endforeach
            </div>

            <!-- Official partners -->
            <h2 class="mt-7 text-[16px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Partenaires officiels' : 'Official partners' }}</h2>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                @foreach($partners as [$paImg, $paName, $paSub])
                <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-3.5 flex items-center gap-3">
                    <img src="{{ asset('images/landing/' . $paImg) }}" alt="" class="w-[34px] h-[34px] object-contain shrink-0">
                    <div class="min-w-0 leading-tight">
                        <p class="text-[13px] md:text-[11.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $paName }}</p>
                        @if($paSub)<p class="mt-0.5 text-[12px] md:text-[9.5px] text-[#6F6B60] dark:text-[#868778] leading-snug">{{ $paSub }}</p>@endif
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-3 flex justify-end">
                <a href="{{ route('partners.index') }}" class="flex items-center gap-2 text-[12px] font-semibold text-[#14532D] dark:text-[#339B56] hover:underline">
                    {{ $isFr ? 'Voir tous les partenaires' : 'See all partners' }}
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </section>

        <!-- Right rail -->
        <aside class="space-y-4">
            <!-- Book your spot -->
            <div class="bg-[#06301A] rounded-xl p-5">
                <p class="flex items-center gap-2.5 text-[13px] font-semibold text-white">
                    <svg viewBox="0 0 24 24" class="w-5 h-5 fill-[#E5A82E]"><path d="M12 2.5 14.9 9l7.1.4-5.5 4.6 1.8 6.9L12 17l-6.3 3.9 1.8-6.9L2 9.4 9.1 9z"/></svg>
                    {{ $isFr ? 'Réservez votre place' : 'Book your spot' }}
                </p>
                @if($meta['price'])
                <p class="mt-3 text-[22px] font-bold text-white">{{ $meta['price'] }}</p>
                @endif
                <p class="mt-1 text-[13px] md:text-[11.5px] text-[#B9C4BC]">{{ $isFr ? 'Inscription obligatoire en ligne' : 'Online registration required' }}</p>
                <a href="{{ $siacUser ? route('events.ticket', ['slug' => $event->slug, 'lang' => $lang]) : '/inscription?lang=' . $lang }}"
                    class="mt-4 w-full h-[42px] bg-[#E9A825] hover:bg-goldbt text-[#3A2E08] rounded-lg flex items-center justify-center gap-2 text-[13px] font-bold transition-colors">
                    {{ $isFr ? 'S\'inscrire maintenant' : 'Sign up now' }}
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ $shareTitle }}&dates={{ $gcalDates }}&location={{ urlencode($meta['venue'] . ', ' . $meta['city']) }}"
                    target="_blank" rel="noopener"
                    class="mt-2.5 w-full h-[42px] border border-white/30 hover:bg-white/10 text-white rounded-lg flex items-center justify-center gap-2.5 text-[12.5px] font-semibold transition-colors">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    {{ $isFr ? 'Ajouter à mon agenda' : 'Add to my calendar' }}
                </a>
                <p class="mt-5 text-[13px] md:text-[11.5px] text-[#B9C4BC]">{{ $isFr ? 'Partager l\'événement' : 'Share the event' }}</p>
                <div class="mt-2.5 flex items-center gap-2.5">
                    <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="w-9 h-9 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A10 10 0 1 0 12 2zm5.8 14.1c-.2.7-1.2 1.3-2 1.4-.5.1-1.2.2-3.5-.7-2.9-1.2-4.8-4.1-4.9-4.3-.1-.2-1.2-1.6-1.2-3s.7-2.1 1-2.4c.2-.3.5-.4.7-.4h.5c.2 0 .4 0 .6.5s.8 1.9.8 2c.1.1.1.3 0 .5-.4.9-.9 1-.7 1.4.9 1.5 2 2.4 3.3 3 .3.1.5.1.7-.1l1-1.2c.2-.3.4-.2.7-.1s1.8.8 2.1 1c.3.1.5.2.6.4 0 .1 0 .7-.2 1z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Facebook" class="w-9 h-9 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path d="M13.5 2h-2.2C9.2 2 7.9 3.4 7.9 5.6v1.9H6v2.8h1.9V18h2.9v-7.7h2.3l.4-2.8h-2.7V5.9c0-.8.3-1.2 1.2-1.2h1.5V2z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener" aria-label="X" class="w-9 h-9 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path d="M11.6 8.7 17.4 2h-1.4l-5 5.8L7 2H2.5l6.1 8.8L2.5 18h1.4l5.3-6.2 4.3 6.2H18zM4.6 3h2.1l8.7 12.4h-2.1z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="LinkedIn" class="w-9 h-9 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                        <svg viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5"><path d="M4.98 3.5a1.75 1.75 0 1 1 0 3.5 1.75 1.75 0 0 1 0-3.5zM3.5 8.5h3v8h-3zM9 8.5h2.8v1.1h.1c.4-.7 1.4-1.4 2.8-1.4 3 0 3.5 1.9 3.5 4.3v4h-3v-3.5c0-.8 0-1.9-1.2-1.9s-1.4.9-1.4 1.9v3.5H9z" transform="scale(0.83) translate(2,1)"/></svg>
                    </a>
                    <a href="mailto:?subject={{ $shareTitle }}&body={{ $shareUrl }}" aria-label="Email" class="w-9 h-9 rounded-full border border-white/30 flex items-center justify-center text-white hover:bg-white/10 transition-colors">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>

            <!-- Practical info -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Informations pratiques' : 'Practical information' }}</h2>
                <ul class="mt-4 space-y-3.5 text-[12px]">
                    <li class="flex items-start gap-3">
                        <i data-lucide="calendar" class="w-4 h-4 text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">Dates</p>
                            <p class="mt-0.5 text-[#6F6B60] dark:text-[#868778]">{{ $dateLong }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="clock" class="w-4 h-4 text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Heure' : 'Time' }}</p>
                            <p class="mt-0.5 text-[#6F6B60] dark:text-[#868778]">{{ $timeTo ? $timeFrom . ' - ' . $timeTo : $timeFrom }} (GMT+1)</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Lieu' : 'Venue' }}</p>
                            <p class="mt-0.5 text-[#6F6B60] dark:text-[#868778]">{{ $meta['venue'] }}<br>{{ $meta['city'] }} - {{ $isFr ? 'Cameroun' : 'Cameroon' }}</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i data-lucide="car" class="w-4 h-4 text-[#55524A] dark:text-[#B4B5A6] mt-0.5 shrink-0"></i>
                        <div>
                            <p class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Accès' : 'Access' }}</p>
                            <p class="mt-0.5 text-[#6F6B60] dark:text-[#868778]">{{ $isFr ? 'Parking disponible' : 'Parking available' }}<br>{{ $isFr ? 'Transports en commun' : 'Public transport' }}</p>
                        </div>
                    </li>
                </ul>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($meta['venue'] . ', ' . $meta['city'] . ', Cameroun') }}" target="_blank" rel="noopener"
                    class="mt-4 w-full h-[36px] bg-[#F6F4EF] dark:bg-[#0A0C09] hover:bg-[#EFECE4] hover:dark:bg-[#0A0C09] rounded-lg flex items-center justify-center gap-2 text-[12px] font-semibold text-[#3A3A35] dark:text-[#F3EFE7] transition-colors">
                    <i data-lucide="map" class="w-3.5 h-3.5"></i>
                    {{ $isFr ? 'Voir sur la carte' : 'View on the map' }}
                </a>
            </div>

            <!-- Organizer -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Organisateur' : 'Organiser' }}</h2>
                <div class="mt-4 flex items-start gap-3">
                    {{-- The organiser here IS the platform, so it uses the brand
                         mark rather than the separate edetail-org-logo.png the
                         design shipped for the same thing. --}}
                    <img src="{{ brand_asset('mark') }}" alt="" class="w-[38px] h-auto object-contain shrink-0">
                    <div>
                        <p class="text-[12.5px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">Artisan Hub 237</p>
                        <p class="mt-1 text-[13px] md:text-[11px] text-[#6F6B60] dark:text-[#868778] leading-snug">{{ $isFr ? 'Plateforme privée dédiée à l\'artisanat camerounais' : 'Private platform dedicated to Cameroonian craftsmanship' }}</p>
                    </div>
                </div>
                <a href="{{ route('about', ['lang' => $lang]) }}"
                    class="mt-4 w-full h-[36px] border border-[#DBDFDC] dark:border-[#262B21] hover:border-leaf hover:text-leaf hover:dark:text-[#339B56] rounded-lg flex items-center justify-center gap-2 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] transition-colors">
                    {{ $isFr ? 'En savoir plus sur nous' : 'Learn more about us' }}
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
                <p class="mt-4 text-[13px] md:text-[11.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Nous contacter' : 'Contact us' }}</p>
                @if(config('legal.company.phone'))
                <p class="mt-2 flex items-center gap-2 text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]"><i data-lucide="phone" class="w-3 h-3"></i>{{ config('legal.company.phone') }}</p>
                @endif
                <p class="mt-1.5 flex items-center gap-2 text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778]"><i data-lucide="mail" class="w-3 h-3"></i>{{ config('legal.company.email') }}</p>
            </div>

            <!-- Downloads -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Télécharger' : 'Download' }}</h2>
                <ul class="mt-3.5 space-y-1">
                    @foreach($downloads as $download)
                    <li>
                        <a href="{{ route('contact', ['lang' => $lang]) }}" title="{{ $isFr ? 'Document disponible sur demande — contactez-nous' : 'Document available on request — contact us' }}" class="flex items-center gap-2.5 px-2 py-2 rounded-md hover:bg-[#FAFAF8] hover:dark:bg-[#12150F] text-[12px] text-[#3A3A35] dark:text-[#F3EFE7]">
                            <i data-lucide="file-text" class="w-[14px] h-[14px] text-[#55524A] dark:text-[#B4B5A6]"></i>
                            <span class="flex-1">{{ $download }}</span>
                            <i data-lucide="download" class="w-[14px] h-[14px] text-[#55524A] dark:text-[#B4B5A6]"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Stay informed -->
            <div class="bg-white dark:bg-[#12150F] border border-[#ECECEA] dark:border-[#262B21] rounded-xl p-5">
                <h2 class="text-[14px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $isFr ? 'Restez informé' : 'Stay informed' }}</h2>
                <p class="mt-2 text-[13px] md:text-[11.5px] text-[#6F6B60] dark:text-[#868778] leading-relaxed">{{ $isFr ? 'Recevez les dernières actualités sur cet événement.' : 'Receive the latest news about this event.' }}</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-3.5 flex gap-2">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $lang }}">
                    <input name="email" type="email" required placeholder="{{ $isFr ? 'Votre adresse email' : 'Your email address' }}"
                        class="ui-field ui-field--sm flex-1 min-w-0">
                    <button type="submit" class="h-[38px] bg-[#02301B] hover:bg-leaf text-white text-[12px] font-semibold px-3.5 rounded-md transition-colors shrink-0">
                        {{ $isFr ? 'S\'abonner' : 'Subscribe' }}
                    </button>
                </form>
            </div>
        </aside>
    </div>
</div>
</main>

@include('pages.partials.directory-footer')

<script>
    lucide.createIcons();
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                const active = b === btn;
                b.classList.toggle('font-semibold', active);
                b.classList.toggle('text-[#14532D] dark:text-[#339B56]', active);
                b.classList.toggle('font-medium', !active);
                b.classList.toggle('text-[#55524A] dark:text-[#B4B5A6]', !active);
                b.querySelector('.tab-bar').classList.toggle('hidden', !active);
            });
            document.querySelectorAll('.tab-panel').forEach(p => {
                p.classList.toggle('hidden', p.dataset.panel !== btn.dataset.tab);
            });
        });
    });
</script>
</body>
</html>
