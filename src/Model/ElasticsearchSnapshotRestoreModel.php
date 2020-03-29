<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchSnapshotRestoreModel extends AbstractAppModel
{
    private $renamePattern;

    private $renameReplacement;

    private $indices;

    private $ignoreUnavailable;

    private $partial;

    private $includeGlobalState;

    public function __construct()
    {
        $this->renamePattern = '(.+)';
        $this->renameReplacement = 'restored-$1';
        $this->includeGlobalState = true;
    }

    public function getRenamePattern(): ?string
    {
        return $this->renamePattern;
    }

    public function setRenamePattern(?string $renamePattern): self
    {
        $this->renamePattern = $renamePattern;

        return $this;
    }

    public function getRenameReplacement(): ?string
    {
        return $this->renameReplacement;
    }

    public function setRenameReplacement(?string $renameReplacement): self
    {
        $this->renameReplacement = $renameReplacement;

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
}
