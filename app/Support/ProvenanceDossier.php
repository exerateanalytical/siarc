<?php

namespace App\Support;

use App\Modules\Products\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The lifetime record a Product Provenance Certificate is drawn from.
 *
 * Where ProvenanceRegistry answers "who held this", this answers everything
 * else a provenance dossier is expected to document: exhibitions, museum
 * accessions, gallery representation, restoration and conservation, valuations,
 * publications, media, the countries the object has passed through, and the
 * paper that supports each of those claims.
 *
 * Every method here is a reporter. If the register holds no exhibitions, the
 * exhibition section is empty; if no ownership row names a country, the journey
 * is an empty array and not "Cameroon". That sounds obvious and is the single
 * easiest thing to get wrong on a document like this, because an empty section
 * looks like a bug and a plausible default looks like a feature. It is the
 * other way round: a dossier that fills its own gaps is worthless precisely at
 * the moment somebody relies on it.
 */
class ProvenanceDossier
{
    /** Event types that count as public exposure of the work. */
    private const PUBLIC_RECORD_TYPES = [
        'exhibition', 'museum_accession', 'gallery_representation',
        'publication', 'media', 'award',
    ];

    /** Event types that document physical care of the object. */
    private const CONSERVATION_TYPES = ['restoration', 'conservation', 'condition_report'];

    /* ─────────────────────────────── Language ──────────────────────────── */

    /**
     * The language every prose string on this class is built in.
     *
     * The basis phrases are the part of a dossier a holder is meant to argue
     * with, so they are worth more care than ordinary interface copy: a French
     * reader who cannot read the sentence explaining why their piece scored
     * twelve out of twenty has been handed a number and told to accept it.
     *
     * Resolution order is explicit argument, then the request's locale, then
     * French. The middle step is what keeps the old call sites honest — a
     * caller that passes nothing still gets whatever language the request is
     * being served in, and nothing that used to print English starts printing
     * French behind its author's back.
     */
    private static function lang(?string $lang): string
    {
        $lang ??= app()->getLocale();

        return in_array($lang, ['fr', 'en'], true) ? $lang : 'fr';
    }

    /** Picks one of the two written forms. Nothing is generated or guessed. */
    private static function t(string $lang, string $en, string $fr): string
    {
        return $lang === 'fr' ? $fr : $en;
    }

    /* ─────────────────────────────── Recording ─────────────────────────── */

