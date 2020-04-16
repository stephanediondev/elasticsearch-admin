<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\CreateUserType;
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
class UserController extends AbstractAppController
{
    /**
     * @Route("/users", name="users")
     */
    public function index(Request $request): Response
    {
        $users = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user');
        $callResponse = $this->callManager->call($callRequest);
        $users1 = $callResponse->getContent();

        foreach ($users1 as $k => $user) {
            $user['user'] = $k;
            $users[$k] = $user;
        }
        ksort($users);

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
     * @Route("/users/create", name="users_create")
     */
    public function create(Request $request, ElasticsearchRoleManager $elasticsearchRoleManager): Response
    {
        $roles = $elasticsearchRoleManager->selectRoles();

        $userModel = new ElasticsearchUserModel();
        $form = $this->createForm(CreateUserType::class, $userModel, ['roles' => $roles]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'enabled' => $userModel->getEnabled(),
                    'email' => $userModel->getEmail(),
                    'full_name' => $userModel->getFullName(),
                    'password' => $userModel->getPassword(),
                    'roles' => $userModel->getRoles(),
                ];
                if ($userModel->getMetadata()) {
                    $json['metadata'] = json_decode($userModel->getMetadata(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_security/user/'.$userModel->getUsername());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'flash_success.users_create');

                return $this->redirectToRoute('users_read', ['user' => $userModel->getUsername()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/user/user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{user}", name="users_read")
     */
    public function read(Request $request, string $user): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user/'.$user);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $user = $callResponse->getContent();
        $userNice = $user[key($user)];
        $userNice['user'] = key($user);

        return $this->renderAbstract($request, 'Modules/user/user_read.html.twig', [
            'user' => $userNice,
        ]);
    }

    /**
     * @Route("/users/{user}/update", name="users_update")
     */
    public function update(Request $request, string $user, ElasticsearchRoleManager $elasticsearchRoleManager): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user/'.$user);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $user = $callResponse->getContent();
        $userNice = $user[key($user)];
        $userNice['user'] = key($user);
        $user = $userNice;

        if (true == isset($user['metadata']) && true == isset($user['metadata']['_reserved']) && true == $user['metadata']['_reserved']) {
            throw new AccessDeniedHttpException();
        }

        $roles = $elasticsearchRoleManager->selectRoles();

        $userModel = new ElasticsearchUserModel();
        $userModel->convert($user);
        $form = $this->createForm(CreateUserType::class, $userModel, ['roles' => $roles, 'update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'email' => $userModel->getEmail(),
                    'full_name' => $userModel->getFullName(),
                    'roles' => $userModel->getRoles(),
                ];
                if ($userModel->getMetadata()) {
                    $json['metadata'] = json_decode($userModel->getMetadata(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_security/user/'.$userModel->getUsername());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                if ($userModel->getChangePassword() && $userModel->getPassword()) {
                    $json = [
                        'password' => $userModel->getPassword(),
                    ];
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('POST');
                    $callRequest->setPath('/_security/user/'.$userModel->getUsername().'/_password');
                    $callRequest->setJson($json);
                    $this->callManager->call($callRequest);
                }

                $this->addFlash('success', 'flash_success.users_update');

                return $this->redirectToRoute('users_read', ['user' => $userModel->getUsername()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/user/user_update.html.twig', [
            'user' => $userNice,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{user}/enable", name="users_enable")
     */
    public function enable(Request $request, string $user): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user/'.$user);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $user = $callResponse->getContent();
        $userNice = $user[key($user)];
        $userNice['user'] = key($user);
        $user = $userNice;

        if (true == isset($user['enabled']) && true == $user['enabled']) {
            throw new AccessDeniedHttpException();
        }

        if (true == isset($user['metadata']) && true == isset($user['metadata']['_reserved']) && true == $user['metadata']['_reserved']) {
            throw new AccessDeniedHttpException();
        }

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setPath('/_security/user/'.$user['username'].'/_enable');
            $this->callManager->call($callRequest);

            $this->addFlash('success', 'flash_success.users_enable');
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('users_read', ['user' => $user['username']]);
    }

    /**
     * @Route("/users/{user}/disable", name="users_disable")
     */
    public function disable(Request $request, string $user): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user/'.$user);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $user = $callResponse->getContent();
        $userNice = $user[key($user)];
        $userNice['user'] = key($user);
        $user = $userNice;

        if (true == isset($user['enabled']) && false == $user['enabled']) {
            throw new AccessDeniedHttpException();
        }

        if (true == isset($user['metadata']) && true == isset($user['metadata']['_reserved']) && true == $user['metadata']['_reserved']) {
            throw new AccessDeniedHttpException();
        }

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setPath('/_security/user/'.$user['username'].'/_disable');
            $this->callManager->call($callRequest);

            $this->addFlash('success', 'flash_success.users_disable');
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('users_read', ['user' => $user['username']]);
    }

    /**
     * @Route("/users/{user}/delete", name="users_delete")
     */
    public function delete(Request $request, string $user): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/user/'.$user);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $user = $callResponse->getContent();
        $userNice = $user[key($user)];
        $userNice['user'] = key($user);
        $user = $userNice;

        if (true == isset($user['metadata']) && true == isset($user['metadata']['_reserved']) && true == $user['metadata']['_reserved']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_security/user/'.$user['username']);
        $this->callManager->call($callRequest);

        $this->addFlash('success', 'flash_success.users_delete');

        return $this->redirectToRoute('users');
    }
}
