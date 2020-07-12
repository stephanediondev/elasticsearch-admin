<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchUserModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchUserVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'ELASTICSEARCH_USER_UPDATE',
            'ELASTICSEARCH_USER_DELETE',
            'ELASTICSEARCH_USER_ENABLE',
            'ELASTICSEARCH_USER_DISABLE',
        ];

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
