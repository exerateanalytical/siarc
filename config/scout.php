<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'collection'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'after_commit' => false,

    'chunk' => [
        'searchable'   => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Index settings are applied to the Meilisearch indexes whenever the
    | `scout:sync-index-settings` Artisan command is executed. The attribute
    | names below mirror what Company::toSearchableArray() returns.
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key'  => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            'companies' => [
                'filterableAttributes' => [
                    'region_id',
                    'city_id',
                    'legal_form',
                    'status',
                    'verification_status',
                    'is_featured',
                ],
                'sortableAttributes' => [
                    'rating_avg',
                    'view_count',
                    'created_at',
                ],
                'searchableAttributes' => [
                    'name',
                    'trade_name',
                    'description_fr',
                    'description_en',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    */

    'algolia' => [
        'id'     => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Typesense Configuration
    |--------------------------------------------------------------------------
    */

    'typesense' => [
        'client-settings' => [
            'api_key'         => env('TYPESENSE_API_KEY', 'xyz'),
            'nodes'           => [
                [
                    'host'     => env('TYPESENSE_HOST', 'localhost'),
                    'port'     => env('TYPESENSE_PORT', '8108'),
                    'path'     => env('TYPESENSE_PATH', ''),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'connection_timeout_seconds' => env('TYPESENSE_CONNECTION_TIMEOUT_SECONDS', 2),
        ],
    ],

];
