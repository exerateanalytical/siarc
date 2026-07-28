<?php
/**
 * WCAG 2.1 contrast ratio calculator.
 *
 * Usage:  php scripts/contrast.php "#F3EFE7" "#12150F" ["label"]
 *         php scripts/contrast.php --table          (checks the dark-mode contract pairs)
 *
 * Dark mode's rule is "measured, not eyeballed" (docs/DARK-MODE-CONTRACT.md §3):
 * 4.5:1 for body text, 3:1 for large text and UI/control boundaries.
 */

function srgbChannel(float $c): float
{
    $c /= 255;
    return $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
}

function luminance(string $hex): float
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

    return 0.2126 * srgbChannel($r) + 0.7152 * srgbChannel($g) + 0.0722 * srgbChannel($b);
}

function ratio(string $a, string $b): float
{
    $la = luminance($a);
    $lb = luminance($b);

    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

/** Flatten a translucent overlay (rgba white/black at $alpha) onto a solid base. */
function overlay(string $base, string $over, float $alpha): string
{
    $b = ltrim($base, '#');
    $o = ltrim($over, '#');
    $out = '';
    for ($i = 0; $i < 3; $i++) {
        $bc = hexdec(substr($b, $i * 2, 2));
        $oc = hexdec(substr($o, $i * 2, 2));
        $out .= str_pad(dechex((int) round($oc * $alpha + $bc * (1 - $alpha))), 2, '0', STR_PAD_LEFT);
    }

    return '#' . strtoupper($out);
}

$argv = $_SERVER['argv'];

if (($argv[1] ?? '') === '--pairs') {
    // Pairs are given on stdin, one "fg bg label" per line.
    $fail = 0;
    while (($line = fgets(STDIN)) !== false) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#!')) {
            continue;
        }
        $parts = preg_split('/\s+/', $line, 4);
        [$fg, $bg, $need] = [$parts[0], $parts[1], (float) ($parts[2] ?? 4.5)];
        $label = $parts[3] ?? '';
        $r = ratio($fg, $bg);
        $ok = $r + 0.005 >= $need;
        $fail += $ok ? 0 : 1;
        printf("%-7s on %-7s  %5.2f:1  need %.1f  %s  %s\n", $fg, $bg, $r, $need, $ok ? 'PASS' : 'FAIL', $label);
    }
    echo $fail ? "\n$fail FAILING PAIR(S)\n" : "\nall pairs pass\n";
    exit($fail ? 1 : 0);
}

if (($argv[1] ?? '') === '--over') {
    // php contrast.php --over BASE OVERLAY ALPHA
    echo overlay($argv[2], $argv[3], (float) $argv[4]) . "\n";
    exit(0);
}

if (count($argv) < 3) {
    fwrite(STDERR, "usage: php scripts/contrast.php <fg> <bg> [label]\n       php scripts/contrast.php --pairs   (fg bg need label per line on stdin)\n       php scripts/contrast.php --over <base> <overlay> <alpha>\n");
    exit(2);
}

printf("%s on %s = %.2f:1  %s\n", $argv[1], $argv[2], ratio($argv[1], $argv[2]), $argv[3] ?? '');
