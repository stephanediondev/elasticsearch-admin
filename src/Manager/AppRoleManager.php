<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\AppRoleModel;
use App\Model\AppUserModel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AppRoleManager extends AbstractAppManager
{
    private bool $permissionsDefined = false;

    private array $permissions = [];

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

    public function getAll(?array $query = []): array
    {
        $query['size'] = 1000;

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-roles/_search');
        $callRequest->setQuery($query);
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

    public function getPermissionsByUser(UserInterface $user): array
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

        $callRequest = new CallRequestModel();
        if ('yes' == $value) {
            $json = [
                'role' => $roleModel->getName(),
                'module' => $module,
                'permission' => $permission,
                'created_at' => (new \Datetime())->format('Y-m-d H:i:s'),
            ];
            $callRequest->setJson($json);
            $callRequest->setMethod('PUT');
        } else {
            $callRequest->setMethod('DELETE');
        }
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-permissions/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-permissions/doc/'.$id);
        }
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

    public function getAttributes(): array
    {
        $attributes = $this->getAttributesRaw();

        ksort($attributes);
        foreach ($attributes as $module => $permissions) {
            sort($permissions);
            $attributes[$module] = $permissions;
        }
        return $attributes;
    }

    public function getAttributesByModule(string $module): array
    {
        $attributes = $this->getAttributesRaw();

        return $attributes[$module] ?? [];
    }

    public function getAttributesRaw(): array
    {
        $attributes = [
            'global' => [
                'CLUSTER_AUDIT',
                'NODES', 'NODES_STATS',
                'INDICES', 'INDICES_STATS', 'INDICES_CREATE', 'INDICES_REINDEX', 'INDICES_CACHE_CLEAR', 'INDICES_FLUSH', 'INDICES_REFRESH',
                'SHARDS', 'SHARDS_STATS', 'SHARDS_REROUTE',
                'MENU_CONFIGURATION',
                'INDEX_TEMPLATES_LEGACY', 'INDEX_TEMPLATES_LEGACY_CREATE',
                'REPOSITORIES', 'REPOSITORIES_CREATE',
                'ELASTICSEARCH_USERS', 'ELASTICSEARCH_USERS_CREATE',
                'ELASTICSEARCH_ROLES', 'ELASTICSEARCH_ROLES_CREATE',
                'MENU_TOOLS',
                'SNAPSHOTS', 'SNAPSHOTS_STATS', 'SNAPSHOTS_CREATE',
                'CAT', 'CAT_EXPORT',
                'CONSOLE', 'CONSOLE_POST', 'CONSOLE_PUT', 'CONSOLE_PATCH', 'CONSOLE_DELETE',
                'MENU_STATS',
                'MENU_APPLICATION',
                'APP_USERS', 'APP_USERS_CREATE',
                'APP_ROLES', 'APP_ROLES_CREATE',
                'APP_UNINSTALL', 'APP_UPGRADE', 'APP_SUBSCRIPTIONS', 'APP_NOTIFICATIONS',
            ],
            'app_user' => [
                'APP_USER_UPDATE', 'APP_USER_DELETE',
            ],
            'app_role' => [
                'APP_ROLE_UPDATE', 'APP_ROLE_DELETE',
            ],
            'index_template_legacy' => [
                'INDEX_TEMPLATE_LEGACY_UPDATE', 'INDEX_TEMPLATE_LEGACY_DELETE', 'INDEX_TEMPLATE_LEGACY_COPY',
            ],
            'index' => [
                'INDEX_UPDATE',
                'INDEX_DELETE',
                'INDEX_CLOSE', 'INDEX_OPEN',
                'INDEX_CACHE_CLEAR', 'INDEX_FLUSH', 'INDEX_REFRESH',
                'INDEX_SEARCH', 'INDEX_IMPORT', 'INDEX_EXPORT',
                'INDEX_ALIASES', 'INDEX_ALIAS_CREATE', 'INDEX_ALIAS_DELETE',
            ],
            'node' => [
                'NODE_SETTINGS', 'NODE_PLUGINS',
            ],
            'repository' => [
                'REPOSITORY_UPDATE', 'REPOSITORY_DELETE', 'REPOSITORY_CLEANUP', 'REPOSITORY_VERIFY',
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

        if (true === $this->callManager->hasFeature('tasks')) {
            $attributes['global'][] = 'TASKS';
        }

        if (true === $this->callManager->hasFeature('pipelines')) {
            $attributes['global'][] = 'PIPELINES';
            $attributes['global'][] = 'PIPELINES_CREATE';
            $attributes['pipeline'] = [
                'PIPELINE_UPDATE', 'PIPELINE_DELETE', 'PIPELINE_COPY',
            ];
        }

        if (true === $this->callManager->hasFeature('deprecations')) {
            $attributes['global'][] = 'DEPRECATIONS';
        }

        if (true === $this->callManager->hasFeature('license')) {
            $attributes['global'][] = 'LICENSE';
        }

        if (true === $this->callManager->hasFeature('license_status')) {
            $attributes['global'][] = 'LICENSE_START_TRIAL';
            $attributes['global'][] = 'LICENSE_START_BASIC';
        }

        if (true === $this->callManager->hasFeature('reload_secure_settings')) {
            $attributes['global'][] = 'NODES_RELOAD_SECURE_SETTINGS';
        }

        if (true === $this->callManager->hasFeature('node_usage')) {
            $attributes['node'][] = 'NODE_USAGE';
        }

        if (true === $this->callManager->hasFeature('ilm')) {
            $attributes['global'][] = 'ILM_POLICIES';
            $attributes['global'][] = 'ILM_POLICIES_STATUS';
            $attributes['global'][] = 'ILM_POLICIES_CREATE';
            $attributes['ilm_policy'] = [
                'ILM_POLICY_UPDATE', 'ILM_POLICY_DELETE', 'ILM_POLICY_COPY', 'ILM_POLICY_APPLY',
            ];
            $attributes['index'][] = 'INDEX_LIFECYCLE';
        }

        if (true === $this->callManager->hasFeature('enrich')) {
            $attributes['global'][] = 'ENRICH_POLICIES';
            $attributes['global'][] = 'ENRICH_POLICIES_STATS';
            $attributes['global'][] = 'ENRICH_POLICIES_CREATE';
            $attributes['enrich_policy'] = [
                'ENRICH_POLICY_DELETE', 'ENRICH_POLICY_COPY', 'ENRICH_POLICY_EXECUTE',
            ];
        }

        if (true === $this->callManager->hasFeature('slm')) {
            $attributes['global'][] = 'SLM_POLICIES';
            $attributes['global'][] = 'SLM_POLICIES_STATS';
            $attributes['global'][] = 'SLM_POLICIES_STATUS';
            $attributes['global'][] = 'SLM_POLICIES_CREATE';
            $attributes['slm_policy'] = [
                'SLM_POLICY_UPDATE', 'SLM_POLICY_DELETE', 'SLM_POLICY_COPY', 'SLM_POLICY_EXECUTE',
            ];
        }

        if (true === $this->callManager->hasFeature('composable_template')) {
            $attributes['global'][] = 'COMPONENT_TEMPLATES';
            $attributes['global'][] = 'COMPONENT_TEMPLATES_CREATE';
            $attributes['component_template'] = [
                'COMPONENT_TEMPLATE_UPDATE', 'COMPONENT_TEMPLATE_DELETE', 'COMPONENT_TEMPLATE_COPY',
            ];

            $attributes['global'][] = 'INDEX_TEMPLATES';
            $attributes['global'][] = 'INDEX_TEMPLATES_CREATE';
            $attributes['index_template'] = [
                'INDEX_TEMPLATE_UPDATE', 'INDEX_TEMPLATE_DELETE', 'INDEX_TEMPLATE_COPY',
            ];
        }

        if (true === $this->callManager->hasFeature('dangling_indices')) {
            $attributes['global'][] = 'DANGLING_INDICES';
            $attributes['global'][] = 'DANGLING_INDICES_IMPORT';
            $attributes['global'][] = 'DANGLING_INDICES_DELETE';
        }

        if (true === $this->callManager->hasFeature('data_streams')) {
            $attributes['global'][] = 'DATA_STREAMS';
            $attributes['global'][] = 'DATA_STREAMS_CREATE';
            $attributes['data_stream'] = [
                'DATA_STREAM_STATS', 'DATA_STREAM_DELETE',
            ];
        }

        if (true === $this->callManager->hasFeature('sql')) {
            $attributes['global'][] = 'SQL';
        }

        if (true === $this->callManager->hasFeature('tombstones')) {
            $attributes['global'][] = 'INDEX_GRAVEYARD';
        }

        if (true === $this->callManager->hasFeature('clone_snapshot')) {
            $attributes['snapshot'][] = 'SNAPSHOT_CLONE';
        }

        if (true === $this->callManager->hasFeature('cluster_settings')) {
            $attributes['global'][] = 'CLUSTER_SETTINGS';
            $attributes['global'][] = 'CLUSTER_SETTING_EDIT';
            $attributes['global'][] = 'CLUSTER_SETTING_REMOVE';
            $attributes['global'][] = 'CLUSTER_DISK_THRESHOLDS';
        }

        if (true === $this->callManager->hasFeature('allocation_explain')) {
            $attributes['global'][] = 'CLUSTER_ALLOCATION_EXPLAIN';
        }

        if (true === $this->callManager->hasFeature('freeze_unfreeze')) {
            $attributes['index'][] = 'INDEX_FREEZE';
            $attributes['index'][] = 'INDEX_UNFREEZE';
        }

        if (true === $this->callManager->hasFeature('force_merge')) {
            $attributes['global'][] = 'INDICES_FORCE_MERGE';
            $attributes['index'][] = 'INDEX_FORCE_MERGE';
        }

        if (true === $this->callManager->hasFeature('delete_by_query')) {
            $attributes['index'][] = 'INDEX_EMPTY';
        }

        if (true === $this->callManager->hasFeature('remote_clusters')) {
            $attributes['global'][] = 'REMOTE_CLUSTERS';
        }

        return $attributes;
    }
}
