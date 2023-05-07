<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchUserFilterType;
use App\Form\Type\ElasticsearchUserType;
use App\Manager\ElasticsearchRoleManager;
use App\Manager\ElasticsearchUserManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchUserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchUserController extends AbstractAppController
{
    private ElasticsearchRoleManager $elasticsearchRoleManager;

    private ElasticsearchUserManager $elasticsearchUserManager;

    public function __construct(ElasticsearchRoleManager $elasticsearchRoleManager, ElasticsearchUserManager $elasticsearchUserManager)
    {
        $this->elasticsearchRoleManager = $elasticsearchRoleManager;
        $this->elasticsearchUserManager = $elasticsearchUserManager;
    }

    #[Route('/elasticsearch-users', name: 'elasticsearch_users')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_USERS', 'global');

        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ElasticsearchUserFilterType::class, null, ['context' => 'user']);

        $form->handleRequest($request);

        $users = $this->elasticsearchUserManager->getAll([
            'enabled' => $form->has('enabled') ? $form->get('enabled')->getData() : false,
            'reserved' => $form->has('reserved') ? $form->get('reserved')->getData() : false,
            'deprecated' => $form->has('deprecated') ? $form->get('deprecated')->getData() : false,
        ]);

        return $this->renderAbstract($request, 'Modules/user/user_index.html.twig', [
            'users' => $this->paginatorManager->paginate([
                'route' => 'elasticsearch_users',
                'route_parameters' => [],
                'total' => count($users),
                'rows' => $users,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/elasticsearch-users/create', name: 'elasticsearch_users_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_USERS_CREATE', 'global');

        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $roles = $this->elasticsearchRoleManager->selectRoles();

        $user = new ElasticsearchUserModel();
        $form = $this->createForm(ElasticsearchUserType::class, $user, ['roles' => $roles]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'enabled' => $user->getEnabled(),
                    'email' => $user->getEmail(),
                    'full_name' => $user->getFullName(),
                    'password' => $user->getPassword(),
                    'roles' => $user->getRoles(),
                ];
                if ($user->getMetadata()) {
                    $json['metadata'] = $user->getMetadata();
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath($this->elasticsearchUserManager->getEndpoint().'/user/'.$user->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('elasticsearch_users_read', ['user' => $user->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/user/user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/elasticsearch-users/{user}', name: 'elasticsearch_users_read')]
    public function read(Request $request, string $user): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_USERS', 'global');

        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/user/user_read.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/elasticsearch-users/{user}/update', name: 'elasticsearch_users_update')]
    public function update(Request $request, string $user): Response
    {
        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_USER_UPDATE', $user);

        $roles = $this->elasticsearchRoleManager->selectRoles();

        $form = $this->createForm(ElasticsearchUserType::class, $user, ['roles' => $roles, 'context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'email' => $user->getEmail(),
                    'full_name' => $user->getFullName(),
                    'roles' => $user->getRoles(),
                ];
                if ($user->getMetadata()) {
                    $json['metadata'] = $user->getMetadata();
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath($this->elasticsearchUserManager->getEndpoint().'/user/'.$user->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                if ($user->getChangePassword() && $user->getPassword()) {
                    $json = [
                        'password' => $user->getPassword(),
                    ];
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('POST');
                    $callRequest->setPath($this->elasticsearchUserManager->getEndpoint().'/user/'.$user->getName().'/_password');
                    $callRequest->setJson($json);
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));
                }

                return $this->redirectToRoute('elasticsearch_users_read', ['user' => $user->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/user/user_update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/elasticsearch-users/{user}/enable', name: 'elasticsearch_users_enable')]
    public function enable(Request $request, string $user): Response
    {
        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_USER_ENABLE', $user);

        try {
            $callResponse = $this->elasticsearchUserManager->enableByName($user->getName());

            $this->addFlash('info', json_encode($callResponse->getContent()));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('elasticsearch_users_read', ['user' => $user->getName()]);
    }

    #[Route('/elasticsearch-users/{user}/disable', name: 'elasticsearch_users_disable')]
    public function disable(Request $request, string $user): Response
    {
        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_USER_DISABLE', $user);

        try {
            $callResponse = $this->elasticsearchUserManager->disableByName($user->getName());

            $this->addFlash('info', json_encode($callResponse->getContent()));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('elasticsearch_users_read', ['user' => $user->getName()]);
    }

    #[Route('/elasticsearch-users/{user}/delete', name: 'elasticsearch_users_delete')]
    public function delete(Request $request, string $user): Response
    {
        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_USER_DELETE', $user);

        $callResponse = $this->elasticsearchUserManager->deleteByName($user->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('elasticsearch_users');
    }
}
