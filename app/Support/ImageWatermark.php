<?php

namespace App\Support;

/**
 * An invisible reference embedded in the delivered copy of a product image.
 *
 * The reference is written into the least significant bit of the blue channel,
 * pixel by pixel, in row order. Blue because the eye is least sensitive to it,
 * and the lowest bit because changing it moves a channel by one step out of
 * 256 — below the noise floor of any camera and invisible on any screen.
 *
 * Be clear about what this is worth. It survives copying, renaming, and being
 * re-saved losslessly as PNG. It does NOT survive JPEG or WebP re-encoding,
 * resizing, cropping, rotation, a screenshot, or a phone's share sheet — all
 * of which discard or rewrite exactly the bits the payload lives in. That is
 * why embed() always writes PNG: handing back a JPEG would destroy the payload
 * on the way out and leave us claiming a mark that was never there.
 *
 * So: this proves a particular file came from us, and it is enough to identify
 * a copy lifted from the site and reposted intact. It is not tamper-proofing
 * and it is not a defence against anyone who knows it is there — one pass of a
 * resize removes it, and nothing here detects that removal. Where a claim has
 * to hold up, the certificate's signatures and the perceptual fingerprint are
 * the evidence; this is a convenience on top of them.
 */
class ImageWatermark
{
    /** Marks the start of a payload so extraction can tell signal from image noise. */
    private const MAGIC = 'AH237W';

    /**
     * Write a copy of the image carrying the reference.
     *
     * Returns false rather than throwing when the source is missing, is not an
     * image, or is too small to hold the payload — callers deliver the original
     * in that case, and a missing mark is a smaller problem than a failed
     * download.
     */
    public static function embed(string $srcPath, string $destPath, string $reference): bool
    {
        if (! function_exists('imagecreatefromstring') || ! is_file($srcPath) || ! is_readable($srcPath)) {
            return false;
        }

        $raw = @file_get_contents($srcPath);
        $src = $raw === false || $raw === '' ? false : @imagecreatefromstring($raw);

        if (! $src instanceof \GdImage) {
            return false;
        }

        $bits = self::payloadBits($reference);
        $width = imagesx($src);
        $height = imagesy($src);

        if ($bits === null || strlen($bits) > $width * $height) {
            imagedestroy($src);

            return false;
        }

        // Work on a truecolor copy: a palette source has no per-pixel blue
        // channel to hide in, and imagesetpixel on a palette image would
        // allocate new entries instead of nudging one bit.
        $canvas = imagecreatetruecolor($width, $height);
        imagecopy($canvas, $src, 0, 0, 0, 0, $width, $height);
        imagedestroy($src);

        $length = strlen($bits);

        for ($i = 0; $i < $length; $i++) {
            $x = $i % $width;
            $y = intdiv($i, $width);

            $rgb = imagecolorat($canvas, $x, $y);
            $blue = ($rgb & 0xFF & ~1) | ($bits[$i] === '1' ? 1 : 0);

            imagesetpixel($canvas, $x, $y, imagecolorallocate(
                $canvas, ($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, $blue
            ));
        }

        $dir = dirname($destPath);

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        // PNG, always. See the class docblock: a JPEG round trip rewrites the
        // low bits and would erase the payload as it was written.
        $ok = @imagepng($canvas, $destPath);
        imagedestroy($canvas);

        return (bool) $ok;
    }

    /**
     * Recover the reference, or null if this file does not carry one.
     *
     * Three things have to line up before anything is returned: the magic
     * header, a plausible length, and a CRC over the whole payload. Any image
     * has low bits that look like data, so without the checks this would
     * happily report noise as a certificate reference.
     */
    public static function extract(string $path): ?string
    {
        if (! function_exists('imagecreatefromstring') || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        $img = $raw === false || $raw === '' ? false : @imagecreatefromstring($raw);

        if (! $img instanceof \GdImage) {
            return null;
        }

        $width = imagesx($img);
        $height = imagesy($img);
        $capacity = $width * $height;

        $magicBits = strlen(self::MAGIC) * 8;
        $headerBits = $magicBits + 8;

        if ($capacity < $headerBits + 32) {
            imagedestroy($img);

            return null;
        }

        $read = function (int $from, int $count) use ($img, $width): string {
            $bits = '';
            for ($i = $from; $i < $from + $count; $i++) {
                $bits .= (imagecolorat($img, $i % $width, intdiv($i, $width)) & 1) ? '1' : '0';
            }

            return $bits;
        };

        $magic = self::bitsToBytes($read(0, $magicBits));

        if ($magic !== self::MAGIC) {
            imagedestroy($img);

            return null;
        }

        $length = bindec($read($magicBits, 8));

        if ($length < 1 || $capacity < $headerBits + ($length * 8) + 32) {
            imagedestroy($img);

            return null;
        }

        $reference = self::bitsToBytes($read($headerBits, $length * 8));
        $crcBits = $read($headerBits + ($length * 8), 32);

        imagedestroy($img);

        $expected = crc32(self::MAGIC . chr($length) . $reference);

        if ((int) bindec($crcBits) !== $expected) {
            return null;
        }

        return $reference;
    }

    public static function isWatermarked(string $path): bool
    {
        return self::extract($path) !== null;
    }

    /**
     * The public reference for a certificate.
     *
     * Derived from the certificate number rather than being the number itself,
     * so that a reference printed on a certificate or found in a stray file
     * does not hand out the sequence position of every other certificate we
     * have issued. Stable, because the same certificate must always yield the
     * same reference for a lookup to mean anything.
     */
    public static function referenceFor(string $certificateNo): string
    {
        $digest = substr(hash('sha256', 'ah237-watermark|' . trim($certificateNo)), 0, 12);

        return 'WMK-AH237-' . strtoupper($digest);
    }

    /** Magic header, one-byte length, reference, CRC32 — as a bit string. */
    private static function payloadBits(string $reference): ?string
    {
        $reference = trim($reference);
        $length = strlen($reference);

        if ($length < 1 || $length > 255) {
            // The length prefix is a single byte, so anything longer could not
            // be described honestly and is refused rather than truncated.
            return null;
        }

        $body = self::MAGIC . chr($length) . $reference;
        $crc = crc32($body);

        $bits = '';

        foreach (str_split($body) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        return $bits . str_pad(decbin($crc), 32, '0', STR_PAD_LEFT);
    }

    private static function bitsToBytes(string $bits): string
    {
        $out = '';

        foreach (str_split($bits, 8) as $byte) {
            $out .= chr((int) bindec($byte));
        }

        return $out;
    }
}
