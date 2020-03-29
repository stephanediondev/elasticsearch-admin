<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchCatModel extends AbstractAppModel
{
    private $command;

    private $index;

    private $repository;

    private $alias;

    private $headers;

    private $sort;

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getIndex(): ?string
    {
        return $this->index;
    }

    public function setIndex(?string $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function getRepository(): ?string
    {
        return $this->repository;
    }

    public function setRepository(?string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getHeaders(): ?string
    {
        return $this->headers;
    }

    public function setHeaders(?string $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function setSort(?string $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getCommandReplace(): ?string
    {
        $command = $this->command;

        if (strstr($this->command, '{index}')) {
            $command = str_replace('{index}', $this->index, $command);
        }

        if (strstr($this->command, '{repository}')) {
            $command = str_replace('{repository}', $this->repository, $command);
        }

        if (strstr($this->command, '{alias}')) {
            $command = str_replace('{alias}', $this->alias, $command);
        }

        return $command;
    }

    public function getCommandHelp(): ?string
    {
        $command = $this->command;

        if (strstr($this->command, '/')) {
            $command = substr($command, 0, strpos($command, '/'));
        }

        return $command;
    }
}
