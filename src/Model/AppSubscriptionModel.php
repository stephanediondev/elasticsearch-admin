<?php
declare(strict_types=1);

namespace App\Model;

use App\Model\AbstractAppModel;

class AppSubscriptionModel extends AbstractAppModel
{
    const TYPE_PUSH = 'push';
    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';
    const TYPE_SLACK = 'slack';
    const TYPE_TEAMS = 'teams';

    private ?string $id = null;

    private ?string $type = null;

    private ?string $userId = null;

    private ?string $endpoint = null;

    private ?string $publicKey = null;

    private ?string $authenticationSecret = null;

    private ?string $contentEncoding = null;

    private ?string $ip = null;

    private ?string $os = null;

    private ?string $client = null;

    private ?array $notifications = null;

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

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setEndpoint(?string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): self
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    public function getAuthenticationSecret(): ?string
    {
        return $this->authenticationSecret;
    }

    public function setAuthenticationSecret(?string $authenticationSecret): self
    {
        $this->authenticationSecret = $authenticationSecret;

        return $this;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(?string $contentEncoding): self
    {
        $this->contentEncoding = $contentEncoding;

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

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getNotifications(): ?array
    {
        return array_values($this->notifications);
    }

    public function setNotifications(?array $notifications): self
    {
        $this->notifications = $notifications;

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

    public function convert(?array $subscription): self
    {
        $this->setId($subscription['id']);
        $this->setType($subscription['type'] ?? self::TYPE_PUSH);
        $this->setUserId($subscription['user_id']);
        $this->setEndpoint($subscription['endpoint']);
        if (true === isset($subscription['public_key'])) {
            $this->setPublicKey($subscription['public_key']);
        }
        if (true === isset($subscription['authentication_secret'])) {
            $this->setAuthenticationSecret($subscription['authentication_secret']);
        }
        if (true === isset($subscription['content_encoding'])) {
            $this->setContentEncoding($subscription['content_encoding']);
        }
        $this->setIp($subscription['ip']);
        $this->setOs($subscription['os']);
        $this->setClient($subscription['client']);
        if (true === isset($subscription['notifications']) && 0 < count($subscription['notifications'])) {
            $this->setNotifications($subscription['notifications']);
        }
        $this->setCreatedAt(new \Datetime($subscription['created_at']));
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'user_id' => $this->getUserId(),
            'type' => $this->getType(),
            'endpoint' => $this->getEndpoint(),
            'public_key' => $this->getPublicKey(),
            'authentication_secret' => $this->getAuthenticationSecret(),
            'content_encoding' => $this->getContentEncoding(),
            'ip' => $this->getIp(),
            'os' => $this->getOs(),
            'client' => $this->getClient(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        if ($this->getNotifications()) {
            $json['notifications'] = $this->getNotifications();
        }

        return $json;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PUSH,
            self::TYPE_EMAIL,
            self::TYPE_SMS,
            self::TYPE_SLACK,
            self::TYPE_TEAMS,
        ];
    }
}
