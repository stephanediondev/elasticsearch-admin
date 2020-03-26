<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\CreateSnapshotType;
use App\Model\ElasticsearchSnapshotModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SnapshotsController extends AbstractAppController
{
    /**
     * @Route("snapshots", name="snapshots")
     */
    public function index(Request $request): Response
    {
        $repositories = [];
        $snapshots = [];

        $query = [
        ];
        $rows = $this->queryManager->query('GET', '/_cat/repositories', ['query' => $query]);

        foreach ($rows as $row) {
            $repositories[] = $row['id'];
        }

        foreach ($repositories as $repository) {
            $query = [
            ];
            $rows = $this->queryManager->query('GET', '/_snapshot/'.$repository.'/_all', ['query' => $query]);

            foreach ($rows['snapshots'] as $row) {
                $row['repository'] = $repository;
                $snapshots[] = $row;
            }
        }

        return $this->renderAbstract($request, 'Modules/snapshots/snapshots_index.html.twig', [
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
        $repositories = [];
        $indices = [];

        $query = [
            's' => 'id',
            'h' => 'id'
        ];
        $rows = $this->queryManager->query('GET', '/_cat/repositories', ['query' => $query]);

        foreach ($rows as $row) {
            $repositories[] = $row['id'];
        }

        $query = [
            's' => 'index',
            'h' => 'index'
        ];
        $rows = $this->queryManager->query('GET', '/_cat/indices', ['query' => $query]);

        foreach ($rows as $row) {
            $indices[] = $row['index'];
        }

        $snapshot = new ElasticsearchSnapshotModel();
        $form = $this->createForm(CreateSnapshotType::class, $snapshot, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $body = [
                'ignore_unavailable' => $snapshot->getIgnoreUnavailable(),
                'include_global_state' => $snapshot->getIncludeGlobalState(),
            ];
            if ($snapshot->getIndices()) {
                $body['indices'] = implode(',', $snapshot->getIndices());
            }
            $this->queryManager->query('PUT', '/_snapshot/'.$snapshot->getRepository().'/'.$snapshot->getName(), ['body' => $body]);

            $this->addFlash('success', 'snapshots_create');

            return $this->redirectToRoute('snapshots_read', ['repository' => $snapshot->getRepository(), 'snapshot' => $snapshot->getName()]);
        }

        return $this->renderAbstract($request, 'Modules/snapshots/snapshots_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/snapshots/{repository}/{snapshot}", name="snapshots_read")
     */
    public function read(Request $request, string $repository, string $snapshot): Response
    {
        $query = [
        ];
        $snapshot = $this->queryManager->query('GET', '/_snapshot/'.$repository.'/'.$snapshot, ['query' => $query]);

        if ($snapshot) {
            return $this->renderAbstract($request, 'Modules/snapshots/snapshots_read.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot['snapshots'][0],
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
        $query = [
        ];
        $snapshot = $this->queryManager->query('GET', '/_snapshot/'.$repository.'/'.$snapshot, ['query' => $query]);

        if ($snapshot) {
            $nodes = [];

            $query = [
            ];
            $rows = $this->queryManager->query('GET', '/_nodes', ['query' => $query]);

            foreach ($rows['nodes'] as $k => $row) {
                $nodes[$k] = $row['name'];
            }

            return $this->renderAbstract($request, 'Modules/snapshots/snapshots_read_failures.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot['snapshots'][0],
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
        $query = [
        ];
        $this->queryManager->query('DELETE', '/_snapshot/'.$repository.'/'.$snapshot, ['query' => $query]);

        $this->addFlash('success', 'snapshots_delete');

        return $this->redirectToRoute('snapshots', []);
    }
}
