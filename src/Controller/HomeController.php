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
        $catCount = $this->queryManager->query('GET', '/_cat/count', ['query' => $query]);

        return $this->renderAbstract($request, 'home_index.html.twig', [
            'catCount' => $catCount,
        ]);
    }
}
