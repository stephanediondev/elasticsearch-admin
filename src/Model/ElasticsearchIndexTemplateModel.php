<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexTemplateModel extends AbstractAppModel
{
    private $name;

    private $indexPatterns;

    private $version;

    private $priority;

    private $settings;

    private $mappings;

    private $aliases;

    private $composedOf;

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

    public function getComposedOf(): ?array
    {
        return $this->composedOf;
    }

    public function setComposedOf(?array $composedOf): self
    {
        $this->composedOf = $composedOf;

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
        $this->setIndexPatterns(implode(', ', $template['index_template']['index_patterns']));
        if (true == isset($template['index_template']['version'])) {
            $this->setVersion($template['index_template']['version']);
        }
        if (true == isset($template['index_template']['priority'])) {
            $this->setPriority($template['index_template']['priority']);
        }
        if (true == isset($template['index_template']['composed_of']) && 0 < count($template['index_template']['composed_of'])) {
            $this->setComposedOf($template['index_template']['composed_of']);
        }
        if (true == isset($template['index_template']['template']) && 0 < count($template['index_template']['template'])) {
            if (true == isset($template['index_template']['template']['settings']) && 0 < count($template['index_template']['template']['settings'])) {
                $this->setSettings(json_encode($template['index_template']['template']['settings'], JSON_PRETTY_PRINT));
            }
            if (true == isset($template['index_template']['template']['mappings']) && 0 < count($template['index_template']['template']['mappings'])) {
                $this->setMappings(json_encode($template['index_template']['template']['mappings'], JSON_PRETTY_PRINT));
            }
            if (true == isset($template['index_template']['template']['aliases']) && 0 < count($template['index_template']['template']['aliases'])) {
                $this->setAliases(json_encode($template['index_template']['template']['aliases'], JSON_PRETTY_PRINT));
            }
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
            $json['template']['settings'] = json_decode($this->getSettings(), true);
        }

        if ($this->getMappings()) {
            $json['template']['mappings'] = json_decode($this->getMappings(), true);
        }

        if ($this->getAliases()) {
            $json['template']['aliases'] = json_decode($this->getAliases(), true);
        }

        return $json;
    }
}
