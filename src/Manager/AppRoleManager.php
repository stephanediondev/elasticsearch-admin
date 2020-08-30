<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\AppRoleModel;
use App\Model\AppUserModel;
use Symfony\Component\HttpFoundation\Response;

class AppRoleManager extends AbstractAppManager
{
    private $attributes = [
        'global' => [
            'CLUSTER_SETTINGS', 'CLUSTER_SETTING_EDIT', 'CLUSTER_SETTING_REMOVE', 'CLUSTER_DISK_THRESHOLDS', 'CLUSTER_ALLOCATION_EXPLAIN', 'CLUSTER_AUDIT',
            'NODES', 'NODES_STATS', 'NODES_RELOAD_SECURE_SETTINGS',
            'INDICES', 'INDICES_STATS', 'INDICES_CREATE', 'INDICES_REINDEX', 'INDICES_FORCE_MERGE', 'INDICES_CACHE_CLEAR', 'INDICES_FLUSH', 'INDICES_REFRESH',
            'SHARDS', 'SHARDS_STATS', 'SHARDS_REROUTE',
            'MENU_CONFIGURATION',
            'INDEX_TEMPLATES_LEGACY', 'INDEX_TEMPLATES_LEGACY_CREATE',
            'INDEX_TEMPLATES', 'INDEX_TEMPLATES_CREATE',
            'COMPONENT_TEMPLATES', 'COMPONENT_TEMPLATES_CREATE',
            'ILM_POLICIES', 'ILM_POLICIES_STATUS',
            'ILM_POLICIES_CREATE',
            'SLM_POLICIES', 'SLM_POLICIES_STATS', 'SLM_POLICIES_STATUS', 'SLM_POLICIES_CREATE',
            'REPOSITORIES', 'REPOSITORIES_CREATE',
            'ENRICH_POLICIES', 'ENRICH_POLICIES_STATS', 'ENRICH_POLICIES_CREATE',
            'ELASTICSEARCH_USERS', 'ELASTICSEARCH_USERS_CREATE',
            'ELASTICSEARCH_ROLES', 'ELASTICSEARCH_ROLES_CREATE',
            'MENU_TOOLS',
            'SNAPSHOTS', 'SNAPSHOTS_STATS', 'SNAPSHOTS_CREATE',
            'PIPELINES', 'PIPELINES_CREATE',
            'TASKS',
            'REMOTE_CLUSTERS',
            'CAT', 'CAT_EXPORT',
            'SQL',
            'INDEX_GRAVEYARD',
            'DANGLING_INDICES', 'DANGLING_INDICES_IMPORT', 'DANGLING_INDICES_DELETE',
            'CONSOLE', 'CONSOLE_POST', 'CONSOLE_PUT', 'CONSOLE_PATCH', 'CONSOLE_DELETE',
            'DEPRECATIONS',
            'LICENSE', 'LICENSE_START_TRIAL', 'LICENSE_START_BASIC',
            'MENU_STATS',
            'MENU_APPLICATION',
            'APP_USERS', 'APP_USERS_CREATE',
            'APP_ROLES', 'APP_ROLES_CREATE',
            'APP_UNINSTALL', 'APP_UPGRADE',
        ],
        'app_user' => [
            'APP_USER_UPDATE', 'APP_USER_DELETE',
        ],
        'app_role' => [
            'APP_ROLE_UPDATE', 'APP_ROLE_DELETE',
        ],
        'component_template' => [
            'COMPONENT_TEMPLATE_UPDATE', 'COMPONENT_TEMPLATE_DELETE', 'COMPONENT_TEMPLATE_COPY',
        ],
        'enrich_policy' => [
            'ENRICH_POLICY_DELETE', 'ENRICH_POLICY_COPY', 'ENRICH_POLICY_EXECUTE',
        ],
        'ilm_policy' => [
            'ILM_POLICY_UPDATE', 'ILM_POLICY_DELETE', 'ILM_POLICY_COPY', 'ILM_POLICY_APPLY',
        ],
        'index_template_legacy' => [
            'INDEX_TEMPLATE_LEGACY_UPDATE', 'INDEX_TEMPLATE_LEGACY_DELETE', 'INDEX_TEMPLATE_LEGACY_COPY',
        ],
        'index_template' => [
            'INDEX_TEMPLATE_UPDATE', 'INDEX_TEMPLATE_DELETE', 'INDEX_TEMPLATE_COPY',
        ],
        'index' => [
            'INDEX_UPDATE',
            'INDEX_DELETE',
            'INDEX_CLOSE', 'INDEX_OPEN',
            'INDEX_FREEZE', 'INDEX_UNFREEZE',
            'INDEX_FORCE_MERGE', 'INDEX_CACHE_CLEAR', 'INDEX_FLUSH', 'INDEX_REFRESH', 'INDEX_EMPTY',
            'INDEX_SEARCH', 'INDEX_IMPORT', 'INDEX_EXPORT',
            'INDEX_LIFECYCLE',
            'INDEX_ALIASES', 'INDEX_ALIAS_CREATE', 'INDEX_ALIAS_DELETE',
        ],
        'node' => [
            'NODE_PLUGINS', 'NODE_USAGE',
        ],
        'pipeline' => [
            'PIPELINE_UPDATE', 'PIPELINE_DELETE', 'PIPELINE_COPY',
        ],
        'repository' => [
            'REPOSITORY_UPDATE', 'REPOSITORY_DELETE', 'REPOSITORY_CLEANUP', 'REPOSITORY_VERIFY',
        ],
        'slm_policy' => [
            'SLM_POLICY_UPDATE', 'SLM_POLICY_DELETE', 'SLM_POLICY_COPY', 'SLM_POLICY_EXECUTE',
        ],
        'snapshot' => [
            'SNAPSHOT_DELETE', 'SNAPSHOT_RESTORE', 'SNAPSHOT_FAILURES',
        ],
        'elasticsearch_user' => [
            'ELASTICSEARCH_USER_UPDATE', 'ELASTICSEARCH_USER_DELETE', 'ELASTICSEARCH_USER_ENABLE', 'ELASTICSEARCH_USER_DISABLE',
        ],
        'elasticsearch_role' => [
            'ELASTICSEARCH_ROLE_UPDATE', 'ELASTICSEARCH_ROLE_DELETE', 'ELASTICSEARCH_ROLE_COPY',
        ],
    ];

