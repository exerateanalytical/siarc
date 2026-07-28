<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The reader behind the public artisan profile.
 *
 * The two designs this serves display roughly thirty figures about a named
 * living person: products sold, happy customers, countries reached, a response
 * rate, a review average, a trust score out of a hundred, "last active: today".
 * About a third of them can be measured from this database. The rest cannot,
 * and not because the query is hard — because the platform has no orders, no
 * customers, no sales and no message-response tracking, and never has. There is
 * nothing to count.
 *
 * So the shape every figure comes back in is deliberate:
 *
 *     ['value' => mixed|null, 'basis' => string, 'known' => bool]
 *
 * `known => false` is not an error state and it is not a zero. It is the
 * platform saying, in a sentence a reader can argue with, that it does not
 * measure this. The distinction it protects is the one between *nothing
 * happened* and *we do not track this*, and those two render identically if you
 * let a null fall through to `(int)`. "0 products sold" is a claim about this
 * artisan's business — a damaging one, on a page a buyer uses to decide — and it
 * is a claim we are in no position to make. So no method here ever substitutes
 * zero for an absence, and no view that reads this class can accidentally do so
 * either, because the absent figures carry no number at all to print.
 *
 * The corresponding cost, and it is a real one: the profile page will look
 * emptier than the artwork. Blocks will say "not tracked" where the design shows
 * a confident figure. That is the correct outcome. A directory that fills its
 * own gaps is worthless at exactly the moment somebody relies on it.
 *
 * Two smaller rules worth naming, because both were decided against the design:
 *
 * Exact GPS never leaves this class. The product passport already withholds
 * coordinates and village for the artisan's physical safety (docs/ahts,
 * conflicts item 9), and a public profile is a *more* exposed surface than the
 * passport, not a less exposed one. Location is reported at city and region
 * granularity or not at all.
 *
 * Nothing here writes. `ProvenanceRegistry::ganFor()` and
 * `ArtisanVerification::forBusiness()` both mint a record on first call, which
 * is right for a certificate page somebody deliberately opened and wrong for a
 * profile view: a crawler hitting a thousand imported SIARC pages would issue a
 * thousand artisan numbers. This class reads the stored value and reports its
 * absence instead.
 */
class ArtisanProfile
{
    /** How many published reviews are needed before a mean is a measurement. */
    private const MIN_REVIEWS_FOR_MEAN = 1;

    /* ─────────────────────────────── Language ──────────────────────────── */

    /** Same resolution order as ProvenanceDossier: argument, request, French. */
    private static function lang(?string $lang): string
    {
        $lang ??= app()->getLocale();

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }

    private static function t(string $lang, string $en, string $fr): string
    {
        return $lang === 'fr' ? $fr : $en;
    }

    /* ─────────────────────────── The reported shape ────────────────────── */

    /** A figure the platform measured. */
    private static function known(mixed $value, string $basis): array
    {
        return ['value' => $value, 'basis' => $basis, 'known' => true];
    }

    /**
     * A figure the platform does not hold.
     *
     * The value is null and stays null. A caller wanting to print something has
     * to reach for the basis, which is a sentence, which cannot be mistaken for
     * a measurement — that friction is the entire point of the design.
     */
    private static function unknown(string $basis): array
    {
        return ['value' => null, 'basis' => $basis, 'known' => false];
    }

    /** Reports a nullable column: the value when set, an explained absence when not. */
    private static function optional(mixed $value, string $basisWhenKnown, string $basisWhenAbsent): array
    {
        return $value === null || $value === '' || $value === []
            ? self::unknown($basisWhenAbsent)
            : self::known($value, $basisWhenKnown);
    }

    /* ────────────────────────────── Identity ───────────────────────────── */

