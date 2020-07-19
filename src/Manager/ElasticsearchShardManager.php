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
    public function getByIndexAndNumber(string $index, int $number): ?ElasticsearchShardModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$repository.'/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $snapshotModel = null;
        } else {
            $snapshot = $callResponse->getContent();
            $snapshot = $snapshot['snapshots'][0];
            $snapshot['repository'] = $repository;

            $snapshotModel = new ElasticsearchShardModel();
            $snapshotModel->convert($snapshot);
        }

        return $snapshotModel;
    }

    public function getAll(string $sort, string $index = null): array
    {
        $shards = [];

        $query = ['bytes' => 'b', 'h' => 'index,shard,prirep,state,unassigned.reason,docs,store,node'];
        if (true == $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = $sort;
        }
        $callRequest = new CallRequestModel();
        if ($index) {
            $callRequest->setPath('/_cat/shards/'.$index);
        } else {
            $callRequest->setPath('/_cat/shards');
        }
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        foreach ($results as $row) {
            $snapshotModel = new ElasticsearchShardModel();
            $snapshotModel->convert($row);
            $shards[] = $snapshotModel;
        }

        return $shards;
    }
}
