<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class NodesController extends AbstractAppController
{
    /**
     * @Route("/nodes", name="nodes")
     */
    public function index(Request $request): Response
    {
        $nodes = [];

        $call = new CallModel();
        $call->setPath('/_cat/nodes');
        $call->setQuery(['s' => 'name', 'h' => 'name,disk.used_percent,ram.percent,cpu,uptime,master,disk.total,disk.used,ram.current,ram.max,heap.percent,heap.max,heap.current']);
        $nodes1 = $this->callManager->call($call);

        foreach ($nodes1 as $node) {
            $nodes[$node['name']] = $node;
        }

        $call = new CallModel();
        $call->setPath('/_nodes');
        $nodes2 = $this->callManager->call($call);

        foreach ($nodes2['nodes'] as $node) {
            $nodes[$node['name']] = array_merge($node, $nodes[$node['name']]);
        }

        $call = new CallModel();
        $call->setPath('/_nodes/stats');
        $nodes3 = $this->callManager->call($call);

        foreach ($nodes3['nodes'] as $node) {
            $nodes[$node['name']]['stats'] = $node;
        }

        return $this->renderAbstract($request, 'Modules/nodes/nodes_index.html.twig', [
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

        $call = new CallModel();
        $call->setPath('/_cat/nodes');
        $call->setQuery(['s' => 'name', 'h' => 'name,disk.used_percent,ram.percent,cpu,uptime,master,disk.total,disk.used,ram.current,ram.max,heap.percent,heap.max,heap.current']);
        $nodes1 = $this->callManager->call($call);

        foreach ($nodes1 as $node) {
            $json[$node['name']] = $node;
        }

        $call = new CallModel();
        $call->setPath('/_nodes');
        $nodes2 = $this->callManager->call($call);

        foreach ($nodes2['nodes'] as $node) {
            $json[$node['name']] = array_merge($node, $json[$node['name']]);
        }

        $call = new CallModel();
        $call->setPath('/_nodes/stats');
        $nodes3 = $this->callManager->call($call);

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
        $call = new CallModel();
        $call->setPath('/_nodes/'.$node);
        $node = $this->callManager->call($call);

        if (true == isset($node['nodes'][key($node['nodes'])])) {
            $call = new CallModel();
            $call->setPath('/_cluster/state');
            $clusterState = $this->callManager->call($call);

            $nodes = [];
            foreach ($clusterState['nodes'] as $k => $v) {
                $nodes[$k] = $v['name'];
            }

            return $this->renderAbstract($request, 'Modules/nodes/nodes_read.html.twig', [
                'master_node' => $nodes[$clusterState['master_node']] ?? false,
                'node' => $node['nodes'][key($node['nodes'])],
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
        $call = new CallModel();
        $call->setPath('/_nodes/'.$node);
        $node = $this->callManager->call($call);

        if (true == isset($node['nodes'][key($node['nodes'])])) {
            $node = $node['nodes'][key($node['nodes'])];

            return $this->renderAbstract($request, 'Modules/nodes/nodes_read_plugins.html.twig', [
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
        $call = new CallModel();
        $call->setPath('/_nodes/'.$node);
        $node = $this->callManager->call($call);

        if (true == isset($node['nodes'][key($node['nodes'])])) {
            $node = $node['nodes'][key($node['nodes'])];

            $call = new CallModel();
            $call->setPath('/_nodes/'.$node['name'].'/usage');
            $usage = $this->callManager->call($call);
            $usage = $usage['nodes'][key($usage['nodes'])];

            return $this->renderAbstract($request, 'Modules/nodes/nodes_read_usage.html.twig', [
                'usage' => $usage,
                'node' => $node,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
