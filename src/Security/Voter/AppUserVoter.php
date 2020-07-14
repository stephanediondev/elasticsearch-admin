<?php

namespace App\Security\Voter;

use App\Model\AppUserModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AppUserVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'APP_USER_UPDATE',
            'APP_USER_DELETE',
        ];

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

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
