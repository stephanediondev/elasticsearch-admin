<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallRequestModel;

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

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $users[] = $k;
        }

        sort($users);

        return $users;
    }
}
