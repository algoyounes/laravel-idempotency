<?php

namespace AlgoYounes\Idempotency\Attributes;

use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractAttributes implements Arrayable
{
    abstract protected function getAttributes(): array;

    public function toArray(): array
    {
        return $this->getAttributes();
    }
}
