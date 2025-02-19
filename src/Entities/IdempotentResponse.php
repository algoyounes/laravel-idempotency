<?php

namespace AlgoYounes\Idempotency\Entities;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class IdempotentResponse
{
    /**
     * @param  array<string, list<string|null>>  $headers
     */
    public function __construct(
        private readonly string $body,
        private readonly int $status,
        private readonly array $headers
    ) {}

    /**
     * @param  array{body: string, status: int, headers: array<string, list<string|null>>}  $attributes
     */
    public static function createFromArray(array $attributes): self
    {
        return new self(
            $attributes['body'],
            $attributes['status'],
            $attributes['headers']
        );
    }

    public static function createFromResponse(Response|JsonResponse $response): self
    {
        $attributes = [
            'body'    => (string) $response->getContent(),
            'status'  => $response->getStatusCode(),
            'headers' => $response->headers->all(),
        ];

        return self::createFromArray($attributes);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, list<string|null>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array{body: string, status: int, headers: array<string, list<string|null>>}
     */
    public function toArray(): array
    {
        return [
            'body'        => $this->getBody(),
            'status'      => $this->getStatus(),
            'headers'     => $this->getHeaders(),
        ];
    }
}
