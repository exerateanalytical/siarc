<?php

/*
|--------------------------------------------------------------------------
| Certificate classification scheme
|--------------------------------------------------------------------------
|
| The platform issues a family of documents rather than one certificate, and a
| reader who is handed two of them has to be able to tell, before reading a
| word, that they belong together and which member each one is. That is the
| whole job of the left-edge band: colour and icon carry the type, the shared
| geometry carries the family.
|
| The scheme is declared here, in one place, rather than inside each view. Four
| of these ten documents exist today and six do not; keeping the table whole
| means the band for a provenance or export certificate is already drawn and
| already distinct on the day someone builds the page, instead of being invented
| then — which is how two documents end up the same colour.
|
| On the colour values. Every band carries cream or white type set small, so
| the constraint is not "which blue looks best" but "which blue still reads at
| 11px under a hairline of gold". They were checked against the rendered sheets,
| not picked off a swatch: the mid-tone versions of burgundy, teal and bronze
| all failed that check and were darkened. The accent is the hairline and the
| icon ring — a light tint of the same hue family, or gold where the type's own
| identity is gold, which is why the accents are not simply one shared cream.
|
| `icon` names a drawing in pages/partials/certificate-band.blade.php. They are
| hand-drawn there rather than pulled from the icon library on purpose: the set
| shipped with this build has no key, no registry ledger, no cargo ship and no
| balance scale, and a band whose icon is "roughly the right idea" tells the
| reader nothing.
|
*/

return [

    'COA' => [
        'colour' => '#1B3A93',   // royal blue
        'accent' => '#A8C2F5',
        'icon'   => 'shield',
        'name'   => [
            'fr' => "Certificat d'authenticité",
            'en' => 'Certificate of Authenticity',
        ],
    ],

    'PRC' => [
        // Gold at its usual brightness cannot carry cream type, so the band
        // takes the deep antique end of the same gold the certificates already
        // use and puts the bright value in the accent, where it belongs.
        'colour' => '#8A6410',
        'accent' => '#F4D98C',
        'icon'   => 'registry',
        'name'   => [
            'fr' => "Certificat d'enregistrement produit",
            'en' => 'Product Registration Certificate',
        ],
    ],

    'OTC' => [
        'colour' => '#0B6B45',   // emerald
        'accent' => '#8FE0BC',
        'icon'   => 'key',
        'name'   => [
            'fr' => 'Certificat de transfert de propriété',
            'en' => 'Ownership Transfer Certificate',
        ],
    ],

    'AVC' => [
        'colour' => '#4A2A7A',   // purple
        'accent' => '#CDB4F0',
        'icon'   => 'verified-artisan',
        'name'   => [
            'fr' => "Certificat de vérification d'artisan",
            'en' => 'Artisan Verification Certificate',
        ],
    ],

    /* ── Declared, drawn, and not yet built ──────────────────────────────── */

    'PPC' => [
        'colour' => '#6E1327',   // burgundy
        'accent' => '#E9AEBC',
        'icon'   => 'timeline-scroll',
        'name'   => [
            'fr' => 'Certificat de provenance',
            'en' => 'Provenance Certificate',
        ],
    ],

    'EAC' => [
        'colour' => '#10285C',   // navy
        'accent' => '#9FB8E8',
        'icon'   => 'export',
        'name'   => [
            'fr' => "Certificat d'exportation",
            'en' => 'Export Certificate',
        ],
    ],

    'EC' => [
        'colour' => '#9B1224',   // crimson
        'accent' => '#F3B0B6',
        'icon'   => 'museum',
        'name'   => [
            'fr' => "Certificat d'exposition",
            'en' => 'Exhibition Certificate',
        ],
    ],

    'RC' => [
        'colour' => '#0B5C63',   // teal
        'accent' => '#8FDCE2',
        'icon'   => 'restoration',
        'name'   => [
            'fr' => 'Certificat de restauration',
            'en' => 'Restoration Certificate',
        ],
    ],

    'VAC' => [
        'colour' => '#7A4A18',   // bronze
        'accent' => '#E2BE8C',
        'icon'   => 'balance',
        'name'   => [
            'fr' => "Certificat d'évaluation",
            'en' => 'Valuation Certificate',
        ],
    ],

    /*
     * The workshop verification certificate. The eleventh type, and the first
     * one about a place rather than an object, which is why it takes the deep
     * emerald of its artwork rather than another value of the burgundy-navy
     * range the object documents share: a reader holding a WVC beside an OTC
     * should see two different colours, and the darker green is far enough from
     * OTC's emerald to stay legible as its own.
     *
     * The icon is the museum front — a building — reused deliberately rather
     * than invented. certificate-band.blade.php draws the ten icons itself and
     * is not a file this pass may edit, so a new key would render an empty ring,
     * which tells a reader less than a shared building glyph does. The colour
     * still separates the two, and the day someone draws a bench-and-tools
     * glyph, only this line changes.
     */
    'WVC' => [
        'colour' => '#0E4D2A',   // deep emerald
        'accent' => '#E7C878',
        'icon'   => 'museum',
        'name'   => [
            'fr' => "Certificat de vérification d'atelier",
            'en' => 'Workshop Verification Certificate',
        ],
    ],

    'DPP' => [
        // Black and gold. The band body is the near-black; the gold is the
        // accent, so this one reads as the darkest member of the set without
        // needing a second colour slot the other nine do not have.
        'colour' => '#14120C',
        'accent' => '#E5B54A',
        'icon'   => 'microchip',
        'name'   => [
            'fr' => 'Passeport numérique du produit',
            'en' => 'Digital Product Passport',
        ],
    ],

];
