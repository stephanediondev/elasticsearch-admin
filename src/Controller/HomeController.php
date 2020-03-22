<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractAppController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request): Response
    {
        $query = [
        ];
        $clusterStats = $this->queryManager->query('GET', '/_cluster/stats', ['query' => $query]);

        return $this->renderAbstract($request, 'home_index.html.twig', [
            'indices' => $clusterStats['indices']['count'],
            'documents' => $clusterStats['indices']['docs']['count'],
            'store_size' => $clusterStats['indices']['store']['size_in_bytes'],
        ]);
    }
}
