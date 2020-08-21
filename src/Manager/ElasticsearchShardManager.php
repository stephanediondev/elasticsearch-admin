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
    public function getAll(string $sort = 'index:asc,shard:asc,prirep:asc', array $filter = []): array
    {
        $shards = [];

        $query = ['bytes' => 'b', 'h' => 'index,shard,prirep,state,unassigned.reason,docs,store,node'];
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = $sort;
        }
        $callRequest = new CallRequestModel();
        if (true === isset($filter['index'])) {
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
                if (true === isset($filter['state']) && 0 < count($filter['state'])) {
                    if (true === in_array($shardModel->getState(), $filter['state'])) {
                        $shards[] = $shardModel;
                    }
                } else {
                    $shards[] = $shardModel;
                }
            }
        }

        return $shards;
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
