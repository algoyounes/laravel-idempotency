<?php

namespace AlgoYounes\Idempotency\Config;

final class IdempotencyConfig
{
    // Idempotency config keys
    public const ENABLED_KEY = 'enabled';
    public const IDEMPOTENCY_HEADER_KEY = 'idempotency_header';
    public const RELAYED_HEADER_KEY = 'idempotency_relayed_header';
    public const ENFORCED_VERBS_KEY = 'enforced_verbs';
    public const DUPLICATE_HANDLING_KEY = 'duplicate_handling';
    public const MAX_LOCK_WAIT_TIME_KEY = 'max_lock_wait_time';
    public const USER_ID_RESOLVER_KEY = 'user_id_resolver';
    public const UNAUTHENTICATED_USER_ID_KEY = 'unauthenticated_user_id';

    // Cache config keys
    public const CACHE_TTL_KEY = 'cache.ttl';
    public const CACHE_STORE_KEY = 'cache.store';

    // Default values
    private const DEFAULT_MAX_LOCK_WAIT_TIME = 10; // 10 seconds
    private const DEFAULT_CACHE_TTL = 86400; // 24 hours
    private const DEFAULT_CACHE_STORE = 'default';

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
    ) {
    }

    // @phpstan-ignore-next-line
    public static function createFromArray(array $attributes): self
    {
        $get = static fn (string $key, int|bool|string|array|null $default = null) => $attributes[$key] ?? $default;

        return new self(
            $get(self::ENABLED_KEY, false),
            $get(self::IDEMPOTENCY_HEADER_KEY),
            $get(self::RELAYED_HEADER_KEY),
            $get(self::ENFORCED_VERBS_KEY),
            $get(self::DUPLICATE_HANDLING_KEY),
            $get(self::MAX_LOCK_WAIT_TIME_KEY, self::DEFAULT_MAX_LOCK_WAIT_TIME),
            $get(self::USER_ID_RESOLVER_KEY, null),
            $get(self::UNAUTHENTICATED_USER_ID_KEY),
            $get(self::CACHE_TTL_KEY, self::DEFAULT_CACHE_TTL),
            $get(self::CACHE_STORE_KEY, self::DEFAULT_CACHE_STORE)
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
        return $this->cacheTtl ?? $default;
    }

    public function getCacheStore(): string
    {
        return $this->cacheStore;
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
