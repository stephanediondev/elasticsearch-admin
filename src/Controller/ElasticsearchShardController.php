<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchShardRerouteType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchShardManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchShardRerouteModel;
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
                        if ($shard->getNode() && 'relocating' != $shard->getState()) {
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
     * @Route("/shards/{index}/{number}/reroute", name="shards_reroute")
     */
    public function reroute(Request $request, string $index, string $number): Response
    {
        $this->denyAccessUnlessGranted('SHARDS_REROUTE', 'global');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $shards = $this->elasticsearchShardManager->getAll();

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $nodesAvailable = $this->elasticsearchShardManager->getNodesAvailable($shards, $nodes);

        $row = [
            'index' => $index->getName(),
            'shard' => $number,
            'state' => $request->query->get('state'),
            'node' => $request->query->get('node'),
        ];

        $reroute = new ElasticsearchShardRerouteModel();
        $reroute->convert($row);

        $commands = [];

        if (true === isset($nodesAvailable[$reroute->getIndex()][$reroute->getNumber()]) && 0 < count($nodesAvailable[$reroute->getIndex()][$reroute->getNumber()]) && 'unassigned' == $reroute->getState()) {
            $commands[] = $this->callManager->hasFeature('extend_reroute') ? 'allocate_replica' : 'allocate';
        }

        if ($reroute->getNode()) {
            $commands[] = 'cancel';
        }

        if (true === isset($nodesAvailable[$reroute->getIndex()][$reroute->getNumber()]) && 0 < count($nodesAvailable[$reroute->getIndex()][$reroute->getNumber()]) && $reroute->getNode()) {
            $commands[] = 'move';
        }

        if (0 < count($commands)) {
            $form = $this->createForm(ElasticsearchShardRerouteType::class, $reroute, ['commands' => $commands, 'nodes' => $nodesAvailable[$reroute->getIndex()][$reroute->getNumber()] ?? []]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    switch ($reroute->getCommand()) {
                        case 'move':
                            $command = [
                                'index' => $reroute->getIndex(),
                                'shard' => $reroute->getNumber(),
                                'from_node' => $reroute->getNode(),
                                'to_node' => $reroute->getToNode(),
                            ];
                            break;
                        case 'cancel':
                            $command = [
                                'index' => $reroute->getIndex(),
                                'shard' => $reroute->getNumber(),
                                'node' => $reroute->getNode(),
                                'allow_primary' => true,
                            ];
                            break;
                        case 'allocate_replica':
                        case 'allocate':
                            $command = [
                                'index' => $reroute->getIndex(),
                                'shard' => $reroute->getNumber(),
                                'node' => $reroute->getNode(),
                            ];
                            break;
                    }
                    $json = [
                        'commands' => [
                            [
                                $reroute->getCommand() => $command,
                            ],
                        ],
                    ];
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('POST');
                    $callRequest->setPath('/_cluster/reroute');
                    $callRequest->setJson($json);
                    $callRequest->setQuery(['explain' => 'true']);
                    $callResponse = $this->callManager->call($callRequest);

                    $content = $callResponse->getContent();
                    unset($content['state']);

                    $this->addFlash('info', json_encode($content));

                    return $this->redirectToRoute('shards');
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/shard/shard_reroute.html.twig', [
                'reroute' => $reroute,
                'form' => $form->createView(),
            ]);
        } else {
            $this->addFlash('danger', 'reroute_not_possible');

            return $this->redirectToRoute('shards');
        }
    }
}
