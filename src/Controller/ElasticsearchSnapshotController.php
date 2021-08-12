<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchSnapshotType;
use App\Form\Type\ElasticsearchSnapshotRestoreType;
use App\Form\Type\ElasticsearchSnapshotFilterType;
use App\Manager\ElasticsearchSnapshotManager;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchSnapshotModel;
use App\Model\ElasticsearchSnapshotRestoreModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchSnapshotController extends AbstractAppController
{
    private ElasticsearchSnapshotManager $elasticsearchSnapshotManager;

    private ElasticsearchRepositoryManager $elasticsearchRepositoryManager;

    private ElasticsearchIndexManager $elasticsearchIndexManager;

    private ElasticsearchNodeManager $elasticsearchNodeManager;

    public function __construct(ElasticsearchSnapshotManager $elasticsearchSnapshotManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchSnapshotManager = $elasticsearchSnapshotManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/snapshots", name="snapshots")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS', 'global');

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();

        $form = $this->createForm(ElasticsearchSnapshotFilterType::class, null, ['repository' => $repositories]);

        $form->handleRequest($request);

        $snapshots = $this->elasticsearchSnapshotManager->getAll($repositories, [
            'name' => $form->get('name')->getData(),
            'state' => $form->get('state')->getData(),
            'repository' => $form->get('repository')->getData(),
        ]);

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_index.html.twig', [
            'snapshots' => $this->paginatorManager->paginate([
                'route' => 'snapshots',
                'route_parameters' => [],
                'total' => count($snapshots),
                'rows' => $snapshots,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/snapshots/stats", name="snapshots_stats")
     */
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS_STATS', 'global');

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();

        $snapshots = $this->elasticsearchSnapshotManager->getAll($repositories);

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['snapshots_total'] = 0;
        $data['totals']['snapshots_total_success'] = 0;
        $data['tables']['snapshots_by_state'] = [];
        $data['tables']['snapshots_by_repository'] = [];

        foreach ($snapshots as $snapshot) {
            $data['totals']['snapshots_total']++;

            if ('success' == $snapshot->getState()) {
                $data['totals']['snapshots_total_success']++;
            }

            foreach (array_keys($data['tables']) as $table) {
                switch ($table) {
                    case 'snapshots_by_repository':
                        if ($snapshot->getRepository()) {
                            if (false === isset($data['tables'][$table][$snapshot->getRepository()])) {
                                $data['tables'][$table][$snapshot->getRepository()] = ['total' => 0, 'title' => $snapshot->getRepository()];
                            }
                            $data['tables'][$table][$snapshot->getRepository()]['total']++;
                        }
                        break;
                    case 'snapshots_by_state':
                        $key = false;
                        switch ($table) {
                            case 'snapshots_by_state':
                                $key = $snapshot->getState();
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
            if (true === isset($data['tables'][$table])) {
                usort($data['tables'][$table], [$this, 'sortByTotal']);
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_stats.html.twig', [
            'data' => $data,
        ]);
    }

    private function sortByTotal($a, $b): int
    {
        return $b['total'] <=> $a['total'];
    }

    /**
     * @Route("/snapshots/create", name="snapshots_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS_CREATE', 'global');

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $snapshot = new ElasticsearchSnapshotModel();
        if ($request->query->get('repository')) {
            $snapshot->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $snapshot->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(ElasticsearchSnapshotType::class, $snapshot, ['repositories' => $repositories, 'indices' => $indices]);

        try {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $callResponse = $this->elasticsearchSnapshotManager->send($snapshot);

                    $this->addFlash('info', json_encode($callResponse->getContent()));

                    return $this->redirectToRoute('snapshots_read', ['repository' => $snapshot->getRepository(), 'snapshot' => $snapshot->getName()]);
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}", name="snapshots_read")
     */
    public function read(Request $request, string $repository, string $snapshot): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS', 'global');

        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($snapshot, $repository);

        if (null === $snapshot) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read.html.twig', [
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/failures", name="snapshots_read_failures")
     */
    public function readFailures(Request $request, string $repository, string $snapshot): Response
    {
        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($snapshot, $repository);

        if (null === $snapshot) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SNAPSHOT_FAILURES', $snapshot);

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read_failures.html.twig', [
            'repository' => $repository,
            'snapshot' => $snapshot,
            'nodes' => $nodes,
        ]);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/delete", name="snapshots_delete")
     */
    public function delete(Request $request, string $repository, string $snapshot): Response
    {
        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($snapshot, $repository);

        if (null === $snapshot) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SNAPSHOT_DELETE', $snapshot);

        $callResponse = $this->elasticsearchSnapshotManager->deleteByNameAndRepository($snapshot->getName(), $snapshot->getRepository());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('snapshots');
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/restore", name="snapshots_read_restore")
     */
    public function restore(Request $request, string $repository, string $snapshot): Response
    {
        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($snapshot, $repository);

        if (null === $snapshot) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SNAPSHOT_RESTORE', $snapshot);

        $snapshotRestoreModel = new ElasticsearchSnapshotRestoreModel();
        $snapshotRestoreModel->setIndices($snapshot->getIndices());
        $form = $this->createForm(ElasticsearchSnapshotRestoreType::class, $snapshotRestoreModel, ['indices' => $snapshot->getIndices()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $snapshotRestoreModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_snapshot/'.$snapshot->getRepository().'/'.$snapshot->getName().'/_restore');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('snapshots_read', ['repository' => $snapshot->getRepository(), 'snapshot' => $snapshot->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read_restore.html.twig', [
            'form' => $form->createView(),
            'repository' => $repository,
            'snapshot' => $snapshot,
        ]);
    }
}
