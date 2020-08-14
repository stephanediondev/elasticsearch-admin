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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @Route("/shards/stats", name="shards_stats")
     */
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHARDS_STATS', 'global');

        $shards = $this->elasticsearchShardManager->getAll($request->query->get('s', 'index:asc,shard:asc,prirep:asc'));

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['shards_total'] = 0;
        $data['totals']['shards_total_unassigned'] = 0;
        $data['totals']['shards_total_documents'] = 0;
        $data['totals']['shards_total_size'] = 0;

        $tables = [
            'shards_by_state',
            'shards_by_unassigned_reason',
            'shards_by_node',
        ];

        foreach ($shards as $shard) {
            $data['totals']['shards_total']++;

            if ('unassigned' == $shard->getState()) {
                $data['totals']['shards_total_unassigned']++;
            }
            $data['totals']['shards_total_documents'] += $shard->getDocuments();
            $data['totals']['shards_total_size'] += $shard->getSize();

            foreach ($tables as $table) {
                switch ($table) {
                    case 'shards_by_node':
                        if ($shard->getNode()) {
                            if (false === isset($data['tables'][$table]['results'][$shard->getNode()])) {
                                $data['tables'][$table]['results'][$shard->getNode()] = ['total' => 0, 'title' => $shard->getNode()];
                            }
                            $data['tables'][$table]['results'][$shard->getNode()]['total']++;
                        }
                        break;
                    case 'shards_by_state':
                    case 'shards_by_unassigned_reason':
                        switch ($table) {
                            case 'shards_by_state':
                                $key = $shard->getState();
                                break;
                            case 'shards_by_unassigned_reason':
                                $key = $shard->getUnassignedReason();
                                break;
                            default:
                                $key = false;
                        }
                        if ($key) {
                            if (false === isset($data['tables'][$table]['results'][$key])) {
                                $data['tables'][$table]['results'][$key] = ['total' => 0, 'title' => $key];
                            }
                            $data['tables'][$table]['results'][$key]['total']++;
                        }
                        break;
                }
            }
        }

        foreach ($tables as $table) {
            if (true === isset($data['tables'][$table]['results'])) {
                usort($data['tables'][$table]['results'], [$this, 'sortByTotal']);
            }
        }

        return $this->renderAbstract($request, 'Modules/shard/shard_stats.html.twig', [
            'data' => $data,
        ]);
    }

    private function sortByTotal($a, $b)
    {
        return $b['total'] - $a['total'];
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
            $content = $this->clusterReroute($json);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

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
            $content = $this->clusterReroute($json);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

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
            $content = $this->clusterReroute($json);

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        if ('indices_read_shards' == $request->query->get('redirect')) {
            return $this->redirectToRoute('indices_read_shards', ['index' => $index->getName()]);
        } else {
            return $this->redirectToRoute('shards');
        }
    }

    private function clusterReroute(array $json): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_cluster/reroute');
        $callRequest->setJson($json);
        $callRequest->setQuery(['explain' => 'true']);
        $callResponse = $this->callManager->call($callRequest);

        $content = $callResponse->getContent();
        unset($content['state']);

        return $content;
    }
}
