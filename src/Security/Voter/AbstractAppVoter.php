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
    }

    public function isGranted(string $attribute, $user)
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
