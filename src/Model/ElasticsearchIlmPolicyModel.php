<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIlmPolicyModel extends AbstractAppModel
{
    private $name;

    private $version;

    private $modifiedDate;

    private $hot;

    private $warm;

    private $cold;

    private $delete;

    private $phases;

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

    public function getModifiedDate(): ?string
    {
        return $this->modifiedDate;
    }

    public function setModifiedDate(?string $modifiedDate): self
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    public function getHot(): ?array
    {
        return $this->hot;
    }

    public function setHot($hot): self
    {
        $this->hot = $hot;

        return $this;
    }

    public function getWarm(): ?array
    {
        return $this->warm;
    }

    public function setWarm($warm): self
    {
        $this->warm = $warm;

        return $this;
    }

    public function getCold(): ?array
    {
        return $this->cold;
    }

    public function setCold($cold): self
    {
        $this->cold = $cold;

        return $this;
    }

    public function getDelete(): ?array
    {
        return $this->delete;
    }

    public function setDelete($delete): self
    {
        $this->delete = $delete;

        return $this;
    }

    public function getPhases(): ?array
    {
        return $this->phases;
    }

    public function setPhases($phases): self
    {
        $this->phases = $phases;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $policy): self
    {
        $this->setName($policy['name']);

        if (true === isset($policy['version'])) {
            $this->setVersion($policy['version']);
        }

        if (true === isset($policy['modified_date'])) {
            $this->setModifiedDate($policy['modified_date']);
        }

        if (true === isset($policy['policy']['phases']) && 0 < count($policy['policy']['phases'])) {
            $this->setPhases($policy['policy']['phases']);

            if (true === isset($policy['policy']['phases']['hot'])) {
                $this->setHot($policy['policy']['phases']['hot']);
            }
            if (true === isset($policy['policy']['phases']['warm'])) {
                $this->setWarm($policy['policy']['phases']['warm']);
            }
            if (true === isset($policy['policy']['phases']['cold'])) {
                $this->setCold($policy['policy']['phases']['cold']);
            }
            if (true === isset($policy['policy']['phases']['delete'])) {
                $this->setDelete($policy['policy']['phases']['delete']);
            }
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'policy' => [
                'phases' => [],
            ],
        ];

        if ($this->getHot()) {
            $json['policy']['phases']['hot'] = $this->getHot();
        }

        if ($this->getWarm()) {
            $json['policy']['phases']['warm'] = $this->getWarm();
        }

        if ($this->getCold()) {
            $json['policy']['phases']['cold'] = $this->getCold();
        }

        if ($this->getDelete()) {
            $json['policy']['phases']['delete'] = $this->getDelete();
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
