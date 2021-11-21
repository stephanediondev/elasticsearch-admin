<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchDataStreamModel extends AbstractAppModel
{
    private ?string $name = null;

    private ?string $timestampFieldName = null;

    private ?array $indices = null;

    private ?int $generation = null;

    private ?string $status = null;

    private ?string $template = null;

    private ?string $ilmPolicy = null;

    private ?bool $hidden = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTimestampFieldName(): ?string
    {
        return $this->timestampFieldName;
    }

    public function setTimestampFieldName(?string $timestampFieldName): self
    {
        $this->timestampFieldName = $timestampFieldName;

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

    public function getGeneration(): ?int
    {
        return $this->generation;
    }

    public function setGeneration(?int $generation): self
    {
        $this->generation = $generation;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getIlmPolicy(): ?string
    {
        return $this->ilmPolicy;
    }

    public function setIlmPolicy(?string $mappingsFlat): self
    {
        $this->ilmPolicy = $mappingsFlat;

        return $this;
    }

    public function isHidden(): ?bool
    {
        return $this->hidden;
    }

    public function convert(?array $stream): self
    {
        if (true === isset($stream['name'])) {
            $this->setName($stream['name']);
        }

        if (true === isset($stream['status'])) {
            $this->setStatus(strtolower($stream['status']));
        }

        if (true === isset($stream['generation'])) {
            $this->setGeneration(intval($stream['generation']));
        }

        if (true === isset($stream['indices']) && 0 < count($stream['indices'])) {
            $this->setIndices($stream['indices']);
        }

        if (true === isset($stream['timestamp_field']['name'])) {
            $this->setTimestampFieldName($stream['timestamp_field']['name']);
        }

        if (true === isset($stream['template'])) {
            $this->setTemplate($stream['template']);
        }

        if (true === isset($stream['ilm_policy'])) {
            $this->setIlmPolicy($stream['ilm_policy']);
        }

        if (true === isset($stream['hidden'])) {
            $this->hidden = $stream['hidden'];
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
