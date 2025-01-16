<?php

namespace AlgoYounes\Idempotency\Entities;

use AlgoYounes\Idempotency\ValueObjects\Checksum;
use Illuminate\Http\Request;

final class IdempotentRequest
{
    /**
     * @param  array<string, list<string|null>>  $body
     * @param  array<string, list<string|null>>  $headers
     */
    public function __construct(
        private readonly array $body,
        private readonly array $headers,
        private readonly string $path,
        private readonly Checksum $checksum
    ) {
    }

    /**
     * @param  array{body: array<string, list<string|null>>, headers: array<string, list<string|null>>, path: string, checksum: Checksum}  $attributes
     */
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
            'body'    => array_filter($request->getPayload()->all()),
            'headers' => $request->headers->all(),
        ];

        $attributes['checksum'] = Checksum::createFromAttributes($attributes);

        return self::createFromArray($attributes);
    }

    /**
     * @return array<string, list<string|null>>
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return array<string, list<string|null>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isPathMismatched(string $path): bool
    {
        return $this->getPath() !== $path;
    }

    public function getChecksum(): Checksum
    {
        return $this->checksum;
    }

    /**
     * @return array{body: array<string, list<string|null>>, headers: array<string, list<string|null>>, path: string, checksum: Checksum}
     */
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
