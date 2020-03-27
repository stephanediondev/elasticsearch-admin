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

    public function __construct()
    {
    }

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
        return $this->mappings;
    }

    public function setSettings(?string $mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getMappings(): ?string
    {
        return $this->settings;
    }

    public function setMappings(?string $settings): self
    {
        $this->settings = $settings;

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
}
