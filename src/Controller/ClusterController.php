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
    public function read(Request $request): Response
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

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read.html.twig', [
            'master_node' => $nodes[$clusterState['master_node']] ?? false,
            'indices' => $clusterStats['indices']['count'] ?? false,
            'shards' => $clusterStats['indices']['shards']['total'] ?? false,
            'documents' => $clusterStats['indices']['docs']['count'] ?? false,
            'store_size' => $clusterStats['indices']['store']['size_in_bytes'] ?? false,
        ]);
    }

    /**
     * @Route("/cluster/settings", name="cluster_settings")
     */
    public function settings(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_cluster/settings');
        $call->setQuery(['include_defaults' => 'true', 'flat_settings' => 'true']);
        $clusterSettings = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read_settings.html.twig', [
            'cluster_settings' => $clusterSettings,
        ]);
    }
}
