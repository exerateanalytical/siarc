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
     * A 64-bit difference hash of the product's cover photograph.
     *
     * Real pixels, real arithmetic: the image is reduced to 9×8 greyscale and
     * each pixel compared with its right-hand neighbour, giving 64 bits that
     * survive rescaling and recompression but change when the picture changes.
     *
     * This is what lets verification say "the photograph on this listing is not
     * the one that was certified". The content hash alone would miss a swap
     * that reused the same file path, and would cry foul over a re-encode that
     * left the picture identical.
     *
     * Returns null when there is no cover image or the file cannot be read —
     * never a placeholder, because a fabricated fingerprint is worse than none.
     */
    public static function perceptualHash(Product $product): ?string
    {
        $product->loadMissing('images');

        $cover = $product->images->firstWhere('is_cover', true)
            ?? $product->images->sortBy('sort_order')->first();

        if (! $cover?->file_path || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $path = storage_path('app/public/' . ltrim($cover->file_path, '/'));

        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        $src = $raw === false ? false : @imagecreatefromstring($raw);

        if (! $src) {
            return null;
        }

        $small = imagecreatetruecolor(9, 8);
        imagecopyresampled($small, $src, 0, 0, 0, 0, 9, 8, imagesx($src), imagesy($src));
        imagedestroy($src);

        $bits = '';

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= self::luma($small, $x, $y) > self::luma($small, $x + 1, $y) ? '1' : '0';
            }
        }

        imagedestroy($small);

        // 64 bits → 16 hex characters, in 8-bit chunks so the value is stable
        // across platforms regardless of integer width.
        $hex = '';
        foreach (str_split($bits, 8) as $byte) {
            $hex .= str_pad(dechex((int) bindec($byte)), 2, '0', STR_PAD_LEFT);
        }

        return $hex;
    }

    /** Rec. 601 luma of one pixel, used by the difference hash. */
    private static function luma(\GdImage $img, int $x, int $y): float
    {
        $rgb = imagecolorat($img, $x, $y);

        return 0.299 * (($rgb >> 16) & 0xFF)
             + 0.587 * (($rgb >> 8) & 0xFF)
             + 0.114 * ($rgb & 0xFF);
    }

    /**
     * Keyed signature over what the certificate asserts.
     *
     * Anyone can copy the visible fields off a certificate page; only the
     * platform holds APP_KEY, so only the platform can produce a signature that
     * recomputes. This is what "digitally signed" means here — an HMAC the
     * server can re-derive — and nothing more. It is not a PKI signature a third
     * party could check without us, and the page does not claim it is.
     */
    public static function signatureFor(string $certificateNo, string $contentHash, ?string $imagePhash = null): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [$certificateNo, $contentHash, $imagePhash ?? '']),
            (string) config('app.key')
        );
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

        $certificateNo = "AHC-COA-{$year}-{$seq}" . ($version > 1 ? "-V{$version}" : '');
        $contentHash   = self::hashFor($product);
        $imagePhash    = self::perceptualHash($product);

        $id = DB::table('product_certificates')->insertGetId([
            'uuid'             => (string) Str::uuid(),
            'certificate_no'   => $certificateNo,
            'version'          => $version,
            'product_id'       => $product->id,
            'business_id'      => $product->business_id,
            // Ambiguous characters are left out so a PIN read aloud or copied
            // off a printout does not fail verification.
            'verification_pin' => strtoupper(Str::password(8, true, true, false, false)),
            'content_hash'     => $contentHash,
            'image_phash'      => $imagePhash,
            'signature'        => self::signatureFor($certificateNo, $contentHash, $imagePhash),
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

        // A photograph replaced in place keeps the same file path, so the
        // content hash above would not notice. The perceptual hash does. Only
        // compared when one was recorded at issue and one can be computed now —
        // an unreadable file must not be reported to a buyer as a mismatch.
        if ($cert->image_phash) {
            $current = self::perceptualHash($product);

            if ($current !== null && $current !== $cert->image_phash) {
                return ['status' => 'superseded', 'certificate' => $cert, 'product' => $product];
            }
        }

        return ['status' => 'valid', 'certificate' => $cert, 'product' => $product];
    }
}
