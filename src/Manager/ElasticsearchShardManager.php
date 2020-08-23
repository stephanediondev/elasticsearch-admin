<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchShardModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchShardManager extends AbstractAppManager
{
    public function getAll(array $query, array $filter = []): array
    {
        $shards = [];

        $callRequest = new CallRequestModel();
        if (true === isset($filter['index']) && '' != $filter['index']) {
            $callRequest->setPath('/_cat/shards/'.$filter['index']);
        } else {
            $callRequest->setPath('/_cat/shards');
        }
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results) {
            foreach ($results as $row) {
                $shardModel = new ElasticsearchShardModel();
                $shardModel->convert($row);
                $shards[] = $shardModel;
            }
        }

        return $this->filter($shards, $filter);
    }

    public function filter(array $shards, array $filter = []): array
    {
        $shardsWithFilter = [];

        foreach ($shards as $row) {
            $score = 0;

            if (true === isset($filter['state']) && 0 < count($filter['state'])) {
                if (false === in_array($row->getState(), $filter['state'])) {
                    $score--;
                }
            }

            if (true === isset($filter['node']) && 0 < count($filter['node'])) {
                if (false === in_array($row->getNode(), $filter['node'])) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $shardsWithFilter[] = $row;
            }
        }

        return $shardsWithFilter;
    }

    public function getNodesAvailable(array $shards, array $nodes): array
    {
        $nodesNotAvailable = [];
        foreach ($shards as $shard) {
            if ($shard->getNode()) {
                $nodesNotAvailable[$shard->getIndex()][$shard->getNumber()][] = $shard->getNode();
            }
        }

        $nodesAvailable = [];
        foreach ($nodesNotAvailable as $index => $shards) {
            foreach ($shards as $shard => $nodesExclude) {
                $nodesAvailable[$index][$shard] = array_diff($nodes, $nodesExclude);
            }
        }

        return $nodesAvailable;
    }
}
