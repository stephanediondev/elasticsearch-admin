<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchCatType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchNodeManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchCatModel;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
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

    #[Route('/cat', name: 'cat')]
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

    #[Route('/cat/export', name: 'cat_export')]
    public function export(Request $request): ?StreamedResponse
    {
        $this->denyAccessUnlessGranted('CAT_EXPORT', 'global');

        set_time_limit(0);

        $type = $request->query->getString('type', 'csv');
        $delimiter = $request->query->getString('delimiter', ';');

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(12);
        $activeSheet = $spreadsheet->getActiveSheet();

        switch ($type) {
            case 'xlsx':
                $writer = new Xlsx($spreadsheet);
                break;
            case 'ods':
                $writer = new Ods($spreadsheet);
                break;
            case 'csv':
                $writer = new Csv($spreadsheet);
                $writer->setDelimiter($delimiter);
                break;
            default:
                throw new Exception('No writers supporting the given type: ' . $type);
        }

        $repositories = $this->elasticsearchRepositoryManager->selectRepositories();
        $aliases = $this->elasticsearchIndexManager->selectAliases();
        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $catModel = new ElasticsearchCatModel();
        $form = $this->createForm(ElasticsearchCatType::class, $catModel, ['repositories' => $repositories, 'aliases' => $aliases, 'nodes' => $nodes]);

        $form->handleRequest($request);

        $filename = str_replace('/', '-', $catModel->getCommandReplace()).'-'.date('Y-m-d-His').'.'.$type;

        if ($form->isSubmitted()) {
            return new StreamedResponse(function () use ($writer, $activeSheet, $catModel) {
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
                    $rows = $callResponse->getContent();
                    if (0 < count($rows)) {
                        $headers = array_keys($rows[0]);
                    }

                    $lines = 1;

                    if (true === isset($headers)) {
                        $line = [];
                        foreach ($headers as $header) {
                            $line[] = $header;
                        }
                        $activeSheet->fromArray($line, null, 'A'.$lines);
                        $lines++;
                    }

                    foreach ($rows as $row) {
                        $line = [];
                        foreach ($row as $column) {
                            $line[] = $column;
                        }
                        $activeSheet->fromArray($line, null, 'A'.$lines);
                        $lines++;
                    }

                    $writer->save('php://output');
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
