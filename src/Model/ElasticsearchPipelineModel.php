<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchPipelineModel extends AbstractAppModel
{
    private $name;

    private $version;

    private $description;

    private $processors;

    private $onFailure;

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

    public function setProcessors($processors): self
    {
        $this->processors = $processors;

        return $this;
    }

    public function getOnFailure(): ?array
    {
        return $this->onFailure;
    }

    public function setOnFailure($onFailure): self
    {
        $this->onFailure = $onFailure;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $pipeline): self
    {
        $this->setName($pipeline['name']);
        if (true === isset($pipeline['description'])) {
            $this->setDescription($pipeline['description']);
        }
        if (true === isset($pipeline['version'])) {
            $this->setVersion($pipeline['version']);
        }
        if (true === isset($pipeline['processors']) && 0 < count($pipeline['processors'])) {
            $this->setProcessors($pipeline['processors']);
        }
        if (true === isset($pipeline['on_failure']) && 0 < count($pipeline['on_failure'])) {
            $this->setOnFailure($pipeline['on_failure']);
        }
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'processors' => $this->getProcessors(),
        ];

        if ($this->getVersion()) {
            $json['version'] = $this->getVersion();
        }

        if ($this->getDescription()) {
            $json['description'] = $this->getDescription();
        }

        if ($this->getOnFailure()) {
            $json['on_failure'] = $this->getOnFailure();
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
