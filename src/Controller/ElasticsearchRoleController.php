<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateRoleType;
use App\Manager\ElasticsearchRoleManager;
use App\Manager\ElasticsearchUserManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchRoleModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchRoleController extends AbstractAppController
{
    /**
     * @Route("/elasticsearch-roles", name="elasticsearch_roles")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLES', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $roles = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role');
        $callResponse = $this->callManager->call($callRequest);
        $roles1 = $callResponse->getContent();

        foreach ($roles1 as $k => $role) {
            $role['role'] = $k;
            $roles[$k] = $role;
        }
        ksort($roles);

        return $this->renderAbstract($request, 'Modules/role/role_index.html.twig', [
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
     * @Route("/elasticsearch-roles/create", name="elasticsearch_roles_create")
     */
    public function create(Request $request, ElasticsearchRoleManager $elasticsearchRoleManager, ElasticsearchUserManager $elasticsearchUserManager): Response
    {
        $this->denyAccessUnlessGranted('ROLES_CREATE', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $role = false;

        if ($request->query->get('role')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_security/role/'.$request->query->get('role'));
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                throw new NotFoundHttpException();
            }

            $role = $callResponse->getContent();
            $roleNice = $role[key($role)];
            $roleNice['name'] = key($role).'-copy';
            $roleNice['role'] = key($role);
            $role = $roleNice;
        }

        $roleModel = new ElasticsearchRoleModel();
        if ($role) {
            $roleModel->convert($role);
        }
        $form = $this->createForm(CreateRoleType::class, $roleModel, ['privileges' => $elasticsearchRoleManager->getPrivileges(), 'users' => $elasticsearchUserManager->selectUsers()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $roleModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_security/role/'.$roleModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('elasticsearch_roles_read', ['role' => $roleModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/role/role_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/elasticsearch-roles/{role}", name="elasticsearch_roles_read")
     */
    public function read(Request $request, string $role): Response
    {
        $this->denyAccessUnlessGranted('ROLES', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role/'.$role);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $role = $callResponse->getContent();
        $roleNice = $role[key($role)];
        $roleNice['role'] = key($role);

        return $this->renderAbstract($request, 'Modules/role/role_read.html.twig', [
            'role' => $roleNice,
        ]);
    }

    /**
     * @Route("/elasticsearch-roles/{role}/update", name="elasticsearch_roles_update")
     */
    public function update(Request $request, string $role, ElasticsearchRoleManager $elasticsearchRoleManager, ElasticsearchUserManager $elasticsearchUserManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_UPDATE', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role/'.$role);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $role = $callResponse->getContent();
        $roleNice = $role[key($role)];
        $roleNice['name'] = key($role);
        $roleNice['role'] = key($role);
        $role = $roleNice;

        if (true == isset($role['metadata']) && true == isset($role['metadata']['_reserved']) && true == $role['metadata']['_reserved']) {
            throw new AccessDeniedHttpException();
        }

        $roleModel = new ElasticsearchRoleModel();
        $roleModel->convert($role);
        $form = $this->createForm(CreateRoleType::class, $roleModel, ['privileges' => $elasticsearchRoleManager->getPrivileges(), 'users' => $elasticsearchUserManager->selectUsers(), 'update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $roleModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_security/role/'.$roleModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('elasticsearch_roles_read', ['role' => $roleModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/role/role_update.html.twig', [
            'role' => $roleNice,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/elasticsearch-roles/{role}/delete", name="elasticsearch_roles_delete")
     */
    public function delete(Request $request, string $role): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DELETE', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_security/role/'.$role);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $role = $callResponse->getContent();
        $roleNice = $role[key($role)];
        $roleNice['role'] = key($role);
        $role = $roleNice;

        if (true == isset($role['metadata']) && true == isset($role['metadata']['_reserved']) && true == $role['metadata']['_reserved']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_security/role/'.$role['role']);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('elasticsearch_roles');
    }
}
