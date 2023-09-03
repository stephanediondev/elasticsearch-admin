<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchRoleType;
use App\Form\Type\ElasticsearchUserFilterType;
use App\Manager\ElasticsearchRoleManager;
use App\Manager\ElasticsearchUserManager;
use App\Model\ElasticsearchRoleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchRoleController extends AbstractAppController
{
    private ElasticsearchRoleManager $elasticsearchRoleManager;

    private ElasticsearchUserManager $elasticsearchUserManager;

    public function __construct(ElasticsearchRoleManager $elasticsearchRoleManager, ElasticsearchUserManager $elasticsearchUserManager)
    {
        $this->elasticsearchRoleManager = $elasticsearchRoleManager;
        $this->elasticsearchUserManager = $elasticsearchUserManager;
    }

    #[Route('/elasticsearch-roles', name: 'elasticsearch_roles')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLES', 'global');

        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ElasticsearchUserFilterType::class, null, ['context' => 'role']);

        $form->handleRequest($request);

        $roles = $this->elasticsearchRoleManager->getAll([
            'reserved' => $form->has('reserved') ? $form->get('reserved')->getData() : false,
            'deprecated' => $form->has('deprecated') ? $form->get('deprecated')->getData() : false,
        ]);

        return $this->renderAbstract($request, 'Modules/role/role_index.html.twig', [
            'roles' => $this->paginatorManager->paginate([
                'route' => 'elasticsearch_roles',
                'route_parameters' => [],
                'total' => count($roles),
                'rows' => $roles,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/elasticsearch-roles/create', name: 'elasticsearch_roles_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLES_CREATE', 'global');

        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $role = null;

        if ($request->query->get('role')) {
            $role = $this->elasticsearchRoleManager->getByName($request->query->getString('role'));

            if (null === $role) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLE_COPY', $role);

            $role->setName($role->getName().'-copy');
        }

        if (null === $role) {
            $role = new ElasticsearchRoleModel();
        }
        $form = $this->createForm(ElasticsearchRoleType::class, $role, ['privileges' => $this->elasticsearchRoleManager->getPrivileges(), 'users' => $this->elasticsearchUserManager->selectUsers()]);

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

    #[Route('/elasticsearch-roles/{role}', name: 'elasticsearch_roles_read')]
    public function read(Request $request, string $role): Response
    {
        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLES', 'global');

        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $role = $this->elasticsearchRoleManager->getByName($role);

        if (null === $role) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/role/role_read.html.twig', [
            'role' => $role,
        ]);
    }

    #[Route('/elasticsearch-roles/{role}/update', name: 'elasticsearch_roles_update')]
    public function update(Request $request, string $role): Response
    {
        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $role = $this->elasticsearchRoleManager->getByName($role);

        if (null === $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLE_UPDATE', $role);

        $form = $this->createForm(ElasticsearchRoleType::class, $role, ['privileges' => $this->elasticsearchRoleManager->getPrivileges(), 'users' => $this->elasticsearchUserManager->selectUsers(), 'context' => 'update']);

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

    #[Route('/elasticsearch-roles/{role}/delete', name: 'elasticsearch_roles_delete')]
    public function delete(Request $request, string $role): Response
    {
        if (false === $this->callManager->hasFeature('security')) {
            throw new AccessDeniedException();
        }

        $role = $this->elasticsearchRoleManager->getByName($role);

        if (null === $role) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ELASTICSEARCH_ROLE_DELETE', $role);

        $callResponse = $this->elasticsearchRoleManager->deleteByName($role->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('elasticsearch_roles');
    }
}
