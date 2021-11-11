<?php

namespace App\Manager;

use App\Manager\CallManager;

class AbstractAppManager
{
    protected CallManager $callManager;

    public function __construct(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }
}
