<?php

namespace AlgoYounes\Idempotency\Contracts;

interface Resolver
{
    public static function resolve(): string;
}
