<?php

namespace App\Controller;

use App\Controller\AbstractAppController;
use App\Exception\CallException;
use App\Form\CreateSlmPolicyType;
use App\Model\CallModel;
use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SlmController extends AbstractAppController
{
    /**
     * @Route("slm", name="slm")
     */
    public function index(Request $request): Response
    {
        $policies = [];

        $call = new CallModel();
        $call->setPath('/_slm/policy');
        $rows = $this->callManager->call($call);

        foreach ($rows as $k => $row) {
            $row['name'] = $k;
            $policies[] = $row;
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_index.html.twig', [
            'policies' => $this->paginatorManager->paginate([
                'route' => 'policies',
                'route_parameters' => [],
                'total' => count($policies),
                'rows' => $policies,
                'page' => 1,
                'size' => count($policies),
            ]),
        ]);
    }

    /**
     * @Route("/slm/create", name="slm_create")
     */
    public function create(Request $request): Response
    {
        $repositories = $this->callManager->selectRepositories();
        $indices = $this->callManager->selectIndices();

        $policy = new ElasticsearchSlmPolicyModel();
        if ($request->query->get('repository')) {
            $policy->setRepository($request->query->get('repository'));
        }
        if ($request->query->get('index')) {
            $policy->setIndices([$request->query->get('index')]);
        }
        $form = $this->createForm(CreateSlmPolicyType::class, $policy, ['repositories' => $repositories, 'indices' => $indices]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $body = [
                    'schedule' => $policy->getSchedule(),
                    'name' => $policy->getSnapshotName(),
                    'repository' => $policy->getRepository(),
                ];
                if ($policy->getIndices()) {
                    $body['config']['indices'] = $policy->getIndices();
                } else {
                    $body['config']['indices'] = ['*'];
                }
                if ($policy->hasRetention()) {
                    $body['retention'] = $policy->getRetention();
                }

                $call = new CallModel();
                $call->setMethod('PUT');
                $call->setPath('/_slm/policy/'.$policy->getName());
                $call->setBody($body);
                $this->callManager->call($call);

                $this->addFlash('success', 'slm_create');

                return $this->redirectToRoute('slm_read', ['name' => $policy->getName()]);
            } catch (CallException $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->renderAbstract($request, 'Modules/slm/slm_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/slm/{name}", name="slm_read")
     */
    public function read(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_slm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            return $this->renderAbstract($request, 'Modules/slm/slm_read.html.twig', [
                'policy' => $policy,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/history", name="slm_read_history")
     */
    public function readHistory(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_slm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            return $this->renderAbstract($request, 'Modules/slm/slm_read_history.html.twig', [
                'policy' => $policy,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/stats", name="slm_read_stats")
     */
    public function readStats(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setPath('/_slm/policy/'.$name);
        $policy = $this->callManager->call($call);
        $policy = $policy[$name];
        $policy['name'] = $name;

        if ($policy) {
            return $this->renderAbstract($request, 'Modules/slm/slm_read_stats.html.twig', [
                'policy' => $policy,
            ]);
        } else {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @Route("/slm/{name}/delete", name="slm_delete")
     */
    public function delete(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setMethod('DELETE');
        $call->setPath('/_slm/policy/'.$name);
        $this->callManager->call($call);

        $this->addFlash('success', 'slm_delete');

        return $this->redirectToRoute('slm', []);
    }

    /**
     * @Route("/slm/{name}/exexute", name="slm_execute")
     */
    public function exexute(Request $request, string $name): Response
    {
        $call = new CallModel();
        $call->setMethod('POST');
        $call->setPath('/_slm/policy/'.$name.'/_execute');
        $this->callManager->call($call);

        $this->addFlash('success', 'slm_execute');

        return $this->redirectToRoute('slm_read', ['name' => $name]);
    }
}
