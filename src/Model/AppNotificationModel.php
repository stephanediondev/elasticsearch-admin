<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class AppNotificationModel extends AbstractAppModel
{
    public const TYPE_CLUSTER_HEALTH = 'cluster_health';
    public const TYPE_NODE_DOWN = 'node_down';
    public const TYPE_NODE_UP = 'node_up';
    public const TYPE_DISK_THRESHOLD = 'disk_threshold';
    public const TYPE_LICENSE = 'license';
    public const TYPE_VERSION = 'version';

    private ?string $id = null;

    private ?string $type = null;

    private ?string $cluster = null;

    private ?string $title = null;

    private ?string $content = null;

    private ?string $color = null;

    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \Datetime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCluster(): ?string
    {
        return $this->cluster;
    }

    public function setCluster(string $cluster): self
    {
        $this->cluster = $cluster;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->getEmoji().' '.$this->getCluster().': '.$this->getTitle();
    }

    public function getEmoji(): ?string
    {
        switch ($this->getColor()) {
            case 'red':
                return "🟥";
            case 'orange':
                return "🟧";
            case 'yellow':
                return "🟨";
            case 'green':
                return "🟩";
            default:
                return "⬜";
        }
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_CLUSTER_HEALTH,
            self::TYPE_NODE_DOWN,
            self::TYPE_NODE_UP,
            self::TYPE_DISK_THRESHOLD,
            self::TYPE_LICENSE,
            self::TYPE_VERSION,
        ];
    }

    public function convert(?array $notification): self
    {
        if (true === isset($notification['id'])) {
            $this->setId($notification['id']);
        }

        if (true === isset($notification['type'])) {
            $this->setType($notification['type']);
        }

        if (true === isset($notification['cluster'])) {
            $this->setCluster($notification['cluster']);
        }

        if (true === isset($notification['title'])) {
            $this->setTitle($notification['title']);
        }

        if (true === isset($notification['content'])) {
            $this->setContent($notification['content']);
        }

        if (true === isset($notification['color'])) {
            $this->setColor($notification['color']);
        }

        if (true === isset($notification['created_at'])) {
            $this->setCreatedAt(new \Datetime($notification['created_at']));
        }

        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'type' => $this->getType(),
            'cluster' => $this->getCluster(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'color' => $this->getColor(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return $json;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
