<?php

namespace AlgoYounes\Idempotency\Contracts;

interface ResolverContract
{
    public static function resolve(): string;
}
