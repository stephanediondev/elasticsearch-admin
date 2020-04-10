<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateEnrichPolicyType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/admin")
 */
class EnrichController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
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
                dump($policyModel->getEnrichFields());
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

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/enrich/enrich_read.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/enrich/{name}/history", name="enrich_read_history")
     */
    public function readHistory(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/enrich/enrich_read_history.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/enrich/{name}/stats", name="enrich_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        return $this->renderAbstract($request, 'Modules/enrich/enrich_read_stats.html.twig', [
            'policy' => $policy,
        ]);
    }

    /**
     * @Route("/enrich/{name}/update", name="enrich_update")
     */
    public function update(Request $request, string $name): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            throw new NotFoundHttpException();
        }

        $policy = $callResponse->getContent();
        $policy = $policy[$name];
        $policy['name'] = $name;

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $indices = $this->elasticsearchIndexManager->selectIndices();

        $policyModel = new ElasticsearchEnrichPolicyModel();
        $policyModel->convert($policy);
        $form = $this->createForm(CreateEnrichPolicyType::class, $policyModel, ['repositories' => $repositories, 'indices' => $indices, 'update' => true]);

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
                $callRequest->setPath('/_enrich/policy/'.$policyModel->getName());
                $callRequest->setJson($json);
                $this->callManager->call($callRequest);

                $this->addFlash('success', 'success.enrich_update');

                return $this->redirectToRoute('enrich_read', ['name' => $policyModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/enrich/enrich_update.html.twig', [
            'policy' => $policy,
            'form' => $form->createView(),
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
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_enrich/policy/'.$name.'/_execute');
        $this->callManager->call($callRequest);

        $this->addFlash('success', 'success.enrich_execute');

        return $this->redirectToRoute('enrich_read', ['name' => $name]);
    }
}
