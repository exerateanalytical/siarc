@php
    /*
     * The Digital Product Passport — the canonical living record of one piece.
     *
     * The distinction from a certificate is the whole design of this page, and
     * it is worth stating plainly because the two look superficially alike. A
     * certificate is frozen at the moment it is issued: it asserts what was true
     * then, it carries a signature over exactly those facts, and when the record
     * moves on it reports itself superseded rather than quietly updating. The
     * passport is the opposite. It is never frozen. It shows the register as it
     * stands right now, with a last-updated stamp, and every certificate in the
     * family is a photograph of it taken on a particular day.
     *
     * The route hands us only $lang and $product; everything else is gathered
     * here. That is the same trade the certificate hub makes, for the same
     * reason: the gathering is the page's substance, and keeping it beside the
     * markup lets a reviewer confirm that each printed line has a query behind
     * it. The cost is database work in a view, acceptable for a page that is one
     * product deep and rate-limited at the route.
     *
     * ── The privacy decision ─────────────────────────────────────────────
     *
     * `spec/80a-digital-product-passport.json` marks this record
     * `public_visibility: true`, `history_retention: PERMANENT`,
     * `soft_delete: false` — while the same record holds the artisan's village
     * and GPS coordinates, the workshop's location, and (in `spec/80b`) the
     * current owner's legal name, city and contact reference.
     * `docs/ahts/20-conflicts.md` item 9 records that as a defect in the
     * specification rather than a feature, and this page implements the
     * recommendation there, not the specification as written:
     *
     *   - No GPS pair, no village, no workshop street address, no phone or
     *     email is rendered anywhere on this page, for anyone. A permanent
     *     public record tying a named craftsperson to their home coordinates is
     *     a safety question before it is a privacy one.
     *   - The maker IS named. Attribution is the point of the platform and the
     *     artisan opted into it by registering the piece — but their location is
     *     coarsened to the region, which is a provenance fact, rather than the
     *     address, which is where they sleep.
     *   - An institutional holder may be named. A museum or a gallery is content
     *     to be publicly associated with a piece; being named is often the point
     *     of their acquiring it.
     *   - An individual holder is shown as a role and a country only — "Private
     *     collector, FR" — never a legal name, unless the register itself has
     *     recorded them at `verification_level: institution`. Publishing a
     *     private collector's name and city beside a valuation is how people get
     *     targeted, and nobody consented to that by buying a bowl.
     *
     * What is deliberately NOT hidden is that a transfer happened at all. The
     * country, the date and the sequence stay, because withholding the person
     * must not amount to concealing the chain — that would damage the one thing
     * a provenance register exists to provide.
     */

    $isFr       = $lang === 'fr';
    $siacUser   = session('siac_user');
    $dfShowHelp = true;

    $business    = $product->business;
    $productName = ($isFr ? $product->name_fr : ($product->name_en ?: $product->name_fr)) ?: $product->name_fr;
    $makerName   = $business?->name_fr;
    $q           = $isFr ? '' : '?lang=en';

    /* ── The permanent identifiers ───────────────────────────────────────
       The passport number IS the PRN. A second numbering scheme for the same
       object would be one more number for a curator to mistype, and the
       specification's own rule is that identifiers are immutable and never
       reused — which is an argument for fewer of them, not more. */
    $prn = \App\Support\ProvenanceRegistry::prnFor($product);
    $oln = \App\Support\ProvenanceRegistry::olnFor($product);
    $gan = $business ? \App\Support\ProvenanceRegistry::ganFor($business) : null;

    /* ── The registers behind the record ─────────────────────────────────
       Null is an ordinary answer from any of these, never an error. */
    $coa = \App\Support\ProductCertificate::forProduct($product);
    $avc = $business ? \App\Support\ArtisanVerification::forBusiness($business) : null;

    /*
     * Signature state is read through signatureState(), never verify().
     * verify() is the buyer-facing check and it WRITES: it increments the
     * certificate's verification counter and stamps last_verified_at. A page
     * that logged a verification every time a crawler passed would corrupt the
     * one statistic on the certificate that carries meaning.
     */
    $coaSignature = $coa ? \App\Support\ProductCertificate::signatureState($coa) : null;

    $chain   = \App\Support\ProvenanceRegistry::chain($product);
    $owner   = \App\Support\ProvenanceRegistry::currentOwner($product);
    $timeline = \App\Support\ProvenanceDossier::timeline($product, $lang);
    $journey  = \App\Support\ProvenanceDossier::journey($product, $lang);
    $summary  = \App\Support\ProvenanceDossier::summary($product);
    $legacy   = \App\Support\ProvenanceDossier::legacyIndex($product, $lang);
    $events   = \App\Support\ProvenanceDossier::events($product);
    $checks   = \App\Support\ProductFlags::checks($product);

    $transfers = \Illuminate\Support\Facades\DB::table('ownership_transfers')
        ->where('product_id', $product->id)->orderBy('id')->get()->all();

    $consignments = \Illuminate\Support\Facades\DB::table('export_consignments')
        ->where('product_id', $product->id)->orderBy('id')->get()->all();

    /* The locale is set per call: the page's language comes from the query
       string and the app locale does not follow it. */
    $date = fn ($value) => $value
        ? \Illuminate\Support\Carbon::parse($value)->locale($isFr ? 'fr' : 'en')->translatedFormat('j F Y')
        : null;

    /*
     * The last-updated stamp is the passport's most important single field,
     * because it is what tells a reader how stale the certificate in their hand
     * might be. It is computed as the newest timestamp across everything the
     * page shows, so a change to the chain moves it even when the product row
     * has not been touched.
     */
    $stamps = array_filter([
        $product->updated_at,
        $coa?->updated_at,
        ...array_map(fn ($h) => $h->updated_at ?? $h->created_at, $chain),
        ...array_map(fn ($e) => $e->updated_at ?? $e->created_at, $events),
        ...array_map(fn ($t) => $t->updated_at ?? $t->created_at, $transfers),
    ]);

    $lastUpdated = $stamps
        ? collect($stamps)->map(fn ($s) => \Illuminate\Support\Carbon::parse($s))->max()
        : null;

    /*
     * The disclosure rule, in one place so it cannot drift between the chain
     * and the current-holder panel.
     *
     * An institution is either an explicitly institutional entity type or a
     * holder the register itself placed at `verification_level: institution`.
     * The maker is always named — they registered the piece under their own
     * trading name, which is a public act.
     */
    $INSTITUTIONAL = ['gallery', 'museum', 'government', 'foundation', 'company', 'institution'];

    $nameable = fn ($holder) => (bool) $holder->is_original_creator
        || in_array(strtolower((string) $holder->entity_type), $INSTITUTIONAL, true)
        || $holder->verification_level === 'institution';

    /* What we call a holder we are not naming. Role and country, nothing more. */
    $roleOf = function ($holder) use ($isFr) {
        $type = strtolower((string) $holder->entity_type);

        return match ($type) {
            'dealer'     => $isFr ? 'Marchand privé' : 'Private dealer',
            'trust'      => $isFr ? 'Fiducie privée' : 'Private trust',
            default      => $isFr ? 'Collectionneur privé' : 'Private collector',
        };
    };

    $labelFor = fn ($holder) => $nameable($holder)
        ? $holder->legal_name
        : $roleOf($holder) . ($holder->country_code ? ', ' . strtoupper($holder->country_code) : '');

    /*
     * Attributes carry the materials, dimensions and appearance the
     * specification asks for. They are printed as the register holds them —
     * whatever templates the artisan filled in — rather than forced into a
     * fixed materials/dimensions/appearance triple that would print empty rows
     * for a piece nobody measured.
     */
    $attributes = $product->attributes
        ->filter(fn ($a) => filled($isFr ? $a->value_fr : ($a->value_en ?: $a->value_fr)))
        ->sortBy(fn ($a) => $a->template?->name_fr)
        ->values();

    $images = $product->images->sortByDesc('is_cover')->sortBy('sort_order')->values();

    /* The digital identity block. Every value here is read from the issued
       certificate row; nothing is computed for display and nothing is faked.
       A null simply means the register does not hold it. */
    $contentHash = $coa?->content_hash;
    $imagePhash  = $coa?->image_phash;

    $summaryLabels = [
        'years_documented'  => ['fr' => 'Années documentées',     'en' => 'Years documented'],
        'ownership_changes' => ['fr' => 'Changements de mains',   'en' => 'Ownership changes'],
        'countries'         => ['fr' => 'Pays traversés',         'en' => 'Countries recorded'],
        'exhibitions'       => ['fr' => 'Expositions',            'en' => 'Exhibitions'],
        'publications'      => ['fr' => 'Publications',           'en' => 'Publications'],
        'restorations'      => ['fr' => 'Restaurations',          'en' => 'Restorations'],
        'conservations'     => ['fr' => 'Conservations',          'en' => 'Conservation records'],
        'awards'            => ['fr' => 'Distinctions',           'en' => 'Awards'],
        'evidence'          => ['fr' => 'Pièces justificatives',  'en' => 'Evidence items'],
        'events'            => ['fr' => 'Événements documentés',  'en' => 'Documented events'],
    ];

    $checkLabels = [
        /* Keyed exactly as ProductFlags::checks() returns. A key it omits is a
           question the platform cannot answer, and gets neither tick nor cross
           below — a greyed box still reads as a box somebody looked at. */
        'not_reported_stolen'     => ['fr' => 'Aucun signalement de vol sur ce registre', 'en' => 'No theft report on this register'],
        'not_under_investigation' => ['fr' => 'Aucune enquête signalée',                  'en' => 'No investigation reported'],
        'ownership_undisputed'    => ['fr' => 'Aucun litige de propriété signalé',        'en' => 'No ownership dispute reported'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $isFr
        ? 'Le passeport numérique de cette pièce : le registre vivant dont les certificats sont des instantanés.'
        : 'The digital passport for this piece: the living record its certificates are snapshots of.' }}">
    <title>{{ $isFr ? 'Passeport numérique' : 'Digital passport' }} — {{ $productName }} — Artisan Hub 237</title>

    <style>
        /* This page's own colour tokens. They used to be an inline
           `tailwind.config` compiled in the browser; the stylesheet is
           static now and reads them from here, so a token that means a
           different shade on another page still resolves per page —
           including inside shared partials. See tailwind.config.cjs. */
        :root {
            --c-gold: 229 168 46;
            --c-leaf: 22 76 40;
            --f-serif: "Playfair Display", Georgia, serif;
        }
    </style>
    @include('pages.partials.icons')
    <style>
        body { font-family: 'Poppins', system-ui, sans-serif; }
        html, body { overflow-x: clip; }
        .dpp-ident { font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
                     font-size: 12.5px; letter-spacing: .02em; word-break: break-all; }
    </style>
    @include('pages.partials.ui-kit')
    @include('pages.partials.favicon')
    {{-- The one stylesheet. Built by `npm run build:assets`; see tailwind.config.cjs. --}}
    <link rel="stylesheet" href="{{ asset_v('vendor/app.css') }}">
