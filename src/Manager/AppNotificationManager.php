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

    protected ElasticsearchClusterManager $elasticsearchClusterManager;

    protected ElasticsearchNodeManager $elasticsearchNodeManager;

    protected $clusterHealth;

    protected $clusterSettings;

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
        $query['size'] = 1000;

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-notifications/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $notifications = [];

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $notification = ['id' => $row['_id']];
                $notification = array_merge($notification, $row['_source']);

                $appNotificationModel = new AppNotificationModel();
                $appNotificationModel->convert($notification);
                $notifications[] = $appNotificationModel;
            }
            usort($notifications, [$this, 'sortByCreatedAt']);
        }

        return $notifications;
    }

    private function sortByCreatedAt(AppNotificationModel $a, AppNotificationModel $b): int
    {
        return $b->getCreatedAt()->format('Y-m-d H:i:s') <=> $a->getCreatedAt()->format('Y-m-d H:i:s');
    }

    public function send(AppNotificationModel $appNotificationModel): CallResponseModel
    {
        $json = $appNotificationModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-notifications/_doc');
        } else {
            $callRequest->setPath('/.elasticsearch-admin-notifications/doc/');
        }
        $callRequest->setJson($json);
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }

    public function generate(): array
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
                $license = $this->callManager->getLicense();

                if ($license && 'basic' != $license['type'] && true === isset($license['expiry_date_in_millis'])) {
                    $now = (new \Datetime());
                    $expire = new \Datetime(date('Y-m-d H:i:s', intval(substr($license['expiry_date_in_millis'], 0, -3))));
                    $interval = $now->diff($expire);

                    if (1 >= $interval->format('%a')) {
                        $licenseInfo = 'license_1_day';
                    } elseif (15 > $interval->format('%a')) {
                        $licenseInfo = 'license_15_days';
                    } elseif (30 > $interval->format('%a')) {
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
            $notification->setType(AppNotificationModel::TYPE_CLUSTER_HEALTH);
            $notification->setCluster($this->clusterHealth['cluster_name']);
            $notification->setTitle('health');
            $notification->setContent(ucfirst($lastInfo['cluster_health']));
            $notification->setColor($lastInfo['cluster_health']);

            $notifications[] = $notification;
        }

        if (true === isset($previousInfo['nodes']) && $previousInfo['nodes']) {
            $nodesDown = array_diff($previousInfo['nodes'], $lastInfo['nodes']);
            foreach ($nodesDown as $nodeDown) {
                $notification = new AppNotificationModel();
                $notification->setType(AppNotificationModel::TYPE_NODE_DOWN);
                $notification->setCluster($this->clusterHealth['cluster_name']);
                $notification->setTitle('node down');
                $notification->setContent($nodeDown);
                $notification->setColor('red');

                $notifications[] = $notification;
            }

            $nodesUp = array_diff($lastInfo['nodes'], $previousInfo['nodes']);
            foreach ($nodesUp as $nodeUp) {
                $notification = new AppNotificationModel();
                $notification->setType(AppNotificationModel::TYPE_NODE_UP);
                $notification->setCluster($this->clusterHealth['cluster_name']);
                $notification->setTitle('node up');
                $notification->setContent($nodeUp);
                $notification->setColor('green');

                $notifications[] = $notification;
            }
        }

        if (true === isset($previousInfo['disk_threshold']) && $previousInfo['disk_threshold']) {
            foreach ($lastInfo['disk_threshold'] as $node => $values) {
                if (true === isset($previousInfo['disk_threshold'][$node]) && $previousInfo['disk_threshold'][$node]['watermark'] != $values['watermark']) {
                    $notification = new AppNotificationModel();
                    $notification->setType(AppNotificationModel::TYPE_DISK_THRESHOLD);
                    $notification->setCluster($this->clusterHealth['cluster_name']);
                    $notification->setTitle('disk threshold');
                    $notification->setContent($node.' '.$values['percent'].'%');
                    $notification->setColor($this->getColor($values['watermark']));

                    $notifications[] = $notification;
                }
            }
        }

        if (true === isset($previousInfo['license']) && $previousInfo['license'] && $previousInfo['license'] != $lastInfo['license']) {
            $notification = new AppNotificationModel();
            $notification->setType(AppNotificationModel::TYPE_LICENSE);
            $notification->setCluster($this->clusterHealth['cluster_name']);
            $notification->setTitle('license');
            switch ($lastInfo['license']) {
                case 'license_ok':
                    $notification->setContent('Valid');
                    break;
                case 'license_30_days':
                    $notification->setContent('Expires in 30 days');
                    break;
                case 'license_15_days':
                    $notification->setContent('Expires in 15 days');
                    break;
                case 'license_1_day':
                    $notification->setContent('Expires today');
                    break;
            }
            $notification->setColor($this->getColor($lastInfo['license']));

            $notifications[] = $notification;
        }

        if (true === isset($previousInfo['versions']) && $previousInfo['versions'] && count($previousInfo['versions']) != count($lastInfo['versions'])) {
            $notification = new AppNotificationModel();
            $notification->setType(AppNotificationModel::TYPE_VERSION);
            $notification->setCluster($this->clusterHealth['cluster_name']);
            $notification->setTitle('ES version');
            if (1 == count($lastInfo['versions'])) {
                $notification->setContent('One version ('.$lastInfo['versions'][0].')');
                $notification->setColor('green');
            } else {
                $notification->setContent('Multiple versions ('.implode(', ', $lastInfo['versions']).')');
                $notification->setColor('red');
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
