<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchEnrichPolicyType;
use App\Manager\ElasticsearchEnrichPolicyManager;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchEnrichController extends AbstractAppController
{
    private ElasticsearchEnrichPolicyManager $elasticsearchEnrichPolicyManager;

    private ElasticsearchIndexManager $elasticsearchIndexManager;

    private ElasticsearchNodeManager $elasticsearchNodeManager;

    public function __construct(ElasticsearchEnrichPolicyManager $elasticsearchEnrichPolicyManager, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchEnrichPolicyManager = $elasticsearchEnrichPolicyManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    #[Route('/enrich', name: 'enrich')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ENRICH_POLICIES_LIST', 'enrich_policy');

        if (false === $this->callManager->hasFeature('enrich')) {
            throw new AccessDeniedException();
        }

        $policies = $this->elasticsearchEnrichPolicyManager->getAll();

        return $this->renderAbstract($request, 'Modules/enrich/enrich_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'enrich',
                'route_parameters' => [],
                'total' => count($policies),
                'rows' => $policies,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
        ]);
    }

    #[Route('/enrich/stats', name: 'enrich_stats')]
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ENRICH_POLICIES_STATS', 'enrich_policy');

        if (false === $this->callManager->hasFeature('enrich')) {
            throw new AccessDeniedException();
        }

        $stats = $this->elasticsearchEnrichPolicyManager->getStats();

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        return $this->renderAbstract($request, 'Modules/enrich/enrich_stats.html.twig', [
            'stats' => $stats,
            'nodes' => $nodes,
        ]);
    }

    #[Route('/enrich/create', name: 'enrich_create')]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ENRICH_POLICIES_CREATE', 'enrich_policy');

        if (false === $this->callManager->hasFeature('enrich')) {
            throw new AccessDeniedException();
        }

        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policy = null;

        if ($request->query->get('policy')) {
            $policy = $this->elasticsearchEnrichPolicyManager->getByName($request->query->get('policy'));

            if (null === $policy) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted('ENRICH_POLICY_COPY', $policy);

            $policy->setName($policy->getName().'-copy');
        }

        if (null === $policy) {
            $policy = new ElasticsearchEnrichPolicyModel();
        }
        $form = $this->createForm(ElasticsearchEnrichPolicyType::class, $policy, ['indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->elasticsearchEnrichPolicyManager->send($policy);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('enrich_read', ['name' => $policy->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/enrich/{name}', name: 'enrich_read')]
    public function read(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('ENRICH_POLICIES_LIST', 'enrich_policy');

        if (false === $this->callManager->hasFeature('enrich')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchEnrichPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    #[Route('/enrich/{name}/delete', name: 'enrich_delete')]
    public function delete(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('enrich')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchEnrichPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ENRICH_POLICY_DELETE', $policy);

        $callResponse = $this->elasticsearchEnrichPolicyManager->deleteByName($policy->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('enrich');
    }

    #[Route('/enrich/{name}/execute', name: 'enrich_execute')]
    public function execute(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('enrich')) {
            throw new AccessDeniedException();
        }

        $policy = $this->elasticsearchEnrichPolicyManager->getByName($name);

        if (null === $policy) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('ENRICH_POLICY_EXECUTE', $policy);

        $callResponse = $this->elasticsearchEnrichPolicyManager->executeByName($policy->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('enrich_stats');
    }
}
