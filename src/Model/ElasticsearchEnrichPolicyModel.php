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
}
