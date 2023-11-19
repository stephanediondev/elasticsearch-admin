<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchReindexModel extends AbstractAppModel
{
    private ?string $source = null;

    private ?string $destination = null;

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $json = [
            'source' => [
                'index' => $this->getSource(),
            ],
            'dest' => [
                'index' => $this->getDestination(),
            ],
        ];

        return $json;
    }
}
