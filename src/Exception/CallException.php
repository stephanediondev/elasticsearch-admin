<?php

namespace App\Exception;

use RuntimeException;

class CallException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
