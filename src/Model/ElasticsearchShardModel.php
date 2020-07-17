<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchShardModel extends AbstractAppModel
{
    private $number;

    private $index;

    private $primaryOrReplica;

    private $state;

    private $unassignedReason;

    private $documents;

    private $size;

    private $node;

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

    public function getPrimaryOrReplica(): ?string
    {
        return $this->primaryOrReplica;
    }

    public function setPrimaryOrReplica(?string $primaryOrReplica): self
    {
        $this->primaryOrReplica = $primaryOrReplica;

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

    public function getUnassignedReason(): ?string
    {
        return $this->unassignedReason;
    }

    public function setUnassignedReason(?string $unassignedReason): self
    {
        $this->unassignedReason = $unassignedReason;

        return $this;
    }

    public function getDocuments(): ?int
    {
        return $this->documents;
    }

    public function setDocuments(?int $documents): self
    {
        $this->documents = $documents;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

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


    public function isPrimary(): bool
    {
        return 'p' == $this->getPrimaryOrReplica();
    }

    public function isReplica(): bool
    {
        return 'r' == $this->getPrimaryOrReplica();
    }

    public function convert(?array $shard): self
    {
        $this->setNumber($shard['shard']);
        $this->setIndex($shard['index']);
        $this->setPrimaryOrReplica($shard['prirep']);
        $this->setState($shard['state']);
        $this->setUnassignedReason($shard['unassigned.reason']);
        $this->setDocuments($shard['docs']);
        $this->setSize($shard['store']);
        $this->setNode($shard['node']);
        return $this;
    }
}
