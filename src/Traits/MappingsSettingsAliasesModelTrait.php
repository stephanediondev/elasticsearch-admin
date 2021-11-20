<?php

namespace App\Traits;

trait MappingsSettingsAliasesModelTrait
{
    private ?array $settings = null;

    private ?string $settingsJson = null;

    private ?array $mappings = null;

    private ?string $mappingsJson = null;

    private ?array $aliases = null;

    private ?string $aliasesJson = null;

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getSettingsJson(): ?string
    {
        return $this->settingsJson;
    }

    public function setSettingsJson(?string $settingsJson): self
    {
        $this->settingsJson = $settingsJson;

        return $this;
    }

    public function getSetting(string $key): ?string
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

    public function setMappings(?array $mappings): self
    {
        $this->mappings = $mappings;

        return $this;
    }

    public function getMappingsJson(): ?string
    {
        return $this->mappingsJson;
    }

    public function setMappingsJson(?string $mappingsJson): self
    {
        $this->mappingsJson = $mappingsJson;

        return $this;
    }

    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    public function setAliases(?array $aliases): self
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function getAliasesJson(): ?string
    {
        return $this->aliasesJson;
    }

    public function setAliasesJson(?string $aliasesJson): self
    {
        $this->aliasesJson = $aliasesJson;

        return $this;
    }
}
