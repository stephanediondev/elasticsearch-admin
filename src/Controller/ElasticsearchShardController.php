<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchShardFilterType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchNodeManager;
use App\Manager\ElasticsearchShardManager;
use App\Model\CallRequestModel;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class ElasticsearchShardController extends AbstractAppController
{
    private ElasticsearchShardManager $elasticsearchShardManager;

    private ElasticsearchIndexManager $elasticsearchIndexManager;

    private ElasticsearchNodeManager $elasticsearchNodeManager;

    public function __construct(ElasticsearchShardManager $elasticsearchShardManager, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchShardManager = $elasticsearchShardManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    private function filter(array $shards, FormInterface $form): array
    {
        return $this->elasticsearchShardManager->filter($shards, [
            'index' => $form->get('index')->getData(),
            'type' => $form->get('type')->getData(),
            'state' => $form->get('state')->getData(),
            'node' => $form->get('node')->getData(),
        ]);
    }

    #[Route('/shards', name: 'shards')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHARDS', 'global');

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $form = $this->createForm(ElasticsearchShardFilterType::class, null, ['node' => $nodes]);

        $form->handleRequest($request);

        $query = [
            'bytes' => 'b',
            'h' => 'index,shard,prirep,state,unassigned.reason,unassigned.details,docs,store,node',
        ];

        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = '' != $request->query->get('sort') ? $request->query->get('sort') : 'index:asc,shard:asc,prirep:asc';
        }

        $shards = $this->elasticsearchShardManager->getAll($query, [
            'index' => $form->get('index')->getData(),
        ]);

        $nodesAvailable = $this->elasticsearchShardManager->getNodesAvailable($shards, $nodes);

        $shards = $this->filter($shards, $form);

        return $this->renderAbstract($request, 'Modules/shard/shard_index.html.twig', [
            'shards' => $this->paginatorManager->paginate([
                'route' => 'shards',
                'route_parameters' => [],
                'total' => count($shards),
                'rows' => $shards,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
            'nodesAvailable' => $nodesAvailable,
        ]);
    }

    #[Route('/shards/stats', name: 'shards_stats')]
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SHARDS_STATS', 'global');

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $form = $this->createForm(ElasticsearchShardFilterType::class, null, ['node' => $nodes]);

        $form->handleRequest($request);

        $query = [
            'bytes' => 'b',
            'h' => 'index,shard,prirep,state,unassigned.reason,docs,store,node',
        ];

        $shards = $this->elasticsearchShardManager->getAll($query, [
            'index' => $form->get('index')->getData(),
        ]);

        $shards = $this->filter($shards, $form);

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['shards_total'] = 0;
        $data['totals']['shards_total_primary'] = 0;
        $data['totals']['shards_total_documents'] = 0;
        $data['totals']['shards_total_size'] = 0;
        $data['totals']['shards_total_unassigned'] = 0;
        $data['tables']['shards_by_state'] = [];
        $data['tables']['shards_by_unassigned_reason'] = [];
        $data['tables']['shards_by_type'] = [];
        $data['tables']['shards_by_node'] = [];

        foreach ($shards as $shard) {
            $data['totals']['shards_total']++;

            if ($shard->isPrimary()) {
                $data['totals']['shards_total_primary']++;
            }
            if ('unassigned' == $shard->getState()) {
                $data['totals']['shards_total_unassigned']++;
            }
            $data['totals']['shards_total_documents'] += $shard->getDocuments();
            $data['totals']['shards_total_size'] += $shard->getSize();

            foreach (array_keys($data['tables']) as $table) {
                switch ($table) {
                    case 'shards_by_node':
                        if ($shard->getNode() && 'relocating' != $shard->getState()) {
                            if (false === isset($data['tables'][$table][$shard->getNode()])) {
                                $data['tables'][$table][$shard->getNode()] = ['total' => 0, 'title' => $shard->getNode()];
                            }
                            $data['tables'][$table][$shard->getNode()]['total']++;
                        }
                        break;
                    case 'shards_by_state':
                    case 'shards_by_unassigned_reason':
                    case 'shards_by_type':
                        $key = false;
                        switch ($table) {
                            case 'shards_by_state':
                                $key = $shard->getState();
                                break;
                            case 'shards_by_unassigned_reason':
                                if ('unassigned' == $shard->getState()) {
                                    $key = $shard->getUnassignedReason();
                                }
                                break;
                            case 'shards_by_type':
                                $key = $shard->isPrimary() ? 'primary' : 'replica';
                                break;
                        }
                        if ($key) {
                            if (false === isset($data['tables'][$table][$key])) {
                                $data['tables'][$table][$key] = ['total' => 0, 'title' => $key];
                            }
                            $data['tables'][$table][$key]['total']++;
                        }
                        break;
                }
            }
        }

        foreach (array_keys($data['tables']) as $table) {
            usort($data['tables'][$table], [$this, 'sortByTotal']);
        }

        return $this->renderAbstract($request, 'Modules/shard/shard_stats.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

    private function sortByTotal(array $a, array $b): int
    {
        return $b['total'] <=> $a['total'];
    }

    #[Route('/shards/{index}/{number}/move', name: 'shards_move')]
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

        if ($request->query->get('redirect')) {
            return $this->redirect($request->query->getString('redirect'));
        } else {
            return $this->redirectToRoute('shards');
        }
    }

    #[Route('/shards/{index}/{number}/allocate-replica', name: 'shards_allocate_replica')]
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
                        $this->callManager->hasFeature('extend_reroute') ? 'allocate_replica' : 'allocate' => [
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

        if ($request->query->get('redirect')) {
            return $this->redirect($request->query->getString('redirect'));
        } else {
            return $this->redirectToRoute('shards');
        }
    }

    #[Route('/shards/{index}/{number}/cancel-allocation', name: 'shards_cancel_allocation')]
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

        if ($request->query->get('redirect')) {
            return $this->redirect($request->query->getString('redirect'));
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
