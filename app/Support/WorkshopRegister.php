<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The register behind the Workshop Verification Certificate.
 *
 * The design for that document asks for roughly ninety facts about a production
 * facility — floor area, drying rooms, extraction, fire equipment, workforce
 * split, licences, quality scores. None of it existed. This is the record those
 * facts are read from, and the rule it is built around is that the certificate
 * may print a number only if a person put that number here.
 *
 * Three things carry the weight.
 *
 * The state machine is the first. draft → submitted → under_inspection →
 * verified, and the moves it refuses matter more than the ones it allows: a
 * workshop cannot become verified without an inspection that actually passed,
 * and a workshop that is not verified gets no certificate at all — exactly as an
 * unregistered product gets no certificate of authenticity. Those refusals throw
 * rather than return false, because a caller who forgets to check a boolean
 * produces a signed attestation for a facility nobody visited.
 *
 * The scoring is the second. assessment() follows the same discipline as
 * ExportRegister::readiness(): every dimension reports a `basis` phrase saying
 * why it scored what it did, and a dimension with nothing to assess reports a
 * maximum of zero and drops out of the denominator rather than quietly taking
 * full marks or a zero it did not earn. A workshop about which the register
 * knows nothing must be legible as unassessed, not as excellent and not as
 * failing.
 *
 * The third is what is missing. The artwork carries an "AI & Field Inspection"
 * panel — satellite confirmation of the site, AI image matching, a fraud-risk
 * score. There is no model, no imagery feed and no fraud system behind this
 * platform, so none of that is modelled, and checks() omits those keys entirely
 * rather than returning them false. A false tick is a claim that a check ran and
 * failed; an absent key is the truth, which is that the check does not exist. A
 * human inspection is a real event with a named, answerable inspector, and that
 * is the only evidence here that can raise a workshop's standing.
 *
 * What this is not: an inspectorate, a licensing body or a certifying authority
 * in law. It records what named inspectors and owners declare, and timestamps
 * it. That is genuinely useful. It is also the limit, and the document says so.
 */
class WorkshopRegister
{
    /** ISO 3166-1 alpha-2 segment used in issued numbers. */
    public const COUNTRY = 'CM';

    /** The certificate type under which everything here is signed and logged. */
    public const TYPE = 'wvc';

    /** Statuses from which each move is legal. Anything else throws. */
    private const ALLOWED_FROM = [
        'submit'          => ['draft', 'submitted'],
        'beginInspection' => ['submitted', 'under_inspection'],
        'verify'          => ['under_inspection', 'submitted', 'verified'],
        'suspend'         => ['verified', 'under_inspection'],
        'revoke'          => ['draft', 'submitted', 'under_inspection', 'verified', 'suspended'],
    ];

    /* ─────────────────────────── Permanent identifier ──────────────────── */

    /**
     * The workshop's lifelong number, assigned once and never reissued.
     *
     * Deliberately not derived from the business: a business may be renamed,
     * re-registered or claimed by a new owner, and a certificate quoting the
     * site must still resolve to the same site afterwards.
     */
    public static function gwnFor(int|object $workshop): string
    {
        $row = is_object($workshop) ? $workshop : self::require($workshop);

        if ($row->gwn) {
            return $row->gwn;
        }

        $gwn = sprintf('AH237-GWN-%s-%s', self::COUNTRY, str_pad((string) $row->id, 10, '0', STR_PAD_LEFT));

        DB::table('workshops')->where('id', $row->id)->update(['gwn' => $gwn, 'updated_at' => now()]);
        $row->gwn = $gwn;

        return $gwn;
    }

    public static function find(int $id): ?object
    {
        return DB::table('workshops')->find($id);
    }

    /* ────────────────────────────── Opening ────────────────────────────── */

    /**
     * Opens a draft workshop for a business.
     *
     * What is carried over is only what the business record genuinely holds —
     * address, coordinates, region, founding year. What is *not* set is every
     * infrastructure and workforce field: the platform has measured nothing at
     * this point, and a zero written here would become a measurement on a
     * printed certificate. geo_verified_at stays null for the same reason; the
     * business's coordinates are a pin somebody dropped, not a site visit.
     */
    public static function openFor(Business $business, array $data = []): object
    {
        return DB::transaction(function () use ($business, $data) {
            $row = [
                'uuid'        => (string) Str::uuid(),
                'business_id' => $business->id,
                'name'        => $data['name'] ?? $business->name_fr,
                'country'     => $data['country'] ?? self::COUNTRY,
                'region_id'   => $data['region_id'] ?? $business->region_id,
                'city_id'     => $data['city_id'] ?? $business->city_id,
                'address'     => $data['address'] ?? $business->address_fr,
                'gps_lat'     => $data['gps_lat'] ?? $business->gps_lat,
                'gps_lng'     => $data['gps_lng'] ?? $business->gps_lng,
                // Only the year is known, so only the year is asserted; the
                // first of January is the conventional stand-in and the field
                // is documented as a year, not a date of founding.
                'established_on' => $data['established_on']
                    ?? ($business->year_established ? $business->year_established . '-01-01' : null),
                'owner_user_id' => $data['owner_user_id'] ?? $business->user_id,
                'status'        => 'draft',
                'created_at'    => now(),
                'updated_at'    => now(),
            ];

            // Anything else the caller genuinely holds is carried through, but
            // nothing is invented to fill a column.
            foreach ([
                'registration_no', 'workshop_type', 'legal_status', 'division', 'subdivision',
                'village', 'community', 'altitude_m', 'total_area_m2', 'production_rooms',
                'finishing_areas', 'storage_areas', 'drying_areas', 'packaging_areas',
                'display_areas', 'water_supply', 'electricity_supply', 'internet',
                'accessibility', 'fire_safety_equipment', 'emergency_exits', 'master_artisans',
                'skilled_workers', 'apprentices', 'female_workers', 'youth_workers',
                'max_monthly_capacity', 'production_standards', 'packaging_standards',
                'manager_name', 'renewable_materials', 'waste_management', 'recycling',
                'water_conservation', 'energy_efficiency', 'carbon_note',
            ] as $column) {
                if (array_key_exists($column, $data)) {
                    $row[$column] = $data[$column];
                }
            }

            $id = DB::table('workshops')->insertGetId($row);

            self::gwnFor($id);

            CertificationAuthority::appendToChain(self::TYPE, $id, 'opened');

            return self::find($id);
        });
    }

