<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
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
        $repositories = $this->callManager->selectRepositories();
        $indices = $this->callManager->selectIndices();
        $aliases = $this->callManager->selectAliases();

        $parameters = [];

        $cat = new ElasticsearchCatModel();
        $form = $this->createForm(FilterCatType::class, $cat, ['repositories' => $repositories, 'indices' => $indices, 'aliases' => $aliases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
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
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
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
