<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Issues and reads the Artisan Verification Certificate.
 *
 * The document asserts one thing: that on a given date the platform held this
 * much evidence about this artisan, itemised. Not that they are a good maker,
 * not that their work is authentic — that is the product certificate's job —
 * only what was on file and who had looked at it.
 *
 * Which is why the level is derived here and nowhere else. A seven-rung ladder
 * from "identity" to "nationally recognised" is exactly the kind of thing that
 * drifts into flattery: the temptation is to hand a paying shop a "master
 * artisan" badge because it looks good on the profile. Every rung below is
 * gated on a row somebody can point at, and the two top rungs are not
 * derivable at all — see levelFor().
 *
 * The same rule governs checks: a check is in the map only if the platform
 * actually performs it. Emitting `false` for a check we never attempt would
 * read as "we looked and they failed", which is a slander we have no evidence
 * for; omitting it reads as "not assessed", which is the truth.
 */
class ArtisanVerification
{
    /** Certificates are re-attested annually, so an old one cannot sit unexamined forever. */
    private const VALID_MONTHS = 12;

    /* ──────────────────────────── The ladder ───────────────────────────── */

    /**
     * The verification level, 0 to 5, derived only from evidence in the DB.
     *
     * The rungs, each requiring everything below it:
     *
     *  0 nothing on file that a reviewer has approved. No certificate.
     *  1 identity      — a reviewer confirmed who this is: id_verified_at set,
     *                    or the shop carries a verified/certified tier, which
     *                    on this platform is only ever set by a reviewer.
     *  2 professional  — trade paperwork was filed and accepted: an approved
     *                    verification_application with at least one accepted
     *                    document, or an accepted document on its own.
     *  3 workshop      — a place of work is on record (address, or GPS).
     *  4 certified     — verification_tier is 'certified', the highest standing
     *                    the platform's own review awards.
     *  5 master        — a third party certified them: a business_certification
     *                    row in 'verified' status. Somebody other than us said
     *                    it, which is the whole difference between rungs 4 and 5.
     *
     * 6 (heritage master) and 7 (nationally recognised) are deliberately
     * unreachable by derivation. Both are designations conferred by bodies
     * outside this platform — a ministry, a UNESCO living-heritage listing —
     * and we hold no register of either. Awarding them from anything we can see
     * in this database would be inventing a national honour, so they can only
     * ever arrive as a hand-entered, sourced record. Until such a register
     * exists, the ladder tops out at 5 and the certificate says what it means.
     */
    public static function levelFor(Business $business): int
    {
        if (! self::identityEstablished($business)) {
            return 0;
        }

        $level = 1;

        if (self::hasAcceptedTradeDocuments($business)) {
            $level = 2;
        } else {
            return $level;
        }

        if (self::hasWorkshopOnRecord($business)) {
            $level = 3;
        } else {
            return $level;
        }

        if ($business->verification_tier === 'certified') {
            $level = 4;
        } else {
            return $level;
        }

        if (self::hasThirdPartyCertification($business)) {
            $level = 5;
        }

        return $level;
    }

    /* ──────────────────────────── The checks ───────────────────────────── */

