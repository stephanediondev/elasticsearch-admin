<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchDataStreamFilterType;
use App\Manager\ElasticsearchDataStreamManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchDataStreamModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchDataStreamController extends AbstractAppController
{
    public function __construct(ElasticsearchDataStreamManager $elasticsearchDataStreamManager)
    {
        $this->elasticsearchDataStreamManager = $elasticsearchDataStreamManager;
    }

    /**
     * @Route("/data-streams", name="data_streams")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('DATA_STREAMS', 'global');

        if (false === $this->callManager->hasFeature('data_streams')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(ElasticsearchDataStreamFilterType::class);

        $form->handleRequest($request);

        $streams = $this->elasticsearchDataStreamManager->getAll([
            'name' => $form->get('name')->getData(),
            'status' => $form->get('status')->getData(),
        ]);

        return $this->renderAbstract($request, 'Modules/data_stream/data_stream_index.html.twig', [
            'streams' => $this->paginatorManager->paginate([
                'route' => 'streams',
                'route_parameters' => [],
                'total' => count($streams),
                'rows' => $streams,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/data-streams/{name}", name="data_streams_read")
     */
    public function read(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('DATA_STREAMS', 'global');

        if (false === $this->callManager->hasFeature('data_streams')) {
            throw new AccessDeniedException();
        }

        $stream = $this->elasticsearchDataStreamManager->getByName($name);

        if (null === $stream) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/data_stream/data_stream_read.html.twig', [
            'stream' => $stream,
        ]);
    }

    /**
     * @Route("/data-streams/{name}/stats", name="data_streams_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        $this->denyAccessUnlessGranted('DATA_STREAMS', 'global');

        if (false === $this->callManager->hasFeature('data_streams')) {
            throw new AccessDeniedException();
        }

        $stream = $this->elasticsearchDataStreamManager->getByName($name);

        if (null === $stream) {
            throw new NotFoundHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_data_stream/'.$stream->getName().'/_stats');
        $callResponse = $this->callManager->call($callRequest);
        $stats = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/data_stream/data_stream_read_stats.html.twig', [
            'stream' => $stream,
            'stats' => $stats,
        ]);
    }

    /**
     * @Route("/data-streams/{name}/delete", name="data_streams_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        if (false === $this->callManager->hasFeature('data_streams')) {
            throw new AccessDeniedException();
        }

        $stream = $this->elasticsearchDataStreamManager->getByName($name);

        if (null === $stream) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('DATA_STREAM_DELETE', $stream);

        $callResponse = $this->elasticsearchDataStreamManager->deleteByName($stream->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('data_streams');
    }
}
