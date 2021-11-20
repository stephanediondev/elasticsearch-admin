<?php
declare(strict_types=1);

namespace App\Manager;

use App\Exception\CallException;
use App\Manager\AbstractAppManager;
use App\Manager\CallManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchIndexTemplateModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexTemplateManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchIndexTemplateModel
    {
        try {
            $callRequest = new CallRequestModel();
            $callRequest->setPath('/_index_template/'.$name);
            $callRequest->setQuery(['flat_settings' => 'true']);
            $callResponse = $this->callManager->call($callRequest);

            if (Response::HTTP_NOT_FOUND == $callResponse->getCode()) {
                $templateModel = null;
            } else {
                $template = $callResponse->getContent();

                $templateModel = new ElasticsearchIndexTemplateModel();
                $templateModel->convert($template['index_templates'][0]);
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
            $callRequest->setPath('/_index_template/'.$filter['name']);
        } else {
            $callRequest->setPath('/_index_template');
        }
        $callRequest->setQuery(['flat_settings' => 'true']);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results) {
            $results = $results['index_templates'];
            usort($results, [$this, 'sortByName']);

            foreach ($results as $row) {
                $templateModel = new ElasticsearchIndexTemplateModel();
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

            if (true === isset($filter['data_stream'])) {
                if ('yes' === $filter['data_stream'] && false === $row->getDataStream()) {
                    $score--;
                }
                if ('no' === $filter['data_stream'] && true === $row->getDataStream()) {
                    $score--;
                }
            }

            if (true === isset($filter['managed'])) {
                if ('yes' === $filter['managed'] && false === $row->isManaged()) {
                    $score--;
                }
                if ('no' === $filter['managed'] && true === $row->isManaged()) {
                    $score--;
                }
            }

            if (true === isset($filter['system'])) {
                if ('yes' === $filter['system'] && false === $row->isSystem()) {
                    $score--;
                }
                if ('no' === $filter['system'] && true === $row->isSystem()) {
                    $score--;
                }
            }

            if (0 <= $score) {
                $templatesWithFilter[] = $row;
            }
        }

        return $templatesWithFilter;
    }

    public function send(ElasticsearchIndexTemplateModel $templateModel): CallResponseModel
    {
        $json = $templateModel->getJson();
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('PUT');
        $callRequest->setPath('/_index_template/'.$templateModel->getName());
        $callRequest->setJson($json);

        return $this->callManager->call($callRequest);
    }

    public function deleteByName(string $name): CallResponseModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setMethod('DELETE');
        $callRequest->setPath('/_index_template/'.$name);

        return $this->callManager->call($callRequest);
    }

    private function sortByName(array $a, array $b): int
    {
        return $a['name'] <=> $b['name'];
    }
}
