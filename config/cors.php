<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure cross-origin resource sharing settings to control which
    | front-end origins may access the API. Credentials are supported
    | to allow Sanctum's SPA authentication cookie workflow.
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'telescope/*',
        'horizon/*',
        'webhooks/*',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(
        array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', 'http://localhost:5173'))))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
