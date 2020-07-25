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
class ScreenshotsController extends AbstractAppController
{
    /**
     * @Route("/screenshots", name="screenshots")
     */
    public function index(Request $request): Response
    {
        $cookie = session_id();

        $base = $request->getSchemeAndHttpHost().$request->getBaseURL();

        $version = $this->callManager->getRoot()['version']['number'];

        $masterNode = $this->callManager->getMasterNode();

        $folder = __DIR__.'/../../screenshots/'.$version;
        if (false == is_dir($folder)) {
            mkdir($folder);
            mkdir($folder.'/original');
            mkdir($folder.'/resized');
        }

        $entries = [
            ['title' => 'Cluster', 'filename' => 'cluster', 'path' => '/admin/cluster'],
            ['title' => 'Cluster settings', 'filename' => 'cluster-settings', 'path' => '/admin/cluster/settings', 'feature' => 'cluster_settings'],
            ['title' => 'Cluster allocation explain', 'filename' => 'cluster-allocation-explain', 'path' => '/admin/cluster/allocation/explain', 'feature' => 'allocation_explain'],
            ['title' => 'Nodes', 'filename' => 'nodes', 'path' => '/admin/nodes'],
            ['title' => 'Node', 'filename' => 'node', 'path' => '/admin/nodes/'.urlencode($masterNode)],
            ['title' => 'Node usage', 'filename' => 'node-usage', 'path' => '/admin/nodes/'.urlencode($masterNode).'/usage', 'feature' => 'node_usage'],
            ['title' => 'Indices', 'filename' => 'indices', 'path' => '/admin/indices'],
            ['title' => 'Indices stats', 'filename' => 'indices-stats', 'path' => '/admin/indices/stats'],
            ['title' => 'Index', 'filename' => 'index', 'path' => '/admin/indices/elasticsearch-admin-test'],
            ['title' => 'Index settings', 'filename' => 'index-settings', 'path' => '/admin/indices/elasticsearch-admin-test/settings'],
            ['title' => 'Index search', 'filename' => 'index-search', 'path' => '/admin/indices/elasticsearch-admin-test/search'],
            ['title' => 'Index import', 'filename' => 'index-import', 'path' => '/admin/indices/elasticsearch-admin-test/import'],
            ['title' => 'Create index', 'filename' => 'index-create', 'path' => '/admin/indices/create'],
            ['title' => 'Index templates legacy', 'filename' => 'index-templates-legacy', 'path' => '/admin/index-templates-legacy'],
            ['title' => 'Create index template legacy', 'filename' => 'index-template-create-legacy', 'path' => '/admin/index-templates-legacy/create'],
            ['title' => 'Shards', 'filename' => 'shards', 'path' => '/admin/shards'],
            ['title' => 'Create Shared file system repository', 'filename' => 'repository-create-fs', 'path' => '/admin/repositories/create/fs'],
            ['title' => 'Create AWS S3 repository', 'filename' => 'repository-create-s3', 'path' => '/admin/repositories/create/s3'],
            ['title' => 'Create SLM policy', 'filename' => 'slm-policy-create', 'path' => '/admin/slm/create', 'feature' => 'slm'],
            ['title' => 'Snaphosts', 'filename' => 'snapshots', 'path' => '/admin/snapshots'],
            ['title' => 'Create snapshot', 'filename' => 'snapshot-create', 'path' => '/admin/snapshots/create'],
            ['title' => 'Create enrich policy', 'filename' => 'enrich-create', 'path' => '/admin/enrich/create', 'feature' => 'enrich'],
            ['title' => 'License', 'filename' => 'license', 'path' => '/admin/license', 'feature' => 'license'],
            ['title' => 'Console', 'filename' => 'console', 'path' => '/admin/console'],
            ['title' => 'SQL access', 'filename' => 'sql', 'path' => '/admin/sql', 'feature' => 'sql'],
            ['title' => 'Users', 'filename' => 'elasticsearch-users', 'path' => '/admin/elasticsearch-users', 'feature' => 'security'],
            ['title' => 'Roles', 'filename' => 'elasticsearch-roles', 'path' => '/admin/elasticsearch-roles', 'feature' => 'security'],
        ];

        $fp = fopen($folder.'/README.md', 'w');

        fwrite($fp, '## Screenshots '.$version);
        fwrite($fp, "\r\n");
        fwrite($fp, "\r\n");

        $results = [];
        foreach ($entries as $entry) {
            $disabled = false;

            if ('repository-create-s3' == $entry['filename'] && false == $this->callManager->hasPlugin('repository-s3')) {
                $disabled = true;
            }

            if (true == isset($entry['feature']) && false == $this->callManager->hasFeature($entry['feature'])) {
                $disabled = true;
            }

            if (false == $disabled) {
                fwrite($fp, '[!['.$entry['title'].'](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/'.$version.'/resized/resized-'.$entry['filename'].'.png)](https://raw.githubusercontent.com/stephanediondev/elasticsearch-admin/master/screenshots/'.$version.'/original/original-'.$entry['filename'].'.png)');
                fwrite($fp, "\r\n");
                fwrite($fp, "\r\n");

                $results[] = [
                    'pageres' => 'pageres '.$base.$entry['path'].' 1280x768 --crop --filename=screenshots/'.$version.'/original/original-'.$entry['filename'].' --overwrite --cookie=\'PHPSESSID='.$cookie.'\'',
                    'convert' => 'convert -resize 800x480 screenshots/'.$version.'/original/original-'.$entry['filename'].'.png screenshots/'.$version.'/resized/resized-'.$entry['filename'].'.png',
                ];
            }
        }

        fclose($fp);

        return $this->renderAbstract($request, 'Modules/screenshots/screenshots_index.html.twig', [
            'results' => $results,
        ]);
    }
}
