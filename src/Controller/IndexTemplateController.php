<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIndexTemplateType;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class IndexTemplateController extends AbstractAppController
{
    /**
     * @Route("/index-templates", name="index_templates")
     */
    public function index(Request $request): Response
    {
        $call = new CallRequestModel();
        $call->setPath('/_cat/templates');
        $call->setQuery(['s' => 'name', 'h' => 'name,index_patterns,order,version']);
        $indexTemplates = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/index_template/index_template_index.html.twig', [
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
     * @Route("/index-templates/create", name="index_templates_create")
     */
    public function create(Request $request): Response
    {
        $templateModel = new ElasticsearchIndexTemplateModel();
        $form = $this->createForm(CreateIndexTemplateType::class, $templateModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'index_patterns' => $templateModel->getIndexToArray(),
                ];
                if ($templateModel->getVersion()) {
                    $json['version'] = $templateModel->getVersion();
                }
                if ($templateModel->getOrder()) {
                    $json['order'] = $templateModel->getOrder();
                }
                if ($templateModel->getSettings()) {
                    $json['settings'] = json_decode($templateModel->getSettings(), true);
                }
                if ($templateModel->getMappings()) {
                    $json['mappings'] = json_decode($templateModel->getMappings(), true);
                }
                $call = new CallRequestModel();
                $call->setMethod('PUT');
                $call->setPath('/_template/'.$templateModel->getName());
                $call->setJson($json);
                $this->callManager->call($call);

                $this->addFlash('success', 'success.index_templates_create');

                return $this->redirectToRoute('index_templates_read', ['name' => $templateModel->getName()]);
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
        try {
            $call = new CallRequestModel();
            $call->setPath('/_template/'.$name);
            $template = $this->callManager->call($call);
            $template = $template[$name];
            $template['name'] = $name;

            return $this->renderAbstract($request, 'Modules/index_template/index_template_read.html.twig', [
                'template' => $template,
            ]);
        } catch (CallException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/index-templates/{name}/settings", name="index_templates_read_settings")
     */
    public function settings(Request $request, string $name): Response
    {
        $call = new CallRequestModel();
        $call->setPath('/_template/'.$name);
        $template = $this->callManager->call($call);
        $template = $template[$name];
        $template['name'] = $name;

        if ($template) {
            return $this->renderAbstract($request, 'Modules/index_template/index_template_read_settings.html.twig', [
                'template' => $template,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/index-templates/{name}/mappings", name="index_templates_read_mappings")
     */
    public function mappings(Request $request, string $name): Response
    {
        $call = new CallRequestModel();
        $call->setPath('/_template/'.$name);
        $template = $this->callManager->call($call);
        $template = $template[$name];
        $template['name'] = $name;

        if ($template) {
            return $this->renderAbstract($request, 'Modules/index_template/index_template_read_mappings.html.twig', [
                'template' => $template,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/index-templates/{name}/update", name="index_templates_update")
     */
    public function update(Request $request, string $name): Response
    {
        $call = new CallRequestModel();
        $call->setPath('/_template/'.$name);
        $template = $this->callManager->call($call);
        $template = $template[$name];
        $template['name'] = $name;

        if ($template) {
            $templateModel = new ElasticsearchIndexTemplateModel();
            $templateModel->convert($template);
            $form = $this->createForm(CreateIndexTemplateType::class, $templateModel, ['update' => true]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $json = [
                        'index_patterns' => $templateModel->getIndexToArray(),
                    ];
                    if ($templateModel->getVersion()) {
                        $json['version'] = $templateModel->getVersion();
                    }
                    if ($templateModel->getOrder()) {
                        $json['order'] = $templateModel->getOrder();
                    }
                    if ($templateModel->getSettings()) {
                        $json['settings'] = json_decode($templateModel->getSettings(), true);
                    }
                    if ($templateModel->getMappings()) {
                        $json['mappings'] = json_decode($templateModel->getMappings(), true);
                    }
                    $call = new CallRequestModel();
                    $call->setMethod('PUT');
                    $call->setPath('/_template/'.$templateModel->getName());
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'success.index_templates_update');

                    return $this->redirectToRoute('index_templates_read', ['name' => $templateModel->getName()]);
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/index_template/index_template_update.html.twig', [
                'template' => $template,
                'form' => $form->createView(),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }


    /**
     * @Route("/index-templates/{name}/delete", name="index_templates_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $call = new CallRequestModel();
        $call->setMethod('DELETE');
        $call->setPath('/_template/'.$name);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.index_templates_delete');

        return $this->redirectToRoute('index_templates', []);
    }
}
