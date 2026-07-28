<?php

return [

    /*
     * The Certification Authority's signing key.
     *
     * Kept outside the repository and outside the web root. Create it with
     * `php artisan certificates:init-authority`; the platform issues
     * hash-only certificates until it exists rather than refusing to issue,
     * so a missing key never blocks an artisan.
     */
    'ca' => [
        'key_path' => env('CERTIFICATE_CA_KEY_PATH', storage_path('app/ca/ah237-ca.key')),
        'name'     => 'ArtisanHub237 Certification Authority',
    ],

    /*
     * Perceptual-fingerprint matching. Two images count as the same work when
     * the Hamming distance between their hashes is at or below this, over 64
     * bits. Ten is the usual working threshold: tolerant of re-encoding and
     * rescaling, tight enough that a different object does not match.
     */
    'fingerprint' => [
        'max_distance' => env('CERTIFICATE_FP_MAX_DISTANCE', 10),
    ],
];
