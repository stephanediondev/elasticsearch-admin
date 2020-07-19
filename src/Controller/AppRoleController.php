<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\AppRoleType;
use App\Manager\AppRoleManager;
use App\Model\AppRoleModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class AppRoleController extends AbstractAppController
{
    public function __construct(AppRoleManager $appRoleManager)
    {
        $this->appRoleManager = $appRoleManager;
    }

    /**
     * @Route("/app-roles", name="app_roles")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_ROLES', 'global');

        $roles = $this->appRoleManager->getAll();

        return $this->renderAbstract($request, 'Modules/app_role/app_role_index.html.twig', [
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
     * @Route("/app-roles/create", name="app_roles_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_ROLES_CREATE', 'global');

        $role = false;

        if ($request->query->get('role')) {
            $role = $this->appRoleManager->getByName($request->query->get('role'));

            if (false == $role) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('APP_ROLE_COPY', $role);

            $role->setName($role->getName().'-copy');
        }

        if (false == $role) {
            $role = new AppRoleModel();
        }
        $form = $this->createForm(AppRoleType::class, $role);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->appRoleManager->send($role);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                sleep(2);

                return $this->redirectToRoute('app_roles_read', ['role' => $role->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/app_role/app_role_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/app-roles/{role}", name="app_roles_read")
     */
    public function read(Request $request, string $role): Response
    {
        $this->denyAccessUnlessGranted('APP_ROLES', 'global');

        $role = $this->appRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/app_role/app_role_read.html.twig', [
            'role' => $role,
            'permissions_saved' => $this->appRoleManager->getPermissionsByRole($role->getName()),
        ]);
    }

    /**
     * @Route("/app-roles/{role}/update", name="app_roles_update")
     */
    public function update(Request $request, string $role): Response
    {
        $role = $this->appRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_ROLE_UPDATE', $role);

        return $this->renderAbstract($request, 'Modules/app_role/app_role_update.html.twig', [
            'role' => $role,
            'modules' => $this->appRoleManager->getAttributes(),
            'permissions_saved' => $this->appRoleManager->getPermissionsByRole($role->getName()),
        ]);
    }

    /**
     * @Route("/app-roles/{role}/module/{module}/permission/{permission}", name="app_roles_permission")
     */
    public function permission(Request $request, string $role, string $module, string $permission): JsonResponse
    {
        $role = $this->appRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_ROLE_UPDATE', $role);

        $json = [];
        $responseStatus = JsonResponse::HTTP_OK;

        $content = $request->getContent();
        $content = json_decode($content, true);

        $value = $content['value'] ?? false;

        if (in_array($value, ['yes', 'no'])) {
            $callResponse = $this->appRoleManager->setPermission($role, $module, $permission, $value);

            return new JsonResponse($callResponse->getContent(), JsonResponse::HTTP_OK);
        } else {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @Route("/app-roles/{role}/delete", name="app_roles_delete")
     */
    public function delete(Request $request, string $role): Response
    {
        $role = $this->appRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_ROLE_DELETE', $role);

        $callResponse = $this->appRoleManager->deleteById($role->getId());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        $callResponse = $this->appRoleManager->deletePermissionsByRoleName($role->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        sleep(2);

        return $this->redirectToRoute('app_roles');
    }
}
