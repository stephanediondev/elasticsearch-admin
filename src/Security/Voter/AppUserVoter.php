<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Model\AppUserModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AppUserVoter extends AbstractAppVoter
{
    protected string $module = 'app_user';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && $subject instanceof AppUserModel;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof UserInterface) {
            return false;
        }

        if ('APP_USER_DELETE' === $attribute && $user->getUserIdentifier() == $subject->getEmail()) {
            return false;
        }

        return $this->isGranted($attribute);
    }
}
