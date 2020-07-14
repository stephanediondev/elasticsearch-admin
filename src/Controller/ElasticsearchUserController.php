<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateElasticsearchUserType;
use App\Manager\ElasticsearchUserManager;
use App\Manager\ElasticsearchRoleManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchUserModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchUserController extends AbstractAppController
{
    public function __construct(ElasticsearchRoleManager $elasticsearchRoleManager, ElasticsearchUserManager $elasticsearchUserManager)
    {
        $this->elasticsearchRoleManager = $elasticsearchRoleManager;
        $this->elasticsearchUserManager = $elasticsearchUserManager;
    }

    /**
     * @Route("/elasticsearch-users", name="elasticsearch_users")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_USERS', 'global');

        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $users = $this->elasticsearchUserManager->getAll();

        return $this->renderAbstract($request, 'Modules/user/user_index.html.twig', [
            'users' => $this->paginatorManager->paginate([
                'route' => 'users',
                'route_parameters' => [],
                'total' => count($users),
                'rows' => $users,
                'page' => 1,
                'size' => count($users),
            ]),
        ]);
    }

    /**
     * @Route("/elasticsearch-users/create", name="elasticsearch_users_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_USERS_CREATE', 'global');

        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $roles = $this->elasticsearchRoleManager->selectRoles();

        $user = new ElasticsearchUserModel();
        $form = $this->createForm(CreateElasticsearchUserType::class, $user, ['roles' => $roles]);

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
                $callRequest->setPath('/_security/user/'.$user->getName());
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

    /**
     * @Route("/elasticsearch-users/{user}", name="elasticsearch_users_read")
     */
    public function read(Request $request, string $user): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_USERS', 'global');

        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (false == $user) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/user/user_read.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/elasticsearch-users/{user}/update", name="elasticsearch_users_update")
     */
    public function update(Request $request, string $user): Response
    {
        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (false == $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_USER_UPDATE', $user);

        $roles = $this->elasticsearchRoleManager->selectRoles();

        $form = $this->createForm(CreateElasticsearchUserType::class, $user, ['roles' => $roles, 'context' => 'update']);

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
                $callRequest->setPath('/_security/user/'.$user->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                if ($user->getChangePassword() && $user->getPassword()) {
                    $json = [
                        'password' => $user->getPassword(),
                    ];
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('POST');
                    $callRequest->setPath('/_security/user/'.$user->getName().'/_password');
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

    /**
     * @Route("/elasticsearch-users/{user}/enable", name="elasticsearch_users_enable")
     */
    public function enable(Request $request, string $user): Response
    {
        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (false == $user) {
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

    /**
     * @Route("/elasticsearch-users/{user}/disable", name="elasticsearch_users_disable")
     */
    public function disable(Request $request, string $user): Response
    {
        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (false == $user) {
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

    /**
     * @Route("/elasticsearch-users/{user}/delete", name="elasticsearch_users_delete")
     */
    public function delete(Request $request, string $user): Response
    {
        if (false == $this->callManager->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $user = $this->elasticsearchUserManager->getByName($user);

        if (false == $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_USER_DELETE', $user);

        $callResponse = $this->elasticsearchUserManager->deleteByName($user->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('elasticsearch_users');
    }
}
