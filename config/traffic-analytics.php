<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify the table name to use for storing traffic logs.
    |
    */
    'table' => 'traffic_logs',

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) analytics should be cached.
    |
    */
    'cache_ttl' => 60,

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable traffic logging globally.
    |
    */
    'enabled' => env('TRAFFIC_ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Skip Paths
    |--------------------------------------------------------------------------
    |
    | Routes that should not be logged.
    |
    */
    'skip_paths' => [
        'api/*',
        'health',
        'status',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Extensions
    |--------------------------------------------------------------------------
    |
    | File extensions that should not be logged.
    |
    */
    'skip_extensions' => [
        'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico',
        'woff', 'woff2', 'ttf', 'eot', 'otf', 'map',
    ],
];
