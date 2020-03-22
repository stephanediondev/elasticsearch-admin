<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NodesController extends AbstractAppController
{
    /**
     * @Route("/nodes", name="nodes")
     */
    public function index(Request $request): Response
    {
        $nodes = [];

        $query = [
            's' => 'name',
        ];
        $nodes1 = $this->queryManager->query('GET', '/_cat/nodes', ['query' => $query]);

        foreach ($nodes1 as $node) {
            $nodes[$node['name']] = $node;
        }

        $query = [
        ];
        $nodes2 = $this->queryManager->query('GET', '/_nodes', ['query' => $query]);

        foreach ($nodes2['nodes'] as $node) {
            $nodes[$node['name']] = array_merge($nodes[$node['name']], $node);
        }

        return $this->renderAbstract($request, 'nodes_index.html.twig', [
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
        $query = [
        ];
        $nodesNode = $this->queryManager->query('GET', '/_nodes/'.$node, ['query' => $query]);

        return $this->renderAbstract($request, 'nodes_read.html.twig', [
            'nodesNode' => $nodesNode['nodes'][key($nodesNode['nodes'])],
        ]);
    }
}