    /**
     * What the platform genuinely checked, as check => outcome.
     *
     * Everything in this map is something the platform performs, so a `false`
     * here means "attempted or expected, and not satisfied" — a shop with no
     * address really has no address on record. Anything we never attempt is
     * absent: tax compliance, criminal record, physical site visit, quality
     * inspection of the work, export licensing, apprenticeship lineage. Those
     * belong to institutions we are not, and a certificate carrying an unticked
     * "criminal record" box would imply we ran one and did not like the answer.
     *
     * @return array<string,bool>
     */
    public static function checksFor(Business $business): array
    {
        $business->loadMissing('user');

        $checks = [
            // Set only by a reviewer looking at the document; a document merely
            // uploaded verifies nothing, so filing one does not flip this.
            'identity_document_verified' => (bool) $business->id_verified_at,
            'workshop_address_on_record' => filled($business->address_fr) || filled($business->address_en)
                                            || ($business->gps_lat !== null && $business->gps_lng !== null),
            'trade_documents_accepted'   => self::hasAcceptedTradeDocuments($business),
            'application_reviewed'       => DB::table('verification_applications')
                                                ->where('business_id', $business->id)
                                                ->where('status', 'approved')->exists(),
            'portfolio_published'        => DB::table('products')
                                                ->where('business_id', $business->id)
                                                ->where('status', 'published')->exists(),
            'third_party_certification'  => self::hasThirdPartyCertification($business),
        ];

        // Contact verification lives on the account, so it can only be reported
        // for a shop that has one. An imported SIARC profile nobody has claimed
        // yet has no account, and reporting "email not verified" for a person
        // who was never asked would be a fabricated failure.
        if ($business->user) {
            $checks['email_verified'] = (bool) $business->user->is_email_verified;
            $checks['phone_verified'] = (bool) $business->user->is_phone_verified;
        }

        return $checks;
    }

    /**
     * Counts, read off the database at issue time.
     *
     * Each key is omitted when its source is null rather than defaulted to
     * zero: "0 years active" is a claim about a shop that simply never told us
     * when it started, and the certificate would print it as fact.
     *
     * @return array<string,int|float>
     */
    public static function metricsFor(Business $business): array
    {
        $metrics = [
            'products_registered' => DB::table('products')->where('business_id', $business->id)->count(),
            'published_products'  => DB::table('products')->where('business_id', $business->id)
                                        ->where('status', 'published')->count(),
        ];

        if ($business->year_established) {
            $metrics['years_active'] = max(0, (int) now()->year - (int) $business->year_established);
        }

        if ($business->created_at) {
            $metrics['member_since'] = (int) Carbon::parse($business->created_at)->year;
        }

        // Self-reported, and labelled as such wherever it renders.
        if ($business->response_time_hours !== null) {
            $metrics['response_time_hours'] = (int) $business->response_time_hours;
        }

        // Reviews exist as a table, so they can be counted honestly — but only
        // when there are some. An "average rating" over zero reviews is not 0,
        // it is nothing, and a certificate must not print a number for it.
        $reviews = DB::table('business_reviews')
            ->where('business_id', $business->id)->where('status', 'published');

        if (($count = (clone $reviews)->count()) > 0) {
            $metrics['reviews_published'] = $count;
            $metrics['average_rating']    = round((float) (clone $reviews)->avg('rating'), 2);
        }

        // Deliberately absent: followers, sales volume, repeat-buyer rate. No
        // table on this platform holds any of them.

        return $metrics;
    }

    /* ────────────────────────────── Issuing ────────────────────────────── */

    /**
     * The live certificate, issuing one on first request.
     *
     * Mirrors ProductCertificate::forProduct — a draft product gets no COA, and
     * an artisan with nothing verified gets no AVC. The alternative is a
     * numbered document attesting to zero evidence, which is precisely the
     * thing this register was built to stop.
     */
    public static function forBusiness(Business $business): ?object
    {
        if (self::levelFor($business) < 1) {
            return null;
        }

        $existing = DB::table('artisan_verifications')
            ->where('business_id', $business->id)
            ->where('status', 'active')
            ->orderByDesc('version')
            ->first();

        return $existing ?: self::issue($business);
    }

