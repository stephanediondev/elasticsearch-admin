<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\AppUserType;
use App\Manager\AppUserManager;
use App\Manager\AppRoleManager;
use App\Model\AppUserModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @Route("/admin")
 */
class AppUserController extends AbstractAppController
{
    private AppRoleManager $appRoleManager;

    private AppUserManager $appUserManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(AppRoleManager $appRoleManager, AppUserManager $appUserManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->appRoleManager = $appRoleManager;
        $this->appUserManager = $appUserManager;
        $this->passwordHasher = $passwordHasher;
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
                'route' => 'app_users',
                'route_parameters' => [],
                'total' => count($users),
                'rows' => $users,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
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
        $form = $this->createForm(AppUserType::class, $user, ['roles' => $roles]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPasswordPlain()));

            try {
                $callResponse = $this->appUserManager->send($user);
                $content = $callResponse->getContent();

                $this->addFlash('info', json_encode($content));

                return $this->redirectToRoute('app_users_read', ['user' => $content['_id']]);
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

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $roles = $user->getRoles();

        if (true === in_array('ROLE_ADMIN', $roles)) {
            $permissionsSaved = $this->appRoleManager->getAttributes();
        } else {
            $permissionsSaved = [];
            foreach ($roles as $role) {
                if (false === in_array($role, ['ROLE_ADMIN', 'ROLE_USER'])) {
                    $permissionsByRole = $this->appRoleManager->getPermissionsByRole($role);
                    foreach ($permissionsByRole as $module => $permissions) {
                        if (false === isset($permissionsSaved[$module])) {
                            $permissionsSaved[$module] = [];
                        }
                        $permissionsSaved[$module] = array_merge($permissionsSaved[$module], $permissions);
                    }
                }
            }

            ksort($permissionsSaved);
            foreach ($permissionsSaved as $module => $permissions) {
                sort($permissions);
                $permissionsSaved[$module] = $permissions;
            }
        }

        return $this->renderAbstract($request, 'Modules/app_user/app_user_read.html.twig', [
            'user' => $user,
            'permissions_saved' => $permissionsSaved,
        ]);
    }

    /**
     * @Route("/app-users/{user}/update", name="app_users_update")
     */
    public function update(Request $request, string $user): Response
    {
        $user = $this->appUserManager->getById($user);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_USER_UPDATE', $user);

        $roles = $this->appRoleManager->selectRoles();

        $form = $this->createForm(AppUserType::class, $user, [
            'roles' => $roles,
            'old_email' => $user->getEmail(),
            'current_user_admin' => $user->currentUserAdmin($this->getuser()),
            'context' => 'update',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($user->getChangePassword() && $user->getPasswordPlain()) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPasswordPlain()));
                }

                $callResponse = $this->appUserManager->send($user);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_users_read', ['user' => $user->getId()]);
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

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('APP_USER_DELETE', $user);

        $callResponse = $this->appUserManager->deleteById($user->getId());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('app_users');
    }

    /**
     * @Route("/profile", name="app_users_profile")
     */
    public function profile(Request $request): Response
    {
        $user = $this->appUserManager->getByEmail($this->getuser()->getUserIdentifier());

        $form = $this->createForm(AppUserType::class, $user, [
            'old_email' => $user->getEmail(),
            'current_user_admin' => $user->currentUserAdmin($this->getuser()),
            'context' => 'profile',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($user->getChangePassword() && $user->getPasswordPlain()) {
                    $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPasswordPlain()));
                }

                $callResponse = $this->appUserManager->send($user);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_users_profile');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/app_user/app_user_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
