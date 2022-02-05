<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchCatType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchCatModel;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/admin")
 */
class ElasticsearchCatController extends AbstractAppController
{
    private ElasticsearchRepositoryManager $elasticsearchRepositoryManager;

    private ElasticsearchIndexManager $elasticsearchIndexManager;

    private ElasticsearchNodeManager $elasticsearchNodeManager;

    public function __construct(ElasticsearchRepositoryManager $elasticsearchRepositoryManager, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchRepositoryManager = $elasticsearchRepositoryManager;
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    /**
     * @Route("/cat", name="cat")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CAT', 'global');

        $parameters = [];

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $aliases = $this->elasticsearchIndexManager->selectAliases();
        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $catModel = new ElasticsearchCatModel();
        $form = $this->createForm(ElasticsearchCatType::class, $catModel, ['repositories' => $repositories, 'aliases' => $aliases, 'nodes' => $nodes]);

        $form->handleRequest($request);

        if ($catModel->getCommand()) {
            try {
                $query = [];
                if ($catModel->getHeaders()) {
                    $query['h'] = $catModel->getHeaders();
                }
                if ($catModel->getSort()) {
                    $query['s'] = $catModel->getSort();
                }
                if ('nodes' == $catModel->getCommand()) {
                    $query['full_id'] = 'true';
                }
                if (true === $catModel->useExpandWildcard() && true === $this->callManager->hasFeature('cat_expand_wildcards')) {
                    $query['expand_wildcards'] = 'all';
                }
                $callRequest = new CallRequestModel();
                $callRequest->setPath('/_cat/'.$catModel->getCommandReplace());
                $callRequest->setQuery($query);
                $callResponse = $this->callManager->call($callRequest);

                $rows = $callResponse->getContent() ?? [];

                if (true === is_array($rows) && 0 < count($rows)) {
                    $parameters['headers'] = array_keys($rows[0]);
                }

                $parameters['rows'] = $this->paginatorManager->paginate([
                    'route' => 'cat',
                    'route_parameters' => [],
                    'total' => count($rows),
                    'rows' => $rows,
                    'array_slice' => true,
                    'page' => $request->query->get('page'),
                    'size' => 100,
                ]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_cat/'.$catModel->getCommandHelp());
            $callRequest->setQuery(['help' => 'true', 'format' => 'text']);
            $callResponse = $this->callManager->call($callRequest);
            $parameters['help'] = $callResponse->getContentRaw();

            $parameters['command'] = $catModel->getCommand();
            $parameters['command_replace'] = $catModel->getCommandReplace();
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }

    /**
     * @Route("/cat/export", name="cat_export")
     */
    public function export(Request $request): ?StreamedResponse
    {
        $this->denyAccessUnlessGranted('CAT_EXPORT', 'global');

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
            default:
                throw new UnsupportedTypeException('No writers supporting the given type: ' . $type);
        }

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $aliases = $this->elasticsearchIndexManager->selectAliases();
        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $catModel = new ElasticsearchCatModel();
        $form = $this->createForm(ElasticsearchCatType::class, $catModel, ['repositories' => $repositories, 'aliases' => $aliases, 'nodes' => $nodes]);

        $form->handleRequest($request);

        $filename = str_replace('/', '-', $catModel->getCommandReplace()).'-'.date('Y-m-d-His').'.'.$type;

        if ($form->isSubmitted()) {
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

        return null;
    }
}
