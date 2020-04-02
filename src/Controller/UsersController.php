<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
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
}
