<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchShardRerouteModel extends AbstractAppModel
{
    private ?int $number = null;

    private ?string $index = null;

    private ?string $command = null;

    private ?string $state = null;

    private ?string $node = null;

    private ?string $toNode = null;

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

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

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(?string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getNode(): ?string
    {
        return $this->node;
    }

    public function setNode(?string $node): self
    {
        $this->node = $node;

        return $this;
    }

    public function getToNode(): ?string
    {
        return $this->toNode;
    }

    public function setToNode(?string $toNode): self
    {
        $this->toNode = $toNode;

        return $this;
    }

    public function convert(?array $shard): self
    {
        if (true === isset($shard['shard'])) {
            $this->setNumber($shard['shard']);
        }

        if (true === isset($shard['index'])) {
            $this->setIndex($shard['index']);
        }

        if (true === isset($shard['state'])) {
            $this->setState(strtolower($shard['state']));
        }

        if (true === isset($shard['node'])) {
            $this->setNode($shard['node']);
        }

        return $this;
    }
}
