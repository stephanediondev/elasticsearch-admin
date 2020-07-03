<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateEnrichPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class EnrichController extends AbstractAppController
{
    /**
     * @Route("/enrich", name="enrich")
     */
    public function index(Request $request): Response
    {
        if (false == $this->hasFeature('enrich')) {
            throw new AccessDeniedHttpException();
        }

        $policies = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows['policies'] as $row) {
            $policy = [];
            $policy['type'] = key($row['config']);
            $policy['name'] = $row['config'][$policy['type']]['name'];
            $policy['indices'] = $row['config'][$policy['type']]['indices'];
            $policy['match_field'] = $row['config'][$policy['type']]['match_field'];
            $policy['enrich_fields'] = $row['config'][$policy['type']]['enrich_fields'];
            $policy['query'] = $row['config'][$policy['type']]['query'] ?? false;
            $policies[] = $policy;
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'enrich',
                'route_parameters' => [],
                'total' => count($policies),
                'rows' => $policies,
                'page' => 1,
                'size' => count($policies),
            ]),
        ]);
    }

    /**
     * @Route("/enrich/stats", name="enrich_stats")
     */
    public function stats(Request $request): Response
    {
        if (false == $this->hasFeature('enrich')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/_stats');
        $callResponse = $this->callManager->call($callRequest);
        $stats = $callResponse->getContent();

        $nodes = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_nodes');
        $callResponse = $this->callManager->call($callRequest);
        $rows = $callResponse->getContent();

        foreach ($rows['nodes'] as $k => $row) {
            $nodes[$k] = $row['name'];
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_stats.html.twig', [
            'stats' => $stats,
            'nodes' => $nodes,
        ]);
    }

    /**
     * @Route("/enrich/create", name="enrich_create")
     */
    public function create(Request $request, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        if (false == $this->hasFeature('enrich')) {
            throw new AccessDeniedHttpException();
        }

        $indices = $elasticsearchIndexManager->selectIndices();

        $policy = false;

        if ($request->query->get('policy')) {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_enrich/policy/'.$request->query->get('policy'));
            $callResponse = $this->callManager->call($callRequest);

            $rows = $callResponse->getContent();

            foreach ($rows['policies'] as $row) {
                $policy = [];
                $policy['type'] = key($row['config']);
                $policy['name'] = $row['config'][$policy['type']]['name'];
                $policy['indices'] = $row['config'][$policy['type']]['indices'];
                $policy['match_field'] = $row['config'][$policy['type']]['match_field'];
                $policy['enrich_fields'] = $row['config'][$policy['type']]['enrich_fields'];
                $policy['query'] = $row['config'][$policy['type']]['query'] ?? false;
            }
        }

        $policyModel = new ElasticsearchEnrichPolicyModel();
        if ($policy) {
            $policyModel->convert($policy);
        }
        $form = $this->createForm(CreateEnrichPolicyType::class, $policyModel, ['indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $policyModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_enrich/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('enrich_read', ['name' => $policyModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/enrich/{name}", name="enrich_read")
     */
    public function read(Request $request, string $name): Response
    {
        if (false == $this->hasFeature('enrich')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $rows = $callResponse->getContent();

        if (false == isset($rows['policies']) || 0 == count($rows['policies'])) {
            throw new NotFoundHttpException();
        }

        foreach ($rows['policies'] as $row) {
            $policy = [];
            $policy['type'] = key($row['config']);
            $policy['name'] = $row['config'][$policy['type']]['name'];
            $policy['indices'] = $row['config'][$policy['type']]['indices'];
            $policy['match_field'] = $row['config'][$policy['type']]['match_field'];
            $policy['enrich_fields'] = $row['config'][$policy['type']]['enrich_fields'];
            $policy['query'] = $row['config'][$policy['type']]['query'] ?? false;
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/enrich/{name}/delete", name="enrich_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        if (false == $this->hasFeature('enrich')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('enrich');
    }

    /**
     * @Route("/enrich/{name}/execute", name="enrich_execute")
     */
    public function execute(Request $request, string $name): Response
    {
        if (false == $this->hasFeature('enrich')) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_enrich/policy/'.$name.'/_execute');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('enrich_stats');
    }
}
