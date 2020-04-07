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

        $call = new CallRequestModel();
        $call->setPath('/_security/role');
        $rows = $this->callManager->call($call);

        foreach ($rows as $k => $row) {
            $roles[] = $k;
        }

        sort($roles);

        return $roles;
    }

    public function getPrivileges()
    {
        $call = new CallRequestModel();
        $call->setPath('/_security/privilege/_builtin');
        return $this->callManager->call($call);
    }
}
