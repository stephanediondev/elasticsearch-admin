<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\ElasticsearchSnapshotModelTrait;

class ElasticsearchSnapshotModel extends AbstractAppModel
{
    use ElasticsearchSnapshotModelTrait;

    private ?string $name = null;

    private ?string $repository = null;

    private ?string $version = null;

    private ?array $failures = null;

    private ?string $state = null;

    private ?string $startTime = null;

    private ?string $endTime = null;

    private ?string $duration = null;

    private ?array $metadata = null;

    private ?string $metadataJson = null;

    public function __construct()
    {
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

    public function getRepository(): ?string
    {
        return $this->repository;
    }

    public function setRepository(?string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getFailures(): ?array
    {
        return $this->failures;
    }

    public function setFailures(?array $failures): self
    {
        $this->failures = $failures;

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

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(?string $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(?string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getMetadataJson(): ?string
    {
        return $this->metadataJson;
    }

    public function setMetadataJson(?string $metadataJson): self
    {
        $this->metadataJson = $metadataJson;

        return $this;
    }

    public function convert(?array $snapshot): self
    {
        $this->setName($snapshot['snapshot']);
        $this->setRepository($snapshot['repository']);

        if (true === isset($snapshot['version'])) {
            $this->setVersion($snapshot['version']);
        }

        if (true === isset($snapshot['state'])) {
            $this->setState(strtolower($snapshot['state']));
        }

        if (true === isset($snapshot['start_time'])) {
            $this->setStartTime($snapshot['start_time']);
        }

        if (true === isset($snapshot['end_time'])) {
            $this->setEndTime($snapshot['end_time']);
        }

        if (true === isset($snapshot['duration_in_millis'])) {
            $this->setDuration(round($snapshot['duration_in_millis']/1000, 2).'s');
        }

        if (true === isset($snapshot['failures'])) {
            $this->setFailures($snapshot['failures']);
        }

        if (true === isset($snapshot['indices'])) {
            $this->setIndices($snapshot['indices']);
        }

        if (true === isset($snapshot['metadata'])) {
            $this->setMetadata($snapshot['metadata']);
            $this->setMetadataJson(json_encode($snapshot['metadata'], JSON_PRETTY_PRINT));
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'ignore_unavailable' => $this->getIgnoreUnavailable(),
            'partial' => $this->getPartial(),
            'include_global_state' => $this->getIncludeGlobalState(),
        ];

        if ($this->getIndices()) {
            $json['indices'] = implode(',', $this->getIndices());
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
