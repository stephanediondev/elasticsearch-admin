<?php

namespace App\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class AppUserModel implements UserInterface
{
    private $id;

    private $email;

    private $changePassword;

    private $password;

    private $passwordPlain;

    private $secretRegister;

    private $roles = [];

    private $createdAt;

    public function __construct()
    {
        $this->changePassword = false;
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

        return array_values(array_unique($roles));
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getChangePassword(): ?bool
    {
        return $this->changePassword;
    }

    public function setChangePassword(?bool $changePassword): self
    {
        $this->changePassword = $changePassword;

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
    }

    public function currentUserAdmin($userConnected): bool
    {
        if ($this->getId() == $userConnected->getId() && true === in_array('ROLE_ADMIN', $this->roles)) {
            return true;
        }

        return false;
    }

    public function convert(?array $user): self
    {
        $this->setId($user['id']);
        if (true === isset($user['email'])) {
            $this->setEmail($user['email']);
        }
        if (true === isset($user['password'])) {
            $this->setPassword($user['password']);
        }
        if (true === isset($user['roles'])) {
            $this->setRoles($user['roles']);
        }
        if (true === isset($user['created_at'])) {
            $this->setCreatedAt(new \Datetime($user['created_at']));
        }
        return $this;
    }

    public function getJson(): array
    {
        $json = [
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'roles' => $this->getRoles(),
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return $json;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
