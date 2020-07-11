<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIlmPolicyType;
use App\Form\ApplyIlmPolicyType;
use App\Manager\ElasticsearchIlmPolicyManager;
use App\Manager\ElasticsearchIndexTemplateLegacyManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateLegacyModel;
use App\Model\ElasticsearchIlmPolicyModel;
use App\Model\ElasticsearchApplyIlmPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class IlmController extends AbstractAppController
{
    public function __construct(ElasticsearchIlmPolicyManager $elasticsearchIlmPolicyManager, ElasticsearchIndexTemplateLegacyManager $elasticsearchIndexTemplateLegacyManager)
    {
        $this->elasticsearchIlmPolicyManager = $elasticsearchIlmPolicyManager;
        $this->elasticsearchIndexTemplateLegacyManager = $elasticsearchIndexTemplateLegacyManager;
    }

    /**
     * @Route("/ilm", name="ilm")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ILM_POLICIES', 'global');

        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $policies = $this->elasticsearchIlmPolicyManager->getAll();

        return $this->renderAbstract($request, 'Modules/ilm/ilm_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'ilm',
                'route_parameters' => [],
                'total' => count($policies),
                'rows' => $policies,
                'page' => 1,
                'size' => count($policies),
            ]),
        ]);
    }

    /**
     * @Route("/ilm/status", name="ilm_status")
     */
    public function status(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ILM_POLICIES_STATUS', 'global');

        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/status');
        $callResponse = $this->callManager->call($callRequest);
        $status = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/ilm/ilm_status.html.twig', [
            'status' => $status,
        ]);
    }

    /**
     * @Route("/ilm/start", name="ilm_start")
     */
    public function start(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ILM_POLICIES_STATUS', 'global');

        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_ilm/start');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('ilm_status');
    }

    /**
     * @Route("/ilm/stop", name="ilm_stop")
     */
    public function stop(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ILM_POLICIES_STATUS', 'global');

        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_ilm/stop');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('ilm_status');
    }

    /**
     * @Route("/ilm/create", name="ilm_create")
     */
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ILM_POLICIES_CREATE', 'global');

        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = false;

        if ($request->query->get('policy')) {
            $policy = $this->elasticsearchIlmPolicyManager->getByName($request->query->get('policy'));

            if (false == $policy) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('ILM_POLICY_COPY', $policy);

            $policy->setName($policy->getName().'-copy');
        }

        if (false == $policy) {
            $policy = new ElasticsearchIlmPolicyModel();
        }
        $form = $this->createForm(CreateIlmPolicyType::class, $policy);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchIlmPolicyManager->send($policy);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('ilm_read', ['name' => $policy->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/ilm/ilm_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ilm/{name}", name="ilm_read")
     */
    public function read(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('ILM_POLICIES', 'global');

        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchIlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/ilm/ilm_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/ilm/{name}/update", name="ilm_update")
     */
    public function update(Request $request, string $name): Response
    {
        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchIlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ILM_POLICY_UPDATE', $policy);

        $form = $this->createForm(CreateIlmPolicyType::class, $policy, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchIlmPolicyManager->send($policy);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('ilm_read', ['name' => $policy->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/ilm/ilm_update.html.twig', [
            'policy' => $policy,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ilm/{name}/apply", name="ilm_apply")
     */
    public function apply(Request $request, string $name): Response
    {
        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchIlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ILM_POLICY_APPLY', $policy);

        $results = $this->elasticsearchIndexTemplateLegacyManager->getAll();

        $indexTemplates = [];
        foreach ($results as $row) {
            $indexTemplates[] = $row->getName();
        }

        $applyPolicyModel = new ElasticsearchApplyIlmPolicyModel();
        $form = $this->createForm(ApplyIlmPolicyType::class, $applyPolicyModel, ['index_templates' => $indexTemplates]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $template = $this->elasticsearchIndexTemplateLegacyManager->getByName($applyPolicyModel->getIndexTemplate());

                if (false == $template) {
                    throw new NotFoundHttpException();
                }

                if (true == $template->isSystem()) {
                    throw new AccessDeniedHttpException();
                }

                $template->setSetting('index.lifecycle.name', $policy->getName());
                $template->setSetting('index.lifecycle.rollover_alias', $applyPolicyModel->getRolloverAlias());

                $callResponse = $this->elasticsearchIndexTemplateLegacyManager->send($template);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('index_templates_legacy_read', ['name' => $applyPolicyModel->getIndexTemplate()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/ilm/ilm_apply.html.twig', [
            'policy' => $policy,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ilm/{name}/delete", name="ilm_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        if (false == $this->hasFeature('ilm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchIlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ILM_POLICY_DELETE', $policy);

        $callResponse = $this->elasticsearchIlmPolicyManager->deleteByName($policy->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('ilm');
    }
}
