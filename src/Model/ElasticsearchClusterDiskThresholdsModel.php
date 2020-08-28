<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchClusterDiskThresholdsModel extends AbstractAppModel
{
    private $enabled;

    private $low;

    private $high;

    private $floodStage;

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLow(): ?string
    {
        return $this->low;
    }

    public function setLow(?string $low): self
    {
        $this->low = $low;

        return $this;
    }

    public function getHigh(): ?string
    {
        return $this->high;
    }

    public function setHigh(?string $high): self
    {
        $this->high = $high;

        return $this;
    }

    public function getFloodStage(): ?string
    {
        return $this->floodStage;
    }

    public function setFloodStage(?string $floodStage): self
    {
        $this->floodStage = $floodStage;

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'persistent' => [
                'cluster.routing.allocation.disk.threshold_enabled' => $this->getEnabled(),
                'cluster.routing.allocation.disk.watermark.low' => $this->getLow(),
                'cluster.routing.allocation.disk.watermark.high' => $this->getHigh(),
                'cluster.routing.allocation.disk.watermark.flood_stage' => $this->getFloodStage(),
            ],
        ];

        return $json;
    }
}
