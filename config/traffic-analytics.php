<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route Prefix
    |--------------------------------------------------------------------------
    | The URI prefix for all traffic analytics routes.
    */
    'route_prefix' => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Applied to all traffic analytics dashboard routes.
    */
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (seconds)
    |--------------------------------------------------------------------------
    | How long analytics query results are cached.
    */
    'cache_ttl' => 60,

    /*
    |--------------------------------------------------------------------------
    | Skipped File Extensions
    |--------------------------------------------------------------------------
    | Requests for these file types will NOT be logged.
    */
    'skip_extensions' => ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'map'],

    /*
    |--------------------------------------------------------------------------
    | Skipped Path Prefixes
    |--------------------------------------------------------------------------
    | Requests starting with these prefixes will NOT be logged.
    */
    'skip_prefixes' => ['_debugbar', 'telescope', 'horizon', 'livewire'],

];