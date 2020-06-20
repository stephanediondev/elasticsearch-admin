<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\ConsoleType;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class ConsoleController extends AbstractAppController
{
    /**
     * @Route("/console", name="console")
     */
    public function index(Request $request): Response
    {
        $parameters = [];

        $callRequest = new CallRequestModel();
        $form = $this->createForm(ConsoleType::class, $callRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->callManager->call($callRequest);
                $parameters['response'] = $callResponse->getContent();
                $parameters['method'] = $callRequest->getMethod();
                $parameters['path'] = $callRequest->getPath();
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        $parameters['form'] = $form->createView();

        return $this->renderAbstract($request, 'Modules/console/console_index.html.twig', $parameters);
    }
}
