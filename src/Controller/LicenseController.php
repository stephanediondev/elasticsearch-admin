<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Model\CallRequestModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin")
 */
class LicenseController extends AbstractAppController
{
    /**
     * @Route("/license", name="license")
     */
    public function read(Request $request): Response
    {
        $call = new CallRequestModel();
        $call->setPath('/_license');
        $license = $this->callManager->call($call);
        $license = $license['license'];

        $call = new CallRequestModel();
        $call->setPath('/_license/trial_status');
        $trialStatus = $this->callManager->call($call);
        $trialStatus = $trialStatus['eligible_to_start_trial'];

        $call = new CallRequestModel();
        $call->setPath('/_license/basic_status');
        $basicStatus = $this->callManager->call($call);
        $basicStatus = $basicStatus['eligible_to_start_basic'];

        $call = new CallRequestModel();
        $call->setPath('/_xpack');
        $xpack = $this->callManager->call($call);

        return $this->renderAbstract($request, 'Modules/license/license_read.html.twig', [
            'license' => $license,
            'trial_status' => $trialStatus,
            'basic_status' => $basicStatus,
            'xpack' => $xpack,
        ]);
    }

    /**
     * @Route("/license/start/trial", name="license_start_trial")
     */
    public function startTrial(Request $request): Response
    {
        $call = new CallRequestModel();
        $call->setMethod('POST');
        $call->setPath('/_license/start_trial');
        $call->setQuery(['acknowledge' => 'true']);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.license_start_trial');

        return $this->redirectToRoute('license', []);
    }

    /**
     * @Route("/license/start/basic", name="license_start_basic")
     */
    public function startBasic(Request $request): Response
    {
        $call = new CallRequestModel();
        $call->setMethod('POST');
        $call->setPath('/_license/start_basic');
        $call->setQuery(['acknowledge' => 'true']);
        $this->callManager->call($call);

        $this->addFlash('success', 'success.license_start_basic');

        return $this->redirectToRoute('license', []);
    }
}
