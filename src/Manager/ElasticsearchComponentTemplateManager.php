<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\CallException;
use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchComponentTemplateModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchComponentTemplateManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchComponentTemplateModel
    {
        try {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_component_template/'.$name);
            $callRequest->setQuery(['flat_settings' => 'true']);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                $templateModel = null;
            } else {
                $template = $callResponse->getContent();

                $templateModel = new ElasticsearchComponentTemplateModel();
                $templateModel->convert($template['component_templates'][0]);
            }
        } catch (CallException $e) {
            $templateModel = null;
        }

        return $templateModel;
    }

    public function getAll(array $filter = []): array
    {
        $templates = [];

        $callRequest = new CallRequestModel();
        if (true === isset($filter['name']) && '' != $filter['name']) {
            $callRequest->setPath('/_component_template/'.$filter['name']);
        } else {
            $callRequest->setPath('/_component_template');
        }
        $callRequest->setQuery(['flat_settings' => 'true']);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results) {
            $results = $results['component_templates'];
            usort($results, [$this, 'sortByName']);

            foreach ($results as $row) {
                $templateModel = new ElasticsearchComponentTemplateModel();
                $templateModel->convert($row);
                $templates[] = $templateModel;
            }
        }

        return $this->filter($templates, $filter);
    }

    public function filter(array $templates, array $filter = []): array
    {
        $templatesWithFilter = [];

        foreach ($templates as $row) {
            $score = 0;

            if (true === isset($filter['managed'])) {
                if ('yes' === $filter['managed'] && false === $row->isManaged()) {
                    $score--;
                }
                if ('no' === $filter['managed'] && true === $row->isManaged()) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $templatesWithFilter[] = $row;
            }
        }

        return $templatesWithFilter;
    }

    public function send(ElasticsearchComponentTemplateModel $templateModel): CallResponseModel
    {
        $json = $templateModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_component_template/'.$templateModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_component_template/'.$name);

        return $this->callManager->call($callRequest);
    }

    private function sortByName(array $a, array $b): int
    {
        return $a['name'] <=> $b['name'];
    }
}
