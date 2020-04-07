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
        $call = new CallRequestModel();
        $call->setPath('/_cat/indices/'.$index);
        $index = $this->callManager->call($call);
        $index = $index[0];

        return $index;
    }

    public function selectIndices()
    {
        $indices = [];

        $call = new CallRequestModel();
        $call->setPath('/_cat/indices');
        $call->setQuery(['s' => 'index', 'h' => 'index']);
        $rows = $this->callManager->call($call);

        foreach ($rows as $row) {
            $indices[] = $row['index'];
        }

        return $indices;
    }

    public function selectAliases()
    {
        $aliases = [];

        $call = new CallRequestModel();
        $call->setPath('/_cat/aliases');
        $call->setQuery(['s' => 'alias', 'h' => 'alias']);
        $rows = $this->callManager->call($call);

        foreach ($rows as $row) {
            $aliases[] = $row['alias'];
        }

        $aliases = array_unique($aliases);

        return $aliases;
    }
}
