<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallRequestModel;

class ElasticsearchClusterManager
{
    /**
     * @required
     */
    public function setCallManager(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    public function getClusterSettings()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/settings');
        $callRequest->setQuery(['include_defaults' => 'true', 'flat_settings' => 'true']);
        $results = $this->callManager->call($callRequest);

        $clusterSettings = [];
        foreach ($results as $type => $rows) {
            foreach ($rows as $k => $v) {
                if (false == array_key_exists($k, $clusterSettings)) {
                    $clusterSettings[$k] = $v;
                }
            }
        }

        return $clusterSettings;
    }
}
