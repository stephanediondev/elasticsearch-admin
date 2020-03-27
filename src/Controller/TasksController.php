<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class TasksController extends AbstractAppController
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function index(Request $request): Response
    {
        $tasks = [];

        $call = new CallModel();
        $call->setPath('/_tasks');
        $nodes = $this->callManager->call($call);

        foreach ($nodes['nodes'] as $node) {
            foreach ($node['tasks'] as $task) {
                $task['node'] = $node['name'];
                $tasks[$task['id']] = $task;
            }
        }

        krsort($tasks);

        return $this->renderAbstract($request, 'Modules/tasks/tasks_index.html.twig', [
            'tasks' => $this->paginatorManager->paginate([
                'route' => 'tasks',
                'route_parameters' => [],
                'total' => count($tasks),
                'rows' => $tasks,
                'page' => 1,
                'size' => count($tasks),
            ]),
        ]);
    }
}
