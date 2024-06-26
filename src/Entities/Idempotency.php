<?php

namespace AlgoYounes\Idempotency\Entities;

use AlgoYounes\Idempotency\Attributes\IdempotencyAttributes;

class Idempotency
{
    private string $userId;
    private string $idempotencyKey;
    private IdempotentResponse $idempotentResponse;
    private IdempotentRequest $idempotentRequest;

    public static function create(string $userId, string $idempotencyKey, IdempotencyAttributes $idempotencyAttributes): self
    {
        return (new self())
            ->setUserId($userId)
            ->setIdempotencyKey($idempotencyKey)
            ->setIdempotentRequest($idempotencyAttributes->getRequest())
            ->setIdempotentResponse($idempotencyAttributes->getResponse());
    }

    private function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    private function setIdempotencyKey(string $idempotencyKey): self
    {
        $this->idempotencyKey = $idempotencyKey;

        return $this;
    }

    private function setIdempotentResponse(IdempotentResponse $idempotentResponse): self
    {
        $this->idempotentResponse = $idempotentResponse;

        return $this;
    }

    private function setIdempotentRequest(IdempotentRequest $idempotentRequest): self
    {
        $this->idempotentRequest = $idempotentRequest;

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

    public function getIdempotentResponse(): IdempotentResponse
    {
        return $this->idempotentResponse;
    }

    public function getIdempotentRequest(): IdempotentRequest
    {
        return $this->idempotentRequest;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'             => $this->getUserId(),
            'idempotency_key'     => $this->getIdempotencyKey(),
            'idempotent_response' => $this->getIdempotentResponse()->toArray(),
            'idempotent_request'  => $this->getIdempotentRequest()->toArray(),
        ];
    }
}
