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
    public static function timeline(Product $product): array
    {
        $entries = [];

        $registeredAt = $product->registered_at ?? $product->created_at;

        if ($registeredAt) {
            $entries[] = [
                'date'         => Carbon::parse($registeredAt)->toDateString(),
                'type'         => 'registration',
                'label'        => 'Registered on ArtisanHub237',
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
                    ? 'Created and first held by ' . $owner->legal_name
                    : 'Acquired by ' . $owner->legal_name,
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
    public static function journey(Product $product): array
    {
        $legs = [];

        foreach (ProvenanceRegistry::chain($product) as $owner) {
            if ($owner->country_code) {
                $legs[] = [
                    'country' => strtoupper($owner->country_code),
                    'date'    => $owner->owned_from ? Carbon::parse($owner->owned_from)->toDateString() : null,
                    'reason'  => $owner->is_original_creator
                        ? 'Made and first held by ' . $owner->legal_name
                        : 'Held by ' . $owner->legal_name,
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
    public static function legacyIndex(Product $product): array
    {
        $events   = self::events($product);
        $chain    = ProvenanceRegistry::chain($product);
        $evidence = DB::table('provenance_evidence')->where('product_id', $product->id)->get()->all();

        $of = fn (array $types) => array_values(array_filter($events, fn ($e) => in_array($e->type, $types, true)));

        $categories = [
            'registration'    => self::scoreRegistration($product),
            'ownership_chain' => self::scoreOwnership($chain),
            'evidence'        => self::scoreEvidence($evidence),
            'public_record'   => self::scorePublicRecord($of(self::PUBLIC_RECORD_TYPES)),
            'conservation'    => self::scoreConservation($of(self::CONSERVATION_TYPES)),
            'valuation'       => self::scoreValuation($of(['valuation'])),
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
    private static function scoreRegistration(Product $product): array
    {
        $have = [];
        $miss = [];

        $product->prn ? $have[] = 'registry number' : $miss[] = 'a registry number';
        ($product->registered_at || $product->created_at) ? $have[] = 'registration date' : $miss[] = 'a registration date';
        $product->business_id ? $have[] = 'an identified maker' : $miss[] = 'an identified maker';
        $product->uuid ? $have[] = 'a permanent reference' : $miss[] = 'a permanent reference';

        $score = count($have) * 5;

        return [
            'score' => $score,
            'max'   => 20,
            'basis' => $miss
                ? 'The registry entry is missing ' . self::list($miss) . '.'
                : 'The registry entry carries a number, a date, an identified maker and a permanent reference.',
        ];
    }

    /**
     * Always assessable: a registered product always has at least the maker's
     * founding row. Judged on whether each holder can be identified and placed,
     * not on how many times the piece has changed hands — a work that has never
     * been sold has a perfect chain, not a thin one.
     */
    private static function scoreOwnership(array $chain): array
    {
        if (! $chain) {
            return ['score' => 0, 'max' => 20, 'basis' => 'No ownership row exists, so the chain begins nowhere.'];
        }

        $score = 8;                                     // an unbroken chain exists
        $notes = [count($chain) . ' documented ' . (count($chain) === 1 ? 'holder' : 'holders')];

        $creator = array_filter($chain, fn ($o) => (bool) $o->is_original_creator);
        if ($creator) {
            $score += 4;
            $notes[] = 'the maker recorded as first holder';
        }

        $placed = count(array_filter($chain, fn ($o) => (bool) $o->country_code));
        if ($placed === count($chain)) {
            $score += 4;
            $notes[] = 'every holder placed in a country';
        } elseif ($placed > 0) {
            $score += 2;
            $notes[] = $placed . ' of ' . count($chain) . ' holders placed in a country';
        }

        $identified = count(array_filter($chain, fn ($o) => $o->verification_level !== 'unverified'));
        if ($identified === count($chain)) {
            $score += 4;
            $notes[] = 'no anonymous holder';
        }

        return ['score' => $score, 'max' => 20, 'basis' => ucfirst(self::list($notes)) . '.'];
    }

    /**
     * Always assessable: whether supporting paper was filed is a question that
     * can be asked of any product, and the answer for a new one is honestly
     * "none yet".
     */
    private static function scoreEvidence(array $evidence): array
    {
        if (! $evidence) {
            return ['score' => 0, 'max' => 20, 'basis' => 'No supporting document has been filed.'];
        }

        $score = min(12, count($evidence) * 4);
        $notes = [count($evidence) . ' supporting ' . (count($evidence) === 1 ? 'document' : 'documents') . ' filed'];

        if (array_filter($evidence, fn ($d) => (bool) $d->content_hash)) {
            $score += 4;
            $notes[] = 'at least one fixed by content hash';
        }

        if (array_filter($evidence, fn ($d) => (bool) $d->provenance_event_id)) {
            $score += 4;
            $notes[] = 'at least one tied to a specific event';
        }

        return ['score' => $score, 'max' => 20, 'basis' => ucfirst(self::list($notes)) . '.'];
    }

    /**
     * Unassessable by default. A piece made this year that has not yet been
     * exhibited, accessioned, written about or awarded has no public record to
     * be incomplete — scoring that absence would measure the artisan's career,
     * which this index has no business doing.
     */
    private static function scorePublicRecord(array $events): array
    {
        if (! $events) {
            return ['score' => 0, 'max' => 0, 'basis' => 'No exhibition, accession or publication has been recorded, so public history is not assessed.'];
        }

        $score = 6;
        $notes = [count($events) . ' public ' . (count($events) === 1 ? 'appearance' : 'appearances') . ' recorded'];

        if (array_filter($events, fn ($e) => (bool) $e->organisation)) {
            $score += 3;
            $notes[] = 'the hosting institution named';
        }
        if (array_filter($events, fn ($e) => $e->reference_no || $e->certificate_ref)) {
            $score += 3;
            $notes[] = 'a catalogue or accession reference given';
        }
        if (array_filter($events, fn ($e) => (bool) $e->is_verified)) {
            $score += 3;
            $notes[] = 'at least one confirmed against the institution';
        }

        return ['score' => $score, 'max' => 15, 'basis' => ucfirst(self::list($notes)) . '.'];
    }

    /**
     * Unassessable by default. An object that has never needed a restorer has
     * no conservation history, and an index that treated that as a gap would
     * score newer and better-kept pieces down.
     */
    private static function scoreConservation(array $events): array
    {
        if (! $events) {
            return ['score' => 0, 'max' => 0, 'basis' => 'No restoration or condition report has been recorded, so conservation history is not assessed.'];
        }

        $ids  = array_column($events, 'id');
        $rows = DB::table('provenance_restorations')->whereIn('provenance_event_id', $ids)->get()->all();

        $score = 6;
        $notes = [count($events) . ' conservation ' . (count($events) === 1 ? 'entry' : 'entries')];

        if (array_filter($rows, fn ($r) => (bool) $r->description)) {
            $score += 3;
            $notes[] = 'the intervention described';
        }
        if (array_filter($rows, fn ($r) => (bool) $r->materials_used)) {
            $score += 3;
            $notes[] = 'the materials used listed';
        }
        if (array_filter($rows, fn ($r) => $r->before_images || $r->after_images)) {
            $score += 3;
            $notes[] = 'before or after images attached';
        }

        return ['score' => $score, 'max' => 15, 'basis' => ucfirst(self::list($notes)) . '.'];
    }

    /**
     * Unassessable by default. Whether a work has been appraised depends on
     * whether its holder needed insurance, not on how well the file is kept.
     */
    private static function scoreValuation(array $events): array
    {
        if (! $events) {
            return ['score' => 0, 'max' => 0, 'basis' => 'No appraisal has been recorded, so valuation history is not assessed.'];
        }

        $rows = DB::table('provenance_valuations')
            ->whereIn('provenance_event_id', array_column($events, 'id'))->get()->all();

        if (! $rows) {
            return ['score' => 0, 'max' => 0, 'basis' => 'A valuation event exists with no appraisal behind it, so it is not assessed.'];
        }

        $score = 4;
        $notes = [count($rows) . ' recorded ' . (count($rows) === 1 ? 'appraisal' : 'appraisals')];

        if (array_filter($rows, fn ($r) => (bool) $r->appraiser)) {
            $score += 2;
            $notes[] = 'the appraiser named';
        }
        if (array_filter($rows, fn ($r) => (bool) $r->appraiser_ref)) {
            $score += 2;
            $notes[] = 'the appraiser identified by reference';
        }

        // An appraisal older than five years no longer describes a market, so
        // recency is part of completeness here rather than a quality judgement.
        $recent = array_filter($rows, fn ($r) => Carbon::parse($r->valued_on)->diffInYears(now()) < 5);
        if ($recent) {
            $score += 2;
            $notes[] = 'at least one made within the last five years';
        }

        return ['score' => $score, 'max' => 10, 'basis' => ucfirst(self::list($notes)) . '.'];
    }

    /**
     * A word for the number, computed from the same ratio and never set by
     * hand. Bands are wide on purpose: a one-point difference between two
     * dossiers is not a difference worth naming.
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

    /** "a, b and c" — the basis lines are read as prose, not as CSV. */
    private static function list(array $items): string
    {
        if (count($items) <= 1) {
            return (string) ($items[0] ?? '');
        }

        $last = array_pop($items);

        return implode(', ', $items) . ' and ' . $last;
    }
}
