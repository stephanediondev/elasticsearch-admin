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

/**
 * @Route("/admin")
 */
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

        $catModel = new ElasticsearchCatModel();
        $form = $this->createForm(FilterCatType::class, $catModel, ['repositories' => $repositories, 'indices' => $indices, 'aliases' => $aliases]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $query = [];
                if ($catModel->getHeaders()) {
                    $query['h'] = $catModel->getHeaders();
                }
                if ($catModel->getSort()) {
                    $query['s'] = $catModel->getSort();
                }
                $call = new CallModel();
                $call->setPath('/_cat/'.$catModel->getCommandReplace());
                $call->setQuery($query);
                $parameters['rows'] = $this->callManager->call($call);
                if (0 < count($parameters['rows'])) {
                    $parameters['headers'] = array_keys($parameters['rows'][0]);
                }
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            $call = new CallModel();
            $call->setPath('/_cat/'.$catModel->getCommandHelp());
            $call->setQuery(['help' => 'true', 'format' => 'text']);
            $parameters['help'] = $this->callManager->call($call);

            $parameters['command'] = $catModel->getCommandReplace();
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }
}
