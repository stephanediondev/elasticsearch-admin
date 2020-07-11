<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchSnapshotModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchSnapshotManager extends AbstractAppManager
{
    public function getByNameAndRepository(string $name, string $repository): ?ElasticsearchSnapshotModel
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

            $snapshotModel = new ElasticsearchSnapshotModel();
            $snapshotModel->convert($snapshot);
        }

        return $snapshotModel;
    }

    public function getAll($repositories): array
    {
        $snapshots = [];

        foreach ($repositories as $repository) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_snapshot/'.$repository.'/_all');
            $callResponse = $this->callManager->call($callRequest);
            $results = $callResponse->getContent();

            if (true == isset($results['snapshots'])) {
                foreach ($results['snapshots'] as $row) {
                    $row['repository'] = $repository;

                    $snapshotModel = new ElasticsearchSnapshotModel();
                    $snapshotModel->convert($row);
                    $snapshots[] = $snapshotModel;
                }
            }
        }

        return $snapshots;
    }

    public function send(ElasticsearchSnapshotModel $snapshotModel): CallResponseModel
    {
        $json = $snapshotModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_slm/policy/'.$snapshotModel->getName());
        $callRequest->setJson($json);
        $callResponse = $this->callManager->call($callRequest);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_slm/policy/'.$name);

        return $this->callManager->call($callRequest);
    }

    private function sortByName($a, $b)
    {
        return $b['name'] < $a['name'];
    }
}
