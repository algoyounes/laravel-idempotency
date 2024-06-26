<?php

namespace AlgoYounes\Idempotency\ValueObjects;

use Stringable;

final class Checksum implements Stringable
{
    private const HASHING_ALGORITHM = 'sha256';

    private function __construct(private readonly string $checksum)
    {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function createFromAttributes(array $attributes): self
    {
        return new self(
            hash(
                self::HASHING_ALGORITHM,
                (string) json_encode($attributes, JSON_PARTIAL_OUTPUT_ON_ERROR)
            )
        );
    }

    public function equals(Checksum $checksum): bool
    {
        return hash_equals(
            $checksum->getValue(),
            $this->getValue()
        );
    }

    public function getValue(): string
    {
        return $this->checksum;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
