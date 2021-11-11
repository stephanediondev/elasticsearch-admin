<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchIlmPolicyModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchIlmPolicyVoter extends AbstractAppVoter
{
    protected string $module = 'ilm_policy';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && ($subject instanceof ElasticsearchIlmPolicyModel || 'ilm_policy' === $subject);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof ElasticsearchIlmPolicyModel) {
            if ($subject->isSystem()) {
                return false;
            }
        }

        return $this->isGranted($attribute);
    }
}