    public static function issue(Business $business, int $version = 1): object
    {
        return DB::transaction(function () use ($business, $version) {
            // Assigned before the certificate is signed, because the artisan's
            // permanent number is one of the facts the hash covers.
            $gan = ProvenanceRegistry::ganFor($business);

            $issuedAt = now();
            $certificateNo = sprintf(
                'AH237-AVC-%s-%s-%s',
                ProvenanceRegistry::COUNTRY,
                $issuedAt->format('Y'),
                str_pad((string) $business->id, 10, '0', STR_PAD_LEFT)
            ) . ($version > 1 ? "-V{$version}" : '');

            $level   = self::levelFor($business);
            $checks  = self::checksFor($business);
            $metrics = self::metricsFor($business);

            $contentHash = self::hashFor($business, $certificateNo, $level, $checks);

            [$caSignature, $caKid] = CertificationAuthority::signCertificate(
                'avc', $certificateNo, $contentHash, $issuedAt->toIso8601String()
            );

            $id = DB::table('artisan_verifications')->insertGetId([
                'uuid'                        => (string) Str::uuid(),
                'certificate_no'              => $certificateNo,
                'version'                     => $version,
                'business_id'                 => $business->id,
                'verification_application_id' => DB::table('verification_applications')
                                                    ->where('business_id', $business->id)
                                                    ->where('status', 'approved')
                                                    ->orderByDesc('reviewed_at')->value('id'),
                'level'                       => $level,
                'checks'                      => json_encode($checks, JSON_UNESCAPED_UNICODE),
                'metrics'                     => json_encode($metrics, JSON_UNESCAPED_UNICODE),
                'status'                      => 'active',
                'content_hash'                => $contentHash,
                'signature'                   => ProductCertificate::signatureFor($certificateNo, $contentHash, $gan),
                'ca_signature'                => $caSignature,
                'ca_kid'                      => $caKid,
                // Ambiguous characters left out so a PIN read aloud off a
                // printout does not fail verification.
                'verification_pin'            => strtoupper(Str::password(8, true, true, false, false)),
                'issued_at'                   => $issuedAt,
                'expires_at'                  => $issuedAt->copy()->addMonths(self::VALID_MONTHS),
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ]);

            CertificationAuthority::appendToChain('avc', $id, 'issued');

            return DB::table('artisan_verifications')->find($id);
        });
    }

