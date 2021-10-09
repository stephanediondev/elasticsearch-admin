<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchComponentTemplateModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchComponentTemplateVoter extends AbstractAppVoter
{
    protected $module = 'component_template';

    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && ($subject instanceof ElasticsearchComponentTemplateModel || 'component_template' === $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof ElasticsearchComponentTemplateModel) {
            if ($subject->isSystem()) {
                return false;
            }
        }

        return $this->isGranted($attribute);
    }
}
