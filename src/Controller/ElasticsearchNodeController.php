<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\ElasticsearchNodeReloadSecureSettingsType;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchReloadSecureSettingsModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchNodeController extends AbstractAppController
{
    public function __construct(ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/nodes", name="nodes")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('NODES', 'global');

        $nodes = $this->elasticsearchNodeManager->getAll();

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        return $this->renderAbstract($request, 'Modules/node/node_index.html.twig', [
            'cluster_settings' => $clusterSettings,
            'nodes' => $this->paginatorManager->paginate([
                'route' => 'nodes',
                'route_parameters' => [],
                'total' => count($nodes),
                'rows' => $nodes,
                'page' => 1,
                'size' => count($nodes),
            ]),
        ]);
    }

    /**
     * @Route("/nodes/fetch", name="nodes_fetch")
     */
    public function fetch(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('NODES', 'global');

        $nodes = $this->elasticsearchNodeManager->getAll();

        return new JsonResponse($nodes, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/nodes/{node}", name="nodes_read")
     */
    public function read(Request $request, string $node): Response
    {
        $this->denyAccessUnlessGranted('NODES', 'global');

        $node = $this->elasticsearchNodeManager->getByName($node);

        if (false == $node) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/node/node_read.html.twig', [
            'node' => $node,
        ]);
    }

    /**
     * @Route("/nodes/{node}/plugins", name="nodes_read_plugins")
     */
    public function readPlugins(Request $request, string $node): Response
    {
        $node = $this->elasticsearchNodeManager->getByName($node);

        if (false == $node) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('NODE_PLUGINS', $node);

        return $this->renderAbstract($request, 'Modules/node/node_read_plugins.html.twig', [
            'node' => $node,
        ]);
    }

    /**
     * @Route("/nodes/{node}/usage", name="nodes_read_usage")
     */
    public function readUsage(Request $request, string $node): Response
    {
        $node = $this->elasticsearchNodeManager->getByName($node);

        if (false == $node) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('NODE_USAGE', $node);

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes/'.$node->getName().'/usage');
        $callResponse = $this->callManager->call($callRequest);
        $usage = $callResponse->getContent();
        $usage = $usage['nodes'][key($usage['nodes'])];

        if (true == isset($usage['rest_actions'])) {
            ksort($usage['rest_actions']);
        } else {
            $usage['rest_actions'] = [];
        }

        return $this->renderAbstract($request, 'Modules/node/node_read_usage.html.twig', [
            'node' => $node,
            'usage' => $usage,
        ]);
    }

    /**
     * @Route("/nodes/{node}/reload-secure-settings", name="nodes_reload_secure_settings")
     */
    public function readReloadSecureSettings(Request $request, string $node): Response
    {
        if (false == $this->callManager->checkVersion('6.4')) {
            throw new AccessDeniedHttpException();
        }

        $node = $this->elasticsearchNodeManager->getByName($node);

        if (false == $node) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('NODE_RELOAD_SECURE_SETTINGS', $node);

        $reloadSecureSettingsModel = new ElasticsearchReloadSecureSettingsModel();
        $form = $this->createForm(ElasticsearchNodeReloadSecureSettingsType::class, $reloadSecureSettingsModel);

        $form->handleRequest($request);

        $parameters = [
            'node' => $node,
            'form' => $form->createView(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $reloadSecureSettingsModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_nodes/'.$node->getName().'/reload_secure_settings');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);
                $parameters['response'] = $callResponse->getContent();
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/node/node_reload_secure_settings.html.twig', $parameters);
    }
}
