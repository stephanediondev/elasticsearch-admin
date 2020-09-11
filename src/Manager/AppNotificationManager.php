<?php

namespace App\Manager;

use App\Exception\ConnectionException;
use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\AppNotificationModel;
use Symfony\Component\HttpFoundation\Response;

class AppNotificationManager extends AbstractAppManager
{
    private $defaultInfo = [
        'cluster_health' => null,
        'nodes' => null,
        'disk_threshold' => null,
    ];

    private $filename = __DIR__.'/../../info.json';

    /**
     * @required
     */
    public function setClusterManager(ElasticsearchClusterManager $elasticsearchClusterManager)
    {
        $this->elasticsearchClusterManager = $elasticsearchClusterManager;
    }

    /**
     * @required
     */
    public function setNodeManager(ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    public function getAll(): array
    {
        try {
            $clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

            $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

            $query = [
                'cluster_settings' => $clusterSettings,
            ];
            $nodes = $this->elasticsearchNodeManager->getAll($query);
            $nodesUp = [];
            $nodesDiskThreshold = [];
            foreach ($nodes as $node) {
                $nodesUp[] = $node['name'];
                $nodesDiskThreshold[$node['name']] = [
                    'watermark' => $node['disk_threshold'] ?? 'watermark_ok',
                    'percent' => $node['disk.used_percent'],
                ];
            }

            if (true === $this->infoFileExists()) {
                $previousInfo = json_decode(file_get_contents($this->filename), true);
            } else {
                $previousInfo = $this->defaultInfo;
            }

            $lastInfo = [
                'cluster_health' => $clusterHealth['status'],
                'nodes' => $nodesUp,
                'disk_threshold' => $nodesDiskThreshold,
            ];

            file_put_contents($this->filename, json_encode($lastInfo));

            return $this->compareInfo($previousInfo, $lastInfo);

        } catch (ConnectionException $e) {
            return [];
        }
    }

    public function compareInfo(array $previousInfo, array $lastInfo): array
    {
        $notifications = [];

        if ($previousInfo['cluster_health'] && $previousInfo['cluster_health'] != $lastInfo['cluster_health']) {
            $notification = new AppNotificationModel();
            $notification->setTitle('cluster health');
            $notification->setBody($previousInfo['cluster_health'].' => '.$lastInfo['cluster_health']);
            $notification->setIcon('favicon-'.$lastInfo['cluster_health'].'-144.png');

            $notifications[] = $notification;
        }

        if ($previousInfo['nodes']) {
            $nodesDown = array_diff($previousInfo['nodes'], $lastInfo['nodes']);
            foreach ($nodesDown as $nodeDown) {
                $notification = new AppNotificationModel();
                $notification->setTitle('node down');
                $notification->setBody($nodeDown);
                $notification->setIcon('favicon-red-144.png');

                $notifications[] = $notification;
            }

            $nodesUp = array_diff($lastInfo['nodes'], $previousInfo['nodes']);
            foreach ($nodesUp as $nodeUp) {
                $notification = new AppNotificationModel();
                $notification->setTitle('node up');
                $notification->setBody($nodeUp);
                $notification->setIcon('favicon-green-144.png');

                $notifications[] = $notification;
            }
        }

        if ($previousInfo['disk_threshold']) {
            foreach ($lastInfo['disk_threshold'] as $node => $values) {
                if (true === isset($previousInfo['disk_threshold'][$node]) && $previousInfo['disk_threshold'][$node]['watermark'] != $values['watermark']) {
                    $notification = new AppNotificationModel();
                    $notification->setTitle('disk threshold');
                    $notification->setBody($node.' '.$values['percent'].'%');
                    $notification->setIcon('favicon-'.$this->getColor($values['watermark']).'-144.png');

                    $notifications[] = $notification;
                }
            }
        }

        return $notifications;
    }

    public function infoFileExists()
    {
        return file_exists($this->filename);
    }

    private function getColor($value)
    {
        switch ($value) {
            case 'watermark_flood_stage':
                return 'red';
            case 'watermark_high':
                return 'orange';
            case 'watermark_low':
                return 'yellow';
            case 'watermark_ok':
                return 'green';
        }

        return 'gray';
    }
}
