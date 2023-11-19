<?php

declare(strict_types=1);

namespace App\Manager;

use App\Manager\AbstractAppManager;
use App\Model\CallRequestModel;
use App\Model\CallResponseModel;
use App\Model\ElasticsearchIndexTemplateLegacyModel;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchIndexTemplateLegacyManager extends AbstractAppManager
{
    public function getByName(string $name): ?ElasticsearchIndexTemplateLegacyModel
    {
        $callRequest = new CallRequestModel();
        $callRequest->setPath('/_template/'.$name);
        $callRequest->setQuery(['flat_settings' => 'true']);
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

    /**
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function getAll(array $filter = []): array
    {
        $templates = [];

        $callRequest = new CallRequestModel();
        if (true === isset($filter['name']) && '' != $filter['name']) {
            $callRequest->setPath('/_template/'.$filter['name']);
        } else {
            $callRequest->setPath('/_template');
        }
        $callRequest->setQuery(['flat_settings' => 'true']);
        $callResponse = $this->callManager->call($callRequest);
        $results = $callResponse->getContent();

        if ($results) {
            ksort($results);

            foreach ($results as $key => $row) {
                $templateModel = new ElasticsearchIndexTemplateLegacyModel();
                $row['name'] = $key;
                $templateModel->convert($row);
                $templates[] = $templateModel;
            }
        }

        return $this->filter($templates, $filter);
    }

    /**
     * @param array<mixed> $templates
     * @param array<mixed> $filter
     * @return array<mixed>
     */
    public function filter(array $templates, array $filter = []): array
    {
        $templatesWithFilter = [];

        foreach ($templates as $row) {
            $score = 0;

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
