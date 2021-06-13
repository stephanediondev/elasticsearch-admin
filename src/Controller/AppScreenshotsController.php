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
class AppScreenshotsController extends AbstractAppController
{
    /**
     * @Route("/screenshots", name="screenshots")
     */
    public function index(Request $request, string $appEnv): Response
    {
        $cookie = $request->getSession()->getId();

        $base = $request->getSchemeAndHttpHost().$request->getBaseURL();

        $version = $this->callManager->getRoot()['version']['number'];

        $masterNode = $this->callManager->getMasterNode();

        $folder = __DIR__.'/../../screenshots/'.$version;
        if (false === is_dir($folder)) {
            mkdir($folder);
            mkdir($folder.'/original');
            mkdir($folder.'/resized');
        }

        $entries = [
            ['title' => 'Cluster', 'filename' => 'cluster', 'path' => '/admin/cluster'],
            ['title' => 'Cluster audit', 'filename' => 'cluster-audit', 'path' => '/admin/cluster/audit'],
            ['title' => 'Cluster settings', 'filename' => 'cluster-settings', 'path' => '/admin/cluster/settings', 'feature' => 'cluster_settings'],
            ['title' => 'Cluster disk thresholds', 'filename' => 'disk-thresholds', 'path' => '/admin/cluster/disk-thresholds', 'feature' => 'cluster_settings'],
            ['title' => 'Cluster allocation explain', 'filename' => 'cluster-allocation-explain', 'path' => '/admin/cluster/allocation/explain', 'feature' => 'allocation_explain'],
            ['title' => 'Nodes', 'filename' => 'nodes', 'path' => '/admin/nodes'],
            ['title' => 'Nodes stats', 'filename' => 'nodes-stats', 'path' => '/admin/nodes/stats'],
            ['title' => 'Nodes reload secure settings', 'filename' => 'nodes-reload-secure-settings', 'path' => '/admin/nodes/reload-secure-settings', 'feature' => 'reload_secure_settings'],
            ['title' => 'Node', 'filename' => 'node', 'path' => '/admin/nodes/'.urlencode($masterNode)],
            ['title' => 'Node settings', 'filename' => 'node-settings', 'path' => '/admin/nodes/'.urlencode($masterNode).'/settings'],
            ['title' => 'Node usage', 'filename' => 'node-usage', 'path' => '/admin/nodes/'.urlencode($masterNode).'/usage', 'feature' => 'node_usage'],
            ['title' => 'Node plugins', 'filename' => 'node-plugins', 'path' => '/admin/nodes/'.urlencode($masterNode).'/plugins'],
            ['title' => 'Indices', 'filename' => 'indices', 'path' => '/admin/indices'],
            ['title' => 'Indices stats', 'filename' => 'indices-stats', 'path' => '/admin/indices/stats'],
            ['title' => 'Index', 'filename' => 'index', 'path' => '/admin/indices/elasticsearch-admin-test'],
            ['title' => 'Index settings', 'filename' => 'index-settings', 'path' => '/admin/indices/elasticsearch-admin-test/settings'],
            ['title' => 'Index search', 'filename' => 'index-search', 'path' => '/admin/indices/elasticsearch-admin-test/search'],
            ['title' => 'Index import from file', 'filename' => 'index-file-import', 'path' => '/admin/indices/elasticsearch-admin-test/file-import'],
            ['title' => 'Index import from database', 'filename' => 'index-database-import', 'path' => '/admin/indices/elasticsearch-admin-test/database-import'],
            ['title' => 'Create index', 'filename' => 'index-create', 'path' => '/admin/indices/create'],
            ['title' => 'Legacy index templates', 'filename' => 'index-templates-legacy', 'path' => '/admin/index-templates-legacy'],
            ['title' => 'Create legacy index template', 'filename' => 'index-template-create-legacy', 'path' => '/admin/index-templates-legacy/create'],
            ['title' => 'Composable index templates', 'filename' => 'index-templates', 'path' => '/admin/index-templates', 'feature' => 'composable_template'],
            ['title' => 'Create composable index template', 'filename' => 'index-template-create', 'path' => '/admin/index-templates/create', 'feature' => 'composable_template'],
            ['title' => 'Shards', 'filename' => 'shards', 'path' => '/admin/shards'],
            ['title' => 'Shards stats', 'filename' => 'shards-stats', 'path' => '/admin/shards/stats'],
            ['title' => 'Index graveyard', 'filename' => 'index-graveyard', 'path' => '/admin/index-graveyard', 'feature' => 'tombstones'],
            ['title' => 'Dangling indices', 'filename' => 'dangling-indices', 'path' => '/admin/dangling-indices', 'feature' => 'dangling_indices'],
            ['title' => 'Create Shared file system repository', 'filename' => 'repository-create-fs', 'path' => '/admin/repositories/create/fs'],
            ['title' => 'Create AWS S3 repository', 'filename' => 'repository-create-s3', 'path' => '/admin/repositories/create/s3'],
            ['title' => 'Create SLM policy', 'filename' => 'slm-policy-create', 'path' => '/admin/slm/create', 'feature' => 'slm'],
            ['title' => 'Snaphosts', 'filename' => 'snapshots', 'path' => '/admin/snapshots'],
            ['title' => 'Snaphosts stats', 'filename' => 'snapshots-stats', 'path' => '/admin/snapshots/stats'],
            ['title' => 'Create snapshot', 'filename' => 'snapshot-create', 'path' => '/admin/snapshots/create'],
            ['title' => 'Create enrich policy', 'filename' => 'enrich-create', 'path' => '/admin/enrich/create', 'feature' => 'enrich'],
            ['title' => 'License', 'filename' => 'license', 'path' => '/admin/license', 'feature' => 'license'],
            ['title' => 'Subscriptions', 'filename' => 'subscriptions', 'path' => '/admin/subscriptions'],
            ['title' => 'Console', 'filename' => 'console', 'path' => '/admin/console'],
            ['title' => 'SQL access', 'filename' => 'sql', 'path' => '/admin/sql', 'feature' => 'sql'],
            ['title' => 'Tasks', 'filename' => 'tasks', 'path' => '/admin/tasks', 'feature' => 'tasks'],
            ['title' => 'Users', 'filename' => 'elasticsearch-users', 'path' => '/admin/elasticsearch-users', 'feature' => 'security'],
            ['title' => 'Roles', 'filename' => 'elasticsearch-roles', 'path' => '/admin/elasticsearch-roles', 'feature' => 'security'],
        ];

        $fp = fopen($folder.'/README.md', 'w');

        fwrite($fp, '## Screenshots '.$version);
        fwrite($fp, "\r\n");
        fwrite($fp, "\r\n");

        $results = [];
        foreach ($entries as $k => $entry) {
            $disabled = false;

            if ('repository-create-s3' == $entry['filename'] && false === $this->callManager->hasPlugin('repository-s3')) {
                $disabled = true;
            }

            if (true === isset($entry['feature']) && false === $this->callManager->hasFeature($entry['feature'])) {
                $disabled = true;
            }

            if (false === $disabled) {
                fwrite($fp, '[!['.$entry['title'].'](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/'.$version.'/resized/resized-'.$entry['filename'].'.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/main/screenshots/'.$version.'/original/original-'.$entry['filename'].'.png)');
                fwrite($fp, "\r\n");
                fwrite($fp, "\r\n");

                $theme = ($k % 2) == 0 ? 'dark' : 'light';

                if ($request->query->get('theme') && $theme == $request->query->get('theme')) {
                    $results[] = [
                        'pageres' => 'pageres '.$base.$entry['path'].' 1280x960 --crop --filename=screenshots/'.$version.'/original/original-'.$entry['filename'].' --overwrite --cookie=\'PHPSESSID='.$cookie.'\'',
                        'convert' => 'convert -resize 800x600 screenshots/'.$version.'/original/original-'.$entry['filename'].'.png screenshots/'.$version.'/resized/resized-'.$entry['filename'].'.png',
                    ];
                }
            }
        }

        fclose($fp);

        if ('prod' != $appEnv) {
            $this->addFlash('warning', 'Set APP_ENV to prod');
        }

        $this->addFlash('warning', 'Run bin/console app:phpunit');
        $this->addFlash('warning', 'Run bin/console cache:clear --env=prod');

        return $this->renderAbstract($request, 'Modules/screenshots/screenshots_index.html.twig', [
            'results' => $results,
        ]);
    }
}
