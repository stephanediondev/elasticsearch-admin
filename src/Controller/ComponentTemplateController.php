<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateComponentTemplateType;
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
    /**
     * @Route("/component-templates", name="component_templates")
     */
    public function component(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template');
        $callResponse = $this->callManager->call($callRequest);
        $componentTemplates = $callResponse->getContent();

        $componentTemplates = $componentTemplates['component_templates'];

        return $this->renderAbstract($request, 'Modules/component_template/component_template_index.html.twig', [
            'componentTemplates' => $this->paginatorManager->paginate([
                'route' => 'componentTemplates',
                'route_parameters' => [],
                'total' => count($componentTemplates),
                'rows' => $componentTemplates,
                'page' => 1,
                'size' => count($componentTemplates),
            ]),
        ]);
    }

    /**
     * @Route("/component-templates/create", name="component_templates_create")
     */
    public function create(Request $request): Response
    {
        $template = false;

        if ($request->query->get('template')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_component_template/'.$request->query->get('template'));
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                throw new NotFoundHttpException();
            }

            $template = $callResponse->getContent();
            $template = $template['component_templates'][0];
            $template = array_merge($template, $template['component_template']['template']);
            $template['name'] = $template['name'].'-copy';
            $template['is_system'] = '.' == substr($template['name'], 0, 1);

            if (true == $template['is_system']) {
                throw new AccessDeniedHttpException();
            }
        }

        $templateModel = new ElasticsearchComponentTemplateModel();
        if ($template) {
            $templateModel->convert($template);
        }
        $form = $this->createForm(CreateComponentTemplateType::class, $templateModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $templateModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_component_template/'.$templateModel->getName());
                $callRequest->setBody(json_encode($json, JSON_FORCE_OBJECT));
                $callResponse = $this->callManager->call($callRequest);

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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template['component_templates'][0];
        $template = array_merge($template, $template['component_template']['template']);
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        return $this->renderAbstract($request, 'Modules/component_template/component_template_read.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/component-templates/{name}/settings", name="component_templates_read_settings")
     */
    public function settings(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template['component_templates'][0];
        $template = array_merge($template, $template['component_template']['template']);
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        return $this->renderAbstract($request, 'Modules/component_template/component_template_read_settings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/component-templates/{name}/mappings", name="component_templates_read_mappings")
     */
    public function mappings(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template['component_templates'][0];
        $template = array_merge($template, $template['component_template']['template']);
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        return $this->renderAbstract($request, 'Modules/component_template/component_template_read_mappings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/component-templates/{name}/update", name="component_templates_update")
     */
    public function update(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template['component_templates'][0];
        $template = array_merge($template, $template['component_template']['template']);
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        if (true == $template['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $templateModel = new ElasticsearchComponentTemplateModel();
        $templateModel->convert($template);
        $form = $this->createForm(CreateComponentTemplateType::class, $templateModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $templateModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_component_template/'.$templateModel->getName());
                $callRequest->setBody(json_encode($json, JSON_FORCE_OBJECT));
                $callResponse = $this->callManager->call($callRequest);

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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template['component_templates'][0];
        $template = array_merge($template, $template['component_template']['template']);
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

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
