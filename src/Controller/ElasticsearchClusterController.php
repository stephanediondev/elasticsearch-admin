<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\ElasticsearchClusterSettingType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\ElasticsearchClusterSettingModel;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchClusterController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/cluster", name="cluster")
     */
    public function read(Request $request): Response
    {
        $clusterStats = $this->elasticsearchClusterManager->getClusterStats();

        $clusterState = $this->elasticsearchClusterManager->getClusterState();

        $nodes = [];
        foreach ($clusterState['nodes'] as $k => $node) {
            $nodes[$k] = $node['name'];
        }

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read.html.twig', [
            'master_node' => $nodes[$clusterState['master_node']] ?? false,
            'indices' => $clusterStats['indices']['count'] ?? false,
            'shards' => $clusterStats['indices']['shards']['total'] ?? false,
            'documents' => $clusterStats['indices']['docs']['count'] ?? false,
            'total_size' => $clusterStats['indices']['store']['size_in_bytes'] ?? false,
        ]);
    }

    /**
     * @Route("/cluster/allocation/explain", name="cluster_allocation_explain")
     */
    public function allocationExplain(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_ALLOCATION_EXPLAIN', 'global');

        if (false == $this->callManager->hasFeature('allocation_explain')) {
            throw new AccessDeniedHttpException();
        }

        $allocationExplain = false;

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_cluster/allocation/explain');
            $callResponse = $this->callManager->call($callRequest);
            $allocationExplain = $callResponse->getContent();
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->renderAbstract($request, 'Modules/cluster/cluster_allocation_explain.html.twig', [
            'allocation_explain' => $allocationExplain,
        ]);
    }

    /**
     * @Route("/cluster/settings", name="cluster_settings")
     */
    public function settings(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTINGS', 'global');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/settings');
        $callRequest->setQuery(['include_defaults' => 'true', 'flat_settings' => 'true']);
        $callResponse = $this->callManager->call($callRequest);
        $clusterSettings = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read_settings.html.twig', [
            'cluster_settings' => $clusterSettings,
            'cluster_settings_not_dynamic' => $this->elasticsearchClusterManager->getClusterSettingsNotDynamic(),
        ]);
    }

    /**
     * @Route("/cluster/settings/{type}/{setting}/edit", name="cluster_settings_edit")
     */
    public function edit(Request $request, string $type, string $setting): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTING_EDIT', 'global');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedHttpException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true == array_key_exists($setting, $clusterSettings)) {
            $clusterSettingModel = new ElasticsearchClusterSettingModel();
            $clusterSettingModel->setType($type);
            $clusterSettingModel->setSetting($setting);
            $clusterSettingModel->setValue($clusterSettings[$setting]);
            $form = $this->createForm(ElasticsearchClusterSettingType::class, $clusterSettingModel);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $json = $clusterSettingModel->getJson();
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('PUT');
                    $callRequest->setPath('/_cluster/settings');
                    $callRequest->setJson($json);
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));

                    return $this->redirectToRoute('cluster_settings');
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/cluster/cluster_edit_setting.html.twig', [
                'form' => $form->createView(),
                'type' => $type,
                'setting' => $setting,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/cluster/settings/{type}/{setting}/remove", name="cluster_settings_remove")
     */
    public function remove(Request $request, string $type, string $setting): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTING_REMOVE', 'global');

        if (false == $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedHttpException();
        }

        $json = [
            $type => [
                $setting => null,
            ],
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_cluster/settings');
        $callRequest->setJson($json);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('cluster_settings');
    }

    /**
     * @Route("/cluster/audit", name="cluster_audit")
     */
    public function audit(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_AUDIT', 'global');

        $parameters = [];

        $parameters['cluster_settings'] = $this->elasticsearchClusterManager->getClusterSettings();

        $parameters['root'] = $this->callManager->getRoot();

        $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();

        $nodes = $this->elasticsearchNodeManager->getAll();

        $versions = [];

        $plugins = [];
        $nodesPlugins = [];

        $cpuPercent = false;
        $heapSize = false;
        $fileDescriptors = false;

        $nodesLimit = [
            'cpu_over_90' => [],
            'heap_size_over_50' => [],
            'file_descriptors_under_65535' => [],
        ];

        foreach ($nodes as $node) {
            $versions[] = $node['version'];
            $nodesPlugins[$node['name']] = [];

            foreach ($node['plugins'] as $plugin) {
                $nodesPlugins[$node['name']][] = $plugin['name'];
                $plugins[] = $plugin['name'];
            }

            if (true == isset($node['stats']['os']['cpu']['percent'])) {
                $cpuPercent = true;
                if (90 < $node['stats']['os']['cpu']['percent']) {
                    $nodesLimit['cpu_over_90'][$node['name']] = $node['stats']['os']['cpu']['percent'];
                }
            }

            if (true == isset($node['ram.max']) && true == isset($node['heap.max'])) {
                $heapSize = true;
                $percent = ($node['heap.max'] * 100) / $node['ram.max'];
                if (50 < $percent) {
                    $nodesLimit['heap_size_over_50'][$node['name']] = $percent;
                }
            }

            if (true == isset($node['stats']['process']['max_file_descriptors'])) {
                $fileDescriptors = true;
                if (65535 > $node['stats']['process']['max_file_descriptors']) {
                    $nodesLimit['file_descriptors_under_65535'][$node['name']] = $node['stats']['process']['max_file_descriptors'];
                }
            }
        }

        $versions = array_unique($versions);
        sort($versions);

        $plugins = array_unique($plugins);
        sort($plugins);

        $query = [
            'h' => 'index,rep,status',
        ];

        if (true == $this->callManager->hasFeature('cat_expand_wildcards')) {
            $query['expand_wildcards'] = 'all';
        }

        $indices = $this->elasticsearchIndexManager->getAll($query);

        $indicesCount = [
            'with_replica' => 0,
            'open' => 0,
        ];
        foreach ($indices as $index) {
            if (0 < $index->getReplicas()) {
                $indicesCount['with_replica']++;
            }
            if ('open' == $index->getStatus()) {
                $indicesCount['open']++;
            }
        }

        $results = ['audit_fail' => [], 'audit_notice' => [], 'audit_pass' => []];

        $checkpointsKeys = [
            'end_of_life',
            'security_features',
            'cluster_name',
            'same_es_version',
            'same_plugins',
            'unassigned_shards',
            'adaptive_replica_selection',
            'indices_with_replica',
            'indices_opened',
            'close_index_not_enabled',
            'allocation_disk_threshold',
            'cpu_below_90',
            'heap_size_below_50',
            'anonymous_access_disabled',
            'license_not_expired',
            'file_descriptors',
        ];

        $checkpoints = [];
        foreach ($checkpointsKeys as $checkpointKey) {
            $checkpoints[$checkpointKey] = $this->translator->trans('audit_checkpoints.'.$checkpointKey);
        }
        asort($checkpoints);

        foreach ($checkpoints as $checkpoint => $title) {
            switch ($checkpoint) {
                case 'end_of_life':
                    $maintenanceTable = $this->elasticsearchClusterManager->getMaintenanceTable();

                    $endOfLife = false;
                    foreach ($maintenanceTable as $row) {
                        if ($row['es_version'] <= $parameters['root']['version']['number']) {
                            $endOfLife = $row;
                        }
                    }

                    if ($endOfLife) {
                        if ($endOfLife['eol_date'] < date('Y-m-d')) {
                            $results['audit_fail'][$checkpoint] = $endOfLife;
                        } else {
                            $results['audit_pass'][$checkpoint] = $endOfLife;
                        }
                    }
                    break;
                case 'security_features':
                    if (false == $this->callManager->hasFeature('security')) {
                        $results['audit_fail'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'cluster_name':
                    if ('elasticsearch' == $parameters['cluster_health']['cluster_name']) {
                        $results['audit_notice'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'same_es_version':
                    if (1 < count($versions)) {
                        $results['audit_fail'][$checkpoint] = $versions;
                    } else {
                        $results['audit_pass'][$checkpoint] = $versions;
                    }
                    break;
                case 'same_plugins':
                    $fail = [];
                    foreach ($plugins as $plugin) {
                        foreach ($nodesPlugins as $node => $plugins) {
                            if (false == in_array($plugin, $plugins)) {
                                $fail[$node][] = $plugin;
                            }
                        }
                    }

                    if (0 < count($fail)) {
                        $results['audit_fail'][$checkpoint] = $fail;
                    } else {
                        $results['audit_pass'][$checkpoint] = $plugins;
                    }
                    break;
                case 'unassigned_shards':
                    if (0 != $parameters['cluster_health']['unassigned_shards']) {
                        $results['audit_fail'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'adaptive_replica_selection':
                    if (true == $this->callManager->hasFeature('adaptive_replica_selection') && true == isset($parameters['cluster_settings']['cluster.routing.use_adaptive_replica_selection']) && 'true' == $parameters['cluster_settings']['cluster.routing.use_adaptive_replica_selection']) {
                        $results['audit_pass'][$checkpoint] = [];
                    } else {
                        $results['audit_fail'][$checkpoint] = [];
                    }
                    break;
                case 'indices_with_replica':
                    if (1 == count($nodes)) {
                        $results['audit_notice'][$checkpoint] = [];
                    } else {
                        if ($indicesCount['with_replica'] < count($indices)) {
                            $results['audit_fail'][$checkpoint] = [];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'indices_opened':
                    if ($indicesCount['open'] < count($indices)) {
                        $results['audit_fail'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'close_index_not_enabled':
                    if (true == isset($parameters['cluster_settings']['cluster.indices.close.enable']) && 'true' == $parameters['cluster_settings']['cluster.indices.close.enable']) {
                        $results['audit_fail'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'allocation_disk_threshold':
                    if (true == isset($parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) && 'true' == $parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) {
                        $results['audit_pass'][$checkpoint] = [];
                    } else {
                        $results['audit_fail'][$checkpoint] = [];
                    }
                    break;
                case 'cpu_below_90':
                    if (true == $cpuPercent) {
                        if (0 < count($nodesLimit['cpu_over_90'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['cpu_over_90'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'heap_size_below_50':
                    if (true == $heapSize) {
                        if (0 < count($nodesLimit['heap_size_over_50'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['heap_size_over_50'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'anonymous_access_disabled':
                    if (false == isset($parameters['cluster_settings']['xpack.security.authc.anonymous.roles']) || false == is_array($parameters['cluster_settings']['xpack.security.authc.anonymous.roles']) || 0 == count($parameters['cluster_settings']['xpack.security.authc.anonymous.roles'])) {
                        $results['audit_pass'][$checkpoint] = [];
                    } else {
                        $results['audit_fail'][$checkpoint] = [];
                    }
                    break;
                case 'license_not_expired':
                    if (true == $this->callManager->hasFeature('license')) {
                        if (false == $this->callManager->hasFeature('_xpack_endpoint_removed')) {
                            $this->endpoint = '_xpack/license';
                        } else {
                            $this->endpoint = '_license';
                        }

                        $callRequest = new CallRequestModel();
                        $callRequest->setPath('/'.$this->endpoint);
                        $callResponse = $this->callManager->call($callRequest);
                        $license = $callResponse->getContent();
                        $license = $license['license'];

                        if (true == isset($license['expiry_date_in_millis'])) {
                            $now = (new \Datetime());
                            $expire = new \Datetime(date('Y-m-d H:i:s', substr($license['expiry_date_in_millis'], 0, -3)));
                            $interval = $now->diff($expire);

                            if (30 > $interval->format('%a')) {
                                $results['audit_fail'][$checkpoint] = $license;
                            } else {
                                $results['audit_pass'][$checkpoint] = $license;
                            }
                        } else {
                            if ('basic' == $license['type']) {
                                $results['audit_notice'][$checkpoint] = $license;
                            }
                        }
                    }
                    break;
                case 'file_descriptors':
                    if (true == $fileDescriptors) {
                        if (0 < count($nodesLimit['file_descriptors_under_65535'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['file_descriptors_under_65535'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
            }
        }

        $parameters['results'] = $results;

        return $this->renderAbstract($request, 'Modules/cluster/cluster_audit.html.twig', $parameters);
    }
}
