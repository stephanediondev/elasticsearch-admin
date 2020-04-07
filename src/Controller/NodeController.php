<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\ElasticsearchClusterManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class NodeController extends AbstractAppController
{
    public function __construct(ElasticsearchClusterManager $elasticsearchClusterManager)
    {
        $this->elasticsearchClusterManager = $elasticsearchClusterManager;
    }

    /**
     * @Route("/nodes", name="nodes")
     */
    public function index(Request $request): Response
    {
        $nodes = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/nodes');
        $callRequest->setQuery(['s' => 'name', 'h' => 'name,disk.used_percent,ram.percent,cpu,uptime,master,disk.total,disk.used,ram.current,ram.max,heap.percent,heap.max,heap.current']);
        $nodes1 = $this->callManager->call($callRequest);

        foreach ($nodes1 as $node) {
            $nodes[$node['name']] = $node;
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes');
        $nodes2 = $this->callManager->call($callRequest);

        foreach ($nodes2['nodes'] as $node) {
            $nodes[$node['name']] = array_merge($node, $nodes[$node['name']]);
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/stats');
        $nodes3 = $this->callManager->call($callRequest);

        foreach ($nodes3['nodes'] as $node) {
            $nodes[$node['name']]['stats'] = $node;
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        return $this->renderAbstract($request, 'Modules/node/node_index.html.twig', [
            'cluster_settings' => $clusterSettings,
            'nodes' => $this->paginatorManager->paginate([
                'route' => 'nodes',
                'route_parameters' => [],
                'total' => count($nodes),
                'rows' => $nodes,
                'page' => 1,
                'size' => count($nodes),
            ]),
        ]);
    }

    /**
     * @Route("/nodes/fetch", name="nodes/fetch")
     */
    public function fetch(Request $request): JsonResponse
    {
        $json = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/nodes');
        $callRequest->setQuery(['s' => 'name', 'h' => 'name,disk.used_percent,ram.percent,cpu,uptime,master,disk.total,disk.used,ram.current,ram.max,heap.percent,heap.max,heap.current']);
        $nodes1 = $this->callManager->call($callRequest);

        foreach ($nodes1 as $node) {
            $json[$node['name']] = $node;
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes');
        $nodes2 = $this->callManager->call($callRequest);

        foreach ($nodes2['nodes'] as $node) {
            $json[$node['name']] = array_merge($node, $json[$node['name']]);
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/stats');
        $nodes3 = $this->callManager->call($callRequest);

        foreach ($nodes3['nodes'] as $node) {
            $json[$node['name']]['stats'] = $node;
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/nodes/{node}", name="nodes_read")
     */
    public function read(Request $request, string $node): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$node);
        $node = $this->callManager->call($callRequest);

        if (true == isset($node['nodes'][key($node['nodes'])])) {
            $node = $node['nodes'][key($node['nodes'])];

            return $this->renderAbstract($request, 'Modules/node/node_read.html.twig', [
                'node' => $node,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/nodes/{node}/plugins", name="nodes_read_plugins")
     */
    public function plugins(Request $request, string $node): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$node);
        $node = $this->callManager->call($callRequest);

        if (true == isset($node['nodes'][key($node['nodes'])])) {
            $node = $node['nodes'][key($node['nodes'])];

            return $this->renderAbstract($request, 'Modules/node/node_read_plugins.html.twig', [
                'node' => $node,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/nodes/{node}/usage", name="nodes_read_usage")
     */
    public function usage(Request $request, string $node): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$node);
        $node = $this->callManager->call($callRequest);

        if (true == isset($node['nodes'][key($node['nodes'])])) {
            $node = $node['nodes'][key($node['nodes'])];

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_nodes/'.$node['name'].'/usage');
            $usage = $this->callManager->call($callRequest);
            $usage = $usage['nodes'][key($usage['nodes'])];

            return $this->renderAbstract($request, 'Modules/node/node_read_usage.html.twig', [
                'node' => $node,
                'usage' => $usage,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
