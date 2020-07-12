<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchEnrichPolicyModel extends AbstractAppModel
{
    private $name;

    private $type;

    private $indices;

    private $matchField;

    private $enrichFields;

    private $query;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIndices(): ?array
    {
        return $this->indices;
    }

    public function setIndices(?array $indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    public function getMatchField(): ?string
    {
        return $this->matchField;
    }

    public function setMatchField(?string $matchField): self
    {
        $this->matchField = $matchField;

        return $this;
    }

    public function getEnrichFields(): ?array
    {
        return $this->enrichFields;
    }

    public function setEnrichFields(?array $enrichFields): self
    {
        $this->enrichFields = $enrichFields;

        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public static function getTypes(): ?array
    {
        return [
            'match' => 'match',
            'geo_match' => 'geo_match',
        ];
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $row): self
    {
        $policy = [];
        $policy['type'] = key($row['config']);
        $policy['name'] = $row['config'][$policy['type']]['name'];
        $policy['indices'] = $row['config'][$policy['type']]['indices'];
        $policy['match_field'] = $row['config'][$policy['type']]['match_field'];
        $policy['enrich_fields'] = $row['config'][$policy['type']]['enrich_fields'];
        $policy['query'] = $row['config'][$policy['type']]['query'] ?? false;

        $this->setType($policy['type']);
        $this->setName($policy['name']);
        if (true == isset($policy['indices'])) {
            $this->setIndices($policy['indices']);
        }
        $this->setMatchField($policy['match_field']);
        if (true == isset($policy['enrich_fields'])) {
            $this->setEnrichFields($policy['enrich_fields']);
        }
        if (true == isset($policy['query'])) {
            $this->setQuery($policy['query']);
        }
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            $this->getType() => [
                'indices' => $this->getIndices(),
                'match_field' => $this->getMatchField(),
                'enrich_fields' => $this->getEnrichFields(),
            ],
        ];

        if ($this->getQuery()) {
            $json[$this->getType()]['query'] = $this->getQuery();
        }

        return $json;
    }
}
