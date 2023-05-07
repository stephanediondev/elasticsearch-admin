<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchSqlModel extends AbstractAppModel
{
    private ?string $query = null;

    private ?string $filter = null;

    private ?int $fetchSize = null;

    public function __construct()
    {
        $this->fetchSize = 100;
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

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function getFetchSize(): ?int
    {
        return $this->fetchSize;
    }

    public function setFetchSize(?int $fetchSize): self
    {
        $this->fetchSize = $fetchSize;

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'query' => $this->getQuery(),
            'fetch_size' => $this->getFetchSize(),
        ];

        if ($this->getFilter()) {
            $json['filter'] = json_decode($this->getFilter(), true);
        }

        return $json;
    }
}
