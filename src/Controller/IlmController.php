<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIlmPolicyType;
use App\Form\ApplyIlmPolicyType;
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
    public function __construct()
    {
        dump($this->xpack);

        if (false == isset($this->xpack['features']['ilm']['enabled']) || false == $this->xpack['features']['ilm']['enabled']) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * @Route("/ilm", name="ilm")
     */
    public function index(Request $request): Response
    {
        $policies = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/policy');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $row['name'] = $k;
            $policies[] = $row;
        }

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
        $policy = false;

        if ($request->query->get('policy')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_ilm/policy/'.$request->query->get('policy'));
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                throw new NotFoundHttpException();
            }

            $policy = $callResponse->getContent();
            $policy = $policy[$request->query->get('policy')];
            $policy['name'] = $request->query->get('policy').'-copy';
        }

        $policyModel = new ElasticsearchIlmPolicyModel();
        if ($policy) {
            $policyModel->convert($policy);
        }
        $form = $this->createForm(CreateIlmPolicyType::class, $policyModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $policyModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_ilm/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('ilm_read', ['name' => $policyModel->getName()]);
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/ilm/ilm_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/ilm/{name}/update", name="ilm_update")
     */
    public function update(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        $policyModel = new ElasticsearchIlmPolicyModel();
        $policyModel->convert($policy);
        $form = $this->createForm(CreateIlmPolicyType::class, $policyModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $policyModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_ilm/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('ilm_read', ['name' => $policyModel->getName()]);
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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $indexTemplates = [];
        foreach ($results as $name => $row) {
            $indexTemplates[] = $name;
        }

        sort($indexTemplates);

        $applyPolicyModel = new ElasticsearchApplyIlmPolicyModel();
        $form = $this->createForm(ApplyIlmPolicyType::class, $applyPolicyModel, ['index_templates' => $indexTemplates]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callRequest = new CallRequestModel();
                $callRequest->setPath('/_template/'.$applyPolicyModel->getIndexTemplate());
                $callRequest->setQuery(['flat_settings' => 'true']);
                $callResponse = $this->callManager->call($callRequest);

                if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                    throw new NotFoundHttpException();
                }

                $template = $callResponse->getContent();
                $template = $template[$applyPolicyModel->getIndexTemplate()];
                $template['name'] = $applyPolicyModel->getIndexTemplate();
                $template['is_system'] = '.' == substr($template['name'], 0, 1);

                if (true == $template['is_system']) {
                    throw new AccessDeniedHttpException();
                }

                $templateModel = new ElasticsearchIndexTemplateLegacyModel();
                $templateModel->convert($template);

                $settings = json_decode($templateModel->getSettings(), true);
                $settings['index.lifecycle.name'] = $policy['name'];
                $settings['index.lifecycle.rollover_alias'] = $applyPolicyModel->getRolloverAlias();

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
                    $json['settings'] = $settings;
                }
                if ($templateModel->getMappings()) {
                    $json['mappings'] = json_decode($templateModel->getMappings(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_template/'.$templateModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

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
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_ilm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('ilm');
    }
}
