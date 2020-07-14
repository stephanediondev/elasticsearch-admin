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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $roleModel = null;
        } else {
            $role = $callResponse->getContent();
            $roleNice = $role[key($role)];
            $roleNice['name'] = key($role);

            $roleModel = new AppRoleModel();
            $roleModel->convert($roleNice);
        }

        return $roleModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        foreach ($results as $k => $row) {
            $row['name'] = $k;
            $roleModel = new AppRoleModel();
            $roleModel->convert($row);
            $roles[$k] = $roleModel;
        }
        ksort($roles);

        return $roles;
    }

    public function send(AppRoleModel $roleModel): CallResponseModel
    {
        $json = $roleModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_security/role/'.$roleModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_security/role/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function selectRoles()
    {
        $roles = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role');
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
        $callRequest->setPath('/_security/privilege/_builtin');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }
}
