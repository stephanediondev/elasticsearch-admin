<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

class ExceptionController extends AbstractAppController
{
    public function read(Request $request, FlattenException $exception, RequestStack $requestStack)
    {
        $parameters = [
            'locale' => $requestStack->getMasterRequest()->getLocale(),
        ];

        $codes = [401, 403, 404, 405, 500, 503];

        if (true == in_array($exception->getStatusCode(), $codes)) {
            if (500 == $exception->getStatusCode()) {
                $parameters['message'] = $exception->getMessage();
                $parameters['file'] = $exception->getFile();
                $parameters['line'] = $exception->getLine();
            }
            return $this->renderAbstract($request, 'Modules/exception/exception_'.$exception->getStatusCode().'.html.twig', $parameters);
        }
    }
}
