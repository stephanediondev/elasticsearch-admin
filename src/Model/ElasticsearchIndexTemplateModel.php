<?php

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\MappingsSettingsAliasesModelTrait;

class ElasticsearchIndexTemplateModel extends AbstractAppModel
{
    use MappingsSettingsAliasesModelTrait;

    private $name;

    private $indexPatterns;

    private $version;

    private $priority;

    private $composedOf;

    private $metadata;

    private $dataStream;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIndexPatterns(): ?string
    {
        return $this->indexPatterns;
    }

    public function setIndexPatterns(?string $indexPatterns): self
    {
        $this->indexPatterns = $indexPatterns;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getComposedOf(): ?array
    {
        return $this->composedOf;
    }

    public function setComposedOf(?array $composedOf): self
    {
        $this->composedOf = $composedOf;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata($metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getDataStream(): ?bool
    {
        return $this->dataStream;
    }

    public function setDataStream(?bool $dataStream): self
    {
        $this->dataStream = $dataStream;

        return $this;
    }

    private function getIndexToArray(): ?array
    {
        $indexPatterns = [];

        foreach (explode(',', $this->indexPatterns) as $indexPattern) {
            $indexPatterns[] = trim($indexPattern);
        }
        return $indexPatterns;
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $template): self
    {
        $this->setName($template['name']);
        $this->setIndexPatterns(implode(', ', $template['index_template']['index_patterns']));
        if (true === isset($template['index_template']['version'])) {
            $this->setVersion($template['index_template']['version']);
        }
        if (true === isset($template['index_template']['priority'])) {
            $this->setPriority($template['index_template']['priority']);
        }
        if (true === isset($template['index_template']['composed_of']) && 0 < count($template['index_template']['composed_of'])) {
            $this->setComposedOf($template['index_template']['composed_of']);
        }
        if (true === isset($template['index_template']['template']) && 0 < count($template['index_template']['template'])) {
            if (true === isset($template['index_template']['template']['settings']) && 0 < count($template['index_template']['template']['settings'])) {
                $this->setSettings($template['index_template']['template']['settings']);
            }
            if (true === isset($template['index_template']['template']['mappings']) && 0 < count($template['index_template']['template']['mappings'])) {
                $this->setMappings($template['index_template']['template']['mappings']);
            }
            if (true === isset($template['index_template']['template']['aliases']) && 0 < count($template['index_template']['template']['aliases'])) {
                $this->setAliases($template['index_template']['template']['aliases']);
            }
        }
        if (true === isset($template['index_template']['_meta']) && 0 < count($template['index_template']['_meta'])) {
            $this->setMetadata($template['index_template']['_meta']);
        }
        if (true === isset($template['index_template']['data_stream'])) {
            $this->setDataStream(true);
        }
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'index_patterns' => $this->getIndexToArray(),
        ];

        if ($this->getVersion()) {
            $json['version'] = $this->getVersion();
        }

        if ($this->getPriority()) {
            $json['priority'] = $this->getPriority();
        }

        if ($this->getComposedOf()) {
            $json['composed_of'] = $this->getComposedOf();
        }

        if ($this->getSettings() || $this->getMappings() || $this->getAliases()) {
            $json['template'] = [];
        }

        if ($this->getSettings()) {
            $json['template']['settings'] = $this->getSettings();
        }

        if ($this->getMappings()) {
            $json['template']['mappings'] = $this->getMappings();
        }

        if ($this->getAliases()) {
            $json['template']['aliases'] = $this->getAliases();
        }

        if ($this->getMetadata()) {
            $json['_meta'] = $this->getMetadata();
        }

        if ($this->getDataStream()) {
            $json['data_stream'] = (object)[];
        }

        return $json;
    }
}
