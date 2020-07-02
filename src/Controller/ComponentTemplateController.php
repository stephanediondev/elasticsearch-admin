<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateComponentTemplateType;
use App\Manager\ElasticsearchComponentTemplateManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchComponentTemplateModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ComponentTemplateController extends AbstractAppController
{
    public function __construct(ElasticsearchComponentTemplateManager $elasticsearchComponentTemplateManager)
    {
        $this->elasticsearchComponentTemplateManager = $elasticsearchComponentTemplateManager;
    }

    /**
     * @Route("/component-templates", name="component_templates")
     */
    public function index(Request $request): Response
    {
        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

        $componentTemplates = $this->elasticsearchComponentTemplateManager->getAll();

        usort($componentTemplates, [$this, 'sortByName']);

        return $this->renderAbstract($request, 'Modules/component_template/component_template_index.html.twig', [
            'componentTemplates' => $this->paginatorManager->paginate([
                'route' => 'component_templates',
                'route_parameters' => [],
                'total' => count($componentTemplates),
                'rows' => $componentTemplates,
                'page' => 1,
                'size' => count($componentTemplates),
            ]),
        ]);
    }

    private function sortByName($a, $b) {
        return $b['name'] < $a['name'];
    }

    /**
     * @Route("/component-templates/create", name="component_templates_create")
     */
    public function create(Request $request): Response
    {
        $template = false;

        if ($request->query->get('template')) {
            $template = $this->elasticsearchComponentTemplateManager->getByName($request->query->get('template'));

            if (false == $template) {
                throw new NotFoundHttpException();
            }

            if (true == $template['is_system']) {
                throw new AccessDeniedHttpException();
            }

            $template['name'] = $template['name'].'-copy';
        }

        $templateModel = new ElasticsearchComponentTemplateModel();
        if ($template) {
            $templateModel->convert($template);
        }
        $form = $this->createForm(CreateComponentTemplateType::class, $templateModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchComponentTemplateManager->send($templateModel);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('component_templates_read', ['name' => $templateModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/component_template/component_template_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/component-templates/{name}", name="component_templates_read")
     */
    public function read(Request $request, string $name): Response
    {
        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/component_template/component_template_read.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/component-templates/{name}/settings", name="component_templates_read_settings")
     */
    public function settings(Request $request, string $name): Response
    {
        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/component_template/component_template_read_settings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/component-templates/{name}/mappings", name="component_templates_read_mappings")
     */
    public function mappings(Request $request, string $name): Response
    {
        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/component_template/component_template_read_mappings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/component-templates/{name}/update", name="component_templates_update")
     */
    public function update(Request $request, string $name): Response
    {
        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        if (true == $template['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $templateModel = new ElasticsearchComponentTemplateModel();
        $templateModel->convert($template);
        $form = $this->createForm(CreateComponentTemplateType::class, $templateModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchComponentTemplateManager->send($templateModel);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('component_templates_read', ['name' => $templateModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/component_template/component_template_update.html.twig', [
            'template' => $template,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/component-templates/{name}/delete", name="component_templates_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        if (true == $template['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('component_templates');
    }
}
