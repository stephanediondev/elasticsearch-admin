<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchIndexModel extends AbstractAppModel
{
    private $name;

    private $settings;

    private $mappings;

    public function __construct()
    {
        $this->settings = json_encode(['number_of_shards' =>  1, 'number_of_replicas' => 1], JSON_PRETTY_PRINT);
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
