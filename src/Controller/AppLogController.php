<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\ElasticsearchIndexQueryType;
use App\Model\CallRequestModel;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class AppLogController extends AbstractAppController
{
    /**
     * @Route("/app-logs", name="app_logs")
     */
    public function search(Request $request): Response
    {
        $this->denyAccessUnlessGranted('APP_LOGS', 'global');

        $form = $this->createForm(ElasticsearchIndexQueryType::class);

        $form->handleRequest($request);

        $parameters = [
            'index' => '.elasticsearch-admin-logs',
            'form' => $form->createView(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $size = 100;
            if ($request->query->get('page') && '' != $request->query->get('page')) {
                $page = $request->query->get('page');
            } else {
                $page = 1;
            }
            if ($request->query->get('s') && '' != $request->query->get('s')) {
                $sort = $request->query->get('s');
            } else {
                $sort = 'created_at.keyword:desc';
            }
            $query = [
                'track_scores' => 'true',
                'q' => $form->get('query')->getData(),
                'sort' => $sort,
                'size' => $size,
                'from' => ($size * $page) - $size,
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setLog(false);
            $callRequest->setPath('/.elasticsearch-admin-logs/_search');
            $callRequest->setQuery($query);
            $callResponse = $this->callManager->call($callRequest);
            $documents = $callResponse->getContent();

            if (true == isset($documents['hits']['total']['value'])) {
                $total = $documents['hits']['total']['value'];
                if ('eq' != $documents['hits']['total']['relation']) {
                    $this->addFlash('info', 'lower_bound_of_the_total');
                }
            } else {
                $total = $documents['hits']['total'];
            }

            $parameters['documents'] = $this->paginatorManager->paginate([
                'route' => 'app_logs',
                'route_parameters' => [],
                'total' => $total,
                'rows' => $documents['hits']['hits'],
                'page' => $page,
                'size' => $size,
            ]);
        }

        return $this->renderAbstract($request, 'Modules/app_log/app_log_index.html.twig', $parameters);
    }
}
