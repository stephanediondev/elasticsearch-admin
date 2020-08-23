<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchIndexModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchIndexModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices/'.$name);
        $callRequest->setQuery(['bytes' => 'b', 'h' => 'uuid,index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string,sth']);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $indexModel = null;
        } else {
            $index1 = $callResponse->getContent();
            $index1 = $index1[0];

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.$name);
            $callRequest->setQuery(['flat_settings' => 'true']);
            $callResponse = $this->callManager->call($callRequest);
            $index2 = $callResponse->getContent();

            $index = array_merge($index1, $index2[key($index2)]);

            if (true === isset($index['mappings']) && false === isset($index['mappings']['properties']) && 0 < count($index['mappings'])) {
                $firstKey = array_key_first($index['mappings']);
                if (true === isset($index['mappings'][$firstKey]['properties'])) {
                    $index['mappings']['properties'] = $index['mappings'][$firstKey]['properties'];
                }
            }

            if (true === isset($index['mappings']) && true === isset($index['mappings']['properties'])) {
                $index['mappings_flat'] = $this->mappingsFlat($index['mappings']['properties']);
            } else {
                $index['mappings_flat'] = [];
            }

            $indexModel = new ElasticsearchIndexModel();
            $indexModel->convert($index);
        }

        return $indexModel;
    }

    public function getAll(array $query, array $filter = []): array
    {
        $indices = [];
        $aliases = [];

        $queryAliases = ['h' => 'alias,index'];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/aliases');
        $callRequest->setQuery($queryAliases);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        if (Response::HTTP_OK == $callResponse->getCode()) {
            foreach ($rows as $row) {
                $aliases[$row['index']][$row['alias']] = [];
            }
        }

        $callRequest = new CallRequestModel();
        if (true === isset($filter['name']) && '' != $filter['name']) {
            $callRequest->setPath('/_cat/indices/'.$filter['name']);
        } else {
            $callRequest->setPath('/_cat/indices');
        }
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results) {
            foreach ($results as $row) {
                if (true === isset($aliases[$row['index']])) {
                    $row['aliases'] = $aliases[$row['index']];
                }
                $indexModel = new ElasticsearchIndexModel();
                $indexModel->convert($row);
                $indices[] = $indexModel;
            }
        }

        return $this->filter($indices, $filter);
    }

    public function filter(array $indices, array $filter = []): array
    {
        $indicesWithFilter = [];

        foreach ($indices as $row) {
            $score = 0;

            if (true === isset($filter['health']) && 0 < count($filter['health'])) {
                if (false === in_array($row->getHealth(), $filter['health'])) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $indicesWithFilter[] = $row;
            }
        }

        return $indicesWithFilter;
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function closeByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_close');
        return $this->callManager->call($callRequest);
    }

    public function openByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_open');
        return $this->callManager->call($callRequest);
    }

    public function freezeByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_freeze');
        return $this->callManager->call($callRequest);
    }

    public function unfreezeByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_unfreeze');
        return $this->callManager->call($callRequest);
    }

    public function forceMergeByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_forcemerge');
        return $this->callManager->call($callRequest);
    }

    public function cacheClearByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_cache/clear');
        return $this->callManager->call($callRequest);
    }

    public function flushByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_flush');
        return $this->callManager->call($callRequest);
    }

    public function refreshByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_refresh');
        return $this->callManager->call($callRequest);
    }

    public function emptyByName(string $name): CallResponseModel
    {
        $json = [
            'query' => [
                'match_all' => (object)[],
            ],
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$name.'/_delete_by_query');
        $callRequest->setJson($json);
        return $this->callManager->call($callRequest);
    }

    private function mappingsFlat(array $properties, string $prefix = '')
    {
        $mappingsFlat = [];
        foreach ($properties as $property => $keys) {
            if ('' != $prefix) {
                $property = $prefix.'.'.$property;
            }

            if (true === isset($keys['type']) && true === in_array($keys['type'], ['nested', 'geo_shape'])) {
                $mappingsFlat[$property] = $keys;
            } elseif (true === isset($keys['properties'])) {
                $mappingsFlat = array_merge($mappingsFlat, $this->mappingsFlat($keys['properties'], $property));
            } else {
                $mappingsFlat[$property] = $keys;
            }
        }
        return $mappingsFlat;
    }

    public function selectIndices(): array
    {
        $query = ['h' => 'index'];
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = 'index';
        }
        $rows = $this->getAll($query);

        $indices = [];
        foreach ($rows as $row) {
            $indices[] = $row->getName();
        }

        return $indices;
    }

    public function selectAliases(): array
    {
        $aliases = [];

        $query = ['h' => 'alias'];
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = 'alias';
        }
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/aliases');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        if (Response::HTTP_OK == $callResponse->getCode()) {
            foreach ($rows as $row) {
                $aliases[] = $row['alias'];
            }
        }

        $aliases = array_unique($aliases);

        return $aliases;
    }
}
