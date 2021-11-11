<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Manager\AppManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/admin")
 */
class AppUninstallController extends AbstractAppController
{
    private AppManager $appManager;

    private TokenStorageInterface $tokenStorage;

    public function __construct(AppManager $appManager, TokenStorageInterface $tokenStorage)
    {
        $this->appManager = $appManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/app-uninstall", name="app_uninstall")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_UNINSTALL', 'global');

        return $this->renderAbstract($request, 'Modules/app_uninstall/app_uninstall_index.html.twig', [
            'indices' => $this->appManager->getIndices(),
        ]);
    }

    /**
     * @Route("/app-uninstall/confirm", name="app_uninstall_confirm")
     */
    public function confirm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_UNINSTALL', 'global');

        foreach ($this->appManager->getIndices() as $index) {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('GET');
            $callRequest->setPath('/'.$index);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $getIndex = $callResponse->getContent();

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('DELETE');
                $callRequest->setPath('/'.array_key_first($getIndex));
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));
            }
        }

        $this->tokenStorage->setToken(null);
        $request->getSession()->invalidate();

        return $this->redirectToRoute('register');
    }
}
