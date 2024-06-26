<?php

namespace AlgoYounes\Idempotency\Managers\Cache;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Entities\Idempotency;
use AlgoYounes\Idempotency\Exceptions\LockWaitExceededException;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class IdempotencyCacheManager
{
    private const CACHE_KEY = 'idempotences:%s:users:%s:data';
    private const CACHE_TTL = 86400; // 24 hours
    private const DEFAULT_MAX_LOCK_WAIT_TIME = 10; // 10 seconds

    public function __construct(
        private readonly CacheRepository $cacheRepository,
        private readonly LockProvider $lockProvider
    ) {
    }

    private function getCacheKey(string $idempotencyKey, string $userId): string
    {
        return sprintf(self::CACHE_KEY, $idempotencyKey, $userId);
    }

    private function getMaxLockWaitTime(): int
    {
        return (int) IdempotencyConfig::get(IdempotencyConfig::MAX_LOCK_WAIT_TIME_KEY, self::DEFAULT_MAX_LOCK_WAIT_TIME);
    }

    public function hasIdempotency(string $idempotencyKey, string $userId): bool
    {
        return $this->cacheRepository->has($this->getCacheKey($idempotencyKey, $userId));
    }

    public function getIdempotency(string $idempotencyKey, string $userId): ?Idempotency
    {
        $idempotency = $this->cacheRepository->get($this->getCacheKey($idempotencyKey, $userId));
        if (! $idempotency instanceof Idempotency) {
            return null;
        }

        return $idempotency;
    }

    public function setIdempotency(string $userId, Idempotency $idempotency): bool
    {
        return $this->cacheRepository->put(
            $this->getCacheKey($userId, $idempotency->getIdempotencyKey()),
            $idempotency,
            (int) IdempotencyConfig::get(IdempotencyConfig::CACHE_TTL_KEY, self::CACHE_TTL)
        );
    }

    public function acquireLock(string $idempotencyKey, string $userId): bool
    {
        return (bool) $this->lockProvider
            ->lock(
                $this->getCacheKey($idempotencyKey, $userId),
                $this->getMaxLockWaitTime()
            )
            ->block($this->getMaxLockWaitTime());
    }

    public function releaseLock(string $idempotencyKey, string $userId): bool
    {
        return $this->lockProvider
            ->lock(
                $this->getCacheKey($idempotencyKey, $userId),
                $this->getMaxLockWaitTime()
            )
            ->release();
    }

    /**
     * @throws LockWaitExceededException
     */
    public function waitForLock(string $idempotencyKey, string $userId): bool
    {
        $tries = 0;
        $sleep = 1;

        while ($tries < $this->getMaxLockWaitTime() - 1) {
            if ($this->hasIdempotency($idempotencyKey, $userId)) {
                return true;
            }

            sleep($sleep);
            $tries++;
        }

        throw new LockWaitExceededException();
    }
}
