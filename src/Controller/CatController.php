<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\CatType;
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
        $form = $this->createForm(CatType::class);

        $form->handleRequest($request);

        return $this->renderAbstract($request, 'cat_index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
