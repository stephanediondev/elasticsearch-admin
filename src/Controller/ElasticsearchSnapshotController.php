<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchSnapshotCloneType;
use App\Form\Type\ElasticsearchSnapshotFilterType;
use App\Form\Type\ElasticsearchSnapshotRestoreType;
use App\Form\Type\ElasticsearchSnapshotType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchNodeManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Manager\ElasticsearchSnapshotManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchSnapshotCloneModel;
use App\Model\ElasticsearchSnapshotModel;
use App\Model\ElasticsearchSnapshotRestoreModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
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

    #[Route('/snapshots', name: 'snapshots', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS_LIST', 'snapshot');

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

    #[Route('/snapshots/stats', name: 'snapshots_stats', methods: ['GET'])]
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS_STATS', 'snapshot');

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
            usort($data['tables'][$table], [$this, 'sortByTotal']);
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_stats.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function sortByTotal(array $a, array $b): int
    {
        return $b['total'] <=> $a['total'];
    }

    #[Route('/snapshots/create', name: 'snapshots_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS_CREATE', 'snapshot');

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $snapshot = new ElasticsearchSnapshotModel();
        if ($request->query->get('repository')) {
            $snapshot->setRepository($request->query->getString('repository'));
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

    #[Route('/snapshots/{repository}/{snapshot}', name: 'snapshots_read', methods: ['GET'])]
    public function read(Request $request, string $repository, string $snapshot): Response
    {
        $this->denyAccessUnlessGranted('SNAPSHOTS_LIST', 'snapshot');

        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($snapshot, $repository);

        if (null === $snapshot) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read.html.twig', [
            'snapshot' => $snapshot,
        ]);
    }

    #[Route('/snapshots/{repository}/{snapshot}/failures', name: 'snapshots_read_failures', methods: ['GET'])]
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

    #[Route('/snapshots/{repository}/{snapshot}/delete', name: 'snapshots_delete', methods: ['GET'])]
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

    #[Route('/snapshots/{repository}/{snapshot}/restore', name: 'snapshots_read_restore', methods: ['GET', 'POST'])]
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

    #[Route('/snapshots/{repository}/{snapshot}/clone', name: 'snapshots_read_clone', methods: ['GET', 'POST'])]
    public function clone(Request $request, string $repository, string $snapshot): Response
    {
        if (false === $this->callManager->hasFeature('clone_snapshot')) {
            throw new AccessDeniedException();
        }

        $snapshot = $this->elasticsearchSnapshotManager->getByNameAndRepository($snapshot, $repository);

        if (null === $snapshot) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SNAPSHOT_CLONE', $snapshot);

        $snapshotCloneModel = new ElasticsearchSnapshotCloneModel();
        $snapshotCloneModel->setIndices($snapshot->getIndices());
        $form = $this->createForm(ElasticsearchSnapshotCloneType::class, $snapshotCloneModel, ['indices' => $snapshot->getIndices()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $snapshotCloneModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_snapshot/'.$snapshot->getRepository().'/'.$snapshot->getName().'/_clone/'.$snapshotCloneModel->getTargetName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('snapshots_read', ['repository' => $snapshot->getRepository(), 'snapshot' => $snapshotCloneModel->getTargetName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read_clone.html.twig', [
            'form' => $form->createView(),
            'repository' => $repository,
            'snapshot' => $snapshot,
        ]);
    }
}
