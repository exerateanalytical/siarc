<?php

namespace App\Support;

/**
 * Perceptual fingerprints of photographs, computed from the pixels.
 *
 * There is no model and no cleverness here: three well-understood hashes, each
 * 64 bits, each derived by arithmetic anyone can repeat. They answer two
 * questions the certificate module keeps asking. Is the photograph on this
 * listing still the one that was certified? And do these two files show the
 * same work?
 *
 * Three hashes rather than one because each fails differently. The DCT hash is
 * the most robust to rescaling and re-encoding and is therefore the primary
 * signal, but it is comparatively blind to fine texture; the block hash
 * notices gross tonal layout; the difference hash notices local edges and is
 * cheap. Agreement between them is what turns "these bits are close" into
 * "this is the same picture", and disagreement is a useful warning that one of
 * them has been fooled.
 *
 * Every method returns null when the file is missing, unreadable, or not an
 * image. A fabricated fingerprint on a certificate would be worse than an
 * absent one, because absence is visibly absent and a fake is not.
 */
class ImageFingerprint
{
    /**
     * DCT-based perceptual hash.
     *
     * The image is reduced to 32×32 greyscale, transformed with a 2-D DCT-II,
     * and only the top-left 8×8 block of coefficients is kept — the low
     * frequencies, which describe the picture's overall shape and survive
     * scaling and JPEG quantisation. The DC term is dropped because it is just
     * mean brightness, so keeping it would make the hash react to exposure
     * rather than content. Each remaining coefficient is compared against the
     * median of the block, which is what makes the result invariant to a
     * constant gain across the image.
     */
    public static function dctHash(string $path): ?string
    {
        $grey = self::greyscaleMatrix($path, 32);

        if ($grey === null) {
            return null;
        }

        $rows = [];
        for ($y = 0; $y < 32; $y++) {
            $rows[$y] = self::dct1d($grey[$y]);
        }

        // Second pass down the columns completes the separable 2-D transform.
        $coeff = [];
        for ($x = 0; $x < 32; $x++) {
            $column = [];
            for ($y = 0; $y < 32; $y++) {
                $column[$y] = $rows[$y][$x];
            }
            $transformed = self::dct1d($column);
            for ($y = 0; $y < 32; $y++) {
                $coeff[$y][$x] = $transformed[$y];
            }
        }

        $values = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                if ($y === 0 && $x === 0) {
                    continue; // DC term: brightness, not content.
                }
                $values[] = $coeff[$y][$x];
            }
        }

        $median = self::median($values);

        // 63 comparisons plus a fixed leading zero in the DC position, so the
        // hash is a full 64 bits and lines up with the other two.
        $bits = '0';
        foreach ($values as $v) {
            $bits .= $v > $median ? '1' : '0';
        }

        return self::bitsToHex($bits);
    }

    /**
     * Block-mean hash: the mean luma of each cell of an 8×8 grid against the
     * median of those means.
     *
     * Averaging over whole blocks rather than sampling single pixels is what
     * makes this tolerant of noise and of the resampling a phone applies on
     * upload, at the cost of ignoring anything smaller than an eighth of the
     * frame.
     */
    public static function blockHash(string $path): ?string
    {
        $grey = self::greyscaleMatrix($path, 8);

        if ($grey === null) {
            return null;
        }

        $means = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $means[] = $grey[$y][$x];
            }
        }

        $median = self::median($means);

        $bits = '';
        foreach ($means as $m) {
            $bits .= $m > $median ? '1' : '0';
        }

        return self::bitsToHex($bits);
    }

    /**
     * Difference hash: 9×8 greyscale, each pixel against its right neighbour.
     *
     * The same construction ProductCertificate::perceptualHash() applies to a
     * Product's cover image; this one takes a path so it can be run over a file
     * that is not yet attached to anything — an upload being checked, or a
     * photograph a buyer has sent us to compare.
     */
    public static function differenceHash(string $path): ?string
    {
        $img = self::load($path);

        if ($img === null) {
            return null;
        }

        $small = imagecreatetruecolor(9, 8);
        imagecopyresampled($small, $img, 0, 0, 0, 0, 9, 8, imagesx($img), imagesy($img));
        imagedestroy($img);

        $bits = '';
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= self::luma($small, $x, $y) > self::luma($small, $x + 1, $y) ? '1' : '0';
            }
        }

        imagedestroy($small);

        return self::bitsToHex($bits);
    }

    /**
     * All three hashes plus a short reference a person can read out.
     *
     * The id is derived from the three hashes together, so it changes if any of
     * them does, and it is deliberately short: it exists to be quoted in an
     * email or written on a form, not to be compared bit by bit.
     *
     * @return array{dct:string, block:string, diff:string, id:string}|null
     */
    public static function fingerprint(string $path): ?array
    {
        $dct   = self::dctHash($path);
        $block = self::blockHash($path);
        $diff  = self::differenceHash($path);

        if ($dct === null || $block === null || $diff === null) {
            return null;
        }

        $digest = strtoupper(substr(hash('sha256', $dct . $block . $diff), 0, 12));

        return [
            'dct'   => $dct,
            'block' => $block,
            'diff'  => $diff,
            'id'    => 'AHF-' . implode('-', str_split($digest, 4)),
        ];
    }

    /** Hamming distance between two equal-length hex hashes. */
    public static function distance(string $hexA, string $hexB): int
    {
        $a = self::hexToBits($hexA);
        $b = self::hexToBits($hexB);

        $length = min(strlen($a), strlen($b));
        $distance = 0;

        for ($i = 0; $i < $length; $i++) {
            if ($a[$i] !== $b[$i]) {
                $distance++;
            }
        }

        // Differing lengths would mean one hash was truncated; every missing
        // bit counts against the match rather than being quietly ignored.
        return $distance + abs(strlen($a) - strlen($b));
    }

    /**
     * Whether two fingerprints describe the same work.
     *
     * The DCT distance decides, because it is the one that holds up under
     * rescaling and re-encoding. But a single hash can be fooled by a picture
     * with similar low-frequency structure, so a match also requires at least
     * one of the other two to agree within the same threshold. Both conditions
     * have to hold; either alone has produced false positives on the flat,
     * evenly-lit product shots this platform is full of.
     *
     * Confidence is the mean agreement across all three hashes, on 0..1. It is
     * a description of the measurement, not a probability — reported so a human
     * reviewing a borderline case can see whether the hashes were unanimous.
     *
     * @return array{match:bool, distance:int, confidence:float}
     */
    public static function matches(array $a, array $b, ?int $max = null): array
    {
        $max ??= (int) config('certificates.fingerprint.max_distance', 10);

        foreach (['dct', 'block', 'diff'] as $key) {
            if (! isset($a[$key], $b[$key]) || ! is_string($a[$key]) || ! is_string($b[$key])) {
                // An incomplete fingerprint cannot be compared, and guessing
                // would be the one thing worse than saying so.
                return ['match' => false, 'distance' => 64, 'confidence' => 0.0];
            }
        }

        $dct   = self::distance($a['dct'], $b['dct']);
        $block = self::distance($a['block'], $b['block']);
        $diff  = self::distance($a['diff'], $b['diff']);

        $match = $dct <= $max && min($block, $diff) <= $max;

        $confidence = round(1 - (($dct + $block + $diff) / 3) / 64, 4);

        return [
            'match'      => $match,
            'distance'   => $dct,
            'confidence' => max(0.0, (float) $confidence),
        ];
    }

    /**
     * Decode to a square greyscale matrix of the given size.
     *
     * Resampling down to the target in one step lets GD do the averaging, which
     * is both faster and less noisy than sampling the full-size image.
     *
     * @return array<int, array<int, float>>|null
     */
    private static function greyscaleMatrix(string $path, int $size): ?array
    {
        $img = self::load($path);

        if ($img === null) {
            return null;
        }

        $small = imagecreatetruecolor($size, $size);
        imagecopyresampled($small, $img, 0, 0, 0, 0, $size, $size, imagesx($img), imagesy($img));
        imagedestroy($img);

        $matrix = [];
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $matrix[$y][$x] = self::luma($small, $x, $y);
            }
        }

        imagedestroy($small);

        return $matrix;
    }

    /** Read a file into a GD image, or null if it is not one. */
    private static function load(string $path): ?\GdImage
    {
        if (! function_exists('imagecreatefromstring') || ! is_file($path) || ! is_readable($path)) {
            return null;
        }

        $raw = @file_get_contents($path);

        if ($raw === false || $raw === '') {
            return null;
        }

        $img = @imagecreatefromstring($raw);

        return $img instanceof \GdImage ? $img : null;
    }

    /** Rec. 601 luma, the same weighting ProductCertificate uses. */
    private static function luma(\GdImage $img, int $x, int $y): float
    {
        $rgb = imagecolorat($img, $x, $y);

        return 0.299 * (($rgb >> 16) & 0xFF)
             + 0.587 * (($rgb >> 8) & 0xFF)
             + 0.114 * ($rgb & 0xFF);
    }

    /**
     * One-dimensional DCT-II.
     *
     * Written out directly rather than as an FFT: the input is 32 samples, so
     * the naive form costs about a thousand multiplications and is over before
     * the file read has finished. Clarity is worth more here than speed.
     *
     * @param  array<int, float>  $input
     * @return array<int, float>
     */
    private static function dct1d(array $input): array
    {
        $n = count($input);
        $out = [];

        for ($k = 0; $k < $n; $k++) {
            $sum = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $sum += $input[$i] * cos(M_PI * ($i + 0.5) * $k / $n);
            }
            // The orthonormal scaling is not strictly needed — the hash only
            // compares coefficients against their own median — but it keeps the
            // numbers in a range that is readable when debugging.
            $out[$k] = $sum * ($k === 0 ? sqrt(1 / $n) : sqrt(2 / $n));
        }

        return $out;
    }

    /** @param array<int, float> $values */
    private static function median(array $values): float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        $mid = intdiv($count, 2);

        return $count % 2 ? $values[$mid] : ($values[$mid - 1] + $values[$mid]) / 2;
    }

    /** 64 bit characters to 16 hex, byte by byte so the value is width-independent. */
    private static function bitsToHex(string $bits): string
    {
        $hex = '';

        foreach (str_split($bits, 8) as $byte) {
            $hex .= str_pad(dechex((int) bindec($byte)), 2, '0', STR_PAD_LEFT);
        }

        return $hex;
    }

    private static function hexToBits(string $hex): string
    {
        $bits = '';

        foreach (str_split(strtolower(trim($hex))) as $char) {
            $value = hexdec($char);
            $bits .= str_pad(decbin((int) $value), 4, '0', STR_PAD_LEFT);
        }

        return $bits;
    }
}