    /**
     * Who this artisan is, as the record holds it.
     *
     * Every field is optional in the schema and most are empty on the imported
     * SIARC profiles, so every field reports its own absence rather than the
     * block reporting a single "incomplete". A reader deciding whether to make
     * contact needs to know *which* of the phone, the email and the workshop is
     * missing; "profile 40% complete" tells them nothing they can act on.
     *
     * Years of experience is the field this method exists to get right. The
     * desktop artwork prints "18+ Years Experience" as fixed type. It is
     * arithmetic on `year_established` and nothing else, and `year_established`
     * is null for most rows, so most artisans get an explained absence. An
     * artisan who has worked forty years and never told us the year gets the
     * absence too, which looks unfair and is the only defensible answer: the
     * alternative is a number we made up about somebody's career.
     */
    public static function identity(Business $business, ?string $lang = null): array
    {
        $lang = self::lang($lang);
        $business->loadMissing(['region', 'city', 'industry']);

        $name = $lang === 'fr'
            ? ($business->name_fr ?: $business->name_en)
            : ($business->name_en ?: $business->name_fr);

        $workshop = self::workshopRow($business);

        return [
            'name' => self::optional(
                $name,
                self::t($lang, 'Registered shop name.', 'Nom de la boutique enregistrée.'),
                self::t($lang, 'No name on the record.', 'Aucun nom au dossier.'),
            ),

            // The Global Artisan Number is minted by the provenance registry on
            // first use. Reading is not use: a profile view must not create an
            // identity number, so an unminted GAN is reported absent.
            'artisan_id' => self::optional(
                $business->gan,
                self::t($lang, 'Global Artisan Number, from the provenance registry.',
                              "Numéro mondial d'artisan, issu du registre de provenance."),
                self::t($lang, 'No artisan number has been issued yet.',
                              "Aucun numéro d'artisan n'a encore été émis."),
            ),

            'siarc_code' => self::optional(
                $business->siarc_code,
                self::t($lang, 'Exhibitor code from the SIARC 2026 register.',
                              'Code exposant du registre SIARC 2026.'),
                self::t($lang, 'Not an imported SIARC exhibitor.', 'Pas un exposant SIARC importé.'),
            ),

            'tagline' => self::optional(
                $lang === 'fr' ? ($business->tagline_fr ?: $business->tagline_en) : ($business->tagline_en ?: $business->tagline_fr),
                self::t($lang, 'Written by the artisan.', "Rédigée par l'artisan."),
                self::t($lang, 'The artisan has written no tagline.', "L'artisan n'a pas rédigé d'accroche."),
            ),

            'description' => self::optional(
                $lang === 'fr' ? ($business->description_fr ?: $business->description_en) : ($business->description_en ?: $business->description_fr),
                self::t($lang, 'Written by the artisan.', "Rédigée par l'artisan."),
                self::t($lang, 'The artisan has written no description.', "L'artisan n'a pas rédigé de description."),
            ),

            'workshop_name' => self::optional(
                $workshop?->name,
                self::t($lang, 'From the workshop register.', "Issu du registre des ateliers."),
                self::t($lang, 'No workshop is registered.', "Aucun atelier n'est enregistré."),
            ),

            // Nationality is not a column on businesses, and a Cameroonian
            // region does not make its occupant Cameroonian. The only place the
            // platform records a country for an artisan is the workshop row, so
            // that is the only place it is read from.
            'country' => self::optional(
                $workshop?->country,
                self::t($lang, 'Country of the registered workshop.', "Pays de l'atelier enregistré."),
                self::t($lang, 'The platform records no nationality; the workshop country is the nearest fact and no workshop is registered.',
                              "La plateforme n'enregistre pas la nationalité ; le pays de l'atelier en est le fait le plus proche et aucun atelier n'est enregistré."),
            ),

            'specialisation' => self::optional(
                $business->source_metier ?: ($business->industry?->{'name_' . $lang} ?? $business->industry?->name_fr),
                self::t($lang, 'Official trade, from the craft taxonomy.', 'Métier officiel, issu de la nomenclature des métiers.'),
                self::t($lang, 'No trade has been recorded.', "Aucun métier n'a été enregistré."),
            ),

            'years_experience' => $business->year_established
                ? self::known(
                    max(0, (int) now()->year - (int) $business->year_established),
                    self::t($lang, 'Current year less the year of establishment on the record.',
                                  "Année en cours moins l'année de création figurant au dossier."),
                )
                : self::unknown(self::t($lang,
                    'The artisan has not recorded a year of establishment, so no length of practice can be stated.',
                    "L'artisan n'a pas indiqué d'année de création ; aucune ancienneté ne peut donc être affirmée.")),

            'employees' => self::optional(
                $business->employee_count,
                self::t($lang, 'Self-reported headcount.', "Effectif déclaré par l'artisan."),
                self::t($lang, 'No headcount has been reported.', "Aucun effectif n'a été déclaré."),
            ),

            'languages' => self::optional(
                $business->languages_spoken,
                self::t($lang, 'Self-reported by the artisan.', "Déclarées par l'artisan."),
                self::t($lang, 'No languages have been declared.', "Aucune langue n'a été déclarée."),
            ),

            'phone' => self::optional(
                $business->phone,
                self::t($lang, 'Contact number on the record.', 'Numéro de contact au dossier.'),
                self::t($lang, 'No telephone number on the record.', 'Aucun numéro de téléphone au dossier.'),
            ),

            'whatsapp' => self::optional(
                $business->whatsapp,
                self::t($lang, 'WhatsApp number on the record.', 'Numéro WhatsApp au dossier.'),
                self::t($lang, 'No WhatsApp number on the record.', 'Aucun numéro WhatsApp au dossier.'),
            ),

            'email' => self::optional(
                $business->email,
                self::t($lang, 'Contact address on the record.', 'Adresse de contact au dossier.'),
                self::t($lang, 'No email address on the record.', "Aucune adresse e-mail au dossier."),
            ),

            'website' => self::optional(
                $business->website,
                self::t($lang, 'Declared by the artisan.', "Déclaré par l'artisan."),
                self::t($lang, 'No website has been declared.', "Aucun site web n'a été déclaré."),
            ),

            // City and region only. The precise coordinates are withheld here
            // for the same reason the passport withholds them, and the profile
            // is the more public of the two surfaces.
            'location' => self::location($business, $lang),

            // A year, formatted as text, because an integer year in a figure
            // slot invites a view to render it beside counts as though it were
            // one.
            'member_since' => $business->created_at
                ? self::known(
                    Carbon::parse($business->created_at)->format('Y-m'),
                    self::t($lang, 'Date the profile was created on this platform.',
                                  'Date de création du profil sur cette plateforme.'),
                )
                : self::unknown(self::t($lang, 'No creation date on the record.', 'Aucune date de création au dossier.')),

            'verification' => self::verificationStanding($business, $lang),
        ];
    }

    /**
     * Coarse location.
     *
     * Deliberately built from the region and city rows rather than from the
     * free-text address, because the address a seller typed frequently contains
     * a quarter and a street. City granularity is the most precise thing this
     * class will publish about where a person works alone with valuable stock.
     */
    private static function location(Business $business, string $lang): array
    {
        $parts = array_values(array_filter([
            $business->city?->{'name_' . $lang} ?? $business->city?->name_fr,
            $business->region?->{'name_' . $lang} ?? $business->region?->name_fr,
        ]));

        return $parts === []
            ? self::unknown(self::t($lang,
                'No city or region is recorded. The free-text address is not used here because it commonly names a street.',
                "Aucune ville ni région enregistrée. L'adresse libre n'est pas utilisée ici car elle nomme souvent une rue."))
            : self::known(implode(', ', $parts), self::t($lang,
                'City and region from the taxonomy. Exact coordinates are withheld for the artisan’s safety.',
                "Ville et région issues de la nomenclature. Les coordonnées exactes sont retenues pour la sécurité de l'artisan."));
    }

