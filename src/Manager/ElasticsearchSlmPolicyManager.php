<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchSlmPolicyModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchSlmPolicyManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchSlmPolicyModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $policyModel = null;
        } else {
            $policy = $callResponse->getContent();
            $policy = $policy[$name];
            $policy['name'] = $name;

            $policyModel = new ElasticsearchSlmPolicyModel();
            $policyModel->convert($policy);
        }

        return $policyModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/policy');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $policies = [];
        if ($results) {
            foreach ($results as $k => $row) {
                $row['name'] = $k;
                $policyModel = new ElasticsearchSlmPolicyModel();
                $policyModel->convert($row);
                $policies[] = $policyModel;
            }
            usort($policies, [$this, 'sortByName']);
        }

        return $policies;
    }

    private function sortByName(ElasticsearchSlmPolicyModel $a, ElasticsearchSlmPolicyModel $b): int
    {
        return $a->getName() <=> $b->getName();
    }

    public function send(ElasticsearchSlmPolicyModel $policyModel): CallResponseModel
    {
        $json = $policyModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_slm/policy/'.$policyModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_slm/policy/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function executeByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/policy/'.$name.'/_execute');

        return $this->callManager->call($callRequest);
    }

    public function getStats(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/stats');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }

    public function getStatus(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_slm/status');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }

    public function start(): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/start');

        return $this->callManager->call($callRequest);
    }

    public function stop(): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_slm/stop');

        return $this->callManager->call($callRequest);
    }
}
