<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Manager\ElasticsearchIndexManager;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class IndexStatsController extends AbstractAppController
{
    public function __construct(ElasticsearchIndexManager $elasticsearchIndexManager)
    {
        $this->elasticsearchIndexManager = $elasticsearchIndexManager;
    }

    /**
     * @Route("/indices/stats", priority=10, name="indices_stats")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('INDICES_STATS', 'global');

        $query = [
            'bytes' => 'b',
            'h' => 'index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string,sth',
        ];

        if (true == $this->callManager->hasFeature('cat_sort')) {
            $query['s'] = $request->query->get('s', 'index:asc');
        }

        if (true == $this->callManager->hasFeature('cat_expand_wildcards')) {
            $query['expand_wildcards'] = 'all';
        }

        $indices = $this->elasticsearchIndexManager->getAll($query);

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['indices_total'] = 0;
        $data['totals']['indices_total_documents'] = 0;
        $data['totals']['indices_total_size'] = 0;

        $tables = [
            'indices_by_status',
            'indices_by_health',
            'indices_by_documents',
            'indices_by_total_size',
        ];

        foreach ($indices as $index) {
            $data['totals']['indices_total']++;

            $data['totals']['indices_total_documents'] += $index->getDocuments();
            $data['totals']['indices_total_size'] += $index->getTotalSize();

            foreach ($tables as $table) {
                switch ($table) {
                    case 'indices_by_documents':
                        $data['tables'][$table]['results'][] = ['total' => $index->getDocuments(), 'title' => $index->getName()];
                        break;
                    case 'indices_by_total_size':
                        $data['tables'][$table]['results'][] = ['total' => $index->getTotalSize(), 'title' => $index->getName()];
                        break;
                    case 'indices_by_status':
                    case 'indices_by_health':
                        switch ($table) {
                            case 'indices_by_status':
                                $key = $index->getStatus();
                                break;
                            case 'indices_by_health':
                                $key = $index->getHealth();
                                break;
                            default:
                                $key = false;
                        }
                        if ($key) {
                            if (false == isset($data['tables'][$table]['results'][$key])) {
                                $data['tables'][$table]['results'][$key] = ['total' => 0, 'title' => $key];
                            }
                            $data['tables'][$table]['results'][$key]['total']++;
                        }
                        break;
                }
            }
        }

        foreach ($tables as $table) {
            usort($data['tables'][$table]['results'], [$this, 'sortByTotal']);
        }

        return $this->renderAbstract($request, 'Modules/index/index_stats.html.twig', [
            'data' => $data,
        ]);
    }

    private function sortByTotal($a, $b)
    {
        return $b['total'] - $a['total'];
    }
}
