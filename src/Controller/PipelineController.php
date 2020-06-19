<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * @Route("/pipelines/{name}", name="pipelines_read")
     */
    public function read(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ingest/pipeline/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $pipeline = $row;
            $pipeline['name'] = $k;
        }

        return $this->renderAbstract($request, 'Modules/pipeline/pipeline_read.html.twig', [
            'pipeline' => $pipeline,
        ]);
    }

    /**
     * @Route("/pipelines/{name}/delete", name="pipelines_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_ingest/pipeline/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('pipelines');
    }
}
