<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ElasticsearchDeprecationController extends AbstractAppController
{
    /**
     * @Route("/deprecations", name="deprecations")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('DEPRECATIONS', 'global');

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_xpack/migration/deprecations');
        $callResponse = $this->callManager->call($callRequest);
        $deprecations = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/deprecation/deprecation_index.html.twig', [
            'deprecations' => $deprecations,
        ]);
    }
}
