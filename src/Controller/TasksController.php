<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TasksController extends AbstractAppController
{
    /**
     * @Route("tasks", name="tasks")
     */
    public function index(Request $request): Response
    {
        $tasks = [];

        $query = [
        ];
        $nodes = $this->queryManager->query('GET', '/_tasks', ['query' => $query]);

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
