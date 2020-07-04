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

        $screenshots = [
            //'login' => '/',
            'cluster' => '/admin/cluster',
            'cluster-settings' => '/admin/cluster/settings',
            'cluster-allocation-explain' => '/admin/cluster/allocation/explain',
            'index-create' => '/admin/indices/create',
            'index-templates' => '/admin/index-templates',
            'index-template-create' => '/admin/index-templates/create',
            'indices' => '/admin/indices',
            'indices-stats' => '/admin/indices/stats',
            'index' => '/admin/indices/elasticsearch-admin-test',
            'nodes' => '/admin/nodes',
            'node' => '/admin/nodes/macos',
            'snapshots' => '/admin/snapshots',
            'snapshot-create' => '/admin/snapshots/create',
            'repository-create-s3' => '/admin/repositories/create/s3',
            'slm-policy-create' => '/admin/slm/create',
            'shards' => '/admin/shards',
            'enrich-create' => '/admin/enrich/create',
            'license' => '/admin/license',
        ];

        $results = [];
        foreach ($screenshots as $filename => $screenshot) {
            $results[] = [
                'pageres' => 'pageres '.$base.$screenshot.' 1280x768 --crop --filename=assets/images/original-'.$filename.' --overwrite --cookie=\'PHPSESSID='.$cookie.'\'',
                'convert' => 'convert -resize 800x480 assets/images/original-'.$filename.'.png assets/images/resized-'.$filename.'.png',
            ];
        }

        return $this->renderAbstract($request, 'Modules/screenshots/screenshots_index.html.twig', [
            'results' => $results,
        ]);
    }
}
