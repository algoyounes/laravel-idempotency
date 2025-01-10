<?php

namespace AlgoYounes\Idempotency\Exceptions;

use AlgoYounes\Idempotency\Entities\IdempotentRequest;
use Exception;

class CheckSumMismatchIdempotencyException extends Exception
{
    public function __construct(
        private readonly string $idempotencyKey,
        private readonly string $userId,
        private readonly IdempotentRequest $idempotentRequest
    ) {
        parent::__construct('checksum mismatched idempotency request');
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getIdempotentRequest(): IdempotentRequest
    {
        return $this->idempotentRequest;
    }
}
