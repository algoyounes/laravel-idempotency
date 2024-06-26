<?php

namespace AlgoYounes\Idempotency\Exceptions;

use Exception;

class LockWaitExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct('maximum wait time for acquiring the lock has been exceeded');
    }
}
