<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIndexTemplateLegacyType;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateLegacyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class IndexTemplateLegacyController extends AbstractAppController
{
    /**
     * @Route("/index-templates-legacy", name="index_templates_legacy")
     */
    public function index(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template');
        $callResponse = $this->callManager->call($callRequest);
        $indexTemplates = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_index.html.twig', [
            'indexTemplates' => $this->paginatorManager->paginate([
                'route' => 'indexTemplates',
                'route_parameters' => [],
                'total' => count($indexTemplates),
                'rows' => $indexTemplates,
                'page' => 1,
                'size' => count($indexTemplates),
            ]),
        ]);
    }

    /**
     * @Route("/index-templates-legacy/create", name="index_templates_legacy_create")
     */
    public function create(Request $request): Response
    {
        $template = false;

        if ($request->query->get('template')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_template/'.$request->query->get('template'));
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                throw new NotFoundHttpException();
            }

            $template = $callResponse->getContent();
            $template = $template[$request->query->get('template')];
            $template['name'] = $request->query->get('template').'-copy';
            $template['is_system'] = '.' == substr($template['name'], 0, 1);

            if (true == $template['is_system']) {
                throw new AccessDeniedHttpException();
            }
        }

        $templateModel = new ElasticsearchIndexTemplateLegacyModel();
        if ($template) {
            $templateModel->convert($template);
        }
        $form = $this->createForm(CreateIndexTemplateLegacyType::class, $templateModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $templateModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_template/'.$templateModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_legacy_read', ['name' => $templateModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index-templates-legacy/{name}", name="index_templates_legacy_read")
     */
    public function read(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template[$name];
        $template['name'] = $name;
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_read.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/index-templates-legacy/{name}/settings", name="index_templates_legacy_read_settings")
     */
    public function settings(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template[$name];
        $template['name'] = $name;
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_read_settings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/index-templates-legacy/{name}/mappings", name="index_templates_legacy_read_mappings")
     */
    public function mappings(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template[$name];
        $template['name'] = $name;
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_read_mappings.html.twig', [
            'template' => $template,
        ]);
    }

    /**
     * @Route("/index-templates-legacy/{name}/update", name="index_templates_legacy_update")
     */
    public function update(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template[$name];
        $template['name'] = $name;
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        if (true == $template['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $templateModel = new ElasticsearchIndexTemplateLegacyModel();
        $templateModel->convert($template);
        $form = $this->createForm(CreateIndexTemplateLegacyType::class, $templateModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $templateModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_template/'.$templateModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_legacy_read', ['name' => $templateModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_update.html.twig', [
            'template' => $template,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index-templates-legacy/{name}/delete", name="index_templates_legacy_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $template = $callResponse->getContent();
        $template = $template[$name];
        $template['name'] = $name;
        $template['is_system'] = '.' == substr($template['name'], 0, 1);

        if (true == $template['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('index_templates_legacy');
    }
}
