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
        $this->settings = json_encode(['index.number_of_shards' => 1, 'index.auto_expand_replicas' => '0-1'], JSON_PRETTY_PRINT);
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

    public function getExcludeSettings(): ?array
    {
        return [
            'index.creation_date',
            'index.provided_name',
            'index.uuid',
            'index.version.created',
            'index.version.upgraded',
            'index.number_of_shards',
            'index.shard.check_on_startup',
            'index.codec',
            'index.routing_partition_size',
            'index.load_fixed_bitset_filters_eagerly',
            'index.hidden',
        ];
    }

    public function convert(?array $index): self
    {
        $this->setName($index['index']);
        if (true == isset($index['mappings']) && 0 < count($index['mappings'])) {
            $this->setMappings(json_encode($index['mappings'], JSON_PRETTY_PRINT));
        }
        return $this;
    }
}
