<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchDataStreamModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchDataStreamManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchDataStreamModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_data_stream/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $dataStreamModel = null;
        } else {
            $stream = $callResponse->getContent();
            $stream = $stream['data_streams'][0];

            $dataStreamModel = new ElasticsearchDataStreamModel();
            $dataStreamModel->convert($stream);
        }

        return $dataStreamModel;
    }

    public function getAll(array $filter = []): array
    {
        $streams = [];

        $callRequest = new CallRequestModel();
        if (true === isset($filter['name']) && '' != $filter['name']) {
            $callRequest->setPath('/_data_stream/'.$filter['name']);
        } else {
            $callRequest->setPath('/_data_stream');
        }
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results) {
            foreach ($results['data_streams'] as $row) {
                $dataStreamModel = new ElasticsearchDataStreamModel();
                $dataStreamModel->convert($row);
                $streams[] = $dataStreamModel;
            }
        }

        return $this->filter($streams, $filter);
    }

    public function filter(array $streams, array $filter = []): array
    {
        $streamsWithFilter = [];

        foreach ($streams as $row) {
            $score = 0;

            if (true === isset($filter['status']) && 0 < count($filter['status'])) {
                if (false === in_array($row->getStatus(), $filter['status'])) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $streamsWithFilter[] = $row;
            }
        }

        return $streamsWithFilter;
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_data_stream/'.$name);

        return $this->callManager->call($callRequest);
    }
}
