<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\AliasType;
use App\Form\IndiceType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ShardsController extends AbstractAppController
{
    /**
     * @Route("shards", name="shards")
     */
    public function index(Request $request): Response
    {
        $query = [
        ];
        $shards = $this->queryManager->query('GET', '/_cat/shards', ['query' => $query]);

        return $this->renderAbstract($request, 'shards_index.html.twig', [
            'shards' => $this->paginatorManager->paginate([
                'route' => 'shards',
                'route_parameters' => [],
                'total' => count($shards),
                'rows' => $shards,
                'page' => 1,
                'size' => count($shards),
            ]),
        ]);
    }
}
