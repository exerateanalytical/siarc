{{-- ============================================================================
     Mobile artisan profile — app-shell replica of
     `certificates/artisan mpbile profile v2.png` (864×1821, i.e. 432×910.5 CSS).

     This is not a narrow rendering of the desktop page. The design is a
     different document: a phone application shell with its own top bar, a dark
     hero card, horizontally scrolled certificate shields and a fixed bottom
     navigation. Sharing a template between the two would have meant a template
     of conditionals with no single shape, so this is a sibling.

     INCLUDE CONTRACT — the parent needs exactly this, and nothing else:

         <div class="lg:hidden">
             @include('pages.businesses.partials.show-mobile')
         </div>

     `$business` (with industry, city, region and products.primaryImage loaded),
     `$lang`, and optionally `$profile` (an App\Support\ArtisanProfile, or null)
     are taken from the parent scope. The partial writes nothing outside its own
     `[data-mobile-profile]` wrapper, defines no globals, and re-includes the UI
     kit under @once so it is safe whether or not the parent already did.

     ArtisanProfile is being written in parallel, so every read of it goes
     through the small resolvers below: when the object is present its answer
     wins, and when it is absent the same fact is derived from the business row.
     That keeps this file renderable today and correct the moment the class
     lands, at the cost of a little duplication.

     WHAT THE DESIGN CLAIMS THAT THIS DOES NOT PRINT
     The mockup carries a trust score of 92/100, a customer rating of 4.9 over
     128 reviews, a star line under every product card, a row of SIARC and
     UNESCO honours, a cart, and a notification bell reading 3. `business_reviews`
     and `business_awards` are empty; ratings have never been stored per product
     (a review attaches to a business); there is no cart table and the operator
     is not party to the sale. Each of those is rendered here as a stated
     absence or omitted. A figure invented to fill a slot in a mockup is read by
     a buyer as a measurement, and the cost of being wrong falls on them.
     ============================================================================ --}}
@include('pages.partials.ui-kit')

