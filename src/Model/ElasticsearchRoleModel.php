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

    public function getApplications(): ?string
    {
        return $this->applications;
    }

    public function setApplications(?string $applications): self
    {
        $this->applications = $applications;

        return $this;
    }

    public function getCluster(): ?array
    {
        return $this->cluster;
    }

    public function setCluster(?array $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function getIndices(): ?string
    {
        return $this->indices;
    }

    public function setIndices(?string $indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    public function getRunAs(): ?array
    {
        return $this->runAs;
    }

    public function setRunAs(?array $runAs): self
    {
        $this->runAs = $runAs;

        return $this;
    }

    public function getMetadata(): ?string
    {
        return $this->metadata;
    }

    public function setMetadata(?string $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function convert(?array $role): self
    {
        $this->setName($role['name']);
        $this->setCluster($role['cluster']);
        $this->setRunAs($role['run_as']);
        if (true == isset($role['indices']) && 0 < count($role['indices'])) {
            $this->setIndices(json_encode($role['indices'], JSON_PRETTY_PRINT));
        }
        if (true == isset($role['applications']) && 0 < count($role['applications'])) {
            $this->setApplications(json_encode($role['applications'], JSON_PRETTY_PRINT));
        }
        if (true == isset($role['metadata']) && 0 < count($role['metadata'])) {
            $this->setMetadata(json_encode($role['metadata'], JSON_PRETTY_PRINT));
        }
        return $this;
    }
}
