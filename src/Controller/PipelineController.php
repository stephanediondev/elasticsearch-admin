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
class PipelineController extends AbstractAppController
{
    /**
     * @Route("/pipelines", name="pipelines")
     */
    public function index(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ingest/pipeline');
        $callResponse = $this->callManager->call($callRequest);
        $pipelines = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/pipeline/pipeline_index.html.twig', [
            'pipelines' => $this->paginatorManager->paginate([
                'route' => 'pipelines',
                'route_parameters' => [],
                'total' => count($pipelines),
                'rows' => $pipelines,
                'page' => 1,
                'size' => count($pipelines),
            ]),
        ]);
    }
}
