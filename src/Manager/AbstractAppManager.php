<?php

namespace App\Manager;

use App\Manager\CallManager;

class AbstractAppManager
{
    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }
}
