<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
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
        $call = new CallModel();
        $call->setPath('/_cat/repositories');
        $repositories = $this->callManager->call($call);

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
        $call = new CallModel();
        $call->setPath('/_snapshot/'.$repository);
        $repositoryQuery = $this->callManager->call($call);
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
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_snapshot/'.$repository);
        $this->callManager->callManager->call($call);

        $this->addFlash('success', 'repositories_delete');

        return $this->redirectToRoute('repositories', []);
    }
}
