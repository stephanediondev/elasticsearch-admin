<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\AppUserType;
use App\Manager\AppManager;
use App\Manager\AppUserManager;
use App\Model\AppUserModel;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AppSecurityController extends AbstractAppController
{
    private AppManager $appManager;

    private AppUserManager $appUserManager;

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(AppManager $appManager, AppUserManager $appUserManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->appManager = $appManager;
        $this->appUserManager = $appUserManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('cluster');
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('HEAD');
        $callRequest->setPath('/.elasticsearch-admin-users');
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            return $this->redirectToRoute('register');
        }

        if ($error = $authenticationUtils->getLastAuthenticationError()) {
            $this->addFlash('danger', strtr($error->getMessageKey(), $error->getMessageData()));
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->renderAbstract($request, 'Modules/security/login.html.twig', [
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('app_login'));
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('cluster');
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('HEAD');
        $callRequest->setPath('/.elasticsearch-admin-users');
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_OK == $callResponse->getCode()) {
            throw new AccessDeniedException();
        }

        $user = new AppUserModel();
        $form = $this->createForm(AppUserType::class, $user, ['context' => 'register']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'settings' => $this->appManager->getSettings('.elasticsearch-admin-users'),
                    'aliases' => ['.elasticsearch-admin-users' => (object)[]],
                ];
                if (true === $this->callManager->checkVersion('7.0')) {
                    $json['mappings'] = $this->appManager->getMappings('.elasticsearch-admin-users');
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setJson($json);
                $callRequest->setPath('/.elasticsearch-admin-users-v'.$this->appManager->getVersion());
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPasswordPlain()));
                $user->setRoles(['ROLE_ADMIN']);

                $callResponse = $this->appUserManager->send($user);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('app_login');
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
