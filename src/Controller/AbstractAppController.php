<?php

namespace App\Controller;

use App\Manager\CallManager;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\PaginatorManager;
use App\Model\CallRequestModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function renderAbstract(Request $request, string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();

        $parameters['master_node'] = $this->callManager->getMasterNode();

        $parameters['root'] = $this->callManager->getRoot();

        $parameters['xpack'] = $this->callManager->getXpack();

        $parameters['plugins'] = $this->callManager->getPlugins();

        $parameters['cat_sort'] = $this->callManager->checkVersion('5.1.1');

        $menus = [];

        if (true == $this->isGranted('CONFIGURATION', 'global')) {
            $entries = [
                ['granted' => 'INDEX_TEMPLATES_LEGACY', 'path' => 'index_templates_legacy'],
                ['granted' => 'INDEX_TEMPLATES', 'path' => 'index_templates'],
                ['granted' => 'COMPONENT_TEMPLATES', 'path' => 'component_templates'],
                ['granted' => 'ILM_POLICIES', 'path' => 'ilm'],
                ['granted' => 'SLM_POLICIES', 'path' => 'slm'],
                ['granted' => 'REPOSITORIES', 'path' => 'repositories'],
                ['granted' => 'ENRICH_POLICIES', 'path' => 'enrich'],
                ['granted' => 'ELASTICSEARCH_USERS', 'path' => 'elasticsearch_users'],
                ['granted' => 'ELASTICSEARCH_ROLES', 'path' => 'elasticsearch_roles'],
                ['granted' => 'APP_USERS', 'path' => 'app_users'],
                ['granted' => 'APP_ROLES', 'path' => 'app_roles'],
            ];

            $menus['configuration'] = $this->populateMenu($entries);
        }

        if (true == $this->isGranted('TOOLS', 'global')) {
            $entries = [
                ['granted' => 'SNAPSHOTS', 'path' => 'snapshots'],
                ['granted' => 'PIPELINES', 'path' => 'pipelines'],
                ['granted' => 'TASKS', 'path' => 'tasks'],
                ['granted' => 'REMOTE_CLUSTERS', 'path' => 'remote_clusters'],
                ['granted' => 'CAT', 'path' => 'cat'],
                ['granted' => 'CONSOLE', 'path' => 'console'],
                ['granted' => 'DEPRECATIONS', 'path' => 'deprecations'],
                ['granted' => 'LICENSE', 'path' => 'license'],
            ];

            $menus['tools'] = $this->populateMenu($entries);
        }

        $parameters['menus'] = $menus;

        return $this->render($view, $parameters, $response);
    }

    private function populateMenu($entries)
    {
        $menu = [];
        foreach ($entries as $entry) {
            if (true == $this->isGranted($entry['granted'], 'global')) {
                $disabled = false;

                if (true == in_array($entry['granted'], ['INDEX_TEMPLATES', 'COMPONENT_TEMPLATES']) && false == $this->callManager->checkVersion('7.8')) {
                    $disabled = true;
                }

                if ('ILM_POLICIES' == $entry['granted'] && false == $this->callManager->hasFeature('ilm')) {
                    $disabled = true;
                }

                if ('SLM_POLICIES' == $entry['granted'] && false == $this->callManager->hasFeature('slm')) {
                    $disabled = true;
                }

                if ('ENRICH_POLICIES' == $entry['granted'] && false == $this->callManager->hasFeature('enrich')) {
                    $disabled = true;
                }

                if (true == in_array($entry['granted'], ['ELASTICSEARCH_USERS', 'ELASTICSEARCH_ROLES']) && false == $this->callManager->hasFeature('security')) {
                    $disabled = true;
                }

                if (true == in_array($entry['granted'], ['LICENSE', 'PIPELINES']) && false == $this->callManager->checkVersion('6.0')) {
                    $disabled = true;
                }

                if (true == in_array($entry['granted'], ['REMOTE_CLUSTERS']) && false == $this->callManager->checkVersion('5.4.0')) {
                    $disabled = true;
                }

                $menu[] = [
                    'path' => $entry['path'],
                    'name' => $this->translator->trans($entry['path']),
                    'disabled' => $disabled,
                ];
            }
        }
        usort($menu, [$this, 'sortByName']);
        return $menu;
    }

    private function sortByName($a, $b)
    {
        return $b['name'] < $a['name'];
    }
}
