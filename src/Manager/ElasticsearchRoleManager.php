<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallRequestModel;

class ElasticsearchRoleManager
{
    /**
     * @required
     */
    public function setCallManager(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function selectRoles()
    {
        $roles = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $roles[] = $k;
        }

        sort($roles);

        return $roles;
    }

    public function getPrivileges()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/privilege/_builtin');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }
}