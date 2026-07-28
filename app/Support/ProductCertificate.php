<?php

namespace App\Support;

use App\Modules\Products\Models\Product;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Cache;
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
 * the one in the photographs. The platform can tie a certificate to a
 * photograph — see perceptualHash() and ImageFingerprint — but nothing ties a
 * photograph to the wood in front of you. That needs a physical mark: an NFC
 * tag, an embedded chip. Until one exists the certificate says so plainly,
 * because a document that overstates its reach is worth less than one that
 * states its limit.
 *
 * Every certificate carries two signatures. The HMAC is ours to re-derive and
 * catches our own corruption; the Ed25519 signature from CertificationAuthority
 * is what a museum or insurer can check against the published public key
 * without asking us anything.
 */
class ProductCertificate
{
    /**
     * Number of times a cover photograph has actually been decoded in this
     * process. Public so a test can assert the decode happens once per product
     * per request rather than once per caller; nothing in the application reads
     * it, and it is never persisted.
     */
    public static int $phashComputations = 0;

    /** Per-process memo of perceptual hashes, keyed on path, size and mtime. */
    private static array $phashMemo = [];

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
     *
     * The result is memoised twice over, because the arithmetic is the single
     * most expensive thing a certificate page does: decoding and resampling a
     * phone-sized photograph costs roughly half a second, and issuance, then
     * verification, then the page itself all ask for the same value.
     *
     * The two layers answer different questions. The per-process memo is keyed
     * on the file's path, size and modification time, and only saves re-reading
     * a file that has not moved during one request. The cross-request cache is
     * keyed on a digest of the file's actual bytes, so it can never go stale:
     * a photograph swapped in place — the exact case this hash exists to catch
     * — produces different bytes and therefore a different key, never a cached
     * answer describing the old picture. Reading and digesting the file costs a
     * few milliseconds against the hundreds the decode costs, which is what
     * makes content-addressing affordable rather than merely correct.
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

        $stamp = $path . '|' . @filesize($path) . '|' . @filemtime($path);

        if (array_key_exists($stamp, self::$phashMemo)) {
            return self::$phashMemo[$stamp];
        }

        $raw = @file_get_contents($path);

        if ($raw === false) {
            return self::$phashMemo[$stamp] = null;
        }

        // Content-addressed, so the cached value can only ever belong to these
        // exact bytes. xxh128 is not a security hash and is not used as one —
        // it is a cache key over a file we just read ourselves.
        $key    = 'coa:phash:' . hash('xxh128', $raw);
        $cached = Cache::get($key);

        if ($cached !== null) {
            return self::$phashMemo[$stamp] = ($cached === '' ? null : $cached);
        }

        $src = @imagecreatefromstring($raw);

        if (! $src) {
            // Cached as "no fingerprint" too: an undecodable file is a stable
            // fact about those bytes, and re-attempting it on every view of a
            // corrupt upload is the same waste in a slower form.
            Cache::put($key, '', now()->addDay());

            return self::$phashMemo[$stamp] = null;
        }

        self::$phashComputations++;

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

        Cache::put($key, $hex, now()->addDay());

        return self::$phashMemo[$stamp] = $hex;
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
     * recomputes. This is the internal integrity check. The externally
     * verifiable one is the authority's Ed25519 signature stored beside it.
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
     *
     * Two visitors opening the same never-before-seen product at the same
     * instant both find nothing and both proceed to issue. That race was real:
     * the lookup and the insert had nothing between them. It is closed here in
     * two places at once, on purpose. A short lock means the ordinary case
     * costs one issuance rather than two; the unique index on (product_id,
     * version) means that even if the lock is unavailable — a cache outage, a
     * second application server — the database itself refuses the duplicate,
     * and the loser of the race re-reads the winner's certificate instead of
     * returning a 500 to a buyer standing in a market.
     */
    public static function forProduct(Product $product): ?object
    {
        if (! $product->business_id || $product->status !== 'published') {
            // A draft is not registered yet; there is nothing to certify.
            return null;
        }

        if ($existing = self::liveCertificate($product)) {
            return $existing;
        }

        $lock = Cache::lock('coa:issue:' . $product->id, 15);

        try {
            // Waiting rather than failing: the wait is bounded by how long an
            // issuance takes, and the alternative is showing a visitor nothing.
            $lock->block(10);
        } catch (\Throwable) {
            // The lock is an optimisation, not the guarantee. Push on and let
            // the unique index below be the thing that cannot be raced.
            $lock = null;
        }

        try {
            if ($existing = self::liveCertificate($product)) {
                return $existing;
            }

            return self::issue($product);
        } catch (UniqueConstraintViolationException $e) {
            // Someone else got there between our check and our insert. Their
            // certificate is as good as the one we were about to write.
            if ($theirs = self::liveCertificate($product)) {
                return $theirs;
            }

            throw $e;
        } finally {
            $lock?->release();
        }
    }

    /** The current, unrevoked certificate for a product, if one has been issued. */
    private static function liveCertificate(Product $product): ?object
    {
        return DB::table('product_certificates')
            ->where('product_id', $product->id)
            ->whereNull('revoked_at')
            ->orderByDesc('version')
            ->first();
    }

    public static function issue(Product $product, int $version = 1): object
    {
        $year = now()->format('Y');
        $seq  = str_pad((string) $product->id, 9, '0', STR_PAD_LEFT);

        $certificateNo = "AHC-COA-{$year}-{$seq}" . ($version > 1 ? "-V{$version}" : '');
        $contentHash   = self::hashFor($product);
        $imagePhash    = self::perceptualHash($product);
        $issuedAt      = now();

        [$caSignature, $caKid] = CertificationAuthority::signCertificate(
            'coa', $certificateNo, $contentHash, $issuedAt->toIso8601String()
        );

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
            // The authority's Ed25519 signature: unlike the HMAC above, this one
            // can be checked by anyone holding the published public key.
            'ca_signature'     => $caSignature,
            'ca_kid'           => $caKid,
            'issued_at'        => $issuedAt,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        CertificationAuthority::appendToChain('coa', $id, 'issued');

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
    /**
     * Whether the authority's signature over this certificate still verifies.
     *
     * Reported separately from the certificate status because they answer
     * different questions: "superseded" means the record moved on, while a
     * failed signature would mean the stored row itself had been tampered with.
     *
     * @return array{state:string, kid:?string}
     */
    public static function signatureState(object $cert): array
    {
        if (! $cert->ca_signature) {
            return ['state' => 'unsigned', 'kid' => null];
        }

        $ok = CertificationAuthority::verifyCertificate(
            'coa',
            $cert->certificate_no,
            $cert->content_hash,
            \Illuminate\Support\Carbon::parse($cert->issued_at)->toIso8601String(),
            $cert->ca_signature
        );

        return ['state' => $ok ? 'valid' : 'invalid', 'kid' => $cert->ca_kid];
    }

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

        return [
            'status'      => 'valid',
            'certificate' => $cert,
            'product'     => $product,
            'signature'   => self::signatureState($cert),
        ];
    }
}
