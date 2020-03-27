<?php

namespace App\Model;

class ElasticsearchRepositoryModel
{
    private $type;

    private $name;

    private $location;

    private $compress;

    private $chunkSize;

    private $maxRestoreBytesPerSec;

    private $maxSnapshotBytesPerSec;

    private $readonly;

    public function __construct()
    {
        $this->type = 'fs';
        $this->compress = true;
        $this->chunkSize = null;
        $this->maxRestoreBytesPerSec = '40mb';
        $this->maxSnapshotBytesPerSec = '40mb';
        $this->readonly = false;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getCompress(): ?bool
    {
        return $this->compress;
    }

    public function setCompress(?bool $compress): self
    {
        $this->compress = $compress;

        return $this;
    }

    public function getChunkSize(): ?string
    {
        return $this->chunkSize;
    }

    public function setChunkSize(?string $chunkSize): self
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    public function getMaxRestoreBytesPerSec(): ?string
    {
        return $this->maxRestoreBytesPerSec;
    }

    public function setMaxRestoreBytesPerSec(?string $maxRestoreBytesPerSec): self
    {
        $this->maxRestoreBytesPerSec = $maxRestoreBytesPerSec;

        return $this;
    }

    public function getMaxSnapshotBytesPerSec(): ?string
    {
        return $this->maxSnapshotBytesPerSec;
    }

    public function setMaxSnapshotBytesPerSec(?string $maxSnapshotBytesPerSec): self
    {
        $this->maxSnapshotBytesPerSec = $maxSnapshotBytesPerSec;

        return $this;
    }

    public function getReadonly(): ?bool
    {
        return $this->readonly;
    }

    public function setReadonly(?bool $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }
}
