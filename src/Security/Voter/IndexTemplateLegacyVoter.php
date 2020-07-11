<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchIndexTemplateLegacyModel;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class IndexTemplateLegacyVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'INDEX_TEMPLATE_LEGACY_UPDATE',
            'INDEX_TEMPLATE_LEGACY_DELETE',
            'INDEX_TEMPLATE_LEGACY_COPY',
        ];

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

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
