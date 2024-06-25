<?php

namespace AlgoYounes\Idempotency\ValueObjects;

final class Checksum
{
    private const HASHING_ALGORITHM = 'sha256';

    private function __construct(private readonly string $checksum)
    {
    }

    public static function createFromPayload(array $payload): self
    {
        return new self(
            hash(
                self::HASHING_ALGORITHM,
                json_encode($payload)
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
