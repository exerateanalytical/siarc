<?php

namespace App\Support;

use App\Modules\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Issues and verifies product Certificates of Authenticity.
 *
 * What a certificate here actually asserts, and nothing more: that on a given
 * date this product was registered on the platform by this artisan, with these
 * details and these photographs. That is a real, checkable claim — a
 * time-stamped record of authorship — and it is what the legal text on the page
 * says.
 *
 * What it deliberately does NOT assert: that the object in a buyer's hands is
 * the one in the photographs. Proving that needs a physical link — an AI visual
 * fingerprint, an invisible watermark, an NFC tag — and the platform has none
 * of those. Printing "AI Visual Fingerprint" with an empty value beside it
 * would be a claim we cannot keep, which is the whole failure mode this page
 * exists to avoid.
 */
class ProductCertificate
{
    /** Facts covered by the hash. A change to any of these supersedes the certificate. */
    private static function certifiedFacts(Product $product): array
    {
        $product->loadMissing(['business', 'category', 'images', 'attributes.template']);

        return [
            'product_uuid'  => $product->uuid,
            'sku'           => $product->sku,
            'name_fr'       => $product->name_fr,
            'name_en'       => $product->name_en,
            'description'   => $product->description_fr,
            'category'      => $product->category?->name_fr,
            'business_uuid' => $product->business?->uuid,
            'business_name' => $product->business?->name_fr,
            'region'        => $product->business?->region?->name_fr,
            'attributes'    => $product->attributes
                ->map(fn ($a) => [$a->template?->name_fr, $a->value_fr])
                ->sortBy(0)->values()->all(),
            'images'        => $product->images->sortBy('sort_order')->pluck('file_path')->values()->all(),
        ];
    }

    /** SHA-256 over the certified facts, so a later edit is detectable. */
    public static function hashFor(Product $product): string
    {
        return hash('sha256', json_encode(self::certifiedFacts($product), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    /**
     * The live certificate for a product, issuing one on first request.
     *
     * Issued lazily rather than on product creation: a certificate for a
     * half-finished draft would be superseded within minutes, and the number
     * sequence would fill with documents nobody ever saw.
     */
    public static function forProduct(Product $product): ?object
    {
        if (! $product->business_id || $product->status !== 'published') {
            // A draft is not registered yet; there is nothing to certify.
            return null;
        }

        $existing = DB::table('product_certificates')
            ->where('product_id', $product->id)
            ->whereNull('revoked_at')
            ->orderByDesc('version')
            ->first();

        if ($existing) {
            return $existing;
        }

        return self::issue($product);
    }

    public static function issue(Product $product, int $version = 1): object
    {
        $year = now()->format('Y');
        $seq  = str_pad((string) $product->id, 9, '0', STR_PAD_LEFT);

        $id = DB::table('product_certificates')->insertGetId([
            'uuid'             => (string) Str::uuid(),
            'certificate_no'   => "AHC-COA-{$year}-{$seq}" . ($version > 1 ? "-V{$version}" : ''),
            'version'          => $version,
            'product_id'       => $product->id,
            'business_id'      => $product->business_id,
            // Ambiguous characters are left out so a PIN read aloud or copied
            // off a printout does not fail verification.
            'verification_pin' => strtoupper(Str::password(8, true, true, false, false)),
            'content_hash'     => self::hashFor($product),
            'issued_at'        => now(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return DB::table('product_certificates')->find($id);
    }

    /**
     * Look a certificate up for public verification.
     *
     * Returns a status a visitor can act on rather than a bare true/false —
     * "this product changed after the certificate was issued" is a different
     * situation from "this certificate does not exist", and a buyer standing in
     * a market needs to be able to tell them apart.
     *
     * @return array{status:string, certificate:?object, product:?Product}
     */
    public static function verify(string $reference, ?string $pin = null): array
    {
        $reference = trim($reference);

        $cert = DB::table('product_certificates')
            ->where('certificate_no', $reference)
            ->orWhere('uuid', $reference)
            ->first();

        if (! $cert) {
            return ['status' => 'notfound', 'certificate' => null, 'product' => null];
        }

        // The PIN is only demanded when one is supplied, so a QR scan (which
        // carries it) and a typed certificate number both work.
        if ($pin !== null && strtoupper(trim($pin)) !== strtoupper($cert->verification_pin)) {
            return ['status' => 'pin_mismatch', 'certificate' => null, 'product' => null];
        }

        $product = Product::with(['business.region', 'category', 'images', 'attributes.template'])
            ->find($cert->product_id);

        if (! $product) {
            return ['status' => 'revoked', 'certificate' => $cert, 'product' => null];
        }

        DB::table('product_certificates')->where('id', $cert->id)->update([
            'verification_count' => $cert->verification_count + 1,
            'last_verified_at'   => now(),
        ]);

        if ($cert->revoked_at) {
            return ['status' => 'revoked', 'certificate' => $cert, 'product' => $product];
        }

        // The record has moved on since issue. Not fraud — usually an edit — but
        // the buyer should be told rather than shown a green tick.
        if (self::hashFor($product) !== $cert->content_hash) {
            return ['status' => 'superseded', 'certificate' => $cert, 'product' => $product];
        }

        return ['status' => 'valid', 'certificate' => $cert, 'product' => $product];
    }
}
