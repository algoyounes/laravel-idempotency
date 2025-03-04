<?php

namespace AlgoYounes\Idempotency\Resolvers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Contracts\Resolver;

class NullUserIdResolver implements Resolver
{
    private static IdempotencyConfig $config;

    public static function setConfig(IdempotencyConfig $config): void
    {
        self::$config = $config;
    }

    public static function resolve(): string
    {
        $value = self::$config->getUnauthenticatedUserId();
        if ($value === '') {
            return 'guest';
        }

        return $value;
    }
}
