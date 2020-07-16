<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\AppRoleModel;
use Symfony\Component\HttpFoundation\Response;

class AppRoleManager extends AbstractAppManager
{
    public function getById(string $id): ?AppRoleModel
    {
        $roleModel = null;

        $callRequest = new CallRequestModel();
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-roles/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elastictsearch-admin-roles/doc/'.$id);
        }
        $callResponse = $this->callManager->call($callRequest);
        $row = $callResponse->getContent();

        if ($row) {
            $role = ['id' => $row['_id']];
            $role = array_merge($role, $row['_source']);

            $roleModel = new AppRoleModel();
            $roleModel->convert($role);
        }

        return $roleModel;
    }

    public function getByName(string $name): ?AppRoleModel
    {
        $roleModel = null;

        $query = [
            'q' => 'name:"'.$name.'"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elastictsearch-admin-roles/_search');
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
        $callRequest->setPath('/.elastictsearch-admin-roles/_search');
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
            if (true == $this->callManager->checkVersion('6.2')) {
                $callRequest->setPath('/.elastictsearch-admin-roles/_doc/'.$roleModel->getId());
            } else {
                $callRequest->setPath('/.elastictsearch-admin-roles/doc/'.$roleModel->getId());
            }
        } else {
            $callRequest->setMethod('POST');
            if (true == $this->callManager->checkVersion('6.2')) {
                $callRequest->setPath('/.elastictsearch-admin-roles/_doc');
            } else {
                $callRequest->setPath('/.elastictsearch-admin-roles/doc/');
            }
        }
        $callRequest->setMethod('POST');
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteById(string $id): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-roles/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elastictsearch-admin-roles/doc/'.$id);
        }
        $callRequest->setMethod('DELETE');

        return $this->callManager->call($callRequest);
    }

    public function selectRoles()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elastictsearch-admin-roles/_search');
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

    public function getSettings(): array
    {
        return [
            'index' => [
                'number_of_shards' => 1,
                'auto_expand_replicas' => '0-1',
            ],
        ];
    }

    public function getMappings(): array
    {
        return [
            'properties' => [
                'name' => [
                    'type' => 'keyword',
                ],
                'created_at' => [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd HH:mm:ss',
                ],
            ],
        ];
    }
}
