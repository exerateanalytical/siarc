<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The register behind the Export Authenticity Certificate.
 *
 * Two things live here and they are worth naming separately, because they have
 * very different failure modes.
 *
 * The first is the state machine. A consignment moves draft → approved →
 * shipped → delivered, and the moves that are refused matter more than the ones
 * that are allowed: a piece reported stolen must not be exportable, and a piece
 * with no Certificate of Authenticity has nothing whose authenticity could be
 * certified for export. Those refusals throw rather than return false, because a
 * caller that forgets to check a boolean would produce a customs document for a
 * stolen object, and that is not a failure mode to leave to caller discipline.
 *
 * The second is the scoring — readiness() and risk(). The artwork these back
 * shows an export readiness of "98% EXCELLENT" across eight categories and a
 * six-line risk assessment, and the entire question is whether those numbers are
 * measurements or decoration. Here they are measurements, and the design that
 * makes them honest is this: every category reports a `basis`, one short phrase
 * saying why it scored what it scored, and a category with nothing to assess
 * reports a maximum of zero rather than full marks.
 *
 * That last rule is the one that costs something and is the one that matters. It
 * is very easy to write a scorer where an absent shipment silently contributes
 * nothing and the percentage stays high; the result is a document that gives a
 * museum a 98% confidence score for a consignment nobody has packed. Instead the
 * unassessable categories drop out of the denominator and are labelled, so the
 * reader sees "98% of the six things we could judge, and here are the two we
 * could not" rather than a flattering average over an absence.
 *
 * The one deliberate exception is compliance, which is scored out of its full
 * maximum even when nothing has been declared. An unmade cultural heritage
 * declaration is not a gap in our knowledge — it is a real, disqualifying
 * failing, because the piece cannot lawfully leave the country without one. So
 * it scores zero rather than dropping out.
 *
 * What this is not: a customs authority, an export licensing body, or an
 * inspector. Every compliance value here is a declaration some named party made,
 * timestamped by us. That is genuinely useful and it is also the limit.
 */
class ExportRegister
{
    /** ISO 3166-1 alpha-2 segment used in issued numbers. */
    public const COUNTRY = 'CM';

    /** The certificate type under which everything here is signed and logged. */
    public const TYPE = 'eac';

    /** Statuses from which each move is legal. Anything else throws. */
    private const ALLOWED_FROM = [
        'approve' => ['draft', 'submitted', 'under_review'],
        'reject'  => ['draft', 'submitted', 'under_review', 'approved'],
        'ship'    => ['approved'],
        'deliver' => ['shipped'],
    ];

    /* ─────────────────────────────── Parties ───────────────────────────── */

