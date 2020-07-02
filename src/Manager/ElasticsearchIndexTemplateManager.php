<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchIndexTemplateModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexTemplateManager extends AbstractAppManager
{
    public function getByName(string $name)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_index_template/'.$name.'?flat_settings=true');
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $templateModel = false;
        } else {
            $template = $callResponse->getContent();
            $templateModel = new ElasticsearchIndexTemplateModel();
            $templateModel->convert($template['index_templates'][0]);
        }

        return $templateModel;
    }

    public function send(ElasticsearchIndexTemplateModel $templateModel)
    {
        $json = $templateModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_index_template/'.$templateModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }
}
