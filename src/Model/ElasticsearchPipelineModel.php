<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchPipelineModel extends AbstractAppModel
{
    private ?string $name = null;

    private ?int $version = null;

    private ?string $description = null;

    private ?array $processors = null;

    private ?string $processorsJson = null;

    private ?array $onFailure = null;

    private ?string $onFailureJson = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getProcessors(): ?array
    {
        return $this->processors;
    }

    public function setProcessors(?array $processors): self
    {
        $this->processors = $processors;

        return $this;
    }

    public function getProcessorsJson(): ?string
    {
        return $this->processorsJson;
    }

    public function setProcessorsJson(?string $processorsJson): self
    {
        $this->processorsJson = $processorsJson;

        return $this;
    }

    public function getOnFailure(): ?array
    {
        return $this->onFailure;
    }

    public function setOnFailure(?array $onFailure): self
    {
        $this->onFailure = $onFailure;

        return $this;
    }

    public function getOnFailureJson(): ?string
    {
        return $this->onFailureJson;
    }

    public function setOnFailureJson(?string $onFailureJson): self
    {
        $this->onFailureJson = $onFailureJson;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $pipeline): self
    {
        if (true === isset($pipeline['name'])) {
            $this->setName($pipeline['name']);
        }

        if (true === isset($pipeline['description'])) {
            $this->setDescription($pipeline['description']);
        }

        if (true === isset($pipeline['version'])) {
            $this->setVersion(intval($pipeline['version']));
        }

        if (true === isset($pipeline['processors']) && 0 < count($pipeline['processors'])) {
            $this->setProcessors($pipeline['processors']);
            $this->setProcessorsJson(json_encode($pipeline['processors'], JSON_PRETTY_PRINT));
        }

        if (true === isset($pipeline['on_failure']) && 0 < count($pipeline['on_failure'])) {
            $this->setOnFailure($pipeline['on_failure']);
            $this->setOnFailureJson(json_encode($pipeline['on_failure'], JSON_PRETTY_PRINT));
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'processors' => json_decode($this->getProcessorsJson(), true),
        ];

        if ($this->getVersion()) {
            $json['version'] = $this->getVersion();
        }

        if ($this->getDescription()) {
            $json['description'] = $this->getDescription();
        }

        if ($this->getOnFailureJson()) {
            $json['on_failure'] = json_decode($this->getOnFailureJson(), true);
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
