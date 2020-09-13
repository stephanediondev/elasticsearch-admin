<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchNodeModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchNodeManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchNodeModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$name);
        $callRequest->setQuery(['flat_settings' => 'true']);
        $callResponse = $this->callManager->call($callRequest);
        $node = $callResponse->getContent();

        if (false === isset($node['nodes'][key($node['nodes'])])) {
            $nodeModel = null;
        } else {
            $id = key($node['nodes']);
            $node = $node['nodes'][$id];
            $node['id'] = $id;

            $nodeModel = new ElasticsearchNodeModel();
            $nodeModel->convert($node);
        }

        return $nodeModel;
    }

    public function getAll(?array $parameters): array
    {
        if (false === isset($parameters['sort'])) {
            $parameters['sort'] = 'name:asc';
        }

        $diskThresholdEnabled = false;

        if (true === isset($parameters['cluster_settings'])) {
            if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) && 'true' == $parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) {
                $diskThresholdEnabled = true;

                $diskWatermarks = [];
                if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.flood_stage'])) {
                    $diskWatermarks['flood_stage'] = $parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.flood_stage'];
                }
                $diskWatermarks['high'] = $parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.high'];
                $diskWatermarks['low'] = $parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.low'];

                $unit = false;
                $types = ['b', 'kb', 'mb', 'gb', 'tb', 'p'];
                foreach ($diskWatermarks as $watermark => $value) {
                    if (strstr($value, '%')) {
                        $unit = 'percent';
                        $diskWatermarks[$watermark] = str_replace('%', '', $value);
                    } else {
                        $unit = 'size';
                        $type = strtolower(substr($value, -2));
                        $value = intval(substr($value, 0, -2));
                        $key = array_search($type, $types);
                        $diskWatermarks[$watermark] = $value * pow(1024, $key);
                    }
                }
            }
        }

        $nodes = [];

        $query = [
            'bytes' => 'b',
            'h' => 'name,file_desc.max,file_desc.current,file_desc.percent,node.role,jdk,load_1m,load_5m,load_15m,disk.used_percent,ram.percent,cpu,uptime,master,disk.total,disk.used,disk.avail,ram.current,ram.max,heap.percent,heap.max,heap.current',
        ];
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = $parameters['sort'];
        }
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/nodes');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $nodes1 = $callResponse->getContent();

        foreach ($nodes1 as $node) {
            $nodes[$node['name']] = $node;

            if ($diskThresholdEnabled) {
                if ('percent' == $unit && true === isset($node['disk.used_percent'])) {
                    if (true === isset($diskWatermarks['flood_stage']) && $diskWatermarks['flood_stage'] <= $node['disk.used_percent']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_flood_stage';
                    } elseif ($diskWatermarks['high'] <= $node['disk.used_percent']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_high';
                    } elseif ($diskWatermarks['low'] <= $node['disk.used_percent']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_low';
                    }
                }

                if ('size' == $unit && true === isset($node['disk.avail'])) {
                    if (true === isset($diskWatermarks['flood_stage']) && $diskWatermarks['flood_stage'] >= $node['disk.avail']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_flood_stage';
                    } elseif ($diskWatermarks['high'] >= $node['disk.avail']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_high';
                    } elseif ($diskWatermarks['low'] >= $node['disk.avail']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_low';
                    }
                }
            }
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes');
        $callResponse = $this->callManager->call($callRequest);
        $nodes2 = $callResponse->getContent();

        foreach ($nodes2['nodes'] as $node) {
            if (true === isset($nodes[$node['name']])) {
                $nodes[$node['name']] = array_merge($node, $nodes[$node['name']]);
            }
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/stats');
        $callResponse = $this->callManager->call($callRequest);
        $nodes3 = $callResponse->getContent();

        foreach ($nodes3['nodes'] as $node) {
            if (true === isset($nodes[$node['name']])) {
                $nodes[$node['name']]['stats'] = $node;
            }
        }

        return $nodes;
    }

    public function filter(array $nodes, array $filter = []): array
    {
        $nodesWithFilter = [];

        foreach ($nodes as $row) {
            $score = 0;

            if (true === isset($row['node.role'])) {
                $roles = str_split($row['node.role']);
                foreach ($this->filterletters() as $letter => $role) {
                    if (true === isset($filter[$role])) {
                        if ('yes' === $filter[$role] && false === in_array($letter, $roles)) {
                            $score--;
                        }
                        if ('no' === $filter[$role] && true === in_array($letter, $roles)) {
                            $score--;
                        }
                    }
                }
            }

            if (true === isset($row['version']) && true === isset($filter['version']) && $filter['version']) {
                if ($row['version'] !== $filter['version']) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $nodesWithFilter[] = $row;
            }
        }

        return $nodesWithFilter;
    }

    public function selectNodes(): array
    {
        $nodes = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows['nodes'] as $k => $row) {
            $nodes[$k] = $row['name'];
        }

        return $nodes;
    }

    public function filterletters()
    {
        return [
            'm' => 'master',
            'd' => 'data',
            'v' => 'voting_only',
            'i' => 'ingest',
        ];
    }

    public function getVersions(array $nodes): array
    {
        $versions = [];
        foreach ($nodes as $node) {
            if (true === isset($node['version'])) {
                $versions[] = $node['version'];
            }
        }
        $versions = array_unique($versions);
        sort($versions);

        return $versions;
    }
}
