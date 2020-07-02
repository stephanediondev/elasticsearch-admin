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
            $templateModel = false;
        } else {
            $template = $callResponse->getContent();
            $templateModel = new ElasticsearchComponentTemplateModel();
            $templateModel->convert($template['component_templates'][0]);
        }

        return $templateModel;
    }

    public function getAll()
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_component_template?flat_settings=true');
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();
        $results = $results['component_templates'];
        usort($results, [$this, 'sortByName']);

        $templates = [];
        foreach ($results as $row) {
            $templateModel = new ElasticsearchComponentTemplateModel();
            $templateModel->convert($row);
            $templates[] = $templateModel;
        }

        return $templates;
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

    private function sortByName($a, $b) {
        return $b['name'] < $a['name'];
    }
}
