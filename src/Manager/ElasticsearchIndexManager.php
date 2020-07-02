<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexManager extends AbstractAppManager
{
    public function getByName(string $name)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices/'.$name);
        $callRequest->setQuery(['bytes' => 'b']);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $index = false;
        } else {
            $index1 = $callResponse->getContent();
            $index1 = $index1[0];

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.$name);
            $callRequest->setQuery(['flat_settings' => 'true']);
            $callResponse = $this->callManager->call($callRequest);
            $index2 = $callResponse->getContent();

            $index = array_merge($index1, $index2[key($index2)]);
            $index['is_system'] = '.' == substr($index['index'], 0, 1);

            if (true == isset($index['mappings']) && false == isset($index['mappings']['properties']) && 0 < count($index['mappings'])) {
                $firstKey = array_key_first($index['mappings']);
                if (true == isset($index['mappings'][$firstKey]['properties'])) {
                    $index['mappings']['properties'] = $index['mappings'][$firstKey]['properties'];
                }
            }

            if (true == isset($index['mappings']) && true == isset($index['mappings']['properties'])) {
                $index['mappings_flat'] = $this->mappingsFlat($index['mappings']['properties']);
            } else {
                $index['mappings_flat'] = [];
            }

            $index['has_geo_point'] = in_array('geo_point', $index['mappings_flat']);
        }

        return $index;
    }

    public function getAll(array $query)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        return $callResponse->getContent();
    }

    public function closeIndex($index)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index.'/_close');
        return $this->callManager->call($callRequest);
    }

    public function openIndex($index)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index.'/_open');
        return $this->callManager->call($callRequest);
    }

    private function mappingsFlat(array $properties, string $prefix = '')
    {
        $mappingsFlat = [];
        foreach ($properties as $property => $keys) {
            if ('' != $prefix) {
                $property = $prefix.'.'.$property;
            }

            if (true == isset($keys['properties'])) {
                $mappingsFlat = array_merge($mappingsFlat, $this->mappingsFlat($keys['properties'], $property));
            } else {
                $mappingsFlat[$property] = $keys['type'];
            }
        }
        return $mappingsFlat;
    }

    public function selectIndices()
    {
        $indices = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices');
        $callRequest->setQuery(['s' => 'index', 'h' => 'index']);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $row) {
            $indices[] = $row['index'];
        }

        return $indices;
    }

    public function selectAliases()
    {
        $aliases = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/aliases');
        $callRequest->setQuery(['s' => 'alias', 'h' => 'alias']);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $row) {
            $aliases[] = $row['alias'];
        }

        $aliases = array_unique($aliases);

        return $aliases;
    }
}
