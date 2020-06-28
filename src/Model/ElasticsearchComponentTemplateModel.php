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

    public function __construct()
    {
        $this->settings = json_encode(['component.number_of_shards' => 1, 'component.auto_expand_replicas' => '0-1'], JSON_PRETTY_PRINT);
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
        if (true == isset($template['version'])) {
            $this->setVersion($template['version']);
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
            'template' => [],
        ];

        if ($this->getVersion()) {
            $json['template']['version'] = $this->getVersion();
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
