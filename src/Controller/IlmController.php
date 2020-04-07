<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateIlmPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
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
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
    }

    /**
     * @Route("/ilm", name="ilm")
     */
    public function index(Request $request): Response
    {
        $policies = [];

        $call = new CallRequestModel();
        $call->setPath('/_ilm/policy');
        $rows = $this->callManager->call($call);

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
        $call = new CallRequestModel();
        $call->setPath('/_ilm/status');
        $status = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/ilm/ilm_status.html.twig', [
            'status' => $status,
        ]);
    }

    /**
     * @Route("/ilm/start", name="ilm_start")
     */
    public function start(Request $request): Response
    {
        $call = new CallRequestModel();
        $call->setMethod('POST');
        $call->setPath('/_ilm/start');
        $status = $this->callManager->call($call);

        $this->addFlash('success', 'success.ilm_start');

        return $this->redirectToRoute('ilm_status', []);
    }

    /**
     * @Route("/ilm/stop", name="ilm_stop")
     */
    public function stop(Request $request): Response
    {
        $call = new CallRequestModel();
        $call->setMethod('POST');
        $call->setPath('/_ilm/stop');
        $status = $this->callManager->call($call);

        $this->addFlash('success', 'success.ilm_stop');

        return $this->redirectToRoute('ilm_status', []);
    }

    /**
     * @Route("/ilm/{name}", name="ilm_read")
     */
    public function read(Request $request, string $name): Response
    {
        try {
            $call = new CallRequestModel();
            $call->setPath('/_ilm/policy/'.$name);
            $policy = $this->callManager->call($call);
            $policy = $policy[$name];
            $policy['name'] = $name;

            return $this->renderAbstract($request, 'Modules/ilm/ilm_read.html.twig', [
                'policy' => $policy,
            ]);
        } catch (CallException $e) {
            throw new NotFoundHttpException();
        }
    }
}
