<?php

namespace App\Model;

class ElasticsearchIndexModel
{
    private $name;

    private $numberOfShards;

    private $numberOfReplicas;

    private $mappings;

    public function __construct()
    {
        $this->numberOfShards = 5;
        $this->numberOfReplicas = 1;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNumberOfShards(): ?int
    {
        return $this->numberOfShards;
    }

    public function setNumberOfShards(?int $numberOfShards): self
    {
        $this->numberOfShards = $numberOfShards;

        return $this;
    }

    public function getNumberOfReplicas(): ?int
    {
        return $this->numberOfReplicas;
    }

    public function setNumberOfReplicas(?int $numberOfReplicas): self
    {
        $this->numberOfReplicas = $numberOfReplicas;

        return $this;
    }

    public function getMappings(): ?string
    {
        return $this->mappings;
    }

    public function setMappings(?string $mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }
}
