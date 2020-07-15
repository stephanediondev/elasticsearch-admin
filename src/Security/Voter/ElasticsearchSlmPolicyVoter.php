<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchSlmPolicyVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'SLM_POLICY_UPDATE',
            'SLM_POLICY_DELETE',
            'SLM_POLICY_COPY',
            'SLM_POLICY_EXECUTE',
        ];

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchSlmPolicyModel;
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
