{{--
    The classification band that runs down the left edge of every certificate
    the platform issues.

    It exists because these documents are a family, not a set of one-offs. A
    buyer who has seen a certificate of authenticity and is then handed a
    transfer deed should recognise the second as the same kind of thing before
    reading a word of it, and should see in the same glance that it is a
    different member. Shared geometry does the first job; colour and icon do the
    second. Nothing else on the sheets is shared verbatim between them, so this
    is the only thing carrying the resemblance, which is why the width and the
    vertical rhythm are fixed here and not left to each view.

    Where it sits. Every certificate is drawn at a measured 1024px canvas whose
    column boundaries came off the artwork, and squeezing those columns to make
    room would quietly change four documents. So the band is positioned over the
    frame margin instead — the dark bezel and the blank gutter that every sheet
    already leaves outside its first card. 40px is what fits there on the
    tightest of the four (the authenticity sheet's first card begins at x 45),
    and it is used on all of them rather than letting each take what it can
    spare, because a band that is 47px on one sheet and 40px on the next stops
    reading as the same band.

    All inline SVG and CSS, as with the ornament and security sheets: a strict
    CSP forbids external requests, and the sheets are scaled by a transform, so
    raster artwork would be soft at every size but one.

    The ten icons are hand-drawn below rather than pulled from the icon library
    this build ships. That library has no key, no registry ledger, no cargo
    ship, no balance scale and no restoration tools, and an icon that is roughly
    the right idea tells the reader nothing — the icon is half of what
    distinguishes one document from another here.

    Parameters:
      $code    — one of the keys in config/certificate_types.php. An unknown
                 code renders nothing at all, rather than a blank band: a
                 document wearing an unclassified band is worse than one wearing
                 none, because it looks classified.
      $lang    — 'fr' or 'en'.
      $height  — optional. Omitted, the band stretches to its positioned
                 ancestor, which is what the certificates want. Given, it draws
                 at a fixed height in normal flow, which is what a preview or a
                 documentation page wants.
--}}
@php
    $cbType = config('certificate_types.' . ($code ?? ''));
    $cbLang = ($lang ?? 'fr') === 'en' ? 'en' : 'fr';
@endphp

@if($cbType)
<style>
    /* The band's own measurements. They are declared as custom properties on
       the element rather than hard-coded into the rules so that a document with
       an unusually deep bezel can nudge the offset without forking the rest —
       the width deliberately cannot be nudged, and the test asserts it. */
    .cert-band-host { position: relative; }
    .cert-band {
        --cert-band-w: 40px;
        position: absolute; left: 0; top: 0; bottom: 0;
        width: var(--cert-band-w);
        z-index: 6;
        /* One colour with the ends darkened, rather than two declared colours.
           A shade computed in CSS would need color-mix(), which not every
           browser in this audience has, and a second hex per type is a second
           thing to get wrong ten times over. */
        background: linear-gradient(180deg, rgba(0,0,0,.30), rgba(0,0,0,0) 20%,
                                            rgba(0,0,0,0) 80%, rgba(0,0,0,.30)), var(--cb);
        /* The band takes the sheet's own corner radius on the left and squares
           off on the right, so it sits inside the frame on the rounded sheets
           and flush on the square ones without either view knowing. */
        border-radius: inherit;
        border-top-right-radius: 0; border-bottom-right-radius: 0;
        display: flex; flex-direction: column; align-items: center;
        padding: 22px 0 18px;
        overflow: hidden;
        pointer-events: none;
    }
    /* A hairline of the accent along the inner edge. This is what stops the
       band reading as a slab of colour dropped on the frame: it gives the sheet
       an edge to start at, the way the gold rule does everywhere else. */
    .cert-band::after {
        content: ''; position: absolute; top: 0; bottom: 0; right: 0; width: 1px;
        background: linear-gradient(180deg, transparent, var(--ca) 12%, var(--ca) 88%, transparent);
        opacity: .7;
    }

    .cert-band-ico {
        width: 26px; height: 26px; border-radius: 50%; flex: none;
        border: 1px solid var(--ca);
        display: flex; align-items: center; justify-content: center;
        color: var(--ca);
        background: rgba(255,255,255,.06);
    }
    .cert-band-ico svg { width: 15px; height: 15px; display: block; }

    /* Vertical type, set to read bottom-to-top the way a book spine does. The
       code is the largest thing on the band because it is the one part a reader
       is expected to quote back — "the AVC" — and it is set in the same weight
       and tracking on all ten. */
    .cert-band-code {
        writing-mode: vertical-rl; transform: rotate(180deg);
        margin-top: 13px;
        font-size: 19px; font-weight: 800; letter-spacing: .17em;
        color: #FFFFFF;
        text-shadow: 0 1px 2px rgba(0,0,0,.35);
    }
    .cert-band-rule {
        width: 14px; height: 1px; flex: none; margin-top: 13px;
        background: var(--ca); opacity: .85;
    }
    .cert-band-name {
        writing-mode: vertical-rl; transform: rotate(180deg);
        margin-top: 13px;
        font-size: 8.5px; font-weight: 600; letter-spacing: .155em;
        text-transform: uppercase;
        color: #FBF6EA; opacity: .92;
        white-space: nowrap;
    }
    .cert-band-foot { margin-top: auto; display: flex; flex-direction: column; align-items: center; gap: 9px; }
    .cert-band-foot svg { display: block; color: var(--ca); }

    @media print {
        /* Colour is the whole point of the band, so it must survive the print
           dialogue's default of dropping backgrounds. */
        .cert-band { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<div class="cert-band"
     data-cert-band="{{ $code }}"
     aria-label="{{ $code }} — {{ $cbType['name'][$cbLang] }}"
     style="--cb: {{ $cbType['colour'] }}; --ca: {{ $cbType['accent'] }};
            @isset($height) position: relative; height: {{ $height }}px; @endisset">

    <span class="cert-band-ico">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            @switch($cbType['icon'])

                {{-- COA. A shield with a check cut into it: the document asserts
                     that a thing is what it says it is, and nothing more. --}}
                @case('shield')
                    <path d="M12 2.6 20 5.4v5.9c0 4.8-3.3 8.2-8 9.9-4.7-1.7-8-5.1-8-9.9V5.4z"/>
                    <path d="M8.6 11.9 11 14.4l4.6-4.7"/>
                    @break

                {{-- PRC. A registry ledger: a bound book with ruled entries and
                     the stamp that closes one. --}}
                @case('registry')
                    <path d="M4.4 3.4h11.4a2 2 0 0 1 2 2v15.1H6.4a2 2 0 0 1-2-2z"/>
                    <path d="M4.4 17.1h13.4"/>
                    <path d="M7.6 7.3h6.8M7.6 10.5h6.8M7.6 13.6h4.2"/>
                    <path d="M19.6 12.4v6.2"/>
                    @break

                {{-- OTC. A key: the document is the handover of title, and a key
                     is the oldest picture of exactly that. --}}
                @case('key')
                    {{-- Drawn upright rather than on the diagonal. At the 15px
                         the band gives it, a diagonal key's bow and shaft
                         collapse into a circle on a stick, which reads as a
                         magnifier; standing it up keeps the teeth legible. --}}
                    <circle cx="12" cy="6.3" r="3.6"/>
                    <path d="M12 9.9v11.1"/>
                    <path d="M12 14.3h3.9M12 17.6h3.1"/>
                    @break

                {{-- AVC. A person with a check at the shoulder: it is the maker
                     who was checked, not the work. --}}
                @case('verified-artisan')
                    <circle cx="9.6" cy="6.9" r="3.4"/>
                    <path d="M3.3 19.6a6.3 6.3 0 0 1 10.4-4.4"/>
                    <circle cx="17.4" cy="17.1" r="4.3"/>
                    <path d="m15.4 17.1 1.6 1.7 3-3.3"/>
                    @break

                {{-- PPC. A scroll with the chain of holders marked along it. --}}
                @case('timeline-scroll')
                    <path d="M6.6 3.6h11a2 2 0 0 1 2 2v12.8a2 2 0 0 1-2 2h-11"/>
                    <path d="M6.6 3.6a2 2 0 0 0-2 2v12.8a2 2 0 0 0 2 2"/>
                    <path d="M8.6 12h8.4"/>
                    <circle cx="9.2" cy="12" r="1.1"/><circle cx="13" cy="12" r="1.1"/><circle cx="16.8" cy="12" r="1.1"/>
                    @break

                {{-- EAC. A globe with a cargo hull crossing its lower third. --}}
                @case('export')
                    <circle cx="12" cy="9.6" r="6.4"/>
                    <path d="M5.6 9.6h12.8M12 3.2c1.9 2 2.9 4.2 2.9 6.4s-1 4.4-2.9 6.4c-1.9-2-2.9-4.2-2.9-6.4s1-4.4 2.9-6.4z"/>
                    <path d="M3.2 18.1h17.6l-1.9 3.2H5.1z"/>
                    @break

                {{-- EC. A museum front: pediment, columns, plinth. --}}
                @case('museum')
                    <path d="M2.9 8.6 12 3.7l9.1 4.9"/>
                    <path d="M5.6 10.4v7.6M9.8 10.4v7.6M14.2 10.4v7.6M18.4 10.4v7.6"/>
                    <path d="M3.4 20.4h17.2"/>
                    @break

                {{-- RC. Restoration tools: a chisel crossed with a fine brush. --}}
                @case('restoration')
                    <path d="M4.2 19.8 13 11l2.4 2.4-8.8 8.8H4.2z" transform="translate(0,-1.4)"/>
                    <path d="M14.6 8.2 17 5.8a2.3 2.3 0 0 1 3.3 3.3l-2.4 2.4z"/>
                    <path d="M3.6 4.1 8 8.5"/>
                    <path d="M2.6 6.4 6.3 2.7"/>
                    @break

                {{-- VAC. A balance scale: a valuation is a weighing, and the
                     document should not pretend to be more decisive than that. --}}
                @case('balance')
                    <path d="M12 3.4v16.4M7.2 20.3h9.6"/>
                    <path d="M4.4 6.6h15.2"/>
                    <path d="M4.4 6.6 1.9 12.4h5z"/>
                    <path d="M19.6 6.6 17.1 12.4h5z"/>
                    @break

                {{-- DPP. A die with its pins: the passport is the record, and
                     the record lives in silicon somewhere. --}}
                @case('microchip')
                    <rect x="7.1" y="7.1" width="9.8" height="9.8" rx="1.6"/>
                    <rect x="10.4" y="10.4" width="3.2" height="3.2" rx="0.6"/>
                    <path d="M9.9 3.6v3.5M14.1 3.6v3.5M9.9 16.9v3.5M14.1 16.9v3.5"/>
                    <path d="M3.6 9.9h3.5M3.6 14.1h3.5M16.9 9.9h3.5M16.9 14.1h3.5"/>
                    @break
            @endswitch
        </svg>
    </span>

    <span class="cert-band-code">{{ $code }}</span>
    <span class="cert-band-rule"></span>
    <span class="cert-band-name">{{ $cbType['name'][$cbLang] }}</span>

    <span class="cert-band-foot">
        {{-- A diamond and a second hairline close the band, so it ends on a
             deliberate mark rather than simply running out at the bottom of the
             sheet. The same two marks on all ten. --}}
        <svg width="8" height="8" viewBox="0 0 10 10" aria-hidden="true">
            <path d="M5 0l5 5-5 5-5-5z" fill="currentColor"/>
        </svg>
        <svg width="14" height="1" viewBox="0 0 14 1" aria-hidden="true">
            <path d="M0 .5h14" stroke="currentColor" stroke-width="1"/>
        </svg>
    </span>
</div>
@endif
