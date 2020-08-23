<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchRepositoryType;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchRepositoryModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchRepositoryController extends AbstractAppController
{
    public function __construct(ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
    }

    /**
     * @Route("/repositories", name="repositories")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('REPOSITORIES', 'global');

        $repositories = $this->elasticsearchRepositoryManager->getAll();

        return $this->renderAbstract($request, 'Modules/repository/repository_index.html.twig', [
            'repositories' => $this->paginatorManager->paginate([
                'route' => 'repositories',
                'route_parameters' => [],
                'total' => count($repositories),
                'rows' => $repositories,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
        ]);
    }

    /**
     * @Route("/repositories/create/{type}", name="repositories_create")
     */
    public function create(Request $request, string $type): Response
    {
        $this->denyAccessUnlessGranted('REPOSITORIES_CREATE', 'global');

        if ('s3' == $type && false === $this->callManager->hasPlugin('repository-s3')) {
            throw new AccessDeniedException();
        }

        if ('gcs' == $type && false === $this->callManager->hasPlugin('repository-gcs')) {
            throw new AccessDeniedException();
        }

        if ('azure' == $type && false === $this->callManager->hasPlugin('repository-azure')) {
            throw new AccessDeniedException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();
        if (true === isset($clusterSettings['path.repo']) && is_array($clusterSettings['path.repo'])) {
            $paths = $clusterSettings['path.repo'];
        } elseif (true === isset($clusterSettings['path.repo.0']) && is_string($clusterSettings['path.repo.0'])) {
            $paths = [$clusterSettings['path.repo.0']];
        } else {
            $paths = [];
        }

        $repository = new ElasticsearchRepositoryModel();
        $repository->setType($type);
        $form = $this->createForm(ElasticsearchRepositoryType::class, $repository, ['type' => $type, 'paths' => $paths]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchRepositoryManager->send($repository);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('repositories_read', ['repository' => $repository->getName()]);
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
        $this->denyAccessUnlessGranted('REPOSITORIES', 'global');

        $repository = $this->elasticsearchRepositoryManager->getByName($repository);

        if (null === $repository) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/repository/repository_read.html.twig', [
            'repository' => $repository,
        ]);
    }

    /**
     * @Route("/repositories/{repository}/update", name="repositories_update")
     */
    public function update(Request $request, string $repository): Response
    {
        $repository = $this->elasticsearchRepositoryManager->getByName($repository);

        if (null === $repository) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('REPOSITORY_UPDATE', $repository);

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();
        if (true === isset($clusterSettings['path.repo']) && is_array($clusterSettings['path.repo'])) {
            $paths = $clusterSettings['path.repo'];
        } elseif (true === isset($clusterSettings['path.repo.0']) && is_string($clusterSettings['path.repo.0'])) {
            $paths = [$clusterSettings['path.repo.0']];
        } else {
            $paths = [];
        }

        $form = $this->createForm(ElasticsearchRepositoryType::class, $repository, ['type' => $repository->getType(), 'paths' => $paths, 'context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchRepositoryManager->send($repository);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('repositories_read', ['repository' => $repository->getName()]);
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
        $repository = $this->elasticsearchRepositoryManager->getByName($repository);

        if (null === $repository) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('REPOSITORY_DELETE', $repository);

        $callResponse = $this->elasticsearchRepositoryManager->deleteByName($repository->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('repositories');
    }

    /**
     * @Route("/repositories/{repository}/cleanup", name="repositories_cleanup")
     */
    public function cleanup(Request $request, string $repository): Response
    {
        $repository = $this->elasticsearchRepositoryManager->getByName($repository);

        if (null === $repository) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('REPOSITORY_CLEANUP', $repository);

        try {
            $callResponse = $this->elasticsearchRepositoryManager->cleanupByName($repository->getName());

            $this->addFlash('info', json_encode($callResponse->getContent()));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('repositories_read', ['repository' => $repository->getName()]);
    }

    /**
     * @Route("/repositories/{repository}/verify", name="repositories_verify")
     */
    public function verify(Request $request, string $repository): Response
    {
        $repository = $this->elasticsearchRepositoryManager->getByName($repository);

        if (null === $repository) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('REPOSITORY_VERIFY', $repository);

        try {
            $callResponse = $this->elasticsearchRepositoryManager->verifyByName($repository->getName());

            $this->addFlash('info', json_encode($callResponse->getContent()));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('repositories_read', ['repository' => $repository->getName()]);
    }
}
