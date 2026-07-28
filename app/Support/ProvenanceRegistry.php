<?php

namespace App\Support;

use App\Modules\Businesses\Models\Business;
use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The registry behind the platform's certificates.
 *
 * Its job is to hand out identifiers that never change and to keep the
 * ownership chain honest. Everything a certificate prints about registration
 * or provenance is read from here, so that a document can never assert a fact
 * the register does not hold.
 *
 * What this deliberately does not do: pretend to be a legal title system. The
 * platform records what verified parties declare and timestamps it. That is a
 * real, useful thing — it is what provenance research actually runs on — but it
 * is not a government register, and the certificates say so.
 */
class ProvenanceRegistry
{
    /** ISO 3166-1 alpha-2 segment used in issued numbers. */
    public const COUNTRY = 'CM';

    /* ─────────────────────────── Permanent identifiers ─────────────────── */

    /**
     * The product's registry number, assigned once and never reissued.
     *
     * Distinct from the marketplace product id on purpose: the id identifies a
     * row a seller can edit, the PRN identifies the registration event.
     */
    public static function prnFor(Product $product): string
    {
        if ($product->prn) {
            return $product->prn;
        }

        $prn = sprintf(
            'AH237-PRN-%s-%s-%s',
            self::COUNTRY,
            ($product->registered_at ?? $product->created_at ?? now())->format('Y'),
            str_pad((string) $product->id, 12, '0', STR_PAD_LEFT)
        );

        DB::table('products')->where('id', $product->id)->update([
            'prn'           => $prn,
            'registered_at' => $product->registered_at ?? $product->created_at ?? now(),
        ]);
        $product->prn = $prn;

        return $prn;
    }

    /**
     * The ownership ledger number: one per product, quoted by every transfer
     * certificate ever issued for it. This is what lets a holder of a single
     * certificate reconstruct the whole chain without holding the others.
     */
    public static function olnFor(Product $product): string
    {
        if ($product->oln) {
            return $product->oln;
        }

        $oln = 'AH237-OLN-' . str_pad((string) $product->id, 10, '0', STR_PAD_LEFT);

        DB::table('products')->where('id', $product->id)->update(['oln' => $oln]);
        $product->oln = $oln;

        return $oln;
    }

    /** The artisan's lifelong number, surviving renames and profile claims. */
    public static function ganFor(Business $business): string
    {
        if ($business->gan) {
            return $business->gan;
        }

        $gan = sprintf('AH237-GAN-%s-%s', self::COUNTRY, str_pad((string) $business->id, 10, '0', STR_PAD_LEFT));

        DB::table('businesses')->where('id', $business->id)->update(['gan' => $gan]);
        $business->gan = $gan;

        return $gan;
    }

    /* ─────────────────────────────── Ownership ─────────────────────────── */

    /**
     * The open ownership row, creating the artisan's founding entry on first
     * call. Provenance starts at the maker, so the chain is never empty for a
     * product that has a maker.
     */
    public static function currentOwner(Product $product): ?object
    {
        $open = DB::table('product_ownerships')
            ->where('product_id', $product->id)
            ->whereNull('owned_until')
            ->orderByDesc('sequence')
            ->first();

        if ($open) {
            return $open;
        }

        return $product->business_id ? self::seedOriginalOwnership($product) : null;
    }

