<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIlmPolicyType;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIlmPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class IlmController extends AbstractAppController
{
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
                'route' => 'policies',
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
        $status = $callResponse->getContent();

        $this->addFlash('success', 'flash_success.ilm_start');

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
        $status = $callResponse->getContent();

        $this->addFlash('success', 'flash_success.ilm_stop');

        return $this->redirectToRoute('ilm_status');
    }

    /**
     * @Route("/ilm/create", name="ilm_create")
     */
    public function create(Request $request): Response
    {
        $policyModel = new ElasticsearchIlmPolicyModel();
        $form = $this->createForm(CreateIlmPolicyType::class, $policyModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'policy' => [
                        'phases' => [],
                    ],
                ];
                if ($policyModel->getHot()) {
                    $json['policy']['phases']['hot'] = json_decode($policyModel->getHot(), true);
                }
                if ($policyModel->getWarm()) {
                    $json['policy']['phases']['warm'] = json_decode($policyModel->getWarm(), true);
                }
                if ($policyModel->getCold()) {
                    $json['policy']['phases']['cold'] = json_decode($policyModel->getCold(), true);
                }
                if ($policyModel->getDelete()) {
                    $json['policy']['phases']['delete'] = json_decode($policyModel->getDelete(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_ilm/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'flash_success.ilm_create');

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
                $json = [
                    'policy' => [
                        'phases' => [],
                    ],
                ];
                if ($policyModel->getHot()) {
                    $json['policy']['phases']['hot'] = json_decode($policyModel->getHot(), true);
                }
                if ($policyModel->getWarm()) {
                    $json['policy']['phases']['warm'] = json_decode($policyModel->getWarm(), true);
                }
                if ($policyModel->getCold()) {
                    $json['policy']['phases']['cold'] = json_decode($policyModel->getCold(), true);
                }
                if ($policyModel->getDelete()) {
                    $json['policy']['phases']['delete'] = json_decode($policyModel->getDelete(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_ilm/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'flash_success.ilm_update');

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
     * @Route("/ilm/{name}/delete", name="ilm_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_ilm/policy/'.$name);
        $this->callManager->call($callRequest);

        $this->addFlash('success', 'flash_success.ilm_delete');

        return $this->redirectToRoute('ilm');
    }
}
