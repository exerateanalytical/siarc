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

    $mName = $isFr ? $business->name_fr : ($business->name_en ?? $business->name_fr);
    $mTagline = $isFr ? $business->tagline_fr : ($business->tagline_en ?? $business->tagline_fr);
    $mIndustry = $business->industry ? ($isFr ? $business->industry->name_fr : ($business->industry->name_en ?? $business->industry->name_fr)) : null;
    $mCity = $business->city?->name_fr;
    $mRegion = $business->region ? ($isFr ? $business->region->name_fr : ($business->region->name_en ?? $business->region->name_fr)) : null;

    // Coarse location only. gps_lat/gps_lng locate a person's home workshop to
    // a few metres; the town and region are what a buyer actually needs.
    $mPlace = collect([$mCity, $mRegion])->filter()->implode(', ') ?: null;

    $mVerified = in_array($business->verification_tier, ['verified', 'certified'], true);
    $mWorkshop = $ask('workshop') ?: ($business->address_fr || $business->address_en ? ($isFr ? $business->address_fr : ($business->address_en ?? $business->address_fr)) : null);
    $mYears = $business->year_established ? max(0, (int) date('Y') - (int) $business->year_established) : null;
    $mMemberSince = $business->created_at;

    // Reviews: the register, not a design figure.
    $mReviews = $business->relationLoaded('reviews') ? $business->reviews : $business->reviews()->get();
    $mReviewCount = $mReviews->count();
    $mRating = $mReviewCount ? number_format($mReviews->avg('rating'), 1, ',', ' ') : null;

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

    // Unread notifications are a real feature with a real count; the badge is
    // drawn from it or not at all.
    $mUser = session('siac_user');
    $mUnread = $mUser
        ? (int) \Illuminate\Support\Facades\DB::table('user_notifications')->where('user_id', $mUser['id'] ?? 0)->whereNull('read_at')->count()
        : 0;

    $mDesc = $isFr ? $business->description_fr : ($business->description_en ?? $business->description_fr);

    $mTabs = array_values(array_filter([
        ['about', $isFr ? 'À PROPOS' : 'ABOUT', 'info'],
        ['workshop', $isFr ? 'ATELIER' : 'WORKSHOP', 'hammer'],
        ['stats', $isFr ? 'CHIFFRES' : 'STATS', 'bar-chart-3'],
        ['reviews', $isFr ? 'AVIS' : 'REVIEWS', 'star'],
        ['awards', $isFr ? 'DISTINCTIONS' : 'AWARDS', 'trophy'],
    ]));

    /* The facts table. Every row is dropped when its field is empty rather than
       printed with a dash, because four rows of "—" reads as a neglected
       profile where three real rows read as a short one. */
    $mFacts = array_values(array_filter([
        $business->languages_spoken ? [$isFr ? 'Langues' : 'Languages', collect($business->languages_spoken)->filter()->implode(', ')] : null,
        $mIndustry ? [$isFr ? 'Métier' : 'Craft', $mIndustry] : null,
        $mYears !== null ? [$isFr ? 'Expérience' : 'Experience', $mYears . ' ' . ($isFr ? 'ans' : 'yrs')] : null,
        $mRegion ? [$isFr ? 'Région' : 'Region', $mRegion] : null,
        $business->employee_count ? [$isFr ? 'Artisans à l’atelier' : 'Workshop size', $business->employee_count] : null,
    ]));
@endphp

