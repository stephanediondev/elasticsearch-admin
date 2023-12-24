<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\CreateAliasType;
use App\Form\Type\ElasticsearchIndexFilterType;
use App\Form\Type\ElasticsearchIndexImportType;
use App\Form\Type\ElasticsearchIndexQueryType;
use App\Form\Type\ElasticsearchIndexSettingType;
use App\Form\Type\ElasticsearchIndexType;
use App\Form\Type\ReindexType;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexAliasModel;
use App\Model\ElasticsearchIndexModel;
use App\Model\ElasticsearchIndexSettingModel;
use App\Model\ElasticsearchReindexModel;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Ods;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchIndexController extends AbstractAppController
{
    private ElasticsearchIndexManager $elasticsearchIndexManager;

    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

    #[Route('/indices', name: 'indices', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_LIST', 'index');

        $query = [
            'bytes' => 'b',
            'h' => 'uuid,index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string,sth',
        ];

        if (true === $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = '' != $request->query->getString('sort') ? $request->query->getString('sort') : 'index:asc';
        }

        $form = $this->createForm(ElasticsearchIndexFilterType::class);

        $form->handleRequest($request);

        $indices = $this->elasticsearchIndexManager->getAll($query, [
            'name' => $form->get('name')->getData(),
            'status' => $form->get('status')->getData(),
            'health' => $form->get('health')->getData(),
            'system' => $form->has('system') ? $form->get('system')->getData() : false,
        ]);

        return $this->renderAbstract($request, 'Modules/index/index_index.html.twig', [
            'indices' => $this->paginatorManager->paginate([
                'route' => 'indices',
                'route_parameters' => [],
                'total' => count($indices),
                'rows' => $indices,
                'array_slice' => true,
                'page' => $request->query->getInt('page'),
                'size' => 100,
            ]),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/indices/stats', name: 'indices_stats', methods: ['GET'])]
    public function stats(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_STATS', 'index');

        $query = [
            'bytes' => 'b',
            'h' => 'uuid,index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string,sth',
        ];

        $form = $this->createForm(ElasticsearchIndexFilterType::class);

        $form->handleRequest($request);

        $indices = $this->elasticsearchIndexManager->getAll($query, [
            'name' => $form->get('name')->getData(),
            'status' => $form->get('status')->getData(),
            'health' => $form->get('health')->getData(),
            'system' => $form->has('system') ? $form->get('system')->getData() : false,
        ]);

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['indices_total'] = 0;
        $data['totals']['indices_total_documents'] = 0;
        $data['totals']['indices_primary_size'] = 0;
        $data['tables']['indices_by_status'] = [];
        $data['tables']['indices_by_health'] = [];
        $data['tables']['indices_by_primary_shards'] = [];
        $data['tables']['indices_by_replicas'] = [];
        $data['tables']['indices_by_documents'] = [];
        $data['tables']['indices_by_primary_size'] = [];

        foreach ($indices as $index) {
            $data['totals']['indices_total']++;

            $data['totals']['indices_total_documents'] += $index->getDocuments();
            $data['totals']['indices_primary_size'] += $index->getPrimarySize();

            foreach (array_keys($data['tables']) as $table) {
                switch ($table) {
                    case 'indices_by_documents':
                        $data['tables'][$table][] = ['total' => $index->getDocuments(), 'title' => $index->getName()];
                        break;
                    case 'indices_by_primary_size':
                        $data['tables'][$table][] = ['total' => $index->getPrimarySize(), 'title' => $index->getName()];
                        break;
                    case 'indices_by_status':
                    case 'indices_by_health':
                    case 'indices_by_primary_shards':
                    case 'indices_by_replicas':
                        $key = false;
                        switch ($table) {
                            case 'indices_by_status':
                                $key = $index->getStatus();
                                break;
                            case 'indices_by_health':
                                $key = $index->getHealth();
                                break;
                            case 'indices_by_primary_shards':
                                $key = $index->getPrimaryShards();
                                break;
                            case 'indices_by_replicas':
                                $key = $index->getReplicas();
                                break;
                        }
                        if (false === isset($data['tables'][$table][$key])) {
                            $data['tables'][$table][$key] = ['total' => 0, 'title' => $key];
                        }
                        $data['tables'][$table][$key]['total']++;
                        break;
                }
            }
        }

        foreach (array_keys($data['tables']) as $table) {
            usort($data['tables'][$table], [$this, 'sortByTotal']);
            $data['tables'][$table] = array_slice($data['tables'][$table], 0, 50);
        }

        return $this->renderAbstract($request, 'Modules/index/index_stats.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function sortByTotal(array $a, array $b): int
    {
        return $b['total'] <=> $a['total'];
    }

    #[Route('/indices/{indices}/mappings/fetch', name: 'indices_mappings_fetch', methods: ['GET'])]
    public function fetchMappings(Request $request, string $indices): JsonResponse
    {
        $this->denyAccessUnlessGranted('INDICES_LIST', 'index');

        $json = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/'.$indices.'/_mapping');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        foreach ($results as $result) {
            if (true === isset($result['mappings']) && true === isset($result['mappings']['properties'])) {
                foreach ($result['mappings']['properties'] as $k => $property) {
                    $json[] = $k;
                }
            }
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    #[Route('/indices/force/merge', name: 'indices_force_merge_all', methods: ['GET'])]
    public function forceMergeAll(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_FORCE_MERGE', 'index');

        if (false === $this->callManager->hasFeature('force_merge')) {
            throw new AccessDeniedException();
        }

        if ($request->query->get('name')) {
            $callResponse = $this->elasticsearchIndexManager->forceMergeByName($request->query->getString('name'));
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_forcemerge');
            $callResponse = $this->callManager->call($callRequest);
        }

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices', $request->query->all());
    }

    #[Route('/indices/cache/clear', name: 'indices_cache_clear_all', methods: ['GET'])]
    public function cacheClearAll(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_CACHE_CLEAR', 'index');

        if ($request->query->get('name')) {
            $callResponse = $this->elasticsearchIndexManager->cacheClearByName($request->query->getString('name'));
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_cache/clear');
            $callResponse = $this->callManager->call($callRequest);
        }

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices', $request->query->all());
    }

    #[Route('/indices/flush', name: 'indices_flush_all', methods: ['GET'])]
    public function flushAll(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_FLUSH', 'index');

        if ($request->query->get('name')) {
            $callResponse = $this->elasticsearchIndexManager->flushByName($request->query->getString('name'));
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_flush');
            $callResponse = $this->callManager->call($callRequest);
        }

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices', $request->query->all());
    }

    #[Route('/indices/refresh', name: 'indices_refresh_all', methods: ['GET'])]
    public function refreshAll(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_REFRESH', 'index');

        if ($request->query->get('name')) {
            $callResponse = $this->elasticsearchIndexManager->refreshByName($request->query->getString('name'));
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/_refresh');
            $callResponse = $this->callManager->call($callRequest);
        }

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices', $request->query->all());
    }

    #[Route('/indices/reindex', name: 'indices_reindex', methods: ['GET', 'POST'])]
    public function reindex(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_REINDEX', 'index');

        $indices = $this->elasticsearchIndexManager->selectIndices();

        $reindexModel = new ElasticsearchReindexModel();
        if ($request->query->get('index')) {
            $reindexModel->setSource($request->query->getString('index'));
        }
        $form = $this->createForm(ReindexType::class, $reindexModel, ['indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $reindexModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_reindex');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read', ['index' => $reindexModel->getDestination()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_reindex.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/indices/create', name: 'indices_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_CREATE', 'index');

        $index = new ElasticsearchIndexModel();
        $form = $this->createForm(ElasticsearchIndexType::class, $index);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [];
                if ($index->getSettingsJson()) {
                    $json['settings'] = json_decode($index->getSettingsJson(), true);
                }
                if ($index->getMappingsJson()) {
                    $json['mappings'] = json_decode($index->getMappingsJson(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$index->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/indices/{index}', name: 'indices_read', methods: ['GET'])]
    public function read(Request $request, string $index): Response
    {
        $this->denyAccessUnlessGranted('INDICES_LIST', 'index');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        return $this->renderAbstract($request, 'Modules/index/index_read.html.twig', [
            'cluster_settings' => $clusterSettings,
            'index' => $index,
        ]);
    }

    #[Route('/indices/{index}/update', name: 'indices_update', methods: ['GET', 'POST'])]
    public function update(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_UPDATE', $index);

        $form = $this->createForm(ElasticsearchIndexType::class, $index, ['context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($index->getMappingsJson()) {
                    $json = json_decode($index->getMappingsJson(), true);
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('PUT');
                    $callRequest->setPath('/'.$index->getName().'/_mapping');
                    $callRequest->setJson($json);
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));
                }

                return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_update.html.twig', [
            'index' => $index,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/indices/{index}/file-import', name: 'indices_read_import', methods: ['GET', 'POST'])]
    public function readImport(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index, false);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_IMPORT', $index);

        $form = $this->createForm(ElasticsearchIndexImportType::class);

        $form->handleRequest($request);

        $parameters = [
            'index' => $index,
            'form' => $form->createView(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('import_file')->getData();

                $spreadsheet = IOFactory::load($file->getRealPath());
                $worksheet = $spreadsheet->getActiveSheet();

                $body = '';

                $excludedMeta = ['_index', '_score'];

                $u = 1;
                $headers = [];
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    if (1 == $u) {
                        foreach ($cellIterator as $cell) {
                            $headers[] = $cell->getFormattedValue();
                        }
                    } else {
                        $id = false;
                        $type = false;
                        $line = [];
                        $key = 0;
                        foreach ($cellIterator as $cell) {
                            if (true === in_array($headers[$key], $excludedMeta)) {
                                $key++;
                                continue;
                            }

                            $value = $cell->getFormattedValue();

                            if ('_id' == $headers[$key]) {
                                $id = $value;
                            } elseif ('_type' == $headers[$key]) {
                                $type = $value;
                            } else {
                                if (true === array_key_exists($headers[$key], $index->getMappingsFlat())) {
                                    if ('keyword' == $index->getMappingsFlat()[$headers[$key]]['type']) {
                                        $parts = explode(PHP_EOL, $value);
                                        if (1 < count($parts)) {
                                            $value = $parts;
                                        }
                                    } elseif ('geo_point' == $index->getMappingsFlat()[$headers[$key]]['type']) {
                                        if ('' == $value) {
                                            $value = false;
                                        } elseif (strstr($value, ',')) {
                                            list($lat, $lon) = explode(',', $value);
                                            $value = ['lat' => $lat, 'lon' => $lon];
                                        }
                                    } elseif (true === $this->isJson($value)) {
                                        $value = json_decode($value, true);
                                    }
                                }

                                if ($value) {
                                    if (strstr($headers[$key], '.')) {
                                        $keys = explode('.', $headers[$key]);
                                        $keysTotal = count($keys);

                                        if (false === isset($line[$keys[0]])) {
                                            $line[$keys[0]] = [];
                                        }

                                        if (2 == $keysTotal) {
                                            $line[$keys[0]][$keys[1]] = $value;
                                        } else {
                                            if (false === isset($line[$keys[0]][$keys[1]])) {
                                                $line[$keys[0]][$keys[1]] = [];
                                            }

                                            if (3 == $keysTotal) {
                                                $line[$keys[0]][$keys[1]][$keys[2]] = $value;
                                            } else {
                                                if (false === isset($line[$keys[0]][$keys[1]][$keys[2]])) {
                                                    $line[$keys[0]][$keys[1]][$keys[2]] = [];
                                                }

                                                if (4 == $keysTotal) {
                                                    $line[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
                                                } else {
                                                    if (false === isset($line[$keys[0]][$keys[1]][$keys[2]][$keys[3]])) {
                                                        $line[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = [];
                                                    }

                                                    if (5 == $keysTotal) {
                                                        $line[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $line[$headers[$key]] = $value;
                                    }
                                }
                            }
                            $key++;
                        }

                        if ($id) {
                            if ($type) {
                                $body .= json_encode(['index' => ['_id' => $id, '_type' => $type]])."\r\n";
                            } else {
                                $body .= json_encode(['index' => ['_id' => $id]])."\r\n";
                            }
                        } else {
                            $body .= json_encode(['index' => (object) []])."\r\n";
                        }

                        $body .= json_encode($line)."\r\n";
                    }
                    $u++;
                }

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath($index->getName().'/_bulk');
                $callRequest->setBody($body);
                $callResponse = $this->callManager->call($callRequest);
                $parameters['response'] = $callResponse->getContent();

                $callResponse = $this->elasticsearchIndexManager->refreshByName($index->getName());

                $this->addFlash('info', json_encode($callResponse->getContent()));
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_import.html.twig', $parameters);
    }

    #[Route('/indices/{index}/export', name: 'indices_read_export', methods: ['GET'])]
    public function readExport(Request $request, string $index): StreamedResponse
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_EXPORT', $index);

        set_time_limit(0);

        $type = $request->query->getString('type', 'csv');
        $delimiter = $request->query->getString('delimiter', ';');
        $filename = $index->getName().'-'.date('Y-m-d-His').'.'.$type;

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

        $size = 1000;
        $query = [
            'sort' => $request->query->getString('sort', '_id:desc'),
            'size' => $size,
            'scroll' => '1m',
        ];
        if ($request->query->get('query') && '' != $request->query->getString('query')) {
            $query['track_scores'] = 'true';
            $query['q'] = $request->query->getString('query');
        }
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index->getName().'/_search');
        $callRequest->setQuery($query);
        if (true === $this->callManager->hasFeature('search_option_fields') && $index->getMappingsFlat()) {
            $body = [
                'fields' => array_keys($index->getMappingsFlat()),
            ];
            if ($json = json_encode($body)) {
                $callRequest->setBody($json);
            }
        }
        $callResponse = $this->callManager->call($callRequest);
        $documents = $callResponse->getContent();

        $searchOptionFields = $this->callManager->hasFeature('search_option_fields');

        return new StreamedResponse(function () use ($writer, $activeSheet, $index, $documents, $searchOptionFields) {
            $outputStream = null;

            $json = [];

            $lines = 1;

            if ('geojson' == $writer) {
                $outputStream = fopen('php://output', 'wb');

                $json['type'] = 'FeatureCollection';
                $json['features'] = [];
            } else {
                $line = [];
                $line[] = '_id';
                $line[] = '_score';
                foreach ($index->getMappingsFlat() as $field => $mapping) {
                    $line[] = $field;
                }
                $activeSheet->fromArray($line, null, 'A'.$lines);
                $lines++;
            }

            while (0 < count($documents['hits']['hits'])) {
                foreach ($documents['hits']['hits'] as $row) {
                    $geoPoint = false;
                    $geoShape = false;

                    $line = [];
                    $line['_id'] = $row['_id'];
                    $line['_score'] = $row['_score'];
                    foreach ($index->getMappingsFlat() as $field => $mapping) {
                        $content = null;

                        if ($searchOptionFields) {
                            if (true === isset($row['fields'][$field])) {
                                $content = $row['fields'][$field];
                            }
                        } else {
                            if (true === isset($row['_source'][$field])) {
                                $content = $row['_source'][$field];
                            } else {
                                $keys = explode('.', $field);

                                $arr = $row['_source'];
                                foreach ($keys as $key) {
                                    $arr = &$arr[$key];
                                }

                                $content = $arr;
                            }
                        }

                        if (true === is_array($content) && 1 === count($content)) {
                            $content = $content[0];
                        }

                        if ('geojson' == $writer) {
                            if ('geo_point' == $mapping['type'] && true === is_array($content)) {
                                $geoPoint = $content;
                            } elseif ('geo_shape' == $mapping['type'] && true === is_array($content)) {
                                $geoShape = $content;
                            } else {
                                $line[$field] = $content;
                            }
                        } else {
                            if ('geo_point' == $mapping['type'] && true === is_array($content) && true === isset($content['lat']) && true === isset($content['lon'])) {
                                $line[$field] = $content['lat'].','.$content['lon'];
                            } elseif ('keyword' == $mapping['type'] && true === is_array($content)) {
                                $line[$field] = implode(PHP_EOL, $content);
                            } elseif (true === is_array($content)) {
                                $line[$field] = json_encode($content);
                            } else {
                                $line[$field] = $content;
                            }
                        }
                    }

                    if ('geojson' == $writer && $geoPoint) {
                        $feature = [];
                        $feature['type'] = 'Feature';
                        $feature['geometry'] = [
                            'type' => 'Point',
                            'coordinates' => [floatval($geoPoint['lon']), floatval($geoPoint['lat'])],
                        ];
                        $feature['properties'] = $line;

                        $json['features'][] = $feature;
                    } elseif ('geojson' == $writer && $geoShape) {
                        if (true === isset($geoShape['type'])) {
                            if ('envelope' != $geoShape['type']) {
                                $geoShape['type'] = $this->cleanGeojsonType($geoShape['type']);

                                if (true === isset($geoShape['geometries'])) {
                                    foreach ($geoShape['geometries'] as $key => $geometry) {
                                        if (true === isset($geometry['type'])) {
                                            $geoShape['geometries'][$key]['type'] = $this->cleanGeojsonType($geometry['type']);
                                        }
                                    }
                                }

                                $feature = [];
                                $feature['type'] = 'Feature';
                                $feature['geometry'] = $geoShape;
                                $feature['properties'] = $line;

                                $json['features'][] = $feature;
                            }
                        }
                    } else {
                        $activeSheet->fromArray($line, null, 'A'.$lines);
                        $lines++;
                    }
                }

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/_search/scroll');
                $callRequest->setJson(['scroll' => '1m', 'scroll_id' => $documents['_scroll_id']]);
                $callResponse = $this->callManager->call($callRequest);
                $documents = $callResponse->getContent();
            }

            if ('geojson' == $writer) {
                if ($outputStream) {
                    if ($jsonEncode = json_encode($json, JSON_PRETTY_PRINT)) {
                        fwrite($outputStream, $jsonEncode);
                    }
                }
            } else {
                $writer->save('php://output');
            }
        }, Response::HTTP_OK, [
            'Content-Disposition' => 'attachment; filename='.$filename,
        ]);
    }

    private function isJson(string $str): bool
    {
        $json = json_decode($str);
        return $json && $str != $json;
    }

    private function cleanGeojsonType(string $type): string
    {
        $type = strtolower($type);
        $type = str_replace('point', 'Point', $type);
        $type = str_replace('multipoint', 'MultiPoint', $type);
        $type = str_replace('polygon', 'Polygon', $type);
        $type = str_replace('multipolygon', 'MultiPolygon', $type);
        $type = str_replace('linestring', 'LineString', $type);
        $type = str_replace('multilinestring', 'MultiLineString', $type);
        $type = str_replace('geometrycollection', 'GeometryCollection', $type);

        return $type;
    }

    #[Route('/indices/{index}/settings', name: 'indices_read_settings', methods: ['GET'])]
    public function settings(Request $request, string $index): Response
    {
        $this->denyAccessUnlessGranted('INDICES_LIST', 'index');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_settings.html.twig', [
            'index' => $index,
            'exclude_settings' => (new ElasticsearchIndexModel())->getExcludeSettings(),
        ]);
    }

    #[Route('/indices/{index}/setting/add', name: 'indices_setting_add', methods: ['GET', 'POST'])]
    public function settingAdd(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_UPDATE', $index);

        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $form = $this->createForm(ElasticsearchIndexSettingType::class, $indexSettingModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this->handleSetting($index, $indexSettingModel);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_settings_add.html.twig', [
            'form' => $form->createView(),
            'index' => $index,
        ]);
    }

    #[Route('/indices/{index}/setting/{setting}/update', name: 'indices_setting_update', methods: ['GET', 'POST'])]
    public function settingUpdate(Request $request, string $index, string $setting): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_UPDATE', $index);

        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $indexSettingModel->setName($setting);
        $indexSettingModel->setValue($index->getSetting($setting));
        $form = $this->createForm(ElasticsearchIndexSettingType::class, $indexSettingModel, ['context' => 'update']);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                return $this->handleSetting($index, $indexSettingModel);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_settings_update.html.twig', [
            'form' => $form->createView(),
            'index' => $index,
        ]);
    }

    private function handleSetting(ElasticsearchIndexModel $index, ElasticsearchIndexSettingModel $indexSettingModel): Response
    {
        $json = $indexSettingModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/'.$index->getName().'/_settings');
        $callRequest->setJson($json);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read_settings', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/setting/{setting}/remove', name: 'indices_setting_remove', methods: ['GET'])]
    public function settingRemove(Request $request, string $index, string $setting): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_UPDATE', $index);

        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $indexSettingModel->setName($setting);
        $indexSettingModel->setValue(null);

        try {
            $json = $indexSettingModel->getJson();
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setPath('/'.$index->getName().'/_settings');
            $callRequest->setJson($json);
            $callResponse = $this->callManager->call($callRequest);

            $this->addFlash('info', json_encode($callResponse->getContent()));

            return $this->redirectToRoute('indices_read_settings', ['index' => $index->getName()]);
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('indices_read_settings', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/mappings', name: 'indices_read_mappings', methods: ['GET'])]
    public function mappings(Request $request, string $index): Response
    {
        $this->denyAccessUnlessGranted('INDICES_LIST', 'index');

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_mappings.html.twig', [
            'index' => $index,
        ]);
    }

    #[Route('/indices/{index}/lifecycle', name: 'indices_read_lifecycle', methods: ['GET'])]
    public function lifecycle(Request $request, string $index): Response
    {
        if (false === $this->callManager->hasFeature('ilm')) {
            throw new AccessDeniedException();
        }

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_LIFECYCLE', $index);

        $callRequest = new CallRequestModel();
        $callRequest->setPath($index->getName().'/_ilm/explain');
        $callResponse = $this->callManager->call($callRequest);
        $lifecycle = $callResponse->getContent();
        $lifecycle = $lifecycle['indices'][$index->getName()];

        return $this->renderAbstract($request, 'Modules/index/index_read_lifecycle.html.twig', [
            'index' => $index,
            'lifecycle' => $lifecycle,
        ]);
    }

    #[Route('/indices/{index}/remove/policy', name: 'indices_remove_policy', methods: ['GET'])]
    public function removePolicy(Request $request, string $index): Response
    {
        if (false === $this->callManager->hasFeature('ilm')) {
            throw new AccessDeniedException();
        }

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_UPDATE', $index);

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath($index->getName().'/_ilm/remove');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read_lifecycle', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/aliases', name: 'indices_read_aliases', methods: ['GET'])]
    public function aliases(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_ALIASES', $index);

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/'.$index->getName().'/_alias');
        $callResponse = $this->callManager->call($callRequest);
        $aliases = $callResponse->getContent();
        $aliases = array_keys($aliases[$index->getName()]['aliases']);

        return $this->renderAbstract($request, 'Modules/index/index_read_aliases.html.twig', [
            'index' => $index,
            'aliases' => $this->paginatorManager->paginate([
                'route' => 'indices_read_aliases',
                'route_parameters' => ['index' => $index->getName()],
                'total' => count($aliases),
                'rows' => $aliases,
                'array_slice' => true,
                'page' => $request->query->getInt('page'),
                'size' => 100,
            ]),
        ]);
    }

    #[Route('/indices/{index}/aliases/create', name: 'indices_aliases_create', methods: ['GET', 'POST'])]
    public function createAlias(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_ALIAS_CREATE', $index);

        $aliasModel = new ElasticsearchIndexAliasModel();
        $form = $this->createForm(CreateAliasType::class, $aliasModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$index->getName().'/_alias/'.$aliasModel->getName());
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read_aliases', ['index' => $index->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_aliases_create.html.twig', [
            'index' => $index,
            'form' => $form->createView(),
        ]);
    }

    #[Route('indices/{index}/aliases/{alias}/delete', name: 'indices_aliases_delete', methods: ['GET'])]
    public function deleteAlias(Request $request, string $index, string $alias): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_ALIAS_DELETE', $index);

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/'.$index->getName().'/_alias/'.$alias);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read_aliases', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/delete', name: 'indices_delete', methods: ['GET'])]
    public function delete(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_DELETE', $index);

        $callResponse = $this->elasticsearchIndexManager->deleteByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices');
    }

    #[Route('/indices/{index}/close', name: 'indices_close', methods: ['GET'])]
    public function close(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $clusterSettings = $this->elasticsearchClusterManager->getClusterSettings();

        if (true === isset($clusterSettings['cluster.indices.close.enable']) && 'false' == $clusterSettings['cluster.indices.close.enable']) {
            throw new AccessDeniedException();
        }

        $this->denyAccessUnlessGranted('INDEX_CLOSE', $index);

        $callResponse = $this->elasticsearchIndexManager->closeByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/open', name: 'indices_open', methods: ['GET'])]
    public function open(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_OPEN', $index);

        $callResponse = $this->elasticsearchIndexManager->openByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/freeze', name: 'indices_freeze', methods: ['GET'])]
    public function freeze(Request $request, string $index): Response
    {
        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            throw new AccessDeniedException();
        }

        if (false === $this->callManager->hasFeature('freeze_unfreeze')) {
            throw new AccessDeniedException();
        }

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_FREEZE', $index);

        $callResponse = $this->elasticsearchIndexManager->freezeByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/unfreeze', name: 'indices_unfreeze', methods: ['GET'])]
    public function unfreeze(Request $request, string $index): Response
    {
        if (true === $this->callManager->hasFeature('freezing_endpoint_removed')) {
            throw new AccessDeniedException();
        }

        if (false === $this->callManager->hasFeature('freeze_unfreeze')) {
            throw new AccessDeniedException();
        }

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_UNFREEZE', $index);

        $callResponse = $this->elasticsearchIndexManager->unfreezeByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/force/merge', name: 'indices_force_merge', methods: ['GET'])]
    public function forceMerge(Request $request, string $index): Response
    {
        if (false === $this->callManager->hasFeature('force_merge')) {
            throw new AccessDeniedException();
        }

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_FORCE_MERGE', $index);

        $callResponse = $this->elasticsearchIndexManager->forceMergeByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/cache/clear', name: 'indices_cache_clear', methods: ['GET'])]
    public function cacheClear(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_CACHE_CLEAR', $index);

        $callResponse = $this->elasticsearchIndexManager->cacheClearByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/flush', name: 'indices_flush', methods: ['GET'])]
    public function flush(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_FLUSH', $index);

        $callResponse = $this->elasticsearchIndexManager->flushByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/refresh', name: 'indices_refresh', methods: ['GET'])]
    public function refresh(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_REFRESH', $index);

        $callResponse = $this->elasticsearchIndexManager->refreshByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/empty', name: 'indices_empty', methods: ['GET'])]
    public function empty(Request $request, string $index): Response
    {
        if (false === $this->callManager->hasFeature('delete_by_query')) {
            throw new AccessDeniedException();
        }

        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_EMPTY', $index);

        $callResponse = $this->elasticsearchIndexManager->emptyByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        $callResponse = $this->elasticsearchIndexManager->refreshByName($index->getName());

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index->getName()]);
    }

    #[Route('/indices/{index}/search', name: 'indices_read_search', methods: ['GET'])]
    public function search(Request $request, string $index): Response
    {
        $index = $this->elasticsearchIndexManager->getByName($index);

        if (null === $index) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('INDEX_SEARCH', $index);

        $form = $this->createForm(ElasticsearchIndexQueryType::class);

        $form->handleRequest($request);

        $parameters = [
            'index' => $index,
            'form' => $form->createView(),
        ];

        try {
            $size = 100;
            if ($request->query->get('page') && '' != $request->query->get('page')) {
                $page = $request->query->getInt('page');
            } else {
                $page = 1;
            }
            if ($request->query->get('sort') && '' != $request->query->get('sort')) {
                $sort = $request->query->getString('sort');
            } else {
                $sort = '_score:desc';
            }
            $query = [
                'track_scores' => 'true',
                'q' => $form->get('query')->getData(),
                'sort' => $sort,
                'size' => $size,
                'from' => ($size * $page) - $size,
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('POST');
            $callRequest->setPath('/'.$index->getName().'/_search');
            $callRequest->setQuery($query);
            if (true === $this->callManager->hasFeature('search_option_fields') && $index->getMappingsFlat()) {
                $body = [
                    'fields' => array_keys($index->getMappingsFlat()),
                ];
                if ($json = json_encode($body)) {
                    $callRequest->setBody($json);
                }
            }
            $callResponse = $this->callManager->call($callRequest);
            $documents = $callResponse->getContent();

            if (true === isset($documents['hits']['total']['value'])) {
                $total = $documents['hits']['total']['value'];
                if ('eq' != $documents['hits']['total']['relation']) {
                    $this->addFlash('warning', 'lower_bound_of_the_total');
                }
            } else {
                $total = $documents['hits']['total'];
            }

            $parameters['documents'] = $this->paginatorManager->paginate([
                'route' => 'indices_read_search',
                'route_parameters' => ['index' => $index->getName()],
                'total' => $total,
                'rows' => $documents['hits']['hits'],
                'page' => $page,
                'size' => 100,
            ]);
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_search.html.twig', $parameters);
    }
}
