<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSlmPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallModel;
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
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
    }

    /**
     * @Route("/slm", name="slm")
     */
    public function index(Request $request): Response
    {
        $policies = [];

        $call = new CallModel();
        $call->setPath('/_slm/policy');
        $rows = $this->callManager->call($call);

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
        $call = new CallModel();
        $call->setPath('/_slm/stats');
        $stats = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/slm/slm_stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * @Route("/slm/status", name="slm_status")
     */
    public function status(Request $request): Response
    {
        $call = new CallModel();
        $call->setPath('/_slm/status');
        $status = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/slm/slm_status.html.twig', [
            'status' => $status,
        ]);
    }

    /**
     * @Route("/slm/start", name="slm_start")
     */
    public function start(Request $request): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_slm/start');
        $status = $this->callManager->call($call);

        $this->addFlash('success', 'success.slm_start');

        return $this->redirectToRoute('slm_status', []);
    }

    /**
     * @Route("/slm/stop", name="slm_stop")
     */
    public function stop(Request $request): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_slm/stop');
        $status = $this->callManager->call($call);

        $this->addFlash('success', 'success.slm_stop');

        return $this->redirectToRoute('slm_status', []);
    }

    /**
     * @Route("/slm/create", name="slm_create")
     */
    public function create(Request $request): Response
    {
        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policyModel = new ElasticsearchSlmPolicyModel();
        if ($request->query->get('repository')) {
            $policyModel->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $policyModel->setIndices([$request->query->get('index')]);
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

                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_slm/policy/'.$policyModel->getName());
                $call->setJson($json);
                $this->callManager->call($call);

                $this->addFlash('success', 'success.slm_create');

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
        try {
            $call = new CallModel();
            $call->setPath('/_slm/policy/'.$name);
            $policy = $this->callManager->call($call);
            $policy = $policy[$name];
            $policy['name'] = $name;

            return $this->renderAbstract($request, 'Modules/slm/slm_read.html.twig', [
                'policy' => $policy,
            ]);
        } catch (CallException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function readHistory(Request $request, string $name): Response
    {
        try {
            $call = new CallModel();
            $call->setPath('/_slm/policy/'.$name);
            $policy = $this->callManager->call($call);
            $policy = $policy[$name];
            $policy['name'] = $name;

            return $this->renderAbstract($request, 'Modules/slm/slm_read_history.html.twig', [
                'policy' => $policy,
            ]);
        } catch (CallException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/stats", name="slm_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        try {
            $call = new CallModel();
            $call->setPath('/_slm/policy/'.$name);
            $policy = $this->callManager->call($call);
            $policy = $policy[$name];
            $policy['name'] = $name;

            return $this->renderAbstract($request, 'Modules/slm/slm_read_stats.html.twig', [
                'policy' => $policy,
            ]);
        } catch (CallException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/update", name="slm_update")
     */
    public function update(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_slm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
            $indices = $this->elasticsearchIndexManager->selectIndices();

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

                    $call = new CallModel();
                    $call->setMethod('PUT');
                    $call->setPath('/_slm/policy/'.$policyModel->getName());
                    $call->setJson($json);
                    $this->callManager->call($call);

                    $this->addFlash('success', 'success.slm_update');

                    return $this->redirectToRoute('slm_read', ['name' => $policyModel->getName()]);
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            return $this->renderAbstract($request, 'Modules/slm/slm_update.html.twig', [
                'policy' => $policy,
                'form' => $form->createView(),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/delete", name="slm_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_slm/policy/'.$name);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.slm_delete');

        return $this->redirectToRoute('slm', []);
    }

    /**
     * @Route("/slm/{name}/exexute", name="slm_execute")
     */
    public function exexute(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_slm/policy/'.$name.'/_execute');
        $this->callManager->call($call);

        $this->addFlash('success', 'success.slm_execute');

        return $this->redirectToRoute('slm_read', ['name' => $name]);
    }
}
