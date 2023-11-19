<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;
use App\Traits\ElasticsearchRoleUserModelTrait;

class ElasticsearchRoleModel extends AbstractAppModel
{
    use ElasticsearchRoleUserModelTrait;

    private ?string $name = null;

    /**
     * @var array<mixed>|null $applications
     */
    private ?array $applications = null;

    private ?string $applicationsJson = null;

    /**
     * @var array<mixed>|null $cluster
     */
    private ?array $cluster = null;

    /**
     * @var array<mixed>|null $indices
     */
    private ?array $indices = null;

    private ?string $indicesJson = null;

    /**
     * @var array<mixed>|null $runAs
     */
    private ?array $runAs = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getApplications(): ?array
    {
        return $this->applications;
    }

    /**
     * @param array<mixed>|null $applications
     */
    public function setApplications(?array $applications): self
    {
        $this->applications = $applications;

        return $this;
    }

    public function getApplicationsJson(): ?string
    {
        return $this->applicationsJson;
    }

    public function setApplicationsJson(?string $applicationsJson): self
    {
        $this->applicationsJson = $applicationsJson;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getCluster(): ?array
    {
        return $this->cluster;
    }

    /**
     * @param array<mixed> $cluster
     */
    public function setCluster(array $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getIndices(): ?array
    {
        return $this->indices;
    }

    /**
     * @param array<mixed>|null $indices
     */
    public function setIndices(?array $indices): self
    {
        $this->indices = $indices;

        return $this;
    }

    public function getIndicesJson(): ?string
    {
        return $this->indicesJson;
    }

    public function setIndicesJson(?string $indicesJson): self
    {
        $this->indicesJson = $indicesJson;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getRunAs(): ?array
    {
        return $this->runAs;
    }

    /**
     * @param array<mixed>|null $runAs
     */
    public function setRunAs(?array $runAs): self
    {
        $this->runAs = $runAs;

        return $this;
    }

    /**
     * @param array<mixed>|null $role
     */
    public function convert(?array $role): self
    {
        if (true === isset($role['name'])) {
            $this->setName($role['name']);
        }

        if (true === isset($role['cluster'])) {
            $this->setCluster($role['cluster']);
        }

        if (true === isset($role['run_as'])) {
            $this->setRunAs($role['run_as']);
        }

        if (true === isset($role['indices']) && 0 < count($role['indices'])) {
            $this->setIndices($role['indices']);
            if ($json = json_encode($role['indices'], JSON_PRETTY_PRINT)) {
                $this->setIndicesJson($json);
            }
        }

        if (true === isset($role['applications']) && 0 < count($role['applications'])) {
            $this->setApplications($role['applications']);
            if ($json = json_encode($role['applications'], JSON_PRETTY_PRINT)) {
                $this->setApplicationsJson($json);
            }
        }

        if (true === isset($role['metadata']) && 0 < count($role['metadata'])) {
            $this->setMetadata($role['metadata']);
            if ($json = json_encode($role['metadata'], JSON_PRETTY_PRINT)) {
                $this->setMetadataJson($json);
            }
        }

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getJson(): array
    {
        $json = [
            'cluster' => $this->getCluster(),
            'run_as' => $this->getRunAs(),
        ];

        if ($this->getApplicationsJson()) {
            $json['applications'] = json_decode($this->getApplicationsJson(), true);
        }

        if ($this->getIndicesJson()) {
            $json['indices'] = json_decode($this->getIndicesJson(), true);
        }

        if ($this->getMetadataJson()) {
            $json['metadata'] = json_decode($this->getMetadataJson(), true);
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