</head>
<body class="bg-[#F5F3EE] dark:bg-[#0A0C09] text-[#1D1B16] dark:text-[#F3EFE7] antialiased">

@include('pages.partials.directory-header')

<main class="max-w-[880px] mx-auto px-4 sm:px-6 py-8 sm:py-12">

    <nav class="flex items-center gap-2 text-[12.5px] mb-5" aria-label="Breadcrumb">
        <a href="{{ route('home', ['lang' => $lang]) }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56]">{{ $isFr ? 'Accueil' : 'Home' }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <a href="/galerie/produits/{{ $product->slug }}{{ $q }}" class="text-[#6F6B60] dark:text-[#868778] hover:text-leaf hover:dark:text-[#339B56]">{{ $productName }}</a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
        <span>{{ $isFr ? 'Passeport' : 'Passport' }}</span>
    </nav>

    {{-- ── What this document is ─────────────────────────────────────────
         Said at the top and in the plainest words available, because a reader
         arriving from a printed certificate needs to know within one sentence
         why the two might disagree. --}}
    <header class="mb-7">
        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Registre vivant' : 'Living record' }}</span>
        <h1 class="mt-3 font-serif text-[26px] sm:text-[34px] font-bold text-[#02301B] dark:text-[#339B56] leading-tight">
            {{ $isFr ? 'Passeport numérique du produit' : 'Digital product passport' }}
        </h1>
        <p class="mt-2 text-[14px] text-[#3A3A35] dark:text-[#F3EFE7]">
            {{ $productName }}@if($makerName) — {{ $makerName }}@endif
        </p>
        <p class="mt-3 text-[13.5px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed max-w-[660px]">
            {{ $isFr
               ? "Cette page est le dossier vivant de la pièce : elle montre le registre tel qu'il est aujourd'hui. Un certificat, lui, est figé au moment de son émission — c'est un instantané de ce dossier, pris un jour donné. Quand les deux divergent, c'est cette page qui fait foi et le certificat qui a vieilli."
               : 'This page is the piece’s living file: it shows the register as it stands today. A certificate, by contrast, is frozen at issue — it is a snapshot of this record taken on one particular day. Where the two disagree, this page is current and the certificate has aged.' }}
        </p>
        @if($lastUpdated)
            <p class="mt-3 text-[12.5px] text-[#6F6B60] dark:text-[#868778]">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 inline-block align-[-2px] text-[#B9B4A9]"></i>
                {{ $isFr ? 'Dernière mise à jour' : 'Last updated' }} :
                <span class="font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $date($lastUpdated) }}</span>
            </p>
        @endif
        {{-- The one line the privacy decision owes the reader. Withholding
             something silently is indistinguishable from not having it. --}}
        <p class="mt-2 text-[12px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[660px]">
            {{ $isFr
               ? "Ce passeport publie le registre, pas les personnes : les coordonnées GPS, les adresses d'atelier, les coordonnées de contact et le nom légal des détenteurs privés sont conservés au registre mais volontairement non publiés."
               : 'This passport publishes the record, not the people: GPS coordinates, workshop addresses, contact details and the legal names of private holders are held in the register but deliberately withheld from this page.' }}
        </p>
    </header>

    {{-- ── Passport identity ─────────────────────────────────────────────
         The passport number is the PRN itself; the UUID is the reference that
         never depends on a numbering scheme surviving. --}}
    <section class="ui-card p-5 sm:p-6">
        <h2 class="ui-card-title">{{ $isFr ? 'Identité du passeport' : 'Passport identity' }}</h2>
        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Numéro de passeport (PRN)' : 'Passport number (PRN)' }}</dt>
                <dd class="ui-dd dpp-ident">{{ $prn }}</dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Grand livre de propriété (OLN)' : 'Ownership ledger (OLN)' }}</dt>
                <dd class="ui-dd dpp-ident">{{ $oln }}</dd>
            </div>
            @if($gan)
                <div>
                    <dt class="ui-dt">{{ $isFr ? "Numéro d'artisan (GAN)" : 'Artisan number (GAN)' }}</dt>
                    <dd class="ui-dd dpp-ident">{{ $gan }}</dd>
                </div>
            @endif
            @if($product->uuid)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Référence permanente (UUID)' : 'Permanent reference (UUID)' }}</dt>
                    <dd class="ui-dd dpp-ident">{{ $product->uuid }}</dd>
                </div>
            @endif
        </dl>
    </section>

    {{-- ── The object ────────────────────────────────────────────────────
         Only rows the register actually holds. A product with no category
         prints no category line rather than an empty one. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? "L'objet" : 'The object' }}</h2>
        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Désignation' : 'Name' }}</dt>
                <dd class="ui-dd">{{ $productName }}</dd>
            </div>
            @if($product->category)
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Métier / catégorie' : 'Trade / category' }}</dt>
                    <dd class="ui-dd">{{ $isFr ? $product->category->name_fr : ($product->category->name_en ?: $product->category->name_fr) }}</dd>
                </div>
            @endif
            @if($date($product->registered_at ?? $product->created_at))
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Enregistré le' : 'Registered on' }}</dt>
                    <dd class="ui-dd">{{ $date($product->registered_at ?? $product->created_at) }}</dd>
                </div>
            @endif
            @if($product->sku)
                <div>
                    <dt class="ui-dt">{{ $isFr ? "Référence de l'atelier" : 'Workshop reference' }}</dt>
                    <dd class="ui-dd dpp-ident">{{ $product->sku }}</dd>
                </div>
            @endif
        </dl>
        @if($isFr ? $product->description_fr : ($product->description_en ?: $product->description_fr))
            <p class="mt-4 text-[13px] text-[#3A3A35] dark:text-[#F3EFE7] leading-relaxed">
                {{ $isFr ? $product->description_fr : ($product->description_en ?: $product->description_fr) }}
            </p>
        @endif
    </section>

    {{-- ── The maker ─────────────────────────────────────────────────────
         Named, because attribution is what the artisan registered for. Placed
         no more precisely than the region: the region is a provenance fact,
         the address is where somebody sleeps. --}}
    @if($business)
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Le créateur' : 'The maker' }}</h2>
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Atelier' : 'Workshop' }}</dt>
                    <dd class="ui-dd">
                        <a href="/galerie/artisans/{{ $business->slug }}{{ $q }}" class="text-leaf dark:text-[#339B56] underline">{{ $makerName }}</a>
                    </dd>
                </div>
                @if($business->region)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Région' : 'Region' }}</dt>
                        <dd class="ui-dd">{{ $business->region->name_fr }}</dd>
                    </div>
                @endif
                @if($business->year_established)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Atelier établi en' : 'Workshop established' }}</dt>
                        <dd class="ui-dd">{{ $business->year_established }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="ui-dt">{{ $isFr ? "Vérification de l'artisan" : 'Artisan verification' }}</dt>
                    <dd class="ui-dd">
                        @if($avc)
                            <span class="ui-pill ui-pill-ok">{{ $isFr ? 'Certificat émis' : 'Certificate issued' }}</span>
                        @else
                            {{-- No AVC is the ordinary state, not a failure. The
                                 register refuses to issue one for an artisan
                                 nobody has checked, and the page has to say so
                                 in words rather than leave a gap. --}}
                            <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Non vérifié' : 'Not verified' }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
            <p class="mt-4 text-[12px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[600px]">
                {{ $isFr
                   ? "L'emplacement de l'atelier n'est indiqué qu'au niveau de la région. Les coordonnées précises et les coordonnées de contact sont détenues au registre et ne sont pas publiées."
                   : 'The workshop is placed no more precisely than its region. Exact coordinates and contact details are held in the register and are not published.' }}
            </p>
        </section>
    @endif

    {{-- ── Materials, dimensions, appearance ─────────────────────────────
         Printed as the register holds them, template by template, rather than
         forced into a fixed triple that would print empty rows for a piece
         nobody measured. --}}
    @if($attributes->isNotEmpty())
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Matériaux, dimensions et aspect' : 'Materials, dimensions and appearance' }}</h2>
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($attributes as $attribute)
                    <div>
                        {{-- The English template name where the register holds
                             one, the French where it does not. Never a
                             translation invented at render time. --}}
                        <dt class="ui-dt">{{ ($isFr ? $attribute->template?->name_fr : ($attribute->template?->name_en ?: $attribute->template?->name_fr)) ?: ($isFr ? 'Caractéristique' : 'Attribute') }}</dt>
                        <dd class="ui-dd">
                            {{ $isFr ? $attribute->value_fr : ($attribute->value_en ?: $attribute->value_fr) }}@if($attribute->unit) {{ $attribute->unit }}@endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    {{-- ── Photographs ───────────────────────────────────────────────────
         The pictures the content and perceptual hashes below were computed
         over, which is why they belong on the passport rather than only on the
         listing: they are part of what is certified. --}}
    @if($images->isNotEmpty())
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Photographies enregistrées' : 'Recorded photographs' }}</h2>
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($images as $image)
                    <figure class="rounded-lg overflow-hidden bg-[#EDEAE3] dark:bg-[#0A0C09]">
                        <img src="{{ asset('storage/' . ltrim($image->file_path, '/')) }}"
                             alt="{{ $isFr ? $image->caption_fr : ($image->caption_en ?: $image->caption_fr) }}"
                             class="w-full h-[150px] object-cover" loading="lazy">
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ── Current status ────────────────────────────────────────────────
         Who holds it now, under the disclosure rule. An institution is named;
         a private holder is a role and a country. --}}
    @if($owner)
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Statut actuel' : 'Current status' }}</h2>
            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Détenteur actuel' : 'Current holder' }}</dt>
                    <dd class="ui-dd">{{ $labelFor($owner) }}</dd>
                </div>
                @if($date($owner->owned_from))
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Détenu depuis' : 'Held since' }}</dt>
                        <dd class="ui-dd">{{ $date($owner->owned_from) }}</dd>
                    </div>
                @endif
                @if($owner->country_code)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Pays' : 'Country' }}</dt>
                        <dd class="ui-dd">{{ strtoupper($owner->country_code) }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Standing au registre' : 'Standing on the register' }}</dt>
                    <dd class="ui-dd">
                        @if(in_array($owner->verification_level, ['verified', 'institution'], true))
                            <span class="ui-pill ui-pill-ok">{{ $isFr ? 'Identité vérifiée' : 'Identity verified' }}</span>
                        @else
                            {{-- "Declared" is what the register holds: the holder
                                 said who they are and nobody checked. --}}
                            <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Identité déclarée' : 'Identity declared' }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
            @if(! $nameable($owner))
                <p class="mt-4 text-[12px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[600px]">
                    {{ $isFr
                       ? "Le détenteur actuel est un particulier. Son nom, sa ville et ses coordonnées sont détenus au registre et ne sont pas publiés ; le transfert lui-même, sa date et son pays le sont."
                       : 'The current holder is a private individual. Their name, city and contact details are held in the register and not published; the transfer itself, its date and its country are.' }}
                </p>
            @endif
        </section>
    @endif

    {{-- ── The ownership chain ───────────────────────────────────────────
         Every holder, in the register's own order, so the chain can be read
         end to end even where a party is not named. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Chaîne de propriété' : 'Ownership chain' }}</h2>
        <p class="mt-1 text-[12.5px] text-[#6F6B60] dark:text-[#868778]">
            {{ $isFr ? 'Détenteurs enregistrés' : 'Recorded holders' }} : {{ count($chain) }}
        </p>
        <ol class="mt-4 space-y-3">
            @foreach($chain as $holder)
                <li class="flex items-start gap-3">
                    <span class="ui-pill ui-pill-neutral mt-0.5">{{ $holder->sequence }}</span>
                    <div class="min-w-0">
                        <p class="text-[13.5px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">{{ $labelFor($holder) }}</p>
                        <p class="text-[12px] text-[#6F6B60] dark:text-[#868778]">
                            @if($holder->is_original_creator){{ $isFr ? 'Créateur' : 'Maker' }} · @endif
                            {{ $date($holder->owned_from) }}
                            @if($holder->owned_until) — {{ $date($holder->owned_until) }}
                            @else — {{ $isFr ? "aujourd'hui" : 'present' }} @endif
                            @if($holder->country_code) · {{ strtoupper($holder->country_code) }} @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- ── Timeline ──────────────────────────────────────────────────────
         Registration, ownership and documented events in one chronological
         list, merged in ProvenanceDossier rather than stitched here so a new
         kind of event cannot be silently dropped. Ownership labels are rebuilt
         under the disclosure rule, because the dossier's label names names. --}}
    @if($timeline)
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Chronologie' : 'Timeline' }}</h2>
            <ol class="mt-4 space-y-3 border-l border-[#E3DFD6] dark:border-[#262B21] pl-4">
                @foreach($timeline as $entry)
                    @php
                        // The registry's own ownership labels embed legal names.
                        // Rebuilt here from the chain rather than printed, so the
                        // privacy rule holds on this list too.
                        $isOwnership = $entry['type'] === 'ownership';
                        $holder      = $isOwnership
                            ? collect($chain)->firstWhere('owner_ref', $entry['reference'])
                            : null;
                    @endphp
                    <li>
                        <p class="text-[13px] font-semibold text-[#1D1B16] dark:text-[#F3EFE7]">
                            @if($holder)
                                @if($holder->is_original_creator)
                                    {{ $isFr ? 'Créé et détenu à l\'origine par ' : 'Created and first held by ' }}{{ $labelFor($holder) }}
                                @else
                                    {{ $isFr ? 'Acquis par ' : 'Acquired by ' }}{{ $labelFor($holder) }}
                                @endif
                            @elseif($isOwnership)
                                {{ $isFr ? 'Changement de détenteur enregistré' : 'Recorded change of holder' }}
                            @else
                                {{ $entry['label'] }}
                            @endif
                        </p>
                        <p class="text-[12px] text-[#6F6B60] dark:text-[#868778]">
                            {{ $date($entry['date']) ?: ($isFr ? 'Date non enregistrée' : 'Date not recorded') }}
                            @if(! $isOwnership && $entry['organisation']) · {{ $entry['organisation'] }} @endif
                            @if($entry['country']) · {{ strtoupper($entry['country']) }} @endif
                            @if($entry['verified'])
                                · <span class="text-leaf dark:text-[#339B56]">{{ $isFr ? 'vérifié' : 'verified' }}</span>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    {{-- ── Journey ───────────────────────────────────────────────────────
         Countries only, first arrival wins. No default origin: a piece whose
         rows carry no country has an undocumented journey, and printing
         "Cameroon" anyway would invent the fact a reader most wants checked. --}}
    @if($journey)
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Parcours géographique' : 'Geographic journey' }}</h2>
            <ol class="mt-4 flex flex-wrap items-center gap-2">
                @foreach($journey as $leg)
                    <li class="flex items-center gap-2">
                        <span class="ui-pill ui-pill-neutral">{{ $leg['country'] }}</span>
                        @if(! $loop->last)
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-[#B9B4A9]"></i>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    {{-- ── Provenance summary ────────────────────────────────────────────
         Counts, each of them a query. A zero here is a real zero. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Résumé de la provenance' : 'Provenance summary' }}</h2>
        <dl class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach($summaryLabels as $key => $label)
                @if(array_key_exists($key, $summary))
                    <div>
                        <dt class="ui-dt">{{ $label[$lang] }}</dt>
                        {{-- years_documented comes back as a raw diff, so a file
                             three months old reads "0.164". Floored to whole
                             years for display: rounding down never overstates
                             how long the record has been kept, which is the only
                             direction that would be a lie. --}}
                        <dd class="ui-dd">{{ $key === 'years_documented' ? (int) floor((float) $summary[$key]) : $summary[$key] }}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </section>

    {{-- ── Legacy Index ──────────────────────────────────────────────────
         Printed exactly as returned, over its own maximum: the denominator
         moves, because a category with nothing to assess drops out entirely
         and a score shown out of a fixed 100 would misreport it. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Indice de patrimoine' : 'Legacy Index' }}</h2>
        <p class="mt-3 text-[26px] font-bold text-[#02301B] dark:text-[#339B56]">{{ $legacy['total'] }}/{{ $legacy['max'] }}</p>
        <p class="mt-2 text-[12.5px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[560px]">
            {{ $isFr
               ? "Cet indice mesure la complétude du dossier documentaire, et non la valeur ni la qualité de l'œuvre ni celle de son artisan."
               : 'This index measures how completely the record is documented. It is not a measure of the merit of the work, nor of the artisan.' }}
        </p>
        <ul class="mt-4 space-y-2">
            @foreach($legacy['categories'] as $category)
                <li class="text-[12.5px] text-[#3A3A35] dark:text-[#F3EFE7]">
                    <span class="font-semibold">{{ $category['score'] }}/{{ $category['max'] }}</span>
                    — {{ $category['basis'] }}
                </li>
            @endforeach
        </ul>
    </section>

    {{-- ── Trust summary ─────────────────────────────────────────────────
         Only the keys ProductFlags::checks() returned. A key it omitted gets
         neither a tick nor a cross, because a printed "no" is a claim we are
         no more entitled to than the "yes". --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Signalements au registre' : 'Reports to the register' }}</h2>
        <p class="mt-1 text-[12.5px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[560px]">
            {{ $isFr
               ? "Ce que personne n'a signalé à ce registre. C'est un négatif utile, pas une enquête : l'absence de signalement de vol n'est pas une preuve de titre propre."
               : 'What nobody has reported to this register. That is a useful negative, not an investigation: the absence of a theft report is not proof of clean title.' }}
        </p>
        <ul class="mt-4 space-y-2">
            @foreach($checkLabels as $key => $label)
                @if(array_key_exists($key, $checks))
                    <li class="flex items-center gap-2 text-[13px]">
                        @if($checks[$key])
                            <i data-lucide="check" class="w-4 h-4 text-leaf dark:text-[#339B56]"></i>
                            <span>{{ $label[$lang] }}</span>
                        @else
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-[#B4231F] dark:text-[#F0555C]"></i>
                            <span>{{ $isFr ? 'Signalement actif' : 'Active report' }} — {{ $label[$lang] }}</span>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </section>

    {{-- ── Certificates issued from this record ──────────────────────────
         The relationship the spec cares about, made navigable: each of these
         is a photograph of the page above, taken on the date shown. --}}
    <section class="ui-card p-5 sm:p-6 mt-4">
        <h2 class="ui-card-title">{{ $isFr ? 'Certificats issus de ce registre' : 'Certificates issued from this record' }}</h2>
        <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="ui-dt">{{ $isFr ? "Certificat d'authenticité" : 'Certificate of authenticity' }}</dt>
                <dd class="ui-dd">
                    @if($coa)
                        <span class="dpp-ident">{{ $coa->certificate_no }}</span>
                        <span class="block text-[12px] text-[#6F6B60] dark:text-[#868778] font-normal">{{ $isFr ? 'Émis le' : 'Issued' }} {{ $date($coa->issued_at) }}</span>
                    @else
                        <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Non émis' : 'Not issued' }}</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? 'Certificats de transfert' : 'Transfer certificates' }}</dt>
                <dd class="ui-dd">{{ count($transfers) }}</dd>
            </div>
            <div>
                <dt class="ui-dt">{{ $isFr ? "Expéditions d'exportation" : 'Export consignments' }}</dt>
                <dd class="ui-dd">{{ count($consignments) }}</dd>
            </div>
        </dl>
        <div class="mt-5 flex flex-wrap gap-2">
            <a href="/certificats/{{ $product->slug }}{{ $q }}" class="ui-btn ui-btn-secondary ui-btn-sm">
                {{ $isFr ? 'Tous les certificats' : 'All certificates' }}
            </a>
            @if($coa)
                <a href="/verifier{{ $isFr ? '?' : '?lang=en&' }}ref={{ $coa->certificate_no }}" class="ui-btn ui-btn-ghost ui-btn-sm">
                    {{ $isFr ? 'Vérifier un certificat' : 'Verify a certificate' }}
                </a>
            @endif
        </div>
    </section>

    {{-- ── Digital identity ──────────────────────────────────────────────
         Read from the issued certificate row. Nothing here is computed for
         display and nothing is invented: a value the register does not hold
         prints as "not held", because a fabricated fingerprint is worse than
         no fingerprint at all. --}}
    @if($coa)
        <section class="ui-card p-5 sm:p-6 mt-4">
            <h2 class="ui-card-title">{{ $isFr ? 'Identité numérique' : 'Digital identity' }}</h2>
            <p class="mt-1 text-[12.5px] text-[#6F6B60] dark:text-[#868778] leading-relaxed max-w-[600px]">
                {{ $isFr
                   ? "Ces valeurs permettent de détecter une modification du dossier après émission. Elles sont calculées lors de l'émission du certificat, jamais au chargement de cette page."
                   : 'These values let a change to the record after issue be detected. They are computed when the certificate is issued, never when this page is loaded.' }}
            </p>
            <dl class="mt-4 space-y-4">
                @if($contentHash)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Empreinte du contenu (SHA-256)' : 'Content hash (SHA-256)' }}</dt>
                        <dd class="ui-dd dpp-ident">{{ $contentHash }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="ui-dt">{{ $isFr ? 'Empreinte perceptuelle de la photographie' : 'Perceptual image hash' }}</dt>
                    <dd class="ui-dd dpp-ident">
                        {{-- Null is a real answer: no cover photograph, or a file
                             the platform could not read. Said, not filled in. --}}
                        {{ $imagePhash ?: ($isFr ? 'Non détenue — aucune photographie exploitable' : 'Not held — no readable photograph') }}
                    </dd>
                </div>
                @if($coaSignature)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? "Signature de l'autorité (Ed25519)" : 'Authority signature (Ed25519)' }}</dt>
                        <dd class="ui-dd">
                            @if($coaSignature['state'] === 'valid')
                                <span class="ui-pill ui-pill-ok">{{ $isFr ? 'Signature valide' : 'Signature valid' }}</span>
                            @elseif($coaSignature['state'] === 'invalid')
                                <span class="ui-pill ui-pill-danger">{{ $isFr ? 'Signature invalide' : 'Signature invalid' }}</span>
                            @else
                                <span class="ui-pill ui-pill-neutral">{{ $isFr ? 'Non signé' : 'Unsigned' }}</span>
                            @endif
                            @if($coaSignature['kid'])
                                <span class="block mt-1 dpp-ident">{{ $isFr ? 'Clé' : 'Key' }} : {{ $coaSignature['kid'] }}</span>
                            @endif
                        </dd>
                    </div>
                @endif
                {{-- The verification PIN is deliberately NOT printed here.
                     It is the shared secret that lets a holder prove at
                     /verifier that they possess the certificate rather than
                     merely a number copied off a public page. Publishing it on
                     a public, permanently indexed record would leave it doing
                     no work at all. It stays on the certificate, which is
                     handed to the holder; the passport says where to find it. --}}
                @if($coa->verification_pin)
                    <div>
                        <dt class="ui-dt">{{ $isFr ? 'Code de vérification' : 'Verification PIN' }}</dt>
                        <dd class="ui-dd text-[13px] font-normal">
                            {{ $isFr
                               ? "Non publié. Le code figure sur le certificat remis au détenteur ; il n'aurait plus aucune valeur s'il était imprimé sur une page publique."
                               : 'Not published. The PIN is printed on the certificate handed to the holder; it would do no work at all if it appeared on a public page.' }}
                        </dd>
                    </div>
                @endif
            </dl>
        </section>
    @endif

    <p class="mt-6 text-[12px] text-[#6F6B60] dark:text-[#868778] leading-relaxed">
        {{ $isFr
           ? "La signature de l'autorité peut être vérifiée hors ligne contre sa clé publique."
           : 'The authority signature can be checked offline against its published key.' }}
        <a href="/autorite-de-certification{{ $q }}" class="text-leaf dark:text-[#339B56] underline">{{ $isFr ? 'Autorité de certification' : 'Certification Authority' }}</a>
    </p>

</main>

@include('pages.partials.directory-footer')
<script>lucide.createIcons();</script>
</body>
</html>
