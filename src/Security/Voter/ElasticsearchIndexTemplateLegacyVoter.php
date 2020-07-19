<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchIndexTemplateLegacyModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchIndexTemplateLegacyVoter extends AbstractAppVoter
{
    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule('index_template_legacy');

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchIndexTemplateLegacyModel;
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

        return $this->isGranted($attribute, $user);
    }
}
