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
        $rows = $this->callManager->call($callRequest);

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
        return $this->callManager->call($callRequest);
    }
}
