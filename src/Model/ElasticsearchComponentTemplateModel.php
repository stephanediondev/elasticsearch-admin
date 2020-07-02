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

    public function convert(?array $template): self
    {
        $this->setName($template['name']);
        if (true == isset($template['component_template']['version'])) {
            $this->setVersion($template['component_template']['version']);
        }
        if (true == isset($template['component_template']['template']['settings']) && 0 < count($template['component_template']['template']['settings'])) {
            $this->setSettings(json_encode($template['component_template']['template']['settings'], JSON_PRETTY_PRINT));
        }
        if (true == isset($template['component_template']['template']['mappings']) && 0 < count($template['component_template']['template']['mappings'])) {
            $this->setMappings(json_encode($template['component_template']['template']['mappings'], JSON_PRETTY_PRINT));
        }
        if (true == isset($template['component_template']['template']['aliases']) && 0 < count($template['component_template']['template']['aliases'])) {
            $this->setAliases(json_encode($template['component_template']['template']['aliases'], JSON_PRETTY_PRINT));
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
