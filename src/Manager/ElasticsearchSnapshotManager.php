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
        $snapshot = $callResponse->getContent();

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $snapshotModel = null;
        } elseif (true == isset($snapshot['responses'][0]['error']['type']) && 'snapshot_missing_exception' == $snapshot['responses'][0]['error']['type']) {
            $snapshotModel = null;
        } else {
            if (true == isset($snapshot['responses'])) {
                $snapshot = $snapshot['responses'][0]['snapshots'][0];
            } else {
                $snapshot = $snapshot['snapshots'][0];
            }
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

            if (true == isset($results['responses'])) {
                foreach ($results['responses'] as $response) {
                    foreach ($response['snapshots'] as $row) {
                        $row['repository'] = $repository;

                        $snapshotModel = new ElasticsearchSnapshotModel();
                        $snapshotModel->convert($row);
                        $snapshots[] = $snapshotModel;
                    }
                }
            } elseif (true == isset($results['snapshots'])) {
                foreach ($results['snapshots'] as $row) {
                    $row['repository'] = $repository;

                    $snapshotModel = new ElasticsearchSnapshotModel();
                    $snapshotModel->convert($row);
                    $snapshots[] = $snapshotModel;
                }
            }
        }
        usort($snapshots, [$this, 'sortByStartTime']);

        return $snapshots;
    }

    private function sortByStartTime($a, $b)
    {
        return $b->getStartTime() > $a->getStartTime();
    }

    public function send(ElasticsearchSnapshotModel $snapshotModel): CallResponseModel
    {
        $json = $snapshotModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_snapshot/'.$snapshotModel->getRepository().'/'.$snapshotModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByNameAndRepository(string $name, string $repository): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_snapshot/'.$repository.'/'.$name);

        return $this->callManager->call($callRequest);
    }

    private function sortByName($a, $b)
    {
        return $b['name'] < $a['name'];
    }
}