<div data-mobile-profile class="mob-root">
<style>
    /* Scoped to [data-mobile-profile] so nothing here can reach the desktop
       document that includes it. Plain CSS for the same reason as the UI kit:
       Tailwind is a runtime CDN bundle here, so @apply is unavailable. */
    [data-mobile-profile] {
        --mob-page:   #FDFAF6;
        --mob-dark:   #0B0B0A;
        --mob-nav:    #05311A;
        --mob-gold:   #E3A33D;
        --mob-green:  #157A43;
        --mob-line:   #EFEBE2;
        background: var(--mob-page);
        padding-bottom: 74px;      /* clears the fixed bottom bar */
        font-family: inherit;
    }
    [data-mobile-profile] .mob-card {
        background: #fff;
        border: 1px solid var(--mob-line);
        border-radius: 14px;
        margin: 0 10px 10px;
        overflow: hidden;
    }
    [data-mobile-profile] .mob-sec-h {
        display: flex; align-items: center; gap: 8px;
        padding: 12px 12px 8px;
        font-size: 12.5px; font-weight: 800; letter-spacing: .06em;
        color: #1B1B18; text-transform: uppercase;
    }
    [data-mobile-profile] .mob-scroll {
        display: flex; gap: 8px; overflow-x: auto;
        padding: 0 12px 12px; scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }
    [data-mobile-profile] .mob-scroll::-webkit-scrollbar { display: none; }

    [data-mobile-profile] .mob-hero {
        position: relative; margin: 0 10px 10px;
        border-radius: 14px; overflow: hidden;
        background: var(--mob-dark); color: #fff;
        min-height: 235px;
    }
    [data-mobile-profile] .mob-hero-meta { display: flex; align-items: center; gap: 8px; font-size: 12.5px; line-height: 17px; color: #E8E3D8; margin-top: 4px; }
    [data-mobile-profile] .mob-hero-meta i { width: 15px; height: 15px; color: var(--mob-gold); flex: none; }

    [data-mobile-profile] .mob-trust {
        margin: 10px 0 0; border: 1px solid rgba(227,163,61,.55);
        border-radius: 10px; background: rgba(0,0,0,.55); padding: 10px 12px;
    }
    [data-mobile-profile] .mob-trust-h { font-size: 11px; font-weight: 800; letter-spacing: .08em; color: var(--mob-gold); }

    [data-mobile-profile] .mob-act { display: grid; grid-template-columns: repeat(4, 1fr); }
    [data-mobile-profile] .mob-act > * {
        display: flex; flex-direction: column; align-items: center; gap: 6px;
        padding: 11px 2px; font-size: 11.5px; color: #1B1B18; text-align: center;
        border-left: 1px solid var(--mob-line); background: none;
    }
    [data-mobile-profile] .mob-act > *:first-child { border-left: 0; }
    [data-mobile-profile] .mob-act i { width: 21px; height: 21px; }

    [data-mobile-profile] .mob-cert {
        flex: 0 0 103px; scroll-snap-align: start;
        border: 1px solid var(--mob-line); border-radius: 10px;
        padding: 10px 6px; text-align: center; background: #fff;
    }
    [data-mobile-profile] .mob-prod { flex: 0 0 99px; scroll-snap-align: start; }
    [data-mobile-profile] .mob-prod img { width: 99px; height: 99px; object-fit: cover; border-radius: 9px; display: block; }

    [data-mobile-profile] .mob-tabs { display: flex; border-bottom: 1px solid var(--mob-line); overflow-x: auto; }
    [data-mobile-profile] .mob-tabs::-webkit-scrollbar { display: none; }
    [data-mobile-profile] .mob-tab {
        flex: 1 0 auto; display: flex; flex-direction: column; align-items: center; gap: 4px;
        padding: 9px 10px; font-size: 10.5px; font-weight: 700; letter-spacing: .03em;
        color: #8A857A; border-bottom: 2px solid transparent; white-space: nowrap;
    }
    [data-mobile-profile] .mob-tab i { width: 17px; height: 17px; }
    [data-mobile-profile] .mob-tab[aria-selected="true"] { color: #14652F; border-bottom-color: #14652F; }

    [data-mobile-profile] .mob-nav {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 40;
        display: grid; grid-template-columns: repeat(5, 1fr);
        background: var(--mob-nav); color: #EAF3EC; padding: 8px 0 10px;
    }
    [data-mobile-profile] .mob-nav a { display: flex; flex-direction: column; align-items: center; gap: 4px; font-size: 11px; color: #D8E7DC; }
    [data-mobile-profile] .mob-nav a i { width: 21px; height: 21px; }
    [data-mobile-profile] .mob-nav .mob-nav-mid { position: relative; }
    [data-mobile-profile] .mob-nav .mob-nav-mid span.mob-disc {
        position: absolute; top: -30px; left: 50%; transform: translateX(-50%);
        width: 62px; height: 62px; border-radius: 50%;
        background: var(--mob-nav); border: 2px solid var(--mob-gold);
        display: flex; align-items: center; justify-content: center;
    }
    [data-mobile-profile] .mob-nav .mob-nav-mid { padding-top: 32px; }
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
            <span class="absolute -top-0.5 -right-0.5 min-w-[17px] h-[17px] px-1 rounded-full bg-[#B42025] text-white text-[10px] font-bold flex items-center justify-center">{{ $mUnread > 9 ? '9+' : $mUnread }}</span>
        @endif
    </a>
    <a href="{{ route('saved.index') }}" aria-label="{{ $isFr ? 'Favoris' : 'Saved' }}" class="p-1">
        <i data-lucide="heart" class="w-6 h-6 text-[#1B1B18]"></i>
    </a>
</header>

{{-- ── Hero ────────────────────────────────────────────────────────────────
     The cover photo sits behind at low opacity where one exists; the mockup's
     carved-mask backdrop is stock art and must not stand in for a shop that
     has uploaded nothing. --}}
<section class="mob-hero">
    @if($business->cover_image)
        <img src="{{ asset('storage/' . $business->cover_image) }}" alt=""
             class="absolute inset-0 w-full h-full object-cover opacity-40" aria-hidden="true">
        <span class="absolute inset-0" style="background:linear-gradient(90deg,#0B0B0A 38%,rgba(11,11,10,.35) 100%)"></span>
    @endif

    <div class="relative flex gap-3 px-3 pt-2.5">
        {{-- Portrait, gold ring, verification pill overlapping its foot --}}
        <div class="relative flex-none w-[118px] pt-2">
            <div class="w-[118px] h-[118px] rounded-full overflow-hidden" style="border:3px solid var(--mob-gold)">
                @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" alt="{{ $mName }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-[#1B1B18] text-[var(--mob-gold)] text-3xl font-bold">
                        {{ mb_strtoupper(mb_substr($mName, 0, 1)) }}
                    </div>
                @endif
            </div>
            @if($mVerified)
                <span class="absolute left-1/2 -translate-x-1/2 top-[112px] whitespace-nowrap rounded-full bg-[#0F4824] border border-[var(--mob-gold)] px-2.5 py-1 text-[9.5px] font-extrabold tracking-wide text-white flex items-center gap-1">
                    <i data-lucide="check" class="w-3 h-3"></i>{{ $isFr ? 'ARTISAN VÉRIFIÉ' : 'VERIFIED ARTISAN' }}
                </span>
            @endif
        </div>

        <div class="min-w-0 flex-1 pt-1">
            <p class="flex items-center gap-2 text-[13px] text-[#E8E3D8]">
                <span aria-hidden="true">🇨🇲</span>{{ $isFr ? 'Cameroun' : 'Cameroon' }}
            </p>
            <h1 class="mt-0.5 flex items-start gap-1.5 text-[21px] leading-tight font-extrabold text-white">
                <span class="min-w-0 break-words">{{ $mName }}</span>
                @if($mVerified)
                    <i data-lucide="badge-check" class="w-5 h-5 mt-0.5 flex-none text-[#22A05B]" aria-label="{{ $isFr ? 'Vérifié' : 'Verified' }}"></i>
                @endif
            </h1>
            @if($mTagline || $mIndustry)
                <p class="mt-0.5 text-[13px] leading-snug text-[#EDE8DD]">{{ $mTagline ?: $mIndustry }}</p>
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
    <div class="relative px-3 pb-3 pt-0.5">
        <div class="mob-trust grid grid-cols-2">
            <div class="pr-3 border-r border-[rgba(227,163,61,.35)]">
                <p class="mob-trust-h">{{ $isFr ? 'INDICE DE CONFIANCE' : 'TRUST SCORE' }}</p>
                @if($mTrustKnown)
                    <p class="mt-1 text-[24px] font-extrabold text-white leading-none">{{ $mTrust['value'] }}<span class="text-[13px] font-semibold text-[#CFC7B4]">/100</span></p>
                    <p class="mt-1.5 flex items-center gap-1.5 text-[12px] text-[#E8E3D8]"><i data-lucide="shield-check" class="w-4 h-4 text-[var(--mob-gold)]"></i>{{ $mTrust['basis'] }}</p>
                @else
                    <p class="mt-1.5 text-[12px] leading-snug text-[#CFC7B4]">{{ $isFr ? 'Non suivi — la plateforme ne calcule pas d’indice de confiance.' : 'Not tracked — the platform computes no trust score.' }}</p>
                @endif
            </div>
            <div class="pl-3">
                <p class="mob-trust-h">{{ $isFr ? 'AVIS CLIENTS' : 'CUSTOMER RATING' }}</p>
                @if($mReviewCount > 0)
                    <p class="mt-1 text-[24px] font-extrabold text-white leading-none">{{ $mRating }}<span class="text-[13px] font-semibold text-[#CFC7B4]">/5</span></p>
                    <p class="mt-1.5 text-[12px] text-[#E8E3D8]">{{ $mReviewCount }} {{ $isFr ? 'avis' : ($mReviewCount === 1 ? 'review' : 'reviews') }}</p>
                @else
                    <p class="mt-1.5 text-[12px] leading-snug text-[#CFC7B4]">{{ $isFr ? 'Aucun avis publié pour cet atelier.' : 'No reviews published for this workshop yet.' }}</p>
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
                    <i data-lucide="{{ $icon }}" class="w-[21px] h-[21px] {{ $gold ? 'text-[#C9942E]' : 'text-[var(--mob-green)]' }}"></i>
                    <span class="{{ $gold ? 'text-[#8A6D1F]' : '' }}">{{ $label }}</span>
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
        <p class="px-3 pb-3 text-[12.5px] leading-snug text-[#8A857A]">
            {{ $isFr
                ? 'Aucun certificat n’a encore été délivré à cet atelier par les registres de la plateforme.'
                : 'The platform registers have not yet issued a certificate to this workshop.' }}
        </p>
    @else
        <div class="mob-scroll">
            @foreach($mCerts->take(8) as $c)
                @php $cNo = is_array($c) ? ($c['no'] ?? null) : ($c->no ?? null); @endphp
                <a class="mob-cert" href="{{ route('certificate.verify', ['ref' => $cNo]) }}">
                    <i data-lucide="shield" class="w-9 h-9 mx-auto text-[#C9942E]"></i>
                    <p class="mt-1.5 text-[15px] font-extrabold text-[#1B1B18]">{{ is_array($c) ? $c['code'] : $c->code }}</p>
                    <p class="mt-0.5 text-[10.5px] leading-tight text-[#3B382F]">{{ is_array($c) ? $c['name'] : $c->name }}</p>
                    @php $cAt = is_array($c) ? ($c['issued_at'] ?? null) : ($c->issued_at ?? null); @endphp
                    @if($cAt)
                        <p class="mt-1 text-[10px] text-[#8A857A]">{{ \Illuminate\Support\Carbon::parse($cAt)->format('d/m/Y') }}</p>
                    @endif
                    <p class="mt-1.5 inline-flex items-center gap-1 text-[10.5px] font-semibold text-[#157A43]">
                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>{{ $isFr ? 'Vérifié' : 'Verified' }}
                    </p>
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
        <p class="px-3 pb-3 text-[12.5px] leading-snug text-[#8A857A]">
            {{ $isFr ? 'Cet atelier n’a pas encore publié de produit.' : 'This workshop has not published a product yet.' }}
        </p>
    @else
        <div class="mob-scroll">
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
                    $pPrice = $pArr ? ($prod['price'] ?? null) : $prod->price_amount;
                    $pCur   = $pArr ? ($prod['currency'] ?? null) : $prod->price_currency;
                    $pCert  = $pArr ? (bool) ($prod['has_authenticity_certificate'] ?? false) : false;
                    $pCat   = $pArr
                        ? $mIndustry
                        : ($prod->category ? ($isFr ? $prod->category->name_fr : ($prod->category->name_en ?? $prod->category->name_fr)) : $mIndustry);
                @endphp
                <div class="mob-prod">
                    <a href="{{ route('products.show', ['slug' => $pSlug, 'lang' => $lang]) }}" class="block relative">
                        @if($pImage)
                            <img src="{{ asset('storage/' . $pImage) }}" alt="{{ $pName }}">
                        @else
                            <span class="block w-[99px] h-[99px] rounded-[9px] bg-[#F8F4EC] flex items-center justify-center">
                                <i data-lucide="image" class="w-6 h-6 text-[#B8B2A4]"></i>
                            </span>
                        @endif
                    </a>
                    <p class="mt-1.5 text-[11px] font-semibold leading-tight text-[#1B1B18]">{{ \Illuminate\Support\Str::limit($pName, 28) }}</p>
                    @if($pCat)
                        <p class="text-[10.5px] text-[#8A857A]">{{ \Illuminate\Support\Str::limit($pCat, 26) }}</p>
                    @endif
                    <div class="mt-1 flex items-center justify-between gap-1">
                        @if($pPrice)
                            <span class="text-[10px] font-extrabold text-[#1B1B18] whitespace-nowrap">{{ in_array($pCur, [null, '', 'XAF'], true) ? 'FCFA' : $pCur }} {{ number_format((float) $pPrice, 0, ',', ' ') }}</span>
                        @else
                            <span class="text-[10.5px] text-[#8A857A]">{{ $isFr ? 'Sur devis' : 'On quote' }}</span>
                        @endif
                        <a href="{{ route('products.show', ['slug' => $pSlug, 'lang' => $lang]) }}"
                           aria-label="{{ $isFr ? 'Demander un devis' : 'Request a quote' }}"
                           class="flex-none w-6 h-6 rounded-md bg-[#157A43] text-white flex items-center justify-center">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
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
            <h2 class="text-[15px] font-extrabold text-[#1B1B18]">{{ $isFr ? 'À propos' : 'About' }} {{ \Illuminate\Support\Str::limit($mName, 22) }}</h2>
            @if($mDesc)
                <p class="mt-2 text-[12.5px] leading-relaxed text-[#3B382F] whitespace-pre-line">{{ $mDesc }}</p>
            @else
                <p class="mt-2 text-[12.5px] leading-relaxed text-[#8A857A]">
                    {{ $isFr ? 'Cet atelier n’a pas encore rédigé de présentation.' : 'This workshop has not written a description yet.' }}
                </p>
            @endif
        </div>
        <dl class="rounded-[10px] border border-[var(--mob-line)] p-2.5 divide-y divide-[#F5F1E8] self-start">
            @forelse($mFacts as [$k, $v])
                <div class="py-1.5 first:pt-0 last:pb-0">
                    <dt class="text-[10.5px] text-[#8A857A]">{{ $k }}</dt>
                    <dd class="text-[12px] font-semibold text-[#1B1B18] leading-snug">{{ $v }}</dd>
                </div>
            @empty
                <p class="text-[11.5px] text-[#8A857A]">{{ $isFr ? 'Fiche à compléter par l’artisan.' : 'The artisan has not filled this in.' }}</p>
            @endforelse
        </dl>
    </div>

    {{-- WORKSHOP: coarse location only. gps_lat/gps_lng are never printed and
         never linked to a map pin; they place a person's home to a few metres. --}}
    <div id="panel-workshop" data-mob-panel="workshop" class="p-3 hidden">
        @if($mWorkshop || $mPlace)
            <dl class="space-y-2.5">
                @if($mWorkshop)
                    <div><dt class="text-[10.5px] text-[#8A857A]">{{ $isFr ? 'Atelier' : 'Workshop' }}</dt>
                        <dd class="text-[13px] font-semibold text-[#1B1B18]">{{ $mWorkshop }}</dd></div>
                @endif
                @if($mPlace)
                    <div><dt class="text-[10.5px] text-[#8A857A]">{{ $isFr ? 'Localisation' : 'Location' }}</dt>
                        <dd class="text-[13px] font-semibold text-[#1B1B18]">{{ $mPlace }}</dd></div>
                @endif
            </dl>
            <p class="mt-3 text-[11.5px] leading-snug text-[#8A857A]">
                {{ $isFr
                    ? 'Seules la ville et la région sont publiées. Les coordonnées exactes ne sont jamais affichées publiquement.'
                    : 'Only the town and region are published. Exact coordinates are never shown publicly.' }}
            </p>
        @else
            <p class="text-[12.5px] text-[#8A857A]">{{ $isFr ? 'Aucune adresse d’atelier au dossier.' : 'No workshop address on file.' }}</p>
        @endif
    </div>

    {{-- STATS: every figure carries the basis it was measured on, and one the
         platform does not measure says so. A counter reading zero and one that
         is not tracked are different facts, and only the first is a judgement
         on the artisan. --}}
    <div id="panel-stats" data-mob-panel="stats" class="p-3 hidden">
        <dl class="grid grid-cols-2 gap-2.5">
            @foreach($mStats as $label => $stat)
                <div class="rounded-[10px] border border-[var(--mob-line)] p-2.5">
                    <dt class="text-[10.5px] text-[#8A857A]">{{ $label }}</dt>
                    @if($stat['known'] ?? false)
                        <dd class="text-[19px] font-extrabold text-[#1B1B18] leading-none mt-1">{{ $stat['value'] }}</dd>
                    @else
                        <dd class="text-[12px] font-semibold text-[#8A857A] mt-1">{{ $mNotTracked }}</dd>
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
        @forelse($mReviews as $rev)
            <article class="py-2.5 border-b border-[#F5F1E8] last:border-0">
                <p class="text-[12px] font-semibold text-[#1B1B18]">{{ $rev->rating }}/5</p>
                <p class="mt-1 text-[12.5px] leading-relaxed text-[#3B382F]">{{ $rev->comment }}</p>
            </article>
        @empty
            <p class="text-[12.5px] leading-snug text-[#8A857A]">
                {{ $isFr
                    ? 'Aucun avis n’a encore été publié sur cet atelier. Le registre des avis est vide — aucune note n’est donc affichée.'
                    : 'No review has been published about this workshop yet. The review register is empty, so no rating is shown.' }}
            </p>
        @endforelse
    </div>

    {{-- AWARDS: the design's ACHIEVEMENTS tab lists SIARC, UNESCO and ministry
         honours. The platform keeps no register of national honours and
         `business_awards` is empty; inventing one would attach a state
         endorsement to a private company that has none. --}}
    <div id="panel-awards" data-mob-panel="awards" class="p-3 hidden">
        @forelse($mAwards as $award)
            <article class="py-2.5 border-b border-[#F5F1E8] last:border-0">
                <p class="text-[13px] font-semibold text-[#1B1B18]">{{ is_array($award) ? $award['title'] : $award->title }}</p>
                @php $aYear = is_array($award) ? ($award['year'] ?? null) : ($award->year ?? null); @endphp
                @if($aYear)<p class="text-[11px] text-[#8A857A]">{{ $aYear }}</p>@endif
            </article>
        @empty
            <p class="text-[12.5px] leading-snug text-[#8A857A]">
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
        <span class="mob-disc"><i data-lucide="shield-check" class="w-6 h-6 text-[var(--mob-gold)]"></i></span>
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

        if (window.lucide) { window.lucide.createIcons(); }
    })();
</script>
</div>
