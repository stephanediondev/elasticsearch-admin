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

    private ?array $os = null;

    private ?array $roles = null;

    private ?array $settings = null;

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

    public function getOs(): ?array
    {
        return $this->os;
    }

    public function setOs(?array $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings(?array $settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getPlugins(): ?array
    {
        return $this->plugins;
    }

    public function setPlugins(?array $plugins): self
    {
        $this->plugins = $plugins;

        return $this;
    }

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
        return $this->name;
    }
}
