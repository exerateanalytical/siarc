{{--
    Security artwork for the four certificates the platform issues, kept in its
    own sheet rather than mixed into coa-ornaments so that decoration and
    security-relevant marks stay separable — someone auditing what the platform
    actually claims should be able to read this file alone.

    Everything here is genuinely what its name says. The guilloché is real
    hypotrochoid mathematics generated below, not a hand-drawn squiggle; the
    microtext is real SVG <text> that resolves into readable characters when the
    reader zooms; the anti-copy screen is a real fine-line pattern that breaks up
    under rescreening. Nothing in this sheet is captioned as a physical printing
    feature, because a screen cannot carry one. The features that need a security
    printer — UV ink, foil, embossing, intaglio, thread, watermarked stock — are
    specified in docs/PRINT-SECURITY-SPEC.md and are deliberately absent here.

    All inline SVG, as with the ornament sheet: a strict CSP forbids external
    requests, and the certificates scale as a single canvas, so raster art would
    be soft at every size but one.
--}}
@php
    // ── Guilloché parameters ────────────────────────────────────────────────
    // A guilloché is a hypotrochoid: a point at distance $d from the centre of
    // a circle of radius $r rolling inside a circle of radius $R. The ratio
    // R/r decides how many lobes the rosette has and, crucially, whether the
    // curve closes — R and r are kept as whole numbers so R/gcd(R,r) gives an
    // exact period and the strand meets its own tail instead of drifting.
    //
    // Rosette. A single hypotrochoid family, however pretty, draws a sunburst:
    // one lobe count, one radius, spokes. What makes a banknote rosette look
    // woven is two families of different lobe count laid over each other so
    // their crossings form a lattice, which is how the real engine-turning lathe
    // works. So there are two here, listed as [R, r, d, strands, direction].
    //
    // R and r are coprime in both, so each curve closes exactly instead of
    // drifting: family A closes after 6 turns having drawn 19 lobes, family B
    // after 5 turns having drawn 13. Small integers matter — a ratio like 170/53
    // also closes, but only after 53 turns, needing tens of thousands of samples
    // to stay smooth and packing the lobes into a solid disc at this diameter.
    //
    // d is kept below (R−r) in both. That is the parameter that decides how deep
    // the petals cut: d≈R−r gives long thin spikes that read as a starburst,
    // while these values leave a narrow annulus of line work, which is the band
    // a rosette is actually made of.
    //
    // The strands of one family are offset by a small fraction of a lobe, not an
    // even division of it. Spreading them evenly just multiplies the lobe count;
    // bunching them makes each lobe a ribbon of parallel lines, which is the
    // look wanted.
    $rosFamilies = [
        [19, 6, 4.6, 6,  1],
        [13, 5, 3.2, 6, -1],
    ];
    $rosSpread  = 0.5;     // total strand offset, in lobes
    $rosRadius  = 86;      // drawn radius inside the 200-unit box
    $rosSteps   = 1200;    // ~80 samples per lobe; below ~40 the curves visibly facet

    // Border band: the same family unrolled onto a line. A straight-run
    // guilloché is the sum of two sinusoids whose frequencies are coprime, so
    // the envelope only repeats after a long run — that is what stops the band
    // reading as a simple wave. 13 and 23 cycles across the 1000px tile were
    // chosen after trying 7 and 11: the lower pair produced recognisable sine
    // waves rather than a band, because at that frequency the crossings are too
    // far apart to knit. Ten strands rather than six for the same reason — the
    // braid is made by the density of crossings, not by the amplitude.
    $bandW = 1000; $bandH = 44; $bandStep = 0.8;
    $bandA = 12.0; $bandB = 6.0;      // amplitudes of the two components
    $bandF1 = 13;  $bandF2 = 23;      // cycles across the tile
    $bandStrands = 10;

    $fmt = fn ($v) => rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');

    // Rosette strands.
    $rosettePath = '';
    foreach ($rosFamilies as [$R, $r, $d, $strands, $dir]) {
        $k     = ($R - $r) / $r;
        // Normalised so both families reach the same outer radius and their
        // lattices actually meet at the rim instead of one floating inside.
        $scale = $rosRadius / (($R - $r) + $d);
        for ($s = 0; $s < $strands; $s++) {
            $spin = ($s / max($strands - 1, 1)) * $rosSpread * (2 * M_PI / $R);
            for ($i = 0; $i <= $rosSteps; $i++) {
                $t  = $dir * ($i / $rosSteps) * 2 * M_PI * $r;
                $x0 = (($R - $r) * cos($t) + $d * cos($k * $t)) * $scale;
                $y0 = (($R - $r) * sin($t) - $d * sin($k * $t)) * $scale;
                $x  = 100 + $x0 * cos($spin) - $y0 * sin($spin);
                $y  = 100 + $x0 * sin($spin) + $y0 * cos($spin);
                $rosettePath .= ($i === 0 ? 'M' : 'L') . $fmt($x) . ' ' . $fmt($y);
            }
        }
    }

    // Border strands: each copy is phase-shifted, which is what produces the
    // braided crossings rather than a stack of parallel waves.
    $borderPath = '';
    for ($s = 0; $s < $bandStrands; $s++) {
        $ph = $s * (2 * M_PI / $bandStrands);
        $first = true;
        for ($x = 0.0; $x <= $bandW; $x += $bandStep) {
            $u = ($x / $bandW) * 2 * M_PI;
            $y = $bandH / 2
                + $bandA * sin($bandF1 * $u + $ph)
                + $bandB * sin($bandF2 * $u + 2 * $ph);
            $borderPath .= ($first ? 'M' : 'L') . $fmt($x) . ' ' . $fmt($y);
            $first = false;
        }
    }

    // The microtext string. It is a statement of who issued the document, which
    // is the only thing microtext is good for: it is not a secret, it is a
    // feature that is cheap to read and expensive to reproduce cleanly.
    $microUnit   = 'ARTISANHUB237 CERTIFICATION AUTHORITY • ';
    $microLine   = str_repeat($microUnit, 34);
    $microRing   = str_repeat($microUnit, 12);
@endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        {{-- ── Guilloché ──────────────────────────────────────────────────────
             Hairline stroke and low opacity on purpose. A guilloché works by
             being finer than the reproduction chain that would copy it, so a
             heavy, confident line would defeat the point as well as fighting
             the certificate's typography. --}}
        <path id="coaGuillocheBorder" d="{{ $borderPath }}"
              fill="none" stroke="#0A3A22" stroke-width="0.35" stroke-opacity="0.55"
              stroke-linecap="round" vector-effect="non-scaling-stroke"/>

        <path id="coaGuillocheRosette" d="{{ $rosettePath }}"
              fill="none" stroke="#9C6E1B" stroke-width="0.32" stroke-opacity="0.6"
              stroke-linecap="round" vector-effect="non-scaling-stroke"/>

        {{-- ── Microtext ──────────────────────────────────────────────────────
             Genuinely 1.6px type. At 100% it reads as a grey rule, which is the
             intended behaviour; zoom in and the characters resolve, which is the
             check a buyer can actually perform. It is real <text>, so a
             screenshot rescaled by a copier loses it while the vector original
             does not. letter-spacing is kept slightly negative because at this
             size the default advance opens gaps that make the line look dotted. --}}
        <g id="coaMicrotextLine">
            <text x="0" y="2" font-family="Helvetica, Arial, sans-serif" font-size="1.6"
                  letter-spacing="-0.02" fill="#0A3A22" fill-opacity="0.62"
                  xml:space="preserve">{{ $microLine }}</text>
        </g>

        {{-- The ring a border or seal rim rides on. Consumers reference it from
             a <textPath href="#coaMicrotextRing">, which is why the geometry and
             the text are separated: one path, many strings. --}}
        <path id="coaMicrotextRing" d="M100 12a88 88 0 1 1-0.1 0" fill="none"/>
        <g id="coaMicrotextPath">
            <text font-family="Helvetica, Arial, sans-serif" font-size="1.8"
                  letter-spacing="-0.02" fill="#0A3A22" fill-opacity="0.62" xml:space="preserve"
            ><textPath href="#coaMicrotextRing">{{ $microRing }}</textPath></text>
        </g>

        {{-- ── Anti-copy line screen ──────────────────────────────────────────
             0.25px lines at 3px pitch. This is honest about what it does: it
             does not stop a copy, it degrades one. A photocopier or a phone
             camera resamples at a frequency close to this pitch and the result
             moirés or fills in, so a reproduction looks visibly unlike the
             original even though nothing about the screen is secret. --}}
        <pattern id="coaAntiCopy" width="3" height="3" patternUnits="userSpaceOnUse"
                 patternTransform="rotate(38)">
            <rect width="3" height="3" fill="none"/>
            <path d="M0 0.5H3" stroke="#0A3A22" stroke-width="0.25" stroke-opacity="0.16"/>
            <path d="M0 2H3" stroke="#9C6E1B" stroke-width="0.25" stroke-opacity="0.12"/>
        </pattern>

        {{-- ── Iridescent seal treatment ──────────────────────────────────────
             NOT a hologram, and never to be captioned as one. This is a
             multi-stop gradient that reads as iridescent foil on a screen; it
             carries no physical property, cannot be authenticated, and would be
             a false security claim if labelled as anything more than decoration.
             It exists so a seal printed on plain paper still looks finished, and
             so an operator who later commissions real foil has the artwork
             position already fixed. --}}
        <linearGradient id="coaHoloGradient" x1="6%" y1="4%" x2="94%" y2="96%">
            <stop offset="0"    stop-color="#F7E1A4"/>
            <stop offset="14%"  stop-color="#8FD8C6"/>
            <stop offset="30%"  stop-color="#9FC4F0"/>
            <stop offset="46%"  stop-color="#D9A8E8"/>
            <stop offset="60%"  stop-color="#F4D98C"/>
            <stop offset="74%"  stop-color="#9BE0B4"/>
            <stop offset="88%"  stop-color="#C9942E"/>
            <stop offset="1"    stop-color="#9C6E1B"/>
        </linearGradient>
    </defs>
</svg>
