<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSlmPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class SlmController extends AbstractAppController
{
    /**
     * @Route("/slm", name="slm")
     */
    public function index(Request $request): Response
    {
        $policies = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows as $k => $row) {
            $row['name'] = $k;
            $policies[] = $row;
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_index.html.twig', [
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
     * @Route("/slm/stats", name="slm_stats")
     */
    public function stats(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/stats');
        $callResponse = $this->callManager->call($callRequest);
        $stats = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/slm/slm_stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * @Route("/slm/status", name="slm_status")
     */
    public function status(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/status');
        $callResponse = $this->callManager->call($callRequest);
        $status = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/slm/slm_status.html.twig', [
            'status' => $status,
        ]);
    }

    /**
     * @Route("/slm/start", name="slm_start")
     */
    public function start(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/start');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_status');
    }

    /**
     * @Route("/slm/stop", name="slm_stop")
     */
    public function stop(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/stop');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_status');
    }

    /**
     * @Route("/slm/create", name="slm_create")
     */
    public function create(Request $request, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $indices = $elasticsearchIndexManager->selectIndices();

        $policy = false;

        if ($request->query->get('policy')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_slm/policy/'.$request->query->get('policy'));
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                throw new NotFoundHttpException();
            }

            $policy = $callResponse->getContent();
            $policy = $policy[$request->query->get('policy')];
            $policy['name'] = $request->query->get('policy').'-copy';
        }

        $policyModel = new ElasticsearchSlmPolicyModel();
        if ($request->query->get('repository')) {
            $policyModel->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $policyModel->setIndices([$request->query->get('index')]);
        }
        if ($policy) {
            $policyModel->convert($policy);
        }
        $form = $this->createForm(CreateSlmPolicyType::class, $policyModel, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'schedule' => $policyModel->getSchedule(),
                    'name' => $policyModel->getSnapshotName(),
                    'repository' => $policyModel->getRepository(),
                ];
                if ($policyModel->getIndices()) {
                    $json['config']['indices'] = $policyModel->getIndices();
                } else {
                    $json['config']['indices'] = ['*'];
                }
                $json['config']['ignore_unavailable'] = $policyModel->getIgnoreUnavailable();
                $json['config']['partial'] = $policyModel->getPartial();
                $json['config']['include_global_state'] = $policyModel->getIncludeGlobalState();

                if ($policyModel->hasRetention()) {
                    $json['retention'] = $policyModel->getRetention();
                }

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_slm/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('slm_read', ['name' => $policyModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/slm/{name}", name="slm_read")
     */
    public function read(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/slm/slm_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function readHistory(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/slm/slm_read_history.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/slm/{name}/stats", name="slm_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/slm/slm_read_stats.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/slm/{name}/update", name="slm_update")
     */
    public function update(Request $request, string $name, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $indices = $elasticsearchIndexManager->selectIndices();

        $policyModel = new ElasticsearchSlmPolicyModel();
        $policyModel->convert($policy);
        $form = $this->createForm(CreateSlmPolicyType::class, $policyModel, ['repositories' => $repositories, 'indices' => $indices, 'update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    'schedule' => $policyModel->getSchedule(),
                    'name' => $policyModel->getSnapshotName(),
                    'repository' => $policyModel->getRepository(),
                ];
                if ($policyModel->getIndices()) {
                    $json['config']['indices'] = $policyModel->getIndices();
                } else {
                    $json['config']['indices'] = ['*'];
                }
                $json['config']['ignore_unavailable'] = $policyModel->getIgnoreUnavailable();
                $json['config']['partial'] = $policyModel->getPartial();
                $json['config']['include_global_state'] = $policyModel->getIncludeGlobalState();

                if ($policyModel->hasRetention()) {
                    $json['retention'] = $policyModel->getRetention();
                }

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_slm/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('slm_read', ['name' => $policyModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_update.html.twig', [
            'policy' => $policy,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/slm/{name}/delete", name="slm_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_slm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm');
    }

    /**
     * @Route("/slm/{name}/execute", name="slm_execute")
     */
    public function execute(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/policy/'.$name.'/_execute');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_read', ['name' => $name]);
    }
}
