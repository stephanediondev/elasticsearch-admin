<?php

namespace App\Exception;

use RuntimeException;

class CallException extends RuntimeException
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
