<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        if (false === $this->callManager->hasFeature('deprecations')) {
            throw new AccessDeniedException();
        }

        $callRequest = new CallRequestModel();
        if (false === $this->callManager->hasFeature('_xpack_endpoint_removed')) {
            $callRequest->setPath('/_xpack/migration/deprecations');
        } else {
            $callRequest->setPath('/_migration/deprecations');
        }
        $callResponse = $this->callManager->call($callRequest);
        $deprecations = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/deprecation/deprecation_index.html.twig', [
            'deprecations' => $deprecations,
        ]);
    }
}
