<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchReindexModel extends AbstractAppModel
{
    private $source;

    private $destination;

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
