<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchRemoteClusterController extends AbstractAppController
{
    private ElasticsearchNodeManager $elasticsearchNodeManager;

    public function __construct(ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    #[Route('/remote-clusters', name: 'remote_clusters', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('REMOTE_CLUSTERS', 'global');

        if (false === $this->callManager->hasFeature('remote_clusters')) {
            throw new AccessDeniedException();
        }

        $remoteClusters = [];

        $masterNode = $this->callManager->getMasterNode();

        $node = $this->elasticsearchNodeManager->getByName($masterNode);

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_remote/info');
            $callResponse = $this->callManager->call($callRequest);
            $remoteClusters = $callResponse->getContent();
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());

            if (true === $this->callManager->hasFeature('role_remote_cluster_client') && false === $node->hasRole('remote_cluster_client')) {
                throw new AccessDeniedException();
            }
        }

        return $this->renderAbstract($request, 'Modules/remote_cluster/remote_cluster_index.html.twig', [
            'remoteClusters' => $this->paginatorManager->paginate([
                'route' => 'remote_clusters',
                'route_parameters' => [],
                'total' => count($remoteClusters),
                'rows' => $remoteClusters,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
        ]);
    }
}
