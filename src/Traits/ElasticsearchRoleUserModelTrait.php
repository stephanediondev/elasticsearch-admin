<?php

declare(strict_types=1);

namespace App\Traits;

trait ElasticsearchRoleUserModelTrait
{
    private ?array $metadata = null;

    private ?string $metadataJson = null;

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

        return null;
    }
}
