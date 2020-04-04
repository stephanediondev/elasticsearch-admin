<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\EditClusterSettingType;
use App\Model\ElasticsearchClusterSettingModel;
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

    /**
     * @Route("/cluster/settings/{type}/{setting}/edit", name="cluster_settings_edit")
     */
    public function edit(Request $request, string $type, string $setting): Response
    {
        $clusterSettings = $this->callManager->getClusterSettings();

        if (true == array_key_exists($setting, $clusterSettings)) {
            $clusterSettingModel = new ElasticsearchClusterSettingModel();
            $clusterSettingModel->setType($type);
            $clusterSettingModel->setSetting($setting);
            $clusterSettingModel->setValue($clusterSettings[$setting]);
            $form = $this->createForm(EditClusterSettingType::class, $clusterSettingModel);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $json = [
                        $clusterSettingModel->getType() => [
                            $clusterSettingModel->getSetting() => $clusterSettingModel->getValue(),
                        ],
                    ];
                    $call = new CallModel();
                    $call->setMethod('PUT');
                    $call->setPath('/_cluster/settings');
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'success.cluster_settings_edit');

                    return $this->redirectToRoute('cluster_settings', []);
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
        $json = [
            $type => [
                $setting => null,
            ],
        ];
        $call = new CallModel();
        $call->setMethod('PUT');
        $call->setPath('/_cluster/settings');
        $call->setJson($json);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.cluster_settings_remove');

        return $this->redirectToRoute('cluster_settings', []);
    }
}
