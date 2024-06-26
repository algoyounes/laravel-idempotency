<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Idempotency Middleware Enable/Disable
    |--------------------------------------------------------------------------
    |
    | This option allows you to enable or disable the idempotency middleware.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | These settings define the cache configuration used for idempotency.
    | TTL specifies the lifetime of the cache in seconds, and store
    | allows you to specify the cache store to be used.
    |
    */
    'cache' => [
        'ttl'   => 86400, // 1 day in seconds
        'store' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Idempotency Header
    |--------------------------------------------------------------------------
    |
    | This option defines the header name to expect in the request for idempotency.
    |
    */
    'idempotency_header' => 'Idempotency-Key',

    /*
    |--------------------------------------------------------------------------
    | Idempotency Relayed Header
    |--------------------------------------------------------------------------
    |
    | This option defines the header name to include in the response when the
    | request is replayed from a cached idempotent request.
    |
    */
    'idempotency_relayed_header' => 'Idempotency-Relayed',

    /*
    |--------------------------------------------------------------------------
    | Duplicate Request Handling
    |--------------------------------------------------------------------------
    |
    | This option defines how to handle duplicate requests.
    | Available options:
    | - replay: Sends the same response seen previously.
    | - exception: Throws a DuplicateIdempotencyRequestException.
    |
    */
    'duplicate_handling' => 'replay',

    /*
    |--------------------------------------------------------------------------
    | Enforced HTTP Verbs
    |--------------------------------------------------------------------------
    |
    | This option defines the HTTP verbs that should be checked for idempotency.
    | Requests with other verbs will pass through the middleware without checks.
    |
    */
    'enforced_verbs' => ['POST', 'PUT', 'PATCH', 'DELETE'],

    /*
    |--------------------------------------------------------------------------
    | Maximum Lock Wait Time
    |--------------------------------------------------------------------------
    |
    | This option specifies the maximum time in seconds to wait for a cache lock
    | to be acquired in case of a race condition.
    |
    */
    'max_lock_wait_time' => 10,

    /*
    |--------------------------------------------------------------------------
    | User ID Resolver
    |--------------------------------------------------------------------------
    |
    | This option allows you to define a custom resolver for the user ID.
    | By default, it uses the authenticated user's ID. To support config caching,
    | define the resolver as a class and method pair.
    |
    | Example:
    | 'user_id_resolver' => [App\Resolvers\UserIdResolver::class, 'resolveUserId'],
    |
    */
    // 'user_id_resolver' => [App\Resolvers\UserIdResolver::class, 'resolveUserId'],

    /*
    |--------------------------------------------------------------------------
    | Unauthenticated User ID
    |--------------------------------------------------------------------------
    |
    | This option defines the user ID to use when the user is not authenticated.
    | This can be any string, such as 'guest'.
    |
    */
    'unauthenticated_user_id' => 'guest',
];
