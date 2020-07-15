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
class ElasticsearchShardController extends AbstractAppController
{
    /**
     * @Route("/shards", name="shards")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHARDS', 'global');

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/shards');
        $callRequest->setQuery(['bytes' => 'b', 's' => $request->query->get('s', 'index:asc,shard:asc,prirep:asc'), 'h' => 'index,shard,prirep,state,unassigned.reason,docs,store,node']);
        $callResponse = $this->callManager->call($callRequest);
        $shards = $callResponse->getContent();

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
