<?php

namespace AlgoYounes\Idempotency\Config;

final class IdempotencyConfig
{
    // Default values
    private const DEFAULT_MAX_LOCK_WAIT_TIME = 10; // 10 seconds
    private const DEFAULT_CACHE_TTL = 86400; // 24 hours
    public const DEFAULT_CACHE_STORE = 'default';
    public const DEFAULT_IDEMPOTENCY_HEADER = 'Idempotency-Key';
    public const DEFAULT_RELAYED_HEADER = 'Idempotency-Relayed';
    public const DEFAULT_ENFORCED_VERBS = ['POST', 'PUT', 'PATCH', 'DELETE'];
    public const DEFAULT_DUPLICATE_HANDLING = 'exception';
    public const DEFAULT_UNAUTHENTICATED_USER_ID = 'guest';

    /**
     * @param  array<string>  $enforcedVerbs
     * @param  class-string|null  $userIdResolver
     */
    private function __construct(
        private readonly bool $enabled,
        private readonly string $idempotencyHeader,
        private readonly string $relayedHeader,
        private readonly array $enforcedVerbs,
        private string $duplicateHandling,
        private int $maxLockWaitTime,
        private readonly ?string $userIdResolver,
        private readonly string $unauthenticatedUserId,
        private readonly int $cacheTtl,
        private readonly string $cacheStore
    ) {}

    /**
     * @param array{
     *     enabled?: bool,
     *     idempotency_header?: string,
     *     idempotency_relayed_header?: string,
     *     enforced_verbs?: string[],
     *     duplicate_handling?: string,
     *     max_lock_wait_time?: int,
     *     user_id_resolver?: class-string|null,
     *     unauthenticated_user_id?: string,
     *     cache?: array{ttl?: int, store?: string}
     * } $attributes
     */
    public static function createFromArray(array $attributes): self
    {
        return new self(
            enabled: $attributes['enabled'] ?? false,
            idempotencyHeader: $attributes['idempotency_header'] ?? self::DEFAULT_IDEMPOTENCY_HEADER,
            relayedHeader: $attributes['idempotency_relayed_header'] ?? self::DEFAULT_RELAYED_HEADER,
            enforcedVerbs: $attributes['enforced_verbs'] ?? self::DEFAULT_ENFORCED_VERBS,
            duplicateHandling: $attributes['duplicate_handling'] ?? self::DEFAULT_DUPLICATE_HANDLING,
            maxLockWaitTime: $attributes['max_lock_wait_time'] ?? self::DEFAULT_MAX_LOCK_WAIT_TIME,
            userIdResolver: $attributes['user_id_resolver'] ?? null,
            unauthenticatedUserId: $attributes['unauthenticated_user_id'] ?? self::DEFAULT_UNAUTHENTICATED_USER_ID,
            cacheTtl: $attributes['cache']['ttl'] ?? self::DEFAULT_CACHE_TTL,
            cacheStore: $attributes['cache']['store'] ?? self::DEFAULT_CACHE_STORE,
        );
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isNotEnabled(): bool
    {
        return $this->enabled === false;
    }

    public function getIdempotencyHeader(): string
    {
        return $this->idempotencyHeader;
    }

    public function getRelayedHeader(): string
    {
        return $this->relayedHeader;
    }

    /**
     * @return array<string>
     */
    public function getEnforcedVerbs(): array
    {
        return $this->enforcedVerbs;
    }

    public function getDuplicateHandling(): string
    {
        return $this->duplicateHandling;
    }

    public function isDuplicateHandlingException(): bool
    {
        return $this->duplicateHandling === 'exception';
    }

    public function getMaxLockWaitTime(): int
    {
        return $this->maxLockWaitTime;
    }

    /**
     * @return class-string|null
     */
    public function getUserIdResolver(): ?string
    {
        return $this->userIdResolver;
    }

    public function getUnauthenticatedUserId(): string
    {
        return $this->unauthenticatedUserId;
    }

    public function getCacheTtl(int $default = self::DEFAULT_CACHE_TTL): int
    {
        return $this->cacheTtl > 0 ? $this->cacheTtl : $default;
    }

    public function getCacheStore(): string
    {
        return $this->cacheStore;
    }

    public function isDefaultCacheStore(): bool
    {
        return $this->getCacheStore() === self::DEFAULT_CACHE_STORE;
    }

    public function setDuplicateHandling(string $duplicateHandling): self
    {
        $this->duplicateHandling = $duplicateHandling;

        return $this;
    }

    public function setMaxLockWaitTime(int $maxLockWaitTime): self
    {
        $this->maxLockWaitTime = $maxLockWaitTime;

        return $this;
    }
}
