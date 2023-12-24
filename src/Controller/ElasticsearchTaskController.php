<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\Type\ElasticsearchTaskFilterType;
use App\Manager\ElasticsearchNodeManager;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/admin')]
class ElasticsearchTaskController extends AbstractAppController
{
    private ElasticsearchNodeManager $elasticsearchNodeManager;

    public function __construct(ElasticsearchNodeManager $elasticsearchNodeManager)
    {
        $this->elasticsearchNodeManager = $elasticsearchNodeManager;
    }

    #[Route('/tasks', name: 'tasks', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('TASKS', 'global');

        if (false === $this->callManager->hasFeature('tasks')) {
            throw new AccessDeniedException();
        }

        $nodes = $this->elasticsearchNodeManager->selectNodes();

        $form = $this->createForm(ElasticsearchTaskFilterType::class, null, ['node' => $nodes]);

        $form->handleRequest($request);

        $tasks = [];

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_tasks');
        if ($form->get('node')->getData()) {
            $callRequest->setQuery(['nodes' => implode(',', $form->get('node')->getData())]);
        }
        $callResponse = $this->callManager->call($callRequest);
        $nodes = $callResponse->getContent();

        if (true === isset($nodes['nodes'])) {
            foreach ($nodes['nodes'] as $node) {
                if (true === isset($node['tasks'])) {
                    foreach ($node['tasks'] as $task) {
                        $task['node'] = $node['name'];
                        $tasks[] = $task;
                    }
                }
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
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param array<mixed> $a
     * @param array<mixed> $b
     */
    private function sortByStartTime(array $a, array $b): int
    {
        return $b['start_time_in_millis'] <=> $a['start_time_in_millis'];
    }
}
