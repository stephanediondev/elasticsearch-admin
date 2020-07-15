<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchIlmPolicyModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchIlmPolicyVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'ILM_POLICY_UPDATE',
            'ILM_POLICY_DELETE',
            'ILM_POLICY_COPY',
            'ILM_POLICY_APPLY',
        ];

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchIlmPolicyModel;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject->isSystem()) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
