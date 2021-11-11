<?php

namespace App\Controller;

use App\Kernel;
use App\Controller\AbstractAppController;
use App\Model\AppUserModel;
use DeviceDetector\DeviceDetector;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class AppExceptionController extends AbstractAppController
{
    public function read(Request $request, FlattenException $exception, RequestStack $requestStack, Security $security, string $installationType)
    {
        $mainRequest = $requestStack->getMainRequest();

        $parameters = ['route' => $mainRequest->attributes->get('_route')];

        $codes = [401, 403, 404, 405, 500, 503];

        if (true === in_array($exception->getStatusCode(), $codes)) {
            $user = $security->getUser();

            if (!$user instanceof AppUserModel) {
                $parameters['firewall'] = false;
            }

            if (503 == $exception->getStatusCode()) {
                $parameters['exception_503'] = true;
                $parameters['message'] = $exception->getMessage();
            }

            if (500 == $exception->getStatusCode()) {
                $dd = new DeviceDetector($request->headers->get('User-Agent'));
                $dd->skipBotDetection();
                $dd->parse();

                $client = $dd->getClient();
                $os = $dd->getOs();

                $parameters['client'] = $client ? $client['name'].' '.$client['version'] : false;
                $parameters['os'] = $os ? $os['name'].' '.$os['version'] : false;

                $parameters['message'] = $exception->getMessage();
                $parameters['file'] = $exception->getFile();
                $parameters['line'] = $exception->getLine();

                $parameters['installation_type'] = $installationType;
                $parameters['php_version'] = phpversion();
                $parameters['symfony_version'] = Kernel::VERSION;
            }

            return $this->renderAbstract($request, 'Modules/exception/exception_'.$exception->getStatusCode().'.html.twig', $parameters);
        }
    }
}
