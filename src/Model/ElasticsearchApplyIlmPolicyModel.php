<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchApplyIlmPolicyModel extends AbstractAppModel
{
    private $indexTemplate;

    private $rolloverAlias;

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
