<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchComponentTemplateModel extends AbstractAppModel
{
    private $name;

    private $version;

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

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): self
    {
        $this->version = $version;

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

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $template): self
    {
        $this->setName($template['name']);
        if (true == isset($template['component_template']['version'])) {
            $this->setVersion($template['component_template']['version']);
        }
        if (true == isset($template['component_template']['template']['settings']) && 0 < count($template['component_template']['template']['settings'])) {
            $this->setSettings($template['component_template']['template']['settings']);
        }
        if (true == isset($template['component_template']['template']['mappings']) && 0 < count($template['component_template']['template']['mappings'])) {
            $this->setMappings($template['component_template']['template']['mappings']);
        }
        if (true == isset($template['component_template']['template']['aliases']) && 0 < count($template['component_template']['template']['aliases'])) {
            $this->setAliases($template['component_template']['template']['aliases']);
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'template' => [],
        ];

        if ($this->getVersion()) {
            $json['version'] = $this->getVersion();
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

        return $json;
    }
}
