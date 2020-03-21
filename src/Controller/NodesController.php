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
        $query = [
        ];
        $nodes = $this->queryManager->query('GET', '/_nodes', ['query' => $query]);

        return $this->renderAbstract($request, 'nodes_index.html.twig', [
            'nodes' => $this->paginator->paginate($nodes['nodes'], $request->query->get('page', 1), 50),
        ]);
    }

    /**
     * @Route("/nodes/{node}", name="nodes_read")
     */
    public function read(Request $request, string $node): Response
    {
        $query = [
        ];
        $content = $this->queryManager->query('GET', '/_nodes/'.$node, ['query' => $query]);

        return $this->renderAbstract($request, 'nodes_read.html.twig', [
            'content' => $content['nodes'][key($content['nodes'])],
        ]);
    }
}
