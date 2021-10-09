<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchPipelineModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchPipelineVoter extends AbstractAppVoter
{
    protected $module = 'pipeline';

    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && ($subject instanceof ElasticsearchPipelineModel || 'pipeline' === $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof ElasticsearchPipelineModel) {
            if ($subject->isSystem()) {
                return false;
            }
        }

        return $this->isGranted($attribute);
    }
}
