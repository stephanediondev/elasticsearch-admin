<?php

namespace App\Model;

class ElasticsearchIndexModel
{
    private $name;

    private $numberOfShards;

    private $numberOfReplicas;

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

    public function getNumberOfShards(): ?string
    {
        return $this->numberOfShards;
    }

    public function setNumberOfShards(?string $numberOfShards): self
    {
        $this->numberOfShards = $numberOfShards;

        return $this;
    }

    public function getNumberOfReplicas(): ?string
    {
        return $this->numberOfReplicas;
    }

    public function setNumberOfReplicas(?string $numberOfReplicas): self
    {
        $this->numberOfReplicas = $numberOfReplicas;

        return $this;
    }
}
