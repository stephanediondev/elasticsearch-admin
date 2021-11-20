<?php
declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchIlmPolicyModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIlmPolicyManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchIlmPolicyModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $policyModel = null;
        } else {
            $policy = $callResponse->getContent();
            $policy = $policy[$name];
            $policy['name'] = $name;

            $policyModel = new ElasticsearchIlmPolicyModel();
            $policyModel->convert($policy);
        }

        return $policyModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/policy');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $policies = [];
        foreach ($results as $k => $row) {
            $row['name'] = $k;
            $policyModel = new ElasticsearchIlmPolicyModel();
            $policyModel->convert($row);
            $policies[] = $policyModel;
        }
        usort($policies, [$this, 'sortByName']);

        return $policies;
    }

    private function sortByName(ElasticsearchIlmPolicyModel $a, ElasticsearchIlmPolicyModel $b): int
    {
        return $a->getName() <=> $b->getName();
    }

    public function send(ElasticsearchIlmPolicyModel $policyModel): CallResponseModel
    {
        $json = $policyModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_ilm/policy/'.$policyModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_ilm/policy/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function getStatus(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_ilm/status');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }

    public function start(): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_ilm/start');

        return $this->callManager->call($callRequest);
    }

    public function stop(): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_ilm/stop');

        return $this->callManager->call($callRequest);
    }
}