    private $permissionsDefined = false;

    private $permissions = [];

    public function getByName(string $name): ?AppRoleModel
    {
        $roleModel = null;

        $query = [
            'q' => 'name:"'.$name.'"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-roles/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results && 1 == count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $role = ['id' => $row['_id']];
                $role = array_merge($role, $row['_source']);

                $roleModel = new AppRoleModel();
                $roleModel->convert($role);
            }
        }

        return $roleModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-roles/_search');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $roles = [];

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $role = ['id' => $row['_id']];
                $role = array_merge($role, $row['_source']);

                $roleModel = new AppRoleModel();
                $roleModel->convert($role);
                $roles[$role['name']] = $roleModel;
            }
            ksort($roles);
        }

        return $roles;
    }

    public function send(AppRoleModel $roleModel): CallResponseModel
    {
        $json = [
            'name' => $roleModel->getName(),
            'created_at' => (new \Datetime())->format('Y-m-d H:i:s'),
        ];
        $callRequest = new CallRequestModel();
        if ($roleModel->getId()) {
            $callRequest->setMethod('PUT');
            if (true === $this->callManager->hasFeature('_doc_as_type')) {
                $callRequest->setPath('/.elasticsearch-admin-roles/_doc/'.$roleModel->getId());
            } else {
                $callRequest->setPath('/.elasticsearch-admin-roles/doc/'.$roleModel->getId());
            }
        } else {
            $callRequest->setMethod('POST');
            if (true === $this->callManager->hasFeature('_doc_as_type')) {
                $callRequest->setPath('/.elasticsearch-admin-roles/_doc');
            } else {
                $callRequest->setPath('/.elasticsearch-admin-roles/doc/');
            }
        }
        $callRequest->setJson($json);
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }

    public function deleteById(string $id): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-roles/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-roles/doc/'.$id);
        }
        $callRequest->setMethod('DELETE');
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }

    public function deletePermissionsByRoleName(string $name): CallResponseModel
    {
        $json = [
            'query' => [
                'match' => [
                    'role' => $name,
                ],
            ],
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/.elasticsearch-admin-permissions/_delete_by_query');
        $callRequest->setJson($json);
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }

    public function getPermissionsByUser(AppUserModel $user): array
    {
        if (false === $this->permissionsDefined) {
            $this->permissionsDefined = true;

            foreach ($user->getRoles() as $role) {
                if (false === in_array($role, ['ROLE_ADMIN', 'ROLE_USER'])) {
                    $permissionsByRole = $this->getPermissionsByRole($role);
                    foreach ($permissionsByRole as $module => $permissions) {
                        if (false === isset($this->permissions[$module])) {
                            $this->permissions[$module] = [];
                        }
                        $this->permissions[$module] = array_merge($this->permissions[$module], $permissions);
                    }
                }
            }
        }

        return $this->permissions;
    }

    public function getPermissionsByRole(string $role): array
    {
        $permissions = [];
        $query = [
            'q' => 'role:"'.$role.'"',
            'size' => 1000,
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-permissions/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $row = $row['_source'];
                $permissions[$row['module']][] = $row['permission'];
            }
        }

        return $permissions;
    }

    public function setPermission(AppRoleModel $roleModel, string $module, string $permission, string $value): CallResponseModel
    {
        $id = $roleModel->getName().'-'.$module.'-'.$permission;

        $json = [
            'role' => $roleModel->getName(),
            'module' => $module,
            'permission' => $permission,
            'created_at' => (new \Datetime())->format('Y-m-d H:i:s'),
        ];
        $callRequest = new CallRequestModel();
        if ('yes' == $value) {
            $callRequest->setMethod('PUT');
        } else {
            $callRequest->setMethod('DELETE');
        }
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-permissions/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-permissions/doc/'.$id);
        }
        $callRequest->setJson($json);
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }

    public function selectRoles(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-roles/_search');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $roles = [];
        $roles[] = 'ROLE_ADMIN';

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $row = $row['_source'];
                $roles[] = $row['name'];
            }
            sort($roles);
        }

        return $roles;
    }

    public function getAttributes()
    {
        ksort($this->attributes);
        foreach ($this->attributes as $module => $permissions) {
            sort($permissions);
            $this->attributes[$module] = $permissions;
        }
        return $this->attributes;
    }

    public function getAttributesByModule($module)
    {
        return $this->attributes[$module] ?? [];
    }
}
