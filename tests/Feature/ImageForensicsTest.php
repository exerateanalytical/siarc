<?php

namespace Tests\Feature;

use App\Support\ImageFingerprint;
use App\Support\ImageWatermark;
use Tests\TestCase;

/**
 * Properties the forensics helpers must hold, exercised on images generated
 * here rather than on fixtures, so a reviewer can see exactly what the pixels
 * are.
 *
 * The pictures deliberately carry two-dimensional structure — stripes, blocks,
 * concentric rings. A flat fill or a plain left-to-right gradient reduces to
 * all-zero bits under a difference hash, so a test built on those would report
 * success for the wrong reason.
 */
class ImageForensicsTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/forensics-' . bin2hex(random_bytes(6));
        @mkdir($this->dir, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->dir);

        parent::tearDown();
    }

    private function path(string $name): string
    {
        return $this->dir . '/' . $name;
    }

    /**
     * A 4×4 grid of distinct tones with a coarse vertical stripe laid over it.
     *
     * The blocks put energy in the low frequencies, which is where the DCT hash
     * looks; the stripe adds local edges for the difference hash. An earlier
     * version of this test used fine stripes only, at eight cycles across the
     * frame — near the Nyquist limit of the 32×32 reduction, so the low-
     * frequency block held nothing but resampling noise and the DCT hash came
     * out effectively random between two copies of the same picture. Real
     * photographs are not like that; this pattern is.
     */
    private function striped(string $name, int $size = 320): string
    {
        $tones = [
            [20, 200, 60, 240],
            [180, 40, 220, 90],
            [70, 230, 30, 160],
            [250, 100, 140, 50],
        ];

        $img  = imagecreatetruecolor($size, $size);
        $cell = max(1, intdiv($size, 4));

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $v = $tones[min(3, intdiv($y, $cell))][min(3, intdiv($x, $cell))];

                if (intdiv($x, max(1, intdiv($size, 8))) % 2) {
                    $v = max(0, min(255, $v - 35));
                }

                imagesetpixel($img, $x, $y, imagecolorallocate($img, $v, $v, $v));
            }
        }

        $path = $this->path($name);
        imagepng($img, $path);
        imagedestroy($img);

        return $path;
    }

    /** Concentric rings: a visibly different picture, also structured. */
    private function rings(string $name, int $size = 320): string
    {
        $img = imagecreatetruecolor($size, $size);
        $c   = $size / 2;

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $r = sqrt(($x - $c) ** 2 + ($y - $c) ** 2);
                // Wide rings for the same reason the blocks above are wide.
                $v = (intdiv((int) $r, max(1, intdiv($size, 5))) % 2) ? 235 : 20;
                imagesetpixel($img, $x, $y, imagecolorallocate($img, $v, $v, $v));
            }
        }

        $path = $this->path($name);
        imagepng($img, $path);
        imagedestroy($img);

        return $path;
    }

    private function rescaled(string $src, string $name, int $size): string
    {
        $in  = imagecreatefrompng($src);
        $out = imagecreatetruecolor($size, $size);
        imagecopyresampled($out, $in, 0, 0, 0, 0, $size, $size, imagesx($in), imagesy($in));

        $path = $this->path($name);
        imagejpeg($out, $path, 82);
        imagedestroy($in);
        imagedestroy($out);

        return $path;
    }

    public function test_hashes_are_sixteen_hex_characters(): void
    {
        $p = $this->striped('a.png');

        foreach ([
            ImageFingerprint::dctHash($p),
            ImageFingerprint::blockHash($p),
            ImageFingerprint::differenceHash($p),
        ] as $hash) {
            $this->assertIsString($hash);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $hash);
        }
    }

    public function test_bits_are_not_degenerate_on_structured_images(): void
    {
        // Guards the test images themselves: a flat or purely horizontal
        // gradient would hash to all zeroes and make every other assertion
        // meaningless.
        foreach ([$this->striped('s.png'), $this->rings('r.png')] as $p) {
            foreach (['dctHash', 'blockHash', 'differenceHash'] as $m) {
                $hash = ImageFingerprint::$m($p);
                $this->assertNotSame('0000000000000000', $hash, "$m produced no bits");
                $this->assertNotSame('ffffffffffffffff', $hash, "$m produced only bits");
            }
        }
    }

    public function test_the_same_picture_rescaled_still_matches(): void
    {
        $big   = $this->striped('big.png', 480);
        $small = $this->rescaled($big, 'small.jpg', 150);

        $a = ImageFingerprint::fingerprint($big);
        $b = ImageFingerprint::fingerprint($small);

        $result = ImageFingerprint::matches($a, $b);

        $this->assertTrue(
            $result['match'],
            'rescaled copy should match, distance was ' . $result['distance']
        );
        $this->assertLessThanOrEqual(config('certificates.fingerprint.max_distance'), $result['distance']);
        $this->assertGreaterThan(0.5, $result['confidence']);
    }

    public function test_a_different_picture_does_not_match(): void
    {
        $a = ImageFingerprint::fingerprint($this->striped('x.png'));
        $b = ImageFingerprint::fingerprint($this->rings('y.png'));

        $result = ImageFingerprint::matches($a, $b);

        $this->assertFalse($result['match'], 'unrelated pictures matched at distance ' . $result['distance']);
        $this->assertGreaterThan(config('certificates.fingerprint.max_distance'), $result['distance']);
    }

    public function test_distance_is_symmetric_and_zero_for_identical_input(): void
    {
        $a = ImageFingerprint::dctHash($this->striped('d1.png'));
        $b = ImageFingerprint::dctHash($this->rings('d2.png'));

        $this->assertSame(0, ImageFingerprint::distance($a, $a));
        $this->assertSame(ImageFingerprint::distance($a, $b), ImageFingerprint::distance($b, $a));
        $this->assertGreaterThan(0, ImageFingerprint::distance($a, $b));
    }

    public function test_fingerprint_id_is_stable_and_quotable(): void
    {
        $p = $this->striped('id.png');

        $one = ImageFingerprint::fingerprint($p);
        $two = ImageFingerprint::fingerprint($p);

        $this->assertSame($one['id'], $two['id']);
        $this->assertStringStartsWith('AHF-', $one['id']);
        $this->assertLessThanOrEqual(32, strlen($one['id']));
    }

    public function test_missing_or_unreadable_files_return_null_everywhere(): void
    {
        $missing = $this->path('nope.png');
        $notAnImage = $this->path('junk.png');
        file_put_contents($notAnImage, 'this is not an image');

        foreach ([$missing, $notAnImage] as $p) {
            $this->assertNull(ImageFingerprint::dctHash($p));
            $this->assertNull(ImageFingerprint::blockHash($p));
            $this->assertNull(ImageFingerprint::differenceHash($p));
            $this->assertNull(ImageFingerprint::fingerprint($p));
            $this->assertNull(ImageWatermark::extract($p));
            $this->assertFalse(ImageWatermark::isWatermarked($p));
            $this->assertFalse(ImageWatermark::embed($p, $this->path('out.png'), 'WMK-TEST'));
        }
    }

    public function test_watermark_round_trips_the_reference_exactly(): void
    {
        $src  = $this->striped('w.png');
        $dest = $this->path('w-marked.png');
        $ref  = ImageWatermark::referenceFor('AHC-COA-2026-000000123');

        $this->assertTrue(ImageWatermark::embed($src, $dest, $ref));
        $this->assertTrue(ImageWatermark::isWatermarked($dest));
        $this->assertSame($ref, ImageWatermark::extract($dest));

        // The mark must be invisible in the ordinary sense: the marked file is
        // still a readable image of the same dimensions.
        $marked = imagecreatefrompng($dest);
        $this->assertSame(imagesx(imagecreatefrompng($src)), imagesx($marked));
    }

    public function test_a_clean_image_carries_no_reference(): void
    {
        $clean = $this->rings('clean.png');

        $this->assertNull(ImageWatermark::extract($clean));
        $this->assertFalse(ImageWatermark::isWatermarked($clean));
    }

    public function test_a_corrupted_payload_returns_null_rather_than_garbage(): void
    {
        $src  = $this->striped('c.png');
        $dest = $this->path('c-marked.png');
        $ref  = ImageWatermark::referenceFor('AHC-COA-2026-000000999');

        $this->assertTrue(ImageWatermark::embed($src, $dest, $ref));

        // Flip the low blue bit of a run of pixels inside the payload. The CRC
        // must catch it; returning a mangled reference would be worse than
        // returning nothing.
        $img = imagecreatefrompng($dest);
        $w   = imagesx($img);

        for ($i = 80; $i < 200; $i++) {
            $x   = $i % $w;
            $y   = intdiv($i, $w);
            $rgb = imagecolorat($img, $x, $y);
            imagesetpixel($img, $x, $y, imagecolorallocate(
                $img, ($rgb >> 16) & 0xFF, ($rgb >> 8) & 0xFF, ($rgb & 0xFF) ^ 1
            ));
        }

        $broken = $this->path('c-broken.png');
        imagepng($img, $broken);
        imagedestroy($img);

        $this->assertNull(ImageWatermark::extract($broken));
    }

    public function test_reference_for_is_stable_and_certificate_specific(): void
    {
        $a = ImageWatermark::referenceFor('AHC-COA-2026-000000001');
        $b = ImageWatermark::referenceFor('AHC-COA-2026-000000002');

        $this->assertSame($a, ImageWatermark::referenceFor('AHC-COA-2026-000000001'));
        $this->assertNotSame($a, $b);
        $this->assertStringStartsWith('WMK-AH237-', $a);
        $this->assertLessThanOrEqual(40, strlen($a));
    }

    public function test_watermarking_does_not_destroy_the_fingerprint(): void
    {
        // The point of using the lowest bit only: the picture, and therefore
        // its fingerprint, is unchanged for every practical purpose.
        $src  = $this->striped('fp.png');
        $dest = $this->path('fp-marked.png');

        ImageWatermark::embed($src, $dest, ImageWatermark::referenceFor('AHC-COA-2026-000000007'));

        $result = ImageFingerprint::matches(
            ImageFingerprint::fingerprint($src),
            ImageFingerprint::fingerprint($dest)
        );

        $this->assertTrue($result['match']);
        $this->assertSame(0, $result['distance']);
    }
}
