<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\Type\ElasticsearchConsoleType;
use App\Model\CallRequestModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class ElasticsearchConsoleController extends AbstractAppController
{
    #[Route('/console', name: 'console')]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('CONSOLE', 'global');

        $parameters = [];

        $testMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

        $methods = [];
        $methods[] = 'GET';
        foreach ($testMethods as $testMethod) {
            if (true === $this->isGranted('CONSOLE_'.$testMethod, 'global')) {
                $methods[] = $testMethod;
            }
        }
        $methods[] = 'HEAD';
        $methods[] = 'OPTIONS';

        $callRequest = new CallRequestModel();
        $form = $this->createForm(ElasticsearchConsoleType::class, $callRequest, ['methods' => $methods]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $callResponse = $this->callManager->call($callRequest);
                $parameters['response'] = $callResponse->getContent();
                $parameters['response_code'] = $callResponse->getCode();
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
