<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the default options for HTTP client requests.
    | These options will be applied to all HTTP requests made using Laravel's
    | HTTP client unless overridden.
    |
    */

    'default_options' => [
        'timeout' => env('HTTP_TIMEOUT', 30),
        'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
        'verify' => env('HTTP_VERIFY_SSL', !env('APP_DEBUG', false)),
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SSL verification settings. In development, you may want to
    | disable SSL verification to avoid certificate issues with self-signed
    | certificates or local development environments.
    |
    */

    'ssl' => [
        'verify' => env('HTTP_VERIFY_SSL', !env('APP_DEBUG', false)),
        'verify_peer' => env('HTTP_VERIFY_PEER', !env('APP_DEBUG', false)),
        'verify_host' => env('HTTP_VERIFY_HOST', !env('APP_DEBUG', false)),
    ],
];