    /**
     * SHA-256 over the certified facts, canonically ordered.
     *
     * The metrics are outside the hash on purpose. They move every time the
     * artisan publishes a piece, and a certificate that reported itself
     * superseded each week would train everyone to ignore the word. What the
     * document actually certifies — who this is, and what was verified about
     * them — is what is bound.
     */
    public static function hashFor(Business $business, string $certificateNo, int $level, array $checks): string
    {
        ksort($checks);

        return hash('sha256', json_encode([
            'business_uuid'  => $business->uuid,
            'gan'            => $business->gan,
            'legal_name'     => $business->name_fr,
            'certificate_no' => $certificateNo,
            'level'          => $level,
            'checks'         => $checks,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /* ───────────────────────────── Verifying ───────────────────────────── */

    /**
     * Public lookup, returning a status a visitor can act on.
     *
     * @return array{status:string, certificate:?object, business:?Business, signature?:array}
     */
    public static function verify(string $ref, ?string $pin = null): array
    {
        $ref = trim($ref);

        $cert = DB::table('artisan_verifications')
            ->where('certificate_no', $ref)
            ->orWhere('uuid', $ref)
            ->first();

        if (! $cert) {
            return ['status' => 'notfound', 'certificate' => null, 'business' => null];
        }

        // The PIN is demanded only when one is supplied, so a QR scan (which
        // carries it) and a typed number both work.
        if ($pin !== null && strtoupper(trim($pin)) !== strtoupper((string) $cert->verification_pin)) {
            return ['status' => 'pin_mismatch', 'certificate' => null, 'business' => null];
        }

        DB::table('artisan_verifications')->where('id', $cert->id)->update([
            'verification_count' => $cert->verification_count + 1,
            'last_verified_at'   => now(),
        ]);

        $business = Business::find($cert->business_id);

        if (! $business || $cert->status === 'revoked') {
            return ['status' => 'revoked', 'certificate' => $cert, 'business' => $business];
        }

        $current = self::hashFor(
            $business, $cert->certificate_no, self::levelFor($business), self::checksFor($business)
        );

        // The evidence has moved since issue — usually a new document accepted,
        // sometimes a check that no longer holds. Not fraud, but the visitor is
        // told rather than shown a green tick over a stale statement.
        if ($current !== $cert->content_hash) {
            return ['status' => 'superseded', 'certificate' => $cert, 'business' => $business,
                    'signature' => self::signatureState($cert)];
        }

        return ['status' => 'valid', 'certificate' => $cert, 'business' => $business,
                'signature' => self::signatureState($cert)];
    }

    /**
     * Whether the authority's signature over the stored row still verifies.
     * Reported apart from the status: "superseded" means the record moved on,
     * a bad signature would mean the row itself was tampered with.
     *
     * @return array{state:string, kid:?string}
     */
    public static function signatureState(object $cert): array
    {
        if (! $cert->ca_signature) {
            return ['state' => 'unsigned', 'kid' => null];
        }

        $ok = CertificationAuthority::verifyCertificate(
            'avc', $cert->certificate_no, $cert->content_hash,
            Carbon::parse($cert->issued_at)->toIso8601String(), $cert->ca_signature
        );

        return ['state' => $ok ? 'valid' : 'invalid', 'kid' => $cert->ca_kid];
    }

    /* ──────────────────────── Identity documents ───────────────────────── */

    /**
     * Files an identity document number.
     *
     * Encrypted with the application key, and the last four kept in clear
     * because that is all any screen needs to confirm "yes, that is the card I
     * showed you". Nothing on the platform needs the full number after a
     * reviewer has looked at it once, so it is never held in a form a database
     * dump would expose.
     *
     * Filing is not verifying: id_verified_at is untouched here, and only a
     * reviewer's own act sets it. Otherwise uploading a photograph of anything
     * would tick "identity verified".
     */
    public static function recordIdentityDocument(Business $business, string $type, string $number): void
    {
        $number = trim($number);

        DB::table('businesses')->where('id', $business->id)->update([
            'id_document_type'      => $type,
            'id_document_encrypted' => Crypt::encryptString($number),
            'id_document_last4'     => substr($number, -4),
            'updated_at'            => now(),
        ]);
    }

    /** Marks the filed document as checked by a named reviewer. */
    public static function verifyIdentityDocument(Business $business, string $reviewerUserId): void
    {
        // Nothing on file is nothing to verify; the tick must trace to a document.
        if (! DB::table('businesses')->where('id', $business->id)->value('id_document_encrypted')) {
            throw new \RuntimeException('No identity document is on file for this business.');
        }

        DB::table('businesses')->where('id', $business->id)->update([
            'id_verified_at' => now(),
            'id_verified_by' => $reviewerUserId,
            'updated_at'     => now(),
        ]);
    }

    /** The only form of the number that may ever be displayed. */
    public static function maskedIdentityDocument(Business $business): ?string
    {
        return $business->id_document_last4 ? '•••• ' . $business->id_document_last4 : null;
    }

    /* ────────────────────────────── Evidence ───────────────────────────── */

    /** Rung 1: somebody with authority confirmed who this is. */
    private static function identityEstablished(Business $business): bool
    {
        return (bool) $business->id_verified_at
            || in_array($business->verification_tier, ['verified', 'certified'], true);
    }

    /** Rung 2: trade paperwork a reviewer accepted. */
    private static function hasAcceptedTradeDocuments(Business $business): bool
    {
        return DB::table('verification_documents')
            ->join('verification_applications', 'verification_applications.id', '=', 'verification_documents.application_id')
            ->where('verification_applications.business_id', $business->id)
            ->where('verification_documents.status', 'accepted')
            ->exists();
    }

    /** Rung 3: a place of work, however coarsely recorded. */
    private static function hasWorkshopOnRecord(Business $business): bool
    {
        return filled($business->address_fr) || filled($business->address_en)
            || ($business->gps_lat !== null && $business->gps_lng !== null);
    }

    /** Rung 5: an accreditation from somebody who is not us. */
    private static function hasThirdPartyCertification(Business $business): bool
    {
        return DB::table('business_certifications')
            ->where('business_id', $business->id)
            ->where('status', 'verified')
            ->exists();
    }
}
