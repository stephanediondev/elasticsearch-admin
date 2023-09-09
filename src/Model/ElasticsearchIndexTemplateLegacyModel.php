<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\MappingsSettingsAliasesModelTrait;

class ElasticsearchIndexTemplateLegacyModel extends AbstractAppModel
{
    use MappingsSettingsAliasesModelTrait;

    private ?string $name = null;

    private ?string $indexPatterns = null;

    private ?string $template = null;

    private ?int $version = null;

    private ?int $order = null;

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

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

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

    private function getIndexToArray(): array
    {
        $indexPatterns = [];

        if ($this->getIndexPatterns()) {
            foreach (explode(',', $this->getIndexPatterns()) as $indexPattern) {
                $indexPatterns[] = trim($indexPattern);
            }
        }

        return $indexPatterns;
    }

    public function isSystem(): ?bool
    {
        return $this->getName() && '.' === substr($this->getName(), 0, 1);
    }

    public function convert(?array $template): self
    {
        if (true === isset($template['name'])) {
            $this->setName($template['name']);
        }

        if (true === isset($template['index_patterns'])) {
            $this->setIndexPatterns(implode(', ', $template['index_patterns']));
        }

        if (true === isset($template['template'])) {
            $this->setTemplate($template['template']);
        }

        if (true === isset($template['version'])) {
            $this->setVersion(intval($template['version']));
        }

        if (true === isset($template['order'])) {
            $this->setOrder(intval($template['order']));
        }

        if (true === isset($template['settings']) && 0 < count($template['settings'])) {
            $this->setSettings($template['settings']);
        }

        if (true === isset($template['mappings']) && 0 < count($template['mappings'])) {
            $this->setMappings($template['mappings']);
        }

        if (true === isset($template['aliases']) && 0 < count($template['aliases'])) {
            $this->setAliases($template['aliases']);
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [];

        if ($this->getIndexPatterns()) {
            $json['index_patterns'] = $this->getIndexToArray();
        }

        if ($this->getTemplate()) {
            $json['template'] = $this->getTemplate();
        }

        if ($this->getVersion()) {
            $json['version'] = $this->getVersion();
        }

        if ($this->getOrder()) {
            $json['order'] = $this->getOrder();
        }

        if ($this->getSettingsJson()) {
            $json['settings'] = json_decode($this->getSettingsJson(), true);
        }

        if ($this->getMappingsJson()) {
            $json['mappings'] = json_decode($this->getMappingsJson(), true);
        }

        if ($this->getAliasesJson()) {
            $json['aliases'] = json_decode($this->getAliasesJson(), true);
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
