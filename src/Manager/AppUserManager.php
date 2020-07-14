<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\AppUserModel;
use Symfony\Component\HttpFoundation\Response;

class AppUserManager extends AbstractAppManager
{
    public function getById(string $id): ?AppUserModel
    {
        $userModel = null;

        $callRequest = new CallRequestModel();
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-users/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elastictsearch-admin-users/doc/'.$id);
        }
        $callResponse = $this->callManager->call($callRequest);
        $row = $callResponse->getContent();

        if ($row) {
            $user = ['id' => $row['_id']];
            $user = array_merge($user, $row['_source']);

            $userModel = new AppUserModel();
            $userModel->convert($user);
        }

        return $userModel;
    }

    public function getByEmail(string $email): ?AppUserModel
    {
        $userModel = null;

        $query = [
            'q' => 'email:"'.$email.'"',
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elastictsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results && 1 == count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $user = ['id' => $row['_id']];
                $user = array_merge($user, $row['_source']);

                $userModel = new AppUserModel();
                $userModel->convert($user);
            }
        }

        return $userModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elastictsearch-admin-users/_search');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $users = [];

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $user = ['id' => $row['_id']];
                $user = array_merge($user, $row['_source']);

                $userModel = new AppUserModel();
                $userModel->convert($user);
                $users[$user['email']] = $userModel;
            }
            ksort($users);
        }

        return $users;
    }

    public function send(AppUserModel $userModel): CallResponseModel
    {
        $json = $userModel->getJson();
        $callRequest = new CallRequestModel();
        if ($userModel->getId()) {
            $callRequest->setMethod('PUT');
            if (true == $this->callManager->checkVersion('6.2')) {
                $callRequest->setPath('/.elastictsearch-admin-users/_doc/'.$userModel->getId());
            } else {
                $callRequest->setPath('/.elastictsearch-admin-users/doc/'.$userModel->getId());
            }
        } else {
            $callRequest->setMethod('POST');
            if (true == $this->callManager->checkVersion('6.2')) {
                $callRequest->setPath('/.elastictsearch-admin-users/_doc');
            } else {
                $callRequest->setPath('/.elastictsearch-admin-users/doc/');
            }
        }
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteById(string $id): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-users/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elastictsearch-admin-users/doc/'.$id);
        }
        $callRequest->setMethod('DELETE');

        return $this->callManager->call($callRequest);
    }
}
