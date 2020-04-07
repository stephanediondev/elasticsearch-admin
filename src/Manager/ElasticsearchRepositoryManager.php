<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallRequestModel;

class ElasticsearchRepositoryManager
{
    /**
     * @required
     */
    public function setCallManager(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function selectRepositories()
    {
        $repositories = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/repositories');
        $callRequest->setQuery(['s' => 'id', 'h' => 'id']);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $row) {
            $repositories[] = $row['id'];
        }

        return $repositories;
    }
}
