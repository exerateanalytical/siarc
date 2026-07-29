{{--
    Cameroon regional choropleth — real geometry, real counts.

    GEOMETRY
    Source : geoBoundaries gbOpen, CMR / ADM1 (10 regions), release 9469f09,
             https://www.geoboundaries.org/api/current/gbOpen/CMR/ADM1/
    Upstream source recorded by geoBoundaries: Wikimedia Commons.
    Licence : Creative Commons Attribution (CC BY) — geoBoundaries gbOpen.
              Attribution is carried in the rendered footnote under the legend.
    Processing: WGS84 lon/lat -> equirectangular (x scaled by cos 7°N, the country's
              mid-latitude), Douglas–Peucker simplified at 0.012° (~1.3 km), rings
              under 2% of a region's largest ring dropped. ~12 KB of path data,
              every region still recognisable and adjacency preserved.
    Inlined on purpose: no CDN, no runtime fetch — it must render offline and on
              shared hosting.

    WHAT IS COUNTED — read this before changing a label.
    The fill is the number of *artisan records the platform holds* for the region:
    every non-deleted business row, published or not. Most of them are unclaimed
    SIARC imports that no visitor can see, so a caption reading "artisans on the
    platform" would be false. Every figure is therefore rendered as a pair —
    records held, and of those, how many are actually published — in the caption,
    in each region's accessible name, and in the hover/focus readout.
    Nothing here is hardcoded; the numbers come from the query below.
--}}
@php
    $cmIsFr = (($lang ?? 'fr') === 'fr');
    $cmLang = $lang ?? 'fr';

    // Artisan records per region: all non-deleted businesses, and the published subset.
    $cmRows = \Illuminate\Support\Facades\DB::table('regions as r')
        ->leftJoin('businesses as b', function ($j) {
            $j->on('b.region_id', '=', 'r.id')->whereNull('b.deleted_at');
        })
        ->groupBy('r.id', 'r.code', 'r.name_fr', 'r.name_en', 'r.sort_order')
        ->orderBy('r.sort_order')
        ->selectRaw("r.code, r.name_fr, r.name_en, count(b.id) as records, sum(case when b.status = 'published' then 1 else 0 end) as published")
        ->get()
        ->keyBy('code');

    $cmUnassigned = (int) \Illuminate\Support\Facades\DB::table('businesses')
        ->whereNull('deleted_at')->whereNull('region_id')->count();

    $cmTotal      = (int) $cmRows->sum('records');
    $cmTotalPub   = (int) $cmRows->sum('published');
    $cmMax        = (int) $cmRows->max('records');
    $cmStep       = max(1, (int) ceil($cmMax / 5));

    // Bucket 0 is a measured zero. A region missing from the query is "not measured"
    // and gets the hatch, which is a different thing and must look different.
    $cmBucket = function (int $n) use ($cmStep) {
        if ($n <= 0) return 0;
        return min(5, (int) ceil($n / $cmStep));
    };

    $cmLegend = [];
    for ($i = 1; $i <= 5; $i++) {
        $lo = ($i - 1) * $cmStep + 1;
        $hi = min($cmMax, $i * $cmStep);
        if ($lo > $cmMax) break;
        $cmLegend[$i] = $lo === $hi ? (string) $lo : $lo . '–' . $hi;
    }

    $cmMap = function (string $code) use ($cmRows, $cmBucket, $cmIsFr, $cmLang, $cmTotal) {
        $row = $cmRows->get($code);
        if (! $row) {
            return [
                'name' => $code, 'records' => '—', 'published' => '—', 'share' => '—',
                'fill' => 'url(#cm-hatch)', 'dash' => '',
                'href' => route('admin.regions', ['lang' => $cmLang]),
                'aria' => $cmIsFr ? $code . ' — non mesuré.' : $code . ' — not measured.',
            ];
        }
        $n    = (int) $row->records;
        $pub  = (int) $row->published;
        $name = $cmIsFr ? $row->name_fr : ($row->name_en ?: $row->name_fr);
        $share = $cmTotal > 0 ? number_format($n / $cmTotal * 100, 1) . ' %' : '0 %';
        $aria = $cmIsFr
            ? sprintf('%s : %d fiche%s artisan, dont %d publiée%s. Ouvrir la région.', $name, $n, $n > 1 ? 's' : '', $pub, $pub > 1 ? 's' : '')
            : sprintf('%s: %d artisan record%s, of which %d published. Open the region.', $name, $n, $n === 1 ? '' : 's', $pub);
        return [
            'name' => $name, 'records' => $n, 'published' => $pub, 'share' => $share,
            'fill' => 'var(--cm-b' . $cmBucket($n) . ')',
            'dash' => $n === 0 ? ' stroke-dasharray="4 3"' : '',
            'href' => route('admin.regions', ['region' => $row->code, 'lang' => $cmLang]),
            'aria' => $aria,
        ];
    };
