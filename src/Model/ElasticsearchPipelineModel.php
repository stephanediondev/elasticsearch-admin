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

    public function getProcessors(): ?string
    {
        return $this->processors;
    }

    public function setProcessors(?string $processors): self
    {
        $this->processors = $processors;

        return $this;
    }

    public function getOnFailure(): ?string
    {
        return $this->onFailure;
    }

    public function setOnFailure(?string $onFailure): self
    {
        $this->onFailure = $onFailure;

        return $this;
    }

    public function convert(?array $pipeline): self
    {
        $this->setName($pipeline['name']);
        if (true == isset($pipeline['description'])) {
            $this->setDescription($pipeline['description']);
        }
        if (true == isset($pipeline['version'])) {
            $this->setVersion($pipeline['version']);
        }
        if (true == isset($pipeline['processors']) && 0 < count($pipeline['processors'])) {
            $this->setProcessors(json_encode($pipeline['processors'], JSON_PRETTY_PRINT));
        }
        if (true == isset($pipeline['on_failure']) && 0 < count($pipeline['on_failure'])) {
            $this->setOnFailure(json_encode($pipeline['on_failure'], JSON_PRETTY_PRINT));
        }
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'processors' => json_decode($this->getProcessors(), true),
        ];

        if ($this->getVersion()) {
            $json['version'] = $this->getVersion();
        }

        if ($this->getDescription()) {
            $json['description'] = $this->getDescription();
        }

        if ($this->getOnFailure()) {
            $json['on_failure'] = json_decode($this->getOnFailure(), true);
        }

        return $json;
    }
}
