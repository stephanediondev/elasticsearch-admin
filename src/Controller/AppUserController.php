<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateAppUserType;
use App\Manager\AppUserManager;
use App\Manager\AppRoleManager;
use App\Model\CallRequestModel;
use App\Model\AppUserModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
class AppUserController extends AbstractAppController
{
    public function __construct(AppRoleManager $appRoleManager, AppUserManager $appUserManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->appRoleManager = $appRoleManager;
        $this->appUserManager = $appUserManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/app-users", name="app_users")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_USERS', 'global');

        $users = $this->appUserManager->getAll();

        return $this->renderAbstract($request, 'Modules/app_user/app_user_index.html.twig', [
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
     * @Route("/app-users/create", name="app_users_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_USERS_CREATE', 'global');

        $roles = $this->appRoleManager->selectRoles();

        $user = new AppUserModel();
        $form = $this->createForm(CreateAppUserType::class, $user, ['roles' => $roles]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPasswordPlain()));

            try {
                $callResponse = $this->appUserManager->send($user);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_users');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/app_user/app_user_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/app-users/{user}", name="app_users_read")
     */
    public function read(Request $request, string $user): Response
    {
        $this->denyAccessUnlessGranted('APP_USERS', 'global');

        $user = $this->appUserManager->getById($user);

        if (false == $user) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/app_user/app_user_read.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/app-users/{user}/update", name="app_users_update")
     */
    public function update(Request $request, string $user): Response
    {
        $user = $this->appUserManager->getById($user);

        if (false == $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_USER_UPDATE', $user);

        $roles = $this->appRoleManager->selectRoles();

        $form = $this->createForm(CreateAppUserType::class, $user, ['roles' => $roles, 'context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->appUserManager->send($user);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_users');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/app_user/app_user_update.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/app-users/{user}/delete", name="app_users_delete")
     */
    public function delete(Request $request, string $user): Response
    {
        $user = $this->appUserManager->getById($user);

        if (false == $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_USER_DELETE', $user);

        $callResponse = $this->appUserManager->deleteById($user->getId());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('app_users');
    }
}
