<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Manager\AppRoleManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class AbstractAppVoter extends Voter
{
    protected Security $security;

    protected AppRoleManager $appRoleManager;

    /**
     * @var array<mixed> $permissions
     */
    protected array $permissions = [];

    protected string $module;

    public function __construct(Security $security, AppRoleManager $appRoleManager)
    {
        $this->security = $security;
        $this->appRoleManager = $appRoleManager;

        $user = $this->security->getuser();
        if ($user) {
            $this->permissions = $this->appRoleManager->getPermissionsByUser($user);
        }
    }

    public function isGranted(string $attribute): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (true === isset($this->permissions[$this->module]) && true === in_array($attribute, $this->permissions[$this->module])) {
            return true;
        }

        return false;
    }
}
