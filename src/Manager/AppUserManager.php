<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\AppUserModel;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;

class AppUserManager extends AbstractAppManager
{
    public function getById(string $id): ?AppUserModel
    {
        $userModel = null;

        $callRequest = new CallRequestModel();
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-users/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-users/doc/'.$id);
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
        $callRequest->setPath('/.elasticsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $user = ['id' => $row['_id']];
                $user = array_merge($user, $row['_source']);

                $userModel = new AppUserModel();
                $userModel->convert($user);
            }
        }

        return $userModel;
    }

    /**
     * @param array<mixed>|null $query
     * @return array<mixed>
     */
    public function getAll(?array $query = []): array
    {
        $query['size'] = 1000;

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-users/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $users = [];

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $user = ['id' => $row['_id']];
                $user = array_merge($user, $row['_source']);

                $userModel = new AppUserModel();
                $userModel->convert($user);
                $users[] = $userModel;
            }
            usort($users, [$this, 'sortByEmail']);
        }

        return $users;
    }

    private function sortByEmail(AppUserModel $a, AppUserModel $b): int
    {
        return $a->getEmail() <=> $b->getEmail();
    }

    public function send(AppUserModel $userModel): CallResponseModel
    {
        $json = $userModel->getJson();
        $callRequest = new CallRequestModel();
        if ($userModel->getId()) {
            $callRequest->setMethod('PUT');
            if (true === $this->callManager->hasFeature('_doc_as_type')) {
                $callRequest->setPath('/.elasticsearch-admin-users/_doc/'.$userModel->getId());
            } else {
                $callRequest->setPath('/.elasticsearch-admin-users/doc/'.$userModel->getId());
            }
        } else {
            $callRequest->setMethod('POST');
            if (true === $this->callManager->hasFeature('_doc_as_type')) {
                $callRequest->setPath('/.elasticsearch-admin-users/_doc');
            } else {
                $callRequest->setPath('/.elasticsearch-admin-users/doc/');
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
            $callRequest->setPath('/.elasticsearch-admin-users/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-users/doc/'.$id);
        }
        $callRequest->setMethod('DELETE');
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }
}
