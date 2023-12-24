<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Model\ElasticsearchIndexModel;
use App\Security\Voter\AbstractAppVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ElasticsearchIndexVoter extends AbstractAppVoter
{
    protected string $module = 'index';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $attributes = $this->appRoleManager->getAttributesByModule($this->module);

        return in_array($attribute, $attributes) && ($subject instanceof ElasticsearchIndexModel || 'index' === $subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (! $user instanceof UserInterface) {
            return false;
        }

        if ($subject instanceof ElasticsearchIndexModel) {
            $includeWhenIsSystem = [
                'INDEX_LIFECYCLE',
                'INDEX_ALIASES',
            ];

            if (false === in_array($attribute, $includeWhenIsSystem) && $subject->isSystem()) {
                return false;
            }

            $excludeWhenClosed = [
                'INDEX_FORCE_MERGE',
                'INDEX_CACHE_CLEAR',
                'INDEX_FLUSH',
                'INDEX_REFRESH',
                'INDEX_EMPTY',
                'INDEX_SEARCH',
                'INDEX_IMPORT',
                'INDEX_ALIASES',
                'INDEX_ALIAS_CREATE',
                'INDICES_REINDEX',
            ];

            if (true === in_array($attribute, $excludeWhenClosed) && 'close' === $subject->getStatus()) {
                return false;
            }

            if ('INDEX_CLOSE' === $attribute && 'close' === $subject->getStatus()) {
                return false;
            }

            if ('INDEX_OPEN' === $attribute && 'open' === $subject->getStatus()) {
                return false;
            }

            if ('INDEX_FREEZE' === $attribute && $subject->getSetting('index.frozen') && 'true' === $subject->getSetting('index.frozen')) {
                return false;
            }

            if ('INDEX_UNFREEZE' === $attribute && ('' === $subject->getSetting('index.frozen') || 'false' === $subject->getSetting('index.frozen'))) {
                return false;
            }
        }

        return $this->isGranted($attribute);
    }
}
