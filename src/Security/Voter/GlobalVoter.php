<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GlobalVoter extends Voter
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        $attributes = [
            'CLUSTER_SETTINGS',
            'CLUSTER_SETTING_EDIT',
            'CLUSTER_SETTING_REMOVE',
            'CLUSTER_ALLOCATION_EXPLAIN',
            'NODES',
            'INDICES',
            'INDICES_STATS',
            'INDICES_CREATE',
            'INDICES_REINDEX',
            'INDICES_FORCE_MERGE',
            'INDICES_CACHE_CLEAR',
            'INDICES_FLUSH',
            'INDICES_REFRESH',
            'SHARDS',

            'CONFIGURATION',
            'INDEX_TEMPLATES_LEGACY',
            'INDEX_TEMPLATES_LEGACY_CREATE',
            'INDEX_TEMPLATES',
            'INDEX_TEMPLATES_CREATE',
            'COMPONENT_TEMPLATES',
            'COMPONENT_TEMPLATES_CREATE',
            'ILM_POLICIES',
            'ILM_POLICIES_STATUS',
            'ILM_POLICIES_CREATE',
            'SLM_POLICIES',
            'SLM_POLICIES_STATS',
            'SLM_POLICIES_STATUS',
            'SLM_POLICIES_CREATE',
            'REPOSITORIES',
            'REPOSITORIES_CREATE',
            'REPOSITORY_UPDATE',
            'REPOSITORY_DELETE',
            'REPOSITORY_CLEANUP',
            'REPOSITORY_VERIFY',
            'ENRICH_POLICIES',
            'ENRICH_POLICIES_STATS',
            'ENRICH_POLICIES_CREATE',
            'ENRICH_POLICY_DELETE',
            'ENRICH_POLICY_EXECUTE',
            'USERS',
            'USERS_CREATE',
            'USER_UPDATE',
            'USER_DELETE',
            'USER_ENABLE',
            'USER_DISABLE',
            'ROLES',
            'ROLES_CREATE',
            'ROLE_UPDATE',
            'ROLE_DELETE',

            'TOOLS',
            'SNAPSHOTS',
            'SNAPSHOTS_CREATE',
            'SNAPSHOT_DELETE',
            'SNAPSHOT_RESTORE',
            'PIPELINES',
            'PIPELINES_CREATE',
            'TASKS',
            'REMOTE_CLUSTERS',
            'CAT',
            'CAT_EXPORT',
            'CONSOLE',
            'DEPRECATIONS',
            'LICENSE',
        ];

        return in_array($attribute, $attributes) && 'global' == $subject;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN', $user);
    }
}
