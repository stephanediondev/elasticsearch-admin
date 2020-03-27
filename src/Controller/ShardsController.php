<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ShardsController extends AbstractAppController
{
    /**
     * @Route("/shards", name="shards")
     */
    public function index(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_cat/shards');
        $call->setQuery(['h' => 'index,shard,prirep,state,unassigned.reason,docs,store,node']);
        $shards = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/shards/shards_index.html.twig', [
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
