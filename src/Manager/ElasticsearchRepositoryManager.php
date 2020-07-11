<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\ElasticsearchRepositoryModel;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
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
        $callRequest->setPath('/_cat/repositories');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $repositories = [];
        foreach ($results as $k => $row) {
            $row['name'] = $row['id'];
            $repositoryModel = new ElasticsearchRepositoryModel();
            $repositoryModel->convert($row);
            $repositories[] = $repositoryModel;
        }

        return $repositories;
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

    public function selectRepositories()
    {
        $repositories = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/repositories');
        $callRequest->setQuery(['s' => 'id', 'h' => 'id']);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $row) {
            $repositories[] = $row['id'];
        }

        return $repositories;
    }
}
