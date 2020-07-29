<?php

namespace App\Exception;

use RuntimeException;

class ConnectionException extends RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
