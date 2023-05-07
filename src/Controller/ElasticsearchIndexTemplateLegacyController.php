<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchIndexTemplateLegacyType;
use App\Form\Type\ElasticsearchTemplateFilterType;
use App\Manager\ElasticsearchIndexTemplateLegacyManager;
use App\Model\ElasticsearchIndexTemplateLegacyModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class ElasticsearchIndexTemplateLegacyController extends AbstractAppController
{
    private ElasticsearchIndexTemplateLegacyManager $elasticsearchIndexTemplateLegacyManager;

    public function __construct(ElasticsearchIndexTemplateLegacyManager $elasticsearchIndexTemplateLegacyManager)
    {
        $this->elasticsearchIndexTemplateLegacyManager = $elasticsearchIndexTemplateLegacyManager;
    }

    #[Route('/index-templates-legacy', name: 'index_templates_legacy')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES_LEGACY_LIST', 'index_template_legacy');

        $form = $this->createForm(ElasticsearchTemplateFilterType::class, null, ['context' => 'index_template_legacy']);

        $form->handleRequest($request);

        $templates = $this->elasticsearchIndexTemplateLegacyManager->getAll([
            'name' => $form->get('name')->getData(),
            'system' => $form->has('system') ? $form->get('system')->getData() : false,
        ]);

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_index.html.twig', [
            'templates' => $this->paginatorManager->paginate([
                'route' => 'index_templates_legacy',
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

    #[Route('/index-templates-legacy/create', name: 'index_templates_legacy_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES_LEGACY_CREATE', 'index_template_legacy');

        $template = null;

        if ($request->query->get('template')) {
            $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($request->query->get('template'));

            if (null === $template) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('INDEX_TEMPLATE_LEGACY_COPY', $template);

            $template->setName($template->getName().'-copy');
        }

        if (null === $template) {
            $template = new ElasticsearchIndexTemplateLegacyModel();
        }
        $form = $this->createForm(ElasticsearchIndexTemplateLegacyType::class, $template);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchIndexTemplateLegacyManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_legacy_read', ['name' => $template->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/index-templates-legacy/{name}', name: 'index_templates_legacy_read')]
    public function read(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES_LEGACY_LIST', 'index_template_legacy');

        $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_read.html.twig', [
            'template' => $template,
        ]);
    }

    #[Route('/index-templates-legacy/{name}/settings', name: 'index_templates_legacy_read_settings')]
    public function settings(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES_LEGACY_LIST', 'index_template_legacy');

        $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_read_settings.html.twig', [
            'template' => $template,
        ]);
    }

    #[Route('/index-templates-legacy/{name}/mappings', name: 'index_templates_legacy_read_mappings')]
    public function mappings(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('INDEX_TEMPLATES_LEGACY_LIST', 'index_template_legacy');

        $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_read_mappings.html.twig', [
            'template' => $template,
        ]);
    }

    #[Route('/index-templates-legacy/{name}/update', name: 'index_templates_legacy_update')]
    public function update(Request $request, string $name): Response
    {
        $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_TEMPLATE_LEGACY_UPDATE', $template);

        $form = $this->createForm(ElasticsearchIndexTemplateLegacyType::class, $template, ['context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchIndexTemplateLegacyManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_legacy_read', ['name' => $template->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index_template_legacy/index_template_legacy_update.html.twig', [
            'template' => $template,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/index-templates-legacy/{name}/delete', name: 'index_templates_legacy_delete')]
    public function delete(Request $request, string $name): Response
    {
        $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($name);

        if (null === $template) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_TEMPLATE_LEGACY_DELETE', $template);

        $callResponse = $this->elasticsearchIndexTemplateLegacyManager->deleteByName($template->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('index_templates_legacy');
    }
}
