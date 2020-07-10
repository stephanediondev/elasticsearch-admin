<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\EditClusterSettingType;
use App\Manager\ElasticsearchClusterManager;
use App\Model\ElasticsearchClusterSettingModel;
use App\Model\CallRequestModel;
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/stats');
        $callResponse = $this->callManager->call($callRequest);
        $clusterStats = $callResponse->getContent();

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/state');
        $callResponse = $this->callManager->call($callRequest);
        $clusterState = $callResponse->getContent();

        $nodes = [];
        foreach ($clusterState['nodes'] as $k => $node) {
            $nodes[$k] = $node['name'];
        }

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read.html.twig', [
            'master_node' => $nodes[$clusterState['master_node']] ?? false,
            'indices' => $clusterStats['indices']['count'] ?? false,
            'shards' => $clusterStats['indices']['shards']['total'] ?? false,
            'documents' => $clusterStats['indices']['docs']['count'] ?? false,
            'total_size' => $clusterStats['indices']['store']['size_in_bytes'] ?? false,
        ]);
    }

    /**
     * @Route("/cluster/allocation/explain", name="cluster_allocation_explain")
     */
    public function allocationExplain(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_ALLOCATION_EXPLAIN', 'global');

        $allocationExplain = false;

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_cluster/allocation/explain');
            $callResponse = $this->callManager->call($callRequest);
            $allocationExplain = $callResponse->getContent();
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->renderAbstract($request, 'Modules/cluster/cluster_allocation_explain.html.twig', [
            'allocation_explain' => $allocationExplain,
        ]);
    }

    /**
     * @Route("/cluster/settings", name="cluster_settings")
     */
    public function settings(Request $request, ElasticsearchClusterManager $elasticsearchClusterManager): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTINGS', 'global');

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cluster/settings');
        $callRequest->setQuery(['include_defaults' => 'true', 'flat_settings' => 'true']);
        $callResponse = $this->callManager->call($callRequest);
        $clusterSettings = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/cluster/cluster_read_settings.html.twig', [
            'cluster_settings' => $clusterSettings,
            'cluster_settings_not_dynamic' => $elasticsearchClusterManager->getClusterSettingsNotDynamic(),
        ]);
    }

    /**
     * @Route("/cluster/settings/{type}/{setting}/edit", name="cluster_settings_edit")
     */
    public function edit(Request $request, string $type, string $setting, ElasticsearchClusterManager $elasticsearchClusterManager): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTING_EDIT', 'global');

        $clusterSettings = $elasticsearchClusterManager->getClusterSettings();

        if (true == array_key_exists($setting, $clusterSettings)) {
            $clusterSettingModel = new ElasticsearchClusterSettingModel();
            $clusterSettingModel->setType($type);
            $clusterSettingModel->setSetting($setting);
            $clusterSettingModel->setValue($clusterSettings[$setting]);
            $form = $this->createForm(EditClusterSettingType::class, $clusterSettingModel);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $json = $clusterSettingModel->getJson();
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('PUT');
                    $callRequest->setPath('/_cluster/settings');
                    $callRequest->setJson($json);
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));

                    return $this->redirectToRoute('cluster_settings');
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/cluster/cluster_edit_setting.html.twig', [
                'form' => $form->createView(),
                'type' => $type,
                'setting' => $setting,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/cluster/settings/{type}/{setting}/remove", name="cluster_settings_remove")
     */
    public function remove(Request $request, string $type, string $setting): Response
    {
        $this->denyAccessUnlessGranted('CLUSTER_SETTING_REMOVE', 'global');

        $json = [
            $type => [
                $setting => null,
            ],
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_cluster/settings');
        $callRequest->setJson($json);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('cluster_settings');
    }
}
