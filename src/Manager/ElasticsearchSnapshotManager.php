<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\CallException;
use App\Manager\AbstractAppManager;
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
        $content = $callResponse->getContent();

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $snapshotModel = null;
        } elseif (true === isset($content['snapshots']) && 0 === count($content['snapshots'])) {
            $snapshotModel = null;
        } elseif (true === isset($content['responses']) && true === isset($content['responses'][0]['error']['type']) && 'repository_exception' == $content['responses'][0]['error']['type']) {
            throw new CallException($content['responses'][0]['error']['reason']);
        } elseif (true === isset($content['responses']) && true === isset($content['responses'][0]['error']['type']) && 'snapshot_missing_exception' == $content['responses'][0]['error']['type']) {
            $snapshotModel = null;
        } else {
            if (true === isset($content['responses'])) {
                $snapshot = $content['responses'][0]['snapshots'][0];
            } else {
                $snapshot = $content['snapshots'][0];
            }
            $snapshot['repository'] = $repository;

            $snapshotModel = new ElasticsearchSnapshotModel();
            $snapshotModel->convert($snapshot);
        }

        return $snapshotModel;
    }

    /**
     * @param array<mixed> $repositories
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function getAll(array $repositories, array $filter = []): array
    {
        $snapshots = [];

        foreach ($repositories as $repository) {
            try {
                $callRequest = new CallRequestModel();
                if (true === isset($filter['name']) && '' != $filter['name']) {
                    $callRequest->setPath('/_snapshot/'.$repository.'/'.$filter['name']);
                } else {
                    $callRequest->setPath('/_snapshot/'.$repository.'/_all');
                }
                $callResponse = $this->callManager->call($callRequest);
                $results = $callResponse->getContent();

                $rows = [];
                if (true === isset($results['responses'])) {
                    foreach ($results['responses'] as $response) {
                        if (true === isset($response['snapshots'])) {
                            foreach ($response['snapshots'] as $row) {
                                $row['repository'] = $repository;
                                $rows[] = $row;
                            }
                        }
                    }
                } elseif (true === isset($results['snapshots'])) {
                    foreach ($results['snapshots'] as $row) {
                        $row['repository'] = $repository;
                        $rows[] = $row;
                    }
                }

                foreach ($rows as $row) {
                    $snapshotModel = new ElasticsearchSnapshotModel();
                    $snapshotModel->convert($row);
                    $snapshots[] = $snapshotModel;
                }
            } catch (CallException $e) {
            }
        }
        usort($snapshots, [$this, 'sortByStartTime']);

        return $this->filter($snapshots, $filter);
    }

    private function sortByStartTime(ElasticsearchSnapshotModel $a, ElasticsearchSnapshotModel $b): int
    {
        return $b->getStartTime() <=> $a->getStartTime();
    }

    /**
     * @param array<mixed> $snapshots
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function filter(array $snapshots, array $filter = []): array
    {
        $snapshotsWithFilter = [];

        foreach ($snapshots as $row) {
            $score = 0;

            if (true === isset($filter['state']) && 0 < count($filter['state'])) {
                if (false === in_array($row->getState(), $filter['state'])) {
                    $score--;
                }
            }

            if (true === isset($filter['repository']) && 0 < count($filter['repository'])) {
                if (false === in_array($row->getRepository(), $filter['repository'])) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $snapshotsWithFilter[] = $row;
            }
        }

        return $snapshotsWithFilter;
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
}
