<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\CreateAliasType;
use App\Form\CreateIndexType;
use App\Form\ReindexType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IndicesController extends AbstractAppController
{
    /**
     * @Route("/indices", name="indices")
     */
    public function index(Request $request): Response
    {
        $query = [
            's' => 'index',
            'h' => 'index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string'
        ];
        $indices = $this->queryManager->query('GET', '/_cat/indices', ['query' => $query]);

        return $this->renderAbstract($request, 'Modules/indices/indices_index.html.twig', [
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
     * @Route("/indices/force/merge", name="indices_force_merge_all")
     */
    public function forceMergeAll(Request $request): Response
    {
        $query = [
        ];
        $this->queryManager->query('POST', '/_forcemerge', ['query' => $query]);

        $this->addFlash('success', 'indices_force_merge_all');

        return $this->redirectToRoute('indices', []);
    }

    /**
     * @Route("/indices/cache/clear", name="indices_cache_clear_all")
     */
    public function cacheClearAll(Request $request): Response
    {
        $query = [
        ];
        $this->queryManager->query('POST', '/_cache/clear', ['query' => $query]);

        $this->addFlash('success', 'indices_cache_clear_all');

        return $this->redirectToRoute('indices', []);
    }

    /**
     * @Route("/indices/flush", name="indices_flush_all")
     */
    public function flushAll(Request $request): Response
    {
        $query = [
        ];
        $this->queryManager->query('POST', '/_flush', ['query' => $query]);

        $this->addFlash('success', 'indices_flush_all');

        return $this->redirectToRoute('indices', []);
    }

    /**
     * @Route("/indices/refresh", name="indices_refresh_all")
     */
    public function refreshAll(Request $request): Response
    {
        $query = [
        ];
        $this->queryManager->query('POST', '/_refresh', ['query' => $query]);

        $this->addFlash('success', 'indices_refresh_all');

        return $this->redirectToRoute('indices', []);
    }

    /**
     * @Route("/indices/reindex", name="indices_reindex")
     */
    public function reindex(Request $request): Response
    {
        $query = [
            's' => 'index',
            'h' => 'index'
        ];
        $rows = $this->queryManager->query('GET', '/_cat/indices', ['query' => $query]);

        foreach ($rows as $row) {
            $indices[] = $row['index'];
        }

        $form = $this->createForm(ReindexType::class, null, ['indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $body = [
                'source' => [
                    'index' => $form->get('source')->getData(),
                ],
                'dest' => [
                    'index' => $form->get('dest')->getData(),
                ],
            ];
            $this->queryManager->query('POST', '/_reindex', ['body' => $body]);

            $this->addFlash('success', 'indices_reindex');

            return $this->redirectToRoute('indices', []);
        }

        return $this->renderAbstract($request, 'Modules/indices/indices_reindex.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/indices/create", name="indices_create")
     */
    public function create(Request $request): Response
    {
        $form = $this->createForm(CreateIndexType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $body = [
                'settings' => [
                    'number_of_shards' => $form->get('number_of_shards')->getData(),
                    'number_of_replicas' => $form->get('number_of_replicas')->getData(),
                ],
            ];
            $this->queryManager->query('PUT', '/'.$form->get('name')->getData(), ['body' => $body]);

            $this->addFlash('success', 'indices_create');

            return $this->redirectToRoute('indices_read', ['index' => $form->get('name')->getData()]);
        }

        return $this->renderAbstract($request, 'Modules/indices/indices_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/indices/{index}", name="indices_read")
     */
    public function read(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('GET', '/_cat/indices/'.$index, ['query' => $query]);

        if ($indice) {
            return $this->renderAbstract($request, 'Modules/indices/indices_read.html.twig', [
                'indice' => $indice[0],
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/indices/{index}/shards", name="indices_read_shards")
     */
    public function shards(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('GET', '/_cat/indices/'.$index, ['query' => $query]);

        if ($indice) {
            $query = [
                'h' => 'shard,prirep,state,unassigned.reason,docs,store,node'
            ];
            $shards = $this->queryManager->query('GET', '/_cat/shards/'.$index, ['query' => $query]);

            return $this->renderAbstract($request, 'Modules/indices/indices_read_shards.html.twig', [
                'indice' => $indice[0],
                'shards' => $this->paginatorManager->paginate([
                    'route' => 'indices_read_shards',
                    'route_parameters' => ['index' => $index],
                    'total' => count($shards),
                    'rows' => $shards,
                    'page' => 1,
                    'size' => count($shards),
                ]),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/indices/{index}/aliases", name="indices_read_aliases")
     */
    public function aliases(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('GET', '/_cat/indices/'.$index, ['query' => $query]);

        if ($indice) {
            $query = [
            ];
            $aliases = $this->queryManager->query('GET', '/'.$index.'/_alias', ['query' => $query]);
            $aliases = array_keys($aliases[$index]['aliases']);

            return $this->renderAbstract($request, 'Modules/indices/indices_read_aliases.html.twig', [
                'indice' => $indice[0],
                'aliases' => $this->paginatorManager->paginate([
                    'route' => 'indices_read_aliases',
                    'route_parameters' => ['index' => $index],
                    'total' => count($aliases),
                    'rows' => $aliases,
                    'page' => 1,
                    'size' => count($aliases),
                ]),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/indices/{index}/aliases/create", name="indices_aliases_create")
     */
    public function createAlias(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('GET', '/_cat/indices/'.$index, ['query' => $query]);

        if ($indice) {
            $form = $this->createForm(CreateAliasType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $query = [
                ];
                $indice = $this->queryManager->query('PUT', '/'.$index.'/_alias/'.$form->get('name')->getData(), ['query' => $query]);

                $this->addFlash('success', 'indices_aliases_create');

                return $this->redirectToRoute('indices_read_aliases', ['index' => $index]);
            }

            return $this->renderAbstract($request, 'Modules/indices/indices_read_aliases_create.html.twig', [
                'indice' => $indice[0],
                'form' => $form->createView(),
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("indices/{index}/aliases/{alias}/delete", name="indices_aliases_delete")
     */
    public function deleteAlias(Request $request, string $index, string $alias): Response
    {
        $query = [
        ];
        $this->queryManager->query('DELETE', '/'.$index.'/_alias/'.$alias, ['query' => $query]);

        $this->addFlash('success', 'indices_aliases_delete');

        return $this->redirectToRoute('indices_read_aliases', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/documents", name="indices_read_documents")
     */
    public function documents(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice1 = $this->queryManager->query('GET', '/_cat/indices/'.$index, ['query' => $query]);

        $query = [
        ];
        $indice2 = $this->queryManager->query('GET', '/'.$index, ['query' => $query]);

        $indice = array_merge($indice1[0], $indice2[key($indice2)]);

        $size = 100;
        $query = [
            'sort' => '_id:desc',
            'size' => $size,
            'from' => ($size * $request->query->get('page', 1)) - $size,
        ];
        $documents = $this->queryManager->query('GET', '/'.$index.'/_search', ['query' => $query]);

        if (true == isset($documents['hits']['total']['value'])) {
            $total = $documents['hits']['total']['value'];
            if ('eq' != $documents['hits']['total']['relation']) {
                $this->addFlash('info', 'lower_bound_of_the_total');
            }
        } else {
            $total = $documents['hits']['total'];
        }

        return $this->renderAbstract($request, 'Modules/indices/indices_read_documents.html.twig', [
            'indice' => $indice,
            'documents' => $this->paginatorManager->paginate([
                'route' => 'indices_read_documents',
                'route_parameters' => ['index' => $index],
                'total' => $total,
                'rows' => $documents['hits']['hits'],
                'page' => $request->query->get('page', 1),
                'size' => $size,
            ]),
        ]);
    }

    /**
     * @Route("/indices/{index}/delete", name="indices_delete")
     */
    public function delete(Request $request, string $index): Response
    {
        $query = [
        ];
        $this->queryManager->query('DELETE', '/'.$index, ['query' => $query]);

        $this->addFlash('success', 'indices_delete');

        return $this->redirectToRoute('indices', []);
    }

    /**
     * @Route("/indices/{index}/close", name="indices_close")
     */
    public function close(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('POST', '/'.$index.'/_close', ['query' => $query]);

        $this->addFlash('success', 'indices_close');

        return $this->redirectToRoute('indices_read', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/open", name="indices_open")
     */
    public function open(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('POST', '/'.$index.'/_open', ['query' => $query]);

        $this->addFlash('success', 'indices_open');

        return $this->redirectToRoute('indices_read', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/force/merge", name="indices_force_merge")
     */
    public function forceMerge(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('POST', '/'.$index.'/_forcemerge', ['query' => $query]);

        $this->addFlash('success', 'indices_force_merge');

        return $this->redirectToRoute('indices_read', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/cache/clear", name="indices_cache_clear")
     */
    public function cacheClear(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('POST', '/'.$index.'/_cache/clear', ['query' => $query]);

        $this->addFlash('success', 'indices_cache_clear');

        return $this->redirectToRoute('indices_read', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/flush", name="indices_flush")
     */
    public function flush(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('POST', '/'.$index.'/_flush', ['query' => $query]);

        $this->addFlash('success', 'indices_flush');

        return $this->redirectToRoute('indices_read', ['index' => $index]);
    }

    /**
     * @Route("/indices/{index}/refresh", name="indices_refresh")
     */
    public function refresh(Request $request, string $index): Response
    {
        $query = [
        ];
        $indice = $this->queryManager->query('POST', '/'.$index.'/_refresh', ['query' => $query]);

        $this->addFlash('success', 'indices_refresh');

        return $this->redirectToRoute('indices_read', ['index' => $index]);
    }
}
