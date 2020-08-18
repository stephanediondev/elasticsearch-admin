<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchDanglingIndicesController extends AbstractAppController
{
    public function __construct(ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/dangling-indices", name="dangling_indices")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('DANGLING_INDICES', 'global');

        if (false === $this->callManager->hasFeature('dangling_indices')) {
            throw new AccessDeniedException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_dangling');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if (true === isset($results['dangling_indices'])) {
            $indices = $results['dangling_indices'];
        } else {
            $indices = [];
        }

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        return $this->renderAbstract($request, 'Modules/dangling_indices/dangling_indices_index.html.twig', [
            'indices' => $this->paginatorManager->paginate([
                'route' => 'indices',
                'route_parameters' => [],
                'total' => count($indices),
                'rows' => $indices,
                'page' => 1,
                'size' => count($indices),
            ]),
            'nodes' => $nodes,
        ]);
    }

    /**
     * @Route("/dangling-indices/{index}/import", name="dangling_indices_import")
     */
    public function import(Request $request, string $index): Response
    {
        $this->denyAccessUnlessGranted('DANGLING_INDICES_IMPORT', 'global');

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_dangling/'.$index);
            $callRequest->setQuery(['accept_data_loss' => 'true']);
            $callResponse = $this->callManager->call($callRequest);

            $content = $callResponse->getContent();

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('dangling_indices');
    }

    /**
     * @Route("/dangling-indices/{index}/delete", name="dangling_indices_delete")
     */
    public function delete(Request $request, string $index): Response
    {
        $this->denyAccessUnlessGranted('DANGLING_INDICES_DELETE', 'global');

        try {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('DELETE');
            $callRequest->setPath('/_dangling/'.$index);
            $callRequest->setQuery(['accept_data_loss' => 'true']);
            $callResponse = $this->callManager->call($callRequest);

            $content = $callResponse->getContent();

            $this->addFlash('info', json_encode($content));
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('dangling_indices');
    }
}
