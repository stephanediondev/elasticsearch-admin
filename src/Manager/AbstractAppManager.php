<?php
declare(strict_types=1);

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