    /**
     * Where the artisan stands on the verification ladder.
     *
     * The level comes from ArtisanVerification, which derives it only from
     * approved evidence and deliberately tops out at 5 — rungs 6 and 7 are
     * external honours nobody here can award. This method reports the rung and
     * the tier and nothing more; it does not translate either into a word like
     * "trusted".
     */
    private static function verificationStanding(Business $business, string $lang): array
    {
        $level = ArtisanVerification::levelFor($business);

        return [
            'level' => self::known($level, self::t($lang,
                'Verification level 0–5, derived only from evidence a reviewer approved.',
                "Niveau de vérification 0 à 5, déduit uniquement de preuves approuvées par un examinateur.")),
            'tier' => self::known($business->verification_tier ?? 'unverified', self::t($lang,
                'Tier set by platform review.', 'Palier attribué par examen de la plateforme.')),
            'checks' => ArtisanVerification::checksFor($business),
            'checks_basis' => self::t($lang,
                'Only checks the platform actually performs appear. Tax compliance, criminal record and site inspection are absent because they are never attempted.',
                "Seules les vérifications réellement effectuées figurent. Conformité fiscale, casier judiciaire et visite de site sont absents car jamais entrepris."),
        ];
    }

    /* ───────────────────────────── Certificates ────────────────────────── */

    /**
     * Every numbered certificate genuinely issued for this artisan or their work.
     *
     * Read straight off the five registers. Nothing is re-derived: if
     * ProductCertificate has not written a row, this artisan has no COA, and
     * computing what its number *would* be and showing it would put a
     * verifiable-looking reference on a public page that resolves to nothing —
     * which is the answer a forgery gets, and teaches readers the check is
     * theatre.
     *
     * A type with no rows reports `issued => false` and an empty list rather
     * than being omitted, so a view can show the full family with the gaps
     * visible. An artisan holding two of five certificates is a fact about
     * them; hiding the other three flatters the page.
     *
     * The two documents the designs also name — the Product Registration
     * Certificate and the Product Provenance Certificate — are not registers.
     * They are views rendered from product data on demand and carry no number
     * of their own, so they are reported separately under `derived_documents`
     * rather than being given a fictitious issue date.
     */
    public static function certificates(Business $business, ?string $lang = null): array
    {
        $lang       = self::lang($lang);
        $productIds = DB::table('products')->where('business_id', $business->id)
            ->whereNull('deleted_at')->pluck('id')->all();

        $blocks = [
            'avc' => self::avcBlock($business, $lang),
            'wvc' => self::wvcBlock($business, $lang),
            'coa' => self::coaBlock($business, $lang),
            'otc' => self::otcBlock($productIds, $lang),
            'eac' => self::eacBlock($business, $lang),
        ];

        $blocks['derived_documents'] = [
            'prc' => [
                'name'  => self::t($lang, 'Product Registration Certificate', "Certificat d'enregistrement de produit"),
                'basis' => self::t($lang,
                    'Rendered on demand from the product record. It is not a numbered register entry and has no issue date of its own.',
                    "Généré à la demande à partir de la fiche produit. Ce n'est pas une entrée de registre numérotée et il n'a pas de date d'émission propre."),
                'available_for' => count($productIds),
            ],
            'ppc' => [
                'name'  => self::t($lang, 'Product Provenance Certificate', 'Certificat de provenance de produit'),
                'basis' => self::t($lang,
                    'Compiled on demand from the provenance register. Its content is only as complete as the events recorded against the piece.',
                    "Compilé à la demande à partir du registre de provenance. Son contenu ne vaut que par les événements enregistrés sur la pièce."),
                'available_for' => count($productIds),
            ],
        ];

        return $blocks;
    }

    private static function block(string $type, string $lang, array $items, string $emptyBasis): array
    {
        return [
            'type'   => $type,
            'name'   => CertificateDirectory::name($type, $lang),
            'issued' => $items !== [],
            'count'  => count($items),
            'items'  => $items,
            'basis'  => $items !== []
                ? self::t($lang, 'Read from the issuing register.', 'Lu dans le registre émetteur.')
                : $emptyBasis,
        ];
    }

    private static function avcBlock(Business $business, string $lang): array
    {
        $rows = DB::table('artisan_verifications')
            ->where('business_id', $business->id)
            ->orderByDesc('issued_at')->get();

        $items = $rows->map(fn ($r) => [
            'number'     => $r->certificate_no,
            'issued_at'  => $r->issued_at,
            'expires_at' => $r->expires_at,
            'status'     => $r->status,
            'level'      => (int) $r->level,
            'url'        => route('artisan.verification.certificate', ['slug' => $business->slug]),
        ])->all();

        return self::block('avc', $lang, $items, self::t($lang,
            'No artisan verification has been issued; the ladder requires at least one reviewer-approved check.',
            "Aucune vérification d'artisan n'a été émise ; l'échelle exige au moins un contrôle approuvé par un examinateur."));
    }

