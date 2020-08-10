<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchRoleModel extends AbstractAppModel
{
    private $name;

    private $applications;

    private $cluster;

    private $indices;

    private $runAs;

    private $metadata;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getApplications(): ?array
    {
        return $this->applications;
    }

    public function setApplications($applications): self
    {
        $this->applications = $applications;

        return $this;
    }

    public function getCluster(): ?array
    {
        return $this->cluster;
    }

    public function setCluster(array $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function getIndices(): ?array
    {
        return $this->indices;
    }

    public function setIndices(array $indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    public function getRunAs(): ?array
    {
        return $this->runAs;
    }

    public function setRunAs($runAs): self
    {
        $this->runAs = $runAs;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata($metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function isReserved(): ?bool
    {
        return true === isset($this->getMetadata()['_reserved']) && true === $this->getMetadata()['_reserved'];
    }

    public function isDeprecated(): ?bool
    {
        return true === isset($this->getMetadata()['_deprecated']) && true === $this->getMetadata()['_deprecated'];
    }

    public function getDeprecatedReason(): ?string
    {
        if (true === isset($this->getMetadata()['_deprecated_reason'])) {
            return $this->getMetadata()['_deprecated_reason'];
        }
    }

    public function convert(?array $role): self
    {
        $this->setName($role['name']);
        $this->setCluster($role['cluster']);
        $this->setRunAs($role['run_as']);
        if (true === isset($role['indices']) && 0 < count($role['indices'])) {
            $this->setIndices($role['indices']);
        }
        if (true === isset($role['applications']) && 0 < count($role['applications'])) {
            $this->setApplications($role['applications']);
        }
        if (true === isset($role['metadata']) && 0 < count($role['metadata'])) {
            $this->setMetadata($role['metadata']);
        }
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'cluster' => $this->getCluster(),
            'run_as' => $this->getRunAs(),
        ];

        if ($this->getApplications()) {
            $json['applications'] = $this->getApplications();
        }

        if ($this->getIndices()) {
            $json['indices'] = $this->getIndices();
        }

        if ($this->getMetadata()) {
            $json['metadata'] = $this->getMetadata();
        }

        return $json;
    }
}
