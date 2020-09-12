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
        'license' => null,
        'versions' => null,
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
            $this->clusterHealth = $this->elasticsearchClusterManager->getClusterHealth();

            $this->clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

            $parameters = [
                'cluster_settings' => $this->clusterSettings,
            ];
            $nodes = $this->elasticsearchNodeManager->getAll($parameters);

            $nodesUp = [];
            $nodesDiskThreshold = [];
            foreach ($nodes as $node) {
                $nodesUp[] = $node['name'];
                $nodesDiskThreshold[$node['name']] = [
                    'watermark' => $node['disk_threshold'] ?? 'watermark_ok',
                    'percent' => $node['disk.used_percent'],
                ];
            }

            $versions = $this->elasticsearchNodeManager->getVersions($nodes);

            $licenseInfo = 'license_ok';

            if (true === $this->callManager->hasFeature('license')) {
                if (false === $this->callManager->hasFeature('_xpack_endpoint_removed')) {
                    $this->endpoint = '_xpack/license';
                } else {
                    $this->endpoint = '_license';
                }

                $callRequest = new CallRequestModel();
                $callRequest->setPath('/'.$this->endpoint);
                $callResponse = $this->callManager->call($callRequest);
                $license = $callResponse->getContent();
                $license = $license['license'];

                if ('basic' != $license['type'] && true === isset($license['expiry_date_in_millis'])) {
                    $now = (new \Datetime());
                    $expire = new \Datetime(date('Y-m-d H:i:s', substr($license['expiry_date_in_millis'], 0, -3)));
                    $interval = $now->diff($expire);

                    if (1 >= $interval->format('%a')) {
                        $licenseInfo = 'license_1_day';
                    } else if (15 > $interval->format('%a')) {
                        $licenseInfo = 'license_15_days';
                    } else if (30 > $interval->format('%a')) {
                        $licenseInfo = 'license_30_days';
                    }
                }
            }

            if (true === $this->infoFileExists()) {
                $previousInfo = json_decode(file_get_contents($this->filename), true);
            } else {
                $previousInfo = $this->defaultInfo;
            }

            $lastInfo = [
                'cluster_health' => $this->clusterHealth['status'],
                'nodes' => $nodesUp,
                'disk_threshold' => $nodesDiskThreshold,
                'license' => $licenseInfo,
                'versions' => $versions,
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

        if (true === isset($previousInfo['cluster_health']) && $previousInfo['cluster_health'] && $previousInfo['cluster_health'] != $lastInfo['cluster_health']) {
            $notification = new AppNotificationModel();
            $notification->setTitle($this->clusterHealth['cluster_name'].': health');
            $notification->setBody(ucfirst($lastInfo['cluster_health']));
            $notification->setIcon('favicon-'.$lastInfo['cluster_health'].'-144.png');

            $notifications[] = $notification;
        }

        if (true === isset($previousInfo['nodes']) && $previousInfo['nodes']) {
            $nodesDown = array_diff($previousInfo['nodes'], $lastInfo['nodes']);
            foreach ($nodesDown as $nodeDown) {
                $notification = new AppNotificationModel();
                $notification->setTitle($this->clusterHealth['cluster_name'].': node down');
                $notification->setBody($nodeDown);
                $notification->setIcon('favicon-red-144.png');

                $notifications[] = $notification;
            }

            $nodesUp = array_diff($lastInfo['nodes'], $previousInfo['nodes']);
            foreach ($nodesUp as $nodeUp) {
                $notification = new AppNotificationModel();
                $notification->setTitle($this->clusterHealth['cluster_name'].': node up');
                $notification->setBody($nodeUp);
                $notification->setIcon('favicon-green-144.png');

                $notifications[] = $notification;
            }
        }

        if (true === isset($previousInfo['disk_threshold']) && $previousInfo['disk_threshold']) {
            foreach ($lastInfo['disk_threshold'] as $node => $values) {
                if (true === isset($previousInfo['disk_threshold'][$node]) && $previousInfo['disk_threshold'][$node]['watermark'] != $values['watermark']) {
                    $notification = new AppNotificationModel();
                    $notification->setTitle($this->clusterHealth['cluster_name'].': disk threshold');
                    $notification->setBody($node.' '.$values['percent'].'%');
                    $notification->setIcon('favicon-'.$this->getColor($values['watermark']).'-144.png');

                    $notifications[] = $notification;
                }
            }
        }

        if (true === isset($previousInfo['license']) && $previousInfo['license'] && $previousInfo['license'] != $lastInfo['license']) {
            $notification = new AppNotificationModel();
            $notification->setTitle($this->clusterHealth['cluster_name'].': license');
            switch ($lastInfo['license']) {
                case 'license_ok':
                    $notification->setBody('Valid');
                    break;
                case 'license_30_days':
                    $notification->setBody('Expires in 30 days');
                    break;
                case 'license_15_days':
                    $notification->setBody('Expires in 15 days');
                    break;
                case 'license_1_day':
                    $notification->setBody('Expires today');
                    break;
            }
            $notification->setIcon('favicon-'.$this->getColor($lastInfo['license']).'-144.png');

            $notifications[] = $notification;
        }

        if (true === isset($previousInfo['versions']) && $previousInfo['versions'] && count($previousInfo['versions']) != count($lastInfo['versions'])) {
            $notification = new AppNotificationModel();
            $notification->setTitle($this->clusterHealth['cluster_name'].': ES version');
            if (1 == count($lastInfo['versions'])) {
                $notification->setBody('One version ('.$lastInfo['versions'][0].')');
                $notification->setIcon('favicon-green-144.png');
            } else {
                $notification->setBody('Multiple versions ('.implode(', ', $lastInfo['versions']).')');
                $notification->setIcon('favicon-red-144.png');
            }

            $notifications[] = $notification;
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
            case 'license_1_day':
            case 'watermark_flood_stage':
                return 'red';
            case 'license_15_days':
            case 'watermark_high':
                return 'orange';
            case 'license_30_days':
            case 'watermark_low':
                return 'yellow';
            case 'license_ok':
            case 'watermark_ok':
                return 'green';
        }

        return 'gray';
    }
}
