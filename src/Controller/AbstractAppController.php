<?php

namespace App\Controller;

use App\Manager\CallManager;
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

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/');
        $callResponse = $this->callManager->call($callRequest);
        $this->root = $callResponse->getContent();

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_xpack');
        $callResponse = $this->callManager->call($callRequest);
        $this->xpack = $callResponse->getContent();

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/plugins');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $this->plugins = [];
        foreach ($results as $row) {
            $this->plugins[] = $row['component'];
        }
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/health');
        $callResponse = $this->callManager->call($callRequest);
        $clusterHealth = $callResponse->getContent();

        $parameters['cluster_health'] = $clusterHealth;

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/master');
        $callResponse = $this->callManager->call($callRequest);
        $master = $callResponse->getContent();

        $parameters['master_node'] = $master[0]['node'] ?? false;

        $parameters['root'] = $this->root;

        $parameters['xpack'] = $this->xpack;

        $parameters['plugins'] = $this->plugins;

        return $this->render($view, $parameters, $response);
    }

    protected function checkVersion(string $versionGoal): bool
    {
        if (true == isset($this->root['version']) && true == isset($this->root['version']['number']) && 0 <= version_compare($this->root['version']['number'], $versionGoal)) {
            return true;
        }

        return false;
    }

    protected function hasFeature(string $feature): bool
    {
        if (true == isset($this->xpack['features'][$feature]) && true == $this->xpack['features'][$feature]['available'] && true == $this->xpack['features'][$feature]['enabled']) {
            return true;
        }

        return false;
    }

    protected function hasPlugin(string $plugin): bool
    {
        if (true == in_array($plugin, $this->plugins)) {
            return true;
        }

        return false;
    }
}