    private static function wvcBlock(Business $business, string $lang): array
    {
        $rows = DB::table('workshop_certificates')
            ->join('workshops', 'workshops.id', '=', 'workshop_certificates.workshop_id')
            ->where('workshops.business_id', $business->id)
            ->orderByDesc('workshop_certificates.issued_at')
            ->select('workshop_certificates.*', 'workshops.gwn')
            ->get();

        $items = $rows->map(fn ($r) => [
            'number'     => $r->certificate_no,
            'issued_at'  => $r->issued_at,
            'expires_at' => $r->expires_at,
            'status'     => $r->status,
            'level'      => (int) $r->level,
            'url'        => $r->gwn ? route('workshop.certificate', ['gwn' => $r->gwn]) : null,
        ])->all();

        return self::block('wvc', $lang, $items, self::t($lang,
            'No workshop certificate has been issued; one requires a passed inspection.',
            "Aucun certificat d'atelier n'a été émis ; il en faut une inspection réussie."));
    }

    private static function coaBlock(Business $business, string $lang): array
    {
        $rows = DB::table('product_certificates')
            ->join('products', 'products.id', '=', 'product_certificates.product_id')
            ->where('product_certificates.business_id', $business->id)
            ->orderByDesc('product_certificates.issued_at')
            ->select('product_certificates.*', 'products.slug as product_slug',
                     'products.name_fr as product_name_fr', 'products.name_en as product_name_en')
            ->get();

        $items = $rows->map(fn ($r) => [
            'number'    => $r->certificate_no,
            'issued_at' => $r->issued_at,
            'status'    => $r->revoked_at ? 'revoked' : 'active',
            'subject'   => $lang === 'fr' ? ($r->product_name_fr ?: $r->product_name_en) : ($r->product_name_en ?: $r->product_name_fr),
            'url'       => route('product.certificate', ['slug' => $r->product_slug]),
        ])->all();

        return self::block('coa', $lang, $items, self::t($lang,
            'No certificate of authenticity has been issued for any of this artisan’s pieces.',
            "Aucun certificat d'authenticité n'a été émis pour les pièces de cet artisan."));
    }

    private static function otcBlock(array $productIds, string $lang): array
    {
        $items = $productIds === [] ? [] : DB::table('ownership_transfers')
            ->whereIn('product_id', $productIds)
            ->whereNotNull('certificate_no')
            ->orderByDesc('transferred_at')
            ->get()
            ->map(fn ($r) => [
                'number'    => $r->certificate_no,
                'issued_at' => $r->issued_at,
                'status'    => $r->status,
                'url'       => route('ownership.transfer.certificate', ['ref' => $r->certificate_no]),
            ])->all();

        return self::block('otc', $lang, $items, self::t($lang,
            'No piece by this artisan has changed recorded hands.',
            "Aucune pièce de cet artisan n'a changé de main au registre."));
    }

    private static function eacBlock(Business $business, string $lang): array
    {
        $items = DB::table('export_consignments')
            ->join('exporters', 'exporters.id', '=', 'export_consignments.exporter_id')
            ->where('exporters.business_id', $business->id)
            ->whereNotNull('export_consignments.certificate_no')
            ->orderByDesc('export_consignments.issued_at')
            ->select('export_consignments.*')
            ->get()
            ->map(fn ($r) => [
                'number'    => $r->certificate_no,
                'issued_at' => $r->issued_at,
                'status'    => $r->status,
                'url'       => route('export.certificate', ['ref' => $r->certificate_no]),
            ])->all();

        return self::block('eac', $lang, $items, self::t($lang,
            'No export certificate has been issued for this artisan.',
            "Aucun certificat d'export n'a été émis pour cet artisan."));
    }

    /* ─────────────────────────────── Products ──────────────────────────── */

    /**
     * The published pieces, as the catalogue holds them.
     *
     * The one thing conspicuously missing against the design is a per-product
     * star rating. `business_reviews` is keyed on `business_id`; there is no
     * product-level review anywhere in this schema. Spreading the shop's mean
     * across its pieces would attach a rating to work nobody rated, so the key
     * is not present at all — absent rather than null, because a null invites a
     * `?? 0` two files away.
     *
     * `price_type = contact` genuinely means no price is published, which is
     * different from a piece nobody has priced; both come back without an
     * amount but with different reasons.
     */
    public static function products(Business $business, int $limit = 12, ?string $lang = null): array
    {
        $lang = self::lang($lang);

        $products = Product::query()
            ->where('business_id', $business->id)
            ->where('status', 'published')
            ->with('primaryImage')
            ->orderBy('sort_order')->orderByDesc('id')
            ->limit(max(1, $limit))
            ->get();

        $certified = $products->isEmpty() ? [] : DB::table('product_certificates')
            ->whereIn('product_id', $products->pluck('id'))
            ->whereNull('revoked_at')
            ->pluck('product_id')->all();

        $items = $products->map(fn (Product $p) => [
            'id'    => $p->id,
            'slug'  => $p->slug,
            'name'  => $lang === 'fr' ? ($p->name_fr ?: $p->name_en) : ($p->name_en ?: $p->name_fr),
            'image' => $p->primaryImage?->file_path,
            'price' => [
                'amount'   => $p->price_amount !== null ? (float) $p->price_amount : null,
                'currency' => $p->price_currency,
                'unit'     => $p->price_unit,
                'type'     => $p->price_type,
                'basis'    => $p->price_amount !== null
                    ? self::t($lang, 'Price set by the artisan.', "Prix fixé par l'artisan.")
                    : ($p->price_type === 'contact'
                        ? self::t($lang, 'The artisan publishes no price and asks buyers to make contact.',
                                        "L'artisan ne publie pas de prix et invite à le contacter.")
                        : self::t($lang, 'No price has been entered.', "Aucun prix n'a été saisi.")),
            ],
            // A real register lookup, not an inference from `is_certified`,
            // which is a seller-set flag on the product row.
            'has_authenticity_certificate' => in_array($p->id, $certified, true),
        ])->all();

        return [
            'items' => $items,
            'count' => count($items),
            'total_published' => DB::table('products')->where('business_id', $business->id)
                ->where('status', 'published')->whereNull('deleted_at')->count(),
            'ratings_basis' => self::t($lang,
                'Pieces carry no rating: reviews on this platform are written about the artisan, not about individual works, so there is nothing to attribute to a piece.',
                "Les pièces ne portent aucune note : les avis de cette plateforme concernent l'artisan et non les œuvres individuelles ; il n'y a donc rien à leur attribuer."),
        ];
    }

