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
            'INDEX_UPDATE',
            'INDEX_DELETE',
            'INDEX_CLOSE_OPEN',
            'INDEX_FREEZE_UNFREEZE',
            'INDEX_FORCE_MERGE',
            'INDEX_CACHE_CLEAR',
            'INDEX_FLUSH',
            'INDEX_REFRESH',
            'INDEX_EMPTY',
            'INDEX_SEARCH',
            'INDEX_IMPORT',
            'INDEX_EXPORT',
            'SHARDS',

            'CONFIGURATION',
            'INDEX_TEMPLATES_LEGACY',
            'INDEX_TEMPLATES_LEGACY_CREATE',
            'INDEX_TEMPLATE_LEGACY_UPDATE',
            'INDEX_TEMPLATE_LEGACY_DELETE',
            'INDEX_TEMPLATES',
            'INDEX_TEMPLATES_CREATE',
            'INDEX_TEMPLATE_UPDATE',
            'INDEX_TEMPLATE_DELETE',
            'COMPONENT_TEMPLATES',
            'COMPONENT_TEMPLATES_CREATE',
            'COMPONENT_TEMPLATE_UPDATE',
            'COMPONENT_TEMPLATE_DELETE',
            'ILM_POLICIES',
            'ILM_POLICIES_STATUS',
            'ILM_POLICIES_CREATE',
            'ILM_POLICY_UPDATE',
            'ILM_POLICY_DELETE',
            'SLM_POLICIES',
            'SLM_POLICIES_STATS',
            'SLM_POLICIES_STATUS',
            'SLM_POLICIES_CREATE',
            'SLM_POLICY_UPDATE',
            'SLM_POLICY_DELETE',
            'SLM_POLICY_EXECUTE',
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
            'USER_ENABLE_DISABLE',
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
            'PIPELINE_UPDATE',
            'PIPELINE_DELETE',
            'TASKS',
            'REMOTE_CLUSTERS',
            'CAT',
            'CONSOLE',
            'DEPRECATIONS',
            'LICENSE',
        ];

        return in_array($attribute, $attributes);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}
