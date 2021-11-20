<?php

namespace App\Exception;

use RuntimeException;

class ConnectionException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
