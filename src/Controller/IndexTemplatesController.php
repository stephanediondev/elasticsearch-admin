<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIndexTemplateType;
use App\Model\CallModel;
use App\Model\ElasticsearchIndexTemplateModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IndexTemplatesController extends AbstractAppController
{
    /**
     * @Route("/index-templates", name="index_templates")
     */
    public function index(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_cat/templates');
        //$call->setQuery(['s' => 'index', 'h' => 'index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string']);
        $indexTemplates = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/index_templates/index_templates_index.html.twig', [
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
        $template = new ElasticsearchIndexTemplateModel();
        $form = $this->createForm(CreateIndexTemplateType::class, $template);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $body = [
                    'index_patterns' => $template->getIndexToArray(),
                ];
                if ($template->getVersion()) {
                    $body['version'] = $template->getVersion();
                }
                if ($template->getOrder()) {
                    $body['order'] = $template->getOrder();
                }
                if ($template->getSettings()) {
                    $body['settings'] = json_decode($template->getSettings(), true);
                }
                if ($template->getMappings()) {
                    $body['mappings'] = json_decode($template->getMappings(), true);
                }
                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_template/'.$template->getName());
                $call->setBody($body);
                $this->callManager->call($call);

                $this->addFlash('success', 'index_templates_create');

                return $this->redirectToRoute('index_templates_read', ['name' => $template->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_templates/index_templates_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/index-templates/{name}", name="index_templates_read")
     */
    public function read(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_template/'.$name);
        $template = $this->callManager->call($call);
        $template = $template[$name];
        $template['name'] = $name;

        if ($template) {
            return $this->renderAbstract($request, 'Modules/index_templates/index_templates_read.html.twig', [
                'template' => $template,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/index-templates/{name}/settings", name="index_templates_read_settings")
     */
    public function settings(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_template/'.$name);
        $template = $this->callManager->call($call);
        $template = $template[$name];
        $template['name'] = $name;

        if ($template) {
            return $this->renderAbstract($request, 'Modules/index_templates/index_templates_read_settings.html.twig', [
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
        $call = new CallModel();
        $call->setPath('/_template/'.$name);
        $template = $this->callManager->call($call);
        $template = $template[$name];
        $template['name'] = $name;

        if ($template) {
            return $this->renderAbstract($request, 'Modules/index_templates/index_templates_read_mappings.html.twig', [
                'template' => $template,
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
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_template/'.$name);
        $this->callManager->call($call);

        $this->addFlash('success', 'index_templates_delete');

        return $this->redirectToRoute('index_templates', []);
    }
}
