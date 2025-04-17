<?php

namespace AlgoYounes\Idempotency\Attributes;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements Arrayable<TKey, TValue>
 */
abstract class AbstractAttributes implements Arrayable
{
    /**
     * @return array<TKey, TValue>
     */
    abstract protected function getAttributes(): array;

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->getAttributes();
    }
}
