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
                $row = $row['_source'];

                $userModel = new AppUserModel();
                $userModel->convert($row);
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
                $row = $row['_source'];

                $userModel = new AppUserModel();
                $userModel->convert($row);
                $users[$row['email']] = $userModel;
            }
            ksort($users);
        }

        return $users;
    }

    public function send(AppUserModel $userModel): CallResponseModel
    {
        $json = $userModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-users/_doc/'.$userModel->getEmail());
        } else {
            $callRequest->setPath('/.elastictsearch-admin-users/doc/'.$userModel->getEmail());
        }
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByEmail(string $email): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        if (true == $this->callManager->checkVersion('6.2')) {
            $callRequest->setPath('/.elastictsearch-admin-users/_doc/'.$email);
        } else {
            $callRequest->setPath('/.elastictsearch-admin-users/doc/'.$email);
        }
        $callRequest->setMethod('DELETE');

        return $this->callManager->call($callRequest);
    }
}
