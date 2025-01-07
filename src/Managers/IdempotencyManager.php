<?php

namespace AlgoYounes\Idempotency\Managers;

use AlgoYounes\Idempotency\Attributes\IdempotencyAttributes;
use AlgoYounes\Idempotency\Builders\IdempotencyBuilder;
use AlgoYounes\Idempotency\Entities\Idempotency;
use AlgoYounes\Idempotency\Entities\IdempotentResponse;
use AlgoYounes\Idempotency\Exceptions\LockWaitExceededException;
use AlgoYounes\Idempotency\Managers\Cache\IdempotencyCacheManager;

class IdempotencyManager
{
    public function __construct(
        private readonly IdempotencyCacheManager $idempotencyCacheManager
    ) {
    }

    public function getIdempotency(string $idempotencyKey, string $userId): ?Idempotency
    {
        return $this->idempotencyCacheManager->getIdempotency($idempotencyKey, $userId);
    }

    public function getIdempotentResponse(string $idempotencyKey, string $userId): ?IdempotentResponse
    {
        $idempotency = $this->getIdempotency($idempotencyKey, $userId);
        if (! $idempotency instanceof Idempotency) {
            return null;
        }

        return $idempotency->getIdempotentResponse();
    }

    public function create(string $idempotencyKey, string $userId, IdempotencyAttributes $attributes): bool
    {
        $idempotency = IdempotencyBuilder::create()
            ->setUserId($userId)
            ->setIdempotencyKey($idempotencyKey)
            ->setAttributes($attributes)
            ->build();

        return $this->idempotencyCacheManager->setIdempotency($idempotency);
    }

    public function acquireLock(string $idempotencyKey, string $userId): bool
    {
        return $this->idempotencyCacheManager->acquireLock($idempotencyKey, $userId);
    }

    public function releaseLock(string $idempotencyKey, string $userId): bool
    {
        return $this->idempotencyCacheManager->releaseLock($idempotencyKey, $userId);
    }

    /**
     * @throws LockWaitExceededException
     */
    public function waitForLock(string $idempotencyKey, string $userId): bool
    {
        return $this->idempotencyCacheManager->waitForLock($idempotencyKey, $userId);
    }
}
