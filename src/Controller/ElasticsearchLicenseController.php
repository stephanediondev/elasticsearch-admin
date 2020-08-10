<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/admin")
 */
class ElasticsearchLicenseController extends AbstractAppController
{
    /**
     * @Route("/license", name="license")
     */
    public function read(Request $request): Response
    {
        $this->denyAccessUnlessGranted('LICENSE', 'global');

        if (false === $this->callManager->hasFeature('license')) {
            throw new AccessDeniedHttpException();
        }

        if (false === $this->callManager->hasFeature('_xpack_endpoint_removed')) {
            $this->endpoint = '_xpack/license';
        } else {
            $this->endpoint = '_license';
        }

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/'.$this->endpoint);
        $callResponse = $this->callManager->call($callRequest);
        $license = $callResponse->getContent();
        $license = $license['license'];

        if (false === $this->callManager->hasFeature('license_status')) {
            $trialStatus = false;
            $basicStatus = false;
        } else {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.$this->endpoint.'/trial_status');
            $callResponse = $this->callManager->call($callRequest);
            $trialStatus = $callResponse->getContent();
            $trialStatus = $trialStatus['eligible_to_start_trial'];

            $callRequest = new CallRequestModel();
            $callRequest->setPath('/'.$this->endpoint.'/basic_status');
            $callResponse = $this->callManager->call($callRequest);
            $basicStatus = $callResponse->getContent();
            $basicStatus = $basicStatus['eligible_to_start_basic'];
        }

        return $this->renderAbstract($request, 'Modules/license/license_read.html.twig', [
            'license' => $license,
            'trial_status' => $trialStatus,
            'basic_status' => $basicStatus,
            'features_by_version' => $this->callManager->getFeaturesByVersion(),
        ]);
    }

    /**
     * @Route("/license/start/trial", name="license_start_trial")
     */
    public function startTrial(Request $request): Response
    {
        $this->denyAccessUnlessGranted('LICENSE_START_TRIAL', 'global');

        if (false === $this->callManager->hasFeature('license_status')) {
            throw new AccessDeniedHttpException();
        }

        if (false === $this->callManager->hasFeature('_xpack_endpoint_removed')) {
            $this->endpoint = '_xpack/license';
        } else {
            $this->endpoint = '_license';
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$this->endpoint.'/start_trial');
        $callRequest->setQuery(['acknowledge' => 'true']);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('license');
    }

    /**
     * @Route("/license/start/basic", name="license_start_basic")
     */
    public function startBasic(Request $request): Response
    {
        $this->denyAccessUnlessGranted('LICENSE_START_BASIC', 'global');

        if (false === $this->callManager->hasFeature('license_status')) {
            throw new AccessDeniedHttpException();
        }

        if (false === $this->callManager->hasFeature('_xpack_endpoint_removed')) {
            $this->endpoint = '_xpack/license';
        } else {
            $this->endpoint = '_license';
        }

        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/'.$this->endpoint.'/start_basic');
        $callRequest->setQuery(['acknowledge' => 'true']);
        $callResponse = $this->callManager->call($callRequest);

        $this->addFlash('info', json_encode($callResponse->getContent()));

        return $this->redirectToRoute('license');
    }
}
