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
class TaskController extends AbstractAppController
{
    /**
     * @Route("/tasks", name="tasks")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('TASKS', 'global');

        $tasks = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_tasks');
        $callResponse = $this->callManager->call($callRequest);
        $nodes = $callResponse->getContent();

        foreach ($nodes['nodes'] as $node) {
            foreach ($node['tasks'] as $task) {
                $task['node'] = $node['name'];
                $tasks[$task['id']] = $task;
            }
        }

        krsort($tasks);

        return $this->renderAbstract($request, 'Modules/task/task_index.html.twig', [
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
