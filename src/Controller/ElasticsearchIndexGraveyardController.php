<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\ElasticsearchShardManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchIndexGraveyardController extends AbstractAppController
{
    /**
     * @Route("/index-graveyard", name="index_graveyard")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDEX_GRAVEYARD', 'global');

        if (false == $this->callManager->hasFeature('tombstones')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setQuery(['filter_path' => '**.tombstones']);
        $callRequest->setPath('/_cluster/state');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if (true == isset($results['metadata']['index-graveyard']['tombstones'])) {
            $tombstones = $results['metadata']['index-graveyard']['tombstones'];
        } else {
            $tombstones = [];
        }

        return $this->renderAbstract($request, 'Modules/index_graveyard/index_graveyard_index.html.twig', [
            'tombstones' => $this->paginatorManager->paginate([
                'route' => 'tombstones',
                'route_parameters' => [],
                'total' => count($tombstones),
                'rows' => $tombstones,
                'page' => 1,
                'size' => count($tombstones),
            ]),
        ]);
    }
}
