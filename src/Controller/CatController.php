<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Form\FilterCatType;
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
        $parameters = [];

        $form = $this->createForm(FilterCatType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = $form->get('command')->getData();

            $query = [];
            if ($form->has('headers') && $form->get('headers')->getData()) {
                $query['h'] = $form->get('headers')->getData();
            }
            if ($form->has('sort') && $form->get('sort')->getData()) {
                $query['s'] = $form->get('sort')->getData();
            }
            $parameters['rows'] = $this->queryManager->query('GET', '/_cat/'.$command, ['query' => $query]);
            if (0 < count($parameters['rows'])) {
                $parameters['headers'] = array_keys($parameters['rows'][0]);
            }

            $query = ['help' => 'true', 'format' => 'text'];
            $parameters['help'] = $this->queryManager->query('GET', '/_cat/'.$command, ['query' => $query]);

            $parameters['command'] = $command;
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/cat/cat_index.html.twig', $parameters);
    }
}
