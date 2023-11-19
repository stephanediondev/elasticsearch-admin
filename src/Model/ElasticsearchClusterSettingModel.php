<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchClusterSettingModel extends AbstractAppModel
{
    private ?string $type = null;

    private ?string $setting = null;

    private ?string $value = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSetting(): ?string
    {
        return $this->setting;
    }

    public function setSetting(?string $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $json = [
            $this->getType() => [
                $this->getSetting() => $this->getValue(),
            ],
        ];

        return $json;
    }

    public function __toString(): string
    {
        return $this->setting ?? '';
    }
}
