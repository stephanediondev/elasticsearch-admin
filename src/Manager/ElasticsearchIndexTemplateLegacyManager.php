<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchIndexTemplateLegacyModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexTemplateLegacyManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchIndexTemplateLegacyModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name.'?flat_settings=true');
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $templateModel = null;
        } else {
            $template = $callResponse->getContent();
            $template = $template[$name];
            $template['name'] = $name;

            $templateModel = new ElasticsearchIndexTemplateLegacyModel();
            $templateModel->convert($template);
        }

        return $templateModel;
    }

    public function getAll(): array
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template?flat_settings=true');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        ksort($results);

        $templates = [];
        foreach ($results as $key => $row) {
            $templateModel = new ElasticsearchIndexTemplateLegacyModel();
            $row['name'] = $key;
            $templateModel->convert($row);
            $templates[] = $templateModel;
        }

        return $templates;
    }

    public function send(ElasticsearchIndexTemplateLegacyModel $templateModel): CallResponseModel
    {
        $json = $templateModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_template/'.$templateModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_template/'.$name);

        return $this->callManager->call($callRequest);
    }
}
