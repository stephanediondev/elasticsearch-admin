<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchRoleModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchRoleVoter extends AbstractAppVoter
{
    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule('elasticsearch_role');

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchRoleModel;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject->isReserved()) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
