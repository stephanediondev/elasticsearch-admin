<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchEnrichPolicyModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchEnrichPolicyManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchEnrichPolicyModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        $results = $callResponse->getContent();

        if (false === isset($results['policies']) || 0 == count($results['policies'])) {
            $policyModel = null;
        } else {
            foreach ($results['policies'] as $row) {
                $policyModel = new ElasticsearchEnrichPolicyModel();
                $policyModel->convert($row);
            }
        }

        return $policyModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/policy');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $policies = [];
        foreach ($results['policies'] as $row) {
            $policyModel = new ElasticsearchEnrichPolicyModel();
            $policyModel->convert($row);
            $policies[] = $policyModel;
        }

        return $policies;
    }

    public function send(ElasticsearchEnrichPolicyModel $policyModel): CallResponseModel
    {
        $json = $policyModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_enrich/policy/'.$policyModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_enrich/policy/'.$name);

        return $this->callManager->call($callRequest);
    }

    public function executeByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        $callRequest->setPath('/_enrich/policy/'.$name.'/_execute');

        return $this->callManager->call($callRequest);
    }

    public function getStats(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_enrich/_stats');
        $callResponse = $this->callManager->call($callRequest);

        return $callResponse->getContent();
    }
}
