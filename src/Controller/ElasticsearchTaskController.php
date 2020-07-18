<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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

        if (false == $this->callManager->checkVersion('2.3')) {
            throw new AccessDeniedHttpException();
        }

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
