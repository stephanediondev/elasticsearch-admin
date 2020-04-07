<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallRequestModel;

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
        $index = $this->callManager->call($callRequest);
        $index = $index[0];

        return $index;
    }

    public function selectIndices()
    {
        $indices = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices');
        $callRequest->setQuery(['s' => 'index', 'h' => 'index']);
        $rows = $this->callManager->call($callRequest);

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
        $rows = $this->callManager->call($callRequest);

        foreach ($rows as $row) {
            $aliases[] = $row['alias'];
        }

        $aliases = array_unique($aliases);

        return $aliases;
    }
}
