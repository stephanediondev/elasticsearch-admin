<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchUserModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchUserVoter extends AbstractAppVoter
{
    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule('elasticsearch_user');

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchUserModel;
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

        if ('ELASTICSEARCH_USER_ENABLE' == $attribute && true == $subject->getEnabled()) {
            return false;
        }

        if ('ELASTICSEARCH_USER_DISABLE' == $attribute && false == $subject->getEnabled()) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
