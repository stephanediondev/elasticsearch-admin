<?php

namespace App\Security\Voter;

use App\Model\AppRoleModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AppRoleVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'APP_ROLE_UPDATE',
            'APP_ROLE_DELETE',
        ];

        return in_array($attribute, $attributes) && $subject instanceof AppRoleModel;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
