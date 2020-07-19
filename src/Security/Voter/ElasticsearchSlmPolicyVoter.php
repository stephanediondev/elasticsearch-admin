<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchSlmPolicyModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchSlmPolicyVoter extends AbstractAppVoter
{
    protected $module = 'slm_policy';

    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

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

        return $this->isGranted($attribute);
    }
}
