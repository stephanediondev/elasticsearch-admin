<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\FilterCatType;
use App\Model\CallModel;
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

        $call = new CallModel();
        $call->setPath('/_cat/repositories');
        $call->setQuery(['s' => 'id', 'h' => 'id']);
        $rows = $this->callManager->call($call);

        foreach ($rows as $row) {
            $repositories[] = $row['id'];
        }

        $call = new CallModel();
        $call->setPath('/_cat/indices');
        $call->setQuery(['s' => 'index', 'h' => 'index']);
        $rows = $this->callManager->call($call);

        foreach ($rows as $row) {
            $indices[] = $row['index'];
        }

        $call = new CallModel();
        $call->setPath('/_cat/aliases');
        $call->setQuery(['s' => 'alias', 'h' => 'alias']);
        $rows = $this->callManager->call($call);

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
            $call = new CallModel();
            $call->setPath('/_cat/'.$cat->getCommandReplace());
            $call->setQuery($query);
            $parameters['rows'] = $this->callManager->call($call);
            if (0 < count($parameters['rows'])) {
                $parameters['headers'] = array_keys($parameters['rows'][0]);
            }

            $call = new CallModel();
            $call->setPath('/_cat/'.$cat->getCommandHelp());
            $call->setQuery(['help' => 'true', 'format' => 'text']);
            $parameters['help'] = $this->callManager->call($call);

            $parameters['command'] = $cat->getCommandReplace();
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }
}
