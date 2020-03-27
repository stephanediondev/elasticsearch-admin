<?php

namespace App\Model;

class ElasticsearchSlmPolicyModel
{
    private $name;

    private $snapshotName;

    private $repository;

    private $indices;

    private $schedule;

    private $expireAfter;

    private $minCount;

    private $maxCount;

    public function __construct()
    {
        $this->snapshotName = '<nightly-snap-{now/d}>';
        $this->schedule = '0 30 1 * * ?';
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

    public function getSnapshotName(): ?string
    {
        return $this->snapshotName;
    }

    public function setSnapshotName(?string $snapshotName): self
    {
        $this->snapshotName = $snapshotName;

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

    public function getIndices(): ?array
    {
        return $this->indices;
    }

    public function setIndices(?array $indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    public function getSchedule(): ?string
    {
        return $this->schedule;
    }

    public function setSchedule(?string $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getExpireAfter(): ?string
    {
        return $this->expireAfter;
    }

    public function setExpireAfter(?string $expireAfter): self
    {
        $this->expireAfter = $expireAfter;

        return $this;
    }

    public function getMinCount(): ?int
    {
        return $this->minCount;
    }

    public function setMinCount(?int $minCount): self
    {
        $this->minCount = $minCount;

        return $this;
    }

    public function getMaxCount(): ?int
    {
        return $this->maxCount;
    }

    public function setMaxCount(?int $maxCount): self
    {
        $this->maxCount = $maxCount;

        return $this;
    }

    public function hasRetention(): ?bool
    {
        if ($this->getExpireAfter() || $this->getMinCount() || $this->getMaxCount()) {
            return true;
        }

        return false;
    }

    public function getRetention(): ?array
    {
        $retention = [];
        if ($this->getExpireAfter()) {
            $retention['expire_after'] = $this->getExpireAfter();
        }

        if ($this->getMinCount()) {
            $retention['min_count'] = $this->getMinCount();
        }

        if ($this->getMaxCount()) {
            $retention['max_count'] = $this->getMaxCount();
        }

        return $retention;
    }
}