    /* ──────────────────────────────── Reviews ──────────────────────────── */

    /**
     * Published reviews, counted and distributed.
     *
     * The table is real and, today, empty across the whole platform. So the
     * empty case is the case that matters: a count of zero, a mean that is
     * `known => false` rather than 0.0, and five genuinely empty buckets. The
     * design shows 4.9 stars and "125 Positive Reviews"; a mean has to come
     * from ratings or not exist, and there is no honest way to render a star
     * row when nobody has rated anybody.
     *
     * Only `published` rows count. A flagged or hidden review is one the
     * platform has withdrawn from public view, and counting it in the mean
     * while refusing to show it would let a moderation decision move a public
     * number invisibly.
     */
    public static function reviews(Business $business, ?string $lang = null): array
    {
        $lang = self::lang($lang);

        $rows = DB::table('business_reviews')
            ->where('business_id', $business->id)
            ->where('status', 'published')
            ->selectRaw('rating, COUNT(*) as n')
            ->groupBy('rating')->pluck('n', 'rating')->all();

        $distribution = [];
        $count = 0;
        $sum   = 0;
        foreach ([5, 4, 3, 2, 1] as $star) {
            $n = (int) ($rows[$star] ?? 0);
            $distribution[$star] = $n;
            $count += $n;
            $sum   += $n * $star;
        }

        $hasReviews = $count >= self::MIN_REVIEWS_FOR_MEAN;

        return [
            'count'        => $count,
            'has_reviews'  => $hasReviews,
            'distribution' => $distribution,
            'mean' => $hasReviews
                ? self::known(round($sum / $count, 1), self::t($lang,
                    'Mean of every published review, to one decimal.',
                    'Moyenne de tous les avis publiés, à une décimale.'))
                : self::unknown(self::t($lang,
                    'Nobody has published a review of this artisan, so there is no average rating — not a zero, and not a default.',
                    "Personne n'a publié d'avis sur cet artisan ; il n'existe donc aucune note moyenne — ni zéro, ni valeur par défaut.")),
            'verified_contact_count' => DB::table('business_reviews')
                ->where('business_id', $business->id)->where('status', 'published')
                ->where('is_verified_contact', 1)->count(),
        ];
    }

    /* ──────────────────────────────── Awards ───────────────────────────── */

    /**
     * Distinctions, from `business_awards` and from nowhere else.
     *
     * The table is empty today, and that is the whole answer. The designs fill
     * this block with SIARC medals, UNESCO recognitions and ministry honours;
     * every one of those is conferred by a body this platform holds no register
     * of and has no relationship with, and a fabricated national distinction
     * beside a real person's name is not decoration, it is a false claim about
     * their standing that they did not make and cannot retract. The same
     * honours were stripped out of the certificate family once already.
     *
     * The issuer is reported verbatim from the row. It is not validated, and
     * the caller should present it as the artisan's own claim rather than the
     * platform's finding.
     */
    public static function awards(Business $business, ?string $lang = null): array
    {
        $lang = self::lang($lang);

        $items = DB::table('business_awards')
            ->where('business_id', $business->id)
            ->orderByDesc('year')
            ->get()
            ->map(fn ($r) => [
                'title'  => $lang === 'fr' ? ($r->title_fr ?: $r->title_en) : ($r->title_en ?: $r->title_fr),
                'issuer' => $r->issuer,
                'year'   => $r->year !== null ? (int) $r->year : null,
            ])->all();

        return [
            'items' => $items,
            'count' => count($items),
            'basis' => $items !== []
                ? self::t($lang,
                    'Entered against this profile and reported verbatim. The platform does not verify the awarding body.',
                    "Saisies sur ce profil et rapportées telles quelles. La plateforme ne vérifie pas l'organisme décernant.")
                : self::t($lang,
                    'No distinction is recorded for this artisan. The platform holds no register of external honours and will not name one that was not entered here.',
                    "Aucune distinction n'est enregistrée pour cet artisan. La plateforme ne tient aucun registre des honneurs externes et n'en nommera aucun qui n'ait été saisi ici."),
        ];
    }

    /* ────────────────────────────── Statistics ─────────────────────────── */