    /* ────────────────────────── Recorded evidence ──────────────────────── */

    /** Appends one inventory item. Quantity defaults to one item, not to none. */
    public static function recordEquipment(int $workshopId, array $item): object
    {
        self::require($workshopId);

        $id = DB::table('workshop_equipment')->insertGetId([
            'workshop_id' => $workshopId,
            'category'    => $item['category'] ?? 'other',
            'label'       => $item['label'],
            'quantity'    => $item['quantity'] ?? 1,
            'notes'       => $item['notes'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return DB::table('workshop_equipment')->find($id);
    }

    /**
     * Appends a licence or permit row.
     *
     * The status defaults to `unassessed`, never `valid`. A row created because
     * the platform knows a workshop ought to hold a business licence is not
     * evidence that it holds one, and verified_at/verified_by stay null until a
     * named person actually looked at the document.
     */
    public static function recordCompliance(int $workshopId, array $item): object
    {
        self::require($workshopId);

        $id = DB::table('workshop_compliance')->insertGetId([
            'workshop_id' => $workshopId,
            'kind'        => $item['kind'],
            'reference'   => $item['reference'] ?? null,
            'issued_on'   => isset($item['issued_on']) ? Carbon::parse($item['issued_on'])->toDateString() : null,
            'expires_on'  => isset($item['expires_on']) ? Carbon::parse($item['expires_on'])->toDateString() : null,
            'status'      => $item['status'] ?? 'unassessed',
            'verified_at' => isset($item['verified_at']) ? Carbon::parse($item['verified_at']) : null,
            'verified_by' => $item['verified_by'] ?? null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return DB::table('workshop_compliance')->find($id);
    }

    /**
     * Records a human inspection.
     *
     * Every sub-score the inspector did not give stays null. A blank dimension
     * is not a nought — it is a part of the workshop nobody assessed — and
     * assessment() reads it that way rather than averaging it in.
     */
    public static function recordInspection(int $workshopId, array $report): object
    {
        self::require($workshopId);

        $row = [
            'uuid'           => (string) Str::uuid(),
            'workshop_id'    => $workshopId,
            'reference'      => $report['reference'] ?? null,
            'inspected_on'   => isset($report['inspected_on'])
                ? Carbon::parse($report['inspected_on'])->toDateString()
                : now()->toDateString(),
            'inspector_name' => $report['inspector_name'] ?? null,
            'inspector_ref'  => $report['inspector_ref'] ?? null,
            'method'         => $report['method'] ?? 'on_site',
            'findings'       => $report['findings'] ?? null,
            'outcome'        => $report['outcome'] ?? null,
            'next_due_on'    => isset($report['next_due_on']) ? Carbon::parse($report['next_due_on'])->toDateString() : null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ];

        foreach (self::DIMENSIONS as $dimension) {
            $row[$dimension . '_score'] = $report[$dimension . '_score'] ?? null;
        }

        $id = DB::table('workshop_inspections')->insertGetId($row);

        return DB::table('workshop_inspections')->find($id);
    }

    /** The seven dimensions an inspector may score, each out of twenty. */
    public const DIMENSIONS = [
        'infrastructure', 'equipment', 'workforce', 'safety',
        'compliance', 'sustainability', 'documentation',
    ];

    /* ────────────────────────────── Lifecycle ──────────────────────────── */

    /**
     * The owner puts the workshop forward for inspection.
     *
     * Not legal from revoked or archived. A revoked workshop is one whose
     * standing was withdrawn for cause, and letting it re-enter the queue by
     * simply resubmitting would make revocation a formality the owner can undo.
     * Re-entry after revocation is a deliberate act by the platform, not a
     * self-service one.
     */
    public static function submit(int $id, ?string $actor = null): object
    {
        $w = self::require($id);

        self::assertTransition($w, 'submit');
        self::move($w, 'submitted', 'submitted', null, $actor);

        return self::require($id);
    }

    public static function beginInspection(int $id, ?string $actor = null): object
    {
        $w = self::require($id);

        self::assertTransition($w, 'beginInspection');
        self::move($w, 'under_inspection', 'inspection_begun', null, $actor);

        return self::require($id);
    }

    /**
     * Raises the workshop to verified against a specific inspection.
     *
     * Three refusals, each of which exists because the alternative is the
     * platform attesting to a facility it has no evidence about:
     *
     *  - the status ladder, so a workshop cannot jump from draft to verified
     *    without ever having been put forward or looked at;
     *  - the inspection must belong to this workshop and must have passed. An
     *    inspection recorded as failed or inconclusive is evidence *against*
     *    verification, and treating "an inspection exists" as sufficient would
     *    make the outcome field decorative;
     *  - the level is capped at what the evidence supports, see levelCap().
     *
     * Note the signature: this method is also the public certificate lookup when
     * handed a reference string. The two are one name because the certificate
     * calls both "verify", and the dispatch is on argument type.
     */
    public static function verify(int|string $ref, int|string|null $second = null, ?int $level = null): object|array
    {
        if (is_string($ref)) {
            return self::verifyCertificate($ref, $second === null ? null : (string) $second);
        }

        $w          = self::require($ref);
        $inspection = DB::table('workshop_inspections')->find((int) $second);

        self::assertTransition($w, 'verify');

        if (! $inspection || (int) $inspection->workshop_id !== (int) $w->id) {
            throw new DomainException("Workshop {$w->gwn} cannot be verified: inspection [{$second}] does not belong to it.");
        }

        if (! in_array($inspection->outcome, ['passed', 'passed_with_conditions'], true)) {
            throw new DomainException(
                "Workshop {$w->gwn} cannot be verified: its inspection recorded an outcome of ["
                . ($inspection->outcome ?? 'none') . '], not a pass.'
            );
        }

        $granted = min($level ?? 1, self::levelCap($inspection));

        DB::table('workshops')->where('id', $w->id)->update([
            'status'             => 'verified',
            'verification_level' => $granted,
            'verified_at'        => now(),
            'next_inspection_on' => $inspection->next_due_on,
            'updated_at'         => now(),
        ]);

        CertificationAuthority::appendToChain(self::TYPE, (int) $w->id, 'verified', 'inspection ' . $inspection->uuid);

        return self::require($ref);
    }

    /**
     * The ceiling a single inspection can justify.
     *
     * The reasoning is about evidence, not generosity. A document review is not
     * a site visit — nobody stood in the building — so it cannot carry a
     * workshop above the bottom tier however good the paperwork looked. A pass
     * with conditions attached is, by its own wording, a pass that is not yet
     * fully met. And an inspection that scored only two of the seven dimensions
     * has not seen enough of the place to support a top-tier attestation.
     *
     * The caller may always ask for less. It may never get more.
     */
    private static function levelCap(object $inspection): int
    {
        $cap = match ($inspection->method) {
            'on_site'                       => 7,
            'photographic'                  => 3,
            'remote', 'document_review'     => 2,
            default                         => 2,
        };

        if ($inspection->outcome === 'passed_with_conditions') {
            $cap = min($cap, 3);
        }

        // And one tier per dimension the inspector actually scored, so the top
        // tier requires an inspection that looked at the whole facility rather
        // than at one room. Floored at one: a pass is still a pass.
        $scored = 0;

        foreach (self::DIMENSIONS as $dimension) {
            if ($inspection->{$dimension . '_score'} !== null) {
                $scored++;
            }
        }

        return max(1, min($cap, $scored + 1));
    }

    public static function suspend(int $id, string $reason, ?string $actor = null): object
    {
        $w = self::require($id);

        self::assertTransition($w, 'suspend');
        self::move($w, 'suspended', 'suspended', $reason, $actor);

        // Live certificates describe a standing the register no longer grants.
        DB::table('workshop_certificates')->where('workshop_id', $id)->where('status', 'active')
            ->update(['status' => 'suspended', 'updated_at' => now()]);

        return self::require($id);
    }

    public static function revoke(int $id, string $reason, ?string $actor = null): object
    {
        $w = self::require($id);

        self::assertTransition($w, 'revoke');

        DB::table('workshops')->where('id', $id)->update([
            'status'             => 'revoked',
            // The tier goes with the standing. Leaving a level behind on a
            // revoked workshop would let a stale page keep printing it.
            'verification_level' => null,
            'updated_at'         => now(),
        ]);

        CertificationAuthority::appendToChain(self::TYPE, $id, 'revoked', $reason, $actor);

        DB::table('workshop_certificates')->where('workshop_id', $id)
            ->whereIn('status', ['active', 'suspended'])
            ->update(['status' => 'revoked', 'revoked_reason' => $reason, 'updated_at' => now()]);

        return self::require($id);
    }

    /* ─────────────────────────────── Issuing ───────────────────────────── */

    /**
     * Mints the Workshop Verification Certificate.
     *
     * Refuses outright for a workshop that is not verified. This is the same
     * rule as a draft product getting no certificate of authenticity, and it is
     * the rule the whole class exists to enforce: the document's entire content
     * is "this facility was inspected and meets a standard", so issuing one for
     * a facility that was never inspected is not a degraded certificate, it is a
     * false one.
     *
     * expires_at is taken from the inspection's next_due_on when it has one,
     * because the certificate's authority is the inspection's authority and must
     * not outlive it. Where the inspector set no return date, a year is used —
     * stated as a policy default rather than pretending an inspector chose it.
     */
    public static function issue(int $workshopId, ?int $inspectionId = null): object
    {
        $w = self::require($workshopId);

        if ($w->status !== 'verified') {
            throw new DomainException(
                "Workshop {$w->gwn} cannot be certified from status [{$w->status}]: "
                . 'a verification certificate may only be issued for a verified workshop.'
            );
        }

        $inspection = $inspectionId
            ? DB::table('workshop_inspections')->find($inspectionId)
            : self::latestPassedInspection($workshopId);

        if (! $inspection) {
            throw new DomainException("Workshop {$w->gwn} cannot be certified: no passed inspection is on file.");
        }

        return DB::transaction(function () use ($w, $workshopId, $inspection) {
            $issuedAt = now();
            $seq      = str_pad((string) (DB::table('workshop_certificates')->count() + 1), 10, '0', STR_PAD_LEFT);
            $no       = sprintf('AH237-WVC-%s-%s-%s', self::COUNTRY, $issuedAt->format('Y'), $seq);

            // Earlier certificates describe an inspection that has been
            // superseded by this one.
            DB::table('workshop_certificates')->where('workshop_id', $workshopId)->where('status', 'active')
                ->update(['status' => 'expired', 'updated_at' => now()]);

            $assessment = self::assessment($workshopId);

            $row = [
                'uuid'           => (string) Str::uuid(),
                'certificate_no' => $no,
                'version'        => DB::table('workshop_certificates')->where('workshop_id', $workshopId)->count() + 1,
                'workshop_id'    => $workshopId,
                'inspection_id'  => $inspection->id,
                'level'          => (int) ($w->verification_level ?: 1),
                'checks'         => json_encode(self::checks($workshopId)),
                // Frozen at issue: the printed page shows what was true on the
                // day, and a later change must show as superseded rather than
                // silently rewrite the document's own figures.
                'metrics'        => json_encode([
                    'assessment'      => $assessment,
                    'export_readiness' => self::exportReadiness($workshopId),
                ]),
                'status'           => 'active',
                'verification_pin' => strtoupper(Str::password(8, true, true, false, false)),
                'issued_at'        => $issuedAt,
                'expires_at'       => $inspection->next_due_on
                    ? Carbon::parse($inspection->next_due_on)->endOfDay()
                    : $issuedAt->copy()->addYear(),
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $row['content_hash'] = self::contentHash($w, $inspection, $row);
            $row['signature']    = ProductCertificate::signatureFor($no, $row['content_hash'], $w->gwn);

            [$row['ca_signature'], $row['ca_kid']] = CertificationAuthority::signCertificate(
                self::TYPE, $no, $row['content_hash'], $issuedAt->toIso8601String()
            );

            $id = DB::table('workshop_certificates')->insertGetId($row);

            CertificationAuthority::appendToChain(self::TYPE, $id, 'issued');

            return DB::table('workshop_certificates')->find($id);
        });
    }

    /**
     * The facts the signature covers.
     *
     * Restricted to what the certificate asserts: which site, at which
     * coordinates, verified to which level, on the strength of which inspection
     * by which named inspector. Deliberately not the equipment inventory or the
     * verification counter — buying a second band saw does not make yesterday's
     * attestation untrue, and putting mutable inventory under the signature
     * would supersede a valid document every time a tool was logged.
     */
    public static function contentHash(object $w, object $inspection, array $row): string
    {
        return hash('sha256', json_encode([
            'certificate_no' => $row['certificate_no'],
            'gwn'            => $w->gwn,
            'workshop_uuid'  => $w->uuid,
            'version'        => (int) $row['version'],
            'name'           => $w->name,
            'country'        => $w->country,
            'gps'            => [$w->gps_lat, $w->gps_lng],
            'level'          => (int) $row['level'],
            'inspection'     => $inspection->uuid,
            'inspected_on'   => (string) $inspection->inspected_on,
            'inspector'      => $inspection->inspector_name,
            'method'         => $inspection->method,
            'outcome'        => $inspection->outcome,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /**
     * Public verification of an issued certificate.
     *
     * Returns a status a buyer or an importer can act on rather than a bare
     * true/false: "this workshop's inspection has lapsed" and "this number does
     * not exist" are entirely different situations and must not collapse into
     * one red cross.
     *
     * Expiry is computed from the date rather than trusted from the status
     * column. Nothing sweeps the table nightly, so a certificate whose
     * inspection lapsed this morning still reads `active` in storage — and a
     * lapsed attestation presented as live is the exact failure this method
     * exists to prevent.
     *
     * @return array{status:string, certificate:?object, signature?:array}
     */
    public static function verifyCertificate(string $ref, ?string $pin = null): array
    {
        $ref = trim($ref);

        $c = DB::table('workshop_certificates')
            ->where('certificate_no', $ref)
            ->orWhere('uuid', $ref)
            ->first();

        if (! $c) {
            return ['status' => 'notfound', 'certificate' => null];
        }

        // Only demanded when one is offered, so a QR scan (which carries the
        // PIN) and a number read off a printed page both work.
        if ($pin !== null && strtoupper(trim($pin)) !== strtoupper((string) $c->verification_pin)) {
            return ['status' => 'pin_mismatch', 'certificate' => null];
        }

        DB::table('workshop_certificates')->where('id', $c->id)->update([
            'verification_count' => (int) $c->verification_count + 1,
            'last_verified_at'   => now(),
        ]);

        $signature = self::signatureState($c);

        if ($c->status === 'revoked') {
            return ['status' => 'revoked', 'certificate' => $c, 'signature' => $signature];
        }

        if ($c->expires_at && Carbon::parse($c->expires_at)->isPast()) {
            return ['status' => 'expired', 'certificate' => $c, 'signature' => $signature];
        }

        if ($c->status === 'expired') {
            return ['status' => 'expired', 'certificate' => $c, 'signature' => $signature];
        }

        if ($c->status === 'suspended') {
            return ['status' => 'superseded', 'certificate' => $c, 'signature' => $signature];
        }

        // The certified facts moved after issue. Usually an amendment rather
        // than fraud, but the holder of the printed page is reading facts the
        // register no longer holds and must be told so.
        $w          = self::find((int) $c->workshop_id);
        $inspection = $c->inspection_id ? DB::table('workshop_inspections')->find($c->inspection_id) : null;

        if ($w && $inspection && $c->content_hash) {
            $recomputed = self::contentHash($w, $inspection, [
                'certificate_no' => $c->certificate_no,
                'version'        => (int) $c->version,
                'level'          => (int) $c->level,
            ]);

            if ($recomputed !== $c->content_hash) {
                return ['status' => 'superseded', 'certificate' => $c, 'signature' => $signature];
            }
        }

        return ['status' => 'valid', 'certificate' => $c, 'signature' => $signature];
    }

    /** @return array{state:string, kid:?string} */
    public static function signatureState(object $c): array
    {
        if (! $c->ca_signature) {
            return ['state' => 'unsigned', 'kid' => null];
        }

        $ok = CertificationAuthority::verifyCertificate(
            self::TYPE,
            $c->certificate_no,
            $c->content_hash,
            $c->issued_at ? Carbon::parse($c->issued_at)->toIso8601String() : null,
            $c->ca_signature
        );

        return ['state' => $ok ? 'valid' : 'invalid', 'kid' => $c->ca_kid];
    }

    /** The most recent inspection that actually passed, or null. */
    public static function latestPassedInspection(int $workshopId): ?object
    {
        return DB::table('workshop_inspections')
            ->where('workshop_id', $workshopId)
            ->whereIn('outcome', ['passed', 'passed_with_conditions'])
            ->orderByDesc('inspected_on')->orderByDesc('id')
            ->first();
    }

    /** The most recent inspection of any outcome, or null. */
    public static function latestInspection(int $workshopId): ?object
    {
        return DB::table('workshop_inspections')
            ->where('workshop_id', $workshopId)
            ->orderByDesc('inspected_on')->orderByDesc('id')
            ->first();
    }

    /* ───────────────────────────── Assessment ──────────────────────────── */

    /**
     * The quality assessment the certificate prints, across seven dimensions.
     *
     * Read the `max` before the `score`. A dimension with max 0 is one the
     * register holds nothing to judge: it contributes to neither the total nor
     * the denominator, so the percentage is over what could actually be
     * assessed, and the unassessed dimensions are shown beside it rather than
     * averaged away.
     *
     * That rule costs something and is the point. It is very easy to write this
     * so that an unrecorded workshop scores well — every absent fact silently
     * contributing nothing to a small denominator — and the result is a signed
     * document giving a facility nobody visited a high quality rating. It is
     * equally easy to write it so that absence scores zero out of full, which
     * defames a workshop for the platform's own missing data. Neither is a
     * measurement. Dropping out with a stated reason is.
     *
     * Every dimension also returns a `basis`: one phrase saying why. It is what
     * lets the printed certificate show its working, and what makes a wrong
     * score arguable rather than merely disappointing.
     *
     * @return array{categories:array<string,array{score:int,max:int,basis:string}>, total:int, max:int, rating:string}
     */
    public static function assessment(int $workshopId): array
    {
        $w          = self::require($workshopId);
        $inspection = self::latestInspection($workshopId);

        $categories = [
            'infrastructure' => self::scoreInfrastructure($w, $inspection),
            'equipment'      => self::scoreEquipment($workshopId, $inspection),
            'workforce'      => self::scoreWorkforce($w, $inspection),
            'safety'         => self::scoreSafety($w, $inspection),
            'compliance'     => self::scoreCompliance($workshopId, $inspection),
            'sustainability' => self::scoreSustainability($w, $inspection),
            'documentation'  => self::scoreDocumentation($workshopId, $inspection),
        ];

        $total = array_sum(array_column($categories, 'score'));
        $max   = array_sum(array_column($categories, 'max'));

        return [
            'categories' => $categories,
            'total'      => $total,
            'max'        => $max,
            'rating'     => self::rating($total, $max),
        ];
    }

    /**
     * Rating bands over the assessable percentage.
     *
     * "unassessed" when nothing at all could be judged. Calling that "poor"
     * would be a claim in the other direction, and just as unsupported.
     */
    public static function rating(int $total, int $max): string
    {
        if ($max <= 0) {
            return 'unassessed';
        }

        $pct = $total / $max * 100;

        return match (true) {
            $pct >= 95 => 'excellent',
            $pct >= 85 => 'very good',
            $pct >= 70 => 'good',
            $pct >= 50 => 'fair',
            default    => 'poor',
        };
    }

    /**
     * The inspector's own score for a dimension, folded in where they gave one.
     *
     * @return array{0:int,1:int,2:?string} [score, max, note]
     */
    private static function inspectorScore(?object $inspection, string $dimension): array
    {
        $value = $inspection?->{$dimension . '_score'};

        if ($value === null) {
            return [0, 0, null];
        }

        $who = $inspection->inspector_name ?: 'an unnamed inspector';

        return [(int) $value, 20, "scored {$value}/20 by {$who} on " . Carbon::parse($inspection->inspected_on)->toDateString()];
    }

    /**
     * Infrastructure. Note there is no path here that produces "0 m²": an
     * unmeasured workshop drops the area out entirely rather than reporting a
     * measurement of nothing.
     */
    private static function scoreInfrastructure(object $w, ?object $inspection): array
    {
        $score = 0;
        $max   = 0;
        $notes = [];

        if ($w->total_area_m2 !== null) {
            $score += 5;
            $max   += 5;
            $notes[] = rtrim(rtrim(number_format((float) $w->total_area_m2, 2, '.', ''), '0'), '.') . ' m² of floor area measured';
        }

        if ($w->production_rooms !== null) {
            $max += 4;
            $score += (int) $w->production_rooms > 0 ? 4 : 0;
            $notes[] = $w->production_rooms . ' production room(s) recorded';
        }

        foreach (['water_supply' => 'a water supply', 'electricity_supply' => 'an electricity supply'] as $column => $label) {
            if ($w->$column) {
                $score += 3;
                $max   += 3;
                $notes[] = $label . ' is recorded';
            }
        }

        if ($w->accessibility !== null) {
            $max += 4;
            $score += ['poor' => 1, 'fair' => 2, 'good' => 3, 'excellent' => 4][$w->accessibility] ?? 0;
            $notes[] = "access rated {$w->accessibility}";
        }

        [$s, $m, $note] = self::inspectorScore($inspection, 'infrastructure');
        $score += $s;
        $max   += $m;

        if ($note) {
            $notes[] = $note;
        }

        return $max === 0
            ? self::cat(0, 0, 'no floor area, room count, utility or access rating has been recorded for this workshop')
            : self::cat($score, $max, implode('; ', $notes));
    }

    /** Equipment. Nothing on the inventory means nothing to judge. */
    private static function scoreEquipment(int $workshopId, ?object $inspection): array
    {
        $rows = DB::table('workshop_equipment')->where('workshop_id', $workshopId)->get();

        [$s, $m, $note] = self::inspectorScore($inspection, 'equipment');

        if ($rows->isEmpty()) {
            return $m === 0
                ? self::cat(0, 0, 'no equipment has been recorded, so there is no inventory to assess')
                : self::cat($s, $m, $note . ', though no inventory has been recorded');
        }

        $score = 0;
        $notes = [];

        $production = $rows->whereIn('category', ['major_machine', 'hand_tool', 'carving_tool', 'power_tool', 'kiln_or_oven']);
        $protective = $rows->whereIn('category', ['safety', 'extraction', 'drying']);

        if ($production->isNotEmpty()) {
            $score += 4;
            $notes[] = $production->count() . ' production item(s) on the inventory';
        } else {
            $notes[] = 'no production machinery or tooling on the inventory';
        }

        if ($protective->isNotEmpty()) {
            $score += 3;
            $notes[] = 'safety or extraction equipment recorded';
        } else {
            $notes[] = 'no safety or extraction equipment recorded';
        }

        if ((int) $rows->sum('quantity') >= 3) {
            $score += 3;
            $notes[] = 'a stocked workshop by item count';
        }

        if ($note) {
            $notes[] = $note;
        }

        return self::cat($score + $s, 10 + $m, implode('; ', $notes));
    }

    /**
     * Workforce. Every count is separately optional, so a workshop that
     * declared its masters but not its apprentices is judged on the masters
     * alone rather than penalised for the silence.
     */
    private static function scoreWorkforce(object $w, ?object $inspection): array
    {
        $score = 0;
        $max   = 0;
        $notes = [];

        foreach ([
            'master_artisans' => [6, 'master artisan'],
            'skilled_workers' => [5, 'skilled worker'],
            'apprentices'     => [5, 'apprentice'],
        ] as $column => [$weight, $label]) {
            if ($w->$column === null) {
                continue;
            }

            $max += $weight;
            $count = (int) $w->$column;
            $score += $count > 0 ? $weight : 0;
            $notes[] = "{$count} {$label}(s) recorded";
        }

        if ($w->female_workers !== null || $w->youth_workers !== null) {
            $max += 4;
            $score += ((int) $w->female_workers > 0 ? 2 : 0) + ((int) $w->youth_workers > 0 ? 2 : 0);
            $notes[] = 'the female and youth split is recorded';
        }

        [$s, $m, $note] = self::inspectorScore($inspection, 'workforce');
        $score += $s;
        $max   += $m;

        if ($note) {
            $notes[] = $note;
        }

        return $max === 0
            ? self::cat(0, 0, 'no workforce figures have been recorded for this workshop')
            : self::cat($score, $max, implode('; ', $notes));
    }

    /**
     * Safety. The two facility booleans are three-valued in storage, and that
     * is load-bearing here: false means an inspector looked and found no fire
     * equipment, which scores nothing out of a real maximum, while null means
     * nobody looked and drops out.
     */
    private static function scoreSafety(object $w, ?object $inspection): array
    {
        $score = 0;
        $max   = 0;
        $notes = [];

        if ($w->fire_safety_equipment !== null) {
            $max += 8;
            $score += $w->fire_safety_equipment ? 8 : 0;
            $notes[] = $w->fire_safety_equipment ? 'fire safety equipment is fitted' : 'no fire safety equipment was found';
        }

        if ($w->emergency_exits !== null) {
            $max += 7;
            $score += $w->emergency_exits ? 7 : 0;
            $notes[] = $w->emergency_exits ? 'emergency exits are present' : 'no emergency exits were found';
        }

        [$s, $m, $note] = self::inspectorScore($inspection, 'safety');
        $score += $s;
        $max   += $m;

        if ($note) {
            $notes[] = $note;
        }

        return $max === 0
            ? self::cat(0, 0, 'nobody has recorded whether this workshop has fire equipment or emergency exits')
            : self::cat($score, $max, implode('; ', $notes));
    }

    /**
     * Compliance, counting only the licences somebody actually assessed.
     *
     * A row sitting at `unassessed` is the platform noting that an obligation
     * exists, not evidence about it, so it adds nothing to the denominator. The
     * effect is that a workshop with three untouched licence rows reads as
     * "unassessed" rather than as "failing" — which is the truth, and the
     * printed certificate says which licences are still unchecked.
     */
    private static function scoreCompliance(int $workshopId, ?object $inspection): array
    {
        $rows = DB::table('workshop_compliance')->where('workshop_id', $workshopId)->get();

        $score = 0;
        $max   = 0;
        $notes = [];
        $unassessed = [];

        foreach ($rows as $row) {
            if ($row->status === 'unassessed') {
                $unassessed[] = str_replace('_', ' ', $row->kind);
                continue;
            }

            $max += 5;
            $kind = str_replace('_', ' ', $row->kind);

            // A licence whose expiry has passed is expired whatever the column
            // says; nothing sweeps this table, so the date is the authority.
            $lapsed = $row->expires_on && Carbon::parse($row->expires_on)->isPast();

            if ($lapsed || $row->status === 'expired') {
                $notes[] = "the {$kind} has expired";
            } elseif (in_array($row->status, ['valid', 'not_applicable'], true)) {
                $score += $row->verified_at ? 5 : 3;
                $notes[] = $row->verified_at
                    ? "the {$kind} was checked by " . ($row->verified_by ?: 'the platform')
                    : "the {$kind} is declared {$row->status} but unchecked";
            } else {
                $score += 1;
                $notes[] = "the {$kind} is {$row->status}";
            }
        }

        [$s, $m, $note] = self::inspectorScore($inspection, 'compliance');
        $score += $s;
        $max   += $m;

        if ($note) {
            $notes[] = $note;
        }

        if ($unassessed) {
            $notes[] = 'never assessed: ' . implode(', ', $unassessed);
        }

        return $max === 0
            ? self::cat(0, 0, $unassessed
                ? 'the licences on file have never been assessed: ' . implode(', ', $unassessed)
                : 'no licence or permit has been recorded for this workshop')
            : self::cat($score, $max, implode('; ', $notes));
    }

    /** Sustainability, judged only on the questions somebody answered. */
    private static function scoreSustainability(object $w, ?object $inspection): array
    {
        $score = 0;
        $max   = 0;
        $notes = [];

        foreach ([
            'renewable_materials' => 'renewable materials',
            'waste_management'    => 'waste management',
            'recycling'           => 'recycling',
            'water_conservation'  => 'water conservation',
        ] as $column => $label) {
            if ($w->$column === null) {
                continue;
            }

            $max += 4;
            $score += $w->$column ? 4 : 0;
            $notes[] = $w->$column ? "{$label} in place" : "no {$label}";
        }

        if ($w->energy_efficiency !== null) {
            $max += 4;
            $score += ['poor' => 1, 'fair' => 2, 'good' => 3, 'excellent' => 4][$w->energy_efficiency] ?? 0;
            $notes[] = "energy efficiency rated {$w->energy_efficiency}";
        }

        [$s, $m, $note] = self::inspectorScore($inspection, 'sustainability');
        $score += $s;
        $max   += $m;

        if ($note) {
            $notes[] = $note;
        }

        return $max === 0
            ? self::cat(0, 0, 'no sustainability practice has been recorded either way for this workshop')
            : self::cat($score, $max, implode('; ', $notes));
    }

    /**
     * Documentation: what a third party could actually read back. An inspection
     * report by a named inspector, and licence references that can be looked up
     * somewhere other than here.
     */
    private static function scoreDocumentation(int $workshopId, ?object $inspection): array
    {
        $score = 0;
        $max   = 0;
        $notes = [];

        if ($inspection) {
            $max += 6;

            if ($inspection->inspector_name) {
                $score += 3;
                $notes[] = 'an inspection report attributed to ' . $inspection->inspector_name;
            } else {
                $notes[] = 'an inspection report with no named inspector';
            }

            if ($inspection->findings) {
                $score += 2;
                $notes[] = 'with written findings';
            } else {
                $notes[] = 'with no written findings';
            }

            if ($inspection->reference) {
                $score += 1;
                $notes[] = 'under a report reference';
            }
        }

        $referenced = DB::table('workshop_compliance')
            ->where('workshop_id', $workshopId)->whereNotNull('reference')->count();

        if ($referenced > 0) {
            $score += 4;
            $max   += 4;
            $notes[] = "{$referenced} licence reference(s) on file";
        }

        [$s, $m, $note] = self::inspectorScore($inspection, 'documentation');
        $score += $s;
        $max   += $m;

        if ($note) {
            $notes[] = $note;
        }

        return $max === 0
            ? self::cat(0, 0, 'no inspection report and no licence reference are on file for this workshop')
            : self::cat($score, $max, implode('; ', $notes));
    }

    /* ───────────────────────── Export readiness ────────────────────────── */

    /**
     * Whether this workshop can ship abroad, on the same rules as assessment().
     *
     * All four categories start unassessable, and that is correct: a workshop
     * that has never been asked about ISPM 15 packaging has not failed the
     * question, and printing "not ISPM 15 compliant" on a certificate because
     * the platform never asked would be a defamatory guess.
     *
     * @return array{categories:array<string,array{score:int,max:int,basis:string}>, total:int, max:int, rating:string}
     */
    public static function exportReadiness(int $workshopId): array
    {
        $w        = self::require($workshopId);
        $business = DB::table('businesses')->find($w->business_id);

        $categories = [
            'packaging'           => self::scoreExportPackaging($w),
            'shipping_experience' => self::scoreShippingExperience($business),
            'customs'             => self::scoreCustoms($workshopId),
            'traceability'        => self::scoreTraceability($w),
        ];

        $total = array_sum(array_column($categories, 'score'));
        $max   = array_sum(array_column($categories, 'max'));

        return [
            'categories' => $categories,
            'total'      => $total,
            'max'        => $max,
            'rating'     => self::rating($total, $max),
        ];
    }

    /**
     * ISPM 15 is the international standard for wood packaging, and it is the
     * one that stops a Cameroonian carving at a European border. It is only
     * claimed when the recorded packaging standard actually names it.
     */
    private static function scoreExportPackaging(object $w): array
    {
        $hasStandard = (bool) $w->packaging_standards;
        $hasArea     = $w->packaging_areas !== null;

        if (! $hasStandard && ! $hasArea) {
            return self::cat(0, 0, 'no packaging standard or packing area has been recorded, so export packing cannot be judged');
        }

        $score = 0;
        $max   = 0;
        $notes = [];

        if ($hasStandard) {
            $max += 6;
            $ispm = (bool) preg_match('/ispm\s*-?\s*15/i', (string) $w->packaging_standards);
            $score += $ispm ? 6 : 3;
            $notes[] = $ispm
                ? 'the recorded packaging standard names ISPM 15'
                : 'a packaging standard is recorded but does not name ISPM 15';
        }

        if ($hasArea) {
            $max += 4;
            $score += (int) $w->packaging_areas > 0 ? 4 : 0;
            $notes[] = $w->packaging_areas . ' dedicated packing area(s)';
        }

        return self::cat($score, $max, implode('; ', $notes));
    }

    /** Read off the business's declared export countries; never inferred. */
    private static function scoreShippingExperience(?object $business): array
    {
        $countries = $business && $business->export_countries
            ? array_filter((array) json_decode($business->export_countries, true))
            : [];

        if (! $countries) {
            return self::cat(0, 0, 'the business has declared no export destinations, so shipping experience is unknown');
        }

        $count = count($countries);

        return self::cat(min(10, $count * 3), 10, "exports declared to {$count} country/countries: " . implode(', ', $countries));
    }

    /** Customs readiness rests on paperwork somebody assessed, not on claims. */
    private static function scoreCustoms(int $workshopId): array
    {
        $rows = DB::table('workshop_compliance')
            ->where('workshop_id', $workshopId)
            ->whereIn('kind', ['export_packaging', 'tax_registration', 'business_licence'])
            ->where('status', '!=', 'unassessed')
            ->get();

        if ($rows->isEmpty()) {
            return self::cat(0, 0, 'no assessed export, tax or trading paperwork is on file for this workshop');
        }

        $score = 0;
        $notes = [];

        foreach ($rows as $row) {
            $kind   = str_replace('_', ' ', $row->kind);
            $lapsed = $row->expires_on && Carbon::parse($row->expires_on)->isPast();

            if (! $lapsed && in_array($row->status, ['valid', 'not_applicable'], true)) {
                $score += 4;
                $notes[] = "the {$kind} is in good standing";
            } else {
                $notes[] = "the {$kind} is {$row->status}";
            }
        }

        return self::cat(min(12, $score), 12, implode('; ', $notes));
    }

    /**
     * Traceability: whether pieces made here can be followed back to here. The
     * workshop number alone does not count — it is issued automatically and
     * proves nothing about the goods — so this rests on products actually
     * registered to the business.
     */
    private static function scoreTraceability(object $w): array
    {
        $registered = DB::table('products')
            ->where('business_id', $w->business_id)->whereNotNull('prn')->count();

        if ($registered === 0) {
            return self::cat(0, 0, 'no product from this workshop has been registered, so nothing can be traced back to it');
        }

        $certified = DB::table('product_certificates')
            ->join('products', 'products.id', '=', 'product_certificates.product_id')
            ->where('products.business_id', $w->business_id)
            ->whereNull('product_certificates.revoked_at')
            ->count();

        $score = 4 + ($certified > 0 ? 4 : 0) + ($w->gwn ? 2 : 0);

        return self::cat($score, 10, "{$registered} registered product(s), {$certified} of them certified, tied to workshop {$w->gwn}");
    }

    /* ─────────────────────────────── Checks ────────────────────────────── */

    /**
     * The tick map the certificate prints.
     *
     * A check the platform never performs is absent from this map, not false.
     * That distinction is the whole design: false says "we looked and it failed",
     * which is a claim about the workshop, while absence says "this check does
     * not exist here", which is a claim about us and is the true one.
     *
     * Hence no satellite_verified, no ai_image_match and no fraud_risk keys. The
     * artwork shows a panel for all three; there is no imagery feed, no model
     * and no fraud system behind this platform, and inventing three columns to
     * fill that panel would be the most expensive lie in the document. Nor is
     * there a site_geolocated key: coordinates typed into a profile are a claim,
     * and until someone stands at them there is no geolocation check to report.
     *
     * @return array<string,bool>
     */
    public static function checks(int $workshopId): array
    {
        $w          = self::require($workshopId);
        $inspection = self::latestInspection($workshopId);

        return [
            'workshop_registered'   => true,
            'owner_account_linked'  => (bool) $w->owner_user_id,
            'address_recorded'      => (bool) $w->address,
            'floor_area_measured'   => $w->total_area_m2 !== null,
            'equipment_inventoried' => DB::table('workshop_equipment')->where('workshop_id', $workshopId)->exists(),
            'inspection_on_file'    => (bool) $inspection,
            'inspected_on_site'     => $inspection && $inspection->method === 'on_site',
            'inspection_passed'     => (bool) self::latestPassedInspection($workshopId),
            'licence_checked'       => DB::table('workshop_compliance')
                ->where('workshop_id', $workshopId)->whereNotNull('verified_at')->exists(),
            'currently_verified'    => $w->status === 'verified',
        ];
    }

    /* ────────────────────────────── Internals ──────────────────────────── */

    private static function cat(int $score, int $max, string $basis): array
    {
        return ['score' => $score, 'max' => $max, 'basis' => $basis];
    }

    private static function require(int $id): object
    {
        $w = self::find($id);

        if (! $w) {
            throw new DomainException("No workshop [{$id}].");
        }

        return $w;
    }

    private static function assertTransition(object $w, string $move): void
    {
        if (! in_array($w->status, self::ALLOWED_FROM[$move], true)) {
            throw new DomainException(
                "Workshop {$w->gwn} cannot {$move} from status [{$w->status}]; "
                . 'permitted from [' . implode(', ', self::ALLOWED_FROM[$move]) . '].'
            );
        }
    }

    private static function move(object $w, string $status, string $event, ?string $note = null, ?string $actor = null): void
    {
        DB::table('workshops')->where('id', $w->id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        CertificationAuthority::appendToChain(self::TYPE, (int) $w->id, $event, $note, $actor);
    }
}
