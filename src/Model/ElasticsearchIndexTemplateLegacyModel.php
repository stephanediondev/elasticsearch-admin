<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexTemplateLegacyModel extends AbstractAppModel
{
    private $name;

    private $indexPatterns;

    private $version;

    private $order;

    private $settings;

    private $mappings;

    private $aliases;

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

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings($settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSetting($key): ?string
    {
        return $this->settings[$key] ?? false;
    }

    public function setSetting(?string $key, ?string $value): self
    {
        $this->settings[$key] = $value;

        return $this;
    }

    public function getMappings(): ?array
    {
        return $this->mappings;
    }

    public function setMappings($mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function setAliases($aliases): self
    {
        $this->aliases = $aliases;

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
        if (true == isset($template['index_patterns'])) {
            $this->setIndexPatterns(implode(', ', $template['index_patterns']));
        }
        if (true == isset($template['template'])) {
            $this->setIndexPatterns($template['template']);
        }
        if (true == isset($template['version'])) {
            $this->setVersion($template['version']);
        }
        if (true == isset($template['order'])) {
            $this->setOrder($template['order']);
        }
        if (true == isset($template['settings']) && 0 < count($template['settings'])) {
            $this->setSettings($template['settings']);
        }
        if (true == isset($template['mappings']) && 0 < count($template['mappings'])) {
            $this->setMappings($template['mappings']);
        }
        if (true == isset($template['aliases']) && 0 < count($template['aliases'])) {
            $this->setAliases($template['aliases']);
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
            $json['settings'] = $this->getSettings();
        }

        if ($this->getMappings()) {
            $json['mappings'] = $this->getMappings();
        }

        if ($this->getAliases()) {
            $json['aliases'] = $this->getAliases();
        }

        return $json;
    }
}
