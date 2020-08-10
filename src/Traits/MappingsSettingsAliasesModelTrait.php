<?php

namespace App\Traits;

trait MappingsSettingsAliasesModelTrait
{
    private $settings;

    private $mappings;

    private $aliases;

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings($settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSetting($key): ?string
    {
        return $this->settings[$key] ?? false;
    }

    public function setSetting(?string $key, ?string $value): self
    {
        $this->settings[$key] = $value;

        return $this;
    }

    public function getMappings(): ?array
    {
        return $this->mappings;
    }

    public function setMappings($mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function setAliases($aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }
}
