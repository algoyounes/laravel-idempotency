<?php

namespace AlgoYounes\Idempotency\Resolvers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;

class NullUserIdResolver
{
    public static function resolve(): string
    {
        return IdempotencyConfig::get(IdempotencyConfig::UNAUTHENTICATED_USER_ID_KEY, 'guest');
    }
}
