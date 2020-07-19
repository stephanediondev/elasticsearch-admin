<?php

namespace App\Security\Voter;

use App\Manager\AppRoleManager;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractAppVoter extends Voter
{
    public function __construct(Security $security, AppRoleManager $appRoleManager)
    {
        $this->security = $security;
        $this->appRoleManager = $appRoleManager;
        //$this->savedAll = $this->rolePermissionManager->getSavedVoter();
    }

    protected function savedAll($module, $permission)
    {
        return true == isset($this->savedAll[$module]) && true == isset($this->savedAll[$module][$permission]) ? $this->savedAll[$module][$permission] : [];
    }

    protected function getAttributes($module)
    {
        return isset($this->savedAll[$module]) ? array_keys($this->savedAll[$module]) : [];
    }

    protected function isGranted($roles)
    {
        foreach ($roles as $role) {
            if ($this->security->isGranted($role)) {
                return true;
            }
        }
        return false;
    }
}
