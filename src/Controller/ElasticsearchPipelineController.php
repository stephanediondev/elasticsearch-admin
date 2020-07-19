<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\ElasticsearchPipelineType;
use App\Manager\ElasticsearchPipelineManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchPipelineModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchPipelineController extends AbstractAppController
{
    public function __construct(ElasticsearchPipelineManager $elasticsearchPipelineManager)
    {
        $this->elasticsearchPipelineManager = $elasticsearchPipelineManager;
    }

    /**
     * @Route("/pipelines", name="pipelines")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('PIPELINES', 'global');

        if (false == $this->callManager->hasFeature('pipelines')) {
            throw new AccessDeniedHttpException();
        }

        $pipelines = $this->elasticsearchPipelineManager->getAll();

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
        $this->denyAccessUnlessGranted('PIPELINES_CREATE', 'global');

        if (false == $this->callManager->hasFeature('pipelines')) {
            throw new AccessDeniedHttpException();
        }

        $pipeline = false;

        if ($request->query->get('pipeline')) {
            $pipeline = $this->elasticsearchPipelineManager->getByName($request->query->get('pipeline'));

            if (false == $pipeline) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('PIPELINE_COPY', $pipeline);

            $pipeline->setName($pipeline->getName().'-copy');
        }

        if (false == $pipeline) {
            $pipeline = new ElasticsearchPipelineModel();
        }
        $form = $this->createForm(ElasticsearchPipelineType::class, $pipeline);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchPipelineManager->send($pipeline);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('pipelines_read', ['name' => $pipeline->getName()]);
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
        $this->denyAccessUnlessGranted('PIPELINES', 'global');

        if (false == $this->callManager->hasFeature('pipelines')) {
            throw new AccessDeniedHttpException();
        }

        $pipeline = $this->elasticsearchPipelineManager->getByName($name);

        if (false == $pipeline) {
            throw new NotFoundHttpException();
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
        if (false == $this->callManager->hasFeature('pipelines')) {
            throw new AccessDeniedHttpException();
        }

        $pipeline = $this->elasticsearchPipelineManager->getByName($name);

        if (false == $pipeline) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('PIPELINE_UPDATE', $pipeline);

        $form = $this->createForm(ElasticsearchPipelineType::class, $pipeline, ['context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchPipelineManager->send($pipeline);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('pipelines_read', ['name' => $pipeline->getName()]);
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
        if (false == $this->callManager->hasFeature('pipelines')) {
            throw new AccessDeniedHttpException();
        }

        $pipeline = $this->elasticsearchPipelineManager->getByName($name);

        if (false == $pipeline) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('PIPELINE_DELETE', $pipeline);

        $callResponse = $this->elasticsearchPipelineManager->deleteByName($pipeline->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('pipelines');
    }
}
