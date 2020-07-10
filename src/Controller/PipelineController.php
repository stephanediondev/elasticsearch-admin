<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreatePipelineType;
use App\Manager\ElasticsearchPipelineManager;
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
    public function __construct(ElasticsearchPipelineManager $elasticsearchPipelineManager)
    {
        $this->elasticsearchPipelineManager = $elasticsearchPipelineManager;
    }

    /**
     * @Route("/pipelines", name="pipelines")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('PIPELINES');

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
        $this->denyAccessUnlessGranted('PIPELINES_CREATE');

        $pipeline = false;

        if ($request->query->get('pipeline')) {
            $pipeline = $this->elasticsearchPipelineManager->getByName($request->query->get('pipeline'));

            if (false == $pipeline) {
                throw new NotFoundHttpException();
            }

            $pipeline->setName($pipeline->getName().'-copy');
        }

        if (false == $pipeline) {
            $pipeline = new ElasticsearchPipelineModel();
        }
        $form = $this->createForm(CreatePipelineType::class, $pipeline);

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
        $this->denyAccessUnlessGranted('PIPELINES');

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
        $this->denyAccessUnlessGranted('PIPELINES');

        $pipeline = $this->elasticsearchPipelineManager->getByName($name);

        if (false == $pipeline) {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(CreatePipelineType::class, $pipeline, ['update' => true]);

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
        $this->denyAccessUnlessGranted('PIPELINES');

        $pipeline = $this->elasticsearchPipelineManager->getByName($name);

        if (false == $pipeline) {
            throw new NotFoundHttpException();
        }

        $callResponse = $this->elasticsearchPipelineManager->deleteByName($pipeline->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('pipelines');
    }
}
