<?php

namespace App\Security\Voter;

use App\Model\ElasticsearchIndexModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchIndexVoter extends AbstractAppVoter
{
    protected $module = 'index';

    protected function supports($attribute, $subject)
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && $subject instanceof ElasticsearchIndexModel;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        $includeWhenIsSystem = [
            'INDEX_SHARDS',
            'INDEX_LIFECYCLE',
            'INDEX_ALIASES',
        ];

        if (false == in_array($attribute, $includeWhenIsSystem) && $subject->isSystem()) {
            return false;
        }

        $excludeWhenClosed = [
            'INDEX_FORCE_MERGE',
            'INDEX_CACHE_CLEAR',
            'INDEX_FLUSH',
            'INDEX_REFRESH',
            'INDEX_EMPTY',
            'INDEX_SHARDS',
            'INDEX_SEARCH',
            'INDEX_IMPORT',
            'INDEX_ALIASES',
            'INDEX_ALIAS_CREATE',
            'INDICES_REINDEX',
        ];

        if (true == in_array($attribute, $excludeWhenClosed) && 'close' == $subject->getStatus()) {
            return false;
        }

        if ('INDEX_CLOSE' == $attribute && 'close' == $subject->getStatus()) {
            return false;
        }

        if ('INDEX_OPEN' == $attribute && 'open' == $subject->getStatus()) {
            return false;
        }

        if ('INDEX_FREEZE' == $attribute && $subject->getSetting('index.frozen') && 'true' == $subject->getSetting('index.frozen')) {
            return false;
        }

        if ('INDEX_UNFREEZE' == $attribute && (false == $subject->getSetting('index.frozen') || 'false' == $subject->getSetting('index.frozen'))) {
            return false;
        }

        return $this->isGranted($attribute, $user);
    }
}
