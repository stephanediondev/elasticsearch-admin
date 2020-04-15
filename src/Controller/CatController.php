<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\FilterCatType;
use App\Manager\ElasticsearchIndexManager;
use App\Manager\ElasticsearchRepositoryManager;
use App\Model\CallRequestModel;
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
    public function index(Request $request, ElasticsearchIndexManager $elasticsearchIndexManager, ElasticsearchRepositoryManager $elasticsearchRepositoryManager): Response
    {
        $repositories = $elasticsearchRepositoryManager->selectRepositories();
        $indices = $elasticsearchIndexManager->selectIndices();
        $aliases = $elasticsearchIndexManager->selectAliases();

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
                $callRequest = new CallRequestModel();
                $callRequest->setPath('/_cat/'.$catModel->getCommandReplace());
                $callRequest->setQuery($query);
                $callResponse = $this->callManager->call($callRequest);
                $parameters['rows'] = $callResponse->getContent();
                if (0 < count($parameters['rows'])) {
                    $parameters['headers'] = array_keys($parameters['rows'][0]);
                }
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_cat/'.$catModel->getCommandHelp());
            $callRequest->setQuery(['help' => 'true', 'format' => 'text']);
            $callResponse = $this->callManager->call($callRequest);
            $parameters['help'] = $callResponse->getContentRaw();

            $parameters['command'] = $catModel->getCommandReplace();
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }
}
