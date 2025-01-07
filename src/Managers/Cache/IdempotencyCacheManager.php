<?php

namespace AlgoYounes\Idempotency\Managers\Cache;

use AlgoYounes\Idempotency\Config\IdempotencyConfig;
use AlgoYounes\Idempotency\Entities\Idempotency;
use AlgoYounes\Idempotency\Exceptions\LockWaitExceededException;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException as LaravelLockTimeoutException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class IdempotencyCacheManager
{
    private const CACHE_KEY = 'idempotence:%s:user:%s:data';
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private readonly CacheRepository $cacheRepository,
        private readonly LockProvider $lockProvider,
        private readonly IdempotencyConfig $config
    ) {
    }

    private function getCacheKey(string $idempotencyKey, string $userId): string
    {
        return sprintf(self::CACHE_KEY, $idempotencyKey, $userId);
    }

    private function getMaxLockWaitTime(): int
    {
        return $this->config->getMaxLockWaitTime();
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

    public function setIdempotency(Idempotency $idempotency): bool
    {
        return $this->cacheRepository->put(
            $this->getCacheKey($idempotency->getIdempotencyKey(), $idempotency->getUserId()),
            $idempotency,
            $this->config->getCacheTtl(self::CACHE_TTL)
        );
    }

    public function acquireLock(string $idempotencyKey, string $userId): bool
    {
        try {
            return (bool) $this->lockProvider
                ->lock(
                    $this->getCacheKey($idempotencyKey, $userId),
                    $this->getMaxLockWaitTime()
                )
                ->block($this->getMaxLockWaitTime());
        } catch (LaravelLockTimeoutException) {
            return false;
        }
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
