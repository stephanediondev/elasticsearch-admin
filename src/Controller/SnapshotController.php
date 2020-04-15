<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSnapshotType;
use App\Form\RestoreSnapshotType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
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
class SnapshotController extends AbstractAppController
{
    /**
     * @Route("/snapshots", name="snapshots")
     */
    public function index(Request $request, ElasticsearchRepositoryManager $elasticsearchRepositoryManager): Response
    {
        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $snapshots = [];

        foreach ($repositories as $repository) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_snapshot/'.$repository.'/_all');
            $callResponse = $this->callManager->call($callRequest);
            $rows = $callResponse->getContent();

            foreach ($rows['snapshots'] as $row) {
                $row['repository'] = $repository;
                $snapshots[] = $row;
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_index.html.twig', [
            'snapshots' => $this->paginatorManager->paginate([
                'route' => 'snapshots',
                'route_parameters' => [],
                'total' => count($snapshots),
                'rows' => $snapshots,
                'page' => 1,
                'size' => count($snapshots),
            ]),
        ]);
    }

    /**
     * @Route("/snapshots/create", name="snapshots_create")
     */
    public function create(Request $request, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $indices = $elasticsearchIndexManager->selectIndices();

        $snapshotModel = new ElasticsearchSnapshotModel();
        if ($request->query->get('repository')) {
            $snapshotModel->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $snapshotModel->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(CreateSnapshotType::class, $snapshotModel, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'ignore_unavailable' => $snapshotModel->getIgnoreUnavailable(),
                    'partial' => $snapshotModel->getPartial(),
                    'include_global_state' => $snapshotModel->getIncludeGlobalState(),
                ];
                if ($snapshotModel->getIndices()) {
                    $json['indices'] = implode(',', $snapshotModel->getIndices());
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_snapshot/'.$snapshotModel->getRepository().'/'.$snapshotModel->getName());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'flash_success.snapshots_create');

                return $this->redirectToRoute('snapshots_read', ['repository' => $snapshotModel->getRepository(), 'snapshot' => $snapshotModel->getName()]);
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $snapshot = $callResponse->getContent();
        $snapshot = $snapshot['snapshots'][0];

        return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read.html.twig', [
            'repository' => $repository,
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/failures", name="snapshots_read_failures")
     */
    public function readFailures(Request $request, string $repository, string $snapshot): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $snapshot = $callResponse->getContent();
        $snapshot = $snapshot['snapshots'][0];

        $nodes = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows['nodes'] as $k => $row) {
            $nodes[$k] = $row['name'];
        }

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
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $this->callManager->call($callRequest);

        $this->addFlash('success', 'flash_success.snapshots_delete');

        return $this->redirectToRoute('snapshots', []);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/restore", name="snapshots_read_restore")
     */
    public function restore(Request $request, string $repository, string $snapshot): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $snapshot = $callResponse->getContent();
        $snapshot = $snapshot['snapshots'][0];

        $snapshotRestoreModel = new ElasticsearchSnapshotRestoreModel();
        $snapshotRestoreModel->setIndices($snapshot['indices']);
        $form = $this->createForm(RestoreSnapshotType::class, $snapshotRestoreModel, ['indices' => $snapshot['indices']]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'ignore_unavailable' => $snapshotRestoreModel->getIgnoreUnavailable(),
                    'partial' => $snapshotRestoreModel->getPartial(),
                    'include_global_state' => $snapshotRestoreModel->getIncludeGlobalState(),
                ];
                if ($snapshotRestoreModel->getRenamePattern()) {
                    $json['rename_pattern'] = $snapshotRestoreModel->getRenamePattern();
                }
                if ($snapshotRestoreModel->getRenameReplacement()) {
                    $json['rename_replacement'] = $snapshotRestoreModel->getRenameReplacement();
                }
                if ($snapshotRestoreModel->getIndices()) {
                    $json['indices'] = implode(',', $snapshotRestoreModel->getIndices());
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_snapshot/'.$repository.'/'.$snapshot['snapshot'].'/_restore');
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'flash_success.snapshots_read_restore');

                return $this->redirectToRoute('snapshots_read', ['repository' => $repository, 'snapshot' => $snapshot['snapshot']]);
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
