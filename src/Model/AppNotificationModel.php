<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class AppNotificationModel extends AbstractAppModel
{
    const TYPE_CLUSTER_HEALTH = 'cluster_health';
    const TYPE_NODE_DOWN = 'node_down';
    const TYPE_NODE_UP = 'node_up';
    const TYPE_DISK_THRESHOLD = 'disk_threshold';
    const TYPE_LICENSE = 'license';
    const TYPE_VERSION = 'version';

    private $type;

    private $title;

    private $body;

    private $color;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

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

    public function getIcon(?int $size = 144): ?string
    {
        return 'favicon-'.$this->getColor().'-'.$size.'.png';
    }

    public function getSubject(): ?string
    {
        return $this->getEmoji().' '.$this->getTitle();
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

    public static function getTypes()
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
}
