<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\AppUserType;
use App\Manager\AppManager;
use App\Manager\CallManager;
use App\Manager\AppUserManager;
use App\Model\CallRequestModel;
use App\Model\AppUserModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class AppSecurityController extends AbstractAppController
{
    public function __construct(AppManager $appManager, AppUserManager $appUserManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->appManager = $appManager;
        $this->appUserManager = $appUserManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('cluster');
        }

        $parameters = [];
        $parameters['no_calls'] = true;

        try {
            $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();
        } catch (CallException $e) {
            throw new ServiceUnavailableHttpException(null, $e->getMessage());
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('HEAD');
        $callRequest->setPath('/.elasticsearch-admin-users');
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            return $this->redirectToRoute('register');
        }

        if ($error = $authenticationUtils->getLastAuthenticationError()) {
            $this->addFlash('danger', $error->getMessageKey());
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        $parameters['last_username'] = $lastUsername;

        return $this->renderAbstract($request, 'Modules/security/login.html.twig', $parameters);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('app_login'));
    }

    /**
     * @Route("/register", name="register")
     */
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
            throw new AccessDeniedHttpException();
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
                if (true == $this->callManager->checkVersion('7.0')) {
                    $json['mappings'] = $this->appManager->getMappings('.elasticsearch-admin-users');
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setJson($json);
                $callRequest->setPath('/.elasticsearch-admin-users-v'.$this->appManager->getVersion());
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                $user->setPassword($this->passwordEncoder->encodePassword($user, $user->getPasswordPlain()));
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