    /**
     * Every counter the designs display, each answering whether it is measured.
     *
     * The split is worth stating plainly, because it is the substance of this
     * whole class. Products, exhibitions, certificates and countries are rows
     * we hold and can count. Sales, customers, response rate and last-seen are
     * not slow queries or missing indexes — there is no orders table, no
     * customers table, and no record of when a message was answered. They can
     * never be computed from this schema as it stands, and the basis string
     * says so rather than implying the figure is merely pending.
     */
    public static function statistics(Business $business, ?string $lang = null): array
    {
        $lang       = self::lang($lang);
        $productIds = DB::table('products')->where('business_id', $business->id)
            ->whereNull('deleted_at')->pluck('id')->all();

        return [
            /* ── measured ── */

            'products_created' => self::known(count($productIds), self::t($lang,
                'Rows in the product catalogue for this artisan.',
                'Fiches produits de cet artisan au catalogue.')),

            'products_published' => self::known(
                DB::table('products')->where('business_id', $business->id)
                    ->where('status', 'published')->whereNull('deleted_at')->count(),
                self::t($lang, 'Catalogue rows in published status.', 'Fiches produits en statut publié.')),

            'certificates_issued' => self::known(
                self::issuedCertificateCount($business, $productIds),
                self::t($lang, 'Numbered entries across the five issuing registers.',
                              'Entrées numérotées dans les cinq registres émetteurs.')),

            'exhibitions' => self::known(
                $productIds === [] ? 0 : DB::table('provenance_events')
                    ->whereIn('product_id', $productIds)->where('type', 'exhibition')->count(),
                self::t($lang, 'Exhibition events recorded in the provenance register against this artisan’s pieces.',
                              "Événements d'exposition enregistrés au registre de provenance sur les pièces de cet artisan.")),

            'countries_reached' => self::countriesReached($productIds, $lang),

            'profile_views' => self::known((int) ($business->views_count ?? 0), self::t($lang,
                'Page loads counted by the platform. It counts views, not people.',
                'Chargements de page comptés par la plateforme. Il compte des vues, non des personnes.')),

            'reviews_published' => self::known(
                DB::table('business_reviews')->where('business_id', $business->id)
                    ->where('status', 'published')->count(),
                self::t($lang, 'Published reviews of this artisan.', 'Avis publiés sur cet artisan.')),

            /* ── self-reported, and labelled as such ── */

            'response_time_hours' => self::optional(
                $business->response_time_hours !== null ? (int) $business->response_time_hours : null,
                self::t($lang, 'Stated by the artisan. The platform does not time replies, so this is a promise, not a measurement.',
                              "Indiqué par l'artisan. La plateforme ne chronomètre pas les réponses : c'est une promesse, non une mesure."),
                self::t($lang, 'The artisan has stated no response time and the platform measures none.',
                              "L'artisan n'a indiqué aucun délai de réponse et la plateforme n'en mesure aucun."),
            ),

            /* ── not tracked, and never zero ── */

            'products_sold' => self::unknown(self::t($lang,
                'The platform records no orders or sales. It introduces buyers to artisans and is not party to the transaction, so no quantity sold exists to count.',
                "La plateforme n'enregistre ni commandes ni ventes. Elle met en relation acheteurs et artisans sans être partie à la transaction ; aucune quantité vendue n'existe donc.")),

            'happy_customers' => self::unknown(self::t($lang,
                'There is no customer record on this platform, and no measure of satisfaction beyond published reviews.',
                "Il n'existe aucun fichier client sur cette plateforme, ni aucune mesure de satisfaction hors des avis publiés.")),

            'response_rate' => self::unknown(self::t($lang,
                'Replies are not timed or matched to enquiries, so the share of messages answered cannot be computed.',
                "Les réponses ne sont ni datées ni rattachées aux demandes ; la part de messages répondus ne peut être calculée.")),

            'positive_reviews' => self::positiveReviews($business, $lang),

            'last_active' => self::unknown(self::t($lang,
                'The platform does not record sign-ins or activity for an artisan. The record’s last edit is a change to the file, not a sign of the person being present.',
                "La plateforme n'enregistre ni connexions ni activité d'un artisan. La dernière modification du dossier est un changement de fiche, non un signe de présence.")),

            'repeat_buyers' => self::unknown(self::t($lang,
                'No orders exist, so no buyer can be seen to return.',
                "Aucune commande n'existe ; aucun acheteur ne peut donc être vu revenir.")),
        ];
    }

    /**
     * How many of the "positive review" figure is real.
     *
     * Ratings of four and five out of five are a defensible reading of
     * "positive" and are countable — but only once reviews exist. With none
     * published, "0 positive reviews" reads as an adverse finding about the
     * artisan when the truth is that nobody has said anything at all, so the
     * empty case is reported as unmeasured rather than as zero.
     */
    private static function positiveReviews(Business $business, string $lang): array
    {
        $total = DB::table('business_reviews')
            ->where('business_id', $business->id)->where('status', 'published')->count();

        if ($total === 0) {
            return self::unknown(self::t($lang,
                'Nobody has published a review, so there is no share of positive ones. Zero here would read as a verdict rather than as silence.',
                "Personne n'a publié d'avis ; il n'existe donc aucune part d'avis positifs. Un zéro se lirait ici comme un verdict et non comme un silence."));
        }

        return self::known(
            DB::table('business_reviews')->where('business_id', $business->id)
                ->where('status', 'published')->where('rating', '>=', 4)->count(),
            self::t($lang, 'Published reviews rating this artisan four or five out of five.',
                          "Avis publiés attribuant quatre ou cinq sur cinq à cet artisan."));
    }

    /** Numbered entries across the five registers that issue them. */
    private static function issuedCertificateCount(Business $business, array $productIds): int
    {
        $n = DB::table('artisan_verifications')->where('business_id', $business->id)->count()
           + DB::table('product_certificates')->where('business_id', $business->id)->count()
           + DB::table('workshop_certificates')
                ->join('workshops', 'workshops.id', '=', 'workshop_certificates.workshop_id')
                ->where('workshops.business_id', $business->id)->count()
           + DB::table('export_consignments')
                ->join('exporters', 'exporters.id', '=', 'export_consignments.exporter_id')
                ->where('exporters.business_id', $business->id)
                ->whereNotNull('export_consignments.certificate_no')->count();

        if ($productIds !== []) {
            $n += DB::table('ownership_transfers')->whereIn('product_id', $productIds)
                ->whereNotNull('certificate_no')->count();
        }

        return $n;
    }

