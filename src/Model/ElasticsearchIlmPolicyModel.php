<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIlmPolicyModel extends AbstractAppModel
{
    private ?string $name = null;

    private ?int $version = null;

    private ?string $modifiedDate = null;

    /**
     * @var array<mixed>|null $hot
     */
    private ?array $hot = null;

    private ?string $hotJson = null;

    /**
     * @var array<mixed>|null $warm
     */
    private ?array $warm = null;

    private ?string $warmJson = null;

    /**
     * @var array<mixed>|null $cold
     */
    private ?array $cold = null;

    private ?string $coldJson = null;

    /**
     * @var array<mixed>|null $delete
     */
    private ?array $delete = null;

    private ?string $deleteJson = null;

    /**
     * @var array<mixed>|null $phases
     */
    private ?array $phases = null;

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

    /**
     * @return array<mixed>|null
     */
    public function getHot(): ?array
    {
        return $this->hot;
    }

    /**
     * @param array<mixed>|null $hot
     */
    public function setHot(?array $hot): self
    {
        $this->hot = $hot;

        return $this;
    }

    public function getHotJson(): ?string
    {
        return $this->hotJson;
    }

    public function setHotJson(?string $hotJson): self
    {
        $this->hotJson = $hotJson;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getWarm(): ?array
    {
        return $this->warm;
    }

    /**
     * @param array<mixed>|null $warm
     */
    public function setWarm(?array $warm): self
    {
        $this->warm = $warm;

        return $this;
    }

    public function getWarmJson(): ?string
    {
        return $this->warmJson;
    }

    public function setWarmJson(?string $warmJson): self
    {
        $this->warmJson = $warmJson;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getCold(): ?array
    {
        return $this->cold;
    }

    /**
     * @param array<mixed>|null $cold
     */
    public function setCold(?array $cold): self
    {
        $this->cold = $cold;

        return $this;
    }

    public function getColdJson(): ?string
    {
        return $this->coldJson;
    }

    public function setColdJson(?string $coldJson): self
    {
        $this->coldJson = $coldJson;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getDelete(): ?array
    {
        return $this->delete;
    }

    /**
     * @param array<mixed>|null $delete
     */
    public function setDelete(?array $delete): self
    {
        $this->delete = $delete;

        return $this;
    }

    public function getDeleteJson(): ?string
    {
        return $this->deleteJson;
    }

    public function setDeleteJson(?string $deleteJson): self
    {
        $this->deleteJson = $deleteJson;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getPhases(): ?array
    {
        return $this->phases;
    }

    /**
     * @param array<mixed>|null $phases
     */
    public function setPhases(?array $phases): self
    {
        $this->phases = $phases;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return $this->getName() && '.' === substr($this->getName(), 0, 1);
    }

    /**
     * @param array<mixed>|null $policy
     */
    public function convert(?array $policy): self
    {
        if (true === isset($policy['name'])) {
            $this->setName($policy['name']);
        }

        if (true === isset($policy['version'])) {
            $this->setVersion(intval($policy['version']));
        }

        if (true === isset($policy['modified_date'])) {
            $this->setModifiedDate($policy['modified_date']);
        }

        if (true === isset($policy['policy']['phases']) && 0 < count($policy['policy']['phases'])) {
            $this->setPhases($policy['policy']['phases']);

            if (true === isset($policy['policy']['phases']['hot'])) {
                $this->setHot($policy['policy']['phases']['hot']);
                if ($json = json_encode($policy['policy']['phases']['hot'], JSON_PRETTY_PRINT)) {
                    $this->setHotJson($json);
                }
            }

            if (true === isset($policy['policy']['phases']['warm'])) {
                $this->setWarm($policy['policy']['phases']['warm']);
                if ($json = json_encode($policy['policy']['phases']['warm'], JSON_PRETTY_PRINT)) {
                    $this->setWarmJson($json);
                }
            }

            if (true === isset($policy['policy']['phases']['cold'])) {
                $this->setCold($policy['policy']['phases']['cold']);
                if ($json = json_encode($policy['policy']['phases']['cold'], JSON_PRETTY_PRINT)) {
                    $this->setColdJson($json);
                }
            }

            if (true === isset($policy['policy']['phases']['delete'])) {
                $this->setDelete($policy['policy']['phases']['delete']);
                if ($json = json_encode($policy['policy']['phases']['delete'], JSON_PRETTY_PRINT)) {
                    $this->setDeleteJson($json);
                }
            }
        }

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $json = [
            'policy' => [
                'phases' => [],
            ],
        ];

        if ($this->getHotJson()) {
            $json['policy']['phases']['hot'] = json_decode($this->getHotJson(), true);
        }

        if ($this->getWarmJson()) {
            $json['policy']['phases']['warm'] = json_decode($this->getWarmJson(), true);
        }

        if ($this->getColdJson()) {
            $json['policy']['phases']['cold'] = json_decode($this->getColdJson(), true);
        }

        if ($this->getDeleteJson()) {
            $json['policy']['phases']['delete'] = json_decode($this->getDeleteJson(), true);
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
