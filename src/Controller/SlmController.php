<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSlmPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchSlmPolicyManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class SlmController extends AbstractAppController
{
    public function __construct(ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchSlmPolicyManager = $elasticsearchSlmPolicyManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

    /**
     * @Route("/slm", name="slm")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policies = $this->elasticsearchSlmPolicyManager->getAll();

        return $this->renderAbstract($request, 'Modules/slm/slm_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'slm',
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
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATS');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

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
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATUS');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

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
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATUS');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

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
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATUS');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

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
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_CREATE');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policy = false;

        if ($request->query->get('policy')) {
            $policy = $this->elasticsearchSlmPolicyManager->getByName($request->query->get('policy'));

            if (false == $policy) {
                throw new NotFoundHttpException();
            }

            $policy->setName($policy->getName().'-copy');
        }

        if (false == $policy) {
            $policy = new ElasticsearchSlmPolicyModel();
        }
        if ($request->query->get('repository')) {
            $policy->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $policy->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(CreateSlmPolicyType::class, $policy, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchSlmPolicyManager->send($policy);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('slm_read', ['name' => $policy->getName()]);
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
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function readHistory(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_read_history.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/slm/{name}/stats", name="slm_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_read_stats.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/slm/{name}/update", name="slm_update")
     */
    public function update(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $form = $this->createForm(CreateSlmPolicyType::class, $policy, ['repositories' => $repositories, 'indices' => $indices, 'update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchSlmPolicyManager->send($policy);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('slm_read', ['name' => $policy->getName()]);
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
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        $callResponse = $this->elasticsearchSlmPolicyManager->deleteByName($policy->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm');
    }

    /**
     * @Route("/slm/{name}/execute", name="slm_execute")
     */
    public function execute(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES');

        if (false == $this->hasFeature('slm')) {
            throw new AccessDeniedHttpException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (false == $policy) {
            throw new NotFoundHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/policy/'.$name.'/_execute');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_read', ['name' => $name]);
    }
}
