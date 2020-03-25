<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RepositoriesController extends AbstractAppController
{
    /**
     * @Route("repositories", name="repositories")
     */
    public function index(Request $request): Response
    {
        $query = [
        ];
        $repositories = $this->queryManager->query('GET', '/_cat/repositories', ['query' => $query]);

        return $this->renderAbstract($request, 'Modules/repositories/repositories_index.html.twig', [
            'repositories' => $this->paginatorManager->paginate([
                'route' => 'repositories',
                'route_parameters' => [],
                'total' => count($repositories),
                'rows' => $repositories,
                'page' => 1,
                'size' => count($repositories),
            ]),
        ]);
    }

    /**
     * @Route("/repositories/{repository}", name="repositories_read")
     */
    public function read(Request $request, string $repository): Response
    {
        $query = [
        ];
        $repositoryQuery = $this->queryManager->query('GET', '/_snapshot/'.$repository, ['query' => $query]);
        $repositoryQuery = $repositoryQuery[key($repositoryQuery)];

        $repositoryQuery['id'] = $repository;
        $repository = $repositoryQuery;

        if ($repository) {
            return $this->renderAbstract($request, 'Modules/repositories/repositories_read.html.twig', [
                'repository' => $repository,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/repositories/{repository}/delete", name="repositories_delete")
     */
    public function delete(Request $request, string $repository): Response
    {
        $query = [
        ];
        $this->queryManager->query('DELETE', '/_snapshot/'.$repository, ['query' => $query]);

        $this->addFlash('success', 'repository_deleted');

        return $this->redirectToRoute('repositories', []);
    }
}
