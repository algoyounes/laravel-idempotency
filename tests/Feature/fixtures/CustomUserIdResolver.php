<?php

namespace AlgoYounes\Idempotency\Tests\Feature\fixtures;

use AlgoYounes\Idempotency\Contracts\Resolver;

class CustomUserIdResolver implements Resolver
{
    public static function resolve(): string
    {
        return 'custom-user-id';
    }
}
