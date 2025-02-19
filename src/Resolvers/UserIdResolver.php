<?php

namespace AlgoYounes\Idempotency\Resolvers;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Contracts\ResolverContract;
use Illuminate\Contracts\Auth\Guard;

readonly class UserIdResolver
{
    private Guard $auth;
    private IdempotencyConfig $config;

    public function __construct()
    {
        $this->auth = make(Guard::class);
        $this->config = make(IdempotencyConfig::class);
    }

    public static function resolve(): string
    {
        return (new self)->getUserId();
    }

    private function getUserId(): string
    {
        $customResolver = $this->config->getUserIdResolver();
        if ($customResolver !== null) {
            return $this->getCustomUserId($customResolver);
        }

        return $this->getDefaultUserId();
    }

    /**
     * @param  class-string  $customResolver
     */
    private function getCustomUserId(string $customResolver): string
    {
        if (class_exists($customResolver) && is_a($customResolver, ResolverContract::class, true)) {
            /** @var ResolverContract $resolver */
            $resolver = app($customResolver);

            return $resolver->resolve();
        }

        return $this->getDefaultUserId();
    }

    private function getDefaultUserId(): string
    {
        return $this->auth->check() ? (string) $this->auth->id() : NullUserIdResolver::resolve();
    }
}
