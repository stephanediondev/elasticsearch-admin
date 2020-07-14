<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateComponentTemplateType;
use App\Manager\ElasticsearchComponentTemplateManager;
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
        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATES', 'global');

        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

        $templates = $this->elasticsearchComponentTemplateManager->getAll();

        return $this->renderAbstract($request, 'Modules/component_template/component_template_index.html.twig', [
            'templates' => $this->paginatorManager->paginate([
                'route' => 'component_templates',
                'route_parameters' => [],
                'total' => count($templates),
                'rows' => $templates,
                'page' => 1,
                'size' => count($templates),
            ]),
        ]);
    }

    /**
     * @Route("/component-templates/create", name="component_templates_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATES_CREATE', 'global');

        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

        $template = false;

        if ($request->query->get('template')) {
            $template = $this->elasticsearchComponentTemplateManager->getByName($request->query->get('template'));

            if (false == $template) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('COMPONENT_TEMPLATE_COPY', $template);

            $template->setName($template->getName().'-copy');
        }

        if (false == $template) {
            $template = new ElasticsearchComponentTemplateModel();
        }
        $form = $this->createForm(CreateComponentTemplateType::class, $template);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchComponentTemplateManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('component_templates_read', ['name' => $template->getName()]);
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
        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATES', 'global');

        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

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
        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATES', 'global');

        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

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
        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATES', 'global');

        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

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
        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATE_UPDATE', $template);

        $form = $this->createForm(CreateComponentTemplateType::class, $template, ['context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchComponentTemplateManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('component_templates_read', ['name' => $template->getName()]);
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
        if (false == $this->checkVersion('7.8')) {
            throw new AccessDeniedHttpException();
        }

        $template = $this->elasticsearchComponentTemplateManager->getByName($name);

        if (false == $template) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('COMPONENT_TEMPLATE_DELETE', $template);

        $callResponse = $this->elasticsearchComponentTemplateManager->deleteByName($template->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('component_templates');
    }
}
