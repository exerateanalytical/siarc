{{--
    Decorative artwork for the Certificate of Authenticity, kept out of the
    document itself so the certificate reads as structure rather than scenery.

    All of it is inline SVG rather than images: the sheet is drawn at 1024px and
    scaled to whatever the viewport allows, and raster ornament would soften at
    every size but one. It also prints crisply and costs no extra request.

    The artwork's border is not a continuous band — it is a heavy kente motif in
    each of the four corners, mirrored, with the long edges left plain. Building
    it that way rather than as a repeating strip is what makes the frame read
    the way the design does.
--}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <defs>
        {{-- Kente tile: nested diamonds with quarter-diamonds at the corners, so
             adjacent tiles join into the continuous lattice of the artwork. --}}
        <pattern id="coaKente" width="34" height="34" patternUnits="userSpaceOnUse">
            <rect width="34" height="34" fill="#FBF8F0"/>
            <path d="M17 0L34 17 17 34 0 17Z" fill="none" stroke="#C9942E" stroke-width="3.2"/>
            <path d="M17 5.5L28.5 17 17 28.5 5.5 17Z" fill="none" stroke="#14602D" stroke-width="3"/>
            <path d="M17 12l5 5-5 5-5-5z" fill="#C9942E"/>
            <path d="M0 0l7 7-7 7zM34 0l-7 7 7 7zM0 34l7-7-7-7zM34 34l-7-7 7-7z" fill="#14602D"/>
            <path d="M17 0l4 4-4 4-4-4zM17 34l4-4-4-4-4 4z" fill="#B4141B"/>
        </pattern>

        {{-- The same lattice in gold on green, for the footer band. --}}
        <pattern id="coaKenteDark" width="34" height="34" patternUnits="userSpaceOnUse">
            <rect width="34" height="34" fill="none"/>
            <path d="M17 0L34 17 17 34 0 17Z" fill="none" stroke="#C9942E" stroke-width="3.2" opacity=".55"/>
            <path d="M17 5.5L28.5 17 17 28.5 5.5 17Z" fill="none" stroke="#E0B04A" stroke-width="2.6" opacity=".38"/>
            <path d="M17 12l5 5-5 5-5-5z" fill="#C9942E" opacity=".6"/>
            <path d="M0 0l7 7-7 7zM34 0l-7 7 7 7zM0 34l7-7-7-7zM34 34l-7-7 7-7z" fill="#C9942E" opacity=".3"/>
        </pattern>

        {{-- Corner motif: the lattice clipped to a right triangle, edged with a
             green/gold diagonal and trailed by loose diamonds that fade along
             the plain part of the border. --}}
        <clipPath id="coaCornerTri"><path d="M0 0H156L0 116Z"/></clipPath>
        <g id="coaCorner">
            <g clip-path="url(#coaCornerTri)"><rect width="156" height="116" fill="url(#coaKente)"/></g>
            <path d="M156 0L0 116" stroke="#14602D" stroke-width="5" fill="none"/>
            <path d="M156 6L8 116" stroke="#C9942E" stroke-width="2.4" fill="none"/>
            <g fill="none" stroke="#C9942E" stroke-width="2.4">
                <path d="M172 9l7 7-7 7-7-7z"/>
                <path d="M196 7l5 5-5 5-5-5z"/>
            </g>
            <path d="M186 12l3.5 3.5-3.5 3.5-3.5-3.5z" fill="#B4141B"/>
        </g>

        {{-- Rosette edge for the wax seal: 44 teeth around the rim. --}}
        <g id="coaSealTeeth">
            @for($i = 0; $i < 44; $i++)
            <rect x="93" y="1" width="8" height="13" rx="2" fill="#B98526"
                  transform="rotate({{ round($i * (360 / 44), 3) }} 97 97)"/>
            @endfor
        </g>

        <linearGradient id="coaSealFace" x1="26%" y1="18%" x2="82%" y2="94%">
            <stop offset="0" stop-color="#F7E1A4"/>
            <stop offset="38%" stop-color="#E0B04A"/>
            <stop offset="72%" stop-color="#C9942E"/>
            <stop offset="100%" stop-color="#9C6E1B"/>
        </linearGradient>
        <linearGradient id="coaGoldRule" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#C9942E" stop-opacity="0"/>
            <stop offset="20%" stop-color="#C9942E"/>
            <stop offset="55%" stop-color="#F4D98C"/>
            <stop offset="85%" stop-color="#C9942E"/>
            <stop offset="1" stop-color="#C9942E" stop-opacity="0"/>
        </linearGradient>

        {{-- Arcs the seal's rim lettering rides on. --}}
        <path id="coaSealTop" d="M25 97a72 72 0 0 1 144 0" fill="none"/>
        <path id="coaSealBot" d="M28 97a69 69 0 0 0 138 0" fill="none"/>

        {{-- Cameroon, simplified to a silhouette at footer scale. --}}
        <path id="coaCameroon"
              d="M20 4l14 1 3 7 10 1 5 8-4 9 6 7-3 8 5 6-2 9-7 5-4 11-9 6-6-8-1-11-8-4-5-10-9-3 2-9-6-7 5-8 3-10 8-4z"/>
    </defs>
</svg>
