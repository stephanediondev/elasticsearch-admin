<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallModel;

class ElasticsearchUserManager
{
    /**
     * @required
     */
    public function setCallManager(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function selectUsers()
    {
        $users = [];

        $call = new CallModel();
        $call->setPath('/_security/user');
        $rows = $this->callManager->call($call);

        foreach ($rows as $k => $row) {
            $users[] = $k;
        }

        sort($users);

        return $users;
    }
}
