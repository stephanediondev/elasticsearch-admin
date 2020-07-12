<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchRoleModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchRoleVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'ELASTICSEARCH_ROLE_UPDATE',
            'ELASTICSEARCH_ROLE_DELETE',
            'ELASTICSEARCH_ROLE_COPY',
        ];

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
