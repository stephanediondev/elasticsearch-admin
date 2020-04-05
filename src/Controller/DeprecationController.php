<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class DeprecationController extends AbstractAppController
{
    /**
     * @Route("/deprecations", name="deprecations")
     */
    public function index(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_migration/deprecations');
        $deprecations = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/deprecation/deprecation_index.html.twig', [
            'deprecations' => $deprecations,
        ]);
    }
}
