<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexTemplateModel extends AbstractAppModel
{
    private $name;

    private $indexPatterns;

    private $version;

    private $order;

    private $settings;

    private $mappings;

    private $aliases;

    public function __construct()
    {
        $this->settings = json_encode(['index.number_of_shards' => 1, 'index.auto_expand_replicas' => '0-1'], JSON_PRETTY_PRINT);
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

    public function getAliases(): ?string
    {
        return $this->aliases;
    }

    public function setAliases(?string $aliases): self
    {
        $this->aliases = $aliases;

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
        if (true == isset($template['aliases']) && 0 < count($template['aliases'])) {
            $this->setAliases(json_encode($template['aliases'], JSON_PRETTY_PRINT));
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

        if ($this->getOrder()) {
            $json['order'] = $this->getOrder();
        }

        if ($this->getSettings()) {
            $json['settings'] = json_decode($this->getSettings(), true);
        }

        if ($this->getMappings()) {
            $json['mappings'] = json_decode($this->getMappings(), true);
        }

        if ($this->getAliases()) {
            $json['aliases'] = json_decode($this->getAliases(), true);
        }

        return $json;
    }
}
