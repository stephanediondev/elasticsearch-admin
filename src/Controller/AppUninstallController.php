<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class AppUninstallController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

    /**
     * @Route("/app-uninstall", name="app_uninstall")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_UNINSTALL', 'global');

        return $this->renderAbstract($request, 'Modules/app_uninstall/app_uninstall_index.html.twig', [
        ]);
    }

    /**
     * @Route("/app-uninstall/confirm", name="app_uninstall_confirm")
     */
    public function confirm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_UNINSTALL', 'global');

        $indices = [
            '.elastictsearch-admin-users',
            '.elastictsearch-admin-roles',
            '.elastictsearch-admin-permissions',
        ];

        foreach ($indices as $index) {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('HEAD');
            $callRequest->setPath($index);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_OK == $callResponse->getCode()) {
                $callResponse = $this->elasticsearchIndexManager->deleteByName($index);
                $this->addFlash('info', json_encode($callResponse->getContent()));
            }
        }

        sleep(2);

        return $this->redirectToRoute('register');
    }
}
