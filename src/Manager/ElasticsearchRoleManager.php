<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchRoleModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchRoleManager extends AbstractAppManager
{
    /**
     * @required
     */
    public function setEndpoint()
    {
        if (true === $this->callManager->hasFeature('_security_endpoint')) {
            $this->endpoint = '/_security';
        } else {
            $this->endpoint = '/_xpack/security';
        }
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getByName(string $name): ?ElasticsearchRoleModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/role/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $roleModel = null;
        } else {
            $role = $callResponse->getContent();
            $roleNice = $role[key($role)];
            $roleNice['name'] = key($role);

            $roleModel = new ElasticsearchRoleModel();
            $roleModel->convert($roleNice);
        }

        return $roleModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/role');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        foreach ($results as $k => $row) {
            $row['name'] = $k;
            $roleModel = new ElasticsearchRoleModel();
            $roleModel->convert($row);
            $roles[$k] = $roleModel;
        }
        ksort($roles);

        return $roles;
    }

    public function send(ElasticsearchRoleModel $roleModel): CallResponseModel
    {
        $json = $roleModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath($this->getEndpoint().'/role/'.$roleModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath($this->getEndpoint().'/role/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function selectRoles(): array
    {
        $roles = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/role');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $roles[] = $k;
        }

        sort($roles);

        return $roles;
    }

    public function getPrivileges()
    {
        if (true === $this->callManager->hasFeature('builtin_privileges')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath($this->getEndpoint().'/privilege/_builtin');
            $callResponse = $this->callManager->call($callRequest);
            return $callResponse->getContent();
        } else {
            return [
                'cluster' => [
                    'all',
                    'create_snapshot',
                    'delegate_pki',
                    'grant_api_key',
                    'manage',
                    'manage_api_key',
                    'manage_autoscaling',
                    'manage_ccr',
                    'manage_data_frame_transforms',
                    'manage_enrich',
                    'manage_ilm',
                    'manage_index_templates',
                    'manage_ingest_pipelines',
                    'manage_ml',
                    'manage_oidc',
                    'manage_own_api_key',
                    'manage_pipeline',
                    'manage_rollup',
                    'manage_saml',
                    'manage_security',
                    'manage_slm',
                    'manage_token',
                    'manage_transform',
                    'manage_watcher',
                    'monitor',
                    'monitor_data_frame_transforms',
                    'monitor_ml',
                    'monitor_rollup',
                    'monitor_snapshot',
                    'monitor_transform',
                    'monitor_watcher',
                    'none',
                    'read_ccr',
                    'read_ilm',
                    'read_slm',
                    'transport_client',
                ],
                'index' => [
                    'all',
                    'create',
                    'create_doc',
                    'create_index',
                    'delete',
                    'delete_index',
                    'index',
                    'maintenance',
                    'manage',
                    'manage_follow_index',
                    'manage_ilm',
                    'manage_leader_index',
                    'monitor',
                    'none',
                    'read',
                    'read_cross_cluster',
                    'view_index_metadata',
                    'write',
                ],
            ];
        }
    }
}