@php
    $isFr = $lang === 'fr';

    /*
     * ArtisanProfile is entirely static, and this partial was written against a
     * $profile instance while that class was still being built in parallel. The
     * instance call always found nothing and fell back in silence, so the whole
     * sheet was rendering from the business row rather than from the register —
     * a failure that looked like a sparse profile rather than a broken one.
     *
     * It now calls the class directly and passes $lang, because every method
     * there resolves a null language to the request locale, which is French: an
     * English reader was being handed French certificate names and bases.
     * The guard stays so a mid-edit class still degrades rather than 500s.
     */
    $ask = function (string $method, $fallback = null, array $args = []) use ($business, $lang) {
        $class = \App\Support\ArtisanProfile::class;

        if (! class_exists($class) || ! method_exists($class, $method)) {
            return $fallback;
        }

        try {
            return $class::{$method}($business, ...array_merge($args, [$lang])) ?? $fallback;
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    /*
     * Image paths on this record come from two places: uploads live under the
     * storage disk, while seeded artwork is committed straight into
     * `public/images/…`. Prefixing the latter with `storage/` yields a 404 and
     * an empty hero, so the prefix is decided by the path itself rather than
     * assumed. Absolute URLs are passed through untouched.
     */
    $mAsset = function (?string $path): ?string {
        if (! $path) {
            return null;
        }
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '//', '/'])) {
            return $path;
        }

        return \Illuminate\Support\Str::startsWith($path, ['images/', 'img/', 'assets/'])
            ? asset($path)
            : asset('storage/' . $path);
    };

    $mName = $isFr ? $business->name_fr : ($business->name_en ?? $business->name_fr);
    $mTagline = $isFr ? $business->tagline_fr : ($business->tagline_en ?? $business->tagline_fr);
    $mIndustry = $business->industry ? ($isFr ? $business->industry->name_fr : ($business->industry->name_en ?? $business->industry->name_fr)) : null;
    $mCity = $business->city?->name_fr;
    $mRegion = $business->region ? ($isFr ? $business->region->name_fr : ($business->region->name_en ?? $business->region->name_fr)) : null;

    // Coarse location only. gps_lat/gps_lng locate a person's home workshop to
    // a few metres; the town and region are what a buyer actually needs.
    $mPlace = collect([$mCity, $mRegion])->filter()->implode(', ') ?: null;

    $mVerified = in_array($business->verification_tier, ['verified', 'certified'], true);
    /* workshop() returns the register's array (name, level, place) or null;
       the row here wants a name. The address fallback stays for shops with no
       registered workshop — it is the artisan's own published wording. */
    $mWorkshopData = $ask('workshop');
    $mWorkshop = is_array($mWorkshopData)
        ? ($mWorkshopData['name'] ?? null)
        : ($mWorkshopData ?: ($business->address_fr || $business->address_en ? ($isFr ? $business->address_fr : ($business->address_en ?? $business->address_fr)) : null));
    $mYears = $business->year_established ? max(0, (int) date('Y') - (int) $business->year_established) : null;
    $mMemberSince = $business->created_at;

    /* Reviews: the register, not a design figure. ArtisanProfile::reviews()
       owns the count, the mean and the five-bucket distribution — it excludes
       flagged and hidden rows and refuses a mean below the minimum sample, and
       none of that should be re-derived here. The rows themselves are read from
       the relation, which is already scoped to `published`, because the
       register returns aggregates and the panel prints written reviews. */
    $mReviews = $business->relationLoaded('reviews') ? $business->reviews : $business->reviews()->get();
    $mReviewData = $ask('reviews');
    $mReviewCount = is_array($mReviewData) ? (int) ($mReviewData['count'] ?? 0) : $mReviews->count();
    $mReviewDist = is_array($mReviewData) ? ($mReviewData['distribution'] ?? []) : $mReviews->groupBy('rating')->map->count()->all();
    $mReviewMean = is_array($mReviewData) && ($mReviewData['mean']['known'] ?? false)
        ? (float) $mReviewData['mean']['value']
        : null;
    // The mean is stated only when the register vouches for it; without the
    // register there is no fallback average, because a mean of one review is
    // not a rating and this file is not the place that decides the threshold.
    $mRating = $mReviewMean !== null ? number_format($mReviewMean, 1, $isFr ? ',' : '.', ' ') : null;
    $mReviewBasis = is_array($mReviewData) ? ($mReviewData['mean']['basis'] ?? null) : null;

    /* Trust score. ArtisanProfile::trustScore() is expected to return the same
       ['value','basis','known'] shape as a statistic. Until it exists there is
       no scoring model on this platform, so the panel states that rather than
       borrowing the mockup's 92. */
    $mTrust = $ask('trustScore', ['value' => null, 'basis' => null, 'known' => false]);
    $mTrustKnown = (bool) ($mTrust['known'] ?? false);

    $mNotTracked = $isFr ? 'Non suivi' : 'Not tracked';

    /* Certificates: the four registers that actually issue documents for a
       business. Each row is a real certificate number that the public verifier
       can resolve — the strip is not decorative. */
    /*
     * ArtisanProfile groups certificates by type -- ['avc' => ['name', 'issued',
     * 'items' => [...]], ...] -- while this strip renders one card per issued
     * document. Flattening here rather than in the markup keeps the shape
     * mismatch in one place: the register is the authority on what exists, and
     * a view should not be reaching into two levels of its structure inline.
     */
    $mCerts = null;
    $mCertsGrouped = $ask('certificates');

    if (is_array($mCertsGrouped)) {
        $mCerts = collect($mCertsGrouped)
            ->filter(fn ($g) => is_array($g) && ! empty($g['items']))
            ->flatMap(fn ($g) => collect($g['items'])->map(fn ($i) => (object) [
                'code'      => strtoupper($g['type'] ?? ''),
                'name'      => $g['name'] ?? '',
                'no'        => $i['number'] ?? null,
                'issued_at' => $i['issued_at'] ?? null,
                'status'    => $i['status'] ?? 'active',
                'url'       => $i['url'] ?? null,
            ]))
            ->values();
    }

    if ($mCerts === null) {
        $mCerts = collect();
        foreach (\Illuminate\Support\Facades\DB::table('artisan_verifications')
            ->where('business_id', $business->id)->where('status', 'active')
            ->orderByDesc('issued_at')->get(['certificate_no', 'issued_at']) as $row) {
            $mCerts->push([
                'code' => 'AVC',
                'name' => $isFr ? "Certificat de vérification d'artisan" : 'Artisan Verification Certificate',
                'no' => $row->certificate_no,
                'issued_at' => $row->issued_at,
                'status' => 'active',
            ]);
        }
        foreach (\Illuminate\Support\Facades\DB::table('product_certificates as pc')
            ->where('pc.business_id', $business->id)->whereNull('pc.revoked_at')
            ->orderByDesc('pc.issued_at')->limit(6)->get(['pc.certificate_no', 'pc.issued_at']) as $row) {
            $mCerts->push([
                'code' => 'PRC',
                'name' => $isFr ? "Certificat d'enregistrement produit" : 'Product Registration Certificate',
                'no' => $row->certificate_no,
                'issued_at' => $row->issued_at,
                'status' => 'active',
            ]);
        }
        $mCerts = $mCerts->map(fn ($c) => (object) $c);
    }
    $mCerts = collect($mCerts);

    /* Products: this shop's own published products only. The desktop page tops
       its grid up with other vendors' goods; on a profile card headed FEATURED
       PRODUCTS that would attribute another artisan's work to this one. */
    $mProductsPayload = $ask('products', null, [12]);
    $mProducts = is_array($mProductsPayload) ? ($mProductsPayload['items'] ?? null) : $mProductsPayload;

    if ($mProducts === null) {
        $mProducts = $business->products->where('status', 'published')->sortBy('sort_order')->take(4)->values();
    }
    $mProducts = collect($mProducts);

    $mAwardsPayload = $ask('awards', ['items' => []]);
    $mAwards = collect(is_array($mAwardsPayload) ? ($mAwardsPayload['items'] ?? []) : $mAwardsPayload);

    /* Statistics arrive as ['value','basis','known']; a false `known` is a
       different fact from a zero and is printed as such. */
    $mStats = $ask('statistics');
    if ($mStats === null) {
        $mStats = [
            ($isFr ? 'Produits publiés' : 'Published products') => [
                'value' => $business->products->where('status', 'published')->count(),
                'basis' => $isFr ? 'Fiches produits publiées' : 'Published product listings',
                'known' => true,
            ],
            ($isFr ? 'Vues du profil' : 'Profile views') => [
                'value' => (int) $business->views_count,
                'basis' => $isFr ? 'Consultations enregistrées' : 'Recorded page views',
                'known' => true,
            ],
            ($isFr ? 'Ventes' : 'Sales') => [
                'value' => null,
                'basis' => $isFr ? "La plateforme n'est pas partie à la vente" : 'The platform is not party to the sale',
                'known' => false,
            ],
            ($isFr ? 'Délai de réponse' : 'Response time') => [
                'value' => $business->response_time_hours ? $business->response_time_hours . ' h' : null,
                'basis' => $isFr ? 'Déclaré par l’artisan' : 'Stated by the artisan',
                'known' => (bool) $business->response_time_hours,
            ],
        ];
    }

    /* The register keys its statistics by machine name — `products_published`,
       `countries_reached` — and the panel was printing those keys raw. The map
       is presentation only: a key with no entry here is title-cased rather than
       dropped, so a counter added to the register still appears. */
    $mStatLabels = [
        'products_created'    => [$isFr ? 'Fiches produits' : 'Products created', 'package'],
        'products_published'  => [$isFr ? 'Produits publiés' : 'Products published', 'package-check'],
        'certificates_issued' => [$isFr ? 'Certificats délivrés' : 'Certificates issued', 'shield-check'],
        'exhibitions'         => [$isFr ? 'Expositions' : 'Exhibitions', 'landmark'],
        'countries_reached'   => [$isFr ? 'Pays atteints' : 'Countries reached', 'globe'],
        'profile_views'       => [$isFr ? 'Vues du profil' : 'Profile views', 'eye'],
        'reviews_published'   => [$isFr ? 'Avis publiés' : 'Reviews published', 'message-square'],
        'response_time_hours' => [$isFr ? 'Délai de réponse (h)' : 'Response time (h)', 'clock'],
        'products_sold'       => [$isFr ? 'Pièces vendues' : 'Pieces sold', 'shopping-bag'],
        'happy_customers'     => [$isFr ? 'Clients' : 'Customers', 'users'],
        'response_rate'       => [$isFr ? 'Taux de réponse' : 'Response rate', 'percent'],
        'positive_reviews'    => [$isFr ? 'Avis positifs' : 'Positive reviews', 'thumbs-up'],
        'last_active'         => [$isFr ? 'Dernière activité' : 'Last active', 'activity'],
        'repeat_buyers'       => [$isFr ? 'Acheteurs fidèles' : 'Repeat buyers', 'repeat'],
    ];

    /* Identity, for the ABOUT facts column. The design's four rows are
       Nationality, Languages, Specialization and Years Experience; the register
       answers all four and reports the absence of each separately, so a row is
       printed only where `known` is true. Nationality in particular is not a
       column on this schema — the register returns the registered workshop's
       country, which is the nearest fact the platform actually holds. */
    $mIdentity = $ask('identity');
    $mFact = function (string $key) use ($mIdentity) {
        $f = is_array($mIdentity) ? ($mIdentity[$key] ?? null) : null;

        return is_array($f) && ($f['known'] ?? false) ? $f : null;
    };

    // Unread notifications are a real feature with a real count; the badge is
    // drawn from it or not at all.
    $mUser = session('siac_user');
    $mUnread = $mUser
        ? (int) \Illuminate\Support\Facades\DB::table('user_notifications')->where('user_id', $mUser['id'] ?? 0)->whereNull('read_at')->count()
        : 0;

    $mDesc = $isFr ? $business->description_fr : ($business->description_en ?? $business->description_fr);

    /* Five tabs, as the design has them. The reviews tab carries its count in
       the label — the design prints "REVIEWS (128)" — but only once the
       register has counted something; "(0)" beside a tab reads as a verdict on
       the artisan rather than as an empty table. */
    $mTabs = array_values(array_filter([
        ['about', $isFr ? 'À PROPOS' : 'ABOUT', 'info'],
        ['workshop', $isFr ? 'ATELIER' : 'WORKSHOP', 'hammer'],
        ['stats', $isFr ? 'CHIFFRES' : 'STATS', 'bar-chart-3'],
        ['reviews', ($isFr ? 'AVIS' : 'REVIEWS') . ($mReviewCount > 0 ? ' (' . $mReviewCount . ')' : ''), 'star'],
        ['awards', $isFr ? 'DISTINCTIONS' : 'ACHIEVEMENTS', 'trophy'],
    ]));

    /* The facts table. Every row is dropped when its field is empty rather than
       printed with a dash, because four rows of "—" reads as a neglected
       profile where three real rows read as a short one.

       The register answers first and the business row is the fallback, so the
       column reads the same four facts the design lists wherever ArtisanProfile
       can supply them. */
    $mLanguages = $mFact('languages');
    $mSpecial = $mFact('specialisation');
    $mCountry = $mFact('country');
    $mYearsFact = $mFact('years_experience');

    $mFacts = array_values(array_filter([
        /* The register stores the workshop country as an ISO code; "CM" in a
           facts column is a database value, not an answer. The map names the
           codes this platform actually issues against and falls back to the
           code itself, so an unmapped country is still printed rather than
           swapped for a guess. */
        $mCountry
            ? ['flag', $isFr ? 'Nationalité' : 'Nationality',
               [
                   'CM' => $isFr ? 'Cameroun' : 'Cameroon',
                   'FR' => $isFr ? 'France' : 'France',
                   'NG' => $isFr ? 'Nigéria' : 'Nigeria',
                   'TD' => $isFr ? 'Tchad' : 'Chad',
                   'GA' => $isFr ? 'Gabon' : 'Gabon',
                   'CF' => $isFr ? 'République centrafricaine' : 'Central African Republic',
                   'GQ' => $isFr ? 'Guinée équatoriale' : 'Equatorial Guinea',
                   'CG' => $isFr ? 'Congo' : 'Congo',
               ][strtoupper((string) $mCountry['value'])] ?? $mCountry['value'],
               $mCountry['basis'] ?? null]
            : null,

        $mLanguages
            ? ['languages', $isFr ? 'Langues' : 'Languages', collect((array) $mLanguages['value'])->filter()->implode(', '), $mLanguages['basis'] ?? null]
            : ($business->languages_spoken ? ['languages', $isFr ? 'Langues' : 'Languages', collect($business->languages_spoken)->filter()->implode(', '), null] : null),

        $mSpecial
            ? ['clipboard-check', $isFr ? 'Spécialisation' : 'Specialization', $mSpecial['value'], $mSpecial['basis'] ?? null]
            : ($mIndustry ? ['clipboard-check', $isFr ? 'Spécialisation' : 'Specialization', $mIndustry, null] : null),

        $mYearsFact
            ? ['hammer', $isFr ? 'Années d’expérience' : 'Years Experience', $mYearsFact['value'] . ' ' . ($isFr ? 'ans' : 'yrs'), $mYearsFact['basis'] ?? null]
            : ($mYears !== null ? ['hammer', $isFr ? 'Années d’expérience' : 'Years Experience', $mYears . ' ' . ($isFr ? 'ans' : 'yrs'), null] : null),

        $mRegion ? ['map', $isFr ? 'Région' : 'Region', $mRegion, null] : null,
        $business->employee_count ? ['users', $isFr ? 'Artisans à l’atelier' : 'Workshop size', $business->employee_count, null] : null,
    ]));

    /* The hero backdrop. The design fills the right half of the card with a
       photograph of the artisan's work; the record's cover image is that
       photograph where one has been uploaded, and the first gallery frame is
       the next best thing the shop has actually published. Neither is invented:
       with no image on file the card keeps the gradient alone. */
    $mHeroArt = $mAsset($business->cover_image);

    if (! $mHeroArt) {
        $mGalleryRow = \Illuminate\Support\Facades\DB::table('business_gallery')
            ->where('business_id', $business->id)->where('type', 'image')
            ->orderBy('sort_order')->orderBy('id')->first(['file_path']);
        $mHeroArt = $mGalleryRow ? $mAsset($mGalleryRow->file_path) : null;
    }
