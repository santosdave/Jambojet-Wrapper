<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JamboJet API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for JamboJet NSK API endpoints.
    | Test environment: https://jmtest.booking.jambojet.com/jm/dotrez/
    |
    */
    'base_url' => env('JAMBOJET_BASE_URL', 'https://jmtest.booking.jambojet.com/jm/dotrez/'),

    /*
    |--------------------------------------------------------------------------
    | API Subscription Key
    |--------------------------------------------------------------------------
    |
    | Your Ocp-Apim-Subscription-Key provided by JamboJet.
    | This is required for all API requests.
    |
    */
    'subscription_key' => env('JAMBOJET_SUBSCRIPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (in seconds) to wait for API responses.
    |
    */
    'timeout' => env('JAMBOJET_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Number of times to retry failed requests before giving up.
    |
    */
    'retry_attempts' => env('JAMBOJET_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Current environment: test, staging, or production
    |
    */
    'environment' => env('JAMBOJET_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | Version Priority
    |--------------------------------------------------------------------------
    |
    | API version resolution priority when multiple versions exist.
    |
    */
    'version_priority' => ['current', 'absolute'],

    'rate_limit' => [
        'enabled' => env('JAMBOJET_RATE_LIMIT_ENABLED', true),
        'max_attempts' => env('JAMBOJET_RATE_LIMIT_MAX', 60),
        'decay_minutes' => env('JAMBOJET_RATE_LIMIT_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure response caching to improve performance.
    |
    */
    'cache' => [
        'enabled' => env('JAMBOJET_CACHE_ENABLED', true),
        'ttl' => env('JAMBOJET_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('JAMBOJET_CACHE_PREFIX', 'jambojet_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API request/response logging.
    |
    */
    'logging' => [
        'enabled' => env('JAMBOJET_LOG_REQUESTS', false),
        'channel' => env('JAMBOJET_LOG_CHANNEL', 'stack'),
    ],
];
