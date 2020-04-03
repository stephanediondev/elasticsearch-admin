<?php

namespace App\Model;

use App\Model\AbstractAppModel;

class ElasticsearchUserModel extends AbstractAppModel
{
    private $username;

    private $enabled;

    private $email;

    private $fullName;

    private $password;

    private $roles;

    public function __construct()
    {
        $this->enabled = true;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

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

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function convert(?array $user): self
    {
        $this->setUsername($user['username']);
        $this->setFullName($user['full_name']);
        $this->setEmail($user['email']);
        $this->setRoles($user['roles']);
        return $this;
    }
}
