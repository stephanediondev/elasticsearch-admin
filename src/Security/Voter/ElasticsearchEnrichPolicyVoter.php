<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchEnrichPolicyVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'ENRICH_POLICY_DELETE',
            'ENRICH_POLICY_COPY',
            'ENRICH_POLICY_EXECUTE',
        ];

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchEnrichPolicyModel;
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
