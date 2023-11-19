<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchNodeModel extends AbstractAppModel
{
    private ?string $id = null;

    private ?string $name = null;

    private ?string $ip = null;

    private ?string $version = null;

    /**
     * @var array<mixed>|null $os
     */
    private ?array $os = null;

    /**
     * @var array<mixed>|null $roles
     */
    private ?array $roles = null;

    /**
     * @var array<mixed>|null $settings
     */
    private ?array $settings = null;

    /**
     * @var array<mixed>|null $plugins
     */
    private ?array $plugins = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

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

    /**
     * @return array<mixed>|null
     */
    public function getOs(): ?array
    {
        return $this->os;
    }

    /**
     * @param array<mixed>|null $os
     */
    public function setOs(?array $os): self
    {
        $this->os = $os;

        return $this;
    }

    /**
     * @return array<mixed>|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array<mixed>|null $roles
     */
    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        if ($roles = $this->getRoles()) {
            return in_array($role, $roles);
        }

        return false;
    }

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

    /**
     * @return array<mixed>|null
     */
    public function getPlugins(): ?array
    {
        return $this->plugins;
    }

    /**
     * @param array<mixed>|null $plugins
     */
    public function setPlugins(?array $plugins): self
    {
        $this->plugins = $plugins;

        return $this;
    }

    /**
     * @param array<mixed>|null $node
     */
    public function convert(?array $node): self
    {
        if (true === isset($node['id'])) {
            $this->setId($node['id']);
        }

        if (true === isset($node['name'])) {
            $this->setName($node['name']);
        }

        if (true === isset($node['ip'])) {
            $this->setIp($node['ip']);
        }

        if (true === isset($node['version'])) {
            $this->setVersion($node['version']);
        }

        if (true === isset($node['os']) && 0 < count($node['os'])) {
            $this->setOs($node['os']);
        }

        if (true === isset($node['roles']) && 0 < count($node['roles'])) {
            $this->setRoles($node['roles']);
        }

        if (true === isset($node['settings']) && 0 < count($node['settings'])) {
            $this->setSettings($node['settings']);
        }

        if (true === isset($node['plugins']) && 0 < count($node['plugins'])) {
            $this->setPlugins($node['plugins']);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
