<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchNodeModel extends AbstractAppModel
{
    private $name;

    private $ip;

    private $version;

    private $os;

    private $settings;

    private $plugins;

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

    public function setOs($os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function setSettings($settings): self
    {
        $this->settings = $settings;

        return $this;
    }

    public function getPlugins(): ?array
    {
        return $this->plugins;
    }

    public function setPlugins($plugins): self
    {
        $this->plugins = $plugins;

        return $this;
    }

    public function convert(?array $node): self
    {
        dump($node);
        $this->setName($node['name']);

        if (true == isset($node['ip'])) {
            $this->setIp($node['ip']);
        }

        if (true == isset($node['os']) && 0 < count($node['os'])) {
            $this->setOs($node['os']);
        }

        if (true == isset($node['settings']) && 0 < count($node['settings'])) {
            $this->setSettings($node['settings']);
        }

        if (true == isset($node['plugins']) && 0 < count($node['plugins'])) {
            $this->setPlugins($node['plugins']);
        }

        return $this;
    }
}
