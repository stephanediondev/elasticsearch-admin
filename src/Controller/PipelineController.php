<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
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
        $call = new CallModel();
        $call->setPath('/_ingest/pipeline');
        $pipelines = $this->callManager->call($call);

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