    /**
     * Countries this artisan's work has demonstrably reached.
     *
     * This one is derivable and so it is derived, from three registers that
     * each record a destination as a fact: an export consignment names the
     * importer's country, an ownership transfer names the country of
     * destination, and a subsequent owner's row names where they are. The
     * artisan's own founding ownership is excluded — a piece that never left
     * the maker's hands has reached nowhere.
     *
     * `businesses.export_countries` is deliberately not used. It is a seller-
     * entered wish list of markets they would like to serve, and reporting it
     * as reach would turn an ambition into a shipping record.
     *
     * Zero here is honest and means what it says: no consignment, transfer or
     * later owner is on file. That is a real absence of events, not an absence
     * of measurement, which is why this returns `known => true`.
     */
    private static function countriesReached(array $productIds, string $lang): array
    {
        if ($productIds === []) {
            return self::known(0, self::t($lang,
                'This artisan has no pieces on record, so none has travelled.',
                "Cet artisan n'a aucune pièce au registre ; aucune n'a donc voyagé."));
        }

        $countries = array_merge(
            DB::table('export_consignments')->whereIn('product_id', $productIds)
                ->whereIn('status', ['approved', 'shipped', 'delivered'])
                ->whereNotNull('importer_country')->pluck('importer_country')->all(),
            DB::table('ownership_transfers')->whereIn('product_id', $productIds)
                ->whereIn('status', ['approved', 'active', 'superseded'])
                ->whereNotNull('country_of_destination')->pluck('country_of_destination')->all(),
            DB::table('product_ownerships')->whereIn('product_id', $productIds)
                ->where('is_original_creator', 0)
                ->whereNotNull('country_code')->pluck('country_code')->all(),
        );

        $unique = array_values(array_unique(array_filter($countries)));

        return self::known(count($unique), self::t($lang,
            'Distinct destination countries named on export consignments, ownership transfers and later owners’ records. Declared target markets are not counted.',
            "Pays de destination distincts nommés sur les expéditions à l'export, les transferts de propriété et les fiches des propriétaires ultérieurs. Les marchés visés déclarés ne sont pas comptés."));
    }

    /* ──────────────────────────── Verification score ───────────────────── */

    /**
     * A score, and a deliberate decision about what it may be called.
     *
     * `HealthScore::compute()` is not used here, and the reason is worth
     * recording so nobody wires it in later as an obvious win. It reads
     * `companies`, `tenders`, `collabcam_*`, `federation_members` and
     * `esg_reports` — a different module, keyed on a different identifier, that
     * an artisan business has no rows in. Pointed at an artisan it would return
     * a number built entirely from its own baselines: twenty points of
     * reputation, forty of sustainability, ten of engagement, for a shop it has
     * never seen. That is the exact fabrication this class exists to prevent,
     * and it would be a fabrication with a very respectable-looking grade
     * letter on it.
     *
     * What is computed instead is narrower and honestly named: a *verification*
     * standing. It measures how much of this artisan's account has been checked
     * by someone, and it does not measure their honesty, their craft, or
     * whether a buyer will be treated well. The caller is expected to label it
     * accordingly. "Trust Score 92/100" beside a photograph of a person is a
     * public assertion about their character; this number cannot support that
     * sentence and should not be dressed in it.
     *
     * Two rules keep it defensible. Every input is nameable and carries its own
     * basis, and the total is the plain sum of the inputs — no weighting curve,
     * no normalising, nothing a reader cannot check with addition. And an input
     * with nothing to assess drops out of the maximum instead of scoring zero,
     * so an artisan with no workshop registered is not marked down for a
     * question nobody asked them.
     *
     * When no input at all has been satisfied, the score is `known => false`
     * rather than zero. A published zero is a statement, and "nothing has been
     * checked" is not the same statement as "everything was checked and failed".
     */
    public static function trustScore(Business $business, ?string $lang = null): array
    {
        $lang   = self::lang($lang);
        $checks = ArtisanVerification::checksFor($business);
        $breakdown = [];

        $flag = function (string $key, int $max, bool $satisfied, string $basis) use (&$breakdown) {
            $breakdown[$key] = [
                'points'    => $satisfied ? $max : 0,
                'max'       => $max,
                'satisfied' => $satisfied,
                'basis'     => $basis,
            ];
        };

        $flag('identity_verified', 25, (bool) ($checks['identity_document_verified'] ?? false), self::t($lang,
            'A reviewer confirmed an identity document.', "Un examinateur a confirmé une pièce d'identité."));

        $flag('trade_documents_accepted', 15, (bool) ($checks['trade_documents_accepted'] ?? false), self::t($lang,
            'Trade paperwork was filed and accepted.', 'Des documents professionnels ont été déposés et acceptés.'));

        $flag('application_reviewed', 10, (bool) ($checks['application_reviewed'] ?? false), self::t($lang,
            'A verification application was reviewed and approved.',
            "Une demande de vérification a été examinée et approuvée."));

        $flag('workshop_address_on_record', 10, (bool) ($checks['workshop_address_on_record'] ?? false), self::t($lang,
            'A place of work is on record.', 'Un lieu de travail figure au dossier.'));

        $flag('portfolio_published', 10, (bool) ($checks['portfolio_published'] ?? false), self::t($lang,
            'At least one piece is published.', 'Au moins une pièce est publiée.'));

        $flag('third_party_certification', 15, (bool) ($checks['third_party_certification'] ?? false), self::t($lang,
            'A body other than this platform certified the artisan.',
            "Un organisme autre que cette plateforme a certifié l'artisan."));

        // Assessable only where the question applies. A shop with no workshop
        // registered is not failing a workshop inspection; it was never asked
        // to have one, so the input leaves the denominator entirely.
        if ($workshop = self::workshopRow($business)) {
            $flag('workshop_verified', 15, $workshop->status === 'verified', self::t($lang,
                'The registered workshop passed inspection.', "L'atelier enregistré a passé l'inspection."));
        }

        // Certificate coverage is proportional, and only asked of an artisan
        // who has published something to certify.
        $published = DB::table('products')->where('business_id', $business->id)
            ->where('status', 'published')->whereNull('deleted_at')->pluck('id')->all();

        if ($published !== []) {
            $covered = DB::table('product_certificates')->whereIn('product_id', $published)
                ->whereNull('revoked_at')->distinct()->count('product_id');

            $breakdown['authenticity_coverage'] = [
                'points'    => (int) round(15 * $covered / count($published)),
                'max'       => 15,
                'satisfied' => $covered > 0,
                'basis'     => self::t($lang,
                    'Share of published pieces carrying a live certificate of authenticity.',
                    "Part des pièces publiées portant un certificat d'authenticité en vigueur."),
            ];
        }

        // Reviews count only once somebody has written one; an unreviewed
        // artisan is unrated, not badly rated.
        $reviews = self::reviews($business, $lang);
        if ($reviews['mean']['known']) {
            $breakdown['reviews'] = [
                'points'    => (int) round(10 * $reviews['mean']['value'] / 5),
                'max'       => 10,
                'satisfied' => true,
                'basis'     => self::t($lang,
                    'Mean published review rating, scaled to ten points.',
                    'Note moyenne des avis publiés, ramenée à dix points.'),
            ];
        }

        $value = array_sum(array_column($breakdown, 'points'));
        $max   = array_sum(array_column($breakdown, 'max'));

        if ($value === 0) {
            return [
                'value'     => null,
                'max'       => $max,
                'known'     => false,
                'label'     => self::t($lang, 'Verification standing', 'Niveau de vérification'),
                'breakdown' => $breakdown,
                'basis'     => self::t($lang,
                    'Nothing on this profile has been checked yet, so there is no standing to report. A zero would say the artisan failed the checks; the truth is that none has been carried out.',
                    "Rien sur ce profil n'a encore été contrôlé ; il n'y a donc aucun niveau à rapporter. Un zéro dirait que l'artisan a échoué aux contrôles, alors qu'aucun n'a été mené."),
            ];
        }

        return [
            'value'     => $value,
            'max'       => $max,
            'known'     => true,
            'label'     => self::t($lang, 'Verification standing', 'Niveau de vérification'),
            'breakdown' => $breakdown,
            'basis'     => self::t($lang,
                'The plain sum of the checks listed below, out of the checks that apply to this artisan. It measures how much of the account has been verified — not the artisan’s honesty, craft or conduct.',
                "Somme simple des contrôles listés ci-dessous, rapportée aux contrôles applicables à cet artisan. Elle mesure la part du compte vérifiée — non l'honnêteté, le savoir-faire ou la conduite de l'artisan."),
        ];
    }

