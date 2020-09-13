<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchSlmPolicyModel extends AbstractAppModel
{
    private $name;

    private $snapshotName;

    private $repository;

    private $indices;

    private $schedule;

    private $expireAfter;

    private $minCount;

    private $maxCount;

    private $ignoreUnavailable;

    private $partial;

    private $includeGlobalState;

    private $nextExecution;

    private $version;

    private $lastSuccess;

    private $lastFailure;

    private $modifiedDate;

    public function __construct()
    {
        $this->snapshotName = '<nightly-snap-{now/d}>';
        $this->schedule = '0 30 1 * * ?';
        $this->includeGlobalState = true;
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

    public function getIgnoreUnavailable(): ?bool
    {
        return $this->ignoreUnavailable;
    }

    public function setIgnoreUnavailable(?bool $ignoreUnavailable): self
    {
        $this->ignoreUnavailable = $ignoreUnavailable;

        return $this;
    }

    public function getPartial(): ?bool
    {
        return $this->partial;
    }

    public function setPartial(?bool $partial): self
    {
        $this->partial = $partial;

        return $this;
    }

    public function getIncludeGlobalState(): ?bool
    {
        return $this->includeGlobalState;
    }

    public function setIncludeGlobalState(?bool $includeGlobalState): self
    {
        $this->includeGlobalState = $includeGlobalState;

        return $this;
    }

    public function getNextExecution(): ?int
    {
        return $this->nextExecution;
    }

    public function setNextExecution(?int $nextExecution): self
    {
        $this->nextExecution = $nextExecution;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(?int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getLastSuccess(): ?array
    {
        return $this->lastSuccess;
    }

    public function setLastSuccess(?array $lastSuccess): self
    {
        $this->lastSuccess = $lastSuccess;

        return $this;
    }

    public function getLastFailure(): ?array
    {
        return $this->lastFailure;
    }

    public function setLastFailure(?array $lastFailure): self
    {
        $this->lastFailure = $lastFailure;

        return $this;
    }

    public function getModifiedDate(): ?int
    {
        return $this->modifiedDate;
    }

    public function setModifiedDate(?int $modifiedDate): self
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    public function getStats(): ?array
    {
        return $this->stats;
    }

    public function setStats(?array $stats): self
    {
        $this->stats = $stats;

        return $this;
    }

    public function isSystem(): ?bool
    {
        return '.' == substr($this->getName(), 0, 1);
    }

    public function convert(?array $policy): self
    {
        $this->setName($policy['name']);
        $this->setSnapshotName($policy['policy']['name']);
        $this->setSchedule($policy['policy']['schedule']);
        $this->setRepository($policy['policy']['repository']);

        if (true === isset($policy['policy']['config']['indices'])) {
            $this->setIndices($policy['policy']['config']['indices']);
        }

        if (true === isset($policy['policy']['retention']['expire_after'])) {
            $this->setExpireAfter($policy['policy']['retention']['expire_after']);
        }

        if (true === isset($policy['policy']['retention']['min_count'])) {
            $this->setMinCount($policy['policy']['retention']['min_count']);
        }

        if (true === isset($policy['policy']['retention']['max_count'])) {
            $this->setMaxCount($policy['policy']['retention']['max_count']);
        }

        if (true === isset($policy['policy']['config']['ignore_unavailable'])) {
            $this->setIgnoreUnavailable($this->convertBoolean($policy['policy']['config']['ignore_unavailable']));
        }

        if (true === isset($policy['policy']['config']['partial'])) {
            $this->setPartial($this->convertBoolean($policy['policy']['config']['partial']));
        }

        if (true === isset($policy['policy']['config']['include_global_state'])) {
            $this->setIncludeGlobalState($this->convertBoolean($policy['policy']['config']['include_global_state']));
        }

        if (true === isset($policy['next_execution_millis'])) {
            $this->setNextExecution($policy['next_execution_millis']);
        }

        if (true === isset($policy['version'])) {
            $this->setVersion($policy['version']);
        }

        if (true === isset($policy['last_success'])) {
            $this->setLastSuccess($policy['last_success']);
        }

        if (true === isset($policy['last_failure'])) {
            $this->setLastFailure($policy['last_failure']);
        }

        if (true === isset($policy['modified_date_millis'])) {
            $this->setModifiedDate($policy['modified_date_millis']);
        }

        if (true === isset($policy['stats'])) {
            $this->setStats($policy['stats']);
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'schedule' => $this->getSchedule(),
            'name' => $this->getSnapshotName(),
            'repository' => $this->getRepository(),
        ];

        if ($this->getIndices()) {
            $json['config']['indices'] = $this->getIndices();
        } else {
            $json['config']['indices'] = ['*'];
        }

        $json['config']['ignore_unavailable'] = $this->getIgnoreUnavailable();

        $json['config']['partial'] = $this->getPartial();

        $json['config']['include_global_state'] = $this->getIncludeGlobalState();

        if ($this->hasRetention()) {
            $json['retention'] = $this->getRetention();
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
