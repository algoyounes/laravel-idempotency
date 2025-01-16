<?php

namespace AlgoYounes\Idempotency\Exceptions;

use AlgoYounes\Idempotency\Entities\IdempotentRequest;

class CheckSumMismatchIdempotencyException extends IdempotencyException
{
    public function __construct(
        protected string $idempotencyKey,
        protected string $userId,
        protected IdempotentRequest $idempotentRequest,
    ) {
        parent::__construct(
            $idempotencyKey,
            $userId,
            $idempotentRequest,
            'checksum mismatched idempotency request'
        );
    }
}
