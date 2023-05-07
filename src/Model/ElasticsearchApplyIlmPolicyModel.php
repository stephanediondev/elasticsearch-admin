<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchApplyIlmPolicyModel extends AbstractAppModel
{
    private ?string $indexTemplate = null;

    private ?string $rolloverAlias = null;

    public function getIndexTemplate(): ?string
    {
        return $this->indexTemplate;
    }

    public function setIndexTemplate(?string $indexTemplate): self
    {
        $this->indexTemplate = $indexTemplate;

        return $this;
    }

    public function getRolloverAlias(): ?string
    {
        return $this->rolloverAlias;
    }

    public function setRolloverAlias(?string $rolloverAlias): self
    {
        $this->rolloverAlias = $rolloverAlias;

        return $this;
    }
}
