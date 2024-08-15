<?php

namespace AlgoYounes\Idempotency\Resolvers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;

class NullUserIdResolver
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
