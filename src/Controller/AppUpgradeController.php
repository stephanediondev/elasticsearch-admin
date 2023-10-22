<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\AppManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchReindexModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AppUpgradeController extends AbstractAppController
{
    private AppManager $appManager;

    public function __construct(AppManager $appManager)
    {
        $this->appManager = $appManager;
    }

    #[Route('/app-upgrade', name: 'app_upgrade', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_UPGRADE', 'global');

        $upgrade = false;
        foreach ($this->appManager->getIndices() as $index) {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('HEAD');
            $callRequest->setPath($index.'-v'.$this->appManager->getVersion());
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                $upgrade = true;
            }
        }

        return $this->renderAbstract($request, 'Modules/app_upgrade/app_upgrade_index.html.twig', [
            'upgrade' => $upgrade,
        ]);
    }

    #[Route('/app-upgrade/confirm', name: 'app_upgrade_confirm', methods: ['GET'])]
    public function confirm(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_UPGRADE', 'global');

        foreach ($this->appManager->getIndices() as $index) {
            //test new version exists
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('HEAD');
            $callRequest->setPath($index.'-v'.$this->appManager->getVersion());
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                //add index new version
                $json = [
                    'settings' => $this->appManager->getSettings($index),
                ];
                if (true === $this->callManager->checkVersion('7.0')) {
                    $json['mappings'] = $this->appManager->getMappings($index);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setJson($json);
                $callRequest->setPath($index.'-v'.$this->appManager->getVersion());
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                //get current index
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('GET');
                $callRequest->setPath($index);
                $callResponse = $this->callManager->call($callRequest);

                if (Response::HTTP_OK == $callResponse->getCode()) {
                    $getIndex = $callResponse->getContent();

                    //reindex
                    $reindexModel = new ElasticsearchReindexModel();
                    $reindexModel->setSource($index);
                    $reindexModel->setDestination($index.'-v'.$this->appManager->getVersion());

                    $json = $reindexModel->getJson();
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('POST');
                    $callRequest->setPath('/_reindex');
                    $callRequest->setJson($json);
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));

                    //delete current index
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('DELETE');
                    $callRequest->setPath('/'.array_key_first($getIndex));
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));
                }

                //add alias
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$index.'-v'.$this->appManager->getVersion().'/_alias/'.$index);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));
            }
        }

        return $this->redirectToRoute('app_upgrade');
    }
}
