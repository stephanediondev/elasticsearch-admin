<?php

namespace App\Controller;

use App\Manager\CallManager;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\PaginatorManager;
use App\Model\CallRequestModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAppController extends AbstractController
{
    /**
     * @required
     */
    public function setCallManager(CallManager $callManager)
    {
        $this->callManager = $callManager;
    }

    /**
     * @required
     */
    public function setClusterManager(ElasticsearchClusterManager $elasticsearchClusterManager)
    {
        $this->elasticsearchClusterManager = $elasticsearchClusterManager;
    }

    /**
     * @required
     */
    public function setPaginatorManager(PaginatorManager $paginatorManager)
    {
        $this->paginatorManager = $paginatorManager;
    }

    public function renderAbstract(Request $request, string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();

        $parameters['master_node'] = $this->callManager->getMasterNode();

        $parameters['root'] = $this->callManager->getRoot();

        $parameters['xpack'] = $this->callManager->getXpack();

        $parameters['plugins'] = $this->callManager->getPlugins();

        return $this->render($view, $parameters, $response);
    }
}
