<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ShardController extends AbstractAppController
{
    /**
     * @Route("/shards", name="shards")
     */
    public function index(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/shards');
        $callRequest->setQuery(['s' => 'index,shard,prirep', 'h' => 'index,shard,prirep,state,unassigned.reason,docs,store,node']);
        $shards = $this->callManager->call($callRequest);

        return $this->renderAbstract($request, 'Modules/shard/shard_index.html.twig', [
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
