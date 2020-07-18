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
        if (true == $this->callManager->checkVersion('6.6')) {
            $this->endpoint = '/_security';
        } else  {
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

    public function selectRoles()
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath($this->getEndpoint().'/privilege/_builtin');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }
}
