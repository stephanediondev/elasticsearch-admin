<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchSlmPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Manager\ElasticsearchSlmPolicyManager;
use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchSlmController extends AbstractAppController
{
    private ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager;

    private ElasticsearchRepositoryManager $elasticsearchRepositoryManager;

    private ElasticsearchIndexManager $elasticsearchIndexManager;

    public function __construct(ElasticsearchSlmPolicyManager $elasticsearchSlmPolicyManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchSlmPolicyManager = $elasticsearchSlmPolicyManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

    #[Route('/slm', name: 'slm')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_LIST', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policies = $this->elasticsearchSlmPolicyManager->getAll();

        return $this->renderAbstract($request, 'Modules/slm/slm_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'slm',
                'route_parameters' => [],
                'total' => count($policies),
                'rows' => $policies,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
        ]);
    }

    #[Route('/slm/stats', name: 'slm_stats')]
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATS', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $stats = $this->elasticsearchSlmPolicyManager->getStats();

        return $this->renderAbstract($request, 'Modules/slm/slm_stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    #[Route('/slm/status', name: 'slm_status')]
    public function status(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATUS', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $status = $this->elasticsearchSlmPolicyManager->getStatus();

        return $this->renderAbstract($request, 'Modules/slm/slm_status.html.twig', [
            'status' => $status,
        ]);
    }

    #[Route('/slm/start', name: 'slm_start')]
    public function start(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATUS', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $callResponse = $this->elasticsearchSlmPolicyManager->start();

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_status');
    }

    #[Route('/slm/stop', name: 'slm_stop')]
    public function stop(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_STATUS', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $callResponse = $this->elasticsearchSlmPolicyManager->stop();

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_status');
    }

    #[Route('/slm/create', name: 'slm_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_CREATE', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policy = null;

        if ($request->query->get('policy')) {
            $policy = $this->elasticsearchSlmPolicyManager->getByName($request->query->get('policy'));

            if (null === $policy) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('SLM_POLICY_COPY', $policy);

            $policy->setName($policy->getName().'-copy');
        }

        if (null === $policy) {
            $policy = new ElasticsearchSlmPolicyModel();
        }
        if ($request->query->get('repository')) {
            $policy->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $policy->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(ElasticsearchSlmPolicyType::class, $policy, ['repositories' => $repositories, 'indices' => $indices]);

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

    #[Route('/slm/{name}', name: 'slm_read')]
    public function read(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_LIST', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    #[Route('/slm/{name}/history', name: 'slm_read_history')]
    public function readHistory(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_LIST', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_read_history.html.twig', [
            'policy' => $policy,
        ]);
    }

    #[Route('/slm/{name}/stats', name: 'slm_read_stats')]
    public function readStats(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('SLM_POLICIES_LIST', 'slm_policy');

        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_read_stats.html.twig', [
            'policy' => $policy,
        ]);
    }

    #[Route('/slm/{name}/update', name: 'slm_update')]
    public function update(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SLM_POLICY_UPDATE', $policy);

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $form = $this->createForm(ElasticsearchSlmPolicyType::class, $policy, ['repositories' => $repositories, 'indices' => $indices, 'context' => 'update']);

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

    #[Route('/slm/{name}/delete', name: 'slm_delete')]
    public function delete(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SLM_POLICY_DELETE', $policy);

        $callResponse = $this->elasticsearchSlmPolicyManager->deleteByName($policy->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm');
    }

    #[Route('/slm/{name}/execute', name: 'slm_execute')]
    public function execute(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('slm')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchSlmPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('SLM_POLICY_EXECUTE', $policy);

        $callResponse = $this->elasticsearchSlmPolicyManager->executeByName($policy->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('slm_read', ['name' => $name]);
    }
}
