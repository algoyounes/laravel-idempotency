<?php

namespace AlgoYounes\Idempotency\Entities;

use AlgoYounes\Idempotency\ValueObjects\Checksum;
use Illuminate\Http\Request;

final class IdempotentRequest
{
    public function __construct(
        private readonly string $body,
        private readonly array $headers,
        private readonly string $path,
        private readonly Checksum $checksum
    ) {
    }

    public static function createFromArray(array $attributes): self
    {
        return new self(
            $attributes['body'],
            $attributes['headers'],
            $attributes['path'],
            $attributes['checksum']
        );
    }

    public static function createFromRequest(Request $request): self
    {
        $attributes = [
            'path'    => $request->path(),
            'body'    => (string) $request->getContent(),
            'headers' => $request->headers->all(),
        ];

        $attributes['checksum'] = Checksum::createFromPayload($attributes);

        return self::createFromArray($attributes);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getChecksum(): Checksum
    {
        return $this->checksum;
    }

    public function toArray(): array
    {
        return [
            'body'        => $this->getBody(),
            'headers'     => $this->getHeaders(),
            'path'        => $this->getPath(),
            'checksum'    => $this->getChecksum(),
        ];
    }
}
