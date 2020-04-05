<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSnapshotType;
use App\Form\RestoreSnapshotType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallModel;
use App\Model\ElasticsearchSnapshotModel;
use App\Model\ElasticsearchSnapshotRestoreModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class SnapshotController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
    }

    /**
     * @Route("/snapshots", name="snapshots")
     */
    public function index(Request $request): Response
    {
        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $snapshots = [];

        foreach ($repositories as $repository) {
            $call = new CallModel();
            $call->setPath('/_snapshot/'.$repository.'/_all');
            $rows = $this->callManager->call($call);

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
    public function create(Request $request): Response
    {
        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

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
                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_snapshot/'.$snapshotModel->getRepository().'/'.$snapshotModel->getName());
                $call->setJson($json);
                $this->callManager->call($call);

                $this->addFlash('success', 'success.snapshots_create');

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
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $snapshot = $this->callManager->call($call);
        $snapshot = $snapshot['snapshots'][0];

        if ($snapshot) {
            return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/failures", name="snapshots_read_failures")
     */
    public function readFailures(Request $request, string $repository, string $snapshot): Response
    {
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $snapshot = $this->callManager->call($call);
        $snapshot = $snapshot['snapshots'][0];

        if ($snapshot) {
            $nodes = [];

            $call = new CallModel();
            $call->setPath('/_nodes');
            $rows = $this->callManager->call($call);

            foreach ($rows['nodes'] as $k => $row) {
                $nodes[$k] = $row['name'];
            }

            return $this->renderAbstract($request, 'Modules/snapshot/snapshot_read_failures.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot,
                'nodes' => $nodes,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/delete", name="snapshots_delete")
     */
    public function delete(Request $request, string $repository, string $snapshot): Response
    {
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.snapshots_delete');

        return $this->redirectToRoute('snapshots', []);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}/restore", name="snapshots_read_restore")
     */
    public function restore(Request $request, string $repository, string $snapshot): Response
    {
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository.'/'.$snapshot);
        $snapshot = $this->callManager->call($call);
        $snapshot = $snapshot['snapshots'][0];

        if ($snapshot) {
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
                    $call = new CallModel();
                    $call->setMethod('POST');
                    $call->setPath('/_snapshot/'.$repository.'/'.$snapshot['snapshot'].'/_restore');
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'success.snapshots_read_restore');

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
        } else {
            throw new NotFoundHttpException();
        }
    }
}
