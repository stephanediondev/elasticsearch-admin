<?php

namespace App\Security\Voter;

use App\Model\AppUserModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AppUserVoter extends AbstractAppVoter
{
    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule('app_user');

        return in_array($attribute, $attributes) && $subject instanceof AppUserModel;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ('APP_USER_DELETE' == $attribute && $user->getEmail() == $subject->getEmail()) {
            return false;
        }

        return $this->isGranted($attribute, $user);
    }
}
