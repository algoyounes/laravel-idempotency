<?php

namespace AlgoYounes\Idempotency\Builders;

use AlgoYounes\Idempotency\Attributes\IdempotencyAttributes;
use AlgoYounes\Idempotency\Entities\Idempotency;

class IdempotencyBuilder
{
    private string $userId;
    private string $idempotencyKey;
    private IdempotencyAttributes $attributes;

    public static function create(): self
    {
        return new self();
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function setIdempotencyKey(string $idempotencyKey): self
    {
        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }

    public function setAttributes(IdempotencyAttributes $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getAttributes(): IdempotencyAttributes
    {
        return $this->attributes;
    }

    public function build(): Idempotency
    {
        return Idempotency::create($this->getUserId(), $this->getIdempotencyKey(), $this->getAttributes());
    }
}
