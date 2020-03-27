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
class ClusterController extends AbstractAppController
{
    /**
     * @Route("/cluster", name="cluster")
     */
    public function index(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_cluster/stats');
        $clusterStats = $this->callManager->call($call);

        $call = new CallModel();
        $call->setPath('/_cluster/state');
        $clusterState = $this->callManager->call($call);

        $nodes = [];
        foreach ($clusterState['nodes'] as $k => $node) {
            $nodes[$k] = $node['name'];
        }

        return $this->renderAbstract($request, 'Modules/home/home_index.html.twig', [
            'master_node' => $nodes[$clusterState['master_node']] ?? false,
            'indices' => $clusterStats['indices']['count'] ?? false,
            'shards' => $clusterStats['indices']['shards']['total'] ?? false,
            'documents' => $clusterStats['indices']['docs']['count'] ?? false,
            'store_size' => $clusterStats['indices']['store']['size_in_bytes'] ?? false,
        ]);
    }
}
