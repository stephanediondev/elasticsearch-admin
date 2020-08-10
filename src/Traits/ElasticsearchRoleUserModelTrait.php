<?php

namespace App\Traits;

trait ElasticsearchRoleUserModelTrait
{
    private $metadata;

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
}