@endphp

<div data-mobile-profile class="mob-root">
<style>
    /* Scoped to [data-mobile-profile] so nothing here can reach the desktop
       document that includes it. Plain CSS for the same reason as the UI kit:
       Tailwind is a runtime CDN bundle here, so @apply is unavailable. */
    /* Every value below is the sampled colour table in
       docs/ARTISAN-PROFILE-V2-SPEC.md ("MEASURED TYPOGRAPHY, SPACING AND
       COLOUR — authoritative"), not an eyeball match. The one worth naming is
       --mob-card: the artwork's cards are two levels lighter than the page, and
       it is the border that separates them. Painting them plain white against
       cream is what made an earlier pass read as a generic admin theme. */
    [data-mobile-profile] {
        --mob-page:   #FCF9F6;
        --mob-card:   #FCFAF6;
        --mob-dark:   #0E0A03;
        --mob-dark2:  #070300;
        --mob-nav:    #02411D;
        --mob-pill:   #054821;
        --mob-gold:   #D3B030;   /* VERIFIED pill / portrait ring */
        --mob-gold2:  #C8860B;
        --mob-star:   #E29A08;   /* stars and rating-bar fill */
        --mob-green:  #14652F;   /* contact / action green */
        --mob-badge:  #003712;   /* product VERIFIED badge green */
        --mob-red:    #CC060E;
        --mob-line:   #EFEAE2;
        --mob-panel:  #070805;
        background: var(--mob-page);
        padding-bottom: 84px;      /* clears the fixed bottom bar + raised disc */
        font-family: inherit;
    }
    [data-mobile-profile] .mob-card {
        background: var(--mob-card);
        border: 1px solid var(--mob-line);
        border-radius: 16px;
        margin: 0 10px 12px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(20,16,8,.04);
    }
    [data-mobile-profile] .mob-sec-h {
        display: flex; align-items: center; gap: 9px;
        padding: 13px 13px 9px;
        font-size: 11px; font-weight: 700; letter-spacing: .06em;
        color: #113B22; text-transform: uppercase;
    }
    [data-mobile-profile] .mob-scroll {
        display: flex; gap: 9px; overflow-x: auto;
        padding: 0 14px 14px; scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }
    [data-mobile-profile] .mob-scroll::-webkit-scrollbar { display: none; }

    /* ── Hero: dark card, artwork/cover fading in from the right ── */
    [data-mobile-profile] .mob-hero {
        position: relative; margin: 0 10px 12px;
        border-radius: 20px; overflow: hidden;
        background: linear-gradient(118deg, var(--mob-dark) 0%, #150E04 55%, var(--mob-dark2) 100%);
        color: #fff;
        min-height: 236px;
    }
    [data-mobile-profile] .mob-hero-art {
        position: absolute; top: 0; right: 0; bottom: 0; width: 62%;
        object-fit: cover; object-position: center;
        -webkit-mask-image: linear-gradient(90deg, transparent 0%, rgba(0,0,0,.55) 38%, #000 72%);
                mask-image: linear-gradient(90deg, transparent 0%, rgba(0,0,0,.55) 38%, #000 72%);
    }
    [data-mobile-profile] .mob-hero-glow {
        position: absolute; top: 0; right: 0; bottom: 0; width: 62%;
        background:
            radial-gradient(120% 90% at 85% 30%, rgba(200,134,11,.32) 0%, rgba(200,134,11,.10) 45%, transparent 75%),
            linear-gradient(90deg, transparent 0%, rgba(60,38,8,.35) 60%, rgba(93,58,12,.45) 100%);
    }
    /* With no photograph on file the right half would be flat black, which
       reads as a broken image rather than as a card. The woven lozenge is
       drawn, not photographed, and claims nothing about the shop. */
    [data-mobile-profile] .mob-hero--bare .mob-hero-glow {
        background:
            repeating-linear-gradient(45deg, rgba(211,176,48,.10) 0 2px, transparent 2px 14px),
            repeating-linear-gradient(-45deg, rgba(211,176,48,.10) 0 2px, transparent 2px 14px),
            radial-gradient(120% 90% at 85% 30%, rgba(200,134,11,.34) 0%, rgba(200,134,11,.11) 48%, transparent 78%),
            linear-gradient(90deg, transparent 0%, rgba(60,38,8,.38) 60%, rgba(93,58,12,.5) 100%);
        -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 55%);
                mask-image: linear-gradient(90deg, transparent 0%, #000 55%);
    }
    [data-mobile-profile] .mob-hero-scrim {
        position: absolute; inset: 0;
        background: linear-gradient(90deg, var(--mob-dark) 34%, rgba(14,10,3,.72) 55%, rgba(14,10,3,.18) 100%);
    }
    /* Row pitch measured off the artwork: the four meta lines sit on a ~23px
       pitch at this width, where the previous pass had them on 29px and pushed
       the trust panel a whole line down the card. */
    [data-mobile-profile] .mob-hero-meta { display: flex; align-items: center; gap: 7px; font-size: 11px; line-height: 15px; color: #EFEADF; margin-top: 3px; }
    [data-mobile-profile] .mob-hero-meta i { width: 14px; height: 14px; color: var(--mob-gold2); flex: none; }

    [data-mobile-profile] .mob-trust {
        margin: 12px 0 0; border: 1px solid rgba(237,168,23,.38);
        border-radius: 14px; background: rgba(7,8,5,.92); padding: 12px 14px 13px;
        backdrop-filter: blur(2px);
    }
    [data-mobile-profile] .mob-trust-h { font-size: 10px; font-weight: 800; letter-spacing: .1em; color: var(--mob-gold); text-transform: uppercase; }
    [data-mobile-profile] .mob-star { width: 15px; height: 15px; color: var(--mob-star); fill: var(--mob-star); }

    /* ── Action row: white card, 4 equal columns split by hairlines ── */
    [data-mobile-profile] .mob-act { display: grid; }
    [data-mobile-profile] .mob-act > * {
        display: flex; flex-direction: column; align-items: center; gap: 7px;
        padding: 12px 2px 11px; font-size: 11px; font-weight: 600;
        color: #1B1B18; text-align: center;
        border-left: 1px solid var(--mob-line); background: none;
    }
    [data-mobile-profile] .mob-act > *:first-child { border-left: 0; }
    [data-mobile-profile] .mob-act i { width: 22px; height: 22px; }

    /* ── Certificate shield cards ── */
    [data-mobile-profile] .mob-cert {
        flex: 0 0 118px; scroll-snap-align: start;
        border: 1px solid var(--mob-line); border-radius: 12px;
        padding: 12px 8px 11px; text-align: center; background: var(--mob-card);
        box-shadow: 0 1px 2px rgba(20,16,8,.05);
    }
    /* Iridescent shield: layered conic/linear gradients clipped to a shield.
       Purely a visual treatment — never captioned as a hologram or a security
       feature; docs/PRINT-SECURITY-SPEC.md governs those claims. */
    [data-mobile-profile] .mob-shield {
        position: relative; width: 56px; height: 62px; margin: 0 auto;
        clip-path: polygon(50% 0%, 96% 12%, 96% 55%, 78% 86%, 50% 100%, 22% 86%, 4% 55%, 4% 12%);
        background:
            conic-gradient(from 210deg at 30% 25%, rgba(255,255,255,.55), transparent 28%),
            conic-gradient(from 20deg at 70% 70%, rgba(255,255,255,.35), transparent 30%),
            linear-gradient(135deg, #8A63D2 0%, #4FB6E0 30%, #6FE0C0 50%, #E8D25A 72%, #C98BD9 100%);
        display: flex; align-items: center; justify-content: center;
    }
    [data-mobile-profile] .mob-shield::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.5) 45%, transparent 60%);
    }
    [data-mobile-profile] .mob-shield img { width: 38px; height: 38px; border-radius: 50%; position: relative; }
    [data-mobile-profile] .mob-vpill {
        display: inline-flex; align-items: center; gap: 4px;
        border: 1px solid #CBE3D2; border-radius: 999px;
        padding: 2.5px 8px; font-size: 9.5px; font-weight: 700; color: var(--mob-badge);
        background: #F2FAF4;
    }

    /* ── Product grid ──
       Four across, which is what the artwork actually does: its tiles measure
       188/190 artwork px on a 16px gutter, with 13px and 14px of card padding
       either side. Symmetric padding and no clipped fifth tile is the tell that
       this is a fitted grid rather than the scroller the certificates strip
       uses, so a sixth piece wraps onto a second row instead of running off the
       edge. At this measure a tile is ~100px, and everything inside it — name,
       category, price, heart, cart — is sized to that, not to the 2-up tile it
       replaced. */
    [data-mobile-profile] .mob-prods { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 9px; padding: 0 13px 13px; }
    [data-mobile-profile] .mob-prod { min-width: 0; }
    [data-mobile-profile] .mob-prod .mob-prod-img {
        position: relative; display: block; border-radius: 10px; overflow: hidden;
        aspect-ratio: 1 / 1; background: #F6F1E8;
    }
    [data-mobile-profile] .mob-prod .mob-prod-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
    [data-mobile-profile] .mob-prod .mob-heart {
        position: absolute; top: 5px; right: 5px; width: 21px; height: 21px;
        border-radius: 50%; background: rgba(255,255,255,.92);
        display: flex; align-items: center; justify-content: center; color: #1B1B18;
    }
    [data-mobile-profile] .mob-cartsq {
        flex: none; width: 24px; height: 24px; border-radius: 7px;
        background: var(--mob-nav); color: #fff;
        display: flex; align-items: center; justify-content: center;
    }

    /* ── Tabs ── */
    [data-mobile-profile] .mob-tabs { display: flex; border-bottom: 1px solid var(--mob-line); overflow-x: auto; }
    [data-mobile-profile] .mob-tabs::-webkit-scrollbar { display: none; }
    [data-mobile-profile] .mob-tab {
        flex: 1 0 auto; display: flex; flex-direction: column; align-items: center; gap: 5px;
        padding: 10px 9px 9px; font-size: 9.5px; font-weight: 700; letter-spacing: .04em;
        color: #6E6A5F; border-bottom: 2.5px solid transparent; white-space: nowrap;
    }
    [data-mobile-profile] .mob-tab i { width: 18px; height: 18px; }
    [data-mobile-profile] .mob-tab[aria-selected="true"] { color: var(--mob-nav); border-bottom-color: var(--mob-nav); }

    /* A stated absence is marked, not merely coloured. `.ap-absent` is the
       machine-readable signal — shared with the desktop page and asserted by
       ArtisanProfilePageTest — that this node reports something the platform
       does not measure. The italic grey is only its appearance; strip the class
       and a later reader cannot tell an absence from a measurement. */
    [data-mobile-profile] .ap-absent { font-style: italic; color: #A8A296; font-weight: 500; }

    /* Facts list: gold-tinted icon chips, as the artwork's right column */
    [data-mobile-profile] .mob-fact { display: flex; align-items: flex-start; gap: 9px; padding: 7px 0; }
    [data-mobile-profile] .mob-fact-ic {
        flex: none; width: 26px; height: 26px; border-radius: 8px;
        background: #F4FAF5; border: 1px solid #DCEEDF; color: var(--mob-green);
        display: flex; align-items: center; justify-content: center;
    }
    [data-mobile-profile] .mob-fact-ic i { width: 14px; height: 14px; }

    /* ── Bottom navigation ── */
    [data-mobile-profile] .mob-nav {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 40;
        display: grid; grid-template-columns: repeat(5, 1fr);
        background: var(--mob-nav); color: #EAF3EC; padding: 10px 0 12px;
        box-shadow: 0 -2px 10px rgba(2,26,12,.25);
    }
    [data-mobile-profile] .mob-nav a { display: flex; flex-direction: column; align-items: center; gap: 5px; font-size: 11px; font-weight: 500; color: #E4F0E7; }
    [data-mobile-profile] .mob-nav a i { width: 22px; height: 22px; }
    [data-mobile-profile] .mob-nav .mob-nav-mid { position: relative; padding-top: 34px; }
    [data-mobile-profile] .mob-nav .mob-nav-mid span.mob-disc {
        position: absolute; top: -30px; left: 50%; transform: translateX(-50%);
        width: 62px; height: 62px; border-radius: 50%;
        background: radial-gradient(circle at 50% 40%, #0A5A2C 0%, var(--mob-nav) 75%);
        border: 3px solid var(--mob-gold);
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 3px 10px rgba(2,26,12,.4);
    }
    [data-mobile-profile] .mob-nav .mob-nav-mid span.mob-disc img { width: 44px; height: 44px; border-radius: 50%; }
</style>

{{-- ── Status bar ──────────────────────────────────────────────────────────
     Purely chrome: the clock is the device's, not a value from the record, so
     it is drawn as a live clock rather than the mockup's frozen 9:41. --}}
<div class="flex items-center justify-between px-5 pt-2 pb-1 text-[15px] font-semibold text-[#1B1B18]">
    <span data-mob-clock>&nbsp;</span>
    <span class="flex items-center gap-1.5 text-[#1B1B18]">
        <i data-lucide="signal" class="w-4 h-4"></i>
        <i data-lucide="wifi" class="w-4 h-4"></i>
        <i data-lucide="battery-full" class="w-5 h-5"></i>
    </span>
</div>

{{-- ── Top app bar ─────────────────────────────────────────────────────────
     The mockup's cart is omitted: there is no cart table and the operator is
     not party to the sale, so a cart icon offers a checkout that cannot
     happen. The bell keeps its badge only when this reader actually has
     unread rows in `user_notifications`. --}}
<header class="flex items-center gap-3 px-4 pb-3">
    <button type="button" aria-label="{{ $isFr ? 'Menu' : 'Menu' }}" class="p-1 -ml-1" data-mob-menu>
        <i data-lucide="menu" class="w-7 h-7 text-[#1B1B18]"></i>
    </button>
    <a href="{{ route('home', ['lang' => $lang]) }}" class="flex-1 min-w-0">
        <img src="{{ brand_asset('full') }}" alt="ArtisanHub237" class="h-9 w-auto">
    </a>
    <a href="{{ route('gallery.search', ['lang' => $lang]) }}" aria-label="{{ $isFr ? 'Rechercher' : 'Search' }}" class="p-1">
        <i data-lucide="search" class="w-6 h-6 text-[#1B1B18]"></i>
    </a>
    <a href="{{ route('notifications.index') }}" aria-label="{{ $isFr ? 'Notifications' : 'Notifications' }}" class="p-1 relative">
        <i data-lucide="bell" class="w-6 h-6 text-[#1B1B18]"></i>
        @if($mUnread > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[17px] h-[17px] px-1 rounded-full bg-[#CC060E] text-white text-[10px] font-bold flex items-center justify-center">{{ $mUnread > 9 ? '9+' : $mUnread }}</span>
        @endif
    </a>
    <a href="{{ route('saved.index') }}" aria-label="{{ $isFr ? 'Favoris' : 'Saved' }}" class="p-1">
        <i data-lucide="heart" class="w-6 h-6 text-[#1B1B18]"></i>
    </a>
    {{-- The artwork's cart. There is no cart on this platform, so the icon is a
         quote-request link — the transaction the operator actually carries. --}}
    <a href="{{ route('messages.compose', ['business' => $business->slug, 'lang' => $lang]) }}"
       aria-label="{{ $isFr ? 'Demander un devis' : 'Request a quote' }}" class="p-1">
        <i data-lucide="shopping-bag" class="w-6 h-6 text-[#1B1B18]"></i>
    </a>
</header>

{{-- ── Hero ────────────────────────────────────────────────────────────────
     The cover photo sits behind at low opacity where one exists; the mockup's
     carved-mask backdrop is stock art and must not stand in for a shop that
     has uploaded nothing. --}}
<section class="mob-hero @if(! $mHeroArt) mob-hero--bare @endif">
    {{-- Right-half backdrop: the shop's own cover photo fading in under a warm
         glow when one exists; the plain gradient glow when not. The mockup's
         carved-mask photo is stock art and never stands in for either. --}}
    @if($mHeroArt)
        <img src="{{ $mHeroArt }}" alt="" aria-hidden="true" class="mob-hero-art">
    @endif
    <span class="mob-hero-glow" aria-hidden="true"></span>
    <span class="mob-hero-scrim" aria-hidden="true"></span>

    <div class="relative flex gap-4 px-4 pt-4">
        {{-- Portrait Ø118, 3px gold ring, VERIFIED pill overlapping its foot --}}
        <div class="relative flex-none w-[118px]">
            <div class="w-[118px] h-[118px] rounded-full overflow-hidden" style="border:3px solid var(--mob-gold); box-shadow:0 0 0 3px rgba(14,10,3,.9), 0 4px 14px rgba(0,0,0,.5)">
                @if($business->logo)
                    <img src="{{ $mAsset($business->logo) }}" alt="{{ $mName }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-[#241A08] text-[var(--mob-gold)] text-3xl font-bold">
                        {{ mb_strtoupper(mb_substr($mName, 0, 1)) }}
                    </div>
                @endif
            </div>
            @if($mVerified)
                <span class="absolute left-1/2 -translate-x-1/2 top-[108px] whitespace-nowrap rounded-full bg-[#054821] border border-[var(--mob-gold)] px-2.5 py-[5px] text-[9.5px] font-extrabold tracking-[.04em] text-white flex items-center gap-1 shadow-md">
                    <i data-lucide="check" class="w-3 h-3 text-[var(--mob-gold)]"></i>{{ $isFr ? 'ARTISAN VÉRIFIÉ' : 'VERIFIED ARTISAN' }}
                </span>
            @endif
        </div>

        <div class="min-w-0 flex-1 pt-0.5">
            <p class="flex items-center gap-2 text-[12px] font-medium text-[#F2EDE2]">
                <span aria-hidden="true">🇨🇲</span>{{ $isFr ? 'Cameroun' : 'Cameroon' }}
            </p>
            <h1 class="mt-1 flex items-start gap-2 text-[19.5px] leading-[1.15] font-extrabold text-white">
                <span class="min-w-0 break-words">{{ $mName }}</span>
                @if($mVerified)
                    <span class="mt-1 flex-none w-[19px] h-[19px] rounded-full bg-[#1E9A50] flex items-center justify-center" aria-label="{{ $isFr ? 'Vérifié' : 'Verified' }}">
                        <i data-lucide="check" class="w-3 h-3 text-white"></i>
                    </span>
                @endif
            </h1>
            @if($mTagline || $mIndustry)
                <p class="mt-1 text-[12px] leading-snug text-[#EFEADF]">{{ $mTagline ?: $mIndustry }}</p>
            @endif

            @if($mPlace)
                <p class="mob-hero-meta"><i data-lucide="map-pin"></i>{{ $mPlace }}</p>
            @endif
            @if($mWorkshop)
                <p class="mob-hero-meta"><i data-lucide="store"></i>{{ $mWorkshop }}</p>
            @endif
            @if($mYears !== null)
                <p class="mob-hero-meta"><i data-lucide="hammer"></i>{{ $mYears }} {{ $isFr ? 'ans d’expérience' : 'years experience' }}</p>
            @endif
            @if($mMemberSince)
                <p class="mob-hero-meta"><i data-lucide="calendar"></i>{{ $isFr ? 'Membre depuis' : 'Member since' }} {{ $mMemberSince->locale($isFr ? 'fr' : 'en')->translatedFormat('F Y') }}</p>
            @endif
        </div>
    </div>

    {{-- Trust and rating panel. The design fills it with 92/100 and 4.9 over
         128 reviews. There is no scoring model and the review register is
         empty, so each half states what it does not have. An empty panel is
         less use to the artisan and more use to the buyer. --}}
    <div class="relative px-4 pb-4 pt-1">
        <div class="mob-trust grid grid-cols-2 text-center">
            <div class="pr-3 border-r border-[rgba(237,168,23,.25)]">
                <p class="mob-trust-h">{{ mb_strtoupper($mTrust['label'] ?? ($isFr ? 'Niveau de vérification' : 'Verification standing')) }}</p>
                @if($mTrustKnown)
                    <p class="mt-1.5 text-[26px] font-extrabold text-white leading-none">{{ $mTrust['value'] }}@if(($mTrust['max'] ?? null)) <span class="text-[14px] font-semibold text-[#CFC7B4]">/{{ $mTrust['max'] }}</span>@endif</p>
                    <p class="mt-2 flex items-start justify-center gap-1.5 text-[11.5px] leading-snug text-[#EFEADF]" title="{{ $mTrust['basis'] }}">
                        <i data-lucide="shield-check" class="w-4 h-4 flex-none text-[var(--mob-gold)]"></i>
                        <span class="min-w-0" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{{ $mTrust['basis'] }}</span>
                    </p>
                @else
                    <p class="mt-2 text-[12px] leading-snug ap-absent" style="color:#9E9A8E">{{ $isFr ? 'Non suivi — la plateforme ne calcule pas d’indice de confiance.' : 'Not tracked — the platform computes no trust score.' }}</p>
                @endif
            </div>
            <div class="pl-3">
                <p class="mob-trust-h">{{ $isFr ? 'AVIS CLIENTS' : 'CUSTOMER RATING' }}</p>
                @if($mRating !== null)
                    <p class="mt-1.5 text-[26px] font-extrabold text-white leading-none">{{ $mRating }} <span class="text-[14px] font-semibold text-[#CFC7B4]">/5</span></p>
                    <p class="mt-2 flex items-center justify-center gap-0.5" aria-hidden="true">
                        @for($s = 1; $s <= 5; $s++)
                            <svg class="mob-star" viewBox="0 0 24 24" @if($s > round($mReviewMean)) style="opacity:.25" @endif><path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                        @endfor
                    </p>
                    <p class="mt-1 text-[11.5px] text-[#CFC7B4]">({{ $mReviewCount }} {{ $isFr ? 'avis' : ($mReviewCount === 1 ? 'Review' : 'Reviews') }})</p>
                @elseif($mReviewCount > 0)
                    {{-- Reviews exist but the register will not average them yet;
                         the count is a fact, the mean is not. --}}
                    <p class="mt-1.5 text-[30px] font-extrabold text-white leading-none">{{ $mReviewCount }}</p>
                    <p class="mt-1 text-[11.5px] text-[#CFC7B4]">{{ $isFr ? 'avis publiés' : ($mReviewCount === 1 ? 'published review' : 'published reviews') }}</p>
                    @if($mReviewBasis)
                        <p class="mt-1.5 text-[10.5px] leading-snug text-[#9C978A]">{{ $mReviewBasis }}</p>
                    @endif
                @else
                    <p class="mt-2 text-[12px] leading-snug ap-absent" style="color:#9E9A8E">{{ $isFr ? 'Aucun avis publié pour cet atelier.' : 'No reviews published for this workshop yet.' }}</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ── Four-up action row ──────────────────────────────────────────────────
     Each control is dropped when the record cannot honour it. "Call Artisan"
     without a number on file, or "Visit Workshop" without an address, is a
     button that wastes a buyer's tap and teaches them the profile is
     decorative. The columns therefore vary in number between shops. --}}
@php
    $mWa = $business->whatsapp ?: $business->phone;
    $mActions = array_values(array_filter([
        ['message-square', $isFr ? 'Message' : 'Message', route('messages.compose', ['business' => $business->slug, 'lang' => $lang]), false],
        $mWorkshop ? ['map-pin', $isFr ? 'Atelier' : 'Visit Workshop', '#panel-workshop', true] : null,
        $business->phone ? ['phone', $isFr ? 'Appeler' : 'Call Artisan', 'tel:' . preg_replace('/[^\d+]/', '', $business->phone), false] : null,
        ['bookmark', $isFr ? 'Suivre' : 'Follow', null, false],
    ]));
@endphp
<section class="mob-card">
    <div class="mob-act" style="grid-template-columns: repeat({{ count($mActions) }}, 1fr)">
        @foreach($mActions as [$icon, $label, $href, $gold])
            @if($href === null)
                <form method="POST" action="{{ route('businesses.toggle-save', $business->slug) }}">
                    @csrf
                    <button type="submit" class="w-full flex flex-col items-center gap-1.5">
                        <i data-lucide="{{ $icon }}" class="w-[21px] h-[21px] text-[var(--mob-green)]"></i>
                        <span>{{ $label }}</span>
                    </button>
                </form>
            @else
                <a href="{{ $href }}">
                    <i data-lucide="{{ $icon }}" class="w-[22px] h-[22px] {{ $gold ? 'text-[#C8860B]' : 'text-[var(--mob-green)]' }}"></i>
                    <span class="{{ $gold ? 'text-[#C8860B] font-semibold' : '' }}">{{ $label }}</span>
                </a>
            @endif
        @endforeach
    </div>
</section>

{{-- ── Verified certificates ───────────────────────────────────────────────
     Every shield is a certificate number a reader can type into the public
     verifier, which is the only reason the strip earns its space. When the
     registers hold nothing for this shop the strip says so rather than
     showing the design's four ready-made shields. --}}
<section class="mob-card">
    <div class="mob-sec-h">
        <i data-lucide="shield-check" class="w-[18px] h-[18px] text-[var(--mob-green)]"></i>
        <span class="flex-1">{{ $isFr ? 'CERTIFICATS VÉRIFIÉS' : 'VERIFIED CERTIFICATES' }}</span>
        @if($mCerts->isNotEmpty())
            <a href="{{ route('certificate.verify') }}" class="text-[11.5px] font-semibold normal-case tracking-normal text-[#14652F] flex items-center gap-1">
                {{ $isFr ? 'Tout voir' : 'View All' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        @endif
    </div>
    @if($mCerts->isEmpty())
        <p class="px-3 pb-3 text-[12.5px] leading-snug ap-absent">
            {{ $isFr
                ? 'Aucun certificat n’a encore été délivré à cet atelier par les registres de la plateforme.'
                : 'The platform registers have not yet issued a certificate to this workshop.' }}
        </p>
    @else
        <div class="mob-scroll">
            @foreach($mCerts->take(8) as $c)
                @php $cNo = is_array($c) ? ($c['no'] ?? null) : ($c->no ?? null); @endphp
                <a class="mob-cert" href="{{ route('certificate.verify', ['ref' => $cNo]) }}">
                    <span class="mob-shield" aria-hidden="true">
                        <img src="{{ brand_asset('mark') }}" alt="">
                    </span>
                    <p class="mt-2 text-[13px] font-extrabold text-[#1B1B18]">{{ is_array($c) ? $c['code'] : $c->code }}</p>
                    <p class="mt-0.5 text-[9.5px] leading-tight text-[#3B382F]">{{ is_array($c) ? $c['name'] : $c->name }}</p>
                    @php $cAt = is_array($c) ? ($c['issued_at'] ?? null) : ($c->issued_at ?? null); @endphp
                    @if($cAt)
                        <p class="mt-1.5 text-[9.5px] text-[#8A857A]">{{ \Illuminate\Support\Carbon::parse($cAt)->format('d/m/Y') }}</p>
                    @endif
                    <p class="mt-2"><span class="mob-vpill"><i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Vérifié' : 'Verified' }}</span></p>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- ── Featured products ───────────────────────────────────────────────────
     No star line under the cards. The design prints "4.9 (28)" on each, but a
     review in this database attaches to a business, never to a product, so
     there is no set to average. The design's cart button is replaced by a
     quote request, which is the transaction this platform actually carries. --}}
<section class="mob-card">
    <div class="mob-sec-h">
        <i data-lucide="package" class="w-[18px] h-[18px] text-[#C9942E]"></i>
        <span class="flex-1">{{ $isFr ? 'PRODUITS EN VITRINE' : 'FEATURED PRODUCTS' }}</span>
        @if($mProducts->isNotEmpty())
            <a href="{{ route('products.index', ['business' => $business->slug, 'lang' => $lang]) }}" class="text-[11.5px] font-semibold normal-case tracking-normal text-[#14652F] flex items-center gap-1">
                {{ $isFr ? 'Tout voir' : 'View All' }} <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        @endif
    </div>
    @if($mProducts->isEmpty())
        <p class="px-3 pb-3 text-[12.5px] leading-snug ap-absent">
            {{ $isFr ? 'Cet atelier n’a pas encore publié de produit.' : 'This workshop has not published a product yet.' }}
        </p>
    @else
        <div class="mob-prods">
            @foreach($mProducts->take(8) as $prod)
                @php
                    /*
                     * The register hands back plain arrays already resolved for
                     * the reader's language; the Eloquent fallback below is what
                     * this partial used before ArtisanProfile existed. Reading
                     * both here keeps one card template rather than two.
                     */
                    $pArr   = is_array($prod);
                    $pName  = $pArr ? ($prod['name'] ?? '') : ($isFr ? $prod->name_fr : ($prod->name_en ?? $prod->name_fr));
                    $pSlug  = $pArr ? ($prod['slug'] ?? '') : $prod->slug;
                    $pImage = $pArr ? ($prod['image'] ?? null) : ($prod->primaryImage?->file_path);
                    /* The register wraps price as ['amount','currency','basis'];
                       casting that array to float printed "FCFA 1" on a piece
                       priced at 85 000 — the most misleading pixel on the page. */
                    $pPrice = $pArr ? ($prod['price']['amount'] ?? null) : $prod->price_amount;
                    $pCur   = $pArr ? ($prod['price']['currency'] ?? null) : $prod->price_currency;
                    $pCert  = $pArr ? (bool) ($prod['has_authenticity_certificate'] ?? false) : false;
                    /* The design prints a star and a review count under every
                       card. A rating exists here only if the register hands one
                       back for this piece, with a count behind it — reviews on
                       this platform are written about the artisan, not about a
                       work, so today nothing satisfies this and no star row is
                       drawn. The shape is read rather than computed so the line
                       appears the day per-piece ratings are recorded, and never
                       before. */
                    $pRating = $pArr && isset($prod['rating']) && is_array($prod['rating']) && ($prod['rating']['known'] ?? false)
                        ? $prod['rating']
                        : null;
                    $pRatingCount = $pRating ? (int) ($pRating['count'] ?? 0) : 0;
                    $pCat   = $pArr
                        ? $mIndustry
                        : ($prod->category ? ($isFr ? $prod->category->name_fr : ($prod->category->name_en ?? $prod->category->name_fr)) : $mIndustry);
                @endphp
                <div class="mob-prod">
                    <a href="{{ route('products.show', ['slug' => $pSlug, 'lang' => $lang]) }}" class="mob-prod-img">
                        @if($pImage)
                            <img src="{{ $mAsset($pImage) }}" alt="{{ $pName }}">
                        @else
                            <span class="absolute inset-0 flex items-center justify-center">
                                <i data-lucide="image" class="w-7 h-7 text-[#B8B2A4]"></i>
                            </span>
                        @endif
                        <span class="mob-heart" aria-hidden="true"><i data-lucide="heart" class="w-3 h-3"></i></span>
                    </a>
                    {{-- Two lines, clamped and given a fixed measure, so the four
                         tiles in a row keep their price lines on one baseline
                         whatever the piece is called. --}}
                    <p class="mt-1.5 text-[10.5px] font-bold leading-[1.25] text-[#1B1B18]"
                       style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:26px">{{ $pName }}</p>
                    @if($pCat)
                        <p class="text-[9px] leading-tight text-[#8A857A] truncate">{{ $pCat }}</p>
                    @endif
                    {{-- The artwork stacks the price on its own line and puts the
                         rating and the cart on the next. At a ~100px tile the
                         price and the cart cannot share a line without one of
                         them being cut, so the artwork's own stack is also the
                         only one that fits. --}}
                    @php $pHasStars = $pRating && $pRatingCount > 0; @endphp
                    @if($pPrice)
                        <p class="mt-1 text-[10.5px] font-extrabold text-[#1B1B18] whitespace-nowrap overflow-hidden text-ellipsis">{{ in_array($pCur, [null, '', 'XAF'], true) ? 'FCFA' : $pCur }} {{ number_format((float) $pPrice, 0, ',', ' ') }}</p>
                    @else
                        <p class="mt-1 text-[10px] text-[#8A857A]">{{ $isFr ? 'Sur devis' : 'On quote' }}</p>
                    @endif
                    <div class="mt-1 flex items-center justify-between gap-1">
                        {{-- The star line, drawn from the register or not at all. --}}
                        @if($pHasStars)
                            <span class="flex items-center gap-0.5 text-[10px] font-semibold text-[#1B1B18] min-w-0" @if($pRating['basis'] ?? null) title="{{ $pRating['basis'] }}" @endif>
                                <svg class="mob-star" viewBox="0 0 24 24" aria-hidden="true" style="width:11px;height:11px"><path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                                {{ number_format((float) $pRating['value'], 1, $isFr ? ',' : '.', ' ') }}
                                <span class="text-[#8A857A] font-medium">({{ $pRatingCount }})</span>
                            </span>
                        @else
                            <span aria-hidden="true"></span>
                        @endif
                        <a href="{{ route('products.show', ['slug' => $pSlug, 'lang' => $lang]) }}"
                           aria-label="{{ $isFr ? 'Demander un devis' : 'Request a quote' }}"
                           class="mob-cartsq">
                            <i data-lucide="shopping-bag" class="w-[13px] h-[13px]"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

{{-- ── Tabs and panels ─────────────────────────────────────────────────────
     All five panels are in the document and toggled by the small script at
     the foot, so the page still reads end to end with JavaScript off — which
     matters on the connections this audience actually browses on. --}}
<section class="mob-card">
    <div class="mob-tabs" role="tablist">
        @foreach($mTabs as $i => [$id, $label, $icon])
            <a class="mob-tab" role="tab" href="#panel-{{ $id }}" data-mob-tab="{{ $id }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                <i data-lucide="{{ $icon }}"></i>{{ $label }}
            </a>
        @endforeach
    </div>

    {{-- ABOUT: the artisan's own words on the left, the facts table on the
         right, exactly as the design lays it out at this width. --}}
    <div id="panel-about" data-mob-panel="about" class="p-3 grid grid-cols-2 gap-3">
        <div>
            <h2 class="text-[13px] font-extrabold text-[#1B1B18]">{{ $isFr ? 'À propos' : 'About' }} {{ \Illuminate\Support\Str::limit($mName, 22) }}</h2>
            @if($mDesc)
                {{-- The design clamps the prose and offers "Read More". The
                     disclosure is drawn only when there is something folded
                     away: a two-line description with a Read More under it
                     promises text that does not exist. --}}
                @php $mLong = mb_strlen(trim($mDesc)) > 220; @endphp
                <p class="mt-2 text-[11.5px] leading-relaxed text-[#3B382F] whitespace-pre-line"
                   data-mob-bio @if($mLong) style="display:-webkit-box;-webkit-line-clamp:5;-webkit-box-orient:vertical;overflow:hidden" @endif>{{ $mDesc }}</p>
                @if($mLong)
                    <button type="button" data-mob-more
                            class="mt-2 flex items-center gap-1.5 text-[11.5px] font-bold text-[var(--mob-green)]"
                            data-more="{{ $isFr ? 'Lire la suite' : 'Read More' }}"
                            data-less="{{ $isFr ? 'Réduire' : 'Read Less' }}">
                        <span>{{ $isFr ? 'Lire la suite' : 'Read More' }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </button>
                @endif
            @else
                <p class="mt-2 text-[11.5px] leading-relaxed ap-absent">
                    {{ $isFr ? 'Cet atelier n’a pas encore rédigé de présentation.' : 'This workshop has not written a description yet.' }}
                </p>
            @endif
        </div>
        <dl class="rounded-[12px] border border-[var(--mob-line)] bg-[#FDFBF7] px-3 py-1.5 self-start">
            @forelse($mFacts as [$ic, $k, $v, $basis])
                <div class="mob-fact" @if($basis) title="{{ $basis }}" @endif>
                    <span class="mob-fact-ic"><i data-lucide="{{ $ic }}"></i></span>
                    <span class="min-w-0">
                        <dt class="text-[9.5px] text-[#8A857A]">{{ $k }}</dt>
                        <dd class="text-[11px] font-semibold text-[#1B1B18] leading-snug">{{ $v }}</dd>
                    </span>
                </div>
            @empty
                <p class="py-2 text-[11.5px] ap-absent">{{ $isFr ? 'Fiche à compléter par l’artisan.' : 'The artisan has not filled this in.' }}</p>
            @endforelse
        </dl>
    </div>

    {{-- WORKSHOP: coarse location only. gps_lat/gps_lng are never printed and
         never linked to a map pin; they place a person's home to a few metres. --}}
    <div id="panel-workshop" data-mob-panel="workshop" class="p-3 hidden">
        @php
            /* The registered workshop, as ArtisanProfile::workshop() returns it.
               Rows whose column is null are dropped rather than dashed, and the
               location comes from the register's own ['value'] so the city and
               region it chose to publish are the ones printed here. */
            $w = is_array($mWorkshopData) ? $mWorkshopData : [];
            $wLevel = is_array($w['verification_level'] ?? null) && ($w['verification_level']['known'] ?? false)
                ? $w['verification_level']['value'] : null;
            $wPlace = is_array($w['location'] ?? null) && ($w['location']['known'] ?? false)
                ? $w['location']['value'] : $mPlace;

            $wRows = array_values(array_filter([
                ($w['name'] ?? $mWorkshop) ? [$isFr ? 'Atelier' : 'Workshop', $w['name'] ?? $mWorkshop] : null,
                ($w['number'] ?? null) ? [$isFr ? 'Numéro d’atelier' : 'Workshop number', $w['number']] : null,
                ($w['type'] ?? null) ? [$isFr ? 'Type' : 'Type', \Illuminate\Support\Str::of($w['type'])->replace('_', ' ')->title()] : null,
                ($w['legal_status'] ?? null) ? [$isFr ? 'Statut juridique' : 'Legal status', \Illuminate\Support\Str::of($w['legal_status'])->replace('_', ' ')->title()] : null,
                ($w['status'] ?? null) ? [$isFr ? 'Statut au registre' : 'Register status', \Illuminate\Support\Str::of($w['status'])->replace('_', ' ')->title()] : null,
                $wLevel !== null ? [$isFr ? 'Niveau d’inspection' : 'Inspection level', $wLevel] : null,
                ($w['verified_at'] ?? null) ? [$isFr ? 'Inspecté le' : 'Inspected on', \Illuminate\Support\Carbon::parse($w['verified_at'])->format('d/m/Y')] : null,
                ($w['established_on'] ?? null) ? [$isFr ? 'Créé le' : 'Established', \Illuminate\Support\Carbon::parse($w['established_on'])->format('d/m/Y')] : null,
                $wPlace ? [$isFr ? 'Localisation' : 'Location', $wPlace] : null,
                $business->employee_count ? [$isFr ? 'Artisans à l’atelier' : 'Workshop size', $business->employee_count] : null,
            ]));
        @endphp
        @if($wRows !== [])
            <dl class="divide-y divide-[#F5F1E8]">
                @foreach($wRows as [$k, $v])
                    <div class="flex items-start justify-between gap-3 py-2">
                        <dt class="text-[11.5px] text-[#8A857A]">{{ $k }}</dt>
                        <dd class="text-[12.5px] font-semibold text-[#1B1B18] text-right">{{ $v }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="mt-3 text-[11.5px] leading-snug text-[#8A857A]">
                {{ $isFr
                    ? 'Seules la ville et la région sont publiées. Les coordonnées exactes ne sont jamais affichées publiquement.'
                    : 'Only the town and region are published. Exact coordinates are never shown publicly.' }}
            </p>
        @else
            <p class="text-[12.5px] ap-absent">{{ $isFr ? 'Aucune adresse d’atelier au dossier.' : 'No workshop address on file.' }}</p>
        @endif
    </div>

    {{-- STATS: every figure carries the basis it was measured on, and one the
         platform does not measure says so. A counter reading zero and one that
         is not tracked are different facts, and only the first is a judgement
         on the artisan. --}}
    <div id="panel-stats" data-mob-panel="stats" class="p-3 hidden">
        <dl class="grid grid-cols-2 gap-2.5">
            @foreach($mStats as $key => $stat)
                @php
                    /* The register keys by machine name; the map turns that into
                       a reader's label. An unmapped key is title-cased rather
                       than skipped, so a counter added to the register still
                       reaches the page. */
                    [$label, $icon] = $mStatLabels[$key] ?? [\Illuminate\Support\Str::of($key)->replace('_', ' ')->ucfirst(), 'circle-dot'];
                @endphp
                <div class="rounded-[10px] border border-[var(--mob-line)] p-2.5">
                    <dt class="flex items-center gap-1.5 text-[9.5px] text-[#8A857A]">
                        <i data-lucide="{{ $icon }}" class="w-3.5 h-3.5 text-[#B8B2A4]"></i>{{ $label }}
                    </dt>
                    @if($stat['known'] ?? false)
                        <dd class="text-[16px] font-extrabold text-[#1B1B18] leading-none mt-1">{{ $stat['value'] }}</dd>
                    @else
                        <dd class="text-[12px] mt-1 ap-absent">{{ $mNotTracked }}</dd>
                    @endif
                    @if($stat['basis'] ?? null)
                        <p class="mt-1 text-[10px] leading-snug text-[#B8B2A4]">{{ $stat['basis'] }}</p>
                    @endif
                </div>
            @endforeach
        </dl>
    </div>

    {{-- REVIEWS --}}
    <div id="panel-reviews" data-mob-panel="reviews" class="p-3 hidden">
        @if($mReviewCount > 0)
            {{-- Summary and distribution. The bars are the register's five
                 buckets divided by its own count — no bar is drawn from a
                 percentage this file made up, and the whole block is absent
                 while the register counts nothing. --}}
            <div class="flex items-start gap-4 pb-3 border-b border-[#F5F1E8]">
                <div class="flex-none text-center">
                    @if($mRating !== null)
                        <p class="text-[28px] font-extrabold leading-none text-[#1B1B18]">{{ $mRating }}</p>
                        <p class="mt-1.5 flex items-center justify-center gap-0.5" aria-hidden="true">
                            @for($s = 1; $s <= 5; $s++)
                                <svg class="mob-star" viewBox="0 0 24 24" @if($s > round($mReviewMean)) style="opacity:.25" @endif><path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                            @endfor
                        </p>
                    @endif
                    <p class="mt-1 text-[11px] text-[#8A857A]">{{ $mReviewCount }} {{ $isFr ? 'avis' : ($mReviewCount === 1 ? 'review' : 'reviews') }}</p>
                </div>
                <div class="min-w-0 flex-1">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php $n = (int) ($mReviewDist[$star] ?? 0); @endphp
                        <div class="flex items-center gap-2 py-[3px]">
                            <span class="w-2 text-[10.5px] text-[#8A857A]">{{ $star }}</span>
                            <svg class="mob-star" style="width:11px;height:11px" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                            <span class="flex-1 h-[6px] rounded-full bg-[#F1ECE2] overflow-hidden">
                                <span class="block h-full rounded-full bg-[var(--mob-star)]" style="width:{{ $mReviewCount > 0 ? round(100 * $n / $mReviewCount, 1) : 0 }}%"></span>
                            </span>
                            <span class="w-5 text-right text-[10.5px] text-[#8A857A]">{{ $n }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            /* The panel prints written reviews; a rating with no words is still
               counted in the figure and in the bars above, but it has nothing
               to read, so the ones carrying text come first. The list is capped
               because a hundred and twenty-eight cards inside a phone tab is a
               scroll nobody finishes — the full set is on the reviews page. */
            $mWritten = $mReviews->sortByDesc(fn ($r) => filled($r->body) || filled($r->title))->values();
            $mShown = $mWritten->take(8);
        @endphp
        @forelse($mShown as $rev)
            <article class="py-3 border-b border-[#F5F1E8] last:border-0">
                <div class="flex items-center gap-2">
                    <p class="flex items-center gap-0.5" aria-hidden="true">
                        @for($s = 1; $s <= 5; $s++)
                            @php $rDim = $s > (int) $rev->rating ? ';opacity:.25' : ''; @endphp
                            <svg class="mob-star" viewBox="0 0 24 24" style="width:13px;height:13px{{ $rDim }}"><path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/></svg>
                        @endfor
                    </p>
                    <span class="text-[11.5px] font-semibold text-[#1B1B18]">{{ $rev->rating }}/5</span>
                    @if($rev->is_verified_contact)
                        <span class="mob-vpill"><i data-lucide="check-circle-2" class="w-3 h-3"></i>{{ $isFr ? 'Contact vérifié' : 'Verified contact' }}</span>
                    @endif
                </div>
                @if($rev->title)
                    <p class="mt-1.5 text-[12.5px] font-bold text-[#1B1B18]">{{ $rev->title }}</p>
                @endif
                @if($rev->body)
                    <p class="mt-1 text-[12.5px] leading-relaxed text-[#3B382F]">{{ $rev->body }}</p>
                @endif
                @if($rev->created_at)
                    <p class="mt-1.5 text-[10.5px] text-[#8A857A]">{{ $rev->created_at->locale($isFr ? 'fr' : 'en')->translatedFormat('d F Y') }}</p>
                @endif
            </article>
        @empty
            <p class="text-[12.5px] leading-snug ap-absent">
                {{ $isFr
                    ? 'Aucun avis n’a encore été publié sur cet atelier. Le registre des avis est vide — aucune note n’est donc affichée.'
                    : 'No review has been published about this workshop yet. The review register is empty, so no rating is shown.' }}
            </p>
        @endforelse
        @if($mReviewCount > $mShown->count())
            <p class="pt-3 text-[11px] text-[#8A857A]">
                {{ $isFr
                    ? $mShown->count() . ' avis sont affichés ici ; les ' . $mReviewCount . ' avis publiés sont tous comptés dans la note et dans les barres ci-dessus.'
                    : $mShown->count() . ' are shown here; all ' . $mReviewCount . ' published reviews are counted in the rating and the bars above.' }}
            </p>
        @endif
    </div>

    {{-- AWARDS: the design's ACHIEVEMENTS tab lists SIARC, UNESCO and ministry
         honours. The platform keeps no register of national honours and
         `business_awards` is empty; inventing one would attach a state
         endorsement to a private company that has none. --}}
    <div id="panel-awards" data-mob-panel="awards" class="p-3 hidden">
        @forelse($mAwards as $award)
            @php
                $aTitle = is_array($award) ? ($award['title'] ?? null) : ($award->title ?? null);
                $aYear = is_array($award) ? ($award['year'] ?? null) : ($award->year ?? null);
                /* Reported verbatim: the register does not verify the awarding
                   body, so the issuer is the artisan's claim and is labelled as
                   entered rather than as a finding of this platform. */
                $aIssuer = is_array($award) ? ($award['issuer'] ?? null) : ($award->issuer ?? null);
            @endphp
            <article class="flex items-start gap-2.5 py-2.5 border-b border-[#F5F1E8] last:border-0">
                <span class="mob-fact-ic"><i data-lucide="trophy"></i></span>
                <div class="min-w-0">
                    <p class="text-[13px] font-semibold text-[#1B1B18]">{{ $aTitle }}</p>
                    @if($aIssuer)<p class="text-[11.5px] text-[#3B382F]">{{ $aIssuer }}</p>@endif
                    @if($aYear)<p class="text-[11px] text-[#8A857A]">{{ $aYear }}</p>@endif
                </div>
            </article>
        @empty
            <p class="text-[12.5px] leading-snug ap-absent">
                {{ $isFr
                    ? 'Aucune distinction enregistrée. La plateforme ne tient pas de registre des honneurs nationaux et n’en attribue aucun.'
                    : 'No award on record. The platform keeps no register of national honours and confers none.' }}
            </p>
        @endforelse
    </div>
</section>

{{-- ── Fixed bottom navigation ─────────────────────────────────────────────
     Five destinations that all resolve. "Profile" points at the dashboard for
     a signed-in reader and at the sign-in page otherwise, rather than at a
     page that would bounce them. --}}
<nav class="mob-nav" aria-label="{{ $isFr ? 'Navigation principale' : 'Main navigation' }}">
    <a href="{{ route('home', ['lang' => $lang]) }}"><i data-lucide="home"></i>{{ $isFr ? 'Accueil' : 'Home' }}</a>
    <a href="{{ route('industries.index', ['lang' => $lang]) }}"><i data-lucide="layout-grid"></i>{{ $isFr ? 'Catégories' : 'Categories' }}</a>
    <a href="{{ route('certificate.verify') }}" class="mob-nav-mid">
        <span class="mob-disc"><img src="{{ brand_asset('mark') }}" alt=""></span>
        {{ $isFr ? 'Vérifier' : 'Verify' }}
    </a>
    <a href="{{ route('products.index', ['lang' => $lang]) }}"><i data-lucide="store"></i>{{ $isFr ? 'Marché' : 'Marketplace' }}</a>
    <a href="{{ $mUser ? route('dashboard.siac') : route('login') }}"><i data-lucide="user"></i>{{ $isFr ? 'Profil' : 'Profile' }}</a>
</nav>

<script>
    (function () {
        var root = document.querySelector('[data-mobile-profile]');
        if (!root) return;

        var clock = root.querySelector('[data-mob-clock]');
        if (clock) {
            var tick = function () {
                clock.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };
            tick();
            setInterval(tick, 30000);
        }

        // Tabs are real anchors, so without this the browser simply jumps to
        // the panel — which is why every panel stays in the document.
        root.querySelectorAll('[data-mob-tab]').forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                var id = tab.getAttribute('data-mob-tab');
                root.querySelectorAll('[data-mob-tab]').forEach(function (t) {
                    t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
                });
                root.querySelectorAll('[data-mob-panel]').forEach(function (p) {
                    p.classList.toggle('hidden', p.getAttribute('data-mob-panel') !== id);
                });
            });
        });

        // "Read More" unclamps the artisan's own text; nothing is loaded, so
        // the full description is in the document either way.
        var more = root.querySelector('[data-mob-more]');
        var bio = root.querySelector('[data-mob-bio]');
        if (more && bio) {
            more.addEventListener('click', function () {
                var open = bio.style.webkitLineClamp === '';
                bio.style.webkitLineClamp = open ? '5' : '';
                bio.style.overflow = open ? 'hidden' : 'visible';
                more.querySelector('span').textContent = open
                    ? more.getAttribute('data-more')
                    : more.getAttribute('data-less');
                more.querySelector('i, svg').style.transform = open ? '' : 'rotate(180deg)';
            });
        }

        if (window.lucide) { window.lucide.createIcons(); }
    })();
</script>
</div>
