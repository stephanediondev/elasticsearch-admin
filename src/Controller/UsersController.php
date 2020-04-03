<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\CreateUserType;
use App\Model\CallModel;
use App\Model\ElasticsearchUserModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class UsersController extends AbstractAppController
{
    /**
     * @Route("/users", name="users")
     */
    public function index(Request $request): Response
    {
        $users = [];

        $call = new CallModel();
        $call->setPath('/_security/user');
        $users1 = $this->callManager->call($call);

        foreach ($users1 as $k => $user) {
            $user['user'] = $k;
            $users[$k] = $user;
        }
        ksort($users);

        return $this->renderAbstract($request, 'Modules/users/users_index.html.twig', [
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
    public function create(Request $request): Response
    {
        $roles = $this->callManager->selectRoles();

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
                $call = new CallModel();
                $call->setMethod('POST');
                $call->setPath('/_security/user/'.$userModel->getUsername());
                $call->setJson($json);
                $this->callManager->call($call);

                $this->addFlash('success', 'success.users_create');

                return $this->redirectToRoute('users_read', ['user' => $userModel->getUsername()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/users/users_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/users/{user}", name="users_read")
     */
    public function read(Request $request, string $user): Response
    {
        $call = new CallModel();
        $call->setPath('/_security/user/'.$user);
        $user = $this->callManager->call($call);

        if (true == isset($user[key($user)])) {
            $userNice = $user[key($user)];
            $userNice['user'] = key($user);
            return $this->renderAbstract($request, 'Modules/users/users_read.html.twig', [
                'user' => $userNice,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/users/{user}/update", name="users_update")
     */
    public function update(Request $request, string $user): Response
    {
        $call = new CallModel();
        $call->setPath('/_security/user/'.$user);
        $user = $this->callManager->call($call);

        if (true == isset($user[key($user)])) {
            $userNice = $user[key($user)];
            $userNice['user'] = key($user);
            $user = $userNice;

            if (true == isset($user['metadata']) && true == isset($user['metadata']['_reserved']) && true == $user['metadata']['_reserved']) {
                throw new AccessDeniedHttpException();
            }

            $roles = $this->callManager->selectRoles();

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
                    $call = new CallModel();
                    $call->setMethod('PUT');
                    $call->setPath('/_security/user/'.$userModel->getUsername());
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'success.users_update');

                    return $this->redirectToRoute('users_read', ['user' => $userModel->getUsername()]);
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/users/users_update.html.twig', [
                'user' => $userNice,
                'form' => $form->createView(),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/users/{user}/enable", name="users_enable")
     */
    public function enable(Request $request, string $user): Response
    {
        $call = new CallModel();
        $call->setPath('/_security/user/'.$user);
        $user = $this->callManager->call($call);

        if (true == isset($user[key($user)])) {
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
                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_security/user/'.$user['username'].'/_enable');
                $this->callManager->call($call);

                $this->addFlash('success', 'success.users_enable');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectToRoute('users_read', ['user' => $user['username']]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/users/{user}/disable", name="users_disable")
     */
    public function disable(Request $request, string $user): Response
    {
        $call = new CallModel();
        $call->setPath('/_security/user/'.$user);
        $user = $this->callManager->call($call);

        if (true == isset($user[key($user)])) {
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
                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_security/user/'.$user['username'].'/_disable');
                $this->callManager->call($call);

                $this->addFlash('success', 'success.users_disable');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            return $this->redirectToRoute('users_read', ['user' => $user['username']]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/users/{user}/delete", name="users_delete")
     */
    public function delete(Request $request, string $user): Response
    {
        $call = new CallModel();
        $call->setPath('/_security/user/'.$user);
        $user = $this->callManager->call($call);

        if (true == isset($user[key($user)])) {
            $userNice = $user[key($user)];
            $userNice['user'] = key($user);
            $user = $userNice;

            if (true == isset($user['metadata']) && true == isset($user['metadata']['_reserved']) && true == $user['metadata']['_reserved']) {
                throw new AccessDeniedHttpException();
            }

            $call = new CallModel();
            $call->setMethod('DELETE');
            $call->setPath('/_security/user/'.$user['username']);
            $this->callManager->call($call);

            $this->addFlash('success', 'success.users_delete');

            return $this->redirectToRoute('users', []);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