    /** Records the maker as first owner, from the product's registration date. */
    public static function seedOriginalOwnership(Product $product): ?object
    {
        $product->loadMissing('business.user', 'business.region');
        $business = $product->business;

        if (! $business) {
            return null;
        }

        $id = DB::table('product_ownerships')->insertGetId([
            'uuid'                => (string) Str::uuid(),
            'product_id'          => $product->id,
            'sequence'            => 1,
            'owner_user_id'       => $business->user_id,
            'owner_ref'           => 'OWN-AH237-' . str_pad((string) $business->id, 8, '0', STR_PAD_LEFT),
            'legal_name'          => $business->user?->name ?: $business->name_fr,
            'entity_type'         => 'individual',
            'country_code'        => self::COUNTRY,
            'address'             => $business->address_fr,
            // The maker's standing is the business's own verification tier —
            // never asserted independently of it.
            'verification_level'  => in_array($business->verification_tier, ['verified', 'certified'], true)
                ? 'verified' : 'declared',
            'is_original_creator' => true,
            'owned_from'          => $product->created_at ?? now(),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return DB::table('product_ownerships')->find($id);
    }

    /** The full chain, oldest first. */
    public static function chain(Product $product): array
    {
        self::currentOwner($product);

        return DB::table('product_ownerships')
            ->where('product_id', $product->id)
            ->orderBy('sequence')
            ->get()->all();
    }

    /* ───────────────────────────── Transfers ───────────────────────────── */

    /**
     * Records a transfer of ownership and issues its certificate.
     *
     * The previous holder's row is closed at the transfer moment and a new one
     * opened, so the chain has no gap and no overlap. Both halves happen in one
     * transaction: a half-applied transfer would leave a product with two
     * current owners, which is the one state a provenance register must never
     * be able to reach.
     *
     * @param array{legal_name:string, entity_type?:string, country_code?:string,
     *              address?:string, owner_user_id?:string, verification_level?:string} $to
     */
    public static function transfer(Product $product, array $to, array $details = []): object
    {
        return DB::transaction(function () use ($product, $to, $details) {
            // Assigned before the certificate is signed, because the signature
            // covers the ledger number.
            self::olnFor($product);

            $from = self::currentOwner($product);
            $at   = isset($details['transferred_at'])
                ? \Illuminate\Support\Carbon::parse($details['transferred_at'])
                : now();

            $sequence = (int) DB::table('product_ownerships')->where('product_id', $product->id)->max('sequence');

            $toId = DB::table('product_ownerships')->insertGetId([
                'uuid'                => (string) Str::uuid(),
                'product_id'          => $product->id,
                'sequence'            => $sequence + 1,
                'owner_user_id'       => $to['owner_user_id'] ?? null,
                'owner_ref'           => 'OWN-AH237-' . str_pad((string) ($sequence + 1), 8, '0', STR_PAD_LEFT),
                'legal_name'          => $to['legal_name'],
                'entity_type'         => $to['entity_type'] ?? 'individual',
                'country_code'        => $to['country_code'] ?? null,
                'address'             => $to['address'] ?? null,
                'verification_level'  => $to['verification_level'] ?? 'declared',
                'is_original_creator' => false,
                'owned_from'          => $at,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            if ($from) {
                DB::table('product_ownerships')->where('id', $from->id)->update([
                    'owned_until' => $at,
                    'updated_at'  => now(),
                ]);
            }

            // Earlier certificates describe an ownership that has now moved on.
            DB::table('ownership_transfers')
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->update(['status' => 'superseded', 'updated_at' => now()]);

            $seq  = DB::table('ownership_transfers')->count() + 1;
            $no   = sprintf('AH237-OTC-%s-%s-%s', self::COUNTRY, $at->format('Y'), str_pad((string) $seq, 12, '0', STR_PAD_LEFT));
            $uuid = (string) Str::uuid();

            $row = [
                'uuid'              => $uuid,
                'certificate_no'    => $no,
                'version'           => 1,
                'product_id'        => $product->id,
                'from_ownership_id' => $from?->id,
                'to_ownership_id'   => $toId,
                'transfer_type'     => $details['transfer_type'] ?? 'sale',
                'transferred_at'    => $at,
                'transfer_city'     => $details['transfer_city'] ?? null,
                'transfer_country'  => $details['transfer_country'] ?? null,
                'transaction_ref'   => $details['transaction_ref'] ?? null,
                'invoice_no'        => $details['invoice_no'] ?? null,
                'payment_ref'       => $details['payment_ref'] ?? null,
                'currency'          => $details['currency'] ?? null,
                'declared_value'    => $details['declared_value'] ?? null,
                'value_is_private'  => (bool) ($details['value_is_private'] ?? false),
                'condition'         => $details['condition'] ?? null,
                'condition_notes'   => $details['condition_notes'] ?? null,
                'accessories'       => $details['accessories'] ?? null,
                'status'            => 'active',
                'verification_pin'  => strtoupper(Str::password(8, true, true, false, false)),
                'issued_at'         => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            $row['content_hash'] = self::transferHash($product, $row);
            $row['signature']    = ProductCertificate::signatureFor($no, $row['content_hash'], $product->oln);

            [$row['ca_signature'], $row['ca_kid']] = CertificationAuthority::signCertificate(
                'otc', $no, $row['content_hash'], $row['issued_at']->toIso8601String()
            );

            $id = DB::table('ownership_transfers')->insertGetId($row);

            self::event('otc', $id, 'issued');

            return DB::table('ownership_transfers')->find($id);
        });
    }

    /** Hash over what the transfer certificate asserts. */
    public static function transferHash(Product $product, array $row): string
    {
        $to   = $row['to_ownership_id'] ? DB::table('product_ownerships')->find($row['to_ownership_id']) : null;
        $from = $row['from_ownership_id'] ? DB::table('product_ownerships')->find($row['from_ownership_id']) : null;

        return hash('sha256', json_encode([
            'product_uuid'   => $product->uuid,
            'oln'            => $product->oln,
            'certificate_no' => $row['certificate_no'],
            'transfer_type'  => $row['transfer_type'],
            'transferred_at' => (string) $row['transferred_at'],
            'from'           => $from?->legal_name,
            'to'             => $to?->legal_name,
            'to_entity'      => $to?->entity_type,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /* ───────────────────────────── Audit trail ─────────────────────────── */

    /**
     * Appends a lifecycle event. Every line a certificate prints on its audit
     * trail is one of these rows, so the trail cannot show a step that never
     * happened.
     */
    public static function event(string $type, int $id, string $event, ?string $note = null, ?string $actor = null): void
    {
        // Goes through the authority so every event joins the hash chain; an
        // audit trail that can be edited without trace is not an audit trail.
        CertificationAuthority::appendToChain($type, $id, $event, $note, $actor);
    }

    /** @return array<int,object> */
    public static function trail(string $type, int $id): array
    {
        return DB::table('certificate_events')
            ->where('certificate_type', $type)
            ->where('certificate_id', $id)
            ->orderBy('occurred_at')
            ->get()->all();
    }
}
