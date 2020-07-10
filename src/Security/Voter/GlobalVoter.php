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
            'TOOLS',
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
            'ENRICH_POLICIES',
            'ENRICH_POLICIES_STATS',
            'ENRICH_POLICIES_CREATE',
            'USERS',
            'USERS_CREATE',
            'ROLES',
            'ROLES_CREATE',
            'SNAPSHOTS',
            'SNAPSHOTS_CREATE',
            'PIPELINES',
            'PIPELINES_CREATE',
            'TASKS',
            'REMOTE_CLUSTERS',
            'CAT',
            'CONSOLE',
            'DEPRECATIONS',
            'LICENSE',
        ];
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, $attributes);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}
