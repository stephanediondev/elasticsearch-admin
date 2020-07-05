<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateRepositoryType;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchRepositoryModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class RepositoryController extends AbstractAppController
{
    /**
     * @Route("/repositories", name="repositories")
     */
    public function index(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/repositories');
        $callResponse = $this->callManager->call($callRequest);
        $repositories = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/repository/repository_index.html.twig', [
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
     * @Route("/repositories/create/{type}", name="repositories_create")
     */
    public function create(Request $request, string $type): Response
    {
        if ('s3' == $type && false == $this->hasPlugin('repository-s3')) {
            throw new AccessDeniedHttpException();
        }

        if ('gcs' == $type && false == $this->hasPlugin('repository-gcs')) {
            throw new AccessDeniedHttpException();
        }

        $repositoryModel = new ElasticsearchRepositoryModel();
        $repositoryModel->setType($type);
        $form = $this->createForm(CreateRepositoryType::class, $repositoryModel, ['type' => $type]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $repositoryModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_snapshot/'.$repositoryModel->getName());
                if ($repositoryModel->getVerify()) {
                    $callRequest->setQuery(['verify' => 'true']);
                } else {
                    $callRequest->setQuery(['verify' => 'false']);
                }
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('repositories_read', ['repository' => $repositoryModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/repository/repository_create.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }

    /**
     * @Route("/repositories/{repository}", name="repositories_read")
     */
    public function read(Request $request, string $repository): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$repository);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $repositoryQuery = $callResponse->getContent();
        $repositoryQuery = $repositoryQuery[key($repositoryQuery)];

        $repositoryQuery['id'] = $repository;
        $repository = $repositoryQuery;

        return $this->renderAbstract($request, 'Modules/repository/repository_read.html.twig', [
            'repository' => $repository,
        ]);
    }

    /**
     * @Route("/repositories/{repository}/update", name="repositories_update")
     */
    public function update(Request $request, string $repository): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_snapshot/'.$repository);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $repositoryQuery = $callResponse->getContent();
        $repositoryQuery = $repositoryQuery[key($repositoryQuery)];

        $repositoryQuery['id'] = $repository;
        $repository = $repositoryQuery;

        $repositoryModel = new ElasticsearchRepositoryModel();
        $repositoryModel->convert($repository);
        $form = $this->createForm(CreateRepositoryType::class, $repositoryModel, ['type' => $repository['type'], 'update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $repositoryModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_snapshot/'.$repositoryModel->getName());
                if ($repositoryModel->getVerify()) {
                    $callRequest->setQuery(['verify' => 'true']);
                } else {
                    $callRequest->setQuery(['verify' => 'false']);
                }
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('repositories_read', ['repository' => $repositoryModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/repository/repository_update.html.twig', [
            'repository' => $repository,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/repositories/{repository}/delete", name="repositories_delete")
     */
    public function delete(Request $request, string $repository): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_snapshot/'.$repository);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('repositories');
    }

    /**
     * @Route("/repositories/{repository}/cleanup", name="repositories_cleanup")
     */
    public function cleanup(Request $request, string $repository): Response
    {
        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_snapshot/'.$repository.'/_cleanup');
            $callResponse = $this->callManager->call($callRequest);

            $this->addFlash('info', json_encode($callResponse->getContent()));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('repositories_read', ['repository' => $repository]);
    }

    /**
     * @Route("/repositories/{repository}/verify", name="repositories_verify")
     */
    public function verify(Request $request, string $repository): Response
    {
        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_snapshot/'.$repository.'/_verify');
            $callResponse = $this->callManager->call($callRequest);

            $this->addFlash('info', json_encode($callResponse->getContent()));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('repositories_read', ['repository' => $repository]);
    }
}
