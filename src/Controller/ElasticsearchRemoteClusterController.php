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
class ElasticsearchRemoteClusterController extends AbstractAppController
{
    /**
     * @Route("/remote-clusters", name="remote_clusters")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('REMOTE_CLUSTERS', 'global');

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_remote/info');
        $callResponse = $this->callManager->call($callRequest);
        $remoteClusters = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/remote_cluster/remote_cluster_index.html.twig', [
            'remoteClusters' => $this->paginatorManager->paginate([
                'route' => 'remote_clusters',
                'route_parameters' => [],
                'total' => count($remoteClusters),
                'rows' => $remoteClusters,
                'page' => 1,
                'size' => count($remoteClusters),
            ]),
        ]);
    }
}
