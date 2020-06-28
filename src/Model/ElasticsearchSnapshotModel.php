<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchSnapshotModel extends AbstractAppModel
{
    private $name;

    private $repository;

    private $indices;

    private $ignoreUnavailable;

    private $partial;

    private $includeGlobalState;

    public function __construct()
    {
        $this->includeGlobalState = true;
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

    public function getRepository(): ?string
    {
        return $this->repository;
    }

    public function setRepository(?string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getIndices(): ?array
    {
        return $this->indices;
    }

    public function setIndices(?array $indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    public function getIgnoreUnavailable(): ?bool
    {
        return $this->ignoreUnavailable;
    }

    public function setIgnoreUnavailable(?bool $ignoreUnavailable): self
    {
        $this->ignoreUnavailable = $ignoreUnavailable;

        return $this;
    }

    public function getPartial(): ?bool
    {
        return $this->partial;
    }

    public function setPartial(?bool $partial): self
    {
        $this->partial = $partial;

        return $this;
    }

    public function getIncludeGlobalState(): ?bool
    {
        return $this->includeGlobalState;
    }

    public function setIncludeGlobalState(?bool $includeGlobalState): self
    {
        $this->includeGlobalState = $includeGlobalState;

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'ignore_unavailable' => $this->getIgnoreUnavailable(),
            'partial' => $this->getPartial(),
            'include_global_state' => $this->getIncludeGlobalState(),
        ];

        if ($this->getIndices()) {
            $json['indices'] = implode(',', $this->getIndices());
        }

        return $json;
    }
}
