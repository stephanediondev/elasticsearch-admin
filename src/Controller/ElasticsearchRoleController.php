<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateElasticsearchRoleType;
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
    public function __construct(ElasticsearchRoleManager $elasticsearchRoleManager, ElasticsearchUserManager $elasticsearchUserManager)
    {
        $this->elasticsearchRoleManager = $elasticsearchRoleManager;
        $this->elasticsearchUserManager = $elasticsearchUserManager;
    }

    /**
     * @Route("/elasticsearch-roles", name="elasticsearch_roles")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLES', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $roles = $this->elasticsearchRoleManager->getAll();

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
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLES_CREATE', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $role = false;

        if ($request->query->get('role')) {
            $role = $this->elasticsearchRoleManager->getByName($request->query->get('role'));

            if (false == $role) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLE_COPY', $role);

            $role->setName($role->getName().'-copy');
        }

        if (false == $role) {
            $role = new ElasticsearchRoleModel();
        }
        $form = $this->createForm(CreateElasticsearchRoleType::class, $role, ['privileges' => $this->elasticsearchRoleManager->getPrivileges(), 'users' => $this->elasticsearchUserManager->selectUsers()]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchRoleManager->send($role);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('elasticsearch_roles_read', ['role' => $role->getName()]);
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
        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLES', 'global');

        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $role = $this->elasticsearchRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/role/role_read.html.twig', [
            'role' => $role,
        ]);
    }

    /**
     * @Route("/elasticsearch-roles/{role}/update", name="elasticsearch_roles_update")
     */
    public function update(Request $request, string $role): Response
    {
        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $role = $this->elasticsearchRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLE_UPDATE', $role);

        $form = $this->createForm(CreateElasticsearchRoleType::class, $role, ['privileges' => $this->elasticsearchRoleManager->getPrivileges(), 'users' => $this->elasticsearchUserManager->selectUsers(), 'context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchRoleManager->send($role);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('elasticsearch_roles_read', ['role' => $role->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/role/role_update.html.twig', [
            'role' => $role,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/elasticsearch-roles/{role}/delete", name="elasticsearch_roles_delete")
     */
    public function delete(Request $request, string $role): Response
    {
        if (false == $this->hasFeature('security')) {
            throw new AccessDeniedHttpException();
        }

        $role = $this->elasticsearchRoleManager->getByName($role);

        if (false == $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLE_DELETE', $role);

        $callResponse = $this->elasticsearchRoleManager->deleteByName($role->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('elasticsearch_roles');
    }
}
