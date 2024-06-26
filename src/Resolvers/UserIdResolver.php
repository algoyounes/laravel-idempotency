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

    /**
     * @param  array<class-string, string>  $customResolver
     */
    private function getCustomUserId(array $customResolver): string
    {
        // @phpstan-ignore-next-line
        [$class, $method] = $customResolver;

        if (class_exists($class) && method_exists($class, $method)) {
            return app($class)->{$method}();
        }

        return $this->getDefaultUserId();
    }

    private function getDefaultUserId(): string
    {
        return $this->auth->check() ? (string) $this->auth->id() : NullUserIdResolver::resolve();
    }
}
