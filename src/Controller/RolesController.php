<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class RolesController extends AbstractAppController
{
    /**
     * @Route("/roles", name="roles")
     */
    public function index(Request $request): Response
    {
        $roles = [];

        $call = new CallModel();
        $call->setPath('/_security/role');
        $roles1 = $this->callManager->call($call);

        foreach ($roles1 as $k => $role) {
            $role['role'] = $k;
            $roles[$k] = $role;
        }
        ksort($roles);

        return $this->renderAbstract($request, 'Modules/roles/roles_index.html.twig', [
            'roles' => $this->paginatorManager->paginate([
                'route' => 'roles',
                'route_parameters' => [],
                'total' => count($roles),
                'rows' => $roles,
                'page' => 1,
                'size' => count($roles),
            ]),
        ]);
    }

    /**
     * @Route("/roles/{role}", name="roles_read")
     */
    public function read(Request $request, string $role): Response
    {
        $call = new CallModel();
        $call->setPath('/_security/role/'.$role);
        $role = $this->callManager->call($call);

        if (true == isset($role[key($role)])) {
            $roleNice = $role[key($role)];
            $roleNice['role'] = key($role);
            return $this->renderAbstract($request, 'Modules/roles/roles_read.html.twig', [
                'role' => $roleNice,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }
}
