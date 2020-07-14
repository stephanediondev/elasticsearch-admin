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
                $row = $row['_source'];

                $roleModel = new AppRoleModel();
                $roleModel->convert($row);
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
                $row = $row['_source'];

                $roleModel = new AppRoleModel();
                $roleModel->convert($row);
                $roles[$row['name']] = $roleModel;
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
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-roles/_doc/'.$roleModel->getName());
        } else {
            $callRequest->setPath('/.elastictsearch-admin-roles/doc/'.$roleModel->getName());
        }
        $callRequest->setMethod('POST');
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-roles/_doc/'.$name);
        } else {
            $callRequest->setPath('/.elastictsearch-admin-roles/doc/'.$name);
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
}
