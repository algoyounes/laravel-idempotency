<?php

namespace AlgoYounes\Idempotency\Resolvers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use Illuminate\Contracts\Auth\Guard;

readonly class UserIdResolver
{
    private Guard $auth;

    public function __construct()
    {
        $this->auth = make(Guard::class);
    }

    public static function resolve(): string
    {
        return (new self())->getUserId();
    }

    private function getUserId(): string
    {
        $customResolver = IdempotencyConfig::get(IdempotencyConfig::USER_ID_RESOLVER_KEY);
        if (is_array($customResolver) && count($customResolver) === 2) {
            return $this->getCustomUserId($customResolver);
        }

        return $this->getDefaultUserId();
    }

    private function getCustomUserId(array $customResolver): string
    {
        [$class, $method] = $customResolver;

        return make($class)->{$method}();
    }

    private function getDefaultUserId(): ?string
    {
        return $this->auth->check() ? (string) $this->auth->id() : NullUserIdResolver::resolve();
    }
}
