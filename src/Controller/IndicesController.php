<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndicesController extends AbstractAppController
{
    /**
     * @Route("/indices", name="indices")
     */
    public function index(Request $request): Response
    {
        $query = [
        ];
        $indices = $this->queryManager->query('GET', '/_cat/indices', ['query' => $query]);

        return $this->renderAbstract($request, 'indices_index.html.twig', [
            'indices' => $this->paginator->paginate($indices, $request->query->get('page', 1), 50),
        ]);
    }

    /**
     * @Route("/indices/{index}", name="indices_read")
     */
    public function read(Request $request, string $index): Response
    {
        $query = [
        ];
        $content = $this->queryManager->query('GET', '/'.$index, ['query' => $query]);

        $size = 50;
        $query = [
            'size' => $size,
            'from' => ($size * $request->query->get('page', 1)) - $size,
        ];
        $documents = $this->queryManager->query('GET', '/'.$index.'/_search', ['query' => $query]);

        return $this->renderAbstract($request, 'indices_read.html.twig', [
            'content' => $content,
            'documents' => $this->paginator->paginate($documents['hits']['hits'], $request->query->get('page', 1), $size),
        ]);
    }
}
