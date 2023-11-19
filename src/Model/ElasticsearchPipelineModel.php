<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchPipelineModel extends AbstractAppModel
{
    private ?string $name = null;

    private ?int $version = null;

    private ?string $description = null;

    /**
     * @var array<mixed>|null $processors
     */
    private ?array $processors = null;

    private ?string $processorsJson = null;

    /**
     * @var array<mixed>|null $onFailure
     */
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

    /**
     * @return array<mixed>|null
     */
    public function getProcessors(): ?array
    {
        return $this->processors;
    }

    /**
     * @param array<mixed>|null $processors
     */
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

    /**
     * @return array<mixed>|null
     */
    public function getOnFailure(): ?array
    {
        return $this->onFailure;
    }

    /**
     * @param array<mixed>|null $onFailure
     */
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
        return $this->getName() && '.' === substr($this->getName(), 0, 1);
    }

    /**
     * @param array<mixed>|null $pipeline
     */
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
            if ($json = json_encode($pipeline['processors'], JSON_PRETTY_PRINT)) {
                $this->setProcessorsJson($json);
            }
        }

        if (true === isset($pipeline['on_failure']) && 0 < count($pipeline['on_failure'])) {
            $this->setOnFailure($pipeline['on_failure']);
            if ($json = json_encode($pipeline['on_failure'], JSON_PRETTY_PRINT)) {
                $this->setOnFailureJson($json);
            }
        }

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $json = [];

        if ($this->getProcessorsJson()) {
            $json['processors'] = json_decode($this->getProcessorsJson(), true);
        }

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
        return $this->name ?? '';
    }
}
