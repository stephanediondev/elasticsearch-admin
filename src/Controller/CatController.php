<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\FilterCatType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchCatModel;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/admin")
 */
class CatController extends AbstractAppController
{
    /**
     * @Route("/cat", name="cat")
     */
    public function index(Request $request, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager): Response
    {
        $this->denyAccessUnlessGranted('CAT');

        $parameters = [];

        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $indices = $elasticsearchIndexManager->selectIndices();
        $aliases = $elasticsearchIndexManager->selectAliases();

        $catModel = new ElasticsearchCatModel();
        $form = $this->createForm(FilterCatType::class, $catModel, ['repositories' => $repositories, 'indices' => $indices, 'aliases' => $aliases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $query = [];
                if ($catModel->getHeaders()) {
                    $query['h'] = $catModel->getHeaders();
                }
                if ($catModel->getSort()) {
                    $query['s'] = $catModel->getSort();
                }
                $callRequest = new CallRequestModel();
                $callRequest->setPath('/_cat/'.$catModel->getCommandReplace());
                $callRequest->setQuery($query);
                $callResponse = $this->callManager->call($callRequest);
                $parameters['rows'] = $callResponse->getContent();
                if (0 < count($parameters['rows'])) {
                    $parameters['headers'] = array_keys($parameters['rows'][0]);
                }
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_cat/'.$catModel->getCommandHelp());
            $callRequest->setQuery(['help' => 'true', 'format' => 'text']);
            $callResponse = $this->callManager->call($callRequest);
            $parameters['help'] = $callResponse->getContentRaw();

            $parameters['command'] = $catModel->getCommandReplace();
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }

    /**
     * @Route("/cat/export", name="cat_export")
     */
    public function export(Request $request, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager): StreamedResponse
    {
        $this->denyAccessUnlessGranted('CAT');

        set_time_limit(0);

        $type = $request->query->get('type', 'csv');
        $delimiter = $request->query->get('delimiter', ';');

        switch ($type) {
            case 'xlsx':
                $writer = WriterEntityFactory::createXLSXWriter();
                break;
            case 'ods':
                $writer = WriterEntityFactory::createODSWriter();
                break;
            case 'csv':
                $writer = WriterEntityFactory::createCSVWriter();
                $writer->setFieldDelimiter($delimiter);
                break;
            case 'geojson':
                $writer = 'geojson';
                break;
            default:
                throw new UnsupportedTypeException('No writers supporting the given type: ' . $type);
        }

        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $indices = $elasticsearchIndexManager->selectIndices();
        $aliases = $elasticsearchIndexManager->selectAliases();

        $catModel = new ElasticsearchCatModel();
        $form = $this->createForm(FilterCatType::class, $catModel, ['repositories' => $repositories, 'indices' => $indices, 'aliases' => $aliases]);

        $form->handleRequest($request);

        $filename = str_replace('/', '-', $catModel->getCommandReplace()).'-'.date('Y-m-d-His').'.'.$type;

        if ($form->isSubmitted() && $form->isValid()) {
            return new StreamedResponse(function () use ($writer, $catModel) {
                try {
                    $query = [];
                    if ($catModel->getHeaders()) {
                        $query['h'] = $catModel->getHeaders();
                    }
                    if ($catModel->getSort()) {
                        $query['s'] = $catModel->getSort();
                    }
                    $callRequest = new CallRequestModel();
                    $callRequest->setPath('/_cat/'.$catModel->getCommandReplace());
                    $callRequest->setQuery($query);
                    $callResponse = $this->callManager->call($callRequest);
                    $parameters['rows'] = $callResponse->getContent();
                    if (0 < count($parameters['rows'])) {
                        $parameters['headers'] = array_keys($parameters['rows'][0]);
                    }

                    $writer->openToFile('php://output');

                    $lines = [];

                    $line = [];
                    foreach ($parameters['headers'] as $header) {
                        $line[] = $header;
                    }
                    $lines[] = WriterEntityFactory::createRowFromArray($line);

                    foreach ($parameters['rows'] as $row) {
                        $line = [];
                        foreach ($row as $column) {
                            $line[] = $column;
                        }
                        $lines[] = WriterEntityFactory::createRowFromArray($line);
                    }

                    $writer->addRows($lines);
                    $writer->close();
                } catch (CallException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }, Response::HTTP_OK, [
                'Content-Disposition' => 'attachment; filename='.$filename,
            ]);
        }
    }
}
