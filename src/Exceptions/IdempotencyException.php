<?php

namespace AlgoYounes\Idempotency\Exceptions;

use AlgoYounes\Idempotency\Entities\IdempotentRequest;
use Exception;

class IdempotencyException extends Exception
{
    public function __construct(
        protected string $idempotencyKey,
        protected string $userId,
        protected IdempotentRequest $idempotentRequest,
        string $message
    ) {
        parent::__construct($message);
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
