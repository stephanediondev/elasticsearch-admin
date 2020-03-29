<?php

namespace App\Model;

class ElasticsearchIndexTemplateModel
{
    private $name;

    private $indexPatterns;

    private $version;

    private $order;

    private $settings;

    private $mappings;

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

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function setSettings(?string $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getMappings(): ?string
    {
        return $this->mappings;
    }

    public function setMappings(?string $mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getIndexToArray(): ?array
    {
        $indexPatterns = [];

        foreach (explode(',', $this->indexPatterns) as $indexPattern) {
            $indexPatterns[] = trim($indexPattern);
        }
        return $indexPatterns;
    }

    public function convert(?array $template): self
    {
        $this->setName($template['name']);
        $this->setIndexPatterns(implode(', ', $template['index_patterns']));
        if (true == isset($template['version'])) {
            $this->setVersion($template['version']);
        }
        if (true == isset($template['order'])) {
            $this->setOrder($template['order']);
        }
        if (true == isset($template['settings']) && 0 < count($template['settings'])) {
            $this->setSettings(json_encode($template['settings'], JSON_PRETTY_PRINT));
        }
        if (true == isset($template['mappings']) && 0 < count($template['mappings'])) {
            $this->setMappings(json_encode($template['mappings'], JSON_PRETTY_PRINT));
        }
        return $this;
    }
}
