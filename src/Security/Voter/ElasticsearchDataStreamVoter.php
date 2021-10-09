<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchDataStreamModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchDataStreamVoter extends AbstractAppVoter
{
    protected $module = 'data_stream';

    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && ($subject instanceof ElasticsearchDataStreamModel || 'data_stream' === $subject);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->isGranted($attribute);
    }
}
