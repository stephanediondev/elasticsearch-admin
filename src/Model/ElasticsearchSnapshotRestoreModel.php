<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\ElasticsearchSnapshotModelTrait;

class ElasticsearchSnapshotRestoreModel extends AbstractAppModel
{
    use ElasticsearchSnapshotModelTrait;

    private ?string $renamePattern = null;

    private ?string $renameReplacement = null;

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

    public function getJson(): array
    {
        $json = [
            'ignore_unavailable' => $this->getIgnoreUnavailable(),
            'partial' => $this->getPartial(),
            'include_global_state' => $this->getIncludeGlobalState(),
        ];

        if ($this->getRenamePattern()) {
            $json['rename_pattern'] = $this->getRenamePattern();
        }

        if ($this->getRenameReplacement()) {
            $json['rename_replacement'] = $this->getRenameReplacement();
        }

        if ($this->getIndices()) {
            $json['indices'] = implode(',', $this->getIndices());
        }

        if ($this->getFeatureStates() && 0 < count($this->getFeatureStates())) {
            $json['feature_states'] = $this->getFeatureStates();
        }

        return $json;
    }
}
