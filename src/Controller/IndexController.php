<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateAliasType;
use App\Form\CreateIndexType;
use App\Form\CreateIndexSettingType;
use App\Form\ImportIndexType;
use App\Form\ReindexType;
use App\Form\SearchIndexType;
use App\Manager\ElasticsearchClusterManager;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexModel;
use App\Model\ElasticsearchIndexAliasModel;
use App\Model\ElasticsearchIndexSettingModel;
use App\Model\ElasticsearchReindexModel;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class IndexController extends AbstractAppController
{
    /**
     * @Route("/indices", name="indices")
     */
    public function index(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/');
        $callResponse = $this->callManager->call($callRequest);
        $root = $callResponse->getContent();

        $query = [
            'bytes' => 'b',
            's' => $request->query->get('s', 'index:asc'),
            'h' => 'index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string,sth',
        ];

        if (true == isset($root['version']) && true == isset($root['version']['number']) && 0 <= version_compare($root['version']['number'], '7.7')) {
            $query['expand_wildcards'] = 'all';
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $indices = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/index/index_index.html.twig', [
            'indices' => $this->paginatorManager->paginate([
                'route' => 'indices',
                'route_parameters' => [],
                'total' => count($indices),
                'rows' => $indices,
                'page' => 1,
                'size' => count($indices),
            ]),
        ]);
    }

    /**
     * @Route("/indices/{indices}/mappings/fetch", name="indices_mappings_fetch")
     */
    public function fetchMappings(Request $request, string $indices): JsonResponse
    {
        $json = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/'.$indices.'/_mapping');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        foreach ($results as $result) {
            if (true == isset($result['mappings']) && true == isset($result['mappings']['properties'])) {
                foreach ($result['mappings']['properties'] as $k => $property) {
                    $json[] = $k;
                }
            }
        }

        return new JsonResponse($json, JsonResponse::HTTP_OK);
    }

    /**
     * @Route("/indices/force/merge", name="indices_force_merge_all")
     */
    public function forceMergeAll(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_forcemerge');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices');
    }

    /**
     * @Route("/indices/cache/clear", name="indices_cache_clear_all")
     */
    public function cacheClearAll(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_cache/clear');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices');
    }

    /**
     * @Route("/indices/flush", name="indices_flush_all")
     */
    public function flushAll(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_flush');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices');
    }

    /**
     * @Route("/indices/refresh", name="indices_refresh_all")
     */
    public function refreshAll(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_refresh');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices');
    }

    /**
     * @Route("/indices/reindex", name="indices_reindex")
     */
    public function reindex(Request $request, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $indices = $elasticsearchIndexManager->selectIndices();

        $reindexModel = new ElasticsearchReindexModel();
        if ($request->query->get('index')) {
            $reindexModel->setSource($request->query->get('index'));
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

    /**
     * @Route("/indices/create", name="indices_create")
     */
    public function create(Request $request): Response
    {
        $indexModel = new ElasticsearchIndexModel();
        $form = $this->createForm(CreateIndexType::class, $indexModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = [];
                if ($indexModel->getSettings()) {
                    $json['settings'] = json_decode($indexModel->getSettings(), true);
                }
                if ($indexModel->getMappings()) {
                    $json['mappings'] = json_decode($indexModel->getMappings(), true);
                }
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$indexModel->getName());
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read', ['index' => $indexModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/indices/{index}", name="indices_read")
     */
    public function read(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchClusterManager $elasticsearchClusterManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $clusterSettings = $elasticsearchClusterManager->getClusterSettings();

        return $this->renderAbstract($request, 'Modules/index/index_read.html.twig', [
            'cluster_settings' => $clusterSettings,
            'index' => $index,
        ]);
    }

    /**
     * @Route("/indices/{index}/update", name="indices_update")
     */
    public function update(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $indexModel = new ElasticsearchIndexModel();
        $indexModel->convert($index);
        $form = $this->createForm(CreateIndexType::class, $indexModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                if ($indexModel->getMappings()) {
                    $json = json_decode($indexModel->getMappings(), true);
                    $callRequest = new CallRequestModel();
                    $callRequest->setMethod('PUT');
                    $callRequest->setPath('/'.$indexModel->getName().'/_mapping');
                    $callRequest->setJson($json);
                    $callResponse = $this->callManager->call($callRequest);

                    $this->addFlash('info', json_encode($callResponse->getContent()));
                }

                return $this->redirectToRoute('indices_read', ['index' => $indexModel->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_update.html.twig', [
            'index' => $index,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/indices/{index}/import-export", name="indices_read_import_export")
     */
    public function readImportExport(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(ImportIndexType::class);

        $form->handleRequest($request);

        $parameters = [
            'index' => $index,
            'form' => $form->createView(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('import_file')->getData();

                $reader = ReaderEntityFactory::createReaderFromFile($file->getClientOriginalName());

                $reader->open($file->getRealPath());

                $body = '';

                foreach ($reader->getSheetIterator() as $sheet) {
                    $u = 1;
                    foreach ($sheet->getRowIterator() as $rowObject) {
                        $row = $rowObject->toArray();
                        if (1 == $u) {
                            $headers = [];
                            foreach ($row as $key => $value) {
                                $headers[] = $value;
                            }
                        } else {
                            $id = false;
                            $line = [];
                            foreach ($row as $key => $value) {
                                if ('_id' == $key) {
                                    $id = $value;
                                } else {
                                    if ($value instanceof \Datetime) {
                                        $value = $value->format('Y-m-d');
                                    }
                                    if (true == array_key_exists($headers[$key], $index['mappings_flat']) && 'keyword' == $index['mappings_flat'][$headers[$key]]) {
                                        $parts = explode(PHP_EOL, $value);
                                        if (1 < count($parts)) {
                                            $value = $parts;
                                        }
                                    }
                                    if (true == array_key_exists($headers[$key], $index['mappings_flat']) && 'geo_point' == $index['mappings_flat'][$headers[$key]]) {
                                        if (strstr($value, ',')) {
                                            list($lat, $lon) = explode(',', $value);
                                            $line[$headers[$key]] = ['lat' => $lat, 'lon' => $lon];
                                        }
                                    } else {
                                        $line[$headers[$key]] = $value;
                                    }
                                }
                            }

                            if ($id) {
                                $body .= json_encode(['index' => ['_id' => $id]])."\r\n";
                            } else {
                                $body .= json_encode(['index' => (object)[]])."\r\n";
                            }

                            $body .= json_encode($line)."\r\n";
                        }
                        $u++;
                    }
                    break;
                }

                $reader->close();

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath($index['index'].'/_bulk');
                $callRequest->setBody($body);
                $callResponse = $this->callManager->call($callRequest);
                $parameters['response'] = $callResponse->getContent();

                $callRequest = new CallRequestModel();
                $callRequest->setMethod('POST');
                $callRequest->setPath('/'.$index['index'].'/_refresh');
                $callResponse = $this->callManager->call($callRequest);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_import_export.html.twig', $parameters);
    }

    /**
     * @Route("/indices/{index}/export", name="indices_read_export")
     */
    public function readExport(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): StreamedResponse
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        set_time_limit(0);

        $type = $request->query->get('type', 'csv');
        $delimiter = $request->query->get('delimiter', ';');
        $filename = $index['index'].'-'.date('Y-m-d-His').'.'.$type;

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

        $size = 1000;
        $query = [
            'sort' => '_id:desc',
            'size' => $size,
            'from' => ($size * $request->query->get('page', 1)) - $size,
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/'.$index['index'].'/_search?scroll=1m');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $documents = $callResponse->getContent();

        return new StreamedResponse(function () use ($writer, $index, $documents) {
            if ('geojson' == $writer) {
                $outputStream = fopen('php://output', 'wb');

                $json = [];
                $json['type'] = 'FeatureCollection';
                $json['features'] = [];
            } else {
                $writer->openToFile('php://output');

                $lines = [];

                $line = [];
                $line[] = '_id';
                foreach ($index['mappings_flat'] as $field => $type) {
                    $line[] = $field;
                }
                $lines[] = WriterEntityFactory::createRowFromArray($line);
            }

            while (0 < count($documents['hits']['hits'])) {
                foreach ($documents['hits']['hits'] as $row) {
                    $geoPoint = false;

                    $line = [];
                    $line['_id'] = $row['_id'];
                    foreach ($index['mappings_flat'] as $field => $type) {
                        if (true == isset($row['_source'][$field])) {
                            if ('geo_point' == $type && true == is_array($row['_source'][$field])) {
                                $geoPoint = $row['_source'][$field]['lat'].','.$row['_source'][$field]['lon'];
                                $line[$field] = $geoPoint;
                            } else {
                                if ('geo_point' == $type && '' != $row['_source'][$field]) {
                                    $line[$field] = $row['_source'][$field];
                                } else if ('geojson' != $writer && true == is_array($row['_source'][$field])) {
                                    $line[$field] = implode(PHP_EOL, $row['_source'][$field]);
                                } else {
                                    $line[$field] = $row['_source'][$field];
                                }
                            }
                        } else {
                            $keys = explode('.', $field);

                            $arr = $row['_source'];
                            foreach ($keys as $key) {
                                $arr = &$arr[$key];
                            }
                            $line[$field] = $arr;
                        }
                    }

                    if ('geojson' == $writer && $geoPoint) {
                        list($latitude, $longitude) = explode(',', $geoPoint);

                        $feature = [];
                        $feature['type'] = 'Feature';
                        $feature['geometry'] = [
                            'type' => 'Point',
                            'coordinates' => [$longitude, $latitude],
                        ];
                        $feature['properties'] = $line;

                        $json['features'][] = $feature;
                    } else {
                        $lines[] = WriterEntityFactory::createRowFromArray($line);
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
                fwrite($outputStream, json_encode($json));
            } else {
                $writer->addRows($lines);
                $writer->close();
            }
        }, Response::HTTP_OK, [
            'Content-Disposition' => 'attachment; filename='.$filename,
        ]);
    }

    /**
     * @Route("/indices/{index}/settings", name="indices_read_settings")
     */
    public function settings(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_settings.html.twig', [
            'index' => $index,
            'exclude_settings' => (new ElasticsearchIndexModel())->getExcludeSettings(),
        ]);
    }

    /**
     * @Route("/indices/{index}/setting/add", name="indices_setting_add")
     */
    public function settingAdd(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $form = $this->createForm(CreateIndexSettingType::class, $indexSettingModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $indexSettingModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$index['index'].'/_settings');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read_settings', ['index' => $index['index']]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_settings_add.html.twig', [
            'form' => $form->createView(),
            'index' => $index,
        ]);
    }

    /**
     * @Route("/indices/{index}/setting/{setting}/update", name="indices_setting_update")
     */
    public function settingUpdate(Request $request, string $index, string $setting, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $indexSettingModel->setName($setting);
        $indexSettingModel->setValue($index['settings'][$setting]);
        $form = $this->createForm(CreateIndexSettingType::class, $indexSettingModel, ['update' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $json = $indexSettingModel->getJson();
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$index['index'].'/_settings');
                $callRequest->setJson($json);
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read_settings', ['index' => $index['index']]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_settings_update.html.twig', [
            'form' => $form->createView(),
            'index' => $index,
        ]);
    }

    /**
     * @Route("/indices/{index}/setting/{setting}/remove", name="indices_setting_remove")
     */
    public function settingRemove(Request $request, string $index, string $setting, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $indexSettingModel = new ElasticsearchIndexSettingModel();
        $indexSettingModel->setName($setting);
        $indexSettingModel->setValue(null);

        try {
            $json = $indexSettingModel->getJson();
            $callRequest = new CallRequestModel();
            $callRequest->setMethod('PUT');
            $callRequest->setPath('/'.$index['index'].'/_settings');
            $callRequest->setJson($json);
            $callResponse = $this->callManager->call($callRequest);

            $this->addFlash('info', json_encode($callResponse->getContent()));

            return $this->redirectToRoute('indices_read_settings', ['index' => $index['index']]);
        } catch (CallException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('indices_read_settings', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/mappings", name="indices_read_mappings")
     */
    public function mappings(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_mappings.html.twig', [
            'index' => $index,
        ]);
    }

    /**
     * @Route("/indices/{index}/lifecycle", name="indices_read_lifecycle")
     */
    public function lifecycle(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath($index['index'].'/_ilm/explain');
        $callResponse = $this->callManager->call($callRequest);
        $lifecycle = $callResponse->getContent();
        $lifecycle = $lifecycle['indices'][$index['index']];

        return $this->renderAbstract($request, 'Modules/index/index_read_lifecycle.html.twig', [
            'index' => $index,
            'lifecycle' => $lifecycle,
        ]);
    }

    /**
     * @Route("/indices/{index}/remove/policy", name="indices_remove_policy")
     */
    public function removePolicy(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath($index['index'].'/_ilm/remove');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read_lifecycle', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/shards", name="indices_read_shards")
     */
    public function shards(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/shards/'.$index['index']);
        $callRequest->setQuery(['bytes' => 'b', 's' => $request->query->get('s', 'shard:asc,prirep:asc'), 'h' => 'shard,prirep,state,unassigned.reason,docs,store,node']);
        $callResponse = $this->callManager->call($callRequest);
        $shards = $callResponse->getContent();

        return $this->renderAbstract($request, 'Modules/index/index_read_shards.html.twig', [
            'index' => $index,
            'shards' => $this->paginatorManager->paginate([
                'route' => 'indices_read_shards',
                'route_parameters' => ['index' => $index['index']],
                'total' => count($shards),
                'rows' => $shards,
                'page' => 1,
                'size' => count($shards),
            ]),
        ]);
    }

    /**
     * @Route("/indices/{index}/aliases", name="indices_read_aliases")
     */
    public function aliases(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/'.$index['index'].'/_alias');
        $callResponse = $this->callManager->call($callRequest);
        $aliases = $callResponse->getContent();
        $aliases = array_keys($aliases[$index['index']]['aliases']);

        return $this->renderAbstract($request, 'Modules/index/index_read_aliases.html.twig', [
            'index' => $index,
            'aliases' => $this->paginatorManager->paginate([
                'route' => 'indices_read_aliases',
                'route_parameters' => ['index' => $index['index']],
                'total' => count($aliases),
                'rows' => $aliases,
                'page' => 1,
                'size' => count($aliases),
            ]),
        ]);
    }

    /**
     * @Route("/indices/{index}/aliases/create", name="indices_aliases_create")
     */
    public function createAlias(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        $aliasModel = new ElasticsearchIndexAliasModel();
        $form = $this->createForm(CreateAliasType::class, $aliasModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callRequest = new CallRequestModel();
                $callRequest->setMethod('PUT');
                $callRequest->setPath('/'.$index['index'].'/_alias/'.$aliasModel->getName());
                $callResponse = $this->callManager->call($callRequest);

                $this->addFlash('info', json_encode($callResponse->getContent()));

                return $this->redirectToRoute('indices_read_aliases', ['index' => $index['index']]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_aliases_create.html.twig', [
            'index' => $index,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("indices/{index}/aliases/{alias}/delete", name="indices_aliases_delete")
     */
    public function deleteAlias(Request $request, string $index, string $alias): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/'.$index.'/_alias/'.$alias);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read_aliases', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/delete", name="indices_delete")
     */
    public function delete(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/'.$index['index']);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices');
    }

    /**
     * @Route("/indices/{index}/close", name="indices_close")
     */
    public function close(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_close');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/open", name="indices_open")
     */
    public function open(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_open');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/freeze", name="indices_freeze")
     */
    public function freeze(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_freeze');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/unfreeze", name="indices_unfreeze")
     */
    public function unfreeze(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_unfreeze');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/force/merge", name="indices_force_merge")
     */
    public function forceMerge(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_forcemerge');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/cache/clear", name="indices_cache_clear")
     */
    public function cacheClear(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_cache/clear');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/flush", name="indices_flush")
     */
    public function flush(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_flush');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/refresh", name="indices_refresh")
     */
    public function refresh(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_refresh');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/empty", name="indices_empty")
     */
    public function empty(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $json = [
            'query' => [
                'match_all' => (object)[],
            ],
        ];
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_delete_by_query');
        $callRequest->setJson($json);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$index['index'].'/_refresh');
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('indices_read', ['index' => $index['index']]);
    }

    /**
     * @Route("/indices/{index}/search", name="indices_read_search")
     */
    public function search(Request $request, string $index, ElasticsearchIndexManager $elasticsearchIndexManager): Response
    {
        $index = $elasticsearchIndexManager->getIndex($index);

        if (false == $index) {
            throw new NotFoundHttpException();
        }

        if (true == $index['is_system']) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(SearchIndexType::class);

        $form->handleRequest($request);

        $parameters = [
            'index' => $index,
            'form' => $form->createView(),
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $size = 100;
            $query = [
                'q' => $form->get('query')->getData(),
                'sort' => '_id:desc',
                'size' => $size,
                'from' => ($size * $request->query->get('page', 1)) - $size,
            ];
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.$index['index'].'/_search');
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
                'route' => 'indices_read_search',
                'route_parameters' => ['index' => $index['index']],
                'total' => $total,
                'rows' => $documents['hits']['hits'],
                'page' => $request->query->get('page', 1),
                'size' => $size,
            ]);
        }

        return $this->renderAbstract($request, 'Modules/index/index_read_search.html.twig', $parameters);
    }
}
