<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\ElasticsearchSnapshotModelTrait;

class ElasticsearchSnapshotCloneModel extends AbstractAppModel
{
    use ElasticsearchSnapshotModelTrait;

    private ?string $targetName = null;

    public function getTargetName(): ?string
    {
        return $this->targetName;
    }

    public function setTargetName(?string $targetName): self
    {
        $this->targetName = $targetName;

        return $this;
    }

    public function getJson(): array
    {
        $json = [];

        if ($this->getIndices()) {
            $json['indices'] = implode(',', $this->getIndices());
        }

        return $json;
    }
}
