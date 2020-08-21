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

        $form = $this->createForm(ElasticsearchSnapshotFilterType::class);

        $form->handleRequest($request);

        $snapshots = $this->elasticsearchSnapshotManager->getAll($repositories, ['name' => $form->get('name')->getData()]);

        $size = 100;
        if ($request->query->get('page') && '' != $request->query->get('page')) {
            $page = $request->query->get('page');
        } else {
            $page = 1;
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_index.html.twig', [
            'snapshots' => $this->paginatorManager->paginate([
                'route' => 'snapshots',
                'route_parameters' => [],
                'total' => count($snapshots),
                'rows' => array_slice($snapshots, ($size * $page) - $size, $size),
                'page' => $page,
                'size' => $size,
            ]),
            'form' => $form->createView(),
        ]);
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
