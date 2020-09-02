<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchNodeFilterType;
use App\Form\Type\ElasticsearchNodeReloadSecureSettingsType;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchReloadSecureSettingsModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchNodeController extends AbstractAppController
{
    public function __construct(ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/nodes", name="nodes")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('NODES', 'global');

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        $nodes = $this->elasticsearchNodeManager->getAll(['sort' => $request->query->get('sort', 'name:asc'), 'cluster_settings' => $clusterSettings]);

        $versions = $this->elasticsearchNodeManager->getVersions($nodes);

        $form = $this->createForm(ElasticsearchNodeFilterType::class, null, ['version' => $versions]);

        $form->handleRequest($request);

        $nodes = $this->elasticsearchNodeManager->filter($nodes, [
            'master' => $form->get('master')->getData(),
            'data' => $form->get('data')->getData(),
            'voting_only' => $form->has('voting_only') ? $form->get('voting_only')->getData() : false,
            'ingest' => $form->get('ingest')->getData(),
            'version' => $form->has('version') ? $form->get('version')->getData() : false,
        ]);

        if ('true' === $request->query->get('fetch')) {
            $template = 'Modules/node/node_list.html.twig';
        } else {
            $template = 'Modules/node/node_index.html.twig';
        }

        return $this->renderAbstract($request, $template, [
            'nodes' => $this->paginatorManager->paginate([
                'route' => 'nodes',
                'route_parameters' => [],
                'total' => count($nodes),
                'rows' => $nodes,
                'page' => 1,
                'size' => count($nodes),
            ]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/nodes/stats", name="nodes_stats")
     */
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('NODES_STATS', 'global');

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        $nodes = $this->elasticsearchNodeManager->getAll(['sort' => $request->query->get('sort', 'name:asc'), 'cluster_settings' => $clusterSettings]);

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['nodes_total'] = 0;
        $data['totals']['nodes_total_disk_avail'] = 0;
        if (true === $this->callManager->hasFeature('cat_nodes_disk')) {
            $data['totals']['nodes_total_disk_used'] = 0;
        }
        $data['tables']['nodes_by_disk_avail'] = [];
        $data['tables']['nodes_by_disk_used'] = [];
        $data['tables']['nodes_by_os'] = [];
        $data['tables']['nodes_by_os_arch'] = [];
        $data['tables']['nodes_by_es_version'] = [];
        $data['tables']['nodes_by_jdk_version'] = [];
        $data['tables']['nodes_by_role'] = [];

        foreach ($nodes as $node) {
            $data['totals']['nodes_total']++;
            $data['totals']['nodes_total_disk_avail'] += $node['disk.avail'];
            if (true === isset($node['disk.used'])) {
                $data['totals']['nodes_total_disk_used'] += $node['disk.used'];
            }

            foreach (array_keys($data['tables']) as $table) {
                switch ($table) {
                    case 'nodes_by_disk_avail':
                        if (true === isset($node['disk.avail'])) {
                            $data['tables'][$table][] = ['total' => $node['disk.avail'], 'title' => $node['name']];
                        }
                        break;
                    case 'nodes_by_disk_used':
                        if (true === isset($node['disk.used'])) {
                            $data['tables'][$table][] = ['total' => $node['disk.used'], 'title' => $node['name']];
                        }
                        break;
                    case 'nodes_by_role':
                        if (true === isset($node['node.role'])) {
                            foreach (str_split($node['node.role']) as $role) {
                                if (false === isset($data['tables'][$table][$role])) {
                                    $data['tables'][$table][$role] = ['total' => 0, 'title' => $role];
                                }
                                $data['tables'][$table][$role]['total']++;
                            }
                        }
                        break;
                    case 'nodes_by_es_version':
                        if (true === isset($node['version'])) {
                            if (false === isset($data['tables'][$table][$node['version']])) {
                                $data['tables'][$table][$node['version']] = ['total' => 0, 'title' => $node['version']];
                            }
                            $data['tables'][$table][$node['version']]['total']++;
                        }
                        break;
                    case 'nodes_by_jdk_version':
                        if (true === isset($node['jdk'])) {
                            if (false === isset($data['tables'][$table][$node['jdk']])) {
                                $data['tables'][$table][$node['jdk']] = ['total' => 0, 'title' => $node['jdk']];
                            }
                            $data['tables'][$table][$node['jdk']]['total']++;
                        }
                        break;
                    case 'nodes_by_os':
                    case 'nodes_by_os_arch':
                        if (true === isset($node['os'])) {
                            $key = false;
                            switch ($table) {
                                case 'nodes_by_os':
                                    if (true === isset($node['os']['pretty_name'])) {
                                        $key = $node['os']['pretty_name'];
                                    }
                                    break;
                                case 'nodes_by_os_arch':
                                    if (true === isset($node['os']['arch'])) {
                                        $key = $node['os']['arch'];
                                    }
                                    break;
                            }
                            if ($key) {
                                if (false === isset($data['tables'][$table][$key])) {
                                    $data['tables'][$table][$key] = ['total' => 0, 'title' => $key];
                                }
                                $data['tables'][$table][$key]['total']++;
                            }
                        }
                        break;
                }
            }
        }

        foreach (array_keys($data['tables']) as $table) {
            if (true === isset($data['tables'][$table])) {
                usort($data['tables'][$table], [$this, 'sortByTotal']);
            }
        }

        return $this->renderAbstract($request, 'Modules/node/node_stats.html.twig', [
            'data' => $data,
            'letters' => $this->elasticsearchNodeManager->filterletters(),
        ]);
    }

    private function sortByTotal($a, $b)
    {
        return $b['total'] - $a['total'];
    }

    /**
     * @Route("/nodes/reload-secure-settings", name="nodes_reload_secure_settings")
     */
    public function readReloadSecureSettings(Request $request): Response
    {
        if (false === $this->callManager->hasFeature('reload_secure_settings')) {
            throw new AccessDeniedException();
        }

        $this->denyAccessUnlessGranted('NODES_RELOAD_SECURE_SETTINGS', 'global');

        $reloadSecureSettingsModel = new ElasticsearchReloadSecureSettingsModel();
        $form = $this->createForm(ElasticsearchNodeReloadSecureSettingsType::class, $reloadSecureSettingsModel);

        $form->handleRequest($request);

        $parameters = [
            'form' => $form->createView(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $reloadSecureSettingsModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_nodes/reload_secure_settings');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);
                $parameters['response'] = $callResponse->getContent();
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/node/node_reload_secure_settings.html.twig', $parameters);
    }

    /**
     * @Route("/nodes/{node}", name="nodes_read")
     */
    public function read(Request $request, string $node): Response
    {
        $this->denyAccessUnlessGranted('NODES', 'global');

        $node = $this->elasticsearchNodeManager->getByName($node);

        if (null === $node) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/node/node_read.html.twig', [
            'node' => $node,
        ]);
    }

    /**
     * @Route("/nodes/{node}/plugins", name="nodes_read_plugins")
     */
    public function readPlugins(Request $request, string $node): Response
    {
        $node = $this->elasticsearchNodeManager->getByName($node);

        if (null === $node) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('NODE_PLUGINS', $node);

        return $this->renderAbstract($request, 'Modules/node/node_read_plugins.html.twig', [
            'node' => $node,
        ]);
    }

    /**
     * @Route("/nodes/{node}/usage", name="nodes_read_usage")
     */
    public function readUsage(Request $request, string $node): Response
    {
        if (false === $this->callManager->hasFeature('node_usage')) {
            throw new AccessDeniedException();
        }

        $node = $this->elasticsearchNodeManager->getByName($node);

        if (null === $node) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('NODE_USAGE', $node);

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$node->getName().'/usage');
        $callResponse = $this->callManager->call($callRequest);
        $usage = $callResponse->getContent();
        $usage = $usage['nodes'][key($usage['nodes'])];

        if (true === isset($usage['rest_actions'])) {
            ksort($usage['rest_actions']);
        } else {
            $usage['rest_actions'] = [];
        }

        return $this->renderAbstract($request, 'Modules/node/node_read_usage.html.twig', [
            'node' => $node,
            'usage' => $usage,
        ]);
    }
}
