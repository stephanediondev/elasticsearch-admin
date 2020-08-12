<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AppOfflineController extends AbstractAppController
{
    /**
     * @Route("/offline", name="offline")
     */
    public function index(Request $request): Response
    {
        return $this->renderAbstract($request, 'Modules/offline/offline_index.html.twig');
    }
}
