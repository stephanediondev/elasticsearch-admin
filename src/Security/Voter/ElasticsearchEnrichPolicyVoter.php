<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Model\ElasticsearchEnrichPolicyModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchEnrichPolicyVoter extends AbstractAppVoter
{
    protected string $module = 'enrich_policy';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && ($subject instanceof ElasticsearchEnrichPolicyModel || 'enrich_policy' === $subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (! $user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof ElasticsearchEnrichPolicyModel) {
            if ($subject->isSystem()) {
                return false;
            }
        }

        return $this->isGranted($attribute);
    }
}
