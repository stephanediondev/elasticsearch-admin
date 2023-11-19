<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchNodeModel;

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

    /**
     * @param array<mixed>|null $parameters
     * @return array<mixed>
     */
    public function getAll(?array $parameters): array
    {
        if (false === isset($parameters['sort'])) {
            $parameters['sort'] = 'name:asc';
        }

        $diskThresholdEnabled = false;

        $diskWatermarks = [];

        $unit = false;

        if (true === isset($parameters['cluster_settings'])) {
            if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) && 'true' == $parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) {
                $diskThresholdEnabled = true;

                if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.flood_stage'])) {
                    $diskWatermarks['flood_stage'] = $parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.flood_stage'];
                }
                $diskWatermarks['high'] = $parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.high'];
                $diskWatermarks['low'] = $parameters['cluster_settings']['cluster.routing.allocation.disk.watermark.low'];

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
                        if ($key) {
                            $diskWatermarks[$watermark] = $value * pow(1024, $key);
                        }
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
                    } elseif (true === isset($diskWatermarks['high']) && $diskWatermarks['high'] <= $node['disk.used_percent']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_high';
                    } elseif (true === isset($diskWatermarks['low']) && $diskWatermarks['low'] <= $node['disk.used_percent']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_low';
                    }
                }

                if ('size' == $unit && true === isset($node['disk.avail'])) {
                    if (true === isset($diskWatermarks['flood_stage']) && $diskWatermarks['flood_stage'] >= $node['disk.avail']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_flood_stage';
                    } elseif (true === isset($diskWatermarks['high']) && $diskWatermarks['high'] >= $node['disk.avail']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_high';
                    } elseif (true === isset($diskWatermarks['low']) && $diskWatermarks['low'] >= $node['disk.avail']) {
                        $nodes[$node['name']]['disk_threshold'] = 'watermark_low';
                    }
                }
            } else {
                if (true === isset($node['disk.used_percent']) && 90 < $node['disk.used_percent']) {
                    $nodes[$node['name']]['disk_threshold'] = 'warning';
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

    /**
     * @param array<mixed> $nodes
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function filter(array $nodes, array $filter = []): array
    {
        $nodesWithFilter = [];

        foreach ($nodes as $row) {
            $score = 0;

            if (true === isset($row['node.role']) && true === isset($filter['roles']) && 0 < count($filter['roles'])) {
                $roles = str_split($row['node.role']);
                foreach ($roles as $role) {
                    if (true === in_array($role, $filter['roles'])) {
                        $score++;
                    }
                }
            } else {
                $score++;
            }

            if (true === isset($row['version']) && true === isset($filter['version']) && $filter['version']) {
                if ($row['version'] !== $filter['version']) {
                    $score--;
                }
            }

            if (0 < $score) {
                $nodesWithFilter[] = $row;
            }
        }

        return $nodesWithFilter;
    }

    /**
     * @param array<mixed>|null $filter
     * @return array<mixed>
     */
    public function selectNodes(?array $filter = []): array
    {
        $nodes = [];

        $query = [
            'h' => 'id,name,node.role',
            'full_id' => 'true',
        ];
        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = 'name';
        }
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/nodes');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        $rows = $this->filter($rows, $filter);

        foreach ($rows as $row) {
            $nodes[$row['id']] = $row['name'];
        }

        return $nodes;
    }

    /**
     * @param array<mixed> $nodes
     * @return array<mixed>
     */
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
