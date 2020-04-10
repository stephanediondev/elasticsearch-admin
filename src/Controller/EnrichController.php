<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateEnrichPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Message\EnrichExecuteMessage;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @Route("/admin")
 */
class EnrichController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager, MessageBusInterface $bus)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
        $this->bus = $bus;
    }

    /**
     * @Route("/enrich", name="enrich")
     */
    public function index(Request $request): Response
    {
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
     * @Route("/enrich/stats", name="enrich_stats")
     */
    public function stats(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/_stats');
        $callResponse = $this->callManager->call($callRequest);
        $stats = $callResponse->getContent();

        $stats = [];//TODO

        return $this->renderAbstract($request, 'Modules/enrich/enrich_stats.html.twig', [
            'stats' => $stats,
        ]);
    }

    /**
     * @Route("/enrich/create", name="enrich_create")
     */
    public function create(Request $request): Response
    {
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policyModel = new ElasticsearchEnrichPolicyModel();
        $form = $this->createForm(CreateEnrichPolicyType::class, $policyModel, ['indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [
                    $policyModel->getType() => [
                        'indices' => $policyModel->getIndices(),
                        'match_field' => $policyModel->getMatchField(),
                        'enrich_fields' => $policyModel->getEnrichFields(),
                    ],
                ];
                if ($policyModel->getQuery()) {
                    $json[$policyModel->getType()]['query'] = $policyModel->getQuery();
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/_enrich/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'success.enrich_create');

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
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

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

        return $this->renderAbstract($request, 'Modules/enrich/enrich_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/enrich/{name}/delete", name="enrich_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_enrich/policy/'.$name);
        $this->callManager->call($callRequest);

        $this->addFlash('success', 'success.enrich_delete');

        return $this->redirectToRoute('enrich', []);
    }

    /**
     * @Route("/enrich/{name}/exexute", name="enrich_execute")
     */
    public function exexute(Request $request, string $name): Response
    {
        /*$callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_enrich/policy/'.$name.'/_execute');
        $this->callManager->call($callRequest);*/

        $message = new EnrichExecuteMessage($name);
        $this->bus->dispatch($message);

        $this->addFlash('success', 'success.enrich_execute');

        return $this->redirectToRoute('enrich_read', ['name' => $name]);
    }
}
