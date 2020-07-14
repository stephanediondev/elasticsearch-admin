<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class AppRoleModel extends AbstractAppModel
{
    private $id;

    private $name;

    private $createdAt;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function convert(?array $role): self
    {
        $this->setId($role['id']);
        $this->setName($role['name']);
        if (true == isset($role['created_at'])) {
            $this->setCreatedAt(new \Datetime($role['created_at']));
        }
        return $this;
    }
}
