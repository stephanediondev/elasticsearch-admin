<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class IndexStatsController extends AbstractAppController
{
    /**
     * @Route("/indices/stats", priority=10, name="indices_stats")
     */
    public function index(Request $request): Response
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_cat/indices');
        $callRequest->setQuery(['bytes' => 'b', 'h' => 'index,docs.count,docs.deleted,pri.store.size,store.size,status,health,pri,rep,creation.date.string,sth']);
        $callResponse = $this->callManager->call($callRequest);
        $indices = $callResponse->getContent();

        $data = ['totals' => [], 'tables' => []];
        $data['totals']['indices_total'] = 0;
        $data['totals']['indices_total_documents'] = 0;
        $data['totals']['indices_total_size'] = 0;

        $tables = [
            'indices_by_status' => 'status',
            'indices_by_health' => 'health',
            'indices_by_documents' => 'docs.count',
            'indices_by_total_size' => 'store.size',
        ];

        foreach ($indices as $index) {
            $data['totals']['indices_total']++;

            $data['totals']['indices_total_documents'] += $index['docs.count'];
            $data['totals']['indices_total_size'] += $index['store.size'];

            foreach ($tables as $key => $table) {
                switch ($key) {
                    case 'indices_by_documents':
                    case 'indices_by_total_size':
                        $data['tables'][$key]['results'][] = ['total' => $index[$table], 'title' => $index['index']];
                        break;
                    default:
                        if (false == isset($data['tables'][$key]['results'][$index[$table]])) {
                            $data['tables'][$key]['results'][$index[$table]] = ['total' => 0, 'title' => $index[$table]];
                        }
                        $data['tables'][$key]['results'][$index[$table]]['total']++;
                        break;

                }
            }
        }

        foreach ($tables as $key => $table) {
            usort($data['tables'][$key]['results'], [$this, 'sortByTotal']);
        }

        return $this->renderAbstract($request, 'Modules/index/index_stats.html.twig', [
            'data' => $data,
        ]);
    }

    private function sortByTotal($a, $b) {
        return $b['total'] - $a['total'];
    }
}
