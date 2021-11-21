<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\MappingsSettingsAliasesModelTrait;

class ElasticsearchComponentTemplateModel extends AbstractAppModel
{
    use MappingsSettingsAliasesModelTrait;

    private ?string $name = null;

    private ?int $version = null;

    private ?array $metadata = null;

    private ?string $metadataJson = null;

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

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getMetadataJson(): ?string
    {
        return $this->metadataJson;
    }

    public function setMetadataJson(?string $metadataJson): self
    {
        $this->metadataJson = $metadataJson;

        return $this;
    }

    public function isManaged(): ?bool
    {
        return true === isset($this->getMetadata()['managed']) && true === $this->getMetadata()['managed'];
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $template): self
    {
        if (true === isset($template['name'])) {
            $this->setName($template['name']);
        }

        if (true === isset($template['component_template']['version'])) {
            $this->setVersion(intval($template['component_template']['version']));
        }

        if (true === isset($template['component_template']['template']['settings']) && 0 < count($template['component_template']['template']['settings'])) {
            $this->setSettings($template['component_template']['template']['settings']);
            $this->setSettingsJson(json_encode($template['component_template']['template']['settings'], JSON_PRETTY_PRINT));
        }

        if (true === isset($template['component_template']['template']['mappings']) && 0 < count($template['component_template']['template']['mappings'])) {
            $this->setMappings($template['component_template']['template']['mappings']);
            $this->setMappingsJson(json_encode($template['component_template']['template']['mappings'], JSON_PRETTY_PRINT));
        }

        if (true === isset($template['component_template']['template']['aliases']) && 0 < count($template['component_template']['template']['aliases'])) {
            $this->setAliases($template['component_template']['template']['aliases']);
            $this->setAliasesJson(json_encode($template['component_template']['template']['aliases'], JSON_PRETTY_PRINT));
        }

        if (true === isset($template['component_template']['_meta']) && 0 < count($template['component_template']['_meta'])) {
            $this->setMetadata($template['component_template']['_meta']);
            $this->setMetadataJson(json_encode($template['component_template']['_meta'], JSON_PRETTY_PRINT));
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

        if ($this->getSettingsJson()) {
            $json['template']['settings'] = json_decode($this->getSettingsJson(), true);
        }

        if ($this->getMappingsJson()) {
            $json['template']['mappings'] = json_decode($this->getMappingsJson(), true);
        }

        if ($this->getAliasesJson()) {
            $json['template']['aliases'] = json_decode($this->getAliasesJson(), true);
        }

        if ($this->getMetadataJson()) {
            $json['_meta'] = json_decode($this->getMetadataJson(), true);
        }

        if (0 == count($json['template'])) {
            $json['template'] = (object)[];
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
