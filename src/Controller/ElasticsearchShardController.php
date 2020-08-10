<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchShardManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchShardController extends AbstractAppController
{
    public function __construct(ElasticsearchShardManager $elasticsearchShardManager, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchShardManager = $elasticsearchShardManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/shards", name="shards")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHARDS', 'global');

        $shards = $this->elasticsearchShardManager->getAll($request->query->get('s', 'index:asc,shard:asc,prirep:asc'));

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $nodesAvailable = $this->elasticsearchShardManager->getNodesAvailable($shards, $nodes);

        return $this->renderAbstract($request, 'Modules/shard/shard_index.html.twig', [
            'shards' => $this->paginatorManager->paginate([
                'route' => 'shards',
                'route_parameters' => [],
                'total' => count($shards),
                'rows' => $shards,
                'page' => 1,
                'size' => count($shards),
            ]),
            'nodesAvailable' => $nodesAvailable,
        ]);
    }

    /**
     * @Route("/shards/{index}/{number}/move", name="shards_move")
     */
    public function move(Request $request, string $index, string $number): Response
    {
        $this->denyAccessUnlessGranted('SHARDS', 'global');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        try {
            $json = [
                'commands' => [
                    [
                        'move' => [
                            'index' => $index->getName(),
                            'shard' => $number,
                            'from_node' => $request->query->get('from_node'),
                            'to_node' => $request->query->get('to_node'),
                        ],
                    ],
                ],
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_cluster/reroute');
            $callRequest->setJson($json);
            $callResponse = $this->callManager->call($callRequest);

            $content = $callResponse->getContent();
            unset($content['state']);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        sleep(2);

        if ('indices_read_shards' == $request->query->get('redirect')) {
            return $this->redirectToRoute('indices_read_shards', ['index' => $index->getName()]);
        } else {
            return $this->redirectToRoute('shards');
        }
    }

    /**
     * @Route("/shards/{index}/{number}/allocate-replica", name="shards_allocate_replica")
     */
    public function allocateReplica(Request $request, string $index, string $number): Response
    {
        $this->denyAccessUnlessGranted('SHARDS', 'global');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        try {
            $json = [
                'commands' => [
                    [
                        'allocate_replica' => [
                            'index' => $index->getName(),
                            'shard' => $number,
                            'node' => $request->query->get('node'),
                        ],
                    ],
                ],
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_cluster/reroute');
            $callRequest->setJson($json);
            $callResponse = $this->callManager->call($callRequest);

            $content = $callResponse->getContent();
            unset($content['state']);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        sleep(2);

        if ('indices_read_shards' == $request->query->get('redirect')) {
            return $this->redirectToRoute('indices_read_shards', ['index' => $index->getName()]);
        } else {
            return $this->redirectToRoute('shards');
        }
    }

    /**
     * @Route("/shards/{index}/{number}/cancel-allocation", name="shards_cancel_allocation")
     */
    public function cancelAllocation(Request $request, string $index, string $number): Response
    {
        $this->denyAccessUnlessGranted('SHARDS', 'global');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        try {
            $json = [
                'commands' => [
                    [
                        'cancel' => [
                            'index' => $index->getName(),
                            'shard' => $number,
                            'node' => $request->query->get('node'),
                            'allow_primary' => true,
                        ],
                    ],
                ],
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_cluster/reroute');
            $callRequest->setJson($json);
            $callResponse = $this->callManager->call($callRequest);

            $content = $callResponse->getContent();
            unset($content['state']);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        sleep(2);

        if ('indices_read_shards' == $request->query->get('redirect')) {
            return $this->redirectToRoute('indices_read_shards', ['index' => $index->getName()]);
        } else {
            return $this->redirectToRoute('shards');
        }
    }
}
