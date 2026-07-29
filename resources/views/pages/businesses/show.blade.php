@php
    /*
    |---------------------------------------------------------------------------
    | Public artisan profile — desktop
    |---------------------------------------------------------------------------
    |
    | A replica of "artisan profile v2 desktop.png", with the design's
    | unsupportable claims removed rather than reworded.
    |
    | The design was drawn against an imagined marketplace. It promises secure
    | payments, a money-back guarantee and worldwide shipping; it shows a 4.9
    | average over 128 reviews; it counts products sold, happy customers and
    | countries reached; and it awards a UNESCO recognition and a ministry
    | prize. config/legal.php states in as many words that this operator
    | processes no sale payments, is not party to the sale, holds no funds and
    | ships nothing. There are no orders in this database, business_reviews is
    | empty and business_awards is empty. Each of those is handled where it
    | appears below, with the reason given at the point of removal, so the next
    | person comparing this page against the PNG can see the gap was deliberate.
    |
    | The rule applied throughout: a value comes from the register or the row
    | does not render, and a figure the platform does not measure is shown as
    | unmeasured rather than as zero. Those two look identical on a screenshot
    | and are entirely different claims — "0 products sold" is a statement about
    | this artisan's business, and it is one we are in no position to make.
    |
    | The phone design is a different document — an application shell with its
    | own top bar and bottom navigation — and lives in
    | pages/businesses/partials/show-mobile.blade.php. Below `lg` this page hands
    | over to it wholesale rather than trying to reflow into it.
    |
    */

    use App\Support\ArtisanProfile;

    $isFr = $lang === 'fr';
    $siacUser = session('siac_user');

    /*
     | Every figure on this page comes from App\Support\ArtisanProfile, which is
     | the single place allowed to decide whether something is known. That class
     | is being written in parallel with this view, so each call is guarded: if
     | it is absent or mid-edit, the page degrades to "nothing is known" instead
     | of throwing a 500 at a visitor. The fallbacks invent nothing — they are
     | the same empty shapes the class returns for a shop with no data, so the
     | degraded page is merely emptier, never wronger.
     */
    $unknownStat = ['value' => null, 'known' => false, 'basis' => ''];

    $ap = function (string $method, array $args, $fallback) use ($business) {
        if (! class_exists(ArtisanProfile::class) || ! method_exists(ArtisanProfile::class, $method)) {
            return $fallback;
        }
        try {
            return ArtisanProfile::{$method}($business, ...$args);
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    /* Every call takes $lang. Each of these methods returns prose of its own —
       the reason a figure is unknown, the name of a certificate, why a piece
       carries no price — and that prose is what a reader actually reads, so a
       missed argument shows up as a French sentence on an English page. */
    $apIdentity = $ap('identity', [$lang], []);
    $apCerts    = $ap('certificates', [$lang], []);
    $apProducts = $ap('products', [12, $lang], null);
    $apReviews  = $ap('reviews', [$lang], [
        'count' => 0, 'has_reviews' => false, 'mean' => $unknownStat,
        'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
    ]);
    $apAwards   = $ap('awards', [$lang], ['items' => [], 'count' => 0]);
    $apStats    = $ap('statistics', [$lang], []);
    $apTrust    = $ap('trustScore', [$lang], $unknownStat + ['max' => null, 'breakdown' => []]);
    $apWorkshop = $ap('workshop', [$lang], null);

    /*
     | The written reviews themselves, and the award rows with their evidence.
     | ArtisanProfile counts and distributes reviews but does not hand back the
     | text; ArtisanReviews and ArtisanAwards are the registers that do, and
     | both are read here under the same guard as everything else so a missing
     | class empties the block rather than throwing at a visitor.
     |
     | The badge beside a reviewer's name is ArtisanReviews' own sentence. The
     | artwork's "Verified Buyer" is a claim this platform cannot make: it is
     | not party to sales, holds no orders and never learns whether money moved.
     | What it can check is that the account messaged this artisan through the
     | platform, and that is what the badge says, at the length the register
     | wrote it — the class states in terms that surfaces must never shorten it
     | into a purchase.
     */
    $reviewRows = collect();
    $reviewBadgeLabel = '';
    if (class_exists(\App\Support\ArtisanReviews::class)) {
        try {
            $reviewRows = collect(\App\Support\ArtisanReviews::publishedFor($business, 3));
            $reviewBadgeLabel = \App\Support\ArtisanReviews::contactBadgeLabel($lang);
        } catch (\Throwable $e) {
            $reviewRows = collect();
        }
    }

    $awardRows = collect();
    if (class_exists(\App\Support\ArtisanAwards::class)) {
        try {
            $awardRows = collect(\App\Support\ArtisanAwards::forBusiness($business, $lang));
        } catch (\Throwable $e) {
            $awardRows = collect();
        }
    }

    /* A statistic's two states, decided once so no block below can drift. */
    $statKnown = fn ($s) => is_array($s) && ! empty($s['known']) && ($s['value'] ?? null) !== null;
    $statText  = function ($s) {
        $v = $s['value'] ?? null;
        if (is_int($v)) return number_format($v);
        if (is_float($v)) return rtrim(rtrim(number_format($v, 1), '0'), '.');
        return (string) $v;
    };
    $notTracked = $isFr ? 'Non suivi' : 'Not tracked';

    $businessName    = $isFr ? $business->name_fr : ($business->name_en ?: $business->name_fr);
    $tagline         = $isFr ? $business->tagline_fr : ($business->tagline_en ?: $business->tagline_fr);
    $descriptionText = $isFr ? $business->description_fr : ($business->description_en ?: $business->description_fr);
    $cityName        = $business->city?->name_fr;
    $regionName      = $business->region ? ($isFr ? $business->region->name_fr : ($business->region->name_en ?: $business->region->name_fr)) : null;
    $industryName    = $business->industry ? ($isFr ? $business->industry->name_fr : ($business->industry->name_en ?: $business->industry->name_fr)) : null;
    $isVerified      = in_array($business->verification_tier, ['verified', 'certified']);

    /*
     | Coarse location only, assembled from the named administrative units rather
     | than from address_fr. The business record holds gps_lat/gps_lng and a
     | street address; a workshop is usually somebody's home, and the product
     | passport already withholds these for the artisan's physical safety. A
     | public profile is a more exposed surface than the passport, not a less
     | exposed one, so nothing finer than the city is printed and no coordinate
     | reaches the markup at all — including inside a map URL.
     */
    $coarseLocation = collect([$cityName, $regionName, $isFr ? 'Cameroun' : 'Cameroon'])->filter()->implode(', ');

    /* The design prints "18+ Years Experience" as a fixed string. This is arithmetic, or nothing. */
    $yearsExperience = $apIdentity['years_experience'] ?? $unknownStat;
    if (! $statKnown($yearsExperience) && $business->year_established) {
        $yearsExperience = ['value' => max(0, (int) date('Y') - (int) $business->year_established), 'known' => true, 'basis' => ''];
    }

    /* Contact details belong to a real person: printed only if the record holds them. */
    $contactPhone = $business->phone ?: null;
    $contactEmail = $business->email ?: null;
    $languages    = collect($business->languages_spoken ?? [])->filter()->implode(', ');

    /* The design shows five craft chips — Wood Carving, Traditional Sculpture,
       Heritage Art, Masks, Statues. Every craft word this record actually
       carries is rendered, in the design's order of authority: the official
       trade off the craft taxonomy first, then the industry it sits under, then
       the free tags the artisan entered against their own shop. The region is
       deliberately not among them: it is already printed on the line above, and
       a place is not a craft. A shop that has entered one trade gets one chip. */
    $shopTags = collect();
    try {
        $shopTags = collect($business->tags->pluck('tag'));
    } catch (\Throwable $e) {
        $shopTags = collect();
    }
    $craftTags = collect([$business->source_metier, $industryName])
        ->merge($shopTags)
        ->map(fn ($t) => trim((string) $t))
        ->filter()
        ->unique(fn ($t) => mb_strtolower($t))
        ->values();

    /* The design bleeds a carved-mask photograph across the hero's right half.
       It renders only from this shop's own images — its cover, or failing that
       the first picture in its gallery. Someone else's mask behind this
       artisan's name and face is a misattribution, not a background. */
    $heroImage = $business->cover_image ?: null;
    if (! $heroImage) {
        try {
            $heroImage = $business->gallery->where('type', 'image')->sortBy('sort_order')->first()?->file_path;
        } catch (\Throwable $e) {
            $heroImage = null;
        }
    }

    $gan = $business->gan ?: null;

    /* Footer family for vendor detail pages. */
    $dfShowHelp = true;
    $dfSocialStyle = 'outline';

    /*
     | SEO/AEO structured data for this profile. Only real columns feed it:
     | vendor_type decides Person (a lone artisan) vs LocalBusiness
     | (cooperative/enterprise); location is the same city/region granularity
     | already used above — never gps_lat/gps_lng; the certificate link is
     | only added when the business actually carries a certificate_no.
     */
    $title = "{$businessName} — Artisan Hub 237";
    $description = \Illuminate\Support\Str::limit(strip_tags((string) $descriptionText), 150);

    $seoAddress = array_filter([
        '@type'           => 'PostalAddress',
        'addressLocality' => $cityName,
        'addressRegion'   => $regionName,
        'addressCountry'  => 'CM',
    ]);

    $seoBusinessSchema = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => $business->vendor_type === 'artisan' ? 'Person' : 'LocalBusiness',
        'name'        => $businessName,
        'url'         => \App\Support\Seo::canonical(request(), route('businesses.show', ['slug' => $business->slug], false)),
        'description' => $description ?: null,
        'image'       => $heroImage ? asset('storage/' . $heroImage) : null,
        'jobTitle'    => $business->vendor_type === 'artisan' ? $industryName : null,
        'address'     => count($seoAddress) > 1 ? $seoAddress : null,
    ]);
    if (! empty($business->certificate_no)) {
        $seoBusinessSchema['hasCredential'] = [
            '@type' => 'EducationalOccupationalCredential',
            'name'  => $isFr ? 'Certificat de membre vérifié — Artisan Hub 237' : 'Verified member certificate — Artisan Hub 237',
            'url'   => route('artisan.verification.certificate', ['slug' => $business->slug]),
        ];
    }

    $seoBreadcrumb = \App\Support\Seo::breadcrumbSchema(array_filter([
        ['name' => $isFr ? 'Accueil' : 'Home', 'url' => route('home', ['lang' => $lang])],
        ['name' => $isFr ? 'Artisans & Entreprises' : 'Artisans & Businesses', 'url' => route('businesses.index', ['lang' => $lang])],
        $industryName ? ['name' => $industryName, 'url' => route('businesses.index', ['lang' => $lang, 'industry' => $business->industry?->slug])] : null,
        ['name' => $businessName, 'url' => route('businesses.show', ['slug' => $business->slug])],
    ]));

    $seoJsonLd = [$seoBusinessSchema, $seoBreadcrumb];
    $seoOgType = 'profile';
    $seoOgImage = $heroImage ? asset('storage/' . $heroImage) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $description }}">
    <title>{{ $title }}</title>

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

    <script src="{{ asset('vendor/lucide-subset.js') }}"></script>
    <link href="{{ asset('vendor/fonts.css') }}" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ── Measured against the artwork ────────────────────────────────
           docs/ARTISAN-PROFILE-V2-SPEC.md, "MEASURED TYPOGRAPHY, SPACING AND
           COLOUR". The artwork is 1024 wide and this container is 1280, so
           every measured value is the artwork's × 1.25.

           The one that changes the whole page's character: the card fill is
           #FCFAF6 against a #FCF9F6 page — two levels apart. The border does
           the separating, not the fill. The kit's default surface is pure
           white, which against cream reads as a generic dashboard, so it is
           overridden here and only here, scoped to this desktop document so
           the phone partial keeps whatever its own owner decided. */
        .ap-desktop .ui-card { background-color: #FCFAF6; }

        /* Section eyebrow: gold glyph + small caps, above every band in the design. */
        .ap-sec-title {
            display: flex; align-items: center; gap: 9px;
            font-size: 11.5px; font-weight: 700; letter-spacing: .02em;
            text-transform: uppercase; color: #1D1B16; line-height: 1.3;
        }
        .ap-sec-title > i { color: #C9942E; flex: none; }
        .ap-sec-link {
            font-size: 11.5px; font-weight: 600; color: #8A6D1F;
            display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
        }
        .ap-sec-link:hover { text-decoration: underline; }

        /* An absence, styled so it can never be mistaken for a measured figure. */
        .ap-absent { font-style: italic; color: #A8A296; font-weight: 500; }

        /* ── Dark ────────────────────────────────────────────────────────
           The four rules above hardcode a colour rather than read a token,
           which is precisely how a card stays cream while the text inside it
           goes light — white on white, the worst failure mode there is. Each
           gets its counterpart here, from docs/DARK-MODE-CONTRACT.md.

           The card fill keeps the artwork's relationship rather than its
           value: surface #12150F sits one step above the page #0A0C09, the
           border still doing the separating, exactly as #FCFAF6 sits above
           #FCF9F6 in light. */
        html.dark .ap-desktop .ui-card { background-color: #12150F; }
        html.dark .ap-sec-title        { color: #F3EFE7; }   /* 17.12:1 on the page */
        html.dark .ap-sec-title > i    { color: #E9A81E; }   /*  9.43:1 */
        html.dark .ap-sec-link         { color: #EDB33A; }   /* 10.39:1 */
        html.dark .ap-absent           { color: #868778; }   /*  5.04:1 on the card */

        /* The workshop map is a drawn evocation, not a photograph or a brand
           mark, so it darkens with the page instead of sitting there as a pale
           rectangle. Its light values are unchanged — they moved out of the
           inline style only so the dark counterpart could exist at all. */
        .ap-map-tile {
            background:
                repeating-linear-gradient(0deg, transparent 0 26px, #DFDACC 26px 28px),
                repeating-linear-gradient(90deg, transparent 0 34px, #E4DFD2 34px 36px),
                linear-gradient(115deg, #E9E4D6 0%, #F2EFE6 55%, #E3E7DB 100%);
        }
        html.dark .ap-map-tile {
            background:
                repeating-linear-gradient(0deg, transparent 0 26px, #262B21 26px 28px),
                repeating-linear-gradient(90deg, transparent 0 34px, #262B21 34px 36px),
                linear-gradient(115deg, #12150F 0%, #1A1E16 55%, #141A12 100%);
        }

        /* The artwork's foil-shield rendering of a certificate, as a purely
           visual gradient treatment (violet→cyan→gold) inside a shield clip. */
        .ap-shield {
            position: relative; display: inline-flex; align-items: center; justify-content: center;
            width: 52px; height: 60px;
            clip-path: polygon(50% 0, 96% 12%, 96% 55%, 50% 100%, 4% 55%, 4% 12%);
            background:
                radial-gradient(120% 90% at 30% 20%, rgba(255,255,255,.55), transparent 45%),
                conic-gradient(from 210deg at 50% 45%, #8D6ADF, #4FB8D8, #E9C25C, #C98BD8, #5FC9B2, #8D6ADF);
        }
        .ap-shield::after {
            content: ''; position: absolute; inset: 3px;
            clip-path: polygon(50% 0, 96% 12%, 96% 55%, 50% 100%, 4% 55%, 4% 12%);
            background: linear-gradient(160deg, rgba(255,255,255,.35), rgba(20,15,5,.28) 70%);
            mix-blend-mode: soft-light;
        }
        .ap-shield-sheen {
            position: absolute; inset: 0;
            background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.5) 46%, transparent 60%);
        }
        .ap-shield-dim { filter: saturate(.25) opacity(.55); }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    @include('pages.partials.seo-head')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset('vendor/app.css') }}">
</head>
<body class="bg-[#FCF9F6] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

{{-- ── Phone ──────────────────────────────────────────────────────────────
     A sibling document, not a reflow: its own top bar, hero card and fixed
     bottom navigation. Included exactly as its header contract specifies.
--}}
<div class="lg:hidden">
    @include('pages.businesses.partials.show-mobile')
</div>

{{-- ── Desktop ─────────────────────────────────────────────────────────── --}}
<div class="hidden lg:block ap-desktop">

@php
    $dirIconVariant = 'vdetail';
    $dirNavActive = 'businesses';
    $dirSearchPlaceholder = $isFr ? 'Rechercher un artisan, une entreprise, un produit...' : 'Search an artisan, a business, a product...';
@endphp
@include('pages.partials.directory-header')

{{-- Admin preview banner. This page is reachable with a non-published status
     only for an administrator (FrontendController::businessShow), and only so
     that the admin console's links to the 509 unclaimed SIARC profiles lead
     somewhere instead of 404ing. The banner is not decoration: without it a
     preview is indistinguishable from a live profile, and an admin could
     reasonably tell an artisan their page is up when the public cannot see it.
     The view is also not counted — see the controller. --}}
@if(! empty($isPreview))
    @php
        // Say the status in the operator's language rather than leaking the
        // column value; fall back to the raw value for any status added later,
        // which is still more useful than hiding it.
        $previewStatusLabels = [
            'draft'     => ['fr' => 'brouillon',   'en' => 'draft'],
            'pending'   => ['fr' => 'en attente',  'en' => 'pending review'],
            'suspended' => ['fr' => 'suspendu',    'en' => 'suspended'],
            'archived'  => ['fr' => 'archivé',     'en' => 'archived'],
        ];
        $previewStatus = $previewStatusLabels[$business->status][$lang] ?? $business->status;
    @endphp
    <div class="max-w-[1280px] mx-auto px-[25px] pt-4">
        <div class="ui-alert ui-alert-warn flex items-start gap-2.5">
            <i data-lucide="eye-off" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <span>
                <strong>{{ $isFr ? 'Aperçu administrateur.' : 'Administrator preview.' }}</strong>
                {{ $isFr
                    ? 'Ce profil est ' . $previewStatus . ' : il n\'est pas visible par le public, et cette visite n\'est pas comptée dans les statistiques de l\'artisan.'
                    : 'This profile is ' . $previewStatus . ': it is not visible to the public, and this visit is not counted towards the artisan\'s statistics.' }}
            </span>
        </div>
    </div>
@endif

<main>
<div class="max-w-[1280px] mx-auto px-[25px] pt-4 pb-10">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────── --}}
    <nav class="flex flex-wrap items-center gap-2 text-[12.5px]" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="{{ route('businesses.index', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56] transition-colors">Artisans</a>
        @if($industryName)
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#6F6B60] dark:text-[#868778]">{{ $industryName }}</span>
        @endif
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span class="text-[#1D1B16] dark:text-[#F3EFE7] font-medium">{{ $businessName }}</span>
    </nav>

    {{-- ── Hero ───────────────────────────────────────────────────────── --}}
    {{-- The artwork's hero: a near-black card (#0E0A03 family) with a faint
         green glow bleeding in from the left, the artisan block across the
         left two-thirds, and a floating dark trust card overlapping the
         hero's right edge and hanging past its bottom. The overlap is real:
         the card is absolutely positioned and the section carries a bottom
         margin sized to the overhang. --}}
    <section class="mt-3 relative rounded-[16px] mb-10">
        <div class="absolute inset-0 rounded-[16px] overflow-hidden bg-[#0E0A03]">
            {{-- Left green glow, as sampled (#106239) in the artwork. --}}
            <div class="absolute -left-24 -top-24 w-[520px] h-[420px] rounded-full opacity-40"
                 style="background: radial-gradient(closest-side, #106239, transparent 70%)"></div>
            {{-- The design bleeds a large carved-mask photograph across the
                 right half, to the card's edge, under a gradient that takes it
                 back to black by the middle of the card. It renders only from
                 this shop's own pictures: the design's stock artwork would put
                 another artisan's work behind this artisan's name and face. --}}
            @if($heroImage)
            <img src="{{ asset('storage/' . $heroImage) }}" alt=""
                 class="absolute inset-y-0 right-0 w-[64%] h-full object-cover object-center" aria-hidden="true">
            {{-- Two layers, as the artwork reads: a horizontal wash that hides
                 the seam behind the name block, and a warm darkening over the
                 whole photograph so white type stays legible on top of it. --}}
            <div class="absolute inset-y-0 right-0 w-[64%] bg-gradient-to-r from-[#0E0A03] via-[#0E0A03]/45 to-transparent"></div>
            <div class="absolute inset-0 bg-[#0E0A03]/15"></div>
            @else
            {{-- No photograph on record: the right half keeps the artwork's
                 darkened-relief feel with a pure CSS carve pattern instead of
                 someone else's mask. --}}
            <div class="absolute inset-y-0 right-0 w-[46%] opacity-[.28]"
                 style="background: repeating-linear-gradient(115deg, #C9942E 0 1px, transparent 1px 26px), repeating-linear-gradient(-115deg, #6B5426 0 1px, transparent 1px 34px)"></div>
            <div class="absolute inset-y-0 right-0 w-[55%] bg-gradient-to-r from-[#0E0A03] to-transparent"></div>
            @endif
        </div>

        <div class="relative flex items-start gap-7 p-7 pr-[292px] min-h-[252px]">

            {{-- Portrait --}}
            <div class="shrink-0">
                @if($business->logo)
                <img src="{{ asset('storage/' . $business->logo) }}" alt=""
                     class="w-[176px] h-[176px] rounded-full object-cover ring-[3px] ring-[#C9942E]">
                @else
                <span class="w-[176px] h-[176px] rounded-full bg-[#2A2318] ring-[3px] ring-[#C9942E] flex items-center justify-center text-[#C9942E]">
                    <i data-lucide="user-round" class="w-14 h-14" stroke-width="1.4"></i>
                </span>
                @endif
            </div>

            {{-- Name block --}}
            <div class="min-w-0 flex-1 pt-1">
                @if($isVerified)
                <span class="inline-flex items-center gap-1.5 bg-[#D3B030] text-[#1B1403] text-[13px] md:text-[11px] font-bold tracking-[.08em] uppercase rounded-full px-3 py-1">
                    <i data-lucide="badge-check" class="w-3 h-3"></i>
                    {{ $isFr ? 'Artisan vérifié' : 'Verified artisan' }}
                </span>
                @endif

                <h1 class="mt-2.5 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[34px] tracking-[-0.01em] leading-[1.15] font-bold text-white">
                    <span>{{ $businessName }}</span>
                    @if($isVerified)
                    <svg viewBox="0 0 16 16" class="w-[22px] h-[22px] shrink-0" aria-hidden="true"><circle cx="8" cy="8" r="8" fill="#17A34A"/><path d="M4.7 8.2 7 10.4l4.3-4.6" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    @endif
                </h1>

                @if($tagline)
                <p class="mt-1.5 text-[13.5px] font-normal leading-snug text-[#E4DCC9]">{{ $tagline }}</p>
                @endif

                <div class="mt-3.5 flex flex-wrap items-center gap-x-6 gap-y-2 text-[12.5px] text-[#CFC6B2]">
                    @if($coarseLocation)
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="map-pin" class="w-[13px] h-[13px] text-[#C9942E]"></i>{{ $coarseLocation }}</span>
                    @endif
                    @if($statKnown($yearsExperience))
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="hammer" class="w-[13px] h-[13px] text-[#C9942E]"></i>{{ $statText($yearsExperience) }} {{ $isFr ? "ans d'expérience" : 'years of experience' }}</span>
                    @endif
                    @if($business->created_at)
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="calendar" class="w-[13px] h-[13px] text-[#C9942E]"></i>{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $business->created_at->locale($lang)->translatedFormat('F Y') }}</span>
                    @endif
                </div>

                @if($craftTags->isNotEmpty())
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($craftTags as $tag)
                    <span class="inline-flex items-center rounded px-2.5 py-1.5 text-[13px] md:text-[11.5px] font-medium text-[#EBC989] border border-[#6B5426] bg-[#231B0E]">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- ── Trust panel ──────────────────────────────────────────
                 The design puts "4.9 / 5" and five gold stars beside this
                 person's face, captioned with a review count. A number in that
                 position is a public claim about someone's character, so it is
                 printed only when ArtisanProfile::trustScore() says it is known,
                 and the breakdown that produced it is reachable from here rather
                 than kept private. Unknown says so plainly.
            --}}
            <div class="absolute right-5 top-5 -bottom-6 w-[236px] rounded-[13px] border border-[#C9942E]/80 bg-[#070805] shadow-[0_18px_40px_-18px_rgba(0,0,0,.65)] p-4 flex flex-col">
                <p class="text-center text-[13px] md:text-[11px] font-bold tracking-[.08em] uppercase text-[#C9942E]">
                    {{-- The register's own name for this figure. It measures how
                         much of the account has been checked, not anyone's honesty
                         or craft, and calling it a trust score would promote it
                         into a claim about the person. --}}
                    {{ $apTrust['label'] ?? ($isFr ? 'Niveau de vérification' : 'Verification standing') }}
                </p>

                @if($statKnown($apTrust))
                <p class="mt-1.5 text-center">
                    <span class="text-[38px] leading-none font-bold text-white">{{ $statText($apTrust) }}</span>
                    @if(($apTrust['max'] ?? null))
                    <span class="text-[15px] text-[#C6BCA6]">/{{ $apTrust['max'] }}</span>
                    @endif
                </p>
                @if(! empty($apTrust['breakdown']))
                <details class="mt-2">
                    <summary class="cursor-pointer list-none text-center text-[12px] md:text-[10.5px] font-semibold text-[#C9942E] hover:underline">
                        {{ $isFr ? 'Comment il est calculé' : 'How this is calculated' }}
                    </summary>
                    <ul class="mt-2 space-y-1.5 text-left">
                        @foreach($apTrust['breakdown'] as $input)
                        <li class="text-[12px] md:text-[10.5px] leading-snug text-[#C6BCA6]">
                            <span class="font-semibold text-[#E4DCC9]">{{ $input['points'] ?? 0 }}/{{ $input['max'] ?? 0 }}</span>
                            — {{ $input['basis'] ?? '' }}
                        </li>
                        @endforeach
                    </ul>
                </details>
                @endif
                @else
                {{-- Not "0". A score of zero would read as an assessment this
                     artisan failed; the truth is that no assessment has run. --}}
                <p class="mt-2 text-center text-[12px] leading-snug ap-absent">
                    {{ $isFr ? 'Pas encore évalué' : 'Not yet assessed' }}
                </p>
                @if(trim((string) ($apTrust['basis'] ?? '')) !== '')
                <p class="mt-1 text-center text-[12px] md:text-[10.5px] leading-snug text-[#9C937F]">{{ $apTrust['basis'] }}</p>
                @endif
                @endif

                {{-- The design's five gold stars and "(128 Reviews)" caption sit
                     here. business_reviews is empty, so there is no average to
                     draw and no stars to fill. --}}
                @if(($apReviews['count'] ?? 0) > 0 && $statKnown($apReviews['mean'] ?? []))
                <p class="mt-2 text-center text-[13px] md:text-[11px] text-[#C6BCA6]">
                    {{ $statText($apReviews['mean']) }}/5 · {{ $apReviews['count'] }} {{ $isFr ? 'avis' : 'reviews' }}
                </p>
                @endif

                <div class="mt-auto pt-3.5 space-y-2">
                    <a href="{{ $siacUser
                            ? route('quotes.create', ['business' => $business->slug, 'lang' => $lang])
                            : route('login', ['lang' => $lang]) }}"
                       class="ui-btn ui-btn-block bg-[#14652F] text-white border-transparent hover:bg-[#157A43]">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        {{ $isFr ? "Contacter l'artisan" : 'Contact artisan' }}
                    </a>
                    @if($apWorkshop)
                    <a href="#ap-workshop" class="ui-btn ui-btn-block bg-transparent text-white border-[#6B5426] hover:border-[#C9942E]">
                        {{ $isFr ? "Voir l'atelier" : 'View workshop' }}
                    </a>
                    @endif
                    <a href="{{ $siacUser ? route('saved.index') : route('login', ['lang' => $lang]) }}"
                       class="ui-btn ui-btn-block rounded-full bg-transparent text-white border-[#6B5426] hover:border-[#C9942E]">
                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                        {{ $isFr ? 'Suivre' : 'Follow' }}
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Identity · About · Workshop ─────────────────────────────────
         The design's second band: a bordered identity table on the left, the
         artisan's own words in the middle, and a location panel on the right.
    --}}
    <div class="mt-5 grid grid-cols-12 gap-2.5 items-start">

        {{-- Identity table.
             The design lists twelve rows and fills every one. Here a row exists
             only if the register holds the value: an artisan who has not
             published a phone number gets no phone row, rather than a row
             reading "—" that implies we asked and they refused. Nationality and
             "workshop visits" have no column on `businesses` at all and are not
             synthesised. --}}
        @php
            /* The artwork's NATIONALITY row. `businesses` has no nationality
               column, and a Cameroonian region does not make its occupant
               Cameroonian — ArtisanProfile says so in as many words and offers
               the registered workshop's country as the nearest fact it holds.
               That is what is printed, under the label that describes it. A
               row headed "nationality" over a workshop address would be the
               page asserting somebody's citizenship. */
            $countryStat = $apIdentity['country'] ?? $unknownStat;
            $workshopCountry = $statKnown($countryStat) ? $statText($countryStat) : null;
            /* The register stores an ISO code. Spelling it out in the reader's
               language renames nothing — it is the same value, legibly. */
            if ($workshopCountry !== null && strlen($workshopCountry) === 2 && class_exists(\Locale::class)) {
                try {
                    $spelled = \Locale::getDisplayRegion('-' . strtoupper($workshopCountry), $lang);
                    if ($spelled !== '' && strtoupper($spelled) !== strtoupper($workshopCountry)) {
                        $workshopCountry = $spelled;
                    }
                } catch (\Throwable $e) {
                    // The code stands as stored.
                }
            }

            /* The artwork's WORKSHOP VISITS row reads "Verified on 15/06/2026".
               The only thing behind such a sentence is an inspection the
               workshop register recorded, so the row exists only when the
               register holds a verification date. No date, no row: "visits
               welcome" is a promise on the artisan's behalf that nobody made. */
            $workshopVisit = null;
            if ($apWorkshop && ! empty($apWorkshop['verified_at'])) {
                try {
                    $workshopVisit = ($isFr ? 'Inspecté le ' : 'Inspected on ')
                        . \Illuminate\Support\Carbon::parse($apWorkshop['verified_at'])->locale($lang)->translatedFormat('d/m/Y');
                } catch (\Throwable $e) {
                    $workshopVisit = null;
                }
            }

            $identityRows = collect([
                $gan            ? [$isFr ? 'Identifiant artisan' : 'Artisan ID', $gan, 'fingerprint', true] : null,
                $business->siarc_code && ! $gan
                                ? [$isFr ? 'Code SIARC' : 'SIARC code', $business->siarc_code, 'hash', true] : null,
                                  [$isFr ? "Nom de l'entreprise" : 'Business name', $businessName, 'store', false],
                $apWorkshop && ($apWorkshop['name'] ?? null)
                                ? [$isFr ? 'Atelier' : 'Workshop', $apWorkshop['name'], 'warehouse', false] : null,
                $workshopCountry ? [$isFr ? "Pays de l'atelier" : 'Workshop country', $workshopCountry, 'flag', false] : null,
                $industryName   ? [$isFr ? 'Spécialisation' : 'Specialisation', $industryName, 'hammer', false] : null,
                $statKnown($yearsExperience)
                                ? [$isFr ? "Années d'expérience" : 'Years experience', $statText($yearsExperience) . ' ' . ($isFr ? 'ans' : 'years'), 'clock', false] : null,
                $languages !== '' ? [$isFr ? 'Langues' : 'Languages', $languages, 'languages', false] : null,
                $contactPhone   ? [$isFr ? 'Téléphone' : 'Phone', $contactPhone, 'phone', false] : null,
                $contactEmail   ? ['Email', $contactEmail, 'mail', false] : null,
                $coarseLocation ? [$isFr ? 'Localisation' : 'Location', $coarseLocation, 'map-pin', false] : null,
                $workshopVisit  ? [$isFr ? "Visites d'atelier" : 'Workshop visits', $workshopVisit, 'door-open', false] : null,
            ])->filter()->values();
        @endphp
        <section class="col-span-12 xl:col-span-4 ui-card">
            <h2 class="ap-sec-title mb-4"><i data-lucide="id-card" class="w-4 h-4"></i>{{ $isFr ? "Fiche d'identité" : 'Identity' }}</h2>
            <dl>
                @foreach($identityRows as [$idLabel, $idValue, $idIcon, $idMono])
                <div class="flex items-start gap-2.5 py-[3.5px] min-h-[25px] first:pt-0">
                    <i data-lucide="{{ $idIcon }}" class="w-[14px] h-[14px] text-[#C9942E] mt-[3px] shrink-0"></i>
                    <dt class="w-[132px] shrink-0 text-[13px] md:text-[11px] font-medium leading-[18px] uppercase tracking-[.04em] text-[#45494B] dark:text-[#B4B5A6]">{{ $idLabel }}</dt>
                    <span class="shrink-0 text-[13px] md:text-[11.5px] leading-[18px] text-[#C8C1B2]" aria-hidden="true">:</span>
                    <dd class="min-w-0 flex-1 text-[12.5px] font-medium leading-[18px] text-[#1D1F24] dark:text-[#F3EFE7] break-words {{ $idMono ? 'font-mono text-[13px] md:text-[11.5px]' : '' }}">{{ $idValue }}</dd>
                </div>
                @endforeach

                {{-- Profile status. "Verified" here means documents were received
                     and checked — config/legal.php is explicit that it is not a
                     guarantee of quality or of an order being fulfilled — so the
                     row says which of the two it is. --}}
                <div class="flex items-start gap-2.5 py-[3.5px] min-h-[25px]">
                    <i data-lucide="shield-check" class="w-[14px] h-[14px] text-[#C9942E] mt-[3px] shrink-0"></i>
                    <dt class="w-[132px] shrink-0 text-[13px] md:text-[11px] font-medium leading-[18px] uppercase tracking-[.04em] text-[#45494B] dark:text-[#B4B5A6]">{{ $isFr ? 'Statut du profil' : 'Profile status' }}</dt>
                    <span class="shrink-0 text-[13px] md:text-[11.5px] leading-[18px] text-[#C8C1B2]" aria-hidden="true">:</span>
                    <dd class="min-w-0 flex-1">
                        @if($isVerified)
                        <span class="ui-pill ui-pill-ok"><i data-lucide="check" class="w-3 h-3"></i>{{ $isFr ? 'Documents vérifiés' : 'Documents checked' }}</span>
                        @else
                        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Non vérifié' : 'Not verified' }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        {{-- About --}}
        <section class="col-span-12 xl:col-span-4 ui-card">
            <h2 class="ap-sec-title mb-4"><i data-lucide="user" class="w-4 h-4"></i>{{ $isFr ? 'À propos de' : 'About' }} {{ $businessName }}</h2>

            @if(trim((string) $descriptionText) !== '')
            <div class="space-y-3 text-[15px] md:text-[12.5px] leading-[1.6] md:leading-[1.75] text-[#3B382F] dark:text-[#F3EFE7]">
                @foreach(preg_split('/\R{2,}/', trim((string) $descriptionText)) as $para)
                <p>{{ $para }}</p>
                @endforeach
            </div>
            @else
            {{-- The design fills this with three paragraphs of first-person copy
                 about Bamoun heritage and ancestral stories. It would be printed
                 word for word on every artisan, most of whom are not Bamoun and
                 none of whom wrote it. --}}
            <p class="text-[12.5px] ap-absent">{{ $isFr ? "Cet artisan n'a pas encore écrit sa présentation." : 'This artisan has not written their introduction yet.' }}</p>
            @endif

            {{-- The design's four counters. "128 Products Sold" and "96 Happy
                 Customers" have no source: this platform records no orders and
                 no customers, and never has. They are kept as tiles — removing
                 them would suggest the figures are simply zero today — but each
                 says what it is. --}}
            @php
                $aboutTiles = [
                    ['products_sold',   $isFr ? 'Produits vendus' : 'Products sold'],
                    ['happy_customers', $isFr ? 'Clients satisfaits' : 'Happy customers'],
                    ['exhibitions',     $isFr ? 'Expositions' : 'Exhibitions'],
                    ['awards_received', $isFr ? 'Distinctions reçues' : 'Awards received'],
                ];
                /* The artwork's fourth tile counts awards received. That one the
                   register can answer, from business_awards, so it is fed in
                   beside the statistics rather than left to say "not tracked". */
                $apStats['awards_received'] = [
                    'value' => (int) ($apAwards['count'] ?? 0),
                    'known' => true,
                    'basis' => $apAwards['basis'] ?? '',
                ];
            @endphp
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                @foreach($aboutTiles as [$tileKey, $tileLabel])
                @php $tile = $apStats[$tileKey] ?? $unknownStat; @endphp
                <div class="rounded-lg border border-[#EFEBE2] dark:border-[#262B21] px-2 py-3 text-center">
                    @if($statKnown($tile))
                    <p class="text-[18px] font-bold leading-none text-[#1D1B16] dark:text-[#F3EFE7]">{{ $statText($tile) }}</p>
                    @else
                    <p class="text-[13px] md:text-[11.5px] leading-tight ap-absent" @if(trim((string) ($tile['basis'] ?? '')) !== '') title="{{ $tile['basis'] }}" @endif>{{ $notTracked }}</p>
                    @endif
                    <p class="mt-1.5 text-[12px] md:text-[9px] font-normal leading-tight text-[#8A857A] dark:text-[#868778]">{{ $tileLabel }}</p>
                </div>
                @endforeach
            </div>
        </section>

        {{-- Workshop location --}}
        <section id="ap-workshop" class="col-span-12 xl:col-span-4 ui-card">
            <h2 class="ap-sec-title mb-4"><i data-lucide="map-pin" class="w-4 h-4"></i>{{ $isFr ? "Localisation de l'atelier" : 'Workshop location' }}</h2>

            <p class="flex items-start gap-2 text-[12.5px] font-semibold leading-snug text-[#1D1B16] dark:text-[#F3EFE7]">
                <i data-lucide="map-pin" class="w-[14px] h-[14px] text-[#157A43] dark:text-[#339B56] mt-[2px] shrink-0"></i>
                {{ $coarseLocation ?: ($isFr ? 'Non communiquée' : 'Not published') }}
            </p>

            {{-- The design shows a pinned street map and prints the decimal
                 coordinates above it — "4.0480° N, 9.7679° E". This workshop is
                 in most cases somebody's home. The product passport already
                 withholds these, and a public profile is the more exposed
                 surface of the two, so no coordinate reaches the markup at all —
                 including inside a map URL. The map area below is a pure CSS
                 evocation of the artwork's tile, keyed to nothing: the pin sits
                 at its decorative centre, not at anybody's door. --}}
            <div class="relative mt-3 h-[132px] rounded-[10px] overflow-hidden border border-[#EFEBE2] dark:border-[#262B21] bg-[#EFEDE6] dark:bg-[#0A0C09]">
                <div class="absolute inset-0 opacity-70 ap-map-tile"></div>
                <div class="absolute left-[8%] top-0 bottom-0 w-[14%] bg-[#CBD9E4]/60 dark:bg-[#0E2436]/60" style="transform: skewX(-18deg)"></div>
                <div class="absolute right-[18%] -top-4 w-10 h-10 rounded-full bg-[#D7E3CE]/80 dark:bg-[#0C3D1D]/80"></div>
                <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-full text-[#0A5C2E] dark:text-[#339B56]">
                    <i data-lucide="map-pin" class="w-7 h-7 fill-[#0A5C2E] dark:fill-[#339B56] text-[#0A5C2E] dark:text-[#339B56]"></i>
                </span>
                @if($coarseLocation)
                <a href="https://www.google.com/maps/search/{{ urlencode($coarseLocation) }}" target="_blank" rel="noopener"
                   class="absolute right-2 bottom-2 inline-flex items-center gap-1.5 rounded-md bg-[#0A5C2E] px-2.5 py-1.5 text-[12px] md:text-[9.5px] font-bold uppercase tracking-[.06em] text-white hover:bg-[#0D6E38]">
                    {{ $isFr ? 'Voir sur la carte' : 'View on map' }}
                </a>
                @endif
            </div>

            <p class="mt-2.5 text-[13px] md:text-[11.5px] leading-relaxed text-[#8A857A] dark:text-[#868778]">
                {{ $isFr
                   ? "Seuls la ville et la région sont publiés. L'adresse exacte de l'atelier est communiquée par l'artisan lors d'une prise de contact."
                   : 'Only the town and region are published. The exact workshop address is given by the artisan when you get in touch.' }}
            </p>

            @if($apWorkshop && ($apWorkshop['reference'] ?? null))
            <p class="mt-3 text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">
                {{ $isFr ? 'Atelier enregistré' : 'Registered workshop' }}
                <span class="block mt-0.5 font-mono text-[13px] md:text-[11.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7] break-all">{{ $apWorkshop['reference'] }}</span>
            </p>
            @endif

            {{-- Only the channels this artisan actually published. --}}
            @php
                $channels = collect([
                    $business->whatsapp ? ['message-circle', 'https://wa.me/' . preg_replace('/\D/', '', $business->whatsapp), 'WhatsApp'] : null,
                    $contactPhone ? ['phone', 'tel:' . $contactPhone, $isFr ? 'Appeler' : 'Call'] : null,
                    $contactEmail ? ['mail', 'mailto:' . $contactEmail, 'Email'] : null,
                    $business->website ? ['globe', $business->website, $isFr ? 'Site web' : 'Website'] : null,
                ])->filter()->values();
            @endphp
            {{-- The artwork closes this card with a row of coloured social
                 discs. Same shape here — one disc per channel this artisan has
                 actually published, none invented to fill the row. --}}
            @if($channels->isNotEmpty())
            <div class="mt-4 flex items-center justify-center gap-2.5">
                @php $chColors = ['message-circle' => '#25A05A', 'phone' => '#0A5C2E', 'mail' => '#C8860B', 'globe' => '#1877A8']; @endphp
                @foreach($channels as [$chIcon, $chHref, $chLabel])
                <a href="{{ $chHref }}" @if(in_array($chIcon, ['globe', 'message-circle'])) target="_blank" rel="noopener" @endif
                   title="{{ $chLabel }}" aria-label="{{ $chLabel }}"
                   class="w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-85 transition-opacity"
                   style="background: {{ $chColors[$chIcon] ?? '#1D1B16' }}">
                    <i data-lucide="{{ $chIcon }}" class="w-4 h-4"></i>
                </a>
                @endforeach
            </div>
            @endif
        </section>
    </div>

    {{-- ── Certificates & verifications ────────────────────────────────
         The design shows six numbered shields, all issued, all green. These are
         the five issuing registers; a register that has issued nothing for this
         artisan says so in its own words rather than showing a blank shield,
         because a greyed-out certificate and a certificate that was never
         applied for look alike and mean different things.

         Every number printed here is the register's own, and every one is
         checkable by a stranger through the public verification page — which is
         the whole point of printing it.
    --}}
    @php
        $certTypes = ['avc', 'wvc', 'coa', 'otc', 'eac'];
        $certBlocks = collect($certTypes)
            ->map(fn ($t) => is_array($apCerts[$t] ?? null) ? $apCerts[$t] + ['type' => $t] : null)
            ->filter()->values();
        $certIssuedCount = $certBlocks->sum(fn ($b) => (int) ($b['count'] ?? 0));
    @endphp
    @if($certBlocks->isNotEmpty())
    <section class="mt-6">
        <div class="flex items-center justify-between gap-4">
            <h2 class="ap-sec-title"><i data-lucide="shield-check" class="w-4 h-4"></i>{{ $isFr ? 'Certificats & vérifications' : 'Certificates & verifications' }}</h2>
            <a href="{{ route('certificate.verify', ['lang' => $lang]) }}" class="ap-sec-link">
                {{ $isFr ? 'Vérifier un certificat' : 'Verify a certificate' }}<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="mt-3 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach($certBlocks as $block)
            @php $issued = ! empty($block['issued']); $first = $block['items'][0] ?? null; @endphp
            {{-- The artwork's card is horizontal: the foil shield on the left,
                 the certificate's name beside it over two lines, the number
                 beneath both, and a bottom row carrying the state, the date and
                 the download control. --}}
            <article class="relative ui-card p-3.5 pb-9 {{ $issued ? '' : 'bg-[#FCFBF8] dark:bg-[#12150F]' }}">
                <div class="flex items-start gap-2.5">
                    {{-- The artwork draws each certificate as a foil shield. On
                         screen this is a visual treatment only — layered
                         gradients in a shield clip with the brand mark centred.
                         It is never captioned as a hologram or a security
                         feature; docs/PRINT-SECURITY-SPEC.md governs claims. --}}
                    <span class="ap-shield shrink-0 {{ $issued ? '' : 'ap-shield-dim' }}">
                        <span class="ap-shield-sheen"></span>
                        <img src="{{ brand_asset('mark') }}" alt="" class="relative w-6 h-6 object-contain">
                    </span>
                    <div class="min-w-0">
                        <p class="text-[13px] md:text-[11.5px] font-semibold leading-snug text-[#1D1B16] dark:text-[#F3EFE7]">{{ $block['name'] ?? strtoupper($block['type']) }}</p>
                        <p class="mt-0.5 text-[12px] md:text-[9.5px] uppercase tracking-[.08em] text-[#B8B2A4]">{{ strtoupper($block['type']) }}</p>
                    </div>
                </div>

                @if($issued && $first)
                <p class="mt-2.5 font-mono text-[12px] md:text-[10px] font-semibold text-[#6F6B60] dark:text-[#868778] break-all">{{ $first['number'] }}</p>
                <div class="mt-2.5 flex items-center gap-2">
                    <span class="ui-pill ui-pill-ok"><i data-lucide="check" class="w-3 h-3"></i>{{ $isFr ? 'Au registre' : 'On register' }}</span>
                    @if(! empty($first['issued_at']))
                    <span class="text-[12px] md:text-[10px] text-[#8A857A] dark:text-[#868778]">{{ \Illuminate\Support\Carbon::parse($first['issued_at'])->format('d/m/Y') }}</span>
                    @endif
                </div>
                @if(($block['count'] ?? 0) > 1)
                <p class="mt-1.5 text-[12px] md:text-[10px] text-[#8A857A] dark:text-[#868778]">+{{ $block['count'] - 1 }} {{ $isFr ? 'autre(s)' : 'more' }}</p>
                @endif

                {{-- The artwork puts a download glyph on every card. It renders
                     where the issuing register gave the entry an address of its
                     own; a glyph on a card with nowhere to go teaches a reader
                     that the control is decoration. --}}
                @if(! empty($first['url']))
                <a href="{{ $first['url'] }}" target="_blank" rel="noopener"
                   title="{{ $isFr ? 'Ouvrir le certificat' : 'Open the certificate' }}"
                   aria-label="{{ $isFr ? 'Ouvrir le certificat' : 'Open the certificate' }} {{ strtoupper($block['type']) }}"
                   class="absolute right-2.5 bottom-2.5 w-7 h-7 rounded-md border border-[#EFE7D4] dark:border-[#6A5210] bg-[#FBF7EC] dark:bg-[#0A0C09] text-[#8A6D1F] dark:text-[#EDB33A] flex items-center justify-center hover:bg-[#F3E7CB] hover:dark:bg-[#3A2B06] transition-colors">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                </a>
                @endif
                @else
                {{-- The register's own explanation of the absence, not ours. --}}
                <p class="mt-2.5 text-[12px] md:text-[10.5px] leading-relaxed ap-absent">{{ $block['basis'] ?? ($isFr ? 'Non émis.' : 'Not issued.') }}</p>
                @endif
            </article>
            @endforeach
        </div>

        @if($certIssuedCount === 0)
        <p class="mt-3 text-[13px] md:text-[11.5px] text-[#8A857A] dark:text-[#868778]">
            {{ $isFr
               ? "Aucun certificat n'a encore été émis pour cet artisan. Un certificat ne s'obtient pas automatiquement : il suppose une démarche et un contrôle."
               : 'No certificate has been issued for this artisan yet. A certificate is not automatic — it follows an application and a check.' }}
        </p>
        @endif
    </section>
    @endif

    {{-- ── Featured products ───────────────────────────────────────────
         Only this artisan's own published pieces. The directory controller tops
         its featured list up to six with recent products from OTHER vendors,
         which is reasonable on a listing page and would be misattribution here:
         under this artisan's name and portrait, a stranger's carving reads as
         their work.
    --}}
    @php
        $products = collect($apProducts['items'] ?? []);

        /* The artwork prints a category under every product name — "Traditional
           Masks", "Wood Sculpture". ArtisanProfile does not carry one, so it is
           read straight off product_categories for exactly the pieces shown. A
           piece filed under no category simply has no second line. */
        $productCategories = collect();
        if ($products->isNotEmpty()) {
            try {
                $productCategories = \Illuminate\Support\Facades\DB::table('products')
                    ->join('product_categories as pc', 'pc.id', '=', 'products.category_id')
                    ->whereIn('products.id', $products->pluck('id')->filter()->all())
                    ->get(['products.id as pid', 'pc.name_fr', 'pc.name_en'])
                    ->mapWithKeys(fn ($r) => [
                        (int) $r->pid => $isFr ? $r->name_fr : ($r->name_en ?: $r->name_fr),
                    ]);
            } catch (\Throwable $e) {
                $productCategories = collect();
            }
        }
    @endphp
    <section class="mt-7">
        <div class="flex items-center justify-between gap-4">
            <h2 class="ap-sec-title"><i data-lucide="package" class="w-4 h-4"></i>{{ $isFr ? 'Produits en vedette' : 'Featured products' }}</h2>
            @if(($apProducts['total_published'] ?? 0) > $products->take(6)->count())
            <a href="{{ route('products.index', ['lang' => $lang]) }}" class="ap-sec-link">
                {{ $isFr ? 'Tous les produits' : 'All products' }} ({{ $apProducts['total_published'] }})<i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
            @endif
        </div>

        @if($products->isNotEmpty())
        <div class="mt-3 grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2">
            @foreach($products->take(6) as $p)
            <article class="ui-card p-0 overflow-hidden">
                <div class="relative">
                    <a href="{{ route('products.show', ['slug' => $p['slug'], 'lang' => $lang]) }}" class="block">
                        @if($p['image'])
                        <img src="{{ asset('storage/' . $p['image']) }}" alt="{{ $p['name'] }}" class="w-full h-[152px] object-cover">
                        @else
                        <span class="w-full h-[152px] bg-[#F6F2E9] dark:bg-[#0A0C09] flex items-center justify-center text-[#C8C1B2]">
                            <i data-lucide="image" class="w-7 h-7"></i>
                        </span>
                        @endif
                    </a>
                    <a href="{{ $siacUser ? route('saved.index') : route('login', ['lang' => $lang]) }}"
                       aria-label="{{ $isFr ? 'Enregistrer' : 'Save' }}"
                       class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 dark:bg-[#12150F]/90 text-[#6F6B60] dark:text-[#868778] flex items-center justify-center hover:text-[#CC060E] hover:dark:text-[#F0555C] transition-colors">
                        <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <div class="p-3">
                    <h3 class="text-[13px] md:text-[11px] font-semibold leading-snug text-[#1D1B16] dark:text-[#F3EFE7]">
                        <a href="{{ route('products.show', ['slug' => $p['slug'], 'lang' => $lang]) }}" class="hover:text-leaf hover:dark:text-[#339B56] transition-colors">{{ $p['name'] }}</a>
                    </h3>

                    @if($productCategories->get((int) ($p['id'] ?? 0)))
                    <p class="mt-1 text-[12px] md:text-[9px] font-normal text-[#8A857A] dark:text-[#868778]">{{ $productCategories->get((int) $p['id']) }}</p>
                    @endif

                    @if(($p['price']['amount'] ?? null) !== null)
                    <p class="mt-2 font-bold text-[#1D1B16] dark:text-[#F3EFE7]">
                        <span class="text-[13px] md:text-[11.5px]">{{ $p['price']['currency'] === 'XAF' ? 'FCFA' : $p['price']['currency'] }}</span>
                        <span class="text-[14px]">{{ $isFr ? number_format($p['price']['amount'], 0, ',', ' ') : number_format($p['price']['amount'], 0, '.', ',') }}</span>
                    </p>
                    @else
                    {{-- No published price is a real state, and a different one
                         from a piece nobody has priced. The register keeps them
                         apart and prints its own reason. --}}
                    <p class="mt-2 text-[13px] md:text-[11px] leading-snug ap-absent">{{ $p['price']['basis'] ?? '' }}</p>
                    @endif

                    {{-- The design puts "4.9 (26)" and a gold star under every
                         card. Reviews on this platform are written about the
                         artisan, never about an individual piece, so there is
                         nothing to attribute here. What CAN be shown is whether
                         the piece has a certificate of authenticity on register. --}}
                    @if($p['has_authenticity_certificate'])
                    {{-- The artwork's badge, at its sampled colour (#003712).
                         The word is the kit's, not the artwork's: "verified"
                         beside a price reads as a verdict on the piece, and
                         what the register actually holds is a certificate of
                         authenticity against it. --}}
                    <span class="mt-2.5 inline-flex items-center gap-1 rounded px-2 py-1 bg-[#003712] text-white text-[12px] md:text-[9px] font-bold uppercase tracking-[.06em]">
                        <i data-lucide="badge-check" class="w-[11px] h-[11px]"></i>{{ $isFr ? 'Certifiée' : 'Certified' }}
                    </span>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
        @if(trim((string) ($apProducts['ratings_basis'] ?? '')) !== '')
        <p class="mt-3 text-[13px] md:text-[11px] leading-relaxed text-[#8A857A] dark:text-[#868778]">{{ $apProducts['ratings_basis'] }}</p>
        @endif
        @else
        <p class="mt-3 text-[12.5px] ap-absent">{{ $isFr ? "Cet artisan n'a encore publié aucune pièce." : 'This artisan has not published any pieces yet.' }}</p>
        @endif
    </section>

    {{-- ── Reviews · Statistics · Achievements ─────────────────────────
         The design's last three-column band.
    --}}
    <div class="mt-7 grid grid-cols-12 gap-2.5 items-start">

        {{-- Customer reviews.
             The design shows "4.9", five gold stars, "Based on 128 reviews", a
             five-bar distribution reading 104/18/4/1/1, and a named testimonial
             with a photograph. business_reviews is empty across the whole
             platform. A mean has to come from ratings or not exist, and an
             invented testimonial puts words in a named buyer's mouth. --}}
        <section class="col-span-12 xl:col-span-5 ui-card">
            <h2 class="ap-sec-title mb-4"><i data-lucide="message-square-quote" class="w-4 h-4"></i>{{ $isFr ? 'Avis clients' : 'Customer reviews' }}</h2>

            @php $hasReviews = ($apReviews['count'] ?? 0) > 0 && $statKnown($apReviews['mean'] ?? []); @endphp
            @if($hasReviews)
            <div class="flex items-start gap-6">
                <div class="shrink-0 text-center">
                    <p class="text-[36px] leading-none font-bold text-[#04240D] dark:text-[#339B56]">{{ $statText($apReviews['mean']) }}</p>
                    <p class="mt-1.5 flex items-center justify-center gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <svg viewBox="0 0 20 20" class="w-[12.5px] h-[12.5px] {{ $i <= round($apReviews['mean']['value']) ? 'fill-[#E29A08]' : 'fill-[#E6E1D6]' }}"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                        @endfor
                    </p>
                    <p class="mt-2 text-[12px] md:text-[10.6px] font-normal text-[#8A857A] dark:text-[#868778]">{{ $isFr ? 'Sur' : 'Based on' }} {{ $apReviews['count'] }} {{ $isFr ? 'avis' : 'reviews' }}</p>
                </div>
                <div class="min-w-0 flex-1">
                    @foreach([5, 4, 3, 2, 1] as $star)
                    @php
                        $n = (int) ($apReviews['distribution'][$star] ?? 0);
                        $pct = $apReviews['count'] > 0 ? round($n / $apReviews['count'] * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-2.5 h-[24px]">
                        <span class="w-[58px] shrink-0 text-[13px] md:text-[11px] font-normal text-[#616469] dark:text-[#B4B5A6]">{{ $star }} {{ $isFr ? 'Étoiles' : 'Stars' }}</span>
                        <span class="flex-1 h-[7px] rounded-full bg-[#F2EEE4] dark:bg-[#0A0C09] overflow-hidden">
                            <span class="block h-full rounded-full bg-[#E29A08]" style="width: {{ $pct }}%"></span>
                        </span>
                        <span class="w-[26px] shrink-0 text-right text-[13px] md:text-[11px] text-[#3C4145] dark:text-[#F3EFE7]">{{ $n }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- The artwork's written review: avatar, name, a badge, the date,
                 a star row and the body. Everything here is a column on the
                 review row or on its author's account.

                 Two things the artwork shows are not rendered, because nothing
                 behind them exists. The reviewer's photograph: `users.avatar`
                 is not among the columns the register hands back, so the
                 initial stands in rather than a stock face. And the product
                 thumbnail beside the text: reviews on this platform are written
                 about the artisan and are not keyed to a piece, so there is no
                 piece to show — picking one of this shop's carvings would
                 attribute a stranger's sentence to a work they never named. --}}
            @if($reviewRows->isNotEmpty())
            <ul class="mt-5 pt-4 border-t border-[#F2EEE4] dark:border-[#262B21] space-y-4">
                @foreach($reviewRows as $r)
                <li class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-full bg-[#F3E7CB] dark:bg-[#3A2B06] text-[#8A6D1F] dark:text-[#EDB33A] flex items-center justify-center shrink-0 text-[13px] font-bold">
                        {{ mb_strtoupper(mb_substr((string) ($r->reviewer_name ?: '?'), 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $r->reviewer_name ?: ($isFr ? 'Compte supprimé' : 'Deleted account') }}</span>
                                @if(! empty($r->is_verified_contact) && $reviewBadgeLabel !== '')
                                <span class="ui-pill ui-pill-ok text-[12px] md:text-[9.5px]"><i data-lucide="message-square" class="w-3 h-3"></i>{{ $reviewBadgeLabel }}</span>
                                @endif
                            </div>
                            @if(! empty($r->published_at))
                            <span class="shrink-0 text-[12px] md:text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ \Illuminate\Support\Carbon::parse($r->published_at)->locale($lang)->translatedFormat('d/m/Y') }}</span>
                            @endif
                        </div>

                        <p class="mt-1 flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                            <svg viewBox="0 0 20 20" class="w-[12.5px] h-[12.5px] {{ $i <= (int) $r->rating ? 'fill-[#E29A08]' : 'fill-[#E6E1D6]' }}"><path d="M10 1.6 12.5 7l5.9.5-4.5 3.9 1.4 5.8L10 14.1l-5.3 3.1 1.4-5.8L1.6 7.5 7.5 7z"/></svg>
                            @endfor
                        </p>

                        @if(trim((string) $r->title) !== '')
                        <p class="mt-1.5 text-[12px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $r->title }}</p>
                        @endif
                        @if(trim((string) $r->body) !== '')
                        <p class="mt-1 text-[15px] md:text-[11.5px] leading-relaxed text-[#3B382F] dark:text-[#F3EFE7]">{{ $r->body }}</p>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>

            @if(($apReviews['count'] ?? 0) > $reviewRows->count())
            <p class="mt-3 text-[13px] md:text-[11px] text-[#8A857A] dark:text-[#868778]">
                {{ $isFr ? 'et' : 'and' }} {{ $apReviews['count'] - $reviewRows->count() }}
                {{ $isFr ? 'autre(s) avis publié(s).' : 'other published review(s).' }}
            </p>
            @endif

            <p class="mt-3 text-[12px] md:text-[10.5px] leading-relaxed text-[#8A857A] dark:text-[#868778]">
                {{ $isFr
                   ? "Chaque avis est lu par un modérateur avant publication. Le badge atteste d'un échange par la messagerie de la plateforme — il n'atteste d'aucun achat : la plateforme n'est pas partie aux ventes."
                   : 'Every review is read by a moderator before publication. The badge attests to an exchange through the platform’s own messaging — it attests to no purchase: the platform is not party to sales.' }}
            </p>
            @endif
            @else
            <p class="text-[12.5px] leading-relaxed ap-absent">
                {{ $isFr
                   ? "Personne n'a encore laissé d'avis sur cet artisan. Aucune note n'est affichée tant qu'il n'y a rien à compter."
                   : 'Nobody has reviewed this artisan yet. No rating is shown while there is nothing to count.' }}
            </p>
            @if(trim((string) ($apReviews['mean']['basis'] ?? '')) !== '')
            <p class="mt-2 text-[13px] md:text-[11px] leading-relaxed text-[#8A857A] dark:text-[#868778]">{{ $apReviews['mean']['basis'] }}</p>
            @endif
            @endif
        </section>

        {{-- Artisan statistics.
             The design lists thirteen rows and gives every one a number. Roughly
             half are measurable here. The rest — products sold, happy customers,
             response rate, last active, repeat buyers — have no source at all:
             this platform records no orders, no customers and no message
             response times. They stay in the list, because dropping them would
             leave the impression the figures are simply zero today, but each one
             names itself as untracked and carries the register's reason. --}}
        @php
            $statRows = [
                ['products_created',    $isFr ? 'Produits créés' : 'Products created',        'package'],
                ['products_published',  $isFr ? 'Produits publiés' : 'Products published',    'layout-grid'],
                ['certificates_issued', $isFr ? 'Certificats émis' : 'Certificates issued',   'badge-check'],
                ['exhibitions',         $isFr ? 'Expositions' : 'Exhibitions',                'landmark'],
                ['countries_reached',   $isFr ? 'Pays atteints' : 'Countries reached',        'globe'],
                ['reviews_published',   $isFr ? 'Avis publiés' : 'Reviews published',         'star'],
                ['profile_views',       $isFr ? 'Vues du profil' : 'Profile views',           'eye'],
                ['response_time_hours', $isFr ? 'Délai de réponse' : 'Response time',         'clock'],
                ['products_sold',       $isFr ? 'Produits vendus' : 'Products sold',          'shopping-bag'],
                ['happy_customers',     $isFr ? 'Clients satisfaits' : 'Happy customers',     'smile'],
                ['response_rate',       $isFr ? 'Taux de réponse' : 'Response rate',          'percent'],
                ['repeat_buyers',       $isFr ? 'Clients fidèles' : 'Repeat buyers',          'repeat'],
                ['last_active',         $isFr ? 'Dernière activité' : 'Last active',          'activity'],
            ];
        @endphp
        <section class="col-span-12 md:col-span-6 xl:col-span-3 ui-card">
            <h2 class="ap-sec-title mb-4"><i data-lucide="bar-chart-3" class="w-4 h-4"></i>{{ $isFr ? "Statistiques de l'artisan" : 'Artisan statistics' }}</h2>
            <ul class="divide-y divide-[#F5F1E8] dark:divide-[#262B21]">
                @foreach($statRows as [$sKey, $sLabel, $sIcon])
                @php $s = $apStats[$sKey] ?? null; @endphp
                @if(is_array($s))
                <li class="flex items-center gap-3 py-2.5 first:pt-0">
                    <i data-lucide="{{ $sIcon }}" class="w-[14px] h-[14px] text-[#C9942E] shrink-0"></i>
                    <span class="min-w-0 flex-1 text-[12px] text-[#3B382F] dark:text-[#F3EFE7]">{{ $sLabel }}</span>
                    @if($statKnown($s))
                    <span class="shrink-0 text-[13px] font-bold text-[#1D1B16] dark:text-[#F3EFE7]" @if(trim((string) ($s['basis'] ?? '')) !== '') title="{{ $s['basis'] }}" @endif>{{ $statText($s) }}</span>
                    @else
                    {{-- Never "0", and never omitted: a counter reading zero is a
                         claim about this artisan, a counter the platform does
                         not keep is a claim about the platform. --}}
                    <span class="shrink-0 text-[13px] md:text-[11px] ap-absent" @if(trim((string) ($s['basis'] ?? '')) !== '') title="{{ $s['basis'] }}" @endif>{{ $notTracked }}</span>
                    @endif
                </li>
                @endif
                @endforeach
            </ul>
        </section>

        {{-- Achievements.
             The design lists a SIARC Excellence Award, a National Craft
             Excellence Award attributed to a ministry, a UNESCO Craft
             Recognition and an African Heritage Expo placing. business_awards
             is empty, and these are honours conferred by real external bodies
             that this platform holds no register of. Printing one would invent
             a national distinction for a named person; this project has already
             had external honours stripped from its certificates once. --}}
        <section class="col-span-12 md:col-span-6 xl:col-span-4 ui-card">
            <h2 class="ap-sec-title mb-4"><i data-lucide="award" class="w-4 h-4"></i>{{ $isFr ? 'Distinctions' : 'Achievements' }}</h2>

            @php
                /* ArtisanAwards carries the evidence pointer as well as the
                   title, so it is preferred where it answered; ArtisanProfile's
                   list is the fallback. The artwork shows four rows and a "view
                   all" button, so four is what is shown and the button appears
                   only when there is a fifth. */
                $awards = $awardRows->isNotEmpty() ? $awardRows : collect($apAwards['items'] ?? []);
            @endphp
            @if($awards->isNotEmpty())
            <ul class="space-y-3.5">
                @foreach($awards as $awIndex => $award)
                <li class="flex items-start gap-2.5 {{ $awIndex >= 4 ? 'ap-award-extra hidden' : '' }}">
                    <span class="w-7 h-7 rounded-lg bg-[#FBF1DD] dark:bg-[#3A2B06] text-[#8A6D1F] dark:text-[#EDB33A] flex items-center justify-center shrink-0">
                        <i data-lucide="medal" class="w-3.5 h-3.5"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[12px] font-semibold leading-snug text-[#1D1B16] dark:text-[#F3EFE7]">
                            {{ $award['title'] }}@if(! empty($award['year']))<span class="text-[#8A857A] dark:text-[#868778]"> — {{ $award['year'] }}</span>@endif
                        </p>
                        @if(! empty($award['issuer']))
                        <p class="mt-0.5 text-[12px] md:text-[10.5px] text-[#8A857A] dark:text-[#868778]">{{ $award['issuer'] }}</p>
                        @endif
                        {{-- The one thing that makes an outside body's honour
                             checkable by somebody other than us. Rendered when
                             the reviewer who recorded it left a pointer. --}}
                        @if(! empty($award['evidence_url']))
                        <a href="{{ $award['evidence_url'] }}" target="_blank" rel="noopener nofollow"
                           class="mt-1 inline-flex items-center gap-1 text-[12px] md:text-[10px] font-semibold text-[#8A6D1F] dark:text-[#EDB33A] hover:underline">
                            <i data-lucide="external-link" class="w-3 h-3"></i>{{ $isFr ? 'Justificatif' : 'Evidence' }}
                        </a>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>

            @if($awards->count() > 4)
            <button type="button" id="ap-awards-all"
                    class="ui-btn ui-btn-block mt-4 bg-transparent text-[#8A6D1F] dark:text-[#EDB33A] border-[#E2CD9B] dark:border-[#6A5210] hover:bg-[#FBF7EC] hover:dark:bg-[#0A0C09] text-[12px] md:text-[10.5px] font-bold uppercase tracking-[.06em]">
                {{ $isFr ? 'Voir toutes les distinctions' : 'View all achievements' }} ({{ $awards->count() }})
            </button>
            @endif

            {{-- Whose statement this is. The row says an outside body honoured
                 this artisan; the platform is repeating it, not confirming it. --}}
            <p class="mt-3 text-[12px] md:text-[10.5px] leading-relaxed text-[#8A857A] dark:text-[#868778]">
                {{ $isFr
                   ? "Distinctions saisies au dossier depuis des justificatifs, et rapportées telles quelles. La plateforme ne vérifie pas l'organisme décernant."
                   : 'Distinctions recorded on file from evidence and reported verbatim. The platform does not verify the awarding body.' }}
            </p>
            @else
            <p class="text-[12px] leading-relaxed ap-absent">
                {{ $isFr
                   ? "Aucune distinction n'est enregistrée pour cet artisan."
                   : 'No award is recorded for this artisan.' }}
            </p>
            <p class="mt-2 text-[13px] md:text-[11px] leading-relaxed text-[#8A857A] dark:text-[#868778]">
                {{ $isFr
                   ? "Les distinctions sont conférées par des organismes extérieurs, dont la plateforme ne tient pas registre. Seul ce qui a été fourni et vérifié figure ici."
                   : 'Awards are conferred by outside bodies, of which the platform keeps no register. Only what has been supplied and checked appears here.' }}
            </p>
            @endif
        </section>
    </div>


</div>

{{-- ── Trust bar ───────────────────────────────────────────────────────
     The design closes with a five-panel cream band reading:

         AUTHENTIC & VERIFIED — Every artisan is verified
         SECURE PAYMENTS      — 100% secure transactions
         WORLDWIDE SHIPPING   — Safe & reliable delivery
         BUYER PROTECTION     — Money-back guarantee
         SUPPORT ARTISANS     — Empowering communities

     Three of those five are things this operator does not do, and they are not
     small print — they sit at the foot of the page a buyer reads just before
     deciding to send money to a stranger, which is precisely when they are
     relied upon.

     config/legal.php, "Paiements": the platform does not receive the price of
     the sale and offers no escrow; settlement happens directly between buyer and
     seller. So SECURE PAYMENTS describes a transaction the platform never sees.

     config/legal.php, "Nous sommes un intermédiaire": each sale is a direct
     contract between buyer and seller, we are not a party to it, we do not
     guarantee it, and "nous ne pouvons pas récupérer des fonds versés hors de
     la plateforme". A money-back guarantee is therefore not merely unsupported,
     it is a promise the operator has already written down that it cannot keep.
     It is the single most damaging line on this page and it is gone.

     Same document: "Nous ne fabriquons, ne stockons, n'inspectons et
     n'expédions aucun produit." WORLDWIDE SHIPPING is not ours to say.

     The band is kept — it does real work, closing the page on why any of this
     is trustworthy — and refilled with five things that are true and checkable.
--}}
@php
    $trustBar = [
        [
            'icon'  => 'badge-check',
            'title' => $isFr ? 'Artisans vérifiés' : 'Verified artisans',
            'body'  => $isFr
                ? "Les pièces d'identité et documents d'activité sont reçus et contrôlés avant qu'un profil ne soit marqué vérifié."
                : 'Identity and trade documents are received and checked before a profile is marked verified.',
        ],
        [
            'icon'  => 'search-check',
            'title' => $isFr ? 'Certificats vérifiables' : 'Checkable certificates',
            'body'  => $isFr
                ? "Chaque numéro de certificat affiché ici se vérifie de façon indépendante, sans passer par l'artisan."
                : 'Every certificate number shown here can be checked independently, without going through the artisan.',
        ],
        [
            'icon'  => 'history',
            'title' => $isFr ? 'Provenance enregistrée' : 'Provenance recorded',
            'body'  => $isFr
                ? "Les pièces inscrites au registre conservent un historique daté : création, expositions, transferts."
                : 'Pieces on the register keep a dated history: making, exhibitions, transfers of hands.',
        ],
        [
            // The honest counterpart of "secure payments": not a guarantee, a
            // description of who holds the money, which is what a buyer needs.
            'icon'  => 'handshake',
            'title' => $isFr ? "L'artisan est payé directement" : 'The artisan is paid directly',
            'body'  => $isFr
                ? "La vente est un contrat entre vous et l'artisan. La plateforme n'y est pas partie, ne reçoit pas le prix et ne détient aucun fonds."
                : 'The sale is a contract between you and the artisan. The platform is not a party to it, does not receive the price and holds no funds.',
        ],
        [
            'icon'  => 'users',
            'title' => $isFr ? 'Soutenir les artisans' : 'Support artisans',
            'body'  => $isFr
                ? "Le contact et la négociation se font en direct, sans intermédiaire commercial entre vous et l'atelier."
                : 'Contact and negotiation happen directly, with no commercial intermediary between you and the workshop.',
        ],
    ];
@endphp
<section class="mt-10 border-y border-[#EFE7D4] dark:border-[#6A5210] bg-[#F9F6EF] dark:bg-[#0A0C09]">
    <div class="max-w-[1280px] mx-auto px-[25px] py-6">
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-x-6 gap-y-5">
            @foreach($trustBar as $tb)
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-[#F3E7CB] dark:bg-[#3A2B06] border border-[#E2CD9B] dark:border-[#6A5210] text-[#8A6D1F] dark:text-[#EDB33A] flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $tb['icon'] }}" class="w-[18px] h-[18px]"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[13px] md:text-[11.5px] font-bold uppercase tracking-[.05em] leading-snug text-[#1D1B16] dark:text-[#F3EFE7]">{{ $tb['title'] }}</p>
                    <p class="mt-1 text-[12px] md:text-[10.5px] leading-relaxed text-[#6F6B60] dark:text-[#868778]">{{ $tb['body'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Said once, plainly, rather than implied by five reassuring icons.
             A "verified" badge is a document check, not a warranty of the goods
             or of the order — config/legal.php is explicit on that, and a buyer
             who misreads it is the person who bears the cost. --}}
        <p class="mt-5 pt-4 border-t border-[#EADFC6] dark:border-[#6A5210] text-[12px] md:text-[10.5px] leading-relaxed text-[#8A857A] dark:text-[#868778]">
            {{ $isFr
               ? "Artisan Hub 237 est une société privée, sans affiliation ministérielle ou publique. La vérification atteste d'un contrôle documentaire : ce n'est ni une garantie de qualité des produits, ni une garantie de bonne exécution d'une commande, ni une caution financière."
               : 'Artisan Hub 237 is a private company with no ministerial or public affiliation. Verification attests to a document check: it is not a guarantee of product quality, of an order being properly fulfilled, or a financial guarantee.' }}
            <a href="{{ route('legal.show', ['doc' => 'avertissement', 'lang' => $lang]) }}" class="font-semibold text-[#8A6D1F] dark:text-[#EDB33A] hover:underline">{{ $isFr ? 'Lire l\'avertissement' : 'Read the disclaimer' }}</a>
        </p>
    </div>
</section>
</main>

@include('pages.partials.directory-footer')

</div>{{-- /desktop --}}

<script>
    lucide.createIcons();
    const awardsAll = document.getElementById('ap-awards-all');
    if (awardsAll) awardsAll.addEventListener('click', () => {
        document.querySelectorAll('.ap-award-extra').forEach(el => el.classList.remove('hidden'));
        awardsAll.remove();
    });
    const mBtn = document.getElementById('mobile-menu-btn');
    const mMenu = document.getElementById('mobile-menu');
    if (mBtn && mMenu) mBtn.addEventListener('click', () => mMenu.classList.toggle('hidden'));
</script>
</body>
</html>
