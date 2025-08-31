<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Model\ElasticsearchUserModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchUserVoter extends AbstractAppVoter
{
    protected string $module = 'elasticsearch_user';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchUserModel;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (! $user instanceof UserInterface) {
            return false;
        }

        if ($subject->isReserved()) {
            return false;
        }

        if ('ELASTICSEARCH_USER_ENABLE' === $attribute && true === $subject->getEnabled()) {
            return false;
        }

        if ('ELASTICSEARCH_USER_DISABLE' === $attribute && false === $subject->getEnabled()) {
            return false;
        }

        return $this->isGranted($attribute);
    }
}
