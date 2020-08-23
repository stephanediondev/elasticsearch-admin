<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/admin")
 */
class ElasticsearchTaskController extends AbstractAppController
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('TASKS', 'global');

        if (false === $this->callManager->hasFeature('tasks')) {
            throw new AccessDeniedException();
        }

        $tasks = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_tasks');
        $callResponse = $this->callManager->call($callRequest);
        $nodes = $callResponse->getContent();

        foreach ($nodes['nodes'] as $node) {
            foreach ($node['tasks'] as $task) {
                $task['node'] = $node['name'];
                $tasks[] = $task;
            }
        }

        usort($tasks, [$this, 'sortByStartTime']);

        return $this->renderAbstract($request, 'Modules/task/task_index.html.twig', [
            'tasks' => $this->paginatorManager->paginate([
                'route' => 'tasks',
                'route_parameters' => [],
                'total' => count($tasks),
                'rows' => $tasks,
                'array_slice' => true,
                'page' => $request->query->get('page'),
                'size' => 100,
            ]),
        ]);
    }

    private function sortByStartTime($a, $b)
    {
        return $b['start_time_in_millis'] - $a['start_time_in_millis'];
    }
}
