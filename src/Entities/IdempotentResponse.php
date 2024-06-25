<?php

namespace AlgoYounes\Idempotency\Entities;

use Illuminate\Http\Response;

final class IdempotentResponse
{
    public function __construct(
        private readonly string $body,
        private readonly int $status,
        private readonly array $headers
    ) {
    }

    public static function createFromArray(array $attributes): self
    {
        return new self(
            $attributes['body'],
            $attributes['status'],
            $attributes['headers']
        );
    }

    public static function createFromResponse(Response $response): self
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

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function toArray(): array
    {
        return [
            'body'        => $this->getBody(),
            'status'      => $this->getStatus(),
            'headers'     => $this->getHeaders(),
        ];
    }
}
