<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\FilterCatType;
use App\Model\ElasticsearchCatModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CatController extends AbstractAppController
{
    /**
     * @Route("/cat", name="cat")
     */
    public function index(Request $request): Response
    {
        $repositories = [];
        $indices = [];
        $aliases = [];

        $query = [
            's' => 'id',
            'h' => 'id'
        ];
        $rows = $this->queryManager->query('GET', '/_cat/repositories', ['query' => $query]);

        foreach ($rows as $row) {
            $repositories[] = $row['id'];
        }

        $query = [
            's' => 'index',
            'h' => 'index'
        ];
        $rows = $this->queryManager->query('GET', '/_cat/indices', ['query' => $query]);

        foreach ($rows as $row) {
            $indices[] = $row['index'];
        }

        $query = [
            's' => 'alias',
            'h' => 'alias'
        ];
        $rows = $this->queryManager->query('GET', '/_cat/aliases', ['query' => $query]);

        foreach ($rows as $row) {
            $aliases[] = $row['alias'];
        }
        $aliases = array_unique($aliases);

        $parameters = [];

        $cat = new ElasticsearchCatModel();
        $form = $this->createForm(FilterCatType::class, $cat, ['repositories' => $repositories, 'indices' => $indices, 'aliases' => $aliases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = [];
            if ($cat->getHeaders()) {
                $query['h'] = $cat->getHeaders();
            }
            if ($cat->getSort()) {
                $query['s'] = $cat->getSort();
            }
            $parameters['rows'] = $this->queryManager->query('GET', '/_cat/'.$cat->getCommandReplace(), ['query' => $query]);
            if (0 < count($parameters['rows'])) {
                $parameters['headers'] = array_keys($parameters['rows'][0]);
            }

            $query = ['help' => 'true', 'format' => 'text'];
            $parameters['help'] = $this->queryManager->query('GET', '/_cat/'.$cat->getCommandHelp(), ['query' => $query]);

            $parameters['command'] = $cat->getCommandReplace();
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }
}
