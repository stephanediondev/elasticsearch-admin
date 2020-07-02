<?php

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\ElasticsearchComponentTemplateModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchComponentTemplateManager extends AbstractAppManager
{
    public function getByName(string $name)
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template/'.$name);
        $callResponse = $this->callManager->call($callRequest);

        if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
            $template = false;
        } else {
            $template = $callResponse->getContent();
            $template = $template['component_templates'][0];
            $template['is_system'] = '.' == substr($template['name'], 0, 1);
        }

        return $template;
    }

    public function getAll()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        return $results['component_templates'];
    }

    public function send(ElasticsearchComponentTemplateModel $templateModel)
    {
        $json = $templateModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_component_template/'.$templateModel->getName());
        $callRequest->setBody(json_encode($json, JSON_FORCE_OBJECT));

        return $this->callManager->call($callRequest);
    }
}
