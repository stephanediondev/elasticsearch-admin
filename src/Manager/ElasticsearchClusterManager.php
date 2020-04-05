<?php

namespace App\Manager;

use App\Manager\CallManager;
use App\Model\CallModel;

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
        $call = new CallModel();
        $call->setPath('/_cluster/settings');
        $call->setQuery(['include_defaults' => 'true', 'flat_settings' => 'true']);
        $results = $this->callManager->call($call);

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
