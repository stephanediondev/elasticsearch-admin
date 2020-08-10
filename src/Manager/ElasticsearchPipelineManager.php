<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchPipelineModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchPipelineManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchPipelineModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ingest/pipeline/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $pipelineModel = null;
        } else {
            $rows = $callResponse->getContent();

            foreach ($rows as $k => $row) {
                $pipeline = $row;
                $pipeline['name'] = $k;
            }

            $pipelineModel = new ElasticsearchPipelineModel();
            $pipelineModel->convert($pipeline);
        }

        return $pipelineModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ingest/pipeline');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $pipelines = [];
        foreach ($results as $k => $row) {
            $row['name'] = $k;
            $pipelineModel = new ElasticsearchPipelineModel();
            $pipelineModel->convert($row);
            $pipelines[] = $pipelineModel;
        }
        usort($pipelines, [$this, 'sortByName']);

        return $pipelines;
    }

    private function sortByName($a, $b)
    {
        return $b->getName() < $a->getName();
    }

    public function send(ElasticsearchPipelineModel $pipelineModel): CallResponseModel
    {
        $json = $pipelineModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_ingest/pipeline/'.$pipelineModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_ingest/pipeline/'.$name);

        return $this->callManager->call($callRequest);
    }
}
