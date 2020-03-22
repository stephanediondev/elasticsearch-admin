<?php

namespace App\Controller;

use App\Manager\QueryManager;
use App\Manager\PaginatorManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAppController extends AbstractController
{
    /**
     * @required
     */
    public function setQueryManager(QueryManager $queryManager)
    {
        $this->queryManager = $queryManager;
    }

    /**
     * @required
     */
    public function setPaginatorManager(PaginatorManager $paginatorManager)
    {
        $this->paginatorManager = $paginatorManager;
    }

    public function addFlashs($results)
    {
        foreach ($results as $type => $messages) {
            foreach ($messages as $message) {
                $this->addFlash($type, $message);
            }
        }
    }

    public function renderAbstract(Request $request, string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters['locale'] = $request->getLocale();
        $parameters['routeAttribute'] = $request->attributes->get('_route');

        $query = [
        ];
        $clusterHealth = $this->queryManager->query('GET', '/_cluster/health', ['query' => $query]);
        $parameters['clusterHealth'] = $clusterHealth;

        /*if (null === $response) {
            $response = new Response();
        }
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);*/

        return $this->render($view, $parameters, $response);
    }
}
