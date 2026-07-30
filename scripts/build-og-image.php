<?php
/**
 * Build the Open Graph share card at public/images/og-cover.png.
 *
 * Why this exists: every page emitted og:title and og:description but never an
 * og:image, because the partial only rendered one if a page set $seoOgImage and
 * no page ever did. A link shared to WhatsApp — which is how this platform will
 * actually spread in Cameroon — showed a bare grey box. Nothing in the repo was
 * usable as a share image either; the largest brand asset was 400x135, and
 * Facebook and WhatsApp both want 1200x630.
 *
 * The output PNG is committed, so this script is not part of any deploy. Run it
 * only when the branding changes:
 *
 *     php scripts/build-og-image.php
 *
 * Font: Georgia Bold, which is what the site's own CSS names as the fallback
 * for Playfair Display (--f-serif). Playfair ships here as woff2 only and GD
 * cannot read woff2. Override the path if you are not on Windows:
 *
 *     OG_SERIF_FONT=/path/to/serif-bold.ttf php scripts/build-og-image.php
 */

const W = 1200;
const H = 630;

$root = dirname(__DIR__);
$out  = $root . '/public/images/og-cover.png';

$font = getenv('OG_SERIF_FONT') ?: 'C:/Windows/Fonts/georgiab.ttf';
if (! is_file($font)) {
    fwrite(STDERR, "No serif font at {$font}. Set OG_SERIF_FONT to a .ttf and re-run.\n");
    exit(1);
}

$im = imagecreatetruecolor(W, H);
imagealphablending($im, true);

/** #RRGGBB -> a GD colour on $im. */
$rgb = function (string $hex) use ($im) {
    [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%2x%2x%2x');
    return imagecolorallocate($im, $r, $g, $b);
};

/* Background: the same diagonal forest gradient the site's hero bands use
   (linear-gradient(120deg, #0A2E18, #0F4824 55%, #0A2E18)). Painted per column
   with a triangular ramp so the lighter band sits just past centre. */
for ($x = 0; $x < W; $x++) {
    $t = $x / (W - 1);
    $k = $t <= 0.55 ? $t / 0.55 : 1 - (($t - 0.55) / 0.45);
    $r = (int) round(0x0A + (0x0F - 0x0A) * $k);
    $g = (int) round(0x2E + (0x48 - 0x2E) * $k);
    $b = (int) round(0x18 + (0x24 - 0x18) * $k);
    imagefilledrectangle($im, $x, 0, $x, H, imagecolorallocate($im, $r, $g, $b));
}

/* Kente strips top and bottom — the motif the real hero bands carry. */
$kente = @imagecreatefrompng($root . '/public/images/landing/hh-kente.png');
if ($kente) {
    $kw = imagesx($kente);
    $kh = imagesy($kente);
    $band = 16;
    for ($x = 0; $x < W; $x += $kw) {
        imagecopyresampled($im, $kente, $x, 0, 0, 0, $kw, $band, $kw, $kh);
        imagecopyresampled($im, $kente, $x, H - $band, 0, 0, $kw, $band, $kw, $kh);
    }
    imagedestroy($kente);
}

/* Brand mark, left. Resampled up from 240px — soft at the edges if you look
   for it, but it is the only mark in the repo and a share card is seen small. */
$markPath = is_file($root . '/public/images/brand/logo-mark.png')
    ? $root . '/public/images/brand/logo-mark.png'
    : $root . '/public/images/landing/logo.png';

$mark = @imagecreatefrompng($markPath);
$textLeft = 96;
if ($mark) {
    $size = 220;
    $mw = imagesx($mark);
    $mh = imagesy($mark);
    $dh = (int) round($size * $mh / $mw);
    $dst = imagecreatetruecolor($size, $dh);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagecopyresampled($dst, $mark, 0, 0, 0, 0, $size, $dh, $mw, $mh);
    imagecopy($im, $dst, 96, (int) ((H - $dh) / 2), 0, 0, $size, $dh);
    $textLeft = 96 + $size + 56;
    imagedestroy($dst);
    imagedestroy($mark);
}

$cream = $rgb('#F8F3ED');
$gold  = $rgb('#E0A52F');
$muted = $rgb('#C9D6CD');

/* Wordmark, split so "237" carries the gold the site gives it.
   The size is measured down to fit rather than hard-coded: at a fixed 74pt the
   "237" ran off the right edge, and a share card that crops its own brand name
   is worse than no card. */
$width = static function (int $pt, string $s) use ($font): int {
    $b = imagettfbbox($pt, 0, $font, $s);
    return $b[2] - $b[0];
};

$avail = W - $textLeft - 72;                 // 72px right margin
$title = 'Artisan Hub 237';
$pt = 74;
while ($pt > 34 && $width($pt, $title) > $avail) {
    $pt -= 2;
}

$y = 292;
imagettftext($im, $pt, 0, $textLeft, $y, $cream, $font, 'Artisan Hub');
imagettftext($im, $pt, 0, $textLeft + $width($pt, 'Artisan Hub '), $y, $gold, $font, '237');

/* Gold rule, echoing the one under page headings. */
imagefilledrectangle($im, $textLeft, $y + 34, $textLeft + 150, $y + 39, $gold);

/* Same treatment for the tagline — it is longer than the wordmark. */
$tag = "L'artisanat camerounais authentique";
$tagPt = 30;
while ($tagPt > 16 && $width($tagPt, $tag) > $avail) {
    $tagPt -= 1;
}
imagettftext($im, $tagPt, 0, $textLeft, $y + 104, $muted, $font, $tag);
imagettftext($im, 22, 0, $textLeft, $y + 154, $muted, $font, 'artisanhub237.com');

imagepng($im, $out, 9);
imagedestroy($im);

$sz = filesize($out);
[$w, $h] = getimagesize($out);
echo "wrote " . str_replace($root . '/', '', $out) . "  {$w}x{$h}  " . round($sz / 1024) . " KB\n";
if ($sz > 8 * 1024 * 1024) {
    fwrite(STDERR, "WARNING: over 8MB — WhatsApp and Facebook will refuse to fetch it.\n");
}
