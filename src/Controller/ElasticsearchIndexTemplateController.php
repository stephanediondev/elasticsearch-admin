<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchIndexTemplateType;
use App\Form\Type\ElasticsearchIndexTemplateFilterType;
use App\Manager\ElasticsearchComponentTemplateManager;
use App\Manager\ElasticsearchIndexTemplateManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchIndexTemplateController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexTemplateManager $elasticsearchIndexTemplateManager, ElasticsearchComponentTemplateManager $elasticsearchComponentTemplateManager)
    {
        $this->elasticsearchIndexTemplateManager = $elasticsearchIndexTemplateManager;
        $this->elasticsearchComponentTemplateManager = $elasticsearchComponentTemplateManager;
    }

    /**
     * @Route("/index-templates", name="index_templates")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES', 'global');

        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ElasticsearchIndexTemplateFilterType::class);

        $form->handleRequest($request);

        $templates = $this->elasticsearchIndexTemplateManager->getAll([
            'name' => $form->get('name')->getData(),
        ]);

        return $this->renderAbstract($request, 'Modules/index_template/index_template_index.html.twig', [
            'templates' => $this->paginatorManager->paginate([
                'route' => 'index_templates',
                'route_parameters' => [],
                'total' => count($templates),
                'rows' => $templates,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index-templates/create", name="index_templates_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES_CREATE', 'global');

        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = null;

        if ($request->query->get('template')) {
            $template = $this->elasticsearchIndexTemplateManager->getByName($request->query->get('template'));

            if (null === $template) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('INDEX_TEMPLATE_COPY', $template);

            $template->setName($template->getName().'-copy');
        }

        $results = $this->elasticsearchComponentTemplateManager->getAll();

        $componentTemplates = [];
        foreach ($results as $row) {
            $componentTemplates[] = $row->getName();
        }

        if (null === $template) {
            $template = new ElasticsearchIndexTemplateModel();
        }
        $form = $this->createForm(ElasticsearchIndexTemplateType::class, $template, ['component_templates' => $componentTemplates]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchIndexTemplateManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_read', ['name' => $template->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_template/index_template_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index-templates/{name}", name="index_templates_read")
     */
    public function read(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES', 'global');

        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = $this->elasticsearchIndexTemplateManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index_template/index_template_read.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/index-templates/{name}/settings", name="index_templates_read_settings")
     */
    public function settings(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES', 'global');

        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = $this->elasticsearchIndexTemplateManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index_template/index_template_read_settings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/index-templates/{name}/mappings", name="index_templates_read_mappings")
     */
    public function mappings(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES', 'global');

        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = $this->elasticsearchIndexTemplateManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index_template/index_template_read_mappings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/index-templates/{name}/update", name="index_templates_update")
     */
    public function update(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = $this->elasticsearchIndexTemplateManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_TEMPLATE_UPDATE', $template);

        $results = $this->elasticsearchComponentTemplateManager->getAll();

        $componentTemplates = [];
        foreach ($results as $row) {
            $componentTemplates[] = $row->getName();
        }

        $form = $this->createForm(ElasticsearchIndexTemplateType::class, $template, ['component_templates' => $componentTemplates, 'context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchIndexTemplateManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_read', ['name' => $template->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_template/index_template_update.html.twig', [
            'template' => $template,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index-templates/{name}/delete", name="index_templates_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = $this->elasticsearchIndexTemplateManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_TEMPLATE_DELETE', $template);

        $callResponse = $this->elasticsearchIndexTemplateManager->deleteByName($template->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('index_templates');
    }

    /**
     * @Route("/index-templates/{name}/simulate", name="index_templates_simulate")
     */
    public function simulate(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES', 'global');

        if (false === $this->callManager->hasFeature('composable_template')) {
            throw new AccessDeniedException();
        }

        $template = $this->elasticsearchIndexTemplateManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_index_template/_simulate/'.$template->getName());
        $callResponse = $this->callManager->call($callRequest);

        return $this->renderAbstract($request, 'Modules/index_template/index_template_simulate.html.twig', [
            'template' => $template,
            'simulate' => $callResponse->getContent(),
        ]);
    }
}