    /* ─────────────────────────────── Workshop ──────────────────────────── */

    /** The workshop row, if one is registered. */
    private static function workshopRow(Business $business): ?object
    {
        return DB::table('workshops')->where('business_id', $business->id)
            ->orderByDesc('id')->first();
    }

    /**
     * The registered workshop, at a granularity that is safe to publish.
     *
     * Returns null rather than an empty shell when no workshop is registered,
     * because a workshop block full of dashes reads as a workshop with missing
     * details rather than as no workshop at all.
     *
     * The coordinates, the street address and the village are not returned, at
     * any verification level. The passport withholds them because an artisan's
     * workshop holds their tools and their stock and they are often alone in
     * it; a public profile is a wider audience than a passport reader, so the
     * argument applies with more force here, not less. Region and city are as
     * precise as this gets, and a buyer who needs the address gets it from the
     * artisan, in a conversation the artisan chose to have.
     */
    public static function workshop(Business $business, ?string $lang = null): ?array
    {
        $lang = self::lang($lang);
        $row  = self::workshopRow($business);

        if (! $row) {
            return null;
        }

        $region = $row->region_id ? DB::table('regions')->where('id', $row->region_id)->first() : null;
        $city   = $row->city_id ? DB::table('cities')->where('id', $row->city_id)->first() : null;

        $place = array_values(array_filter([
            $city?->{'name_' . $lang} ?? $city?->name_fr ?? null,
            $region?->{'name_' . $lang} ?? $region?->name_fr ?? null,
            $row->country,
        ]));

        return [
            'name'   => $row->name,
            'number' => $row->gwn,
            'type'   => $row->workshop_type,
            'legal_status' => $row->legal_status,
            'status' => $row->status,
            'verification_level' => self::optional(
                $row->verification_level !== null ? (int) $row->verification_level : null,
                self::t($lang, 'Level awarded at the last passed inspection.',
                              'Niveau attribué lors de la dernière inspection réussie.'),
                self::t($lang, 'The workshop has not been inspected.', "L'atelier n'a pas été inspecté."),
            ),
            'verified_at' => $row->verified_at,
            'established_on' => $row->established_on,
            'location' => $place === []
                ? self::unknown(self::t($lang, 'No region or city is recorded for the workshop.',
                                              "Aucune région ni ville n'est enregistrée pour l'atelier."))
                : self::known(implode(', ', $place), self::t($lang,
                    'Region and city only. Coordinates, street address and village are withheld for the artisan’s safety.',
                    "Région et ville uniquement. Coordonnées, adresse et village sont retenus pour la sécurité de l'artisan.")),
        ];
    }
}
