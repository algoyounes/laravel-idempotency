<?php

namespace AlgoYounes\Idempotency\Config;

// TODO: Add helper methods to get config values in the helpers.php file

final class IdempotencyConfig
{
    // Idempotency config keys
    public const ENABLED_KEY = 'enabled';
    public const IDEMPOTENCY_HEADER_KEY = 'idempotency_header';
    public const RELAYED_HEADER_KEY = 'idempotency_relayed_header';
    public const ENFORCED_VERBS_KEY = 'enforced_verbs';
    public const IGNORE_EMPTY_KEY = 'ignore_empty_key';
    public const DUPLICATE_HANDLING_KEY = 'duplicate_handling';
    public const MAX_LOCK_WAIT_TIME_KEY = 'max_lock_wait_time';
    public const USER_ID_RESOLVER_KEY = 'user_id_resolver';
    public const UNAUTHENTICATED_USER_ID_KEY = 'unauthenticated_user_id';

    // Cache config keys
    public const CACHE_TTL_KEY = 'cache.ttl';
    public const CACHE_STORE_KEY = 'cache.store';

    // @phpstan-ignore-next-line
    public static function get(string $key, mixed $default = null)
    {
        return config(sprintf('idempotency.%s', $key), $default);
    }
}
