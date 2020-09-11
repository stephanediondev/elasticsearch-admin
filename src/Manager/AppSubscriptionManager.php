<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\AppSubscriptionModel;
use Symfony\Component\HttpFoundation\Response;

class AppSubscriptionManager extends AbstractAppManager
{
    public function getById(string $id): ?AppSubscriptionModel
    {
        $subscriptionModel = null;

        $callRequest = new CallRequestModel();
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-subscriptions/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-subscriptions/doc/'.$id);
        }
        $callResponse = $this->callManager->call($callRequest);
        $row = $callResponse->getContent();

        if ($row) {
            $subscription = ['id' => $row['_id']];
            $subscription = array_merge($subscription, $row['_source']);

            $subscriptionModel = new AppSubscriptionModel();
            $subscriptionModel->convert($subscription);
        }

        return $subscriptionModel;
    }

    public function getAll(?array $query = []): array
    {
        $query['size'] = 1000;

        $callRequest = new CallRequestModel();
        $callRequest->setPath('/.elasticsearch-admin-subscriptions/_search');
        $callRequest->setQuery($query);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        $subscriptions = [];

        if ($results && 0 < count($results['hits']['hits'])) {
            foreach ($results['hits']['hits'] as $row) {
                $subscription = ['id' => $row['_id']];
                $subscription = array_merge($subscription, $row['_source']);

                $subscriptionModel = new AppSubscriptionModel();
                $subscriptionModel->convert($subscription);
                $subscriptions[] = $subscriptionModel;
            }
            usort($subscriptions, [$this, 'sortByCreatedAt']);
        }

        return $subscriptions;
    }

    private function sortByCreatedAt($a, $b)
    {
        return $b->getCreatedAt()->format('Y-m-d H:i:s') > $a->getCreatedAt()->format('Y-m-d H:i:s');
    }

    public function send(AppSubscriptionModel $subscriptionModel): CallResponseModel
    {
        $json = $subscriptionModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('POST');
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-subscriptions/_doc');
        } else {
            $callRequest->setPath('/.elasticsearch-admin-subscriptions/doc/');
        }
        $callRequest->setJson($json);
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }

    public function deleteById(string $id): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        if (true === $this->callManager->hasFeature('_doc_as_type')) {
            $callRequest->setPath('/.elasticsearch-admin-subscriptions/_doc/'.$id);
        } else {
            $callRequest->setPath('/.elasticsearch-admin-subscriptions/doc/'.$id);
        }
        $callRequest->setMethod('DELETE');
        $callRequest->setQuery(['refresh' => 'true']);

        return $this->callManager->call($callRequest);
    }
}