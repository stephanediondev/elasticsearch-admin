<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\AliasType;
use App\Form\IndiceType;
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

        return $this->renderAbstract($request, 'snapshots_index.html.twig', [
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
     * @Route("/snapshots/{repository}/{snapshot}", name="snapshots_read")
     */
    public function read(Request $request, string $repository, string $snapshot): Response
    {
        $query = [
        ];
        $snapshot = $this->queryManager->query('GET', '/_snapshot/'.$repository.'/'.$snapshot, ['query' => $query]);

        if ($snapshot) {
            return $this->renderAbstract($request, 'snapshots_read.html.twig', [
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

            return $this->renderAbstract($request, 'snapshots_read_failures.html.twig', [
                'repository' => $repository,
                'snapshot' => $snapshot['snapshots'][0],
                'nodes' => $nodes,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