    /**
     * Appends a documented event, writing the typed detail row when the type
     * carries one.
     *
     * Both halves go in one transaction: a valuation event with no valuation
     * row would print an appraisal on a certificate with no appraiser, date or
     * currency behind it, which is exactly the kind of half-fact this register
     * exists to make impossible.
     */
    public static function record(Product $product, string $type, array $data): object
    {
        return DB::transaction(function () use ($product, $type, $data) {
            $id = DB::table('provenance_events')->insertGetId([
                'uuid'                => (string) Str::uuid(),
                'product_id'          => $product->id,
                'type'                => $type,
                'title'               => $data['title'] ?? $type,
                'organisation'        => $data['organisation'] ?? null,
                'venue'               => $data['venue'] ?? null,
                'country'             => isset($data['country']) ? strtoupper((string) $data['country']) : null,
                'city'                => $data['city'] ?? null,
                'started_on'          => $data['started_on'] ?? $data['performed_on'] ?? $data['valued_on'] ?? null,
                'ended_on'            => $data['ended_on'] ?? null,
                'reference_no'        => $data['reference_no'] ?? null,
                'certificate_ref'     => $data['certificate_ref'] ?? null,
                'notes'               => $data['notes'] ?? null,
                'recorded_by_user_id' => $data['recorded_by_user_id'] ?? null,
                'evidence_count'      => 0,
                // Never set from the caller's data. Verification is a separate
                // act by a person who checked the institution's own record.
                'is_verified'         => false,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            if ($type === 'valuation' && isset($data['amount'], $data['currency'])) {
                DB::table('provenance_valuations')->insert([
                    'provenance_event_id' => $id,
                    'appraiser'           => $data['appraiser'] ?? '',
                    'appraiser_ref'       => $data['appraiser_ref'] ?? null,
                    'valued_on'           => $data['valued_on'] ?? $data['started_on'] ?? now()->toDateString(),
                    // Kept as the caller's string so the decimal column receives
                    // the exact figure rather than a float's idea of it.
                    'amount'              => (string) $data['amount'],
                    'currency'            => strtoupper((string) $data['currency']),
                    'purpose'             => $data['purpose'] ?? 'insurance',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            if (in_array($type, ['restoration', 'conservation'], true) && isset($data['restorer'])) {
                DB::table('provenance_restorations')->insert([
                    'provenance_event_id' => $id,
                    'restorer'            => $data['restorer'],
                    'restorer_ref'        => $data['restorer_ref'] ?? null,
                    'performed_on'        => $data['performed_on'] ?? $data['started_on'] ?? now()->toDateString(),
                    'description'         => $data['description'] ?? null,
                    'materials_used'      => $data['materials_used'] ?? null,
                    'before_images'       => isset($data['before_images']) ? json_encode($data['before_images']) : null,
                    'after_images'        => isset($data['after_images']) ? json_encode($data['after_images']) : null,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            return DB::table('provenance_events')->find($id);
        });
    }

    /* ─────────────────────────────── Reading ───────────────────────────── */

    /** @return array<int,object> */
    public static function events(Product $product): array
    {
        return DB::table('provenance_events')
            ->where('product_id', $product->id)
            ->orderByRaw('started_on is null, started_on')
            ->orderBy('id')
            ->get()->all();
    }

    /** @return array<int,object> */
    public static function byType(Product $product, string $type): array
    {
        return array_values(array_filter(self::events($product), fn ($e) => $e->type === $type));
    }

    /**
     * The dossier's spine: registration, every ownership, every documented
     * event, in one chronological list.
     *
     * Merged here rather than in the view because a timeline stitched together
     * at render time is a timeline that can silently omit a section when a new
     * kind of event is added. One list, one sort, one place to audit.
     *
     * @return array<int,array{date:?string,type:string,label:string,organisation:?string,country:?string,reference:?string,verified:bool}>
     */
    public static function timeline(Product $product, ?string $lang = null): array
    {
        $lang    = self::lang($lang);
        $entries = [];

        $registeredAt = $product->registered_at ?? $product->created_at;

        if ($registeredAt) {
            $entries[] = [
                'date'         => Carbon::parse($registeredAt)->toDateString(),
                'type'         => 'registration',
                'label'        => self::t($lang, 'Registered on ArtisanHub237', 'Enregistré sur ArtisanHub237'),
                'organisation' => $product->business?->name_fr,
                'country'      => null,
                'reference'    => $product->prn,
                'verified'     => (bool) $product->prn,
            ];
        }

        foreach (ProvenanceRegistry::chain($product) as $owner) {
            $entries[] = [
                'date'         => $owner->owned_from ? Carbon::parse($owner->owned_from)->toDateString() : null,
                'type'         => 'ownership',
                'label'        => $owner->is_original_creator
                    ? self::t($lang, 'Created and first held by ', 'Créé et détenu à l\'origine par ') . $owner->legal_name
                    : self::t($lang, 'Acquired by ', 'Acquis par ') . $owner->legal_name,
                'organisation' => $owner->legal_name,
                'country'      => $owner->country_code,
                'reference'    => $owner->owner_ref,
                // "Verified" on the chain means the register checked that party,
                // nothing softer.
                'verified'     => in_array($owner->verification_level, ['verified', 'institution'], true),
            ];
        }

        foreach (self::events($product) as $event) {
            $entries[] = [
                'date'         => $event->started_on ? Carbon::parse($event->started_on)->toDateString() : null,
                'type'         => $event->type,
                'label'        => $event->title,
                'organisation' => $event->organisation,
                'country'      => $event->country,
                'reference'    => $event->reference_no ?: $event->certificate_ref,
                'verified'     => (bool) $event->is_verified,
            ];
        }

        // Undated entries sort last: a record that does not know when something
        // happened must not invent a position by being given today's date.
        usort($entries, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                return 0;
            }
            if ($a['date'] === null) {
                return 1;
            }
            if ($b['date'] === null) {
                return -1;
            }

            return strcmp($a['date'], $b['date']);
        });

        return $entries;
    }

    /**
     * The geographic journey: each country once, in the order it was first
     * entered, with the date and the record that put it there.
     *
     * Returns an empty array when no row names a country. There is no default
     * origin — a piece whose ownership rows carry no country is a piece whose
     * journey is undocumented, and saying "Cameroon" anyway would be inventing
     * the single fact a provenance reader most wants checked.
     *
     * @return array<int,array{country:string,date:?string,reason:string}>
     */
    public static function journey(Product $product, ?string $lang = null): array
    {
        $lang = self::lang($lang);
        $legs = [];

        foreach (ProvenanceRegistry::chain($product) as $owner) {
            if ($owner->country_code) {
                $legs[] = [
                    'country' => strtoupper($owner->country_code),
                    'date'    => $owner->owned_from ? Carbon::parse($owner->owned_from)->toDateString() : null,
                    'reason'  => $owner->is_original_creator
                        ? self::t($lang, 'Made and first held by ', 'Fabriqué et détenu à l\'origine par ') . $owner->legal_name
                        : self::t($lang, 'Held by ', 'Détenu par ') . $owner->legal_name,
                ];
            }
        }

        foreach (self::events($product) as $event) {
            if ($event->country) {
                $legs[] = [
                    'country' => strtoupper($event->country),
                    'date'    => $event->started_on ? Carbon::parse($event->started_on)->toDateString() : null,
                    'reason'  => trim(str_replace('_', ' ', $event->type) . ': ' . $event->title),
                ];
            }
        }

        usort($legs, fn ($a, $b) => match (true) {
            $a['date'] === $b['date'] => 0,
            $a['date'] === null       => 1,
            $b['date'] === null       => -1,
            default                   => strcmp($a['date'], $b['date']),
        });

        // First arrival wins: the journey is where it went, not how often.
        $seen = [];
        $out  = [];

        foreach ($legs as $leg) {
            if (! isset($seen[$leg['country']])) {
                $seen[$leg['country']] = true;
                $out[] = $leg;
            }
        }

        return $out;
    }

    /**
     * The counts the certificate's summary panel prints. Every one is a query.
     *
     * @return array<string,int>
     */
    public static function summary(Product $product): array
    {
        $events = self::events($product);
        $chain  = ProvenanceRegistry::chain($product);

        $count = fn (array $types) => count(array_filter($events, fn ($e) => in_array($e->type, $types, true)));

        $dates = array_values(array_filter(array_column(self::timeline($product), 'date')));

        return [
            'years_documented' => $dates
                ? Carbon::parse($dates[0])->diffInYears(Carbon::parse(end($dates)))
                : 0,
            // Changes, not holders: the maker's founding row is not a change.
            'ownership_changes' => max(0, count($chain) - 1),
            'exhibitions'       => $count(['exhibition']),
            'restorations'      => $count(['restoration']),
            'conservations'     => $count(['conservation']),
            'museums'           => $count(['museum_accession']),
            'galleries'         => $count(['gallery_representation']),
            'publications'      => $count(['publication']),
            'media'             => $count(['media']),
            'awards'            => $count(['award']),
            'valuations'        => $count(['valuation']),
            'events'            => count($events),
            'evidence'          => DB::table('provenance_evidence')->where('product_id', $product->id)->count(),
            'countries'         => count(self::journey($product)),
        ];
    }

    /* ───────────────────────────── Legacy Index ────────────────────────── */

    /**
     * The Legacy Index: how completely this object's history is documented.
     *
     * Three rules govern it, and they are rules about fairness rather than
     * about arithmetic.
     *
     * It scores the record, never the work and never the maker. Nothing here
     * looks at price, craftsmanship, region, or how long an artisan has been
     * trading. A number printed on a certificate under someone's name is a
     * public claim about them, and the only claim this platform is entitled to
     * make is about how well the file is kept. A newly registered piece with a
     * complete file scores well; a famous piece with nothing behind it does not.
     *
     * It shows its working. Every category returns a `basis` phrase saying why
     * it scored what it did, so the holder can read the number, disagree with
     * it, and know exactly which missing document would move it. A score that
     * cannot be argued with is not a score, it is an assertion.
     *
     * It refuses to score what it cannot see. A category with nothing to assess
     * comes back with max 0 and says so, and drops out of the denominator
     * entirely. The alternative — scoring an absence — is unfair in both
     * directions: award full marks and the index means nothing; award zero and
     * a piece is punished for never having needed a restorer or never having
     * been sent abroad, which is not a defect in anybody's record-keeping.
     *
     * @return array{categories:array<string,array{score:int,max:int,basis:string}>,total:int,max:int,band:string}
     */
    public static function legacyIndex(Product $product, ?string $lang = null): array
    {
        $lang     = self::lang($lang);
        $events   = self::events($product);
        $chain    = ProvenanceRegistry::chain($product);
        $evidence = DB::table('provenance_evidence')->where('product_id', $product->id)->get()->all();

        $of = fn (array $types) => array_values(array_filter($events, fn ($e) => in_array($e->type, $types, true)));

        $categories = [
            'registration'    => self::scoreRegistration($product, $lang),
            'ownership_chain' => self::scoreOwnership($chain, $lang),
            'evidence'        => self::scoreEvidence($evidence, $lang),
            'public_record'   => self::scorePublicRecord($of(self::PUBLIC_RECORD_TYPES), $lang),
            'conservation'    => self::scoreConservation($of(self::CONSERVATION_TYPES), $lang),
            'valuation'       => self::scoreValuation($of(['valuation']), $lang),
        ];

        $total = array_sum(array_column($categories, 'score'));
        $max   = array_sum(array_column($categories, 'max'));

        return [
            'categories' => $categories,
            'total'      => $total,
            'max'        => $max,
            'band'       => self::band($total, $max),
        ];
    }

    /**
     * Always assessable: every registered product has a registration to judge.
     * 20 points across the four facts that make the entry citable.
     */
    private static function scoreRegistration(Product $product, string $lang = 'en'): array
    {
        $have = [];
        $miss = [];

        $product->prn ? $have[] = true : $miss[] = self::t($lang, 'a registry number', 'un numéro de registre');
        ($product->registered_at || $product->created_at) ? $have[] = true : $miss[] = self::t($lang, 'a registration date', 'une date d\'enregistrement');
        $product->business_id ? $have[] = true : $miss[] = self::t($lang, 'an identified maker', 'un artisan identifié');
        $product->uuid ? $have[] = true : $miss[] = self::t($lang, 'a permanent reference', 'une référence permanente');

        $score = count($have) * 5;

        return [
            'score' => $score,
            'max'   => 20,
            'basis' => $miss
                ? self::t($lang, 'The registry entry is missing ', 'Il manque à l\'entrée de registre ') . self::list($miss, $lang) . '.'
                : self::t(
                    $lang,
                    'The registry entry carries a number, a date, an identified maker and a permanent reference.',
                    'L\'entrée de registre porte un numéro, une date, un artisan identifié et une référence permanente.'
                ),
        ];
    }

    /**
     * Always assessable: a registered product always has at least the maker's
     * founding row. Judged on whether each holder can be identified and placed,
     * not on how many times the piece has changed hands — a work that has never
     * been sold has a perfect chain, not a thin one.
     */
    private static function scoreOwnership(array $chain, string $lang = 'en'): array
    {
        if (! $chain) {
            return ['score' => 0, 'max' => 20, 'basis' => self::t(
                $lang,
                'No ownership row exists, so the chain begins nowhere.',
                'Aucune ligne de propriété n\'existe, la chaîne ne commence donc nulle part.'
            )];
        }

        $score = 8;                                     // an unbroken chain exists
        $notes = [count($chain) . self::t(
            $lang,
            ' documented ' . (count($chain) === 1 ? 'holder' : 'holders'),
            ' détenteur' . (count($chain) === 1 ? '' : 's') . ' documenté' . (count($chain) === 1 ? '' : 's')
        )];

        $creator = array_filter($chain, fn ($o) => (bool) $o->is_original_creator);
        if ($creator) {
            $score += 4;
            $notes[] = self::t($lang, 'the maker recorded as first holder', 'l\'artisan enregistré comme premier détenteur');
        }

        $placed = count(array_filter($chain, fn ($o) => (bool) $o->country_code));
        if ($placed === count($chain)) {
            $score += 4;
            $notes[] = self::t($lang, 'every holder placed in a country', 'chaque détenteur situé dans un pays');
        } elseif ($placed > 0) {
            $score += 2;
            $notes[] = self::t(
                $lang,
                $placed . ' of ' . count($chain) . ' holders placed in a country',
                $placed . ' détenteurs sur ' . count($chain) . ' situés dans un pays'
            );
        }

        $identified = count(array_filter($chain, fn ($o) => $o->verification_level !== 'unverified'));
        if ($identified === count($chain)) {
            $score += 4;
            $notes[] = self::t($lang, 'no anonymous holder', 'aucun détenteur anonyme');
        }

        return ['score' => $score, 'max' => 20, 'basis' => ucfirst(self::list($notes, $lang)) . '.'];
    }

    /**
     * Always assessable: whether supporting paper was filed is a question that
     * can be asked of any product, and the answer for a new one is honestly
     * "none yet".
     */
    private static function scoreEvidence(array $evidence, string $lang = 'en'): array
    {
        if (! $evidence) {
            return ['score' => 0, 'max' => 20, 'basis' => self::t(
                $lang,
                'No supporting document has been filed.',
                'Aucun document justificatif n\'a été déposé.'
            )];
        }

        $score = min(12, count($evidence) * 4);
        $notes = [count($evidence) . self::t(
            $lang,
            ' supporting ' . (count($evidence) === 1 ? 'document' : 'documents') . ' filed',
            ' document' . (count($evidence) === 1 ? '' : 's') . ' justificatif' . (count($evidence) === 1 ? '' : 's') . ' déposé' . (count($evidence) === 1 ? '' : 's')
        )];

        if (array_filter($evidence, fn ($d) => (bool) $d->content_hash)) {
            $score += 4;
            $notes[] = self::t($lang, 'at least one fixed by content hash', 'au moins un scellé par empreinte de contenu');
        }

        if (array_filter($evidence, fn ($d) => (bool) $d->provenance_event_id)) {
            $score += 4;
            $notes[] = self::t($lang, 'at least one tied to a specific event', 'au moins un rattaché à un événement précis');
        }

        return ['score' => $score, 'max' => 20, 'basis' => ucfirst(self::list($notes, $lang)) . '.'];
    }

    /**
     * Unassessable by default. A piece made this year that has not yet been
     * exhibited, accessioned, written about or awarded has no public record to
     * be incomplete — scoring that absence would measure the artisan's career,
     * which this index has no business doing.
     */
    private static function scorePublicRecord(array $events, string $lang = 'en'): array
    {
        if (! $events) {
            return ['score' => 0, 'max' => 0, 'basis' => self::t(
                $lang,
                'No exhibition, accession or publication has been recorded, so public history is not assessed.',
                'Aucune exposition, acquisition muséale ni publication n\'a été enregistrée : l\'histoire publique n\'est donc pas évaluée.'
            )];
        }

        $score = 6;
        $notes = [count($events) . self::t(
            $lang,
            ' public ' . (count($events) === 1 ? 'appearance' : 'appearances') . ' recorded',
            ' apparition' . (count($events) === 1 ? '' : 's') . ' publique' . (count($events) === 1 ? '' : 's') . ' enregistrée' . (count($events) === 1 ? '' : 's')
        )];

        if (array_filter($events, fn ($e) => (bool) $e->organisation)) {
            $score += 3;
            $notes[] = self::t($lang, 'the hosting institution named', 'l\'institution d\'accueil nommée');
        }
        if (array_filter($events, fn ($e) => $e->reference_no || $e->certificate_ref)) {
            $score += 3;
            $notes[] = self::t($lang, 'a catalogue or accession reference given', 'une référence de catalogue ou d\'inventaire fournie');
        }
        if (array_filter($events, fn ($e) => (bool) $e->is_verified)) {
            $score += 3;
            $notes[] = self::t($lang, 'at least one confirmed against the institution', 'au moins une confirmée auprès de l\'institution');
        }

        return ['score' => $score, 'max' => 15, 'basis' => ucfirst(self::list($notes, $lang)) . '.'];
    }

    /**
     * Unassessable by default. An object that has never needed a restorer has
     * no conservation history, and an index that treated that as a gap would
     * score newer and better-kept pieces down.
     */
    private static function scoreConservation(array $events, string $lang = 'en'): array
    {
        if (! $events) {
            return ['score' => 0, 'max' => 0, 'basis' => self::t(
                $lang,
                'No restoration or condition report has been recorded, so conservation history is not assessed.',
                'Aucune restauration ni constat d\'état n\'a été enregistré : l\'histoire de conservation n\'est donc pas évaluée.'
            )];
        }

        $ids  = array_column($events, 'id');
        $rows = DB::table('provenance_restorations')->whereIn('provenance_event_id', $ids)->get()->all();

        $score = 6;
        $notes = [count($events) . self::t(
            $lang,
            ' conservation ' . (count($events) === 1 ? 'entry' : 'entries'),
            ' entrée' . (count($events) === 1 ? '' : 's') . ' de conservation'
        )];

        if (array_filter($rows, fn ($r) => (bool) $r->description)) {
            $score += 3;
            $notes[] = self::t($lang, 'the intervention described', 'l\'intervention décrite');
        }
        if (array_filter($rows, fn ($r) => (bool) $r->materials_used)) {
            $score += 3;
            $notes[] = self::t($lang, 'the materials used listed', 'les matériaux employés listés');
        }
        if (array_filter($rows, fn ($r) => $r->before_images || $r->after_images)) {
            $score += 3;
            $notes[] = self::t($lang, 'before or after images attached', 'des images avant ou après jointes');
        }

        return ['score' => $score, 'max' => 15, 'basis' => ucfirst(self::list($notes, $lang)) . '.'];
    }

    /**
     * Unassessable by default. Whether a work has been appraised depends on
     * whether its holder needed insurance, not on how well the file is kept.
     */
    private static function scoreValuation(array $events, string $lang = 'en'): array
    {
        if (! $events) {
            return ['score' => 0, 'max' => 0, 'basis' => self::t(
                $lang,
                'No appraisal has been recorded, so valuation history is not assessed.',
                'Aucune expertise n\'a été enregistrée : l\'historique d\'estimation n\'est donc pas évalué.'
            )];
        }

        $rows = DB::table('provenance_valuations')
            ->whereIn('provenance_event_id', array_column($events, 'id'))->get()->all();

        if (! $rows) {
            return ['score' => 0, 'max' => 0, 'basis' => self::t(
                $lang,
                'A valuation event exists with no appraisal behind it, so it is not assessed.',
                'Un événement d\'estimation existe sans expertise derrière lui : il n\'est donc pas évalué.'
            )];
        }

        $score = 4;
        $notes = [count($rows) . self::t(
            $lang,
            ' recorded ' . (count($rows) === 1 ? 'appraisal' : 'appraisals'),
            ' expertise' . (count($rows) === 1 ? '' : 's') . ' enregistrée' . (count($rows) === 1 ? '' : 's')
        )];

        if (array_filter($rows, fn ($r) => (bool) $r->appraiser)) {
            $score += 2;
            $notes[] = self::t($lang, 'the appraiser named', 'l\'expert nommé');
        }
        if (array_filter($rows, fn ($r) => (bool) $r->appraiser_ref)) {
            $score += 2;
            $notes[] = self::t($lang, 'the appraiser identified by reference', 'l\'expert identifié par une référence');
        }

        // An appraisal older than five years no longer describes a market, so
        // recency is part of completeness here rather than a quality judgement.
        $recent = array_filter($rows, fn ($r) => Carbon::parse($r->valued_on)->diffInYears(now()) < 5);
        if ($recent) {
            $score += 2;
            $notes[] = self::t($lang, 'at least one made within the last five years', 'au moins une réalisée au cours des cinq dernières années');
        }

        return ['score' => $score, 'max' => 10, 'basis' => ucfirst(self::list($notes, $lang)) . '.'];
    }

    /**
     * A word for the number, computed from the same ratio and never set by
     * hand. Bands are wide on purpose: a one-point difference between two
     * dossiers is not a difference worth naming.
     *
     * Deliberately not translated. The band is a key, not prose: the views look
     * it up in their own vocabulary and pick a colour by matching it, so a band
     * that changed word with the language would silently lose both. It is also
     * the guarantee that a reader comparing the French and English sheets of the
     * same dossier sees the same verdict, which is the point of translating the
     * reasoning at all.
     */
    private static function band(int $total, int $max): string
    {
        if ($max <= 0) {
            return 'unassessed';
        }

        $pct = ($total / $max) * 100;

        return match (true) {
            $pct >= 90 => 'comprehensive',
            $pct >= 75 => 'strong',
            $pct >= 50 => 'partial',
            $pct >= 25 => 'limited',
            default    => 'minimal',
        };
    }

    /** "a, b and c" / "a, b et c" — the basis lines are read as prose, not as CSV. */
    private static function list(array $items, string $lang = 'en'): string
    {
        if (count($items) <= 1) {
            return (string) ($items[0] ?? '');
        }

        $last = array_pop($items);

        return implode(', ', $items) . self::t($lang, ' and ', ' et ') . $last;
    }
}
