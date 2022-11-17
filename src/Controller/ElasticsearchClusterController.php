<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchClusterSettingType;
use App\Form\Type\ElasticsearchClusterDiskThresholdsType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchNodeManager;
use App\Manager\ElasticsearchSlmPolicyManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\ElasticsearchClusterSettingModel;
use App\Model\ElasticsearchClusterDiskThresholdsModel;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchClusterController extends AbstractAppController
{
    private ElasticsearchIndexManager $elasticsearchIndexManager;

    private ElasticsearchNodeManager $elasticsearchNodeManager;

    private ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager;

    private ElasticsearchRepositoryManager $elasticsearchRepositoryManager;

    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager, ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
        $this->elasticsearchSlmPolicyManager = $elasticsearchSlmPolicyManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
    }

    #[Route('/cluster', name: 'cluster')]
    public function read(Request $request): Response
    {
        $clusterStats = $this->elasticsearchClusterManager->getClusterStats();

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read.html.twig', [
            'indices' => $clusterStats['indices']['count'] ?? false,
            'documents' => $clusterStats['indices']['docs']['count'] ?? false,
            'total_size' => $clusterStats['indices']['store']['size_in_bytes'] ?? false,
        ]);
    }

    #[Route('/cluster/allocation/explain', name: 'cluster_allocation_explain')]
    public function allocationExplain(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_ALLOCATION_EXPLAIN', 'global');

        if (false === $this->callManager->hasFeature('allocation_explain')) {
            throw new AccessDeniedException();
        }

        $allocationExplain = false;

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_cluster/allocation/explain');
            $json = [];
            if ($request->query->get('index')) {
                $json['index'] = $request->query->get('index');
            }
            if ('true' == $request->query->get('primary')) {
                $json['primary'] = true;
            }
            if ('false' == $request->query->get('primary')) {
                $json['primary'] = false;
            }
            if ($request->query->get('shard') || '0' == $request->query->get('shard')) {
                $json['shard'] = $request->query->get('shard');
            }
            if ($request->query->get('current_node')) {
                $json['current_node'] = $request->query->get('current_node');
            }
            if (0 < count($json)) {
                $callRequest->setJson($json);
            }
            if ('include' == $request->query->get('yes_decisions')) {
                $callRequest->setQuery(['include_yes_decisions' => 'true']);
            }
            $callResponse = $this->callManager->call($callRequest);
            $allocationExplain = $callResponse->getContent();

            if (true === isset($allocationExplain['shard']) && true === is_array($allocationExplain['shard'])) {
                $allocationExplain['index'] = $allocationExplain['shard']['index'];
                $allocationExplain['primary'] = $allocationExplain['shard']['primary'];
                $allocationExplain['shard'] = $allocationExplain['shard']['id'];
            }
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->renderAbstract($request, 'Modules/cluster/cluster_allocation_explain.html.twig', [
            'allocation_explain' => $allocationExplain,
        ]);
    }

    #[Route('/cluster/retry/failed', name: 'cluster_retry_failed')]
    public function retryFailed(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_ALLOCATION_EXPLAIN', 'global');

        if (false === $this->callManager->hasFeature('allocation_explain')) {
            throw new AccessDeniedException();
        }

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_cluster/reroute');
            $callRequest->setQuery(['retry_failed' => 'true']);
            $callResponse = $this->callManager->call($callRequest);

            $content = $callResponse->getContent();
            unset($content['state']);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('cluster_allocation_explain', $request->query->all());
    }

    #[Route('/cluster/settings', name: 'cluster_settings')]
    public function settings(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTINGS', 'global');

        if (false === $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedException();
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

    #[Route('/cluster/settings/{type}/{setting}/edit', name: 'cluster_settings_edit')]
    public function edit(Request $request, string $type, string $setting): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTING_EDIT', 'global');

        if (false === $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true === array_key_exists($setting, $clusterSettings)) {
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

    #[Route('/cluster/settings/{type}/{setting}/remove', name: 'cluster_settings_remove')]
    public function remove(Request $request, string $type, string $setting): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTING_REMOVE', 'global');

        if (false === $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedException();
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

    #[Route('/cluster/audit', name: 'cluster_audit')]
    public function audit(Request $request, string $elasticsearchUsername, string $elasticsearchPassword): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_AUDIT', 'global');

        $parameters = [];

        $parameters['cluster_settings'] = $this->elasticsearchClusterManager->getClusterSettings();

        $parameters['cluster_stats'] = $this->elasticsearchClusterManager->getClusterStats();

        $parameters['root'] = $this->callManager->getRoot();

        $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();

        $nodes = $this->elasticsearchNodeManager->getAll(['cluster_settings' => $parameters['cluster_settings']]);

        $versions = $this->elasticsearchNodeManager->getVersions($nodes);

        $plugins = [];
        $nodesPlugins = [];
        $formatMsgNoLookups = [];

        $diskPercent = false;
        $heapSize = false;
        $heapSizeJvm = false;
        $inputArgumentsJvm = false;
        $fileDescriptors = false;

        if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) && 'true' == $parameters['cluster_settings']['cluster.routing.allocation.disk.threshold_enabled']) {
            $diskThresholdEnabled = true;
        } else {
            $diskThresholdEnabled = false;
        }

        $nodesLimit = [
            'heap_size_over_50' => [],
            'file_descriptors_under_65535' => [],
            'over_disk_thresholds' => [],
            'heap_size_init_not_equal_max' => [],
        ];

        $dataNodes = 0;

        foreach ($nodes as $node) {
            if (true === isset($node['node.role'])) {
                $roles = str_split($node['node.role']);
                if (true === in_array('d', $roles)) {
                    $dataNodes++;
                }
            }

            $nodesPlugins[$node['name']] = [];

            foreach ($node['plugins'] as $plugin) {
                $nodesPlugins[$node['name']][] = $plugin['name'];
                $plugins[] = $plugin['name'];
            }

            if (true === isset($node['disk.used_percent']) && $diskThresholdEnabled) {
                $diskPercent = true;

                if (true === isset($node['disk_threshold'])) {
                    $nodesLimit['over_disk_thresholds'][$node['name']] = $node['disk_threshold'];
                }
            }

            if (true === isset($node['ram.max']) && true === isset($node['heap.max'])) {
                $heapSize = true;
                $percent = ($node['heap.max'] * 100) / $node['ram.max'];
                if (50 < $percent) {
                    $nodesLimit['heap_size_over_50'][$node['name']] = $percent;
                }
            }

            if (true === isset($node['jvm']['mem']['heap_init_in_bytes']) && true === isset($node['jvm']['mem']['heap_max_in_bytes'])) {
                $heapSizeJvm = true;
                if ($node['jvm']['mem']['heap_init_in_bytes'] != $node['jvm']['mem']['heap_max_in_bytes']) {
                    $nodesLimit['heap_size_init_not_equal_max'][$node['name']] = [
                        'init' => $node['jvm']['mem']['heap_init_in_bytes'],
                        'max' => $node['jvm']['mem']['heap_max_in_bytes'],
                    ];
                }
            }

            if (true === isset($node['jvm']['input_arguments'])) {
                $inputArgumentsJvm = true;
                if (true === in_array('-Dlog4j2.formatMsgNoLookups=true', $node['jvm']['input_arguments'])) {
                    $formatMsgNoLookups[$node['name']] = true;
                } else {
                    $formatMsgNoLookups[$node['name']] = false;
                }
            }

            if (true === isset($node['file_desc.max'])) {
                $fileDescriptors = true;
                if (-1 < $node['file_desc.max'] && 65535 > $node['file_desc.max']) {
                    $nodesLimit['file_descriptors_under_65535'][$node['name']] = $node['file_desc.max'];
                }
            }
        }

        $plugins = array_unique($plugins);
        sort($plugins);

        $query = [
            'h' => 'index,rep,status',
        ];

        $indices = $this->elasticsearchIndexManager->getAll($query);

        $indicesCount = [
            'with_replica' => 0,
            'enough_data_nodes' => 0,
            'open' => 0,
        ];
        foreach ($indices as $index) {
            if (0 < $index->getReplicas()) {
                $indicesCount['with_replica']++;
            }
            if ($dataNodes > $index->getReplicas()) {
                $indicesCount['enough_data_nodes']++;
            }
            if ('open' == $index->getStatus()) {
                $indicesCount['open']++;
            }
        }

        $results = ['audit_fail' => [], 'audit_notice' => [], 'audit_pass' => []];

        $checkpointsKeys = [
            'security_features',
            'cluster_name',
            'same_es_version',
            'same_plugins',
            'unassigned_shards',
            'adaptive_replica_selection',
            'indices_with_replica',
            'indices_replicas_data_nodes',
            'indices_opened',
            'close_index_not_enabled',
            'allocation_disk_threshold',
            'heap_size_below_50',
            'anonymous_access_disabled',
            'license_not_expired',
            'file_descriptors',
            'password_not_changeme',
            'below_disk_thresholds',
            'cluster_not_readonly',
            'heap_size_init_equal_max',
            'slm_policies_schedule_unique',
            'repositories_connected',
            'shard_allocation_enabled',
            'max_shards_per_node',
            'total_shards_per_node',
            'replication_100_percent',
            'deprecations',
            'format_msg_no_lookups',
        ];

        $checkpoints = [];
        foreach ($checkpointsKeys as $checkpointKey) {
            $checkpoints[$checkpointKey] = $this->translator->trans('audit_checkpoints.'.$checkpointKey);
        }
        asort($checkpoints);

        foreach ($checkpoints as $checkpoint => $title) {
            switch ($checkpoint) {
                case 'security_features':
                    if (false === $this->callManager->hasFeature('security')) {
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
                            if (false === in_array($plugin, $plugins)) {
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
                        $results['audit_fail'][$checkpoint] = $parameters['cluster_health']['unassigned_shards'];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'adaptive_replica_selection':
                    if (true === $this->callManager->hasFeature('adaptive_replica_selection')) {
                        if (true === $this->callManager->hasFeature('adaptive_replica_selection') && true === isset($parameters['cluster_settings']['cluster.routing.use_adaptive_replica_selection']) && 'true' == $parameters['cluster_settings']['cluster.routing.use_adaptive_replica_selection']) {
                            $results['audit_pass'][$checkpoint] = [];
                        } else {
                            $results['audit_fail'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'indices_with_replica':
                    if (1 == count($nodes)) {
                        $results['audit_notice'][$checkpoint] = [];
                    } else {
                        if (true === isset($parameters['cluster_stats']['indices']['shards']['replication'])) {
                            $replication = round($parameters['cluster_stats']['indices']['shards']['replication']*100, 2).'%';
                        } else {
                            $replication = null;
                        }
                        if ($indicesCount['with_replica'] < count($indices)) {
                            $results['audit_fail'][$checkpoint] = $replication;
                        } else {
                            $results['audit_pass'][$checkpoint] = $replication;
                        }
                    }
                    break;
                case 'replication_100_percent':
                    if (1 == count($nodes)) {
                        $results['audit_notice'][$checkpoint] = [];
                    } else {
                        if (true === isset($parameters['cluster_stats']['indices']['shards']['replication'])) {
                            $replication = $parameters['cluster_stats']['indices']['shards']['replication'];
                            if (1 > $replication) {
                                $results['audit_fail'][$checkpoint] = round($replication * 100, 2).'%';
                            } else {
                                $results['audit_pass'][$checkpoint] = round($replication * 100, 2).'%';
                            }
                        }
                    }
                    break;
                case 'indices_replicas_data_nodes':
                    if ($indicesCount['enough_data_nodes'] < count($indices)) {
                        $results['audit_fail'][$checkpoint] = $dataNodes;
                    } else {
                        $results['audit_pass'][$checkpoint] = $dataNodes;
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
                    if (true === isset($parameters['cluster_settings']['cluster.indices.close.enable']) && 'true' == $parameters['cluster_settings']['cluster.indices.close.enable']) {
                        $results['audit_fail'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'cluster_not_readonly':
                    if (true === isset($parameters['cluster_settings']['cluster.blocks.read_only']) && 'true' == $parameters['cluster_settings']['cluster.blocks.read_only']) {
                        $results['audit_fail'][$checkpoint] = [];
                    } else {
                        $results['audit_pass'][$checkpoint] = [];
                    }
                    break;
                case 'allocation_disk_threshold':
                    if (true === $diskThresholdEnabled) {
                        $results['audit_pass'][$checkpoint] = [];
                    } else {
                        $results['audit_fail'][$checkpoint] = [];
                    }
                    break;
                case 'below_disk_thresholds':
                    if (true === $diskPercent && true === $diskThresholdEnabled) {
                        if (0 < count($nodesLimit['over_disk_thresholds'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['over_disk_thresholds'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'heap_size_below_50':
                    if (true === $heapSize) {
                        if (0 < count($nodesLimit['heap_size_over_50'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['heap_size_over_50'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'heap_size_init_equal_max':
                    if (true === $heapSizeJvm) {
                        if (0 < count($nodesLimit['heap_size_init_not_equal_max'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['heap_size_init_not_equal_max'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'anonymous_access_disabled':
                    if (false === isset($parameters['cluster_settings']['xpack.security.authc.anonymous.roles']) || false === is_array($parameters['cluster_settings']['xpack.security.authc.anonymous.roles']) || 0 == count($parameters['cluster_settings']['xpack.security.authc.anonymous.roles'])) {
                        $results['audit_pass'][$checkpoint] = [];
                    } else {
                        $results['audit_fail'][$checkpoint] = [];
                    }
                    break;
                case 'license_not_expired':
                    if (true === $this->callManager->hasFeature('license')) {
                        $license = $this->callManager->getLicense();

                        if ('basic' != $license['type'] && true === isset($license['expiry_date_in_millis'])) {
                            $now = (new \Datetime());
                            $expire = new \Datetime(date('Y-m-d H:i:s', intval($license['expiry_date_in_millis'] / 1000)));
                            $interval = $now->diff($expire);

                            if (30 > $interval->format('%r%a')) {
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
                    if (true === $fileDescriptors) {
                        if (0 < count($nodesLimit['file_descriptors_under_65535'])) {
                            $results['audit_fail'][$checkpoint] = $nodesLimit['file_descriptors_under_65535'];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'password_not_changeme':
                    if ('elastic' == $elasticsearchUsername) {
                        if ('changeme' == $elasticsearchPassword) {
                            $results['audit_fail'][$checkpoint] = [];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'slm_policies_schedule_unique':
                    if (true === $this->callManager->hasFeature('slm')) {
                        $schedules = [];
                        $policies = $this->elasticsearchSlmPolicyManager->getAll();
                        foreach ($policies as $policy) {
                            $schedules[] = $policy->getSchedule();
                        }
                        $schedules = array_unique($schedules);

                        if (count($schedules) != count($policies)) {
                            $results['audit_fail'][$checkpoint] = [];
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                    break;
                case 'repositories_connected':
                    $errors = [];
                    $repositories = $this->elasticsearchRepositoryManager->getAll();
                    if (0 < count($repositories)) {
                        foreach ($repositories as $repository) {
                            try {
                                $callResponse = $this->elasticsearchRepositoryManager->verifyByName($repository->getName());
                                if (Response::HTTP_OK != $callResponse->getCode()) {
                                    $errors[] = $repository->getName();
                                }
                            } catch (Callexception $e) {
                                $errors[] = $repository->getName();
                            }
                        }

                        if (0 < count($errors)) {
                            $results['audit_fail'][$checkpoint] = $errors;
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    } else {
                        $results['audit_notice'][$checkpoint] = [];
                    }
                    break;
                case 'shard_allocation_enabled':
                    if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.enable'])) {
                        if ('all' == strtolower($parameters['cluster_settings']['cluster.routing.allocation.enable'])) {
                            $results['audit_pass'][$checkpoint] = $parameters['cluster_settings']['cluster.routing.allocation.enable'];
                        } else {
                            $results['audit_fail'][$checkpoint] = $parameters['cluster_settings']['cluster.routing.allocation.enable'];
                        }
                    }
                    break;
                case 'max_shards_per_node':
                    if (true === isset($parameters['cluster_settings']['cluster.max_shards_per_node'])) {
                        if (1000 < $parameters['cluster_settings']['cluster.max_shards_per_node']) {
                            $results['audit_fail'][$checkpoint] = $parameters['cluster_settings']['cluster.max_shards_per_node'];
                        } else {
                            $results['audit_pass'][$checkpoint] = $parameters['cluster_settings']['cluster.max_shards_per_node'];
                        }
                    }
                    break;
                case 'total_shards_per_node':
                    if (true === isset($parameters['cluster_settings']['cluster.routing.allocation.total_shards_per_node']) && -1 < $parameters['cluster_settings']['cluster.routing.allocation.total_shards_per_node']) {
                        if (1000 < $parameters['cluster_settings']['cluster.routing.allocation.total_shards_per_node']) {
                            $results['audit_fail'][$checkpoint] = $parameters['cluster_settings']['cluster.routing.allocation.total_shards_per_node'];
                        } else {
                            $results['audit_pass'][$checkpoint] = $parameters['cluster_settings']['cluster.routing.allocation.total_shards_per_node'];
                        }
                    }
                    break;
                case 'deprecations':
                    if (true === $this->callManager->hasFeature('deprecations')) {
                        $callRequest = new CallRequestModel();
                        if (false === $this->callManager->hasFeature('_xpack_endpoint_removed')) {
                            $callRequest->setPath('/_xpack/migration/deprecations');
                        } else {
                            $callRequest->setPath('/_migration/deprecations');
                        }
                        $callResponse = $this->callManager->call($callRequest);
                        $deprecations = $callResponse->getContent();
                        $messages = [];
                        foreach ($deprecations as $key => $rows) {
                            if (0 < count($rows)) {
                                $messages[$key] = count($rows);
                            }
                        }
                        if (0 < count($messages)) {
                            $results['audit_notice'][$checkpoint] = $messages;
                        } else {
                            $results['audit_pass'][$checkpoint] = [];
                        }
                    }
                break;
            case 'format_msg_no_lookups':
                if (true === $inputArgumentsJvm) {
                    $fail = [];
                    foreach ($formatMsgNoLookups as $node => $parameter) {
                        if (false === $parameter) {
                            $fail[] = $node;
                        }
                    }

                    if (0 < count($fail)) {
                        $results['audit_fail'][$checkpoint] = $fail;
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

    #[Route('/cluster/disk-thresholds', name: 'cluster_disk_thresholds')]
    public function diskThresholds(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_DISK_THRESHOLDS', 'global');

        if (false === $this->callManager->hasFeature('cluster_settings')) {
            throw new AccessDeniedException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        $clusterDiskThresholdsModel = new ElasticsearchClusterDiskThresholdsModel();
        $clusterDiskThresholdsModel->setEnabled('true' == $clusterSettings['cluster.routing.allocation.disk.threshold_enabled'] ? true : false);
        $clusterDiskThresholdsModel->setLow($clusterSettings['cluster.routing.allocation.disk.watermark.low']);
        $clusterDiskThresholdsModel->setHigh($clusterSettings['cluster.routing.allocation.disk.watermark.high']);
        if (true === isset($clusterSettings['cluster.routing.allocation.disk.watermark.flood_stage'])) {
            $clusterDiskThresholdsModel->setFloodStage($clusterSettings['cluster.routing.allocation.disk.watermark.flood_stage']);
        }
        $form = $this->createForm(ElasticsearchClusterDiskThresholdsType::class, $clusterDiskThresholdsModel, ['cluster_settings' => $clusterSettings]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $clusterDiskThresholdsModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_cluster/settings');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('cluster_disk_thresholds');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/cluster/cluster_disk_thresholds.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
