<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexManager
{
    /**
     * @required
     */
    public function setCallManager(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function getIndex($index)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices/'.$index);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $index = false;
        } else {
            $index1 = $callResponse->getContent();
            $index1 = $index1[0];

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.$index);
            $callResponse = $this->callManager->call($callRequest);
            $index2 = $callResponse->getContent();

            $index = array_merge($index1, $index2[key($index2)]);
        }

        return $index;
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
