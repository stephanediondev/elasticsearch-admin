<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\RequestType;
use App\Model\CallModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class RequestController extends AbstractAppController
{
    /**
     * @Route("/request", name="request")
     */
    public function index(Request $request): Response
    {
        $callModel = new CallModel();
        $form = $this->createForm(RequestType::class, $callModel);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $parameters['response'] = $this->callManager->call($callModel);

            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/request/request_index.html.twig', $parameters);
    }
}
