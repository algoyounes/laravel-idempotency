<?php

namespace AlgoYounes\Idempotency\Tests\Feature\fixtures;

use AlgoYounes\Idempotency\Contracts\ResolverContract;

class CustomUserIdResolver implements ResolverContract
{
    public static function resolve(): string
    {
        return 'custom-user-id';
    }
}
