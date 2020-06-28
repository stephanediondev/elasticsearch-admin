<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreatePipelineType;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchPipelineModel;
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
     * @Route("/pipelines/create", name="pipelines_create")
     */
    public function create(Request $request): Response
    {
        $pipeline = false;

        if ($request->query->get('pipeline')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_ingest/pipeline/'.$request->query->get('pipeline'));
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                throw new NotFoundHttpException();
            }

            $pipeline = $callResponse->getContent();
            $pipeline = $pipeline[$request->query->get('pipeline')];
            $pipeline['name'] = $request->query->get('pipeline').'-copy';
        }

        $pipelineModel = new ElasticsearchPipelineModel();
        if ($pipeline) {
            $pipelineModel->convert($pipeline);
        }
        $form = $this->createForm(CreatePipelineType::class, $pipelineModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $pipelineModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_ingest/pipeline/'.$pipelineModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('pipelines_read', ['name' => $pipelineModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/pipeline/pipeline_create.html.twig', [
            'form' => $form->createView(),
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
     * @Route("/pipelines/{name}/update", name="pipelines_update")
     */
    public function update(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ingest/pipeline/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $pipeline = $callResponse->getContent();
        $pipeline = $pipeline[$name];
        $pipeline['name'] = $name;

        $pipelineModel = new ElasticsearchPipelineModel();
        $pipelineModel->convert($pipeline);
        $form = $this->createForm(CreatePipelineType::class, $pipelineModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $pipelineModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_ingest/pipeline/'.$pipelineModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('pipelines_read', ['name' => $pipelineModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/pipeline/pipeline_update.html.twig', [
            'pipeline' => $pipeline,
            'form' => $form->createView(),
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
