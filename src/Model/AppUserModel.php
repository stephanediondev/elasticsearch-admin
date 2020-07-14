<?php

namespace App\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class AppUserModel implements UserInterface
{
    private $email;

    private $passwordPlain;

    private $secretRegister;

    private $roles = [];

    private $createdAt;

    /**
     * @var string The hashed password
     */
    private $password;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordPlain(): ?string
    {
        return $this->passwordPlain;
    }

    public function setPasswordPlain(string $passwordPlain): self
    {
        $this->passwordPlain = $passwordPlain;

        return $this;
    }

    public function getSecretRegister(): ?string
    {
        return $this->secretRegister;
    }

    public function setSecretRegister(string $secretRegister): self
    {
        $this->secretRegister = $secretRegister;

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

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function convert(?array $user): self
    {
        $this->setEmail($user['email']);
        $this->setRoles($user['roles']);
        $this->setCreatedAt(new \Datetime($user['created_at']));
        return $this;
    }
}
