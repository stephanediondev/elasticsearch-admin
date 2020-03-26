<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $call->setQuery(['s' => 'name', 'h' => 'name,disk.used_percent,uptime,master']);
        $nodes1 = $this->callManager->call($call);

        foreach ($nodes1 as $node) {
            $nodes[$node['name']] = $node;
        }

        $call = new CallModel();
        $call->setPath('/_nodes');
        $nodes2 = $this->callManager->call($call);

        foreach ($nodes2['nodes'] as $node) {
            $nodes[$node['name']] = array_merge($nodes[$node['name']], $node);
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
}
