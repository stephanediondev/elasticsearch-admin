<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;

class ElasticsearchUserManager extends AbstractAppManager
{
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
