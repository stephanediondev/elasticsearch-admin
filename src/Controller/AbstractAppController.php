<?php

namespace App\Controller;

use App\Exception\ConnectionException;
use App\Manager\CallManager;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\PaginatorManager;
use App\Manager\AppThemeManager;
use App\Model\CallRequestModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

abstract class AbstractAppController extends AbstractController
{
    protected $callManager;

    protected $elasticsearchClusterManager;

    protected $appThemeManager;

    protected $paginatorManager;

    protected $translator;

    protected $themeDefault;

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
    public function setAppThemeManager(AppThemeManager $appThemeManager)
    {
        $this->appThemeManager = $appThemeManager;
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

    /**
     * @required
     */
    public function setThemeDefault(string $themeDefault)
    {
        $this->themeDefault = $themeDefault;
    }

    public function renderAbstract(Request $request, string $view, array $parameters = [], Response $response = null): Response
    {
        $twig = $this->container->get('twig');

        if ($request->query->get('theme') && true === in_array($request->query->get('theme'), $this->appThemeManager->predefinedThemes())) {
            $saved = $this->appThemeManager->getPredefined($request->query->get('theme'));
        } else {
            $saved = $request->cookies->get('theme') ? json_decode($request->cookies->get('theme'), true) : [];
        }
        $predefined = $this->appThemeManager->getPredefined($this->themeDefault);
        foreach ($predefined as $k => $v) {
            $twig->addGlobal('theme_'.$k, $saved[$k] ?? $v);
        }

        if (false === isset($parameters['firewall'])) {
            $parameters['firewall'] = true;
        }

        $menus = [];

        if (false === isset($parameters['exception_503']) || false === $parameters['exception_503']) {
            try {
                $parameters['cluster_health'] = $this->elasticsearchClusterManager->getClusterHealth();
            } catch (ConnectionException $e) {
                throw new ServiceUnavailableHttpException(null, $e->getMessage());
            }

            $parameters['master_node'] = $this->callManager->getMasterNode();

            $parameters['root'] = $this->callManager->getRoot();

            $parameters['cat_sort'] = $this->callManager->hasFeature('cat_sort');

            if (true === $parameters['firewall']) {
                if (true === $this->isGranted('MENU_CONFIGURATION', 'global')) {
                    $entries = [
                        ['granted' => 'INDEX_TEMPLATES_LEGACY_LIST', 'subject' => 'index_template_legacy', 'path' => 'index_templates_legacy'],
                        ['granted' => 'INDEX_TEMPLATES_LIST', 'subject' => 'index_template', 'path' => 'index_templates', 'feature' => 'composable_template'],
                        ['granted' => 'COMPONENT_TEMPLATES_LIST', 'subject' => 'component_template', 'path' => 'component_templates', 'feature' => 'composable_template'],
                        ['granted' => 'ILM_POLICIES_LIST', 'subject' => 'ilm_policy', 'path' => 'ilm', 'feature' => 'ilm'],
                        ['granted' => 'SLM_POLICIES_LIST', 'subject' => 'slm_policy', 'path' => 'slm', 'feature' => 'slm'],
                        ['granted' => 'REPOSITORIES_LIST', 'subject' => 'repository', 'path' => 'repositories'],
                        ['granted' => 'ENRICH_POLICIES_LIST', 'subject' => 'enrich_policy', 'path' => 'enrich', 'feature' => 'enrich'],
                        ['granted' => 'ELASTICSEARCH_USERS', 'subject' => 'global', 'path' => 'elasticsearch_users', 'feature' => 'security'],
                        ['granted' => 'ELASTICSEARCH_ROLES', 'subject' => 'global', 'path' => 'elasticsearch_roles', 'feature' => 'security'],
                        ['granted' => 'DATA_STREAMS_LIST', 'subject' => 'data_stream', 'path' => 'data_streams', 'feature' => 'data_streams'],
                    ];

                    $menus['configuration'] = $this->populateMenu($entries);
                }

                if (true === $this->isGranted('MENU_TOOLS', 'global')) {
                    $entries = [
                        ['granted' => 'SNAPSHOTS_LIST', 'subject' => 'snapshot', 'path' => 'snapshots'],
                        ['granted' => 'PIPELINES_LIST', 'subject' => 'pipeline', 'path' => 'pipelines', 'feature' => 'pipelines'],
                        ['granted' => 'TASKS', 'subject' => 'global', 'path' => 'tasks', 'feature' => 'tasks'],
                        ['granted' => 'REMOTE_CLUSTERS', 'subject' => 'global', 'path' => 'remote_clusters', 'feature' => 'remote_clusters'],
                        ['granted' => 'CAT', 'subject' => 'global', 'path' => 'cat'],
                        ['granted' => 'SQL', 'subject' => 'global', 'path' => 'sql', 'feature' => 'sql'],
                        ['granted' => 'CONSOLE', 'subject' => 'global', 'path' => 'console'],
                        ['granted' => 'DEPRECATIONS', 'subject' => 'global', 'path' => 'deprecations', 'feature' => 'deprecations'],
                        ['granted' => 'LICENSE', 'subject' => 'global', 'path' => 'license', 'feature' => 'license'],
                        ['granted' => 'INDEX_GRAVEYARD', 'subject' => 'global', 'path' => 'index_graveyard', 'feature' => 'tombstones'],
                        ['granted' => 'DANGLING_INDICES', 'subject' => 'global', 'path' => 'dangling_indices', 'feature' => 'dangling_indices'],
                    ];

                    $menus['tools'] = $this->populateMenu($entries);
                }

                if (true === $this->isGranted('MENU_STATS', 'global')) {
                    $entries = [
                        ['granted' => 'NODES_STATS', 'subject' => 'node', 'path' => 'nodes_stats'],
                        ['granted' => 'INDICES_STATS', 'subject' => 'index', 'path' => 'indices_stats'],
                        ['granted' => 'SHARDS_STATS', 'subject' => 'global', 'path' => 'shards_stats'],
                        ['granted' => 'SLM_POLICIES_STATS', 'subject' => 'slm_policy', 'path' => 'slm_stats', 'feature' => 'slm'],
                        ['granted' => 'SNAPSHOTS_STATS', 'subject' => 'snapshot', 'path' => 'snapshots_stats'],
                    ];

                    $menus['stats'] = $this->populateMenu($entries);
                }

                if (true === $this->isGranted('MENU_APPLICATION', 'global')) {
                    $entries = [
                        ['granted' => 'APP_USERS', 'subject' => 'global', 'path' => 'app_users'],
                        ['granted' => 'APP_ROLES', 'subject' => 'global', 'path' => 'app_roles'],
                        ['granted' => 'APP_UNINSTALL', 'subject' => 'global', 'path' => 'app_uninstall'],
                        ['granted' => 'APP_UPGRADE', 'subject' => 'global', 'path' => 'app_upgrade'],
                        ['granted' => 'APP_NOTIFICATIONS', 'subject' => 'global', 'path' => 'app_notifications'],
                    ];

                    $menus['application'] = $this->populateMenu($entries);
                }
            }
        }

        $parameters['menus'] = $menus;

        return $this->render($view, $parameters, $response);
    }

    private function populateMenu($entries)
    {
        $menu = [];
        foreach ($entries as $entry) {
            if (true === $this->isGranted($entry['granted'], $entry['subject'])) {
                $menu[] = [
                    'path' => $entry['path'],
                    'name' => $this->translator->trans(str_replace('_stats', '', $entry['path'])),
                ];
            }
        }
        usort($menu, [$this, 'sortByName']);
        return $menu;
    }

    private function sortByName($a, $b)
    {
        return ($b['name'] > $a['name']) ? -1 : 1;
    }
}
