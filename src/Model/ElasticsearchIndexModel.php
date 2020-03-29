<?php

namespace App\Model;

class ElasticsearchIndexModel
{
    private $name;

    private $settings;

    private $mappings;

    public function __construct()
    {
        $this->settings = '{"number_of_shards":  5, "number_of_replicas": 1}';
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

    public function getSettings(): ?string
    {
        return $this->settings;
    }

    public function setSettings(?string $settings): self
    {
        $this->settings = $settings;

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
