<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIlmPolicyModel extends AbstractAppModel
{
    private $name;

    private $hot;

    private $warm;

    private $cold;

    private $delete;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getHot(): ?string
    {
        return $this->hot;
    }

    public function setHot(?string $hot): self
    {
        $this->hot = $hot;

        return $this;
    }

    public function getWarm(): ?string
    {
        return $this->warm;
    }

    public function setWarm(?string $warm): self
    {
        $this->warm = $warm;

        return $this;
    }

    public function getCold(): ?string
    {
        return $this->cold;
    }

    public function setCold(?string $cold): self
    {
        $this->cold = $cold;

        return $this;
    }

    public function getDelete(): ?string
    {
        return $this->delete;
    }

    public function setDelete(?string $delete): self
    {
        $this->delete = $delete;

        return $this;
    }

    public function convert(?array $policy): self
    {
        $this->setName($policy['name']);
        if (true == isset($policy['policy']['phases']) && 0 < count($policy['policy']['phases'])) {
            if (true == isset($policy['policy']['phases']['hot'])) {
                $this->setHot(json_encode($policy['policy']['phases']['hot'], JSON_PRETTY_PRINT));
            }
            if (true == isset($policy['policy']['phases']['warm'])) {
                $this->setWarm(json_encode($policy['policy']['phases']['warm'], JSON_PRETTY_PRINT));
            }
            if (true == isset($policy['policy']['phases']['cold'])) {
                $this->setCold(json_encode($policy['policy']['phases']['cold'], JSON_PRETTY_PRINT));
            }
            if (true == isset($policy['policy']['phases']['delete'])) {
                $this->setDelete(json_encode($policy['policy']['phases']['delete'], JSON_PRETTY_PRINT));
            }
        }
        return $this;
    }
}
