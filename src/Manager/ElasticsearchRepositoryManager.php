<?php
declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchRepositoryModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchRepositoryManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchRepositoryModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $repositoryModel = null;
        } else {
            $repositoryQuery = $callResponse->getContent();
            $repositoryQuery = $repositoryQuery[key($repositoryQuery)];

            $repositoryQuery['name'] = $name;
            $repository = $repositoryQuery;

            $repositoryModel = new ElasticsearchRepositoryModel();
            $repositoryModel->convert($repository);
        }

        return $repositoryModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/_all');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $repositories = [];
        if ($results) {
            foreach ($results as $k => $row) {
                $row['name'] = $k;
                $repositoryModel = new ElasticsearchRepositoryModel();
                $repositoryModel->convert($row);
                $repositories[] = $repositoryModel;
            }
            usort($repositories, [$this, 'sortByName']);
        }

        return $repositories;
    }

    private function sortByName(ElasticsearchRepositoryModel $a, ElasticsearchRepositoryModel $b): int
    {
        return $a->getName() <=> $b->getName();
    }

    public function send(ElasticsearchRepositoryModel $repositoryModel): CallResponseModel
    {
        $json = $repositoryModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_snapshot/'.$repositoryModel->getName());
        if ($repositoryModel->getVerify()) {
            $callRequest->setQuery(['verify' => 'true']);
        } else {
            $callRequest->setQuery(['verify' => 'false']);
        }
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_snapshot/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function verifyByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_snapshot/'.$name.'/_verify');

        return $this->callManager->call($callRequest);
    }

    public function cleanupByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_snapshot/'.$name.'/_cleanup');

        return $this->callManager->call($callRequest);
    }

    public function selectRepositories(): array
    {
        $repositories = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/_all');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        if ($rows) {
            foreach ($rows as $k => $row) {
                $repositories[] = $k;
            }
            sort($repositories);
        }

        return $repositories;
    }
}
