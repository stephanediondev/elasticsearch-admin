<?php

declare(strict_types=1);

namespace App\Traits;

trait MappingsSettingsAliasesModelTrait
{
    /**
     * @var array<mixed>|null $settings
     */
    private ?array $settings = null;

    private ?string $settingsJson = null;

    /**
     * @var array<mixed>|null $mappings
     */
    private ?array $mappings = null;

    private ?string $mappingsJson = null;

    /**
     * @var array<mixed>|null $aliases
     */
    private ?array $aliases = null;

    private ?string $aliasesJson = null;

    /**
     * @return array<mixed>|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /**
     * @param array<mixed>|null $settings
     */
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

    public function getSetting(string $key): mixed
    {
        return $this->settings[$key] ?? null;
    }

    public function setSetting(?string $key, ?string $value): self
    {
        $this->settings[$key] = $value;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getMappings(): ?array
    {
        return $this->mappings;
    }

    /**
     * @param array<mixed>|null $mappings
     */
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

    /**
     * @return array<mixed>|null
     */
    public function getAliases(): ?array
    {
        return $this->aliases;
    }

    /**
     * @param array<mixed>|null $aliases
     */
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