@endphp

<div class="cm-choropleth mt-3" data-cm-default="{{ $cmIsFr ? 'Survolez une région pour voir ses chiffres.' : 'Hover a region to see its figures.' }}">
    <style>
        /* Sequential green ramp from the dark-mode contract's brand family; gold is
           reserved for the focus/hover accent so it never competes with the data. */
        .cm-choropleth {
            --cm-b0: #FFFFFF; --cm-b1: #E2F3E8; --cm-b2: #A9DCBB; --cm-b3: #4FA872; --cm-b4: #2E7D4B; --cm-b5: #14652F;
            --cm-stroke: #FCF9F6; --cm-accent: #E29A08;
            --cm-label: #1A1A17; --cm-halo: rgba(255,255,255,.9); --cm-hatch: #D5CEC0;
        }
        .dark .cm-choropleth {
            --cm-b0: #1A1E16; --cm-b1: #123A22; --cm-b2: #1B5B33; --cm-b3: #268049; --cm-b4: #2E9250; --cm-b5: #4FB874;
            --cm-stroke: #0A0C09; --cm-accent: #E9A81E;
            --cm-label: #F3EFE7; --cm-halo: rgba(0,0,0,.75); --cm-hatch: #39402F;
        }
        .cm-choropleth svg { width: 100%; height: auto; display: block; }
        .cm-region { cursor: pointer; outline: none; }
        .cm-region path { stroke: var(--cm-stroke); stroke-width: 2.5; transition: fill .12s ease; }
        .cm-region:hover path, .cm-region:focus path, .cm-region:focus-visible path {
            stroke: var(--cm-accent); stroke-width: 5; stroke-dasharray: none;
        }
        .cm-label { font-size: 40px; font-weight: 700; text-anchor: middle; dominant-baseline: central;
            fill: var(--cm-label); stroke: var(--cm-halo); stroke-width: 5; paint-order: stroke; pointer-events: none; }
        @media (prefers-reduced-motion: reduce) { .cm-region path { transition: none; } }
    </style>

    {{-- viewBox padded by 4 on each side so the 2.5/5px strokes are not clipped. --}}
    <svg viewBox="-4 -4 676.2 1008" role="group" aria-label="{{ $cmIsFr ? 'Carte du Cameroun : fiches artisans par région' : 'Map of Cameroon: artisan records by region' }}" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <pattern id="cm-hatch" width="8" height="8" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                <rect width="8" height="8" fill="var(--cm-b0)"/>
                <line x1="0" y1="0" x2="0" y2="8" stroke="var(--cm-hatch)" stroke-width="3"/>
            </pattern>
        </defs>
        @php($m = $cmMap('EN'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M495 271.3 L504.5 269.4 L520 269 L550.6 276.4 L563 270.1 L570 272.5 L575.7 270.4 L580.2 272.3 L587.2 270.9 L604.6 275.4 L616 273.7 L624.4 270.5 L609.1 260.9 L601.6 251 L592.3 242.7 L585.6 227.9 L578.1 223 L578.2 212.3 L571.7 201.1 L573 190.9 L567.9 173.4 L570.4 146.3 L577.3 133.6 L573.2 128.5 L571.6 123.2 L571.3 120 L575.1 112.7 L569.4 103.6 L572.4 93.9 L566.2 84 L560.9 84.8 L557.7 82.3 L556.7 59.7 L552.6 49.9 L551 40.2 L544.7 37.6 L539.9 31.3 L534.7 30.7 L532.2 27.5 L527.7 26.5 L526.8 13.3 L517.9 0.5 L485.4 0 L495.2 50.1 L494.2 59.6 L498.2 62.3 L519.3 63.8 L530.3 77.2 L536.4 79 L533.5 86.4 L534.4 88.8 L532.2 91.7 L534.6 98.3 L530.6 105.8 L531.2 113.6 L526.2 118.5 L533.9 125.9 L533.6 131.9 L530.8 136.7 L517.9 141.2 L514.8 146.2 L502.2 151.5 L491.7 159.2 L486.1 159.9 L477 155.1 L475.2 161.1 L471.3 164.5 L460.3 180.8 L452.1 184.2 L449.4 195 L444 203.1 L442 209.4 L437.4 214.5 L439.3 226.6 L436.5 230.9 L435 244.1 L430.7 253.9 L425.7 258.5 L426.7 262.8 L430.2 264.8 L449.6 260 L447.8 255 L448.5 251.9 L450.9 250.5 L472.4 252.6 L483.7 262.8 L493.8 264.8 L495 271.3Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="522.3" y="164.8" class="cm-label">EN</text>
        </a>
        @php($m = $cmMap('NO'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M425.7 258.5 L418.6 260.1 L413.2 266.5 L416 273.3 L411.9 277.8 L412.2 281.4 L416.9 285 L413.6 290.8 L412.3 304.9 L409.6 309.7 L406.6 311.4 L394.7 312.7 L385.4 320.8 L378.7 324.3 L383.8 327.5 L381.6 343.6 L382.7 349.4 L380.5 351.1 L378.7 358.2 L373.5 364.4 L374 376.9 L367.3 379.4 L364 387.4 L355.1 392.4 L346 390.5 L339.9 402.2 L324.6 406.7 L327.7 431.6 L345.9 448.8 L358.4 451.8 L361 456.8 L359.2 459.6 L359.9 466.3 L370.2 471.5 L391.4 475.8 L401.3 480.8 L412.5 478.8 L416.5 471.6 L422.7 471.4 L436 481.9 L447.1 475.7 L453.7 476.4 L464 474.1 L467.6 474.6 L467.9 479.7 L469.7 481.1 L487.4 483 L489.5 488.2 L495.7 494 L496.3 497.2 L505.8 506.1 L507.2 518.2 L513.8 519.2 L518.8 517.9 L522.7 520.1 L525.4 525.7 L535.3 531.2 L540.1 530.6 L564.8 517.9 L572.1 517.2 L581.4 508.7 L586.1 508.8 L601.1 495.9 L612.2 482.6 L614.9 477.5 L616.7 466.3 L616.5 465 L609.5 462.9 L605.3 456.7 L599.4 441.4 L599.2 436.4 L590.1 418.3 L583.5 393.7 L580.4 389.9 L573.3 386.3 L564.5 385.6 L562.1 380.5 L558.2 379 L556 374.3 L550.1 375.5 L544.2 373.5 L531 360.3 L522.5 354.9 L519.5 348.6 L511 340.1 L511.2 332.5 L504.4 325.5 L500.4 324.1 L480.2 301 L481.7 292.2 L493.8 277.6 L495 271.3 L493.8 264.8 L483.7 262.8 L473.5 253 L466.7 251.6 L450.9 250.5 L448.5 251.9 L447.8 255 L449.6 260 L438.7 263.6 L428.5 264.5 L425.7 258.5Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="469.3" y="392.3" class="cm-label">NO</text>
        </a>
        @php($m = $cmMap('AD'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M379.2 620.9 L451.9 620.7 L463.6 619.9 L475.2 614.6 L486 616.9 L499 615.5 L507.2 613 L509.7 610.1 L513.9 612.2 L517.9 611.5 L526.2 604 L542.9 597.1 L547.5 590.8 L549.3 582.7 L562.2 554.9 L570.6 551.8 L578.2 529.5 L583.7 520.7 L583.6 512.6 L586.1 508.8 L581.4 508.7 L572.1 517.2 L564.8 517.9 L540.1 530.6 L535.3 531.2 L525.4 525.7 L522.7 520.1 L518.8 517.9 L513.8 519.2 L507.2 518.2 L505.8 506.1 L496.3 497.2 L495.7 494 L489.5 488.2 L487.4 483 L469.7 481.1 L467.9 479.7 L467.6 474.6 L464 474.1 L453.7 476.4 L447.1 475.7 L436 481.9 L422.7 471.4 L416.5 471.6 L412.5 478.8 L401.3 480.8 L391.4 475.8 L370.2 471.5 L359.9 466.3 L359.2 459.6 L361 456.8 L358.4 451.8 L345.9 448.8 L327.7 431.6 L326.5 428.8 L322.4 436.7 L322.7 446.8 L312.8 459 L307.5 470.4 L305.9 475.3 L307.1 486.3 L300.8 488.8 L289.1 502 L287.1 510.4 L290.5 526 L280.4 527.4 L272.1 534.4 L270.5 540.1 L268.3 540.6 L266 544.6 L269.4 549.4 L267.6 551.5 L268.1 555.5 L262.5 566.4 L254.9 568.3 L254.3 573.7 L246.7 575.5 L242.6 573.4 L234.3 572.7 L235.5 586.9 L234.1 593.8 L242.6 597.1 L241.7 607.9 L244.2 613.5 L243.2 619.3 L272.6 612.5 L285.4 602.1 L294.8 600.5 L300.7 596.3 L304.7 601.4 L312.2 605.5 L316.5 605.7 L326.8 601.4 L329.9 602.4 L342 611 L348.7 620.4 L379.2 620.9Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="394.9" y="538.1" class="cm-label">AD</text>
        </a>
        @php($m = $cmMap('NW'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M234.3 572.7 L231.3 570.9 L227.9 572.7 L225.7 571.8 L225.5 561.2 L223.2 557.5 L215.3 559.6 L211 558 L206.5 550 L201.9 548 L201.2 545.1 L203.5 538.1 L194.7 534.4 L188.2 528.4 L185.1 528.2 L183.1 526.5 L182.5 520.5 L180.6 518.2 L177.4 537.3 L173.6 540.1 L149.1 541.7 L146 536.7 L145.9 530.9 L143.6 528.9 L121 551.3 L111.5 551.7 L105.5 573.8 L110.6 584.2 L113 593.6 L113.2 605.7 L111.2 608.4 L107.3 608 L94.7 616.2 L96.9 621.6 L95.6 627 L102.7 633.2 L107.2 632.7 L112 638.5 L124 642.5 L127.6 640.3 L139 642.7 L146.1 638.8 L152.2 640.2 L154.7 643.7 L157.5 642.6 L158.8 636.7 L182.9 634 L191 617.1 L194.4 616.2 L202.7 618.6 L213.3 614.1 L217.1 610.1 L218 598.1 L228.3 598.7 L234.1 593.8 L235.5 586.9 L234.3 572.7Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="169.7" y="584.4" class="cm-label">NW</text>
        </a>
        @php($m = $cmMap('OU'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M243.2 619.3 L244.2 613.5 L241.7 607.9 L243.1 599.2 L242 596.2 L234.1 593.8 L228.3 598.7 L218 598.1 L218.4 603.3 L215.3 613 L202.7 618.6 L194.4 616.2 L191 617.1 L182.9 634 L158.8 636.7 L156.8 643.4 L154.7 643.7 L152.2 640.2 L146.1 638.8 L139 642.7 L140.3 647.7 L136 649.2 L127.1 656.8 L126 664.4 L122.3 673.7 L119 676.4 L118 683.5 L120.3 684.4 L124.8 682.7 L128.7 685.8 L130.9 692.9 L129.6 696 L131.6 700.5 L135.7 703 L140.1 713.9 L142.8 714.1 L145.3 708.9 L150.4 705.3 L152.6 699.4 L163.8 700.3 L163.9 703.6 L168.4 712.8 L170.6 711.9 L172.5 715 L178.3 716.3 L183.2 716.5 L188.9 713.8 L196.9 709.1 L199.3 705.6 L207 711.2 L212.8 711 L217.1 695.8 L219.8 691.9 L223.2 690.5 L223.3 679.6 L232.6 671.4 L231.5 667.7 L234.6 662.5 L233.5 652 L235.5 646.6 L242 640.1 L239.6 623.3 L243.2 619.3Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="182.7" y="664.8" class="cm-label">OU</text>
        </a>
        @php($m = $cmMap('LT'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M173.8 715.3 L170.6 711.9 L168.4 712.8 L162.4 699.4 L155.9 700.7 L152.6 699.4 L150.4 705.3 L145.3 708.9 L142.8 714.1 L140.1 713.9 L135.7 703 L131.6 700.5 L129.6 696 L130.9 692.9 L128.7 685.8 L124.8 682.7 L120.3 684.4 L118 683.5 L119 676.4 L115.6 676.6 L112.9 681 L109 681.4 L110.7 689.2 L115.2 694.8 L117.8 702.1 L113.9 702 L112.2 704.1 L110.4 709.2 L113.1 714.5 L105.6 721.8 L104.9 728.5 L99.4 734.2 L99.1 738.4 L93.9 737.8 L88.3 746.9 L87.9 754.1 L89.6 757.7 L83.1 767 L82.3 773.9 L90.1 775.5 L92.6 780.4 L89 784.8 L82.9 787.8 L78.3 797.5 L72.5 802.4 L79.6 804.8 L84.2 802.6 L82.2 801.1 L83.4 796.3 L86.7 798.3 L89.5 797.8 L88.7 794.5 L84.1 789.7 L86.4 786.5 L86.4 789.1 L89.4 792.7 L94.3 794.4 L97.5 793.5 L104.5 787.4 L97.9 799.3 L109.6 800.5 L102.3 802.2 L109.2 810.9 L104.7 808 L101.2 809.5 L97.7 806.8 L95.1 811.8 L90.8 811.6 L99.8 826.5 L99.4 834.2 L119.2 853.8 L121.6 858.7 L127.1 856.5 L132.6 850.7 L134.6 839.6 L140.7 831.3 L147.5 829 L153.8 834 L159.3 829.9 L162.5 815.4 L165.7 811.3 L170.8 809.5 L183.4 817.6 L187.1 812.6 L187.8 799.2 L191.3 792.8 L199.2 788.7 L202.1 788.9 L222.4 781.6 L227.6 777.4 L227.3 774.6 L218.3 766.7 L213.3 758.9 L210.3 749.5 L210.7 740.7 L200.7 740.7 L196.3 752.1 L194.9 752.8 L192.1 748.8 L182.2 757.7 L169.5 751.8 L165.5 751.9 L164.9 747.3 L172.9 736.6 L173.9 732 L171.3 728.8 L162.8 734.1 L161.6 730.9 L162.7 726.5 L170.4 722.1 L173.6 718 L173.8 715.3Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="134.1" y="760.7" class="cm-label">LT</text>
        </a>
        @php($m = $cmMap('CE'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M152.8 833.8 L151.1 836.6 L155.5 851 L157.5 851.8 L196.6 840.6 L201.9 840.8 L205.1 846.7 L212.5 844.3 L215.5 846.5 L220.2 845.7 L221.8 846.9 L224.5 850.1 L227 860.4 L230.7 862 L230.9 865.7 L236 870.3 L247.9 870.6 L252.5 869.2 L261.3 870.6 L268.9 869.3 L278.5 864.3 L279.8 858 L283.1 853.1 L293.2 848.2 L295.8 842.8 L315.6 851.6 L316.1 856.5 L323.9 854.2 L328.3 840.2 L339.7 831.4 L344.9 824.2 L348.5 825.1 L351.9 831.8 L354 833.3 L355.4 832.2 L357.5 828 L357.5 815.8 L350.4 812 L348.9 806.9 L356.2 801.6 L363.1 799.4 L372.8 791.5 L373.3 787.8 L370.8 783.7 L374.6 773.7 L374.3 764.7 L377.9 759.5 L379.4 750.3 L387.1 744.2 L407.5 745.2 L409.8 738.1 L411.3 723.9 L407.2 712.5 L394.2 709.3 L395.4 702.7 L394.4 700.3 L375.6 681.7 L388.1 679.5 L396 662.7 L390 647.1 L379.5 630.4 L379.2 620.9 L348.7 620.4 L342 611 L329.9 602.4 L326.8 601.4 L316.5 605.7 L312.2 605.5 L304.7 601.4 L300.7 596.3 L294.8 600.5 L285.4 602.1 L272.6 612.5 L243.2 619.3 L240.2 621.4 L239.7 630.2 L242.2 638.4 L240.7 642.3 L234.7 647.7 L233.2 653.9 L234.6 662.5 L231.5 667.7 L232.6 671.4 L223.3 679.6 L223.2 690.5 L219.8 691.9 L217.1 695.8 L212.8 711 L207 711.2 L199.3 705.6 L196.9 709.1 L183.2 716.5 L173.8 715.3 L173.6 718 L170.4 722.1 L162.7 726.5 L161.3 733 L164.1 733.6 L171.3 728.8 L173.5 730 L172.9 736.6 L167 743.5 L164.8 750.4 L166.3 752.5 L169.5 751.8 L180.8 757.7 L192.1 748.8 L194.9 752.8 L196.3 752.1 L200.7 740.7 L210.7 740.7 L210.3 749.5 L213.3 758.9 L218.3 766.7 L227.3 774.6 L227.6 777.4 L222.4 781.6 L202.1 788.9 L199.2 788.7 L191.3 792.8 L187.8 799.2 L187.1 812.6 L183.4 817.6 L170.8 809.5 L165.7 811.3 L162.5 815.4 L159.3 829.9 L152.8 833.8Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="263.2" y="755.2" class="cm-label">CE</text>
        </a>
        @php($m = $cmMap('ES'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M354 833.3 L353.1 846 L368.6 865.2 L371.6 875.1 L371.9 887.4 L378.3 893.1 L400.9 897.5 L409.8 895.9 L416.4 902.6 L418 902.4 L418.4 897.4 L422.4 900.2 L432.9 902.8 L435.5 902.5 L443.5 893.3 L446.2 892.2 L449.7 955.1 L499.6 955.3 L505.1 953.8 L516.5 955.7 L527.6 952.1 L530.2 952.9 L534.3 957.9 L545.6 964.2 L548 964.9 L554.2 962.1 L556.9 965 L556.2 968.3 L558.3 969.8 L564.8 968.7 L574.5 971.5 L577.6 968.5 L584.8 967.3 L594.7 977.4 L602.4 973.4 L608.1 972.6 L630.5 978.3 L642.7 988.5 L654 990.8 L653.2 994.7 L654.7 1000 L657.4 999.9 L661 997 L663.9 997.5 L663.7 988.6 L656.5 971.4 L658.6 966.6 L659.4 953.8 L661 952.7 L664.6 953.9 L668.1 952 L668.2 950.1 L664.2 939.1 L658.3 929.1 L658.7 908.5 L656.2 901.5 L659.5 897.3 L657.9 890.5 L655.3 886.2 L652.2 883.2 L648.6 882.8 L641.3 874.2 L634.6 874 L587.2 821.1 L573.2 797.2 L570 795.4 L580.6 790.5 L573.8 783.4 L572.7 769.5 L565.9 758.7 L548.4 748.8 L540.8 739.9 L539.6 721 L536.5 704.6 L538 698.6 L536.9 692.7 L533.6 688.8 L527.1 686.8 L523.8 683.4 L532.3 663.3 L529.4 654.6 L532.1 645.9 L531.5 628 L529.7 626.8 L524.7 628.7 L520.4 627.2 L514.7 617.6 L515.3 612.4 L510.9 610 L507.2 613 L499 615.5 L482.3 616.9 L475.2 614.6 L463.6 619.9 L379.2 620.9 L379.5 630.4 L390 647.1 L396 662.7 L388.1 679.5 L375.6 681.7 L394.4 700.3 L395.4 702.7 L394.2 709.3 L407.2 712.5 L411.3 723.9 L409.8 738.1 L407.5 745.2 L387.1 744.2 L379.4 750.3 L377.9 759.5 L374.3 764.7 L374.6 773.7 L370.8 783.7 L373.3 787.8 L372.8 791.5 L363.1 799.4 L356.2 801.6 L348.9 806.9 L350.4 812 L357.5 815.8 L357.5 828 L354 833.3Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="510.5" y="828.3" class="cm-label">ES</text>
        </a>
        @php($m = $cmMap('SW'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M105.5 573.8 L101.7 575 L96.1 573.5 L91.3 580.8 L84 580.5 L80.8 590.8 L79.3 592.3 L73.1 591.1 L73.5 594.4 L67.9 605.7 L53 613.6 L35.8 628.5 L34.8 631.8 L30.2 634.4 L30.4 642.5 L27.4 646 L32.3 651.3 L34.3 656.2 L26.6 682.8 L26.8 692.3 L24.1 697.6 L19.9 700.8 L12.5 715.2 L9.3 717.3 L11.1 722.6 L6 724.9 L4.9 735 L1.6 736.6 L0.3 739.5 L0.4 742.3 L2.7 743.6 L0 746.7 L1.2 751.2 L14.2 752.8 L19.9 750.2 L14.4 745.8 L13.6 738.3 L11.4 735.2 L12 732.3 L18.6 745.2 L24 748.6 L25.9 745.2 L35.1 750.9 L33.3 760.7 L36.3 765.3 L36 770.4 L42.9 778 L42.2 786.8 L53.8 794.5 L60.7 794.4 L64 799.3 L72.1 793.8 L69.1 797.8 L72.5 802.4 L78.3 797.5 L82.9 787.8 L89 784.8 L92.6 780.4 L90.1 775.5 L82.3 773.9 L83.1 767 L89.6 757.7 L87.9 754.1 L88.3 746.9 L93.9 737.8 L99.1 738.4 L99.4 734.2 L104.9 728.5 L105.6 721.8 L113.1 714.5 L110.4 709.2 L112.2 704.1 L113.9 702 L117.8 702.1 L115.2 694.8 L110.7 689.2 L109 681.4 L112.9 681 L115.6 676.6 L119 676.4 L122.3 673.7 L127.1 656.8 L136 649.2 L140.3 647.7 L139 642.7 L127.6 640.3 L122.7 642.4 L112 638.5 L107.2 632.7 L102.7 633.2 L95.6 627 L96.9 621.6 L94.7 616.2 L107.3 608 L111.2 608.4 L113.2 605.7 L113 593.6 L110.6 584.2 L105.5 573.8Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="69.2" y="695.1" class="cm-label">SW</text>
        </a>
        @php($m = $cmMap('SU'))
        <a href="{{ $m['href'] }}" class="cm-region" tabindex="0" aria-label="{{ $m['aria'] }}" data-cm-name="{{ $m['name'] }}" data-cm-records="{{ $m['records'] }}" data-cm-published="{{ $m['published'] }}" data-cm-share="{{ $m['share'] }}">
            <title>{{ $m['aria'] }}</title>
            <path d="M152.8 833.8 L152.1 831.5 L147.5 829 L140.7 831.3 L134.6 839.6 L132.6 850.7 L127.1 856.5 L121.6 858.7 L127.7 874.9 L126.9 879.4 L121.9 888.4 L114.7 925.4 L115.9 945 L117.4 948.6 L122.3 952.4 L139.9 956.1 L233.7 953.8 L248.1 955.3 L249.2 945.6 L251.2 944 L275.6 942.9 L285.4 946.4 L312.5 945.3 L320.8 945.7 L325.2 947.7 L331.9 946.1 L344.4 948.2 L355.6 947.4 L368.9 950.1 L375.1 947.9 L379.3 949.5 L385.4 948.1 L396.9 948.7 L402.4 945.5 L406.1 945.7 L414.2 947.8 L417.1 955.3 L449.7 955.1 L446.2 892.2 L443.5 893.3 L435.5 902.5 L429.3 902.7 L418.4 897.4 L418 902.4 L416.4 902.6 L409.8 895.9 L400.9 897.5 L389.8 894.4 L382.3 894.3 L374.2 890.4 L371.9 887.4 L370.8 870.5 L364.7 858.8 L357.1 852.3 L353.1 846 L354 833.3 L347.2 824.2 L344.9 824.2 L339.7 831.4 L328.3 840.2 L325.7 850.2 L322.3 855.3 L316.1 856.5 L315.6 851.6 L295.8 842.8 L293.2 848.2 L283.1 853.1 L279.8 858 L278.5 864.3 L268.9 869.3 L261.3 870.6 L252.5 869.2 L247.9 870.6 L236 870.3 L230.9 865.7 L230.7 862 L227 860.4 L224.5 850.1 L221.8 846.9 L220.2 845.7 L215.5 846.5 L212.5 844.3 L205.1 846.7 L201.9 840.8 L196.6 840.6 L157.5 851.8 L155.5 851 L151.1 836.6 L152.8 833.8Z" fill="{{ $m['fill'] }}"{!! $m['dash'] !!} />
            <text x="282" y="887.1" class="cm-label">SU</text>
        </a>
    </svg>

    {{-- Hover / focus readout. Also the visible partner of each region's <title>. --}}
    <p class="cm-readout mt-2 min-h-[32px] text-[11.5px] leading-snug text-[#3B382F] dark:text-[#B4B5A6]" aria-live="polite">
        {{ $cmIsFr ? 'Survolez une région pour voir ses chiffres.' : 'Hover a region to see its figures.' }}
    </p>

    {{-- Legend --}}
    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1.5">
        <span class="text-[10px] font-semibold uppercase tracking-wide text-[#8A857A] dark:text-[#868778]">{{ $cmIsFr ? 'Fiches' : 'Records' }}</span>
        <span class="flex items-center gap-1">
            <span class="w-[13px] h-[13px] rounded-[3px] border border-dashed border-[#8A857A] dark:border-[#868778]" style="background: var(--cm-b0)"></span>
            <span class="text-[10.5px] text-[#6F6B60] dark:text-[#868778]">0</span>
        </span>
        @foreach($cmLegend as $cmI => $cmRange)
        <span class="flex items-center gap-1">
            <span class="w-[13px] h-[13px] rounded-[3px] border border-[#E7E2D8] dark:border-[#262B21]" style="background: var(--cm-b{{ $cmI }})"></span>
            <span class="text-[10.5px] text-[#6F6B60] dark:text-[#868778]">{{ $cmRange }}</span>
        </span>
        @endforeach
        @if($cmRows->count() < 10)
        <span class="flex items-center gap-1">
            <svg width="13" height="13" aria-hidden="true"><rect width="13" height="13" rx="3" fill="url(#cm-hatch)"/></svg>
            <span class="text-[10.5px] text-[#6F6B60] dark:text-[#868778]">{{ $cmIsFr ? 'non mesuré' : 'not measured' }}</span>
        </span>
        @endif
    </div>

    {{-- What is actually being counted. Do not shorten this into "artisans". --}}
    <p class="mt-2.5 text-[11px] leading-relaxed text-[#6F6B60] dark:text-[#868778]">
        @if($cmIsFr)
            La teinte suit le nombre de <strong class="font-semibold text-[#3B382F] dark:text-[#B4B5A6]">fiches artisans détenues</strong> par région —
            {{ number_format($cmTotal, 0, ',', ' ') }} au total, dont
            <strong class="font-semibold text-[#157A43] dark:text-[#339B56]">{{ number_format($cmTotalPub, 0, ',', ' ') }} publiée{{ $cmTotalPub > 1 ? 's' : '' }}</strong>
            et donc visibles du public. Les autres sont des fiches enregistrées non publiées (imports SIARC non encore revendiqués).
            @if($cmUnassigned > 0) {{ $cmUnassigned }} fiche{{ $cmUnassigned > 1 ? 's' : '' }} sans région ne figure{{ $cmUnassigned > 1 ? 'nt' : '' }} pas sur la carte.@endif
        @else
            Shading follows the number of <strong class="font-semibold text-[#3B382F] dark:text-[#B4B5A6]">artisan records held</strong> per region —
            {{ number_format($cmTotal) }} in total, of which
            <strong class="font-semibold text-[#157A43] dark:text-[#339B56]">{{ number_format($cmTotalPub) }} {{ $cmTotalPub === 1 ? 'is' : 'are' }} published</strong>
            and therefore publicly visible. The rest are stored but unpublished records (unclaimed SIARC imports).
            @if($cmUnassigned > 0) {{ $cmUnassigned }} record{{ $cmUnassigned > 1 ? 's' : '' }} with no region {{ $cmUnassigned > 1 ? 'are' : 'is' }} not on the map.@endif
        @endif
    </p>
    <p class="mt-1 text-[9.5px] text-[#8A857A] dark:text-[#868778]">{{ $cmIsFr ? 'Contours' : 'Boundaries' }}: geoBoundaries (gbOpen CMR ADM1), CC BY.</p>
</div>

<script>
(function () {
    document.querySelectorAll('.cm-choropleth').forEach(function (root) {
        var out = root.querySelector('.cm-readout');
        var base = root.getAttribute('data-cm-default');
        var fr = document.documentElement.lang !== 'en';
        function show(a) {
            var n = a.getAttribute('data-cm-records'), p = a.getAttribute('data-cm-published');
            out.textContent = a.getAttribute('data-cm-name') + ' — ' + n + (fr ? ' fiche' + (n > 1 ? 's' : '') : ' record' + (n == 1 ? '' : 's'))
                + ' (' + a.getAttribute('data-cm-share') + '), ' + p + (fr ? ' publiée' + (p > 1 ? 's' : '') : ' published');
        }
        function clear() { out.textContent = base; }
        root.querySelectorAll('.cm-region').forEach(function (a) {
            a.addEventListener('mouseenter', function () { show(a); });
            a.addEventListener('mouseleave', clear);
            a.addEventListener('focus', function () { show(a); });
            a.addEventListener('blur', clear);
            a.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                    e.preventDefault();
                    window.location.href = a.getAttribute('href');
                }
            });
        });
    });
})();
</script>