    /**
     * The exporter record for a business, created on first use.
     *
     * Note what is *not* set: export_licence_no and verified_at stay null. The
     * platform has seen no licence and verified nothing at this point, and a
     * verified_at stamped at creation would turn a row we made into a check we
     * performed.
     */
    public static function exporterFor(Business $business): object
    {
        $existing = DB::table('exporters')->where('business_id', $business->id)->first();

        if ($existing) {
            return $existing;
        }

        $id = DB::table('exporters')->insertGetId([
            'uuid'          => (string) Str::uuid(),
            'business_id'   => $business->id,
            'user_id'       => $business->user_id,
            'legal_name'    => $business->name_fr,
            'trading_name'  => $business->name_en,
            'country'       => self::COUNTRY,
            'address'       => $business->address_fr,
            'contact_email' => $business->email,
            'contact_phone' => $business->phone,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return DB::table('exporters')->find($id);
    }

    /**
     * Records a condition inspection.
     *
     * Hung off the product with the consignment optional, because a condition
     * report outlives the export it was made for: the provenance certificate
     * wants the same rows, and a cancelled consignment must not take an
     * inspector's findings with it.
     *
     * Facets the inspector did not record stay null. A blank facet is not
     * "excellent" — it is a part of the object nobody looked at — and the
     * scoring reads it that way.
     */
    public static function recordCondition(Product $product, array $report, ?int $consignmentId = null): object
    {
        $row = [
            'uuid'                  => (string) Str::uuid(),
            'product_id'            => $product->id,
            'export_consignment_id' => $consignmentId,
            'inspected_at'          => isset($report['inspected_at']) ? Carbon::parse($report['inspected_at']) : now(),
            'inspector_name'        => $report['inspector_name'] ?? null,
            'inspector_ref'         => $report['inspector_ref'] ?? null,
            'notes'                 => $report['notes'] ?? null,
            'report_ref'            => $report['report_ref'] ?? null,
            'created_at'            => now(),
            'updated_at'            => now(),
        ];

        foreach (['surface', 'structural', 'finish', 'preservation', 'packaging', 'overall'] as $facet) {
            $row[$facet] = $report[$facet] ?? null;
        }

        $id = DB::table('condition_reports')->insertGetId($row);

        return DB::table('condition_reports')->find($id);
    }

    /* ────────────────────────────── Lifecycle ──────────────────────────── */

    /**
     * Opens a draft consignment.
     *
     * Both numbers are assigned now rather than at issue, because the exporter
     * needs a reference to quote on the paperwork they file before approval, and
     * a number that appears only once the answer is yes is useless to them.
     *
     * @param array{name:string, type?:string, country?:string, city?:string, address?:string} $importer
     */
    public static function open(Product $product, array $importer, array $opts = []): object
    {
        return DB::transaction(function () use ($product, $importer, $opts) {
            $year = now()->format('Y');
            $seq  = str_pad((string) (DB::table('export_consignments')->count() + 1), 12, '0', STR_PAD_LEFT);

            $product->loadMissing('business');

            $id = DB::table('export_consignments')->insertGetId([
                'uuid'               => (string) Str::uuid(),
                'certificate_no'     => sprintf('AH237-EAC-%s-%s-%s', self::COUNTRY, $year, $seq),
                'gecn'               => sprintf('AH237-GECN-%s-%s-%s', self::COUNTRY, $year, $seq),
                'version'            => 1,
                'product_id'         => $product->id,
                'exporter_id'        => $opts['exporter_id']
                    ?? ($product->business ? self::exporterFor($product->business)->id : null),
                // Tied to whoever the ownership chain says currently holds it,
                // so the certificate names a sender the register agrees with.
                'owner_ownership_id' => $opts['owner_ownership_id']
                    ?? ProvenanceRegistry::currentOwner($product)?->id,

                'importer_name'      => $importer['name'],
                'importer_type'      => $importer['type'] ?? 'individual',
                'importer_country'   => $importer['country'] ?? null,
                'importer_city'      => $importer['city'] ?? null,
                'importer_address'   => $importer['address'] ?? null,
                'intended_purpose'   => $opts['intended_purpose'] ?? 'sale',

                'country_of_origin'      => $opts['country_of_origin'] ?? self::COUNTRY,
                'origin_certificate_ref' => $opts['origin_certificate_ref'] ?? null,
                // Left null unless a declaration was actually supplied. The
                // scoring reads null as "nobody has declared", which is true.
                'cultural_heritage_declaration' => $opts['cultural_heritage_declaration'] ?? null,
                'ethical_sourcing_declaration'  => $opts['ethical_sourcing_declaration'] ?? null,
                'protected_materials'           => $opts['protected_materials'] ?? 'unassessed',
                'export_permit_no'       => $opts['export_permit_no'] ?? null,
                'customs_declaration_no' => $opts['customs_declaration_no'] ?? null,
                'inspection_status'      => $opts['inspection_status'] ?? null,
                'inspected_at'           => $opts['inspected_at'] ?? null,

                'status'     => 'draft',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            CertificationAuthority::appendToChain(self::TYPE, $id, 'opened');

            return self::find($id);
        });
    }

    public static function find(int $id): ?object
    {
        return DB::table('export_consignments')->find($id);
    }

    /**
     * Approves the consignment for export.
     *
     * Three refusals, each of which exists because the alternative is the
     * platform putting its name on a document it has no business signing:
     *
     *  - the status ladder, so an already-shipped consignment cannot be
     *    retroactively approved into a different history;
     *  - an active flag on the piece, because exporting something reported
     *    stolen or under investigation is the precise harm a provenance register
     *    is supposed to make harder;
     *  - a missing Certificate of Authenticity, because an *authenticity*
     *    certificate for export must have an authenticity record underneath it.
     *
     * The flag check uses ProductFlags::isClear() and therefore refuses on any
     * live concern, including a disputed ownership or an export restriction, not
     * only the two named above. That is stricter than the letter of the rule and
     * correct: none of the four flags describe a piece that should leave.
     */
    public static function approve(int $id, ?string $actor = null): object
    {
        $c = self::require($id);

        self::assertTransition($c, 'approve');

        if (! ProductFlags::isClear((int) $c->product_id)) {
            $flags = implode(', ', array_column(ProductFlags::active((int) $c->product_id), 'flag'));

            throw new DomainException("Consignment {$c->certificate_no} cannot be approved: the piece carries an active concern ({$flags}).");
        }

        if (! self::coaFor((int) $c->product_id)) {
            throw new DomainException("Consignment {$c->certificate_no} cannot be approved: the piece holds no Certificate of Authenticity to certify for export.");
        }

        self::move($c, 'approved', 'approved', null, $actor);

        return self::find($id);
    }

    public static function reject(int $id, string $reason, ?string $actor = null): object
    {
        $c = self::require($id);

        self::assertTransition($c, 'reject');

        // The reason is stored and logged rather than discarded: an exporter
        // told only "rejected" cannot fix anything, and a register that cannot
        // say why it refused is not auditable.
        DB::table('export_consignments')->where('id', $id)->update([
            'rejected_reason' => $reason,
            'status'          => 'rejected',
            'updated_at'      => now(),
        ]);

        CertificationAuthority::appendToChain(self::TYPE, $id, 'rejected', $reason, $actor);

        return self::find($id);
    }

    /**
     * Books the physical movement and marks the consignment shipped.
     *
     * Only legal from approved. This is the transition the whole state machine
     * exists for: a shipment row created before approval would mean a piece
     * physically left while its clearance was still pending, and no later
     * correction can un-ship it.
     */
    public static function ship(int $id, array $shipment, ?string $actor = null): object
    {
        $c = self::require($id);

        self::assertTransition($c, 'ship');

        return DB::transaction(function () use ($c, $id, $shipment, $actor) {
            $shippedAt = isset($shipment['shipped_at']) ? Carbon::parse($shipment['shipped_at']) : now();

            DB::table('shipments')->insert([
                'uuid'                  => (string) Str::uuid(),
                'export_consignment_id' => $id,
                'carrier'               => $shipment['carrier'] ?? null,
                'service'               => $shipment['service'] ?? null,
                'awb_no'                => $shipment['awb_no'] ?? null,
                'bill_of_lading_no'     => $shipment['bill_of_lading_no'] ?? null,
                'tracking_no'           => $shipment['tracking_no'] ?? null,
                'flight_or_vessel'      => $shipment['flight_or_vessel'] ?? null,
                'port_of_exit'          => $shipment['port_of_exit'] ?? null,
                'shipped_at'            => $shippedAt,
                'expected_at'           => isset($shipment['expected_at']) ? Carbon::parse($shipment['expected_at']) : null,
                'package_count'         => $shipment['package_count'] ?? null,
                'crate_ref'             => $shipment['crate_ref'] ?? null,
                'gross_weight_kg'       => $shipment['gross_weight_kg'] ?? null,
                'net_weight_kg'         => $shipment['net_weight_kg'] ?? null,
                'dimensions'            => $shipment['dimensions'] ?? null,
                // Absent means not stated, and is stored as false rather than
                // assumed true. A crate whose protections nobody recorded is not
                // a protected crate.
                'shock_protection'      => (bool) ($shipment['shock_protection'] ?? false),
                'climate_protection'    => (bool) ($shipment['climate_protection'] ?? false),
                'humidity_protection'   => (bool) ($shipment['humidity_protection'] ?? false),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            self::move($c, 'shipped', 'shipped', $shipment['carrier'] ?? null, $actor);

            return self::find($id);
        });
    }

    public static function deliver(int $id, ?string $actor = null): object
    {
        $c = self::require($id);

        self::assertTransition($c, 'deliver');

        return DB::transaction(function () use ($c, $id, $actor) {
            DB::table('shipments')->where('export_consignment_id', $id)->whereNull('delivered_at')
                ->update(['delivered_at' => now(), 'updated_at' => now()]);

            self::move($c, 'delivered', 'delivered', null, $actor);

            return self::find($id);
        });
    }

    /* ─────────────────────────────── Issuing ───────────────────────────── */

    /**
     * Issues the certificate: hashes the certified facts, signs them, and logs
     * the issuance on the authority's hash chain.
     *
     * The PIN drops ambiguous characters for the same reason the Certificate of
     * Authenticity does — it gets read aloud down a phone line to a customs
     * broker, and an O that was a 0 fails verification for no reason.
     */
    public static function issue(int $id): object
    {
        $c = self::require($id);

        return DB::transaction(function () use ($c, $id) {
            $issuedAt = now();
            $hash     = self::contentHash($c);

            [$caSignature, $caKid] = CertificationAuthority::signCertificate(
                self::TYPE, $c->certificate_no, $hash, $issuedAt->toIso8601String()
            );

            DB::table('export_consignments')->where('id', $id)->update([
                'content_hash'     => $hash,
                // The HMAC catches our own storage corruption; the Ed25519
                // signature beside it is what an importing museum can check
                // against the published key without asking us anything.
                'signature'        => ProductCertificate::signatureFor($c->certificate_no, $hash, $c->gecn),
                'ca_signature'     => $caSignature,
                'ca_kid'           => $caKid,
                'verification_pin' => $c->verification_pin
                    ?: strtoupper(Str::password(8, true, true, false, false)),
                'issued_at'        => $issuedAt,
                'updated_at'       => now(),
            ]);

            CertificationAuthority::appendToChain(self::TYPE, $id, 'issued');

            return self::find($id);
        });
    }

    /**
     * The facts the signature covers.
     *
     * Restricted to what the certificate actually asserts about the export: the
     * piece, the parties, the purpose and the declarations. Deliberately not the
     * shipment — a re-booked flight does not make the certificate untrue, and
     * putting the tracking number under the signature would supersede a valid
     * document every time a carrier changed.
     */
    public static function contentHash(object $c): string
    {
        $product = Product::find($c->product_id);

        return hash('sha256', json_encode([
            'certificate_no'    => $c->certificate_no,
            'gecn'              => $c->gecn,
            'version'           => (int) $c->version,
            'product_uuid'      => $product?->uuid,
            'prn'               => $product?->prn,
            'exporter'          => DB::table('exporters')->where('id', $c->exporter_id)->value('legal_name'),
            'importer'          => $c->importer_name,
            'importer_type'     => $c->importer_type,
            'importer_country'  => $c->importer_country,
            'intended_purpose'  => $c->intended_purpose,
            'country_of_origin' => $c->country_of_origin,
            'cultural_heritage' => $c->cultural_heritage_declaration,
            'ethical_sourcing'  => $c->ethical_sourcing_declaration,
            'protected_materials' => $c->protected_materials,
            'export_permit_no'  => $c->export_permit_no,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /**
     * Public verification of an export certificate.
     *
     * Returns a status a customs officer or a receiving registrar can act on
     * rather than a bare true/false, on the same reasoning as the Certificate of
     * Authenticity: "this consignment's declarations were amended after the
     * certificate was issued" is a completely different situation from "this
     * number does not exist", and the two must not collapse into one red cross.
     *
     * @return array{status:string, consignment:?object, signature?:array}
     */
    public static function verify(string $ref, ?string $pin = null): array
    {
        $ref = trim($ref);

        $c = DB::table('export_consignments')
            ->where('certificate_no', $ref)
            ->orWhere('gecn', $ref)
            ->orWhere('uuid', $ref)
            ->first();

        if (! $c) {
            return ['status' => 'notfound', 'consignment' => null];
        }

        // Only demanded when one is offered, so a QR scan (which carries the
        // PIN) and a number typed off a printed page both work.
        if ($pin !== null && strtoupper(trim($pin)) !== strtoupper((string) $c->verification_pin)) {
            return ['status' => 'pin_mismatch', 'consignment' => null];
        }

        DB::table('export_consignments')->where('id', $c->id)->update([
            'verification_count' => (int) $c->verification_count + 1,
            'last_verified_at'   => now(),
        ]);

        $signature = self::signatureState($c);

        if (in_array($c->status, ['revoked', 'cancelled', 'rejected'], true)) {
            return ['status' => 'revoked', 'consignment' => $c, 'signature' => $signature];
        }

        // The declarations moved after issue. Usually an amendment rather than
        // fraud, but the holder of the printed page is looking at facts the
        // register no longer holds, and must be told so.
        if ($c->content_hash && self::contentHash($c) !== $c->content_hash) {
            return ['status' => 'superseded', 'consignment' => $c, 'signature' => $signature];
        }

        return ['status' => 'valid', 'consignment' => $c, 'signature' => $signature];
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

    /* ────────────────────────────── Readiness ──────────────────────────── */

    /**
     * Export readiness across the eight categories the certificate prints.
     *
     * Read the `max` field before the `score`. A category with max 0 is one the
     * register holds nothing to judge, and it contributes to neither the score
     * nor the total maximum — so the percentage is over what could actually be
     * assessed, and the unassessed categories are shown beside it rather than
     * averaged away. Full marks for an absence would be the single easiest way
     * to make this whole document a lie.
     *
     * Every category also returns a `basis`: one phrase stating why. That is not
     * decoration either — it is what lets the printed certificate show its
     * working, and it is what makes a wrong score arguable rather than merely
     * disappointing.
     *
     * @return array{categories:array<string,array{score:int,max:int,basis:string}>, total:int, max:int, rating:string}
     */
    public static function readiness(int $id): array
    {
        $c         = self::require($id);
        $productId = (int) $c->product_id;

        $categories = [
            'authenticity'  => self::scoreAuthenticity($productId),
            'provenance'    => self::scoreProvenance($productId),
            'documentation' => self::scoreDocumentation($c, $productId),
            'ownership'     => self::scoreOwnership($c, $productId),
            'packaging'     => self::scorePackaging($id),
            'insurance'     => self::scoreInsurance($productId),
            'compliance'    => self::scoreCompliance($c),
            'logistics'     => self::scoreLogistics($id),
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
     * "unassessed" when nothing at all could be judged — a rating of "poor" for
     * a consignment we know nothing about would be a claim we cannot support in
     * the other direction.
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
     * Authenticity: is there a live Certificate of Authenticity, does it still
     * describe the current record, and does its signature check out.
     *
     * Always assessable — a product either holds a certificate or it does not,
     * and "none issued" is a finding rather than an unknown, so it scores zero
     * out of the full maximum.
     */
    private static function scoreAuthenticity(int $productId): array
    {
        $coa = self::coaFor($productId);

        if (! $coa) {
            return self::cat(0, 20, 'no Certificate of Authenticity has been issued for this piece');
        }

        $score = 10;
        $notes = ['certificate of authenticity on record'];

        $product = Product::find($productId);
        $current = $product && ProductCertificate::hashFor($product) === $coa->content_hash;

        if ($current) {
            $score += 4;
            $notes[] = 'its content hash still matches the live record';
        } else {
            $notes[] = 'the product record changed after the certificate was issued';
        }

        $state = ProductCertificate::signatureState($coa)['state'];

        if ($state === 'valid') {
            $score += 6;
            $notes[] = 'the authority signature verifies';
        } elseif ($state === 'unsigned') {
            $notes[] = 'it carries no authority signature';
        } else {
            $notes[] = 'the authority signature does not verify';
        }

        return self::cat($score, 20, implode('; ', $notes));
    }

    /**
     * Provenance: does the chain start at the maker, is it unbroken, and is the
     * holder at the far end anything more than self-declared.
     */
    private static function scoreProvenance(int $productId): array
    {
        $rows = DB::table('product_ownerships')->where('product_id', $productId)->orderBy('sequence')->get();

        if ($rows->isEmpty()) {
            return self::cat(0, 15, 'no ownership record exists for this piece');
        }

        $score = 0;
        $notes = [];

        if ((bool) $rows->first()->is_original_creator) {
            $score += 8;
            $notes[] = 'the chain begins with the maker';
        } else {
            $notes[] = 'the chain does not begin with the maker';
        }

        // Contiguous sequence numbers and exactly one open end. Either failing
        // means the chain has a hole a piece could have been swapped through.
        $contiguous = $rows->pluck('sequence')->map('intval')->all() === range(1, $rows->count());
        $open       = $rows->whereNull('owned_until')->count();

        if ($contiguous && $open === 1) {
            $score += 4;
            $notes[] = 'unbroken with a single current holder';
        } else {
            $notes[] = 'the chain is broken or has more than one open holder';
        }

        $current = $rows->firstWhere('owned_until', null);

        if ($current && in_array($current->verification_level, ['verified', 'institution'], true)) {
            $score += 3;
            $notes[] = 'the current holder is verified';
        } else {
            $notes[] = 'the current holder is self-declared';
        }

        return self::cat($score, 15, implode('; ', $notes));
    }

    /**
     * Documentation: the three references that make an export checkable by
     * somebody other than us — origin, permit, customs declaration — plus a
     * dated condition report, which is what a receiving institution compares the
     * object against when the crate is opened.
     *
     * Always assessable: their absence is exactly what a customs officer needs
     * to be told, not something we should decline to judge.
     */
    private static function scoreDocumentation(object $c, int $productId): array
    {
        $held   = [];
        $absent = [];

        foreach ([
            'origin_certificate_ref' => 'certificate of origin',
            'export_permit_no'       => 'export permit',
            'customs_declaration_no' => 'customs declaration',
        ] as $column => $label) {
            $c->$column ? $held[] = $label : $absent[] = $label;
        }

        $score = count($held) * 5;

        // A report signed by a named inspector is the one worth points: an
        // unattributed condition note cannot be taken up with anybody.
        $report = DB::table('condition_reports')
            ->where('product_id', $productId)
            ->whereNotNull('inspector_name')
            ->orderByDesc('inspected_at')
            ->first();

        if ($report) {
            $score += 5;
            $held[] = 'a condition report by ' . $report->inspector_name;
        } else {
            $absent[] = 'an inspected condition report';
        }

        $basis = $held ? 'holds ' . implode(', ', $held) : 'no export paperwork is on file';

        if ($absent) {
            $basis .= '; missing ' . implode(', ', $absent);
        }

        return self::cat($score, 20, $basis);
    }

    /**
     * Ownership: does the consignment name a sender the ownership chain agrees
     * with, and is that sender still the current holder.
     */
    private static function scoreOwnership(object $c, int $productId): array
    {
        if (! DB::table('product_ownerships')->where('product_id', $productId)->exists()) {
            return self::cat(0, 10, 'no ownership record exists to check the sender against');
        }

        if (! $c->owner_ownership_id) {
            return self::cat(0, 10, 'the consignment names no holder from the ownership chain');
        }

        $row = DB::table('product_ownerships')->find($c->owner_ownership_id);

        if (! $row || (int) $row->product_id !== $productId) {
            return self::cat(0, 10, 'the named holder does not belong to this piece');
        }

        if ($row->owned_until !== null) {
            // The sender held the piece once but no longer does. Not necessarily
            // fraud — a consignment left open through a sale does this — but it
            // is precisely the discrepancy an importing registrar must see.
            return self::cat(5, 10, 'the named holder is a past owner, not the current one');
        }

        return self::cat(10, 10, 'the sender is the current holder in the ownership chain');
    }

    /**
     * Packaging. Unassessable with no shipment: nothing has been packed, so
     * there is no crate to judge, and scoring it out of ten would penalise a
     * consignment for a step it has not reached yet.
     */
    private static function scorePackaging(int $id): array
    {
        $s = self::shipment($id);

        if (! $s) {
            return self::cat(0, 0, 'nothing has been packed yet, so there is no crate to assess');
        }

        $score = 0;
        $has   = [];
        $not   = [];

        foreach ([
            'shock_protection'    => 'shock protection',
            'climate_protection'  => 'climate control',
            'humidity_protection' => 'humidity control',
        ] as $column => $label) {
            if ($s->$column) {
                $score += 3;
                $has[] = $label;
            } else {
                $not[] = $label;
            }
        }

        if ($s->crate_ref) {
            $score += 1;
            $has[] = 'an identified crate';
        }

        $basis = $has ? 'crate provides ' . implode(', ', $has) : 'the crate records no protective measures';

        if ($not) {
            $basis .= '; no ' . implode(', ', $not);
        }

        return self::cat($score, 10, $basis);
    }

    /**
     * Insurance, read off the piece's live ownership transfer record — which is
     * where the platform already holds cover details, and duplicating them onto
     * the consignment would create two answers to one question.
     *
     * Unassessable when no cover has been recorded anywhere. Note this is not
     * the same as uninsured: a shipper may hold a blanket policy we never see.
     * Saying so is the honest report.
     */
    private static function scoreInsurance(int $productId): array
    {
        $cover = DB::table('ownership_transfers')
            ->where('product_id', $productId)
            ->whereNotNull('insurer_name')
            ->orderByDesc('id')
            ->first();

        if (! $cover) {
            return self::cat(0, 0, 'no insurance cover has been recorded with the platform');
        }

        $score = 3;
        $notes = ['cover declared with ' . $cover->insurer_name];

        if ($cover->insurance_policy_no) {
            $score += 3;
            $notes[] = 'under a stated policy number';
        } else {
            $notes[] = 'with no policy number given';
        }

        if ($cover->insurance_value) {
            $score += 2;
            $notes[] = 'for a stated value';
        } else {
            $notes[] = 'for no stated value';
        }

        // A policy whose window does not span today covers nothing today, so it
        // must not score as though it did.
        $inForce = $cover->coverage_start && $cover->coverage_end
            && now()->betweenIncluded(Carbon::parse($cover->coverage_start), Carbon::parse($cover->coverage_end));

        if ($inForce) {
            $score += 2;
            $notes[] = 'in force today';
        } else {
            $notes[] = 'with no in-force coverage window on record';
        }

        return self::cat($score, 10, implode(', ', $notes));
    }

    /**
     * Compliance — the one category scored out of its full maximum even when
     * nothing has been declared.
     *
     * The reasoning: elsewhere an absence means the platform cannot judge, and
     * dropping out is the honest answer. Here an absence means a legally
     * required declaration has not been made, which is a determinate failing and
     * the single most important thing this document can tell a customs officer.
     * An unassessed protected-materials question in particular must cost the
     * full five points — "we never checked whether this is ivory" cannot be
     * allowed to read the same as "we checked and it is not".
     */
    private static function scoreCompliance(object $c): array
    {
        $score = 0;
        $notes = [];

        foreach ([
            'cultural_heritage_declaration' => 'cultural heritage',
            'ethical_sourcing_declaration'  => 'ethical sourcing',
        ] as $column => $label) {
            $value = $c->$column;

            if (in_array($value, ['compliant', 'not_applicable'], true)) {
                $score += 5;
                $notes[] = "{$label} declared {$value}";
            } elseif ($value === null) {
                $notes[] = "no {$label} declaration has been made";
            } else {
                $notes[] = "{$label} is {$value}";
            }
        }

        if ($c->protected_materials === 'none') {
            $score += 5;
            $notes[] = 'assessed as containing no protected materials';
        } elseif ($c->protected_materials === 'unassessed') {
            $notes[] = 'protected materials have never been assessed';
        } else {
            $notes[] = "protected materials are {$c->protected_materials}";
        }

        return self::cat($score, 15, implode('; ', $notes));
    }

    /**
     * Logistics. Unassessable with no shipment booked, for the same reason as
     * packaging: there is no carrier to judge.
     */
    private static function scoreLogistics(int $id): array
    {
        $s = self::shipment($id);

        if (! $s) {
            return self::cat(0, 0, 'no carrier has been booked yet');
        }

        $score = 0;
        $notes = [];

        if ($s->carrier) {
            $score += 3;
            $notes[] = 'booked with ' . $s->carrier;
        } else {
            $notes[] = 'no carrier named';
        }

        // Either document is the consignment's legal identity in transit; one is
        // enough, and which one depends on air versus sea.
        if ($s->awb_no || $s->bill_of_lading_no) {
            $score += 3;
            $notes[] = 'under a transport document';
        } else {
            $notes[] = 'with no air waybill or bill of lading';
        }

        if ($s->port_of_exit) {
            $score += 2;
            $notes[] = 'leaving via ' . $s->port_of_exit;
        }

        if ($s->tracking_no || $s->expected_at) {
            $score += 2;
            $notes[] = 'trackable to an expected arrival';
        }

        return self::cat($score, 10, implode(', ', $notes));
    }

    /* ──────────────────────────────── Risk ─────────────────────────────── */

    /**
     * The risk assessment the certificate prints.
     *
     * The default is `unassessed`, never `low`. That inversion is the whole
     * point of this method. A risk model that starts every line at low and
     * raises it on evidence reports "low risk across the board" for a
     * consignment about which the platform knows nothing at all — which is the
     * most dangerous possible output, because it is indistinguishable on the
     * page from a consignment that was genuinely examined and found clean.
     *
     * Compliance is the sharpest case: a piece whose materials were never
     * examined is not low compliance risk, it is unquantified compliance risk,
     * and the two must not print the same.
     *
     * @return array<string,array{level:string, basis:string}>
     */
    public static function risk(int $id): array
    {
        $c         = self::require($id);
        $productId = (int) $c->product_id;
        $flags     = array_column(ProductFlags::active($productId), 'flag');
        $s         = self::shipment($id);

        return [
            'counterfeit'   => self::riskCounterfeit($productId, $flags),
            'ownership'     => self::riskOwnership($c, $productId, $flags),
            'compliance'    => self::riskCompliance($c, $flags),
            'transit'       => self::riskTransit($s),
            'environmental' => self::riskEnvironmental($s),
            'insurance'     => self::riskInsurance($productId),
        ];
    }

    private static function riskCounterfeit(int $productId, array $flags): array
    {
        if (in_array('reported_stolen', $flags, true)) {
            return self::level('high', 'the piece is the subject of an active theft report');
        }

        $coa = self::coaFor($productId);

        if (! $coa) {
            return self::level('high', 'nothing certifies this piece: no Certificate of Authenticity exists');
        }

        $product = Product::find($productId);

        if (! $product || ProductCertificate::hashFor($product) !== $coa->content_hash) {
            return self::level('medium', 'the product record has changed since its certificate was issued');
        }

        if (ProductCertificate::signatureState($coa)['state'] !== 'valid') {
            return self::level('medium', 'the certificate carries no verifying authority signature');
        }

        return self::level('low', 'a signed certificate of authenticity matches the current record');
    }

    private static function riskOwnership(object $c, int $productId, array $flags): array
    {
        if (in_array('disputed_ownership', $flags, true)) {
            return self::level('high', 'ownership of this piece is under active dispute');
        }

        if (in_array('under_investigation', $flags, true)) {
            return self::level('high', 'the piece is under investigation');
        }

        $rows = DB::table('product_ownerships')->where('product_id', $productId)->get();

        if ($rows->isEmpty()) {
            return self::level('unassessed', 'no ownership chain has been recorded for this piece');
        }

        if (! $c->owner_ownership_id) {
            return self::level('unassessed', 'the consignment names no holder to check against the chain');
        }

        $row = $rows->firstWhere('id', $c->owner_ownership_id);

        if (! $row || $row->owned_until !== null) {
            return self::level('medium', 'the sender is not the chain\'s current holder');
        }

        return in_array($row->verification_level, ['verified', 'institution'], true)
            ? self::level('low', 'the sender is the verified current holder')
            : self::level('medium', 'the sender is the current holder but self-declared');
    }

    private static function riskCompliance(object $c, array $flags): array
    {
        if (in_array('export_restricted', $flags, true)) {
            return self::level('high', 'an export restriction has been raised against this piece');
        }

        // Restricted or CITES-listed material with no permit is the concrete
        // high case: the export is unlawful as it stands, not merely unproven.
        if (in_array($c->protected_materials, ['cites_listed', 'restricted'], true) && ! $c->export_permit_no) {
            return self::level('high', "the piece contains {$c->protected_materials} material and no export permit is on file");
        }

        if (in_array($c->cultural_heritage_declaration, ['restricted'], true)) {
            return self::level('high', 'the cultural heritage declaration records a restriction');
        }

        // The honest default. Nobody has looked, so nothing can be said — and
        // saying "low" here would be the platform inventing a clearance.
        if ($c->protected_materials === 'unassessed') {
            return self::level('unassessed', 'the piece has never been assessed for protected or CITES-listed materials');
        }

        if ($c->cultural_heritage_declaration === null || $c->ethical_sourcing_declaration === null) {
            return self::level('unassessed', 'the cultural heritage or ethical sourcing declaration has not been made');
        }

        if ($c->cultural_heritage_declaration === 'pending' || $c->ethical_sourcing_declaration === 'pending') {
            return self::level('medium', 'a compliance declaration is still pending');
        }

        return self::level('low', 'declarations are complete and no protected materials were found');
    }

    private static function riskTransit(?object $s): array
    {
        if (! $s) {
            return self::level('unassessed', 'no shipment has been booked, so no route can be judged');
        }

        $documented = (bool) ($s->awb_no || $s->bill_of_lading_no);
        $traceable  = (bool) $s->tracking_no;

        if ($s->carrier && $documented && $traceable) {
            return self::level('low', 'a named carrier, a transport document and live tracking are all on record');
        }

        if ($s->carrier || $documented) {
            return self::level('medium', 'the booking is only partly documented');
        }

        return self::level('high', 'a shipment exists with no carrier and no transport document');
    }

    private static function riskEnvironmental(?object $s): array
    {
        if (! $s) {
            return self::level('unassessed', 'nothing has been packed, so no handling conditions are known');
        }

        $protections = (int) (bool) $s->shock_protection
            + (int) (bool) $s->climate_protection
            + (int) (bool) $s->humidity_protection;

        return match (true) {
            $protections === 3 => self::level('low', 'the crate records shock, climate and humidity protection'),
            $protections > 0   => self::level('medium', "the crate records only {$protections} of three protective measures"),
            default            => self::level('high', 'the crate records no shock, climate or humidity protection'),
        };
    }

    private static function riskInsurance(int $productId): array
    {
        $cover = DB::table('ownership_transfers')
            ->where('product_id', $productId)
            ->whereNotNull('insurer_name')
            ->orderByDesc('id')
            ->first();

        if (! $cover) {
            // Explicitly not "high": the platform holds no cover details, which
            // does not mean none exists. Unassessed is the only true answer.
            return self::level('unassessed', 'no insurance cover has been declared to the platform');
        }

        $inForce = $cover->coverage_start && $cover->coverage_end
            && now()->betweenIncluded(Carbon::parse($cover->coverage_start), Carbon::parse($cover->coverage_end));

        if ($inForce && $cover->insurance_policy_no && $cover->insurance_value) {
            return self::level('low', 'a valued policy is in force for the shipping window');
        }

        if (! $inForce) {
            return self::level('high', 'the declared cover has no in-force window covering today');
        }

        return self::level('medium', 'cover is declared but incompletely specified');
    }

    /* ────────────────────────────── Internals ──────────────────────────── */

    private static function cat(int $score, int $max, string $basis): array
    {
        return ['score' => $score, 'max' => $max, 'basis' => $basis];
    }

    private static function level(string $level, string $basis): array
    {
        return ['level' => $level, 'basis' => $basis];
    }

    private static function require(int $id): object
    {
        $c = self::find($id);

        if (! $c) {
            throw new DomainException("No export consignment [{$id}].");
        }

        return $c;
    }

    /** The live Certificate of Authenticity, or null. Never issues one. */
    private static function coaFor(int $productId): ?object
    {
        return DB::table('product_certificates')
            ->where('product_id', $productId)
            ->whereNull('revoked_at')
            ->orderByDesc('version')
            ->first();
    }

    /** The latest booking for a consignment, or null when none exists. */
    private static function shipment(int $id): ?object
    {
        return DB::table('shipments')->where('export_consignment_id', $id)->orderByDesc('id')->first();
    }

    private static function assertTransition(object $c, string $move): void
    {
        if (! in_array($c->status, self::ALLOWED_FROM[$move], true)) {
            throw new DomainException(
                "Consignment {$c->certificate_no} cannot {$move} from status [{$c->status}]; "
                . 'permitted from [' . implode(', ', self::ALLOWED_FROM[$move]) . '].'
            );
        }
    }

    private static function move(object $c, string $status, string $event, ?string $note = null, ?string $actor = null): void
    {
        DB::table('export_consignments')->where('id', $c->id)->update([
            'status'     => $status,
            'updated_at' => now(),
        ]);

        CertificationAuthority::appendToChain(self::TYPE, (int) $c->id, $event, $note, $actor);
    }
}
