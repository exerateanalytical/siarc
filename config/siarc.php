<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SIARC "overall" (standalone) mode
    |--------------------------------------------------------------------------
    | When true, the whole platform presents as SIARC 2026: the root landing
    | page (/) becomes the SIARC home and the SIARC module is the primary
    | experience. When false, SIARC is a module inside the national gallery
    | and lives under /siarc.
    |
    | This is the default/boot value; it can be toggled at runtime by an admin
    | (stored durably in the cache) — see siarcStandalone()/siarcSetStandalone()
    | in app/Support/route_helpers.php.
    */
    'standalone' => (bool) env('SIARC_STANDALONE', false),

    // Slug prefix used to resolve the current SIARC event.
    'event_slug' => 'siarc',
];
