<?php

namespace App\Controller;

use App\Exception\ConnectionException;
use App\Manager\CallManager;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\PaginatorManager;
use App\Model\CallRequestModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

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
        if (false == isset($parameters['firewall'])) {
            $parameters['firewall'] = true;
        }

        $menus = [];

        if (true == $parameters['firewall'] && (false == isset($parameters['exception_503']) || false == $parameters['exception_503'])) {
            try {
                $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();
            } catch (ConnectionException $e) {
                throw new ServiceUnavailableHttpException(null, $e->getMessage());
            }

            $parameters['master_node'] = $this->callManager->getMasterNode();

            $parameters['root'] = $this->callManager->getRoot();

            $parameters['xpack'] = $this->callManager->getXpack();

            $parameters['plugins'] = $this->callManager->getPlugins();

            $parameters['cat_sort'] = $this->callManager->hasFeature('cat_sort');

            if (true == $this->isGranted('MENU_CONFIGURATION', 'global')) {
                $entries = [
                    ['granted' => 'INDEX_TEMPLATES_LEGACY', 'path' => 'index_templates_legacy'],
                    ['granted' => 'INDEX_TEMPLATES', 'path' => 'index_templates', 'feature' => 'composable_template'],
                    ['granted' => 'COMPONENT_TEMPLATES', 'path' => 'component_templates', 'feature' => 'composable_template'],
                    ['granted' => 'ILM_POLICIES', 'path' => 'ilm', 'feature' => 'ilm'],
                    ['granted' => 'SLM_POLICIES', 'path' => 'slm', 'feature' => 'slm'],
                    ['granted' => 'REPOSITORIES', 'path' => 'repositories'],
                    ['granted' => 'ENRICH_POLICIES', 'path' => 'enrich', 'feature' => 'enrich'],
                    ['granted' => 'ELASTICSEARCH_USERS', 'path' => 'elasticsearch_users', 'feature' => 'security'],
                    ['granted' => 'ELASTICSEARCH_ROLES', 'path' => 'elasticsearch_roles', 'feature' => 'security'],
                ];

                $menus['configuration'] = $this->populateMenu($entries);
            }

            if (true == $this->isGranted('MENU_TOOLS', 'global')) {
                $entries = [
                    ['granted' => 'SNAPSHOTS', 'path' => 'snapshots'],
                    ['granted' => 'PIPELINES', 'path' => 'pipelines', 'feature' => 'pipelines'],
                    ['granted' => 'TASKS', 'path' => 'tasks', 'feature' => 'tasks'],
                    ['granted' => 'REMOTE_CLUSTERS', 'path' => 'remote_clusters', 'feature' => 'remote_clusters'],
                    ['granted' => 'CAT', 'path' => 'cat'],
                    ['granted' => 'SQL', 'path' => 'sql', 'feature' => 'sql'],
                    ['granted' => 'CONSOLE', 'path' => 'console'],
                    ['granted' => 'DEPRECATIONS', 'path' => 'deprecations', 'feature' => 'deprecations'],
                    ['granted' => 'LICENSE', 'path' => 'license', 'feature' => 'license'],
                    ['granted' => 'INDEX_GRAVEYARD', 'path' => 'index_graveyard', 'feature' => 'tombstones'],
                ];

                $menus['tools'] = $this->populateMenu($entries);
            }

            if (true == $this->isGranted('MENU_APPLICATION', 'global')) {
                $entries = [
                    ['granted' => 'APP_USERS', 'path' => 'app_users'],
                    ['granted' => 'APP_ROLES', 'path' => 'app_roles'],
                    ['granted' => 'APP_UNINSTALL', 'path' => 'app_uninstall'],
                    ['granted' => 'APP_LOGS', 'path' => 'app_logs'],
                    ['granted' => 'APP_UPGRADE', 'path' => 'app_upgrade'],
                ];

                $menus['application'] = $this->populateMenu($entries);
            }
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

                if (true == isset($entry['feature']) && false == $this->callManager->hasFeature($entry['feature'])) {
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
